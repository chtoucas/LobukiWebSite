<?php

require_once 'Cache.php';

/**
 * This class extends Cache and uses output buffering to get the data to cache.
 */
class OutputCache extends Cache {
    /**
     * Constructor
     *
     * $options is an assoc. To have a look at availables options,
     * see the constructor of the Cache class in 'Cache.php'
     *
     * @param array $options options
     * @access public
     */
    public function __construct($options) {
        parent::__construct($options);
    }

    /**
     * Start the cache
     *
     * @param string $id cache id
     * @param string $group name of the cache group
     * @param boolean $doNotTestCacheValidity if set to true, the cache validity won't be tested
     * @return boolean true if the cache is hit (FALSE else)
     * @access public
     */
    public function start($id, $group = 'default', $doNotTestCacheValidity = FALSE) {
        $data = $this->get($id, $group, $doNotTestCacheValidity);
        if ($data !== FALSE) {
            echo($data);
            return true;
        }
        ob_start();
        ob_implicit_flush(FALSE);
        return FALSE;
    }

    /**
     * Stop the cache
     *
     * @access public
     */
    public function end() {
        $data = ob_get_contents();
        ob_end_clean();
        $this->save($data, $this->_id, $this->_group);
        echo($data);
    }

}

// EOF
