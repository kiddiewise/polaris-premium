<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_polaris_set_cart_qty', 'polaris_set_cart_qty');
add_action('wp_ajax_nopriv_polaris_set_cart_qty', 'polaris_set_cart_qty');

function polaris_set_cart_qty()
{
    $nonce = sanitize_text_field(polaris_get_request_string($_POST, 'nonce'));
    if (!$nonce || !wp_verify_nonce($nonce, 'polaris_nonce')) {
        wp_send_json_error(['message' => 'Geçersiz nonce'], 403);
    }

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(['message' => 'Sepet bulunamadı'], 400);
    }

    $key = sanitize_text_field(polaris_get_request_string($_POST, 'cart_key'));
    $qty = absint(polaris_get_request_string($_POST, 'qty'));

    if ($key === '') {
        wp_send_json_error(['message' => 'Sepet anahtarı eksik'], 400);
    }

    $cart_contents = WC()->cart->get_cart();
    if (!isset($cart_contents[$key])) {
        wp_send_json_error(['message' => 'Sepet satırı bulunamadı'], 404);
    }

    $cart_item = $cart_contents[$key];
    $product   = isset($cart_item['data']) && $cart_item['data'] instanceof WC_Product
        ? $cart_item['data']
        : null;

    if (!$product) {
        wp_send_json_error(['message' => 'Ürün bulunamadı'], 404);
    }

    $max_quantity = $product->get_max_purchase_quantity();
    if ($qty > 0 && $max_quantity > 0 && $qty > $max_quantity) {
        wp_send_json_error(['message' => 'İzin verilen maksimum adet aşıldı'], 400);
    }

    if ($qty > 1 && $product->is_sold_individually()) {
        wp_send_json_error(['message' => 'Bu ürün tek adet satın alınabilir'], 400);
    }

    if ($qty > 0 && !$product->has_enough_stock($qty)) {
        wp_send_json_error(['message' => 'Yeterli stok bulunmuyor'], 400);
    }

    $is_valid = apply_filters('woocommerce_update_cart_validation', true, $key, $cart_item, $qty);
    if (!$is_valid) {
        wp_send_json_error(['message' => 'Sepet adedi doğrulanamadı'], 400);
    }

    if (!WC()->cart->set_quantity($key, $qty, true)) {
        wp_send_json_error(['message' => 'Sepet güncellenemedi'], 400);
    }

    $payload = function_exists('polaris_get_minicart_payload')
        ? polaris_get_minicart_payload()
        : ['count' => (int) WC()->cart->get_cart_contents_count()];

    wp_send_json_success($payload);
}
