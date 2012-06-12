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
    if ($this->getObject()->isNew())
    {
      unset($this['slug']);
    }
    else
    {
      $this->getValidator('slug')->setOption('required', true);
      $this->setValidator('slug', new sfValidatorAnd(array($this->getValidator('slug'), new sfValidatorCallback(array('callback' => array($this, 'validateSlug'))))));
    }
  	// Redundant relation pointing back the other way
  	unset($this['a_entity_list']);
    aEntityTools::formConfigure($this, true);
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
    $this->object->save();
  }
}
