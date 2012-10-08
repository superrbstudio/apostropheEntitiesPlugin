<?php // List entities related to this entity, grouped by subclass. ?>

<?php // See also aEntityBlog/listRelated which is specifically ?>
<?php // crafted for use with the blog and should register itself ?>
<?php // automatically. ?>

<?php // Generates links via the directoryRoute option for each class ?>
<?php // (see aEntityTools) which expects a slug parameter. If there is no ?>
<?php // directoryRoute option configured, no link is generated. ?>

<?php // If the compact option is true, a comma-separated list is used. ?>
<?php // otherwise a ul list is used. ?>

<?php $entity = $sf_data->getRaw('entity') ?>
<?php $compact = isset($compact) ? $sf_data->getRaw('compact') : false ?>

<?php $entitiesByClass = aEntityTools::groupEntitiesByClass($entity['Entities']) ?>

<?php $infos = aEntityTools::getClassInfos() ?>
<?php foreach($entitiesByClass as $class => $entities): ?>
  <?php $info = $infos[$class] ?>
  <?php if (count($entities)): ?>
    <ul class="a-related-entities">
      <li class="subclass <?php echo $info['cssPlural'] ?>">
        <span class="subclass-name"><?php echo $info['plural'] ?></span>

        <?php if ($compact): ?>
          <span class="subclass-items">
        <?php else: ?>
          <ul class="entities">
        <?php endif ?>

        <?php $i = 1; foreach ($entities as $entity): ?>

          <?php if (!$compact): ?>
            <li class="entity">
          <?php endif ?>

          <?php if ($info['directoryRoute']): ?>
            <?php echo link_to(aHtml::entities($entity['name']), url_for(aUrl::addParams('@' . $info['directoryRoute'], array('slug' => $entity['slug'])))) ?>
          <?php else: ?>
            <?php echo aHtml::entities($entity['name']) ?>
          <?php endif ?>

          <?php if ($compact): ?>
            <?php echo (($i < count($entities)) ? ', ' : '')?>
          <?php else: ?>
            </li>
          <?php endif ?>

        <?php endforeach ?>
        <?php if ($compact): ?>
          </span>
        <?php else: ?>
          </ul>
        <?php endif ?> 
      </li>
    </ul>
  <?php endif ?>
<?php endforeach ?>
