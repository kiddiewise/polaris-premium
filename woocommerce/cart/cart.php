<?php
if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_cart'); ?>

<section class="container polaris-cart-page">
  <header class="polaris-page-head polaris-cart-head">
    <div>
      <h1><?php esc_html_e('Sepet', 'polaris'); ?></h1>
      <p><?php esc_html_e('Urunlerini kontrol et, adetleri guncelle ve guvenle odemeye gec.', 'polaris'); ?></p>
    </div>
    <a class="btn btn-ghost" href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/')); ?>">
      <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
      <?php esc_html_e('Alisverise devam et', 'polaris'); ?>
    </a>
  </header>

  <form class="woocommerce-cart-form polaris-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
    <?php do_action('woocommerce_before_cart_table'); ?>

    <div class="polaris-surface polaris-cart-items">
      <?php do_action('woocommerce_before_cart_contents'); ?>

      <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) : ?>
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
              <?php echo wp_kses_post(apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key)); ?>
            </div>

            <div class="polaris-cart-item__meta">
              <?php echo wc_get_formatted_cart_item_data($cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
          </div>

          <div class="polaris-cart-item__controls">
            <div class="polaris-cart-item__subtotal">
              <?php echo wp_kses_post(apply_filters('woocommerce_cart_item_subtotal', $line_subtotal, $cart_item, $cart_item_key)); ?>
            </div>

            <?php if ($_product->is_sold_individually()) : ?>
              <div class="polaris-qty polaris-qty--readonly">
                <span>1</span>
                <input type="hidden" name="cart[<?php echo esc_attr($cart_item_key); ?>][qty]" value="1" />
              </div>
            <?php else : ?>
              <div class="polaris-qty" data-cart-qty>
                <button type="button" data-cart-qty-minus aria-label="<?php esc_attr_e('Azalt', 'polaris'); ?>">-</button>
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
                <button type="button" data-cart-qty-plus aria-label="<?php esc_attr_e('Arttir', 'polaris'); ?>">+</button>
              </div>
            <?php endif; ?>

            <?php
            echo apply_filters(
                'woocommerce_cart_item_remove_link',
                sprintf(
                    '<a href="%s" class="polaris-cart-item__remove remove" aria-label="%s" data-product_id="%s" data-product_sku="%s"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></a>',
                    esc_url(wc_get_cart_remove_url($cart_item_key)),
                    esc_attr__('Bu urunu sil', 'polaris'),
                    esc_attr($product_id),
                    esc_attr($_product->get_sku())
                ),
                $cart_item_key
            );
            ?>
          </div>
        </article>
      <?php endforeach; ?>

      <?php do_action('woocommerce_cart_contents'); ?>
      <?php do_action('woocommerce_after_cart_contents'); ?>
    </div>

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

      <button type="submit" class="button btn btn-primary" name="update_cart" value="<?php esc_attr_e('Sepeti guncelle', 'polaris'); ?>">
        <?php esc_html_e('Sepeti guncelle', 'polaris'); ?>
      </button>

      <?php do_action('woocommerce_cart_actions'); ?>
      <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
    </div>

    <?php do_action('woocommerce_after_cart_table'); ?>
    <?php do_action('woocommerce_before_cart_collaterals'); ?>
    <div class="cart-collaterals polaris-cart-collaterals">
      <?php do_action('woocommerce_cart_collaterals'); ?>
    </div>
  </form>
</section>

<?php do_action('woocommerce_after_cart'); ?>
