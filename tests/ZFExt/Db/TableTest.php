<?php
use PHPUnit_Framework_TestCase as TestCase;

class ZFExt_Db_TableTest extends TestCase
{
    protected $db;
    protected $cache;

    protected function setUp()
    {
        parent::setUp();

        $this->db = Zend_Db::factory('Pdo_Mysql', array(
            'host'     => DB_HOST,
            'username' => DB_USERNAME,
            'password' => DB_PASSWORD,
            'dbname'   => DB_NAME
        ));

        $this->cache = Zend_Cache::factory('Core', 'APC', array(
            'lifetime' => 300,
            'automatic_serialization' => true,
        ));
    }

    public function testNewInstanceBeingCreatedWithDbAndCachePassedAsArguments()
    {
        $userModel = new Users(array('db' => $this->db, 'cache' => $this->cache));

        $this->assertInstanceOf('ZFExt_Db_Table', $userModel);
        $this->assertInstanceOf('Zend_Db_Adapter_Pdo_Mysql', $userModel->getAdapter());
        $this->assertInstanceOf('Zend_Cache_Core', $userModel->getCache());
    }

    public function testThatCreateCacheIdMethodWillProduceStringContainingAtLeastMethodName()
    {
        $userModel = new Users(array('db' => $this->db, 'cache' => $this->cache));
        $cacheId = $userModel->createCacheId('someFunctionName', array('foo' => 'bar'));

        $this->assertContains('someFunctionName', $cacheId, '');
        $this->assertContains('foo', $cacheId);
        $this->assertContains('bar', $cacheId);
    }

    public function testThatRemoveByPartialIdMethodWillDeleteAtLeastOneRecordFromCache()
    {
        $userModel = new Users(array('db' => $this->db, 'cache' => $this->cache));
        $user_id = 1;
        $user = $userModel->get($user_id);

        $result = $userModel->removeByPartialId("user_id__i_{$user_id}");
        $this->assertTrue((bool) $result);
    }

    public function testThatRemoveByPartialIdMethodWillFailIfThereIsNoCache()
    {
        $userModel = new Users(array('db' => $this->db));
        $user_id = 1;
        $user = $userModel->get($user_id);

        $result = $userModel->removeByPartialId("user_id__i_{$user_id}");
        $this->assertFalse($result);
    }

    public function testThatFetchUserWithIdEqualOneFromDbWithCacheDisabled()
    {
        $userModel = new Users(array('db' => $this->db));
        $user_id = 1;
        $user = $userModel->get($user_id, false);

        $this->assertNotEmpty($user);
    }

    public function testThatFetchUserWithIdEqualOneFromDbWithCacheEnabled()
    {
        $userModel = new Users(array('db' => $this->db, 'cache' => $this->cache));
        $user_id = 1;
        $user = $userModel->get($user_id);

        $this->assertNotEmpty($user);

        foreach ($userModel->getCache()->getIds() as $cacheId)
        {
            // strpos will return boolean false if don't find Users__get
            // and user_id__i_$user_id substrings in $cacheId
            if (strpos($cacheId, 'Users__get') !== false &&
                strpos($cacheId, "user_id__i_{$user_id}") !== false
            ) {
                $this->assertEquals($user, $userModel->getCache()->load($cacheId));
                break;
            }
        }
    }

    public function testThatFetchAllActiveUsersFromDbWithCacheDisabled()
    {
        $userModel = new Users(array('db' => $this->db, 'cache' => $this->cache));
        $users = $userModel->getAll(false);

        $this->assertNotEmpty($users);
    }

    public function testThatFetchingDataFromDbWillStoreItInCacheForLaterUseUnderCacheIdContainingAtLeastMethodName()
    {
        $userModel = new Users(array('db' => $this->db, 'cache' => $this->cache));
        $users = $userModel->getAll();

        foreach ($userModel->getCache()->getIds() as $cacheId)
        {
            // strpos will return boolean false if don't find Users__getAll substring in $cacheId
            if (strpos($cacheId, 'Users__getAll') !== false) {
                $this->assertEquals($users, $userModel->getCache()->load($cacheId));
                break;
            }
        }
    }
}

/**
 * Class Users implements abstract class ZFExt_Db_Table, the
 * one I wish to test.
 */
class Users extends ZFExt_Db_Table
{
    protected $_name = 'users';
    protected $_primary = 'user_id';

    /**
     * @param $id
     * @param bool $useCache
     * @return false|mixed
     */
    public function get($id, $useCache = true)
    {
        $select = $this->select()
            ->where('user_id = :user_id');

        return $this->loadOne($select, array('user_id' => $id), null, $useCache, __METHOD__);
    }

    /**
     * @param bool $useCache
     * @return false|mixed
     */
    public function getAll($useCache = true)
    {
        $select = $this->select()
            ->where('is_active = :is_active');

        return $this->loadAll($select, array('is_active' => 1), null, $useCache, __METHOD__);
    }
}