<?php

/**
 * PluginaEntity form.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class PluginaEntityForm extends BaseaEntityForm
{
  public function getUseFields()
  {
    return array('name', 'slug');
  }
  
  public function setup()
  {
  	parent::setup();
    $this->useFields($this->getUseFields());
  	unset($this['type']);
  	unset($this['created_at']);
  	unset($this['updated_at']);
    unset($this['pages_list']);
    if (isset($this['name']))
    {
      $this->getValidator('name')->setOption('required', true);
    }
    if (isset($this['slug']))
    {
      if ($this->getObject()->isNew())
      {
        unset($this['slug']);
      }
      else
      {
        $this->getValidator('slug')->setOption('required', true);
        $this->setValidator('slug', new sfValidatorAnd(array($this->getValidator('slug'), new sfValidatorCallback(array('callback' => array($this, 'validateSlug'))))));
      }
    }
  	// Redundant relation pointing back the other way
  	unset($this['a_entity_list']);
    aEntityTools::formConfigure($this, true);
    $this->addWidgetForBlogPosts();
    $this->addWidgetForEvents();
  }

  public function addWidgetForBlogPosts()
  {
    aBlogToolkit::addBlogItemsWidget($this, 'aBlogPost', 'blog_posts');
  }

  public function addWidgetForEvents()
  {
    aBlogToolkit::addBlogItemsWidget($this, 'aEvent', 'events');
  }

  /**
   * Avoid disastrous performance impact of letting Doctrine get its
   * mitts on the blog posts as related objects and then try to save them
   */
  public function getBlogItemIds($model, $name)
  {
    $sql = new aMysql();
    $type = ($model === 'aBlogPost') ? 'post' : 'event';
    return $sql->queryScalar('SELECT ae.blog_item_id FROM a_entity_to_blog_item ae INNER JOIN a_blog_item bi ON ae.entity_id = :entity_id AND ae.blog_item_id = bi.id AND bi.type = :type', array('entity_id' => $this->getObject()->getId(), 'type' => $type));
  }

  /**
   * Just make the slug unique rather than complaining
   */
  public function validateSlug($validator, $value)
  {
    while (true)
    {
      $existing = Doctrine::getTable('aEntity')->findOneBySlug($value);
      if ($existing && ($existing->id !== $this->getObject()->id))
      {
        $value .= rand(0, 9);
      }
      else
      {
        break;
      }
    }

    return $value;
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();
    aEntityTools::formUpdateDefaultsFromObject($this);
  }

  protected function doSave($con = null)
  {
    // The first save ensures we have an id for the object so
    // that formSaveLists can make backlinks successfully. The
    // second ensures that link and unlink actually did something
    // with the forward links
    parent::doSave($con);
    aEntityTools::formSaveLists($this, $con);
    $this->relateToBlogPostsAndEvents();
    $this->object->save();
  }

  public function relateToBlogPostsAndEvents()
  {
    $ids = array();
    if (!(isset($this['blog_posts']) || isset($this['events'])))
    {
      return;
    }
    if (isset($this['blog_posts']) && $this->getValue('blog_posts'))
    {
      $ids = array_merge($ids, $this->getValue('blog_posts'));
    }
    if (isset($this['events']) && $this->getValue('events'))
    {
      $ids = array_merge($ids, $this->getValue('events'));
    }
    $object = $this->getObject();

    // Relate the entity to its blog items the hard way to stay under
    // Doctrine's radar

    $sql = new aMysql();
    $sql->query('DELETE FROM a_entity_to_blog_item WHERE entity_id = :id', array('id' => $object->getId()));
    foreach ($ids as $id) 
    {
      $sql->insert('a_entity_to_blog_item', array('entity_id' => $object->getId(), 'blog_item_id' => $id));
    }

    // If we do it this way Doctrine insists on saving all the
    // blog items with disastrous performance consequences due to
    // search engine updates

    // $object->unlink('BlogItems');
    // $object->link('BlogItems', $ids);
  }
}
