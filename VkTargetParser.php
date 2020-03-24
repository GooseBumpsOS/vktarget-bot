<?php

namespace App;

require_once 'SocialFactory.php';

class VkTargetParser
{
    private $login;
    private $pass;
    private $cookie = null;

    private $factory = null;

    public function __construct($login, $password)
    {
        $this->login = $login;
        $this->pass = $password;

        $this->factory = new SocialFactory();

    }

    public function loop($sleep = 600)
    {
        for (; ;) {

            $rawHtml = $this->_sendReqToVktarget('https://vktarget.ru/list/');
//die($rawHtml);
            $tasks = explode(';', explode('LIST_TABLE = ', $rawHtml)[1]);
            $tasks = json_decode($tasks[0], true);
            unset($rawHtml);

            for ($i = 1; $i < count($tasks); $i++) {
                $id = $tasks[$i]['id'];

                switch (true) {

                    case preg_match('/vk\.com/m', $tasks[$i]['url']):
                        $this->_doTask('vk', $tasks[$i]['url'], $id, $tasks[$i]['type_name'] . ' ' . $tasks[$i]['type_name_link']);
                        break;


                }
            }

//            echo "New iteration\n";
            sleep($sleep);
        }
    }

    private function _login()
    {

        //TODO сделать функцию получения данных для входа

    }

    private function _doTask($socialNetwork, $link, $id, $task)
    {

        $social = $this->factory->generate($socialNetwork);
        $social->makeTask($link, $task);
        $this->_approveTask($id);
        $this->_makeUserActivity();
    }

    private function _makeUserActivity()
    {

        _sendReqToVktarget('https://vktarget.ru/api/all.php', ['action' => 'active_user', 'k' => mt_rand(18, 100)]);

        sleep(rand(10, 60));

    }

    private function _approveTask($id)
    {

        _sendReqToVktarget('https://vktarget.ru/api/all.php?action=check_task', ['tid' => $id, 'host_state' => 'vktarget.ru']);

    }

    private function _sendReqToVktarget($url, $params = [])
    {

        is_null($this->cookie) ? $this->_login() : true;

        $ch = curl_init(); // Инициализация сеанса
        curl_setopt($ch, CURLOPT_URL, $url); // Куда данные послать
        curl_setopt($ch, CURLOPT_HEADER, 0); // получать заголовки
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36');
        curl_setopt($ch, CURLOPT_REFERER, 'http://in.3level.ru');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        if (count($params) > 0) {

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,
                http_build_query($params));

        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); // это необходимо, чтобы cURL не высылал заголовок на ожидание
        $tempRes = curl_exec($ch);
        curl_close($ch); // Завершаем сеанс

        return $tempRes;
    }
}