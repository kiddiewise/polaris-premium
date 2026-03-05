<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<section class="container polaris-content">
  <h1><?php echo esc_html__('Page not found', 'polaris'); ?></h1>
  <p><?php echo esc_html__('The page you are looking for does not exist.', 'polaris'); ?></p>
  <a class="btn btn-primary" href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html__('Back to home', 'polaris'); ?></a>
</section>
<?php
get_footer();
