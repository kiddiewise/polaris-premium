<?php
if (!defined('ABSPATH')) {
    exit;
}

$whatsapp_message = __('Merhaba, sikca sorulan sorular sayfanizdan ulastim. Bilgi almak istiyorum.', 'polaris');
$whatsapp_url     = function_exists('polaris_get_whatsapp_url')
    ? polaris_get_whatsapp_url($whatsapp_message)
    : esc_url('https://wa.me/905462629002?text=' . rawurlencode($whatsapp_message));
$whatsapp_number  = function_exists('polaris_get_whatsapp_number')
    ? polaris_get_whatsapp_number()
    : '905462629002';
$whatsapp_display = '+' . ltrim((string) $whatsapp_number, '+');

$faq_sections = [
    [
        'title'  => __('Siparis ve odeme', 'polaris'),
        'kicker' => __('Siparis adimlari ve odeme secenekleri', 'polaris'),
        'icon'   => 'fa-solid fa-credit-card',
        'items'  => [
            [
                'question' => __('Siparisimi nasil verebilirim?', 'polaris'),
                'answer'   => __('Ana sayfada gormek istediginiz urune tiklayarak urun sayfasina gidin. Satin almak istediginiz adedi belirleyip Sepete Ekle secenegiyle urunleri sepetinize ekleyebilirsiniz. Urunleri sepete attiktan sonra Sepete Git sayfasindan toplam tutari ve kargo seceneklerini gorebilir, ardindan ekrandaki adimlari takip ederek siparisinizi tamamlayabilirsiniz.', 'polaris'),
            ],
            [
                'question' => __('Hangi odeme yontemlerini kabul ediyorsunuz?', 'polaris'),
                'answer'   => __('Sitemiz uzerinden yaptiginiz alisverislerde kredi karti ile odeme yapabilir veya hesabimiza EFT gonderebilirsiniz. Detaylar icin sitedeki Hesap Bilgileri bolumunu inceleyebilirsiniz.', 'polaris'),
            ],
        ],
    ],
    [
        'title'  => __('Kargo ve teslimat', 'polaris'),
        'kicker' => __('Kargo bedeli, surec ve teslimat bilgileri', 'polaris'),
        'icon'   => 'fa-solid fa-truck-fast',
        'items'  => [
            [
                'question' => __('Kargo ucreti ne kadar?', 'polaris'),
                'answer'   => __('1.000 TL ve uzeri alisverislerde kargo ucretsizdir. Bu tutarin altindaki siparislerde 150 TL kargo ucreti uygulanir.', 'polaris'),
            ],
            [
                'question' => __('Hangi kargo sirketiyle calisiyorsunuz?', 'polaris'),
                'answer'   => __('Siparislerin en guvenli ve hizli sekilde ulastirilmasi icin anlasmali kargo sirketimiz DHL Kargo dur.', 'polaris'),
            ],
            [
                'question' => __('Siparisim kac gunde kargoya verilir?', 'polaris'),
                'answer'   => __('Stoklar guncel tutuldugu icin siparisler mumkun olan en kisa surede kargoya verilir. Ancak uretim gerekebilecek durumlar veya siparis yogunlugu nedeniyle tedarik ve kargolama suresi 3-4 is gunune kadar uzayabilir. Siparisinizin guncel durumunu Siparislerim bolumunden takip edebilir veya bizimle iletisime gecebilirsiniz.', 'polaris'),
            ],
        ],
    ],
    [
        'title'  => __('Iade ve satis politikasi', 'polaris'),
        'kicker' => __('Iade kosullari ve satis politikasi', 'polaris'),
        'icon'   => 'fa-solid fa-rotate-left',
        'items'  => [
            [
                'question' => __('Urun iadesi nasil yapilir?', 'polaris'),
                'answer'   => __('Sitemizden aldiginiz Polaris markali tum urunleri kosulsuz olarak iade edebilirsiniz. Diger marka urunlerde ise Tuketicinin Korunmasi Hakkinda Kanun kapsaminda belirtilen sartlari tasiyan iade talepleri icin basvuruda bulunabilirsiniz. Ayrintilar icin Iptal ve Iade Kosullari bolumunu inceleyebilirsiniz.', 'polaris'),
            ],
            [
                'question' => __('Toptan satisiniz var mi?', 'polaris'),
                'answer'   => __('Hayir, Polaris kursun urunlerinde toptan satis bulunmamaktadir.', 'polaris'),
            ],
        ],
    ],
    [
        'title'  => __('Urunler hakkinda', 'polaris'),
        'kicker' => __('Polaris urun aileleri ve renk secenekleri', 'polaris'),
        'icon'   => 'fa-solid fa-fish',
        'items'  => [
            [
                'question' => __('Polaris marka kursunlari neden tercih etmeliyim?', 'polaris'),
                'answer'   => __('Polaris kursunlari av verimliligi, gorunurluk ve cevresel etki acisindan avantaj sunar. Kaplama ve aerodinamik yapi atis performansini destekler. Renkli ve glowlu seriler su altinda gorunurlugu artirarak hem baliklarin dikkatini ceker hem de su altinda kalan urunlerin daha kolay fark edilmesini saglar. Glowlu urunlerde kullanimdan once beyaz veya tercihen UV isik tutulmasi daha parlak ve daha uzun sureli gorunurluk saglar. Ayrica kaplama, ciplak kursunun suyla dogrudan temasini azaltarak cevresel etkiyi ve kullanici temas riskini dusurmeye yardimci olur.', 'polaris'),
            ],
            [
                'question' => __('Urunlerinizde renk tercihi yapabiliyor muyuz?', 'polaris'),
                'answer'   => __('Glowlu fosforlu serilerde renk alternatifi yoktur; urunler griye yakin beyaz renktedir. Renkli serilerde ise stok durumuna gore UV sari, turuncu, yesil, pembe ve diger renkler karisik gonderilir. Mesaj yoluyla renk talebinizi iletebilirsiniz; stok uygunsa yardimci olunur.', 'polaris'),
            ],
        ],
    ],
    [
        'title'  => __('Yardim ve iletisim', 'polaris'),
        'kicker' => __('Sorunuzun cevabi burada yoksa hizli destek alin', 'polaris'),
        'icon'   => 'fa-solid fa-headset',
        'items'  => [
            [
                'question' => __('Aradigim sorunun cevabini bulamazsam ne yapabilirim?', 'polaris'),
                'answer'   => __('Aradiginiz bilgi bu sayfada yer almiyorsa bizimle iletisime gecebilirsiniz. Size yardimci olmaktan memnuniyet duyariz.', 'polaris'),
            ],
        ],
    ],
];

get_header();
?>

<section class="polaris-content polaris-faq-page">
  <div class="container">
    <header class="polaris-faq-hero polaris-surface fade-up active">
      <div class="polaris-faq-hero__copy">
        <p class="polaris-faq-kicker"><?php echo esc_html__('Yardim merkezi', 'polaris'); ?></p>
        <h1><?php echo esc_html__('Sikca Sorulan Sorular', 'polaris'); ?></h1>
        <p class="polaris-faq-subtitle"><?php echo esc_html__('Siparis, odeme, kargo, iade ve urun detaylariyla ilgili en cok sorulan konulari tek ekranda toparladik.', 'polaris'); ?></p>
        <div class="polaris-faq-tags" aria-hidden="true">
          <span><?php echo esc_html__('Siparis', 'polaris'); ?></span>
          <span><?php echo esc_html__('Kargo', 'polaris'); ?></span>
          <span><?php echo esc_html__('Iade', 'polaris'); ?></span>
          <span><?php echo esc_html__('Urun Bilgisi', 'polaris'); ?></span>
        </div>
      </div>
      <a class="btn btn-primary polaris-faq-hero__cta" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener">
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
        <?php echo esc_html__('Bilgi almak istiyorum', 'polaris'); ?>
      </a>
    </header>

    <div class="polaris-faq-layout">
      <div class="polaris-faq-main">
        <?php foreach ($faq_sections as $section) : ?>
          <article class="polaris-faq-block polaris-surface fade-up active">
            <header class="polaris-faq-block__head">
              <span class="polaris-faq-block__icon" aria-hidden="true"><i class="<?php echo esc_attr($section['icon']); ?>"></i></span>
              <div>
                <h2><?php echo esc_html($section['title']); ?></h2>
                <p><?php echo esc_html($section['kicker']); ?></p>
              </div>
            </header>

            <div class="polaris-faq-items">
              <?php foreach ($section['items'] as $item_index => $item) : ?>
                <details class="polaris-faq-item"<?php echo 0 === $item_index ? ' open' : ''; ?>>
                  <summary>
                    <span class="polaris-faq-item__q"><?php echo esc_html($item['question']); ?></span>
                    <span class="polaris-faq-item__toggle" aria-hidden="true"><i class="fa-solid fa-plus"></i></span>
                  </summary>
                  <div class="polaris-faq-item__a">
                    <p><?php echo esc_html($item['answer']); ?></p>
                  </div>
                </details>
              <?php endforeach; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <aside class="polaris-faq-aside">
        <article class="polaris-faq-contact polaris-surface fade-up active">
          <h3><?php echo esc_html__('Hizli destek', 'polaris'); ?></h3>
          <p><?php echo esc_html__('Destek ekibimize WhatsApp uzerinden hizli sekilde ulasabilirsiniz.', 'polaris'); ?></p>
          <a class="btn btn-primary" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener">
            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
            <?php echo esc_html__('WhatsApp Destek Hatti', 'polaris'); ?>
          </a>
          <span class="polaris-faq-contact__number"><?php echo esc_html($whatsapp_display); ?></span>
        </article>

        <article class="polaris-faq-note polaris-surface fade-up active">
          <h3><?php echo esc_html__('Kisa notlar', 'polaris'); ?></h3>
          <ul>
            <li><?php echo esc_html__('1.000 TL ve uzeri siparislerde kargo ucretsizdir.', 'polaris'); ?></li>
            <li><?php echo esc_html__('Siparis yogunluguna gore kargoya verilis suresi 3-4 is gunune uzayabilir.', 'polaris'); ?></li>
            <li><?php echo esc_html__('Renk talepleri mesaj ile iletilebilir; stok uygunsa destek saglanir.', 'polaris'); ?></li>
          </ul>
        </article>
      </aside>
    </div>
  </div>
</section>

<?php
get_footer();
