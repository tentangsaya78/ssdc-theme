<?php

/**
 * ssdc functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package ssdc
 */

if (! defined('SSDC_VERSION')) {
	/*
	 * Set the theme’s version number.
	 *
	 * This is used primarily for cache busting. If you use `npm run bundle`
	 * to create your production build, the value below will be replaced in the
	 * generated zip file with a timestamp, converted to base 36.
	 */
	define('SSDC_VERSION', '0.1.0');
}

if (! defined('SSDC_TYPOGRAPHY_CLASSES')) {
	/*
	 * Set Tailwind Typography classes for the front end, block editor and
	 * classic editor using the constant below.
	 *
	 * For the front end, these classes are added by the `ssdc_content_class`
	 * function. You will see that function used everywhere an `entry-content`
	 * or `page-content` class has been added to a wrapper element.
	 *
	 * For the block editor, these classes are converted to a JavaScript array
	 * and then used by the `./javascript/block-editor.js` file, which adds
	 * them to the appropriate elements in the block editor (and adds them
	 * again when they’re removed.)
	 *
	 * For the classic editor (and anything using TinyMCE, like Advanced Custom
	 * Fields), these classes are added to TinyMCE’s body class when it
	 * initializes.
	 */
	define(
		'SSDC_TYPOGRAPHY_CLASSES',
		'prose prose-neutral max-w-none prose-a:text-primary'
	);
}

if (! function_exists('ssdc_setup')) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function ssdc_setup()
	{
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on ssdc, use a find and replace
		 * to change 'ssdc' to the name of your theme in all the template files.
		 */
		load_theme_textdomain('ssdc', get_template_directory() . '/languages');

		// Add default posts and comments RSS feed links to head.
		add_theme_support('automatic-feed-links');

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support('title-tag');

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support('post-thumbnails');

		// This theme uses wp_nav_menu() in two locations.
		register_nav_menus(
			array(
				'menu-1' => __('Primary', 'ssdc'),
				'menu-2' => __('Footer Menu', 'ssdc'),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support('customize-selective-refresh-widgets');

		// Add support for editor styles.
		add_theme_support('editor-styles');

		// Enqueue editor styles.
		add_editor_style('style-editor.css');

		// Add support for responsive embedded content.
		add_theme_support('responsive-embeds');

		// Remove support for block templates.
		remove_theme_support('block-templates');
	}
endif;
add_action('after_setup_theme', 'ssdc_setup');

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function ssdc_widgets_init()
{
	register_sidebar(
		array(
			'name'          => __('Footer', 'ssdc'),
			'id'            => 'sidebar-1',
			'description'   => __('Add widgets here to appear in your footer.', 'ssdc'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action('widgets_init', 'ssdc_widgets_init');

/**
 * Enqueue scripts and styles.
 */
function ssdc_scripts()
{
	wp_enqueue_style('ssdc-style', get_stylesheet_uri(), array(), SSDC_VERSION);
	wp_enqueue_script('ssdc-script', get_template_directory_uri() . '/js/script.min.js', array(), SSDC_VERSION, true);

	wp_enqueue_style('bootstrap-icon',  get_template_directory_uri() . '/assets/bootstrap-icons/font/bootstrap-icons.min.css', array(), SSDC_VERSION);

	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}
add_action('wp_enqueue_scripts', 'ssdc_scripts');

/**
 * Enqueue the block editor script.
 */
function ssdc_enqueue_block_editor_script()
{
	$current_screen = function_exists('get_current_screen') ? get_current_screen() : null;

	if (
		$current_screen &&
		$current_screen->is_block_editor() &&
		'widgets' !== $current_screen->id
	) {
		wp_enqueue_script(
			'ssdc-editor',
			get_template_directory_uri() . '/js/block-editor.min.js',
			array(
				'wp-blocks',
				'wp-edit-post',
			),
			SSDC_VERSION,
			true
		);
		wp_add_inline_script('ssdc-editor', "tailwindTypographyClasses = '" . esc_attr(SSDC_TYPOGRAPHY_CLASSES) . "'.split(' ');", 'before');
	}
}
add_action('enqueue_block_assets', 'ssdc_enqueue_block_editor_script');

/**
 * Add the Tailwind Typography classes to TinyMCE.
 *
 * @param array $settings TinyMCE settings.
 * @return array
 */
function ssdc_tinymce_add_class($settings)
{
	$settings['body_class'] = SSDC_TYPOGRAPHY_CLASSES;
	return $settings;
}
add_filter('tiny_mce_before_init', 'ssdc_tinymce_add_class');

/**
 * Limit the block editor to heading levels supported by Tailwind Typography.
 *
 * @param array  $args Array of arguments for registering a block type.
 * @param string $block_type Block type name including namespace.
 * @return array
 */
function ssdc_modify_heading_levels($args, $block_type)
{
	if ('core/heading' !== $block_type) {
		return $args;
	}

	// Remove <h1>, <h5> and <h6>.
	$args['attributes']['levelOptions']['default'] = array(2, 3, 4);

	return $args;
}
add_filter('register_block_type_args', 'ssdc_modify_heading_levels', 10, 2);

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

// inc/register-functions.php
require get_template_directory() . '/inc/register-functions.php';

// inc/participants-post-type.php
require get_template_directory() . '/inc/participants-post-type.php';

// inc//ajax-handler.php
require get_template_directory() . '/inc/ssdc-ajax-handler.php';




/* ===============================
*** ==========TANBAHAN ==========***
==================================*/
// disable admin bar

add_filter('show_admin_bar', '__return_false');


// enable svg upload media

function ssdc_mime_types($mimes)
{
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}

add_filter('upload_mimes', 'ssdc_mime_types');


// custom logo

add_theme_support('custom-logo');

// disable gutemberg 

add_filter('use_block_editor_for_post_type', '__return_false');

function ssdc_remove_gutenberg_support()
{
	remove_post_type_support('page', 'editor');
}
add_action('init', 'ssdc_remove_gutenberg_support');




// replace wordpress logo with custom logo on login page
function custom_login_logo()
{ ?>
	<style type="text/css">
		#login h1 a,
		.login h1 a {
			background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/assets/logo-ts.png');
			/* Sesuaikan path gambar */
			background-size: contain;
			width: 320px;
			/* Sesuaikan lebar logo Anda */
			height: 50px;
			/* Sesuaikan tinggi logo Anda */
		}
	</style>
<?php }
add_action('login_enqueue_scripts', 'custom_login_logo');

// Mengubah link logo agar mengarah ke home website, bukan wordpress.org
function custom_login_logo_url()
{
	return home_url();
}
add_filter('login_headerurl', 'custom_login_logo_url');

// custom style login 
function custom_login_styles()
{ ?>
	<style type="text/css">
		/* Mengubah background halaman login */
		body.login {
		
			font-family: 'Benton Sans', sans-serif;
		}

		/* Mengubah gaya form login */
		.login form {
			border-radius: 10px;
			box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
			background-color: #fff;
		}

		.login form .input,
		.login form input[type=checkbox],
		.login input[type=text] {
			background: #fff;
			border-radius: 8px;
		}

		/* Mengubah warna tombol login */
	
		.wp-core-ui .button.button-large {
			border-color: var(--color-light) !important;
			text-shadow: none !important;
			border-radius: 8px;
			padding: 20px 40px;
		}
	</style>
<?php }
add_action('login_enqueue_scripts', 'custom_login_styles');

// contact form
//==================================

// Menangani request AJAX
add_action('wp_ajax_handle_contact_form', 'handle_contact_form');
add_action('wp_ajax_nopriv_handle_contact_form', 'handle_contact_form');

function handle_contact_form() {
    // 1. Verifikasi Nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'contact_form_nonce')) {
        wp_send_json_error('Security check failed.');
    }

    // 2. Sanitasi Input
    $name    = sanitize_text_field($_POST['name']);
    $email   = sanitize_email($_POST['email']);
    $message = sanitize_textarea_field($_POST['message']);

    if (empty($name) || empty($email) || empty($message)) {
        wp_send_json_error('All fields are required.');
    }

    // 3. Kirim Email
    $to      = get_field('email', 'option'); // Email admin website 
    $subject = "New Contact Form Message from " . $name;
    $body    = "Name: $name\nEmail: $email\n\nMessage:\n$message";
    $headers = array('Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $email);

    if (wp_mail($to, $subject, $body, $headers)) {
        wp_send_json_success('Thank you! Your message has been sent.');
    } else {
        wp_send_json_error('Failed to send message. Please try again later.');
    }
}