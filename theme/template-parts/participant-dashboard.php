<?php
$current_user = wp_get_current_user();
$user_id      = $current_user->ID;

// Ambil post participant milik user ini
$existing = get_posts([
    'post_type'   => 'participants',
    'author'      => $user_id,
    'numberposts' => 1,
    'post_status' => ['publish', 'pending', 'draft'],
]);

$post_id  = $existing ? $existing[0]->ID : null;
$has_data = !is_null($post_id);
$status   = $has_data ? get_post_status($post_id) : null;

// Helper
function pd_meta($post_id, $key) {
    return esc_html(get_post_meta($post_id, $key, true));
}

// Timeline steps
$timeline = [
    ['key' => 'registered',  'label' => 'Registration',       'desc' => 'Account created',               'icon' => 'bi-person-check'],
    ['key' => 'submitted',   'label' => 'Form Submitted',      'desc' => 'Team data submitted',           'icon' => 'bi-send-check'],
    ['key' => 'reviewing',   'label' => 'Under Review',        'desc' => 'Panitia sedang mereview data',  'icon' => 'bi-hourglass-split'],
    ['key' => 'approved',    'label' => 'Approved',            'desc' => 'Pendaftaran disetujui',         'icon' => 'bi-patch-check'],
    ['key' => 'competing',   'label' => 'Competition Phase',   'desc' => 'Kompetisi sedang berlangsung',  'icon' => 'bi-trophy'],
];

// Determine current step
$current_step = 0;
if ($has_data) {
    $current_step = 1; // submitted
    if ($status === 'pending') $current_step = 2; // reviewing
    if ($status === 'publish') $current_step = 3; // approved
}

// Important dates (bisa dari ACF options page)
$important_dates = [
    ['date' => '01 May 2026',  'label' => 'Registration Deadline',    'done' => true],
    ['date' => '15 May 2026',  'label' => 'Submission Deadline',      'done' => false],
    ['date' => '01 Jun 2026',  'label' => 'Announcement of Finalists','done' => false],
    ['date' => '15 Jun 2026',  'label' => 'Final Judging',            'done' => false],
    ['date' => '30 Jun 2026',  'label' => 'Winners Announced',        'done' => false],
];
?>

<div class="min-h-screen bg-light">

    <!-- ── Topbar ── -->
    <div class="bg-white px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <?php if (has_custom_logo()) the_custom_logo(); ?>
            <div>
                <p class="text-[10px] uppercase tracking-widest opacity-50">SSDC 2026</p>
                <h1 class="text-base font-semibold">Participant Dashboard</h1>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm opacity-60 hidden md:block"><?php echo esc_html($current_user->display_name); ?></span>
            <?php if ($has_data) : ?>
                <a href="<?php echo get_permalink(get_page_by_path('account')); ?>"
                   class="text-xs border border-white/20 px-3 py-1.5 rounded-full hover:bg-white/10 transition flex items-center gap-1.5">
                    <i class="bi bi-pencil"></i> Edit Form
                </a>
            <?php endif; ?>
            <a href="<?php echo wp_logout_url(home_url()); ?>"
               class="text-xs border border-white/20 px-3 py-1.5 rounded-full hover:bg-white/10 transition">
                Logout
            </a>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

        <?php if (!$has_data) : ?>
        <!-- ══ NO SUBMISSION STATE ══ -->
        <div class="bg-white rounded-2xl border border-secondary/10 shadow-sm p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-secondary/10 flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-clipboard-x text-3xl text-secondary/40"></i>
            </div>
            <h2 class="text-xl font-semibold text-primary mb-2">Belum Ada Pendaftaran</h2>
            <p class="text-secondary text-sm mb-6">Kamu belum mengisi form pendaftaran tim.<br>Segera daftarkan timmu sebelum deadline!</p>
            <a href="<?php echo get_permalink(get_page_by_path('account')); ?>"
               class="inline-flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-full text-sm font-medium hover:bg-primary/80 transition">
                <i class="bi bi-pencil-square"></i> Isi Form Pendaftaran
            </a>
        </div>

        <?php else : ?>

        <!-- ══ GREETING BANNER ══ -->
        <div class="bg-primary rounded-2xl p-6 text-white relative overflow-hidden">
            <div class="absolute right-0 top-0 w-48 h-48 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute right-10 bottom-0 w-24 h-24 bg-white/5 rounded-full translate-y-1/2"></div>
            <div class="relative z-10">
                <p class="text-xs uppercase tracking-widest opacity-60 mb-1">Welcome back</p>
                <h2 class="text-2xl font-bold mb-1"><?php echo esc_html($current_user->display_name); ?> 👋</h2>
                <p class="text-sm opacity-70">
                    Team: <strong class="text-white"><?php echo pd_meta($post_id, 'team_name') ?: '—'; ?></strong>
                    &nbsp;·&nbsp;
                    <?php echo pd_meta($post_id, 'institution_name') ?: '—'; ?>
                    &nbsp;·&nbsp;
                    <?php echo pd_meta($post_id, 'country') ?: '—'; ?>
                </p>
            </div>
            <!-- Status pill -->
            <div class="absolute top-6 right-6">
                <?php
                $pill = match($status) {
                    'pending' => ['Under Review', 'bg-accent text-white'],
                    'publish' => ['Approved ✓',   'bg-green-400 text-white'],
                    'draft'   => ['Rejected',      'bg-red-400 text-white'],
                    default   => ['Unknown',        'bg-secondary/30 text-white'],
                };
                ?>
                <span class="text-xs font-semibold px-4 py-1.5 rounded-full <?php echo $pill[1]; ?>">
                    <?php echo $pill[0]; ?>
                </span>
            </div>
        </div>

        <!-- ══ MAIN GRID ══ -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- LEFT: Team Summary + Members -->
            <div class="md:col-span-2 space-y-5">

                <!-- Team Card -->
                <div class="bg-white rounded-2xl border border-secondary/10 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-secondary/5 flex items-center justify-between">
                        <p class="text-xs uppercase tracking-widest text-secondary font-medium">Team Information</p>
                        <a href="<?php echo get_permalink(get_page_by_path('account')); ?>"
                           class="text-xs text-primary hover:underline flex items-center gap-1">
                            <i class="bi bi-pencil text-[10px]"></i> Edit
                        </a>
                    </div>
                    <div class="p-6 grid grid-cols-2 gap-x-8 gap-y-4">
                        <?php
                        $team_fields = [
                            ['Team Name',    'team_name'],
                            ['Institution',  'institution_name'],
                            ['Faculty',      'faculty'],
                            ['Country',      'country'],
                            ['Region',       'region'],
                            ['Category',     'category'],
                        ];
                        foreach ($team_fields as [$label, $key]) :
                            $val = pd_meta($post_id, $key);
                        ?>
                            <div>
                                <p class="text-xs text-secondary"><?php echo $label; ?></p>
                                <p class="text-sm font-medium text-primary"><?php echo $val ?: '—'; ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Members Card -->
                <div class="bg-white rounded-2xl border border-secondary/10 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-secondary/5">
                        <p class="text-xs uppercase tracking-widest text-secondary font-medium">Team Members</p>
                    </div>
                    <div class="divide-y divide-secondary/5">

                        <?php
                        $members = [
                            ['Head of Team', [
                                ['head_name',     'head_email',    'head_phone'],
                                ['head_semester', 'head_year'],
                            ], 'bg-primary text-white', '1'],
                            ['Team Member 1', [
                                ['m1_name', 'm1_email', 'm1_phone'],
                                ['m1_semester', 'm1_year'],
                            ], 'bg-secondary/20 text-secondary', '2'],
                            ['Team Member 2', [
                                ['m2_name', 'm2_email', 'm2_phone'],
                                ['m2_semester', 'm2_year'],
                            ], 'bg-secondary/20 text-secondary', '3'],
                        ];
                        foreach ($members as [$title, $fields, $badge_cls, $num]) :
                            $name = pd_meta($post_id, $fields[0][0]);
                            if (!$name && $num !== '1') continue;
                        ?>
                        <div class="px-6 py-4 flex items-start gap-4">
                            <span class="w-7 h-7 rounded-full <?php echo $badge_cls; ?> text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                                <?php echo $num; ?>
                            </span>
                            <div class="flex-1 grid grid-cols-2 gap-x-8 gap-y-2">
                                <div class="col-span-2">
                                    <p class="text-xs text-secondary"><?php echo $title; ?></p>
                                    <p class="text-sm font-semibold text-primary"><?php echo $name ?: '—'; ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-secondary">Email</p>
                                    <p class="text-sm text-primary"><?php echo pd_meta($post_id, $fields[0][1]) ?: '—'; ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-secondary">Phone</p>
                                    <p class="text-sm text-primary"><?php echo pd_meta($post_id, $fields[0][2]) ?: '—'; ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-secondary">Semester</p>
                                    <p class="text-sm text-primary"><?php echo pd_meta($post_id, $fields[1][0]) ?: '—'; ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-secondary">Year</p>
                                    <p class="text-sm text-primary"><?php echo pd_meta($post_id, $fields[1][1]) ?: '—'; ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <!-- Lecturer -->
                        <?php $lect = pd_meta($post_id, 'lect_name'); if ($lect) : ?>
                        <div class="px-6 py-4 flex items-start gap-4">
                            <span class="w-7 h-7 rounded-full bg-accent/20 text-accent flex items-center justify-center shrink-0 mt-0.5">
                                <i class="bi bi-person-workspace text-xs"></i>
                            </span>
                            <div class="flex-1 grid grid-cols-2 gap-x-8 gap-y-2">
                                <div class="col-span-2">
                                    <p class="text-xs text-secondary">Lecturer / Mentor</p>
                                    <p class="text-sm font-semibold text-primary"><?php echo $lect; ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-secondary">Title</p>
                                    <p class="text-sm text-primary"><?php echo pd_meta($post_id,'lect_title') ?: '—'; ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-secondary">Email</p>
                                    <p class="text-sm text-primary"><?php echo pd_meta($post_id,'lect_email') ?: '—'; ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Submission Card -->
                <div class="bg-white rounded-2xl border border-secondary/10 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-secondary/5 flex items-center justify-between">
                        <p class="text-xs uppercase tracking-widest text-secondary font-medium">Submission</p>
                        <?php
                        $sub_link = get_post_meta($post_id, 'submission_link', true);
                        $sub_file = get_post_meta($post_id, 'submission_file_url', true);
                        $has_sub  = $sub_link || $sub_file;
                        ?>
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium <?php echo $has_sub ? 'bg-green-100 text-green-600' : 'bg-red-50 text-red-400'; ?>">
                            <?php echo $has_sub ? '✓ Submitted' : '✗ Not Submitted'; ?>
                        </span>
                    </div>
                    <div class="p-6 space-y-4">
                        <?php if ($sub_link) : ?>
                            <div>
                                <p class="text-xs text-secondary mb-1">Submission Link</p>
                                <a href="<?php echo esc_url($sub_link); ?>" target="_blank"
                                   class="text-sm text-primary hover:underline flex items-center gap-1.5 break-all">
                                    <i class="bi bi-link-45deg shrink-0"></i>
                                    <?php echo esc_html($sub_link); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if ($sub_file) : ?>
                            <div>
                                <p class="text-xs text-secondary mb-1">Uploaded File</p>
                                <a href="<?php echo esc_url($sub_file); ?>" target="_blank"
                                   class="inline-flex items-center gap-2 bg-primary/10 text-primary text-sm px-4 py-2.5 rounded-full hover:bg-primary/20 transition">
                                    <i class="bi bi-file-earmark-arrow-down"></i>
                                    <?php echo esc_html(basename($sub_file)); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (!$has_sub) : ?>
                            <div class="text-center py-4">
                                <p class="text-sm text-secondary/50 mb-3">Belum ada submission yang diunggah.</p>
                                <a href="<?php echo get_permalink(get_page_by_path('account')); ?>"
                                   class="inline-flex items-center gap-2 bg-accent text-white text-sm px-5 py-2.5 rounded-full hover:bg-accent/80 transition">
                                    <i class="bi bi-upload"></i> Upload Submission
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /LEFT -->

            <!-- RIGHT: Status + Timeline + Dates -->
            <div class="space-y-5">

                <!-- Registration Status Card -->
                <div class="bg-white rounded-2xl border border-secondary/10 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-secondary/5">
                        <p class="text-xs uppercase tracking-widest text-secondary font-medium">Registration Status</p>
                    </div>
                    <div class="p-5">
                        <?php
                        $status_info = match($status) {
                            'pending' => [
                                'icon'  => 'bi-hourglass-split',
                                'color' => 'text-accent',
                                'bg'    => 'bg-accent/10',
                                'label' => 'Under Review',
                                'desc'  => 'Data tim kamu sedang direview oleh panitia. Kami akan memberitahu kamu segera.',
                            ],
                            'publish' => [
                                'icon'  => 'bi-patch-check-fill',
                                'color' => 'text-green-600',
                                'bg'    => 'bg-green-50',
                                'label' => 'Approved!',
                                'desc'  => 'Selamat! Pendaftaran timmu telah disetujui. Pastikan submission sudah siap sebelum deadline.',
                            ],
                            'draft' => [
                                'icon'  => 'bi-x-circle',
                                'color' => 'text-red-500',
                                'bg'    => 'bg-red-50',
                                'label' => 'Needs Revision',
                                'desc'  => 'Data pendaftaranmu memerlukan revisi. Silakan perbarui dan submit ulang.',
                            ],
                            default => [
                                'icon'  => 'bi-question-circle',
                                'color' => 'text-secondary',
                                'bg'    => 'bg-secondary/10',
                                'label' => 'Unknown',
                                'desc'  => '',
                            ],
                        };
                        ?>
                        <div class="<?php echo $status_info['bg']; ?> rounded-xl p-4 text-center mb-4">
                            <i class="bi <?php echo $status_info['icon']; ?> text-3xl <?php echo $status_info['color']; ?> block mb-2"></i>
                            <p class="font-semibold <?php echo $status_info['color']; ?> text-base"><?php echo $status_info['label']; ?></p>
                            <p class="text-xs text-secondary mt-1 leading-relaxed"><?php echo $status_info['desc']; ?></p>
                        </div>

                        <?php if ($status === 'draft') : ?>
                            <a href="<?php echo get_permalink(get_page_by_path('account')); ?>"
                               class="w-full flex items-center justify-center gap-2 bg-primary text-white py-2.5 rounded-full text-sm font-medium hover:bg-primary/80 transition">
                                <i class="bi bi-pencil"></i> Perbarui Data
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Progress Timeline -->
                <div class="bg-white rounded-2xl border border-secondary/10 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-secondary/5">
                        <p class="text-xs uppercase tracking-widest text-secondary font-medium">Progress</p>
                    </div>
                    <div class="p-5">
                        <div class="relative">
                            <!-- Vertical line -->
                            <div class="absolute left-3.5 top-4 bottom-4 w-px bg-secondary/10"></div>

                            <div class="space-y-5">
                                <?php foreach ($timeline as $i => $step) :
                                    $done    = $i <= $current_step;
                                    $current = $i === $current_step;
                                ?>
                                    <div class="flex items-start gap-4 relative">
                                        <!-- Dot -->
                                        <div class="w-7 h-7 rounded-full shrink-0 flex items-center justify-center z-10
                                            <?php echo $done
                                                ? ($current ? 'bg-primary text-white ring-4 ring-primary/20' : 'bg-primary/20 text-primary')
                                                : 'bg-secondary/10 text-secondary/30'; ?>">
                                            <i class="bi <?php echo $step['icon']; ?> text-xs"></i>
                                        </div>
                                        <!-- Label -->
                                        <div class="pt-0.5">
                                            <p class="text-sm font-medium <?php echo $done ? 'text-primary' : 'text-secondary/40'; ?>">
                                                <?php echo $step['label']; ?>
                                                <?php if ($current) : ?>
                                                    <span class="ml-1.5 text-[10px] bg-primary/10 text-primary px-2 py-0.5 rounded-full font-semibold">Now</span>
                                                <?php endif; ?>
                                            </p>
                                            <p class="text-xs <?php echo $done ? 'text-secondary' : 'text-secondary/30'; ?>">
                                                <?php echo $step['desc']; ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Important Dates -->
                <div class="bg-white rounded-2xl border border-secondary/10 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-secondary/5">
                        <p class="text-xs uppercase tracking-widest text-secondary font-medium">Important Dates</p>
                    </div>
                    <div class="divide-y divide-secondary/5">
                        <?php foreach ($important_dates as $d) : ?>
                            <div class="px-5 py-3 flex items-center gap-3">
                                <div class="w-1.5 h-1.5 rounded-full shrink-0 <?php echo $d['done'] ? 'bg-green-400' : 'bg-secondary/20'; ?>"></div>
                                <div class="flex-1">
                                    <p class="text-xs font-medium text-primary <?php echo $d['done'] ? 'line-through opacity-50' : ''; ?>">
                                        <?php echo $d['label']; ?>
                                    </p>
                                    <p class="text-[10px] text-secondary"><?php echo $d['date']; ?></p>
                                </div>
                                <?php if ($d['done']) : ?>
                                    <i class="bi bi-check-circle-fill text-green-400 text-xs"></i>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="bg-white rounded-2xl border border-secondary/10 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-secondary/5">
                        <p class="text-xs uppercase tracking-widest text-secondary font-medium">Quick Links</p>
                    </div>
                    <div class="p-3 space-y-1">
                        <?php
                        $links = [
                            ['Edit Registration',  get_permalink(get_page_by_path('account')), 'bi-pencil'],
                            ['Competition Rules',  home_url('/#rule'),                         'bi-book'],
                            ['Judging Criteria',   home_url('/#judging'),                      'bi-star'],
                            ['Contact Organizer',  'mailto:info@ssdc2026.com',                 'bi-envelope'],
                        ];
                        foreach ($links as [$label, $href, $icon]) :
                        ?>
                            <a href="<?php echo esc_url($href); ?>"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-light transition group">
                                <span class="w-7 h-7 rounded-full bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition">
                                    <i class="bi <?php echo $icon; ?> text-primary text-xs"></i>
                                </span>
                                <span class="text-sm text-primary"><?php echo $label; ?></span>
                                <i class="bi bi-chevron-right text-secondary/30 text-xs ml-auto"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div><!-- /RIGHT -->

        </div><!-- /MAIN GRID -->
        <?php endif; // has_data ?>

    </div><!-- /max-w -->
</div>