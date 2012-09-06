<?php $selected = array('icon','a-selected','alt','icon-right'); // Class names for selected filters ?>
<?php $classInfos = $sf_data->getRaw('classInfos') ?>
<?php $itemsByClass = $sf_data->getRaw('itemsByClass') ?>
<?php foreach ($itemsByClass as $class => $items): ?>
  <?php if (count($items) > 1): ?>
    <hr class="a-hr" />
    <div class="a-subnav-section entities <?php echo $class ?>">
      <h4 class="filter-label<?php echo ($sf_params->get('entity')) ? ' open' : '' ?>"><?php echo a_($classInfos[$class]['plural']) ?></h4>
      <div class="a-filter-options blog clearfix<?php echo ($sf_params->get('entity')) ? ' open' : '' ?>">
        <?php foreach ($items as $entity): ?>
          <?php $selected_entity = ($entity['slug'] === $sf_params->get('entity')) ? $selected : array() ?>
          <div class="a-filter-option">
            <?php echo a_button($entity['name'], url_for($entity['filterUrl']), array_merge(array('a-link'),$selected_entity)) ?>
          </div>
        <?php endforeach ?>
      </div>
    </div>
  <?php endif ?>
<?php endforeach ?>
