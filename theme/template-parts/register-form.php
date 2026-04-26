<?php
// Redirect if already logged in
if (is_user_logged_in()) {
    wp_redirect(home_url('/editor-dashboard'));
    exit;
}

$errors   = [];
$success  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ssdc_register_nonce'])) {
    if (!wp_verify_nonce($_POST['ssdc_register_nonce'], 'ssdc_register_action')) {
        $errors[] = 'Security check failed.';
    } else {
        $username  = sanitize_user($_POST['username'] ?? '');
        $email     = sanitize_email($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        $fullname  = sanitize_text_field($_POST['fullname'] ?? '');

        // Validation
        if (empty($username))   $errors[] = 'Username is required.';
        if (empty($fullname))   $errors[] = 'Full name is required.';
        if (empty($email))      $errors[] = 'Email is required.';
        if (!is_email($email))  $errors[] = 'Invalid email format.';
        if (empty($password))   $errors[] = 'Password is required.';
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $password2) $errors[] = 'Password confirmation does not match.';
        if (username_exists($username)) $errors[] = 'Username is already taken.';
        if (email_exists($email))       $errors[] = 'Email is already registered.';

        if (empty($errors)) {
            $user_id = wp_create_user($username, $password, $email);

            if (is_wp_error($user_id)) {
                $errors[] = $user_id->get_error_message();
            } else {
                // Set role contributor
                $user = new WP_User($user_id);
                $user->set_role('contributor');

                // Save additional meta
                update_user_meta($user_id, 'first_name', $fullname);

                // Send notification email
                ssdc_send_welcome_email($user_id, $email, $fullname, $username, $password);

                $success = true;
            }
        }
    }
}
?>

<section class="min-h-screen bg-gradient-to-br from-light via-primary/10 to-gray-100 flex items-center justify-center py-16 px-4">
    <div class="w-full max-w-lg">

        <!-- Logo / Branding -->
        <div class="text-center mb-10">
            <?php if (has_custom_logo()) : 
                $custom_logo_url = wp_get_attachment_url(get_theme_mod('custom_logo'));?>
                <img src="<?php echo $custom_logo_url; ?>" alt="" class="max-w-40 mx-auto">

            <?php else : ?>
                <h1 class="text-3xl font-bold"><?php bloginfo('name'); ?></h1>
            <?php endif; ?>
        </div>

        <?php if ($success) : ?>
            <!-- Success State -->
            <div class="bg-white shadow-xl p-8 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-medium mb-2">Registration Successful!</h2>
                <p class="text-gray-500 mb-6">Please check your email for account details and next steps.</p>
                <a href="<?php echo wp_login_url(); ?>" class="inline-block bg-primary text-white px-6 py-3 font-medium hover:bg-primary/90 transition">
                    Login Now
                </a>
            </div>

        <?php else : ?>
            <!-- Form -->
            <div class="bg-white shadow-xl p-8 rounded-lg">
                <h2 class="text-xl font-medium mb-6">Create an Account</h2>

                <?php if (!empty($errors)) : ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 mb-6 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            <?php foreach ($errors as $error) : ?>
                                <li><?php echo esc_html($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <?php wp_nonce_field('ssdc_register_action', 'ssdc_register_nonce'); ?>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            name="fullname"
                            value="<?php echo esc_attr($_POST['fullname'] ?? ''); ?>"
                            class="w-full border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-primary text-sm rounded-full"
                            placeholder="John Doe"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            name="username"
                            value="<?php echo esc_attr($_POST['username'] ?? ''); ?>"
                            class="w-full border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-primary text-sm rounded-full"
                            placeholder="johndoe"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input
                            type="email"
                            name="email"
                            value="<?php echo esc_attr($_POST['email'] ?? ''); ?>"
                            class="w-full border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-primary text-sm rounded-full"
                            placeholder="john@example.com"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <input
                            type="password"
                            name="password"
                            class="w-full border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-primary text-sm rounded-full"
                            placeholder="Minimum 8 characters"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                        <input
                            type="password"
                            name="password2"
                            class="w-full border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-primary text-sm rounded-full"
                            placeholder="Repeat password"
                            required
                        />
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-primary text-white py-3 font-medium hover:bg-primary/90 transition text-sm tracking-wide rounded-full"
                    >
                        Register Now
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-6">
                    Already have an account?
                    <a href="<?php echo wp_login_url(); ?>" class="text-primary hover:underline">Login here</a>
                </p>
            </div>
        <?php endif; ?>

    </div>
</section>