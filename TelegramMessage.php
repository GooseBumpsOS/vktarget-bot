<?php

namespace App\Notifier;

use Jajo\JSONDB;

require_once 'jsondb/JSONDB.php';

Trait TelegramMessage
{
    public function _isUnique($text, $chat_id)
    {

        $db = new JSONDB();

        if (count($db->select('text')->from('telegram.messages')->where(['text' => $text])->get()) > 0)
            return false;
        else
            return true;
    }

    public function sendTgMessage($text, $chat_id = 304165806)
    {

        file_get_contents("https://api.telegram.org/bot863176881:AAHBJ2IUoNAIkxv9fLKpOfQop5eQe9p68gk/sendMessage?chat_id={$chat_id}&text=" . urlencode($text));

        $db = new JSONDB();
        $db->insert('telegram.messages', [

            'text' => $text

        ]);

    }

    public function sendTgUniqueNotification($text, $chat_id = 304165806)
    {
        if ($this->_isUnique($text, $chat_id))
            $this->sendTgMessage($text, $chat_id = 304165806);
        else
            return true;

    }

}