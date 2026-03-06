<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Force custom product detail template.
 *
 * This bypasses legacy/special single page mappings and ensures
 * Woo product URLs always render our theme's custom design.
 */
function polaris_force_single_product_template($template) {
    if (!is_singular('product')) {
        return $template;
    }

    $custom = get_template_directory() . '/woocommerce/single-product.php';
    if (file_exists($custom)) {
        return $custom;
    }

    return $template;
}
add_filter('template_include', 'polaris_force_single_product_template', 999);

/**
 * Backup routing for Woo template loader path.
 */
function polaris_force_wc_single_template($template, $template_name, $template_path) {
    if ($template_name !== 'single-product.php') {
        return $template;
    }

    $custom = get_template_directory() . '/woocommerce/single-product.php';
    if (file_exists($custom)) {
        return $custom;
    }

    return $template;
}
add_filter('woocommerce_locate_template', 'polaris_force_wc_single_template', 999, 3);
