<?php
// Ambil semua user contributor
$contributors = get_users(['role' => 'contributor', 'orderby' => 'registered', 'order' => 'DESC']);

// Ambil participant post per user
function ssdc_get_participant($user_id) {
    $posts = get_posts([
        'post_type'   => 'participants',
        'author'      => $user_id,
        'numberposts' => 1,
        'post_status' => ['publish', 'pending', 'draft'],
    ]);
    return $posts ? $posts[0] : null;
}

function ssdc_pm($post_id, $key) {
    return esc_html(get_post_meta($post_id, $key, true));
}

// Build data array untuk Alpine + export
$table_data = [];
foreach ($contributors as $u) {
    $p = ssdc_get_participant($u->ID);
    $table_data[] = [
        'id'               => $u->ID,
        'post_id'          => $p ? $p->ID : null,
        'registered'       => get_userdata($u->ID)->user_registered,
        'display_name'     => $u->display_name,
        'email'            => $u->user_email,
        'team_name'        => $p ? get_post_meta($p->ID, 'team_name', true) : '',
        'institution'      => $p ? get_post_meta($p->ID, 'institution_name', true) : '',
        'country'          => $p ? get_post_meta($p->ID, 'country', true) : '',
        'region'           => $p ? get_post_meta($p->ID, 'region', true) : '',
        'category'         => $p ? get_post_meta($p->ID, 'category', true) : '',
        'status'           => $p ? get_post_status($p->ID) : 'no_submission',
        'head_name'        => $p ? get_post_meta($p->ID, 'head_name', true) : '',
        'head_email'       => $p ? get_post_meta($p->ID, 'head_email', true) : '',
        'head_phone'       => $p ? get_post_meta($p->ID, 'head_phone', true) : '',
        'head_semester'    => $p ? get_post_meta($p->ID, 'head_semester', true) : '',
        'head_year'        => $p ? get_post_meta($p->ID, 'head_year', true) : '',
        'm1_name'          => $p ? get_post_meta($p->ID, 'm1_name', true) : '',
        'm1_email'         => $p ? get_post_meta($p->ID, 'm1_email', true) : '',
        'm1_phone'         => $p ? get_post_meta($p->ID, 'm1_phone', true) : '',
        'm2_name'          => $p ? get_post_meta($p->ID, 'm2_name', true) : '',
        'm2_email'         => $p ? get_post_meta($p->ID, 'm2_email', true) : '',
        'm2_phone'         => $p ? get_post_meta($p->ID, 'm2_phone', true) : '',
        'lect_name'        => $p ? get_post_meta($p->ID, 'lect_name', true) : '',
        'lect_email'       => $p ? get_post_meta($p->ID, 'lect_email', true) : '',
        'lect_title'       => $p ? get_post_meta($p->ID, 'lect_title', true) : '',
        'faculty'          => $p ? get_post_meta($p->ID, 'faculty', true) : '',
        'submission_link'  => $p ? get_post_meta($p->ID, 'submission_link', true) : '',
        'submission_file'  => $p ? get_post_meta($p->ID, 'submission_file_url', true) : '',
    ];
}

$current_user = wp_get_current_user();

// Stats
$total       = count($contributors);
$submitted   = count(array_filter($table_data, fn($r) => in_array($r['status'], ['pending','publish'])));
$approved    = count(array_filter($table_data, fn($r) => $r['status'] === 'publish'));
$no_sub      = count(array_filter($table_data, fn($r) => $r['status'] === 'no_submission'));
?>

<div
    class="min-h-screen bg-light"
    x-data="dashboard()"
    x-init="init()"
>

    <!-- ── Top Bar ── -->
    <div class="bg-primary text-white px-6 py-4 flex items-center justify-between shadow-md">
        <div class="flex items-center gap-3">
            <?php if (has_custom_logo()) the_custom_logo(); ?>
            <div>
                <p class="text-xs opacity-60 uppercase tracking-widest">SSDC 2026</p>
                <h1 class="text-lg font-semibold leading-tight"> Dashboard</h1>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm opacity-70"><?php echo esc_html($current_user->display_name); ?></span>
            <a href="<?php echo wp_logout_url(home_url()); ?>"
               class="text-xs border border-white/30 px-3 py-1.5 rounded-full hover:bg-white/10 transition">
                Logout
            </a>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">

        <!-- ── Stats ── -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <?php
            $stats = [
                ['Total Registered', $total,     'bg-white border border-secondary/10', 'text-primary'],
                ['Submitted',        $submitted,  'bg-white border border-secondary/10', 'text-accent'],
                ['Approved',         $approved,   'bg-white border border-secondary/10', 'text-green-600'],
                ['No Submission',    $no_sub,     'bg-white border border-secondary/10', 'text-secondary'],
            ];
            foreach ($stats as [$label, $val, $card, $text]) : ?>
                <div class="<?php echo $card; ?> rounded-2xl p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-widest text-secondary mb-1"><?php echo $label; ?></p>
                    <p class="text-4xl font-bold <?php echo $text; ?>"><?php echo $val; ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Toolbar ── -->
        <div class="bg-white rounded-2xl shadow-sm border border-secondary/10 p-4 mb-4 flex flex-wrap items-center gap-3">

            <!-- Search -->
            <div class="relative flex-1 min-w-48">
                <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-secondary/50 text-sm"></i>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Search team, name, country..."
                    class="w-full bg-light border-0 rounded-full pl-10 pr-4 py-2.5 text-sm text-primary placeholder-secondary/40 focus:outline-none focus:ring-2 focus:ring-primary/20"
                />
            </div>

            <!-- Filter Status -->
            <div class="relative">
                <select x-model="filterStatus" class="bg-light border-0 rounded-full px-4 py-2.5 text-sm text-primary focus:outline-none focus:ring-2 focus:ring-primary/20 appearance-none pr-8">
                    <option value="">All Status</option>
                    <option value="pending">Under Review</option>
                    <option value="publish">Approved</option>
                    <option value="draft">Draft</option>
                    <option value="no_submission">No Submission</option>
                </select>
                <i class="bi bi-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-secondary/50 text-xs pointer-events-none"></i>
            </div>

            <!-- Filter Region -->
            <div class="relative">
                <select x-model="filterRegion" class="bg-light border-0 rounded-full px-4 py-2.5 text-sm text-primary focus:outline-none focus:ring-2 focus:ring-primary/20 appearance-none pr-8">
                    <option value="">All Regions</option>
                    <option value="north_asia">North Asia</option>
                    <option value="saarc">SAARC</option>
                    <option value="anz">ANZ</option>
                    <option value="asean">ASEAN</option>
                </select>
                <i class="bi bi-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-secondary/50 text-xs pointer-events-none"></i>
            </div>

            <div class="flex items-center gap-2 ml-auto">
                <!-- Export CSV -->
                <button @click="exportCSV()"
                        class="flex items-center gap-2 bg-light border border-secondary/20 text-secondary hover:text-primary hover:border-primary/30 px-4 py-2.5 rounded-full text-sm transition">
                    <i class="bi bi-filetype-csv"></i>
                    CSV
                </button>
                <!-- Export PDF -->
                <button @click="exportPDF()"
                        class="flex items-center gap-2 bg-primary text-white hover:bg-primary/80 px-4 py-2.5 rounded-full text-sm transition">
                    <i class="bi bi-filetype-pdf"></i>
                    PDF
                </button>
            </div>
        </div>

        <!-- ── Table ── -->
        <div class="bg-white rounded-2xl shadow-sm border border-secondary/10 overflow-hidden mb-4">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-secondary/10 bg-light">
                            <th class="text-left px-5 py-3.5 text-xs uppercase tracking-wider text-secondary font-medium">#</th>
                            <th class="text-left px-5 py-3.5 text-xs uppercase tracking-wider text-secondary font-medium cursor-pointer hover:text-primary" @click="sortBy('team_name')">
                                Team <i class="bi" :class="sort.field==='team_name' ? (sort.asc ? 'bi-sort-up' : 'bi-sort-down') : 'bi-chevron-expand'"></i>
                            </th>
                            <th class="text-left px-5 py-3.5 text-xs uppercase tracking-wider text-secondary font-medium">Head of Team</th>
                            <th class="text-left px-5 py-3.5 text-xs uppercase tracking-wider text-secondary font-medium cursor-pointer hover:text-primary" @click="sortBy('country')">
                                Country <i class="bi" :class="sort.field==='country' ? (sort.asc ? 'bi-sort-up' : 'bi-sort-down') : 'bi-chevron-expand'"></i>
                            </th>
                            <th class="text-left px-5 py-3.5 text-xs uppercase tracking-wider text-secondary font-medium">Institution</th>
                            <th class="text-left px-5 py-3.5 text-xs uppercase tracking-wider text-secondary font-medium cursor-pointer hover:text-primary" @click="sortBy('registered')">
                                Registered <i class="bi" :class="sort.field==='registered' ? (sort.asc ? 'bi-sort-up' : 'bi-sort-down') : 'bi-chevron-expand'"></i>
                            </th>
                            <th class="text-left px-5 py-3.5 text-xs uppercase tracking-wider text-secondary font-medium">Status</th>
                            <th class="text-left px-5 py-3.5 text-xs uppercase tracking-wider text-secondary font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, i) in filtered" :key="row.id">
                            <tr class="border-b border-secondary/5 hover:bg-light/50 transition cursor-pointer"
                                @click="openDetail(row)">
                                <td class="px-5 py-4 text-secondary/50 text-xs" x-text="i + 1"></td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-primary" x-text="row.team_name || '—'"></p>
                                    <p class="text-xs text-secondary" x-text="row.email"></p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-primary" x-text="row.head_name || row.display_name"></p>
                                    <p class="text-xs text-secondary" x-text="row.head_phone || ''"></p>
                                </td>
                                <td class="px-5 py-4 text-primary" x-text="row.country || '—'"></td>
                                <td class="px-5 py-4 text-primary max-w-[160px] truncate" x-text="row.institution || '—'"></td>
                                <td class="px-5 py-4 text-secondary text-xs" x-text="formatDate(row.registered)"></td>
                                <td class="px-5 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium"
                                          :class="{
                                            'bg-yellow-100 text-yellow-700': row.status === 'pending',
                                            'bg-green-100 text-green-700':  row.status === 'publish',
                                            'bg-secondary/10 text-secondary': row.status === 'draft',
                                            'bg-red-50 text-red-400':       row.status === 'no_submission',
                                          }"
                                          x-text="{
                                            pending:'Under Review',
                                            publish:'Approved',
                                            draft:'Draft',
                                            no_submission:'No Submission'
                                          }[row.status] || row.status">
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <button @click.stop="openDetail(row)"
                                            class="text-xs border border-primary/20 text-primary px-3 py-1.5 rounded-full hover:bg-primary hover:text-white transition">
                                        View
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filtered.length === 0">
                            <td colspan="8" class="px-5 py-12 text-center text-secondary">
                                <i class="bi bi-inbox text-4xl block mb-2 opacity-30"></i>
                                No participants found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Table Footer -->
            <div class="px-5 py-3 border-t border-secondary/5 flex items-center justify-between">
                <p class="text-xs text-secondary">
                    Showing <span class="font-medium text-primary" x-text="filtered.length"></span>
                    of <span class="font-medium text-primary"><?php echo $total; ?></span> participants
                </p>
                <p class="text-xs text-secondary">Click row to view details</p>
            </div>
        </div>

    </div><!-- /max-w -->
</div><!-- /x-data -->


<!-- ════ DETAIL MODAL ════ -->
<div
    x-data
    x-show="$store.modal.open"
    x-transition.opacity
    @keydown.escape.window="$store.modal.open = false"
    class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    @click.self="$store.modal.open = false"
>
    <div
        x-show="$store.modal.open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
        @click.stop
    >
        <template x-if="$store.modal.data">

            <div>
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white border-b border-secondary/10 px-6 py-4 flex items-center justify-between rounded-t-2xl z-10">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-secondary">Participant Detail</p>
                        <h2 class="text-lg font-semibold text-primary" x-text="$store.modal.data.team_name || 'No Team Name'"></h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="downloadSinglePDF($store.modal.data)"
                                class="text-xs bg-primary text-white px-3 py-1.5 rounded-full hover:bg-primary/80 transition flex items-center gap-1">
                            <i class="bi bi-download"></i> PDF
                        </button>
                        <button @click="$store.modal.open = false"
                                class="w-8 h-8 rounded-full bg-secondary/10 hover:bg-secondary/20 flex items-center justify-center text-secondary transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-6">

                    <!-- Status -->
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold"
                              :class="{
                                'bg-yellow-100 text-yellow-700': $store.modal.data.status === 'pending',
                                'bg-green-100 text-green-700':  $store.modal.data.status === 'publish',
                                'bg-secondary/10 text-secondary': $store.modal.data.status === 'draft',
                                'bg-red-50 text-red-400':        $store.modal.data.status === 'no_submission',
                              }"
                              x-text="{pending:'Under Review',publish:'Approved',draft:'Draft',no_submission:'No Submission'}[$store.modal.data.status]">
                        </span>
                        <span class="text-xs text-secondary">
                            Registered: <span x-text="formatDate($store.modal.data.registered)"></span>
                        </span>
                    </div>

                    <!-- Institution -->
                    <div>
                        <p class="text-xs uppercase tracking-widest text-secondary mb-3">Institution</p>
                        <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                            <?php
                            $detail_fields_inst = [
                                ['Region',      'region'],
                                ['Country',     'country'],
                                ['Institution', 'institution'],
                                ['Faculty',     'faculty'],
                                ['Category',    'category'],
                            ];
                            foreach ($detail_fields_inst as [$lbl, $key]) : ?>
                                <div>
                                    <p class="text-xs text-secondary"><?php echo $lbl; ?></p>
                                    <p class="text-sm font-medium text-primary" x-text="$store.modal.data.<?php echo $key; ?> || '—'"></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Head of Team -->
                    <div>
                        <p class="text-xs uppercase tracking-widest text-secondary mb-3">Head of Team</p>
                        <div class="bg-light rounded-xl p-4 grid grid-cols-2 gap-x-6 gap-y-3">
                            <?php foreach ([['Name','head_name'],['Email','head_email'],['Phone','head_phone'],['Semester','head_semester'],['Year','head_year']] as [$l,$k]) : ?>
                                <div>
                                    <p class="text-xs text-secondary"><?php echo $l; ?></p>
                                    <p class="text-sm font-medium text-primary" x-text="$store.modal.data.<?php echo $k; ?> || '—'"></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Member 1 -->
                    <div x-show="$store.modal.data.m1_name">
                        <p class="text-xs uppercase tracking-widest text-secondary mb-3">Team Member 1</p>
                        <div class="bg-light rounded-xl p-4 grid grid-cols-2 gap-x-6 gap-y-3">
                            <?php foreach ([['Name','m1_name'],['Email','m1_email'],['Phone','m1_phone']] as [$l,$k]) : ?>
                                <div>
                                    <p class="text-xs text-secondary"><?php echo $l; ?></p>
                                    <p class="text-sm font-medium text-primary" x-text="$store.modal.data.<?php echo $k; ?> || '—'"></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Member 2 -->
                    <div x-show="$store.modal.data.m2_name">
                        <p class="text-xs uppercase tracking-widest text-secondary mb-3">Team Member 2</p>
                        <div class="bg-light rounded-xl p-4 grid grid-cols-2 gap-x-6 gap-y-3">
                            <?php foreach ([['Name','m2_name'],['Email','m2_email'],['Phone','m2_phone']] as [$l,$k]) : ?>
                                <div>
                                    <p class="text-xs text-secondary"><?php echo $l; ?></p>
                                    <p class="text-sm font-medium text-primary" x-text="$store.modal.data.<?php echo $k; ?> || '—'"></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Lecturer -->
                    <div x-show="$store.modal.data.lect_name">
                        <p class="text-xs uppercase tracking-widest text-secondary mb-3">Lecturer / Mentor</p>
                        <div class="bg-light rounded-xl p-4 grid grid-cols-2 gap-x-6 gap-y-3">
                            <?php foreach ([['Name','lect_name'],['Title','lect_title'],['Email','lect_email'],['Faculty','faculty']] as [$l,$k]) : ?>
                                <div>
                                    <p class="text-xs text-secondary"><?php echo $l; ?></p>
                                    <p class="text-sm font-medium text-primary" x-text="$store.modal.data.<?php echo $k; ?> || '—'"></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Submission -->
                    <div>
                        <p class="text-xs uppercase tracking-widest text-secondary mb-3">Submission</p>
                        <div class="space-y-2">
                            <div x-show="$store.modal.data.submission_link">
                                <p class="text-xs text-secondary mb-1">Link</p>
                                <a :href="$store.modal.data.submission_link" target="_blank"
                                   class="text-sm text-primary hover:underline break-all flex items-center gap-1">
                                    <i class="bi bi-link-45deg"></i>
                                    <span x-text="$store.modal.data.submission_link"></span>
                                </a>
                            </div>
                            <div x-show="$store.modal.data.submission_file">
                                <p class="text-xs text-secondary mb-1">File</p>
                                <a :href="$store.modal.data.submission_file" target="_blank"
                                   class="inline-flex items-center gap-2 text-sm bg-primary/10 text-primary px-4 py-2 rounded-full hover:bg-primary/20 transition">
                                    <i class="bi bi-file-earmark-arrow-down"></i>
                                    Download File
                                </a>
                            </div>
                            <p x-show="!$store.modal.data.submission_link && !$store.modal.data.submission_file"
                               class="text-sm text-secondary/50 italic">No submission yet.</p>
                        </div>
                    </div>

                </div><!-- /p-6 -->
            </div>

        </template>
    </div>
</div>


<!-- jsPDF CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<script>
const SSDC_DATA = <?php echo json_encode($table_data); ?>;

document.addEventListener('alpine:init', () => {

    Alpine.store('modal', { open: false, data: null });

    Alpine.data('dashboard', () => ({
        rows: SSDC_DATA,
        search: '',
        filterStatus: '',
        filterRegion: '',
        sort: { field: 'registered', asc: false },

        init() {},

        get filtered() {
            let data = [...this.rows];

            if (this.search) {
                const q = this.search.toLowerCase();
                data = data.filter(r =>
                    (r.team_name  || '').toLowerCase().includes(q) ||
                    (r.head_name  || '').toLowerCase().includes(q) ||
                    (r.institution|| '').toLowerCase().includes(q) ||
                    (r.country    || '').toLowerCase().includes(q) ||
                    (r.email      || '').toLowerCase().includes(q)
                );
            }

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

        sortBy(field) {
            if (this.sort.field === field) this.sort.asc = !this.sort.asc;
            else { this.sort.field = field; this.sort.asc = true; }
        },

        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
        },

        openDetail(row) {
            Alpine.store('modal').data = row;
            Alpine.store('modal').open = true;
        },

        // ── Export CSV ──
        exportCSV() {
            const cols = [
                'No','Team Name','Head Name','Head Email','Head Phone',
                'Member 1','Member 2','Lecturer','Institution','Faculty',
                'Country','Region','Category','Submission Link','Status','Registered'
            ];
            const rows = this.filtered.map((r, i) => [
                i+1, r.team_name, r.head_name, r.head_email, r.head_phone,
                r.m1_name, r.m2_name, r.lect_name, r.institution, r.faculty,
                r.country, r.region, r.category, r.submission_link,
                {pending:'Under Review',publish:'Approved',draft:'Draft',no_submission:'No Submission'}[r.status] || r.status,
                this.formatDate(r.registered)
            ]);

            const csvContent = [cols, ...rows]
                .map(row => row.map(v => `"${(v||'').toString().replace(/"/g,'""')}"`).join(','))
                .join('\n');

            const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
            const url  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href     = url;
            a.download = `ssdc2026-participants-${new Date().toISOString().slice(0,10)}.csv`;
            a.click();
            URL.revokeObjectURL(url);
        },

        // ── Export PDF (All) ──
        exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

            doc.setFontSize(14);
            doc.setTextColor(30, 30, 60);
            doc.text('SSDC 2026 — Participant List', 14, 14);
            doc.setFontSize(8);
            doc.setTextColor(120, 120, 140);
            doc.text(`Generated: ${new Date().toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'numeric'})}  |  Total: ${this.filtered.length}`, 14, 20);

            doc.autoTable({
                startY: 25,
                head: [['#','Team','Head of Team','Country','Institution','Category','Status','Registered']],
                body: this.filtered.map((r, i) => [
                    i+1,
                    r.team_name || '—',
                    `${r.head_name||'—'}\n${r.head_email||''}`,
                    r.country || '—',
                    r.institution || '—',
                    r.category || '—',
                    {pending:'Under Review',publish:'Approved',draft:'Draft',no_submission:'No Submission'}[r.status] || r.status,
                    this.formatDate(r.registered),
                ]),
                styles:     { fontSize: 8, cellPadding: 3, lineColor: [230,232,240], lineWidth: 0.2 },
                headStyles: { fillColor: [30,30,60], textColor: 255, fontStyle: 'bold', fontSize: 8 },
                alternateRowStyles: { fillColor: [247,248,252] },
                columnStyles: { 2: { cellWidth: 45 } },
            });

            doc.save(`ssdc2026-participants-${new Date().toISOString().slice(0,10)}.pdf`);
        },

        // ── Export PDF (Single) ──
        downloadSinglePDF(r) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ unit: 'mm', format: 'a4' });

            const statusMap = {pending:'Under Review',publish:'Approved',draft:'Draft',no_submission:'No Submission'};

            // Header
            doc.setFillColor(30, 30, 60);
            doc.rect(0, 0, 210, 28, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(16); doc.setFont(undefined, 'bold');
            doc.text('SSDC 2026 — Participant Detail', 14, 12);
            doc.setFontSize(9); doc.setFont(undefined, 'normal');
            doc.text(`Generated: ${new Date().toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'numeric'})}`, 14, 20);

            doc.setTextColor(30, 30, 60);
            let y = 36;

            const section = (title) => {
                doc.setFillColor(245, 246, 250);
                doc.rect(14, y-4, 182, 7, 'F');
                doc.setFontSize(8); doc.setFont(undefined, 'bold');
                doc.setTextColor(100, 100, 130);
                doc.text(title.toUpperCase(), 16, y);
                doc.setTextColor(30, 30, 60);
                y += 6;
            };

            const row2 = (l1, v1, l2, v2) => {
                doc.setFontSize(7.5); doc.setFont(undefined, 'normal'); doc.setTextColor(120,120,140);
                doc.text(l1, 16, y); doc.text(l2, 110, y);
                doc.setFontSize(9); doc.setFont(undefined, 'bold'); doc.setTextColor(30,30,60);
                doc.text(String(v1||'—'), 16, y+5); doc.text(String(v2||'—'), 110, y+5);
                y += 12;
            };

            // Team
            section('Team Information');
            row2('Team Name', r.team_name, 'Status', statusMap[r.status]||r.status);
            row2('Institution', r.institution, 'Faculty', r.faculty);
            row2('Country', r.country, 'Region', r.region);
            row2('Category', r.category, 'Registered', this.formatDate(r.registered));
            y += 2;

            // Head
            section('Head of Team');
            row2('Name', r.head_name, 'Email', r.head_email);
            row2('Phone', r.head_phone, 'Semester / Year', `${r.head_semester||'—'} / ${r.head_year||'—'}`);
            y += 2;

            // Members
            if (r.m1_name) {
                section('Team Member 1');
                row2('Name', r.m1_name, 'Email', r.m1_email);
                row2('Phone', r.m1_phone, '', '');
                y += 2;
            }
            if (r.m2_name) {
                section('Team Member 2');
                row2('Name', r.m2_name, 'Email', r.m2_email);
                row2('Phone', r.m2_phone, '', '');
                y += 2;
            }

            // Lecturer
            if (r.lect_name) {
                section('Lecturer / Mentor');
                row2('Name', r.lect_name, 'Title', r.lect_title);
                row2('Email', r.lect_email, 'Faculty', r.faculty);
                y += 2;
            }

            // Submission
            section('Submission');
            doc.setFontSize(8); doc.setFont(undefined, 'normal'); doc.setTextColor(120,120,140);
            doc.text('Link:', 16, y);
            doc.setTextColor(30,30,60); doc.setFont(undefined, 'bold');
            doc.text(String(r.submission_link||'—'), 30, y);
            y += 7;

            doc.save(`ssdc2026-${(r.team_name||'participant').replace(/\s+/g,'-').toLowerCase()}.pdf`);
        },

    }));
});
</script>