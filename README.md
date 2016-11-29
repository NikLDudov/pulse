## Laravel Pulse
A very simple monitoring system that could assist me in generating real-time websocket dashboards for client projects.  The needs were as follows:
1. 100% cache based.  No DB hits.  
2. Easy setting and resetting of data
3. Implementation with Pusher to push data out on a regular basis

This library does this in a very simple way.
1. You add "sensors" via the db or php artisan commands.  Sensors can be arrays or numbers.
2. You then put the sensors wherever you want to include them in your code
3. You set up a cron for the heartbeat (and also the setting or resetting of data if you want)
4. You set up an html page that listens for the "heartbeat"

## Installation

#### 1. Run the migration
#### 2. Add this to your config app.php
    Scottlaurent\Pulse\ServiceProviders\LaravelServiceProvider::class
#### 3. Optional: Add this to your config app.php to load the included helpers
	Scottlaurent\Pulse\ServiceProviders\LaravelServiceProviderHelpers::class

## Client Side
```
	<script src="https://js.pusher.com/3.0/pusher.min.js"></script>
    <script>
        var pusher = new Pusher('YOUR-KEY');
		var pusherChannel_1 = pusher.subscribe('pulse_group_1');
	    pusherChannel_1.bind('heartbeat', function(event) {
            console.log(event);
		});
	</event>
```

## Basic Server-Side Usage
```php
    // from the command line, build a sensor.  Behind the scenes, this simply adds a table to the database and regenerates the cache of sensors
    >> php artisan pulse:create_sensor "Conversions Today"
    
    <?php
    
        // set the value manually
        sensor('conversions-today')->set(leads()->createdToday()->count());
        
        // or just increment by 1
        sensor('conversions-today')->increment();
```
## Advanced Server-Side Usage
```php

// Sample cron to push all events in group 1 for one minute, resting each second between pushes (ie: 60 times per minute)
$schedule
    ->command('pulse:heartbeat --group 1 --sleep 1')
    ->everyMinute();

// Sample cron to reset all hourly-based sensors (you can use hourly, daily, weekly, monthly, yearly)
$schedule->call(function () {
    pulse()->resetSensorsBasedOnResetEach('hourly');
})->hourly();

$schedule->call(function () {
    pulse()->resetSensorsBasedOnResetEach('daily');
})->daily();

// this is an example that pushes items into a sensor array on a regular basis (useful for known how many people are currently online or something similar).
$schedule->call(function () {
    sensor_reset('agents-in-session');
    foreach (\App\Models\Users\Agents::all() as $agent) {
        sensor_push('agents-in-session',$agent->full_name);
    }
})->everyMinute();

```

## Known Limitations
Right now, the code works only with Pusher (it uses Pusher's syntax).  I should probably use broadcasters that use laravel's broadcasters interface, but just didn't need to yet, as we have historically used Pusher as our broadcaster.




## Contributors
Scott Laurent, Michael Paas

## License

MIT