<?php
$Products = $Model->products;
$Count = count($Products);
$Pager = $Model->pager;
$PageIndex = $Pager->pageIndex;
?>
<h1 class="<?= $Model->collection->tag ?>"><?= $Model->collection->title ?></h1>
<ul id=products>
<?php
$j = 0;
for ($i = 0; $i < $Count; $i++) {
    $product = $Products[$i];
?><li><?= $Html->link($product->link(), $Html->image($product->smallImage(1), $product->shortTitle, NULL, NULL, 60, 60), array('title' => $product->shortTitle)) ?></li>
<?php } ?>
</ul>
<div class=clear></div>
<?php if (0 == $Count) {
?><p>Bientôt disponible</p><?
} ?>
<p class=nav>
<?php if (!$Pager->isFirstPage()) { ?>
<?= $Html->link($Model->collection->link($PageIndex - 1), 'précédents') ?>
<?php } ?>
<?php if (!$Pager->isFirstPage() && !$Pager->isLastPage()) { ?>
<?= $Html->image('bullet-pink.png', NULL, NULL, 'bullet_pink') ?>
<?php } ?>
<?php if (!$Pager->isLastPage()) { ?>
<?= $Html->link($Model->collection->link($PageIndex + 1), 'la suite...') ?>
<?php } ?>
</p>
<div class=clear></div>
