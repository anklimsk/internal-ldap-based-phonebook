<?php
/**
 * This file is the behavior file of the application. Is used for processing
 *  deferred save data.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Model.Behavior
 */

App::uses('ModelBehavior', 'Model');
App::uses('Hash', 'Utility');
App::uses('ClassRegistry', 'Utility');

/**
 * The behavior is used for processing deferred save data.
 *
 * @package app.Model.Behavior
 */
class DeferredSaveBehavior extends ModelBehavior {

/**
 * Object of model `Employee`
 *
 * @var object
 */
	protected $_modelEmployee = null;

/**
 * Object of model `EmployeeEdit`
 *
 * @var object
 */
	protected $_modelEmployeeData = null;

/**
 * Initiate behavior for the model using specified settings.
 *
 * Available settings:
 *
 * - dataModel: (string, optional) model name with new data.
 *   Default - EmployeeEdit.
 *
 * @param Model $model Model using the behavior
 * @param array $settings Settings to override for model.
 * @return void
 */
	public function setup(Model $model, $settings = []) {
		if (!isset($this->settings[$model->alias])) {
			$this->settings[$model->alias] = ['dataModel' => 'EmployeeEdit'];
		}
		$this->settings[$model->alias] = array_merge($this->settings[$model->alias], $settings);

		$this->_modelEmployee = ClassRegistry::init('Employee');
		$this->_modelEmployeeData = ClassRegistry::init($this->settings[$model->alias]['dataModel']);
	}

/**
 * Return object of model with new data.
 *
 * @return object Object of model with new data.
 */
	public function getObjectDataModel() {
		return $this->_modelEmployeeData;
	}

/**
 * Preparing data for deferred saving: base64 encodes employee photos
 *  and serialized data.
 *
 * @param Model $model Model using this behavior.
 * @return bool Success
 */
	protected function _prepareDataForSave(Model $model) {
		if (!isset($model->data[$model->alias]['data']) ||
			empty($model->data[$model->alias]['data'])) {
			return false;
		}

		if (is_string($model->data[$model->alias]['data'])) {
			//@codingStandardsIgnoreStart
			if (@unserialize($model->data[$model->alias]['data']) !== false) {
			//@codingStandardsIgnoreEnd
				return true;
			} else {
				return false;
			}
		} elseif (!is_array($model->data[$model->alias]['data'])) {
			return false;
		}

		$dataTypes = [
			'changed',
			'current'
		];
		$objDataModel = $this->getObjectDataModel($model);
		foreach ($dataTypes as $dataType) {
			if (isset($model->data[$model->alias]['data'][$dataType][$objDataModel->alias][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO]) &&
				!empty($model->data[$model->alias]['data'][$dataType][$objDataModel->alias][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO])) {
				$model->data[$model->alias]['data'][$dataType][$objDataModel->alias][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO] =
					base64_encode($model->data[$model->alias]['data'][$dataType][$objDataModel->alias][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO]);
			}
		}

		$model->data[$model->alias]['data'] = serialize($model->data[$model->alias]['data']);

		return true;
	}

/**
 * beforeValidate is called before a model is validated, you can use this callback to
 * add behavior validation rules into a models validate array. Returning false
 * will allow you to make the validation fail.
 *
 * Actions:
 *  - Preparing deferred save data.
 *
 * @param Model $model Model using this behavior
 * @param array $options Options passed from Model::save().
 * @return mixed False or null will abort the operation. Any other result will continue.
 * @see Model::save()
 */
	public function beforeValidate(Model $model, $options = []) {
		return $this->_prepareDataForSave($model);
	}

/**
 * After find callback. Can be used to modify any results returned by find.
 *
 * Actions:
 *  - Base64 decodes employee photo;
 *  - Unserialized data of deferred save.
 *
 * @param Model $model Model using this behavior
 * @param mixed $results The results of the find operation
 * @param bool $primary Whether this model is being queried directly (vs. being queried as an association)
 * @return mixed An array value will replace the value of $results - any other value will be ignored.
 */
	public function afterFind(Model $model, $results, $primary = false) {
		if (!$primary || empty($results)) {
			return $results;
		}

		$objDataModel = $this->getObjectDataModel($model);
		foreach ($results as &$resultItem) {
			if (isset($resultItem[$model->alias]['data']) && !empty($resultItem[$model->alias]['data'])) {
				//@codingStandardsIgnoreStart
				$resultItem[$model->alias]['data'] = @unserialize($resultItem[$model->alias]['data']);
				//@codingStandardsIgnoreEnd
			}

			$dataTypes = [
				'changed',
				'current'
			];
			foreach ($dataTypes as $dataType) {
				if (isset($resultItem[$model->alias]['data'][$dataType][$objDataModel->alias][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO]) &&
					!empty($resultItem[$model->alias]['data'][$dataType][$objDataModel->alias][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO])) {
					$resultItem[$model->alias]['data'][$dataType][$objDataModel->alias][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO] = base64_decode($resultItem[$model->alias]['data'][$dataType][$objDataModel->alias][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO]);
				}
			}
		}

		return $results;
	}

/**
 * beforeSave is called before a model is saved. Returning false from a beforeSave callback
 * will abort the save operation.
 *
 * Actions:
 *  - Preparing deferred save data.
 *
 * @param Model $model Model using this behavior
 * @param array $options Options passed from Model::save().
 * @return mixed False if the operation should abort. Any other result will continue.
 * @see Model::save()
 */
	public function beforeSave(Model $model, $options = []) {
		return $this->_prepareDataForSave($model);
	}

/**
 * Preparing information of deferred save for displaying.
 *
 * @param Model $model Model using this behavior.
 * @param array $deferredSave Data for preparing
 * @return array Prepared information of deferred save
 */
	public function prepareDataForDisplay(Model $model, $deferredSave) {
		if (empty($deferredSave) || !is_array($deferredSave)) {
			return $deferredSave;
		}

		static $employeesManagerInfoFull = null;
		if (is_null($employeesManagerInfoFull)) {
			$employeesManagerInfoFull = $this->_modelEmployee->getListEmployeesManagerInfo();
		}
		if (isAssoc($deferredSave)) {
			$this->_prepareDataForDisplayItem($deferredSave, $employeesManagerInfoFull);
		} else {
			foreach ($deferredSave as &$deferredSaveItem) {
				$this->_prepareDataForDisplayItem($deferredSaveItem, $employeesManagerInfoFull);
			}
		}

		return $deferredSave;
	}

/**
 * Preparing item information of deferred save for displaying.
 *
 * @param array &$deferredSave Data for preparing
 * @param array $employeesManagerInfoFull Information about managers
 * @return void
 */
	protected function _prepareDataForDisplayItem(&$deferredSave, $employeesManagerInfoFull = []) {
		if (empty($deferredSave) || !is_array($deferredSave)) {
			return;
		}

		$manager = Hash::get($deferredSave, CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER);
		if (!empty($manager) && isset($employeesManagerInfoFull[$manager])) {
			$deferredSave[CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER] = $employeesManagerInfoFull[$manager];
		}

		$employeePhoto = Hash::get($deferredSave, CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO);
		if (!empty($employeePhoto)) {
			$deferredSave[CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO] = base64_encode($employeePhoto);
		}
	}

/**
 * Return local data of employee for modified data.
 *
 * @param Model $model Model using this behavior.
 * @param array $data Modified data for retrieving local data.
 * @param int $userRole User role.
 * @return array|bool Return local data for modified data,
 *  or False on failure.
 */
	protected function _getLocalData(Model $model, $data = [], $userRole = null) {
		$objDataModel = $this->getObjectDataModel($model);
		if (empty($data) || !isset($data[$objDataModel->alias]) ||
			empty($data[$objDataModel->alias]) || !isset($data[$objDataModel->alias][CAKE_LDAP_LDAP_DISTINGUISHED_NAME]) ||
			empty($data[$objDataModel->alias][CAKE_LDAP_LDAP_DISTINGUISHED_NAME])) {
			return false;
		}

		$dn = $data[$objDataModel->alias][CAKE_LDAP_LDAP_DISTINGUISHED_NAME];
		$editFields = array_keys($data[$objDataModel->alias]);
		if (empty($editFields)) {
			return false;
		}

		if (isset($this->_modelEmployee->belongsTo['Manager'])) {
			$this->_modelEmployee->belongsTo['Manager']['fields'][] = 'Manager.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME;
		}
		$excludeFields = $this->_modelEmployee->getListExcludeFieldsDb($userRole);
		$localData = $this->_modelEmployee->get($dn, $excludeFields);
		if (empty($localData)) {
			return false;
		}

		$result = [];
		foreach ($editFields as $editFieldName) {
			$dataPath = null;
			switch ($editFieldName) {
				case CAKE_LDAP_LDAP_DISTINGUISHED_NAME:
					$dataPath = $this->_modelEmployee->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME;
					break;
				case CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER:
					$dataPath = 'Manager.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME;
					break;
				case CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT:
					$dataPath = 'Department.value';
					break;
				case CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER:
					$dataPath = 'Othertelephone.{n}.value';
					// no break
				case CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER:
					if (empty($dataPath)) {
						$dataPath = 'Othermobile.{n}.value';
					}
					$localDataEditField = Hash::extract($localData, $dataPath);
					if (!empty($localDataEditField) && !empty($data[$objDataModel->alias][$editFieldName])) {
						$values = [];
						foreach ($localDataEditField as $i => &$value) {
							$index = array_search($value, $data[$objDataModel->alias][$editFieldName]);
							if ($index !== false) {
								$values[$index] = $value;
								unset($localDataEditField[$i]);
							}
						}
						unset($value);
						ksort($values);
						if (!empty($localDataEditField)) {
							$values = array_merge($values, $localDataEditField);
						}
						$localDataEditField = $values;
					}
					$result[$objDataModel->alias][$editFieldName] = $localDataEditField;
					continue 2;
					// break;
				default:
					$dataPath = $this->_modelEmployee->alias . '.' . $editFieldName;
			}
			$result[$objDataModel->alias][$editFieldName] = Hash::get($localData, $dataPath);
		}
		$result['employee_id'] = Hash::get($localData, $this->_modelEmployee->alias . '.id');

		return $result;
	}

/**
 * Return data for saving deferred save from modified data of employee.
 *
 * @param Model $model Model using this behavior.
 * @param array $data Modified data of employee to create deferred save.
 * @param int $userRole User role.
 * @return array|bool Return data for saving deferred save,
 *  or False on failure.
 */
	public function getDeferredData(Model $model, $data = [], $userRole = null) {
		$localData = $this->_getLocalData($model, $data, $userRole);
		if (empty($localData)) {
			return false;
		}

		$objDataModel = $this->getObjectDataModel($model);
		$excludeFields = $this->_modelEmployee->getListExcludeFieldsLdap($userRole);
		$readOnlyFields = $this->_modelEmployee->getListReadOnlyFieldsLdap();
		if (!empty($excludeFields) || !empty($readOnlyFields)) {
			$excludeFieldsFull = [];
			if (!empty($excludeFields)) {
				$excludeFieldsFull += array_flip($excludeFields);
			}
			if (!empty($readOnlyFields)) {
				$excludeFieldsFull += array_flip($readOnlyFields);
			}
			$data[$objDataModel->alias] = array_diff_key($data[$objDataModel->alias], $excludeFieldsFull);
			$localData[$objDataModel->alias] = array_diff_key($localData[$objDataModel->alias], $excludeFieldsFull);
		}

		$diffDataNew = Hash::diff($data[$objDataModel->alias], $localData[$objDataModel->alias]);
		if (empty($diffDataNew)) {
			return null;
		}

		$diffDataCurr = Hash::diff($localData[$objDataModel->alias], $data[$objDataModel->alias]);
		$result = [
			'employee_id' => $localData['employee_id'],
			'changed' => [$objDataModel->alias => $diffDataNew],
			'current' => [$objDataModel->alias => $diffDataCurr],
		];
		$result['changed'][$objDataModel->alias][CAKE_LDAP_LDAP_DISTINGUISHED_NAME] = $localData[$objDataModel->alias][CAKE_LDAP_LDAP_DISTINGUISHED_NAME];

		return $result;
	}

/**
 * Queries the datasource and returns a result for pagination.
 *
 * Actions:
 *  - Preparing data for displaying.
 *
 * @param Model $model Model using this behavior.
 * @param array $conditions SQL conditions.
 * @param string|array $fields String of single field name, or an array of field names.
 * @param string $order SQL ORDER BY fragment.
 * @param int|string $limit Limit for result.
 * @param int|string $page Current page number.
 * @param int|string $recursive Number of associations to recurse through during find calls.
 *  Fetches only the first level by default.
 * @param array $extra Extra optionts.
 * @return array|null Array of records, or Null on failure.
 * @link https://book.cakephp.org/2.0/en/core-libraries/components/pagination.html#custom-query-pagination
 */
	public function paginate(
		Model $model,
		$conditions,
		$fields,
		$order,
		$limit,
		$page = 1,
		$recursive = null,
		$extra = []
	) {
		$findOpt = compact('conditions', 'fields', 'order', 'limit', 'page');
		if ($recursive != $model->recursive) {
			$findOpt['recursive'] = $recursive;
		}
		$result = $model->find('all', array_merge($findOpt, (array)$extra));
		if (empty($result)) {
			return $result;
		}

		$objDataModel = $this->getObjectDataModel($model);
		foreach ($result as &$resultItem) {
			if (!is_array($resultItem[$model->alias]['data'])) {
				continue;
			}

			if (isset($resultItem[$model->alias]['data']['changed'][$objDataModel->alias])) {
				$resultItem[$model->alias]['data']['changed'][$objDataModel->alias] = $this->prepareDataForDisplay($model, $resultItem[$model->alias]['data']['changed'][$objDataModel->alias]);
			}
			if (isset($resultItem[$model->alias]['data']['current'][$objDataModel->alias])) {
				$resultItem[$model->alias]['data']['current'][$objDataModel->alias] = $this->prepareDataForDisplay($model, $resultItem[$model->alias]['data']['current'][$objDataModel->alias]);
			}
		}

		return $result;
	}

/**
 * Return reference employee ID.
 *
 * @param Model $model Model using this behavior.
 * @param int|string $id ID of record for retrieving employee ID.
 * @return int|bool Return employee ID,
 *  or False on failure.
 */
	public function getRefId(Model $model, $id = null) {
		if (empty($id) || !$model->hasField('employee_id')) {
			return false;
		}

		$model->id = $id;
		return $model->field('employee_id');
	}

/**
 * Return name of data.
 *
 * @param Model $model Model using this behavior.
 * @param int|string|array $id ID of record or array data
 *  for retrieving name.
 * @return string|bool Return name of data,
 *  or False on failure.
 */
	public function getName(Model $model, $id = null) {
		if (is_array($id)) {
			$refId = $id;
		} else {
			$refId = $model->getRefId($id);
		}

		return $this->_modelEmployee->getName($refId);
	}

/**
 * Return an array of information for creating a breadcrumbs.
 *
 * @param Model $model Model using this behavior.
 * @param int|string|array $id ID of record or array data
 *  for retrieving name.
 * @param bool|null $includeRoot If True, include information of root breadcrumb.
 *  If Null, include information of root breadcrumb if $ID is not empty.
 * @return array Return an array of information for creating a breadcrumbs.
 */
	public function getBreadcrumbInfo(Model $model, $id = null, $includeRoot = null) {
		if (is_array($id)) {
			$refId = $id;
		} else {
			$refId = $model->getRefId($id);
		}
		$result = $this->_modelEmployee->getBreadcrumbInfo($refId, $includeRoot);
		$breadcrumbRoot = $model->createBreadcrumb(null);
		$breadcrumbInfo = $model->createBreadcrumb($id);
		if (!empty($breadcrumbRoot) && !empty($breadcrumbInfo)) {
			$breadcrumbRoot[1] = $breadcrumbInfo[1];
		}
		if (!empty($breadcrumbRoot)) {
			$result[] = $breadcrumbRoot;
		}

		return $result;
	}
}
