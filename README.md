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
