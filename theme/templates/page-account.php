<?php
/*
 * Template Name: Account - My Participants
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// get_header();
wp_head(  );
get_template_part('template-parts/account-participants');
// get_footer();
wp_footer(  );
