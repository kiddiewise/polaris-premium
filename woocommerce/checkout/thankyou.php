<?php
if (!defined('ABSPATH')) {
    exit;
}

$order_id = isset($order) && $order instanceof WC_Order ? (int) $order->get_id() : 0;
?>

<section class="container polaris-thankyou-page">
  <div class="polaris-thankyou-shell">
    <?php if ($order_id > 0) : ?>
      <?php do_action('woocommerce_before_thankyou', $order_id); ?>

      <?php if ($order->has_status('failed')) : ?>
        <article class="polaris-thankyou-hero polaris-surface is-failed">
          <span class="polaris-thankyou-hero__icon" aria-hidden="true">
            <i class="fa-solid fa-triangle-exclamation"></i>
          </span>
          <div>
            <h1><?php esc_html_e('Odeme tamamlanamadi', 'polaris'); ?></h1>
            <p><?php esc_html_e('Siparisiniz alindi ancak odeme asamasinda bir sorun olustu. Lutfen tekrar deneyin.', 'polaris'); ?></p>
          </div>
        </article>

        <div class="polaris-thankyou-actions">
          <a class="btn btn-primary" href="<?php echo esc_url($order->get_checkout_payment_url()); ?>"><?php esc_html_e('Odemeyi tekrar dene', 'polaris'); ?></a>
          <?php if (is_user_logged_in()) : ?>
            <a class="btn btn-ghost" href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>"><?php esc_html_e('Hesabim', 'polaris'); ?></a>
          <?php endif; ?>
        </div>
      <?php else : ?>
        <article class="polaris-thankyou-hero polaris-surface">
          <span class="polaris-thankyou-hero__icon" aria-hidden="true">
            <i class="fa-solid fa-circle-check"></i>
          </span>
          <div>
            <h1><?php esc_html_e('Siparisiniz alindi', 'polaris'); ?></h1>
            <p><?php esc_html_e('Siparisiniz hazirlaniyor. Guncellemeleri kisa surede sizinle paylasacagiz.', 'polaris'); ?></p>
            <div class="polaris-thankyou-hero__chips">
              <span><i class="fa-solid fa-lock" aria-hidden="true"></i> <?php esc_html_e('Guvenli odeme', 'polaris'); ?></span>
              <span><i class="fa-solid fa-truck-fast" aria-hidden="true"></i> <?php esc_html_e('Hizli kargo', 'polaris'); ?></span>
            </div>
          </div>
        </article>

        <div class="polaris-thankyou-grid">
          <section class="polaris-surface polaris-thankyou-card">
            <h2><?php esc_html_e('Siparis detaylari', 'polaris'); ?></h2>

            <dl class="polaris-thankyou-meta">
              <div>
                <dt><?php esc_html_e('Siparis no', 'polaris'); ?></dt>
                <dd>#<?php echo esc_html($order->get_order_number()); ?></dd>
              </div>
              <div>
                <dt><?php esc_html_e('Tarih', 'polaris'); ?></dt>
                <dd><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></dd>
              </div>
              <div>
                <dt><?php esc_html_e('Toplam', 'polaris'); ?></dt>
                <dd><?php echo wp_kses_post($order->get_formatted_order_total()); ?></dd>
              </div>
              <div>
                <dt><?php esc_html_e('Odeme', 'polaris'); ?></dt>
                <dd><?php echo esc_html($order->get_payment_method_title()); ?></dd>
              </div>
            </dl>

            <?php if ($order->get_billing_phone() || $order->get_billing_email()) : ?>
              <div class="polaris-thankyou-contact">
                <?php if ($order->get_billing_phone()) : ?>
                  <p><i class="fa-solid fa-phone" aria-hidden="true"></i> <?php echo esc_html($order->get_billing_phone()); ?></p>
                <?php endif; ?>
                <?php if ($order->get_billing_email()) : ?>
                  <p><i class="fa-solid fa-envelope" aria-hidden="true"></i> <?php echo esc_html($order->get_billing_email()); ?></p>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </section>

          <section class="polaris-surface polaris-thankyou-card">
            <h2><?php esc_html_e('Siparis icerigi', 'polaris'); ?></h2>

            <div class="polaris-thankyou-items">
              <?php foreach ($order->get_items() as $item_id => $item) : ?>
                <?php
                $product   = $item->get_product();
                $quantity  = (int) $item->get_quantity();
                $subtotal  = $order->get_formatted_line_subtotal($item);
                $name      = $item->get_name();
                $permalink = $product && $product->is_visible() ? $product->get_permalink($item) : '';
                $thumb     = $product ? $product->get_image('woocommerce_thumbnail', ['loading' => 'lazy']) : wc_placeholder_img('woocommerce_thumbnail');
                ?>
                <article class="polaris-thankyou-item">
                  <div class="polaris-thankyou-item__thumb"><?php echo wp_kses_post($thumb); ?></div>
                  <div class="polaris-thankyou-item__content">
                    <?php if (!empty($permalink)) : ?>
                      <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($name); ?></a>
                    <?php else : ?>
                      <span><?php echo esc_html($name); ?></span>
                    <?php endif; ?>
                    <small><?php echo esc_html(sprintf(__('Adet: %d', 'polaris'), $quantity)); ?></small>
                  </div>
                  <strong class="polaris-thankyou-item__price"><?php echo wp_kses_post($subtotal); ?></strong>
                </article>
              <?php endforeach; ?>
            </div>

            <?php $order_totals = $order->get_order_item_totals(); ?>
            <?php if (!empty($order_totals)) : ?>
              <dl class="polaris-thankyou-totals">
                <?php foreach ($order_totals as $total) : ?>
                  <div>
                    <dt><?php echo wp_kses_post($total['label']); ?></dt>
                    <dd><?php echo wp_kses_post($total['value']); ?></dd>
                  </div>
                <?php endforeach; ?>
              </dl>
            <?php endif; ?>
          </section>
        </div>

        <div class="polaris-thankyou-actions">
          <a class="btn btn-primary" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php esc_html_e('Alisverise devam et', 'polaris'); ?></a>
          <a class="btn btn-ghost" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Ana sayfaya don', 'polaris'); ?></a>
        </div>
      <?php endif; ?>

      <?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order_id); ?>
      <?php do_action('woocommerce_thankyou', $order_id); ?>
    <?php else : ?>
      <article class="polaris-thankyou-hero polaris-surface">
        <span class="polaris-thankyou-hero__icon" aria-hidden="true">
          <i class="fa-solid fa-receipt"></i>
        </span>
        <div>
          <h1><?php esc_html_e('Tesekkurler', 'polaris'); ?></h1>
          <p><?php esc_html_e('Siparis bilgisi bulunamadi. Guncel siparislerinizi hesabinizdan takip edebilirsiniz.', 'polaris'); ?></p>
        </div>
      </article>

      <div class="polaris-thankyou-actions">
        <a class="btn btn-primary" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php esc_html_e('Magazaya git', 'polaris'); ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>