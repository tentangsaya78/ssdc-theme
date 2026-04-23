<?php
$args = [
    'post_type'      => 'judge',
    'posts_per_page' => -1,
    'order'          => 'ASC',
    'orderby'        => 'menu_order',
    'post_status'    => 'publish',
];

$judges = new WP_Query($args);

$colors = ['bg-[#676D7D] text-white', 'bg-primary text-white', 'bg-accent'];
$i = 0;
?>

<section id="judges">
    <div class="container-full mb-9">
        <h2 class="section-title text-center">Meet The Judges</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
        <?php if ($judges->have_posts()) : while ($judges->have_posts()) : $judges->the_post();
            $photo  = get_the_post_thumbnail_url(get_the_ID(), 'full');
            $name   = get_field('judge_name') ?: get_the_title();
            $title  = get_field('description');
            $from   = get_field('location');
            $profile = get_the_content();
            $color  = get_field('color');
            $id     = get_the_ID();
        ?>
            <div
                class="flex flex-col <?php echo esc_attr($color); ?> group cursor-pointer"
                x-data
                @click="$dispatch('open-judge', { id: <?php echo $id; ?> })"
            >
                <img
                    src="<?php echo esc_url($photo); ?>"
                    alt="<?php echo esc_attr(wp_strip_all_tags($name)); ?>"
                    class="w-full aspect-square group-hover:opacity-80 duration-200"
                />
                <div class="p-6 h-full flex flex-col justify-between gap-6">
                    <div>
                        <h2 class="text-7xl"><?php echo wp_kses_post($name); ?></h2>
                        <p class="text-xl"><?php echo esc_html($title); ?></p>
                    </div>
                    <p class="text-lg font-medium"><?php echo esc_html($from); ?></p>
                </div>
            </div>
        <?php $i++; endwhile; wp_reset_postdata(); endif; ?>
    </div>
</section>

<!-- Popup / Modal -->
<?php
// Reset query untuk ambil data popup
$judges_popup = new WP_Query($args);
$judges_data = [];
$profile = apply_filters('the_content', get_the_content());

if ($judges_popup->have_posts()) : while ($judges_popup->have_posts()) : $judges_popup->the_post();
    $j = 0;
    $color = get_field('color');
    $judges_data[] = [
        'id'      => get_the_ID(),
        'photo'   => get_the_post_thumbnail_url(get_the_ID(), 'full'),
        'name'    => get_field('judge_name') ?: get_the_title(),
        'title'   => get_field('description'),
        'from'    => get_field('location'),
         'profile' => apply_filters('the_content', get_the_content()), // <-- fix di sini
        'color'   => get_field('color'),
    ];
    $j++;
endwhile; wp_reset_postdata(); endif;
?>

<div
    x-data="<?php echo esc_attr(json_encode([
        'open'   => false,
        'judge'  => null,
        'judges' => $judges_data,
    ])); ?>"
    x-init="window.addEventListener('open-judge', (e) => {
        judge = judges.find(j => j.id === e.detail.id);
        open = true;
    })"
    x-show="open"
    x-transition.opacity
    @keydown.escape.window="open = false"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
    @click.self="open = false"
>
    <div
        x-show="open"
        x-transition.scale
        class="bg-white shadow-2xl max-w-lg w-full overflow-hidden"
        @click.stop
    >
        <!-- Color bar -->
        <div class="h-1.5" :class="judge?.color?.split(' ')[0]"></div>

        <!-- Header -->
        <div class="flex items-start gap-4 p-6">
            <img
                :src="judge?.photo"
                :alt="judge?.name"
                class="w-16 h-16 rounded-full object-cover"
            />
            <div class="flex-1">
                <h3
                    class="text-2xl font-semibold leading-tight"
                    x-html="judge?.name"
                ></h3>
                <p class="text-sm text-gray-500 mt-1" x-text="judge?.title"></p>
            </div>
            <button
                @click="open = false"
                class="text-gray-400 hover:text-gray-600 transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="px-6 pb-6">
            <span class="inline-flex items-center gap-1 text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full mb-4">
                📍 <span x-text="judge?.from"></span>
            </span>
            <div
                class="text-sm text-gray-600 leading-relaxed flex flex-col gap-4" 
                x-html="judge?.profile"
            > </div>
        </div>
    </div>
</div>