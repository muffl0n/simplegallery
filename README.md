simplegallery
=======

simplegallery is a very lightweight gallery script for PHP. It uses [ImageMagick](http://php.net/manual/en/book.imagick.php) for resizing and [jQuery](http://jquery.com/) for navigation (cursor keys).

Getting started
=======
* link the Script gallery.php to the directory with your JPG-Files
* create directory ".thumbs" and grant your webserver write privileges

Example:
```
cd /var/www/example.com/www/pics
ln -s ~/git/simplegallery/gallery.php index.php
mkdir .thumbs
chgrp www-data .thumbs
chmod g+rwx .thumbs
```

Not simply access your site (e.g. www.example.com/pics). The thumbnails will be generated on your first visit. They will also be regenerated if the source files change.

Google Maps embedding
=======
Google Maps embedding is done if the file contains EXIF information of the location where the foto has been taken. You also need to rename "config.php.sample" to "config.php" and set your API-Key accordingly.


PHP_CodeSniffer
=======
Run with: 
```
phpcs -s --standard=phpcs_ruleset.xml gallery.php
```

See: http://pear.php.net/manual/de/package.php.php-codesniffer.php

