<?php
if (!defined('ABSPATH')) {
    exit;
}

add_filter('wc_add_to_cart_message_html', function ($message, $products) {
    return '<div class="woocommerce-message">Sepete eklendi.</div>';
}, 10, 2);

add_action('init', function () {
    remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
});

/**
 * Giris/kayit sonrasi kullaniciyi Woo hesap paneline yonlendir.
 */
function polaris_account_panel_url()
{
    if (function_exists('wc_get_page_permalink')) {
        $url = wc_get_page_permalink('myaccount');
        if (!empty($url)) {
            return $url;
        }
    }

    return home_url('/');
}

add_filter('woocommerce_login_redirect', function ($redirect, $user) {
    return polaris_account_panel_url();
}, 20, 2);

add_filter('woocommerce_registration_redirect', function ($redirect) {
    return polaris_account_panel_url();
}, 20);
