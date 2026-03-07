<?php
if (!defined('ABSPATH')) {
    exit;
}

function polaris_disable_wp_emojis()
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    add_filter('emoji_svg_url', '__return_false');
}
add_action('init', 'polaris_disable_wp_emojis');

function polaris_disable_wp_embeds()
{
    wp_deregister_script('wp-embed');
}
add_action('wp_enqueue_scripts', 'polaris_disable_wp_embeds', 99);

function polaris_dequeue_dashicons_for_guests()
{
    if (is_user_logged_in() || is_admin()) {
        return;
    }

    wp_deregister_style('dashicons');
}
add_action('wp_enqueue_scripts', 'polaris_dequeue_dashicons_for_guests', 99);

function polaris_optimize_wc_cart_fragments()
{
    if (is_admin() || is_customize_preview()) {
        return;
    }

    wp_dequeue_script('wc-cart-fragments');
}
add_action('wp_enqueue_scripts', 'polaris_optimize_wc_cart_fragments', 100);

function polaris_disable_wc_cart_fragments_data($script_data, $handle)
{
    if (is_customize_preview()) {
        return $script_data;
    }

    if ('wc-cart-fragments' === $handle) {
        return null;
    }

    return $script_data;
}
add_filter('woocommerce_get_script_data', 'polaris_disable_wc_cart_fragments_data', 10, 2);

function polaris_add_security_headers($headers)
{
    if (is_admin()) {
        return $headers;
    }

    $headers['X-Content-Type-Options'] = 'nosniff';
    $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
    $headers['Permissions-Policy'] = 'camera=(), microphone=(), geolocation=()';

    if (empty($headers['X-Frame-Options'])) {
        $headers['X-Frame-Options'] = 'SAMEORIGIN';
    }

    return $headers;
}
add_filter('wp_headers', 'polaris_add_security_headers', 10, 1);
