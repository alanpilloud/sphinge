## Minimal requirements

 - php 7.0
 - mysql 5.7 (mariadb won't work for the moment)

### Enable Scheduled Verification

First, make sure the scheduled task is running :

```
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

and then, run the worker :

```
$ php artisan queue:work
```

Since queue workers are long-lived processes, they will not pick up changes to your code without being restarted.
So, the simplest way to deploy an application using queue workers is to restart the workers during your deployment process.
You may gracefully restart all of the workers by issuing the queue:restart command:

```
$ php artisan queue:restart
```

### Shared host configuration

If you are running Sphinge on a shared host, you can run a scheduled task on this url :

```
http://localhost:8000/api/websites/sync
```

As a measure of security, this url can't be run on a webbrowser.
