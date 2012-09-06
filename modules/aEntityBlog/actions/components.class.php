<?php

class aEntityBlogComponents extends sfComponents
{
  public function executeSidebar()
  {
    $classInfos = aEntityTools::getClassInfos();
    $itemsByClass = array();
    foreach ($classInfos as $class => $info)
    {
      $itemsByClass[$class] = array();
    }
    foreach ($this->items as $item)
    {
      if (isset($itemsByClass[$item['type']]))
      {
        $itemsByClass[$item['type']][] = $item;
      }
    }
    foreach ($itemsByClass as $class => &$items)
    {
      $table = Doctrine::getTable($class);
      usort($items, array($table, 'comparator'));
    }
    $this->classInfos = $classInfos;
    $this->itemsByClass = $itemsByClass;
  }
}
