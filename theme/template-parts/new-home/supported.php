<section class="py-20 bg-primary">
    <div class="container">
        <h4 class="text-center text-white"> Supported By</h4>
        <?php
        $supported_logo  = get_field('supported_logo');
        if ($supported_logo): ?>
            <div id="supported" class="splide">
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php
                        foreach ($supported_logo as $key => $value): ?>
                            <li  class="splide__slide flex justify-center items-center w-[220px] h-[80px] px-4">
                                <img src="<?php echo $value['url'] ?>" alt="" class="w-full h-[80px] object-contain">
                        </li>

                        <?php endforeach; ?>
                    </ul>
                </div>

            </div>

        <?php endif; ?>
    </div>
</section>