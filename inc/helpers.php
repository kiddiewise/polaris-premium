<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * page-login.php template'ini kullanan sayfayi bulur.
 */
function polaris_get_login_page_url()
{
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
