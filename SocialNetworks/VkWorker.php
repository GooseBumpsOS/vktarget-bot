<?php

namespace App\SocialNetworks;

require_once 'TelegramMessage.php';
require_once 'TaskManagerInterface.php';

use App\Notifier\TelegramMessage;

class VkWorker implements TaskManagerInterface
{
    use TelegramMessage;

    private $api_key;

    public function __construct($api_key)
    {

        $this->api_key = $api_key;

    }

    private function _vkApiGenerator($method, $params)
    {
        file_get_contents('https://api.vk.com/method/' . $method . '?' . http_build_query($params) . '&access_token=' . $this->api_key . '&v=5.103');
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
            case preg_match('/(поделиться)|(Расскажите)/m', $type):
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
