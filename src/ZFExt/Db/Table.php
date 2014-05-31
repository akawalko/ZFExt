<?php
abstract class ZFExt_Db_Table extends Zend_Db_Table_Abstract
{
    const CACHE = 'cache';

    /**
     * @var Zend_Cache_Core
     */
    protected $_cache;

    /**
     * @param Zend_Cache_Core
     * @return ZFExt_Db_Table
     */
    public function setCache($cache)
    {
        if (is_object($cache)) {
            $this->_cache = $cache;
        }
        return $this;
    }

    /**
     * @return Zend_Cache_Core
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * @param array $options
     * @return ZFExt_Db_Table
     */
    public function setOptions(array $options)
    {
        if (isset($options[self::CACHE])) {
            $this->setCache($options[self::CACHE]);
        }
        return parent::setOptions($options);
    }

    /**
     * @param   string      $methodName
     * @param   array       $methodArguments
     * @return string
     */
    public function createCacheId($methodName, $methodArguments = array())
    {
        $cacheId = str_replace(':', '_', $methodName);

        if (!empty($methodArguments)) {
            $cacheId .= '__' . preg_replace('#[^a-zA-Z0-9_]#', '_', serialize($methodArguments));
        }

        return trim($cacheId, '_');
    }

    /**
     * @param $id
     * @return bool|int
     */
    public function removeByPartialId($id)
    {
        if (is_object($this->_cache))
        {
            $counter = 0;
            $cacheIds = $this->_cache->getIds();

            foreach ($cacheIds as $cid)
            {
                if (strpos($cid, $id) !== false) {
                    $this->_cache->remove($cid);
                    $counter++;
                }
            }
            return $counter;
        }
        return false;
    }

    /**
     * @param string|\Zend_Db_Select    $sql
     * @param array                     $bind
     * @param null|mixed                $fetchMode
     * @param bool                      $useCache
     * @param string                    $calleeMethodName
     * @return false|mixed
     */
    public function loadOne($sql, $bind = array(), $fetchMode = null, $useCache = true, $calleeMethodName = '')
    {
        return $this->_loadFromCacheIfPossible('fetchRow', func_get_args());
    }

    /**
     * @param string|\Zend_Db_Select    $sql
     * @param array                     $bind
     * @param null|mixed                $fetchMode
     * @param bool                      $useCache
     * @param string                    $calleeMethodName
     * @return false|mixed
     */
    public function loadAll($sql, $bind = array(), $fetchMode = null, $useCache = true, $calleeMethodName = '')
    {
        return $this->_loadFromCacheIfPossible('fetchAll', func_get_args());
    }

    /**
     * @param $fetchMethod
     * @param array $args
     * @return false|mixed
     */
    protected function _loadFromCacheIfPossible($fetchMethod, array $args)
    {
        $sql = $args[0];
        $bind = $args[1];
        $fetchMode = $args[2];
        $useCache = $args[3];
        $calleeMethodName = $args[4];

        if ($useCache && is_object($this->_cache))
        {
            $cacheId = $this->createCacheId($calleeMethodName, $bind);

            if (($results = $this->_cache->load($cacheId)) === false)
            {
                $results = call_user_func(array($this->_db, $fetchMethod), $sql, $bind, $fetchMode);
                $this->_cache->save($results, $cacheId);
            }

            return $results;
        }

        return call_user_func(array($this->_db, $fetchMethod), $sql, $bind, $fetchMode);
    }
}