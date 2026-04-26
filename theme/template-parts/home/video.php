<?php
$video = get_field('video');
$video_thumbnail = get_field('video_thumbnail');

$email = get_field('email', 'option');
$instagram = get_field('instagram', 'option');
$logo_footer = get_field('logo_footer', 'option');
?>
<div x-data="{openContact: false}">
    <section class="w-full pb-12 lg:pb-40">
        <div class="relative font-inter antialiased">
            <div
                class="relative flex flex-col justify-center bg-primary py-10 overflow-hidden">
                <div class="w-full max-w-6xl mx-auto px-4 md:px-6">
                    <div class="flex justify-center">
                        <!-- Modal video component -->
                        <div
                            class="[&_[x-cloak]]:hidden"
                            x-data="{ modalOpen: false }">
                            <!-- Video thumbnail -->
                            <button
                                class="relative flex justify-center items-center cursor-pointer focus:outline-none focus-visible:ring focus-visible:ring-indigo-300 group"
                                @click="modalOpen = true"
                                aria-controls="modal"
                                aria-label="Watch the video">
                                <img
                                    class="shadow-2xl transition-shadow duration-300 ease-in-out"
                                    src="<?php echo $video_thumbnail['url']; ?>"
                                    width="768"
                                    height="432"
                                    alt="Modal video thumbnail" />
                                <!-- Play icon -->
                                <svg
                                    class="absolute pointer-events-none group-hover:scale-110 transition-transform duration-300 ease-in-out"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="72"
                                    height="72">
                                    <circle
                                        class="fill-primary/40"
                                        cx="36"
                                        cy="36"
                                        r="36"
                                        fill-opacity=".8"></circle>
                                    <path
                                        class="fill-secondary drop-shadow-2xl"
                                        d="M44 36a.999.999 0 0 0-.427-.82l-10-7A1 1 0 0 0 32 29V43a.999.999 0 0 0 1.573.82l10-7A.995.995 0 0 0 44 36V36c0 .001 0 .001 0 0Z"></path>
                                </svg>
                            </button>
                            <!-- End: Video thumbnail -->

                            <!-- Modal backdrop -->
                            <div
                                class="fixed inset-0 z-[99999] bg-black bg-opacity-50 transition-opacity"
                                x-show="modalOpen"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-out duration-100"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                aria-hidden="true"
                                x-cloak>
                            </div>
                            <!-- End: Modal backdrop -->

                            <!-- Modal dialog -->
                            <div
                                id="modal"
                                class="fixed inset-0 z-[99999] flex px-4 md:px-6 py-6"
                                role="dialog"
                                aria-modal="true"
                                x-show="modalOpen"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-75"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-out duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-75"
                                x-cloak>
                                <div
                                    class="max-w-5xl mx-auto h-full flex items-center">
                                    <div
                                        class="w-full max-h-full shadow-2xl aspect-video bg-black overflow-hidden"
                                        @click.outside="modalOpen = false"
                                        @keydown.escape.window="modalOpen = false">
                                        <video
                                            x-init="$watch('modalOpen', value => value ? $el.play() : $el.pause())"
                                            width="1920"
                                            height="1080"
                                            loop
                                            controls>
                                            <source
                                                src="<?php echo $video['url']; ?>"
                                                type="video/mp4" />
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                </div>
                            </div>
                            <!-- End: Modal dialog -->
                        </div>
                        <!-- End: Modal video component -->
                    </div>
                </div>
            </div>
        </div>
        <div class="container-full mt-9 flex flex-col gap-3">
            <p class="text-3xl">Check Us Out</p>
            <div class="flex lg:flex-col lg:justify-start justify-center items-center"></div>
            <a
                href="<?php echo $instagram['url']; ?>"
                class="flex items-center gap-2 text-xl hover:text-accent duration-200">
                <i class="bi bi-instagram text-3xl"></i>
                <?php echo $instagram['title']; ?>
            </a>
            <button
                @click="openContact = true"
                class="flex items-center gap-2 text-xl hover:text-accent duration-200">
                <i class="bi bi-envelope text-3xl"></i>
                <span>Please Send Message</span>
            </button>
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