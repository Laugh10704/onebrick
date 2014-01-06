#!/bin/bash

mysql -uroot -p1_brick! drupal7 -e "insert into users (name, pass, mail, theme, signature, language, init, timezone) values ('', '', '', '', '', '', '', '');"
mysql -uroot -p1_brick! drupal7 -e "update users set uid = 0 where name = '';"
