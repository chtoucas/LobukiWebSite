<ul id=menu_categories class=clr>
<li class=more><?= $Html->link('sticker-mural/', 'Sticker mural', array('id' => 'sticker-mural')) ?><span></span>
<ul class=rose>
<li><?= $Html->link('sticker-mural/90-euros/', '90 €') ?></li>
<li><?= $Html->link('sticker-mural/45-euros/', '45 €') ?></li>
<li><?= $Html->link('sticker-mural/20-euros/', '20 €') ?></li>
<li><?= $Html->link('sticker-mural/4-euros/', '4 €') ?></li>
<li><?= $Html->link('sticker-mural/personnalisation', 'personnalisation') ?></li>
</ul>
</li>
<li class=more><?= $Html->link('affiche/', 'Affiche', array('id' => 'affiche')) ?><span></span>
<ul class=green>
<li><?= $Html->link('affiche/12-euros/', '12 €') ?></li>
</ul>
</li>
<li class=single><?= $Html->link('bon-cadeau', 'Bon cadeau', array('id' => 'store_gift')) ?><span></span></li>
<li class=single><?= $Html->link('nouveautes', 'Nouveautés', array('id' => 'store_news')) ?><span></span></li>
<li class="last single rel"><?= $Html->link('panier', 'Panier', array('id' => 'store_basket')) ?><span></span></li>
</ul>
