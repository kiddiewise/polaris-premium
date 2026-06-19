<?php
/**
 * Content product card.
 *
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

global $product;

if (!$product || !is_a($product, 'WC_Product')) {
    return;
}

if (!$product->is_visible()) {
    return;
}

if (!function_exists('polaris_archive_get_cart_qty_map')) {
    function polaris_archive_get_cart_qty_map() {
        static $qty_map = null;

        if (is_array($qty_map)) {
            return $qty_map;
        }

        $qty_map = [];

        if (!function_exists('WC') || !WC()->cart) {
            return $qty_map;
        }

        foreach (WC()->cart->get_cart() as $cart_item) {
            $pid = isset($cart_item['variation_id']) && (int) $cart_item['variation_id'] > 0
                ? (int) $cart_item['variation_id']
                : (int) $cart_item['product_id'];

            $qty_map[$pid] = ($qty_map[$pid] ?? 0) + (int) $cart_item['quantity'];
        }

        return $qty_map;
    }
}

$product_id   = $product->get_id();
$title        = $product->get_name();
$link         = $product->get_permalink();
$image        = $product->get_image('woocommerce_thumbnail', ['loading' => 'lazy']);
$price_html   = $product->get_price_html();
$cart_qty_map = polaris_archive_get_cart_qty_map();
$initial_qty  = isset($cart_qty_map[$product_id]) ? (int) $cart_qty_map[$product_id] : 0;
$badge        = polaris_get_product_sale_badge($product);
$quick_add    = polaris_product_supports_quick_add($product);
?>

<article <?php wc_product_class('p-card', $product); ?> data-product-card data-product-id="<?php echo esc_attr($product_id); ?>">
  <a class="p-card__media" href="<?php echo esc_url($link); ?>">
    <?php if ($badge !== '') : ?>
      <span class="badge badge-sale"><?php echo esc_html($badge); ?></span>
    <?php endif; ?>
    <?php echo wp_kses_post($image); ?>
  </a>

  <div class="p-card__body">
    <a class="p-card__title" href="<?php echo esc_url($link); ?>"><?php echo esc_html($title); ?></a>
    <div class="p-card__price"><?php echo wp_kses_post($price_html); ?></div>

    <?php if ($quick_add) : ?>
      <div class="p-card__cart-actions" data-card-actions>
        <button class="p-card__cta js-add-to-cart<?php echo $initial_qty > 0 ? ' hidden' : ''; ?>" type="button" data-product-id="<?php echo esc_attr($product_id); ?>">
          <?php echo esc_html__('Sepete ekle', 'polaris'); ?>
        </button>
        <div class="p-card__qty<?php echo $initial_qty > 0 ? '' : ' hidden'; ?>" data-card-qty-wrap>
          <button class="p-card__qty-btn" type="button" data-card-minus aria-label="<?php echo esc_attr__('Azalt', 'polaris'); ?>">-</button>
          <div class="p-card__qty-center">
            <span class="p-card__qty-label"><?php echo esc_html__('Sepette', 'polaris'); ?></span>
            <span class="p-card__qty-value" data-card-qty><?php echo (int) max(1, $initial_qty); ?></span>
          </div>
          <button class="p-card__qty-btn" type="button" data-card-plus aria-label="<?php echo esc_attr__('Arttir', 'polaris'); ?>">+</button>
        </div>
      </div>
    <?php elseif ($product->is_purchasable() && $product->is_in_stock()) : ?>
      <a class="p-card__cta" href="<?php echo esc_url($link); ?>"><?php echo esc_html($product->add_to_cart_text()); ?></a>
    <?php else : ?>
      <button class="p-card__cta p-card__cta--disabled" type="button" disabled><?php echo esc_html__('Stokta yok', 'polaris'); ?></button>
    <?php endif; ?>
  </div>
</article>
