<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_polaris_get_minicart', 'polaris_get_minicart');
add_action('wp_ajax_nopriv_polaris_get_minicart', 'polaris_get_minicart');

function polaris_get_minicart() {
    $nonce = sanitize_text_field(polaris_get_request_string($_POST, 'nonce'));
    if (!$nonce || !wp_verify_nonce($nonce, 'polaris_nonce')) {
        wp_send_json_error(['message' => 'Geçersiz nonce'], 403);
    }

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(['message' => 'WooCommerce sepeti bulunamadı'], 400);
    }

    wp_send_json_success(polaris_get_minicart_payload());
}

function polaris_get_minicart_payload()
{
    if (!function_exists('WC') || !WC()->cart) {
        return [];
    }

    $cart = WC()->cart;

    $items = [];
    ob_start();

    if ($cart->is_empty()) {
        echo '<div class="search-empty">' . esc_html__('Sepetiniz şu an boş.', 'polaris') . '</div>';
    } else {
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product = isset($cart_item['data']) ? $cart_item['data'] : null;
            if (!$product || !is_object($product)) {
                continue;
            }

            $product_id = $product->get_id();
            $qty        = (int) $cart_item['quantity'];
            $image      = $product->get_image('woocommerce_thumbnail');
            $title      = $product->get_name();
            $price      = $product->get_price_html();

            if (!isset($items[$product_id])) {
                $items[$product_id] = [
                    'product_id' => (int) $product_id,
                    'qty'        => 0,
                    'cart_key'   => (string) $cart_item_key,
                ];
            }
            $items[$product_id]['qty'] += $qty;

            echo '<div class="polaris-minicart-item" data-cart-key="' . esc_attr($cart_item_key) . '">';
            echo '  <a class="polaris-minicart-thumb" href="' . esc_url($product->get_permalink()) . '">' . wp_kses_post($image) . '</a>';
            echo '  <div class="polaris-minicart-content">';
            echo '    <div class="polaris-minicart-title">' . esc_html($title) . '</div>';
            echo '    <div class="polaris-minicart-meta">';
            echo '      <div class="polaris-minicart-price">' . wp_kses_post($price) . '</div>';
            echo '      <div class="polaris-minicart-actions">';
            echo '        <div class="qty-stepper">';
            echo '          <button type="button" data-qty-minus aria-label="' . esc_attr__('Azalt', 'polaris') . '">-</button>';
            echo '          <span data-qty-val>' . esc_html((string) $qty) . '</span>';
            echo '          <button type="button" data-qty-plus aria-label="' . esc_attr__('Arttır', 'polaris') . '">+</button>';
            echo '        </div>';
            echo '        <button type="button" class="polaris-minicart-remove" data-qty-remove aria-label="' . esc_attr__('Ürünü kaldır', 'polaris') . '">';
            echo '          <i class="fa-regular fa-trash-can" aria-hidden="true"></i>';
            echo '        </button>';
            echo '      </div>';
            echo '    </div>';
            echo '  </div>';
            echo '</div>';
        }
    }

    $html = ob_get_clean();

    $payload          = function_exists('polaris_get_cart_bootstrap_data') ? polaris_get_cart_bootstrap_data() : [];
    $payload['html']  = $html;
    $payload['items'] = array_values($items);

    return $payload;
}
