<?php

namespace Scottlaurent\Pulse\LaravelCommands;

class ReadSensor extends BaseCommand
{
	/**
	 * @var string
	 */
	protected $signature = 'pulse:sensor_read {sensor_names : Name of Sensor(s) -- Seprarated by commas if multiple.}';

	/**
	 * @var string
	 */
	protected $description = 'Read a Sensor';

	/**
	 *
	 */
	public function _handle()
    {
	    foreach (explode(',',$this->argument('sensor_names')) as $sensor_name) {

		    $value = $this->pulse->sensor($sensor_name)->get();

		    if (is_array($value)) {
			    $value = json_encode($value);
		    }

		    $this->info($sensor_name . ' -- Sensor Reading: ' . $value);
	    }

    }
}
