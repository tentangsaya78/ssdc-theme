<?php
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user = wp_get_current_user();
$user_id      = $current_user->ID;
$errors       = [];

// Fetch existing post
$existing = get_posts([
    'post_type'   => 'participants',
    'author'      => $user_id,
    'numberposts' => 1,
    'post_status' => ['publish', 'pending', 'draft'],
]);

$post_id  = $existing ? $existing[0]->ID : null;
$is_edit  = !is_null($post_id);
$status   = $is_edit ? get_post_status($post_id) : null;

// If already approved, cannot edit
$is_locked = ($status === 'publish');

// Submission (file/link) opens on this date — change as needed
$submission_open_ts   = new DateTime('2026-05-21 00:00:00', new DateTimeZone('Asia/Jakarta'));
$now_ts               = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
$is_submission_closed = $now_ts < $submission_open_ts;

// Helper
function sp_val($post_id, $key)
{
    if (!$post_id) return esc_attr($_POST[$key] ?? '');
    return esc_attr(get_post_meta($post_id, $key, true) ?: ($_POST[$key] ?? ''));
}

// ── Handle Submit ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ssdc_participant_nonce'])) {
    if (!wp_verify_nonce($_POST['ssdc_participant_nonce'], 'ssdc_participant_action')) {
        $errors[] = 'Security check failed.';
    } elseif ($is_locked) {
        $errors[] = 'Data cannot be changed because it has already been approved.';
    } elseif ($is_submission_closed && !empty($_POST['has_submission'])) {
        $errors[] = 'Submission of files/links is not yet open. Please wait until May 21, 2026.';
    } else {
        $keys = [
            'region',
            'country',
            'institution_type',
            'institution_type_other',
            'institution_name',
            'faculty',
            'team_name',
            'head_name',
            'head_email',
            'head_phone',
            'head_semester',
            'head_year',
            'm1_name',
            'm1_email',
            'm1_phone',
            'm1_semester',
            'm1_year',
            'm2_name',
            'm2_email',
            'm2_phone',
            'm2_semester',
            'm2_year',
            'lect_name',
            'lect_faculty',
            'lect_title',
            'lect_title_other',
            'lect_email',
            'submission_link',
        ];

        $data = [];
        foreach ($keys as $k) $data[$k] = sanitize_text_field($_POST[$k] ?? '');
        foreach (['head_email', 'm1_email', 'm2_email', 'lect_email'] as $e) {
            $data[$e] = sanitize_email($data[$e]);
        }
        $data['submission_link'] = esc_url_raw($_POST['submission_link'] ?? '');

        // Resolve "Others" fields
        if ($data['institution_type'] === 'Others (Please Mention)' && !empty($data['institution_type_other'])) {
            $data['institution_type_display'] = $data['institution_type_other'];
        } else {
            $data['institution_type_display'] = $data['institution_type'];
        }
        if ($data['lect_title'] === 'Others (Please Mention)' && !empty($data['lect_title_other'])) {
            $data['lect_title_display'] = $data['lect_title_other'];
        } else {
            $data['lect_title_display'] = $data['lect_title'];
        }

        // Validation
        if (empty($data['region']))           $errors[] = 'Region is required.';
        if (empty($data['country']))          $errors[] = 'Country is required.';
        if (empty($data['institution_type'])) $errors[] = 'Type of Institution is required.';
        if ($data['institution_type'] === 'Others (Please Mention)' && empty($data['institution_type_other']))
            $errors[] = 'Please specify your institution type.';
        if (empty($data['institution_name'])) $errors[] = 'Institution Name is required.';
        if (empty($data['faculty']))          $errors[] = 'Faculty is required.';
        if (empty($data['team_name']))        $errors[] = 'Team Name is required.';
        if (empty($data['head_name']))        $errors[] = 'Head of Team name is required.';
        if (!is_email($data['head_email']))   $errors[] = 'Head of Team email is not valid.';
        if (empty($data['head_phone']))       $errors[] = 'Head of Team mobile number is required.';
        if (empty($data['head_semester']))    $errors[] = 'Head of Team semester is required.';
        if (empty($data['head_year']))        $errors[] = 'Head of Team year is required.';

        // Validate member 1 if any field is filled
        $m1_any = $data['m1_name'] || $data['m1_email'] || $data['m1_phone'];
        if ($m1_any) {
            if (empty($data['m1_name']))      $errors[] = 'Member 1 name is required if registering a member.';
            if (!is_email($data['m1_email'])) $errors[] = 'Member 1 email is not valid.';
        }

        // Validate member 2 — only allowed if member 1 exists
        $m2_any = $data['m2_name'] || $data['m2_email'] || $data['m2_phone'];
        if ($m2_any && !$data['m1_name']) {
            $errors[] = 'Member 1 must be filled before adding Member 2.';
        }

        if (empty($errors)) {
            $post_data = [
                'post_title'  => $data['team_name'],
                'post_type'   => 'participants',
                'post_status' => 'pending',
                'post_author' => $user_id,
            ];

            if ($is_edit) {
                $post_data['ID'] = $post_id;
                $result = wp_update_post($post_data);
            } else {
                $result = wp_insert_post($post_data);
                $post_id = $result;
            }

            if ($result && !is_wp_error($result)) {
                foreach ($data as $k => $v) update_post_meta($result, $k, $v);

                // File upload
                if (!empty($_FILES['submission_file']['name'])) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    require_once ABSPATH . 'wp-admin/includes/media.php';
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                    $att = media_handle_upload('submission_file', $result);
                    if (!is_wp_error($att)) {
                        update_post_meta($result, 'submission_file_id', $att);
                        update_post_meta($result, 'submission_file_url', wp_get_attachment_url($att));
                    }
                }

                // Redirect to dashboard
                wp_redirect(add_query_arg(
                    'submitted',
                    '1',
                    get_permalink(get_page_by_path('participant-dashboard'))
                ));
                exit;
            } else {
                $errors[] = 'Failed to save data. Please try again.';
            }
        }
    }
}

$saved_file_url  = $post_id ? get_post_meta($post_id, 'submission_file_url', true) : '';
$saved_file_name = $saved_file_url ? basename($saved_file_url) : '';

// Step tracking for progress bar
$steps = ['Institution', 'Head of Team', 'Members', 'Lecturer', 'Submission'];
?>

<div
    class="min-h-screen bg-light py-10 px-4"
    x-data="regForm()"
    x-init="init()">
    <div class="max-w-2xl mx-auto">

        <!-- ── Header ── -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-widest text-secondary mb-1">SSDC 2026</p>
                <h1 class="text-2xl font-semibold text-primary">
                    <?php echo $is_edit ? 'Update Registration' : 'Team Registration'; ?>
                </h1>
            </div>
            <div class="flex items-center gap-3 text-right text-sm">
                <?php if ($is_edit) : ?>
                    <a href="<?php echo get_permalink(get_page_by_path('participant-dashboard')); ?>"
                        class="text-xs border border-secondary/20 text-secondary hover:text-primary hover:border-primary/30 px-3 py-1.5 rounded-full transition flex items-center gap-1.5">
                        <i class="bi bi-arrow-left text-[10px]"></i> Dashboard
                    </a>
                <?php endif; ?>
                <div>
                    <span class="block font-medium text-primary"><?php echo esc_html($current_user->display_name); ?></span>
                    <a href="<?php echo wp_logout_url(home_url()); ?>"
                        class="text-xs text-secondary hover:text-primary transition">Logout</a>
                </div>
            </div>
        </div>

        <!-- ── Submission Countdown Banner ── -->

        <!-- ── Status Banner (edit mode) ── -->
        <?php if ($is_edit) :
            $banner = match ($status) {
                'pending' => ['bg-accent/10 border-accent/20',   'text-accent',    'bi-hourglass-split',    'Under Review',  'Your data is being reviewed by the committee. You can still update before it is approved.'],
                'publish' => ['bg-green-50 border-green-200',    'text-green-600', 'bi-patch-check-fill',   'Approved',      'Your registration has been approved. Data cannot be changed.'],
                'draft'   => ['bg-red-50 border-red-200',        'text-red-500',   'bi-exclamation-circle', 'Needs Revision', 'Your data needs revision. Please update and resubmit.'],
                default   => ['bg-secondary/5 border-secondary/10', 'text-secondary', 'bi-info-circle',        'Draft',         ''],
            };
        ?>
            <div class="mb-5 flex items-center gap-3 bg-white rounded-2xl px-5 py-3.5 border <?php echo $banner[0]; ?> shadow-sm">
                <i class="bi <?php echo $banner[2]; ?> text-lg <?php echo $banner[1]; ?> shrink-0"></i>
                <div>
                    <p class="text-sm font-semibold <?php echo $banner[1]; ?>"><?php echo $banner[3]; ?></p>
                    <p class="text-xs text-secondary"><?php echo $banner[4]; ?></p>
                </div>
                <?php if ($status !== 'publish') : ?>
                    <span class="ml-auto text-xs bg-primary/10 text-primary px-3 py-1 rounded-full font-medium">Editable</span>
                <?php else : ?>
                    <span class="ml-auto text-xs bg-green-100 text-green-600 px-3 py-1 rounded-full font-medium">Locked</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- ── Errors ── -->
        <?php if (!empty($errors)) : ?>
            <div class="mb-5 bg-red-50 border border-red-200 rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-red-600 mb-2 flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo count($errors); ?> error(s) found:
                </p>
                <ul class="text-sm text-red-500 space-y-1 list-disc list-inside">
                    <?php foreach ($errors as $e) echo "<li>" . esc_html($e) . "</li>"; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- ── Step Progress ── -->
        <div class="bg-white rounded-2xl shadow-sm border border-secondary/10 p-4 mb-5">
            <div class="flex items-center justify-between">
                <?php foreach ($steps as $i => $step) : ?>
                    <div class="flex flex-col items-center gap-1 flex-1"
                        x-on:click="currentStep = <?php echo $i; ?>">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold cursor-pointer transition"
                            :class="currentStep === <?php echo $i; ?>
                            ? 'bg-primary text-white'
                            : completedSteps.includes(<?php echo $i; ?>)
                                ? 'bg-primary/20 text-primary'
                                : 'bg-light text-secondary/40'">
                            <template x-if="completedSteps.includes(<?php echo $i; ?>) && currentStep !== <?php echo $i; ?>">
                                <i class="bi bi-check text-sm font-bold"></i>
                            </template>
                            <template x-if="!completedSteps.includes(<?php echo $i; ?>) || currentStep === <?php echo $i; ?>">
                                <span><?php echo $i + 1; ?></span>
                            </template>
                        </div>
                        <span class="text-[10px] text-center hidden md:block"
                            :class="currentStep === <?php echo $i; ?> ? 'text-primary font-medium' : 'text-secondary/50'">
                            <?php echo $step; ?>
                        </span>
                    </div>
                    <?php if ($i < count($steps) - 1) : ?>
                        <div class="h-px flex-1 mx-1 mb-4"
                            :class="completedSteps.includes(<?php echo $i; ?>) ? 'bg-primary/30' : 'bg-secondary/10'"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($is_locked) : ?>
            <!-- ── LOCKED VIEW ── -->
            <div class="bg-white rounded-2xl shadow-sm border border-green-200 p-6 text-center">
                <i class="bi bi-lock-fill text-4xl text-green-400 block mb-3"></i>
                <p class="text-base font-semibold text-primary mb-1">Data Locked</p>
                <p class="text-sm text-secondary mb-5">Your registration has been approved and can no longer be changed.</p>
                <a href="<?php echo get_permalink(get_page_by_path('participant-dashboard')); ?>"
                    class="inline-flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-full text-sm font-medium hover:bg-primary/80 transition">
                    <i class="bi bi-speedometer2"></i> Back to Dashboard
                </a>
            </div>

        <?php else : ?>

            <!-- ══════ FORM ══════ -->
            <form method="POST" enctype="multipart/form-data" @submit.prevent="handleSubmit($el)">
                <?php wp_nonce_field('ssdc_participant_action', 'ssdc_participant_nonce'); ?>
                <input type="hidden" name="has_submission" id="has_submission" value="0" />

                <!-- ════ STEP 0: INSTITUTION ════ -->
                <div x-show="currentStep === 0" x-transition.opacity>
                    <div class="bg-white rounded-2xl shadow-sm p-6 mb-4 border border-secondary/10">
                        <div class="flex items-center gap-3 mb-5">
                            <span class="w-7 h-7 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center">1</span>
                            <div>
                                <p class="text-base font-semibold text-primary">Institution</p>
                                <p class="text-xs text-secondary">Institution and team information</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div>
                                <label class="ssdc-label">Region <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <select name="region" x-model="region" @change="updateCountries()" class="ssdc-select">
                                        <option value="">Choose</option>
                                        <option value="north_asia" <?php selected(sp_val($post_id, 'region'), 'north_asia'); ?>>North Asia</option>
                                        <option value="saarc" <?php selected(sp_val($post_id, 'region'), 'saarc'); ?>>SAARC</option>
                                        <option value="anz" <?php selected(sp_val($post_id, 'region'), 'anz'); ?>>ANZ</option>
                                        <option value="asean" <?php selected(sp_val($post_id, 'region'), 'asean'); ?>>ASEAN</option>
                                    </select>
                                    <i class="bi bi-chevron-down ssdc-chevron"></i>
                                </div>
                            </div>
                            <div>
                                <label class="ssdc-label">Country <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <select name="country" class="ssdc-select"
                                        :disabled="!region"
                                        :class="!region ? 'opacity-40 cursor-not-allowed' : ''">
                                        <option value="">Choose</option>
                                        <template x-for="c in countryOptions" :key="c">
                                            <option :value="c" x-text="c"
                                                :selected="c === '<?php echo esc_js(sp_val($post_id, 'country')); ?>'"></option>
                                        </template>
                                    </select>
                                    <i class="bi bi-chevron-down ssdc-chevron"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Institution Type with "Others" reveal -->
                        <div class="mb-4" x-data="{ instType: '<?php echo esc_js(sp_val($post_id, 'institution_type')); ?>' }">
                            <label class="ssdc-label">Type of Institution <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <select x-model="instType"
                                    x-bind:name="instType === 'Others (Please Mention)' ? '' : 'institution_type'"
                                    class="ssdc-select">
                                    <option value="">Choose</option>
                                    <?php foreach (['University', 'Polytechnic', 'College', 'Institute of Technology', 'Others (Please Mention)'] as $o) : ?>
                                        <option <?php selected(sp_val($post_id, 'institution_type'), $o); ?>><?php echo $o; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="bi bi-chevron-down ssdc-chevron"></i>
                            </div>

                            <div x-show="instType === 'Others (Please Mention)'" x-transition.opacity class="mt-2">
                                <input type="text"
                                    x-bind:name="instType === 'Others (Please Mention)' ? 'institution_type' : 'institution_type_other'"
                                    placeholder="Please specify your institution type"
                                    value="<?php echo sp_val($post_id, 'institution_type_other'); ?>"
                                    class="ssdc-input w-full" />
                            </div>
                        </div>

                        <?php ssdc_field('Institution Name',              'institution_name', 'Institution Name',              sp_val($post_id, 'institution_name'), 'text', true); ?>
                        <?php ssdc_field('Faculty / School / Department', 'faculty',          'Faculty / School / Department', sp_val($post_id, 'faculty'),          'text', true); ?>

                        <div class="border-t border-secondary/10 pt-4 mt-2">
                            <?php ssdc_field("Team's Name", 'team_name', "Team's Name", sp_val($post_id, 'team_name'), 'text', true); ?>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" @click="nextStep(0)"
                            class="bg-primary text-white px-6 py-3 rounded-full text-sm font-medium hover:bg-primary/80 transition flex items-center gap-2">
                            Next: Head of Team <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- ════ STEP 1: HEAD OF TEAM ════ -->
                <div x-show="currentStep === 1" x-transition.opacity>
                    <div class="bg-white rounded-2xl shadow-sm p-6 mb-4 border border-secondary/10">
                        <div class="flex items-center gap-3 mb-5">
                            <span class="w-7 h-7 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center">2</span>
                            <div>
                                <p class="text-base font-semibold text-primary">Head of Team</p>
                                <p class="text-xs text-secondary">Team leader information</p>
                            </div>
                        </div>

                        <?php ssdc_field('Name',          'head_name',  'Name',          sp_val($post_id, 'head_name'),  'text',  true); ?>
                        <?php ssdc_field('Email Address', 'head_email', 'Email Address', sp_val($post_id, 'head_email'), 'email', true); ?>
                        <?php ssdc_field('Mobile Number', 'head_phone', 'Mobile Number', sp_val($post_id, 'head_phone'), 'tel',   true); ?>
                        <?php ssdc_grade('head_semester', 'head_year',  sp_val($post_id, 'head_semester'), sp_val($post_id, 'head_year'), true); ?>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" @click="currentStep = 0"
                            class="border border-secondary/20 text-secondary px-6 py-3 rounded-full text-sm hover:border-primary/30 hover:text-primary transition flex items-center gap-2">
                            <i class="bi bi-arrow-left"></i> Back
                        </button>
                        <button type="button" @click="nextStep(1)"
                            class="bg-primary text-white px-6 py-3 rounded-full text-sm font-medium hover:bg-primary/80 transition flex items-center gap-2">
                            Next: Members <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- ════ STEP 2: MEMBERS ════ -->
                <div x-show="currentStep === 2" x-transition.opacity>

                    <!-- Member 1 -->
                    <div class="bg-white rounded-2xl shadow-sm p-6 mb-4 border border-secondary/10">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-full bg-secondary/20 text-secondary text-xs font-bold flex items-center justify-center">2</span>
                                <div>
                                    <p class="text-base font-semibold text-primary">Team Member 1</p>
                                    <p class="text-xs text-secondary">Optional — max 3 members including team leader</p>
                                </div>
                            </div>
                            <button type="button" @click="showM1 = !showM1"
                                class="text-xs border px-3 py-1.5 rounded-full transition"
                                :class="showM1 ? 'border-primary/30 text-primary bg-primary/5' : 'border-secondary/20 text-secondary hover:border-primary/30'">
                                <span x-text="showM1 ? 'Remove' : '+ Add Member 1'"></span>
                            </button>
                        </div>

                        <div x-show="showM1" x-transition.opacity>
                            <?php ssdc_field('Name',          'm1_name',  'Name',          sp_val($post_id, 'm1_name'));  ?>
                            <?php ssdc_field('Email Address', 'm1_email', 'Email Address', sp_val($post_id, 'm1_email'), 'email'); ?>
                            <?php ssdc_field('Mobile Number', 'm1_phone', 'Mobile Number', sp_val($post_id, 'm1_phone'), 'tel'); ?>
                            <?php ssdc_grade('m1_semester', 'm1_year', sp_val($post_id, 'm1_semester'), sp_val($post_id, 'm1_year')); ?>
                        </div>

                        <div x-show="!showM1" class="bg-light rounded-xl px-4 py-3 text-sm text-secondary/50 text-center">
                            Click "+ Add Member 1" to register a second team member
                        </div>
                    </div>

                    <!-- Member 2 -->
                    <div class="bg-white rounded-2xl shadow-sm p-6 mb-4 border border-secondary/10"
                        :class="!showM1 ? 'opacity-50 pointer-events-none' : ''">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-full bg-secondary/20 text-secondary text-xs font-bold flex items-center justify-center">3</span>
                                <div>
                                    <p class="text-base font-semibold text-primary">Team Member 2</p>
                                    <p class="text-xs text-secondary">Optional — requires Member 1 first</p>
                                </div>
                            </div>
                            <button type="button" @click="showM2 = !showM2" :disabled="!showM1"
                                class="text-xs border px-3 py-1.5 rounded-full transition"
                                :class="showM2 ? 'border-primary/30 text-primary bg-primary/5' : 'border-secondary/20 text-secondary hover:border-primary/30'">
                                <span x-text="showM2 ? 'Remove' : '+ Add Member 2'"></span>
                            </button>
                        </div>

                        <div x-show="showM2 && showM1" x-transition.opacity>
                            <?php ssdc_field('Name',          'm2_name',  'Name',          sp_val($post_id, 'm2_name')); ?>
                            <?php ssdc_field('Email Address', 'm2_email', 'Email Address', sp_val($post_id, 'm2_email'), 'email'); ?>
                            <?php ssdc_field('Mobile Number', 'm2_phone', 'Mobile Number', sp_val($post_id, 'm2_phone'), 'tel'); ?>
                            <?php ssdc_grade('m2_semester', 'm2_year', sp_val($post_id, 'm2_semester'), sp_val($post_id, 'm2_year')); ?>
                        </div>

                        <div x-show="!showM2 || !showM1" class="bg-light rounded-xl px-4 py-3 text-sm text-secondary/50 text-center">
                            <span x-show="!showM1">Please add Member 1 first</span>
                            <span x-show="showM1 && !showM2">Click "+ Add Member 2" to register a third team member</span>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" @click="currentStep = 1"
                            class="border border-secondary/20 text-secondary px-6 py-3 rounded-full text-sm hover:border-primary/30 hover:text-primary transition flex items-center gap-2">
                            <i class="bi bi-arrow-left"></i> Back
                        </button>
                        <button type="button" @click="nextStep(2)"
                            class="bg-primary text-white px-6 py-3 rounded-full text-sm font-medium hover:bg-primary/80 transition flex items-center gap-2">
                            Next: Lecturer <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- ════ STEP 3: LECTURER ════ -->
                <div x-show="currentStep === 3" x-transition.opacity>
                    <div class="bg-white rounded-2xl shadow-sm p-6 mb-4 border border-secondary/10">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-full bg-accent/20 text-accent flex items-center justify-center">
                                    <i class="bi bi-person-workspace text-xs"></i>
                                </span>
                                <div>
                                    <p class="text-base font-semibold text-primary">Lecturer / Mentor</p>
                                    <p class="text-xs text-secondary">Must</p>
                                </div>
                            </div>
                            <button type="button" @click="showLect = !showLect"
                                class="text-xs border px-3 py-1.5 rounded-full transition"
                                :class="showLect ? 'border-accent/30 text-accent bg-accent/5' : 'border-secondary/20 text-secondary hover:border-accent/30'">
                                <span x-text="showLect ? 'Remove' : '+ Add Lecturer'"></span>
                            </button>
                        </div>

                        <div x-show="showLect" x-transition.opacity>
                            <?php ssdc_field('Name',                          'lect_name',    'Name',                          sp_val($post_id, 'lect_name')); ?>
                            <?php ssdc_field('Faculty / School / Department', 'lect_faculty', 'Faculty / School / Department', sp_val($post_id, 'lect_faculty')); ?>

                            <div class="mb-4" x-data="{ lectTitle: '<?php echo esc_js(sp_val($post_id, 'lect_title')); ?>' }">
                                <label class="ssdc-label">Title</label>
                                <div class="relative">
                                    <select x-model="lectTitle"
                                        x-bind:name="lectTitle === 'Others (Please Mention)' ? '' : 'lect_title'"
                                        class="ssdc-select">
                                        <option value="">Choose</option>
                                        <?php foreach (['Dean', 'Head of Department', 'Professor', 'Senior Lecturer', 'Lecturer', 'Assistant Lecturer', 'Others (Please Mention)'] as $o) : ?>
                                            <option <?php selected(sp_val($post_id, 'lect_title'), $o); ?>><?php echo $o; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <i class="bi bi-chevron-down ssdc-chevron"></i>
                                </div>
                                <div x-show="lectTitle === 'Others (Please Mention)'" x-transition.opacity class="mt-2">
                                    <input type="text"
                                        x-bind:name="lectTitle === 'Others (Please Mention)' ? 'lect_title' : 'lect_title_other'"
                                        placeholder="Please specify the title"
                                        value="<?php echo sp_val($post_id, 'lect_title_other'); ?>"
                                        class="ssdc-input w-full" />
                                </div>
                            </div>

                            <?php ssdc_field('Email Address', 'lect_email', 'Email Address', sp_val($post_id, 'lect_email'), 'email'); ?>
                        </div>

                        <div x-show="!showLect" class="bg-light rounded-xl px-4 py-3 text-sm text-secondary/50 text-center">
                            Click "+ Add Lecturer" to register a supervising lecturer
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" @click="currentStep = 2"
                            class="border border-secondary/20 text-secondary px-6 py-3 rounded-full text-sm hover:border-primary/30 hover:text-primary transition flex items-center gap-2">
                            <i class="bi bi-arrow-left"></i> Back
                        </button>
                        <button type="button" @click="nextStep(3)"
                            class="bg-primary text-white px-6 py-3 rounded-full text-sm font-medium hover:bg-primary/80 transition flex items-center gap-2">
                            Next: Submission <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- ════ STEP 4: SUBMISSION ════ -->
                <div x-show="currentStep === 4" x-transition.opacity
                    x-data="{ fileName: '<?php echo esc_js($saved_file_name); ?>' }">

                   <!--  hiden submission -->
                    <div class="bg-white rounded-2xl shadow-sm p-6 mb-4 border border-secondary/10 hidden">
                        <div class="flex items-center gap-3 mb-5">
                            <span class="w-7 h-7 rounded-full bg-accent text-white text-xs flex items-center justify-center">
                                <i class="bi bi-upload"></i>
                            </span>
                            <div>
                                <p class="text-base font-semibold text-primary">Submit Your Work</p>
                                <p class="text-xs text-secondary">Upload a file or provide a Google Drive link</p>
                            </div>
                        </div>

                        <!-- Info box -->
                        <div class="bg-accent/5 border border-accent/10 rounded-xl p-4 mb-5 text-xs text-secondary leading-relaxed">
                            <p class="font-medium text-primary mb-1"><i class="bi bi-info-circle mr-1"></i> Submission Guidelines</p>
                            <ul class="space-y-1 list-disc list-inside">
                                <li>Accepted formats: PDF, ZIP, or RAR (max 5MB)</li>
                                <li>Or provide a Google Drive / OneDrive link with public access</li>
                                <li>File name must follow the format: <strong>TeamName_SSDC2026</strong></li>
                                <li>Submission deadline: <strong>July 19, 2026</strong></li>
                                <li>Submissions open from: <strong>May 21, 2026</strong></li>
                            </ul>
                        </div>

                        <?php if ($is_submission_closed) : ?>
                            <!-- ── SUBMISSION LOCKED ── -->
                            <div class="rounded-2xl border-2 border-dashed border-amber-200 bg-amber-50 p-6 text-center">
                                <i class="bi bi-clock-history text-3xl text-amber-400 block mb-2"></i>
                                <p class="text-sm font-semibold text-amber-700 mb-1">Submission Opens May 21, 2026</p>
                                <p class="text-xs text-amber-600 mb-3">You can complete all other steps now. File upload and link submission will be available starting May 21.</p>
                                <p class="text-xs font-bold text-amber-700" id="countdown-timer">Calculating...</p>
                            </div>
                        <?php else : ?>
                            <!-- Google Drive Link -->
                            <div class="mb-5">
                                <label class="ssdc-label">
                                    Submission Link
                                    <span class="font-normal text-secondary ml-1">(Google Drive / OneDrive)</span>
                                </label>
                                <input type="url" name="submission_link"
                                    placeholder="https://drive.google.com/..."
                                    value="<?php echo sp_val($post_id, 'submission_link'); ?>"
                                    class="ssdc-input w-full" />
                            </div>

                            <!-- Divider -->
                            <div class="flex items-center gap-3 mb-5">
                                <div class="flex-1 h-px bg-secondary/10"></div>
                                <span class="text-xs text-secondary">or upload file</span>
                                <div class="flex-1 h-px bg-secondary/10"></div>
                            </div>

                            <!-- Dropzone -->
                            <div
                                class="w-full rounded-2xl cursor-pointer border-2 border-dashed border-secondary/20 hover:border-primary/40 hover:bg-primary/5 transition-all duration-200 relative h-44 flex items-center justify-center"
                                @click="$refs.fileInput.click()"
                                @dragover.prevent="$el.classList.add('border-accent','bg-accent/5')"
                                @dragleave="$el.classList.remove('border-accent','bg-accent/5')"
                                @drop.prevent="e => { $el.classList.remove('border-accent','bg-accent/5'); const f=e.dataTransfer.files[0]; if(f) fileName=f.name; }">
                                <input type="file" name="submission_file" x-ref="fileInput"
                                    @change="e => { const f=e.target.files[0]; if(f) fileName=f.name; }"
                                    class="hidden" accept=".pdf,.zip,.rar" />

                                <div x-show="!fileName" class="text-center text-secondary">
                                    <svg class="w-10 h-10 mx-auto mb-2 text-secondary/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="text-sm font-medium">Drag & drop or click to upload</p>
                                    <p class="text-xs text-secondary/40 mt-1">PDF, ZIP, RAR (max 5MB)</p>
                                </div>

                                <div x-show="fileName" class="flex items-center gap-3 px-6 w-full">
                                    <span class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </span>
                                    <span class="text-sm text-primary font-medium truncate flex-1" x-text="fileName"></span>
                                    <button type="button" @click.stop="fileName=''; $refs.fileInput.value=''"
                                        class="w-6 h-6 rounded-full bg-secondary/10 hover:bg-red-100 flex items-center justify-center text-secondary hover:text-red-500 transition shrink-0">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($saved_file_url) : ?>
                            <div class="mt-3 flex items-center gap-2 bg-light rounded-xl px-4 py-2.5">
                                <i class="bi bi-paperclip text-secondary text-xs"></i>
                                <span class="text-xs text-secondary">Saved file:</span>
                                <a href="<?php echo esc_url($saved_file_url); ?>" target="_blank"
                                    class="text-xs text-primary hover:underline ml-1 flex-1 truncate">
                                    <?php echo esc_html($saved_file_name); ?>
                                </a>
                                <span class="text-xs text-secondary/40">Upload a new file to replace</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Review Summary -->
                    <div class="bg-white rounded-2xl shadow-sm border border-secondary/10 p-5 mb-5">
                        <p class="text-xs uppercase tracking-widest text-secondary mb-4">Review Summary</p>
                        <div class="space-y-2.5 text-sm">
                            <?php
                            $review = [
                                ['Team',        sp_val($post_id, 'team_name')        ?: '—'],
                                ['Institution', sp_val($post_id, 'institution_name') ?: '—'],
                                ['Country',     sp_val($post_id, 'country')          ?: '—'],
                                ['Head',        sp_val($post_id, 'head_name')        ?: '—'],
                            ];
                            foreach ($review as [$l, $v]) : ?>
                                <div class="flex items-center justify-between">
                                    <span class="text-secondary"><?php echo $l; ?></span>
                                    <span class="font-medium text-primary"><?php echo $v; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="flex justify-between pb-16">
                        <button type="button" @click="currentStep = 3"
                            class="border border-secondary/20 text-secondary px-6 py-3 rounded-full text-sm hover:border-primary/30 hover:text-primary transition flex items-center gap-2">
                            <i class="bi bi-arrow-left"></i> Back
                        </button>

                        <?php if ($is_submission_closed) : ?>
                            <!-- Save data only — no file/link yet -->
                            <button type="submit"
                                :disabled="isSubmitting"
                                onclick="document.getElementById('has_submission').value='0'"
                                class="bg-primary text-white px-8 py-3 rounded-full text-sm font-medium hover:bg-primary/80 active:scale-95 transition-all flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                                <span x-show="!isSubmitting">
                                    <i class="bi bi-floppy mr-1"></i>
                                    Save Registration
                                </span>
                                <span x-show="isSubmitting" class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    Saving...
                                </span>
                            </button>
                        <?php else : ?>
                            <!-- Full submit with file/link -->
                            <button type="submit"
                                :disabled="isSubmitting"
                                onclick="document.getElementById('has_submission').value='1'"
                                class="bg-primary text-white px-8 py-3 rounded-full text-sm font-medium hover:bg-primary/80 active:scale-95 transition-all flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                                <span x-show="!isSubmitting">
                                    <i class="bi bi-send mr-1"></i>
                                    <?php echo $is_edit ? 'Update Registration' : 'Submit Registration'; ?>
                                </span>
                                <span x-show="isSubmitting" class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    Submitting...
                                </span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

            </form>
        <?php endif; // not locked 
        ?>

    </div><!-- /max-w -->
</div><!-- /x-data -->

<!-- Toast -->
<div x-data x-show="$store.toast.show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 px-5 py-3 rounded-full bg-primary text-white text-sm font-medium shadow-2xl whitespace-nowrap">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
    </svg>
    <span x-text="$store.toast.message"></span>
</div>

<style>
    .ssdc-label {
        @apply block text-sm font-medium text-primary mb-1.5;
    }

    .ssdc-input {
        @apply bg-light border border-secondary/20 rounded-full px-5 py-3 text-sm text-primary placeholder-secondary/40 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary/50 transition w-full;
    }

    .ssdc-select {
        @apply bg-light border border-secondary/20 rounded-full px-5 py-3 text-sm text-primary focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary/50 transition w-full pr-10;
    }

    .ssdc-chevron {
        @apply absolute right-4 top-1/2 -translate-y-1/2 text-secondary/50 pointer-events-none text-xs;
    }
</style>

<script>
    // ── Countdown timer (Step 4 lock) ──
    (function() {
        const el = document.getElementById('countdown-timer');
        if (!el) return;
        const target = new Date('2026-05-21T00:00:00+07:00').getTime();

        function tick() {
            const diff = target - Date.now();
            if (diff <= 0) {
                el.textContent = 'Submissions are now open! Please refresh the page.';
                return;
            }
            const d = Math.floor(diff / 86400000);
            const h = Math.floor((diff % 86400000) / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            el.textContent = 'Opens in: ' + d + 'd ' + h + 'h ' + m + 'm ' + s + 's';
            setTimeout(tick, 1000);
        }
        tick();
    })();

    document.addEventListener('alpine:init', () => {
        Alpine.store('toast', {
            show: false,
            message: ''
        });

        Alpine.data('regForm', () => ({
            currentStep: 0,
            completedSteps: [],
            region: '<?php echo esc_js(sp_val($post_id, 'region')); ?>',
            countryOptions: [],
            showM1: <?php echo sp_val($post_id, 'm1_name')   ? 'true' : 'false'; ?>,
            showM2: <?php echo sp_val($post_id, 'm2_name')   ? 'true' : 'false'; ?>,
            showLect: <?php echo sp_val($post_id, 'lect_name') ? 'true' : 'false'; ?>,
            isSubmitting: false,

            countryMap: {
                north_asia: ['Japan', 'South Korea', 'China', 'Taiwan', 'Hongkong', 'Macau'],
                saarc: ['India', 'Pakistan', 'Bangladesh', 'Sri Lanka'],
                anz: ['Australia', 'New Zealand'],
                asean: ['Singapore', 'Malaysia', 'Philippines', 'Indonesia', 'Thailand', 'Vietnam'],
            },

            init() {
                if (this.region) this.updateCountries();

                <?php if ($is_edit) : ?>
                    this.completedSteps = [0, 1, 2, 3, 4];
                    this.currentStep = 0;
                <?php endif; ?>

                <?php if (!empty($errors)) : ?>
                    Alpine.store('toast').message = 'Please fix the errors below.';
                    Alpine.store('toast').show = true;
                    setTimeout(() => Alpine.store('toast').show = false, 3500);
                <?php endif; ?>
            },

            updateCountries() {
                this.countryOptions = this.countryMap[this.region] || [];
            },

            nextStep(step) {
                if (!this.completedSteps.includes(step)) {
                    this.completedSteps.push(step);
                }
                this.currentStep = step + 1;
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            },

            handleSubmit(form) {
                this.isSubmitting = true;
                form.submit();
            },
        }));
    });
</script>

<?php
// ── Helper functions ──
function ssdc_field($label, $name, $placeholder, $value = '', $type = 'text', $required = false)
{
    $req = $required ? '<span class="text-red-400">*</span>' : '';
    $req_attr = $required ? ' required' : '';
    echo "
    <div class='mb-4'>
        <label class='ssdc-label'>{$label} {$req}</label>
        <input type='{$type}' name='{$name}' placeholder='{$placeholder}' value='{$value}' class='ssdc-input'{$req_attr} />
    </div>";
}

function ssdc_grade($sem_name, $year_name, $sem_val = '', $year_val = '', $required = false)
{
    $req      = $required ? 'required' : '';
    $ast      = $required ? '<span class="text-red-400">*</span>' : '';
    echo "
    <div class='mb-4'>
        <label class='ssdc-label'>Grade {$ast}</label>
        <div class='grid grid-cols-2 gap-3'>
            <input type='text' name='{$sem_name}'  placeholder='Semester' value='{$sem_val}'  class='ssdc-input' {$req} />
            <input type='text' name='{$year_name}' placeholder='Admission Year'     value='{$year_val}' class='ssdc-input' {$req} />
        </div>
    </div>";
}
