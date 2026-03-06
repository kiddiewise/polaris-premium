<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('WC') || !WC()) {
    return;
}

$account_template = WC()->plugin_path() . '/templates/myaccount/my-account.php';

$account_template_exists = file_exists($account_template);

if (is_user_logged_in()) {
    if ($account_template_exists) {
        include $account_template;
    }
    return;
}

if (!function_exists('wc_get_template')) {
    return;
}

$logo_id    = get_theme_mod('custom_logo');
$logo_image = $logo_id ? wp_get_attachment_image($logo_id, 'full', false, [
    'class'   => 'polaris-auth-brand__logo-img',
    'loading' => 'eager',
    'alt'     => get_bloginfo('name'),
]) : '';
?>

<section class="polaris-content polaris-auth-content">
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
            <?php esc_html_e('Güvenli giriş alanı', 'polaris'); ?>
          </span>

          <h1><?php esc_html_e('Hesabına gir, siparişlerini hızla yönet', 'polaris'); ?></h1>
          <p><?php esc_html_e('Tek alanda giriş yap veya kayıt ol. Google ile giriş seçeneğiyle saniyeler içinde devam et.', 'polaris'); ?></p>

          <div class="polaris-auth-trust">
            <span><i class="fa-solid fa-lock" aria-hidden="true"></i> <?php esc_html_e('SSL koruması', 'polaris'); ?></span>
            <span><i class="fa-solid fa-bolt" aria-hidden="true"></i> <?php esc_html_e('Hızlı erişim', 'polaris'); ?></span>
            <span><i class="fa-solid fa-user-check" aria-hidden="true"></i> <?php esc_html_e('Tek tıkla giriş', 'polaris'); ?></span>
          </div>
        </aside>

        <article class="polaris-surface polaris-auth-card fade-up active">
          <header class="polaris-auth-head">
            <h2><?php esc_html_e('Giriş / Kayıt', 'polaris'); ?></h2>
            <p>
              <?php if (function_exists('polaris_google_login_is_enabled') && polaris_google_login_is_enabled()) : ?>
                <?php esc_html_e('Google ile giriş seçeneği aktif. Formdan tek tıkla devam edebilirsiniz.', 'polaris'); ?>
              <?php else : ?>
                <?php esc_html_e('Google ile giriş alanı görünür, entegrasyon ayarı tamamlandığında aktif olur.', 'polaris'); ?>
              <?php endif; ?>
            </p>
          </header>

          <?php wc_get_template('myaccount/form-login.php'); ?>
        </article>
      </div>
    </div>
  </section>
</section>
