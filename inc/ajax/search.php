<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_polaris_live_search', 'polaris_live_search');
add_action('wp_ajax_nopriv_polaris_live_search', 'polaris_live_search');

function polaris_live_search() {
  // Security
  if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'polaris_nonce')) {
    wp_send_json_error(['message' => 'Invalid nonce'], 403);
  }

  $q = isset($_POST['q']) ? sanitize_text_field(wp_unslash($_POST['q'])) : '';
  $q = trim($q);

  if ($q === '' || mb_strlen($q) < 2) {
    wp_send_json_success([]);
  }

  $args = [
    'post_type'      => 'product',
    'post_status'    => 'publish',
    's'              => $q,
    'posts_per_page' => 8,
    'no_found_rows'  => true,
  ];

  $query = new WP_Query($args);
  $items = [];

  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();

      $pid = get_the_ID();
      $product = function_exists('wc_get_product') ? wc_get_product($pid) : null;

      $img = '';
      if (has_post_thumbnail($pid)) {
        $img = get_the_post_thumbnail_url($pid, 'woocommerce_thumbnail');
      }
      if (!$img && function_exists('wc_placeholder_img_src')) {
        $img = wc_placeholder_img_src('woocommerce_thumbnail');
      }

      $cat_name = '';
      $terms = get_the_terms($pid, 'product_cat');
      if (!is_wp_error($terms) && !empty($terms)) {
        $cat_name = $terms[0]->name;
      }

      $items[] = [
        'title'    => get_the_title(),
        'url'      => get_permalink(),
        'image'    => $img,
        'category' => $cat_name,
        'price'    => $product ? wp_strip_all_tags($product->get_price_html()) : '',
      ];
    }
    wp_reset_postdata();
  }

  wp_send_json_success($items);
}
