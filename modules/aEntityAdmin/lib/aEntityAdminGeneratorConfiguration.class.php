<?php

/**
 * aEntity module configuration.
 */
class aEntityAdminGeneratorConfiguration extends BaseaEntityAdminGeneratorConfiguration
{
	public $singular;
	public $plural;
	public $class;
	protected function init()
	{
		if (is_null($this->class))
		{
			$request = sfContext::getInstance()->getRequest();
  		if (!$request->getParameter('class'))
  		{
  			throw new sfException("No class parameter in current route, cannot implement admin of aEntity subclasses");
  		}
  		$this->class = ucfirst($request->getParameter('class'));
	  	$validClasses = Doctrine::getTable('aEntity')->getOption('subclasses');
	  	if (!in_array($this->class, $validClasses))
	  	{
	  		throw new sfException("Must be one of " . implode(', ', $validClasses));
	  	}
	  	$this->singular = $this->class;
	  	$this->plural = $this->singular . 's';
		}
	}
	public function getListTitle()
	{
		$this->init();
	    return "Manage " . $this->plural;
	}
	public function getEditTitle()
	{
		$this->init();
	    return "Edit " . $this->singular;
	}
	public function getNewTitle()
	{
		$this->init();
	    return "New " . $this->singular;
	}
	public function getListDisplay()
  {
  	$result = parent::getListDisplay();
  	$result = array_flip($result);
  	unset($result['id']);
  	unset($result['type']);
  	return array_keys($result);
  }

  public function getFieldsDefault()
  {
  	$result = parent::getFieldsDefault();
  	unset($result['id']);
  	unset($result['type']);
  	return $result;
  }

  public function getFieldsList()
  {
  	$result = parent::getFieldsList();
  	unset($result['id']);
  	unset($result['type']);
  	return $result;
  }

  public function getFieldsFilter()
  {
  	$result = parent::getFieldsFilter();
  	unset($result['id']);
  	unset($result['type']);
  	return $result;
  }

  public function getFieldsForm()
  {
  	$result = parent::getFieldsForm();
  	unset($result['id']);
  	unset($result['type']);
  	return $result;
  }

  public function getFieldsEdit()
  {
  	$result = parent::getFieldsEdit();
  	unset($result['id']);
  	unset($result['type']);
  	return $result;
  }

  public function getFieldsNew()
  {
  	$result = parent::getFieldsNew();
  	unset($result['id']);
  	unset($result['type']);
  	return $result;
  }

  public function getFormClass()
  {
  	$this->init();
    return $this->class . 'Form';
  }

  public function getFilterFormClass()
  {
  	$this->init();
  	return $this->class . 'FormFilter';
  }

  public function getPager($model)
  {
  	// Override default model class
  	$this->init();
  	$model = $this->class;
  	$class = $this->getPagerClass();
    return new $class($model, $this->getPagerMaxPerPage());
  }

  public function getDefaultSort()
  {
    return array('name', 'asc');
  }
}
