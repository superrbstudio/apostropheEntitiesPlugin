<?php
  $entity = $sf_data->getRaw('entity') ?>
  $classInfo = $sf_data->getRaw('classInfo') ?>
?>
<div class="a-entity a-entity-excerpt <?php echo strtolower($classInfo['css']) ?>">
  <div class="details">
    <h3 class="title"><?php echo link_to(aHtml::entities($entity['name']), '@a_entity_show?class=' . $classInfo['cssPlural'] . '&slug=' . $entity['slug']) ?></h3>
    <ul class="meta">
      <?php include_partial('aEntity/listRelated', array('entity' => $entity, 'compact' => true)) ?>
    </ul>
    <div class="body">
      <?php echo link_to('READ MORE', '@a_entity_show?class=' . $classInfo['cssPlural'] . '&slug=' . $entity['slug']) ?>
    </div>
  </div>
</div>
