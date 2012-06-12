<?php

class aEntityRoute extends sfDoctrineRoute
{
  /**
   * If there is no explicit 'class' parameter, take it from the
   * parameters of the current request. This allows the admin generator
   * to generate links to individual items and actions without crashing due
   * to a missing parameter. This is a key part of the solution to the problem
   * of using a single admin generator module for all subclasses of aEntity.
   */
  public function generate($params, $context = array(), $absolute = false)
  {
  	if (!isset($params['class']))
  	{
  		$request = sfContext::getInstance()->getRequest();
  		if ($request->getParameter('class'))
  		{
  			$params['class'] = $request->getParameter('class');
  		}
  	}
  	if (!$params['class'])
  	{
  		throw new sfException("No class parameter, cannot generate entity route");
  	}
  	$class = ucfirst($params['class']);
  	$validClasses = Doctrine::getTable('aEntity')->getOption('subclasses');
  	if (!in_array($class, $validClasses))
  	{
  		throw new sfException("Must be one of " . implode(', ', $validClasses));
  	}

  	return parent::generate($params, $context, $absolute);
  }
}