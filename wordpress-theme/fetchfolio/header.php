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
  document.documentElement.dataset.theme = ffSavedTheme || (matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
} catch (e) { document.documentElement.dataset.theme = 'dark'; }
window.wp_consent_type = 'optin';
document.addEventListener('DOMContentLoaded', function () {
  document.dispatchEvent(new CustomEvent('wp_consent_type_defined'));
});
</script>
<?php wp_head(); ?>
</head>
<body <?php body_class(is_front_page() ? 'home' : 'page'); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e('Skip to content', 'fetchfolio'); ?></a>
<div class="terminal-window">
  <div class="terminal-bar">
    <span class="terminal-dot dot-red" aria-hidden="true"></span><span class="terminal-dot dot-yellow" aria-hidden="true"></span><span class="terminal-dot dot-green" aria-hidden="true"></span>
    <span class="terminal-title"><?php echo esc_html($user . '@' . $host . ':' . fetchfolio_terminal_path()); ?></span>
    <button class="theme-toggle" type="button" data-ff-theme-toggle aria-label="Switch to light theme" aria-pressed="false"><span aria-hidden="true">☼</span><span class="theme-toggle-label">Light</span></button>
  </div>
