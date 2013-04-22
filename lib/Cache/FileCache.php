<?php

require_once 'Cache.php';

/**
 * This class extends Cache and offers a cache system driven by a master file
 *
 * With this class, cache validity is only dependent of a given file. Cache files
 * are valid only if they are older than the master file. It's a perfect way for
 * caching templates results (if the template file is newer than the cache, cache
 * must be rebuild...) or for config classes...
 */
class FileCache extends Cache {
    /**
     * Complete path of the file used for controlling the cache lifetime
     */
    private $_masterFile = '';

    /**
     * Masterfile mtime
     */
    private $_masterFile_mtime = 0;

    /**
     * $options is an assoc. To have a look at availables options,
     * see the constructor of the Cache class in 'Cache.php'
     *
     * Comparing to Cache constructor, there is another option :
     * $options = array(
     *     (...) see Cache constructor
     *     'masterFile' => complete path of the file used for controlling the cache lifetime(string)
     * );
     *
     * @param array $options options
     * @access public
     */
    public function __construct($options = array(NULL)) {
        $options['lifetime'] = 0;
        parent::__construct($options);
        if (isset($options['masterFile'])) {
            $this->_masterFile = $options['masterFile'];
        } else {
            throw new CacheException('FileCache : masterFile option must be set !');
        }
        if (!($this->_masterFile_mtime = @filemtime($this->_masterFile))) {
            throw new CacheException(
                'FileCache : Unable to read masterFile : '.$this->_masterFile); //, -3);
        }
    }

    /**
     * Test if a cache is available and (if yes) return it
     *
     * @param string $id cache id
     * @param string $group name of the cache group
     * @param boolean $doNotTestCacheValidity if set to true, the cache validity won't be tested
     * @return string data of the cache (else : FALSE)
     * @access public
     */
    public function get($id, $group = 'default', $doNotTestCacheValidity = FALSE) {
        if ($data = parent::get($id, $group, true)) {
            if ($filemtime = $this->lastModified()) {
                if ($filemtime > $this->_masterFile_mtime) {
                    return $data;
                }
            }
        }
        return FALSE;
    }
}

// EOF
