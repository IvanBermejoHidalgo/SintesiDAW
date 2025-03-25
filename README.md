Poner en all:

sudo vim /etc/apache2/apache2.conf 

<Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
</Directory>

