<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_polaris_product_family', 'polaris_product_family');
add_action('wp_ajax_nopriv_polaris_product_family', 'polaris_product_family');

function polaris_product_family() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'polaris_nonce')) {
        wp_send_json_error(['message' => 'Geçersiz nonce'], 403);
    }

    wp_send_json_success([
        'items' => [],
    ]);
}
