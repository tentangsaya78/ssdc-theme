<?php
$title = get_field('about_title');
$sub_title = get_field('about_sub_title');
$tagline = get_field('tagline');
$about_description = get_field('about_description');
$menus = get_field('about_menu');
?>
<section x-data="{openContact: false}" id="about" class="py-16">
    <div class="container ">
        <div class="grid grid-cols-1 md:grid-cols-6 items-center gap-6 lg:gap-10">
            <div class="md:col-span-4">
                <?php if ($title): ?>
                    <h2 class="section-title font-bold text-gray-400 fade-up"><?php echo $title; ?></h2>
                <?php endif; ?>
                <?php if ($sub_title): ?>
                    <h3 class="text-2xl font-medium mb-5 tracking-[10%]"><?php echo $sub_title; ?></h3>
                <?php endif; ?>
                <?php if ($tagline): ?>
                    <p class="text-xl lg:text-5xl font-medium uppercase mb-10"> <?php echo $tagline; ?></p>
                <?php endif; ?>
                <?php if ($about_description): ?>
                    <div class=" flex flex-col gap-6">
                        <?php echo $about_description; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="md:col-span-2">
                <ul class="flex flex-col justify-end  gap-4 ">
                    <?php if ($menus): ?>
                        <?php foreach ($menus as $key => $menu): ?>
                            <li>
                                <a href="<?php echo $menu['link']['url']; ?>" class="flex gap-3 lg:text-3xl font-bold text-gray-500 hover:text-accent duration-200 group">
                                    <i class="bi bi-arrow-up-right"> </i>
                                    <?php echo $menu['link']['title']; ?>
                                </a>
                            </li>
                    <?php endforeach;
                    endif; ?>
                    <li>
                        <button @click="openContact = true" class="flex gap-3 lg:text-3xl font-bold text-gray-500  hover:text-accent duration-200 group cursor-pointer">
                            <i class="bi bi-arrow-up-right"> </i>
                            Contact</button>
                    </li>
                </ul>
            </div>
        </div>

    </div>

    <!--  popup contact -->
    <div x-show="openContact" class="bg-gray-100 py-20 px-6 fixed top-0 left-0 z-50 w-full h-full overflow-y-scroll flex justify-center items-center">
        <div class="max-w-md w-full mx-auto relative">
            <button @click="openContact = false" class="absolute top-4 right-4">
                <i class="bi bi-x text-3xl"></i>
            </button>
            <?php get_template_part('template-parts/home/contact'); ?>
        </div>
    </div>

</section>