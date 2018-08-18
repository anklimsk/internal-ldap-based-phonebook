<?php
App::uses('ModelBehavior', 'Model');
App::uses('GeocodeLib', 'Tools.Lib');

/**
 * A geocoding behavior for CakePHP to easily geocode addresses.
 * Uses the GeocodeLib for actual geocoding.
 * Also provides some useful geocoding tools like validation and distance conditions.
 *
 * Note that your lat/lng fields should be of type "float(10,6) DEFAULT NULL".
 * NULL as default is important as invalid or not found addresses should result in NULL
 * instead of 0.0 (which is a truthy value!).
 * If you need 0.0, cast it in your beforeSave() callback.
 *
 * @author Mark Scherer
 * @licence MIT
 * @link http://www.dereuromark.de/2012/06/12/geocoding-with-cakephp/
 */
class GeocoderBehavior extends ModelBehavior {

	protected $_defaultConfig = [
		'real' => false, 'address' => ['street', 'postal_code', 'city', 'country'],
		'require' => false, 'allowEmpty' => true, 'invalidate' => [], 'expect' => [],
		'lat' => 'lat', 'lng' => 'lng', 'formatted_address' => 'formatted_address',
		'host' => null, 'language' => 'de', 'region' => '', 'bounds' => '',
		'overwrite' => false, 'update' => [], 'before' => 'save',
		'min_accuracy' => GeocodeLib::ACC_COUNTRY, 'allow_inconclusive' => true, 'unit' => GeocodeLib::UNIT_KM,
		'log' => true, // log successfull results to geocode.log (errors will be logged to error.log in either case)
		'params' => [], // Array of parameters to pass to GeocoderLib
	];

	public $Geocode;

	/**
	 * Initiate behavior for the model using specified settings. Available settings:
	 *
	 * - address: (array | string, optional) set to the field name that contains the
	 * 			string from where to generate the slug, or a set of field names to
	 * 			concatenate for generating the slug.
	 *
	 * - real: (boolean, optional) if set to true then field names defined in
	 * 			label must exist in the database table. DEFAULTS TO: false
	 *
	 * - expect: (array)postal_code, locality, sublocality, ...
	 *
	 * - accuracy: see above
	 *
	 * - override: lat/lng override on changes?
	 *
	 * - update: what fields to update (key=>value array pairs)
	 *
	 * - before: validate/save (defaults to save)
	 * 			set to false if you only want to use the validation rules etc
	 *
	 * @param Model $Model The model using the behaviour
	 * @param array $config Settings to override for model.
	 */
	public function setup(Model $Model, $config = []) {
		$this->settings[$Model->alias] = $this->_defaultConfig;
		$this->settings[$Model->alias] = $config + $this->settings[$Model->alias];
	}

	public function beforeValidate(Model $Model, $options = []) {
		parent::beforeValidate($Model, $options);

		if ($this->settings[$Model->alias]['before'] === 'validate') {
			return $this->geocode($Model);
		}

		return true;
	}

	public function beforeSave(Model $Model, $options = []) {
		parent::beforeSave($Model, $options);

		if ($this->settings[$Model->alias]['before'] === 'save') {
			return $this->geocode($Model);
		}

		return true;
	}

	/**
	 * Run before a model is saved, used to set up slug for model.
	 *
	 * @param Model $Model Model about to be saved.
	 * @return bool True if save should proceed, false otherwise
	 */
	public function geocode(Model $Model, $return = true) {
		// Make address fields an array
		if (!is_array($this->settings[$Model->alias]['address'])) {
			$addressfields = [$this->settings[$Model->alias]['address']];
		} else {
			$addressfields = $this->settings[$Model->alias]['address'];
		}
		$addressfields = array_unique($addressfields);

		// Make sure all address fields are available
		if ($this->settings[$Model->alias]['real']) {
			foreach ($addressfields as $field) {
				if (!$Model->hasField($field)) {
					return $return;
				}
			}
		}

		$addressData = [];
		foreach ($addressfields as $field) {
			if (!empty($Model->data[$Model->alias][$field])) {
				$addressData[] = $Model->data[$Model->alias][$field];
			}
		}

		$Model->data[$Model->alias]['geocoder_result'] = [];

		if ((!$this->settings[$Model->alias]['real'] || ($Model->hasField($this->settings[$Model->alias]['lat']) && $Model->hasField($this->settings[$Model->alias]['lng']))) &&
			($this->settings[$Model->alias]['overwrite'] || empty($Model->data[$Model->alias][$this->settings[$Model->alias]['lat']]) || ((int)$Model->data[$Model->alias][$this->settings[$Model->alias]['lat']] === 0 && (int)$Model->data[$Model->alias][$this->settings[$Model->alias]['lng']] === 0))
		) {
			if (!empty($Model->whitelist) && (!in_array($this->settings[$Model->alias]['lat'], $Model->whitelist) || !in_array($this->settings[$Model->alias]['lng'], $Model->whitelist))) {
				return $return;
			}
		}

		$geocode = $this->_geocode($addressData, $this->settings[$Model->alias]);

		if (empty($geocode) && !empty($this->settings[$Model->alias]['allowEmpty'])) {
			return true;
		}
		if (empty($geocode)) {
			return false;
		}

		// If both are 0, thats not valid, otherwise continue
		if (empty($geocode['lat']) && empty($geocode['lng'])) {
			// Prevent 0 inserts of incorrect runs
			if (isset($Model->data[$Model->alias][$this->settings[$Model->alias]['lat']])) {
				unset($Model->data[$Model->alias][$this->settings[$Model->alias]['lat']]);
			}
			if (isset($Model->data[$Model->alias][$this->settings[$Model->alias]['lng']])) {
				unset($Model->data[$Model->alias][$this->settings[$Model->alias]['lng']]);
			}
			if ($this->settings[$Model->alias]['require']) {
				if ($fields = $this->settings[$Model->alias]['invalidate']) {
					$Model->invalidate($fields[0], $fields[1], isset($fields[2]) ? $fields[2] : true);
				}
				return false;
			}
			return true;
		}

		// Valid lat/lng found
		$Model->data[$Model->alias][$this->settings[$Model->alias]['lat']] = $geocode['lat'];
		$Model->data[$Model->alias][$this->settings[$Model->alias]['lng']] = $geocode['lng'];

		if (!empty($this->settings[$Model->alias]['formatted_address'])) {
			$Model->data[$Model->alias][$this->settings[$Model->alias]['formatted_address']] = $geocode['formatted_address'];
		} else {
			if (isset($Model->data[$Model->alias][$this->settings[$Model->alias]['formatted_address']])) {
				unset($Model->data[$Model->alias][$this->settings[$Model->alias]['formatted_address']]);
			}
		}

		$Model->data[$Model->alias]['geocoder_result'] = $geocode;
		$Model->data[$Model->alias]['geocoder_result']['address_data'] = implode(' ', $addressData);

		if (!empty($this->settings[$Model->alias]['update'])) {
			foreach ($this->settings[$Model->alias]['update'] as $key => $field) {
				if (!empty($geocode[$key])) {
					$Model->data[$Model->alias][$field] = $geocode[$key];
				}
			}
		}

		return $return;
	}

	/**
	 * Adds the distance to this point as a virtual field.
	 *
	 * @param Model $Model
	 * @param string|float $lat Fieldname (Model.lat) or float value
	 * @param string|float $lng Fieldname (Model.lng) or float value
	 * @return void
	 */
	public function setDistanceAsVirtualField(Model $Model, $lat, $lng, $modelName = null) {
		$Model->virtualFields['distance'] = $this->distance($Model, $lat, $lng, null, null, $modelName);
	}

	/**
	 * Forms a sql snippet for distance calculation on db level using two lat/lng points.
	 *
	 * @param string|float $lat Fieldname (Model.lat) or float value
	 * @param string|float $lng Fieldname (Model.lng) or float value
	 * @return string
	 */
	public function distance(Model $Model, $lat, $lng, $fieldLat = null, $fieldLng = null, $modelName = null) {
		if ($fieldLat === null) {
			$fieldLat = $this->settings[$Model->alias]['lat'];
		}
		if ($fieldLng === null) {
			$fieldLng = $this->settings[$Model->alias]['lng'];
		}
		if ($modelName === null) {
			$modelName = $Model->alias;
		}

		$value = $this->_calculationValue($this->settings[$Model->alias]['unit']);

		return $value . ' * ACOS(COS(PI()/2 - RADIANS(90 - ' . $modelName . '.' . $fieldLat . ')) * ' .
			'COS(PI()/2 - RADIANS(90 - ' . $lat . ')) * ' .
			'COS(RADIANS(' . $modelName . '.' . $fieldLng . ') - RADIANS(' . $lng . ')) + ' .
			'SIN(PI()/2 - RADIANS(90 - ' . $modelName . '.' . $fieldLat . ')) * ' .
			'SIN(PI()/2 - RADIANS(90 - ' . $lat . ')))';
	}

	/**
	 * Snippet for custom pagination
	 *
	 * @return array
	 */
	public function distanceConditions(Model $Model, $distance = null, $fieldName = null, $fieldLat = null, $fieldLng = null, $modelName = null) {
		if ($fieldLat === null) {
			$fieldLat = $this->settings[$Model->alias]['lat'];
		}
		if ($fieldLng === null) {
			$fieldLng = $this->settings[$Model->alias]['lng'];
		}
		if ($modelName === null) {
			$modelName = $Model->alias;
		}
		$conditions = [
			$modelName . '.' . $fieldLat . ' <> 0',
			$modelName . '.' . $fieldLng . ' <> 0',
		];
		$fieldName = !empty($fieldName) ? $fieldName : 'distance';
		if ($distance !== null) {
			$conditions[] = '1=1 HAVING ' . $modelName . '.' . $fieldName . ' < ' . intval($distance);
		}
		return $conditions;
	}

	/**
	 * Snippet for custom pagination
	 *
	 * @return string
	 */
	public function distanceField(Model $Model, $lat, $lng, $fieldName = null, $modelName = null) {
		if ($modelName === null) {
			$modelName = $Model->alias;
		}
		$fieldName = (!empty($fieldName) ? $fieldName : 'distance');
		return $this->distance($Model, $lat, $lng, null, null, $modelName) . ' AS ' . $modelName . '.' . $fieldName;
	}

	/**
	 * Snippet for custom pagination
	 * still useful?
	 *
	 * @return string
	 */
	public function distanceByField(Model $Model, $lat, $lng, $byFieldName = null, $fieldName = null, $modelName = null) {
		if ($modelName === null) {
			$modelName = $Model->alias;
		}
		if ($fieldName === null) {
			$fieldName = 'distance';
		}
		if ($byFieldName === null) {
			$byFieldName = 'radius';
		}

		return $this->distance($Model, $lat, $lng, null, null, $modelName) . ' ' . $byFieldName;
	}

	/**
	 * Snippet for custom pagination
	 *
	 * @return int count
	 */
	public function paginateDistanceCount(Model $Model, $conditions = null, $recursive = -1, $extra = []) {
		if (!empty($extra['radius'])) {
			$conditions[] = $extra['distance'] . ' < ' . $extra['radius'] .
				(!empty($extra['startRadius']) ? ' AND ' . $extra['distance'] . ' > ' . $extra['startRadius'] : '') .
				(!empty($extra['endRadius']) ? ' AND ' . $extra['distance'] . ' < ' . $extra['endRadius'] : '');
		}
		if (!empty($extra['group'])) {
			unset($extra['group']);
		}
		$extra['behavior'] = true;
		return $Model->paginateCount($conditions, $recursive, $extra);
	}

	/**
	 * Returns if a latitude is valid or not.
	 * validation rule for models
	 *
	 * @param Model
	 * @param float $latitude
	 * @return bool
	 */
	public function validateLatitude(Model $Model, $latitude) {
		if (is_array($latitude)) {
			$latitude = array_shift($latitude);
		}
		return ($latitude <= 90 && $latitude >= -90);
	}

	/**
	 * Returns if a longitude is valid or not.
	 * validation rule for models
	 *
	 * @param Model
	 * @param float $longitude
	 * @return bool
	 */
	public function validateLongitude(Model $Model, $longitude) {
		if (is_array($longitude)) {
			$longitude = array_shift($longitude);
		}
		return ($longitude <= 180 && $longitude >= -180);
	}

	/**
	 * Uses the GeocodeLib to query
	 *
	 * @param array $addressFields (simple array of address pieces)
	 * @return array
	 */
	protected function _geocode($addressFields, $options = []) {
		$address = implode(' ', $addressFields);
		if (empty($address)) {
			return [];
		}

		$geocodeOptions = [
			'log' => $options['log'], 'min_accuracy' => $options['min_accuracy'],
			'expect' => $options['expect'], 'allow_inconclusive' => $options['allow_inconclusive'],
			'host' => $options['host']
		];
		$this->Geocode = new GeocodeLib($geocodeOptions);
		if (!empty($options['params'])) {
			foreach ($options['params'] as $v) {
				$this->Geocode->setParams($v);
			}
		}

		$config = ['language' => $options['language']];
		if (!$this->Geocode->geocode($address, $config)) {
			return ['lat' => null, 'lng' => null, 'formatted_address' => ''];
		}

		return $this->Geocode->getResult();
	}

	/**
	 * Get the current unit factor
	 *
	 * @param int $unit Unit constant
	 * @return float Value
	 */
	protected function _calculationValue($unit) {
		if (!isset($this->Geocode)) {
			$this->Geocode = new GeocodeLib();
		}
		return $this->Geocode->convert(6371.04, GeocodeLib::UNIT_KM, $unit);
	}

}
