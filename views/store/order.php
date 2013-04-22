<?php
$Order = $Model->order;
$Items = $Order->items;
$Settings = LobukiApp::CurrentSettings();
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
</head>
<body>
<p>
Envoi du panier à Paypal en cours...
</p>
<form name=frmpaypal action="<?=$Settings->paypalUrl()?>" method="post">
<input type=hidden name=cmd value="_cart" />
<input type=hidden name=upload value=1 />
<input type=hidden name=currency_code value=EUR />
<input type=hidden name=business value="<?= $Settings->paypalAccount() ?>" />
<input type=hidden name=invoice value="<?= $Order->orderNbr ?>" />

<input type=hidden name=lc value=FR />
<input type=hidden name=country value=FR />
<input type=hidden name=cbt value="Revenir au site Lobuki Stickers" />
<input type=hidden name=no_shipping value=1 />

<input type=hidden name=last_name value="<?= $Order->billingName?>" />
<input type=hidden name=first_name value="<?= $Order->billingFirstname?>" />
<input type=hidden name=address1 value="<?= substr($Order->billingStreet, 0, 100)?>" />
<input type=hidden name=address2 value="<?= substr($Order->billingStreet, 100, 100)?>" />
<input type=hidden name=zip value="<?= $Order->billingZipcode?>" />
<input type=hidden name=city value="<?= $Order->billingCity?>" />
<input type=hidden name=email value="<?= $Order->billingEmail?>" />
<input type=hidden name=night_phone_b value="<?= $Order->billingPhone?>" />

<input type=hidden name=item_name value="Votre panier lobuki-sticker.com" />

<?php
$i = 1;
foreach ($Items as $item) {
?>
<input type=hidden name=item_name_<?=$i?> value="<?= $item->name?>" />
<input type=hidden name=quantity_<?=$i?> value="<?= $item->qtity?>" />
<input type=hidden name=amount_<?=$i?> value="<?= $item->unitPrice?>" />
<?php
    if (1 == $i) {
        ?><input type=hidden name=shipping_1 value="<?= $Order->shippingPrice?>" /><?php
    } else {
        ?><input type=hidden name=shipping_<?=$i?> value=0 /><?php
    }
    $i++;
}
?>

<input type=hidden name=return value="<?= $Path->absoluteLink('confirm.php')?>" />
<input type=hidden name=cancel_return value="<?= $Path->absoluteLink('panier')?>" />

<!-- 150x50 px
<input type=hidden name=image_url value="<?=$Path->image('logo.png')?>" /> -->
<input type=hidden name=charset value="UTF-8" />
</form>
<script>
document.forms['frmpaypal'].submit();
</script>
</body>
</html>
