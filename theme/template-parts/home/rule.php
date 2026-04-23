<?php
$args = [
    'post_type'      => 'rule',
    'posts_per_page' => -1,
    'order'          => 'ASC',
    'orderby'        => 'menu_order',
    'post_status'    => 'publish',
];

$rules = new WP_Query($args);

$nav_items = [];
$counter = 1;

// Kumpulkan data untuk navigasi
if ($rules->have_posts()) :
    while ($rules->have_posts()) : $rules->the_post();
        $nav_items[] = [
            'title' => get_the_title(),
            'slug'  => get_post_field('post_name', get_the_ID()),
        ];
    endwhile;
    wp_reset_postdata();
endif;

// Reset query
$rules = new WP_Query($args);
?>

<section id="rule" class="bg-gradient-to-br from-light via-primary/20 to-gray-100">
    <div class="border-b border-secondary">
        <div class="container-grid grid grid-cols-1 md:grid-cols-12 border-x divide-secondary">
            <div class="md:col-span-10 h-40"></div>
            <div class="md:col-span-2 h-40"></div>
        </div>
    </div>

    <div class="border-b border-secondary">
        <div class="container-grid grid grid-cols-1 md:grid-cols-12 border-x md:divide-x divide-secondary items-start">

            <!-- Content -->
            <div class="md:col-span-10 p-6">
                <?php if ($rules->have_posts()) : $counter = 1; while ($rules->have_posts()) : $rules->the_post();
                    $slug    = get_post_field('post_name', get_the_ID());
                    $title   = get_the_title();
                    $content = apply_filters('the_content', get_the_content());
                    $subtitle = get_field('subtitle'); // opsional ACF
                ?>
                    <div id="<?php echo esc_attr($slug); ?>" class="rule-item flex flex-wrap gap-5 mb-20">
                        <div class="shrink-0">
                           <!--  <h2 class="text-4xl lg:text-7xl font-bold"><?php // echo $counter; ?></h2> -->
                             <?php if (has_post_thumbnail()) : ?>
                                <img src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'full')); ?>" alt="<?php echo esc_attr(wp_strip_all_tags($title)); ?>" />
                            <?php endif; ?>

                        </div>
                        <div class="my-paragraph text-xl flex flex-col gap-5">
                            <h3 class="text-3xl lg:text-5xl mb-5 font-medium">
                                <?php echo wp_kses_post($title); ?>
                                <?php if ($subtitle) : ?>
                                    <span class="text-primary uppercase text-2xl"><?php echo esc_html($subtitle); ?></span>
                                <?php endif; ?>
                            </h3>
                            <?php echo $content; ?>
                        </div>
                    </div>
                <?php $counter++; endwhile; wp_reset_postdata(); endif; ?>
            </div>

            <!-- Sidebar Navigasi -->
            <div class="md:col-span-2 p-6 sticky top-6 flex justify-center items-center">
                <a href="#rule" class="text-xl">Back to Top <i class="bi bi-arrow-up"></i></a>
            </div>

        </div>
    </div>
</section>