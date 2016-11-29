<?php

namespace Scottlaurent\Pulse\LaravelCommands;

class Heartbeat extends BaseCommand
{
	/**
	 * @var string
	 */
	protected $signature = 'pulse:heartbeat 
		{--sleep=1 : Time between heartbeats } 
		{--group=1 : Sensor group }
	';

	/**
	 * @var string
	 */
	protected $description = 'Sends out a pulse every second with sensor values in a group';

	/**
	 *
	 */
	public function _handle()
    {
	    $this->pulse->heartbeat($this->option('group'),$this->option('sleep'));
    }
}
