<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('polaris_get_cart_bootstrap_data')) {
    function polaris_get_cart_bootstrap_data()
    {
        $empty_payload = [
            'count'    => 0,
            'items'    => [],
            'summary'  => [
                'subtotal' => function_exists('wc_price') ? wc_price(0) : '0',
                'shipping' => esc_html__('Hesaplanacak', 'polaris'),
                'total'    => function_exists('wc_price') ? wc_price(0) : '0',
            ],
            'freeship' => [
                'threshold' => 1000.0,
                'subtotal'  => 0.0,
                'remaining' => 1000.0,
                'percent'   => 0,
            ],
        ];

        if (!function_exists('WC') || !WC()->cart) {
            return $empty_payload;
        }

        $cart      = WC()->cart;
        $threshold = 1000.0;
        $subtotal  = (float) $cart->get_cart_contents_total();
        $shipping  = (float) $cart->get_shipping_total() + (float) $cart->get_shipping_tax();
        $total     = (float) $cart->get_total('edit');
        $remaining = max(0.0, $threshold - $subtotal);
        $percent   = $threshold > 0 ? min(100, (int) round(($subtotal / $threshold) * 100)) : 0;

        $items = [];
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product_id = 0;
            if (isset($cart_item['variation_id']) && (int) $cart_item['variation_id'] > 0) {
                $product_id = (int) $cart_item['variation_id'];
            } elseif (isset($cart_item['product_id'])) {
                $product_id = (int) $cart_item['product_id'];
            }

            if ($product_id <= 0) {
                continue;
            }

            if (!isset($items[$product_id])) {
                $items[$product_id] = [
                    'product_id' => $product_id,
                    'qty'        => 0,
                    'cart_key'   => (string) $cart_item_key,
                ];
            }

            $items[$product_id]['qty'] += isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 0;
        }

        if ($subtotal >= $threshold) {
            $shipping_label = esc_html__('Ücretsiz kargo', 'polaris');
        } elseif ($shipping > 0.0) {
            $shipping_label = function_exists('wc_price') ? wc_price($shipping) : (string) $shipping;
        } else {
            $shipping_label = esc_html__('Hesaplanacak', 'polaris');
        }

        return [
            'count'    => (int) $cart->get_cart_contents_count(),
            'items'    => array_values($items),
            'summary'  => [
                'subtotal' => function_exists('wc_price') ? wc_price($subtotal) : (string) $subtotal,
                'shipping' => $shipping_label,
                'total'    => function_exists('wc_price') ? wc_price($total) : (string) $total,
            ],
            'freeship' => [
                'threshold' => $threshold,
                'subtotal'  => $subtotal,
                'remaining' => $remaining,
                'percent'   => $percent,
            ],
        ];
    }
}

function polaris_assets()
{
    $css_path = get_template_directory() . '/assets/css/main.css';
    $js_path  = get_template_directory() . '/assets/js/main.js';
    $css_ver  = file_exists($css_path) ? (string) filemtime($css_path) : '1.0.0';
    $js_ver   = file_exists($js_path) ? (string) filemtime($js_path) : '1.0.0';

    wp_enqueue_style(
        'polaris-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@400;500;600&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'polaris-fa',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
        [],
        '6.5.2'
    );

    wp_enqueue_style(
        'polaris-main',
        get_template_directory_uri() . '/assets/css/main.css',
        ['polaris-fonts', 'polaris-fa'],
        $css_ver
    );

    wp_enqueue_script(
        'polaris-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        $js_ver,
        true
    );
    wp_script_add_data('polaris-main', 'defer', true);

    wp_localize_script('polaris-main', 'polaris_ajax', [
        'ajax_url'           => admin_url('admin-ajax.php'),
        'nonce'              => wp_create_nonce('polaris_nonce'),
        'wc_ajax_add_to_cart' => class_exists('WC_AJAX')
            ? WC_AJAX::get_endpoint('add_to_cart')
            : add_query_arg('wc-ajax', 'add_to_cart', home_url('/')),
        'cart_init'          => polaris_get_cart_bootstrap_data(),
    ]);
}
add_action('wp_enqueue_scripts', 'polaris_assets');

function polaris_resource_hints($urls, $relation_type)
{
    if ('preconnect' === $relation_type) {
        $urls[] = 'https://fonts.googleapis.com';
        $urls[] = [
            'href'        => 'https://fonts.gstatic.com',
            'crossorigin' => 'anonymous',
        ];
        $urls[] = [
            'href'        => 'https://cdnjs.cloudflare.com',
            'crossorigin' => 'anonymous',
        ];
    }

    if ('dns-prefetch' === $relation_type) {
        $urls[] = '//fonts.googleapis.com';
        $urls[] = '//fonts.gstatic.com';
        $urls[] = '//cdnjs.cloudflare.com';
    }

    return $urls;
}
add_filter('wp_resource_hints', 'polaris_resource_hints', 10, 2);
