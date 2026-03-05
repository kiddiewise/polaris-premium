<?php
if (!defined('ABSPATH')) exit;

// Woo'nun "Sepeti görüntüle" linkli mesajını sadeleştir
add_filter('wc_add_to_cart_message_html', function($message, $products){
  return '<div class="woocommerce-message">Sepete eklendi.</div>';
}, 10, 2);