<?php // List entities related to this entity, grouped by subclass. ?>

<?php // See also aEntityBlog/listRelated which is specifically ?>
<?php // crafted for use with the blog and should register itself ?>
<?php // automatically. ?>

<?php // Generates links via the route option for each class ?>
<?php // (see aEntityTools) which expects a slug parameter. If there is no ?>
<?php // route option configured, no link is generated. ?>

<?php // If the compact option is true, a comma-separated list is used. ?>
<?php // otherwise li's are generated (you supply the enclosing ul). ?>

<?php $entity = $sf_data->getRaw('entity') ?>
<?php $compact = isset($compact) ? $sf_data->getRaw('compact') : false ?>

<?php $entitiesByClass = aEntityTools::groupEntitiesByClass($entity['Entities']) ?>

<?php $infos = aEntityTools::getClassInfos() ?>
<?php foreach($entitiesByClass as $class => $entities): ?>
  <?php $info = $infos[$class] ?>
  <?php if (count($entities)): ?>
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

        <?php if ($info['route']): ?>
          <?php echo link_to(aHtml::entities($entity['name']), url_for(aUrl::addParams('@' . $info['route'], array('slug' => $entity['slug'])))) ?>
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
  <?php endif ?>
<?php endforeach ?>
