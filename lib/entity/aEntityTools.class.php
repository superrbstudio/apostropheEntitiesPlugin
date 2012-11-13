<?php

/**
 * Utilities to extend form classes to provide distinct widgets for association with
 * each subclass of entity. Also utilities to get enough information to intelligently
 * label widgets, give them class names, etc. for each subclass.
 */
class aEntityTools
{
  /**
   * Everything you need to know to label each subclass, apply a CSS 
   * class to it, display its list widget in a form, link to its
   * best permalink page (or something that redirects there)... that 
   * kind of thing should be returned here
   */
  static public function getClassInfos()
  {
    $infos = array();
    $options = sfConfig::get('app_aEntities_entities', array());
    foreach (Doctrine::getTable('aEntity')->getOption('subclasses') as $class)
    {
      $infos[$class] = array(
        // name is the class name
        'name' => $class,
        // singular is more human-friendly, defaults to class name
        'singular' => isset($options[$class]['labels']['singular']) ? $options[$class]['labels']['singular'] : $class, 
        'plural' => isset($options[$class]['labels']['plural']) ? $options[$class]['labels']['plural'] : ($class . 's'), 
        // css is suitable for a slug or css class name
        'css' => isset($options[$class]['css']['singular']) ? $options[$class]['css']['singular'] : strtolower($class), 
        'cssPlural' => isset($options[$class]['css']['plural']) ? $options[$class]['css']['plural'] : strtolower($class) . 's', 
        // For list widgets in forms, not user-visible
        'list' => strtolower($class) . '_list',
        'route' => isset($options[$class]['route']) ? $options[$class]['route'] : false,
        'filters' => isset($options[$class]['filters']) ? $options[$class]['filters'] : array()
      );
    }
    return $infos;
  }

  static public function getClasses()
  {
    return array_keys(aEntityTools::getClassInfos());
  }

  static public function getClassInfo($name)
  {
    $classInfos = aEntityTools::getClassInfos();
    if (isset($classInfos[$name]))
    {
      return $classInfos[$name];
    }
    return null;
  }

  /**
   * Return information about entity classes that should
   * appear in a list of entity classes browsable via the
   * directory. (Your project may have classes that are not.)
   */
  static public function getMenu()
  {
    $info = sfConfig::get('app_aEntities_directory');
    $result = array();
    if (isset($info['menu']))
    {
      foreach ($info['menu'] as $class) 
      {
        $result[$class] = aEntityTools::getClassInfo($class);
      }
    }
    return $result;
  }

  /**
   * Helpful when using the css plural (which is a nice lowercase
   * hyphenated name for the class) in a route parameter
   */
  static public function findClassInfoByCssPlural($cssPlural)
  {
    $classInfos = aEntityTools::getClassInfos();
    foreach ($classInfos as $class => $classInfo)
    {
      if ($classInfo['cssPlural'] === $cssPlural)
      {
        return $classInfo;
      }
    }
    return null;
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
      $table = Doctrine::getTable($class);
      $query = $table->createQuery('e');
      if ($excludeSelf && (!$form->isNew()))
      {
        $query->andWhere('e.id <> ?', $form->getObject()->getId());
      }
      $table->addOrderBy($query);

      $list = aEntityTools::listForClass($class);
      $form->setWidget($list, new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => $class, 'query' => $query)));
      $form->setValidator($list, new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => $class, 'required' => false, 'query' => $query)));
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
    /**
     * Cache for the life of the current request
     */
    if (!isset($form->aEntitiesByClass))
    {
      $form->aEntitiesByClass = aEntityTools::groupEntitiesByClass($form->getObject()->Entities);
    }
    return $form->aEntitiesByClass;
  }

  /**
   * Group all entities in the passed array or collection into
   * separate arrays by subclass. Sorts them according to the
   * comparator method of each table subclass
   */
  static public function groupEntitiesByClass($entities)
  {
    $entitiesByClass = array();
    $classes = Doctrine::getTable('aEntity')->getOption('subclasses');
    foreach ($classes as $class)
    {
      $entitiesByClass[$class] = array();
    }
    foreach ($entities as $entity)
    {
      $entitiesByClass[$entity['type']][$entity['id']] = $entity;
    }
    foreach ($classes as $class)
    {
      uasort($entitiesByClass[$class], array(Doctrine::getTable($class), 'comparator'));
    }
    return $entitiesByClass;
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

    if (!isset($form[aEntityTools::listForClass($class)]))
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

  /**
   * Convenient helper for creating an executeImage action
   * in any module that edits entities. This code accepts an
   * uploaded image file and scales it in sizes as specified
   * by app_aEntities_imageSizes, dropping it in /uploads/entities/5.jpg
   * (where the id of the entity is 5). This is helpful for creating
   * an official headshot for the entity if you do not wish to
   * associate entities with media items in Apostrophe's library.
   *
   * The entity must already be in $actions->entity.
   * 
   * Returns true on a successful upload, false if nothing 
   * was uploaded or an error took place.
   *
   * Assumes your entities have a has_image boolean property
   * that can be set.
   *
   * Typical usage: aEntityTools::executeImage($this, $request)
   */
  static public function executeImage($actions, sfWebRequest $request)
  {
    $actions->imageForm = new aEntityImageForm();
    if (($request->getMethod() === 'POST') && ($request->getParameter('image')))
    {
      $actions->imageForm->bind($request->getParameter('image'), $request->getFiles('image'));
      if ($actions->imageForm->isValid())
      {
        $file = $actions->imageForm->getValue('file');
        $sizes = sfConfig::get('app_aEntities_imageSizes');
        foreach ($sizes as $name => $settings)
        {
          $final = sfConfig::get('sf_upload_dir') . '/entities/' . $actions->entity->getId() . '.' . $name . '.jpg';
          aImageConverter::cropOriginal($file->getTempName(), $final, $settings['width'], $settings['height']);
        }
        $actions->entity->setHasImage(true);
        $actions->entity->save();
        return true;
      }
    }
    return false;
  }
}
