<?php


namespace App;

use Jajo\JSONDB;

require_once 'jsondb/JSONDB.php';

define('tableName', 'auth.data');

class Auth
{
    private $vkAuth;
    private $twitter;
    private $vktarget;

    public function __construct()
    {

        $this->_init();

    }

    private function _init()
    {
        $db = new JSONDB();
        $result = $db->select('*')->from(tableName)->where(['isUse' => false])->get();

        if (count($result) > 0 && !is_null($result))
            $result = $result[0];
        else
            throw new \Exception('Проблемы в БД, вернулся пустой результат');

        $db->update(['isUse' => true])->from(tableName)->where(['isUse' => false])->get();

        switch (true) {

            case isset($result['Vk']):
                $this->setVkAuth($result['Vk']);
//            case isset($result['Twitter']):
//                $this->setTwitter($result['Twitter']);
            case isset($result['Vktarget']):
                $this->setVktarget($result['Vktarget']);
        }
    }


    /**
     * @return mixed
     */
    public function getTwitter(string $key): string
    {
        return $this->twitter[$key];
    }

    /**
     * @param mixed $twitter
     * @throws \Exception
     */
    public function setTwitter($twitter)
    {
//        if ($this->_isKeysExist($twitter, 'login', 'pass', 'token'))
//            throw new \Exception('Key not exist in Twitter Array Config');

        $this->twitter = $twitter;
    }

    /**
     * @return array
     * ['login', 'pass']
     */
    public function getVktarget(string $key): string
    {
        return $this->vktarget[$key];
    }

    /**
     * @param mixed $vktarget
     * @throws \Exception
     */
    public function setVktarget($vktarget)
    {
        if ($this->_isKeysExist($vktarget, 'login', 'pass'))
            throw new \Exception('Key not exist in Vktaget Array Config');


        $this->vktarget = $vktarget;
    }

    /**
     * @param $key
     * @return string
     * ['login', 'pass', 'token']
     */
    public function getVkAuth(string $key): string
    {
        return $this->vkAuth[$key];
    }

    /**
     * @param mixed $vkAuth
     * ['login', 'pass', 'token']
     * @throws \Exception
     */
    public function setVkAuth(array $vkAuth)
    {
        if ($this->_isKeysExist($vkAuth, 'login', 'pass', 'token'))
            throw new \Exception('Key not exist in Vk Array Config');

        $this->vkAuth = $vkAuth;
    }

    private function _isKeysExist($arr, ...$keys)
    {
        foreach ($keys as $el)
            if (!array_key_exists($el, $arr))
                return false;

    }
}