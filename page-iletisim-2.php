<?php
if (!defined('ABSPATH')) {
    exit;
}

$company_name    = 'Zoka Balık Av Malzemeleri';
$affiliate_text  = 'Polaris Kurşun, Zoka Balık Av Malzemeleri şirketi iştirakidir.';
$support_email   = 'destek@polariskursun.com';
$instagram_url   = 'https://www.instagram.com/polariskursun/';
$facebook_url    = 'https://www.facebook.com/people/Polaris-Kur%C5%9Fun/61570044187417/?ref=NONE_xav_ig_profile_page_web#';
$youtube_label   = 'polariskurşun';
$youtube_url     = 'https://www.youtube.com/results?search_query=' . rawurlencode($youtube_label);
$whatsapp_text   = __('Merhaba, iletişim sayfanızdan ulaştım. Bilgi almak istiyorum.', 'polaris');
$whatsapp_url    = function_exists('polaris_get_whatsapp_url')
    ? polaris_get_whatsapp_url($whatsapp_text)
    : esc_url('https://wa.me/905462629002?text=' . rawurlencode($whatsapp_text));
$whatsapp_number = function_exists('polaris_get_whatsapp_number')
    ? polaris_get_whatsapp_number()
    : '905462629002';
$whatsapp_label  = '+' . ltrim((string) $whatsapp_number, '+');

get_header();
?>

<section class="polaris-content polaris-contact-page">
  <div class="container">
    <header class="polaris-contact-hero polaris-surface fade-up active">
      <p class="polaris-contact-kicker"><?php echo esc_html__('İletişim', 'polaris'); ?></p>
      <h1><?php echo esc_html($company_name); ?></h1>
      <p class="polaris-contact-subtitle"><?php echo esc_html($affiliate_text); ?></p>
    </header>

    <div class="polaris-contact-grid">
      <article class="polaris-contact-card polaris-surface fade-up active">
        <div class="polaris-contact-card__head">
          <span class="polaris-contact-card__icon"><i class="fa-solid fa-headset" aria-hidden="true"></i></span>
          <h2><?php echo esc_html__('Hızlı Destek', 'polaris'); ?></h2>
        </div>

        <p><?php echo esc_html__('WhatsApp destek hattımız üzerinden hızlı bilgi alabilirsiniz.', 'polaris'); ?></p>
        <a class="btn btn-primary polaris-contact-cta" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener">
          <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
          <?php echo esc_html__('WhatsApp ile iletişime geç', 'polaris'); ?>
        </a>
        <span class="polaris-contact-meta"><?php echo esc_html($whatsapp_label); ?></span>
      </article>

      <article class="polaris-contact-card polaris-surface fade-up active">
        <div class="polaris-contact-card__head">
          <span class="polaris-contact-card__icon"><i class="fa-regular fa-envelope" aria-hidden="true"></i></span>
          <h2><?php echo esc_html__('E-Posta', 'polaris'); ?></h2>
        </div>

        <p><?php echo esc_html__('Detaylı talepleriniz için e-posta kanalımızı kullanabilirsiniz.', 'polaris'); ?></p>
        <a class="btn btn-ghost polaris-contact-cta" href="mailto:<?php echo esc_attr($support_email); ?>">
          <i class="fa-regular fa-paper-plane" aria-hidden="true"></i>
          <?php echo esc_html($support_email); ?>
        </a>
      </article>

      <article class="polaris-contact-card polaris-surface polaris-contact-card--full fade-up active">
        <div class="polaris-contact-card__head">
          <span class="polaris-contact-card__icon"><i class="fa-solid fa-hashtag" aria-hidden="true"></i></span>
          <h2><?php echo esc_html__('Sosyal Medya', 'polaris'); ?></h2>
        </div>

        <div class="polaris-contact-socials">
          <a class="polaris-contact-social polaris-contact-social--instagram" href="<?php echo esc_url($instagram_url); ?>" target="_blank" rel="noopener">
            <i class="fa-brands fa-instagram" aria-hidden="true"></i>
            <span>Instagram</span>
            <small>@polariskursun</small>
          </a>

          <a class="polaris-contact-social polaris-contact-social--youtube" href="<?php echo esc_url($youtube_url); ?>" target="_blank" rel="noopener">
            <i class="fa-brands fa-youtube" aria-hidden="true"></i>
            <span>YouTube</span>
            <small><?php echo esc_html($youtube_label); ?></small>
          </a>

          <a class="polaris-contact-social polaris-contact-social--facebook" href="<?php echo esc_url($facebook_url); ?>" target="_blank" rel="noopener">
            <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
            <span>Facebook</span>
            <small>Polaris Kurşun</small>
          </a>
        </div>
      </article>
    </div>
  </div>
</section>

<?php
get_footer();
