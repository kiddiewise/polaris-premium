<?php
/**
 * Template Name: Polaris Login Register
 * Description: WooCommerce login/register ekranini modern bir kapsayici ile gosterir.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main class="polaris-content">
  <section class="container polaris-auth-page">
    <div class="polaris-auth-shell fade-up active">
      <header class="polaris-auth-head">
        <span class="polaris-auth-kicker"><?php esc_html_e('Guvenli oturum', 'polaris'); ?></span>
        <h1><?php esc_html_e('Hesabina gir veya kayit ol', 'polaris'); ?></h1>
        <p><?php esc_html_e('Siparislerini takip et, adreslerini yonet ve hizli odeme avantajindan yararlan.', 'polaris'); ?></p>
      </header>

      <article class="polaris-surface polaris-auth-card">
        <?php if (function_exists('wc') && shortcode_exists('woocommerce_my_account')) : ?>
          <?php echo do_shortcode('[woocommerce_my_account]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php else : ?>
          <?php if (is_user_logged_in()) : ?>
            <p><?php esc_html_e('Zaten giris yaptiniz.', 'polaris'); ?></p>
          <?php else : ?>
            <?php
            wp_login_form([
                'label_username' => __('E-posta veya kullanici adi', 'polaris'),
                'label_password' => __('Sifre', 'polaris'),
                'label_log_in'   => __('Giris yap', 'polaris'),
                'remember'       => true,
            ]);
            ?>
            <?php polaris_google_login_render_button('page-login-fallback'); ?>
          <?php endif; ?>
        <?php endif; ?>
      </article>
    </div>
  </section>
</main>

<?php
get_footer();