<?php $events = $entity->getSortedEvents(array('limit' => 10)) ?>
<?php if (count($events)): ?>
  <h3>Events</h3>
  <?php foreach ($events as $event): ?>
    <ul class="related-articles">
      <li class="related-article"><?php echo link_to($event['title'], 'a_event_post', $event) ?></li>
      <?php include_partial('aEvent/meta', array('aEvent' => $event)) ?>

      <div class="a-blog-item-excerpt">
        <?php echo aHtml::simplify($event->getRichTextForArea('blog-body', 100), array('allowedTags' => '<em><strong>'))  ?>
      </div>
    </ul>
  <?php endforeach ?>
  <?php echo link_to('More Related Events', 'aEvent/index?' . http_build_query(array('entity' => $entity['slug']))) ?>
<?php endif ?>

