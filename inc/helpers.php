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

    $login_page_url = polaris_get_login_page_url();
    if (!empty($login_page_url)) {
        return $login_page_url;
    }

    return $my_account_url;
}
