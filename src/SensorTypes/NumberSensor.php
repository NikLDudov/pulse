<?php

namespace Scottlaurent\Pulse\SensorTypes;


/**
 * Class NumberSensor
 * @package Scottlaurent\Pulse\SensorTypes
 */
/**
 * Class NumberSensor
 * @package Scottlaurent\Pulse\SensorTypes
 */
final class NumberSensor extends BaseSensor
{

	/**
	 *
	 */
	public function set($value=null)
	{
		$level = $this->determineLevel($value);
		$value_array = [
			'value' => $value,
			'level' => $level,
			'updated_at' => now()->toDateTimeString()
		];
		if ($this->model_object->ttl > 0) {
			$ttl = (int) ($this->model_object->ttl / 60);
			$this->cache_store->put($this->generateCacheKey('value'),$value_array,$ttl);
		} else {
			$this->cache_store->forever($this->generateCacheKey('value'),$value_array);
		}

		$this->flashBeacon();
		
		return $this->get();
	}

	/**
	 * @param $value
	 * @return string
	 */
	public function determineLevel($value) {

		if ($value==0) {
			$level = 'info';
		} else {
			$level = array_rand(['danger','warning','success'],1);
		}

		return $level;
	}
	
	
	/**
	 * Increments a sensor by a given value
	 *
	 * @param int $incremental
	 * @return mixed
	 */
	public function increment($incremental = 1)
	{
		if ($value = $this->get()) {
			$new_value = $value['value'] + $incremental;
		} else {
			$new_value = $incremental;
		}

		if ($this->model_object->max && $new_value > $this->model_object->max) {
			$new_value = $this->model_object->max;
		}

		return $this->set($new_value);
	}
	
	
	/**
	 * Decrements a sensor by a given value
	 *
	 * @param int $decremental
	 * @return mixed
	 */
	public function decrement($decremental = 1)
	{
		if ($value = $this->get()) {
			$new_value = $value['value'] - $decremental;
		} else {
			$new_value = 0 - $decremental;
		}

		if ($this->model_object->min && $new_value < $this->model_object->min) {
			$new_value = $this->model_object->min;
		}

		return $this->set($new_value);
	}

	/**
	 *
	 */
	public function __toString()
	{
		return (string) $this->get();
	}
}
