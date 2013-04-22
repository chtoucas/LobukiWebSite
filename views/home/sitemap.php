<h1>Plan du site</h1>
<ul class=map>
<li><?= $Html->link('', 'Accueil') ?></li>
<li><?= $Html->link('qui-est-lobuki', 'Qui est Lobuki ?') ?></li>
<li><?= $Html->link('contact', 'Contact') ?></li>
<li><?= $Html->link('actualite-lobuki', 'Actualité Lobuki') ?></li>
<li><?= $Html->link('nouveautes', 'Nouveautés') ?></li>
<li><?= $Html->link('bon-cadeau', 'Bon cadeau') ?></li>
<li><?= $Html->link('sticker-mural/personnalisation', 'Personnalisez votre sticker mural') ?></li>
<li><?= $Html->link('points-de-vente', 'Points de vente') ?></li>
<li><?= $Html->link('paiement', 'Paiement') ?></li>
<li><?= $Html->link('panier', 'Panier') ?></li>
<li><?= $Html->link('conditions-generales-de-vente', 'CGV') ?></li>
<li><?= $Html->link('mode-d-emploi', 'Sticker, mode d\'emploi') ?></li>
<li><?= $Html->link('questions-frequentes', 'FAQ') ?></li>
</ul>
<?php
$categories  = $Model->categories;
$collections = $Model->collections;

for ($i = 0, $count = count($collections); $i < $count; $i++) {
?>
<?php
    $collection = $collections[$i];
    if (array_key_exists($collection->tag, $categories)) {
?><div class=map>
<h3><?= $collection->shortTitle ?></h3><?php
        $sub = $categories[$collection->tag];

?><ul><?php
?><li><?= $Html->link($collection->link(), 'Tous les produits') ?></li><?php
        for ($j = 0, $subcount = count($sub); $j < $subcount; $j++) {
            $category = $sub[$j];
?><li><?= $Html->link($category->link(), $category->title) ?></li><?php
        }
?></ul></div><?php
    }
}
?>
<div class="clr"></div>
