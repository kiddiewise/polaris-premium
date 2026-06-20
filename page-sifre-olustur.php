<?php
/**
 * Template Name: Polaris Şifre Oluştur
 * Description: Güvenli özel şifre oluşturma ve sıfırlama ekranı.
 */

if (!defined('ABSPATH')) {
    exit;
}

$reset_state = polaris_password_reset_process_request();
$reset_user  = $reset_state['user'];
$reset_key   = $reset_state['key'];
$reset_login = $reset_state['login'];
$errors      = $reset_state['errors'];
$is_valid    = $reset_user instanceof WP_User;

get_header();

$logo_id    = get_theme_mod('custom_logo');
$logo_image = $logo_id ? wp_get_attachment_image($logo_id, 'full', false, [
    'class'   => 'polaris-auth-brand__logo-img',
    'loading' => 'eager',
    'alt'     => get_bloginfo('name'),
]) : '';
?>

<main class="polaris-content">
  <section class="container polaris-auth-page polaris-password-reset-page">
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
            <?php esc_html_e('Güvenli şifre alanı', 'polaris'); ?>
          </span>

          <h1><?php esc_html_e('Hesabını güçlü bir şifreyle koru', 'polaris'); ?></h1>
          <p><?php esc_html_e('Yeni şifren yalnızca WordPress’in güvenli parola sistemi üzerinden kaydedilir.', 'polaris'); ?></p>

          <div class="polaris-auth-trust">
            <span><i class="fa-solid fa-key" aria-hidden="true"></i> <?php esc_html_e('Tek kullanımlık bağlantı', 'polaris'); ?></span>
            <span><i class="fa-solid fa-lock" aria-hidden="true"></i> <?php esc_html_e('Güvenli doğrulama', 'polaris'); ?></span>
            <span><i class="fa-solid fa-user-shield" aria-hidden="true"></i> <?php esc_html_e('Şifreli saklama', 'polaris'); ?></span>
          </div>
        </aside>

        <article class="polaris-surface polaris-auth-card polaris-password-reset-card fade-up active">
          <header class="polaris-auth-head">
            <h2><?php echo $is_valid ? esc_html__('Yeni şifre oluştur', 'polaris') : esc_html__('Bağlantı kullanılamıyor', 'polaris'); ?></h2>
            <p><?php echo $is_valid ? esc_html__('En az 10 karakterden oluşan güçlü bir şifre belirleyin.', 'polaris') : esc_html__('Güvenliğiniz için yeni bir şifre oluşturma bağlantısı isteyin.', 'polaris'); ?></p>
          </header>

          <?php if ($errors instanceof WP_Error && $errors->has_errors()) : ?>
            <div class="polaris-auth-notice polaris-auth-notice--error" role="alert">
              <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
              <div>
                <?php foreach ($errors->get_error_messages() as $message) : ?>
                  <p><?php echo esc_html(wp_strip_all_tags($message)); ?></p>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <?php if ($is_valid) : ?>
            <form class="polaris-password-reset-form" action="<?php echo esc_url(polaris_get_password_reset_page_url()); ?>" method="post" autocomplete="off" data-password-reset-form>
              <input type="hidden" name="key" value="<?php echo esc_attr($reset_key); ?>">
              <input type="hidden" name="login" value="<?php echo esc_attr($reset_login); ?>">
              <input type="hidden" name="rp_key" value="<?php echo esc_attr($reset_key); ?>">
              <?php wp_nonce_field('polaris_password_reset_' . (int) $reset_user->ID, 'polaris_password_reset_nonce'); ?>

              <div class="polaris-password-field">
                <label for="polaris-password-1"><?php esc_html_e('Yeni şifre', 'polaris'); ?></label>
                <div class="polaris-password-field__control">
                  <i class="fa-solid fa-lock polaris-password-field__icon" aria-hidden="true"></i>
                  <input type="password" id="polaris-password-1" name="pass1" minlength="10" maxlength="150" autocomplete="new-password" required data-password-primary>
                  <button class="polaris-password-toggle" type="button" aria-label="<?php echo esc_attr__('Şifreyi göster', 'polaris'); ?>" aria-pressed="false" data-password-toggle>
                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                  </button>
                </div>
              </div>

              <div class="polaris-password-field">
                <label for="polaris-password-2"><?php esc_html_e('Yeni şifre tekrar', 'polaris'); ?></label>
                <div class="polaris-password-field__control">
                  <i class="fa-solid fa-lock polaris-password-field__icon" aria-hidden="true"></i>
                  <input type="password" id="polaris-password-2" name="pass2" minlength="10" maxlength="150" autocomplete="new-password" required data-password-confirm>
                </div>
              </div>

              <div class="polaris-password-strength" data-password-strength aria-live="polite">
                <span class="polaris-password-strength__bar" aria-hidden="true"><span></span></span>
                <span data-password-strength-label><?php esc_html_e('En az 10 karakter kullanın.', 'polaris'); ?></span>
              </div>

              <p class="polaris-password-hint"><?php echo esc_html(wp_get_password_hint()); ?></p>

              <?php do_action('resetpass_form', $reset_user); ?>

              <button class="polaris-password-reset-submit" type="submit">
                <i class="fa-solid fa-check" aria-hidden="true"></i>
                <?php esc_html_e('Şifreyi kaydet', 'polaris'); ?>
              </button>
            </form>
          <?php else : ?>
            <a class="polaris-password-reset-submit" href="<?php echo esc_url(polaris_password_reset_login_url()); ?>">
              <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
              <?php esc_html_e('Giriş sayfasına dön', 'polaris'); ?>
            </a>
          <?php endif; ?>
        </article>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
