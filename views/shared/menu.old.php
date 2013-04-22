<ul id=menu_categories class=clr>
<?php /* <li class=more><span id=sticker-mural class=menu_title>Sticker mural</span><span></span> */ ?>
<li class=more><?= $Html->link('sticker-mural', 'Sticker mural', array('id' => 'sticker-mural')) ?><span></span>
<ul class=rose>
<li><?= $Html->link('sticker-mural/90-euros', '90 €') ?></li>
<li><?= $Html->link('sticker-mural/45-euros', '45 €') ?></li>
<li><?= $Html->link('sticker-mural/20-euros', '20 €') ?></li>
<li><?= $Html->link('sticker-mural/4-euros', '4 €') ?></li>
<li><?= $Html->link('sticker-mural/personnalisation', 'personnalisation') ?></li>
<?php /* <li><?= $Html->link('sticker-mural', 'tous les stickers') ?></li> */ ?>
</ul>
</li>
<?php /* <li class=more><span id=affiche class=menu_title>Affiche</span><span></span> */ ?>
<li class=more><?= $Html->link('affiche', 'Affiche', array('id' => 'affiche')) ?><span></span>
<ul class=green>
<li><?= $Html->link('affiche/12-euros', '12 €') ?></li>
</ul>
</li>
<?php /*
<li class=more><span id=textile class=menu_title>Textile</span><span></span>
<ul class=blue>
<li><?= $Html->link('textile/body', 'body') ?></li>
<li><?= $Html->link('textile/tee-shirt', 'tee shirt') ?></li>
<li><?= $Html->link('textile/sac-en-toile', 'sac en toile') ?></li>
<li><?= $Html->link('textile/personnalisation', 'personnalisation') ?></li>
</ul>
</li>
<li class=more><span id=papeterie class=menu_title>Papeterie</span><span></span>
<ul class=red>
<li><?= $Html->link('papeterie/etiquettes-cahier', 'étiquettes cahier') ?></li>
</ul>
</li>
 */ ?>
<li class=single><?= $Html->link('bon-cadeau', 'Bon cadeau', array('id' => 'store_gift')) ?><span></span></li>
<li class=single><?= $Html->link('nouveautes', 'Nouveautés', array('id' => 'store_news')) ?><span></span></li>
<li class="last single rel"><?= $Html->link('panier', 'Panier', array('id' => 'store_basket')) ?><span></span></li>
</ul>
