<?php
$Qtity = $Model->qtity;
$Product = $Model->product;
$Update = $Model->update;
$HasSharedImage = $Product->hasSharedImage();
$Settings = \Lobuki\LobukiApp::CurrentSettings();
?>
<div id=basket_feedback>
<?php if ($Update) { ?>
<p class=OK>Vous avez <?=$Qtity?> exemplaire<?=$Qtity>1?'s':''?> de <em><?=$Product->title?></em> dans
<?= $Html->link('panier', '<strong>votre panier</strong>') ?>
</p>
<?php } ?>
</div>
<div id=imgs>
<ul id=medium_imgs>
<li><?= $Html->imageLink($Product->bigImage(1), $Html->image($Product->mediumImage(1), 'Aperçu n°1', NULL, 'curr_img', 300, NULL) . '<span class=zoom></span>', array('rel' => 'lightbox')) ?></li>
<?php
if ($HasSharedImage) {
    $nbOfImages = $Product->nbOfImages - 1;
    $sharedImage = $Product->sharedImage;
}
else {
    $nbOfImages = $Product->nbOfImages;
}
for ($i = 1; $i < $nbOfImages; $i++) {
    ?><li class=hide><?= $Html->imageLink($Product->bigImage($i + 1), $Html->image($Product->mediumImage($i + 1), 'Aperçu n°' . ($i + 1), NULL, NULL, 300, 300) . '<span class=zoom></span>', array('rel' => 'lightbox')) ?></li><?php
}
if ($HasSharedImage) {
    ?><li class=hide><?= $Html->imageLink($sharedImage->bigImage(), $Html->image($sharedImage->mediumImage(), 'Aperçu n°' . ($nbOfImages + 1), NULL, NULL, 300, 300) . '<span class=zoom></span>', array('rel' => 'lightbox')) ?></li><?php
}
?>
</ul>
<ul id=small_imgs>
<?php
for ($i = 0; $i < $nbOfImages; $i++) {
    //$class = 3 == $i ? ' class=last' : '';
    ?><li><?= $Html->image($Product->smallImage($i + 1), 'Miniature n°' . ($i + 1), NULL, NULL, 60, 60) ?></li><?php
}
if ($HasSharedImage) {
    //$class = 3 == $i ? ' class=last' : '';
    ?><li><?= $Html->image($sharedImage->smallImage(), 'Miniature n°' . ($nbOfImages + 1), NULL, NULL, 60, 60) ?></li><?php
}
?>
</ul>
</div>
<div id=details>
<h1 class="<?= $Model->category->collection->tag ?>"><?= $Product->title ?><?php if ($Product->isNew) { ?><span>nouveauté !</span><?php } ?></h1>
<?php if ($Product->subtitle) { ?>
<p class=subtitle><span><?= $Product->subtitle ?></span></p>
<?php } ?>
<p><?= $Product->description ?></p>
<ul id=product_chars>
<li><em>Dimensions :</em> <?= $Product->width ?> x <?= $Product->height ?> cm</li>
<li><em>Prix :</em> <?= $Product->price ?> euros</li>
</ul>
<form id=add_to_basket action="<?= $Settings->virtualPath() ?>panier" method=post>
<input type=hidden name=tag value="<?= $Product->tag ?>" />
<input type=hidden name=category value="<?= $Product->category ?>" />
<input type=hidden name=collection value="<?= $Product->collection ?>" />
<input type=hidden name=update value=<?= $Update ? 'true' : 'false' ?> />
<input type=hidden name=oqty value=<?= $Qtity ?> />
<input type=hidden name=ajax value=false />
<div>
<label for=qty>Quantité : </label><input maxlength=2 name=qty id=qty type=text value=<?= $Qtity ?> />
<div class=minusplus>
<span class=plus></span>
<span class=minus></span>
</div>
</div>
<?php
if ($Update) {
    $txt = 'Mettre à jour';
    $class = ' class=hide';
} else {
    $txt = 'Ajouter au panier !';
    $class = '';
}
?>
<p<?= $class ?> id=basket>
<input class=button type=submit value="<?= $txt ?>" />
</p>
</form>
</div>
<div class="clear"></div>
<?php
$productsInCategory = $Model->productsInCategory;
$count = count($productsInCategory);

if ($count > 1) {
?>
<div id=nav>
<h3>à voir aussi...</h3>
<ul>
<?php
    for ($i = 0; $i < $count; $i++) {
        $p = $productsInCategory[$i];

        if ($p->tag == $Product->tag) {
?><li><?= $Html->image($p->tinyImage(1), $p->shortTitle, NULL, NULL, 25, 25) ?></li><?php
        } else {
?><li><?= $Html->link($p->link(), $Html->image($p->tinyImage(1), $p->shortTitle, NULL, NULL, 25, 25)) ?></li><?php
        }
    }
}
?>
</ul>
<div class="clear"></div>
</div>
<div id=ajax_status>En cours...</div>
