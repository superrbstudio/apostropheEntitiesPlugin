<?php // An example you'll likely override. ?>
<?php $entity = $sf_data->getRaw('entity') ?>

<h2 class="directory-name"><?php echo aHtml::entities($entity['name']) ?></h2>
<?php include_partial('aEntity/listRelated', array('entity' => $entity, 'compact' => false)) ?>

<?php include_component('aEntity', 'relatedPosts', array('entity' => $entity)) ?>
<?php include_component('aEntity', 'relatedEvents', array('entity' => $entity)) ?>

