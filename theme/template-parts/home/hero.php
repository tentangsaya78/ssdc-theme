<?php
// custom logo url
if( has_custom_logo() ) {
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    $custom_logo_url = wp_get_attachment_url( $custom_logo_id );
}
$logo_hero = get_field( 'logo_hero' );
$title = get_field( 'hero_title' );
$sub_title = get_field( 'hero_sub_title' );
$date = get_field( 'date' );
$hero_location = get_field( 'hero_location' );
$organizer  = get_field( 'organizer' );

$button = get_field( 'hero_button' );

?>

<section id="home" class="hero pt-32 pb:40 lg:pb-80 ">
    <div class="px-6 lg:px-10 mb-8">
        <div class="max-w-[870px]">
            <?php if($logo_hero): ?>
            <img src="<?php echo $logo_hero['url']; ?>" class="max-w-[229px] mb-6 lg:mb-10" alt="" />
            <?php endif; ?>
            <?php 
            if ( $title ) {
              
                echo '<h1 class="text-4xl md:text-5xl lg:text-7xl font-medium">' . wp_kses_post( $title ) . '</h1>';
            }
            ?>
            <?php if($sub_title): ?>
            <p class="text-3xl md:text-4xl lg:text-5xl text-primary  font-medium mb-6 lg:mb-6 uppercase pt-4 lg:pt-10">
               <?php echo $sub_title; ?>
            </p>
            <?php endif; ?>
            <?php if($date): ?>
            <p class="text-2xl font-medium">
               <?php echo $date; ?>
            </p>
            <?php endif; ?>
            <?php if($hero_location): ?>
            <p class="text-xl ">
               <?php echo $hero_location; ?>
            </p>
            <?php endif; ?>
        </div>
    </div>
   
    <div class="flex justify-between items-center flex-wrap gap-6 lg:gap-12">
         <?php if($organizer): ?>
        <div
            class="p-6 border-4 lg:border-l-0  bg-white border-primary max-w-max w-full lg:w-4/6 lg:pr-20"
        >
            <div class="flex gap-4 lg:gap-12 flex-wrap justify-center text-center lg:text-left">
                <div>
                    <span> Organized by </span>
                    <img
                        src="<?php echo $organizer['organized_by']['url']; ?>"
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


