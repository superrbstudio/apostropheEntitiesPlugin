<?php $entity = $sf_data->getRaw('entity') ?>
<?php $posts = $sf_data->getRaw('posts') ?>
<?php if (count($posts)): ?>
  <h3>Posts</h3>
  <?php foreach ($posts as $post): ?>
    <ul class="related-articles">
      <li class="related-article"><?php echo link_to($post['title'], 'a_blog_post', $post) ?></li>
      <?php include_partial('aBlog/meta', array('a_blog_post' => $post)) ?>

      <div class="a-blog-item-excerpt">
        <?php echo aHtml::simplify($post->getRichTextForArea('blog-body', 100), array('allowedTags' => '<em><strong>'))  ?>
      </div>
    </ul>
  <?php endforeach ?>
  <?php echo link_to('More Related Posts', 'aBlog/index?' . http_build_query(array('entity' => $entity['slug']))) ?>
<?php endif ?>
