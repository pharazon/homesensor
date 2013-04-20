homesensor
==========

Software for managing DS2490 based temperature (and other) sensors, and to draw graph.

==Installation==

Ubuntu/Debian

apt-get install curl php5 php5-mysql php5-curl owfs perl python python-serial python-mysqldb python-configobj screen

mkdir -p ~/app/composer
cd ~/app/composer
curl -sS https://getcomposer.org/installer | php
#add composer to your path eg. edit ~/.bashrc and add
alias composer=~/app/composer/composer.phar

Usage 
git clone https://github.com/pharazon/homesensor.git
cd homesensor
composer update

Setup DB

mysql -u root -p 
mysql> create database Lampo;
Query OK, 0 rows affected (0.00 sec)

mysql> create user homesensor@localhost identified by 'homesensor';
Query OK, 0 rows affected (0.37 sec)

mysql> grant all on Lampo.* to homesensor@localhost;
Query OK, 0 rows affected (0.00 sec)

mysql> flush privileges;
Query OK, 0 rows affected (0.08 sec)

===Setup Detector===
If you are using the electricity power detector

cd homesensor/detector
cp detector.ini.sample detector.ini
ln -s PATH_TO_HOME_SENSOR/etc/init.d/homesensor /etc/rc2.d/S99homesensor
python detector.py


