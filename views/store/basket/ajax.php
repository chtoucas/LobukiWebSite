<?php $Settings = \Lobuki\LobukiApp::CurrentSettings(); ?>
<div id=ajax_result>
<?php if ($Model->isSuccess) { ?>
<?php $Qtity = $Model->qtity; ?>
<p class=OK>
<?php /* <?= $Html->image('close-16x16.png', 'Fermer', NULL, 'close', 16, 16) ?> */ ?>
<?php if ($Qtity > 0) { ?>
<?php if ($Model->update) { ?>
<a href="<?= $Settings->virtualPath() ?>panier"><strong>Votre panier</strong></a> a bien été mis à jour :
<em><?= $Model->product->title ?></em> (<?= $Qtity ?> exemplaire<?= $Qtity > 1 ? 's' : ''?>)
<?php } else { ?>
<em><?= $Model->product->title ?></em> (<?= $Qtity ?> exemplaire<?= $Qtity > 1 ? 's' : ''?>) a bien été ajouté à <a href="<?= $Settings->virtualPath() ?>panier"><strong>votre panier</strong></a>
<?php } ?>
<?php } else { ?>
<em><?= $Model->product->title ?></em> a bien été enlevé de <a href="<?= $Settings->virtualPath() ?>panier"><strong>votre panier</strong></a>
<?php } ?>
<?php } else { ?>
<p class=KO>
<?php /* <?= $Html->image('close-16x16.png', 'Fermer', NULL, 'close', 16, 16) ?> */ ?>
Désolé, nous n'avons pas pu enregistrer votre demande. Veuillez réessayer plus tard.
<?php } ?>
</p>
</div>
