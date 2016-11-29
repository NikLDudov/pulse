<?php

namespace Scottlaurent\Pulse\ServiceProviders;

use Illuminate\Support\ServiceProvider;
use Scottlaurent\Pulse\Pulse;
use Scottlaurent\Pulse\LaravelCommands as Commands;

/**
 * Class LaravelServiceProvider
 * @package Scottlaurent\Pulse\ServiceProviders
 */
class LaravelServiceProvider extends ServiceProvider
{

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
		Commands\CreateSensor::class,
	    Commands\ReadSensor::class,
	    Commands\Heartbeat::class,
    ];

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->commands($this->commands);
	}
	
	/**
	 * Register the application services.
	 * @return Pulse
	 * @throws \Exception
	 */
	public function register()
	{
		$this->app->bind('pulse', function () {
			return new Pulse();
		});
	}
	
	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['pulse'];
	}
	
}