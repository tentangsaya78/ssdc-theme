<section class="py-20 bg-primary">
<div class="container">
    <h4 class="text-center text-white"> Supported By</h4>
<?php
$supported_logo  = get_field('supported_logo');
if($supported_logo):?>
<div class="flex flex-wrap gap-4 justify-center items-center">
<?php 
foreach($supported_logo as $key => $value):?>
<div class="flex justify-center items-center w-[220px] h-[80px] px-4">
<img src="<?php echo $value['url'] ?>" alt=""  class="w-full h-[80px] object-contain">
</div>

<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</section>
