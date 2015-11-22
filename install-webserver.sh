#!/bin/bash

sudo apt-get -y update
sudo apt-get install -y apache2 git php5 php5-curl mysql-client curl php5-mysql

curl -sS https://getcomposer.org/installer | sudo php &> /tmp/getcomposer.txt

sudo php composer.phar require aws/aws-sdk-php &> /tmp/runcomposer.txt

sudo mv vendor /var/www/html &> /tmp/movevendor.txt

sudo php /var/www/html/setup.php &> /tmp/database-setup.txt

echo "Hello, My Name is UZMA FARHEEN" > /tmp/hello.txt
