<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_polaris_set_cart_qty', 'polaris_set_cart_qty');
add_action('wp_ajax_nopriv_polaris_set_cart_qty', 'polaris_set_cart_qty');

function polaris_set_cart_qty()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'polaris_nonce')) {
        wp_send_json_error(['message' => 'Geçersiz nonce'], 403);
    }

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(['message' => 'Sepet bulunamadı'], 400);
    }

    $key = isset($_POST['cart_key']) ? sanitize_text_field(wp_unslash($_POST['cart_key'])) : '';
    $qty = isset($_POST['qty']) ? absint(wp_unslash($_POST['qty'])) : 0;

    if ($key === '') {
        wp_send_json_error(['message' => 'Sepet anahtarı eksik'], 400);
    }

    $cart_contents = WC()->cart->get_cart();
    if (!isset($cart_contents[$key])) {
        wp_send_json_error(['message' => 'Sepet satırı bulunamadı'], 404);
    }

    WC()->cart->set_quantity($key, $qty, true);

    wp_send_json_success([
        'count' => (int) WC()->cart->get_cart_contents_count(),
    ]);
}
