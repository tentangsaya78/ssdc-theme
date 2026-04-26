<?php
/**
 * Kirim welcome email ke user baru
 */
function ssdc_send_welcome_email($user_id, $email, $fullname, $username, $password) {
    $site_name = get_bloginfo('name');
    $login_url = wp_login_url();
    $admin_email = get_option('admin_email');

    // ── Email ke User ──────────────────────────────────────────
    $user_subject = "🎉 Selamat datang di {$site_name} – Registrasi Berhasil!";

    $user_message = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; }
        .header { background: #1a1a2e; padding: 32px 40px; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; }
        .header p { color: #aaaacc; margin: 6px 0 0; font-size: 13px; }
        .body { padding: 40px; }
        .body h2 { font-size: 20px; color: #111; margin-bottom: 16px; }
        .body p { color: #444; font-size: 14px; line-height: 1.7; margin-bottom: 12px; }
        .info-box { background: #f8f9ff; border-left: 4px solid #4f46e5; padding: 16px 20px; margin: 24px 0; }
        .info-box p { margin: 4px 0; font-size: 13px; color: #333; }
        .info-box strong { color: #111; }
        .btn { display: inline-block; background: #4f46e5; color: #ffffff !important; text-decoration: none; padding: 12px 28px; font-size: 14px; font-weight: 600; margin: 20px 0; }
        .footer { background: #f4f4f4; padding: 20px 40px; font-size: 12px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class='wrapper'>
        <div class='header'>
            <h1>{$site_name}</h1>
            <p>SketchUp Students Design Competition 2026</p>
        </div>
        <div class='body'>
            <h2>Halo, {$fullname}! 👋</h2>
            <p>Happy! You have successfully registered as a participant in the <strong>SketchUp Students Design Competition 2026</strong>. We are very happy to have you join.</p>

            <p>Below are your account details:</p>

            <div class='info-box'>
                <p><strong>Username:</strong> {$username}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Password:</strong> {$password}</p>
                <p><strong>Role:</strong> Participant (Contributor)</p>
            </div>

            <p>Log in immediately to complete your profile and get the latest information about the competition.</p>

            <a href='{$login_url}' class='btn'>Login to My Account →</a>

            <p style='font-size:12px; color:#999;'>For security reasons, we recommend changing your password immediately after the first login.</p>
        </div>
        <div class='footer'>
            &copy; " . date('Y') . " {$site_name} &nbsp;|&nbsp; Do not reply to this email directly.
        </div>
    </div>
</body>
</html>
";

    // ── Email ke Admin ──────────────────────────────────────────
    $admin_subject = "[{$site_name}] New participants register: {$fullname}";

    $admin_message = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; }
        .header { background: #1a1a2e; padding: 24px 32px; }
        .header h1 { color: #fff; margin: 0; font-size: 18px; }
        .body { padding: 32px; }
        .body p { color: #444; font-size: 14px; line-height: 1.7; }
        .info-box { background: #f8f9ff; border-left: 4px solid #4f46e5; padding: 14px 18px; margin: 20px 0; }
        .info-box p { margin: 4px 0; font-size: 13px; }
        .btn { display: inline-block; background: #4f46e5; color: #fff !important; text-decoration: none; padding: 10px 22px; font-size: 13px; font-weight: 600; margin-top: 16px; }
        .footer { background: #f4f4f4; padding: 16px 32px; font-size: 11px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class='wrapper'>
        <div class='header'>
            <h1>Notifikasi Admin – Peserta Baru</h1>
        </div>
        <div class='body'>
            <p>New participants have registered at <strong>{$site_name}</strong>:</p>
            <div class='info-box'>
                <p><strong>Name:</strong> {$fullname}</p>
                <p><strong>Username:</strong> {$username}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Registration Date:</strong> " . current_time('d M Y, H:i') . " WIB</p>
            </div>
            <a href='" . admin_url('users.php') . "' class='btn'>View on Dashboard →</a>
        </div>
        <div class='footer'>
            &copy; " . date('Y') . " {$site_name}
        </div>
    </div>
</body>
</html>
";

    $headers = ['Content-Type: text/html; charset=UTF-8', "From: {$site_name} <{$admin_email}>"];

    // Kirim ke user
    wp_mail($email, $user_subject, $user_message, $headers);

    // Kirim ke admin
    wp_mail($admin_email, $admin_subject, $admin_message, $headers);
}


/**
 * Nonaktifkan email default WordPress saat register
 */
add_filter('wp_new_user_notification_email', '__return_false');
add_filter('wp_new_user_notification_email_admin', '__return_false');


/**
 * Shortcode [ssdc_register]
 */
add_shortcode('ssdc_register', function() {
    ob_start();
    get_template_part('template-parts/register-form');
    return ob_get_clean();
});