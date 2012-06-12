<?php // Include me from your a/globalProjectButtons partial or just adapt me accordingly ?>
<?php if ($sf_user->hasCredential('admin')): ?>
  <li><?php echo link_to('<span class="icon"></span>Manage', '@a_entity?class=Organization', array('class' => 'a-btn icon a-users no-bg alt')) ?></li>
<?php endif ?>