<?php
$Products = $Model->products;
$Count = count($Products);
$Pager = $Model->pager;
$PageIndex = $Pager->pageIndex;
?>
<h1 class="<?= $Model->category->collection->tag ?>"><?= $Model->category->title ?></h1>
<ul id=products>
<?php
$j = 0;
for ($i = 0; $i < $Count; $i++) {
    $j++;
    if (4 == $j ) { $j = 0; $class = ' class=last'; }
    else { $class = ''; }
    $product = $Products[$i];
    $imgClass = 'S' == $product->shape ? 'square' : 'rectangle';
?><li<?= $class ?>><?= $Html->link($product->link(), $Html->image($product->previewImage(), $product->shortTitle, NULL, $imgClass), array('title' => $product->shortTitle)) ?>
</li>
<?php } ?>
</ul>
<div class=clear></div>
<?php if (0 == $Count) {
?><p>Bientôt disponible</p><?
} ?>
<p class=nav>
<?php if (!$Pager->isFirstPage()) { ?>
<?= $Html->link($Model->category->link($PageIndex - 1), 'précédents...') ?>
<?php } ?>
<?php if (!$Pager->isFirstPage() && !$Pager->isLastPage()) { ?>
<?= $Html->image('bullet-pink.png', NULL, NULL, 'bullet_pink') ?>
<?php } ?>
<?php if (!$Pager->isLastPage()) { ?>
<?= $Html->link($Model->category->link($PageIndex + 1), 'la suite...') ?>
<?php } ?>
</p>
<div class=clear></div>
<?php /*
<script>
window.onload = function() { var img = new Image();
<?php
for ($i = 0; $i < $Count; $i++) {
    $product = $Products[$i];
?>
img.src = '<?= $Path->image($product->tinyImage()) ?>';
<?php } ?>
};
</script>
 */?>
