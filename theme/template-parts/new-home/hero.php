<?php
// custom logo url
if (has_custom_logo()) {
    $custom_logo_id = get_theme_mod('custom_logo');
    $custom_logo_url = wp_get_attachment_url($custom_logo_id);
}
$logo_hero = get_field('logo_hero');
$title = get_field('hero_title');
$sub_title = get_field('hero_sub_title');
$date = get_field('date');
$hero_location = get_field('hero_location');
$organizer  = get_field('organizer');

$button = get_field('hero_button');

?>
<div x-data="{openContact : false}">
    <section id="home" class="hero">
        <div
            class="hero-wrap py-20  bg-top-right bg-contain bg-no-repeat">
            <div class="container">
                <div
                    class="grid grid-cols-1 md:grid-cols-6 gap-4 items-center lg:min-h-screen">
                    <div class="kiri md:col-span-4 flex flex-col gap-4">
                        <img
                            src="<?php echo $logo_hero['url']; ?>"
                            alt=""
                            class="w-full h-auto max-w-[160px]" />
                        <div class="mb-6">
                            <h1 class="hero-title font-bold text-3xl lg:text-6xl mb-3"><?php echo $title; ?></h1>
                            <h2 class="hero-subtitle text-primary text-2xl lg:text-4xl uppercase font-medium"><?php echo $sub_title ?></h2>
                        </div>

                        <div class="md:col-span-2 gap-6 flex flex-col">
                            <div class="flex flex-wrap gap-4 lg:gap-6">
                                <div class="">
                                    <div class="text-xl font-medium p-2 max-w-max mb-2 date relative">
                                        <div class="aksen-date absolute top-0 left-0 w-full h-full bg-accent"></div>
                                        <span class="relative z-20"> <?php echo $date; ?> </span>

                                    </div>
                                    <p class="italic hero-location"><?php echo $hero_location; ?></p>
                                </div>
                                <!-- sponsored -->
                                <div class="flex flex-wrap gap-4  hero-organizer">
                                    <?php if ($organizer): ?>
                                        <div>
                                            <p class="font-medium mb-2 text-gray-500">
                                                Organized by
                                            </p>
                                            <img
                                                src="<?php echo $organizer['organized_by']['url'] ?>"
                                                alt=""
                                                class="w-full h-auto max-w-[160px]" />
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-500 mb-3">
                                                Sponsored by
                                            </p>
                                            <img
                                                src="<?php echo $organizer['sponsored_by']['url'] ?>"
                                                alt=""
                                                class="w-full h-auto max-w-[160px]" />
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>


                            <!--    button -->
                            <div class="flex flex-wrap gap-6 items-center hero-button">
                                <a href="<?php echo esc_url('/register', 'ssdc') ?>" class="btn btn-primary">Register Now</a>
                                <button @click="openContact = true" class="btn btn-outline cursor-pointer">Contact Us</button>
                            </div>


                        </div>
                    </div>
                    <div class="kanan"></div>
                </div>
            </div>
        </div>
    </section>

    <section x-show="openContact" class="bg-gray-100 py-20 px-6 fixed top-0 left-0 z-50 w-full h-full overflow-y-scroll flex justify-center items-center">
        <div class="max-w-md w-full mx-auto relative">
            <button @click="openContact = false" class="absolute top-4 right-4">
                <i class="bi bi-x text-3xl"></i>
            </button>
            <?php get_template_part('template-parts/home/contact'); ?>
        </div>
    </section>
</div>
<style>
    .hero {
        background-color: #fff;
        background-image: url('<?php echo get_template_directory_uri(); ?>/assets/grid-2.png');
    }

    .hero-wrap {
        background-image: url('<?php echo get_template_directory_uri() ?>/assets/bg-hero-2.svg');
    }

    .date {
        position: relative;
    }
</style>