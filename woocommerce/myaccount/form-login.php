<?php
/**
 * My Account login/register form.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

defined('ABSPATH') || exit;

$registration_enabled = 'yes' === get_option('woocommerce_enable_myaccount_registration');
$default_panel        = $registration_enabled && isset($_GET['register']) && '1' === sanitize_text_field(wp_unslash($_GET['register']))
    ? 'register'
    : 'login';
$redirect_to          = isset($_REQUEST['redirect_to']) ? wp_validate_redirect(esc_url_raw(wp_unslash($_REQUEST['redirect_to'])), '') : '';

do_action('woocommerce_before_customer_login_form');
?>

<div class="polaris-auth-forms" data-auth-panels data-default-panel="<?php echo esc_attr($default_panel); ?>">
  <?php if ($registration_enabled) : ?>
    <div class="polaris-auth-tabs" role="tablist" aria-label="<?php echo esc_attr__('Giriş ve kayıt sekmeleri', 'polaris'); ?>">
      <button
        type="button"
        class="polaris-auth-tab<?php echo $default_panel === 'login' ? ' is-active' : ''; ?>"
        data-auth-panel-btn="login"
        role="tab"
        aria-selected="<?php echo $default_panel === 'login' ? 'true' : 'false'; ?>"
      >
        <?php echo esc_html__('Giriş', 'polaris'); ?>
      </button>
      <button
        type="button"
        class="polaris-auth-tab<?php echo $default_panel === 'register' ? ' is-active' : ''; ?>"
        data-auth-panel-btn="register"
        role="tab"
        aria-selected="<?php echo $default_panel === 'register' ? 'true' : 'false'; ?>"
      >
        <?php echo esc_html__('Üye Ol', 'polaris'); ?>
      </button>
    </div>
  <?php endif; ?>

  <div class="u-columns col2-set" id="customer_login">
    <div class="u-column1 col-1 polaris-auth-panel<?php echo $default_panel === 'login' ? ' is-active' : ''; ?>" data-auth-panel="login">
      <form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>
        <h3 class="polaris-auth-panel__title"><?php esc_html_e('Giriş Yap', 'polaris'); ?></h3>

        <?php do_action('woocommerce_login_form_start'); ?>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide polaris-auth-field">
          <label for="username" class="screen-reader-text"><?php esc_html_e('Kullanıcı adı veya e-posta adresi', 'polaris'); ?>&nbsp;<span class="required">*</span></label>
          <span class="polaris-auth-field__icon" aria-hidden="true"><i class="fa-regular fa-envelope"></i></span>
          <input
            type="text"
            class="woocommerce-Input woocommerce-Input--text input-text"
            name="username"
            id="username"
            autocomplete="username"
            value="<?php echo !empty($_POST['username']) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>"
            placeholder="<?php echo esc_attr__('E-posta', 'polaris'); ?>"
          />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide polaris-auth-field">
          <label for="password" class="screen-reader-text"><?php esc_html_e('Şifre', 'polaris'); ?>&nbsp;<span class="required">*</span></label>
          <span class="polaris-auth-field__icon" aria-hidden="true"><i class="fa-solid fa-lock"></i></span>
          <input
            class="woocommerce-Input woocommerce-Input--text input-text"
            type="password"
            name="password"
            id="password"
            autocomplete="current-password"
            placeholder="<?php echo esc_attr__('Şifre', 'polaris'); ?>"
          />
        </p>

        <?php do_action('woocommerce_login_form'); ?>

        <p class="form-row polaris-auth-row polaris-auth-row--remember">
          <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
            <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
            <span><?php esc_html_e('Beni hatırla', 'polaris'); ?></span>
          </label>
        </p>

        <p class="form-row polaris-auth-row polaris-auth-row--submit">
          <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
          <?php if (!empty($redirect_to)) : ?>
            <input type="hidden" name="redirect" value="<?php echo esc_attr($redirect_to); ?>" />
          <?php endif; ?>
          <button type="submit" class="woocommerce-button button woocommerce-form-login__submit" name="login" value="<?php esc_attr_e('Giriş Yap', 'polaris'); ?>">
            <?php esc_html_e('Giriş Yap', 'polaris'); ?>
          </button>
        </p>

        <p class="woocommerce-LostPassword lost_password">
          <a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Parolanızı mı unuttunuz?', 'polaris'); ?></a>
        </p>

        <?php do_action('woocommerce_login_form_end'); ?>

        <?php if ($registration_enabled) : ?>
          <p class="polaris-auth-switch-note">
            <button type="button" class="polaris-auth-switch-btn" data-auth-panel-btn="register">
              <?php esc_html_e('Hesabınız yok mu? Üye olun', 'polaris'); ?>
            </button>
          </p>
        <?php endif; ?>
      </form>
    </div>

    <?php if ($registration_enabled) : ?>
      <div class="u-column2 col-2 polaris-auth-panel<?php echo $default_panel === 'register' ? ' is-active' : ''; ?>" data-auth-panel="register">
        <form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action('woocommerce_register_form_tag'); ?> novalidate>
          <h3 class="polaris-auth-panel__title"><?php esc_html_e('Üye Ol', 'polaris'); ?></h3>

          <?php do_action('woocommerce_register_form_start'); ?>

          <?php if ('no' === get_option('woocommerce_registration_generate_username')) : ?>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide polaris-auth-field">
              <label for="reg_username" class="screen-reader-text"><?php esc_html_e('Kullanıcı adı', 'polaris'); ?>&nbsp;<span class="required">*</span></label>
              <span class="polaris-auth-field__icon" aria-hidden="true"><i class="fa-regular fa-user"></i></span>
              <input
                type="text"
                class="woocommerce-Input woocommerce-Input--text input-text"
                name="username"
                id="reg_username"
                autocomplete="username"
                value="<?php echo !empty($_POST['username']) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>"
                placeholder="<?php echo esc_attr__('Kullanıcı adı', 'polaris'); ?>"
              />
            </p>
          <?php endif; ?>

          <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide polaris-auth-field">
            <label for="reg_email" class="screen-reader-text"><?php esc_html_e('E-posta adresi', 'polaris'); ?>&nbsp;<span class="required">*</span></label>
            <span class="polaris-auth-field__icon" aria-hidden="true"><i class="fa-regular fa-envelope"></i></span>
            <input
              type="email"
              class="woocommerce-Input woocommerce-Input--text input-text"
              name="email"
              id="reg_email"
              autocomplete="email"
              value="<?php echo !empty($_POST['email']) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>"
              placeholder="<?php echo esc_attr__('E-posta', 'polaris'); ?>"
            />
          </p>

          <?php if ('no' === get_option('woocommerce_registration_generate_password')) : ?>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide polaris-auth-field">
              <label for="reg_password" class="screen-reader-text"><?php esc_html_e('Şifre', 'polaris'); ?>&nbsp;<span class="required">*</span></label>
              <span class="polaris-auth-field__icon" aria-hidden="true"><i class="fa-solid fa-lock"></i></span>
              <input
                type="password"
                class="woocommerce-Input woocommerce-Input--text input-text"
                name="password"
                id="reg_password"
                autocomplete="new-password"
                placeholder="<?php echo esc_attr__('Şifre', 'polaris'); ?>"
              />
            </p>
          <?php else : ?>
            <p class="polaris-auth-help">
              <?php esc_html_e('Şifreniz e-posta adresinize gönderilecektir.', 'polaris'); ?>
            </p>
          <?php endif; ?>

          <?php do_action('woocommerce_register_form'); ?>

          <p class="woocommerce-form-row form-row polaris-auth-row polaris-auth-row--submit">
            <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
            <?php if (!empty($redirect_to)) : ?>
              <input type="hidden" name="redirect" value="<?php echo esc_attr($redirect_to); ?>" />
            <?php endif; ?>
            <button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e('Üye Ol', 'polaris'); ?>">
              <?php esc_html_e('Üye Ol', 'polaris'); ?>
            </button>
          </p>

          <?php do_action('woocommerce_register_form_end'); ?>

          <p class="polaris-auth-switch-note">
            <button type="button" class="polaris-auth-switch-btn" data-auth-panel-btn="login">
              <?php esc_html_e('Zaten hesabınız var mı? Giriş yapın', 'polaris'); ?>
            </button>
          </p>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php do_action('woocommerce_after_customer_login_form'); ?>
