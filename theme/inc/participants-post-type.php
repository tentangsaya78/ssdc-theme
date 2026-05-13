<?php
add_action('init', function () {
    register_post_type('participants', [
        'labels' => [
            'name'          => 'Participants',
            'singular_name' => 'Participant',
        ],
        'public'              => false,
        'publicly_queryable'  => true,  // agar bisa di-query
        'show_ui'             => true,
        'show_in_menu'        => true,
        'supports'            => ['title', 'author'],
        'menu_icon'           => 'dashicons-groups',
        'capability_type'     => 'participant',
        'map_meta_cap'        => true,
        'capabilities'        => [
            // Kemampuan yang dibutuhkan
            'edit_post'              => 'edit_participant',
            'read_post'              => 'read_participant',
            'delete_post'            => 'delete_participant',
            'edit_posts'             => 'edit_participants',
            'edit_others_posts'      => 'edit_others_participants',
            'publish_posts'          => 'publish_participants',
            'read_private_posts'     => 'read_private_participants',
            'delete_posts'           => 'delete_participants',
            'delete_private_posts'   => 'delete_private_participants',
            'delete_published_posts' => 'delete_published_participants',
            'delete_others_posts'    => 'delete_others_participants',
            'edit_private_posts'     => 'edit_private_participants',
            'edit_published_posts'   => 'edit_published_participants',
            'create_posts'           => 'create_participants',
        ],
    ]);
});


/**
 * Assign capabilities ke role
 * Jalankan SEKALI saat theme activate, atau bisa pakai flush di admin_init
 */
function ssdc_assign_participant_caps() {
    $roles_caps = [
        // Administrator — full access
        'administrator' => [
            'edit_participant'              => true,
            'read_participant'              => true,
            'delete_participant'            => true,
            'edit_participants'             => true,
            'edit_others_participants'      => true,
            'publish_participants'          => true,
            'read_private_participants'     => true,
            'delete_participants'           => true,
            'delete_private_participants'   => true,
            'delete_published_participants' => true,
            'delete_others_participants'    => true,
            'edit_private_participants'     => true,
            'edit_published_participants'   => true,
            'create_participants'           => true,
        ],
        // Editor — bisa lihat & edit semua, tidak bisa delete
        'editor' => [
            'edit_participant'              => true,
            'read_participant'              => true,
            'delete_participant'            => false,
            'edit_participants'             => true,
            'edit_others_participants'      => true,
            'publish_participants'          => true,
            'read_private_participants'     => true,
            'delete_participants'           => false,
            'delete_others_participants'    => false,
            'edit_private_participants'     => true,
            'edit_published_participants'   => true,
            'create_participants'           => true,
        ],
        // Contributor — hanya bisa buat & lihat miliknya sendiri
        'contributor' => [
            'edit_participant'              => true,
            'read_participant'              => true,
            'delete_participant'            => false,
            'edit_participants'             => true,
            'edit_others_participants'      => false,
            'publish_participants'          => false,
            'read_private_participants'     => false,
            'delete_participants'           => false,
            'delete_others_participants'    => false,
            'edit_private_participants'     => false,
            'edit_published_participants'   => true,
            'create_participants'           => true,
        ],
    ];

    foreach ($roles_caps as $role_name => $caps) {
        $role = get_role($role_name);
        if (!$role) continue;
        foreach ($caps as $cap => $grant) {
            if ($grant) $role->add_cap($cap);
            else        $role->remove_cap($cap);
        }
    }
}

// Jalankan sekali saat theme loaded
add_action('after_switch_theme', 'ssdc_assign_participant_caps');

// Fallback: jalankan juga di admin_init jika caps belum ada
add_action('admin_init', function () {
    $admin = get_role('administrator');
    if ($admin && !$admin->has_cap('edit_participants')) {
        ssdc_assign_participant_caps();
    }
});


/**
 * Filter query: contributor hanya lihat post miliknya sendiri
 */
add_action('pre_get_posts', function ($query) {
    if (!is_admin()) return;
    if (!$query->is_main_query()) return;
    if ($query->get('post_type') !== 'participants') return;

    $user = wp_get_current_user();

    // Contributor hanya lihat miliknya
    if (in_array('contributor', $user->roles) && !current_user_can('edit_others_participants')) {
        $query->set('author', $user->ID);
    }
});


/**
 * Filter query di frontend (template-parts)
 * Contributor hanya bisa ambil post miliknya sendiri
 */
add_filter('posts_where', function ($where, $query) {
    if (is_admin()) return $where;
    if ($query->get('post_type') !== 'participants') return $where;

    $user = wp_get_current_user();

    if (in_array('contributor', $user->roles)) {
        global $wpdb;
        $where .= $wpdb->prepare(
            " AND {$wpdb->posts}.post_author = %d",
            $user->ID
        );
    }

    return $where;
}, 10, 2);



add_filter('login_redirect', function ($redirect_to, $request, $user) {
    if (!isset($user->roles)) return $redirect_to;
    if (in_array('contributor', $user->roles))
        return get_permalink(get_page_by_path('participant-dashboard'));
    if (in_array('editor', $user->roles))
        return get_permalink(get_page_by_path('editor-dashboard'));
    return $redirect_to;
}, 10, 3);


// =================================
// TAMBAHAN UNTUK TAMPIL DI ADMIN
//=========================================

// ── Register meta box untuk CPT Participants
add_action('add_meta_boxes', function () {
    add_meta_box(
        'participants_detail',
        'Participant Detail',
        'participants_meta_box_cb',
        'participants',
        'normal',
        'high'
    );
});

function participants_meta_box_cb($post) {
    $fields = [
        'Team Info' => [
            'team_name'          => 'Team Name',
            'category'           => 'Category',
            'institution_name'   => 'Institution',
            'faculty'            => 'Faculty',
            'country'            => 'Country',
            'region'             => 'Region',
        ],
        'Head of Team' => [
            'head_name'          => 'Name',
            'head_email'         => 'Email',
            'head_phone'         => 'Phone',
            'head_semester'      => 'Semester',
            'head_year'          => 'Admission Year',
        ],
        'Member 1' => [
            'm1_name'            => 'Name',
            'm1_email'           => 'Email',
            'm1_phone'           => 'Phone',
            'm1_semester'        => 'Semester',
            'm1_year'            => 'Admission Year',
        ],
        'Member 2' => [
            'm2_name'            => 'Name',
            'm2_email'           => 'Email',
            'm2_phone'           => 'Phone',
            'm2_semester'        => 'Semester',
            'm2_year'            => 'Admission Year',
        ],
        'Lecturer / Mentor' => [
            'lect_name'          => 'Name',
            'lect_title'         => 'Title',
            'lect_email'         => 'Email',
        ],
        'Submission' => [
            'submission_link'     => 'Link',
            'submission_file_url' => 'File URL',
        ],
    ];

    wp_nonce_field('participants_meta_save', 'participants_meta_nonce');

    foreach ($fields as $group => $items) {
        echo '<h4 style="margin:16px 0 6px;border-bottom:1px solid #ddd;padding-bottom:4px">' . esc_html($group) . '</h4>';
        echo '<table class="form-table" style="margin:0">';
        foreach ($items as $key => $label) {
            $value = get_post_meta($post->ID, $key, true);
            echo '<tr>';
            echo '<th style="width:160px"><label for="' . esc_attr($key) . '">' . esc_html($label) . '</label></th>';
            echo '<td><input type="text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="regular-text" /></td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}

// ── Save meta box
add_action('save_post_participants', function ($post_id) {
    if (
        !isset($_POST['participants_meta_nonce']) ||
        !wp_verify_nonce($_POST['participants_meta_nonce'], 'participants_meta_save') ||
        defined('DOING_AUTOSAVE') && DOING_AUTOSAVE
    ) return;

    $keys = [
        'team_name', 'category', 'institution_name', 'faculty', 'country', 'region',
        'head_name', 'head_email', 'head_phone', 'head_semester', 'head_year',
        'm1_name', 'm1_email', 'm1_phone', 'm1_semester', 'm1_year',
        'm2_name', 'm2_email', 'm2_phone', 'm2_semester', 'm2_year',
        'lect_name', 'lect_title', 'lect_email',
        'submission_link', 'submission_file_url',
    ];

    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
        }
    }
});