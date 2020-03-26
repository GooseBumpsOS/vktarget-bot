<?php

namespace App\SocialNetworks;

require_once 'TelegramMessage.php';
require_once 'TaskManagerInterface.php';

use App\Notifier\TelegramMessage;

define('api_key', '23f2b13416cd5bd6b7af0017f8fce3b86478491f8eee9a2267c39e8a267441e929e273f2670d94a283a73');

class VkWorker implements TaskManagerInterface
{
    use TelegramMessage;

    private function _vkApiGenerator($method, $params)
    {
        file_get_contents('https://api.vk.com/method/' . $method . '?' . http_build_query($params) . '&access_token=' . api_key . '&v=5.103');
    }

    public function makeTask($link, $type)
    {
        switch (true) {

            case preg_match('/сообщество/m', $type):
                preg_match('/\d+$/m', $link, $res);
                $this->_vkApiGenerator('groups.join', ['group_id' => $res[0]]);
                break;
            case preg_match('/лайк/m', $type):
                preg_match_all('/(-\d+)|(\d+$)/', $link, $res);//res[0] => owner_id; [1] =>item_id
                $this->_vkApiGenerator('likes.add', ['item_id' => $res[0][1], 'type' => 'post', 'owner_id' => $res[0][0]]);
                break;
            case preg_match('/(поделиться)|(расскажите)/m', $type):
                preg_match('/[^\/]+$/', $link, $res);
                $this->_vkApiGenerator('wall.repost', ['object' => $res[0]]);
                break;
        case preg_match('/друзья/m', $type):
            preg_match('/[^\/id]+$/', $link, $res);
            $this->_vkApiGenerator('friends.add', ['user_id' => $res[0], 'text' => 'vktarget ' . rand()]);
            break;
            default:
                $this->sendTgUniqueNotification('Что-то новенькое: ' . $link . "    " . $type);
                break;


        }
    }
}
