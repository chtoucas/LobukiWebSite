<?php
/// Author: Pascal Tran-Ngoc-Bich <chtoucas@narvalo.org>

namespace Lobuki\Controllers;

require_once 'Narvalo.php';
require_once 'Lobuki.php';

use Lobuki;
use Narvalo\Web;
use Narvalo\Web\Mvc;

/*
 * Controller
 */

/* {{{ HomeController */

class HomeController extends Lobuki\LobukiBaseController {
    /* {{{ about() */

    public function about() {
        $this->publiclyCache();

        // Model
        $model = new Lobuki\LobukiMasterModel();
        $model->description = 'Lobuki-sticker.com, des dessins plein de poésies';
        $model->keywords    = 'lobuki, laurine, manu, découverte, éducatif, pédagogique, éveil, curiosité, sticker, adhésif, mural, décoration, déco, poétique, couleur, original, dessin, illustration, enfant, kids, imaginaire, poésie';
        $model->title       = 'Qui est Lobuki ?';

        return new LobukiHomeAboutView($model);
    }

    /* }}} */
    /* {{{ cgv() */

    public function cgv() {
        $this->publiclyCache();

        // Model
        $model = new Lobuki\LobukiMasterModel();
        $model->description = 'Les conditions générales de vente Lobuki';
        $model->keywords    = 'conditions générales de vente,lobuki, stickers muraux ludiques';
        $model->title       = 'Conditions générales de vente';

        return new LobukiHomeCgvView($model);
    }

    /* }}} */
    /* {{{ contact() */

    public function contact() {
        $this->publiclyCache();

        // Model
        $model = new Lobuki\LobukiMasterModel();
        $model->description = 'Questions stickers, Contactez Lobuki';
        $model->keywords    = 'contacter lobuki, personnalisation de sticker, sticker personnalisé';
        $model->title       = 'Contact';

        return new LobukiHomeContactView($model);
    }

    /* }}} */
    /* {{{ faq() */

    public function faq() {
        $this->publiclyCache();

        // Model
        $model = new Lobuki\LobukiMasterModel();
        $model->description = 'La foire aux questions Lobuki';
        $model->keywords    = 'sticker lobuki, pose de sticker mural, vinyle blanc opaque repositionnable, aspect mat, pièce à décorer, chambre d\'enfant, sticker repositionnable, sticker personnalisé, personnalisation de sticker';
        $model->title       = 'FAQ';

        return new LobukiHomeFaqView($model);
    }

    /* }}} */
    /* {{{ help() */

    public function help() {
        $this->publiclyCache();

        // Model
        $model = new Lobuki\LobukiMasterModel();
        $model->description = 'Comment poser les stickers Lobuki ?';
        $model->keywords    = 'mode d\'emploi pose de sticker, sticker mural, illustrations Lobuki, sticker repositionnable, mode d\'emploi pose sticker vinyl, mode d\'emploi pose stickers lobuki';
        $model->title       = 'Sticker, mode d\'emploi';

        return new LobukiHomeHelpView($model);
    }

    /* }}} */
    /* {{{ index() */

    public function index() {
        //$this->publiclyCache();

        $images = array(
            'nouvelle-affiche-poissons.jpg',
            'nouvelle-affiche-oiseaux.jpg',
        );

        $random_image = new Web\Image(
            $images[\array_rand($images, 1)],
            'Découvrez dès maintenant les affiches Lobuki',
            734,
            442);

        // Model
        $model = new LobukiHomeIndexModel();
        $model->description = 'Lobuki-sticker.com, les stickers éducatifs pour enfants';
        $model->keywords    = 'sticker couleur, sticker enfant, sticker nuage, stickers muraux, histoire murale, illustration originale, illustration poétique, planche éducative, sticker saison, sticker animaux, sticker migration des oiseaux, fascicule éducatif, sticker décoration et connaissance, sticker lobuki';
        $model->title       = 'Les adhésifs ludiques pour les enfants !';
        $model->image       = $random_image;

        return new LobukiHomeIndexView($model);
    }

    /* }}} */
    /* {{{ news() */

    public function news() {
        $this->publiclyCache();

        // Model
        $model = new Lobuki\LobukiMasterModel();
        $model->title = 'Actualité Lobuki';

        return new LobukiHomeNewsView($model);
    }

    /* }}} */
    /* {{{ payment() */

    public function payment() {
        $this->publiclyCache();

        // Model
        $model = new Lobuki\LobukiMasterModel();
        $model->title = 'Paiement';

        return new LobukiHomePaymentView($model);
    }

    /* }}} */
    /* {{{ resellers() */

    public function resellers() {
        $this->publiclyCache();

        // Model
        $model = new Lobuki\LobukiMasterModel();
        $model->title = 'Points de vente';

        return new LobukiHomeResellersView($model);
    }

    /* }}} */
    /* {{{ robotmap() */

    public function robotmap() {
        //header('Content-Type: text/xml; charset=utf-8', TRUE);

        //$this->publiclyCache();

        // Query datastore
        $collections = $this->dataStore->findCollections();
        $categories  = $this->dataStore->findCategories();
        $products    = $this->dataStore->findAllProducts();

        // Model
        $model = new LobukiHomeRobotmapModel();
        $model->collections = $collections;
        $model->categories  = $categories;
        $model->products    = $products;

        return new LobukiHomeRobotmapView($model);
    }

    /* }}} */
    /* {{{ sitemap() */

    public function sitemap() {
        $this->publiclyCache();

        // Query datastore
        $collections = $this->dataStore->findCollections();
        $categories  = $this->dataStore->findCategories();

        // Model
        $model = new LobukiHomeSitemapModel();
        $model->description = 'Plan du site Lobuki-sticker.com';
        $model->keywords    = 'lobukisticker, sticker découverte, sticker constellation, sticker science, sticker animaux, sticker décoration, plan du site lobuki, FAQ lobuki, mode d\'emploi sticker lobuki';
        $model->title       = 'Plan du site';
        $model->collections = $collections;
        $model->categories  = $categories;

        return new LobukiHomeSitemapView($model);
    }

    /* }}} */
}

/* }}} */

/*
 * Views
 */

/* {{{ LobukiHomeAboutView */

class LobukiHomeAboutView extends Lobuki\LobukiChildView {
    public function __construct(Lobuki\LobukiMasterModel $_model_ = NULL) {
        parent::__construct('home', 'about', $_model_, TRUE /* hasHead */);
    }
}

/* }}} */
/* {{{ LobukiHomeAvailableSoonView */

class LobukiHomeAvailableSoonView extends Lobuki\LobukiChildView {
    public function __construct(Lobuki\LobukiMasterModel $_model_ = NULL) {
        parent::__construct('store', 'availablesoon', $_model_);
    }
}

/* }}} */
/* {{{ LobukiHomeCgvView */

class LobukiHomeCgvView extends Lobuki\LobukiChildView {
    public function __construct(Lobuki\LobukiMasterModel $_model_ = NULL) {
        parent::__construct('home', 'cgv', $_model_, TRUE /* hasHead */);
    }
}

/* }}} */
/* {{{ LobukiHomeContactView */

class LobukiHomeContactView extends Lobuki\LobukiChildView {
    public function __construct(Lobuki\LobukiMasterModel $_model_ = NULL) {
        parent::__construct('home', 'contact', $_model_, TRUE /* hasHead */);
    }
}

/* }}} */
/* {{{ LobukiHomeFaqView */

class LobukiHomeFaqView extends Lobuki\LobukiChildView {
    public function __construct(Lobuki\LobukiMasterModel $_model_ = NULL) {
        parent::__construct('home', 'faq', $_model_, TRUE /* hasHead */);
    }
}

/* }}} */
/* {{{ LobukiHomeHelpView */

class LobukiHomeHelpView extends Lobuki\LobukiChildView {
    public function __construct(Lobuki\LobukiMasterModel $_model_ = NULL) {
        parent::__construct('home', 'help', $_model_, TRUE /* hasHead */);
    }
}

/* }}} */
/* {{{ LobukiHomeIndexView */

class LobukiHomeIndexView extends Lobuki\LobukiChildView {
    public function __construct(LobukiHomeIndexModel $_model_ = NULL) {
        parent::__construct('home', 'index', $_model_);
    }
}

/* }}} */
/* {{{ LobukiHomeNewsView */

class LobukiHomeNewsView extends Lobuki\LobukiChildView {
    public function __construct(Lobuki\LobukiMasterModel $_model_ = NULL) {
        parent::__construct('home', 'news', $_model_);
    }
}

/* }}} */
/* {{{ LobukiHomePaymentView */

class LobukiHomePaymentView extends Lobuki\LobukiChildView {
    public function __construct(Lobuki\LobukiMasterModel $_model_ = NULL) {
        parent::__construct('home', 'payment', $_model_);
    }
}

/* }}} */
/* {{{ LobukiHomeResellersView */

class LobukiHomeResellersView extends Lobuki\LobukiChildView {
    public function __construct(Lobuki\LobukiMasterModel $_model_ = NULL) {
        parent::__construct('home', 'resellers', $_model_, TRUE /* hasHead */);
    }
}

/* }}} */
/* {{{ LobukiHomeRobotmapView */

class LobukiHomeRobotmapView extends Mvc\PageBase {
    public function __construct(LobukiHomeRobotmapModel $_model_) {
          $this->model = $_model_;
    }

    public function getViewPath() {
        return LobukiApp::ViewPath('home', 'robotmap.php');
    }
}

/* }}} */
/* {{{ LobukiHomeSitemapView */

class LobukiHomeSitemapView extends Lobuki\LobukiChildView {
    public function __construct(LobukiHomeSitemapModel $_model_ = NULL) {
        parent::__construct('home', 'sitemap', $_model_, TRUE /* hasHead */);
    }
}

/* }}} */

/*
 * Models
 */

/* {{{ LobukiHomeIndexModel */

class LobukiHomeIndexModel extends Lobuki\LobukiMasterModel {
    public
        $image;
}

/* }}} */
/* {{{ LobukiHomeRobotmapModel */

class LobukiHomeRobotmapModel {
    public
        $categories,
        $collections,
        $products;
}

/* }}} */
/* {{{ LobukiHomeSitemapModel */

class LobukiHomeSitemapModel extends Lobuki\LobukiMasterModel {
    public
        $categories,
        $collections;
}

/* }}} */

// EOF

