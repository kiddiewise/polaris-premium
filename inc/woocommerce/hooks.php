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

/**
 * Kayit formu gizlilik metnini Turkce/tek satir olacak sekilde ozellestirir.
 */
add_filter('woocommerce_registration_privacy_policy_text', function ($text) {
    $privacy_url = function_exists('get_privacy_policy_url') ? get_privacy_policy_url() : '';

    if (!empty($privacy_url)) {
        return sprintf(
            'Kişisel verileriniz, bu sitedeki deneyiminizi desteklemek, hesabınıza erişimi yönetmek ve %s açıklanan diğer amaçlar için kullanılacaktır.',
            '<a href="' . esc_url($privacy_url) . '" class="woocommerce-privacy-policy-link" target="_blank" rel="noopener">gizlilik ilkesinde</a>'
        );
    }

    return 'Kişisel verileriniz, bu sitedeki deneyiminizi desteklemek, hesabınıza erişimi yönetmek ve gizlilik ilkesinde açıklanan diğer amaçlar için kullanılacaktır.';
}, 20);

/**
 * Cart sayfasını tema içindeki page-cart.php ile render ederek header/footer'ı garanti eder.
 */
add_filter('template_include', function ($template) {
    if (!function_exists('is_cart') || !is_cart()) {
        return $template;
    }

    $cart_template = locate_template('page-cart.php');
    if (!empty($cart_template)) {
        return $cart_template;
    }

    return $template;
}, 30);

/**
 * 1000 TL ve üzeri sepette tüm kargo ücretlerini ücretsiz yapar.
 */
add_filter('woocommerce_package_rates', function ($rates, $package) {
    if (!function_exists('WC') || !WC()->cart) {
        return $rates;
    }

    $threshold = 1000.0;
    $subtotal  = (float) WC()->cart->get_subtotal();

    if ($subtotal < $threshold) {
        return $rates;
    }

    foreach ($rates as $rate_id => $rate) {
        if (!is_object($rate)) {
            continue;
        }

        $rates[$rate_id]->cost = 0;

        if (isset($rates[$rate_id]->taxes) && is_array($rates[$rate_id]->taxes)) {
            foreach ($rates[$rate_id]->taxes as $tax_id => $amount) {
                $rates[$rate_id]->taxes[$tax_id] = 0;
            }
        }
    }

    return $rates;
}, 99, 2);
