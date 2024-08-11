# NOTICE 

> THIS PROJECT HAD STOPPED DEVELOPMENT AND MOVE TO LARAVEL FRAMEWORK
> 
> Please refer to `main` branch for the latest version.

# Ecolla Product Listing Website (Without payment API or function)

## Description

A product listing and ordering website which designed for Ecolla Enterprise.

This project will use MVC concept to design a dynamic enviroment for the client.

### Version of the component used

Wamp64 is recommended for this project in debugging and testing.
Component used for this project:
- PHP: 7.4.x
  - Imagick 3.4.4 (Native php extension)
- MySQL: 5.7.x

### Things to download
- wamp64: https://sourceforge.net/projects/wampserver/
- ImageMagick: http://www.imagemagick.org/script/download.php
- Imagick PHP extension dll: https://pecl.php.net/package/imagick/3.4.4/windows
  
### Instruction on installation
1. Wamp64
  - Make sure you have installed PHP and MySQL version as the number listed above.
  - Recommend to install in `c:\wamp64\`
2. ImageMagick Extension
  - Make sure you have check x64 or x86 and thread safe enable or disable from phpinfo() (example shown below)
  - Recommend to install the ImageMagick in `c:\imagemagick\`
  - Make sure "Add application directory to your system path" option is ticked while installing the program
  - After ImageMagick installation, copy all files from `C:\imagemagick\modules\coders` and `C:\imagemagick\modules\filters` to `C:\imagemagick` to load ImageMagick supported formats
  - Go to Control Panel -> System -> Advanced Settings -> Environment Variables -> New System Variable -> MAGICK_HOME = `C:\imagemagick`
  - Download the correct version (x64 or x86 and thread safe enable or disable) for extension dll
  - Copy php_imagick.dll to `C:\wamp\bin\php\{selected php version}\ext\`
  - Copy all dll files (*.dll) to `C:\wamp\bin\apache\{selected apache version}\bin\`
  - Edit `php.ini` by left clicking wamp64 icon and choose php, `php.ini` to promt out the editor
  - Add `extension=php_imagick.dll` line in extensions section
3. MySQL Setup
  - Edit `my.ini` by left clicking wamp64 icon and choose MySQL, `my.ini` to promt out the editor
  - Make sure `my.ini` file content element ```character-set-server=utf8``` below ```[mysqld]``` tag to make sure MySQL is running with `utf-8` natively
  - Make sure `my.ini` file content element ```default-time-zone = "+08:00"``` below ```[mysqld]``` tag to make sure MySQL is running with time zone GMT+8 natively
  - Logon to `phpmyadmin` with `root` and password as blank. Make sure to create the the database named `ecolladb` before importing the database from `assets/database-backup/ecolladb.sql`
4. PHP Setup
  - Edit `php.ini` by left clicking wamp64 icon and choose php, `php.ini` to promt out the editor
  - Make sure session.cache_expire has value 10080: `session.cache_expire = 10080`. Meaning: change the session expire time to 7 days (default is no session expire)
  - Make sure upload_max_filesize has value 10M: `upload_max_filesize = 10M`. Meaning: change the maximum file upload size (Dafault is 2M (2MB))
  - Make sure date.timezone has value Asia/Kuala_Lumpur: `date.timezone ="Asia/Kuala_Lumpur"`. Meaning: Change the php time zone settings to Asia/Kuala Lumpur
  - Make sure `extension=gd2` is present
  - Make sure `extension=php_imagick.dll` is present
5. Website setup
  - Move all source files to `c:\wamp64\www\`, Please make sure you delete all file from `c:\wamp64\www\` first.
  - Make sure wamp64 of 3 services can run properly and go to localhost/index.php

### phpinfo() on getPHPInfo.php
```html
<?php
    phpinfo();
?>
```
### Admin login page
You may register account using `registerAccount()` at Controller class, export the user data from database and send it to me.

### Problem faced when setup the wamp64
- Only 2 of 3 services running, the one which not running is MySQL
  - This problem coused by MySQL Service not running. Go to Windows `Services` and change the startup type of `wampmysqld64` to automatic and restart the computer may solve this problem.

### Resource
This project also used:
- gd


## Reference

Coming soon
