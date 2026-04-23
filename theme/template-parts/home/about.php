<?php
 $title = get_field('about_title');
 $sub_title = get_field('about_sub_title');
 $tagline = get_field('tagline');
 $about_description = get_field('about_description');
 $menus = get_field('about_menu');
?>
<section id="about">
    <div class="px-6 lg:px-10 pb-40">
        <div class="flex flex-wrap justify-between gap-10 items-end xl:pr-12">
            <div class="w-full max-w-[850px]">
                <div class="flex flex-col gap-0">
                    <?php if($title): ?>
                    <h2 class="section-title leading-none"><?php echo $title; ?></h2>
                    <?php endif; ?>
                    <?php if($sub_title): ?>
                    <h3 class="text-8xl font-medium mb-5 tracking-[10%]"><?php echo $sub_title; ?></h3>
                    <?php endif; ?>
                  <?php if($tagline): ?>
                    <p class="text-3xl lg:text-5xl font-medium uppercase mb-10"> <?php echo $tagline; ?></p>
                    <?php endif; ?>
                    <?php if($about_description): ?>
                    <div class="text-xl text-justify tracking-[10%] flex flex-col gap-6">
                        <?php echo $about_description; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex justify-end w-full max-w-[360px]">
                <ul class="flex flex-col justify-end  gap-4 ">
                    <?php if($menus): ?>
                        <?php foreach ($menus as $key => $menu): ?>
                          <li>
                                <a href="<?php echo $menu['link']['url']; ?>" class="flex gap-3 text-3xl hover:text-accent duration-200 group">
                                    <i class="bi bi-arrow-up-right"> </i>
                                    <?php echo $menu['link']['title']; ?>
                                </a>
                            </li>  
                  <?php endforeach; 
                endif; ?>
                    
                </ul>
            </div>
        </div>
    </div>
</section>