homesensor
==========

Software for managing DS2490 based temperature (and other) sensors, and to draw graph.

==Installation==

Ubuntu/Debian

apt-get install curl php5 php5-mysql owfs perl python

mkdir -p ~/app/composer
cd ~/app/composer
curl -sS https://getcomposer.org/installer | php
#add composer to your path eg. edit ~/.bashrc and add
alias composer=~/app/composer/composer.phar

Usage 
git clone https://github.com/pharazon/homesensor.git
cd homesensor
composer update
