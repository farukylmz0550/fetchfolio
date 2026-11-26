<?php get_header(); ?>
<main class="terminal-body" id="main">
  <div><span class="prompt">$</span> <span class="cmd">cat blog/<?php echo esc_html(get_post_field('post_name', get_queried_object_id())); ?>.md</span><span class="cursor"></span></div>
  <?php while (have_posts()) : the_post(); ?>
    <article id="content" class="ff-content">
      <h1><?php the_title(); ?></h1>
      <p class="post-meta"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time></p>
      <figure class="post-featured-image">
        <?php if (has_post_thumbnail()) : ?>
          <?php the_post_thumbnail('large', array('alt' => the_title_attribute(array('echo' => false)), 'loading' => 'eager', 'fetchpriority' => 'high')); ?>
        <?php else : ?>
          <div class="post-image-placeholder"><?php esc_html_e('Set a featured image in the post editor', 'fetchfolio'); ?></div>
        <?php endif; ?>
      </figure>
      <div class="post-content"><?php the_content(); ?></div>
    </article>
  <?php endwhile; ?>
  <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: home_url('/blog/')); ?>" class="back-link">&lt; cd blog</a>
</main>
<?php get_footer(); ?>
