<h1>Retour paiement</h1>
<?php if ($Model->isSuccess) { ?>
<p>Votre transaction a bien été effectuée et un avis de réception
correspondant à votre achat vous a été envoyé par email.
</p>
<p>Si vous disposez d'un compte PayPal vous pouvez vous
connecter à votre compte à l'adresse
<a href='https://www.paypal.com'>www.paypal.com</a>
pour afficher les détails de cette transaction.</p>
<?php } else { ?>
<p>Un problème est survenue lors du paiement de votre commande.</p>
<p>Veuillez nous excuser pour la gêne occasionnée</p>
<p>L'équipe Lobuki-sticker.com</p>
<?php } ?>
