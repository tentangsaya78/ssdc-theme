<?php


/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package ssdc
 */

get_header();

$args = array(
    'post_type' => 'rule',
    'posts_per_page' => -1,
    'order' => 'ASC',
    'orderby' => 'menu_order',
    'post_status' => 'publish'
);
$rules = new WP_Query($args);

?>
<div class="bg-white min-h-screen">
    <div class="rule-header pt-40 pb-10 mb-20">
        <div class="container">
            <h1 class="text-3xl lg:text-6xl text-gray-500 font-bold"><?php the_title(); ?></h1>
        </div>
    </div>
    <div class="container pb-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-3 sticky top-40 self-start">
                <?php if ($rules->have_posts()): ?>
                    <ul class="rules-list ">
                        <?php while ($rules->have_posts()): $rules->the_post(); ?>
                            <li class="mb-3 <?php /* current rule */ echo get_the_ID() === get_queried_object_id() ? 'text-primary font-medium' : '' ?>">
                                <a href="<?php echo get_the_permalink(); ?>" class="text-lg hover:text-accent duration-200">
                                    <?php echo get_the_title(); ?>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="lg:col-span-9 content">
                <?php while (have_posts()) : the_post(); ?>
                    <?php the_content(); ?>
                <?php endwhile;

                if (is_singular('rule')): ?>
                    <div class="py-5 border-t border-gray-300 mt-20">
                        <?php
                        the_post_navigation(
                            array(
                                'next_text' => '<span aria-hidden="true" class="text-sm italic text-muted">' . __('Next ', 'ssdc') . '</span> ' .
                                    '<span class="sr-only">' . __('Next:', 'ssdc') . '</span> <br/>' .
                                    '<span class="font-medium">%title</span>',
                                'prev_text' => '<span aria-hidden="true" class="text-sm italic text-muted">' . __('Previous ', 'ssdc') . '</span> ' .
                                    '<span class="sr-only ">' . __('Previous:', 'ssdc') . '</span> <br/>' .
                                    '<span class="font-medium">%title</span>',
                            )
                        ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
<?php
get_footer();
?>
<style>
    .content p, .content ul li , .content ol li {
        margin-bottom: 1rem;
    }
    .rule-header {
        background-image: url('<?php echo get_template_directory_uri(); ?>/assets/grid-2.png');
        background-position: center;
    }
    .rules-list{

    }
</style>