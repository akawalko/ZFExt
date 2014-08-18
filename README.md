ZFExt
=====
ZFExt is a library extending components of the Zend Framework 1.12. I have several projects build on top of ZF1, so I decided to share my tips and tricks with you.

## ZFExt_Db_Table
This class is derived from Zend_Db_Table. It provide clean, almost transparent cache layer. 
### Usage
First we have to create class that inherits from ZFExt_Db_Table. Here we declare two standard methods, **get()** & **getAll()**, which will fetch record from database table called **users** by ID and fetch all records respectively. By passing argument called **$useCache** we can control when retrieve data from the database and when from the buffer.
```php
<?php
class Users extends ZFExt_Model_DbTable
{
    protected $_name = 'users';
    protected $_primary = 'user_id';
 
    public function get($id, $useCache = true)
    {
        $select = $this->select()
            ->where('user_id = :user_id');
 
        return $this->loadOne($select, array('user_id' => $id), null, $useCache, __METHOD__);
    }
 
    public function getAll($useCache = true)
    {
        $select = $this->select()
            ->where('is_active = :is_active');
 
        return $this->loadAll($select, array('is_active' => 1), null, $useCache, __METHOD__);
    }
}
```
Now we can initialize our class and fetch some data form database.
```php
<?php
/** Composer Autoloader **/
require 'vendor/autoload.php';

$db = Zend_Db::factory('Pdo_Mysql', array(
    'host'     => '127.0.0.1',
    'username' => 'your_db_user',
    'password' => 'your_db_passord',
    'dbname'   => 'your_db_name'
));
 
$cache = Zend_Cache::factory('Core', 'APC', array(
    'lifetime' => 3600,
    'automatic_serialization' => true,
));
 
$userModel = new Users(array('db' => $db, 'cache' => $cache));
 
$users = $userModel->getAll();
```
Using $userModel->getAll () fetch all active users and saves the results in buffer under the identifier: 
```
Users__getAll__a_1__s_9__is_active__i_1
```

### Available methods
* setCache($cache)
* getCache()
* setOptions(array $options)
* createCacheId($methodName, $methodArguments = array())
* removeByPartialId($id)
* loadOne($sql, $bind = array(), $fetchMode = null, $useCache = true, $calleeMethodName = '')
* loadAll($sql, $bind = array(), $fetchMode = null, $useCache = true, $calleeMethodName = '')
* _loadFromCacheIfPossible($fetchMethod, array $args)

### Brief description of the methods

**setCache()** 
Sets or overrides the cache object. Method is used internally by the constructor, if we passed an associative array with the index named *cache* as in the example above.

**getCache()**
Returns the cache object.

**setOptions()**
Sets or overrides options. Method is used internally by the constructor, if we passed an associative array.

**createCacheId()**
Generate descriptive cache ID based on two arguments. Method is used internally by methods **loadOne()** & **loadAll()**. **$methodName** is pretty self-explanatory. It is also mandatory. Second argument called **$methodArguments** is an array of values that will be used during the execution of the query.

**removeByPartialId()**
Removes cache entry by partial ID. It's useful to remove entries from specific model class without removing all entries.

**loadOne()** & **loadAll()** are only the proxy methods, providing an intuitive API (first 3 arguments are the same as in the methods fetchRow () & fetchAll () in class [Zend_Db_Adapter](http://framework.zend.com/manual/1.12/en/zend.db.adapter.html "Zend_Db_Adapter"). They internally use method **_loadFromCacheIfPossible()**.

Let's look at the argment list. 
**$sql** may be raw SQL query or Zend_Db_Select object. 
**$bind** is an array of values that will be used during the execution of the query. It is best when the parameters are named, because it will be used to generate descriptive cache ID. For the same reason, the last argument, **$calleeMethodName** is the name of the function that uses the **loadOne ()** / **loadAll ()** to fech data.  **$fetchMode** controls the way in which the Zend_Db returns query results. Specify the fetch mode using Zend_Db class constants FETCH_ASSOC, FETCH_NUM, FETCH_BOTH, FETCH_COLUMN, and FETCH_OBJ. To learn more, see the online documentation for Zend, chapter [Zend_Db_Statement](http://framework.zend.com/manual/1.11/en/zend.db.statement.html#zend.db.statement.fetching.fetch-mode "Zend_Db_Statement"). 
**$useCache** is boolean flag that control when retrieve data from the database and when from the buffer.

**_loadFromCacheIfPossible()** 
Used internally by methods **loadOne()** & **loadAll()**. It takes two arguments. **$fetchMethod** is a string which may take 2 values: *fetchRow*, *fetchAll* respectively. They are the names of methods from class Zend_Db_Adapter that will be used to retrieve  data from database (in case no data found in buffer).  **$args** is an array containing all arguments passed to methods **loadOne()** & **loadAll()**.
