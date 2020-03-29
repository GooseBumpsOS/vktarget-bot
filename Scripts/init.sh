#!/bin/bash

var=`date +%s`;

mkdir /home/georgy/vkTargetBots/$var;

cp -r /home/georgy/vkTargetBase /home/georgy/$var/

echo "[
  {
    "Vk": {
      "login": "51979581736",
      "pass": "vv11Fu76B9x",
      "token": "9102fe8b7a62ab6655d02ef32203e1594c545a5bd51eea84d7494b7fd2264fe44aec0cd8879c7f5a758c9"
    },
    "Vktarget": {
      "login": "aleshenka-golikov@bk.ru",
      "pass": "aleshenka-golikov@bk.ru"
    }
  }
]" >  /home/georgy/vkTargetBots/$var/jsondb/Tables/auth.data.json;

nohup php /home/georgy/vkTargetBots/$var/index.php > /home/georgy/vkTargetBots/$var/logTarget.txt 2>&1 &
