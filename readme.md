### Enable Scheduled Verification

First, make sure the scheduled task is running :

```
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

and then, run the worker :

```
$ php artisan queue:work
```
