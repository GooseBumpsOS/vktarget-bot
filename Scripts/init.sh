#!/bin/bash

var=`date +%s`;

mkdir /home/georgy/vkTargetBots/$var;

cp -r /home/georgy/vkTargetBase/* /home/georgy/vkTargetBots/$var/

echo '[
  {
    "Vk": {
      "login": "959780904914",
      "pass": "glSp2YupsyeN",
      "token": "6f3f82d89fc317cff67aeb7e4bcd1765b101182576c01aceb95fd88d24e803dd2bd858d3e0fab7b4f5f9d"
    },
    "Vktarget": {
      "login": "grisha.gukov@mail.ru",
      "pass": "grisha.gukov@mail.ru"
    }
  }
]' >  /home/georgy/vkTargetBots/$var/jsondb/Tables/auth.data.json;

cd /home/georgy/vkTargetBots/$var/;

nohup php index.php >logTarget.txt 2>&1 & echo $! > pid.txt

cd ~;
