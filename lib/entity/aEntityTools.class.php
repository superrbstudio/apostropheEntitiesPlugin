<?php

/**
 * Utilities to extend form classes to provide distinct widgets for association with
 * each subclass of entity. Also utilities to get enough information to intelligently
 * label widgets, give them class names, etc. for each subclass.
 */
class aEntityTools
{
  /**
   * Everything you need to know to label each subclass, apply a CSS class to it, 
   * display its list widget in a form... that kind of thing should be returned here
   */
  static public function getClassInfos()
  {
    $infos = array();
    $labels = sfConfig::get('app_aEntities_labels', array());
    foreach (Doctrine::getTable('aEntity')->getOption('subclasses') as $class)
    {
      $infos[$class] = array(
        'singular' => isset($labels[$class]['singular']) ? $labels[$class]['singular'] : $class, 
        'plural' => isset($labels[$class]['plural']) ? $labels[$class]['plural'] : ($class . 's'), 
        'css' => strtolower($class), 
        'cssPlural' => strtolower($class) . 's',
        'list' => strtolower($class) . '_list',
      );
    }
    return $infos;
  }

  /**
   * Create separate widgets to associate the object of the given form
   * with one or more entities of each subclass. If the form is
   * for an entity object, use excludeSelf to prevent the entity from
   * being associated with itself. If the form is not an entity object
   * that parameter should be false.
   */
  static public function formConfigure($form, $excludeSelf)
  {
    // Valid but all jumbled up; we'll substitute a widget for each subclass
    unset($form['entities_list']);
    // Widgets and validators to associate independently with each subclass of Entity
    foreach (Doctrine::getTable('aEntity')->getOption('subclasses') as $class)
    {
      // Exclude self
      $tableMethod = Doctrine::getTable($class)->createQuery();
      if ($excludeSelf && (!$form->isNew()))
      {
        $tableMethod->andWhere('id <> ?', $form->getObject()->getId());
      }
      $list = aEntityTools::listForClass($class);
      error_log($list);
      $form->setWidget($list, new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => $class, 'query' => $tableMethod)));
      $form->setValidator($list, new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => $class, 'required' => false, 'query' => $tableMethod)));
    }
  }

  /**
   * Return all entities in an associative array by class name. A cache of this
   * information is added to the $context object as $context->aEntitiesByClass to
   * avoid performance issues. TODO: address the hydration performance issues here
   * if necessary
   */
  static public function formGetEntitiesByClass($form)
  {
    if (!isset($form->aEntitiesByClass))
    {
      $entitiesByClass = array();
      foreach (Doctrine::getTable('aEntity')->getOption('subclasses') as $class)
      {
        $entitiesByClass[$class] = array();
      }
      foreach ($form->getObject()->Entities as $entity)
      {
        $entitiesByClass[$entity->type][$entity->id] = $entity;
      }
      $form->aEntitiesByClass = $entitiesByClass;
    }
    return $form->aEntitiesByClass;
  }

  static public function formUpdateDefaultsFromObject($form)
  {
    $entitiesByClass = aEntityTools::formGetEntitiesByClass($form);
    foreach (Doctrine::getTable('aEntity')->getOption('subclasses') as $class)
    {
      $form->setDefault(aEntityTools::listForClass($class), array_keys($entitiesByClass[$class]));
    }
  }

  static public function listForClass($class)
  {
    return strtolower($class) . '_list';
  }

  /**
   * Implements the saving of many to many relationships between the 
   * object of the form and the subclasses of Entity. Used in place of
   * saveEntityList.
   *
   * ACHTUNG: you MUST call parent::doSave($con) *FIRST* or this will bomb
   * when saving backlinks to a new object. You MUST also call $this->object->save() 
   * again AFTER this call returns or the forward links won't be saved.
   * Sorry about that - the alternatives are worse. Your doSave() should
   * follow this pattern:
   *
   * parent::doSave($con);
   * aEntityTools::formSaveLists($this, $con);
   * $this->object->save();
   *
   * This requirement does not apply if the form object is not an aEntity subclass,
   * because there are no backlinks in that scenario.
   */

  static public function formSaveLists($form, $con = null)
  {
    foreach (Doctrine::getTable('aEntity')->getOption('subclasses') as $class)
    {
      aEntityTools::formSaveListForClass($form, $class, $con);
    }
  }

  /**
   * Implementation detail of formSaveLists, above
   */
  static public function formSaveListForClass($form, $class, $con = null)
  {
    if (!$form->isValid())
    {
      throw $form->getErrorSchema();
    }

    if (!$form->getWidget(aEntityTools::listForClass($class)))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $form->getConnection();
    }

    $entitiesByClass = aEntityTools::formGetEntitiesByClass($form);
    $existing = array_keys($entitiesByClass[$class]);
    $values = $form->getValue(aEntityTools::listForClass($class));
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $form->getObject()->unlink('Entities', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $form->getObject()->link('Entities', array_values($link));
    }
    
    // We must also establish the relationship in the opposite direction,
    // and clean it up when necessary in the opposite direction too.
    // Otherwise it is not visible if we look at the other object due to the
    // unidirectional nature of Doctrine many-to-many relations.
    //
    // Avoid performance problems with a little SQL.
    //
    // This is only appropriate when both objects are Entities

    if ($form->getObject() instanceOf aEntity)
    {
      $sql = new aMysql();
      foreach ($unlink as $id)
      {
        $sql->query('DELETE FROM a_entity_to_entity WHERE entity_1_id = :other_id AND entity_2_id = :our_id',
          array('our_id' => $form->getObject()->id, 'other_id' => $id));
      }
      foreach ($link as $id)
      {
        $sql->query('INSERT INTO a_entity_to_entity (entity_1_id, entity_2_id) VALUES (:other_id, :our_id)',
          array('our_id' => $form->getObject()->id, 'other_id' => $id));
      }
    }
  }
}