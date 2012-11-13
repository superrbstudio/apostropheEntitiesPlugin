<?php

class aEntityImageForm extends sfForm
{
  public function configure()
  {
    $this->setWidget('file', new sfWidgetFormInputFile());
    $this->setValidator('file', new sfValidatorFile(array('mime_types' => array('image/jpeg', 'image/png', 'image/gif'), 'required' => true)));
    $this->widgetSchema->setNameFormat('image[%s]');
  }
}
