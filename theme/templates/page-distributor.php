<?php
/*
 * Template Name: Distributor Dashboard
 */

// Hanya administrator & editor yang boleh akses
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$user = wp_get_current_user();
$allowed_roles = ['administrator', 'editor'];
if (!array_intersect($allowed_roles, $user->roles)) {
    wp_redirect(home_url());
    exit;
}

get_header();
get_template_part('template-parts/distributor-dashboard');
get_footer();