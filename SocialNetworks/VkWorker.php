<?php

namespace App\SocialNetworks;

require_once 'TelegramMessage.php';

use App\Notifier\TelegramMessage;

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
            case preg_match('/поделиться/m', $type):
                preg_match('/[^\/]+$/', $link, $res);
                $this->_vkApiGenerator('wall.repost', ['object' => $res[0]]);
                break;
//        case 'Рассказать о группе':
//            break;
//        case 'Добавить в друзья':
//            break;
            default:
                $this->sendTgMsg('Что-то новенькое: ' . $link . "    " . $type);
                break;


        }
    }
}