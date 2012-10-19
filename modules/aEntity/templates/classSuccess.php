<?php // An example you'll likely override. ?>
<?php // TODO: make the filter code easier to reuse ?>

<h2><?php echo $classInfo['plural'] ?></h2>

<?php $filterIds = array() ?>

<div id="a-entity-browser">
  <?php foreach ($entities as $entity): ?>
    <?php include_partial('aEntity/listItem', array('entity' => $entity, 'classInfo' => $classInfo)) ?>
  <?php endforeach ?>
  <?php include_partial('aPager/pager', array('pager' => $pager, 'pagerUrl' => '@a_entity_class?class=' . $classInfo['cssPlural'])) ?>
</div>
  
<div class="a-entity-controls">
  <h3>Sorting and Filtering Options</h3>
  <div>
    <?php echo a_filter_dropdown('alpha', array('choices' => array(
    'asc' => 'Sort ASCENDING (A-Z)', 'desc' => 'Sort DESCENDING (Z-A)'))) ?>
  </div>
  <?php foreach ($filters as $filterClass => $filterInfo): ?>
    <?php $filterIds[] = $filterInfo['css'] ?>
    <?php $all = is_null($sf_params->get($filterInfo['css'], null)) ?>

    <div>
      <?php echo a_filter_dropdown($filterInfo['css'], array('choices' => $filterInfo['itemInfos'], 'valueColumn' => 'slug', 'labelColumn' => 'name', 'chooseOne' => 'Filter by ' . strtoupper($filterInfo['singular']), 'revertToAll' => 'All ' . strtoupper($filterInfo['plural']))) ?>
    </div>
  <?php endforeach ?>
</div>

