<?php

namespace App;

use App\Notifier\TelegramMessage;

require_once 'SocialFactory.php';
require_once 'TelegramMessage.php';

define('mailCodeFileName', 'mailcode.txt');

class VkTargetParser
{
    private $login;
    private $pass;
    private $cookie = null;

    private $factory = null;

    use TelegramMessage { sendTgMessage as private; sendTgUniqueNotification as private;}

    public function __construct(Auth $auth)
    {
        $this->login = $auth->getVktarget('login');
        $this->pass = $auth->getVktarget('pass');

        $this->factory = new SocialFactory($auth);

        $this->_login();

    }

    public function loop()
    {
        for (; ;) {

            $rawHtml = $this->_sendReqToVktarget('https://vktarget.ru/list/');
            $tasks = explode(';', explode('LIST_TABLE = ', $rawHtml)[1]);
            $tasks = json_decode($tasks[0], true);
            $balance = $this->_getBalance($tasks);

            if ($balance > 100)
                $this->_withdraw($balance);

            unset($rawHtml);

            for ($i = 1; $i < count($tasks); $i++) {
                $id = $tasks[$i]['id'];

                switch (true) {

                    case preg_match('/vk\.com/m', $tasks[$i]['url']):
                        $this->_doTask('vk', $tasks[$i]['url'], $id, $tasks[$i]['type_name'] . ' ' . $tasks[$i]['type_name_link']);
                        break;


                }
            }
            $this->_makeUserActivity();
            echo "New iteration\n";
        }
    }

    private function _login()
    {
        $loginParams = [
            'action' => 'auth',
            'email' => $this->login,
            'password' => $this->pass,
            'js_on' => '2020',
            'timezone_diff' => '-180',
            'answer_code' => ''
        ];

        $response = $this->_sendReqToVktarget('https://vktarget.ru/', [], false, true);

        preg_match('/PHPSESSID=\w+;/m', $response, $cookie);

        $magicbox = explode('magicbox', $response);

        $headers = [
            'Referer: https://vktarget.ru/',
//            'Accept-Encoding: gzip, deflate',
            'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7,la;q=0.6',
            'Cookie: ' . $cookie[0],
            'Accept: */*',
            'VKN: ' . $magicbox[1],
            'Sec-Fetch-Dest: empty',
            'X-Requested-With: XMLHttpRequest',
            'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36',
            'Content-Type: application/x-www-form-urlencoded',
            'Origin: https://vktarget.ru',
            'Sec-Fetch-Site: same-origin',
            'Sec-Fetch-Mode: cors'];

        $this->_sendReqToVktarget('https://vktarget.ru/img/header/logo.svg', [], false, false, $headers);

        $this->_sendReqToVktarget('https://vktarget.ru/api/all.php', $loginParams, false, false, $headers);

        $this->cookie = $cookie[0];

    }

    private function _doTask($socialNetwork, $link, $id, $task)
    {

        $social = $this->factory->generate($socialNetwork);
        $social->makeTask($link, $task);
        $this->_approveTask($id);
    }

    private function _makeUserActivity()
    {
        for ($i = 0; $i < 20; $i++) {

            $this->_sendReqToVktarget('https://vktarget.ru/api/all.php', ['action' => 'active_user', 'k' => mt_rand(18, 100)]);

            sleep(rand(2, 6));

        }

    }

    private function _getBalance($jsonInfo)
    {

        return intval($jsonInfo[0]['user_balance']);

    }

    private function _withdraw($sum, $wallet = '410018224994438')
    {

        $date = time();//TIMESTAMP for bot search of command

        $this->_sendReqToVktarget('https://vktarget.ru/api/all.php?no_cache=0.981409013479047&action=withdraw', [

            'answer' => '',
            'code' => '',
            'type' => 'yandex',
            'sum' => $sum,
            'wallet' => $wallet,


        ]);

        $this->sendTgMessage("Хотим сделать вывод $sum руб. Нужен код с почты $this->login. Папка " . getcwd());

        while (!file_exists(mailCodeFileName)) {

            sleep(5);
            if ($code = $this->getUpdate('mailcode', $date) !== false)
                file_put_contents(mailCodeFileName, $code);

        }

        $mailCode = file_get_contents(mailCodeFileName);
        system('rm ' . mailCodeFileName);

        $this->_sendReqToVktarget('https://vktarget.ru/api/all.php?action=withdraw', [

            'answer' => '',
            'code' => $mailCode,
            'type' => 'yandex',
            'sum' => $sum,
            'wallet' => $wallet

        ]);

        $this->sendTgMessage("Создали заявку, спасибо");

    }

    private function _approveTask($id)
    {

        $this->_sendReqToVktarget('https://vktarget.ru/api/all.php?action=check_task', ['tid' => $id, 'host_state' => 'vktarget.ru']);

    }

    private function _sendReqToVktarget($url, $params = [], $needLogin = true, $needReturnHeader = false, $headers = ['\'Expect:\''], $connectionTimeOut = 20)
    {

        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

        $ch = curl_init(); // Инициализация сеанса
        curl_setopt($ch, CURLOPT_URL, $url); // Куда данные послать
        curl_setopt($ch, CURLOPT_HEADER, $needReturnHeader);
//        curl_setopt($ch, CURLOPT_NOBODY, $needReturnHeader);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectionTimeOut);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);

        if (count($params) > 0) {

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,
                http_build_query($params));

        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // это необходимо, чтобы cURL не высылал заголовок на ожидание
        $tempRes = curl_exec($ch);
        $last_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch); // Завершаем сеанс

        if (strpos($last_url, 'login') !== false && $needLogin == true) {

            $this->_login();

            return $this->_sendReqToVktarget($url, $params, $needLogin, $needReturnHeader, $headers, $connectionTimeOut);


        }

        return $tempRes;
    }
}
