<?php
if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_checkout_form', $checkout);

if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('Checkout is not available whilst you are logged out.', 'woocommerce')));
    return;
}
?>

<section class="container polaris-checkout-page">
  <div class="polaris-checkout-shell">
    <header class="polaris-page-head polaris-checkout-head">
      <div class="polaris-checkout-head__main">
        <span class="polaris-checkout-kicker">
          <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
          <?php esc_html_e('Güvenli ödeme', 'polaris'); ?>
        </span>
        <h1><?php esc_html_e('Ödeme', 'polaris'); ?></h1>
        <p><?php esc_html_e('Bilgilerini tamamla, siparişini kontrol et ve satın alımı bitir.', 'polaris'); ?></p>

        <ol class="polaris-checkout-steps" aria-label="<?php echo esc_attr__('Ödeme adımları', 'polaris'); ?>">
          <li class="is-active">
            <span>01</span>
            <?php esc_html_e('Bilgiler', 'polaris'); ?>
          </li>
          <li>
            <span>02</span>
            <?php esc_html_e('Ödeme', 'polaris'); ?>
          </li>
          <li>
            <span>03</span>
            <?php esc_html_e('Tamamlandı', 'polaris'); ?>
          </li>
        </ol>
      </div>

      <button class="btn btn-ghost polaris-checkout-summary-toggle" type="button" data-checkout-summary-toggle aria-expanded="false">
        <i class="fa-solid fa-receipt" aria-hidden="true"></i>
        <?php esc_html_e('Sipariş özeti', 'polaris'); ?>
      </button>
    </header>

    <form name="checkout" method="post" class="checkout woocommerce-checkout polaris-checkout-form" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>">
      <div class="polaris-checkout-layout">
        <div class="polaris-checkout-main">
          <?php if ($checkout->get_checkout_fields()) : ?>
            <?php do_action('woocommerce_checkout_before_customer_details'); ?>

            <div class="col2-set" id="customer_details">
              <section class="col-1 polaris-surface polaris-checkout-card">
                <header class="polaris-checkout-card__head">
                  <span class="polaris-checkout-card__index">01</span>
                  <div>
                    <h2><?php esc_html_e('Fatura bilgileri', 'polaris'); ?></h2>
                    <p><?php esc_html_e('Fatura ve iletişim bilgilerini doldurun.', 'polaris'); ?></p>
                  </div>
                </header>
                <?php do_action('woocommerce_checkout_billing'); ?>
              </section>

              <section class="col-2 polaris-surface polaris-checkout-card">
                <header class="polaris-checkout-card__head">
                  <span class="polaris-checkout-card__index">02</span>
                  <div>
                    <h2><?php esc_html_e('Teslimat bilgileri', 'polaris'); ?></h2>
                    <p><?php esc_html_e('Adres ve teslimat detaylarını kontrol edin.', 'polaris'); ?></p>
                  </div>
                </header>
                <?php do_action('woocommerce_checkout_shipping'); ?>
              </section>
            </div>

            <?php do_action('woocommerce_checkout_after_customer_details'); ?>
          <?php endif; ?>
        </div>

        <aside class="polaris-checkout-side polaris-surface" data-checkout-summary>
          <div class="polaris-checkout-side__handle" aria-hidden="true"></div>
          <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>
          <header class="polaris-checkout-side__head">
            <h2 id="order_review_heading"><?php esc_html_e('Sipariş özeti', 'polaris'); ?></h2>
            <p><?php esc_html_e('Ürünler, kargo ve ödeme adımını buradan tamamlayın.', 'polaris'); ?></p>
          </header>
          <?php do_action('woocommerce_checkout_before_order_review'); ?>

          <div id="order_review" class="woocommerce-checkout-review-order">
            <?php do_action('woocommerce_checkout_order_review'); ?>
          </div>

          <div class="polaris-checkout-assurance" aria-label="<?php echo esc_attr__('Güven bilgileri', 'polaris'); ?>">
            <span><i class="fa-solid fa-lock" aria-hidden="true"></i> <?php esc_html_e('256-bit SSL', 'polaris'); ?></span>
            <span><i class="fa-solid fa-truck-fast" aria-hidden="true"></i> <?php esc_html_e('Hızlı teslimat', 'polaris'); ?></span>
            <span><i class="fa-solid fa-headset" aria-hidden="true"></i> <?php esc_html_e('Destek hattı', 'polaris'); ?></span>
          </div>

          <?php do_action('woocommerce_checkout_after_order_review'); ?>
        </aside>

        <button class="polaris-checkout-overlay" type="button" data-checkout-overlay aria-label="<?php echo esc_attr__('Sipariş özetini kapat', 'polaris'); ?>"></button>
      </div>
    </form>
  </div>
</section>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
