<section id="ss" class="bg-primary text-white/70 pt-16 lg:pt-28">
    <div class="container-full pb-16 lg:pb-28">
        <h2 class="text-3xl lg:text-5xl text-white/50 fade-up font-bold">
            SketchUp <br>
            Ecosystems
        </h2>
        <div class="flex flex-col gap-2 mt-5 lg:text-xl">
             <a href="https://help.sketchup.com/en/predesign-sketchup" target="_blank" class="group flex gap-2 items-center">Predesign Information <i class="bi bi-arrow-right group-hover:translate-x-2 duration-200"></i></a>
              <a href="https://help.sketchup.com/en/sketchup-education/using-sketchup-higher-education-and-universities" target="_blank" class="group flex gap-2 items-center">Using SketchUp Studio for Higher Education
 <i class="bi bi-arrow-right group-hover:translate-x-2 duration-200"></i></a>
        </div>
       
    </div>

    <div id="splide-ecosystem" class="splide" aria-label="Ecosystem Carousel">
        <div class="splide__track">
            <ul class="splide__list">
                <?php
                $args = array(
                    'post_type'      => 'ecosystem',
                    'posts_per_page' => -1,
                    'status'         => 'publish'
                );
                $ecosystem_query = new WP_Query($args);

                if ($ecosystem_query->have_posts()) :
                    while ($ecosystem_query->have_posts()) : $ecosystem_query->the_post(); 
                        $image_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                        ?>
                        <li class="splide__slide lg:w-[960px]">
                            <?php if ($image_url) : ?>
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" class="w-full h-auto mb-5" />
                            <?php endif; ?>
                            <h4 class="text-3xl lg:text-5xl font-medium text-center">
                                <?php the_title(); ?>
                            </h4>
                        </li>
                    <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </ul>
        </div>
    </div>

    <div class="flex items-center gap-6 justify-center pt-10 pb-20">
        <button id="prevBtn" class="text-4xl" aria-label="Previous slide">
            <i class="bi bi-chevron-left font-medium"></i>
        </button>
        <button id="nextBtn" class="text-4xl" aria-label="Next slide">
            <i class="bi bi-chevron-right font-medium"></i>
        </button>
    </div>
</section>