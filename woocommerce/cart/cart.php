<?php
if (!defined('ABSPATH')) {
    exit;
}

$cart = (function_exists('WC') && WC()->cart) ? WC()->cart : null;
if (!$cart) {
    return;
}

$shipping_threshold  = 1000.0;
$cart_is_empty       = $cart->is_empty();
$cart_subtotal_value = (float) $cart->get_subtotal();
$cart_subtotal_html  = $cart->get_cart_subtotal();
$cart_shipping_value = (float) $cart->get_shipping_total() + (float) $cart->get_shipping_tax();
$cart_total_html     = $cart->get_total();
$cart_count          = (int) $cart->get_cart_contents_count();
$checkout_url        = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/');
$shop_url            = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');

if ($cart_subtotal_value >= $shipping_threshold) {
    $cart_shipping_html = esc_html__('Ücretsiz', 'polaris');
} elseif ($cart_shipping_value > 0) {
    $cart_shipping_html = wc_price($cart_shipping_value);
} else {
    $cart_shipping_html = esc_html__('Hesaplanacak', 'polaris');
}

do_action('woocommerce_before_cart');
?>

<section class="container polaris-cart-page">
  <div class="polaris-cart-shell">
    <header class="polaris-cart-top">
      <div class="polaris-cart-top__left">
        <span class="polaris-cart-top__kicker">
          <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
          <?php esc_html_e('Güvenli sepet', 'polaris'); ?>
        </span>
        <h1><?php esc_html_e('Sepetim', 'polaris'); ?></h1>
        <p><?php esc_html_e('Ürünlerini kontrol et, adetleri güncelle ve hızlıca ödeme adımına geç.', 'polaris'); ?></p>
      </div>
      <a class="btn btn-ghost polaris-cart-top__back" href="<?php echo esc_url($shop_url); ?>">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
        <?php esc_html_e('Alışverişe devam et', 'polaris'); ?>
      </a>
    </header>

    <form class="woocommerce-cart-form polaris-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
      <?php do_action('woocommerce_before_cart_table'); ?>

      <div class="polaris-cart-layout">
        <div class="polaris-cart-main">
          <div class="polaris-surface polaris-cart-items">
            <?php do_action('woocommerce_before_cart_contents'); ?>

            <?php if ($cart_is_empty) : ?>
              <div class="polaris-cart-empty">
                <div class="polaris-cart-empty__icon"><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i></div>
                <h2><?php esc_html_e('Sepetin şu an boş', 'polaris'); ?></h2>
                <p><?php esc_html_e('Bir ürün ekleyerek siparişini başlatabilirsin.', 'polaris'); ?></p>
                <a class="btn btn-primary" href="<?php echo esc_url($shop_url); ?>"><?php esc_html_e('Ürünleri keşfet', 'polaris'); ?></a>
              </div>
            <?php else : ?>
              <?php foreach ($cart->get_cart() as $cart_item_key => $cart_item) : ?>
                <?php
                $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

                if (!$_product || !$_product->exists() || $cart_item['quantity'] <= 0 || !apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                    continue;
                }

                $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                $thumbnail         = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail'), $cart_item, $cart_item_key);
                $product_name      = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
                $line_subtotal     = WC()->cart->get_product_subtotal($_product, $cart_item['quantity']);
                $item_meta         = wc_get_formatted_cart_item_data($cart_item);
                ?>

                <article class="polaris-cart-item woocommerce-cart-form__cart-item cart_item">
                  <a class="polaris-cart-item__thumb" href="<?php echo esc_url($product_permalink ?: '#'); ?>">
                    <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                  </a>

                  <div class="polaris-cart-item__content">
                    <a class="polaris-cart-item__title" href="<?php echo esc_url($product_permalink ?: '#'); ?>">
                      <?php echo wp_kses_post($product_name); ?>
                    </a>

                    <div class="polaris-cart-item__unit-price">
                      <?php esc_html_e('Birim fiyat:', 'polaris'); ?>
                      <strong><?php echo wp_kses_post(apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key)); ?></strong>
                    </div>

                    <?php if (!empty($item_meta)) : ?>
                      <div class="polaris-cart-item__meta">
                        <?php echo $item_meta; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                      </div>
                    <?php endif; ?>
                  </div>

                  <div class="polaris-cart-item__controls">
                    <div class="polaris-cart-item__subtotal-label"><?php esc_html_e('Toplam', 'polaris'); ?></div>
                    <div class="polaris-cart-item__subtotal">
                      <?php echo wp_kses_post(apply_filters('woocommerce_cart_item_subtotal', $line_subtotal, $cart_item, $cart_item_key)); ?>
                    </div>

                    <?php if ($_product->is_sold_individually()) : ?>
                      <div class="polaris-qty polaris-qty--readonly">
                        <span><?php esc_html_e('Adet', 'polaris'); ?>: 1</span>
                        <input type="hidden" name="cart[<?php echo esc_attr($cart_item_key); ?>][qty]" value="1" />
                      </div>
                    <?php else : ?>
                      <div class="polaris-qty" data-cart-qty>
                        <button type="button" data-cart-qty-minus aria-label="<?php esc_attr_e('Adedi azalt', 'polaris'); ?>">-</button>
                        <?php
                        $product_quantity = woocommerce_quantity_input(
                            [
                                'input_name'   => "cart[{$cart_item_key}][qty]",
                                'input_value'  => $cart_item['quantity'],
                                'max_value'    => $_product->get_max_purchase_quantity(),
                                'min_value'    => '0',
                                'product_name' => $product_name,
                            ],
                            $_product,
                            false
                        );
                        echo $product_quantity; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        ?>
                        <button type="button" data-cart-qty-plus aria-label="<?php esc_attr_e('Adedi artır', 'polaris'); ?>">+</button>
                      </div>
                    <?php endif; ?>

                    <?php
                    echo apply_filters(
                        'woocommerce_cart_item_remove_link',
                        sprintf(
                            '<a href="%s" class="polaris-cart-item__remove remove" aria-label="%s" data-product_id="%s" data-product_sku="%s"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></a>',
                            esc_url(wc_get_cart_remove_url($cart_item_key)),
                            esc_attr__('Ürünü sil', 'polaris'),
                            esc_attr($product_id),
                            esc_attr($_product->get_sku())
                        ),
                        $cart_item_key
                    );
                    ?>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>

            <?php do_action('woocommerce_cart_contents'); ?>
            <?php do_action('woocommerce_after_cart_contents'); ?>
          </div>

          <?php if (!$cart_is_empty) : ?>
            <div class="polaris-surface polaris-cart-actions">
              <?php if (wc_coupons_enabled()) : ?>
                <div class="coupon polaris-cart-coupon">
                  <label for="coupon_code"><?php esc_html_e('Kupon', 'polaris'); ?></label>
                  <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e('Kupon kodu', 'polaris'); ?>" />
                  <button type="submit" class="button btn btn-ghost" name="apply_coupon" value="<?php esc_attr_e('Kuponu uygula', 'polaris'); ?>">
                    <?php esc_html_e('Kuponu uygula', 'polaris'); ?>
                  </button>
                  <?php do_action('woocommerce_cart_coupon'); ?>
                </div>
              <?php endif; ?>

              <button type="submit" class="button btn btn-primary" name="update_cart" value="<?php esc_attr_e('Sepeti güncelle', 'polaris'); ?>">
                <?php esc_html_e('Sepeti güncelle', 'polaris'); ?>
              </button>

              <?php do_action('woocommerce_cart_actions'); ?>
              <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
            </div>
          <?php endif; ?>
        </div>

        <aside class="polaris-surface polaris-cart-summary" aria-label="<?php esc_attr_e('Sepet özeti', 'polaris'); ?>">
          <div class="polaris-cart-summary__head">
            <h2><?php esc_html_e('Sepet toplamı', 'polaris'); ?></h2>
            <span><?php echo esc_html(sprintf(__('%d ürün', 'polaris'), $cart_count)); ?></span>
          </div>

          <div class="polaris-cart-summary__rows">
            <div class="polaris-cart-summary__row">
              <span><?php esc_html_e('Ara toplam', 'polaris'); ?></span>
              <strong><?php echo wp_kses_post($cart_subtotal_html); ?></strong>
            </div>
            <div class="polaris-cart-summary__row">
              <span><?php esc_html_e('Kargo ücreti', 'polaris'); ?></span>
              <strong><?php echo wp_kses_post($cart_shipping_html); ?></strong>
            </div>
            <div class="polaris-cart-summary__row polaris-cart-summary__row--total">
              <span><?php esc_html_e('Toplam', 'polaris'); ?></span>
              <strong><?php echo wp_kses_post($cart_total_html); ?></strong>
            </div>
          </div>

          <?php if (!$cart_is_empty) : ?>
            <a class="button btn btn-primary" href="<?php echo esc_url($checkout_url); ?>">
              <?php esc_html_e('Ödemeyi tamamla', 'polaris'); ?>
            </a>
          <?php else : ?>
            <a class="button btn btn-primary" href="<?php echo esc_url($shop_url); ?>">
              <?php esc_html_e('Alışverişe başla', 'polaris'); ?>
            </a>
          <?php endif; ?>

          <p class="polaris-cart-summary__hint"><?php esc_html_e('1.000 ₺ ve üzeri siparişlerde kargo ücretsizdir.', 'polaris'); ?></p>
        </aside>
      </div>

      <?php if (!$cart_is_empty) : ?>
        <div class="polaris-cart-sticky-mobile" aria-label="<?php esc_attr_e('Mobil ödeme çubuğu', 'polaris'); ?>">
          <div class="polaris-cart-sticky-mobile__meta">
            <span><?php esc_html_e('Toplam', 'polaris'); ?></span>
            <strong><?php echo wp_kses_post($cart_total_html); ?></strong>
          </div>
          <a class="button btn btn-primary" href="<?php echo esc_url($checkout_url); ?>">
            <?php esc_html_e('Ödemeyi tamamla', 'polaris'); ?>
          </a>
        </div>
      <?php endif; ?>

      <?php do_action('woocommerce_after_cart_table'); ?>
    </form>
  </div>
</section>

<?php do_action('woocommerce_after_cart'); ?>
