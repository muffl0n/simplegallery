simplegallery
=======

simplegallery is a very lightweight gallery script for PHP. It uses [ImageMagick](http://php.net/manual/en/book.imagick.php) for resizing and [jQuery](http://jquery.com/) for navigation (cursor keys).

Getting started
=======
* link the Script gallery.php to the directory with your JPG-Files
* create directory ".thumbs" and grant your webserver write privileges

Example:
```cd /var/www/example.com/pics
ln -s ~/git/simplegallery/gallery.php index.php
mkdir .thumbs
chgrp www-data .thumbs
chmod g+rwx .thumbs```
