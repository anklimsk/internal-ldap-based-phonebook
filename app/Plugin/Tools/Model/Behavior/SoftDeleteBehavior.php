<?php

/**
 * Copyright 2007-2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2007-2010, Cake Development Corporation (http://cakedc.com)
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
App::uses('ModelBehavior', 'Model');

/**
 * Soft Delete Behavior
 *
 * Note: To make delete() return true with SoftDelete attached, you need to modify your AppModel and overwrite
 * delete() there:
 *
 * public function delete($id = null, $cascade = true) {
 *   $result = parent::delete($id, $cascade);
 *   if (!$result && $this->Behaviors->loaded('SoftDelete')) {
 *     return $this->softDeleted;
 *   }
 *   return $result;
 * }
 *
 */
class SoftDeleteBehavior extends ModelBehavior {

	/**
	 * Default settings
	 *
	 * @var array
	 */
	protected $_defaultConfig = [
		'attribute' => 'softDeleted',
		'fields' => [
			'deleted' => 'deleted_date'
		]
	];

	/**
	 * Holds activity flags for models
	 *
	 * @var array
	 */
	public $runtime = [];

	/**
	 * Setup callback
	 *
	 * @param Model $model
	 * @param array $config
	 * @return void
	 */
	public function setup(Model $model, $config = []) {
		$config += $this->_defaultConfig;

		$error = 'SoftDeleteBehavior::setup(): model ' . $model->alias . ' has no field ';
		$fields = $this->_normalizeFields($model, $config['fields']);
		foreach ($fields as $flag => $date) {
			if ($model->hasField($flag)) {
				if ($date && !$model->hasField($date)) {
					trigger_error($error . $date, E_USER_NOTICE);
					return;
				}
				continue;
			}
			trigger_error($error . $flag, E_USER_NOTICE);
			return;
		}

		$this->settings[$model->alias] = ['fields' => $fields] + $config;
		$this->softDelete($model, true);

		$attribute = $this->settings[$model->alias]['attribute'];
		$model->$attribute = false;
	}

	/**
	 * Before find callback
	 *
	 * @param Model $model
	 * @param array $query
	 * @return array
	 */
	public function beforeFind(Model $model, $query) {
		$runtime = $this->runtime[$model->alias];
		if ($runtime) {
			if (!is_array($query['conditions'])) {
				$query['conditions'] = [];
			}
			$conditions = array_filter(array_keys($query['conditions']));

			$fields = $this->_normalizeFields($model);

			foreach ($fields as $flag => $date) {
				if ($runtime === true || $flag === $runtime) {
					if (!in_array($flag, $conditions) && !in_array($model->alias . '.' . $flag, $conditions)) {
						$query['conditions'][$model->alias . '.' . $flag] = false;
					}

					if ($flag === $runtime) {
						break;
					}
				}
			}
			return $query;
		}
	}

	/**
	 * Before delete callback
	 *
	 * @param Model $model
	 * @param array $query
	 * @return bool Success
	 */
	public function beforeDelete(Model $model, $cascade = true) {
		$runtime = $this->runtime[$model->alias];
		if ($runtime) {
			if ($this->delete($model, $model->id)) {
				$attribute = $this->settings[$model->alias]['attribute'];
				$model->$attribute = true;
			}
			return false;
		}
		return true;
	}

	/**
	 * Mark record as deleted
	 *
	 * @param Model $model
	 * @param int $id
	 * @return bool Success
	 */
	public function delete(Model $model, $id) {
		$runtime = $this->runtime[$model->alias];

		$data = [];
		$fields = $this->_normalizeFields($model);
		foreach ($fields as $flag => $date) {
			if ($runtime === true || $flag === $runtime) {
				$data[$flag] = true;
				if ($date) {
					$data[$date] = date('Y-m-d H:i:s');
				}
				if ($flag === $runtime) {
					break;
				}
			}
		}

		$keys = $this->_getCounterCacheKeys($model, $id);

		$model->create();
		$model->set($model->primaryKey, $id);
		$options = [
			'validate' => false,
			'fieldList' => array_keys($data),
			'counterCache' => false
		];
		$result = (bool)$model->save([$model->alias => $data], $options);

		if ($result && !empty($keys[$model->alias])) {
			$model->updateCounterCache($keys[$model->alias]);
		}

		return $result;
	}

	/**
	 * Mark record as not deleted
	 *
	 * @param Model $model
	 * @param int $id
	 * @return bool Success
	 */
	public function undelete(Model $model, $id) {
		$runtime = $this->runtime[$model->alias];
		$this->softDelete($model, false);

		$data = [];
		$fields = $this->_normalizeFields($model);
		foreach ($fields as $flag => $date) {
			if ($runtime === true || $flag === $runtime) {
				$data[$flag] = false;
				if ($date) {
					$data[$date] = null;
				}
				if ($flag === $runtime) {
					break;
				}
			}
		}

		$model->create();
		$model->set($model->primaryKey, $id);
		$options = [
			'validate' => false,
			'fieldList' => array_keys($data),
			'counterCache' => false
		];
		$result = $model->save([$model->alias => $data], $options);
		$this->softDelete($model, $runtime);

		if ($result) {
			$keys = $this->_getCounterCacheKeys($model, $id);
			if (!empty($keys[$model->alias])) {
				$model->updateCounterCache($keys[$model->alias]);
			}
		}

		return $result;
	}

	/**
	 * Enable/disable SoftDelete functionality
	 *
	 * Usage from model:
	 * $this->softDelete(false); deactivate this behavior for model
	 * $this->softDelete('field_two'); enabled only for this flag field
	 * $this->softDelete(true); enable again for all flag fields
	 * $config = $this->softDelete(null); for obtaining current setting
	 *
	 * @param Model $model
	 * @param mixed $active
	 * @return mixed If $active is null, then current setting/null, or boolean if runtime setting for model was changed
	 */
	public function softDelete(Model $model, $active) {
		if ($active === null) {
			return isset($this->runtime[$model->alias]) ? $this->runtime[$model->alias] : null;
		}

		$result = !isset($this->runtime[$model->alias]) || $this->runtime[$model->alias] !== $active;
		$this->runtime[$model->alias] = $active;
		$this->_softDeleteAssociations($model, $active);
		return $result;
	}

	/**
	 * Returns number of outdated softdeleted records prepared for purge
	 *
	 * @param Model $model
	 * @param mixed $expiration anything parseable by strtotime(), by default '-90 days'
	 * @return int
	 */
	public function purgeDeletedCount(Model $model, $expiration = '-90 days') {
		$this->softDelete($model, false);
		return $model->find('count', ['conditions' => $this->_purgeDeletedConditions($model, $expiration), 'recursive' => -1]);
	}

	/**
	 * Purge table
	 *
	 * @param Model $model
	 * @param mixed $expiration anything parseable by strtotime(), by default '-90 days'
	 * @return bool If there were some outdated records
	 */
	public function purgeDeleted(Model $model, $expiration = '-90 days') {
		$this->softDelete($model, false);
		$records = $model->find('all', [
			'conditions' => $this->_purgeDeletedConditions($model, $expiration),
			'fields' => [$model->primaryKey],
			'recursive' => -1]);
		if ($records) {
			foreach ($records as $record) {
				$model->delete($record[$model->alias][$model->primaryKey]);
			}
			return true;
		}
		return false;
	}

	/**
	 * Returns conditions for finding outdated records
	 *
	 * @param Model $model
	 * @param mixed $expiration anything parseable by strtotime(), by default '-90 days'
	 * @return array
	 */
	protected function _purgeDeletedConditions(Model $model, $expiration = '-90 days') {
		$purgeDate = date('Y-m-d H:i:s', strtotime($expiration));
		$conditions = [];
		foreach ($this->settings[$model->alias]['fields'] as $flag => $date) {
			$conditions[$model->alias . '.' . $flag] = true;
			if ($date) {
				$conditions[$model->alias . '.' . $date . ' <'] = $purgeDate;
			}
		}
		return $conditions;
	}

	/**
	 * Return normalized field array
	 *
	 * @param Model $model
	 * @param array $config
	 * @return array
	 */
	protected function _normalizeFields(Model $model, $config = []) {
		if (empty($config)) {
			$config = $this->settings[$model->alias]['fields'];
		}
		$result = [];
		foreach ($config as $flag => $date) {
			if (is_numeric($flag)) {
				$flag = $date;
				$date = false;
			}
			$result[$flag] = $date;
		}
		return $result;
	}

	/**
	 * Modifies conditions of hasOne and hasMany associations.
	 *
	 * If multiple delete flags are configured for model, then $active=true doesn't
	 * do anything - you have to alter conditions in association definition
	 *
	 * @param Model $model
	 * @param mixed $active
	 * @return void
	 */
	protected function _softDeleteAssociations(Model $model, $active) {
		if (empty($model->belongsTo)) {
			return;
		}
		$fields = array_keys($this->_normalizeFields($model));
		$parentModels = array_keys($model->belongsTo);

		foreach ($parentModels as $parentModel) {
			foreach (['hasOne', 'hasMany'] as $assocType) {
				if (empty($model->{$parentModel}->{$assocType})) {
					continue;
				}

				foreach ($model->{$parentModel}->{$assocType} as $assoc => $assocConfig) {
					$modelName = !empty($assocConfig['className']) ? $assocConfig['className'] : $assoc;
					if ($model->alias !== $modelName) {
						continue;
					}

					$conditions = $model->{$parentModel}->{$assocType}[$assoc]['conditions'];
					if (!is_array($conditions)) {
						$conditions = [];
					}

					$multiFields = 1 < count($fields);
					foreach ($fields as $field) {
						if ($active) {
							if (!isset($conditions[$field]) && !isset($conditions[$assoc . '.' . $field])) {
								if (is_string($active)) {
									if ($field === $active) {
										$conditions[$assoc . '.' . $field] = false;
									} elseif (isset($conditions[$assoc . '.' . $field])) {
										unset($conditions[$assoc . '.' . $field]);
									}
								} elseif (!$multiFields) {
									$conditions[$assoc . '.' . $field] = false;
								}
							}
						} elseif (isset($conditions[$assoc . '.' . $field])) {
							unset($conditions[$assoc . '.' . $field]);
						}
					}
				}
			}
		}
	}

	/**
	 * Retrieves the foreign key values for the `belongsTo` associations
	 * with enabled counter caching.
	 *
	 * The returned array has the following format:
	 *
	 * {{{
	 * array(
	 *     'ModelAlias' => array(
	 *         'foreign_key_name' => foreign key value
	 *     )
	 * )
	 * }}}
	 *
	 * @param Model $model
	 * @param int $id The ID of the current record
	 * @return array
	 */
	protected function _getCounterCacheKeys(Model $model, $id) {
		$keys = [];
		if (!empty($model->belongsTo)) {
			$fields = [];
			foreach ($model->belongsTo as $alias => $assoc) {
				if (!empty($assoc['counterCache']) && isset($assoc['foreignKey']) && is_string($assoc['foreignKey'])) {
					$fields[$alias] = $assoc['foreignKey'];
				}
			}

			if (!empty($fields)) {
				$keys = $model->find('first', [
					'fields' => $fields,
					'conditions' => [$model->alias . '.' . $model->primaryKey => $id],
					'recursive' => -1,
					'callbacks' => false
				]);
			}
		}
		return $keys;
	}

}
