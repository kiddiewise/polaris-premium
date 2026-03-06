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
  <header class="polaris-page-head polaris-checkout-head">
    <div>
      <h1><?php esc_html_e('Odeme', 'polaris'); ?></h1>
      <p><?php esc_html_e('Bilgilerini tamamla, siparisini kontrol et ve satin alimi bitir.', 'polaris'); ?></p>
    </div>
    <button class="btn btn-ghost polaris-checkout-summary-toggle" type="button" data-checkout-summary-toggle>
      <i class="fa-solid fa-receipt" aria-hidden="true"></i>
      <?php esc_html_e('Siparis ozeti', 'polaris'); ?>
    </button>
  </header>

  <form name="checkout" method="post" class="checkout woocommerce-checkout polaris-checkout-form" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>">
    <div class="polaris-checkout-layout">
      <div class="polaris-checkout-main">
        <?php if ($checkout->get_checkout_fields()) : ?>
          <?php do_action('woocommerce_checkout_before_customer_details'); ?>

          <div class="col2-set" id="customer_details">
            <section class="col-1 polaris-surface polaris-checkout-card">
              <h2><?php esc_html_e('Fatura bilgileri', 'polaris'); ?></h2>
              <?php do_action('woocommerce_checkout_billing'); ?>
            </section>

            <section class="col-2 polaris-surface polaris-checkout-card">
              <h2><?php esc_html_e('Teslimat bilgileri', 'polaris'); ?></h2>
              <?php do_action('woocommerce_checkout_shipping'); ?>
            </section>
          </div>

          <?php do_action('woocommerce_checkout_after_customer_details'); ?>
        <?php endif; ?>
      </div>

      <aside class="polaris-checkout-side polaris-surface" data-checkout-summary>
        <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>
        <h2 id="order_review_heading"><?php esc_html_e('Siparis ozeti', 'polaris'); ?></h2>
        <?php do_action('woocommerce_checkout_before_order_review'); ?>

        <div id="order_review" class="woocommerce-checkout-review-order">
          <?php do_action('woocommerce_checkout_order_review'); ?>
        </div>

        <?php do_action('woocommerce_checkout_after_order_review'); ?>
      </aside>
    </div>
  </form>
</section>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
