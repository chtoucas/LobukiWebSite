<?php
/// Author: Pascal Tran-Ngoc-Bich <chtoucas@narvalo.org>

namespace Lobuki;

require_once 'Narvalo.php';

use Narvalo;
use Narvalo\Persistence;
use Narvalo\Web;
use Narvalo\Web\Mvc;

const StyleVersion  = '1.1.4';
const ScriptVersion = '1.4';

// =============================================================================

/* {{{ ServerContext */

class ServerContext {
    const
        // http://lobuki.narvalo.lan
        Development   = 1,
        // http://t.lobuki-sticker.com
        Test          = 2,
        // http://t.lobuki-sticker.com
        PreProduction = 3,
        // http://www.lobuki-sticker.com or http://lobuki-sticker.com
        Production    = 4;
}

/* }}} */
/* {{{ LobukiSettingsFactory */

class LobukiSettingsFactory {
    const OvhBaseDir = '/homez.311/lobukist/';

    public static function CreateSettings($_context_) {
        switch ($_context_) {
            case ServerContext::Development:
                return self::CreateDevelopmentSettings();
            case ServerContext::Test:
                return self::CreateTestSettings();
            case ServerContext::Production:
                return self::CreateProductionSettings();
            case ServerContext::PreProduction:
                return self::CreatePreProductionSettings();
            default:
                throw new \Exception('Invalid context');
        }
    }

    protected static function CreateDevelopmentSettings() {
        return new Web\Settings(array(
            'AltStaticsPrefix'      => '/devil/lobuki/assets/',
            'BaseUrl'               => 'http://narvalo.lan/devil/lobuki',
            'DatabaseDriver'        => LobukiDataStoreFactory::PDO_ENGINE,
            'DatabaseHost'          => 'localhost',
            'DatabaseName'          => 'lobukisticker',
            'DatabasePassword'      => 'lobu3510',
            'DatabaseUserName'      => 'lobukisticker',
            'EnableClientCache'     => FALSE,
            'EnableServerCache'     => FALSE,
            'ErrorLog'              => '',
            'GoogleAnalyticsKey'    => '',
            'PaypalAccount'         => 'policr_1305391710_biz@gmail.com',
            'PaypalAuthToken'       => 'AA8lumdmVajynwo3uZ3qBkwLusHOA3oP8je3jcbqnT-fLp9CVzlqVXQk',
            'PaypalUrl'             => 'https://www.sandbox.paypal.com/cgi-bin/webscr',
            'PaypalNotifyUrl'       => 'ssl://www.sandbox.paypal.com',
            'ScriptVersion'         => NULL,
            'StaticsPrefix'         => '/devil/lobuki/assets/',
            'StyleVersion'          => NULL,
            'TempDir'               => '/var/tmp/phpcache/lobukidev/',
            'ViewRepository'        => '/opt/www/src/sites/lobuki-sticker.com/views',
            'VirtualPath'           => '/devil/lobuki/',
        ));
    }

    protected static function CreateTestSettings() {
        return new Web\Settings(array(
            'AltStaticsPrefix'      => '//c1.narvalo.lan/lobuki/',
            'BaseUrl'               => 'http://lobuki.narvalo.lan',
            'DatabaseDriver'        => LobukiDataStoreFactory::PDO_ENGINE,
            'DatabaseHost'          => 'localhost',
            'DatabaseName'          => 'lobukisticker',
            'DatabasePassword'      => 'lobu3510',
            'DatabaseUserName'      => 'lobukisticker',
            'EnableClientCache'     => TRUE,
            'EnableServerCache'     => TRUE,
            'ErrorLog'              => '',
            'GoogleAnalyticsKey'    => '',
            'PaypalAccount'         => 'policr_1305391710_biz@gmail.com',
            'PaypalAuthToken'       => 'AA8lumdmVajynwo3uZ3qBkwLusHOA3oP8je3jcbqnT-fLp9CVzlqVXQk',
            'PaypalUrl'             => 'https://www.sandbox.paypal.com/cgi-bin/webscr',
            'PaypalNotifyUrl'       => 'ssl://www.sandbox.paypal.com',
            'ScriptVersion'         => ScriptVersion,
            'StaticsPrefix'         => '//c0.narvalo.lan/lobuki/',
            'StyleVersion'          => StyleVersion,
            'TempDir'               => '/var/tmp/phpcache/lobuki/',
            'ViewRepository'        => '/opt/share/sites/lobuki/views',
            'VirtualPath'           => '/',
        ));
    }

    protected static function CreatePreProductionSettings() {
        return new Web\Settings(array(
            'AltStaticsPrefix'      => '//s1.lobuki-sticker.com//',
            'BaseUrl'               => 'http://t.lobuki-sticker.com',
            'DatabaseDriver'        => LobukiDataStoreFactory::PDO_ENGINE,
            'DatabaseHost'          => 'mysql5-10.perso',
            'DatabaseName'          => 'lobukisticker',
            'DatabasePassword'      => 'lobu3510',
            'DatabaseUserName'      => 'lobukisticker',
            'EnableClientCache'     => TRUE,
            'EnableServerCache'     => TRUE,
            'ErrorLog'              => self::OvhBaseDir . 'tmp/phperror-test.log',
            'GoogleAnalyticsKey'    => '',
            'PaypalAccount'         => 'policr_1305391710_biz@gmail.com',
            'PaypalAuthToken'       => 'AA8lumdmVajynwo3uZ3qBkwLusHOA3oP8je3jcbqnT-fLp9CVzlqVXQk',
            'PaypalNotifyUrl'       => 'ssl://www.sandbox.paypal.com',
            'PaypalUrl'             => 'https://www.sandbox.paypal.com/cgi-bin/webscr',
            'ScriptVersion'         => ScriptVersion,
            'StaticsPrefix'         => '//s.lobuki-sticker.com/',
            'StyleVersion'          => StyleVersion,
            'TempDir'               => self::OvhBaseDir . 'tmp/lobukitest/',
            'ViewRepository'        => self::OvhBaseDir . 'share/lobukitest/views',
            'VirtualPath'           => '/',
        ));
    }

    protected static function CreateProductionSettings() {
        return new Web\Settings(array(
            'AltStaticsPrefix'      => '//s1.lobuki-sticker.com//',
            'BaseUrl'               => 'http://lobuki-sticker.com',
            'DatabaseDriver'        => LobukiDataStoreFactory::PDO_ENGINE,
            'DatabaseHost'          => 'mysql5-10.perso',
            'DatabaseName'          => 'lobukisticker',
            'DatabasePassword'      => 'lobu3510',
            'DatabaseUserName'      => 'lobukisticker',
            'EnableClientCache'     => TRUE,
            'EnableServerCache'     => TRUE,
            'ErrorLog'              => self::OvhBaseDir . 'tmp/phperror.log',
            'GoogleAnalyticsKey'    => 'UA-24774948-1',
            //'GoogleAnalyticsKey'    => 'UA-2305828-2',
            'PaypalAccount'         => 'contact@lobuki-sticker.com',
            'PaypalAuthToken'       => '55vlEVBLseoxOjv3Bw67QKeDzoh1WXiFfXOYZk6Tqgd5fb7M6OV4vo3Fug8',
            'PaypalUrl'             => 'https://www.paypal.com/cgi-bin/webscr',
            'PaypalNotifyUrl'       => 'ssl://www.paypal.com',
            'ScriptVersion'         => ScriptVersion,
            'StaticsPrefix'         => '//s.lobuki-sticker.com/',
            'StyleVersion'          => StyleVersion,
            'TempDir'               => self::OvhBaseDir . 'tmp/lobuki/',
            'ViewRepository'        => self::OvhBaseDir . 'share/lobuki/views',
            'VirtualPath'           => '/',
        ));
    }
}

/* }}} */

/* {{{ LobukiControllerFactory */

class LobukiControllerFactory implements Mvc\IControllerFactory {
    public function __construct() {
        ;
    }

    public function createController($_controllerName_) {
        $dao = LobukiDataStoreFactory::Instance(self::CurrentSettings());

        switch ($_controllerName_) {
            case 'home':   $typeName = 'HomeController';   break;
            case 'store':  $typeName = 'StoreController';  break;
            case 'paypal': $typeName = 'PaypalController'; break;
            default:
                throw new Mvc\ControllerException();
        }

        $type = new Narvalo\Type($typeName, 'Lobuki\Controllers');

        Narvalo\Loader::LoadType($type);

        $className = $type->getFullyQualifiedName();

        return new $className($dao);
    }
}

/* }}} */
/* {{{ LobukiApp */

class LobukiApp extends Mvc\ApplicationBase {
    public function __construct($_context_) {
        $settings = LobukiSettingsFactory::CreateSettings($_context_);

        parent::__construct($settings);
    }

    public function createController($_controllerName_) {
        $dao = LobukiDataStoreFactory::Instance($this->settings);

        switch ($_controllerName_) {
            case 'home':
                $typeName = 'HomeController';   break;
            case 'store':
                $typeName = 'StoreController';  break;
            case 'paypal':
                $typeName = 'PaypalController'; break;
            default:
                throw new Mvc\ControllerException();
        }

        $type = new Narvalo\Type($typeName, 'Lobuki\Controllers');

        Narvalo\Loader::LoadType($type);

        $className = $type->getFullyQualifiedName();

        return new $className($dao);
    }

    protected function createErrorView($_status_, $_message_) {
        return new LobukiErrorView(new LobukiErrorModel($_status_, $_message_));
    }

    public function start($_debugLevel_) {
        parent::start($_debugLevel_);

        Web\PathFactoryBuilder::Current()
            ->setPathFactory(new Web\SettingsPathFactory($this->settings));

        return $this;
    }
}

/* }}} */
/* {{{ LobukiSession */

// TODO:
// There is a bug here, if the session expires but not the cookie
// the basket is empty but not the cookie value.
// Also, if the cookie expires but not the session, the basket may
// not be empty but the cookie value is.
class LobukiSession {
    const BasketKey = 'basket';

    public static function GetBasketId() {
        self::_Start();
        if (\array_key_exists(self::BasketKey, $_SESSION)) {
            return $_SESSION[self::BasketKey];
        } else {
            self::SetBasketCookie(0);
            return NULL;
        }
    }

    public static function SetBasketId($_id_) {
        self::_Start();
        $_SESSION[self::BasketKey] = $_id_;
    }

    public static function SetBasketCookie($_value_) {
        if ($_value_ > 0) {
            \setcookie(self::BasketKey, $_value_);
        } else {
            // Destroy basket: unset the cookie.
            \setcookie(self::BasketKey, '', \time() - 3600);
        }
    }

    private static function _Start() {
        if ('' === \session_id()) {
            \session_start();
        }
    }
}

/* }}} */

// {{{ LobukiMasterPage

class LobukiMasterPage extends Mvc\MasterPageBase {
    public function __construct(LobukiMasterModel $_model_, LobukiChildView $_child_) {
        parent::__construct($_model_, $_child_);
    }

    public function getViewPath() {
        return LobukiApp::ViewPath('shared', 'site.master.php');
    }
}

// }}}
/* {{{ LobukiChildView */

class LobukiChildView extends Mvc\ChildViewBase {
    public
        $head,
        $content,
        $menu,
        $breadCrumb;
    protected
        $viewPath;

    public function __construct($_controllerName_, $_actionName_,
        LobukiMasterModel $_model_ = NULL,
        $_hasHead_ = FALSE) {

        if (NULL === $_model_) {
            $_model_ = new LobukiMasterModel();
        }

        $_model_->actionName = $_actionName_;
        $_model_->controllerName = $_controllerName_;
        $_model_->id = $_controllerName_ . '_' . $_actionName_;

        $this->head = $_hasHead_
            ? new HeadControl($_controllerName_, $_actionName_)
            : new Mvc\NullView();
        $this->menu = new MenuControl();
        $this->breadCrumb = new BreadCrumbControl($_controllerName_, $_actionName_);
        $this->content = new ContentControl($this);

        $path = LobukiSitemap::Link($_controllerName_, $_actionName_);

        $factory = Web\PathFactoryBuilder::Current()->getPathFactory();

        //$_model_->canonicalUrl = $factory->absoluteLink($path)
        $_model_->canonicalUrl = '/' . $path
            . ('' !== $_model_->page && 1 !== $_model_->page
                 ? '/' . $_model_->page : '');

        $master = new LobukiMasterPage($_model_, $this);

        $this->viewPath
            = LobukiApp::ViewPath($_controllerName_, $_actionName_ . '.php');

        parent::__construct($master, $_model_);
    }

    public function getViewPath() {
        return $this->viewPath;
    }
}

/* }}} */

/* {{{ LobukiMasterModel */

class LobukiMasterModel {
    const
        TitleMaxLength = 65,
        DescriptionMaxLength  = 150;

    public
        // URL canonique
        $canonicalUrl =  '',
        // Meta-description
        $description = 'Lobuki-sticker.com, les stickers éducatifs pour enfants',
        // Identifiant unique
        $actionName = '',
        $controllerName = '',
        $id,
        // Mot-clés
        $keywords = 'sticker couleur, sticker enfant, sticker nuage, stickers muraux, histoire murale, illustration originale, illustration poétique, planche éducative, sticker saison, sticker animaux, sticker migration des oiseaux, fascicule éducatif, sticker décoration et connaissance, sticker lobuki',
        // Numéro de la page quand il y a pagination
        $page = 1,
        // Titre
        $title = '';

    public function description() {
        return self::_Truncate($this->description, self::DescriptionMaxLength);
    }

    public function title() {
        return self::_Truncate('Lobuki Stickers :: ' . $this->title,
            self::TitleMaxLength, ' ', '');
    }

    private static function _Truncate($_text_, $_limit_, $_break_ = ' ', $_pad_ = '...') {
        if (\strlen($_text_) <= $_limit_) {
            return $_text_;
        }

        $text = \substr($_text_, 0, $_limit_);
        if (FALSE !== ($breakpoint = \strrpos($text, $_break_))) {
            $text = \substr($text, 0, $breakpoint);
        }

        return $text . $_pad_;
    }
}

/* }}} */

/* {{{ LobukiErrorView */

class LobukiErrorView extends LobukiChildView {
    protected $message,
        $status;

    public function __construct(LobukiErrorModel $_model_) {
        $this->message = $_model_->message();
        $this->status = $_model_->status();

        parent::__construct('error', 'index', $_model_);
    }

    public function render() {
        \error_log($this->message);
        Web\HttpError::Header($this->status);
        parent::render();
    }
}

/* }}} */
/* {{{ LobukiErrorModel */

class LobukiErrorModel extends LobukiMasterModel {
    public
        $message,
        $title;
    protected
        $status;

    public function __construct($_status_, $_message_) {
        $this->message = $_message_;
        $this->status = $_status_;
        $this->title = Web\HttpError::DefaultMessage($_status_);
    }

    public function message() {
        return $this->message;
    }

    public function status() {
        return $this->status;
    }
}

/* }}} */

// {{{ LobukiBaseController

class LobukiBaseController extends Mvc\BaseController {
    protected
        $currentSettings,
        $dataStore;

    public function __construct(ILobukiDataStore $_dataStore_) {
        $this->currentSettings = LobukiApp::CurrentSettings();
        $this->dataStore = $_dataStore_;
        $this->enableClientCache
            = $this->currentSettings->enableClientCache();
    }
}

// }}}

// {{{ BreadCrumbControl

class BreadCrumbControl implements Mvc\IView {
    protected $actionName, $controllerName;

    public function __construct($_controllerName_, $_actionName_) {
        $this->actionName = $_actionName_;
        $this->controllerName = $_controllerName_;
    }

    public function render() {
        LobukiSitemap::BreadCrumb($this->controllerName, $this->actionName);
    }
}

// }}}
// {{{ HeadControl

class HeadControl extends Mvc\StaticViewBase {
    protected $viewPath;

    public function __construct($_controllerName_, $_actionName_) {
        $this->viewPath
            = LobukiApp::ViewPath($_controllerName_, $_actionName_, 'head.php');
    }

    public function getViewPath() {
        return $this->viewPath;
    }
}

// }}}
// {{{ MenuControl

class MenuControl extends Mvc\ViewBase {
    public function __construct() {
        ;
    }

    public function getViewPath() {
        return LobukiApp::ViewPath('shared', 'menu.php');
    }
}

// }}}
// {{{ ContentControl

class ContentControl implements Mvc\IView {
    protected $child;

    public function __construct(LobukiChildView $_child_) {
        $this->child = $_child_;
    }

    public function render() {
        $this->child->renderChild();
    }
}

// }}}

// {{{ LobukiSitemap

class LobukiSitemap {
    public static function AddItem($_controllerName_, $_actionName_, array $_item_) {
        $key = $_controllerName_ . '-' . $_actionName_;

        self::$Map[$key] = $_item_;
    }

    public static function BreadCrumb($_controllerName_, $_actionName_) {
        $key = $_controllerName_ . '-' . $_actionName_;

        if (!\array_key_exists($key, self::$Map)) {
            return;
        }

        $item = self::$Map[$key];
        $parent = $item['parent'];

        if ('' === $parent) {
            return;
        }

        $map = array( array($item['link'], $item['label']) );

        while ('' !== $parent) {
            if (!\array_key_exists($parent, self::$Map)) {
                return;
            }

            $item = self::$Map[$parent];
            $map[] = array($item['link'], $item['label']);
            $parent = $item['parent'];
        }

        $result = '<p id=breadcrumb>Vous êtes ici : ';

        $helper = new Web\HtmlHelper();

        $count = \count($map) - 1;
        for ($i = $count; $i > 0; $i--) {
            $item = $map[$i];
            $result .= $helper->link($item[0], $item[1],
                array('id' => 'nav-' . $i)) . '<span>&gt;</span>';
        }

        $item = $map[0];

        $result .= '<strong>' . $item[1] . '</strong>' . '</p>';

        print $result;
    }

    public static function Link($_controllerName_, $_actionName_) {
        return self::$Map[$_controllerName_ . '-' . $_actionName_]['link'];
    }

    protected static $Map = array(
        'error-index'
            => array(
                'parent' => '',
                'link'  => '',
                'label' => 'Problème technique'),
        'home-about'
            => array(
                'parent' => 'home-index',
                'link'  => 'qui-est-lobuki',
                'label' => 'Qui est Lobuki ?'),
        'home-availablesoon'
            => array(
                'parent' => 'home-index',
                'link'  => '',
                'label' => 'Bientôt disponibles'),
        'home-cgv'
            => array(
                'parent' => 'home-index',
                'link'  => 'conditions-generales-de-vente',
                'label' => 'CGV'),
        'home-contact'
            => array(
                'parent' => 'home-index',
                'link'  => 'contact',
                'label' => 'Contact'),
        'home-faq'
            => array(
                'parent' => 'home-index',
                'link'  => 'questions-frequentes',
                'label' => 'FAQ'),
        'home-help'
            => array(
                'parent' => 'home-index',
                'link'  => 'mode-d-emploi',
                'label' => 'Mode d\'emploi'),
        'home-index'
            => array(
                'parent' => '',
                'link'  => '',
                'label' => 'Accueil'),
        'home-news'
            => array(
                'parent' => 'home-index',
                'link'  => 'actualite-lobuki',
                'label' => 'Actualité'),
        'home-payment'
            => array(
                'parent' => 'home-index',
                'link'  => 'paiement',
                'label' => 'Paiement'),
        'home-resellers'
            => array(
                'parent' => 'home-index',
                'link'  => 'points-de-vente',
                'label' => 'Points de vente'),
        'home-sitemap'
            => array(
                'parent' => 'home-index',
                'link'  => 'plan-du-site',
                'label' => 'Plan du site'),
        'store-basket'
            => array(
                'parent' => 'home-index',
                'link'  => 'panier',
                'label' => 'Panier'),
        'store-gift'
            => array(
                'parent' => 'home-index',
                'link'  => 'bon-cadeau',
                'label' => 'Bon cadeau'),
        'store-index'
            => array(
                'parent' => 'home-index',
                'link'  => 'collections',
                'label' => 'Collections'),
        'store-news'
            => array(
                'parent' => 'home-index',
                'link'  => 'nouveautes',
                'label' => 'Nouveautés'),
        'paypal-synch'
            => array(
                'parent' => 'home-index',
                'link'  => 'paypal/synch',
                'label' => 'Retour paiement'),
    );
}

// }}}

// =============================================================================

//namespace Lobuki\Model;

/* {{{ CategoryBase */

abstract class CategoryBase {
    public
        $description,
        $enabled,
        $parent,
        $position,
        $shortTitle,
        $tag,
        $title;

    public abstract function link($_page_ = 1);
}

/* }}} */
/* {{{ Category */

class Category extends CategoryBase {
    public $parent;

    public function link($_page_ = 1) {
        if (1 == $_page_) {
            return $this->parent . '/' . $this->tag . '/';
        } else {
            return $this->parent . '/' . $this->tag . '/?p=' . $_page_;
        }
    }
}

/* }}} */
/* {{{ Collection */

class Collection extends CategoryBase {
    public function link($_page_ = 1) {
        if (1 == $_page_) {
            return $this->tag . '/';
        } else {
            return $this->tag . '/?p=' . $_page_;
        }
    }
}

/* }}} */
/* {{{ Product */

class Product {
    public
        $category,
        $collection,
        $description,
        $enabled,
        $height,
        $isNew,
        $nbOfImages,
        $position,
        $price,
        $shape,
        $sharedImage,
        $sharedImageTag,
        $shippingPrice,
        $shortTitle,
        $subtitle,
        $tag,
        $title,
        $width;

    public function hasSharedImage() {
        return '' !== $this->sharedImageTag;
    }

    public function link() {
        return $this->collection. '/'
            . $this->category . '/'
            . $this->tag;
    }

    public function bigImage($_i_) {
        return $this->link() . '/big' . $_i_ . '.jpg';
    }

    public function mediumImage($_i_) {
        return $this->link() . '/medium' . $_i_ . '.jpg';
    }

    public function previewImage() {
        return $this->link() . '/preview.jpg';
    }

    public function smallImage($_i_) {
        return $this->link() . '/small' . $_i_ . '.jpg';
    }

    public function tinyImage() {
        return $this->link() . '/tiny.png';
    }
}

/* }}} */
/* {{{ SharedImage */

class SharedImage {
    protected
        $collection,
        $link,
        $tag;

    public function __construct($_collection_, $_tag_) {
        $this->collection = $_collection_;
        $this->tag = $_tag_;
        $this->link = $this->collection. '/shared/' . $this->tag;
    }

    public function bigImage() {
        return $this->link . '-big.jpg';
    }

    public function mediumImage() {
        return $this->link . '-medium.jpg';
    }

    public function smallImage() {
        return $this->link . '-small.jpg';
    }
}

/* }}} */

/* {{{ Basket */

class Basket {
    protected
        $dataStore,
        $id,
        $lines;

    public function __construct(ILobukiDataStore $_dataStore_, $_id_) {
        $this->dataStore = $_dataStore_;
        $this->id = $_id_;
    }

    public function countProducts() {
        return \count($this->lines());
    }

    public function countProductQtity($_collection_, $_category_, $_product_) {
        $lines = $this->lines();
        $key = self::_Key($_collection_, $_category_, $_product_);

        return \array_key_exists($key, $lines) ? $lines[$key]->qtity : 0;
    }

    public function lines() {
        if (NULL === $this->lines) {
            if (NULL === $this->id) {
                $this->lines = array();
            }
            else {
                $this->lines = $this->dataStore->findBasketLines($this->id);
            }
        }
        return $this->lines;
    }

    public static function TotalPrice(array $_products_) {
        $shippingPrice = 0;
        $productsPrice = 0;
        for ($i = 0, $count = \count($_products_); $i < $count; $i++) {
            $shippingPrice = \max($shippingPrice, $_products_[$i]->shippingPrice);
            $productsPrice += $_products_[$i]->totalPrice();
        }

        $result = new \StdClass();
        $result->shippingPrice = $shippingPrice;
        $result->productsPrice = $productsPrice;
        $result->totalPrice    = $shippingPrice + $productsPrice;
        return $result;
    }

    public function addProduct($_collection_, $_category_, $_product_, $_qtity_, $_price_) {
        $nbOfProducts = $this->countProducts();

        $count = $this->countProductQtity($_collection_, $_category_, $_product_);

        if ($count > 0) {
            // Product already in basket
            if (0 == $_qtity_) {
                // Remove product
                $nbOfProducts = $this->_removeFromBasket(
                    $_collection_, $_category_, $_product_);
            } else if ($count != $_qtity_) {
                // Update product's qtity
                $nbOfProducts = $this->_updateBasket(
                    $_collection_, $_category_, $_product_, $_qtity_);
            }
        } else if (0 == $count && $_qtity_ > 0) {
            // Add product to basket
            if (NULL === $this->id) {
                // First create basket
                $this->_createBasket();
            }
            $nbOfProducts = $this->_addToBasket(
                $_collection_, $_category_, $_product_, $_qtity_, $_price_);
        }

        return $nbOfProducts;
    }

    public function findOrder() {
        return $this->dataStore->findBasketOrder($this->id);
    }

    public function findProducts() {
        if (NULL === $this->id) {
            return array();
        }
        else {
            return $this->dataStore->findBasketProducts($this->id);
        }
    }

    public function reset() {
        $this->id = $this->dataStore->createBasket();
        LobukiSession::SetBasketId($this->id);
    }

    public static function Key(BasketLine $_line_) {
        return self::_Key($_line_->collection, $_line_->category,
            $_line_->product);
    }

    private static function _Key($_collection_, $_category_, $_tag_) {
        return $_collection_ . '#' . $_category_ . '#' . $_tag_;
    }

    private function _addToBasket($_collection_, $_category_, $_product_,
        $_qtity_, $_price_) {
        $count = $this->dataStore->addToBasket($this->id, $_collection_,
            $_category_, $_product_, $_qtity_, $_price_);
        $key = self::_Key($_collection_, $_category_, $_product_);
        $this->lines[$key] = $_qtity_;
        return $count;
    }

    private function _updateBasket($_collection_, $_category_, $_product_,
        $_qtity_) {
        $count = $this->dataStore->updateBasket($this->id, $_collection_,
            $_category_, $_product_, $_qtity_);
        $key = self::_Key($_collection_, $_category_, $_product_);
        $this->lines[$key] = $_qtity_;
        return $count;
    }

    private function _removeFromBasket($_collection_, $_category_, $_product_) {
        $count = $this->dataStore->removeFromBasket($this->id, $_collection_,
            $_category_, $_product_);
        $key = self::_Key($_collection_, $_category_, $_product_);
        unset($this->lines[$key]);
        return $count;
    }

    private function _createBasket() {
        if (NULL === $this->id) {
            $this->id = $this->dataStore->createBasket();
            LobukiSession::SetBasketId($this->id);
        }
    }
}

/* }}} */
/* {{{ BasketLine */

class BasketLine {
    public
        $collection,
        $category,
        $product,
        $qtity,
        $unitPrice;
}

/* }}} */
/* {{{ BasketProduct */

class BasketProduct extends Product {
    public
        $qtity,
        $unitPrice;

    public function totalPrice() {
        return $this->qtity * $this->unitPrice;
    }
}

/* }}} */
/* {{{ Order */

class Order {
    public
        $basket,
        $orderNbr,
        $shippingAddressIsDifferent,
        $billingName,
        $billingFirstname,
        $billingStreet,
        $billingZipcode,
        $billingCity,
        $billingPhone,
        $billingEmail,
        $shippingName = '',
        $shippingFirstname = '',
        $shippingStreet = '',
        $shippingZipcode = '',
        $shippingCity = '',
        $shippingPhone = '',
        $shippingEmail = '',
        $productsPrice,
        $shippingPrice,
        $totalPrice,
        $creationTime,
        $status,
        $emailSent,
        $items;

    public function __construct($_basket_) {
        $this->basket = $_basket_;
    }

    public function newOrderNbr() {
        $uniq = $this->basket . \microtime(TRUE) * 10000;
        return Narvalo\base58_encode($uniq);
        //return Narvalo\FlickrBase58::Encode($uniq);
    }

    public function isValid() {
        return self::_IsValidString($this->billingName)
            && self::_IsValidString($this->billingFirstname)
            && self::_IsValidString($this->billingStreet, 200)
            && self::_IsValidString($this->billingZipcode, 5)
            && self::_IsValidString($this->billingCity)
            && self::_IsValidEmail($this->billingEmail)
            && self::_IsValidPhone($this->billingPhone)
            && (!$this->shippingAddressIsDifferent
                || (
                    self::_IsValidString($this->shippingName)
                    && self::_IsValidString($this->shippingFirstname)
                    && self::_IsValidString($this->shippingStreet, 200)
                    && self::_IsValidString($this->shippingZipcode, 5)
                    && self::_IsValidString($this->shippingCity)
                    && self::_IsValidEmail($this->shippingEmail, TRUE)
                    && self::_IsValidPhone($this->shippingPhone)
            /*
             */
                )
            );
    }

    public function toQueryString() {
        return \implode('&',
            array(
                'bs=' . ($this->shippingAddressIsDifferent ? '1' : '0'),
                'bname=' . \urlencode($this->billingName),
                'bfirstname=' . \urlencode($this->billingFirstname),
                'bstreet=' . \urlencode($this->billingStreet),
                'bzipcode=' . \urlencode($this->billingZipcode),
                'bcity=' . \urlencode($this->billingCity),
                'bemail=' . \urlencode($this->billingEmail),
                'bphone=' . \urlencode($this->billingPhone),
                'sname=' . \urlencode($this->shippingName),
                'sfirstname=' . \urlencode($this->shippingFirstname),
                'sstreet=' . \urlencode($this->shippingStreet),
                'szipcode=' . \urlencode($this->shippingZipcode),
                'scity=' . \urlencode($this->shippingCity),
                'semail=' . \urlencode($this->shippingEmail),
                'sphone=' . \urlencode($this->shippingPhone),
            )
        );
    }

    private static function _IsValidString($_val_, $_length_ = 50) {
        return NULL !== $_val_
            && '' !== $_val_
            && \strlen($_val_) <= $_length_;
    }

    private static function _IsValidEmail($_val_, $_nullable_ = FALSE) {
        return $_nullable_
            || (NULL !== $_val_
                && '' !== $_val_
                && \strlen($_val_) <= 50
                //&& filter_var($_val_, FILTER_VALIDATE_EMAIL));
                // Pretty basic email validation.
                && 1 === \preg_match(
                    '{^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$}', $_val_));
    }

    private static function _IsValidPhone($_val_) {
        return NULL === $_val_
            || '' === $_val_
            || 1 === \preg_match('{^\d{10}$}', $_val_);
    }
}

/* }}} */
/* {{{ OrderItem */

class OrderItem {
    public
        $id,
        $name,
        $qtity,
        $unitPrice;
}

/* }}} */

// =============================================================================

//namespace Lobuki\Persistence;

/* {{{ CategoryDto */

class CategoryDto extends Category {
    public
        $products;
}

/* }}} */
/* {{{ CollectionDto */

class CollectionDto extends Category {
    public
        $products;
}

/* }}} */

/* {{{ ILobukiDataStore */

interface ILobukiDataStore {
    public function addToBasket($_basket_, $_collection_,
        $_category_, $_product_, $_qtity_, $_price_);

    public function countProductsInCategory($_collection_, $_category_);

    public function countProductsInCollection($_collection_);

    public function createBasket();

    public function createOrder($_order_);

    public function findAllProducts();

    public function findAllProductsInCategory($_collection_, $_category_);

    public function findAllProductsInCollection($_collection_);

    public function findBasketLines($_basket_);

    public function findBasketOrder($_basket_);

    public function findBasketProducts($_basket_);

    public function findCategories();

    public function findCategoriesInCollection($_collection_);

    public function findCategory($_collection_, $_category_);

    public function findCategoryWithProducts($_collection_, $_category_,
        $_startIndex_, $_pageCount_);

    public function findCollection($_collection_);

    public function findCollections();

    public function findNewProducts();

    public function findProduct($_collection_, $_category_, $_product_);

    public function findProductsInCategory($_collection_, $_category_,
        $_startIndex_, $_pageCount_);

    public function findProductsInCollection($_collection_, $_startIndex_,
        $_pageCount_);

    public function removeFromBasket($_basket_, $_collection_,
        $_category_, $_product_);

    public function updateBasket($_basket_, $_collection_,
        $_category_, $_product_, $_qtity_);
}

/* }}} */

/* {{{ LobukiDataStoreFactory */

class LobukiDataStoreFactory {
    const
        PDO_ENGINE = 0,
        MYSQLI_ENGINE = 1;

    public static function Instance(Web\Settings $_settings_) {
        switch ($_settings_->databaseDriver()) {
            case self::MYSQLI_ENGINE;
                $class = 'Lobuki\SqlLobukiMysqliDataStore';    break;
            case self::PDO_ENGINE:
            default:
                $class = 'Lobuki\SpLobukiPdoDataStore';        break;
        }

        $dao = new $class(
            $_settings_->databaseHost(),
            $_settings_->databaseName(),
            $_settings_->databaseUserName(),
            $_settings_->databasePassword()
        );

        if ($_settings_->enableServerCache()) {
            include_once 'Cache.php';

            $dao = new FileCachedLobukiDataStore($dao, $_settings_->tempDir());
        }

        return $dao;
    }
}

/* }}} */

/* {{{ SpLobukiPdoDataStore */

class SpLobukiPdoDataStore
    extends Persistence\PdoDataStore
    implements ILobukiDataStore, \Serializable {

    /* {{{ serialize() */

    public function serialize() {
        ;
    }

    /* }}} */
    /* {{{ unserialize() */

    public function unserialize($_data_) {
        ;
    }

    /* }}} */

    /* {{{ addToBasket() */

    public function addToBasket($_basket_, $_collection_,
        $_category_, $_product_, $_qtity_, $_price_) {

        $sql = 'CALL uspAddToBasket(?,?,?,?,?,?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_basket_, \PDO::PARAM_INT);
        $stmt->bindValue(2, $_collection_, \PDO::PARAM_STR);
        $stmt->bindValue(3, $_category_, \PDO::PARAM_STR);
        $stmt->bindValue(4, $_product_, \PDO::PARAM_STR);
        $stmt->bindValue(5, $_qtity_, \PDO::PARAM_INT);
        $stmt->bindValue(6, $_price_, \PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_NUM);
        $this->free($stmt);

        if (NULL === $row || FALSE === $row) {
            return NULL;
        } else {
            return $row[0];
        }
    }

    /* }}} */
    /* {{{ createBasket() */

    public function createBasket() {
        $sql = 'INSERT INTO Baskets (creationTime) VALUES(NOW())';

        if (FALSE === $this->query($sql)) {
            throw new DataStoreException('Unable to create new basket');
        }

        return $this->lastInsertId();
    }

    /* }}} */
    /* {{{ createOrder() */

    public function createOrder($_order_) {
        $sql = 'CALL uspCreateOrder(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

        $o =& $_order_;

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $o->newOrderNbr(), \PDO::PARAM_STR);
        $stmt->bindValue(2, $o->basket, \PDO::PARAM_INT);
        $stmt->bindValue(3, $o->shippingAddressIsDifferent, \PDO::PARAM_BOOL);
        $stmt->bindValue(4, $o->billingName, \PDO::PARAM_STR);
        $stmt->bindValue(5, $o->billingFirstname, \PDO::PARAM_STR);
        $stmt->bindValue(6, $o->billingStreet, \PDO::PARAM_STR);
        $stmt->bindValue(7, $o->billingZipcode, \PDO::PARAM_STR);
        $stmt->bindValue(8, $o->billingCity, \PDO::PARAM_STR);
        $stmt->bindValue(9, $o->billingEmail, \PDO::PARAM_STR);
        $stmt->bindValue(10, $o->billingPhone, \PDO::PARAM_STR);
        $stmt->bindValue(11, $o->shippingName, \PDO::PARAM_STR);
        $stmt->bindValue(12, $o->shippingFirstname, \PDO::PARAM_STR);
        $stmt->bindValue(13, $o->shippingStreet, \PDO::PARAM_STR);
        $stmt->bindValue(14, $o->shippingZipcode, \PDO::PARAM_STR);
        $stmt->bindValue(15, $o->shippingCity, \PDO::PARAM_STR);
        $stmt->bindValue(16, $o->shippingEmail, \PDO::PARAM_STR);
        $stmt->bindValue(17, $o->shippingPhone, \PDO::PARAM_STR);
        $stmt->execute();

        $order = $stmt->fetchObject('Lobuki\Order', array($o->basket));

        if (NULL === $order || FALSE === $order) {
            $this->free($stmt);
            return NULL;
        }

        $stmt->nextRowset();

        $items = array();

        while ($line = $stmt->fetchObject('Lobuki\OrderItem')) {
            $items[] = $line;
        }
        $this->free($stmt);

        $order->items = $items;

        return $order;
    }

    /* }}} */
    /* {{{ countProductsInCategory() */

    // \return int
    public function countProductsInCategory($_collection_, $_category_) {
        $sql = 'CALL uspCountProductsInCategory(?,?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_collection_, \PDO::PARAM_STR);
        $stmt->bindValue(2, $_category_, \PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_NUM);
        $this->free($stmt);

        if (NULL === $row || FALSE === $row) {
            return NULL;
        } else {
            return $row[0];
        }
    }

    /* }}} */
    /* {{{ countProductsInCollection() */

    // \return int
    public function countProductsInCollection($_collection_) {
        $sql = 'CALL uspCountProductsInCollection(?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_collection_, \PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_NUM);
        $this->free($stmt);

        if (NULL === $row || FALSE === $row) {
            return NULL;
        } else {
            return $row[0];
        }
    }

    /* }}} */
    /* {{{ findAllProducts() */

    // \return array of Product
    public function findAllProducts() {
        $sql = 'CALL uspFindAllProducts()';

        $products = array();

        $result = $this->query($sql);
        while ($product = $result->fetchObject('Lobuki\Product')) {
            $products[] = $product;
        }
        $this->free($result);

        return $products;
    }

    /* }}} */
    /* {{{ findAllProductsInCategory() */

    // \return array of Product
    public function findAllProductsInCategory($_collection_, $_category_) {
        $sql = 'CALL uspFindAllProductsInCategory(?,?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_collection_, \PDO::PARAM_STR);
        $stmt->bindValue(2, $_category_, \PDO::PARAM_STR);
        $stmt->execute();

        $products = array();

        while ($product = $stmt->fetchObject('Lobuki\Product')) {
            $products[] = $product;
        }
        $this->free($stmt);

        return $products;
    }

    /* }}} */
    /* {{{ findAllProductsInCollection() */

    // \return array of Product
    public function findAllProductsInCollection($_collection_) {
        $sql = 'CALL uspFindAllProductsInCollection(?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_collection_, \PDO::PARAM_STR);
        $stmt->execute();

        $products = array();

        while ($product = $stmt->fetchObject('Lobuki\Product')) {
            $products[] = $product;
        }
        $this->free($stmt);

        return $products;
    }

    /* }}} */
    /* {{{ findBasketLines() */

    public function findBasketLines($_basket_) {
        $sql = 'CALL uspFindBasketLines(?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_basket_, \PDO::PARAM_INT);
        $stmt->execute();

        $basket = array();

        while ($line = $stmt->fetchObject('Lobuki\BasketLine')) {
            $basket[Basket::Key($line)] = $line;
        }
        $this->free($stmt);

        return $basket;
    }

    /* }}} */
    /* {{{ findBasketOrder() */

    public function findBasketOrder($_basket_) {
        $sql = 'CALL uspFindBasketOrder(?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_basket_, \PDO::PARAM_INT);
        $stmt->execute();

        $order = $stmt->fetchObject('Lobuki\Order', array($_basket_));
        $this->free($stmt);

        if (FALSE === $order) {
            return NULL;
        } else {
            return $order;
        }
    }

    /* }}} */
    /* {{{ findBasketProducts() */

    public function findBasketProducts($_basket_) {
        $sql = 'CALL uspFindBasketProducts(?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_basket_, \PDO::PARAM_INT);
        $stmt->execute();

        $products = array();

        while ($product = $stmt->fetchObject('Lobuki\BasketProduct')) {
            $products[] = $product;
        }
        $this->free($stmt);

        return $products;
    }

    /* }}} */
    /* {{{ findCategories() */

    // \return array of Category's
    public function findCategories() {
        $sql = 'CALL uspFindCategories()';

        $categories = array();

        $result = $this->query($sql);
        while ($category = $result->fetchObject('Lobuki\Category')) {
            $parent = $category->parent;
            if (\array_key_exists($parent, $categories)) {
                $categories[$parent][] = $category;
            } else {
                $categories[$parent] = array($category);
            }
        }
        $this->free($result);

        return $categories;
    }

    /* }}} */
    /* {{{ findCategoriesInCollection() */

    // \return array of Category
    public function findCategoriesInCollection($_collection_) {
        $sql = 'CALL uspFindCategoriesInCollection(?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_collection_, \PDO::PARAM_STR);
        $stmt->execute();

        $categories = array();

        while ($category = $stmt->fetchObject('Lobuki\Category')) {
            $categories[] = $category;
        }
        $this->free($stmt);

        return $categories;
    }

    /* }}} */
    /* {{{ findCategory() */

    // \return Category
    public function findCategory($_collection_, $_category_) {
        $sql = 'CALL uspFindCategory(?,?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_collection_, \PDO::PARAM_STR);
        $stmt->bindValue(2, $_category_, \PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->free($stmt);

        if (NULL === $row || FALSE == $row) {
            return NULL;
        }

        $category = new Category();
        $category->description = $row['description'];
        $category->enabled     = '1' === $row['enabled'];
        $category->parent      = $row['parent'];
        $category->position    = $row['position'];
        $category->shortTitle  = $row['shortTitle'];
        $category->tag         = $row['tag'];
        $category->title       = $row['title'];

        $collection = new Collection();
        $collection->description = $row['co_description'];
        $collection->enabled     = '1' === $row['co_enabled'];
        $collection->parent      = $row['co_parent'];
        $collection->position    = $row['co_position'];
        $collection->shortTitle  = $row['co_shortTitle'];
        $collection->tag         = $row['co_tag'];
        $collection->title       = $row['co_title'];

        $category->collection = $collection;

        return $category;
    }

    /* }}} */
    /* {{{ findCategoryWithProducts() */

    public function findCategoryWithProducts($_collection_, $_category_,
        $_startIndex_, $_pageCount_) {

        $sql = 'CALL uspFindCategoryWithProducts(?,?,?,?,?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_collection_, \PDO::PARAM_STR);
        $stmt->bindValue(2, $_category_, \PDO::PARAM_STR);
        $stmt->bindValue(3, $_startIndex_, \PDO::PARAM_INT);
        $stmt->bindValue(4, $_pageCount_, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (NULL === $row || FALSE == $row) {
            $this->free($stmt);
            return NULL;
        }

        $categoryDto = new CategoryDto();
        $categoryDto->description = $row['description'];
        $categoryDto->enabled     = '1' === $row['enabled'];
        $categoryDto->parent      = $row['parent'];
        $categoryDto->position    = $row['position'];
        $categoryDto->shortTitle  = $row['shortTitle'];
        $categoryDto->tag         = $row['tag'];
        $categoryDto->title       = $row['title'];

        $collection = new Collection();
        $collection->description = $row['co_description'];
        $collection->enabled     = '1' === $row['co_enabled'];
        $collection->parent      = $row['co_parent'];
        $collection->position    = $row['co_position'];
        $collection->shortTitle  = $row['co_shortTitle'];
        $collection->tag         = $row['co_tag'];
        $collection->title       = $row['co_title'];

        $categoryDto->collection = $collection;

        $stmt->nextRowset();

        $products = array();

        while ($item = $stmt->fetchObject('Lobuki\Product')) {
            $products[] = $item;
        }

        $categoryDto->products = $products;

        $this->free($stmt);

        return $categoryDto;
    }

    /* }}} */
    /* {{{ findCollection() */

    // \return Collection
    public function findCollection($_tag_) {
        $sql = 'CALL uspFindCollection(?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_tag_, \PDO::PARAM_STR);
        $stmt->execute();

        $collection = $stmt->fetchObject('Lobuki\Collection');
        $this->free($stmt);

        if (FALSE === $collection) {
            return NULL;
        } else {
            return $collection;
        }
    }

    /* }}} */
    /* {{{ findCollections() */

    // \return array of Collection
    public function findCollections() {
        $sql = 'CALL uspFindCollections()';

        $collections = array();

        $result = $this->query($sql);
        while ($collection = $result->fetchObject('Lobuki\Collection')) {
            $collections[] = $collection;
        }
        $this->free($result);

        return $collections;
    }

    /* }}} */
    /* {{{ findNewProducts() */

    // \return array of Product
    public function findNewProducts() {
        $sql = 'CALL uspFindNewProducts()';

        $products = array();

        $result = $this->query($sql);
        while ($product = $result->fetchObject('Lobuki\Product')) {
            $products[] = $product;
        }
        $this->free($result);

        return $products;
    }

    /* }}} */
    /* {{{ findProduct() */

    // \return Product
    public function findProduct($_collection_, $_category_, $_tag_) {
        $sql = 'CALL uspFindProduct(?,?,?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_collection_, \PDO::PARAM_STR);
        $stmt->bindValue(2, $_category_, \PDO::PARAM_STR);
        $stmt->bindValue(3, $_tag_, \PDO::PARAM_STR);
        $stmt->execute();

        $product = $stmt->fetchObject('Lobuki\Product');
        $this->free($stmt);

        if (NULL === $product || FALSE === $product) {
            return NULL;
        }

        if ('' !== $product->sharedImageTag) {
            $product->sharedImage = new SharedImage(
                $product->collection,
                $product->sharedImageTag
            );
        }

        return $product;
    }

    /* }}} */
    /* {{{ findProductsInCategory() */

    // \return array of Product
    public function findProductsInCategory($_collection_, $_category_,
        $_startIndex_, $_pageCount_) {

        $sql ='CALL uspFindProductsInCategory(?,?,?,?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_collection_, \PDO::PARAM_STR);
        $stmt->bindValue(2, $_category_, \PDO::PARAM_STR);
        $stmt->bindValue(3, $_startIndex_, \PDO::PARAM_INT);
        $stmt->bindValue(4, $_pageCount_, \PDO::PARAM_INT);
        $stmt->execute();

        $products = array();

        while ($product = $stmt->fetchObject('Lobuki\Product')) {
            $products[] = $product;
        }
        $this->free($stmt);

        return $products;
    }

    /* }}} */
    /* {{{ findProductsInCollection() */

    // \return array of Product
    public function findProductsInCollection($_collection_,
        $_startIndex_, $_pageCount_) {

        $sql = 'CALL uspFindProductsInCollection(?,?,?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_collection_, \PDO::PARAM_STR);
        $stmt->bindValue(2, $_startIndex_, \PDO::PARAM_INT);
        $stmt->bindValue(3, $_pageCount_, \PDO::PARAM_INT);
        $stmt->execute();

        $products = array();

        while ($product = $stmt->fetchObject('Lobuki\Product')) {
            $products[] = $product;
        }
        $this->free($stmt);

        return $products;
    }

    /* }}} */
    /* {{{ removeFromBasket() */

    public function removeFromBasket($_basket_, $_collection_,
        $_category_, $_product_) {

        $sql = 'CALL uspRemoveFromBasket(?,?,?,?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_basket_, \PDO::PARAM_INT);
        $stmt->bindValue(2, $_collection_, \PDO::PARAM_STR);
        $stmt->bindValue(3, $_category_, \PDO::PARAM_STR);
        $stmt->bindValue(4, $_product_, \PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_NUM);
        $this->free($stmt);

        if (NULL === $row || FALSE === $row) {
            return NULL;
        } else {
            return $row[0];
        }
    }

    /* }}} */
    /* {{{ updateBasket() */

    public function updateBasket($_basket_, $_collection_,
        $_category_, $_product_, $_qtity_) {

        $sql = 'CALL uspUpdateBasket(?,?,?,?,?)';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(1, $_qtity_, \PDO::PARAM_INT);
        $stmt->bindValue(2, $_basket_, \PDO::PARAM_INT);
        $stmt->bindValue(3, $_collection_, \PDO::PARAM_STR);
        $stmt->bindValue(4, $_category_, \PDO::PARAM_STR);
        $stmt->bindValue(5, $_product_, \PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_NUM);
        $this->free($stmt);

        if (NULL === $row || FALSE === $row) {
            return NULL;
        } else {
            return $row[0];
        }
    }

    /* }}} */
}

/* }}} */
/* {{{ FileCachedLobukiDataStore */

class FileCachedLobukiDataStore implements ILobukiDataStore {
    private
        $_cache,
        $_inner;

    public function __construct(ILobukiDataStore $_inner_, $_tempDir_) {
        $this->_inner = $_inner_;

        $options = array(
            'automaticCleaningFactor'   => 20,
            'cacheDir'                  => $_tempDir_,
            'fileNameProtection'        => TRUE,
            'hashedDirectoryLevel'      => 1,
            'lifeTime'                  => 3600, // 1H
            'writeControl'              => TRUE,
            'readControl'               => TRUE,
            'readControlType'           => 'crc32',
        );
        $this->_cache = new \MethodCache($options);
    }

    public function createBasket() {
        return $this->_inner->createBasket();
    }

    public function createOrder($_order_) {
        return $this->_inner->createOrder($_order_);
    }

    public function findBasketLines($_basket_) {
        return $this->_inner->findBasketLines($_basket_);
    }

    public function findBasketOrder($_basket_) {
        return $this->_inner->findBasketOrder($_basket_);
    }

    public function findBasketProducts($_basket_) {
        return $this->_inner->findBasketProducts($_basket_);
    }

    public function addToBasket($_basket_, $_collection_,
        $_category_, $_product_, $_qtity_, $_price_) {

        return $this->_inner->addToBasket($_basket_, $_collection_,
            $_category_, $_product_, $_qtity_, $_price_);
    }

    public function updateBasket($_basket_, $_collection_,
        $_category_, $_product_, $_qtity_) {

        return $this->_inner->updateBasket($_basket_, $_collection_,
            $_category_, $_product_, $_qtity_);
    }

    public function removeFromBasket($_basket_, $_collection_,
        $_category_, $_product_) {

        return $this->_inner->removeFromBasket($_basket_, $_collection_,
            $_category_, $_product_);
    }

    public function countProductsInCategory($_collection_, $_category_) {
        try {
            return \count($this->findAllProductsInCategory(
                $_collection_, $_category_));
        } catch (\CacheException $e) {
            return $this->_inner->countProductsInCategory(
                $_collection_, $_category_);
        }
    }

    public function countProductsInCollection($_collection_) {
        try {
            return \count($this->findAllProductsInCollection($_collection_));
        } catch (\CacheException $e) {
            return $this->_inner->countProductsInCollection($_collection_);
        }
    }

    public function findAllProducts() {
        try {
            return $this->_cache->invokeMethod($this->_inner, 'findAllProducts');
        } catch (\CacheException $e) {
            return $this->_inner->findAllProducts();
        }
    }

    public function findAllProductsInCategory($_collection_, $_category_) {
        try {
            return $this->_cache->invokeMethod($this->_inner,
                'findAllProductsInCategory', $_collection_, $_category_);
        } catch (\CacheException $e) {
            return $this->_inner->findAllProductsInCategory(
                $_collection_, $_category_);
        }
    }

    public function findAllProductsInCollection($_collection_) {
        try {
            return $this->_cache->invokeMethod($this->_inner,
                'findAllProductsInCollection', $_collection_);
        } catch (\CacheException $e) {
            return $this->_inner->findAllProductsInCollection($_collection_);
        }
    }

    public function findCategories() {
        try {
            return $this->_cache->invokeMethod($this->_inner,
                'findCategories');
        } catch (\CacheException $e) {
            return $this->_inner->findCategories();
        }
    }

    public function findCategoriesInCollection($_collection_) {
        try {
            return $this->_cache->invokeMethod($this->_inner,
                'findCategoriesInCollection', $_collection_);
        } catch (\CacheException $e) {
            return $this->_inner->findCategoriesInCollection($_collection_);
        }
    }

    public function findCategory($_collection_, $_category_) {
        try {
            return $this->_cache->invokeMethod($this->_inner,
                'findCategory', $_collection_, $_category_);
        } catch (\CacheException $e) {
            return $this->_inner->findCategory($_collection_, $_category_);
        }
    }

    public function findCollection($_collection_) {
        try {
            return $this->_cache->invokeMethod($this->_inner,
                'findCollection', $_collection_);
        } catch (\CacheException $e) {
            return $this->_inner->findCollection($_collection_);
        }
    }

    public function findCollections() {
        try {
            return $this->_cache->invokeMethod($this->_inner,
                'findCollections');
        } catch (\CacheException $e) {
            return $this->_inner->findCollections();
        }
    }

    public function findNewProducts() {
        try {
            return $this->_cache->invokeMethod($this->_inner,
                'findNewProducts');
        } catch (\CacheException $e) {
            return $this->_inner->findNewProducts();
        }
    }

    public function findProduct($_collection_, $_category_, $_product_) {
        try {
            return $this->_cache->invokeMethod($this->_inner, 'findProduct',
                $_collection_, $_category_, $_product_);
        } catch (\CacheException $e) {
            return $this->_inner->findProduct($_collection_, $_category_, $_product_);
        }
    }

    public function findProductsInCategory($_collection_,
        $_category_, $_startIndex_, $_pageCount_) {

        try {
            return array_slice($this->findAllProductsInCategory($_collection_, $_category_),
                $_startIndex_, $_pageCount_);
        } catch (\CacheException $e) {
            return $this->_inner->findProductsInCategory($_collection_, $_category_, $_startIndex_, $_pageCount_);
        }
    }

    public function findProductsInCollection($_collection_,
        $_startIndex_, $_pageCount_) {

        try {
            return array_slice(
                $this->findAllProductsInCollection($_collection_),
                $_startIndex_, $_pageCount_);
        } catch (\CacheException $e) {
            return $this->_inner->findProductsInCollection(
                $_collection_, $_startIndex_, $_pageCount_);
        }
    }

    public function findCategoryWithProducts($_collection_, $_category_,
        $_startIndex_, $_pageCount_) {
        try {
            return $this->_cache->invokeMethod($this->_inner,
                'findCategoryWithProducts',
                $_collection_, $_category_,
                $_startIndex_, $_pageCount_);
        } catch (\CacheException $e) {
            return $this->_inner->findCategoryWithProducts(
                $_collection_, $_category_,
                $_startIndex_, $_pageCount_);
        }
    }
}

/* }}} */

// EOF

