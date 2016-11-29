<?php

namespace Scottlaurent\Pulse\ServiceProviders;

use Illuminate\Support\ServiceProvider;
use Scottlaurent\Pulse\LaravelCommands as Commands;

/**
 * Class LaravelServiceProviderHelpers
 * @package Scottlaurent\Pulse\ServiceProviders
 */
class LaravelServiceProviderHelpers extends ServiceProvider
{
	
	/**
	 * Include some useful helpers
	 * @return void
	 */
	public function boot()
	{
		include_once(__DIR__ . '/../Helpers/LaravelHelpers.php');
	}
	
	/**
	 * Register the application services.
	 */
	public function register()
	{
		//
	}
}