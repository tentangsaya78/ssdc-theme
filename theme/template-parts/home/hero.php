<?php
// custom logo url
if( has_custom_logo() ) {
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    $custom_logo_url = wp_get_attachment_url( $custom_logo_id );
}

$title = get_field( 'hero_title' );
$sub_title = get_field( 'hero_sub_title' );
$date = get_field( 'date' );
$organizer  = get_field( 'organizer' );

$button = get_field( 'hero_button' );

?>

<section id="home" class="hero pt-32 pb-80 ">
    <div class="px-6 lg:px-10 mb-8">
        <div class="max-w-[870px]">
            <img src="<?php echo $custom_logo_url; ?>" class="max-w-[229px] mb-14" alt="" />
            <?php 
            if ( $title ) {
              
                echo '<h1 class="text-4xl md:text-5xl lg:text-7xl font-medium">' . wp_kses_post( $title ) . '</h1>';
            }
            ?>
            <?php if($sub_title): ?>
            <p class="text-2xl lg:text-4xl font-medium mb-14 uppercase pt-10">
               <?php echo $sub_title; ?>
            </p>
            <?php endif; ?>
            <?php if($date): ?>
            <p class="text-2xl font-medium">
               <?php echo $date; ?>
            </p>
            <?php endif; ?>
        </div>
    </div>
   
    <div class="flex justify-between items-center flex-wrap gap-6 lg:gap-12">
         <?php if($organizer): ?>
        <div
            class="p-6 border-4 border-l-0 bg-white border-primary max-w-max w-full lg:w-4/6 lg:pr-20"
        >
            <div class="flex gap-4 lg:gap-12 flex-wrap">
                <div>
                    <span> organized by </span>
                    <img
                        src="<?php echo $organizer['organized_by']['url']; ?>"
                        class="w-[200px] h-[50px] object-contain"
                        alt=""
                    />
                </div>
                <div>
                    <span> Suported by </span>
                     <img
                        src="<?php echo $organizer['suported_by']['url']; ?>"
                        class="w-[200px] h-[50px] object-contain"
                        alt=""
                    />
                </div>
                <div>
                    <span> Sponsored by </span>
                     <img
                        src="<?php echo $organizer['sponsored_by']['url']; ?>"
                        class="w-[200px] h-[50px] object-contain"
                        alt=""
                    />
                </div>
            </div>
        </div>
        <?php endif; ?> 
      <?php if($button): ?>
        <a href="<?php echo $button['url']; ?>" class="bg-accent lg:rounded-l-full p-6 pl-8 w-full lg:w-2/6 h-max flex items-center text-4xl font-medium hover:bg-primary hover:text-white transition duration-200 uppercase">
           <?php echo $button['title']; ?>
        </a>
        <?php endif; ?>
    </div>
</section>


