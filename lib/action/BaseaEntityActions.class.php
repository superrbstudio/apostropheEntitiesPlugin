<?php

class BaseaEntityActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    // redirect to a listing of entities for the first entity class
    // in the menu. If no menu is defined use the first entity class that
    // is allowed in the dirctory. If no list of allowed classes is defined
    // either use the first entity class in the schema. TODO: move this
    // logic into aEntityTools where it can be reused.
    
    $classInfos = aEntityTools::getClassInfos();
    $directoryInfo = sfConfig::get('app_aEntities_directory');
    $default = isset($directoryInfo['menu']) ? reset($directoryInfo['menu']) : isset($directoryInfo['allowed']) ? reset($directoryInfo['allowed']) : reset(aEntityTools::getClasses());
    if (!$default) 
    {
      return $this->forward404();
    }
    $default = $classInfos[$default]['cssPlural'];
    return $this->redirect($this->generateUrl('a_entity_class', array('class' => $default)));
  }

  public function executeClass(sfWebRequest $request)
  {
    $this->classInfo = aEntityTools::findClassInfoByCssPlural($request-> getParameter('class'));
    if (!$this->classInfo)
    {
      return $this->forward404();
    }

    $directoryInfo = sfConfig::get('app_aEntities_directory');
    $allowed = isset($directoryInfo['allowed']) ? $directoryInfo['allowed'] : array();
    if (!in_array($this->classInfo['name'], $allowed))
    {
      $this->forward404();
    }

    $this->class = $this->classInfo['name'];
    $this->pager = new sfDoctrinePager($this->class, 10);
    $table = Doctrine::getTable($this->class);
    $query = $table->createQuery('e');
    $query->leftJoin('e.Entities r');
    $query->addSelect('e.*, r.*');
    $alpha = $request->getParameter('alpha', 'asc');
    if ($alpha)
    {
      $table->addOrderBy($query, $alpha);
    }
    $filterClasses = $this->classInfo['filters'];
    $this->filters = array();
    $n = 1;
    foreach ($filterClasses as $filterClass)
    {
      $filterClassInfo = aEntityTools::getClassInfo($filterClass);
      $filterQuery = Doctrine::getTable($filterClass)->findAllSortedQuery();
      $filterQuery->innerJoin("e.Entities o$n WITH o$n.type = ?", $this->class);
      $filterQuery->select('e.*');
      $filterClassInfo['itemInfos'] = $filterQuery->fetchArray();
      $this->filters[$filterClass] = $filterClassInfo;
      $filterSlug = $request->getParameter($filterClassInfo['css'], null);
      if (!is_null($filterSlug))
      {
        $query->innerJoin("e.Entities o$n WITH o$n.slug = ?", $filterSlug);
      }
      $n++;
    }
    $this->pager->setQuery($query);
    $this->pager->setPage($this->getRequestParameter('page', 1));
    $this->pager->init();
    // Tom, give us a way to get the entity image if you want to array-hydrate this jawn
    $this->entities = $this->pager->getResults(/*Doctrine::HYDRATE_ARRAY*/);
  }

  public function executeShow(sfWebRequest $request)
  {
    $this->entity = Doctrine::getTable('aEntity')->findOneBySlug($request->getParameter('slug'));
    $this->classInfo = aEntityTools::getClassInfo(get_class($this->entity));
  }
}