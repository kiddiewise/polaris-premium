<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('polaris_get_whatsapp_number')) {
    function polaris_get_whatsapp_number()
    {
        $default_number = '905462629002';
        $raw_number     = get_theme_mod('polaris_whatsapp_number', $default_number);
        $raw_number     = apply_filters('polaris_whatsapp_number', $raw_number);
        $number         = preg_replace('/\D+/', '', (string) $raw_number);

        return $number !== '' ? $number : $default_number;
    }
}

if (!function_exists('polaris_get_whatsapp_url')) {
    function polaris_get_whatsapp_url($message = '')
    {
        $number = polaris_get_whatsapp_number();
        $url    = 'https://wa.me/' . $number;

        $message = wp_strip_all_tags((string) $message);
        if ($message !== '') {
            $url .= '?text=' . rawurlencode($message);
        }

        return esc_url($url);
    }
}

if (!function_exists('polaris_get_social_links')) {
    function polaris_get_social_links()
    {
        $links = [
            'instagram' => get_theme_mod('polaris_instagram_url', 'https://instagram.com/'),
            'youtube'   => get_theme_mod('polaris_youtube_url', 'https://youtube.com/'),
        ];

        $links = apply_filters('polaris_social_links', $links);

        if (!is_array($links)) {
            return [
                'instagram' => 'https://instagram.com/',
                'youtube'   => 'https://youtube.com/',
            ];
        }

        $instagram = isset($links['instagram']) ? esc_url((string) $links['instagram']) : 'https://instagram.com/';
        $youtube   = isset($links['youtube']) ? esc_url((string) $links['youtube']) : 'https://youtube.com/';

        return [
            'instagram' => $instagram !== '' ? $instagram : 'https://instagram.com/',
            'youtube'   => $youtube !== '' ? $youtube : 'https://youtube.com/',
        ];
    }
}

/**
 * page-login.php template'ini kullanan sayfayi bulur.
 */
function polaris_get_login_page_url()
{
    // 1) PHP page template ile atanmis sayfa.
    $pages = get_posts([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'page-login.php',
        'no_found_rows'  => true,
    ]);

    if (!empty($pages) && !is_wp_error($pages)) {
        $url = get_permalink((int) $pages[0]);
        if (!empty($url)) {
            return $url;
        }
    }

    // 2) Siklikla kullanilan giris slug'lari.
    $candidates = ['giris', 'login', 'hesaba-giris', 'hesabim-giris'];
    foreach ($candidates as $slug) {
        $page = get_page_by_path($slug, OBJECT, 'page');
        if ($page instanceof WP_Post && $page->post_status === 'publish') {
            $url = get_permalink((int) $page->ID);
            if (!empty($url)) {
                return $url;
            }
        }
    }

    return '';
}

/**
 * Header/mobil profil ikonu icin tekil giris noktasi URL'i.
 */
function polaris_get_account_entry_url()
{
    $my_account_url = function_exists('wc_get_page_permalink')
        ? wc_get_page_permalink('myaccount')
        : wp_login_url();

    if (is_user_logged_in()) {
        return $my_account_url;
    }

    $login_page_url = polaris_get_login_page_url();
    if (!empty($login_page_url)) {
        return $login_page_url;
    }

    return $my_account_url;
}

/**
 * Aktif istek login/register landing sayfasi mi?
 */
function polaris_is_login_page_request()
{
    if (!is_page()) {
        return false;
    }

    $login_page_url = polaris_get_login_page_url();
    if (empty($login_page_url)) {
        return false;
    }

    $login_page_id   = url_to_postid($login_page_url);
    $current_page_id = get_queried_object_id();

    return !empty($login_page_id) && (int) $login_page_id === (int) $current_page_id;
}

/**
 * Login/hesap sayfalarinda auth UI siniflarini body'e ekler.
 */
function polaris_add_auth_body_classes($classes)
{
    $is_login_page = polaris_is_login_page_request();
    $is_account    = function_exists('is_account_page') && is_account_page();

    if ($is_login_page) {
        // page-giris.php ile acilan giris sayfasinda premium auth secicileri aktif olur.
        $classes[] = 'page-template-page-login';
    }

    if ($is_login_page || $is_account) {
        $classes[] = 'polaris-auth-screen';
    }

    return array_values(array_unique($classes));
}
add_filter('body_class', 'polaris_add_auth_body_classes');

/**
 * Login sayfasi block template kaynakli bos gelse bile
 * dogrudan page-login.php render edilir.
 */
function polaris_mark_account_pages_uncacheable()
{
    if (!defined('DONOTCACHEPAGE')) {
        define('DONOTCACHEPAGE', true);
    }

    if (!defined('DONOTCACHEDB')) {
        define('DONOTCACHEDB', true);
    }

    if (!defined('DONOTMINIFY')) {
        define('DONOTMINIFY', true);
    }

    nocache_headers();
}

function polaris_force_login_page_template()
{
    if (is_admin() || wp_doing_ajax() || !is_page()) {
        return;
    }

    $my_account_url = function_exists('wc_get_page_permalink')
        ? wc_get_page_permalink('myaccount')
        : home_url('/');

    $login_page_url = polaris_get_login_page_url();
    if (empty($login_page_url)) {
        if (function_exists('is_account_page') && is_account_page()) {
            polaris_mark_account_pages_uncacheable();
        }
        return;
    }

    $is_login_page = polaris_is_login_page_request();

    if (function_exists('is_account_page') && is_account_page()) {
        polaris_mark_account_pages_uncacheable();
    }

    if ($is_login_page) {
        polaris_mark_account_pages_uncacheable();
    }

    // Giris yapmis kullanici /giris'e girerse panel sayfasina yonlendir.
    if ($is_login_page && is_user_logged_in()) {
        wp_safe_redirect($my_account_url);
        exit;
    }

    // Giris yapmamis kullanici /my-account* acarsa /giris'e yonlendir.
    if (!is_user_logged_in() && function_exists('is_account_page') && is_account_page() && !$is_login_page) {
        wp_safe_redirect($login_page_url);
        exit;
    }

    if (!$is_login_page) {
        return;
    }

    $template_file = get_template_directory() . '/page-login.php';
    if (!file_exists($template_file)) {
        return;
    }

    status_header(200);
    include $template_file;
    exit;
}
add_action('template_redirect', 'polaris_force_login_page_template', 0);
