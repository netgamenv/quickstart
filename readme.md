# Requirements

1. Linux ( ubuntu is better )
2. PHP 7.0+ ( with php-fpm )
3. Nginx

# Installation

## 1. Deployment 

Deploy sources to folder /var/www/netgame on your server via git clone or by copying files.

You can use another folder, but then don't forget to change corresponding pathes in nginx config.

## 2. Nginx

First of all you need two domains - one for frontend, and second for backend. For testing pusposes it can be domain and subdomain. For example - netgame.loc and callback.netgame.loc

Then copy .deploy/ubuntu/etc/nginx/netgame.conf to /etc/nginx/sites-enabled folder and change domains in that config to your own.

If you've changed deployment path, then change it in config.

Then restart nginx

## 3. Permissions

In order to run this demo, you need proper permissions on certain folders and files

cp data/games.csv-example data/games.csv
cp data/players.csv-example data/players.csv
touch data/sessions.csv
touch data/transactions.csv
chmod 0777 data/*.csv
chmod 0777 runtime/

## 4. Credentials

In order to run demo site you need a proper casino credentials such as login, url and secret. Request it from our managers and write down into file .env, as it showed in .env-example file

## 5. Check

If you've done all previous step, then you should get ready to work demo website with games and users. Just open you frontend domain in web browser and press play or demo at games in list 

# Step to integration

There's two way to integrate this code into your website.

## 1. Adapting

Yes, you can just copy and paste all code from common folder and then write your own implementation of sql-based repositories, instead of csv-based ones. Main benefit of this approach is that you get unchanged processing logic. 

## 2. Rewriting

You can take our Request and NetGameProvider classes as basis of your game logic and then rewrite it according to specific of your application. Main benefit - it will perfectly fits to you application.
