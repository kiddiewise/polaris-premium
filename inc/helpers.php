<?php
if (!defined('ABSPATH')) {
    exit;
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

    $login_page_id   = url_to_postid($login_page_url);
    $current_page_id = get_queried_object_id();
    $is_login_page   = !empty($login_page_id) && (int) $login_page_id === (int) $current_page_id;

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
