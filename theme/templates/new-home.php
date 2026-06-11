
<?php 
/* Template Name:New Home Page */
get_header();
$bg_image = get_template_directory_uri() . '/assets/bg-hero.svg';?>
<main>
<?php
get_template_part( 'template-parts/new-home/hero' );
get_template_part( 'template-parts/new-home/about' );
get_template_part( 'template-parts/new-home/video' );
get_template_part( 'template-parts/new-home/judges' );
get_template_part( 'template-parts/new-home/rules' );
//get_template_part( 'template-parts/new-home/cta' );
get_template_part( 'template-parts/new-home/sketchup' );
get_template_part( 'template-parts/new-home/supported');


?>
</main>
<?php
get_footer(); 
?>
