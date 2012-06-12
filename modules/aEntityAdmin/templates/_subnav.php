<?php use_helper('a') ?>

<?php $infos = aEntityTools::getClassInfos() ?>

<ul class="a-ui a-controls a-admin-action-controls">
  <?php foreach ($infos as $class => $info): ?>
	  <li class="dashboard"><h4><?php echo link_to(__($info['plural'] . ' Dashboard'), "@a_entity?class=$class") ?></h4></li>
	  <li><?php echo link_to('<span class="icon"></span>'.__('Add ' . $info['singular']), "@a_entity_new?class=$class", array('class' => 'a-btn icon a-add')) ?></li>
  <?php endforeach ?>
</ul>
