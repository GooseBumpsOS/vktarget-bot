<?php


namespace App\SocialNetworks;

use App\Notifier\TelegramMessage;

require_once 'TaskManagerInterface.php';
require_once 'TelegramMessage.php';


class OkWorker implements TaskManagerInterface
{
    use TelegramMessage;

    private function _apiRequest(string $url, array $params, $type)
    {

        //$cookie = $this->_simpleRequest(); //TODO сделать вход
        $tokens = $this->_findTokens($this->_simpleRequest($url), $type, $url);
        $this->_simpleRequest($url . '?' . http_build_query(array_merge($params, $tokens)));

    }

    private function _findTokens($rawHtml, $type, $url): array
    {

        switch ($type) {

            case 'share':

                $jsArr = explode(';', explode('var pageCtx=', $rawHtml)[1])[0];

                preg_match('/gwtHash: ".+",/m', $jsArr, $gwt);
                $gwt = explode('",', explode('gwtHash: "', $gwt[0])[1])[0];

                preg_match('st\.groupId=\d+/m', $jsArr, $gId);
                $gId = explode('st.groupId=', $gId[0])[1];

                preg_match('/[^\/]+$/m', $url, $topicId);

                $rnowRndId = explode('"', explode('hook_Block_ReshareNow_'.$topicId[0].'_0_2_', $jsArr)[1])[0];

                $resArr['cmd'] = 'ReshareNow';
                $resArr['st.cmd'] = 'altGroupForum';
                $resArr['st.layer.cmd'] = 'PopLayerReshare';
                $resArr['st.layer.refId2'] = '0';
                $resArr['st.layer.refId1'] = $topicId[0];
                $resArr['st.layer.intmdrId'] = $topicId[0];
                $resArr['st.layer.disCnt'] = 'off';
                $resArr['st.vpl.mini'] = 'false';
                $resArr['st.layer.type'] = '2';
                $resArr['gwt.requested'] = $gwt;
                $resArr['st.groupId'] = $gId;
                $resArr['st.layer.rnowRndId'] = $rnowRndId;



                break;


        }

    }

    public function makeTask($link, $type)
    {

        switch (true) {

            case preg_match('/сообщество/m', $type):
                preg_match('/\d+$/m', $link, $res);
                $this->_apiRequest('groups.join', ['group_id' => $res[0]], 'share');
                break;
            default:
                $this->sendTgUniqueNotification('Что-то новенькое: ' . $link . "    " . $type);
                break;


        }

    }

    private function _simpleRequest($url, $params = false, $cookie = '', $needLogin = true, $needReturnHeader = false, $headers = ['\'Expect:\''], $connectionTimeOut = 20)
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

        curl_setopt($ch, CURLOPT_COOKIE, $cookie);

        if ($params) {

            curl_setopt($ch, CURLOPT_POST, 1);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // это необходимо, чтобы cURL не высылал заголовок на ожидание
        $tempRes = curl_exec($ch);
        $last_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch); // Завершаем сеанс
    }
}