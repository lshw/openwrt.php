#!/bin/bash
cd $( dirname $0 )

# apt install php php-sqlite3
php -S 0.0.0.0:11451 -t public 
