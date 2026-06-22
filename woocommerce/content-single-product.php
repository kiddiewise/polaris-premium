<?php
/**
 * Product content for the single-product template.
 *
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

global $product;

if (!$product instanceof WC_Product) {
    return;
}

do_action('woocommerce_before_single_product');

if (post_password_required()) {
    echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    return;
}
?>
<article id="product-<?php the_ID(); ?>" <?php wc_product_class('pd-shell', $product); ?>>
    <div class="container pd-container">
        <div class="pd-layout">
            <?php do_action('woocommerce_before_single_product_summary'); ?>

            <section class="pd-summary summary entry-summary" aria-label="<?php echo esc_attr__('Ürün bilgileri', 'polaris'); ?>">
                <?php do_action('woocommerce_single_product_summary'); ?>
            </section>

            <?php polaris_render_product_family(); ?>

            <?php polaris_render_product_accordion(); ?>
        </div>

        <div class="pd-below-summary">
            <?php do_action('woocommerce_after_single_product_summary'); ?>
        </div>
    </div>
</article>
<?php do_action('woocommerce_after_single_product'); ?>
