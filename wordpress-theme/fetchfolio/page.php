<?php get_header(); ?>
<main class="terminal-body" id="main">
  <?php $slug = get_post_field('post_name', get_queried_object_id()); ?>
  <div><span class="prompt">$</span> <span class="cmd">cat <?php echo esc_html($slug === 'licenses' ? 'LICENSE' : $slug . '.md'); ?></span><span class="cursor"></span></div>
  <?php while (have_posts()) : the_post(); ?>
    <article id="content" class="ff-content"><?php the_content(); ?></article>
  <?php endwhile; ?>
  <a href="<?php echo esc_url(home_url('/')); ?>" class="back-link">&lt; cd ~</a>
</main>
<?php get_footer(); ?>
