#!/bin/bash

if [[ ! -e ~/dbDump ]]; then
    mkdir ~/dbDump
fi

cp -r ../jsondb/Tables ~/dbDump
