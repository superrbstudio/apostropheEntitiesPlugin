<?php 

class BaseaEntityComponents extends sfComponents
{
  public function executeRelatedPosts()
  {
    $this->posts = $this->entity->getSortedPosts(array('limit' => 10));
  }

  public function executeRelatedEvents()
  {
    $this->events = $this->entity->getSortedEvents(array('limit' => 10));
  }
}
