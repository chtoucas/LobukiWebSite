<?php $Settings = \Lobuki\LobukiApp::CurrentSettings(); ?>
<!DOCTYPE html>
<html lang=fr>
<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8"/>
<title><?= $Model->title() ?></title>
<meta name=description content="<?= $Model->description() ?>"/>
<meta name=keywords content="<?= $Model->keywords ?>"/>
<meta name=copyright content="Copyright © 2010 Lobuki Stickers" />
<meta name=robots content="index, follow" />
<base href="<?= $Settings->baseUrl() ?>/"/>
<link rel=canonical href="<?= $Model->canonicalUrl ?>"/>
<?php /* <link rel="shortcut icon" type="image/gif" href="/favicon.ico" /> */ ?>
<?php if (\Lobuki\LobukiApp::$DebugStylesheet) { ?>
<?= $Html->cssList(array(
    'yui/01-reset.css', 'yui/02-base.css', 'yui/03-fonts.css',
    '01-layout.css', '02-typo.css', '03-colors.css', '04-fx.css',
    '05-jquery.css', '06-pages.css')) ?>
<!--[if lt IE 8]><?= $Html->css('ie.debug.css') ?><![endif]-->
<?php } else { ?>
<?= $Html->css('site.css') ?>
<!--[if lt IE 8]><?= $Html->css('ie.css') ?><![endif]-->
<?php } ?>
<?php $Child->head->render(); ?>
</head>
<?php ob_flush(); ?>
<body class="<?= $Model->id ?>">
<div id=global>
<div id=preamble class=clr>
<?php $logo = new \Narvalo\Web\Image('logo.png', 'Lobuki', 156, 162); ?>
<?= $Html->link('', $Html->imageTag($logo, array('id' => 'logo'))) ?>
<ul id=menu_site>
<li><a href="//lobuki.blogspot.com/" rel=external>Blog</a><span></span></li>
<li><a href="//www.facebook.com/people/Lobuki-Sticker/100001839649404" rel=external>Facebook</a><span></span></li>
<li><?= $Html->link('actualite-lobuki', 'Actualité Lobuki', array('id' => 'home_news')) ?><span></span></li>
<li><?= $Html->link('points-de-vente', 'Points de vente', array('id' => 'home_resellers')) ?><span></span></li>
<li><?= $Html->link('paiement', 'Paiement', array('id' => 'home_payment')) ?></li>
</ul>
<?php /*
<p id=warning>
<span>La boutique sera fermée à partir du 12 août ! Les commandes passées à partir
de cette date ne seront traitées et expédiées qu'à partir du 1er septembre.</span>
</p>
 */ ?>
</div>
<div id=main class=clr>
<?php $baseline = new \Narvalo\Web\Image('baseline.png', 'La décoration ludique pour les enfants', 264, 136); ?>
<?= $Html->imageTag($baseline, array('id' => 'baseline')) ?>
<?php $Child->menu->render(); ?>
<?php $Child->breadCrumb->render(); ?>
<div id=main_content>
<?php $Child->content->render(); ?>
</div>
</div>
<div id=footer class=clr>
<ul>
<li><?= $Html->link('qui-est-lobuki', 'Qui est Lobuki') ?><span></span></li>
<li><?= $Html->link('mode-d-emploi', 'Sticker, mode d\'emploi') ?><span></span></li>
<li><?= $Html->link('plan-du-site', 'Plan du site') ?><span></span></li>
<li><?= $Html->link('contact', 'Contact') ?><span></span></li>
<li><?= $Html->link('conditions-generales-de-vente', 'CGV') ?><span></span></li>
<li><?= $Html->link('questions-frequentes', 'FAQ') ?><span></span></li>
<li><em>© 2010 Lobuki - Marque et modèles déposés</em></li>
</ul>
</div>
</div>
<?php if ($Settings->enableGoogleAnalytics()) { ?>
<script>var _gaq=_gaq||[];_gaq.push(['_setAccount','<?= $Settings->googleAnalyticsKey() ?>'],['_trackPageview'],['_trackPageLoadTime']);</script>
<?php } ?>
<script>var DEBUG=<?= \Lobuki\LobukiApp::$DebugJavascript ? '!0': '!1'?>,BASE='<?=  \Narvalo\Web\PathFactoryBuilder::Current()->getPathFactory()->scriptPrefix()?>',ROUTE=['<?= $Model->controllerName ?>','<?= $Model->actionName ?>'];</script>
<?php if (\Lobuki\LobukiApp::$DebugJavascript) { ?>
<?= $Html->jsList(array('debug/yepnope.js', 'debug/main.js')) ?>
<?php } else { ?>
<script>(function(){var a=document.createElement("script");a.type="text/javascript";a.async=!0;a.src=BASE+"main.js";var b=document.getElementsByTagName("script")[0];b.parentNode.insertBefore(a,b)})();</script>
<?php } ?>
</body>
</html>
