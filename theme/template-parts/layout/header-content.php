<?php
/**
 * Template part for displaying the header content
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ssdc
 */

 $menu = [
	"Home"=>"/#home",
	"About"=>"/#about",
	"Judges"=>"/#judges",
	"Guideline & Rules"=>"/#rule",
	"FAQ"=>"/#frequently-asked-questions",
	"Registration & Submission"=>"/register",
	"SketchUp Ecosystems"=>"/#ss",
 ]
?>

<header id="masthead">

<div x-data="{ menuOpen: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 100">
    <div class="menu fixed z-5 top-2 lg:top-5 left-2 lg:left-6 transition-all duration-300"
        :class="[
            menuOpen ? 'rounded-xl p-4 lg:p-6 bg-white border border-secondary/20 ' : '',
            !menuOpen && scrolled ? 'rounded-xl p-2 bg-white shadow-md' : ''
        ]" x-transition >
        <div class="flex items-start gap-2">
            <button @click="menuOpen = !menuOpen" class="menu flex items-center gap-2 text-2xl ">
                <i class="text-4xl leading-0" :class="menuOpen ? 'bi bi-x' : 'bi bi-list'"></i>
                <span :class="menuOpen ? 'hidden' : ''" class="text-2xl">MENU</span>
            </button>

            <div x-show="menuOpen" x-transition>
                <ul class="flex flex-col gap-4">
					<?php foreach ($menu as $key => $value): ?>
						<li @click="menuOpen = false">
							<a href="<?php echo $value; ?>" class="text-2xl hover:text-accent duration-200">
								<?php echo $key; ?>
							</a>
						</li>
					<?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="fixed z-5 top-2 lg:top-5 right-2 lg:right-6 rounded-full p-3 bg-white border border-secondary/20 flex gap-2 items-center">
 <i class="bi bi-translate text-primary"></i> <span>Language</span>  <span class="p-2 rounded-full bg-primary text-white text-sm"> <?php echo do_shortcode('[gtranslate]'); ?></span>  
</div>

</header><!-- #masthead -->
