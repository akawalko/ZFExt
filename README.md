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
