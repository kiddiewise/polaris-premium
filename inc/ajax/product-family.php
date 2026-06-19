<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_polaris_product_family', 'polaris_product_family');
add_action('wp_ajax_nopriv_polaris_product_family', 'polaris_product_family');

function polaris_product_family() {
    $nonce = sanitize_text_field(polaris_get_request_string($_POST, 'nonce'));
    if (!$nonce || !wp_verify_nonce($nonce, 'polaris_nonce')) {
        wp_send_json_error(['message' => 'Geçersiz nonce'], 403);
    }

    wp_send_json_success([
        'items' => [],
    ]);
}
