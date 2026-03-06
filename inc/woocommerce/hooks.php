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