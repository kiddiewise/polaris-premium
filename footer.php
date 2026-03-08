</main>

<?php
$social_links = function_exists('polaris_get_social_links')
    ? polaris_get_social_links()
    : [
        'instagram' => 'https://www.instagram.com/polariskursun/',
        'youtube'   => 'https://www.youtube.com/results?search_query=polariskursun',
        'facebook'  => 'https://www.facebook.com/people/Polaris-Kur%C5%9Fun/61570044187417/?ref=NONE_xav_ig_profile_page_web#',
    ];

$instagram_url = !empty($social_links['instagram']) ? $social_links['instagram'] : 'https://www.instagram.com/polariskursun/';
$youtube_url   = !empty($social_links['youtube']) ? $social_links['youtube'] : 'https://www.youtube.com/results?search_query=polariskursun';
$facebook_url  = !empty($social_links['facebook']) ? $social_links['facebook'] : 'https://www.facebook.com/people/Polaris-Kur%C5%9Fun/61570044187417/?ref=NONE_xav_ig_profile_page_web#';

$whatsapp_footer_url = function_exists('polaris_get_whatsapp_url')
    ? polaris_get_whatsapp_url(__('Merhaba, web sitenizden ulaştım.', 'polaris'))
    : esc_url('https://wa.me/905462629002');

$kurumsal_links = [
    ['label' => 'Banka Bilgileri', 'path' => '/banka-bilgileri/'],
    ['label' => 'Hakkımızda', 'path' => '/hakkimizda/'],
    ['label' => 'İletişim', 'path' => '/iletisim-2/'],
    ['label' => 'Gizlilik Politikası', 'path' => '/gizlilik-politikasi/'],
    ['label' => 'Kişisel Verilere İlişkin Aydınlatma Metni', 'path' => '/kisisel-verilere-iliskin-aydinlatma-metni/'],
    ['label' => 'Kişisel Verilere İlişkin Beyan ve Rıza Onay Metni', 'path' => '/kisisel-verilere-iliskin-beyan-ve-riza-onay-metni/'],
];

$kullanici_links = [
    ['label' => 'Siparişim Nerede?', 'path' => '/order-tracking/'],
    ['label' => 'Sıkça Sorulan Sorular', 'path' => '/sikca-sorulan-sorular/'],
    ['label' => 'Üyelik Sözleşmesi', 'path' => '/uyelik-sozlesmesi/'],
    ['label' => 'Şartlar ve Koşullar', 'path' => '/sartlar-ve-kosullar/'],
    ['label' => 'Mesafeli Satış Sözleşmesi', 'path' => '/mesafeli-satis-sozlesmesi/'],
    ['label' => 'İptal ve İade Koşulları', 'path' => '/iptal-ve-iade-kosullari/'],
];
?>

<footer class="footer">
  <div class="container">
    <div class="footer-shell">
      <div class="footer-columns">
        <section class="footer-column footer-column--about">
          <h4><?php echo esc_html(get_bloginfo('name')); ?></h4>
          <p class="footer-copy">Polaris surf kurşunları ile balıkçılık deneyimini üst seviyeye taşı.</p>

          <div class="footer-socials" aria-label="<?php echo esc_attr__('Sosyal medya bağlantıları', 'polaris'); ?>">
            <a class="footer-social footer-social--facebook" href="<?php echo esc_url($facebook_url); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr__('Facebook', 'polaris'); ?>">
              <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
              <span>Facebook</span>
            </a>
            <a class="footer-social footer-social--instagram" href="<?php echo esc_url($instagram_url); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr__('Instagram', 'polaris'); ?>">
              <i class="fa-brands fa-instagram" aria-hidden="true"></i>
              <span>Instagram</span>
            </a>
            <a class="footer-social footer-social--youtube" href="<?php echo esc_url($youtube_url); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr__('YouTube', 'polaris'); ?>">
              <i class="fa-brands fa-youtube" aria-hidden="true"></i>
              <span>YouTube</span>
            </a>
            <a class="footer-social footer-social--whatsapp" href="<?php echo esc_url($whatsapp_footer_url); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr__('WhatsApp', 'polaris'); ?>">
              <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
              <span>WhatsApp</span>
            </a>
          </div>
        </section>

        <nav class="footer-column" aria-label="<?php echo esc_attr__('Kurumsal bağlantılar', 'polaris'); ?>">
          <h4>Kurumsal</h4>
          <ul class="footer-links">
            <?php foreach ($kurumsal_links as $link) : ?>
              <li>
                <a href="<?php echo esc_url(home_url($link['path'])); ?>"><?php echo esc_html($link['label']); ?></a>
              </li>
            <?php endforeach; ?>
          </ul>
        </nav>

        <nav class="footer-column" aria-label="<?php echo esc_attr__('Kullanıcı bağlantıları', 'polaris'); ?>">
          <h4>Kullanıcı</h4>
          <ul class="footer-links">
            <?php foreach ($kullanici_links as $link) : ?>
              <li>
                <a href="<?php echo esc_url(home_url($link['path'])); ?>"><?php echo esc_html($link['label']); ?></a>
              </li>
            <?php endforeach; ?>
          </ul>
        </nav>

        <section class="footer-column footer-column--etbis">
          <h4>ETBİS Doğrulama</h4>
          <a class="footer-etbis-link" href="https://etbis.ticaret.gov.tr/tr/Home/SearchSiteResult?siteId=21fecc45-af70-4a44-a765-d6b1c60e5438" target="_blank" rel="noopener">
            <img
              src="https://polariskursun.com/wp-content/uploads/2025/11/etbis.png"
              width="180"
              height="236"
              loading="lazy"
              alt="<?php echo esc_attr__('ETBİS doğrulama karekodu', 'polaris'); ?>"
            >
          </a>
          <a class="footer-etbis-text" href="https://etbis.ticaret.gov.tr/tr/Home/SearchSiteResult?siteId=21fecc45-af70-4a44-a765-d6b1c60e5438" target="_blank" rel="noopener">ETBİS'e Kayıtlıdır</a>

          <div class="footer-payments" aria-label="<?php echo esc_attr__('Ödeme yöntemleri', 'polaris'); ?>">
            <span class="footer-payment footer-payment--visa" title="VISA" aria-label="VISA">
              <i class="fa-brands fa-cc-visa" aria-hidden="true"></i>
            </span>
            <span class="footer-payment footer-payment--mastercard" title="Mastercard" aria-label="Mastercard">
              <i class="fa-brands fa-cc-mastercard" aria-hidden="true"></i>
            </span>
            <span class="footer-payment footer-payment--troy" title="TROY" aria-label="TROY">
              <span>TROY</span>
            </span>
            <span class="footer-payment footer-payment--amex" title="American Express" aria-label="American Express">
              <i class="fa-brands fa-cc-amex" aria-hidden="true"></i>
            </span>
          </div>
        </section>
      </div>

      <nav class="footer-quick-menu" aria-label="<?php echo esc_attr__('Hızlı menü', 'polaris'); ?>">
        <h4>Hızlı Menü</h4>
        <?php if (has_nav_menu('main_menu')) : ?>
          <?php
          wp_nav_menu([
              'theme_location' => 'main_menu',
              'container'      => false,
              'fallback_cb'    => '__return_false',
              'menu_class'     => 'footer-quick-menu__list',
              'depth'          => 1,
          ]);
          ?>
        <?php else : ?>
          <ul class="footer-quick-menu__list">
            <li><a href="<?php echo esc_url(home_url('/')); ?>">Anasayfa</a></li>
            <li><a href="<?php echo esc_url(home_url('/hakkimizda/')); ?>">Hakkımızda</a></li>
            <li><a href="<?php echo esc_url(home_url('/iletisim-2/')); ?>">İletişim</a></li>
            <li><a href="<?php echo esc_url(home_url('/sikca-sorulan-sorular/')); ?>">Sıkça Sorulan Sorular</a></li>
          </ul>
        <?php endif; ?>
      </nav>
    </div>

    <div class="footer-bottom">
      <span>&copy; <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?>. Tüm hakları saklıdır.</span>
      <span>Türkiye geneli hızlı teslimat ve güvenli ödeme altyapısı.</span>
    </div>
  </div>
</footer>

<?php get_template_part('template-parts/navigation/bottom-nav'); ?>

<?php wp_footer(); ?>
</body>
</html>
