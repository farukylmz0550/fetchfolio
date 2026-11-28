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

/** Keep Polylang language management within FetchFolio's five-language limit. */
function fetchfolio_limit_polylang_languages($term, $taxonomy) {
    if ($taxonomy !== 'language' || is_wp_error($term)) return $term;

    $languages = get_terms(array('taxonomy' => 'language', 'hide_empty' => false, 'fields' => 'ids'));
    if (!is_wp_error($languages) && count($languages) >= 5) {
        return new WP_Error(
            'fetchfolio_language_limit',
            __('FetchFolio supports up to five languages. Remove one before adding another.', 'fetchfolio')
        );
    }

    return $term;
}
add_filter('pre_insert_term', 'fetchfolio_limit_polylang_languages', 10, 2);

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
    add_rewrite_rule('^llms\\.txt/?$', 'index.php?fetchfolio_llms_txt=1', 'top');
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
    $vars[] = 'fetchfolio_llms_txt';
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

/** Publish translated Markdown URLs and the existing AI access policy at /llms.txt. */
function fetchfolio_serve_llms_txt() {
    if (!get_query_var('fetchfolio_llms_txt')) return;

    status_header(200);
    header('Content-Type: text/plain; charset=UTF-8');

    $languages = function_exists('pll_languages_list')
        ? pll_languages_list(array('hide_empty' => false, 'fields' => 'slug'))
        : array();
    $default_language = function_exists('pll_default_language')
        ? pll_default_language('slug')
        : '';

    if (empty($languages)) {
        $languages = array($default_language);
    }

    $pages = get_pages(array(
        'post_status' => 'publish',
        'sort_column' => 'menu_order,post_title',
    ));
    $posts = get_posts(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    ));
    $content = array_merge($pages, $posts);
    $lines = array(
        '# FetchFolio',
        'Language-indexed directory of public pages and posts. Markdown companions are provided for AI-friendly reading.',
    );

    foreach ($languages as $language) {
        $language = sanitize_key($language);
        $lines[] = '';
        $lines[] = '## Language: ' . strtoupper($language) . ' (' . $language . ')';

        foreach ($content as $item) {
            if (in_array($item->post_name, array('home', 'blogs'), true)) continue;

            $translated_id = $item->ID;
            if (function_exists('pll_get_post')) {
                $translated_id = pll_get_post($item->ID, $language);
                if (!$translated_id && $language === $default_language) {
                    $translated_id = $item->ID;
                }
            } elseif ($language !== $default_language) {
                continue;
            }

            if (!$translated_id) continue;
            $translated_post = get_post($translated_id);
            if (!$translated_post || !is_post_publicly_viewable($translated_post)) continue;

            $title = wp_strip_all_tags(html_entity_decode(get_the_title($translated_post), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $markdown_url = untrailingslashit(get_permalink($translated_post)) . '.md';
            $lines[] = '- [' . $title . '](' . esc_url_raw($markdown_url) . ')';
        }
    }

    $lines[] = '';
    $lines[] = '## AI access policy';
    $lines[] = '- Allow public pages for search engines and AI assistants fetching content at a visitor’s request.';
    $lines[] = '- Disallow automated collection, training, dataset creation, and bulk extraction.';
    $lines[] = '- Disallow GPTBot, Google-Extended, ClaudeBot, anthropic-ai, PerplexityBot, Meta-ExternalAgent, Bytespider, CCBot, DeepSeekBot, GLM-Spider, QwenBot, Amazonbot, Applebot-Extended, cohere-ai, Diffbot, and ImagesiftBot.';
    $lines[] = '- Allow visitor-requested access by ChatGPT-User, OAI-SearchBot, Claude-User, Claude-SearchBot, Perplexity-User, and Meta-ExternalFetcher.';
    $lines[] = '- WordPress dashboard and login routes require an authorized account.';
    $lines[] = '- See /robots.txt for crawler-specific rules.';

    echo implode("\n", $lines) . "\n";
    exit;
}
add_action('template_redirect', 'fetchfolio_serve_llms_txt', 0);

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
    $requested_path = trim(sanitize_text_field($requested_path), '/');

    $language = '';
    $languages = function_exists('pll_languages_list')
        ? pll_languages_list(array('hide_empty' => false, 'fields' => 'slug'))
        : array();
    $path_segments = explode('/', $requested_path);
    if (!empty($languages) && isset($path_segments[0]) && in_array($path_segments[0], $languages, true)) {
        $language = array_shift($path_segments);
        $requested_path = implode('/', $path_segments);
    }

    if (strpos($requested_path, 'blogs/') === 0) {
        $post = get_page_by_path(substr($requested_path, 6), OBJECT, 'post');
    } else {
        $post = get_page_by_path($requested_path, OBJECT, array('page', 'post'));
    }

    if ($post && $language && function_exists('pll_get_post')) {
        $translated_id = pll_get_post($post->ID, $language);
        $post = $translated_id ? get_post($translated_id) : null;
    }

    if (!$post || $post->post_status !== 'publish' || !is_post_publicly_viewable($post)) {
        status_header(404);
        header('Content-Type: text/plain; charset=UTF-8');
        echo esc_html__('Not found', 'fetchfolio') . "\n";
        exit;
    }

    $canonical_post = $post;
    if ($language && function_exists('pll_get_post')) {
        $canonical_id = pll_get_post($post->ID, $language);
        if ($canonical_id) $canonical_post = get_post($canonical_id);
    }

    status_header(200);
    header('Content-Type: text/markdown; charset=UTF-8');
    header('X-Robots-Tag: noindex, follow');
    header('Link: <' . esc_url_raw(get_permalink($canonical_post)) . '>; rel=canonical');
    $body = apply_filters('the_content', $post->post_content);
    // The post title is the document H1. Keep body H2/H3 headings at their
    // authored levels and promote any body H1 to H2 under the document title.
    echo '# ' . wp_strip_all_tags(get_the_title($post)) . "\n\n" . fetchfolio_html_to_markdown($body) . "\n";
    exit;
}
add_action('template_redirect', 'fetchfolio_serve_markdown', 0);

function fetchfolio_assets() {
    wp_enqueue_style('fetchfolio-site', get_theme_file_uri('/assets/css/site.css'), array(), '3.3.0');
    wp_enqueue_style('fetchfolio-theme', get_stylesheet_uri(), array('fetchfolio-site'), '3.3.0');
    wp_enqueue_script('fetchfolio-preferences', get_theme_file_uri('/assets/js/preferences.js'), array(), '3.3.0', true);
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

function fetchfolio_homepage_settings_assets($hook) {
    if ($hook !== 'appearance_page_fetchfolio-homepage-settings') return;
    wp_enqueue_media();
    wp_enqueue_script(
        'fetchfolio-homepage-settings',
        get_theme_file_uri('/assets/js/homepage-settings.js'),
        array('jquery', 'media-editor'),
        '3.3.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'fetchfolio_homepage_settings_assets');

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
    $social_fields = array(
        'github' => __('GitHub URL', 'fetchfolio'),
        'youtube' => __('YouTube URL', 'fetchfolio'),
        'makerworld' => __('MakerWorld URL', 'fetchfolio'),
        'linkedin' => __('LinkedIn URL', 'fetchfolio'),
        'discord' => __('Discord URL', 'fetchfolio'),
    );
    ?>
    <div class="wrap">
      <h1><?php esc_html_e('Homepage Settings', 'fetchfolio'); ?></h1>
      <p><?php esc_html_e('Manage the terminal identity, profile links, and homepage sections from this screen.', 'fetchfolio'); ?></p>
      <?php if (isset($_GET['updated'])) : ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e('Homepage settings saved.', 'fetchfolio'); ?></p></div><?php endif; ?>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="fetchfolio_save_homepage_settings">
        <?php wp_nonce_field('fetchfolio_homepage_settings'); ?>
        <h2><?php esc_html_e('Terminal identity', 'fetchfolio'); ?></h2>
        <table class="form-table" role="presentation"><tbody>
          <tr><th scope="row"><label for="fetchfolio_user"><?php esc_html_e('Terminal user', 'fetchfolio'); ?></label></th><td><input class="regular-text" id="fetchfolio_user" name="fetchfolio_user" value="<?php echo esc_attr(get_theme_mod('fetchfolio_user', 'fetch')); ?>"></td></tr>
          <tr><th scope="row"><label for="fetchfolio_host"><?php esc_html_e('Terminal host', 'fetchfolio'); ?></label></th><td><input class="regular-text" id="fetchfolio_host" name="fetchfolio_host" value="<?php echo esc_attr(get_theme_mod('fetchfolio_host', 'folio')); ?>"></td></tr>
          <tr><th scope="row"><label for="fetchfolio_accent"><?php esc_html_e('Accent color', 'fetchfolio'); ?></label></th><td><input type="color" id="fetchfolio_accent" name="fetchfolio_accent" value="<?php echo esc_attr(get_theme_mod('fetchfolio_accent', '#38bdf8')); ?>"></td></tr>
          <tr><th scope="row"><label for="fetchfolio_ascii"><?php esc_html_e('ASCII art', 'fetchfolio'); ?></label></th><td><textarea class="large-text code" rows="12" id="fetchfolio_ascii" name="fetchfolio_ascii"><?php echo esc_textarea($ascii_default); ?></textarea></td></tr>
        </tbody></table>

        <h2><?php esc_html_e('Social profiles', 'fetchfolio'); ?></h2>
        <p><?php esc_html_e('Set the URL for the built-in profile icons. Leave a URL empty to hide that icon.', 'fetchfolio'); ?></p>
        <table class="form-table" role="presentation"><tbody>
          <?php foreach ($social_fields as $key => $label) : $setting = 'fetchfolio_' . $key; ?>
            <tr><th scope="row"><label for="<?php echo esc_attr($setting); ?>"><?php echo esc_html($label); ?></label></th><td><input class="regular-text" type="url" id="<?php echo esc_attr($setting); ?>" name="<?php echo esc_attr($setting); ?>" value="<?php echo esc_attr(get_theme_mod($setting, '')); ?>"></td></tr>
          <?php endforeach; ?>
        </tbody></table>

        <h2><?php esc_html_e('Custom links', 'fetchfolio'); ?></h2>
        <p><?php esc_html_e('Add up to eight other links. Each needs a name and URL; an icon image is optional.', 'fetchfolio'); ?></p>
        <table class="form-table" role="presentation"><tbody>
          <?php for ($slot = 1; $slot <= 8; $slot++) : $label_key = 'fetchfolio_link_' . $slot . '_label'; $url_key = 'fetchfolio_link_' . $slot . '_url'; $icon_key = 'fetchfolio_link_' . $slot . '_icon'; $icon_url = get_theme_mod($icon_key, ''); ?>
            <tr>
              <th scope="row"><label for="<?php echo esc_attr($label_key); ?>"><?php printf(esc_html__('Link %d', 'fetchfolio'), $slot); ?></label></th>
              <td>
                <p><label for="<?php echo esc_attr($label_key); ?>"><?php esc_html_e('Name', 'fetchfolio'); ?></label><br><input class="regular-text" id="<?php echo esc_attr($label_key); ?>" name="<?php echo esc_attr($label_key); ?>" value="<?php echo esc_attr(get_theme_mod($label_key, '')); ?>"></p>
                <p><label for="<?php echo esc_attr($url_key); ?>"><?php esc_html_e('URL', 'fetchfolio'); ?></label><br><input class="regular-text" type="url" id="<?php echo esc_attr($url_key); ?>" name="<?php echo esc_attr($url_key); ?>" value="<?php echo esc_attr(get_theme_mod($url_key, '')); ?>"></p>
                <p><label for="<?php echo esc_attr($icon_key); ?>"><?php esc_html_e('Icon image URL', 'fetchfolio'); ?></label><br>
                  <input class="regular-text fetchfolio-icon-url" type="url" id="<?php echo esc_attr($icon_key); ?>" name="<?php echo esc_attr($icon_key); ?>" value="<?php echo esc_attr($icon_url); ?>">
                  <button type="button" class="button fetchfolio-select-icon"><?php esc_html_e('Choose image', 'fetchfolio'); ?></button>
                  <?php if ($icon_url) : ?><img src="<?php echo esc_url($icon_url); ?>" alt="" style="width:24px;height:24px;object-fit:contain;vertical-align:middle;margin-left:8px;"><?php endif; ?>
                </p>
              </td>
            </tr>
          <?php endfor; ?>
        </tbody></table>

        <h2><?php esc_html_e('Homepage sections', 'fetchfolio'); ?></h2>
        <p><?php esc_html_e('Edit the short text shown beside each section and choose its position.', 'fetchfolio'); ?></p>
        <table class="form-table" role="presentation"><tbody>
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
    set_theme_mod('fetchfolio_accent', sanitize_hex_color($input['fetchfolio_accent'] ?? '') ?: '#38bdf8');
    set_theme_mod('fetchfolio_ascii', sanitize_textarea_field($input['fetchfolio_ascii'] ?? ''));
    foreach (array('github', 'youtube', 'makerworld', 'linkedin', 'discord') as $key) {
        set_theme_mod('fetchfolio_' . $key, esc_url_raw($input['fetchfolio_' . $key] ?? ''));
    }
    for ($slot = 1; $slot <= 8; $slot++) {
        set_theme_mod('fetchfolio_link_' . $slot . '_label', sanitize_text_field($input['fetchfolio_link_' . $slot . '_label'] ?? ''));
        set_theme_mod('fetchfolio_link_' . $slot . '_url', esc_url_raw($input['fetchfolio_link_' . $slot . '_url'] ?? ''));
        set_theme_mod('fetchfolio_link_' . $slot . '_icon', esc_url_raw($input['fetchfolio_link_' . $slot . '_icon'] ?? ''));
    }
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
            $menu_titles = array('about' => __('About', 'fetchfolio'), 'cv' => __('CV', 'fetchfolio'));
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
        'github' => array('GitHub', 'github.svg'),
        'youtube' => array('YouTube', 'youtube.svg'),
        'makerworld' => array('MakerWorld', 'makerworld.svg'),
        'linkedin' => array('LinkedIn', 'linkedin.svg'),
        'discord' => array('Discord', 'discord.svg'),
    );
    foreach ($links as $key => $data) {
        $url = trim((string) get_theme_mod('fetchfolio_' . $key, ''));
        if (!$url) continue;
        if (!preg_match('~^https?://~i', $url)) $url = 'https://' . $url;
        printf('<a class="social-box %1$s" href="%2$s" target="_blank" rel="noopener noreferrer" title="%3$s" aria-label="%3$s"><img src="%4$s" alt=""></a>', esc_attr($key === 'discord' ? 'purple' : $key), esc_url($url), esc_attr($data[0]), esc_url(get_theme_file_uri('/assets/icons/' . $data[1])));
    }

    for ($slot = 1; $slot <= 8; $slot++) {
        $label = trim((string) get_theme_mod('fetchfolio_link_' . $slot . '_label', ''));
        $url = trim((string) get_theme_mod('fetchfolio_link_' . $slot . '_url', ''));
        if ($label === '' || $url === '') continue;
        if (!preg_match('~^https?://~i', $url)) $url = 'https://' . $url;
        $icon = trim((string) get_theme_mod('fetchfolio_link_' . $slot . '_icon', ''));
        $class = sanitize_html_class(sanitize_title($label));
        printf('<a class="social-box custom-link %1$s" href="%2$s" target="_blank" rel="noopener noreferrer" title="%3$s" aria-label="%3$s">', esc_attr($class), esc_url($url), esc_attr($label));
        if ($icon) {
            printf('<img src="%s" alt="">', esc_url($icon));
        } else {
            printf('<span aria-hidden="true">%s</span>', esc_html(wp_html_excerpt($label, 1, '')));
        }
        echo '</a>';
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
