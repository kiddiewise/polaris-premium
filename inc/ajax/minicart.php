<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_polaris_get_minicart', 'polaris_get_minicart');
add_action('wp_ajax_nopriv_polaris_get_minicart', 'polaris_get_minicart');

function polaris_get_minicart() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'polaris_nonce')) {
        wp_send_json_error(['message' => 'Geçersiz nonce'], 403);
    }

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(['message' => 'WooCommerce sepeti bulunamadı'], 400);
    }

    $cart = WC()->cart;

    $items = [];
    ob_start();

    if ($cart->is_empty()) {
        echo '<div class="search-empty">Sepetiniz şu an boş.</div>';
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
            echo '  <a class="polaris-minicart-thumb" href="' . esc_url(get_permalink($product_id)) . '">' . $image . '</a>';
            echo '  <div>';
            echo '    <div class="polaris-minicart-title">' . esc_html($title) . '</div>';
            echo '    <div class="polaris-minicart-meta">';
            echo '      <div>' . wp_kses_post($price) . '</div>';
            echo '      <div class="qty-stepper">';
            echo '        <button type="button" data-qty-minus aria-label="Azalt">-</button>';
            echo '        <span data-qty-val>' . esc_html((string) $qty) . '</span>';
            echo '        <button type="button" data-qty-plus aria-label="Arttır">+</button>';
            echo '      </div>';
            echo '    </div>';
            echo '  </div>';
            echo '</div>';
        }

        echo '<div class="polaris-minicart-total">';
        echo esc_html__('Toplam:', 'polaris') . ' <strong>' . wp_kses_post($cart->get_cart_total()) . '</strong>';
        echo '</div>';
    }

    $html = ob_get_clean();

    $count     = (int) $cart->get_cart_contents_count();
    $threshold = 1000.0;
    $subtotal  = (float) $cart->get_cart_contents_total();
    $remaining = max(0.0, $threshold - $subtotal);
    $percent   = $threshold > 0 ? min(100, (int) round(($subtotal / $threshold) * 100)) : 0;

    wp_send_json_success([
        'html'     => $html,
        'count'    => $count,
        'items'    => array_values($items),
        'freeship' => [
            'threshold' => $threshold,
            'subtotal'  => $subtotal,
            'remaining' => $remaining,
            'percent'   => $percent,
        ],
    ]);
}
