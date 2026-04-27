<?php
/**
 * Template part for displaying the header content
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ssdc
 */

$current_user = wp_get_current_user();
$user_id      = $current_user->ID;
$is_logged_in = is_user_logged_in();

$menu = [
    "Home"                    => "/#home",
    "About"                   => "/#about",
    "Judges"                  => "/#judges",
    "Guideline & Rules"       => "/#rule",
    "FAQ"                     => "/#frequently-asked-questions",
    "Registration & Submission" => "/register",
    "SketchUp Ecosystems"     => "/#ss",
];
?>

<header id="masthead">

    <!-- ===== HAMBURGER MENU (kiri) ===== -->
    <div x-data="{ menuOpen: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 100">
        <div class="menu fixed z-50 top-2 lg:top-5 left-2 lg:left-6 transition-all duration-300"
            :class="[
                menuOpen ? 'rounded-xl p-4 lg:p-6 bg-white border border-secondary/20 shadow-lg' : '',
                !menuOpen && scrolled ? 'rounded-xl p-2 bg-white shadow-md' : ''
            ]" x-transition>
            <div class="flex items-start gap-2">
                <button @click="menuOpen = !menuOpen" class="menu flex items-center gap-2 text-2xl">
                    <i class="text-4xl leading-none" :class="menuOpen ? 'bi bi-x' : 'bi bi-list'"></i>
                    <span :class="menuOpen ? 'hidden' : ''" class="text-2xl">MENU</span>
                </button>

                <div x-show="menuOpen" x-transition>
                    <ul class="flex flex-col gap-4">
                        <?php foreach ($menu as $key => $value) : ?>
                            <li @click="menuOpen = false">
                                <a href="<?php echo esc_url($value); ?>" class="text-2xl hover:text-accent duration-200">
                                    <?php echo esc_html($key); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>

                        <!-- Login/Logout di dalam menu mobile -->
                        <li class="border-t border-secondary/20 pt-4 mt-2">
                            <?php if ($is_logged_in) : ?>
                                <div class="flex flex-col gap-2">
                                    <span class="text-sm opacity-60 flex items-center gap-2">
                                        <i class="bi bi-person-circle text-lg"></i>
                                        <?php echo esc_html($current_user->display_name); ?>
                                    </span>
                                    <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>"
                                        class="text-sm text-red-500 hover:text-red-700 flex items-center gap-2 duration-200">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </a>
                                </div>
                            <?php else : ?>
                                <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>"
                                    class="text-2xl hover:text-accent duration-200 flex items-center gap-2">
                                    <i class="bi bi-box-arrow-in-right"></i> Login
                                </a>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== USER AREA (kanan, desktop) ===== -->
    <div class="fixed z-50 top-2 lg:top-5 right-2 lg:right-6 flex items-center gap-2">

        <?php if ($is_logged_in) : ?>
            <!-- Sudah login: tampilkan username + tombol logout -->
            <div class="flex items-center gap-2 rounded-full pl-3 pr-1 py-1 bg-white border border-secondary/20">
                <i class="bi bi-person-circle text-primary text-lg"></i>
                <span class="text-sm font-medium hidden md:block max-w-[120px] truncate">
                    <?php echo esc_html($current_user->display_name); ?>
                </span>
                <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>"
                    class="p-1.5 rounded-full bg-primary text-white hover:bg-primary/80 transition-colors duration-200 shrink-0 flex items-center justify-center w-8 h-8"
                    title="Logout">
                    <i class="bi bi-box-arrow-right text-xs leading-none"></i>
                </a>
            </div>

        <?php else : ?>
            <!-- Belum login: tampilkan tombol Login -->
            <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>"
                class="flex items-center gap-2 rounded-full pl-3 pr-1 py-1 bg-white border border-secondary/20 hover:shadow-md transition-all duration-200 text-sm font-medium">
                <i class="bi bi-person text-primary text-lg"></i>
                <span class="hidden md:block">Login</span>
                <span class="p-1.5 rounded-full bg-primary text-white">
                    <i class="bi bi-box-arrow-in-right text-xs leading-none"></i>
                </span>
            </a>
        <?php endif; ?>

        <!-- ===== LANGUAGE SWITCHER ===== -->
        <div class="rounded-full pl-3 bg-white border border-secondary/20 flex gap-2 items-center">
            <i class="bi bi-translate text-primary"></i>
            <span class="hidden md:block text-sm">Language</span>
            <span class="p-2 rounded-full bg-primary text-white text-sm">
                <?php echo do_shortcode('[gtranslate]'); ?>
            </span>
        </div>
    </div>

</header><!-- #masthead -->