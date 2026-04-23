<?php
// Redirect jika sudah login
if (is_user_logged_in()) {
    wp_redirect(home_url('/dashboard'));
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
 

        // Validasi
        if (empty($username))   $errors[] = 'Username wajib diisi.';
        if (empty($fullname))   $errors[] = 'Nama lengkap wajib diisi.';
        if (empty($email))      $errors[] = 'Email wajib diisi.';
        if (!is_email($email))  $errors[] = 'Format email tidak valid.';
        if (empty($password))   $errors[] = 'Password wajib diisi.';
        if (strlen($password) < 8) $errors[] = 'Password minimal 8 karakter.';
        if ($password !== $password2) $errors[] = 'Konfirmasi password tidak cocok.';
        if (username_exists($username)) $errors[] = 'Username sudah digunakan.';
        if (email_exists($email))       $errors[] = 'Email sudah terdaftar.';

        if (empty($errors)) {
            $user_id = wp_create_user($username, $password, $email);

            if (is_wp_error($user_id)) {
                $errors[] = $user_id->get_error_message();
            } else {
                // Set role contributor
                $user = new WP_User($user_id);
                $user->set_role('contributor');

                // Simpan meta tambahan
                update_user_meta($user_id, 'first_name', $fullname);

                // Kirim email notifikasi
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
                <h2 class="text-2xl font-semibold mb-2">Registrasi Berhasil!</h2>
                <p class="text-gray-500 mb-6">Silakan cek email Anda untuk detail akun dan langkah selanjutnya.</p>
                <a href="<?php echo wp_login_url(); ?>" class="inline-block bg-primary text-white px-6 py-3 font-medium hover:bg-primary/90 transition">
                    Login Sekarang
                </a>
            </div>

        <?php else : ?>
            <!-- Form -->
            <div class="bg-white shadow-xl p-8">
                <h2 class="text-2xl font-semibold mb-6">Buat Akun</h2>

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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            name="fullname"
                            value="<?php echo esc_attr($_POST['fullname'] ?? ''); ?>"
                            class="w-full border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-primary text-sm"
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
                            class="w-full border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-primary text-sm"
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
                            class="w-full border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-primary text-sm"
                            placeholder="john@example.com"
                            required
                        />
                    </div>

                 

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <input
                            type="password"
                            name="password"
                            class="w-full border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-primary text-sm"
                            placeholder="Minimal 8 karakter"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
                        <input
                            type="password"
                            name="password2"
                            class="w-full border border-gray-300 px-4 py-2.5 focus:outline-none focus:border-primary text-sm"
                            placeholder="Ulangi password"
                            required
                        />
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-primary text-white py-3 font-medium hover:bg-primary/90 transition text-sm tracking-wide"
                    >
                        Daftar Sekarang
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-6">
                    Sudah punya akun?
                    <a href="<?php echo wp_login_url(); ?>" class="text-primary hover:underline">Login di sini</a>
                </p>
            </div>
        <?php endif; ?>

    </div>
</section>