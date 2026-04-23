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