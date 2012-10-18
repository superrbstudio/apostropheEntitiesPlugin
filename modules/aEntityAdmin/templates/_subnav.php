<?php use_helper('a') ?>

<?php $infos = aEntityTools::getClassInfos() ?>

<ul class="a-ui a-controls a-admin-action-controls">
  <?php foreach ($infos as $class => $info): ?>
	  <li class="a-admin-action-controls-item">
      <?php echo link_to(__($info['plural'] . ' Dashboard'), "@a_entity_admin?class=$class", array('class' => 'a-btn')) ?>
      <?php echo link_to('<span class="icon"></span>'.__('Add ' . $info['singular']), "@a_entity_admin_new?class=$class", array('class' => 'a-btn icon a-add no-label')) ?>
    </li>
  <?php endforeach ?>
  <?php include_partial('aEntityAdmin/extraDashboards') ?>
</ul>
