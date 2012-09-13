<?php // List entities related to this blog post or event, ?>
<?php // much as we list categories and tags related to it. ?>
<?php // Include this partial from the _beforeTags or _afterTags partial ?>
<?php // in your module level overrides of the aBlog module, and both ?>
<?php // blog posts and events will display their related entities, if any ?>

<?php $aBlogItem = $sf_data->getRaw('aBlogItem') ?>

<?php $type = $aBlogItem->getType() ?>
<?php // Links to see other posts or events filtered by entities ?>
<?php // that are related to this post or event ?>
<?php $entitiesByClass = aEntityTools::groupEntitiesByClass($aBlogItem->getEntities()) ?>

<?php $infos = aEntityTools::getClassInfos() ?>
<?php foreach($entitiesByClass as $class => $entities): ?>
  <?php $info = $infos[$class] ?>
  <?php if (count($entities)): ?>
    <div class="a-blog-item-<?php echo $info['cssPlural'] ?> <?php echo $info['cssPlural'] ?>">
      <span class="a-blog-item-<?php echo $info['css'] ?>-label"><?php echo $info['plural'] ?>:</span>
      <?php $i = 1; foreach ($entities as $entity): ?>
        <?php echo link_to($entity['name'], aUrl::addParams((($type == 'post') ? 'aBlog' : 'aEvent' ).'/index', array('entity' => $entity['slug']))) ?><?php echo (($i < count($entities)) ? ', ':'')?>
      <?php $i++; endforeach ?>
    </div>
  <?php endif ?>
<?php endforeach ?>
