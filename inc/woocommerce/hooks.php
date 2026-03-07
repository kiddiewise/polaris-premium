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
 * Shop ve urun kategori arsivlerinde tema icindeki custom archive template'ini zorunlu kullanir.
 * Bu sayede header/footer/bottom nav her kosulda render edilir.
 */
add_filter('template_include', function ($template) {
    if (is_admin() || wp_doing_ajax()) {
        return $template;
    }

    if (!function_exists('is_shop')) {
        return $template;
    }

    $shop_template = locate_template('woocommerce/archive-product.php');
    if (empty($shop_template)) {
        return $template;
    }

    $shop_page_id = function_exists('wc_get_page_id') ? (int) wc_get_page_id('shop') : 0;
    $is_shop_page = $shop_page_id > 0 && is_page($shop_page_id);
    $is_product_archive = is_post_type_archive('product');
    $is_product_tax = function_exists('is_product_taxonomy') && is_product_taxonomy();

    if (is_shop() || $is_shop_page || $is_product_archive || $is_product_tax) {
        return $shop_template;
    }

    return $template;
}, 90);

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

/**
 * Hesabim paneli: menu strukuru ve sifre degistirme endpoint'i.
 */
function polaris_register_change_password_endpoint()
{
    add_rewrite_endpoint('change-password', EP_PAGES);
}

add_action('init', 'polaris_register_change_password_endpoint');

add_action('admin_init', function () {
    $rewrite_version = '1';
    $stored_version  = (string) get_option('polaris_account_rewrite_version', '');

    if ($stored_version === $rewrite_version) {
        return;
    }

    flush_rewrite_rules();
    update_option('polaris_account_rewrite_version', $rewrite_version, false);
});

add_filter('woocommerce_account_menu_items', function ($items) {
    return [
        'dashboard'       => __('Dashboard', 'polaris'),
        'orders'          => __('Siparisler', 'polaris'),
        'edit-address'    => __('Adresler', 'polaris'),
        'edit-account'    => __('Hesap Detaylari', 'polaris'),
        'change-password' => __('Sifre Degistir', 'polaris'),
        'customer-logout' => __('Cikis', 'polaris'),
    ];
}, 30);

add_action('woocommerce_account_change-password_endpoint', function () {
    wc_get_template('myaccount/form-change-password.php');
});

add_action('template_redirect', function () {
    if (
        !is_user_logged_in() ||
        !function_exists('is_account_page') ||
        !is_account_page() ||
        !isset($_POST['polaris_change_password_submit'])
    ) {
        return;
    }

    $endpoint = function_exists('WC') && WC() && WC()->query
        ? WC()->query->get_current_endpoint()
        : '';

    if ('change-password' !== $endpoint) {
        return;
    }

    $nonce = isset($_POST['polaris_change_password_nonce'])
        ? sanitize_text_field(wp_unslash($_POST['polaris_change_password_nonce']))
        : '';

    if (empty($nonce) || !wp_verify_nonce($nonce, 'polaris_change_password_action')) {
        wc_add_notice(__('Guvenlik dogrulamasi basarisiz. Lutfen tekrar deneyin.', 'polaris'), 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('change-password'));
        exit;
    }

    $current_password = isset($_POST['current_password']) ? (string) wp_unslash($_POST['current_password']) : '';
    $new_password     = isset($_POST['new_password']) ? (string) wp_unslash($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? (string) wp_unslash($_POST['confirm_password']) : '';

    $current_password = trim($current_password);
    $new_password     = trim($new_password);
    $confirm_password = trim($confirm_password);

    $user_id = get_current_user_id();
    $user    = $user_id ? get_userdata($user_id) : null;

    if (!$user) {
        wc_add_notice(__('Kullanici bilgisi bulunamadi.', 'polaris'), 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('change-password'));
        exit;
    }

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        wc_add_notice(__('Tum sifre alanlarini doldurun.', 'polaris'), 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('change-password'));
        exit;
    }

    if (!wp_check_password($current_password, $user->data->user_pass, $user_id)) {
        wc_add_notice(__('Mevcut sifreniz hatali.', 'polaris'), 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('change-password'));
        exit;
    }

    if (strlen($new_password) < 8) {
        wc_add_notice(__('Yeni sifre en az 8 karakter olmali.', 'polaris'), 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('change-password'));
        exit;
    }

    if ($new_password !== $confirm_password) {
        wc_add_notice(__('Yeni sifre alanlari birbiriyle uyusmuyor.', 'polaris'), 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('change-password'));
        exit;
    }

    if ($current_password === $new_password) {
        wc_add_notice(__('Yeni sifreniz mevcut sifrenizden farkli olmali.', 'polaris'), 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('change-password'));
        exit;
    }

    wp_set_password($new_password, $user_id);
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);
    do_action('wp_login', $user->user_login, $user);

    wc_add_notice(__('Sifreniz basariyla guncellendi.', 'polaris'), 'success');
    wp_safe_redirect(wc_get_account_endpoint_url('change-password'));
    exit;
}, 25);
