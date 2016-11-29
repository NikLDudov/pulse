<?php

namespace Scottlaurent\Pulse\LaravelCommands;
use Scottlaurent\Pulse\Pulse;
use Carbon\Carbon;
use Illuminate\Console\Command;


/**
 * Class BaseCommand
 * @package Scottlaurent\Pulse\LaravelCommands
 */
class BaseCommand extends Command
{

	/**
	 * @var Carbon
	 */
	public $start_time;

	/**
	 * @var Carbon
	 */
	public $end_time;

	/**
	 * @var Pulse
	 */
	public $pulse;

	/**
	 * BaseCommand constructor.
	 */
	public function __construct()
    {
        parent::__construct();

	    $this->pulse = new \Scottlaurent\Pulse\Pulse();
    }

	/**
	 *
	 */
	public function handle()
    {
	   $this->start();

	   $this->_handle();

	   $this->finish();
    }

	/**
	 *
	 */
	protected function start() {

		$this->start_time = Carbon::now();

		$this->info("Starting at ".$this->start_time->toDateTimeString()."...");

	}

	/**
	 *
	 */
	protected function finish()
    {
	    $this->end_time = Carbon::now();

        $this->info('...Finished at ' . $this->end_time->toDateTimeString());

        $this->info(
            " Process Time: "
            . $this->start_time->diffInSeconds($this->end_time)
        );
    }
}