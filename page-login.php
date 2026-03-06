<?php
/**
 * Template Name: Polaris Login Register
 * Description: WooCommerce login/register ekranini modern bir kapsayici ile gosterir.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (is_user_logged_in()) {
    $account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/');
    wp_safe_redirect($account_url);
    exit;
}

if (!defined('DONOTCACHEPAGE')) {
    define('DONOTCACHEPAGE', true);
}
nocache_headers();

get_header();

$logo_id    = get_theme_mod('custom_logo');
$logo_image = $logo_id ? wp_get_attachment_image($logo_id, 'full', false, [
    'class'   => 'polaris-auth-brand__logo-img',
    'loading' => 'eager',
    'alt'     => get_bloginfo('name'),
]) : '';
?>

<main class="polaris-content">
  <section class="container polaris-auth-page">
    <div class="polaris-auth-shell">
      <div class="polaris-auth-orb polaris-auth-orb--one" aria-hidden="true"></div>
      <div class="polaris-auth-orb polaris-auth-orb--two" aria-hidden="true"></div>

      <div class="polaris-auth-layout">
        <aside class="polaris-auth-brand fade-up active">
          <a class="polaris-auth-brand__logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
            <?php if (!empty($logo_image)) : ?>
              <?php echo wp_kses_post($logo_image); ?>
            <?php else : ?>
              <span class="polaris-auth-brand__logo-fallback"><?php echo esc_html(get_bloginfo('name')); ?></span>
            <?php endif; ?>
          </a>

          <span class="polaris-auth-kicker">
            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
            <?php esc_html_e('Guvenli giris alani', 'polaris'); ?>
          </span>

          <h1><?php esc_html_e('Hesabina gir, siparislerini hizla yonet', 'polaris'); ?></h1>
          <p><?php esc_html_e('Tek alanda giris yap veya kayit ol. Google ile giris secenegiyle saniyeler icinde devam et.', 'polaris'); ?></p>

          <div class="polaris-auth-trust">
            <span><i class="fa-solid fa-lock" aria-hidden="true"></i> <?php esc_html_e('SSL korumasi', 'polaris'); ?></span>
            <span><i class="fa-solid fa-bolt" aria-hidden="true"></i> <?php esc_html_e('Hizli erisim', 'polaris'); ?></span>
            <span><i class="fa-solid fa-user-check" aria-hidden="true"></i> <?php esc_html_e('Tek tikla giris', 'polaris'); ?></span>
          </div>
        </aside>

        <article class="polaris-surface polaris-auth-card fade-up active">
          <header class="polaris-auth-head">
            <h2><?php esc_html_e('Giris / Kayit', 'polaris'); ?></h2>
            <p><?php esc_html_e('Google ile giris butonu formlarin icinde otomatik yer alir.', 'polaris'); ?></p>
          </header>

          <?php if (function_exists('WC') && WC()) : ?>
            <?php
            $wc_my_account_template = WC()->plugin_path() . '/templates/myaccount/my-account.php';
            if (file_exists($wc_my_account_template)) {
                include $wc_my_account_template;
            }
            ?>
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
    </div>
  </section>
</main>

<?php
get_footer();
