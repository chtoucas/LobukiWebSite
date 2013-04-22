<?= '<?xml version="1.0" encoding="UTF-8"?>' . "\n" ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<url>
<loc><?= $Path->absoluteLink('') ?></loc>
<changefreq>weekly</changefreq>
<priority>1</priority>
</url>
<url>
<loc><?= $Path->absoluteLink('qui-est-lobuki') ?></loc>
<changefreq>weekly</changefreq>
<priority>0.30</priority>
</url>
<url>
<loc><?= $Path->absoluteLink('contact') ?></loc>
<changefreq>weekly</changefreq>
<priority>0.30</priority>
</url>
<url>
<loc><?= $Path->absoluteLink('actualite-lobuki') ?></loc>
<changefreq>weekly</changefreq>
<priority>0.90</priority>
</url>
<url>
<loc><?= $Path->absoluteLink('nouveautes') ?></loc>
<changefreq>weekly</changefreq>
<priority>0.90</priority>
</url>
<url>
<loc><?= $Path->absoluteLink('bon-cadeau') ?></loc>
<changefreq>monthly</changefreq>
<priority>0.90</priority>
</url>
<url>
<loc><?= $Path->absoluteLink('points-de-vente') ?></loc>
<changefreq>monthly</changefreq>
<priority>0.50</priority>
</url>
<url>
<loc><?= $Path->absoluteLink('paiement') ?></loc>
<priority>0.30</priority>
</url>
<url>
<loc><?= $Path->absoluteLink('panier') ?></loc>
<priority>0.30</priority>
</url>
<url>
<loc><?= $Path->absoluteLink('conditions-generales-de-vente') ?></loc>
<changefreq>monthly</changefreq>
<priority>0.30</priority>
</url>
<url>
<loc><?= $Path->absoluteLink('mode-d-emploi') ?></loc>
<changefreq>monthly</changefreq>
<priority>0.50</priority>
</url>
<url>
<loc><?= $Path->absoluteLink('questions-frequentes') ?></loc>
<changefreq>monthly</changefreq>
<priority>0.50</priority>
</url>
<?php
$categories  = $Model->categories;
$collections = $Model->collections;
$products    = $Model->products;

for ($i = 0, $count = count($collections); $i < $count; $i++) {
    $collection = $collections[$i];
    if (array_key_exists($collection->tag, $categories)) {
        $sub = $categories[$collection->tag];

?><url><loc><?= $Path->absoluteLink($collection->link()) ?></loc><priority>0.90</priority></url><?php
        for ($j = 0, $subcount = count($sub); $j < $subcount; $j++) {
            $category = $sub[$j];
?><url><loc><?= $Path->absoluteLink($category->link()) ?></loc><priority>0.90</priority></url><?php
        }
    }
}

for ($i = 0, $count = count($products); $i < $count; $i++) {
    $product = $products[$i];
?><url><loc><?= $Path->absoluteLink($product->link()) ?></loc><priority>0.80</priority></url><?php
}
?>
</urlset>
