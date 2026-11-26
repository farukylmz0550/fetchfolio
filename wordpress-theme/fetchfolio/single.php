<?php get_header(); ?>
<main class="terminal-body" id="main">
  <div><span class="prompt">$</span> <span class="cmd">cat blog/<?php echo esc_html(get_post_field('post_name', get_queried_object_id())); ?>.md</span><span class="cursor"></span></div>
  <?php while (have_posts()) : the_post(); ?>
    <article id="content" class="ff-content"><h1><?php the_title(); ?></h1><p class="post-meta"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time></p><?php the_content(); ?></article>
  <?php endwhile; ?>
  <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: home_url('/blog/')); ?>" class="back-link">&lt; cd blog</a>
</main>
<?php get_footer(); ?>
