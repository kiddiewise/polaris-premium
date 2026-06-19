<?php
/**
 * Mini-cart proxy.
 *
 * @package WooCommerce\Templates
 * @version 10.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('WC') || !WC()) {
    return;
}

$template = WC()->plugin_path() . '/templates/cart/mini-cart.php';
if (file_exists($template)) {
    include $template;
}
