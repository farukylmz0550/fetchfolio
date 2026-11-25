<?php get_header(); ?>
<main class="terminal-body" id="main">
  <div><span class="prompt">$</span> <span class="cmd">cat 404.md</span><span class="cursor"></span></div>
  <article class="ff-content"><h1><?php esc_html_e('File not found', 'fetchfolio'); ?></h1><p><span style="color:#ef4444">cat:</span> <?php echo esc_html(wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)); ?>: <?php esc_html_e('No such file or directory.', 'fetchfolio'); ?></p><a href="<?php echo esc_url(home_url('/')); ?>" class="back-link">&lt; cd ~</a></article>
</main>
<?php get_footer(); ?>
