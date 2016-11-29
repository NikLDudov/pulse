<?php

namespace Scottlaurent\Pulse\LaravelCommands;

class CreateSensor extends BaseCommand
{
	/**
	 * @var string
	 */
	protected $signature = 'pulse:create_sensor {name : Name of Sensor}';

	/**
	 * @var string
	 */
	protected $description = 'Create a new Sensor';

	/**
	 *
	 */
	public function _handle()
    {
	    $field_type = $this->choice('What type of sensor?', ['number', 'array'], 0);

	    $group_id = $this->ask('Do you want to add this sensor to a particular group ID?.  If not, hit enter.',1);

	    $default_value = null;

		if ($field_type == 'number') {
			$min = $this->ask('What is the minimum value allowed. Hit enter for 62-bit default.',-pow(2,62));
			$max = $this->ask('What is the maximum value allowed. Hit enter for 62-bit default.',pow(2,62));
			$default_value = $this->ask('What is the default value to be set whenever this sensor is reset? Hit enter for default.',0);
		}

		if ($field_type == 'array') {
			$max = $this->ask('What is the maximum array length allowed. The default is 25. Setting this too high can cause your pulse pushes to grow in size and cause packet loss.',25);
		}

	    $this->info('A Time To Live (TTL) setting is useful when you want a sensor to persist until a period of time passes without activity in which case it will die out.  This is different than forcing a reset of the value on a periodic basis.  A TTL sensor could be used for example to let you know if any event occurred more than X times in 5 minutes.  Because after 5 minutes if there was no activity, the value would simply null out and start over.');

	    $ttl = $this->ask('If you want to set a TTL, enter it now, or just hit enter',-1);

	    $reset_each = $this->choice('Would you like to reset the value of this sensor on a period basis?  If not, hit enter.', [
		    'never',
	        'hourly',
	        'daily',
	        'weekly',
		    'monthly',
	        'yearly'
	    ],0);

		$parameters = [
			'name' => $this->argument('name'),
			'default_value' => $default_value,
			'field_type' => $field_type,
			'min' => isset($min) ? $min : null,
			'max' => isset($max) ? $max : null,
			'ttl' => isset($ttl) ? $ttl : null,
			'reset_each' => isset($reset_each) ? $reset_each : null,
			'group_id' => isset($group_id) ? $group_id : null
		];

	    $sensor = $this->pulse->createSensor($parameters);

	    $this->info(studly_case($sensor->field_type) . " sensor '" . $sensor->name . "' (" . $sensor->slug . ") created");

	    if ($field_type == 'number') {
		    $this->info("USAGE: sensor_increment('" . $sensor->slug . "');");
	    }
	    
	    if ($field_type == 'array') {
		    $this->info("USAGE: sensor_push('" . $sensor->slug . "','some value');");
	    }
    }
}
