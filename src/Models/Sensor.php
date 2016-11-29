<?php

namespace Scottlaurent\Pulse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Cache;
use Validator;

/**
 * Class Sensor
 * @package Scottlaurent\Pulse\Models
 */
class Sensor extends Model
{
	/**
	 * @var int
	 */
	static $default_group_id = 1;

	/**
	 * @var int
	 */
	static $default_ttl = -1;

	/**
	 * @var string
	 */
    protected $table = 'pulse_sensors';

	/**
	 * @var array
	 */
	protected $rules = [
		'ttl' => 'required',
		'name' => 'required|min:8|unique:pulse_sensors,name',
		'slug' => 'required|unique:pulse_sensors,slug',
		'field_type' => 'required'
	];

	/**
	 * @var array
	 */
	protected $guarded = ['id'];

	/**
	 *
	 */
	protected static function boot()
    {
        parent::boot();

        static::creating(function ($object) {

	        $object->group_id = $object->group_id ?: self::$default_group_id;
	        $object->slug = self::slugify($object->name);
	        $object->cache_key_base = self::generateCacheKeyBase($object->group_id, $object->slug);
	        $object->ttl = $object->ttl ?: self::$default_ttl;

	        $object->validate();
        });

        static::updated(function ($object) {
	        self::cache_store()->forever($object->cache_key_base.':settings',$object);
        });

        static::saved(function ($object) {
	        self::cache_store()->forever($object->cache_key_base.':settings',$object);
        });
    }

	/**
	 * @return mixed
	 */
	protected static function cache_store() {
		return Cache::store(config('cache.pulse_store'));
	}

	/**
	 * @param $slug
	 * @param null $group_id
	 * @return mixed
	 * @throws
	 */
	public static function getBySensorSlug($slug, $group_id = null)
	{
		$group_id = $group_id ?: self::$default_group_id;
		if ($sensor = self::cache_store()
			->rememberForever(
				self::generateCacheKeyBase($group_id, $slug) . ':settings',
				function() use($group_id, $slug) {
					return self::where(['group_id'=>$group_id,'slug'=>$slug])->first();
				}
			)
		) {
			return $sensor;
		}
	}
	
	
	/**
	 * @param $name
	 * @param null $group_id
	 * @return mixed
	 */
	public static function getBySensorName($name, $group_id = null)
	{
		return self::getBySensorSlug(self::slugify($name),$group_id);
	}

	/**
	 * @param $group_id
	 * @param $slug
	 * @return mixed
	 */
	static function generateCacheKeyBase($group_id, $slug) {
		return implode(':',['pulse','sensors','group',$group_id,'sensor',$slug]);
	}
	
	
	/**
	 * @throws ValidationException
	 */
	public function validate()
    {
	    $validation_values_array = [];
	    foreach ($this->rules as $field=>$rule) {
		    $validation_values_array[$field] = $this->$field;
	    }
        $validator = Validator::make($validation_values_array, $this->rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
	
	/**
	 * Slugify our group names
	 *
	 * @param $text
	 * @return string
	 */
	static public function slugify($text)
	{
		// replace non letter or digits by -
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);

		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);

		// trim
		$text = trim($text, '-');

		// remove duplicate -
		$text = preg_replace('~-+~', '-', $text);

		// lowercase
		$text = strtolower($text);
		if (empty($text))
		{
			return 'n-a';
		}
		return $text;
	}


}
