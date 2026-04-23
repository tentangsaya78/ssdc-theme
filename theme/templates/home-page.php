
<?php 
/* Template Name: Home Page */
get_header();
$bg_image = get_template_directory_uri() . '/assets/bg-hero.svg';?>
<main style="background-image:  url('<?php echo esc_url($bg_image); ?>');" class="bg-no-repeat bg-top-right">
<?php
get_template_part( 'template-parts/home/hero' );
get_template_part( 'template-parts/home/about');
get_template_part( 'template-parts/home/video');
get_template_part( 'template-parts/home/judges');
get_template_part( 'template-parts/home/rule');
get_template_part( 'template-parts/home/sketchup');

?>
</main>
<?php
get_footer(); 
?>
