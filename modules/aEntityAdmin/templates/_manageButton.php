<?php // Adapt me for your needs and include me at project level. Organization should be ?>
<?php // replaced with a reasonable default entity subclass for your situation ?>

<?php if ($sf_user->hasCredential('admin')): ?>
  <li><?php echo link_to('<span class="icon"></span>Manage', '@a_entity_admin?class=Organization', array('class' => 'a-btn icon a-users no-bg alt')) ?></li>
<?php endif ?>
