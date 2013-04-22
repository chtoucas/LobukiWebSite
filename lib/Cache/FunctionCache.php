<?php

require_once 'Cache.php';

/**
 * This class extends Cache and can be used to cache the result and output of functions/methods
 *
 * This class is completly inspired from Sebastian Bergmann's
 * PEAR/Cache_Function class. This is only an adaptation to
 * Cache
 *
 * There are some examples in the 'docs/examples' file
 * Technical choices are described in the 'docs/technical' file
 *
 * @package Cache
 * @version $Id: Function.php 225008 2006-12-14 12:59:43Z cweiske $
 * @author Sebastian BERGMANN <sb@sebastian-bergmann.de>
 * @author Fabien MARTY <fab@php.net>
 */
class FunctionCache extends Cache {
    /**
     * Default cache group for function caching
     */
    private $_defaultGroup = 'FunctionCache';

    /**
     * Don't cache the method call when its output contains the string "NOCACHE"
     *
     * if set to true, the output of the method will never be displayed (because the output is used
     * to control the cache)
     */
    private $_dontCacheWhenTheOutputContainsNOCACHE = FALSE;

    /**
     * Don't cache the method call when its result is FALSE
     */
    private $_dontCacheWhenTheResultIsFalse = FALSE;

    /**
     * Don't cache the method call when its result is null
     */
    private $_dontCacheWhenTheResultIsNull = FALSE;

    /**
     * Debug the FunctionCache caching process
     */
    private $_debugCacheLiteFunction = FALSE;

    /**
     * Constructor
     *
     * $options is an assoc. To have a look at availables options,
     * see the constructor of the Cache class in 'Cache.php'
     *
     * Comparing to Cache constructor, there is another option :
     * $options = array(
     *     (...) see Cache constructor
     *     'debugCacheLiteFunction' => (bool) debug the caching process,
     *     'defaultGroup' => default cache group for function caching (string),
     *     'dontCacheWhenTheOutputContainsNOCACHE' => (bool) don't cache when the function output contains "NOCACHE",
     *     'dontCacheWhenTheResultIsFalse' => (bool) don't cache when the function result is FALSE,
     *     'dontCacheWhenTheResultIsNull' => (bool don't cache when the function result is null
     * );
     */
    public function __construct($options = array(NULL)) {
        $availableOptions = array('debugCacheLiteFunction', 'defaultGroup', 'dontCacheWhenTheOutputContainsNOCACHE', 'dontCacheWhenTheResultIsFalse', 'dontCacheWhenTheResultIsNull');
        while (list($name, $value) = each($options)) {
            if (in_array($name, $availableOptions)) {
                $property = '_'.$name;
                $this->$property = $value;
            }
        }
        reset($options);
        parent::__construct($options);
    }

    /**
     * Calls a cacheable function or method (or not if there is already a cache for it)
     *
     * Arguments of this method are read with func_get_args. So it doesn't appear
     * in the function definition. Synopsis :
     * call('functionName', $arg1, $arg2, ...)
     * (arg1, arg2... are arguments of 'functionName')
     */
    public function call() {
        $arguments = func_get_args();
        $id = $this->_makeId($arguments);
        $data = $this->get($id, $this->_defaultGroup);
        if ($data !== FALSE) {
            if ($this->_debugCacheLiteFunction) {
                echo "Cache hit !\n";
            }
            $array = unserialize($data);
            $output = $array['output'];
            $result = $array['result'];
        } else {
            if ($this->_debugCacheLiteFunction) {
                echo "Cache missed !\n";
            }
            ob_start();
            ob_implicit_flush(FALSE);
            $target = array_shift($arguments);
            if (is_array($target)) {
                // in this case, $target is for example array($obj, 'method')
                $object = $target[0];
                $method = $target[1];
                $result = call_user_func_array(array(&$object, $method), $arguments);
            } else {
                if (strstr($target, '::')) { // classname::staticMethod
                    list($class, $method) = explode('::', $target);
                    $result = call_user_func_array(array($class, $method), $arguments);
                } else if (strstr($target, '->')) { // object->method
                    // use a stupid name ($objet_123456789 because) of problems where the object
                    // name is the same as this var name
                    list($object_123456789, $method) = explode('->', $target);
                    global $$object_123456789;
                    $result = call_user_func_array(array($$object_123456789, $method), $arguments);
                } else { // function
                    $result = call_user_func_array($target, $arguments);
                }
            }
            $output = ob_get_contents();
            ob_end_clean();
            if ($this->_dontCacheWhenTheResultIsFalse) {
                if ((is_bool($result)) && (!($result))) {
                    echo($output);
                    return $result;
                }
            }
            if ($this->_dontCacheWhenTheResultIsNull) {
                if (is_null($result)) {
                    echo($output);
                    return $result;
                }
            }
            if ($this->_dontCacheWhenTheOutputContainsNOCACHE) {
                if (strpos($output, 'NOCACHE') > -1) {
                    return $result;
                }
            }
            $array['output'] = $output;
            $array['result'] = $result;
            $this->save(serialize($array), $id, $this->_defaultGroup);
        }
        echo($output);
        return $result;
    }

    /**
     * Drop a cache file
     *
     * Arguments of this method are read with func_get_args. So it doesn't appear
     * in the function definition. Synopsis :
     * remove('functionName', $arg1, $arg2, ...)
     * (arg1, arg2... are arguments of 'functionName')
     */
    public function drop() {
        $id = $this->_makeId(func_get_args());
        return $this->remove($id, $this->_defaultGroup);
    }

    /**
     * Make an id for the cache
     *
     * @var array result of func_get_args for the call() or the remove() method
     * @return string id
     */
    private function _makeId($arguments) {
        $id = serialize($arguments); // Generate a cache id
        if (!$this->fileNameProtection) {
            $id = md5($id);
            // if fileNameProtection is set to FALSE, then the id has to be hashed
            // because it's a very bad file name in most cases
        }
        return $id;
    }
}

// EOF
