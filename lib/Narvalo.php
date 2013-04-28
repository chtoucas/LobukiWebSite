<?php

// =============================================================================

namespace Narvalo;

const Version = '0.1';

// {{{ ArgumentException

class ArgumentException extends \Exception {
    private $_paramName;

    public function __construct($_message_ = '', $_paramName_ = '',
        \Exception $_innerException_ = NULL) {

        $this->_paramName = $_paramName_;
        parent::__construct($_message_, 0, $_innerException_);
    }

    public function getParamName() {
        return $this->_paramName;
    }
}

// }}}
// {{{ ArgumentNullException

class ArgumentNullException extends ArgumentException { }

// }}}
// {{{ InvalidOperationException

class InvalidOperationException extends \Exception { }

// }}}

// {{{ Type

class Type {
    const
        UnknownType  = 0,
        // simple types
        NullType     = 1,
        BooleanType  = 2,
        IntegerType  = 3,
        FloatType    = 4,
        StringType   = 5,
        // complex types
        ArrayType    = 10,
        HashType     = 11,
        ObjectType   = 12,
        ResourceType = 13;
    const
        Delimiter       = '\\',
        GlobalNamespace = '\\';

    private static
        /// \see http://www.php.net/manual/fr/language.oop5.basic.php
        $_TypeNameRegex = "/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/",
        // TODO: check this
        $_AssemblyNameRegex
            = "/^[a-zA-Z_\x7f-\xff][\\a-zA-Z0-9_\x7f-\xff]*[a-zA-Z0-9_\x7f-\xff]$/";

    private
        /// \var string
        $_name,
        /// \var string
        $_assemblyName;

    public function __construct($_name_, $_assemblyName_ = self::GlobalNamespace) {
        $this->_name         = $_name_;
        $this->_assemblyName = $_assemblyName_;
    }

    /// \brief  Return the datatype of $_val_
    ///
    /// Why create our own function? you say, there is already gettype!
    /// According to the documentation, we should never rely on gettype...
    /// Another difference with gettype is that we return a different type
    /// for hashes (associative arrays) and real arrays.
    ///
    /// \param  $_val_ (mixed) Any PHP structure
    /// \return A string representing a somehow extended PHP type:
    ///      - null
    ///      - boolean
    ///      - integer
    ///      - float
    ///      - string
    ///      - array
    ///      - hash
    ///      - object
    ///      - resource
    /// \return Returns NULL if none of above
    public static function GetType($_value_) {
        if (NULL === $_value_) {
            // keep this on top

            return NullType;
        }
        elseif (\is_string($_value_)) {

            return StringType;
        }
        elseif (\is_int($_value_)) {

            return IntegerType;
        }
        elseif (\is_float($_value_)) {

            return FloatType;
        }
        elseif (\is_bool($_value_)) {

            return BooleanType;
        }
        elseif (\is_array($_value_)) {
            // much faster alternative to the usual snippet:
            // array_keys($_value_) === range(0, count($_value_) - 1)
            // || empty($_value_)

            $i = 0;

            while (list($k, ) = each($_value_)) {
                if ($k !== $i) {

                    return HashType;
                }
                $i++;
            }

            return ArrayType;
        }
        elseif (\is_object($_value_)) {

            return ObjectType;
        }
        elseif (\is_resource($_value_)) {

            return ResourceType;
        }
        else {

            return UnknownType;
        }
    }

    public static function IsComplex($_value_) {
        return !self::IsSimple($_value_);
    }

    public static function IsSimple($_value_) {
        if (NULL === $_value_) {
            throw new ArgumentNullException('XXX', 'value');
        }

        switch ($type = self::ParseType($_value_)) {
            case BooleanType:
            case IntegerType:
            case FloatType:
            case StringType:
                return \TRUE;
            default:
                return \FALSE;
        }
    }

    /// \return bool.
    public static function IsValidName($_name_) {
        return 1 === \preg_match(self::$_TypeNameRegex, $_name_);
    }

    /// \return bool.
    public static function IsValidAssemblyName($_name_) {
        return 1 === \preg_match(self::$_AssemblyNameRegex,  $_name_);
    }

    /// \return string.
    public function getFullyQualifiedName() {
        return Type::Delimiter . $this->getQualifiedName();
    }

    /// \return string.
    public function getName() {
        return $this->_name;
    }

    /// \return string.
    public function getQualifiedName() {
        return $this->_assemblyName . Type::Delimiter . $this->_name;
    }
}

// }}}
// {{{ Loader

class Loader {
    const
        FileExtension = '.php';

    /// \brief  Dynamically load a code file.
    /// \throw  InvalidOperationException
    /// \return void.
    public static function LoadFile($_path_) {
        if (\FALSE === (include_once $_path_)) {
            // NB: only works if the included file does not return FALSE
            throw new InvalidOperationException();
        }
    }

    public static function LoadType(Type $_type_) {
        self::LoadFile( self::GetTypePath($_type_) );
    }

    protected static function GetTypePath(Type $_type_) {
        return self::ToPath($_type_->getQualifiedName());
    }

    protected static function ToPath($_name_) {
        return \str_replace(Type::Delimiter, \DIRECTORY_SEPARATOR, $_name_)
            . self::FileExtension;
    }
}

// }}}

// {{{ BorgException

class BorgException extends \Exception { }

// }}}
// {{{ Borg

/// Straightforward implementation of the Borg Pattern.
class Borg {
    private static
        $_Initialized = \FALSE,
        $_SharedState;

    protected $state;

    /// \throw  BorgException
    public function __construct($_state_ = NULL) {
        if (NULL !== $_state_) {
            // We initialize the borg by using the provided state.
            self::Initialize($_state_);
        } else if (\FALSE === self::$_Initialized) {
            throw new BorgException('The borg has not yet been initialized.');
        }

        $this->state =& self::$_SharedState;
    }

    /// \throw  BorgException
    /// \return void
    protected static function Initialize(array $_state_) {
        if (\TRUE === self::$_Initialized) {
            throw new BorgException('The borg has already been initialized.');
        }

        self::$_SharedState = $_state_;
        self::$_Initialized = \TRUE;
    }

    /// return boolean
    public static function IsReady() {
        return self::$_Initialized;
    }
}

// }}}
// {{{ ArrayBorg

class ArrayBorg extends Borg {
    public function __construct(array $_state_ = NULL) {
        parent::__construct($_state_);
    }

    /// \return boolean
    public function hasKey($_key_) {
        return \array_key_exists($_key_, $this->state);
    }

    /// \return void
    public function setValue($_key_, $_value_) {
        $this->state[$_key_] = $_value_;
    }

    /// \return mixed
    public function getValue($_key_) {
        if (!$this->hasKey($_key_)) {
            return NULL;
        }

        return $this->state[$_key_];
    }
}

// }}}

const Base58Alphabet = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';

// {{{ base58_encode()

function base58_encode($_val_) {
    $alpha &= Base58Alphabet;
    $result = '';

    while (\bccomp($_val_, $count) >= 0) {
        $div = \bcdiv($_val_, 58);
        $mod = \bcmod($_val_, 58);
        $result = $alpha[$mod] . $result;
        $_val_ = \intval($div);
    }

    if ($_val_) {
        $result = $alpha[$_val_] . $result;
    }

    return $result;
}

// }}}
// {{{ base58_decode()

function base58_decode($_val_) {
    $result = 0;
    $pow  = 1;

    while (($len = \strlen($_val_)) > 0) {
        $digit = $_val_[$len - 1];
        $result += \bcmul($pow, \strpos(Base58Alphabet, $digit));
        $pow = \bcmul($pow, 58);
        $_val_ = \substr($_val_, 0, -1);
    }

    return $result;
}

// }}}

// =============================================================================

namespace Narvalo\Persistence;

class DataStoreException extends \Exception { }

// {{{ DataPager

class DataPager {
    public
        $itemMax,
        $pageCount,
        $pageIndex;

    public function __construct($_pageIndex_, $_pageCount_, $_itemMax_) {
        $this->itemMax   = $_itemMax_;
        $this->pageIndex = $_pageIndex_;
        $this->pageCount = $_pageCount_;
    }

    public static function Initialize($_pageIndex_, $_itemCount_, $_itemMax_) {
        if (NULL === $_itemCount_) {
            $pageCount = 1;
            $pageIndex = 1;
        } else {
            $pageCount
                = 1 + ($_itemCount_ - $_itemCount_ % $_itemMax_) / $_itemMax_;
            $pageIndex = \min(\max(1, $_pageIndex_), $pageCount);
        }

        return new self($pageIndex, $pageCount, $_itemMax_);
    }

    public function isFirstPage() {
        return 1 === $this->pageIndex;
    }

    public function isLastPage() {
        return $this->pageCount === $this->pageIndex;
    }

    public function getStartIndex() {
        return ($this->pageIndex - 1) * $this->itemMax;
    }
}

// }}}
// {{{ DataStore

class DataStore {
    public static $Debug = \FALSE;
    protected
        $database,
        $host,
        $password,
        $userName;

    public function __construct($_host_, $_database_, $_userName_, $_password_) {
        $this->database = $_database_;
        $this->host     = $_host_;
        $this->userName = $_userName_;
        $this->password = $_password_;
    }
}

// }}}
// {{{ PdoDataStore

// FIXME: remove the dependency on MySQL
class PdoDataStore extends DataStore {
    protected $handle;

    /// \brief Close connection to MySQL
    public function close() {
        if (NULL === $this->handle) {
            return;
        }

        $this->handle = NULL;
    }

    protected function free($_result_) {
        $_result_->closeCursor();
    }

    /// \brief Open connection to MySQL
    protected function open() {
        if (NULL !== $this->handle) {
            return;
        }

        $dsn = \sprintf('mysql:host=%s;dbname=%s', $this->host, $this->database);

        try {
            $handle = new \PDO($dsn,
                $this->userName, $this->password, array(
                    \PDO::ATTR_ERRMODE
                        => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_PERSISTENT
                        => true,
                    \PDO::MYSQL_ATTR_INIT_COMMAND 
                        => "SET NAMES utf8"
                ));
        } catch (\PDOException $e) {
            throw new DataStoreException(
                'Unable to connect to MySQL: ' . $e->getMessage());
        }

        // Tell PDO to throw an exception on error.
        //$handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //$handle->query('SET NAMES \'utf8\'');

        $this->handle =& $handle;
    }

    protected function quote($_val_) {
        $this->open();
        return $this->handle->quote($_val_);
    }

    protected function lastInsertId() {
        return $this->handle->lastInsertId();
    }

    protected function prepare($_query_) {
        $this->open();

        if (DataStore::$Debug) {
            \error_log('DEBUG ' . __CLASS__ . ': ' . $_query_);
        }

        try {
            $stmt = $this->handle->prepare($_query_);
        } catch (\PDOException $e) {
            throw new DataStoreException(
                'Unable to prepare stmt MySQL: ' . $e->getMessage() . '. ' . $e->getCode());
        }

        return $stmt;
    }

    /// \brief Execute $_query_ and return the result
    protected function query($_query_) {
        $this->open();

        if (DataStore::$Debug) {
            \error_log('DEBUG: ' . __CLASS__ . ' ' . $_query_);
        }

        try {
            $result = $this->handle->query($_query_);
        } catch (\PDOException $e) {
            throw new DataStoreException(
                'Unable to query MySQL: ' . $e->getMessage(). '. ' . $e->getCode());
        }

        return $result;
    }
}

// }}}
// {{{ MysqliDataStore

class MysqliDataStore extends DataStore {
    protected $handle;

    /// \brief Close connection to MySQL
    public function close() {
        if (NULL === $this->handle) {
            return;
        }

        if (\FALSE === $this->handle->close()) {
            \error_log('Unable to close connexion to MySQL: ' . $this->handle->error);
        }

        $this->handle = NULL;
    }

    protected function free($_result_) {
        $_result_->free();

        if ($this->handle->more_results()) {
            $this->handle->next_result();
        }
    }

    protected function nextResult() {
        $this->handle->next_result();
    }

    /// \brief Open connection to MySQL
    protected function open() {
        if (NULL !== $this->handle) {
            return;
        }

        $handle = new mysqli($this->host, $this->userName,
            $this->password, $this->database);

        if (mysqli_connect_errno()) {
            throw new DataStoreException(
                'Unable to connect to MySQL: ' . mysqli_connect_error());
        }

        $handle->query("SET NAMES 'utf8'");

        $this->handle =& $handle;
    }

    protected function quote($_val_) {
        $this->open();
        return $this->handle->real_escape_string($_val_);
    }

    protected function lastInsertId() {
        return $this->handle->insert_id;
    }

    protected function multiQuery($_queries_) {
        $this->open();

        $result = $this->handle->multi_query($_queries_);

        if (\FALSE === $result) {
            throw new DataStoreException(
                'Unable to query MySQL: ' . $this->handle->error);
        }

        return $result;
    }

    /// \brief Execute $_query_ and return the result
    protected function query($_query_) {
        $this->open();

        $result = $this->handle->query($_query_);

        if (\FALSE === $result) {
            throw new DataStoreException(
                'Unable to query MySQL: ' . $this->handle->error);
        }

        return $result;
    }

    protected function storeResult() {
        return $this->handle->store_result();
    }
}

// }}}

// =============================================================================

namespace Narvalo\Web;

use Narvalo;

// {{{ BadRequestException

class BadRequestException extends \Exception { }

// }}}
// {{{ UnsupportedHttpMethodException

class UnsupportedHttpMethodException extends \Exception { }

// }}}
// {{{ HttpException

class HttpException extends \Exception {
    private $_statusCode;

    public function __construct($_httpCode_, $_message_,
        \Exception $_innerException_ = NULL) {

        $this->_statusCode = $_httpCode_;

        parent::__construct($_message_, 0, $_innerException_);
    }
}

// }}}

// {{{ IHttpHandler

interface IHttpHandler {
    /// \return void.
    public function processRequest(HttpContext $_context_);
}

// }}}

// {{{ HttpVerbs

final class HttpVerbs {
    const
        Get    = 'GET',
        Post   = 'POST',
        Put    = 'PUT',
        Delete = 'DELETE',
        Head   = 'HEAD';
}

// }}}
// {{{ HttpRequest

final class HttpRequest {
    public function __construct() {
        ;
    }

    public function getHttpMethod() {
        ;
    }

    public function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'];
    }
}

// }}}
// {{{ HttpResponse

final class HttpResponse {
    public
        $status,
        $statusCode,
        $statusDescription;

    private
        $_headers = array();

    public function __construct() {
        ;
    }

    public function getHeaders() {
        return $this->_headers;
    }

    public function appendHeader($_name_, $_value_) {
        $this->_headers[$_name_] = $_value_;
    }

    public function clear() {
        throw new Narvalo\NotImplementedException();
        $this->clearHeaders();
        $this->clearContent();
    }

    public function clearHeaders() {
        $this->_headers = array();
    }

    public function clearContent() {
        throw new Narvalo\NotImplementedException();
    }

    public function redirect($_url_) {
        throw new Narvalo\NotImplementedException();
    }

    public function end() {
        throw new Narvalo\NotImplementedException();
        exit();
    }
}

// }}}
// {{{ HttpContext

final class HttpContext {
    public static
        /// \var Narvalo\Web\HttpContext
        $Current;

    private
        $_errors = array(),
        /// \var Narvalo\Web\HttpRequest
        $_request,
        /// \var Narvalo\Web\HttpResponse
        $_response;

    public function __construct(HttpRequest $_request_, HttpResponse $_response_) {
        $this->_request  = $_request_;
        $this->_response = $_response_;
        // TODO: make it a borg ?
        self::$Current = $this;
    }

    public static function Current() {
        return self::$Current;
    }

    public function allErrors() {
        return $this->_errors;
    }

    public function application() {
        throw new Narvalo\NotImplementedException();
    }

    public function error() {
        throw new Narvalo\NotImplementedException();
    }

    /// \return Narvalo\Web\HttpRequest
    public function getRequest() {
        return $this->_request;
    }

    /// \return Narvalo\Web\HttpResponse
    public function getResponse() {
        return $this->_response;
    }

    public function addError() {
        throw new Narvalo\NotImplementedException();
    }

    public function clearError() {
        $this->_errors = array();
    }
}

// }}}
// {{{ HttpApplication

class HttpApplication implements IHttpHandler {
    const
        BeginRequestEvent               = 1,
        AuthenticateRequestEvent        = 2,
        AuthorizeRequestEvent           = 3,
        PostAuthorizeRequestEvent       = 4,
        ResolveRequestCacheEvent        = 5,
        PostResolveRequestCacheEvent    = 6,
        PostMapRequestHandlerEvent      = 7,
        AcquireRequestStateEvent        = 8,
        PreRequestHandlerExecuteEvent   = 9,
        ReleaseRequestStateEvent        = 10,
        PostReleaseRequestStateEvent    = 11,
        UpdateRequestCacheEvent         = 12,
        PostUpdateRequestCacheEvent     = 13,
        LogRequestEvent                 = 14,
        PostLogRequestEvent             = 15,
        EndRequestEvent                 = 16,

        ErrorEvent                      = 20;

    private
        $_eventHandlers = array();

    public function __construct() {
        ;
    }

    /// \var Narvalo\Web\HttpContext
    public function getHttpContext() {
        return HttpContext::Current();
    }

    public function getRequest() {
        return HttpContext::Current()->getRequest();
    }

    public function getResponse() {
        return HttpContext::Current()->getResponse();
    }

    public function registerEventHandler($_eventType_, $_handler_) {
        if (!array_key_exists($_eventType_, $this->_eventHandlers)) {
            $this->_eventHandlers[$_eventType_] = array();
        }

        $this->_eventHandlers[$_eventType_][] = $_handler_;
    }

    public function onStart() {
        ;
    }

    public function onEnd() {
        ;
    }

    public function onError() {
        ;
    }

    public function onSessionStart() {
        ;
    }

    public function onSessionEnd() {
        ;
    }

    private function _processEvent($_eventType_) {
        foreach ($this->_eventHandlers[$_eventType_] as $handler) {
            $handler( $this->Context() );
        }
    }

    public function processRequest(HttpContext $_context_) {
        //HttpContext::SetCurrent($_context_);

        foreach ($this->_eventHandlers as $type => $handlers) {
            $this->_processEvent($type);
        }
    }
}

// }}}

// {{{ RequestContext

class RequestContext {
    private
        /// \var Narvalo\Web\HttpContext
        $_httpContext;

    public function __construct(HttpContext $_httpContext_) {
        $this->_httpContext = $_httpContext_;
    }

    /// \brief  Informations sur la requête courante.
    public function getHttpContext() {
        return $this->_httpContext;
    }
}

// }}}

/*
// {{{ HttpServer

class HttpServer {
    private
        /// \var Narvalo\Web\HttpApplication
        $_httpApplication;

    private static
        /// \var Narvalo\Web\IHttpModules[]
        $_Modules = array();

    public function __construct(HttpApplication $_httpApplication_) {
        $this->_httpApplication = $_httpApplication_;
    }

    public static function AddModule(IHttpModule $_module_) {
        self::$_Modules[] = $_module_;
    }

    public function start() {
        $this->_httpApplication->onStart();

        foreach (self::$_Modules as $module) {
            $module->init($this->_httpApplication);
        }
    }

    public function processRequest() {
        $context = new HttpContext(new HttpRequest(), new HttpResponse());
        $this->_httpApplication->processRequest($context);
    }

    public function shutdown() {
        $this->_httpApplication->onEnd();
    }
}

// }}}
 */

// {{{ IPathFactory

interface IPathFactory {
    /// \return string
    public function absoluteLink($_filePath_);

    /// \return string
    public function image($_filePath_);

    /// \return string
    public function link($_filePath_);

    /// \return string
    public function script($_filePath_);

    /// \return string
    public function scriptPrefix();

    /// \return string
    public function style($_filePath_);
}

// }}}
// {{{ ISettings

// Placeholder for website settings.
interface ISettings {
    public function altStaticsPrefix();

    /// Base URL.
    /// \return string
    public function baseUrl();

    /// Database driver
    /// \return string
    public function databaseDriver();

    /// Database Host
    /// \return string
    public function databaseHost();

    /// Database Name
    /// \return string
    public function databaseName();

    /// Database Password
    /// \return string
    public function databasePassword();

    /// Database UserName
    /// \return string
    public function databaseUserName();

    /// TRUE if enabled, FALSE otherwise.
    /// \return boolean
    public function enableClientCache();
    //
    /// TRUE if enabled, FALSE otherwise.
    /// \return boolean
    public function enableServerCache();

    /// TRUE if enabled, FALSE otherwise.
    /// \return boolean
    public function enableGoogleAnalytics();

    ///  Error log file name.
    /// \return boolean
    public function errorLog();

    /// Google Analytics Key.
    /// \return string
    public function googleAnalyticsKey();

    /// Version of images.
    /// \return string
    //public function imageVersion();

    public function paypalAccount();

    public function paypalAuthToken();

    public function paypalNotifyUrl();

    public function paypalUrl();

    /// Version for script files.
    /// \return string
    public function scriptVersion();

    /// Prefix for all statics.
    /// For instance: //statics.domain.com/
    /// \return string
    public function staticsPrefix();

    /// Version used for style files.
    /// \return string
    public function styleVersion();

    /// Directory used to save temporary files.
    /// \return string
    public function tempDir();

    /// Path to views repository.
    /// \return string
    public function viewRepository();

    /// Virtual server path.
    /// \return string
    public function virtualPath();
}

// }}}

// {{{ DebugLevel

class DebugLevel {
    const
        None       = 0,
        JavaScript = 1,
        StyleSheet = 2,
        RunTime    = 4,
        DataBase   = 8;

    /// Enable full debug.
    public static function All() {
        return
              self::DataBase
            | self::JavaScript
            | self::RunTime
            | self::StyleSheet
            ;
    }

    /// Only debug the UI.
    public static function UI() {
        return self::JavaScript | self::StyleSheet;
    }
}

// }}}
// {{{ HttpError

class HttpError {
    const
        SeeOther           = 303,
        BadRequest         = 400,
        Unauthorized       = 401,
        Forbidden          = 403,
        NotFound           = 404,
        MethodNotAllowed   = 405,
        PreconditionFailed = 412,
        FatalError         = 500;

    public static function Header($_status_) {
        $statusLine = '';
        switch ($_status_) {
            case self::SeeOther:
                $statusLine = '303 See Other';              break;
            case self::BadRequest:
                $statusLine = '400 Bad Request';            break;
            case self::Unauthorized:
                $statusLine = '401 Unauthorized';           break;
            case self::Forbidden:
                $statusLine = '403 Forbidden';              break;
            case self::NotFound:
                $statusLine = '404 Not Found';              break;
            case self::MethodNotAllowed:
                $statusLine = '405 Method Not Allowed';     break;
            case self::PreconditionFailed:
                $statusLine = '412 Precondition Failed';    break;
            case self::FatalError:
            default:
                $statusLine = '500 Internal Server Error';  break;
        }

        \header('HTTP/1.1 ' . $statusLine);
    }

    public static function DefaultMessage($_status_) {
        $message = '';
        switch ($_status_) {
            case self::SeeOther:
                $message = '';                                      break;
            case self::BadRequest:
                $message = 'Requête invalide';                      break;
            case self::Unauthorized:
                $message = 'Accès non autorisé';                    break;
            case self::Forbidden:
                $message = 'Interdit';                              break;
            case self::NotFound:
                $message = 'Page non trouvée';                      break;
            case self::MethodNotAllowed:
                $message = 'Méthode invalide';                      break;
            case self::PreconditionFailed:
                $message = 'Une précondition n\'est pas remplie';   break;
            case self::FatalError:
                $message = 'Problème technique';                    break;
            default:
                $message = '';                                      break;
        }

        return $message;
    }

    public static function SeeOther($_url_) {
        self::Header(self::SeeOther);
        \header('Location: ' . $_url_);
        exit();
    }

    public static function BadRequest($_msg_ = '') {
        self::Render(self::BadRequest, $_msg_);
    }

    public static function Unauthorized($_msg_ = '') {
        self::Render(self::Unauthorized, $_msg_);
    }

    public static function Forbidden($_msg_ = '') {
        self::Render(self::Forbidden, $_msg_);
    }

    public static function NotFound($_msg_ = '') {
        self::Render(self::NotFound, $_msg_);
    }

    public static function MethodNotAllowed($_msg_ = '') {
        self::Render(self::MethodNotAllowed, $_msg_);
    }

    public static function PreconditionFailed ($_msg_ = '') {
        self::Render(self::PreconditionFailed, $_msg_);
    }

    public static function FatalError($_msg_ = '') {
        self::Render(self::FatalError, $_msg_);
    }

    protected static function Render($_status_, $_msg_) {
        $msg = '' !== $_msg_ ? $_msg_ : self::DefaultMessage($_status_);
        self::Header($_status_);
        // FIXME EOL const.
        echo $msg . "\n";
        exit();
    }
}

// }}}
// {{{ Settings

class Settings extends Narvalo\ArrayBorg implements ISettings {
    const
        AltStaticsPrefix      = 'AltStaticsPrefix',
        BaseUrl               = 'BaseUrl',
        DatabaseDriver        = 'DatabaseDriver',
        DatabaseHost          = 'DatabaseHost',
        DatabaseName          = 'DatabaseName',
        DatabasePassword      = 'DatabasePassword',
        DatabaseUserName      = 'DatabaseUserName',
        EnableClientCache     = 'EnableClientCache',
        EnableServerCache     = 'EnableServerCache',
        ErrorLog              = 'ErrorLog',
        GoogleAnalyticsKey    = 'GoogleAnalyticsKey',
        //ImageVersion          = 'ImageVersion',
        PaypalAccount         = 'PaypalAccount',
        PaypalAuthToken       = 'PaypalAuthToken',
        PaypalNotifyUrl       = 'PaypalNotifyUrl',
        PaypalUrl             = 'PaypalUrl',
        ScriptVersion         = 'ScriptVersion',
        StaticsPrefix         = 'StaticsPrefix',
        StyleVersion          = 'StyleVersion',
        TempDir               = 'TempDir',
        ViewRepository        = 'ViewRepository',
        VirtualPath           = 'VirtualPath';

    //#region ISettings

    public function altStaticsPrefix() {
        return $this->getValue(self::AltStaticsPrefix);
    }

    public function baseUrl() {
        return $this->getValue(self::BaseUrl);
    }

    public function databaseHost() {
        return $this->getValue(self::DatabaseHost);
    }

    public function databaseDriver() {
        return $this->getValue(self::DatabaseDriver);
    }

    public function databaseName() {
        return $this->getValue(self::DatabaseName);
    }

    public function databasePassword() {
        return $this->getValue(self::DatabasePassword);
    }

    public function databaseUserName() {
        return $this->getValue(self::DatabaseUserName);
    }

    public function enableClientCache() {
        return $this->getValue(self::EnableClientCache);
    }

    public function enableGoogleAnalytics() {
        return '' !== $this->googleAnalyticsKey();
    }

    public function enableServerCache() {
        return $this->getValue(self::EnableServerCache);
    }

    public function errorLog() {
        return $this->getValue(self::ErrorLog);
    }

    public function googleAnalyticsKey() {
        return $this->getValue(self::GoogleAnalyticsKey);
    }

    /* public function imageVersion() {
        return $this->getValue(self::ImageVersion);
    } */

    public function paypalAccount() {
        return $this->getValue(self::PaypalAccount);
    }

    public function paypalAuthToken() {
        return $this->getValue(self::PaypalAuthToken);
    }

    public function paypalNotifyUrl() {
        return $this->getValue(self::PaypalNotifyUrl);
    }

    public function paypalUrl() {
        return $this->getValue(self::PaypalUrl);
    }

    public function scriptVersion() {
        return $this->getValue(self::ScriptVersion);
    }

    public function staticsPrefix() {
        return $this->getValue(self::StaticsPrefix);
    }

    public function styleVersion() {
        return $this->getValue(self::StyleVersion);
    }

    public function tempDir() {
        return $this->getValue(self::TempDir);
    }

    public function viewRepository() {
        return $this->getValue(self::ViewRepository);
    }

    public function virtualPath() {
        return $this->getValue(self::VirtualPath);
    }

    //#endregion
}

// }}}

// {{{ DefaultPathFactory

class DefaultPathFactory implements IPathFactory {
    private
        $_baseUrl;

    public function __construct() {
        // TODO: handle https
        $this->_baseUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/';
    }

    //#region IPathFactory

    public function absoluteLink($_filePath_) {
        return $this->_baseUrl . $_filePath_;
    }

    public function image($_filePath_) {
        return '/img/' + $_filePath_;
    }

    public function link($_filePath_) {
        return '/' + $_filePath_;
    }

    public function script($_filePath_) {
        return '/script/' +$_filePath_;
    }

    public function scriptPrefix() {
        return '/script/';
    }

    public function style($_filePath_) {
        return '/style/' + $_filePath_;
    }

    //#endregion
}

// }}}
// {{{ SettingsPathFactory

class SettingsPathFactory implements IPathFactory {
    private
        $_altImagePrefix,
        $_baseUrl,
        $_imagePrefix,
        $_scriptPrefix,
        $_stylePrefix,
        $_virtualPath;

    public function __construct(ISettings $_settings_) {
        $altStaticsPrefix = $_settings_->altStaticsPrefix();
        $staticsPrefix = $_settings_->staticsPrefix();

        /* if (NULL !== ($version = $_settings_->imageVersion())) {
            $this->_altImagePrefix = $altStaticsPrefix . 'img/' . $version . '/';
            $this->_imagePrefix    = $staticsPrefix . 'img/' . $version . '/';
        } else {
            $this->_altImagePrefix = $altStaticsPrefix . 'img/';
            $this->_imagePrefix    = $staticsPrefix . 'img/';
        } */
        $this->_altImagePrefix = $altStaticsPrefix . 'img/';
        $this->_imagePrefix    = $staticsPrefix . 'img/';

        if (NULL !== ($version = $_settings_->scriptVersion())) {
            $this->_scriptPrefix = $staticsPrefix . 'js/' . $version . '/';
        } else {
            $this->_scriptPrefix = $staticsPrefix . 'js/';
        }

        if (NULL !== ($version = $_settings_->styleVersion())) {
            $this->_stylePrefix = $staticsPrefix . 'css/' . $version . '/';
        } else {
            $this->_stylePrefix = $staticsPrefix . 'css/';
        }

        $this->_baseUrl     = $_settings_->baseUrl();
        $this->_virtualPath = $_settings_->virtualPath();
    }

    //#region IPathFactory

    public function absoluteLink($_filePath_) {
        return $this->_baseUrl . '/' . $_filePath_;
        //return $this->_baseUrl . $this->_virtualPath . $_filePath_;
    }

    public function image($_filePath_) {
        if (\strlen($_filePath_) & 1) {
            return $this->_imagePrefix . $_filePath_;
        } else {
            return $this->_altImagePrefix . $_filePath_;
        }
    }

    public function link($_filePath_) {
        return $this->_virtualPath . $_filePath_;
    }

    public function scriptPrefix() {
        return $this->_scriptPrefix;
    }

    public function script($_filePath_) {
        return $this->_scriptPrefix . $_filePath_;
    }

    public function style($_filePath_) {
        return $this->_stylePrefix . $_filePath_;
    }

    //#endregion
}

// }}}
// {{{ PathFactoryBuilder

class PathFactoryBuilder {
    private static $_Instance;
    private $_factory;

    public function __construct() {
        $this->setPathFactory(new DefaultPathFactory());
    }

    /// \return PathFactoryBuilder
    public static function Current() {
        if (NULL === self::$_Instance) {
            self::$_Instance = new self();
        }
        return self::$_Instance;
    }

    /// \return IPathFactory
    public function getPathFactory() {
        return $this->_factory;
    }

    /// \return void
    public function setPathFactory(IPathFactory $_factory_) {
        $this->_factory = $_factory_;
    }
}

// }}}
// {{{ Image

class Image {
    public
        $alt,
        $height,
        $path,
        $width;

    public function __construct(
        $_path_, $_alt_ = '', $_width_ = NULL, $_height_ = NULL) {
        $this->alt    = $_alt_;
        $this->height = $_height_;
        $this->path   = $_path_;
        $this->width  = $_width_;
    }
}

// }}}
// {{{ HtmlHelper

class HtmlHelper {
    protected $factory;

    // FIXME: remove the NULL option.
    public function __construct(IPathFactory $_factory_ = NULL) {
        $this->factory = NULL === $_factory_
            ? PathFactoryBuilder::Current()->getPathFactory()
            : $_factory_;
    }

    public function css($_path_, $_media_ = NULL) {
        $tag = '<link rel="stylesheet" href="' . $this->factory->style($_path_) . '"';

        if (NULL !== $_media_) {
            $tag .= ' media="' . $_media_ . '"/>';
        } else {
            $tag .= '/>';
        }

        return $tag;
    }

    public function cssList(array $_paths_, $_media_ = NULL) {
        $tag = '';
        for ($i = 0, $count = \count($_paths_); $i < $count; $i++) {
            $tag .= $this->css($_paths_[$i], $_media_);
        }
        return $tag;
    }

    public function imageTag(Image $_img_, array $_attributes_ = array()) {
        $tag = '<img src="' . $this->factory->image($_img_->path) . '"';

        if (NULL !== $_img_->alt) {
            $tag .= ' alt="' . $_img_->alt . '"';
        }

        if (NULL !== $_img_->height) {
            $tag .= ' height=' . $_img_->height;
        }

        if (NULL !== $_img_->width) {
            $tag .= ' width=' . $_img_->width;
        }

        $tag .= $this->_serialize($_attributes_) . ' />';

        return $tag;
    }

    public function image($_path_, $_alt_ = NULL, $_id_ = NULL,
        $_class_ = NULL, $_height_ = NULL, $_width_ = NULL) {

        $tag = '<img src="' . $this->factory->image($_path_) . '"';

        if (NULL !== $_alt_) {
            $tag .= ' alt="' . $_alt_ . '"';
        }

        if (NULL !== $_id_) {
            $tag .= ' id=' . $_id_;
        }

        if (NULL !== $_class_) {
            $tag .= ' class="' . $_class_ . '"';
        }

        if (NULL !== $_height_) {
            $tag .= ' height=' . $_height_;
        }

        if (NULL !== $_width_) {
            $tag .= ' width=' . $_width_;
        }

        $tag .= ' />';

        return $tag;
    }

    public function imageLink($_path_, $_inner_, array $_attributes_ = array()) {
        return $this->_link($this->factory->image($_path_), $_inner_, $_attributes_);
    }

    public function js($_path_, $_inline_ = NULL) {
        $tag = '<script src="' . $this->factory->script($_path_) . '">';

        if (NULL !== $_inline_) {
            $tag .= $_inline_;
        }

        $tag .= '</script>';

        return $tag;
    }

    public function jsList(array $_paths_) {
        $tag = '';
        for ($i = 0, $count = \count($_paths_); $i < $count; $i++) {
            $tag .= $this->js($_paths_[$i], NULL);
        }
        return $tag;
    }

    public function link($_path_, $_inner_, array $_attributes_ = array()) {
        return $this->_link($this->factory->link($_path_), $_inner_, $_attributes_);
    }

    private function _link($_link_, $_inner_, array $_attributes_ = array()) {
        $_attributes_['href'] = $_link_;

        return '<a'. $this->_serialize($_attributes_) . '>' . $_inner_ . '</a>';
    }

    private function _serialize($_attributes_) {
        $attributes = '';
        foreach ($_attributes_ as $k => $v) {
            $attributes .= ' ' . $k . '="' . $v . '"';
        }

        return $attributes;
    }
}

// }}}

// =============================================================================

namespace Narvalo\Web\Mvc;

use Narvalo\Persistence;
use Narvalo\Web;

// {{{ ActionException

class ActionException extends \Exception { }

// }}}
// {{{ ControllerException

class ControllerException extends \Exception { }

// }}}
// {{{ ViewException

class ViewException extends \Exception { }

// }}}

/*
// {{{ MvcHandler : Narvalo\Web\IHttpHandler

class MvcHandler implements IHttpHandler {
    public static
        /// \var bool
        $DisableMvcResponseHeader = \FALSE;

    private static
        /// \var string
        $_MvcVersion = VERSION,
        /// \var string
        $_MvcVersionHeaderName = 'X-NarvaloMvc-Version';

    private
        /// \var Narvalo\Web\Mvc\ControllerBuilder
        $_controllerBuilder,
        /// \var Narvalo\Web\RequestContext
        $_requestContext;

    public function __construct(Web\RequestContext $_requestContext_) {
        $this->_requestContext = $_requestContext_;
    }

    public function controllerBuilder() {
        if (NULL === $this->_controllerBuilder) {
            $this->_controllerBuilder = ControllerBuilder::Current();
        }

        return $this->_controllerBuilder;
    }

    public function setControllerBuilder(ControllerBuilder $_controllerBuilder_) {
        $this->_controllerBuilder = $_controllerBuilder_;
    }

    public function requestContext() {
        return $this->_requestContext;
    }

    protected function addVersionHeader(Web\HttpContext $_httpContext_) {
        if (!self::$DisableMvcResponseHeader) {
            $_httpContext_->response()
                ->appendHeader(self::$_MvcVersionHeaderName, self::$_MvcVersion);
        }
    }

    public function processRequest(Web\HttpContext $_httpContext_) {
        $this->addVersionHeader($_httpContext_);
        $this->_removeOptionalRoutingParameters();

        // récupère le type de contrôleur
        $controllerName = $this->_requestContext->routeData()->getRequiredString('controller');

        // initialise puis execute le contrôleur
        $factory = $this->controllerBuilder()->controllerFactory;
        $controller = $factory->createController($this->_requestContext, $controllerName);

        if (NULL === $controller) {
            throw new Narvalo\InvalidOperationException();
        }

        $controller->execute($this->_requestContext);
    }

    private function _removeOptionalRoutingParameters() {
        //$rvd = $this->_requestContext->RouteData()->Values();
    }
}

// }}}
// {{{ MvcRouteHandler : Narvalo\Web\IRouteHandler

class MvcRouteHandler implements IRouteHandler {
    public function getHttpHandler(RequestContext $_requestContext_) {
        return new MvcHandler($_requestContext_);
    }
}

// }}}
 */

// {{{ IControllerFactory

interface IControllerFactory {
    public function createController($_controllerName_);
}

// }}}
// {{{ IView

interface IView {
    public function render();
}

// }}}

// {{{ NullView : IView

class NullView implements IView {
    public function render() {
        ;
    }
}

// }}}

// {{{ StaticViewBase : IView

abstract class StaticViewBase implements IView {
    protected abstract function getViewPath();

    public function render() {
        if (\FALSE === (include $this->getViewPath())) {
            throw new ViewException(
                'Unable to include the view: ' . $this->getViewPath());
        }
    }
}

// }}}

// {{{ ViewBase : IView

abstract class ViewBase implements IView {
    protected
        $data = array(),
        $model;

    protected function __construct($_model_) {
        $this->model = $_model_;
    }

    /// \return string
    abstract public function getViewPath();

    /// \throw  ViewException
    /// \return void
    public function render() {
        $factory = Web\PathFactoryBuilder::Current()->getPathFactory();

        // Extract the view's properties into current scope
        $this->data['Model'] = $this->model;
        $this->data['Path']  = $factory;
        $this->data['Html']  = new Web\HtmlHelper($factory);

        \extract($this->data, \EXTR_REFS);

        if (\FALSE === (include $this->getViewPath())) {
            throw new ViewException('Unable to include the view: ' . $this->getViewPath());
        }
    }
}

// }}}
// {{{ PageBase : ViewBase

abstract class PageBase extends ViewBase {
    protected function __construct($_model_) {
        parent::__construct($_model_);
    }

    /// \return void
    public function render() {
        // Cleanup existing buffers
        while (\ob_get_level() > 0) {
            \ob_end_flush();
        }
        // Start buffering
        \ob_start();

        try {
            parent::render();
        } catch (ViewException $e) {
            // Fail with correct error code.
            Web\HttpError::FatalError();
        }

        // Output the result
        \ob_flush();
        // End buffering
        \ob_end_flush();

        exit();
    }
}

// }}}

// {{{ MasterPageBase : PageBase

abstract class MasterPageBase extends PageBase {
    protected function __construct($_model_, ChildViewBase $_child_) {
        parent::__construct($_model_);
        $this->data['Child'] = $_child_;
    }
}

// }}}
// {{{ ChildViewBase : ViewBase

abstract class ChildViewBase extends ViewBase {
    protected $master;

    protected function __construct($_master_, $_model_) {
        $this->master = $_master_;

        parent::__construct($_model_);
    }

    public function render() {
        $this->master->render();
    }

    public function renderChild() {
        parent::render();
    }
}

// }}}

// {{{ BaseController

class BaseController {
    const
        HttpDateFormat = 'D, d M Y H:i:s';
    protected
        $enableClientCache = \TRUE;

    // FIXME Move this elsewhere
    protected function publiclyCache($_duration_ = null) {
        if (\FALSE === $this->enableClientCache) {
            return;
        }

        $time = \time();
        $duration = NULL !== $_duration_ ? $_duration_ : 3600;

        \header('Last-Modified: ' . \gmdate(self::HttpDateFormat, $time) . ' GMT');
        \header('Expires: ' . \gmdate(self::HttpDateFormat, $time + $duration) . ' GMT');
        \header('Pragma: public');
        \header('Cache-Control: maxage=' . $duration);
    }
}

// }}}
// {{{ ControllerContext

class ControllerContext {
    public
        $controllerName,
        $actionName,
        $request;
}

// }}}

// {{{ ApplicationBase

abstract class ApplicationBase {
    const
        ActionKey             = 'action',
        ControllerKey         = 'controller',
        DefaultControllerName = 'home',
        DefaultActionName     = 'index';

    public static
        $Debug           = \FALSE,
        $DebugJavascript = \FALSE,
        $DebugStylesheet = \FALSE;

    private static $_Settings;
    protected $settings;

    protected function __construct(Web\ISettings $_settings_) {
        self::$_Settings = $_settings_;
        $this->settings = $_settings_;
    }

    public static function CurrentSettings() {
        return self::$_Settings;
    }

    abstract public function createController($_controllerName_);

    abstract protected function createErrorView($_status_, $_message_);

    public static function ViewPath() {
        return self::$_Settings->viewRepository()
            . \DIRECTORY_SEPARATOR
            . \join(\DIRECTORY_SEPARATOR, \func_get_args());
    }

    protected static function ResolveContext(array &$_req_) {
        $req = $_req_;

        if (isset($_req_[self::ControllerKey])) {
            $controllerName = $_req_[self::ControllerKey];
            unset($req[self::ControllerKey]);
        } else {
            $controllerName = self::DefaultControllerName;
        }

        if (isset($_req_[self::ActionKey])) {
            $actionName = $_req_[self::ActionKey];
            unset($req[self::ActionKey]);
        } else {
            $actionName = self::DefaultActionName;
        }

        $context = new ControllerContext();
        $context->controllerName = $controllerName;
        $context->actionName = $actionName;
        $context->request = $req;

        return $context;
    }

    protected static function SetupDebugging($_debugLevel_) {
        // We do not want PHP to override the cache options.
        \ini_set('session.cache_limiter', NULL);
        // Document expires after n minutes.
        // http://php.net/session.cache-expire
        //session.cache_expire = 180

        self::$DebugJavascript
            = $_debugLevel_ & Web\DebugLevel::JavaScript;
        self::$DebugStylesheet
            = $_debugLevel_ & Web\DebugLevel::StyleSheet;

        if ($_debugLevel_ & Web\DebugLevel::RunTime) {
            \ini_set('display_startup_errors', 1);
            \ini_set('display_errors',         1);
            \ini_set('track_errors',           0);
            \ini_set('log_errors',             1);

            self::$Debug = \TRUE;
        } else {
            // Turn off all error reporting except hidden one
            \ini_set('display_startup_errors', 0);
            \ini_set('display_errors',         0);
            \ini_set('track_errors',           0);
            \ini_set('log_errors',             1);

            self::$Debug = \FALSE;
        }

        if ($_debugLevel_ & Web\DebugLevel::DataBase) {
            Persistence\DataStore::$Debug = \TRUE;
        }
    }

    protected function invokeAction(ControllerContext $_context_) {
        $actionName     = $_context_->actionName;
        $controllerName = $_context_->controllerName;
        $request        = $_context_->request;

        $controller = $this->createController($controllerName);

        try {
            $refl = new \ReflectionObject($controller);

            if (!$refl->hasMethod($actionName)) {
                throw new ActionException('La page demandée n\'existe pas.');
            }

            return $refl->getMethod($actionName)->invoke($controller, $request);
        } catch (\ReflectionException $e) {
            throw new ActionException('La page demandée n\'existe pas.');
        }
    }

    public function processRequest(array &$_req_) {
        $context = self::ResolveContext($_req_);

        try {
            $view = $this->invokeAction($context);
        } catch (ActionException $ae) {
            $view = $this->createErrorView(
                Web\HttpError::NotFound, $ae->getMessage());
        } catch (BadRequestException $be) {
            $view = $this->createErrorView(
                Web\HttpError::NotFound, $be->getMessage());
        } catch (\Exception $e) {
            $view = $this->createErrorView(
                Web\HttpError::FatalError, $e->getMessage());
        }

        try {
            $view->render();
        } catch (ViewException $ve) {
            Web\HttpError::FatalError();
        }
    }

    public function start($_debugLevel_) {
        self::SetupDebugging($_debugLevel_);

        \ini_set('default_mimetype', 'text/html; charset=utf-8');

        if (NULL !== ($errorLog = $this->settings->errorLog()) && '' !== $errorLog) {
            \ini_set('error_log', $errorLog);
        }

        return $this;
    }
}

// }}}

// =============================================================================

// EOF
