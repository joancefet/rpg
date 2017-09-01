# pokemon-rpg

Import the .sql file into your database and edit the values of settings to your need

Define database values in scheduler/cronConfig.php
Define database values in includes/config.php

Replace ```#serverip#``` with the ip of your server in the .htaccess file in scheduler/

Define the following crons and replace yourdomain with your full domain name i.e. https://pokeworld.nl 
Minute/Hour/Day/Month/Weekday

```
0 1 * * *       "/usr/bin/wget -O - yourdomain/scheduler/cron_day.php >/dev/null"
0 4 * * *       "/usr/bin/wget -O - yourdomain/scheduler/cron_backup.php >/dev/null"
0 0 * *	*       "/usr/bin/wget -O - yourdomain/scheduler/cron_daycare.php >/dev/null"
0 3 * *	*       "/usr/bin/wget -O - yourdomain/scheduler/cron_log_chat.php >/dev/null"
0 0,12 * * *    "/usr/bin/wget -O - yourdomain/scheduler/cron_market.php >/dev/null"	
```

There are three styles you can choose from, just change the style inclusion within index.php on line 327 to one of the following:

```
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="css/style-christmas.css" />
<link rel="stylesheet" type="text/css" href="css/style-spring.css" />
```
