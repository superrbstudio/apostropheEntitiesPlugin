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
    <select id="alpha" name="alpha">
      <option <?php echo ($sf_params->get('alpha') === 'asc') ? 'selected' : '' ?> value="asc">Sort ASCENDING (A-Z)</option>
      <option <?php echo ($sf_params->get('alpha') === 'desc') ? 'selected' : '' ?> value="desc">Sort DESCENDING (Z-A)</option>
    </select>
  </div>
  <?php foreach ($filters as $filterClass => $filterInfo): ?>
    <?php $filterIds[] = $filterInfo['css'] ?>
    <?php $all = is_null($sf_params->get($filterInfo['css'], null)) ?>

    <div>
      <select id="<?php echo $filterInfo['css'] ?>" name="<?php echo $filterInfo['css'] ?>">
        <option <?php echo $all ? 'selected' : '' ?> value=""><?php echo $all ? 'Filter by ' . strtoupper($filterInfo['singular']) : ('All ' . strtoupper($filterInfo['plural'])) ?></option>
        <?php foreach ($filterInfo['itemInfos'] as $itemInfo): ?>
          <option <?php echo ($sf_params->get($filterInfo['css']) === $itemInfo['slug']) ? 'selected' : '' ?> value="<?php echo $itemInfo['slug'] ?>"><?php echo $itemInfo['name'] ?></option>
        <?php endforeach ?>
      </select>
    </div>
  <?php endforeach ?>
</div>

<script type="text/javascript">
$(function() {
  var ids = <?php echo json_encode($filterIds) ?>;

  $('#alpha').change(function() {
    document.location.href = apostrophe.addParameterToUrl(document.location.href, 'alpha', $(this).val());
  });

  $.each(ids, function(index, id) {
    $('#' + id).change(function() {
      // Drop any page number when switching filters
      document.location.href = apostrophe.addParameterToUrl(
        apostrophe.addParameterToUrl(
          document.location.href, id, $(this).val()),
        'page', '');
    });
  });

});
</script>
