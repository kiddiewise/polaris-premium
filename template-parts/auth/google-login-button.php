<?php
if (!defined('ABSPATH')) {
    exit;
}

$context = isset($args['context']) ? sanitize_html_class((string) $args['context']) : 'default';
$redirect_to = isset($args['redirect_to']) ? esc_url($args['redirect_to']) : '';
$is_enabled = isset($args['is_enabled']) ? (bool) $args['is_enabled'] : false;
$disabled_message = isset($args['disabled_message']) ? sanitize_text_field((string) $args['disabled_message']) : '';
?>
<div class="polaris-google-login" data-google-login-wrap data-context="<?php echo esc_attr($context); ?>">
  <button
    class="polaris-google-login__button"
    type="button"
    data-google-login-btn
    data-redirect="<?php echo esc_attr($redirect_to); ?>"
    <?php echo $is_enabled ? '' : 'disabled aria-disabled="true"'; ?>
  >
    <span class="polaris-google-login__icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" role="img" aria-hidden="true" focusable="false">
        <path fill="#EA4335" d="M12 10.2v3.9h5.4c-.2 1.3-1.6 3.9-5.4 3.9-3.2 0-5.9-2.7-5.9-6s2.6-6 5.9-6c1.8 0 3 .8 3.7 1.5l2.5-2.4C16.6 3.7 14.5 3 12 3 7 3 3 7 3 12s4 9 9 9c5.2 0 8.6-3.6 8.6-8.7 0-.6-.1-1.1-.1-1.6H12z"/>
        <path fill="#34A853" d="M3 7.8l3.2 2.3C7 8 9.3 6 12 6c1.8 0 3 .8 3.7 1.5l2.5-2.4C16.6 3.7 14.5 3 12 3 8.3 3 5.1 5.1 3 7.8z"/>
        <path fill="#4A90E2" d="M12 21c2.5 0 4.6-.8 6.2-2.2l-2.9-2.4c-.8.6-1.9 1.1-3.3 1.1-3.7 0-5.1-2.5-5.4-3.8L3.3 16c2.1 3 5.4 5 8.7 5z"/>
        <path fill="#FBBC05" d="M3 12c0 1.4.3 2.7.9 3.9l3.3-2.4c-.2-.6-.3-1-.3-1.5s.1-.9.3-1.5L3.9 8.1C3.3 9.3 3 10.6 3 12z"/>
      </svg>
    </span>
    <span class="polaris-google-login__label"><?php esc_html_e('Google ile giriş yap', 'polaris'); ?></span>
  </button>
  <p class="polaris-google-login__status<?php echo !$is_enabled && !empty($disabled_message) ? ' is-info' : ''; ?>" data-google-login-status aria-live="polite">
    <?php echo !$is_enabled && !empty($disabled_message) ? esc_html($disabled_message) : ''; ?>
  </p>
</div>
