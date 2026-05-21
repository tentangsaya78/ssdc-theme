<?php


$email = get_field('email', 'option');
$instagram = get_field('instagram', 'option');
$logo_footer = get_field('logo_footer', 'option');

// tambahan 
$videos = get_field('videos');

$video = get_field('video');
$video_thumbnail = get_field('video_thumbnail');

?>
<div x-data="{openContact: false}">

    <!-- tambahan -->
    <!-- TEST TAMBAHAN -->
    <section class="py-16 bg-primary" x-data="{ modalOpen: false, embedUrl: '', fileUrl: '', isExternal: false }">

        <div class="splide" id="video-slider" aria-label="Video Gallery">
            <div class="splide__track">
                <ul class="splide__list">
                    <?php if ($videos) : foreach ($videos as $video_item) :
                            $use_video_url   = $video_item['use_video_url'];
                            $video_url       = $video_item['video_url'];
                            $video           = $video_item['video'];
                            $video_thumbnail = $video_item['video_thumbnail'];
                    ?>
                            <li class="splide__slide">
                                <button
                                    class="relative flex justify-center items-center focus:outline-none group w-full"
                                    @click="isExternal = <?php echo $use_video_url ? 'true' : 'false'; ?>; embedUrl = '<?php echo $use_video_url ? get_embed_url($video_url) : ''; ?>'; fileUrl = '<?php echo !$use_video_url ? esc_url($video['url']) : ''; ?>'; modalOpen = true"
                                    aria-label="Watch the video">
                                    <img
                                        class="shadow-2xl w-full h-[460px] object-cover"
                                        src="<?php echo esc_url($video_thumbnail['url']); ?>"
                                        width="768" height="432"
                                        alt="<?php echo esc_attr($video_thumbnail['alt']); ?>" />
                                    <svg class="absolute pointer-events-none" xmlns="http://www.w3.org/2000/svg" width="72" height="72">
                                        <circle class="fill-primary/40" cx="36" cy="36" r="36" fill-opacity=".8" />
                                        <path class="fill-secondary" d="M44 36a.999.999 0 0 0-.427-.82l-10-7A1 1 0 0 0 32 29V43a.999.999 0 0 0 1.573.82l10-7A.995.995 0 0 0 44 36Z" />
                                    </svg>
                                </button>
                            </li>
                    <?php endforeach;
                    endif; ?>
                </ul>
            </div>
        </div>

        <!-- Backdrop -->

        <div
            style="display:none"
            x-show="modalOpen"
            @click="modalOpen = false; embedUrl = ''; fileUrl = '';"
            class="fixed inset-0 z-[99998] bg-black bg-opacity-50"
            aria-hidden="true">
        </div>

        <!-- Modal -->
        <div
            style="display:none"
            x-show="modalOpen"
            @keydown.escape.window="modalOpen = false; embedUrl = ''; fileUrl = '';"
            class="fixed inset-0 z-[99999] flex items-center justify-center px-4 md:px-6 py-6 pointer-events-none">
            <div class="w-full max-w-5xl aspect-video bg-black shadow-2xl overflow-hidden pointer-events-auto">
                <template x-if="isExternal">
                    <iframe :src="embedUrl" class="w-full h-full" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                </template>
                <template x-if="!isExternal">
                    <video x-effect="modalOpen ? $el.play() : ($el.pause(), $el.currentTime = 0)" class="w-full h-full" loop controls autoplay>
                        <source :src="fileUrl" type="video/mp4" />
                    </video>
                </template>
            </div>
        </div>

    </section>

    <!-- Contact -->
    <div class="container-full flex flex-wrap justify-center gap-3 py-10">
        <p class=" lg:text-3xl w-full text-center ">Check Us Out</p>
        <div class=" flex lg:flex-col lg:justify-start justify-center items-center"></div>
        <a
            href="<?php echo $instagram['url']; ?>" target="_blank"
            class="flex items-center gap-2 lg:text-xl hover:text-accent duration-200">
            <i class="bi bi-instagram lg:text-xl"></i>
            <?php echo $instagram['title']; ?>
        </a>
        <button
            @click="openContact = true"
            class="flex items-center gap-2 lg:text-xl hover:text-accent duration-200  cursor-pointer">
            <i class="bi bi-envelope lg:text-xl"></i>
            <span>Please Send Message</span>
        </button>
    </div>

    <!-- END TEST TAMBAHAN -->
    <section x-show="openContact" class="bg-gray-100 py-20 px-6 fixed top-0 left-0 z-50 w-full h-full overflow-y-scroll flex justify-center items-center">
        <div class="max-w-md w-full mx-auto relative">
            <button @click="openContact = false" class="absolute top-4 right-4">
                <i class="bi bi-x text-3xl"></i>
            </button>
            <?php get_template_part('template-parts/home/contact'); ?>
        </div>
    </section>
</div>