<?php

define('OVH_BASE_DIR', '/homez.311/lobukist/');

$host = $_SERVER['HTTP_HOST'];

switch ($host) {
    case 'narvalo.lan':
        set_include_path('/opt/www/src/sites/lobuki-sticker.com/lib');
        break;
    case 'lobuki.narvalo.lan':
        // FIXME: should be able to set the include path
        break;
    case 't.lobuki-sticker.com':
        set_include_path(OVH_BASE_DIR . 'share/lobukitest/lib');
        break;
    case 'lobuki-sticker.com':
    default:
        set_include_path(OVH_BASE_DIR . 'share/lobuki/lib');
        break;
}

require_once 'Narvalo.php';
require_once 'Lobuki.php';

use Narvalo\Web as Web;

switch ($host) {
    case 'narvalo.lan':
        $context = Lobuki\ServerContext::Development;
        $debugLevel = Web\DebugLevel::All();
        //$debugLevel = Web\DebugLevel::RunTime;
        //$debugLevel = Web\DebugLevel::DataBase;
        //$debugLevel = Web\DebugLevel::JavaScript;
        //$debugLevel = Web\DebugLevel::StyleSheet;
        break;
    case 'lobuki.narvalo.lan':
        $context = Lobuki\ServerContext::Test;
        $debugLevel = Web\DebugLevel::RunTime;
        break;
    case 't.lobuki-sticker.com':
        $context = Lobuki\ServerContext::PreProduction;
        $debugLevel = Web\DebugLevel::RunTime;
        break;
    case 'lobuki-sticker.com':
    default:
        $context = Lobuki\ServerContext::Production;
        $debugLevel = Web\DebugLevel::None;
        break;
}

// Start application and process request
$app = new Lobuki\LobukiApp($context);
$app->start($debugLevel)->processRequest($_REQUEST);

