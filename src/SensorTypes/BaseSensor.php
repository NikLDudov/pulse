<?php

namespace Scottlaurent\Pulse\SensorTypes;

use Scottlaurent\Pulse\Pulse;

/**
 * Class BaseSensor
 * @package Scottlaurent\Pulse\SensorTypes
 */
abstract class BaseSensor
{
	
	/**
	 * @var
	 */
	var $model_object;
	
	/**
	 * @var
	 */
	var $cache_store;
	

	/**
	 * BaseSensor constructor.
	 * @param $model_object
	 * @param $cache_store
	 */
	public function __construct($model_object, $cache_store)
	{
		$this->model_object = $model_object;
		$this->cache_store = $cache_store;
	}

	/**
	 *
	 */
	public function get()
	{
		return $this->cache_store->get($this->generateCacheKey('value'),$this->model_object->default_value);
	}

	/**
	 *
	 */
	public function clear()
	{
		$this->set(null);
		return $this->get();
	}
	/**
	 *
	 */
	public function reset()
	{
		$this->set($this->model_object->default_value);
		return $this->get();
	}
	
	/**
	 * @param $key_name
	 * @return string
	 */
	public function generateCacheKey($key_name) {
		return $this->model_object->cache_key_base . ':' . $key_name;
	}
	
	/**
	 *
	 */
	public function flashBeacon()
	{
		$pulse = new Pulse();
		$pulse->startHeartbeat(null,$this->model_object->group_id);
	}

}