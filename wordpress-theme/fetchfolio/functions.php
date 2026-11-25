<?php
/** FetchFolio theme setup and settings. */

if (!defined('ABSPATH')) {
    exit;
}

function fetchfolio_setup() {
    load_theme_textdomain('fetchfolio', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
    register_nav_menus(array('primary' => __('Terminal sections', 'fetchfolio')));
}
add_action('after_setup_theme', 'fetchfolio_setup');

function fetchfolio_assets() {
    wp_enqueue_style('fetchfolio-site', get_theme_file_uri('/assets/css/site.css'), array(), '3.0.0');
    wp_enqueue_style('fetchfolio-theme', get_stylesheet_uri(), array('fetchfolio-site'), '3.0.0');
    wp_enqueue_script('fetchfolio-preferences', get_theme_file_uri('/assets/js/preferences.js'), array(), '3.0.0', true);
    $accent = sanitize_hex_color(get_theme_mod('fetchfolio_accent', '#38bdf8'));
    if ($accent) {
        wp_add_inline_style('fetchfolio-theme', ':root{--ff-user-accent:' . $accent . '}');
    }
}
add_action('wp_enqueue_scripts', 'fetchfolio_assets');

/** Treat statistics as opt-in when WP Consent API is present. */
function fetchfolio_consent_type($type) {
    return 'optin';
}
add_filter('wp_get_consent_type', 'fetchfolio_consent_type');

/** Let WP Statistics recognize the theme's own WP Consent API consent controls. */
function fetchfolio_statistics_consent_provider($providers) {
    $providers['fetchfolio-theme'] = __('FetchFolio theme consent controls', 'fetchfolio');
    return $providers;
}
add_filter('wp_statistics_consent_api_compatible_plugins', 'fetchfolio_statistics_consent_provider');

function fetchfolio_customizer($wp_customize) {
    $wp_customize->add_section('fetchfolio_identity', array(
        'title' => __('FetchFolio terminal', 'fetchfolio'),
        'priority' => 30,
    ));

    $fields = array(
        'fetchfolio_user' => array(__('Terminal user', 'fetchfolio'), 'user'),
        'fetchfolio_host' => array(__('Terminal host', 'fetchfolio'), 'hostname'),
        'fetchfolio_accent' => array(__('Accent color', 'fetchfolio'), '#38bdf8'),
        'fetchfolio_ascii' => array(__('ASCII art', 'fetchfolio'), ''),
        'fetchfolio_github' => array(__('GitHub URL', 'fetchfolio'), ''),
        'fetchfolio_youtube' => array(__('YouTube URL', 'fetchfolio'), ''),
        'fetchfolio_makerworld' => array(__('MakerWorld URL', 'fetchfolio'), ''),
        'fetchfolio_linkedin' => array(__('LinkedIn URL', 'fetchfolio'), ''),
        'fetchfolio_discord' => array(__('Discord URL', 'fetchfolio'), ''),
    );

    foreach ($fields as $id => $field) {
        $is_color = $id === 'fetchfolio_accent';
        $is_textarea = $id === 'fetchfolio_ascii';
        $is_url = in_array($id, array('fetchfolio_github', 'fetchfolio_youtube', 'fetchfolio_makerworld', 'fetchfolio_linkedin', 'fetchfolio_discord'), true);
        $wp_customize->add_setting($id, array(
            'default' => $field[1],
            'sanitize_callback' => $is_color ? 'sanitize_hex_color' : ($is_url ? 'esc_url_raw' : ($is_textarea ? 'sanitize_textarea_field' : 'sanitize_text_field')),
            'transport' => 'refresh',
        ));
        $wp_customize->add_control($id, array(
            'section' => 'fetchfolio_identity',
            'label' => $field[0],
            'type' => $is_color ? 'color' : ($is_textarea ? 'textarea' : 'text'),
        ));
    }
}
add_action('customize_register', 'fetchfolio_customizer');

/** Import editable WordPress starter pages and posts once on first activation. */
function fetchfolio_import_starter_content() {
    if (get_option('fetchfolio_starter_content_version')) return;

    $file = get_theme_file_path('/assets/starter-content.json');
    if (!is_readable($file)) return;
    $data = json_decode(file_get_contents($file), true);
    if (!is_array($data) || empty($data['pages'])) return;

    $page_ids = array();
    foreach ($data['pages'] as $order => $page) {
        $existing = get_page_by_path($page['slug'], OBJECT, 'page');
        $id = wp_insert_post(array(
            'ID' => $existing ? $existing->ID : 0,
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => $page['title'],
            'post_name' => $page['slug'],
            'post_content' => $page['content'],
            'menu_order' => $order,
        ), true);
        if (!is_wp_error($id)) $page_ids[$page['slug']] = $id;
    }

    $home = get_page_by_path('home', OBJECT, 'page');
    $home_id = wp_insert_post(array(
        'ID' => $home ? $home->ID : 0,
        'post_type' => 'page', 'post_status' => 'publish',
        'post_title' => __('Home', 'fetchfolio'), 'post_name' => 'home', 'post_content' => '',
    ), true);
    $blog = get_page_by_path('blog', OBJECT, 'page');
    $blog_id = wp_insert_post(array(
        'ID' => $blog ? $blog->ID : 0,
        'post_type' => 'page', 'post_status' => 'publish',
        'post_title' => __('Blog', 'fetchfolio'), 'post_name' => 'blog', 'post_content' => '',
    ), true);
    if (!is_wp_error($home_id) && !is_wp_error($blog_id)) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home_id);
        update_option('page_for_posts', $blog_id);
    }

    foreach (($data['posts'] ?? array()) as $post) {
        $existing = get_page_by_path($post['slug'], OBJECT, 'post');
        wp_insert_post(array(
            'ID' => $existing ? $existing->ID : 0,
            'post_type' => 'post', 'post_status' => 'publish',
            'post_title' => $post['title'], 'post_name' => $post['slug'],
            'post_content' => $post['content'], 'post_excerpt' => $post['excerpt'] ?? '',
            'post_date' => ($post['date'] ?? current_time('Y-m-d')) . ' 12:00:00',
        ));
    }

    // Remove only the temporary sample post created for the first preview.
    $temporary_post = get_page_by_path('welcome-to-fetchfolio', OBJECT, 'post');
    if ($temporary_post) wp_delete_post($temporary_post->ID, true);
    foreach (array('hello-world') as $demo_slug) {
        $demo_post = get_page_by_path($demo_slug, OBJECT, 'post');
        if ($demo_post) wp_delete_post($demo_post->ID, true);
    }
    $sample_page = get_page_by_path('sample-page', OBJECT, 'page');
    if ($sample_page) wp_delete_post($sample_page->ID, true);

    $menu = wp_get_nav_menu_object('Terminal Sections');
    $menu_id = $menu ? $menu->term_id : wp_create_nav_menu('Terminal Sections');
    if (!is_wp_error($menu_id)) {
        foreach ((array) wp_get_nav_menu_items($menu_id) as $item) wp_delete_post($item->ID, true);
        $slugs = array('about', 'education', 'achievements', 'cv', 'contact');
        foreach ($slugs as $slug) {
            if (empty($page_ids[$slug])) continue;
            $page = null;
            foreach ($data['pages'] as $candidate) if ($candidate['slug'] === $slug) $page = $candidate;
            $menu_titles = array('about' => 'About', 'cv' => 'CV');
            wp_update_nav_menu_item($menu_id, 0, array(
                'menu-item-title' => $menu_titles[$slug] ?? $page['title'], 'menu-item-object' => 'page',
                'menu-item-object-id' => $page_ids[$slug], 'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish', 'menu-item-description' => $page['description'] ?? '',
            ));
        }
        if (!is_wp_error($blog_id)) {
            wp_update_nav_menu_item($menu_id, 0, array(
                'menu-item-title' => __('Blog', 'fetchfolio'), 'menu-item-object' => 'page',
                'menu-item-object-id' => $blog_id, 'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish', 'menu-item-description' => __('Notes, Guides & Updates', 'fetchfolio'),
            ));
        }
        $locations = get_theme_mod('nav_menu_locations', array());
        $locations['primary'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }

    $settings = $data['settings'] ?? array();
    foreach (array('user', 'host', 'accent', 'ascii', 'github', 'youtube', 'makerworld', 'linkedin', 'discord') as $key) {
        if (array_key_exists($key, $settings)) set_theme_mod('fetchfolio_' . $key, $settings[$key]);
    }
    update_option('permalink_structure', '/%postname%/');
    flush_rewrite_rules(false);
    update_option('fetchfolio_starter_content_version', '1');
}
add_action('after_switch_theme', 'fetchfolio_import_starter_content');

class FetchFolio_Menu_Walker extends Walker_Nav_Menu {
    public function start_el(&$output, $data_object, $depth = 0, $args = null, $current_object_id = 0) {
        $label = apply_filters('the_title', $data_object->title, $data_object->ID);
        $description = $data_object->description ?: __('Open section', 'fetchfolio');
        $output .= '<a class="nav-row" href="' . esc_url($data_object->url) . '"><span class="key">' . esc_html($label) . ':</span><span class="value">' . esc_html($description) . '</span></a>';
    }

    public function end_el(&$output, $data_object, $depth = 0, $args = null) {
        // The homepage menu intentionally contains direct links, without list wrappers.
    }
}

function fetchfolio_terminal_path() {
    if (is_front_page()) return '~/';
    if (is_home() || is_category() || is_tag() || is_archive()) return '~/blog';
    if (is_singular('post')) return '~/blog/' . get_post_field('post_name', get_queried_object_id());
    if (is_404()) return '~/404';
    return '~/' . sanitize_title(get_the_title());
}

function fetchfolio_social_links() {
    $links = array(
        'github' => array('GitHub', 'github.svg', 'https://github.com'),
        'youtube' => array('YouTube', 'youtube.svg', 'https://www.youtube.com'),
        'makerworld' => array('MakerWorld', 'makerworld.svg', 'https://makerworld.com'),
        'linkedin' => array('LinkedIn', 'linkedin.svg', 'https://www.linkedin.com'),
        'discord' => array('Discord', 'discord.svg', 'https://discord.com'),
    );
    foreach ($links as $key => $data) {
        $url = trim((string) get_theme_mod('fetchfolio_' . $key, ''));
        if ($url && !preg_match('~^https?://~i', $url)) $url = 'https://' . $url;
        $url = esc_url($url ?: $data[2]);
        printf('<a class="social-box %1$s" href="%2$s" target="_blank" rel="noopener noreferrer" title="%3$s" aria-label="%3$s"><img src="%4$s" alt=""></a>', esc_attr($key === 'discord' ? 'purple' : $key), $url, esc_attr($data[0]), esc_url(get_theme_file_uri('/assets/icons/' . $data[1])));
    }
}

function fetchfolio_favicon() {
    if (!has_site_icon()) printf('<link rel="icon" href="%s" sizes="any">', esc_url(get_theme_file_uri('/assets/favicon.ico')));
}
add_action('wp_head', 'fetchfolio_favicon', 1);

/** Keep the short /admin path familiar to FetchFolio users. */
function fetchfolio_admin_shortcut() {
    $path = trim((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    if ($path !== 'admin') return;

    $destination = is_user_logged_in() ? admin_url() : wp_login_url(admin_url());
    wp_safe_redirect($destination, 302, 'FetchFolio');
    exit;
}
add_action('template_redirect', 'fetchfolio_admin_shortcut', 1);
