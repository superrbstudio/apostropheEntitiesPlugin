<?php // List entities related to this entity, grouped by subclass. ?>

<?php // See also aEntityBlog/listRelated which is specifically ?>
<?php // crafted for use with the blog and should register itself ?>
<?php // automatically. ?>

<?php // Generates links via a directory route that you supply, ?>
<?php // which expects slug and class parameters. The class parameter ?>
<?php // will be set to the cssPlural of the class (people, not Person) ?>
<?php // as it makes a much nicer URL. ?>

<?php // If the compact option is true, a comma-separated list is used. ?>
<?php // otherwise a ul list is used. ?>

<?php $entity = $sf_data->getRaw('entity') ?>
<?php $directoryRoute = $sf_data->getRaw('directoryRoute') ?>
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

          <?php echo link_to(aHtml::entities($entity['name']), url_for(aUrl::addParams($directoryRoute, array('class' => $info['cssPlural'], 'slug' => $entity['slug'])))) ?>

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
