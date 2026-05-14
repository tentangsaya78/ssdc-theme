<?php
/**
 * ══════════════════════════════════════════════════════════════
 *  SSDC 2026 — AJAX Status Update Handler
 *  Tambahkan kode ini ke functions.php tema Anda (atau plugin).
 * ══════════════════════════════════════════════════════════════
 *
 *  Dipanggil oleh dashboard template (ssdc-participants-manager.php)
 *  via fetch() ke admin-ajax.php — TANPA full page reload,
 *  sehingga paginasi tetap di halaman yang sama setelah approve/reject.
 */

add_action('wp_ajax_ssdc_update_status', 'ssdc_handle_status_update');

function ssdc_handle_status_update() {

    // 1. Verifikasi nonce
    if ( ! check_ajax_referer( 'ssdc_editor_action', 'nonce', false ) ) {
        wp_send_json_error( [ 'message' => 'Invalid nonce. Refresh the page and try again.' ], 403 );
    }

    // 2. Cek kapabilitas user
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( [ 'message' => 'You do not have permission to perform this action.' ], 403 );
    }

    // 3. Ambil & validasi parameter
    $action = sanitize_text_field( $_POST['bulk_action'] ?? '' );
    $ids    = array_filter( array_map( 'intval', (array) ( $_POST['ids'] ?? [] ) ) );

    $status_map = [
        'approve' => 'publish',
        'reject'  => 'draft',
        'pending' => 'pending',
    ];

    if ( ! isset( $status_map[ $action ] ) ) {
        wp_send_json_error( [ 'message' => 'Invalid action: ' . $action ], 400 );
    }

    if ( empty( $ids ) ) {
        wp_send_json_error( [ 'message' => 'No IDs provided.' ], 400 );
    }

    // 4. Update setiap post
    $updated = 0;
    foreach ( $ids as $id ) {
        // Pastikan post type benar sebelum update
        if ( get_post_type( $id ) !== 'participants' ) {
            continue;
        }
        $result = wp_update_post( [
            'ID'          => $id,
            'post_status' => $status_map[ $action ],
        ] );
        if ( $result && ! is_wp_error( $result ) ) {
            $updated++;
        }
    }

    // 5. Kirim response
    wp_send_json_success( [
        'message' => "Updated {$updated} participant(s) to \"{$status_map[$action]}\".",
        'count'   => $updated,
        'action'  => $action,
        'status'  => $status_map[ $action ],
    ] );
}