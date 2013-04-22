<?php
$Order = $Model->order;
$Products = $Model->products;
$Count = count($Products);
$Settings = \Lobuki\LobukiApp::CurrentSettings();
?>
<h1>Votre Panier</h1>
<div class="para clr">
<h2><span>Livraison en</span> France Métropolitaine</h2>
<p><a class=underline href="/contact">Contactez-nous</a> si vous voulez vous faire livrer en
dehors de la France métropolitaine ou <strong>payer par chèque</strong>.
Tous les prix affichés sont TTC.</p>
</div>
<div class=clear></div>
<?php if ($Count > 0) { ?>
<table id=products>
<thead>
<tr>
<th></th>
<th><span>produit</span></th>
<th><span>quantité</span></th>
<th><span>prix</span></th>
<th></th>
</tr>
</thead>
<tbody>
<?php
for ($i = 0; $i < $Count; $i++) {
    $product = $Products[$i];
?>
<tr>
<td class=preview><?= $Html->imageLink($product->bigImage(1), $Html->image($product->tinyImage(1), $product->shortTitle), array('rel' => 'lightbox')) ?></td>
<td class=title><div><?= $Html->link($product->link(), $product->title) ?></div></td>
<td class=qtity>
<form action="<?= $Settings->virtualPath() ?>panier" method="POST">
<input type=hidden name=tag value="<?= $product->tag ?>" />
<input type=hidden name=category value="<?= $product->category ?>" />
<input type=hidden name=collection value="<?= $product->collection ?>" />
<input type=hidden name=update value=true />
<input type=hidden name=oqty value=<?= $product->qtity ?> />
<input type=hidden name=ajax value=false />
<input class=qtity_val maxlength=2 name=qty type=text value="<?= $product->qtity ?>"/>
<div class=minusplus>
<span class=plus></span>
<span class=minus></span>
</div>
</form>
</td>
<td class=price><div><?= $product->totalPrice() ?> €</div></td>
<td class=delete>
<form action="<?= $Settings->virtualPath() ?>panier" method="POST">
<input type=hidden name=tag value="<?= $product->tag ?>" />
<input type=hidden name=category value="<?= $product->category ?>" />
<input type=hidden name=collection value="<?= $product->collection ?>" />
<input type=hidden name=update value=true />
<input type=hidden name=oqty value=<?= $product->qtity ?> />
<input type=hidden name=ajax value=false />
<input type=hidden name=qty value=0 />
<input class=small_button type=submit value="Supprimer" />
</form>
</td>
</tr>
<?php } ?>
<tr class=summary>
<td></td>
<td></td>
<td><span>frais de port</span></td>
<td class=price><div><?= $Model->shippingPrice ?> €</div></td>
<td></td>
</tr>
<tr class=summary>
<td></td>
<td></td>
<td><span>total</span></td>
<td class=price><div><?= $Model->totalPrice ?> €</div></td>
<td></td>
</tr>
</tbody>
</table>
<form id=order method=POST action="/acheter">
<fieldset>
<legend><span>Adresse de</span> Facturation</legend>
<div class="para clr">
<p>L’adresse de livraison et l’adresse de facturation sont-elles identiques ?</p>
<p>
<span id=show class=bs><input type=radio name=bs value=1 <?=$Order->shippingAddressIsDifferent ? 'checked=checked' : ''?> /> Oui</span>
<span id=hide class=bs><input type=radio name=bs value=0 <?=$Order->shippingAddressIsDifferent ? '' : 'checked=checked'?> /> Non</span>
</p>
</div>
<div class=clear></div>
<p>
<label for=bfirstname>prénom<span>*</span></label>
<input class=firstname name=bfirstname id=bfirstname maxlength=50 type=text value="<?=$Order->billingFirstname?>" />
<label for=bname>nom<span>*</span></label>
<input class=name name=bname id=bname maxlength=50 type=text value="<?=$Order->billingName?>" />
</p>
<p>
<label for=bstreet>rue<span>*</span></label>
<input class=street name=bstreet id=bstreet maxlength=200 type=text value="<?=$Order->billingStreet?>" />
</p>
<p>
<label for=bzipcode>code postal<span>*</span></label>
<input class=zipcode name=bzipcode id=bzipcode maxlength=5 type=text value="<?=$Order->billingZipcode?>" />
<label for=bcity>ville<span>*</span></label>
<input class=city name=bcity id=bcity maxlength=50 type=text value="<?=$Order->billingCity?>" />
</p>
<p>
<label for=bemail>email<span>*</span></label>
<input class=email name=bemail id=bemail maxlength=50 type=text value="<?=$Order->billingEmail?>" />
<label for=bphone>tél.</label>
<input class=phone name=bphone id=bphone maxlength=10 type=text value="<?=$Order->billingPhone?>" />
</p>
<p><span class="mandatory<?=$Model->isNotValid ? ' highlight_mandatory' : ''?>">* veuillez renseigner tous les champs obligatoires avec des informations valides</span></p>
</fieldset>
<fieldset id=shipping>
<legend><span>Adresse de</span> Livraison</legend>
<div class=para></div>
<div class=clear></div>
<p>
<label for=sfirstname>prénom<span>*</span></label>
<input class=firstname name=sfirstname id=sfirstname maxlength=50 type=text value="<?=$Order->shippingFirstname?>" />
<label for=sname>nom<span>*</span></label>
<input class=name name=sname id=sname type=text maxlength=50 value="<?=$Order->shippingName?>" />
</p>
<p>
<label for=sstreet>rue<span>*</span></label>
<input class=street name=sstreet id=sstreet maxlength=200 type=text value="<?=$Order->shippingStreet?>" />
</p>
<p>
<label for=szipcode>code postal<span>*</span></label>
<input class=zipcode name=szipcode id=szipcode maxlength=5 type=text value="<?=$Order->shippingZipcode?>" />
<label for=scity>ville<span>*</span></label>
<input class=city name=scity id=scity type=text maxlength=50 value="<?=$Order->shippingCity?>" />
</p>
<p>
<label for=semail>email<span></span></label>
<input class=email name=semail id=semail type=text maxlength=50 value="<?=$Order->shippingEmail?>" />
<label for=sphone>tél.</label>
<input class=phone name=sphone id=sphone type=text maxlength=10 value="<?=$Order->shippingPhone?>" />
</p>
<p><span class="mandatory<?=$Model->isNotValid ? ' highlight_mandatory' : ''?>">* veuillez renseigner tous les champs obligatoires avec des informations valides</span></p>
</fieldset>
<div id=payment>
<div id=paypal>
<p>Nous utilisons Paypal pour le paiement en ligne. Pourquoi ?</p>
<ul>
<li>c'est <strong>sécurisé</strong> : PayPal crypte et protège vos données bancaires une fois pour toutes.</li>
<li>c'est <strong>rapide</strong> : payez en utilisant simplement une adresse email et un mot de passe</li>
<li>c'est <strong>gratuit</strong> : PayPal est gratuit pour tous vos achats par internet.</li>
</ul>
</div>
<div id=pay>
<p><input type=submit value="Acheter !" class=button /></p>
<p>En cliquant sur <strong>Acheter</strong>, vous acceptez sans réserve les conditions
générales de vente de lobuki-sticker.com.
<a href="/conditions-generales-de-vente" class=underline>Cliquez ici</a> pour les visualiser.</p>
</div>
</div>
</form>
<?php } else { ?>
<p id=empty>Votre panier est vide
<?php if ('' !== $Model->backUrl) { ?>
<a class=button href="<?=$Model->backUrl?>"><span>Continuer les achats</span></a>
<?php } ?>
</p>
<?php } ?>
<div id=ajax_status>En cours...</div>
