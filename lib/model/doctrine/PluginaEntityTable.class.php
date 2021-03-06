<?php

/**
 * PluginaEntityTable
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PluginaEntityTable extends Doctrine_Table
{
  /**
   * Returns an instance of this class.
   *
   * @return object PluginaEntityTable
   */
  public static function getInstance()
  {
      return Doctrine_Core::getTable('PluginaEntity');
  }

  /**
   * Convenience functions wrapping findAllSortedBody.
   */

  public function findAllSorted()
  {
    return $this->findAllSortedBody(array('hydrate' => Doctrine_Core::HYDRATE_RECORD));
  }

  public function findAllSortedArray()
  {
    return $this->findAllSortedBody(array('hydrate' => Doctrine_Core::HYDRATE_ARRAY));
  }

  /**
   * These are helpful as table methods for sfDoctrinePager
   */
  public function findAllSortedQuery()
  {
    return $this->findAllSortedBody(array('queryOnly' => true));
  }

  public function findAllSortedAndRelatedQuery()
  {
    return $this->findAllSortedBody(array('queryOnly' => true, 'related' => true));
  }

  public function findAllSortedBody($options = array())
  {
    $hydrate = isset($options['hydrate']) ? $options['hydrate'] : Doctrine_Core::HYDRATE_RECORD;
    $order = isset($options['order']) ? $options['order'] : null;
    $related = isset($options['related']) ? $options['related'] : null;
    $confirmed = isset($options['confirmed']) ? $options['confirmed'] : null;
    $owner = isset($options['owner']) ? $options['owner'] : null;
    $letter = isset($options['letter']) ? $options['letter'] : null;

    $query = $this->createQuery('e');
    $this->addOrderBy($query, $order);
    if ($owner) 
    {
      $query->andWhere('e.owner_id = ?', is_object($owner) ? $owner->getId() : $owner);
    }
    if (!is_null($confirmed))
    {
      if ($confirmed)
      {
        $query->andWhere('e.confirmed IS TRUE AND e.owner_confirmed IS TRUE');
      }
      else
      {
        $query->andWhere('e.confirmed IS NULL OR e.confirmed IS FALSE OR e.owner_confirmed IS NULL OR e.owner_confirmed IS FALSE');
      }
    }
    if ($letter)
    {
      $this->addLetterToQuery($query, $letter);
    }
    if ($related)
    {
      $query->leftJoin('e.Entities r');
    }
    $query = $this->enhanceFindAllSortedBody($query, $options);
    $queryOnly = isset($options['queryOnly']) ? $options['queryOnly'] : null;
    if ($queryOnly)
    {
      return $query;
    }
    return $query->execute(array(), $hydrate);
  }

  protected function addLetterToQuery($query, $letter)
  {
    $query->andWhere('e.name LIKE ?', $letter . '%');
  }

  /**
   * An override point to add additional constraints
   * or joins to the query 
   */
  public function enhanceFindAllSortedBody($query, $options)
  {
    return $query;
  }

  /**
   * See also comparator, which must perform the same sort in PHP land with two
   * existing elements
   */
  public function addOrderBy($query, $order = "asc")
  {
    $query->orderBy("e.name $order");
  }

  /**
   * This sorting method is used when a query has already returned a mixed group of
   * entities that have different sorting rules (example: people and organizations)
   * and we have pulled out a subset of them that share a single subclass and wish to
   * sort those according to the appropriate rule for that subclass. Note that this
   * must do the same thing as addOrderBy() if you override it.
   *
   * Note: array hydration must be supported here, so use []
   */

  public function comparator($e1, $e2)
  {
    return strcasecmp($e1['name'], $e2['name']);
  }

  protected $related = array();

  /**
   * Return all related entities, grouped by subclass, sorted
   * in their preferred order via their comparators
   */
  public function groupEntitiesBySubclass($entity)
  {
    if (!isset($this->related[$entity['id']]))
    {
      $this->related[$entity['id']] = aEntityTools::groupEntitiesBySubclass($entity['Entities']);
    }
    return $this->related[$entity['id']];
  }

  /**
   * Get all related entities of a particular subclass. Now
   * utilizes groupEntitiesBySubclass and uses the preferred
   * comparator for the subclass
   */
  public function getEntitiesBySubclass($entity, $subclass)
  {
    $entitiesBySubclass = $this->groupEntitiesBySubclass($entity);
    return $entitiesBySubclass[$subclass];
  }
}
