<?php

/**
 * apostropheEntitiesPlugin configuration.
 * 
 * @package     apostropheEntitiesPlugin
 * @subpackage  config
 * @author      Your name here
 * @version     SVN: $Id: PluginConfiguration.class.php 17207 2009-04-10 15:36:26Z Kris.Wallsmith $
 */
class apostropheEntitiesPluginConfiguration extends sfPluginConfiguration
{
  static $registered = false;
  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    // Yes, this can get called twice. This is Fabien's workaround:
    // http://trac.symfony-project.org/ticket/8026
    
    if (!self::$registered)
    {
      $enabledModules = sfConfig::get('sf_enabled_modules', array());
      // Don't bomb if they didn't bother enabling the entity blog module
      if (in_array('aEntityBlog', $enabledModules))
      {
        $this->dispatcher->connect('aBlog.filterForEngineParams', array($this, 'blogfilterForEngineParams'));
        $this->dispatcher->connect('aBlog.extraFilterCriteria', array($this, 'blogFilterCriteria'));
      }
      self::$registered = true;
    }
  }

  public function blogFilterCriteria(sfEvent $event, $result)
  {
    $result[] = array(
      'leftJoin' => 'LEFT JOIN a_entity_to_blog_item aebi ON aebi.blog_item_id = bi.id LEFT JOIN a_entity ae ON aebi.entity_id = ae.id',
      'filterWhere' => 'ae.slug = :entity_slug',
      'filterKey' => 'entity_slug',
      // "Why are you using a subquery in a subquery?" Fetching just the ids
      // with a subquery and then populating everything else is dramatically
      // faster in my tests. Like, nearly zero time instead of seven seconds
      // with a big database. However to make sure MySQL understands it doesn't
      // have to run the inner query once for every row of the outer, we must
      // "decorrelate" it by giving it an alias of its own and passing
      // it through a "SELECT *" to break any possible link between the inner
      // and outer queries. It's pretty deep.
      //
      // Adding DISTINCT also helps a ton when there are lots of entities.
      //
      // See:
      // http://stackoverflow.com/questions/6135376/mysql-select-where-field-in-subquery-extremely-slow-why
      'selectTemplate' => 'SELECT ae.* FROM a_entity ae WHERE ae.id IN (SELECT * FROM (SELECT DISTINCT(ae.id) %QUERY% AND ae.id IS NOT NULL) AS subquery) ORDER BY ae.name ASC',
      'arrayKey' => 'entities',
      'urlParameter' => 'entity',
      'urlColumn' => 'slug',
      'labelTemplate' => '<span>concerning</span> %value%',
      // This plugin now requires PHP 5.3+. I think we can all live with that for
      // a bleeding edge plugin since 5.4+ is now stable.
      'labelValueCallback' => function($slug)
      {
        $entity = Doctrine::getTable('aEntity')->findOneBySlug($slug);
        if (!$entity)
        {
          return '?';
        }
        return $entity->getName();
      },
      'sidebarComponent' => array('aEntityBlog', 
        'sidebar')
    );
    return $result;
  }

  public function blogFilterForEngineParams(sfEvent $event, $options)
  {
    if ($event['request']->getParameter('entity'))
    {
      $options['entity_slug'] = $event['request']->getParameter('entity');
    }
    return $options;
  }
}
