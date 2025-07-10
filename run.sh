#!/bin/bash
cd $( dirname $0 )

# 需要安装如下软件包
# apt install php php-sqlite3 php-curl
php -S 0.0.0.0:11451 -t public 
