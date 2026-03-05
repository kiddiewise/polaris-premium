<?php
// same fallback as root index.php
if (!defined('ABSPATH')) exit;
get_header();
?>
<div class="container">
<?php
if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        the_content();
    endwhile;
else :
    echo '<p>' . esc_html__("Hiç içerik yok.", 'polaris') . '</p>';
endif;
?>
</div>
<?php
get_footer();
