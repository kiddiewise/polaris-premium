<?php
/**
 * My Account change password form.
 *
 * @package PolarisPremium
 */

defined('ABSPATH') || exit;
?>

<section class="polaris-account-password">
  <header class="polaris-account-password__head">
    <h3><?php esc_html_e('Sifre Degistir', 'polaris'); ?></h3>
    <p><?php esc_html_e('Hesap guvenliginiz icin guclu bir sifre belirleyin.', 'polaris'); ?></p>
  </header>

  <form method="post" class="polaris-account-password__form" autocomplete="off" novalidate>
    <p class="form-row form-row-wide">
      <label for="current_password"><?php esc_html_e('Mevcut Sifre', 'polaris'); ?> <span class="required">*</span></label>
      <input type="password" class="input-text" name="current_password" id="current_password" required>
    </p>

    <p class="form-row form-row-wide">
      <label for="new_password"><?php esc_html_e('Yeni Sifre', 'polaris'); ?> <span class="required">*</span></label>
      <input type="password" class="input-text" name="new_password" id="new_password" required>
    </p>

    <p class="form-row form-row-wide">
      <label for="confirm_password"><?php esc_html_e('Yeni Sifre Tekrar', 'polaris'); ?> <span class="required">*</span></label>
      <input type="password" class="input-text" name="confirm_password" id="confirm_password" required>
    </p>

    <p class="form-row">
      <?php wp_nonce_field('polaris_change_password_action', 'polaris_change_password_nonce'); ?>
      <button type="submit" class="button" name="polaris_change_password_submit" value="1">
        <?php esc_html_e('Sifreyi Guncelle', 'polaris'); ?>
      </button>
    </p>
  </form>
</section>
