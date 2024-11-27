sudo apt-get install libimage-exiftool-perl
sudo apt install imagemagick
sudo apt install php-zip



Adicionar no `/etc/php/8.1/apache2/php.ini`
```
file_uploads = On
upload_max_filesize = 30M
post_max_size = 30M
```

Adicionar essa configuração ao `000-default.conf` do apache

```
<Directory /var/www/html>
    AllowOverride All
</Directory>
```