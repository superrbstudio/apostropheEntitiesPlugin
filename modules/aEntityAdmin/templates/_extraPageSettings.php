<?php // Include me from your a/extraPageSettings partial if you want to allow ?>
<?php // entities to be associated with any page ?>
<hr/>
<?php foreach(aEntityTools::getClassInfos() as $class => $info): ?>
  <h4><?php echo $info['plural'] ?></h4>

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
