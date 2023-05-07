Sakai Developer Test Harness
============================

This is a set of php scripts when used in conjunction with Dr. Chuck's build scripts that allow a user to control their Sakai
through a browser.

This needs a copy of `sakai-scripts` checked out somewhere in your disk hierarchy.  On Linux
with Apache, the simplest place is `/var/www/sakai-scripts` as `/var/www` is the home directory
for the `www-data` account. 

The owner of the entire `sakai-scripts` folder must be the same as the web server account.
For example on my ac laptopn, the web server runs as `csev` and the `sakai-scripts` folder
is owned by `csev` so the scripts can alter the files.    On Linux, the Apache server
generally runs as `www-data` so once things are set up you will need to:

    cd /var/www
    git clone https://github.com/csev/sakai-scripts
    chown -R www-data:www-data /var/www

To configure this software, copy `config-dist.php` to `config.php` and edit as appropriate.


Detailed Notes
--------------

These are notes for my setup on Linux.

Use the Tsugi dev environment to set up Apache, PHP, and PHPMyAdmin

    cd /root
    https://github.com/tsugiproject/tsugi-build.git
    cd tsugi-build
    bash ubuntu/build-dev.sh

Install some extra bits for phpMyAdmin:

    apt install php-mysqli php-xml php-curl php-zip curl composer

Configure the Tsugi bit:

    cp tsugi-build/ubuntu/ubuntu-env-demo.sh ubuntu-env.sh
    vi ubuntu-env.sh 

Make devtest the `MAIN_REPO` in the `ubuntu-env.sh` file

    export MAIN_REPO=https://github.com/csev/devtest.git

Configure the Tsugi server  

    source ubuntu-env.sh
    bash /usr/local/bin/tsugi-dev-configure.sh return

    a2enmod proxy_http
    systemctl restart apache2

See if your web server came up:

    http://67.205.132.116/

You might want to set up a password for your MySQL - do this as root:

    mysql -u root
    ALTER USER 'root'@'localhost' IDENTIFIED BY 'root';

This root password will be needed when setting up the sakai-scripts `config.sh`
file.

Set up permissions on the `/var/www` so `www-data` can use it as a real log in folder
(remember this is not a serious production server).

    chown -R www-data:www-data /var/www
    cd /var/www
    su -s "/bin/bash" www-data

    vi ~/.bashrc
    export PATH=.:$PATH
    cd /var/www/html/phpMyAdmin/
    composer update

Do the rest as `www-data` using `su` as necessary to switch from `root` to `www-data`.
Check out `sakai-scripts`:

    cd /var/www
    git clone https://github.com/csev/sakai-scripts.git

    cd sakai-scripts

Setup `config.sh`, `sakai.properties`, and `server.xml`  - then manually compile,
start, and test Sakai:

    bash ubuntu.sh
    bash db.sh
    bash co.sh
    bash na.sh
    bash qmv.sh
    bash start.sh
    bash tail.sh

    http://67.205.132.116:8080/

Switch back to `root` and set up virtual hosting and HTTP proxy in Apache:

    vi /etc/apache2/sites-available/qasak.sakaicloud.com.conf 

    <VirtualHost *:80>
        ServerName qasak.sakaicloud.com
        ServerAdmin webmaster@localhost

        ProxyPreserveHost On

        ProxyPass / http://127.0.0.1:8080/
        ProxyPassReverse / http://127.0.0.1:8080/
    </VirtualHost>

    a2ensite qasak.sakaicloud.com
    systemctl reload apache2

    apache2ctl -M
    apache2ctl -S

Then set up two domains to point to the server:

    https://qadev.sakaicloud.com/ ==> 67.205.132.116
    https://qasak.sakaicloud.com/ ==> 67.205.132.116

I use CloudFlare for DNS, DDOS, and proxy so it handles certificates for me.



