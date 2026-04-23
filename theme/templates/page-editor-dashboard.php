<?php
/*
 * Template Name: Editor Dashboard
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$user = wp_get_current_user();
if (!in_array('editor', $user->roles) && !in_array('administrator', $user->roles)) {
    wp_redirect(home_url());
    exit;
}

// get_header();
wp_head(  );
get_template_part('template-parts/editor-dashboard');
wp_footer(  );