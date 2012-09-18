<?php

/*
 * This file is part of Apostrophe
 * (c) 2012 P'unk Avenue LLC, www.punkave.com
 */

/**
 * @package    apostropheEntitiesPlugin
 * @subpackage Tasks
 * @author     Tom Boutell <tom@punkave.com>
 */

class apostropheEntitiesGenerateTestEntitiesTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('dictionary', null, sfCommandOption::PARAMETER_REQUIRED, 'The dictionary file', '/usr/share/dict/words'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The database connection', 'doctrine'),
      new sfCommandOption('amount', null, sfCommandOption::PARAMETER_REQUIRED, 'The number of entities to be generated', '50'),
    ));


    $this->namespace        = 'apostrophe';
    $this->name             = 'generate-test-entities';
    $this->briefDescription = 'Adds 50 random test entities';
    $this->detailedDescription = <<<EOF
This task adds 50 test entities of randomly chosen subclasses 
with random content. Content for these entities comes from 
/usr/share/dict/words unless --dictionary=filename is
specified. Entities are given random relationships to
other entities and to any blog posts and events in the
system.
EOF;
  }

  // $options would conflict with the base class metadata ):
  protected $optionValues;

  protected function execute($args = array(), $options = array())
  {    
    $this->optionValues = $options;
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();
    // So we can play with app.yml settings from the application
    $context = sfContext::createInstance($this->configuration);

    $admin = Doctrine::getTable('sfGuardUser')->findOneByUsername('admin');
    
    $classInfos = aEntityTools::getClassInfos();
    $ids = array();
    for ($i = 0; ($i < $options['amount']); $i++)
    {
      echo("Creating entity " . ($i + 1) . " of " . $options['amount'] . "...\n");

      $classInfo = $classInfos[array_rand($classInfos)];
      $class = $classInfo['name'];
      $entity = new $class();
      $entity->randomize($this);
      $entity->save();
      $ids[] = $entity->getId();
    }
    // Link the entities efficiently with raw SQL
    $sql = new aMysql();
    $linked = array();
    echo("Linking entities randomly...\n");
    foreach ($ids as $id) 
    {
      $links = mt_rand(0, 5);
      for ($i = 0; ($i <= $links); $i++) 
      {
        $otherId = $ids[array_rand($ids)];
        if ($otherId !== $id) 
        {
          if ((!isset($linked[$id][$otherId])) && (!isset($linked[$otherId][$id]))) 
          {
            // Entity relationships are saved bidirectionally
            $sql->insert('a_entity_to_entity', array('entity_1_id' => $id, 'entity_2_id' => $otherId));
            $sql->insert('a_entity_to_entity', array('entity_2_id' => $id, 'entity_1_id' => $otherId));
            if (!isset($linked[$id]))
            {
              $linked[$id] = array();
            }
            $linked[$id][$otherId] = true;
          }
        }
      }
    }
    echo("Linking entities to blog posts randomly...\n");
    // Link the entities to random blog posts too if there are any
    $blogIds = $sql->queryScalar('SELECT id FROM a_blog_item WHERE type="post" AND published_at < NOW() AND status = "published"');
    if (count($blogIds)) 
    {
      foreach ($ids as $id) 
      {
        $picked = array();
        $links = mt_rand(0, 5);
        for ($i = 0; ($i <= $links); $i++) 
        {
          $blogId = $blogIds[array_rand($blogIds)];
          if (!isset($picked[$blogId]))
          {
            $sql->insert('a_entity_to_blog_item', array('entity_id' => $id, 'blog_item_id' => $blogId));
            $picked[$blogId] = true;
          }
        }
      }
    }
  }
  
  protected $words = null;
  public function getWords($count)
  {
    if (is_null($this->words))
    {
      $this->words = file($this->optionValues['dictionary'], FILE_IGNORE_NEW_LINES);
    }
    $result = array();
    for ($i = 0; ($i < $count); $i++)
    {
      $result[] = $this->words[array_rand($this->words)];
    }
    return $result;
  }

  public function getWord()
  {
    $result = $this->getWords(1);
    return reset($result);
  }
}
