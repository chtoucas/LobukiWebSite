<?php
/// Author: Pascal Tran-Ngoc-Bich <chtoucas@narvalo.org>

namespace Lobuki\Controllers;

require_once 'Narvalo.php';
require_once 'Lobuki.php';

use Lobuki;
use Narvalo\Persistence;
use Narvalo\Web;
use Narvalo\Web\Mvc;

/*
 * Controller
 */

/* {{{ StoreController */

class StoreController extends Lobuki\LobukiBaseController {
    const
        MAX_RELATED_PRODUCTS       = 19,
        MAX_PRODUCTS_IN_CATEGORY   = 16,
        MAX_PRODUCTS_IN_COLLECTION = 40;

    /* {{{ basket() */

    /// Basket
    public function basket($_req_) {
        $useAjax = isset($_req_['ajax']) && 'true' === $_req_['ajax'];
        $httpMethod = $_SERVER['REQUEST_METHOD'];

        switch ($httpMethod) {
            case 'POST':
                return $this->doPostBasket($_req_, $useAjax);
            case 'GET':
                return $this->doGetBasket($_req_);
            default:
                throw new Web\UnsupportedHttpMethodException();
        }
    }

    /* }}} */
    /* {{{ category() */

    /// List all products in a category
    public function category($_req_) {
        $this->publiclyCache();

        // Filter request
        if (!isset($_req_['collection'])) {
            throw new Web\BadRequestException('The argument collection is mandatory');
        }
        if (!isset($_req_['category'])) {
            throw new Web\BadRequestException('The argument category is mandatory');
        }

        $page = isset($_req_['p']) ? (int)$_req_['p'] : 1;

        $collectionTag = $_req_['collection'];
        $categoryTag   = $_req_['category'];

        // Query datastore
        $category = $this->dataStore->findCategory($collectionTag,
            $categoryTag);

        if (NULL === $category) {
            throw new Web\BadRequestException('La catégorie demandée n\'existe pas');
        }

        $count = $this->dataStore->countProductsInCategory(
            $collectionTag, $categoryTag);

        $pager = Persistence\DataPager::Initialize(
            $page, $count, self::MAX_PRODUCTS_IN_CATEGORY);

        $products = $this->dataStore->findProductsInCategory(
            $collectionTag, $categoryTag, $pager->getStartIndex(),
            self::MAX_PRODUCTS_IN_CATEGORY);

        // Model
        $model = new LobukiStoreCategoryModel();
        $model->description = $category->description;
        $model->keywords    = 'sticker étoile, sticker poétique, sticker constellation, sticker repositionnable, sticker vinyle, sticker mural, sticker enfant, sticker éducatif, sticker éveil, kit sticker, sticker zodiaque, sticker bélier, sticker taureau, sticker gémeaux, sticker cancer, sticker lion, sticker vierge, sticker balance, sticker scorpion, sticker sagittaire, sticker capricorne, sticker verseau, sticker poissons, sticker cassiopée, sticker grande ourse, sticker petite ourse, sticker mythologique';
        $model->title       = $category->title . ($pager->pageIndex > 1 ? ' (page 2)' : '');
        $model->category    = $category;
        $model->pager       = $pager;
        $model->products    = $products;

        // Sitemap
        Lobuki\LobukiSitemap::AddItem('store', 'collection',
            array(
                'parent'  => 'home-index',
                'link'    => $category->collection->link(),
                'label'   => $category->collection->shortTitle
            ));
        Lobuki\LobukiSitemap::AddItem('store', 'category',
            array(
                'parent'  => 'store-collection',
                'link'    => $category->link(),
                'label'   => $category->shortTitle
            ));

        return new LobukiStoreCategoryView($model);
    }

    /* }}} */
    /* {{{ collection() */

    /// List all collection in a collection
    public function collection($_req_) {
        $this->publiclyCache();

        // Filter request
        if (!isset($_req_['collection'])) {
            throw new Web\BadRequestException('The argument collection is mandatory');
        }

        $page = isset($_req_['p']) ? (int)$_req_['p'] : 1;

        $collectionTag = $_req_['collection'];

        // Query datastore
        $collection = $this->dataStore->findCollection($collectionTag);

        if (NULL === $collection) {
            throw new Web\BadRequestException(
                'La collection demandée n\'existe pas');
        }

        $count = $this->dataStore->countProductsInCollection($collectionTag);

        $pager = Persistence\DataPager::Initialize(
            $page, $count, self::MAX_PRODUCTS_IN_COLLECTION);

        $products = $this->dataStore->findProductsInCollection(
            $collectionTag, $pager->getStartIndex(),
            self::MAX_PRODUCTS_IN_COLLECTION);

        // Model
        $model = new LobukiStoreCollectionModel();
        $model->description = $collection->description;
        $model->keywords    = 'sticker découverte, sticker poétique, sticker éducatif, sticker éveil, sticker enfant, sticker mural, sticker couleur, livre d\'éveil, signes du zodiaque, sticker constellation, sticker étoile, sticker Cassiopée, sticker grande ourse, sticker petite ourse, qu\'est-ce qu\'une étoile filante, qu\'est-ce qu\'un arc-en-ciel, comment se forme un nuage, sticker arc-en-ciel, sticker nuage, dessin poétique, dessin coloré, sticker saison, sticker lumière, sticker science naturelle, sticker cerisier, sticker animaux, migration des oiseaux, oiseaux migrateurs';
        $model->title       = $collection->title . ($pager->pageIndex > 1 ? ' (page 2)' : '');
        $model->collection  = $collection;
        $model->pager       = $pager;
        $model->products    = $products;

        // Sitemap
       Lobuki\LobukiSitemap::AddItem('store', 'collection',
            array(
                'parent' => 'home-index',
                'link'   => $collection->link($pager->pageIndex),
                'label'  => $collection->shortTitle
            ));

        return new LobukiStoreCollectionView($model);
    }

    /* }}} */
    /* {{{ gift() */

    /// Gift
    public function gift() {
        $this->publiclyCache();

        // Model
        $model = new Lobuki\LobukiMasterModel();
        $model->description = 'stickers muraux, déco enfant, stickers éducatifs, bon cadeau enfant, bon d\'achat enfant, lobuki, cadeau de naissance, sticker animaux, sticker constellation, sticker abécédaire, sticker alphabet, stickers originaux, illustration pour enfant, sticker couleur';
        $model->keywords    = 'Le bon cadeau Lobuki : le parfait cadeau de naissance';
        $model->title       = 'Bon cadeau';

        return new LobukiStoreGiftView($model);
    }

    /* }}} */
    /* {{{ news() */

    /// New Products
    public function news() {
        $this->publiclyCache();

        // Query datastore
        $products = $this->dataStore->findNewProducts();

        // Model
        $model = new LobukiStoreNewsModel();
        $model->title    = 'Nouveautés';
        $model->products = $products;

        return new LobukiStoreNewsView($model);
    }

    /* }}} */
    /* {{{ order() */

    public function order($_req_) {
        $httpMethod = $_SERVER['REQUEST_METHOD'];

        switch ($httpMethod) {
            case 'POST':
                break;
            case 'GET':
            default:
                throw new Web\UnsupportedHttpMethodException();
        }

        $factory = Web\PathFactoryBuilder::Current()->getPathFactory();

        $basket = Lobuki\LobukiSession::GetBasketId();
        if (NULL === $basket) {
            \header('Location: ' . $factory->absoluteLink('panier'));
        }

        $order = self::_ParseOrderRequest($basket, $_req_);

        if (!$order->isValid()) {
            $url = $factory->absoluteLink('panier')
                . '?' . $order->toQueryString() . '&invalid=1';
            \header('Location: ' . $url);
            exit();
        }

        $order = $this->dataStore->createOrder($order);

        if (NULL == $order) {
            throw new \Exception(
                'Une erreur est intervenue lors du traitement de votre commande');
        }

        // Model
        $model = new LobukiStoreOrderModel();
        $model->order = self::_EscapeOrder($order);

        return new LobukiStoreOrderView($model);
    }

    /* }}} */
    /* {{{ personalizeSticker() */

    public function personalizeSticker() {
        $this->publiclyCache();

        // Model
        $model = new Lobuki\LobukiMasterModel();
        $model->description = 'Personnalisation de stickers';
        $model->keywords    = 'sticker lobuki, pose de sticker mural, vinyle blanc opaque repositionnable, aspect mat, pièce à décorer, chambre d\'enfant, sticker repositionnable, sticker personnalisé, personnalisation de sticker';
        $model->title       = 'Personnalisez vos stickers';

        // Sitemap
        Lobuki\LobukiSitemap::AddItem('store', 'collection',
            array(
                'parent' => 'home-index',
                'link' => 'sticker-mural/',
                'label' => 'Sticker mural',
            ));
        Lobuki\LobukiSitemap::AddItem('store', 'personalizeSticker',
            array(
                'parent' => 'store-collection',
                'link' => 'sticker-mural/personnalisation',
                'label' => 'Personnalisation'
            ));

        return new LobukiStorePersonalizeStickerView($model);
    }

    /* }}} */
    /* {{{ product() */

    /// View a product
    public function product($_req_) {
        // Filter request
        if (!isset($_req_['collection'])) {
            throw new Web\BadRequestException('The argument collection is mandatory');
        }
        if (!isset($_req_['category'])) {
            throw new Web\BadRequestException('The argument category is mandatory');
        }
        if (!isset($_req_['product'])) {
            throw new Web\BadRequestException('The argument product is mandatory');
        }

        $collectionTag = $_req_['collection'];
        $categoryTag   = $_req_['category'];
        $productTag    = $_req_['product'];

        // Query datastore
        $product = $this->dataStore->findProduct(
            $collectionTag, $categoryTag, $productTag);

        if (NULL == $product) {
            throw new Web\BadRequestException(
                'Le produit demandé n\'est plus disponible');
        }

        $category = $this->dataStore->findCategory(
            $product->collection, $product->category);

        if (NULL == $category) {
            throw new \Exception('Category not found');
        }

        // related products
        $productsInCategory = $this->dataStore->findProductsInCategory(
            $product->collection, $product->category,
            0, self::MAX_RELATED_PRODUCTS);

        $basket = new Lobuki\Basket($this->dataStore, Lobuki\LobukiSession::GetBasketId());
        $qtity = $basket->countProductQtity(
            $product->collection, $product->category, $product->tag);

        // Model
        $model = new LobukiStoreProductModel();
        $model->description         = $product->shortTitle . ' - ' . $product->description;
        $model->title               = $product->title;
        $model->category            = $category;
        $model->product             = $product;
        $model->productsInCategory  = $productsInCategory;
        $model->qtity               = $qtity > 0 ? $qtity : 1;
        $model->update              = $qtity > 0;

        // Sitemap
        Lobuki\LobukiSitemap::AddItem('store', 'collection',
            array(
                'parent' => 'home-index',
                'link'   => $category->collection->link(),
                'label'  => $category->collection->shortTitle
            ));
        Lobuki\LobukiSitemap::AddItem('store', 'category',
            array(
                'parent' => 'store-collection',
                'link' => $category->link(),
                'label' => $category->shortTitle
            ));
        Lobuki\LobukiSitemap::AddItem('store', 'product',
            array(
                'parent' => 'store-category',
                'link' => $product->link(),
                'label' => $product->shortTitle
            ));

        return new LobukiStoreProductView($model);
    }

    /* }}} */

    /* {{{ doGetBasket() */

    protected function doGetBasket($_req_) {
        $basketId = Lobuki\LobukiSession::GetBasketId();
        $basket = new Lobuki\Basket($this->dataStore, $basketId);
        $products = $basket->findProducts();
        $prices = $basket->TotalPrice($products);

        if (empty($_req_)) {
            $order = $basket->findOrder();

            if (NULL === $order) {
                $order = new Lobuki\Order($basketId);
            } else {
                $order = self::_EscapeOrder($order);
            }
        } else {
            $order = self::_EscapeOrder(
                self::_ParseOrderRequest($basketId, $_req_));
        }

        $factory = Web\PathFactoryBuilder::Current()->getPathFactory();

        $referer = \array_key_exists('HTTP_REFERER', $_SERVER)
            ? $_SERVER['HTTP_REFERER'] : '';
        if ('' != $referer && $factory->absoluteLink('panier') != $referer) {
            $backUrl = $referer;
        } else {
            $backUrl = '';
        }

        $model = new LobukiStoreBasketModel();
        $model->title         = 'Panier';
        $model->basket        = $basket;
        $model->backUrl       = $backUrl;
        $model->order         = $order;
        $model->products      = $products;
        $model->shippingPrice = 0 + $prices->shippingPrice;
        $model->totalPrice    = $prices->totalPrice;
        $model->isNotValid    = isset($_req_['invalid'])
                                && 1 == $_req_['invalid'];

        return new LobukiStoreBasketView($model);
    }

    /* }}} */
    /* {{{ doPostBasket() */

    protected function doPostBasket($_req_, $_use_ajax_) {
        // Filter request
        if (!isset($_req_['oqty'])) {
            return $this->_onPostBasketBadRequest(
                'The argument oqty is mandatory', $_use_ajax_);
        }
        if (!isset($_req_['qty'])) {
            return $this->_onPostBasketBadRequest(
                'The argument qty is mandatory', $_use_ajax_);
        }
        if (!isset($_req_['collection'])) {
            return $this->_onPostBasketBadRequest(
                'The argument collection is mandatory', $_use_ajax_);
        }
        if (!isset($_req_['category'])) {
            return $this->_onPostBasketBadRequest(
                'The argument category is mandatory', $_use_ajax_);
        }
        if (!isset($_req_['tag'])) {
            return $this->_onPostBasketBadRequest(
                'The argument tag is mandatory', $_use_ajax_);
        }

        // Set parameters
        $oqtity        = (int)$_req_['oqty'];
        $qtity         = (int)$_req_['qty'];
        $update        = 'true' === $_req_['update'];
        $collectionTag = $_req_['collection'];
        $categoryTag   = $_req_['category'];
        $productTag    = $_req_['tag'];

        // Validate parameters
        if ($oqtity < 0 || $oqtity > 99) {
            return $this->_onPostBasketBadRequest(
                'The argument oqty is not valid', $_use_ajax_);
        }

        if (
               ($update && $qtity < 0)
            || (!$update && 0 == $qtity)
            || $qtity > 99) {
            return $this->_onPostBasketBadRequest(
                'The argument qty is not valid', $_use_ajax_);
        }

        $isSuccess = FALSE;

        // Query datastore
        $product = $this->dataStore->findProduct(
            $collectionTag, $categoryTag, $productTag);

        if (NULL === $product) {
            return $this->_onPostBasketBadRequest(
                'Le produit demandé n\'existe pas', $_use_ajax_);
        }

        // Update basket
        try {
            $basketId = Lobuki\LobukiSession::GetBasketId();
            $basket = new Lobuki\Basket($this->dataStore, $basketId);
            $nbOfProducts = $basket->addProduct(
                $product->collection, $product->category,
                $product->tag, $qtity, $product->price);

            if ($nbOfProducts >= 0) {
                $isSuccess = TRUE;
                Lobuki\LobukiSession::SetBasketCookie($nbOfProducts);
            } else if (-1 == $nbOfProducts) {
                // We lost the basket, reset it.
                $basket->reset();
            }
        } catch (\Exception $e) {
            \error_log($e->getMessage());
            $isSuccess = FALSE;
        }

        if ($_use_ajax_) {
            $model = new LobukiStoreBasketAjaxModel();
            $model->isSuccess = $isSuccess;
            $model->update    = $update;
            $model->product   = $product;
            $model->qtity     = $qtity;

            return new LobukiStoreBasketAjaxView($model);
        }
        else {
            // GPG pattern to avoid refresh problems
            if ($isSuccess) {
                $factory = Web\PathFactoryBuilder::Current()->getPathFactory();

                \header('Location: ' . $factory->absoluteLink('panier'));
                exit();
            } else {
                throw new \Exception(
                    'Une erreur est intervenue, veuillez réessayer plus tard.');
            }
        }
    }

    /* }}} */

    /* {{{ _Escape() */

    private static function _Escape($_val_) {
        return \htmlspecialchars($_val_, ENT_COMPAT, "UTF-8", FALSE);
    }

    /* }}} */
    /* {{{ _EscapeOrder() */

    private static function _EscapeOrder($_order_) {
        $order = $_order_;
        $order->billingName
            = self::_Escape($_order_->billingName);
        $order->billingFirstname
            = self::_Escape($_order_->billingFirstname);
        $order->billingStreet
            = self::_Escape($_order_->billingStreet);
        $order->billingZipcode
            = self::_Escape($_order_->billingZipcode);
        $order->billingCity
            = self::_Escape($_order_->billingCity);
        $order->billingPhone
            = self::_Escape($_order_->billingPhone);
        $order->billingEmail
            = self::_Escape($_order_->billingEmail);
        $order->shippingName
            = self::_Escape($_order_->shippingName);
        $order->shippingFirstname
            = self::_Escape($_order_->shippingFirstname);
        $order->shippingStreet
            = self::_Escape($_order_->shippingStreet);
        $order->shippingZipcode
            = self::_Escape($_order_->shippingZipcode);
        $order->shippingCity
            = self::_Escape($_order_->shippingCity);
        $order->shippingPhone
            = self::_Escape($_order_->shippingPhone);
        $order->shippingEmail
            = self::_Escape($_order_->shippingEmail);
        return $order;
    }

    /* }}} */
    /* {{{ _ParseOrderRequest() */

    private static function _ParseOrderRequest($_basket_, $_req_) {
        $order = new Lobuki\Order($_basket_);
        $order->shippingAddressIsDifferent
            = isset($_req_['bs']) ? (1 == $_req_['bs']) : FALSE;
        $order->billingName
            = isset($_req_['bname']) ? \trim($_req_['bname']) : '';
        $order->billingFirstname
            = isset($_req_['bfirstname']) ? \trim($_req_['bfirstname']) : '';
        $order->billingStreet
            = isset($_req_['bstreet']) ? \trim($_req_['bstreet']) : '';
        $order->billingZipcode
            = isset($_req_['bzipcode']) ? \trim($_req_['bzipcode']) : '';
        $order->billingCity
            = isset($_req_['bcity']) ? \trim($_req_['bcity']) : '';
        $order->billingPhone
            = isset($_req_['bphone']) ? \trim($_req_['bphone']) : '';
        $order->billingEmail
            = isset($_req_['bemail']) ? \trim($_req_['bemail']) : '';

        if ($order->shippingAddressIsDifferent) {
            $order->shippingName
                = isset($_req_['sname']) ? \trim($_req_['sname']) : '';
            $order->shippingFirstname
                = isset($_req_['sfirstname']) ? \trim($_req_['sfirstname']) : '';
            $order->shippingStreet
                = isset($_req_['sstreet']) ? \trim($_req_['sstreet']) : '';
            $order->shippingZipcode
                = isset($_req_['szipcode']) ? \trim($_req_['szipcode']) : '';
            $order->shippingCity
                = isset($_req_['scity']) ? \trim($_req_['scity']) : '';
            $order->shippingPhone
                = isset($_req_['sphone']) ? \trim($_req_['sphone']) : '';
            $order->shippingEmail
                = isset($_req_['semail']) ? \trim($_req_['semail']) : '';
        }

        return $order;
    }

    /* }}} */

    /* {{{ _onPostBasketBadRequest() */

    private function _onPostBasketBadRequest($_msg_, $_use_ajax_) {
        if ($_use_ajax_) {
            $model = new LobukiStoreBasketAjaxModel();
            $model->isSuccess = FALSE;
            return new LobukiStoreBasketAjaxView($model);
        } else {
            throw new Web\BadRequestException($_msg_);
        }
    }

    /* }}} */
}

/* }}} */

/*
 * Views
 */

/* {{{ LobukiStoreBasketAjaxView */

class LobukiStoreBasketAjaxView extends Mvc\PageBase {
    public function __construct(LobukiStoreBasketAjaxModel $_model_) {
          $this->model = $_model_;
    }

    public function getViewPath() {
        return Lobuki\LobukiApp::ViewPath('store', 'basket', 'ajax.php');
    }
}

/* }}} */
/* {{{ LobukiStoreBasketView */

class LobukiStoreBasketView extends Lobuki\LobukiChildView {
    public function __construct(LobukiStoreBasketModel $_model_ = NULL) {
        parent::__construct('store', 'basket', $_model_, FALSE /* hasHead */);
    }
}

/* }}} */
/* {{{ LobukiStoreCategoryView */

class LobukiStoreCategoryView extends Lobuki\LobukiChildView {
    public function __construct(LobukiStoreCategoryModel $_model_ = NULL) {
        parent::__construct('store', 'category', $_model_, FALSE /* hasHead */);
    }
}

/* }}} */
/* {{{ LobukiStoreCollectionView */

class LobukiStoreCollectionView extends Lobuki\LobukiChildView {
    public function __construct(LobukiStoreCollectionModel $_model_ = NULL) {
        parent::__construct('store', 'collection', $_model_, FALSE /* hasHead */);
    }
}

/* }}} */
/* {{{ LobukiStoreGiftView */

class LobukiStoreGiftView extends Lobuki\LobukiChildView {
    public function __construct(Lobuki\LobukiMasterModel $_model_ = NULL) {
        parent::__construct('store', 'gift', $_model_, TRUE /* hasHead */);
    }
}

/* }}} */
/* {{{ LobukiStoreNewsView */

class LobukiStoreNewsView extends Lobuki\LobukiChildView {
    public function __construct(LobukiStoreNewsModel $_model_ = NULL) {
        parent::__construct('store', 'news', $_model_, TRUE /* hasHead */);
    }
}

/* }}} */
/* {{{ LobukiStoreOrderView */

class LobukiStoreOrderView extends Mvc\PageBase {
    public function __construct(LobukiStoreOrderModel $_model_) {
          $this->model = $_model_;
    }

    public function getViewPath() {
        return Lobuki\LobukiApp::ViewPath('store', 'order', 'main.php');
    }
}

/* }}} */
/* {{{ LobukiStorePersonalizeStickerView */

class LobukiStorePersonalizeStickerView extends Lobuki\LobukiChildView {
    public function __construct(Lobuki\LobukiMasterModel $_model_ = NULL) {
        parent::__construct('store', 'personalizeSticker', $_model_, TRUE /* hasHead */);
    }
}

/* }}} */
/* {{{ LobukiStoreProductView */

class LobukiStoreProductView extends Lobuki\LobukiChildView {
    public function __construct(LobukiStoreProductModel $_model_ = NULL) {
        parent::__construct('store', 'product', $_model_, FALSE /* hasHead */);
    }
}

/* }}} */

/*
 * Models
 */

/* {{{ LobukiStoreBasketAjaxModel */

class LobukiStoreBasketAjaxModel {
    public
        $isSuccess;
}

/* }}} */
/* {{{ LobukiStoreBasketModel */

class LobukiStoreBasketModel extends Lobuki\LobukiMasterModel {
    public
        $backUrl,
        $basket,
        $order,
        $products,
        $shippingPrice,
        $isNotValid,
        $totalPrice;
}

/* }}} */
/* {{{ LobukiStoreCategoryModel */

class LobukiStoreCategoryModel extends Lobuki\LobukiMasterModel {
    public
        $category,
        $pager,
        $products;
}

/* }}} */
/* {{{ LobukiStoreCollectionModel */

class LobukiStoreCollectionModel extends Lobuki\LobukiMasterModel {
    public
        $collection,
        $pager,
        $products;
}

/* }}} */
/* {{{ LobukiStoreIndexModel */

class LobukiStoreIndexModel extends Lobuki\LobukiMasterModel {
    public
        $collections;
}

/* }}} */
/* {{{ LobukiStoreNewsModel */

class LobukiStoreNewsModel extends Lobuki\LobukiMasterModel {
    public
        $products;
}

/* }}} */
/* {{{ LobukiStoreOrderModel */

class LobukiStoreOrderModel {
    public
        $order;
}

/* }}} */
/* {{{ LobukiStoreProductModel */

class LobukiStoreProductModel extends Lobuki\LobukiMasterModel {
    public
        $category,
        $product,
        $productsInCategory,
        $qtity,
        $update;
}

/* }}} */

// EOF

