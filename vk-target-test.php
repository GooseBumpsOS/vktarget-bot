<?php

define('api_key', '23f2b13416cd5bd6b7af0017f8fce3b86478491f8eee9a2267c39e8a267441e929e273f2670d94a283a73');


loop();

function loop($sleep = 600)
{
    for (; ;) {

        $rawHtml = _sendReqToVktarget('https://vktarget.ru/list/');
//die($rawHtml);
        $tasks = explode(';', explode('LIST_TABLE = ', $rawHtml)[1]);
        $tasks = json_decode($tasks[0], true);

        for ($i = 1; $i < count($tasks); $i++) {

            if (preg_match('/vk\.com/m', $tasks[$i]['url'])) {
                $id = $tasks[$i]['id'];
                makeTask($tasks[$i]['url'], $tasks[$i]['type_name'] . ' ' . $tasks[$i]['type_name_link']);
                approveTask($id);
            }
        }

	makeUserActivity();
        echo "New iteration\n";
        //sleep($sleep);
    }
}

function makeUserActivity(){

	_sendReqToVktarget(' https://vktarget.ru/api/all.php', ['action' => 'active_user', 'k' => mt_rand(18, 100)]);

	sleep(rand(10,60));

}

function approveTask($id)
{

    _sendReqToVktarget('https://vktarget.ru/api/all.php?action=check_task', ['tid' => $id, 'host_state' => 'vktarget.ru']);

}

function makeTask($link, $type)
{

    switch (true) {

        case preg_match('/сообщество/m', $type):
            preg_match('/\d+$/m', $link, $res);
            vkApiGenerator('groups.join', ['group_id' => $res[0]]);
            break;
        case preg_match('/лайк/m', $type):
            preg_match_all('/(-\d+)|(\d+$)/', $link, $res);//res[0] => owner_id; [1] =>item_id
            vkApiGenerator('likes.add', ['item_id' => $res[0][1], 'type' => 'post', 'owner_id' => $res[0][0]]);
            break;
        case preg_match('/поделиться/m', $type):
            preg_match('/[^\/]+$/', $link, $res);
            vkApiGenerator('wall.repost', ['object' => $res[0]]);
            break;
//        case 'Рассказать о группе':
//            break;
//        case 'Добавить в друзья':
//            break;
        default:
            sendTgMsg('Что-то новенькое: ' . $link . "    " . $type);
            break;


    }


}

function sendTgMsg($text, $chat_id = 304165806)
{

//    file_get_contents("https://api.telegram.org/bot863176881:AAHBJ2IUoNAIkxv9fLKpOfQop5eQe9p68gk/sendMessage?chat_id={$chat_id}&text=" . urlencode($text));

}

function vkApiGenerator($method, $params)
{


    file_get_contents('https://api.vk.com/method/' . $method . '?' . http_build_query($params) . '&access_token=' . api_key . '&v=5.103');

}

function _sendReqToVktarget($url, $params = [])
{

    $ch = curl_init(); // Инициализация сеанса
    curl_setopt($ch, CURLOPT_URL, $url); // Куда данные послать
    curl_setopt($ch, CURLOPT_HEADER, 0); // получать заголовки
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36');
    curl_setopt($ch, CURLOPT_REFERER, 'http://in.3level.ru');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID=b47ied42u9hh7i1o59pul0ieji; ref_page=https%3A%2F%2Fyandex.ru%2F; _ga=GA1.2.86841377.1584923293; _gid=GA1.2.1951376851.1584923293; _ym_uid=1584923293405960383; _ym_d=1584923293; _ym_isad=2; COPINY_AUTH=SFBZMFRiNmNZTFA1eUN0bFRnb0xJQT09; io=78fviUjXh6j4xJyERJwZ; _gat_gtag_UA_55670847_1=1; _gat=1");
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


//https://api.vk.com/method/groups.join?group_id=191672960&not_sure=1&access_token=23f2b13416cd5bd6b7af0017f8fce3b86478491f8eee9a2267c39e8a267441e929e273f2670d94a283a73&v=5.103

//https://oauth.vk.com/authorize?client_id=7369325&display=page&redirect_uri=https://oauth.vk.com/blank.html&scope=friends,messages,groups,stats,wall,offline&response_type=token&v=5.103&state=123456

//23f2b13416cd5bd6b7af0017f8fce3b86478491f8eee9a2267c39e8a267441e929e273f2670d94a283a73 api-key

//16c9d9537f@emailtown.club

//77081267010
//50dadbb1b3fdc314aa3abd
