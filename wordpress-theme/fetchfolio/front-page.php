<?php get_header(); ?>
<main class="terminal-body" id="main">
  <?php
    $ascii = get_theme_mod('fetchfolio_ascii', '');
    if (!$ascii) $ascii = file_get_contents(get_theme_file_path('/assets/ascii-art.txt')) ?: 'FetchFolio';
  ?>
  <div class="ascii-art" aria-hidden="true"><?php echo esc_html($ascii); ?></div>
  <div class="fetch-info">
    <h1><span class="user-host"><?php echo esc_html(get_theme_mod('fetchfolio_user', 'user')); ?></span>@<span class="user-host"><?php echo esc_html(get_theme_mod('fetchfolio_host', 'hostname')); ?></span></h1>
    <div class="separator">-------------------------------------</div>
    <nav aria-label="<?php esc_attr_e('Sections', 'fetchfolio'); ?>">
      <?php if (has_nav_menu('primary')) : ?>
        <?php wp_nav_menu(array('theme_location' => 'primary', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1, 'walker' => new FetchFolio_Menu_Walker())); ?>
      <?php else : ?>
        <?php foreach (get_pages(array('sort_column' => 'menu_order,post_title')) as $page) : ?>
          <a class="nav-row" href="<?php echo esc_url(get_permalink($page)); ?>"><span class="key"><?php echo esc_html(get_the_title($page)); ?>:</span><span class="value"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($page->post_content), 7, '…')); ?></span></a>
        <?php endforeach; ?>
        <a class="nav-row" href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: home_url('/')); ?>"><span class="key">Blog:</span><span class="value"><?php esc_html_e('Notes, guides & updates', 'fetchfolio'); ?></span></a>
      <?php endif; ?>
    </nav>
    <nav class="social-links" aria-label="<?php esc_attr_e('Profiles', 'fetchfolio'); ?>">
      <?php fetchfolio_social_links(); ?>
      <a class="social-box white" href="<?php echo esc_url(home_url('/licenses/')); ?>" title="<?php esc_attr_e('Licenses', 'fetchfolio'); ?>" aria-label="<?php esc_attr_e('Licenses', 'fetchfolio'); ?>"><img src="<?php echo esc_url(get_theme_file_uri('/assets/icons/license.svg')); ?>" alt=""></a>
    </nav>
    <?php while (have_posts()) : the_post(); if (trim(get_the_content())) : ?><div class="home-content"><?php the_content(); ?></div><?php endif; endwhile; ?>
  </div>
</main>
<?php get_footer(); ?>
