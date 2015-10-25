<?php namespace DuyDucNguyen\Database\Traits;

use Cache;
use Illuminate\Database\Eloquent\Model;

trait SluggableTrait {
	/**
	 * Boot the sluggable trait for a model. 
	 * (Eloquent Model has bootTraits method in order to boot all traits with prefix 'boot')
	 *
	 * @return void
	 */
	public static function bootSluggable()
	{
		$sluggableCol = static::getSluggableColumn();
		$slugCol = static::getSlugColumn();

		static::registerModelEvent('saving', function(Model $model) use ($sluggableCol, $slugCol)
		{
			//Check sluggable column is does dirty
			if ($model->isDirty($sluggableCol)) {
				$slug = str_slug($model->$sluggableCol);
				$lastSlug = static::getLastSlug($slug, $slugCol);

				if ($lastSlug) {
					$slug = static::generateUniqueSlug($slug, $lastSlug);
				}

				$model->$slugCol = $slug;
			}
		}, 0);

		if (static::needCache()) {
			//cache slug
			static::cacheSlug();
			//delete slug in cache when deleted record
			static::registerModelEvent('deleted', function(Model $model) use ($slugCol)
			{
				Cache::forget(static::class . ':' . $model->$slugCol, $model->id);
			}, 0);

		}
	}

	/**
	 * Get the name of the sluggable column.
	 *
	 * @return string
	 */
	public static function getSluggableColumn()
	{
		if (!defined('static::SLUGGABLE_COLUMN')) {
			throw new Exception("Please specify sluggable column const");
		}

		return static::SLUGGABLE_COLUMN;
	}

	/**
	 * Get the name of the slug column.
	 *
	 * @return string
	 */
	public static function getSlugColumn()
	{
		if (!defined('static::SLUG')) {
			throw new Exception("Please specify slug column const");
		}

		return static::SLUG;
	}

	/**
	 * Do we need to cache slug?
	 * @return bool
	 */
	public static function needCache()
	{
		return defined('static::ENABLE_SLUG_CACHE') ? static::ENABLE_SLUG_CACHE : false;
	}

	/**
	 * Cache for model. Use to get the id of record by it's slug.
	 * @param  sring $slug 
	 * @return void
	 */
	public static function cacheSlug()
	{
		$slugCol = static::getSlugColumn();

		static::registerModelEvent('saved', function(Model $model) use ($slugCol)
		{
			if ($model->isDirty($slugCol)) {
				Cache::forever(static::class . ':' . $model->$slugCol, $model->id);
			}
		}, 0);
	}

	/**
	 * Find the slug and get the lastest inserted slug.
	 * @param  string $slug    
	 * @param  string $slugCol Name of the slug column.
	 * @return string|false          
	 */
	public static function getLastSlug($slug, $slugCol)
	{
		$lastSlug = static::whereRaw("$slugCol REGEXP '^{$slug}(-[0-9]*)?$'")
					->orderBy($slugCol, 'desc')
					->first();
 

		if (isset($lastSlug->$slugCol)) {
			return $lastSlug->$slugCol;
		}	

		return false;

	}

	/**
	 * Generate unique slug
	 * @param  string $slug     
	 * @param  string $lastSlug Last slug in database
	 * @return string           
	 */
	public static function generateUniqueSlug($slug, $lastSlug)
	{
		return "{$slug}-" . ((intval(str_replace("{$slug}-", '', $lastSlug))) + 1); //Generate unique slug by increase the suffix number
	}

	/**
	 * Get slug of record
	 * @return string
	 */
	public function getSlug()
	{
		$slugCol = static::getSlugColumn();
		return $this->$slugCol;
	}
}