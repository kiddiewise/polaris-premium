<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_polaris_get_minicart', 'polaris_get_minicart');
add_action('wp_ajax_nopriv_polaris_get_minicart', 'polaris_get_minicart');

function polaris_get_minicart() {
  if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'polaris_nonce')) {
    wp_send_json_error(['message' => 'Invalid nonce'], 403);
  }

  if (!function_exists('WC')) {
    wp_send_json_error(['message' => 'WooCommerce missing'], 400);
  }

  ob_start();

  $cart = WC()->cart;
  if (!$cart || $cart->is_empty()) {
    echo '<div class="search-empty">Sepetiniz boş.</div>';
  } else {
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
      $product = $cart_item['data'];
      if (!$product) continue;

      $pid = $product->get_id();
      $qty = (int) $cart_item['quantity'];

      $img = $product->get_image('woocommerce_thumbnail');
      $title = $product->get_name();
      $price = $product->get_price_html();

      echo '<div class="polaris-minicart-item" data-cart-key="' . esc_attr($cart_item_key) . '">';
      echo '  <a class="polaris-minicart-thumb" href="' . esc_url(get_permalink($pid)) . '">' . $img . '</a>';
      echo '  <div>';
      echo '    <div class="polaris-minicart-title">' . esc_html($title) . '</div>';
      echo '    <div class="polaris-minicart-meta">';
      echo '      <div style="color:var(--accent);font-weight:850;">' . wp_kses_post($price) . '</div>';
      echo '      <div class="qty-stepper" data-stepper>';
      echo '        <button type="button" data-qty-minus aria-label="Azalt">-</button>';
      echo '        <span data-qty-val>' . (int) $qty . '</span>';
      echo '        <button type="button" data-qty-plus aria-label="Arttır">+</button>';
      echo '      </div>';
      echo '    </div>';
      echo '  </div>';
      echo '</div>';
    }

    echo '<div style="padding:10px 6px;color:rgba(255,255,255,.72);font-size:13px;">';
    echo 'Toplam: <span style="color:rgba(255,255,255,.92);font-weight:850;">' . wp_kses_post($cart->get_cart_total()) . '</span>';
    echo '</div>';
  }

  $html = ob_get_clean();
  $count = $cart ? (int) $cart->get_cart_contents_count() : 0;

    $subtotal = 0;
  if ($cart) {
    // cart contents total excl shipping; float
    $subtotal = (float) $cart->get_cart_contents_total();
  }
  $threshold = 1000.0;
  $remaining = max(0.0, $threshold - $subtotal);
  $percent = $threshold > 0 ? min(100, (int) round(($subtotal / $threshold) * 100)) : 0;

  wp_send_json_success([
    'html'  => $html,
    'count' => $count,
    'freeship' => [
      'threshold' => $threshold,
      'subtotal'  => $subtotal,
      'remaining' => $remaining,
      'percent'   => $percent,
    ],
  ]);
}