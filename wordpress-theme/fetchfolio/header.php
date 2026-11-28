<?php
if (!defined('ABSPATH')) exit;
$user = get_theme_mod('fetchfolio_user', 'user');
$host = get_theme_mod('fetchfolio_host', 'hostname');
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>
try {
  var ffSavedTheme = localStorage.getItem('fetchfolio-theme');
  document.documentElement.dataset.theme = ffSavedTheme === 'light' ? 'light' : 'dark';
} catch (e) { document.documentElement.dataset.theme = 'dark'; }
window.wp_consent_type = 'optin';
document.addEventListener('DOMContentLoaded', function () {
  document.dispatchEvent(new CustomEvent('wp_consent_type_defined'));
});
</script>
<meta name="color-scheme" content="dark light">
<meta name="theme-color" content="#0c0c0d" data-ff-theme-color>
<?php wp_head(); ?>
</head>
<body <?php body_class(is_front_page() ? 'home' : 'page'); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e('Skip to content', 'fetchfolio'); ?></a>
<div class="terminal-window">
  <div class="terminal-bar">
    <span class="terminal-dot dot-red" aria-hidden="true"></span><span class="terminal-dot dot-yellow" aria-hidden="true"></span><span class="terminal-dot dot-green" aria-hidden="true"></span>
    <span class="terminal-title"><?php echo esc_html($user . '@' . $host . ':' . fetchfolio_terminal_path()); ?></span>
    <?php
    $ff_languages = function_exists('pll_the_languages')
        ? pll_the_languages(array(
            'raw' => true,
            'show_flags' => false,
            'show_labels' => 'codes',
            'hide_if_empty' => false,
            'hide_if_no_translation' => false,
        ))
        : array();
    ?>
    <?php if (is_array($ff_languages) && !empty($ff_languages)) : ?>
      <nav class="language-switcher" aria-label="<?php esc_attr_e('Language', 'fetchfolio'); ?>">
        <span class="language-switcher-prompt" aria-hidden="true">$ lang</span>
        <?php $ff_language_first = true; foreach ($ff_languages as $ff_language) :
            $ff_language_slug = sanitize_key($ff_language['slug'] ?? '');
            if ($ff_language_slug === '') continue;
        ?>
          <?php if (!$ff_language_first) : ?><span class="language-switcher-separator" aria-hidden="true">|</span><?php endif; ?>
          <?php if (!empty($ff_language['is_current'])) : ?>
            <span class="language-switcher-current" lang="<?php echo esc_attr($ff_language_slug); ?>" aria-current="true"><?php echo esc_html(strtoupper($ff_language_slug)); ?></span>
          <?php else : ?>
            <a class="language-switcher-link" href="<?php echo esc_url($ff_language['url']); ?>" hreflang="<?php echo esc_attr($ff_language['locale'] ?? $ff_language_slug); ?>" lang="<?php echo esc_attr($ff_language_slug); ?>" aria-label="<?php echo esc_attr(sprintf(__('Switch to %s', 'fetchfolio'), strtoupper($ff_language_slug))); ?>"><?php echo esc_html(strtoupper($ff_language_slug)); ?></a>
          <?php endif; ?>
        <?php $ff_language_first = false; endforeach; ?>
      </nav>
    <?php endif; ?>
    <button class="theme-toggle" type="button" data-ff-theme-toggle
      data-ff-light-text="<?php echo esc_attr__('Light', 'fetchfolio'); ?>"
      data-ff-dark-text="<?php echo esc_attr__('Dark', 'fetchfolio'); ?>"
      data-ff-light-aria="<?php echo esc_attr__('Switch to light theme', 'fetchfolio'); ?>"
      data-ff-dark-aria="<?php echo esc_attr__('Switch to dark theme', 'fetchfolio'); ?>"
      aria-label="<?php echo esc_attr__('Switch to light theme', 'fetchfolio'); ?>" aria-pressed="false"><span aria-hidden="true">☼</span><span class="theme-toggle-label"><?php esc_html_e('Light', 'fetchfolio'); ?></span></button>
  </div>
