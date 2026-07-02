<?php
add_action('admin_post_ssdc_submit_participant', 'ssdc_handle_participant_submission');

function ssdc_handle_participant_submission() {

    if (!is_user_logged_in()) {
        wp_die('Anda harus login terlebih dahulu.');
    }

    if (!wp_verify_nonce($_POST['submission_nonce'] ?? '', 'submit_participant_action')) {
        wp_die('Sesi tidak valid, silakan reload halaman.');
    }

    $current_user = wp_get_current_user();
    $user_id      = $current_user->ID;
    $redirect_to  = !empty($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : home_url();

    $data      = ssdc_get_participant_submission($user_id);
    $post_id   = $data['post_id'];
    $is_edit   = $data['is_edit'];
    $is_locked = $data['is_locked'];

    if ($is_locked) {
        wp_redirect(add_query_arg('sub_error', 'locked', $redirect_to));
        exit;
    }

    $link_input    = isset($_POST['submission_link']) ? trim($_POST['submission_link']) : '';
    $file_uploaded = !empty($_FILES['submission_file']['name']);

    if (empty($link_input) && !$file_uploaded) {
        wp_redirect(add_query_arg('sub_error', 'empty', $redirect_to));
        exit;
    }

    if (!empty($link_input) && !filter_var($link_input, FILTER_VALIDATE_URL)) {
        wp_redirect(add_query_arg('sub_error', 'invalid_url', $redirect_to));
        exit;
    }

    // Buat atau update post
    if (!$is_edit) {
        $post_id = wp_insert_post([
            'post_type'   => 'participants',
            'post_title'  => $current_user->display_name . ' - Submission',
            'post_status' => 'pending',
            'post_author' => $user_id,
        ]);
    } else {
        wp_update_post([
            'ID'          => $post_id,
            'post_status' => 'pending',
        ]);
    }

    if (!$post_id || is_wp_error($post_id)) {
        wp_redirect(add_query_arg('sub_error', 'save_failed', $redirect_to));
        exit;
    }

    // Simpan link
    if (!empty($link_input)) {
        update_post_meta($post_id, 'submission_link', esc_url_raw($link_input));
    } else {
        delete_post_meta($post_id, 'submission_link');
    }

    // Simpan file
    if ($file_uploaded) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $allowed_types = ['pdf', 'doc', 'docx', 'zip', 'rar', 'jpg', 'jpeg', 'png'];
        $file_ext = strtolower(pathinfo($_FILES['submission_file']['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            wp_redirect(add_query_arg('sub_error', 'invalid_type', $redirect_to));
            exit;
        }

        $attachment_id = media_handle_upload('submission_file', $post_id);

        if (is_wp_error($attachment_id)) {
            wp_redirect(add_query_arg('sub_error', 'upload_failed', $redirect_to));
            exit;
        }

        $file_url = wp_get_attachment_url($attachment_id);
        update_post_meta($post_id, 'submission_file', $file_url);
        update_post_meta($post_id, 'submission_attachment_id', $attachment_id);
    }

    wp_redirect(add_query_arg('submitted', '1', $redirect_to));
    exit;
}