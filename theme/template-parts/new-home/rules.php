<section id="rule" class="py-20">
<?php
$args = array(
    'post_type' => 'rule',
    'posts_per_page' => -1,
    'order' => 'ASC',
    'orderby' => 'menu_order',
    'post_status' => 'publish'
);

$rule = new WP_Query($args);

if ($rule->have_posts()) :  ?>
    <div class="container ">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($rule->have_posts()) : $rule->the_post();
                $rule_title = get_the_title();
                $rule_slug = get_post_field('post_name', get_the_ID());
                $rule_content = get_the_content();
                $rule_link = get_the_permalink();
                $rule_image = get_the_post_thumbnail_url(get_the_ID(), 'full');
            ?>
                <a href="<?php echo esc_url($rule_link); ?>" class="rule-item relative  bg-white rounded-lg border border-gray-300  h-full max-h-[400px] overflow-hidden group">
                    <div class="absolute top-0 inset-0 h-full w-full bg-gradient-to-t  from-black via-black/30 to-transparent group-hover:opacity-0 duration-300"></div>
                    <div class="rule-item__image ">
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php echo esc_url($rule_image); ?>" alt="<?php echo esc_attr($rule_title); ?>" class="w-full h-full object-cover group-hover:scale-105 duration-300">
                        <?php endif; ?>
                    </div>
                    <div class="rule-item__content p-4 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-20 text-2xl lg:text-4xl text-white text-center">
                        <h4 class="rule-item__title">
                            <?php echo esc_html($rule_title); ?>
                        </h4>
                    </div>
                </a>
            <?php
            endwhile; ?>
        </div>
    </div>
<?php wp_reset_postdata();
endif;
?>
</section>