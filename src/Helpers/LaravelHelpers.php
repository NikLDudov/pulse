<?php

/**
 * @param null $sensor_name
 * @return \Illuminate\Foundation\Application|mixed
 */
function pulse($sensor_name = null) {
	return $sensor_name ? app('pulse')->sensor($sensor_name) : app('pulse');
}

/**
 * @param int $beats
 */
function pulse_start($group_id=1,$beats=5)
{
	pulse()->startHeartbeat($beats,$group_id);
}

/**
 * @param $sensor_name
 * @return mixed
 */
function sensor($sensor_name) {
	return app('pulse')->sensor($sensor_name);
}

/**
 * @param $sensor_name
 * @return mixed
 */
function sensor_reading($sensor_name) {
	return app('pulse')->sensor($sensor_name)->get();
}

/**
 * @param $sensor_names
 * @return bool
 */
function sensor_increment($sensor_names) {

	try {

		arrayify($sensor_names);

		foreach ($sensor_names as $sensor_name) {
		    $sensor = sensor($sensor_name);
			$sensor->increment();
		}

	} catch (Exception $e) {

		return false;

	}

	return true;
}

/**
 * @param $sensor_names
 * @return bool
 */
function sensor_reset($sensor_names) {

	try {

		arrayify($sensor_names);

		foreach ($sensor_names as $sensor_name) {
		    $sensor = sensor($sensor_name);
			$sensor->reset();
		}

	} catch (Exception $e) {

		return false;

	}

	return true;
}

/**
 * @param $sensor_names
 * @return bool
 */
function sensor_decrement($sensor_names) {

	try {

		arrayify($sensor_names);

		foreach ($sensor_names as $sensor_name) {
		    $sensor = sensor($sensor_name);
			$sensor->decrement();
		}

	} catch (Exception $e) {

		return false;

	}

	return true;
}

/**
 * @param $sensor_names
 * @return bool
 */
function sensor_push($sensor_names,$value) {

	try {

		arrayify($sensor_names);

		foreach ($sensor_names as $sensor_name) {
		    $sensor = sensor($sensor_name);
			$sensor->push($value);
		}

	} catch (Exception $e) {

		return false;

	}

	return true;
}

