<?php // Include me from your a/extraPageSettings partial if you want to allow ?>
<?php // entities to be associated with any page ?>
<?php foreach(aEntityTools::getClassInfos() as $class => $info): ?>
  <hr/>
  <h3><?php echo $info['plural'] ?></h3>

  <div class="a-options-section <?php echo $info['css'] ?> clearfix">
    <div class="a-form-row">
      <div class="a-form-field">
        <?php echo $form[$info['list']]->render() ?>
      </div>
      <?php echo $form[$info['list']]->renderError() ?>
    </div>
  </div>
  <?php a_js_call('aMultipleSelect(?, ?)', '.' . $info['css'], array('choose-one' => 'Choose One')) ?>
<?php endforeach ?>
