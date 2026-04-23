<?php
/*
 * Template Name: Participant Dashboard
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$user = wp_get_current_user();
if (!in_array('contributor', $user->roles) && !in_array('administrator', $user->roles)) {
    wp_redirect(home_url());
    exit;
}

wp_head();
get_template_part('template-parts/participant-dashboard');
wp_footer();