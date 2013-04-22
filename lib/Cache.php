<?php

class CacheException extends Exception { }

/**
 * Fast, light and safe Cache Class
 *
 * Cache is a fast, light and safe cache system. It's optimized
 * for file containers. It is fast and safe (because it uses file
 * locking and/or anti-corruption tests).
 *
 * There are some examples in the 'docs/examples' file
 * Technical choices are described in the 'docs/technical' file
 *
 * Memory Caching is from an original idea of
 * Mike BENOIT <ipso@snappymail.ca>
 */
class Cache {
    /**
     * File Name protection
     *
     * if set to TRUE, you can use any cache id or group name
     * if set to FALSE, it can be faster but cache ids and group names
     * will be used directly in cache file names so be carefull with
     * special characters...
     */
    protected $fileNameProtection = TRUE;

    /**
     * Directory where to put the cache files
     * (make sure to add a trailing slash)
     */
    private $_cacheDir = '/tmp/';

    /**
     * Enable / disable caching
     *
     * (can be very usefull for the debug of cached scripts)
     */
    private $_caching = TRUE;

    /**
     * Cache lifetime (in seconds)
     *
     * If null, the cache is valid forever.
     */
    private $_lifeTime = 3600;

    /**
     * Enable / disable fileLocking
     *
     * (can avoid cache corruption under bad circumstances)
     */
    private $_fileLocking = TRUE;

    /**
     * Timestamp of the last valid cache
     */
    private $_refreshTime;

    /**
     * File name (with path)
     */
    private $_file;

    /**
     * File name (without path)
     */
    private $_fileName;

    /**
     * Enable / disable write control (the cache is read just after writing to detect corrupt entries)
     *
     * Enable write control will lightly slow the cache writing but not the cache reading
     * Write control can detect some corrupt cache files but maybe it's not a perfect control
     */
    private $_writeControl = TRUE;

    /**
     * Enable / disable read control
     *
     * If enabled, a control key is embeded in cache file and this key is compared with the one
     * calculated after the reading.
     */
    private $_readControl = TRUE;

    /**
     * Type of read control (only if read control is enabled)
     *
     * Available values are :
     * 'md5' for a md5 hash control (best but slowest)
     * 'crc32' for a crc32 hash control (lightly less safe but faster, better choice)
     * 'strlen' for a length only test (fastest)
     */
    private $_readControlType = 'crc32';

    /**
     * Current cache id
     */
    private $_id;

    /**
     * Current cache group
     */
    private $_group;

    /**
     * Enable / Disable "Memory Caching"
     *
     * NB : There is no lifetime for memory caching ! 
     */
    private $_memoryCaching = FALSE;

    /**
     * Enable / Disable "Only Memory Caching"
     * (be carefull, memory caching is "beta quality")
     */
    private $_onlyMemoryCaching = FALSE;

    /**
     * Memory caching array
     */
    private $_memoryCachingArray = array();

    /**
     * Memory caching counter
     */
    private $_memoryCachingCounter = 0;

    /**
     * Memory caching limit
     */
    private $_memoryCachingLimit = 1000;

    /**
     * Enable / disable automatic serialization
     *
     * it can be used to save directly datas which aren't strings
     * (but it's slower)
     */
    private $_automaticSerialization = FALSE;

    /**
     * Disable / Tune the automatic cleaning process
     *
     * The automatic cleaning process destroy too old (for the given life time)
     * cache files when a new cache file is written.
     * 0               => no automatic cache cleaning
     * 1               => systematic cache cleaning
     * x (integer) > 1 => automatic cleaning randomly 1 times on x cache write
     */
    private $_automaticCleaningFactor = 0;

    /**
     * Nested directory level
     *
     * Set the hashed directory structure level. 0 means "no hashed directory 
     * structure", 1 means "one level of directory", 2 means "two levels"... 
     * This option can speed up Cache only when you have many thousands of 
     * cache file. Only specific benchs can help you to choose the perfect value 
     * for you. Maybe, 1 or 2 is a good start.
     */
    private $_hashedDirectoryLevel = 0;

    /**
     * Umask for hashed directory structure
     */
    private $_hashedDirectoryUmask = 0700;

    /**
     * $options is an assoc. Available options are :
     * $options = array(
     *     'cacheDir' => directory where to put the cache files (string),
     *     'caching' => enable / disable caching (boolean),
     *     'lifeTime' => cache lifetime in seconds (int),
     *     'fileLocking' => enable / disable fileLocking (boolean),
     *     'writeControl' => enable / disable write control (boolean),
     *     'readControl' => enable / disable read control (boolean),
     *     'readControlType' => type of read control 'crc32', 'md5', 'strlen' (string),
     *     'pearErrorMode' => pear error mode (when raiseError is called) (cf PEAR doc) (int),
     *     'memoryCaching' => enable / disable memory caching (boolean),
     *     'onlyMemoryCaching' => enable / disable only memory caching (boolean),
     *     'memoryCachingLimit' => max nbr of records to store into memory caching (int),
     *     'fileNameProtection' => enable / disable automatic file name protection (boolean),
     *     'automaticSerialization' => enable / disable automatic serialization (boolean),
     *     'automaticCleaningFactor' => distable / tune automatic cleaning process (int),
     *     'hashedDirectoryLevel' => level of the hashed directory system (int),
     *     'hashedDirectoryUmask' => umask for hashed directory structure (int),
     *     'errorHandlingAPIBreak' => API break for better error handling ? (boolean)
     * );
     *
     * If sys_get_temp_dir() is available and the
     * 'cacheDir' option is not provided in the
     * constructor options array its output is used
     * to determine the suitable temporary directory.
     *
     * @see http://de.php.net/sys_get_temp_dir
     * @see http://pear.php.net/bugs/bug.php?id=18328
     */
    public function __construct($options = array(NULL)) {
        foreach($options as $key => $value) {
            $this->setOption($key, $value);
        }
        if (!isset($options['cacheDir']) && function_exists('sys_get_temp_dir')) {
            $this->setOption('cacheDir', sys_get_temp_dir() . DIRECTORY_SEPARATOR);
        }
    }

    /**
     * Generic way to set a Cache option
     *
     * see Cache constructor for available options
     */
    public function setOption($name, $value) {
        $availableOptions = array('errorHandlingAPIBreak', 'hashedDirectoryUmask', 'hashedDirectoryLevel', 'automaticCleaningFactor', 'automaticSerialization', 'fileNameProtection', 'memoryCaching', 'onlyMemoryCaching', 'memoryCachingLimit', 'cacheDir', 'caching', 'lifeTime', 'fileLocking', 'writeControl', 'readControl', 'readControlType', 'pearErrorMode');
        if (in_array($name, $availableOptions)) {
            $property = '_'.$name;
            $this->$property = $value;
        }
    }

    /**
     * Test if a cache is available and (if yes) return it
     *
     * @param string $id cache id
     * @param string $group name of the cache group
     * @param boolean $doNotTestCacheValidity if set to TRUE, the cache validity won't be tested
     * @return string data of the cache (else : FALSE)
     */
    public function get($id, $group = 'default', $doNotTestCacheValidity = FALSE) {
        $this->_id = $id;
        $this->_group = $group;
        $data = FALSE;

        if ($this->_caching) {
            $this->_setRefreshTime();
            $this->_setFileName($id, $group);
            clearstatcache();

            if ($this->_memoryCaching) {
                if (isset($this->_memoryCachingArray[$this->_file])) {
                    if ($this->_automaticSerialization) {
                        return unserialize($this->_memoryCachingArray[$this->_file]);
                    }
                    return $this->_memoryCachingArray[$this->_file];
                }
                if ($this->_onlyMemoryCaching) {
                    return FALSE;
                }
            }

            if ($doNotTestCacheValidity || is_null($this->_refreshTime)) {
                if (file_exists($this->_file)) {
                    $data = $this->_read();
                }
            } else {
                if ((file_exists($this->_file)) && (@filemtime($this->_file) > $this->_refreshTime)) {
                    $data = $this->_read();
                }
            }

            if ($data and $this->_memoryCaching) {
                $this->_memoryCacheAdd($data);
            }

            if ($this->_automaticSerialization and is_string($data)) {
                $data = unserialize($data);
            }

            return $data;
        }
        return FALSE;
    }

    /**
     * Save some data in a cache file
     *
     * @param string $data data to put in cache (can be another type than strings if automaticSerialization is on)
     * @param string $id cache id
     * @param string $group name of the cache group
     * @return boolean TRUE if no problem (else : FALSE or a PEAR_Error object)
     */
    public function save($data, $id = NULL, $group = 'default') {
        if ($this->_caching) {
            if ($this->_automaticSerialization) {
                $data = serialize($data);
            }
            if (isset($id)) {
                $this->_setFileName($id, $group);
            }
            if ($this->_memoryCaching) {
                $this->_memoryCacheAdd($data);
                if ($this->_onlyMemoryCaching) {
                    return TRUE;
                }
            }
            if ($this->_automaticCleaningFactor>0 && ($this->_automaticCleaningFactor==1 || mt_rand(1, $this->_automaticCleaningFactor)==1)) {
                $this->clean(FALSE, 'old');
            }
            if ($this->_writeControl) {
                $res = $this->_writeAndControl($data);

                if (is_bool($res)) {
                    if ($res) {
                        return TRUE;
                    }

                    // if $res if FALSE, we need to invalidate the cache
                    @touch($this->_file, time() - 2*abs($this->_lifeTime));

                    return FALSE;
                }
            } else {
                $res = $this->_write($data);
            }
            if (is_object($res)) {
                // $res is a PEAR_Error object
                // XXX now we throw an Exception
                //if (!($this->_errorHandlingAPIBreak)) {
                //return FALSE; // we return FALSE (old API)
                //}
            }
            return $res;
        }
        return FALSE;
    }

    /**
     * Remove a cache file
     *
     * @param string $id cache id
     * @param string $group name of the cache group
     * @param boolean $checkbeforeunlink check if file exists before removing it
     * @return boolean TRUE if no problem
     */
    public function remove($id, $group = 'default', $checkbeforeunlink = FALSE) {
        $this->_setFileName($id, $group);
        if ($this->_memoryCaching) {
            if (isset($this->_memoryCachingArray[$this->_file])) {
                unset($this->_memoryCachingArray[$this->_file]);
                $this->_memoryCachingCounter = $this->_memoryCachingCounter - 1;
            }
            if ($this->_onlyMemoryCaching) {
                return TRUE;
            }
        }
        if ( $checkbeforeunlink ) {
            if (!file_exists($this->_file)) return TRUE;
        }
        return $this->_unlink($this->_file);
    }

    /**
     * Clean the cache
     *
     * if no group is specified all cache files will be destroyed
     * else only cache files of the specified group will be destroyed
     *
     * @param string $group name of the cache group
     * @param string $mode flush cache mode : 'old', 'ingroup', 'notingroup', 
     *                                        'callback_myFunction'
     * @return boolean TRUE if no problem
     */
    public function clean($group = FALSE, $mode = 'ingroup') {
        return $this->_cleanDir($this->_cacheDir, $group, $mode);
    }

    /**
     * Set a new life time
     *
     * @param int $newLifeTime new life time (in seconds)
     */
    public function setLifeTime($newLifeTime) {
        $this->_lifeTime = $newLifeTime;
        $this->_setRefreshTime();
    }

    /**
     * Save the state of the caching memory array into a cache file cache
     *
     * @param string $id cache id
     * @param string $group name of the cache group
     */
    public function saveMemoryCachingState($id, $group = 'default') {
        if ($this->_caching) {
            $array = array(
                'counter' => $this->_memoryCachingCounter,
                'array' => $this->_memoryCachingArray
            );
            $data = serialize($array);
            $this->save($data, $id, $group);
        }
    }

    /**
     * Load the state of the caching memory array from a given cache file cache
     *
     * @param string $id cache id
     * @param string $group name of the cache group
     * @param boolean $doNotTestCacheValidity if set to TRUE, the cache validity won't be tested
     */
    public function getMemoryCachingState($id, $group = 'default', $doNotTestCacheValidity = FALSE) {
        if ($this->_caching) {
            if ($data = $this->get($id, $group, $doNotTestCacheValidity)) {
                $array = unserialize($data);
                $this->_memoryCachingCounter = $array['counter'];
                $this->_memoryCachingArray = $array['array'];
            }
        }
    }

    /**
     * Return the cache last modification time
     *
     * BE CAREFUL : THIS METHOD IS FOR HACKING ONLY !
     *
     * @return int last modification time
     */
    public function lastModified() {
        return @filemtime($this->_file);
    }

    /**
     * Extend the life of a valid cache file
     *
     * see http://pear.php.net/bugs/bug.php?id=6681
     */
    public function extendLife() {
        @touch($this->_file);
    }

    /**
     * Compute & set the refresh time
     */
    private function _setRefreshTime() {
        if (is_null($this->_lifeTime)) {
            $this->_refreshTime = null;
        } else {
            $this->_refreshTime = time() - $this->_lifeTime;
        }
    }

    /**
     * Remove a file
     *
     * @param string $file complete file path and name
     * @return boolean TRUE if no problem
     */
    private function _unlink($file) {
        if (!@unlink($file)) {
            throw new CacheException('Cache : Unable to remove cache !'); //, -3);
        }
        return TRUE;
    }

    /**
     * Recursive function for cleaning cache file in the given directory
     *
     * @param string $dir directory complete path (with a trailing slash)
     * @param string $group name of the cache group
     * @param string $mode flush cache mode : 'old', 'ingroup', 'notingroup',
     'callback_myFunction'
     * @return boolean TRUE if no problem
     */
    private function _cleanDir($dir, $group = FALSE, $mode = 'ingroup') {
        if ($this->fileNameProtection) {
            $motif = ($group) ? 'phpcache_'.md5($group).'_' : 'phpcache_';
        } else {
            $motif = ($group) ? 'phpcache_'.$group.'_' : 'phpcache_';
        }
        if ($this->_memoryCaching) {
            foreach($this->_memoryCachingArray as $key => $v) {
                if (strpos($key, $motif) !== FALSE) {
                    unset($this->_memoryCachingArray[$key]);
                    $this->_memoryCachingCounter = $this->_memoryCachingCounter - 1;
                }
            }
            if ($this->_onlyMemoryCaching) {
                return TRUE;
            }
        }
        if (!($dh = opendir($dir))) {
            throw new CacheException('Cache : Unable to open cache directory !'); //, -4);
        }
        $result = TRUE;
        while ($file = readdir($dh)) {
            if (($file != '.') && ($file != '..')) {
                if (substr($file, 0, 6)=='phpcache_') {
                    $file2 = $dir . $file;
                    if (is_file($file2)) {
                        switch (substr($mode, 0, 9)) {
                        case 'old':
                            // files older than lifeTime get deleted from cache
                            if (!is_null($this->_lifeTime)) {
                                if ((time() - @filemtime($file2)) > $this->_lifeTime) {
                                    $result = ($result and ($this->_unlink($file2)));
                                }
                            }
                            break;
                        case 'notingrou':
                            if (strpos($file2, $motif) === FALSE) {
                                $result = ($result and ($this->_unlink($file2)));
                            }
                            break;
                        case 'callback_':
                            $func = substr($mode, 9, strlen($mode) - 9);
                            if ($func($file2, $group)) {
                                $result = ($result and ($this->_unlink($file2)));
                            }
                            break;
                        case 'ingroup':
                        default:
                            if (strpos($file2, $motif) !== FALSE) {
                                $result = ($result and ($this->_unlink($file2)));
                            }
                            break;
                        }
                    }
                    if ((is_dir($file2)) and ($this->_hashedDirectoryLevel>0)) {
                        $result = ($result and ($this->_cleanDir($file2 . '/', $group, $mode)));
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Add some date in the memory caching array
     *
     * @param string $data data to cache
     */
    private function _memoryCacheAdd($data) {
        $this->_memoryCachingArray[$this->_file] = $data;
        if ($this->_memoryCachingCounter >= $this->_memoryCachingLimit) {
            list($key, ) = each($this->_memoryCachingArray);
            unset($this->_memoryCachingArray[$key]);
        } else {
            $this->_memoryCachingCounter = $this->_memoryCachingCounter + 1;
        }
    }

    /**
     * Make a file name (with path)
     *
     * @param string $id cache id
     * @param string $group name of the group
     */
    private function _setFileName($id, $group) {
        if ($this->fileNameProtection) {
            $suffix = 'phpcache_' . md5($group) . '_' . md5($id);
        } else {
            $suffix = 'phpcache_' . $group . '_' . $id;
        }

        $root = $this->_cacheDir;

        if ($this->_hashedDirectoryLevel > 0) {
            $hash = md5($suffix);

            for ($i = 0; $i < $this->_hashedDirectoryLevel; $i++) {
                $root = $root . 'phpcache_' . substr($hash, 0, $i + 1) . '/';
            }
        }

        $this->_fileName = $suffix;
        $this->_file = $root . $suffix;
    }

    /**
     * Read the cache file and return the content
     *
     * @return string content of the cache file (else : FALSE or a PEAR_Error object)
     */
    private function _read() {
        $fp = @fopen($this->_file, 'rb');

        if ($this->_fileLocking) {
            @flock($fp, LOCK_SH);
        }

        if ($fp) {
            clearstatcache();

            $length = @filesize($this->_file);
            $mqr = get_magic_quotes_runtime();

            if ($mqr) {
                set_magic_quotes_runtime(0);
            }

            if ($this->_readControl) {
                $hashControl = @fread($fp, 32);
                $length = $length - 32;
            }

            if ($length) {
                $data = @fread($fp, $length);
            } else {
                $data = '';
            }

            if ($mqr) {
                set_magic_quotes_runtime($mqr);
            }

            if ($this->_fileLocking) {
                @flock($fp, LOCK_UN);
            }

            @fclose($fp);

            if ($this->_readControl) {
                $hashData = $this->_hash($data, $this->_readControlType);

                if ($hashData != $hashControl) {
                    if (!(is_null($this->_lifeTime))) {
                        @touch($this->_file, time() - 2*abs($this->_lifeTime)); 
                    } else {
                        @unlink($this->_file);
                    }

                    return FALSE;
                }
            }

            return $data;
        }
        throw new CacheException('Cache : Unable to read cache !'); //, -2);
    }

    /**
     * Write the given data in the cache file
     *
     * @param string $data data to put in cache
     * @return boolean TRUE if ok (a PEAR_Error object else)
     */
    private function _write($data) {
        if ($this->_hashedDirectoryLevel > 0) {
            $hash = md5($this->_fileName);
            $root = $this->_cacheDir;

            for ($i=0 ; $i<$this->_hashedDirectoryLevel ; $i++) {
                $root = $root . 'phpcache_' . substr($hash, 0, $i + 1) . '/';
                if (!(@is_dir($root))) {
                    @mkdir($root, $this->_hashedDirectoryUmask);
                }
            }
        }
        $fp = @fopen($this->_file, "wb");

        if ($fp) {
            if ($this->_fileLocking) {
                @flock($fp, LOCK_EX);
            }
            if ($this->_readControl) {
                @fwrite($fp, $this->_hash($data, $this->_readControlType), 32);
            }
            $mqr = get_magic_quotes_runtime();
            if ($mqr) {
                set_magic_quotes_runtime(0);
            }
            @fwrite($fp, $data);
            if ($mqr) {
                set_magic_quotes_runtime($mqr);
            }
            if ($this->_fileLocking) {
                @flock($fp, LOCK_UN);
            }
            @fclose($fp);

            return TRUE;
        }

        throw new CacheException('Cache : Unable to write cache file : '.$this->_file); //, -1);
    }

    /**
     * Write the given data in the cache file and control it just after to avoir corrupted cache entries
     *
     * @param string $data data to put in cache
     * @return boolean TRUE if the test is ok (else : FALSE or a PEAR_Error object)
     */
    private function _writeAndControl($data) {
        $result = $this->_write($data);
        if (is_object($result)) {
            return $result; # We return the PEAR_Error object
        }
        $dataRead = $this->_read();
        if (is_object($dataRead)) {
            return $dataRead; # We return the PEAR_Error object
        }
        if ((is_bool($dataRead)) && (!$dataRead)) {
            return FALSE;
        }
        return ($dataRead==$data);
    }

    /**
     * Make a control key with the string containing datas
     *
     * @param string $data data
     * @param string $controlType type of control 'md5', 'crc32' or 'strlen'
     * @return string control key
     */
    private function _hash($data, $controlType) {
        switch ($controlType) {
        case 'md5':
            return md5($data);
        case 'crc32':
            return sprintf('% 32d', crc32($data));
        case 'strlen':
            return sprintf('% 32d', strlen($data));
        default:
            throw new CacheException('Unknown controlType ! (available values are only \'md5\', \'crc32\', \'strlen\')'); //, -5);
        }
    }
}

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
class MethodCache extends Cache {
    /**
     * Default cache group for function caching
     */
    private $_defaultGroup = 'MethodCache';

    /**
     * Constructor
     *
     * $options is an assoc. To have a look at availables options,
     * see the constructor of the Cache class in 'Cache.php'
     *
     * Comparing to Cache constructor, there is another option :
     * $options = array(
     *     (...) see Cache constructor
     *     'defaultGroup' => default cache group for function caching (string),
     *     'dontCacheWhenTheOutputContainsNOCACHE' => (bool) don't cache when the function output contains "NOCACHE",
     *     'dontCacheWhenTheResultIsFalse' => (bool) don't cache when the function result is FALSE,
     *     'dontCacheWhenTheResultIsNull' => (bool don't cache when the function result is null
     * );
     */
    public function __construct($options = array(NULL)) {
        $availableOptions = array('defaultGroup');
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
    public function invokeMethod($_obj_, $_method_) {
        $args = func_get_args();
        $uniqid = $this->_makeId($args);
        $data = $this->get($uniqid, $this->_defaultGroup);

        if (FALSE !== $data) {
            $array = unserialize($data);
            $output = $array['output'];
            $result = $array['result'];
        } else {
            ob_start();
            ob_implicit_flush(FALSE);

            $params = array_slice($args, 2);

            $result = call_user_func_array(array(&$_obj_, $_method_), $params);
            $output = ob_get_contents();

            ob_end_clean();

            $array['output'] = $output;
            $array['result'] = $result;

            $this->save(serialize($array), $uniqid, $this->_defaultGroup);
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
        $uniqid = $this->_makeId(func_get_args());
        return $this->remove($uniqid, $this->_defaultGroup);
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
