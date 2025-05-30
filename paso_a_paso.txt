Poner en all:

sudo vim /etc/apache2/apache2.conf 

<Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
</Directory>




<!-- AÑADIR HTTPS -->

sudo ufw allow "Apache Full"

sudo a2enmod ssl

sudo systemctl restart apache2

sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/apache-selfsigned.key -out /etc/ssl/certs/apache-selfsigned.crt

sudo nano /etc/apache2/sites-available/sintesi.local.conf

# sintesi.local.conf
ESTE NO ES
<!-- <VirtualHost *:80>
    ServerAdmin admin@sintesi.local
    ServerName www.sintesi.local
    ServerAlias sintesi.local
    DocumentRoot /var/www/sintesi.local/public
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined


</VirtualHost>
<VirtualHost *:443>
   ServerName www.sintesi.local
   DocumentRoot /var/www/sintesi.local/public

   SSLEngine on
   SSLCertificateFile /etc/ssl/certs/apache-selfsigned.crt
   SSLCertificateKeyFile /etc/ssl/private/apache-selfsigned.key
</VirtualHost> -->


<VirtualHost *:80>
    ServerAdmin admin@sintesi.local
    ServerName www.sintesi.local
    Redirect / https://www.sintesi.local/

</VirtualHost>
<VirtualHost *:443>
   ServerName www.sintesi.local
   DocumentRoot /var/www/sintesi.local/public

   SSLEngine on
   SSLCertificateFile /etc/ssl/certs/apache-selfsigned.crt
   SSLCertificateKeyFile /etc/ssl/private/apache-selfsigned.key
</VirtualHost>


sudo systemctl reload apache2
















INSTALAR EL PROYECTO EN LOCAL

sudo apt update

sudo apt install apache2

PONER EL PROYECTO EN /var/www/sintesi.local

sudo chown -R tu-usuario:www-data /var/www/sintesi.local

sudo apt install mysql-server -y

sudo apt install php-mysql

sudo mysql -u root -p

CREATE USER 'usuario'@'localhost' IDENTIFIED BY 'usuario';

GRANT ALL PRIVILEGES ON *.* TO 'usuario'@'localhost' WITH GRANT OPTION;

exit;

mysql -u usuario -p

create database ShopList;

use ShopList;

METER TODAS LAS TABLAS

exit

sudo mkdir /var/www/sintesi.local

sudo ufw allow "Apache Full"

sudo a2enmod ssl

sudo systemctl restart apache2

sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/apache-selfsigned.key -out /etc/ssl/certs/apache-selfsigned.crt

sudo nano /etc/apache2/sites-available/sintesi.local.conf

    # sintesi.local.conf
    <VirtualHost *:80>
        ServerAdmin admin@sintesi.local
        ServerName www.shoplist.local
        Redirect / https://www.shoplist.local/

    </VirtualHost>
    <VirtualHost *:443>
    ServerName www.shoplist.local
    DocumentRoot /var/www/sintesi.local/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/apache-selfsigned.crt
    SSLCertificateKeyFile /etc/ssl/private/apache-selfsigned.key
    </VirtualHost>

sudo systemctl restart apache2

cd /etc/apache2/sites-available

sudo a2ensite sintesi.local.conf

sudo systemctl restart apache2

sudo vim /etc/apache2/apache2.conf 

    Cambiar a All
    <Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

sudo vi /etc/hosts

127.0.0.1   www.shoplist.local

sudo a2enmod rewrite

sudo systemctl restart apache2

sudo vim /etc/apache2/sites-available/000-default.conf

DocumentRoot /var/www/sintesi.local/public

sudo systemctl restart apache2


composer require phpmailer/phpmailer