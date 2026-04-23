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

<footer id="colophon">
<section class="bg-primary text-white pt-20 pb-3 border-t-2 border-white/20">
    <div class="container-full">
        <div class="flex justify-between gap-6 lg:gap-12 mb-20">
            <img src="<?php echo $logo_footer['url']; ?>" alt="" class="max-w-[300px] h-auto">
            <div>
            <h4 class="text-5xl">Contact Info</h4>
            <ul>
                <li>
                    <a href="mailto:<?php echo $email; ?>" class="text-3xl flex items-center gap-3 hover:text-accent duration-200 group">
                        <i class="bi bi-envelope-fill"> </i>
                        <span><?php echo $email; ?></span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo $instagram['url']; ?>" class="text-3xl flex items-center gap-3 hover:text-accent duration-200 group:">
                        <i class="bi bi-instagram"> </i>
                        <span><?php echo $instagram['title']; ?></span>
                    </a>
                </li>
            </ul>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 justify-center items-center">
<p class="text-xl">Copyright All Rights Reserved</p>
<a href="#" class="text-xl text-center"> Back to Top <i class="bi bi-arrow-up"></i></a>
<div></div>
        </div>
        
    </div>
</section>

</footer><!-- #colophon -->
