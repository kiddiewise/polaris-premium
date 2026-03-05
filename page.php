<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<section class="container polaris-content">
  <?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
      <article <?php post_class(); ?>>
        <h1><?php the_title(); ?></h1>
        <?php the_content(); ?>
      </article>
    <?php endwhile; ?>
  <?php endif; ?>
</section>
<?php
get_footer();
