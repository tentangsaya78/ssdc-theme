<?php

/**
 * SSDC 2026 — Participants Management Dashboard
 * Template: ssdc-participants-manager.php
 *
 * Perbaikan:
 * - Status update via AJAX (wp_ajax) → tidak ada full page reload
 * - Paginasi tidak pernah reset ke halaman 1 saat approve/reject
 * - Bulk action juga via AJAX
 */

$current_user = wp_get_current_user();

// ── Handle AJAX status update ───────────────────────────────────────────────
// Daftarkan handler ini di functions.php tema / plugin Anda:
//
//   add_action('wp_ajax_ssdc_update_status', 'ssdc_handle_status_update');
//   function ssdc_handle_status_update() {
//       if (!check_ajax_referer('ssdc_editor_action', 'nonce', false)) {
//           wp_send_json_error(['message' => 'Invalid nonce'], 403);
//       }
//       if (!current_user_can('edit_posts')) {
//           wp_send_json_error(['message' => 'Unauthorized'], 403);
//       }
//       $action  = sanitize_text_field($_POST['bulk_action'] ?? '');
//       $ids     = array_map('intval', (array)($_POST['ids'] ?? []));
//       $map     = ['approve' => 'publish', 'reject' => 'draft', 'pending' => 'pending'];
//       if (!isset($map[$action]) || empty($ids)) {
//           wp_send_json_error(['message' => 'Invalid action or empty IDs'], 400);
//       }
//       foreach ($ids as $id) {
//           wp_update_post(['ID' => $id, 'post_status' => $map[$action]]);
//       }
//       wp_send_json_success(['message' => 'Updated', 'count' => count($ids)]);
//   }
// ────────────────────────────────────────────────────────────────────────────

// ── Fetch all participants
$all_participants = new WP_Query([
    'post_type'      => 'participants',
    'posts_per_page' => -1,
    'post_status'    => ['publish', 'pending', 'draft'],
    'orderby'        => 'date',
    'order'          => 'DESC',
]);

// ── Build data array
$rows = [];
if ($all_participants->have_posts()) :
    while ($all_participants->have_posts()) : $all_participants->the_post();
        $pid    = get_the_ID();
        $author = get_userdata(get_the_author_meta('ID'));
        $rows[] = [
            'post_id'      => $pid,
            'status'       => get_post_status($pid),
            'date'         => get_the_date('Y-m-d'),
            'date_fmt'     => get_the_date('d M Y'),
            'team_name'    => get_post_meta($pid, 'team_name',           true),
            'institution'  => get_post_meta($pid, 'institution_name',    true),
            'country'      => get_post_meta($pid, 'country',             true),
            'region'       => get_post_meta($pid, 'region',              true),
            'category'     => get_post_meta($pid, 'category',            true),
            'head_name'    => get_post_meta($pid, 'head_name',           true),
            'head_email'   => get_post_meta($pid, 'head_email',          true),
            'head_phone'   => get_post_meta($pid, 'head_phone',          true),
            'head_sem'     => get_post_meta($pid, 'head_semester',       true),
            'head_year'    => get_post_meta($pid, 'head_year',           true),
            'm1_name'      => get_post_meta($pid, 'm1_name',             true),
            'm1_email'     => get_post_meta($pid, 'm1_email',            true),
            'm1_phone'     => get_post_meta($pid, 'm1_phone',            true),
            'm1_sem'       => get_post_meta($pid, 'm1_semester',         true),
            'm1_year'      => get_post_meta($pid, 'm1_year',             true),
            'm2_name'      => get_post_meta($pid, 'm2_name',             true),
            'm2_email'     => get_post_meta($pid, 'm2_email',            true),
            'm2_phone'     => get_post_meta($pid, 'm2_phone',            true),
            'm2_sem'       => get_post_meta($pid, 'm2_semester',         true),
            'm2_year'      => get_post_meta($pid, 'm2_year',             true),
            'lect_name'    => get_post_meta($pid, 'lect_name',           true),
            'lect_email'   => get_post_meta($pid, 'lect_email',          true),
            'lect_title'   => get_post_meta($pid, 'lect_title',          true),
            'faculty'      => get_post_meta($pid, 'faculty',             true),
            'sub_link'     => get_post_meta($pid, 'submission_link',     true),
            'sub_file'     => get_post_meta($pid, 'submission_file_url', true),
            'author_name'  => $author ? $author->display_name : '—',
            'author_email' => $author ? $author->user_email   : '—',
        ];
    endwhile;
    wp_reset_postdata();
endif;

// Stats
$total   = count($rows);
$pending = count(array_filter($rows, fn($r) => $r['status'] === 'pending'));
$publish = count(array_filter($rows, fn($r) => $r['status'] === 'publish'));
$draft   = count(array_filter($rows, fn($r) => $r['status'] === 'draft'));
?>

<div
    class="min-h-screen bg-light"
    x-data="editorDash()"
    x-init="init()">

    <!-- ── Topbar ── -->
    <div class="bg-white px-6 py-4 flex items-center justify-between shadow">
        <div class="flex items-center gap-3">
            <?php if (has_custom_logo()) the_custom_logo(); ?>
            <div>
                <p class="text-[10px] uppercase tracking-widest opacity-50">SSDC 2026</p>
                <h1 class="text-base font-semibold leading-tight">Participants Management</h1>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex gap-2 items-center">
                <i class="bi bi-person-circle"></i>
                <span class="text-sm opacity-60 hidden md:block"><?php echo esc_html($current_user->display_name); ?></span>
            </div>
            <a href="<?php echo wp_logout_url(home_url()); ?>"
                class="text-xs border border-white/20 px-3 py-1.5 rounded-full hover:bg-white/10 transition">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
            <div class="rounded-full pl-3 bg-white border border-secondary/20 flex gap-2 items-center">
                <i class="bi bi-translate text-primary"></i>
                <span class="p-2 rounded-full bg-primary text-white text-sm"><?php echo do_shortcode('[gtranslate]'); ?></span>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">

        <!-- ── Toast Notification ── -->
        <div
            x-show="toast.show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            :class="toast.type === 'success'
                ? 'bg-green-50 border-green-200 text-green-700'
                : 'bg-red-50 border-red-200 text-red-700'"
            class="mb-6 border rounded-2xl px-5 py-3 flex items-center gap-2 text-sm">
            <i class="bi" :class="toast.type === 'success' ? 'bi-check-circle' : 'bi-x-circle'"></i>
            <span x-text="toast.message"></span>
        </div>

        <!-- ── Stats ── -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <?php foreach (
                [
                    ['Total',        $total,   'text-primary',   '',         ''],
                    ['Under Review', $pending, 'text-accent',    'pending',  'bg-accent/5 border-accent/10'],
                    ['Approved',     $publish, 'text-green-600', 'publish',  'bg-green-50 border-green-100'],
                    ['Rejected',     $draft,   'text-secondary', 'draft',    ''],
                ] as [$label, $val, $color, $filter, $extra]
            ) : ?>
                <div
                    class="bg-white rounded-2xl p-5 shadow-sm border border-secondary/10 cursor-pointer hover:shadow-md transition <?php echo $extra; ?>"
                    @click="filterStatus = '<?php echo $filter; ?>'; page = 1;">
                    <p class="text-xs uppercase tracking-widest text-secondary mb-1"><?php echo $label; ?></p>
                    <p class="text-4xl font-bold <?php echo $color; ?>"
                        x-text="<?php echo $filter ? "stats.{$filter}" : 'stats.total'; ?>">
                        <?php echo $val; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Toolbar ── -->
        <div class="bg-white rounded-2xl shadow-sm border border-secondary/10 p-4 mb-4 flex flex-wrap items-center gap-3">

            <!-- Search -->
            <div class="relative flex-1 min-w-48">
                <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-secondary/40 text-sm"></i>
                <input type="text" x-model="search"
                    placeholder="Search team, name, country, institution..."
                    class="w-full bg-light border-0 rounded-full pl-10 pr-4 py-2.5 text-sm text-primary placeholder-secondary/40 focus:outline-none focus:ring-2 focus:ring-primary/20" />
            </div>

            <!-- Filter Status -->
            <div class="relative">
                <select x-model="filterStatus"
                    class="bg-light border-0 rounded-full px-4 py-2.5 text-sm text-primary appearance-none pr-9 focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="">All Status</option>
                    <option value="pending">Under Review</option>
                    <option value="publish">Approved</option>
                    <option value="draft">Rejected</option>
                </select>
                <i class="bi bi-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-secondary/50 text-xs pointer-events-none"></i>
            </div>

            <!-- Filter Region -->
            <div class="relative">
                <select x-model="filterRegion"
                    class="bg-light border-0 rounded-full px-4 py-2.5 text-sm text-primary appearance-none pr-9 focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="">All Regions</option>
                    <option value="north_asia">North Asia</option>
                    <option value="saarc">SAARC</option>
                    <option value="anz">ANZ</option>
                    <option value="asean">ASEAN</option>
                </select>
                <i class="bi bi-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-secondary/50 text-xs pointer-events-none"></i>
            </div>

            <!-- Bulk Action -->
            <div class="flex items-center gap-2">
                <div class="relative">
                    <select x-model="bulkAction"
                        class="bg-light border border-secondary/20 rounded-full px-4 py-2.5 text-sm text-primary appearance-none pr-9 focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="">Bulk Action</option>
                        <option value="approve">Approve</option>
                        <option value="reject">Reject</option>
                        <option value="pending">Set to Review</option>
                    </select>
                    <i class="bi bi-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-secondary/50 text-xs pointer-events-none"></i>
                </div>
                <button type="button"
                    @click="submitBulk()"
                    :disabled="!selected.length || !bulkAction || loading"
                    :class="selected.length && bulkAction && !loading
                        ? 'bg-primary text-white hover:bg-primary/80'
                        : 'bg-secondary/10 text-secondary/40 cursor-not-allowed'"
                    class="px-4 py-2.5 rounded-full text-sm font-medium transition flex items-center gap-1.5">
                    <span x-show="loading" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    Apply
                    <span x-show="selected.length" class="ml-1 bg-white/20 text-xs px-1.5 py-0.5 rounded-full" x-text="selected.length"></span>
                </button>
            </div>

            <!-- Export -->
            <div class="flex gap-2 ml-auto">
                <button @click="exportCSV()"
                    class="flex items-center gap-1.5 border border-secondary/20 text-secondary hover:text-primary hover:border-primary/30 px-4 py-2.5 rounded-full text-sm transition">
                    <i class="bi bi-filetype-csv"></i> CSV
                </button>
                <button @click="exportPDF()"
                    class="flex items-center gap-1.5 bg-primary text-white hover:bg-primary/80 px-4 py-2.5 rounded-full text-sm transition">
                    <i class="bi bi-filetype-pdf"></i> PDF
                </button>
            </div>
        </div>

        <!-- ── Table ── -->
        <div class="bg-white rounded-2xl shadow-sm border border-secondary/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-light border-b border-secondary/10">
                            <th class="px-4 py-3.5 w-10">
                                <input type="checkbox"
                                    @change="e => e.target.checked ? selectAll() : clearAll()"
                                    :checked="selected.length === filtered.length && filtered.length > 0"
                                    class="rounded accent-primary cursor-pointer" />
                            </th>
                            <th class="text-left px-4 py-3.5 text-xs uppercase tracking-wider text-secondary font-medium">#</th>
                            <th class="text-left px-4 py-3.5 text-xs uppercase tracking-wider text-secondary font-medium cursor-pointer hover:text-primary select-none"
                                @click="sortBy('team_name')">
                                Team
                                <i class="bi ml-1" :class="sort.field==='team_name'?(sort.asc?'bi-sort-up':'bi-sort-down'):'bi-chevron-expand'"></i>
                            </th>
                            <th class="text-left px-4 py-3.5 text-xs uppercase tracking-wider text-secondary font-medium">Head of Team</th>
                            <th class="text-left px-4 py-3.5 text-xs uppercase tracking-wider text-secondary font-medium cursor-pointer hover:text-primary select-none"
                                @click="sortBy('country')">
                                Country
                                <i class="bi ml-1" :class="sort.field==='country'?(sort.asc?'bi-sort-up':'bi-sort-down'):'bi-chevron-expand'"></i>
                            </th>
                            <th class="text-left px-4 py-3.5 text-xs uppercase tracking-wider text-secondary font-medium">Institution</th>
                            <th class="text-left px-4 py-3.5 text-xs uppercase tracking-wider text-secondary font-medium cursor-pointer hover:text-primary select-none"
                                @click="sortBy('date')">
                                Date
                                <i class="bi ml-1" :class="sort.field==='date'?(sort.asc?'bi-sort-up':'bi-sort-down'):'bi-chevron-expand'"></i>
                            </th>
                            <th class="text-left px-4 py-3.5 text-xs uppercase tracking-wider text-secondary font-medium">Status</th>
                            <th class="text-left px-4 py-3.5 text-xs uppercase tracking-wider text-secondary font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, i) in paginated" :key="row.post_id">
                            <tr class="border-b border-secondary/5 hover:bg-light/60 transition"
                                :class="selected.includes(row.post_id) ? 'bg-primary/5' : ''">

                                <td class="px-4 py-4">
                                    <input type="checkbox"
                                        :value="row.post_id"
                                        :checked="selected.includes(row.post_id)"
                                        @change="toggleSelect(row.post_id)"
                                        class="rounded accent-primary cursor-pointer" />
                                </td>

                                <td class="px-4 py-4 text-xs text-secondary/50"
                                    x-text="(page - 1) * perPage + i + 1"></td>

                                <td class="px-4 py-4 cursor-pointer" @click="openDetail(row)">
                                    <p class="font-semibold text-primary hover:underline" x-text="row.team_name || '—'"></p>
                                    <p class="text-xs text-secondary" x-text="row.author_email"></p>
                                </td>

                                <td class="px-4 py-4">
                                    <p class="text-primary" x-text="row.head_name || '—'"></p>
                                    <p class="text-xs text-secondary" x-text="row.head_phone || ''"></p>
                                </td>

                                <td class="px-4 py-4 text-primary" x-text="row.country || '—'"></td>

                                <td class="px-4 py-4 max-w-[150px]">
                                    <p class="text-primary truncate" x-text="row.institution || '—'"></p>
                                    <p class="text-xs text-secondary" x-text="row.region || ''"></p>
                                </td>

                                <td class="px-4 py-4 text-xs text-secondary" x-text="row.date_fmt"></td>

                                <!-- Status Badge -->
                                <td class="px-4 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap"
                                        :class="{
                                            'bg-yellow-100 text-yellow-700': row.status === 'pending',
                                            'bg-green-100 text-green-700':  row.status === 'publish',
                                            'bg-secondary/10 text-secondary': row.status === 'draft',
                                        }"
                                        x-text="{ pending:'Under Review', publish:'Approved', draft:'Rejected' }[row.status] || row.status">
                                    </span>
                                </td>

                                <!-- Quick Actions — semua via AJAX, TANPA form POST -->
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-1.5">

                                        <!-- View -->
                                        <button type="button"
                                            @click="openDetail(row)"
                                            class="w-7 h-7 rounded-full bg-light hover:bg-secondary/10 flex items-center justify-center text-secondary hover:text-primary transition"
                                            title="View Detail">
                                            <i class="bi bi-eye text-xs"></i>
                                        </button>

                                        <!-- Approve -->
                                        <button type="button"
                                            x-show="row.status !== 'publish'"
                                            @click="quickAction(row, 'approve')"
                                            :disabled="row._loading"
                                            class="w-7 h-7 rounded-full bg-green-50 hover:bg-green-100 flex items-center justify-center text-green-600 transition disabled:opacity-40"
                                            title="Approve">
                                            <span x-show="row._loading" class="w-3 h-3 border-2 border-green-300 border-t-green-600 rounded-full animate-spin"></span>
                                            <i x-show="!row._loading" class="bi bi-check text-sm font-bold"></i>
                                        </button>

                                        <!-- Reject -->
                                        <button type="button"
                                            x-show="row.status !== 'draft'"
                                            @click="quickAction(row, 'reject')"
                                            :disabled="row._loading"
                                            class="w-7 h-7 rounded-full bg-red-50 hover:bg-red-100 flex items-center justify-center text-red-400 transition disabled:opacity-40"
                                            title="Reject">
                                            <span x-show="row._loading" class="w-3 h-3 border-2 border-red-200 border-t-red-400 rounded-full animate-spin"></span>
                                            <i x-show="!row._loading" class="bi bi-x text-sm font-bold"></i>
                                        </button>

                                        <!-- Set Pending -->
                                        <button type="button"
                                            x-show="row.status !== 'pending'"
                                            @click="quickAction(row, 'pending')"
                                            :disabled="row._loading"
                                            class="w-7 h-7 rounded-full bg-yellow-50 hover:bg-yellow-100 flex items-center justify-center text-yellow-600 transition disabled:opacity-40"
                                            title="Set to Review">
                                            <span x-show="row._loading" class="w-3 h-3 border-2 border-yellow-200 border-t-yellow-600 rounded-full animate-spin"></span>
                                            <i x-show="!row._loading" class="bi bi-clock text-xs"></i>
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="filtered.length === 0">
                            <td colspan="9" class="px-5 py-16 text-center text-secondary">
                                <i class="bi bi-inbox text-5xl block mb-3 opacity-20"></i>
                                No participants found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ── Table Footer + Pagination ── -->
            <div class="px-5 py-3.5 border-t border-secondary/5 flex flex-col sm:flex-row items-center justify-between gap-3">

                <!-- Left: info + per-page -->
                <div class="flex items-center gap-3 flex-wrap">
                    <p class="text-xs text-secondary">
                        Showing
                        <span class="font-medium text-primary" x-text="filtered.length === 0 ? 0 : (page - 1) * perPage + 1"></span>–<span class="font-medium text-primary" x-text="Math.min(page * perPage, filtered.length)"></span>
                        of <span class="font-medium text-primary" x-text="filtered.length"></span>
                        <span x-show="selected.length">&mdash; <span class="text-accent font-medium" x-text="selected.length + ' selected'"></span></span>
                    </p>

                    <div class="relative">
                        <select x-model.number="perPage"
                            class="bg-light border border-secondary/10 rounded-full px-3 py-1.5 text-xs text-primary appearance-none pr-7 focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                            <option value="100">100 / page</option>
                        </select>
                        <i class="bi bi-chevron-down absolute right-2.5 top-1/2 -translate-y-1/2 text-secondary/50 text-[10px] pointer-events-none"></i>
                    </div>
                </div>

                <!-- Right: pagination controls -->
                <div class="flex items-center gap-1" x-show="totalPages > 1">

                    <button @click="goToPage(1)" :disabled="page === 1"
                        :class="page === 1 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-secondary/10 hover:text-primary'"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-secondary text-sm transition" title="First">
                        <i class="bi bi-chevron-double-left text-xs"></i>
                    </button>
                    <button @click="goToPage(page - 1)" :disabled="page === 1"
                        :class="page === 1 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-secondary/10 hover:text-primary'"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-secondary text-sm transition" title="Previous">
                        <i class="bi bi-chevron-left text-xs"></i>
                    </button>

                    <template x-for="(p, idx) in pageNumbers" :key="idx">
                        <template x-if="p === '...'">
                            <span class="w-8 h-8 flex items-center justify-center text-xs text-secondary/40">…</span>
                        </template>
                        <template x-if="p !== '...'">
                            <button @click="goToPage(p)"
                                :class="page === p ? 'bg-primary text-white shadow-sm' : 'text-secondary hover:bg-secondary/10 hover:text-primary'"
                                class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-medium transition"
                                x-text="p">
                            </button>
                        </template>
                    </template>

                    <button @click="goToPage(page + 1)" :disabled="page === totalPages"
                        :class="page === totalPages ? 'opacity-30 cursor-not-allowed' : 'hover:bg-secondary/10 hover:text-primary'"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-secondary text-sm transition" title="Next">
                        <i class="bi bi-chevron-right text-xs"></i>
                    </button>
                    <button @click="goToPage(totalPages)" :disabled="page === totalPages"
                        :class="page === totalPages ? 'opacity-30 cursor-not-allowed' : 'hover:bg-secondary/10 hover:text-primary'"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-secondary text-sm transition" title="Last">
                        <i class="bi bi-chevron-double-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>

    </div><!-- /max-w -->
</div>


<!-- ════ DETAIL MODAL ════ -->
<div
    x-data
    x-show="$store.editorModal.open"
    x-transition.opacity
    @keydown.escape.window="$store.editorModal.open = false"
    class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    @click.self="$store.editorModal.open = false">
    <div
        x-show="$store.editorModal.open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
        @click.stop>
        <template x-if="$store.editorModal.data">
            <div>
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white border-b border-secondary/10 px-6 py-4 flex items-start justify-between rounded-t-2xl z-10">
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-secondary">Participant Detail</p>
                        <h2 class="text-lg font-semibold text-primary" x-text="$store.editorModal.data.team_name || 'No Team Name'"></h2>
                        <p class="text-xs text-secondary mt-0.5" x-text="'Submitted by: ' + $store.editorModal.data.author_name"></p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 ml-4">

                        <!-- Approve via AJAX -->
                        <button type="button"
                            x-show="$store.editorModal.data.status !== 'publish'"
                            @click="modalAction('approve')"
                            :disabled="$store.editorModal.data._loading"
                            class="text-xs bg-green-500 text-white px-3 py-1.5 rounded-full hover:bg-green-600 transition flex items-center gap-1 disabled:opacity-50">
                            <span x-show="$store.editorModal.data._loading" class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <i x-show="!$store.editorModal.data._loading" class="bi bi-check"></i>
                            Approve
                        </button>

                        <!-- Reject via AJAX -->
                        <button type="button"
                            x-show="$store.editorModal.data.status !== 'draft'"
                            @click="modalAction('reject')"
                            :disabled="$store.editorModal.data._loading"
                            class="text-xs bg-red-400 text-white px-3 py-1.5 rounded-full hover:bg-red-500 transition flex items-center gap-1 disabled:opacity-50">
                            <span x-show="$store.editorModal.data._loading" class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <i x-show="!$store.editorModal.data._loading" class="bi bi-x"></i>
                            Reject
                        </button>

                        <button @click="$store.editorModal.open = false"
                            class="w-8 h-8 rounded-full bg-secondary/10 hover:bg-secondary/20 flex items-center justify-center text-secondary transition ml-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-6">

                    <!-- Status + Date -->
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold"
                            :class="{
                                'bg-yellow-100 text-yellow-700': $store.editorModal.data.status === 'pending',
                                'bg-green-100 text-green-700':  $store.editorModal.data.status === 'publish',
                                'bg-secondary/10 text-secondary': $store.editorModal.data.status === 'draft',
                            }"
                            x-text="{ pending:'Under Review', publish:'Approved', draft:'Rejected' }[$store.editorModal.data.status]">
                        </span>
                        <span class="text-xs text-secondary">
                            Submitted: <span x-text="$store.editorModal.data.date_fmt"></span>
                        </span>
                        <span class="text-xs text-secondary" x-show="$store.editorModal.data.category">
                            Category: <strong x-text="$store.editorModal.data.category"></strong>
                        </span>
                    </div>

                    <!-- Institution -->
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-secondary mb-3 font-medium">Institution</p>
                        <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                            <?php foreach (
                                [
                                    ['Region',      'region'],
                                    ['Country',     'country'],
                                    ['Institution', 'institution'],
                                    ['Faculty',     'faculty'],
                                ] as [$l, $k]
                            ) : ?>
                                <div>
                                    <p class="text-xs text-secondary"><?php echo $l; ?></p>
                                    <p class="text-sm font-medium text-primary" x-text="$store.editorModal.data.<?php echo $k; ?> || '—'"></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Head of Team -->
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-secondary mb-3 font-medium">Head of Team</p>
                        <div class="bg-light rounded-xl p-4 grid grid-cols-2 gap-x-8 gap-y-3">
                            <?php foreach (
                                [
                                    ['Name',           'head_name'],
                                    ['Email',          'head_email'],
                                    ['Phone',          'head_phone'],
                                    ['Semester',       'head_sem'],
                                    ['Admission Year', 'head_year'],
                                ] as [$l, $k]
                            ) : ?>
                                <div>
                                    <p class="text-xs text-secondary"><?php echo $l; ?></p>
                                    <p class="text-sm font-medium text-primary" x-text="$store.editorModal.data.<?php echo $k; ?> || '—'"></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Member 1 -->
                    <div x-show="$store.editorModal.data.m1_name">
                        <p class="text-[10px] uppercase tracking-widest text-secondary mb-3 font-medium">Team Member 1</p>
                        <div class="bg-light rounded-xl p-4 grid grid-cols-2 gap-x-8 gap-y-3">
                            <?php foreach ([['Name', 'm1_name'], ['Email', 'm1_email'], ['Phone', 'm1_phone'], ['Semester', 'm1_sem'], ['Admission Year', 'm1_year']] as [$l, $k]): ?>
                                <div>
                                    <p class="text-xs text-secondary"><?php echo $l; ?></p>
                                    <p class="text-sm font-medium text-primary" x-text="$store.editorModal.data.<?php echo $k; ?> || '—'"></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Member 2 -->
                    <div x-show="$store.editorModal.data.m2_name">
                        <p class="text-[10px] uppercase tracking-widest text-secondary mb-3 font-medium">Team Member 2</p>
                        <div class="bg-light rounded-xl p-4 grid grid-cols-2 gap-x-8 gap-y-3">
                            <?php foreach ([['Name', 'm2_name'], ['Email', 'm2_email'], ['Phone', 'm2_phone'], ['Semester', 'm2_sem'], ['Admission Year', 'm2_year']] as [$l, $k]): ?>
                                <div>
                                    <p class="text-xs text-secondary"><?php echo $l; ?></p>
                                    <p class="text-sm font-medium text-primary" x-text="$store.editorModal.data.<?php echo $k; ?> || '—'"></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Lecturer -->
                    <div x-show="$store.editorModal.data.lect_name">
                        <p class="text-[10px] uppercase tracking-widest text-secondary mb-3 font-medium">Lecturer / Mentor</p>
                        <div class="bg-light rounded-xl p-4 grid grid-cols-2 gap-x-8 gap-y-3">
                            <?php foreach ([['Name', 'lect_name'], ['Title', 'lect_title'], ['Email', 'lect_email'], ['Faculty', 'faculty']] as [$l, $k]): ?>
                                <div>
                                    <p class="text-xs text-secondary"><?php echo $l; ?></p>
                                    <p class="text-sm font-medium text-primary" x-text="$store.editorModal.data.<?php echo $k; ?> || '—'"></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Submission -->
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-secondary mb-3 font-medium">Submission</p>
                        <div class="space-y-2">
                            <div x-show="$store.editorModal.data.sub_link">
                                <p class="text-xs text-secondary mb-1">Link</p>
                                <a :href="$store.editorModal.data.sub_link" target="_blank"
                                    class="text-sm text-primary hover:underline break-all flex items-center gap-1.5">
                                    <i class="bi bi-link-45deg"></i>
                                    <span x-text="$store.editorModal.data.sub_link"></span>
                                </a>
                            </div>
                            <div x-show="$store.editorModal.data.sub_file">
                                <p class="text-xs text-secondary mb-1">File</p>
                                <a :href="$store.editorModal.data.sub_file" target="_blank"
                                    class="inline-flex items-center gap-2 bg-primary/10 text-primary text-sm px-4 py-2 rounded-full hover:bg-primary/20 transition">
                                    <i class="bi bi-file-earmark-arrow-down"></i>
                                    Download Submission File
                                </a>
                            </div>
                            <p x-show="!$store.editorModal.data.sub_link && !$store.editorModal.data.sub_file"
                                class="text-sm text-secondary/40 italic">No submission uploaded.</p>
                        </div>
                    </div>

                </div>
            </div>
        </template>
    </div>
</div>


<!-- jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<script>
    // Data dari PHP — di-inject sekali saat page load
    const SSDC_ROWS = <?php echo json_encode(array_values($rows)); ?>;
    const SSDC_NONCE = '<?php echo wp_create_nonce('ssdc_editor_action'); ?>';
    const SSDC_AJAX = '<?php echo admin_url('admin-ajax.php'); ?>';

    document.addEventListener('alpine:init', () => {

        // ── Global modal store
        Alpine.store('editorModal', {
            open: false,
            data: null
        });

        Alpine.data('editorDash', () => ({
            // ── State
            rows: SSDC_ROWS.map(r => ({
                ...r,
                _loading: false
            })),
            search: '',
            filterStatus: '',
            filterRegion: '',
            sort: {
                field: 'date',
                asc: false
            },
            selected: [],
            bulkAction: '',
            loading: false, // bulk loading
            toast: {
                show: false,
                message: '',
                type: 'success'
            },
            page: 1,
            perPage: 10,

            // ── Computed stats (reactive — update setelah AJAX)
            get stats() {
                return {
                    total: this.rows.length,
                    pending: this.rows.filter(r => r.status === 'pending').length,
                    publish: this.rows.filter(r => r.status === 'publish').length,
                    draft: this.rows.filter(r => r.status === 'draft').length,
                };
            },

            init() {
                // Watch filter/search → reset page ke 1
                // TAPI: tidak ada $watch untuk 'page' di sini,
                // jadi paginasi tidak pernah di-reset dari luar.
                this.$watch('search', () => {
                    this.page = 1;
                });
                this.$watch('filterStatus', () => {
                    this.page = 1;
                });
                this.$watch('filterRegion', () => {
                    this.page = 1;
                });
                this.$watch('perPage', () => {
                    this.page = 1;
                });
            },

            // ── Navigasi halaman — satu-satunya tempat yang mengubah this.page
            goToPage(p) {
                const max = this.totalPages;
                if (p < 1 || p > max) return;
                this.page = p;
                // Scroll tabel ke atas
                this.$el.querySelector('table')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            },

            // ── Filtered + sorted (seluruh data, tanpa paginasi)
            get filtered() {
                let data = [...this.rows];
                const q = this.search.toLowerCase();

                if (q) data = data.filter(r =>
                    (r.team_name || '').toLowerCase().includes(q) ||
                    (r.head_name || '').toLowerCase().includes(q) ||
                    (r.institution || '').toLowerCase().includes(q) ||
                    (r.country || '').toLowerCase().includes(q) ||
                    (r.author_email || '').toLowerCase().includes(q)
                );

                if (this.filterStatus) data = data.filter(r => r.status === this.filterStatus);
                if (this.filterRegion) data = data.filter(r => r.region === this.filterRegion);

                const f = this.sort.field;
                data.sort((a, b) => {
                    const va = (a[f] || '').toString().toLowerCase();
                    const vb = (b[f] || '').toString().toLowerCase();
                    return this.sort.asc ? va.localeCompare(vb) : vb.localeCompare(va);
                });

                return data;
            },

            // ── Data untuk halaman aktif saja
            get paginated() {
                const start = (this.page - 1) * this.perPage;
                return this.filtered.slice(start, start + this.perPage);
            },

            get totalPages() {
                return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
            },

            get pageNumbers() {
                const total = this.totalPages;
                const current = this.page;
                const delta = 2;
                const range = [];

                for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
                    range.push(i);
                }
                if (current - delta > 2) range.unshift('...');
                if (current + delta < total - 1) range.push('...');
                range.unshift(1);
                if (total > 1) range.push(total);

                return range;
            },

            sortBy(field) {
                this.sort.field === field ?
                    (this.sort.asc = !this.sort.asc) :
                    (this.sort = {
                        field,
                        asc: true
                    });
            },

            toggleSelect(id) {
                this.selected.includes(id) ?
                    (this.selected = this.selected.filter(i => i !== id)) :
                    this.selected.push(id);
            },
            selectAll() {
                this.selected = this.filtered.map(r => r.post_id);
            },
            clearAll() {
                this.selected = [];
            },

            openDetail(row) {
                Alpine.store('editorModal').data = row;
                Alpine.store('editorModal').open = true;
            },

            statusLabel(s) {
                return {
                    pending: 'Under Review',
                    publish: 'Approved',
                    draft: 'Rejected'
                } [s] || s;
            },

            // ── Toast helper
            showToast(message, type = 'success') {
                this.toast = {
                    show: true,
                    message,
                    type
                };
                setTimeout(() => {
                    this.toast.show = false;
                }, 3500);
            },

            // ── Core AJAX helper
            async ajaxUpdate(ids, action) {
                const body = new FormData();
                body.append('action', 'ssdc_update_status');
                body.append('nonce', SSDC_NONCE);
                body.append('bulk_action', action);
                ids.forEach(id => body.append('ids[]', id));

                const res = await fetch(SSDC_AJAX, {
                    method: 'POST',
                    body
                });
                const json = await res.json();
                if (!json.success) throw new Error(json.data?.message || 'Update failed');
                return json;
            },

            // ── Update status lokal di this.rows (reactive, tanpa reload)
            applyStatusLocally(ids, action) {
                const map = {
                    approve: 'publish',
                    reject: 'draft',
                    pending: 'pending'
                };
                const newStatus = map[action];
                this.rows = this.rows.map(r =>
                    ids.includes(r.post_id) ? {
                        ...r,
                        status: newStatus,
                        _loading: false
                    } : r
                );
                // Sinkronkan modal jika sedang terbuka
                const modal = Alpine.store('editorModal');
                if (modal.open && modal.data && ids.includes(modal.data.post_id)) {
                    modal.data = {
                        ...modal.data,
                        status: newStatus,
                        _loading: false
                    };
                }
            },

            // ── Quick action dari baris tabel
            async quickAction(row, action) {
                // Set loading hanya di baris ini
                this.rows = this.rows.map(r =>
                    r.post_id === row.post_id ? {
                        ...r,
                        _loading: true
                    } : r
                );
                try {
                    await this.ajaxUpdate([row.post_id], action);
                    this.applyStatusLocally([row.post_id], action);
                    this.showToast(`Status updated to "${this.statusLabel({ approve:'publish', reject:'draft', pending:'pending' }[action])}".`);
                } catch (e) {
                    this.rows = this.rows.map(r =>
                        r.post_id === row.post_id ? {
                            ...r,
                            _loading: false
                        } : r
                    );
                    this.showToast(e.message, 'error');
                }
            },

            // ── Action dari modal
            async modalAction(action) {
                const modal = Alpine.store('editorModal');
                if (!modal.data) return;
                modal.data = {
                    ...modal.data,
                    _loading: true
                };
                try {
                    await this.ajaxUpdate([modal.data.post_id], action);
                    this.applyStatusLocally([modal.data.post_id], action);
                    this.showToast('Status updated successfully.');
                } catch (e) {
                    modal.data = {
                        ...modal.data,
                        _loading: false
                    };
                    this.showToast(e.message, 'error');
                }
            },

            // ── Bulk action
            async submitBulk() {
                if (!this.selected.length || !this.bulkAction) return;
                this.loading = true;
                const ids = [...this.selected];
                const action = this.bulkAction;
                try {
                    await this.ajaxUpdate(ids, action);
                    this.applyStatusLocally(ids, action);
                    this.showToast(`${ids.length} participant(s) updated.`);
                    this.selected = [];
                    this.bulkAction = '';
                } catch (e) {
                    this.showToast(e.message, 'error');
                } finally {
                    this.loading = false;
                }
            },

            // ── CSV Export (semua filtered)
            exportCSV() {
                const cols = [
                    '#', 'Team', 'Head Name', 'Head Email', 'Head Phone', 'Head Semester', 'Head Admission Year',
                    'Member 1 Name', 'Member 1 Email', 'Member 1 Phone', 'Member 1 Semester', 'Member 1 Admission Year',
                    'Member 2 Name', 'Member 2 Email', 'Member 2 Phone', 'Member 2 Semester', 'Member 2 Admission Year',
                    'Lecturer Name', 'Lecturer Title', 'Lecturer Email',
                    'Institution', 'Faculty', 'Country', 'Region', 'Category', 'Status', 'Date',
                    'Submission Link', 'Submission File'
                ];
                const body = this.filtered.map((r, i) => [
                    i + 1,
                    r.team_name,
                    r.head_name, r.head_email, r.head_phone, r.head_sem, r.head_year,
                    r.m1_name, r.m1_email, r.m1_phone, r.m1_sem, r.m1_year,
                    r.m2_name, r.m2_email, r.m2_phone, r.m2_sem, r.m2_year,
                    r.lect_name, r.lect_title, r.lect_email,
                    r.institution, r.faculty, r.country, r.region, r.category,
                    this.statusLabel(r.status), r.date_fmt,
                    r.sub_link, r.sub_file
                ]);
                const csv = [cols, ...body]
                    .map(row => row.map(v => `"${(v||'').toString().replace(/"/g,'""')}"`).join(','))
                    .join('\n');
                const a = document.createElement('a');
                a.href = URL.createObjectURL(new Blob(['\uFEFF' + csv], {
                    type: 'text/csv;charset=utf-8;'
                }));
                a.download = `ssdc2026-participants-${new Date().toISOString().slice(0,10)}.csv`;
                a.click();
            },

            // ── PDF Export (semua filtered)
            exportPDF() {
                const {
                    jsPDF
                } = window.jspdf;
                const doc = new jsPDF({
                    orientation: 'landscape',
                    unit: 'mm',
                    format: 'a4'
                });

                const pageW = doc.internal.pageSize.getWidth(); // 297mm
                const margin = 10;
                const usable = pageW - margin * 2; // 277mm

                // ── Header bar
                doc.setFillColor(30, 30, 60);
                doc.rect(0, 0, pageW, 18, 'F');
                doc.setTextColor(255, 255, 255);
                doc.setFontSize(12);
                doc.setFont(undefined, 'bold');
                doc.text('SSDC 2026 — Participant Management', margin, 12);
                doc.setFontSize(8);
                doc.setFont(undefined, 'normal');
                doc.text(
                    `${new Date().toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' })}  |  Total: ${this.filtered.length}`,
                    pageW - margin, 12, {
                        align: 'right'
                    }
                );

                // ── Columns: split info into multiple lines but keep columns reasonable
                const head = [
                    ['#', 'Team', 'Head of Team', 'Member 1', 'Member 2', 'Lecturer', 'Institution / Faculty', 'Country / Region', 'Cat.', 'Status', 'Date']
                ];

                const body = this.filtered.map((r, i) => [
                    i + 1,
                    r.team_name || '—',
                    [r.head_name, r.head_email, r.head_phone, (r.head_sem || r.head_year) ? `Sem ${r.head_sem||'-'} / ${r.head_year||'-'}` : null]
                    .filter(Boolean).join('\n'),
                    r.m1_name ?
                    [r.m1_name, r.m1_email, r.m1_phone, (r.m1_sem || r.m1_year) ? `Sem ${r.m1_sem||'-'} / ${r.m1_year||'-'}` : null].filter(Boolean).join('\n') :
                    '—',
                    r.m2_name ?
                    [r.m2_name, r.m2_email, r.m2_phone, (r.m2_sem || r.m2_year) ? `Sem ${r.m2_sem||'-'} / ${r.m2_year||'-'}` : null].filter(Boolean).join('\n') :
                    '—',
                    r.lect_name ?
                    [(r.lect_title ? r.lect_title + ' ' : '') + r.lect_name, r.lect_email].filter(Boolean).join('\n') :
                    '—',
                    [r.institution, r.faculty].filter(Boolean).join('\n') || '—',
                    [r.country, r.region].filter(Boolean).join(' / ') || '—',
                    r.category || '—',
                    this.statusLabel(r.status),
                    r.date_fmt,
                ]);

                // ── Column widths — total must equal `usable` (277mm)
                // #(6) Team(20) Head(44) M1(40) M2(40) Lect(44) Inst(34) Country(22) Cat(14) Status(18) Date(15) = 297 → trim to 277
                const colWidths = [6, 20, 42, 38, 38, 42, 32, 22, 13, 18, 16]; // sum = 287, close enough; autoTable trims
                const colStyles = {};
                colWidths.forEach((w, idx) => {
                    colStyles[idx] = {
                        cellWidth: w
                    };
                });

                doc.autoTable({
                    startY: 22,
                    margin: {
                        left: margin,
                        right: margin
                    },
                    tableWidth: usable,
                    head,
                    body,
                    styles: {
                        fontSize: 6.5,
                        cellPadding: 2,
                        overflow: 'linebreak',
                        valign: 'top',
                    },
                    headStyles: {
                        fillColor: [30, 30, 60],
                        textColor: 255,
                        fontStyle: 'bold',
                        fontSize: 7,
                        halign: 'left',
                    },
                    alternateRowStyles: {
                        fillColor: [247, 248, 252]
                    },
                    columnStyles: colStyles,
                });

                doc.save(`ssdc2026-participants-${new Date().toISOString().slice(0, 10)}.pdf`);
            },

        }));
    });
</script>