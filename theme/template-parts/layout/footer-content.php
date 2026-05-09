<?php

/**
 * Template part for displaying the footer content
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ssdc
 */

$email = get_field('email', 'option');
$instagram = get_field('instagram', 'option');
$logo_footer = get_field('logo_footer', 'option');
?>

<footer x-data="{openContact:false}" id="colophon">
    <section class="bg-primary text-white pt-20 pb-3 border-t-2 border-white/20">
        <div class="container-full">
            <div class="flex flex-wrap justify-between gap-6 lg:gap-12 mb-20">
                <img src="<?php echo $logo_footer['url']; ?>" alt="" class="w-[180px] lg:max-w-[300px] h-auto">
                <div>
                    <h4 class="">Contact Info</h4>
                    <ul>
                        <li>
                            <button
                                @click="openContact = true"
                                class="flex items-center gap-2  hover:text-accent duration-200">
                                <i class="bi bi-envelope lg:text-xl"></i>
                                <span>Please Send Message</span>
                            </button>
                        </li>
                        <li>
                            <a href="<?php echo $instagram['url']; ?>" class=" flex items-center gap-3 hover:text-accent duration-200 group:">
                                <i class="bi bi-instagram lg:text-xl"> </i>
                                <span><?php echo $instagram['title']; ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 justify-center items-center">
                <p class="text-sm ">Copyright All Rights Reserved</p>
                <a href="#" class="text-sm  lg:text-center"> Back to Top <i class="bi bi-arrow-up"></i></a>
                <div></div>
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
</footer><!-- #colophon -->