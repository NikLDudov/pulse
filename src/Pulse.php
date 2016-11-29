<?php

namespace Scottlaurent\Pulse;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Cache;
use Exception;
use Pusher;

/**
 * Class Pulse
 *
 * @package   Scottlaurent\Pulse
 * @author    Scott Laurent <scott@baselineapplications.com>
 * @license   http://opensource.org/licenses/MIT MIT
 */
class Pulse
{

	/**
	 * @var string
	 */
	static $model_name = '\\Scottlaurent\\Pulse\\Models\\Sensor';

	/**
	 * @var \Scottlaurent\Pulse\Models\Sensor
	 */
	protected $model;

	/**
	 * @var \Illuminate\Support\Facades\Cache
	 */
	protected $cache_store;

	/**
	 * @var Pusher
	 */
	public $broadcaster;
	
	
	/**
	 * Pulse constructor.
	 * @param null $model
	 * @param null $cache_store
	 * @param null $broadcaster
	 */
	public function __construct($model=null, $cache_store=null, $broadcaster=null)
	{
		$this->model = $model ?: new static::$model_name;
		$this->cache_store = $cache_store ?: Cache::store(config('cache.pulse_store'));
		$this->broadcaster = $broadcaster ?: $this->generatePusherBroadcaster();
	}

	/**
	 * Start broadcasting a pulse.
	 *
	 * Start the heartbeat pulse.  Normally when a listeners/dashboards loads it will start
	 * with maybe 5 heartbeats to make sure it picks up the most recent pulse.
	 * Otherwise, everytime a value changes, this runs as well, but only needs to run once
	 * which effectively updates all listeners/dashboards.
	 *
	 * @param int $beats
	 * @param int $group_id
	 */
	public static function startHeartbeat($beats=null, $group_id=1) {
		$self = (new self);
		$cache_store = $self->cache_store;
		$heartbeats_remaining = $cache_store->get('pulse:remaining:heartbeats:group:'.$group_id);
		$beats = $heartbeats_remaining > $beats ? $heartbeats_remaining : $beats;
		$beats = $beats ?: 1;
		(new self)->cache_store->forever('pulse:remaining:heartbeats:group:'.$group_id,$beats);
	}

	/**
	 *
	 * The "heartbeat" -- Broadcast a pulse.
	 *
	 * This should be set to run every minute in cron. This exact same logic
	 * could be replicated on another system or in javascript running on Node.
	 *
	 *
	 * @param int $group_id
	 * @param int $frequency
	 * @throws Exception
	 */
	public static function heartbeat($group_id = 1, $frequency = 5) {

		$self = (new self);
		$cache_store = $self->cache_store;
		$broadcaster = $self->broadcaster;

		// this process should not run more than 60 seconds
		$end_time =  Carbon::now()->addMinute(1);

		while (Carbon::now() < $end_time) {

			// check to make sure we have the need to send out a pulse
			// this will conserve massive amounts of resources in slow periods where there are few updates

			if ($heartbeats_remaining = $cache_store->get('pulse:remaining:heartbeats:group:'.$group_id)) {


				// generate a payload for our broadcaster
				$payload = [
					'packaged_at' => Carbon::now()->toDateTimeString()
				];

				foreach (self::getSensorListByGroupId($group_id) as $sensor) {
					$payload['sensors'][str_replace('-', '_', $sensor['slug'])] = [
						'structure' => [
							'field_type' => $sensor['field_type'],
							'max' => $sensor['max'],
							'min' => $sensor['min'],
						],
						'value' => self::sensor($sensor['slug'], $group_id)->get()
					];
				}

				// use the broadcaster to send the event over websockts to our listeners/dashboards
				$broadcaster->trigger('pulse_group_' . $group_id, 'heartbeat', $payload);

				$heartbeats_remaining--;
				$cache_store->forever('pulse:remaining:heartbeats:group:' . $group_id,$heartbeats_remaining);
			}

			sleep ($frequency);
		}
	}
	
	
	/**
	 * @param $name
	 * @param int $group_id
	 * @return mixed
	 * @throws Exception
	 */
	public static function sensor($name, $group_id=1) {

		$self = new self;
		$model_object = $self->model->getBySensorSlug($name,$group_id) ?: $self->model->getBySensorName($name,$group_id);

		if ($model_object) {
			$class_name =
				'\\Scottlaurent\\Pulse\\SensorTypes\\'
				. studly_case($model_object->field_type)
				. 'Sensor';
			return new $class_name($model_object,$self->cache_store);
		}

		throw new Exception('Could not locate sensor object ' . $name);
	}


	/**
	 * Do resets of data
	 */
	public static function resetSensorsBasedOnResetEach($reset_each)
	{
		foreach ((new self)->model->where('reset_each',$reset_each)->get() as $sensor_definition) {
			if ($sensor = self::sensor($sensor_definition->name,$sensor_definition->group_id)) {
				$sensor->reset();
			}
		}
	}

	/**
	 * @param int $group_id
	 * @throws Exception
	 */
	public static function resetGroup($group_id=1) {
		foreach ((new self)->model->where('group_id',$group_id)->get() as $sensor_definition) {
			if ($sensor = self::sensor($sensor_definition->name,$sensor_definition->group_id)) {
				$sensor->reset();
			}
		}
	}

	/**
	 * @param $name
	 * @param array $parameters
	 * @return string
	 * @throws Exception
	 */
	public function createNumberSensor($name,$parameters=[])
	{
		$parameters = is_array($name) ? $name : ['name'=>$name] + $parameters;
		$parameters['field_type'] = 'number';
		$this->createSensor($parameters);
		return (string) $this->sensor($name)->reset();
	}

	/**
	 * @param $parameters
	 * @return mixed
	 * tinker:
	 */
	public function createArraySensor($name,$parameters=[])
	{
		$parameters = is_array($name) ? $name : ['name'=>$name] + $parameters;
		$parameters['field_type'] = 'array';
		$this->createSensor($parameters);
		return (array) $this->sensor($name)->reset();
	}

	/**
	 * @param $group_id
	 * @return mixed
	 */
	public static function getSensorListByGroupId($group_id) {
		return (new self)->cache_store->get('pulse:sensors:group:'.$group_id.':sensors');
	}

	/**
	 * @param $parameters
	 * @return array|static
	 */
	public function createSensor($parameters)
	{
		try {
			$sensor_object = $this->model->create($parameters);
			$this->rebuildGroup($sensor_object->group_id);
			return $sensor_object;
		} catch (ValidationException $e) {
			dd($e->validator->errors()->getMessages());
		}
	}

	/**
	 *
	 */
	public function rebuildGroup($group_id=1)
	{
		$group_base_cache_keys = [];
		foreach ($this->model->where('group_id',$group_id)->get() as $sensor_object) {
			$group_base_cache_keys[] = $sensor_object->toArray();
		}
		$this->cache_store->forever('pulse:sensors:group:'.$group_id.':sensors',$group_base_cache_keys);
		return $this->getSensorListByGroupId($group_id);
	}

	/**
	 *
	 */
	private function generatePusherBroadcaster() {
		$config = config('broadcasting.connections')['pusher'];
		return new \Pusher($config['key'], $config['secret'], $config['app_id']);
	}
}