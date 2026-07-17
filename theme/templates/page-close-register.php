<?php
/* Template Name: Close Register Page */
get_header();
?>
<section class="bg-primary text-white/80">
    <div class="container flex justify-center items-center min-h-[calc(100vh-100px)]">
        <div class="max-w-3xl text-center p-6 lg:p-10 mx-auto">
            <p>Ok but should not be page not found. Should say registration has closed... <br>
             please use your <a href="<?= wp_login_url(); ?>" class="underline hover:text-accent duration-200"> to login </a> to submit via our portal</p>
        </div>
    </div>
</section>
<?php get_footer(); ?>