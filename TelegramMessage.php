<?php


namespace App\Notifier;


trait TelegramMessage
{

    function sendTgMsg($text, $chat_id = 304165806)
    {

        file_get_contents("https://api.telegram.org/bot863176881:AAHBJ2IUoNAIkxv9fLKpOfQop5eQe9p68gk/sendMessage?chat_id={$chat_id}&text=" . urlencode($text));

    }

}