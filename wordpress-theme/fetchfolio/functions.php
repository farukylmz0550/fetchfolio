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

function fetchfolio_default_site_title() {
    if (in_array(get_option('blogname'), array('[Your Site Name]', 'WordPress'), true)) {
        update_option('blogname', 'FetchFolio');
    }
}
add_action('after_setup_theme', 'fetchfolio_default_site_title', 20);

function fetchfolio_remove_default_site_description() {
    if (get_option('blogdescription') === '[A short description of your site.]') {
        update_option('blogdescription', '');
    }
}
add_action('after_setup_theme', 'fetchfolio_remove_default_site_description', 21);

/** Route public content's .md companion URL to a plain-text Markdown response. */
function fetchfolio_markdown_rewrite() {
    add_rewrite_rule('^(.+)\\.md/?$', 'index.php?fetchfolio_markdown=$matches[1]', 'top');
    add_rewrite_rule('^ai\\.txt/?$', 'index.php?fetchfolio_ai_txt=1', 'top');
}
add_action('init', 'fetchfolio_markdown_rewrite');

function fetchfolio_blog_post_rewrite() {
    add_rewrite_rule('^blogs/(?!.*\\.md$)([^/]+)/?$', 'index.php?name=$matches[1]', 'top');
}
add_action('init', 'fetchfolio_blog_post_rewrite', 1);

function fetchfolio_blog_post_permalink($permalink, $post, $leavename = false, $sample = false) {
    if ($post->post_type !== 'post') return $permalink;
    $slug = $leavename ? '%postname%' : $post->post_name;
    return home_url('/blogs/' . $slug);
}
add_filter('post_link', 'fetchfolio_blog_post_permalink', 10, 4);

function fetchfolio_blog_canonical_no_trailing_slash($redirect_url, $requested_url) {
    $path = (string) wp_parse_url($requested_url, PHP_URL_PATH);
    if (!preg_match('#^/blogs/[^/]+/?$#', $path)) return $redirect_url;
    return substr($path, -1) === '/' ? untrailingslashit($redirect_url) : false;
}
add_filter('redirect_canonical', 'fetchfolio_blog_canonical_no_trailing_slash', 10, 2);

function fetchfolio_redirect_legacy_post_url() {
    if (!is_singular('post')) return;
    $current_path = untrailingslashit((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
    $canonical_path = untrailingslashit((string) wp_parse_url(get_permalink(), PHP_URL_PATH));
    if ($current_path !== $canonical_path) {
        wp_safe_redirect(get_permalink(), 301, 'FetchFolio');
        exit;
    }
}
add_action('template_redirect', 'fetchfolio_redirect_legacy_post_url', 1);

add_action('after_switch_theme', function () {
    fetchfolio_markdown_rewrite();
    fetchfolio_blog_post_rewrite();
    flush_rewrite_rules(false);
});

function fetchfolio_markdown_alternate() {
    if (is_singular()) {
        printf('<link rel="alternate" type="text/markdown" href="%s">' . "\n", esc_url(untrailingslashit(get_permalink()) . '.md'));
    }
}
add_action('wp_head', 'fetchfolio_markdown_alternate', 2);

function fetchfolio_markdown_query_var($vars) {
    $vars[] = 'fetchfolio_markdown';
    $vars[] = 'fetchfolio_ai_txt';
    return $vars;
}
add_filter('query_vars', 'fetchfolio_markdown_query_var');

/** Publish crawler preferences through WordPress's virtual robots.txt and /ai.txt. */
function fetchfolio_crawler_policy() {
    $base = "# Public content is available to search engines and user-requested AI fetchers.\n"
        . "User-agent: *\nAllow: /\nDisallow: /wp-admin/\nDisallow: /wp-login.php\nDisallow: /admin\n"
        . "Allow: /wp-admin/admin-ajax.php\n\n"
        . "User-agent: Googlebot\nAllow: /\nDisallow: /wp-admin/\nDisallow: /wp-login.php\nDisallow: /admin\n\n"
        . "User-agent: Bingbot\nAllow: /\nDisallow: /wp-admin/\nDisallow: /wp-login.php\nDisallow: /admin\n\n"
        . "User-agent: OAI-SearchBot\nAllow: /\nDisallow: /wp-admin/\nDisallow: /wp-login.php\nDisallow: /admin\n\n"
        . "User-agent: ChatGPT-User\nAllow: /\nDisallow: /wp-admin/\nDisallow: /wp-login.php\nDisallow: /admin\n\n"
        . "User-agent: Claude-SearchBot\nAllow: /\nDisallow: /wp-admin/\nDisallow: /wp-login.php\nDisallow: /admin\n\n"
        . "User-agent: Claude-User\nAllow: /\nDisallow: /wp-admin/\nDisallow: /wp-login.php\nDisallow: /admin\n\n"
        . "User-agent: Perplexity-User\nAllow: /\nDisallow: /wp-admin/\nDisallow: /wp-login.php\nDisallow: /admin\n\n"
        . "User-agent: Meta-ExternalFetcher\nAllow: /\nDisallow: /wp-admin/\nDisallow: /wp-login.php\nDisallow: /admin\n\n"
        . "User-agent: GPTBot\nDisallow: /\n\nUser-agent: Google-Extended\nDisallow: /\n\n"
        . "User-agent: ClaudeBot\nDisallow: /\n\nUser-agent: anthropic-ai\nDisallow: /\n\n"
        . "User-agent: PerplexityBot\nDisallow: /\n\nUser-agent: Meta-ExternalAgent\nDisallow: /\n\n"
        . "User-agent: Bytespider\nDisallow: /\n\nUser-agent: CCBot\nDisallow: /\n\n"
        . "User-agent: DeepSeekBot\nDisallow: /\n\nUser-agent: GLM-Spider\nDisallow: /\n\n"
        . "User-agent: QwenBot\nDisallow: /\n\nUser-agent: Amazonbot\nDisallow: /\n\n"
        . "User-agent: Applebot-Extended\nDisallow: /\n\nUser-agent: cohere-ai\nDisallow: /\n\n"
        . "User-agent: Diffbot\nDisallow: /\n\nUser-agent: ImagesiftBot\nDisallow: /\n";
    return $base . "\nSitemap: " . home_url('/wp-sitemap.xml') . "\n";
}
add_filter('robots_txt', function ($output, $public) {
    if (!$public) return "User-agent: *\nDisallow: /\n";
    return fetchfolio_crawler_policy();
}, 10, 2);

function fetchfolio_serve_ai_txt() {
    if (!get_query_var('fetchfolio_ai_txt')) return;
    status_header(200);
    header('Content-Type: text/plain; charset=UTF-8');
    header('X-Robots-Tag: noindex');
    echo "# FetchFolio AI access policy\n"
        . "Allow public pages to search engines and AI assistants fetching content at a visitor's request.\n"
        . "Disallow automated collection, training, dataset creation, and bulk extraction.\n"
        . "Disallow automated collection by GPTBot, Google-Extended, ClaudeBot, anthropic-ai, PerplexityBot, Meta-ExternalAgent, Bytespider, CCBot, DeepSeekBot, GLM-Spider, QwenBot, Amazonbot, Applebot-Extended, cohere-ai, Diffbot, and ImagesiftBot.\n"
        . "Allow visitor-requested access by ChatGPT-User, OAI-SearchBot, Claude-User, Claude-SearchBot, Perplexity-User, and Meta-ExternalFetcher.\n"
        . "WordPress dashboard and login routes are private and require an authorized account.\n"
        . "See /robots.txt for crawler-specific rules.\n";
    exit;
}
add_action('template_redirect', 'fetchfolio_serve_ai_txt', 0);

function fetchfolio_html_to_markdown($html, $heading_offset = 0) {
    if (!class_exists('DOMDocument')) return trim(wp_strip_all_tags($html));
    $previous = libxml_use_internal_errors(true);
    $document = new DOMDocument('1.0', 'UTF-8');
    $document->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $convert = function ($node) use (&$convert, $heading_offset) {
        if ($node->nodeType === XML_TEXT_NODE) return $node->nodeValue;
        if ($node->nodeType !== XML_ELEMENT_NODE) return '';
        $tag = strtolower($node->nodeName);
        $inner = '';
        foreach ($node->childNodes as $child) $inner .= $convert($child);
        $inner = trim($inner);
        switch ($tag) {
            case 'h1': case 'h2': case 'h3': case 'h4': case 'h5': case 'h6':
                $level = max(2, min(6, (int) substr($tag, 1) + $heading_offset));
                return "\n\n" . str_repeat('#', $level) . ' ' . $inner . "\n\n";
            case 'p': case 'div': return $inner === '' ? '' : "\n\n" . $inner . "\n\n";
            case 'br': return "  \n";
            case 'strong': case 'b': return '**' . $inner . '**';
            case 'em': case 'i': return '*' . $inner . '*';
            case 'code': return '`' . $inner . '`';
            case 'pre': return "\n\n```\n" . trim($node->textContent) . "\n```\n\n";
            case 'a': $href = $node->getAttribute('href'); return $href ? '[' . $inner . '](' . $href . ')' : $inner;
            case 'li': return '- ' . $inner . "\n";
            case 'ul': case 'ol': return "\n" . $inner . "\n";
            case 'blockquote': return "\n\n> " . str_replace("\n", "\n> ", $inner) . "\n\n";
            case 'hr': return "\n\n---\n\n";
            case 'img': $alt = $node->getAttribute('alt'); $src = $node->getAttribute('src'); return $src ? '![' . $alt . '](' . $src . ')' : '';
            default: return $inner;
        }
    };
    $markdown = '';
    foreach ($document->documentElement->childNodes as $child) $markdown .= $convert($child);
    $markdown = preg_replace("/\\n{3,}/", "\n\n", $markdown);
    return trim(html_entity_decode($markdown, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

function fetchfolio_serve_markdown() {
    $requested_path = get_query_var('fetchfolio_markdown');
    if (!$requested_path) return;
    $requested_path = sanitize_text_field($requested_path);
    if (strpos($requested_path, 'blogs/') === 0) {
        $post = get_page_by_path(substr($requested_path, 6), OBJECT, 'post');
    } else {
        $post = get_page_by_path($requested_path, OBJECT, array('page', 'post'));
    }
    if (!$post || $post->post_status !== 'publish' || !is_post_publicly_viewable($post)) {
        status_header(404);
        header('Content-Type: text/plain; charset=UTF-8');
        echo "Not found\n";
        exit;
    }
    status_header(200);
    header('Content-Type: text/markdown; charset=UTF-8');
    header('X-Robots-Tag: noindex, follow');
    header('Link: <' . esc_url_raw(get_permalink($post)) . '>; rel=canonical');
    $body = apply_filters('the_content', $post->post_content);
    // The post title is the document H1. Keep body H2/H3 headings at their
    // authored levels and promote any body H1 to H2 under the document title.
    echo '# ' . wp_strip_all_tags(get_the_title($post)) . "\n\n" . fetchfolio_html_to_markdown($body) . "\n";
    exit;
}
add_action('template_redirect', 'fetchfolio_serve_markdown', 0);

function fetchfolio_assets() {
    wp_enqueue_style('fetchfolio-site', get_theme_file_uri('/assets/css/site.css'), array(), '3.1.0');
    wp_enqueue_style('fetchfolio-theme', get_stylesheet_uri(), array('fetchfolio-site'), '3.1.0');
    wp_enqueue_script('fetchfolio-preferences', get_theme_file_uri('/assets/js/preferences.js'), array(), '3.1.1', true);
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
        'fetchfolio_user' => array(__('Terminal user', 'fetchfolio'), 'fetch'),
        'fetchfolio_host' => array(__('Terminal host', 'fetchfolio'), 'folio'),
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

    $wp_customize->add_section('fetchfolio_home_sections', array(
        'title' => __('Homepage sections', 'fetchfolio'),
        'priority' => 31,
        'description' => __('Set the short text shown after each section name and choose its position.', 'fetchfolio'),
    ));
    $labels = array(
        'about' => __('About description', 'fetchfolio'),
        'education' => __('Education description', 'fetchfolio'),
        'achievements' => __('Achievements description', 'fetchfolio'),
        'cv' => __('CV description', 'fetchfolio'),
        'blog' => __('Blog description', 'fetchfolio'),
        'contact' => __('Contact description', 'fetchfolio'),
    );
    foreach (fetchfolio_home_descriptions() as $slug => $default) {
        $key = 'fetchfolio_home_' . $slug . '_description';
        $wp_customize->add_setting($key, array(
            'default' => $default,
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'refresh',
        ));
        $wp_customize->add_control($key, array(
            'section' => 'fetchfolio_home_sections',
            'label' => $labels[$slug],
            'type' => 'text',
        ));
    }
    $position_labels = array(
        'about' => __('About position', 'fetchfolio'),
        'education' => __('Education position', 'fetchfolio'),
        'achievements' => __('Achievements position', 'fetchfolio'),
        'cv' => __('CV position', 'fetchfolio'),
        'blog' => __('Blog position', 'fetchfolio'),
        'contact' => __('Contact position', 'fetchfolio'),
    );
    foreach (fetchfolio_home_positions() as $slug => $default) {
        $key = 'fetchfolio_home_' . $slug . '_position';
        $wp_customize->add_setting($key, array(
            'default' => $default,
            'sanitize_callback' => 'absint',
            'transport' => 'refresh',
        ));
        $wp_customize->add_control($key, array(
            'section' => 'fetchfolio_home_sections',
            'label' => $position_labels[$slug],
            'type' => 'select',
            'choices' => array_combine(range(1, 6), range(1, 6)),
        ));
    }
}
add_action('customize_register', 'fetchfolio_customizer');

function fetchfolio_home_descriptions() {
    return array(
        'about' => __('Personal Introduction', 'fetchfolio'),
        'education' => __('School History & Studies', 'fetchfolio'),
        'achievements' => __('Projects & Achievements', 'fetchfolio'),
        'cv' => __('Resume & Skills', 'fetchfolio'),
        'blog' => __('Articles & Updates', 'fetchfolio'),
        'contact' => __('Email & Social Media', 'fetchfolio'),
    );
}

function fetchfolio_home_positions() {
    return array('about' => 1, 'education' => 2, 'achievements' => 3, 'cv' => 4, 'blog' => 5, 'contact' => 6);
}

function fetchfolio_sort_home_menu($items, $args) {
    if (($args->theme_location ?? '') !== 'primary') return $items;
    $defaults = fetchfolio_home_positions();
    usort($items, function ($left, $right) use ($defaults) {
        $left_slug = $left->object === 'page' ? get_post_field('post_name', $left->object_id) : '';
        $right_slug = $right->object === 'page' ? get_post_field('post_name', $right->object_id) : '';
        if ($left_slug === 'blogs') $left_slug = 'blog';
        if ($right_slug === 'blogs') $right_slug = 'blog';
        $left_position = absint(get_theme_mod('fetchfolio_home_' . $left_slug . '_position', $defaults[$left_slug] ?? 99));
        $right_position = absint(get_theme_mod('fetchfolio_home_' . $right_slug . '_position', $defaults[$right_slug] ?? 99));
        return ($left_position <=> $right_position) ?: ($left->menu_order <=> $right->menu_order);
    });
    return $items;
}
add_filter('wp_nav_menu_objects', 'fetchfolio_sort_home_menu', 10, 2);

function fetchfolio_add_homepage_settings_page() {
    add_theme_page(
        __('Homepage Settings', 'fetchfolio'),
        __('Homepage Settings', 'fetchfolio'),
        'edit_theme_options',
        'fetchfolio-homepage-settings',
        'fetchfolio_render_homepage_settings_page'
    );
}
add_action('admin_menu', 'fetchfolio_add_homepage_settings_page');

function fetchfolio_render_homepage_settings_page() {
    if (!current_user_can('edit_theme_options')) return;
    $labels = array(
        'about' => __('About description', 'fetchfolio'),
        'education' => __('Education description', 'fetchfolio'),
        'achievements' => __('Achievements description', 'fetchfolio'),
        'cv' => __('CV description', 'fetchfolio'),
        'blog' => __('Blog description', 'fetchfolio'),
        'contact' => __('Contact description', 'fetchfolio'),
    );
    $position_labels = array(
        'about' => __('About position', 'fetchfolio'),
        'education' => __('Education position', 'fetchfolio'),
        'achievements' => __('Achievements position', 'fetchfolio'),
        'cv' => __('CV position', 'fetchfolio'),
        'blog' => __('Blog position', 'fetchfolio'),
        'contact' => __('Contact position', 'fetchfolio'),
    );
    $ascii_default = get_theme_mod('fetchfolio_ascii', '');
    if ($ascii_default === '') $ascii_default = (string) file_get_contents(get_theme_file_path('/assets/ascii-art.txt'));
    ?>
    <div class="wrap">
      <h1><?php esc_html_e('Homepage Settings', 'fetchfolio'); ?></h1>
      <p><?php esc_html_e('Edit the text and order shown on the homepage. These descriptions are independent of page content.', 'fetchfolio'); ?></p>
      <?php if (isset($_GET['updated'])) : ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e('Homepage settings saved.', 'fetchfolio'); ?></p></div><?php endif; ?>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="fetchfolio_save_homepage_settings">
        <?php wp_nonce_field('fetchfolio_homepage_settings'); ?>
        <table class="form-table" role="presentation"><tbody>
          <tr><th scope="row"><label for="fetchfolio_user"><?php esc_html_e('Terminal user', 'fetchfolio'); ?></label></th><td><input class="regular-text" id="fetchfolio_user" name="fetchfolio_user" value="<?php echo esc_attr(get_theme_mod('fetchfolio_user', 'fetch')); ?>"></td></tr>
          <tr><th scope="row"><label for="fetchfolio_host"><?php esc_html_e('Terminal host', 'fetchfolio'); ?></label></th><td><input class="regular-text" id="fetchfolio_host" name="fetchfolio_host" value="<?php echo esc_attr(get_theme_mod('fetchfolio_host', 'folio')); ?>"></td></tr>
          <tr><th scope="row"><label for="fetchfolio_ascii"><?php esc_html_e('ASCII art', 'fetchfolio'); ?></label></th><td><textarea class="large-text code" rows="12" id="fetchfolio_ascii" name="fetchfolio_ascii"><?php echo esc_textarea($ascii_default); ?></textarea></td></tr>
          <?php foreach (fetchfolio_home_descriptions() as $slug => $default) : $description_key = 'fetchfolio_home_' . $slug . '_description'; $position_key = 'fetchfolio_home_' . $slug . '_position'; ?>
            <tr><th scope="row"><label for="<?php echo esc_attr($description_key); ?>"><?php echo esc_html($labels[$slug]); ?></label></th><td><input class="regular-text" id="<?php echo esc_attr($description_key); ?>" name="<?php echo esc_attr($description_key); ?>" value="<?php echo esc_attr(get_theme_mod($description_key, $default)); ?>"></td></tr>
            <tr><th scope="row"><label for="<?php echo esc_attr($position_key); ?>"><?php echo esc_html($position_labels[$slug]); ?></label></th><td><select id="<?php echo esc_attr($position_key); ?>" name="<?php echo esc_attr($position_key); ?>"><?php for ($position = 1; $position <= 6; $position++) : ?><option value="<?php echo esc_attr($position); ?>" <?php selected((int) get_theme_mod($position_key, fetchfolio_home_positions()[$slug]), $position); ?>><?php echo esc_html($position); ?></option><?php endfor; ?></select></td></tr>
          <?php endforeach; ?>
        </tbody></table>
        <?php submit_button(__('Save Homepage Settings', 'fetchfolio')); ?>
      </form>
    </div>
    <?php
}

function fetchfolio_save_homepage_settings() {
    if (!current_user_can('edit_theme_options')) wp_die(esc_html__('You are not allowed to change these settings.', 'fetchfolio'));
    check_admin_referer('fetchfolio_homepage_settings');
    $input = wp_unslash($_POST);
    set_theme_mod('fetchfolio_user', sanitize_text_field($input['fetchfolio_user'] ?? 'fetch'));
    set_theme_mod('fetchfolio_host', sanitize_text_field($input['fetchfolio_host'] ?? 'folio'));
    set_theme_mod('fetchfolio_ascii', sanitize_textarea_field($input['fetchfolio_ascii'] ?? ''));
    foreach (fetchfolio_home_descriptions() as $slug => $default) {
        $description_key = 'fetchfolio_home_' . $slug . '_description';
        $position_key = 'fetchfolio_home_' . $slug . '_position';
        set_theme_mod($description_key, sanitize_text_field($input[$description_key] ?? $default));
        set_theme_mod($position_key, max(1, min(6, absint($input[$position_key] ?? fetchfolio_home_positions()[$slug]))));
    }
    wp_safe_redirect(add_query_arg('updated', '1', admin_url('themes.php?page=fetchfolio-homepage-settings')));
    exit;
}
add_action('admin_post_fetchfolio_save_homepage_settings', 'fetchfolio_save_homepage_settings');

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
    $blog = get_page_by_path('blogs', OBJECT, 'page');
    $blog_id = wp_insert_post(array(
        'ID' => $blog ? $blog->ID : 0,
        'post_type' => 'page', 'post_status' => 'publish',
        'post_title' => __('Blog', 'fetchfolio'), 'post_name' => 'blogs', 'post_content' => '',
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
        $slug = $data_object->object === 'page' ? get_post_field('post_name', $data_object->object_id) : '';
        if ($slug === 'blogs') $slug = 'blog';
        $descriptions = array(
            'about' => __('Personal Introduction', 'fetchfolio'),
            'education' => __('School History & Studies', 'fetchfolio'),
            'achievements' => __('Projects & Achievements', 'fetchfolio'),
            'cv' => __('Resume & Skills', 'fetchfolio'),
            'blog' => __('Articles & Updates', 'fetchfolio'),
            'contact' => __('Email & Social Media', 'fetchfolio'),
        );
        $description = get_theme_mod('fetchfolio_home_' . $slug . '_description', $descriptions[$slug] ?? __('Open section', 'fetchfolio'));
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
