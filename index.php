<?php

include 'VkTargetParser.php';

$main = new \App\VkTargetParser('16c9d9537f@emailtown.club', '16c9d9537f@emailtown.club');
$main->loop(60);


//https://api.vk.com/method/groups.join?group_id=191672960&not_sure=1&access_token=23f2b13416cd5bd6b7af0017f8fce3b86478491f8eee9a2267c39e8a267441e929e273f2670d94a283a73&v=5.103

//https://oauth.vk.com/authorize?client_id=7369325&display=page&redirect_uri=https://oauth.vk.com/blank.html&scope=friends,messages,groups,stats,wall,offline&response_type=token&v=5.103&state=123456

//23f2b13416cd5bd6b7af0017f8fce3b86478491f8eee9a2267c39e8a267441e929e273f2670d94a283a73 api-key

//16c9d9537f@emailtown.club

//77081267010
//50dadbb1b3fdc314aa3abd
//var jq = document.createElement('script');
//jq.src = "https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js";
//document.getElementsByTagName('head')[0].appendChild(jq);
//
//            jQuery.ajax({
//                url: "/api/all.php",
//                type: "post",
//                data: {
//    action: "auth",
//                    email: '16c9d9537f@emailtown.club',
//                    password: '16c9d9537f@emailtown.club',
//                    js_on: 2020,
//                    timezone_diff: -20,
//                    answer_code: ''
//                },
//                headers: {
//    VKN:     $("html").prop("nextSibling").nodeValue.match(/magicbox(.*)magicbox/)[1]
//},
//                success: function(e) {
//alert('ok')
//}
//            })