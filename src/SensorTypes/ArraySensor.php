<?php

namespace Scottlaurent\Pulse\SensorTypes;


/**
 * Class ArraySensor
 * @package Scottlaurent\Pulse\SensorTypes
 */
final class ArraySensor extends BaseSensor
{

	/**
	 *
	 */
	public function set($value=null)
	{
		$value_cache_key = $this->generateCacheKey('value');

		if ($this->model_object->ttl > 0) {
			$ttl = (int) ($this->model_object->ttl / 60);
			$this->cache_store->put($value_cache_key,$value,$ttl);
		} else {
			$this->cache_store->forever($value_cache_key,$value);
		}

		$this->flashBeacon();
		
		return $this->get();
	}

	/**
	 *
	 */
	public function get()
	{
		$value = $this->cache_store->get($this->generateCacheKey('value'));
		return $value ?: [];
	}

	/**
	 *
	 */
	public function clear()
	{
		$this->set([]);
		return $this->get();
	}

	/**
	 *
	 */
	public function push($value,$level='info')
	{
		$array = $this->get() ?: [];

		if ($value) {

			$array[] = [
				'value' => $value,
				'level' => $level,
				'updated_at' => now()->toDateTimeString()
			];
		}

		if ($max = $this->model_object->max) {
			$max = -1 * $max;
			$array = array_splice($array,$max);
		}

		return $this->set($array);
	}

	/**
	 *
	 */
	public function __toString()
	{
		return (array) $this->get();
	}
}