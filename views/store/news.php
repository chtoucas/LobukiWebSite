<h1>Nouveautés</h1>
<ul>
<?php
$products = $Model->products;
$j = 0;
for ($i = 0, $count = count($products); $i < $count; $i++) {
    $j++;
    if (4 == $j ) { $j = 0; $class = ' class=last'; }
    else { $class = ''; }
    $product = $products[$i];
    $imgClass = 'S' == $product->shape ? 'square' : 'rectangle';
?><li<?= $class ?>><?= $Html->link($product->link(), $Html->image($product->previewImage(), $product->shortTitle, NULL, $imgClass), array('title' => $product->shortTitle)) ?></li>
<?php } ?>
</ul>
<div class=clear></div>
