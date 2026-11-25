<?php get_header(); ?>
<main class="terminal-body" id="main">
  <div><span class="prompt">$</span> <span class="cmd">ls blog/</span><span class="cursor"></span></div>
  <div class="blog-list">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <article class="blog-card">
        <div class="blog-meta"><?php echo esc_html(get_the_date('Y-m-d')); ?></div>
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <?php if (has_excerpt()) : ?><p><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
        <a class="blog-read" href="<?php the_permalink(); ?>"><?php esc_html_e('read post', 'fetchfolio'); ?> &gt;</a>
      </article>
    <?php endwhile; the_posts_pagination(); else : ?><p class="blog-empty"><?php esc_html_e('No posts yet.', 'fetchfolio'); ?></p><?php endif; ?>
  </div>
  <a href="<?php echo esc_url(home_url('/')); ?>" class="back-link">&lt; cd ~</a>
</main>
<?php get_footer(); ?>
