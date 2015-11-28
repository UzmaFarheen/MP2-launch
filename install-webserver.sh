#!/bin/bash

sudo apt-get -y update
sudo apt-get install -y apache2 git php5 php5-curl mysql-client curl php5-mysql

git clone https://github.com/UzmaFarheen/ITMO544-localinstance.git
git clone https://github.com/UzmaFarheen/MP1-PHP.git
git clone https://github.com/UzmaFarheen/MP2-launch.git

sudo mv ./MP1-PHP/index.html /var/www/html
sudo mv ./MP1-PHP/page2.html /var/www/html
sudo mv ./MP1-PHP/*.css /var/www/html
sudo mv ./MP1-PHP/*.js /var/www/html
sudo mv ./MP1-PHP/*.php /var/www/html
sudo mv ./ITMO544-localinstance/images/*.jpeg /var/www/html

curl -sS https://getcomposer.org/installer | sudo php &> /tmp/getcomposer.txt

sudo php composer.phar require aws/aws-sdk-php &> /tmp/runcomposer.txt

sudo mv vendor /var/www/html &> /tmp/movevendor.txt

sudo php /var/www/html/setup.php &> /tmp/database-setup.txt

echo "Hello, My Name is UZMA FARHEEN" > /tmp/hello.txt
