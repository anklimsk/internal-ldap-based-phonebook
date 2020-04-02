<?php
/**
 * This file is the model file of the application. Used for
 *  management employees.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2020, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Model
 */

App::uses('EmployeeDb', 'CakeLdap.Model');
App::uses('ClassRegistry', 'Utility');
App::uses('Hash', 'Utility');
App::uses('CakeTime', 'Utility');
App::uses('PhoneNumber', 'Utility');
App::uses('UserInfo', 'CakeLdap.Utility');
App::uses('PdfView', 'CakeTCPDF.View');
App::uses('SpreadsheetView', 'CakeSpreadsheet.View');

/**
 * The model is used for management employees.
 *
 * This model allows:
 * - Retrieve information about employee;
 * - Render file for export.
 *
 * @package app.Model
 */
class Employee extends EmployeeDb {

/**
 * Name of the model.
 *
 * @var string
 * @link http://book.cakephp.org/2.0/en/models/model-attributes.html#name
 */
	public $name = 'Employee';

/**
 * List of behaviors to load when the model object is initialized.
 *
 * @var array
 * @link http://book.cakephp.org/2.0/en/models/behaviors.html#using-behaviors
 */
	public $actsAs = [
		'Containable',
		'CakeTheme.BreadCrumb'
	];

/**
 * Array of virtual fields this model has.
 *
 * @var array
 * @link http://book.cakephp.org/2.0/en/models/model-attributes.html#virtualfields
 */
	public $virtualFields = [
		CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => '1',
		CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => '2',
	];

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = [
		'DepartmentExtension' => [
			'className' => 'DepartmentExtension',
			'foreignKey' => false,
			'conditions' => ['Employee.department_id = DepartmentExtension.department_id'],
			'dependent' => false,
			'fields' => [
				'DepartmentExtension.id',
				'DepartmentExtension.lft',
				'DepartmentExtension.name',
			],
		],
	];

/**
 * hasOne associations
 *
 * @var array
 */
	public $hasOne = [
		'OrderEmployees' => [
			'className' => 'CakeLdap.SubordinateDb',
			'foreignKey' => false,
			'conditions' => ['Employee.id = OrderEmployees.id'],
			'dependent' => false,
			'fields' => [
				'OrderEmployees.id',
				'OrderEmployees.lft'
			],
		],
	];

/**
 * Object of model `Setting`
 *
 * @var object
 */
	protected $_modelSetting = null;

/**
 * Timestamp of last update information about employees
 *
 * @var int
 */
	protected $_lastUpdate = 0;

/**
 * Path to export directory
 *
 * @var string
 */
	public $pathExportDir = EXPORT_DIR;

/**
 * Constructor. Binds the model's database table to the object.
 *
 * @param bool|int|string|array $id Set this ID for this model on startup,
 * can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 */
	public function __construct($id = false, $table = null, $ds = null) {
		$this->_modelSetting = ClassRegistry::init('Setting');
		parent::__construct($id, $table, $ds);
	}

/**
 * Set timestamp of last update information about employees
 *
 * @param int $timestamp Timestamp of last update
 * @return void
 */
	protected function _setLastUpdate($timestamp = 0) {
		$this->_lastUpdate = (int)$timestamp;
	}

/**
 * Return timestamp of last update information about employees
 *
 * @return int Return timestamp of last update
 */
	protected function _getLastUpdate() {
		return (int)$this->_lastUpdate;
	}

/**
 * Update timestamp of last update information about employees
 *
 * @return bool Success
 */
	protected function _updateLastUpdateTimestamp() {
		$lastUpdate = $this->_getLastUpdate();

		$now = time();
		if ($now - $lastUpdate < 60) {
			return true;
		}

		$modelLastProcessed = ClassRegistry::init('LastProcessed');
		if (!$modelLastProcessed->setLastProcessed(LAST_PROCESSED_EMPLOYEE, $this->id)) {
			return false;
		}

		$this->_setLastUpdate($now);

		return true;
	}

/**
 * Called after each successful save operation.
 *
 * Actions:
 *  - Update timestamp of last update information about employees;
 *  - Clear cache.
 *
 * @param bool $created True if this save created a new record
 * @param array $options Options passed from Model::save().
 * @return void
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#aftersave
 * @see Model::save()
 */
	public function afterSave($created, $options = []) {
		parent::afterSave($created, $options);
		Cache::delete('number_of_employees', CACHE_KEY_STATISTICS_INFO);
		Cache::clear(false, CACHE_KEY_EMPLOYEES_LOCAL_INFO);
		$this->_updateLastUpdateTimestamp();
	}

/**
 * Called after every deletion operation.
 *
 * Actions:
 *  - Update timestamp of last update information about employees;
 *  - Clear cache.
 *
 * @return void
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#afterdelete
 */
	public function afterDelete() {
		parent::afterDelete();
		Cache::delete('number_of_employees', CACHE_KEY_STATISTICS_INFO);
		Cache::clear(false, CACHE_KEY_EMPLOYEES_LOCAL_INFO);
		$this->_updateLastUpdateTimestamp();
	}

/**
 * Return fields configuration for helper
 *
 * @return array Return array fields configuration.
 */
	public function getFieldsConfig() {
		$cachePath = 'local_fields_fields_schema_ext';
		$cached = Cache::read($cachePath, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);
		if ($cached !== false) {
			return $cached;
		}

		$result = parent::getFieldsConfig();
		$telFields = [
			'name' => [
				$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER,
				'Othermobile.{n}.value',
			],
			'description' => [
				'Othertelephone.{n}.value'
			]
		];
		foreach ($telFields as $infoType => $fieldsValuePath) {
			foreach ($fieldsValuePath as $fieldValuePath) {
				if (!isset($result[$fieldValuePath])) {
					continue;
				}

				$result[$fieldValuePath]['type'] = 'telephone_' . $infoType;
				$result[$fieldValuePath]['truncate'] = false;
			}
		}
		if (isset($result['Department.value'])) {
			$result['Department.value']['type'] = 'department_name';
		}

		Cache::write($cachePath, $result, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);

		return $result;
	}

/**
 * Return number of employees.
 *
 * @return int Number of employees.
 */
	public function getNumberOf() {
		$cachePath = 'number_of_employees';
		$cached = Cache::read($cachePath, CACHE_KEY_STATISTICS_INFO);
		if ($cached !== false) {
			return $cached;
		}

		$conditions = [
			$this->alias . '.block' => false
		];
		$this->recursive = -1;
		$result = (int)$this->find('count', compact('conditions'));
		Cache::write($cachePath, $result, CACHE_KEY_STATISTICS_INFO);

		return $result;
	}

/**
 * Return configuration of models for CakeSearchInfo plugin
 *
 * @param int $userRole Bit mask of user role for
 *  retrieve configuration.
 * @return array Return array plugin CakeSearchInfo configuration.
 */
	public function getSearchTargetModels($userRole = null) {
		if (empty($userRole)) {
			$userRole = USER_ROLE_USER;
		}
		$userRole = (int)$userRole;
		$language = (string)Configure::read('Config.language');
		$cachePath = 'search_target_models_' .
			md5($userRole . '_' . $language);
		$cached = Cache::read($cachePath, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);
		if ($cached !== false) {
			return $cached;
		}

		$result = [];
		$ldapFieldsInfoFull = $this->getLdapFieldsInfoForUserRole($userRole);
		if (empty($ldapFieldsInfoFull)) {
			return $result;
		}

		$excludeFields = [
			CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID,
			CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME,
			CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
			CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS,
			CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO,
			CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY,
		];
		$ldapFieldsInfo = array_diff_key($ldapFieldsInfoFull, array_flip($excludeFields));
		$ldapFieldsInfo = Hash::sort($ldapFieldsInfo, '{s}.priority', 'asc');
		$fields = [];
		$order = null;
		$name = __('Employees');
		$contain = [];
		foreach ($ldapFieldsInfo as $fieldName => $fieldInfo) {
			$label = $fieldName;
			if (isset($fieldInfo['label']) && !empty($fieldInfo['label'])) {
				$label = $fieldInfo['label'];
			}

			switch ($fieldName) {
				case CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT:
					$fullFieldName = 'Department.value';
					$contain[] = 'Department';
					$contain[] = 'DepartmentExtension';
					break;
				case CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER:
					$fullFieldName = 'Manager.' . $this->Manager->displayField;
					$contain[] = 'Manager';
					break;
				case CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER:
				case CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER:
					$fullFieldName = $this->alias . '.' . $fieldName;
					if ($fieldName === CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER) {
						$contain[] = 'Othertelephone';
					} else {
						$contain[] = 'Othermobile';
					}
					break;
				default:
					$fullFieldName = $this->alias . '.' . $fieldName;
			}
			$fields[$fullFieldName] = $label;
		}
		if (empty($fields)) {
			return $result;
		}

		reset($fields);
		$order = [key($fields) => 'asc'];
		$conditions = [
			'Employee.block' => false
		];
		$result[$this->alias] = compact('fields', 'order', 'name', 'contain', 'conditions');
		Cache::write($cachePath, $result, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);

		return $result;
	}

/**
 * Return configuration of include fields for CakeSearchInfo plugin
 *
 * @param int $userRole Bit mask of user role for
 *  retrieve configuration.
 * @return array Return array plugin CakeSearchInfo include fields.
 */
	public function getSearchIncludeFields($userRole = null) {
		if (empty($userRole)) {
			$userRole = USER_ROLE_USER;
		}
		$userRole = (int)$userRole;
		$cachePath = 'search_include_fields_' . md5($userRole);
		$cached = Cache::read($cachePath, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);
		if ($cached !== false) {
			return $cached;
		}

		$result = [];
		$ldapFieldsInfo = $this->getLdapFieldsInfoForUserRole($userRole);
		$includeFields = [
			CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID,
			CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'Manager.id',
			CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'Department.id',
			CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
			CAKE_LDAP_LDAP_ATTRIBUTE_NAME => $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
		];
		foreach ($includeFields as $ldapField => $includeField) {
			if (isset($ldapFieldsInfo[$ldapField])) {
				$result[$this->alias][] = $includeField;
			}
		}
		Cache::write($cachePath, $result, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);

		return $result;
	}

/**
 * Return list of extended fields for display in roles:
 *  - secretary;
 *  - human resources;
 *  - administrator.
 *
 * @return array Return array list of extended fields.
 */
	public function getListExtendedFields() {
		$cachePath = 'list_extended_fields';
		$cached = Cache::read($cachePath, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);
		if ($cached !== false) {
			return $cached;
		}

		$result = [];
		$extendedFields = $this->_modelSetting->getConfig('ExtendedFields');
		if (empty($extendedFields) || !is_array($extendedFields)) {
			return $result;
		}

		$modelConfigSync = ClassRegistry::init('CakeLdap.ConfigSync');
		$fieldsLdap = $modelConfigSync->getListFieldsLdap();
		if (empty($fieldsLdap)) {
			return $result;
		}

		$result = array_values(array_intersect($fieldsLdap, $extendedFields));
		Cache::write($cachePath, $result, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);

		return $result;
	}

/**
 * Return list of exclude fields LDAP for user role.
 *
 * @param int $userRole Bit mask of user role for
 *  retrieve list of exclude fields.
 * @return array Return array list of exclude fields LDAP.
 */
	public function getListExcludeFieldsLdap($userRole = null) {
		$result = [];
		$extendedFields = $this->getListExtendedFields();
		$ldapFieldsInfo = $this->getLdapFieldsInfoForUserRole($userRole);
		if (empty($extendedFields) || empty($ldapFieldsInfo)) {
			return $result;
		}

		$result = array_values(array_diff($extendedFields, array_keys($ldapFieldsInfo)));

		return $result;
	}

/**
 * Return list of exclude fields database for user role. Used for display
 *  information of employee.
 *
 * @param int $userRole Bit mask of user role for
 *  retrieve list of exclude fields.
 * @return array Return array list of exclude fields database.
 */
	public function getListExcludeFieldsDb($userRole = null) {
		if (empty($userRole)) {
			$userRole = USER_ROLE_USER;
		}
		$userRole = (int)$userRole;
		$cachePath = 'list_exclude_fields_' . md5($userRole);
		$cached = Cache::read($cachePath, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);
		if ($cached !== false) {
			return $cached;
		}

		$fields = $this->getListExcludeFieldsLdap($userRole);
		$result = [];
		foreach ($fields as $fieldName) {
			switch ($fieldName) {
				case CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT:
					$result[] = 'department_id';
					break;
				case CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER:
					$result[] = 'manager_id';
					break;
				default:
					$result[] = $fieldName;
			}
		}
		Cache::write($cachePath, $result, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);

		return $result;
	}

/**
 * Return list of read only fields LDAP include model name.
 *
 * @param string $modelName Name of model for including in result.
 * @return array Return list of read only fields LDAP.
 */
	public function getListReadOnlyFieldsLdap($modelName = null) {
		$result = [
			CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
			CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID,
			CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME,
		];
		$companyName = $this->_modelSetting->getConfig('Company');
		if (!empty($companyName)) {
			$result[] = CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY;
		}

		$readOnlyFields = $this->_modelSetting->getConfig('ReadOnlyFields');
		if (empty($readOnlyFields)) {
			return $result;
		}

		$result = array_values(array_unique(array_merge($result, $readOnlyFields)));
		if (empty($modelName)) {
			return $result;
		}

		array_walk(
			$result,
			function (&$v ,$k, $a) {
				$v = "$a.$v";
			},
			$modelName
		);

		return $result;
	}

/**
 * Return list of fields for exclude for user role. Used for display
 *  information of employee.
 *
 * @param int $userRole Bit mask of user role for
 *  retrieve list of fields for exclude labels.
 * @return array Return array list of fields for exclude labels.
 */
	public function getListExcludeFieldsLabel($userRole = null) {
		if (empty($userRole)) {
			$userRole = USER_ROLE_USER;
		}
		$userRole = (int)$userRole;
		$cachePath = 'list_exclude_fields_label_' . md5($userRole);
		$cached = Cache::read($cachePath, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);
		if ($cached !== false) {
			return $cached;
		}

		$fields = $this->getListExcludeFieldsLdap($userRole);
		$fields[] = 'block';
		$result = [];
		foreach ($fields as $fieldName) {
			switch ($fieldName) {
				case CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT:
					$result[] = 'Department.value';
					break;
				case CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER:
					$result[] = 'Manager.name';
					$result[] = 'Subordinate.{n}';
					break;
				case CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER:
					$result[] = 'Othertelephone.{n}.value';
					break;
				case CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER:
					$result[] = 'Othermobile.{n}.value';
					break;
				default:
					$result[] = $this->alias . '.' . $fieldName;
			}
		}
		Cache::write($cachePath, $result, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);

		return $result;
	}

/**
 * Return information about LDAP fields for user role.
 *
 * @param int $userRole Bit mask of user role for
 *  retrieve information about LDAP fields.
 * @return array Return array information about LDAP fields.
 */
	public function getLdapFieldsInfoForUserRole($userRole = null) {
		if (empty($userRole)) {
			$userRole = USER_ROLE_USER;
		}
		$userRole = (int)$userRole;
		$language = (string)Configure::read('Config.language');
		$cachePath = 'ldap_fields_info_user_role_' . md5($userRole . '_' . $language);
		$cached = Cache::read($cachePath, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);
		if ($cached !== false) {
			return $cached;
		}

		$result = [];
		$modelConfigSync = ClassRegistry::init('CakeLdap.ConfigSync');
		$ldapFieldsInfo = $modelConfigSync->getLdapFieldsInfo();
		if (empty($ldapFieldsInfo)) {
			return $result;
		}

		if (empty($userRole)) {
			$userRole = USER_ROLE_USER;
		}

		$userInfoData = ['role' => (int)$userRole];
		$userInfoLib = new UserInfo();
		if ($userInfoLib->checkUserRole([USER_ROLE_SECRETARY, USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN], true, $userInfoData)) {
			Cache::write($cachePath, $ldapFieldsInfo, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);

			return $ldapFieldsInfo;
		}

		$extendedFields = $this->getListExtendedFields();
		if (empty($extendedFields)) {
			Cache::write($cachePath, $ldapFieldsInfo, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);

			return $ldapFieldsInfo;
		}

		$result = array_diff_key($ldapFieldsInfo, array_flip($extendedFields));
		Cache::write($cachePath, $result, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);

		return $result;
	}

/**
 * Return options for paginator helper for user role.
 *
 * @param int $userRole Bit mask of user role for
 *  retrieve options for paginator helper.
 * @return array Return array options for paginator helper.
 */
	public function getPaginatorOptions($userRole = null) {
		if (empty($userRole)) {
			$userRole = USER_ROLE_USER;
		}
		$userRole = (int)$userRole;
		$language = (string)Configure::read('Config.language');
		$cachePath = 'paginator_helper_options_' . md5($userRole . '_' . $language);
		$cached = Cache::read($cachePath, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);
		if ($cached !== false) {
			return $cached;
		}

		$result = [];
		$excludeFields = [
			$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME,
			$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME,
			$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME
		];
		$targetModels = $this->getSearchTargetModels($userRole);
		$filterOptions = $this->getFilterOptions();
		$filterOptions = array_diff_key($filterOptions, array_flip($excludeFields));
		foreach ($filterOptions as $fieldName => $fieldInfo) {
			if (!isset($targetModels[$this->alias]['fields'][$fieldName])) {
				switch ($fieldName) {
					case 'Othertelephone.{n}.value':
						if (!isset($targetModels[$this->alias]['fields'][$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER])) {
							continue 2;
						}
						break;
					case 'Othermobile.{n}.value':
						if (!isset($targetModels[$this->alias]['fields'][$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER])) {
							continue 2;
						}
						break;
					default:
						continue 2;
				}
			}
			$result[$fieldName] = $fieldInfo;
		}
		Cache::write($cachePath, $result, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);

		return $result;
	}

/**
 * Return options for paginator component for user role.
 *
 * @param int $userRole Bit mask of user role for
 *  retrieve options for paginator component.
 * @return array|bool Return array options for paginator component,
 *  or False, if display photo of employee not allowed.
 */
	public function getPaginatorOptionsGallery($userRole = null) {
		if (empty($userRole)) {
			$userRole = USER_ROLE_USER;
		}
		$userRole = (int)$userRole;
		$cachePath = 'paginator_helper_options_' . md5($userRole);
		$cached = Cache::read($cachePath, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);
		if ($cached !== false) {
			return $cached;
		}

		$result = [
			'page' => '1',
			'limit' => '20',
			'maxLimit' => '250',
			'order' => [
				'DepartmentExtension.lft' => 'asc',
				$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'asc',
				'OrderEmployees.lft' => 'asc',
			],
			'contain' => [
				'DepartmentExtension',
				'OrderEmployees',
			]
		];
		$fields = [
			CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
			CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
			CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS,
			CAKE_LDAP_LDAP_ATTRIBUTE_TITLE,
			CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION,
			CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO,
		];
		$excludeFields = $this->getListExcludeFieldsDb($userRole);
		if (in_array(CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO, $excludeFields)) {
			return false;
		}

		$userRoleFields = array_values(array_diff($fields, $excludeFields));
		array_walk(
			$userRoleFields,
			function (&$v, $k, $a) {
				$v = "$a.$v";
			},
			$this->alias
		);
		$result['fields'] = $userRoleFields;
		$result['fields'][] = $this->alias . '.id';
		if (in_array('department_id', $excludeFields)) {
			unset($result['order']['DepartmentExtension.lft']);
			$result['contain'] = array_values(array_diff($result['contain'], ['DepartmentExtension']));
		}
		if (in_array(CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION, $excludeFields)) {
			unset($result['order'][$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION]);
		}

		$targetModels = $this->getSearchTargetModels($userRole);
		$result['conditions'] = $targetModels[$this->alias]['conditions'];
		Cache::write($cachePath, $result, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);

		return $result;
	}

/**
 * Return list of managers
 *
 * @param int|string $limit Limit for result
 * @return array Return list of managers.
 */
	public function getListEmployeesManagerInfo($limit = CAKE_LDAP_SYNC_AD_LIMIT) {
		$limit = (int)$limit;
		$cachePath = 'list_manager_info_' . md5($limit);
		$cached = Cache::read($cachePath, CACHE_KEY_EMPLOYEES_LOCAL_INFO);
		if ($cached !== false) {
			return $cached;
		}

		$result = [];
		$conditions = [
			$this->alias . '.block' => false
		];
		$fields = [
			$this->alias . '.id',
			$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME,
			$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
			$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE,
		];
		$order = [
			$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'asc',
		];
		$this->recursive = -1;
		$data = $this->find('all', compact('fields', 'conditions', 'order', 'limit'));
		if (empty($data)) {
			return $result;
		}

		$result = Hash::combine($data, '{n}.' . $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME, '{n}.' . $this->alias);
		Cache::write($cachePath, $result, CACHE_KEY_EMPLOYEES_LOCAL_INFO);

		return $result;
	}

/**
 * Return list of birthdays
 *
 * @param int $timestamp Timestamp of day for retrieving list birthdays.
 * @param int|string $limit Limit for result
 * @return array Return list of birthdays.
 */
	public function getListBirthday($timestamp = null, $limit = null) {
		$useCache = true;
		if (!empty($timestamp)) {
			$useCache = false;
		}
		if (empty($timestamp) || (!is_int($timestamp) || !ctype_digit($timestamp))) {
			$timestamp = time();
		}

		if (empty($limit)) {
			$limit = BIRTHDAY_LIST_LIMIT;
		}
		$limit = (int)$limit;
		$date = date('Y-m-d', (int)$timestamp);
		$cachePath = 'list_birthdays_' . md5($limit);
		if ($useCache) {
			$cached = Cache::read($cachePath, CACHE_KEY_EMPLOYEES_LOCAL_INFO);
			if (($cached !== false) && isset($cached['date']) && ($cached['date'] === $date)) {
				return $cached['birthdays'];
			}
		}

		$fields = [
			$this->alias . '.id',
			$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
			$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME,
			$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME,
			$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME,
			$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
			$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS,
			'Department.value'
		];
		$conditions = [
			$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY . ' like' => '____-' . date('m-d', (int)$timestamp),
			$this->alias . '.block' => false
		];
		$order = [$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'asc'];
		$contain = ['Department'];
		$birthdays = (array)$this->find('all', compact('fields', 'conditions', 'contain', 'order', 'limit'));
		$info = compact('date', 'birthdays');
		if ($useCache) {
			Cache::write($cachePath, $info, CACHE_KEY_EMPLOYEES_LOCAL_INFO);
		}

		return $birthdays;
	}

/**
 * Return export file name
 *
 * @param string $type Export type
 * @param bool $extendView If true, append to result postfix.
 * @param bool $returnHash If true, return MD5 hash for export file.
 *  Otherwise, return file name.
 * @return string Export file name or hash.
 */
	public function expandTypeExportToFilename($type = null, $extendView = false, $returnHash = false) {
		$type = mb_strtolower($type);
		$extendView = (bool)$extendView;
		$returnHash = (bool)$returnHash;
		$language = (string)Configure::read('Config.language');
		$hash = md5(serialize(compact('type', 'extendView', 'returnHash')) . '_' . $language);
		$cachePath = 'export_filename_' . $hash;
		$cached = Cache::read($cachePath, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);
		if ($cached !== false) {
			return $cached;
		}

		if ($returnHash) {
			Cache::write($cachePath, $hash, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);

			return $hash;
		}

		switch ($type) {
			case GENERATE_FILE_DATA_TYPE_ALPH:
				$extType = __('by alphabet');
				break;
			case GENERATE_FILE_DATA_TYPE_DEPART:
				$extType = __('by department');
				break;
			default:
				$extType = '';
		}

		$fileName = [__('Directory')];
		if (!empty($extType)) {
			$fileName[] = $extType;
		}

		if ($extendView) {
			$fileName[] = __('full');
		}
		$result = implode(' ', $fileName);
		Cache::write($cachePath, $result, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);

		return $result;
	}

/**
 * Return label of Non-staff personnel
 *
 * @return string Return label of Non-staff personnel.
 */
	public function getEmptyDepartmentName() {
		$result = __('Non-staff personnel');

		return $result;
	}

/**
 * Return text for header for export configuration
 *
 * @param array $listLabels List of label items in format:
 *  - key: `label` - full label of item;
 *  - key: `altLabel` - alternative (short) label of item.
 * @param int|string $limitLength Limit of length for label.
 *  If greater than, use `altLabel` filed.
 * @return string Text for header.
 */
	protected function _getTextLabelFromList($listLabels = [], $limitLength = EXPORT_LABEL_ITEM_LENGTH_LIMIT) {
		$result = '';
		if (empty($listLabels) || !is_array($listLabels)) {
			return $result;
		}

		$defaultLabelValue = [
			'label' => '',
			'altLabel' => '',
		];
		$labels = [];
		foreach ($listLabels as $labelInfo) {
			$labelInfo += $defaultLabelValue;
			if ((count($listLabels) == 1) || (mb_strlen($labelInfo['label']) <= $limitLength)) {
				$labels[] = $labelInfo['label'];
			} else {
				$labels[] = $labelInfo['altLabel'];
			}
		}
		$result = implode(' / ', $labels);

		return $result;
	}

/**
 * Return array of export configuration
 *
 * @param string $type Export type
 * @param bool $extendView If true, include in configuration extended fields.
 * @return array|bool Return array of export configuration, or False on failure.
 */
	public function getExportConfig($type = null, $extendView = true) {
		$type = mb_strtolower($type);
		$extendView = (bool)$extendView;
		$language = (string)Configure::read('Config.language');
		$cachePath = 'export_config_' . md5(serialize(compact('type', 'extendView')) . '_' . $language);
		$cached = Cache::read($cachePath, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);
		if ($cached !== false) {
			return $cached;
		}

		$createDate = __('Created %s', CakeTime::i18nFormat(time(), '%x'));
		$header = [];
		$width = [];
		$align = [];

		$company = $this->_modelSetting->getConfig('Company');
		$userRole = USER_ROLE_USER;
		if ($extendView) {
			$userRole = USER_ROLE_SECRETARY;
		}

		$listCfgFieldsLabel = [
			'label' => null,
			'altLabel' => null,
		];
		$ldapFieldsInfo = $this->getLdapFieldsInfoForUserRole($userRole);
		$headerItems = [];
		if ((isset($ldapFieldsInfo[CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME]) && isset($ldapFieldsInfo[CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME]) &&
			isset($ldapFieldsInfo[CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME])) ||
			isset($ldapFieldsInfo[CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME]) ||
			isset($ldapFieldsInfo[CAKE_LDAP_LDAP_ATTRIBUTE_NAME])) {
			$headerItems[] = array_intersect_key($ldapFieldsInfo[CAKE_LDAP_LDAP_ATTRIBUTE_NAME], $listCfgFieldsLabel);
		}
		if (isset($ldapFieldsInfo[CAKE_LDAP_LDAP_ATTRIBUTE_MAIL])) {
			$headerItems[] = array_intersect_key($ldapFieldsInfo[CAKE_LDAP_LDAP_ATTRIBUTE_MAIL], $listCfgFieldsLabel);
		}
		if (!empty($headerItems)) {
			$header[] = $this->_getTextLabelFromList($headerItems);
			$width[] = 50;
			$align[] = 'L';
		}

		switch ($type) {
			case GENERATE_FILE_DATA_TYPE_ALPH:
				$titletext = __('Directory of staff by alphabet');
				$headerItems = [];
				$headerFields = [
					CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT,
					CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION,
					CAKE_LDAP_LDAP_ATTRIBUTE_TITLE,
				];
				foreach ($headerFields as $headerField) {
					if (isset($ldapFieldsInfo[$headerField])) {
						$headerItems[] = array_intersect_key($ldapFieldsInfo[$headerField], $listCfgFieldsLabel);
					}
				}
				if (!empty($headerItems)) {
					$header[] = $this->_getTextLabelFromList($headerItems);
					$width[] = 65;
					$align[] = 'L';
				}
				break;
			case GENERATE_FILE_DATA_TYPE_DEPART:
				$titletext = __('Directory of staff by department');
				if (isset($ldapFieldsInfo[CAKE_LDAP_LDAP_ATTRIBUTE_TITLE])) {
					$header[] = $ldapFieldsInfo[CAKE_LDAP_LDAP_ATTRIBUTE_TITLE]['label'];
					$width[] = 65;
					$align[] = 'L';
				}
				break;
			case null:
			default:
				return false;
		}

		$headerItems = [];
		$headerFields = [
			CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER,
			CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE,
		];
		foreach ($headerFields as $headerField) {
			if (isset($ldapFieldsInfo[$headerField])) {
				$headerItems[] = array_intersect_key($ldapFieldsInfo[$headerField], $listCfgFieldsLabel);
			}
		}
		if (!empty($headerItems)) {
			$header[] = $this->_getTextLabelFromList($headerItems);
			$width[] = 20;
			$align[] = 'C';
		}

		$headerItems = [];
		$headerFields = [
			CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER,
			CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER,
		];
		foreach ($headerFields as $headerField) {
			if (isset($ldapFieldsInfo[$headerField])) {
				$headerItems[] = array_intersect_key($ldapFieldsInfo[$headerField], $listCfgFieldsLabel);
			}
		}
		if (!empty($headerItems)) {
			$header[] = $this->_getTextLabelFromList($headerItems);
			$width[] = 25;
			$align[] = 'C';
		}

		if (isset($ldapFieldsInfo[CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME])) {
			$header[] = $ldapFieldsInfo[CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME]['label'];
			$width[] = 20;
			$align[] = 'C';
		}

		$lineWidth = array_sum($width);
		if (($lineWidth > 0) && ($lineWidth != PDF_LINE_WIDTH)) {
			$scale = PDF_LINE_WIDTH / $lineWidth;
			foreach ($width as &$itemWidth) {
				$itemWidth = (int)$itemWidth * $scale;
			}
		}

		$fileName = $this->expandTypeExportToFilename($type, $extendView, true);
		$orientation = 'P';

		$result = compact(
			'titletext',
			'createDate',
			'company',
			'header',
			'width',
			'align',
			'fileName',
			'orientation'
		);
		Cache::write($cachePath, $result, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);

		return $result;
	}

/**
 * Return array of not processed data for export
 *
 * @param string $type Export type
 * @param bool $extendView If true, include in result extended fields.
 * @param int|string $limit Limit for result
 * @return array|bool Return array of not processed data for export,
 *  or False on failure.
 */
	protected function _getExportData($type = null, $extendView = true, $limit = null) {
		$type = mb_strtolower($type);
		if (!in_array($type, [GENERATE_FILE_DATA_TYPE_DEPART, GENERATE_FILE_DATA_TYPE_ALPH])) {
			return false;
		}

		if (empty($limit)) {
			$limit = EXPORT_DATA_LIMIT;
		}

		$userRole = USER_ROLE_USER;
		if ($extendView) {
			$userRole = USER_ROLE_SECRETARY;
		}

		$targetModels = $this->getSearchTargetModels($userRole);
		if (empty($targetModels) || !isset($targetModels[$this->alias])) {
			return false;
		}

		$conditions = $targetModels[$this->alias]['conditions'];
		$fields = array_keys($targetModels[$this->alias]['fields']);
		$fields[] = 'DepartmentExtension.name';

		$order = [];
		if (in_array($this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME, $fields)) {
			$order[$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME] = 'asc';
		}
		if (in_array($this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME, $fields)) {
			$order[$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME] = 'asc';
		}

		$contain = $targetModels[$this->alias]['contain'];
		$contain[] = 'DepartmentExtension';
		if ($type === GENERATE_FILE_DATA_TYPE_DEPART) {
			$contain[] = 'OrderEmployees';
			$order = [
				'DepartmentExtension.lft' => 'asc',
				$this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'asc',
				'OrderEmployees.lft' => 'asc',
			] + $order;
		}

		$result = $this->find('all', compact('conditions', 'fields', 'contain', 'order', 'limit'));

		return $result;
	}

/**
 * Return array data for export
 *
 * @param string $type Export type
 * @param bool $extendView If true, include in result extended fields.
 * @param string $useFullDepartName Limit for result
 * @return array|bool Return array data for export, or False on failure.
 */
	public function getExportData($type = null, $extendView = true, $useFullDepartName = true) {
		$type = mb_strtolower($type);
		if (!in_array($type, [GENERATE_FILE_DATA_TYPE_DEPART, GENERATE_FILE_DATA_TYPE_ALPH])) {
			return false;
		}

		$employees = $this->_getExportData($type, $extendView, EXPORT_DATA_LIMIT);
		if ($employees === false) {
			return false;
		}

		$countryCode = $this->_modelSetting->getConfig('CountryCode');
		$numberFormat = $this->_modelSetting->getConfig('NumberFormat');
		$phoneNumber = new PhoneNumber();

		$nonStaffPersonnelLabel = $this->getEmptyDepartmentName();
		$data = [];
		$prevFirstLetter = '';
		$groupHeader = '';
		$subHeader = null;
		foreach ($employees as $employee) {
			$allTelephonesData = [];
			$allIntTelephonesData = [];
			$fullDepartment = '';
			$shortDepartment = '';
			$departmentData = Hash::get($employee, 'Department.value');
			if (!empty($departmentData)) {
				$shortDepartment = '<b>' . $departmentData . '</b>';
				$fullDepartment = $departmentData;
				$departmentExtension = Hash::get($employee, 'DepartmentExtension.name');
				if (!empty($departmentExtension)) {
					$fullDepartment = $departmentExtension;
				}
				$fullDepartment = '<b>' . $fullDepartment . '</b>';
			}

			$division = '';
			$divisionData = Hash::get($employee, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION);
			if (!empty($divisionData)) {
				$division = '<b><i>' . $divisionData . '</i></b>';
			}

			$othertelephoneData = Hash::extract($employee, 'Othertelephone.{n}.value');
			if (!empty($othertelephoneData)) {
				$allTelephonesData = Hash::merge($allTelephonesData, $othertelephoneData);
			}

			$mobileData = Hash::get($employee, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER);
			if (!empty($mobileData)) {
				$allTelephonesData[] = $mobileData;
			}

			$otherMobileData = Hash::extract($employee, 'Othermobile.{n}.value');
			if (!empty($otherMobileData)) {
				$allTelephonesData = Hash::merge($allTelephonesData, $otherMobileData);
			}

			$allTelephones = null;
			if (!empty($allTelephonesData)) {
				foreach ($allTelephonesData as &$tel) {
					$tel = $phoneNumber->format($tel, $countryCode, $numberFormat);
				}
				$allTelephones = implode(',<br />', $allTelephonesData);
			} elseif (!is_null($othertelephoneData) || !is_null($mobileData) ||
				!is_null($otherMobileData)) {
				$allTelephones = '';
			}

			$intTelephoneData = Hash::get($employee, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER);
			if (!empty($intTelephoneData)) {
				$allIntTelephonesData[] = $intTelephoneData;
			}

			$sipTelephoneData = Hash::get($employee, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE);
			if (!empty($sipTelephoneData)) {
				$allIntTelephonesData[] = $sipTelephoneData;
			}

			$allIntTelephones = null;
			if (!empty($allIntTelephonesData)) {
				$allIntTelephones = implode(',<br />', $allIntTelephonesData);
			} elseif (!is_null($intTelephoneData) || !is_null($sipTelephoneData)) {
				$allIntTelephones = '';
			}

			$title = '';
			$titleData = Hash::get($employee, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE);
			if (!empty($titleData)) {
				$title = '<i>' . mb_ucfirst($titleData) . '</i>';
			}

			$surnameData = Hash::get($employee, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME);
			$givennameData = Hash::get($employee, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME);
			$middlenameData = Hash::get($employee, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME);
			$displaynameData = Hash::get($employee, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME);
			$nameData = Hash::get($employee, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME);
			$name = null;
			if (!empty($surnameData) && !empty($givennameData) && !empty($middlenameData)) {
				$name = '<b>' . $surnameData . '</b><br />' . $givennameData . ' ' . $middlenameData;
			} elseif (!empty($displaynameData)) {
				$name = '<b>' . $displaynameData . '</b>';
			} elseif (!empty($nameData)) {
				$name = '<b>' . $nameData . '</b>';
			}

			$emailData = Hash::get($employee, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL);
			if (!empty($emailData)) {
				$email = '<i>' . $emailData . '</i>';
				if (!empty($name)) {
					$name .= '<br />';
				}
				$name = (string)$name . $email;
			}

			$office = Hash::get($employee, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME);

			$rowData = [];
			if (!is_null($name)) {
				$rowData[] = $name;
			}

			switch ($type) {
				case GENERATE_FILE_DATA_TYPE_DEPART:
					$subHeader = $nonStaffPersonnelLabel;
					if (!empty($shortDepartment)) {
						$subHeader = ($useFullDepartName ? $fullDepartment : $shortDepartment);
					}

					$groupHeader = '';
					if (!empty($divisionData)) {
						$groupHeader = $divisionData;
					}

					if (!is_null($titleData)) {
						$rowData[] = $title;
					}
					break;
				case GENERATE_FILE_DATA_TYPE_ALPH:
					$firstLetter = '-';
					if (!empty($surnameData)) {
						$firstLetter = mb_strtoupper(mb_substr($surnameData, 0, 1));
					}

					if ($firstLetter != $prevFirstLetter) {
						$prevFirstLetter = $firstLetter;
						$groupHeader = $firstLetter;
					}

					if (!is_null($departmentData) || !is_null($divisionData) || !is_null($titleData)) {
						$rowDataList = [];
						if (!is_null($departmentData) && !empty($fullDepartment)) {
							$rowDataList[] = $fullDepartment;
						}
						if (!is_null($divisionData) && !empty($division)) {
							$rowDataList[] = $division;
						}
						if (!is_null($titleData) && !empty($titleData)) {
							$rowDataList[] = $title;
						}
						$rowData[] = implode('<br />', $rowDataList);
					}
					break;
			}

			if (!is_null($allIntTelephones)) {
				$rowData[] = $allIntTelephones;
			}

			if (!is_null($allTelephones)) {
				$rowData[] = $allTelephones;
			}

			if (!is_null($office)) {
				$rowData[] = $office;
			}

			if (!is_null($subHeader)) {
				$data[$subHeader][$groupHeader][] = $rowData;
			} else {
				$data[$groupHeader][] = $rowData;
			}
		}
		if (isset($data[$nonStaffPersonnelLabel])) {
			$NonStaffPersonnelData = $data[$nonStaffPersonnelLabel];
			unset($data[$nonStaffPersonnelLabel]);
			$data[$nonStaffPersonnelLabel] = $NonStaffPersonnelData;
		}

		return $data;
	}

/**
 * Return path to export directory
 *
 * @return string Return path to export directory
 */
	public function getPathExportDir() {
		if (!empty($this->pathExportDir)) {
			return $this->pathExportDir;
		}

		return EXPORT_DIR;
	}

/**
 * Generate export files: PDF of Excel
 *
 * @param string $viewType Type of export view: PDF or Excel
 * @param string $type Export type
 * @param bool $extendView If true, include data from extended fields.
 * @return bool Success
 */
	protected function _generateExport($viewType = null, $type = null, $extendView = false) {
		if (empty($viewType) || empty($type)) {
			return false;
		}

		$viewType = mb_strtolower($viewType);
		$type = mb_strtolower($type);
		if (!in_array($type, [GENERATE_FILE_DATA_TYPE_DEPART, GENERATE_FILE_DATA_TYPE_ALPH])) {
			return false;
		}

		Configure::write('debug', 0);
		if (function_exists('mb_internal_encoding')) {
			$encoding = Configure::read('App.encoding');
			if (!empty($encoding)) {
				mb_internal_encoding($encoding);
			}
		}
		$fullDepart = true;
		switch ($viewType) {
			case GENERATE_FILE_VIEW_TYPE_PDF:
				$timeLimit = PDF_GENERATE_TIME_LIMIT;
				$viewClass = 'PdfView';
				$ext = '.pdf';
				$signature = '25504446';
				break;
			case GENERATE_FILE_VIEW_TYPE_EXCEL:
				$timeLimit = EXCEL_GENERATE_TIME_LIMIT;
				$viewClass = 'SpreadsheetView';
				if ($type === GENERATE_FILE_DATA_TYPE_DEPART) {
					$fullDepart = false;
				}
				$ext = '.xlsx';
				$signature = '504b0304';
				break;
			default:
				return false;
		}
		set_time_limit($timeLimit);
		$exportConfig = $this->getExportConfig($type, $extendView);
		if (empty($exportConfig)) {
			return false;
		}

		$exportData = $this->getExportData($type, $extendView, $fullDepart);
		if (empty($exportData)) {
			return false;
		}

		$view = new $viewClass();
		$view->viewPath = 'Employees';
		$view->set(compact('exportConfig', 'exportData'));
		$viewData = $view->render('export');
		if (empty($viewData)) {
			return false;
		}

		// Checking file signature
		// https://en.wikipedia.org/wiki/List_of_file_signatures
		$dataSignature = bin2hex(substr($viewData, 0, 4));
		if ($dataSignature !== $signature) {
			return false;
		}

		$pathExportDir = $this->getPathExportDir();
		$fileName = $pathExportDir . $exportConfig['fileName'] . $ext;
		if (file_put_contents($fileName, $viewData, LOCK_EX) === false) {
			return false;
		}

		return true;
	}

/**
 * Task generate export files: PDF, Excel or All
 *
 * @param string $view Type of export view: PDF, Excel or All
 * @param string $type Export type
 * @param int $idTask The ID of the QueuedTask
 * @param bool $forceUpdate Flag of forced update files
 * @return bool Success
 */
	public function generateExportTask($view = null, $type = null, $idTask = null, $forceUpdate = false) {
		$view = mb_strtolower($view);
		$type = mb_strtolower($type);
		$viewTypes = [];
		$generateTypes = [];
		$step = 0;
		$result = true;
		switch ($view) {
			case GENERATE_FILE_VIEW_TYPE_ALL:
				$viewTypes = [GENERATE_FILE_VIEW_TYPE_PDF, GENERATE_FILE_VIEW_TYPE_EXCEL];
				break;
			case GENERATE_FILE_VIEW_TYPE_PDF:
			case GENERATE_FILE_VIEW_TYPE_EXCEL:
				$viewTypes = [$view];
				break;
			default:
				return false;
		}
		switch ($type) {
			case GENERATE_FILE_DATA_TYPE_ALL:
				$generateTypes = [GENERATE_FILE_DATA_TYPE_ALPH, GENERATE_FILE_DATA_TYPE_DEPART];
				break;
			case GENERATE_FILE_DATA_TYPE_ALPH:
			case GENERATE_FILE_DATA_TYPE_DEPART:
				$generateTypes = [$type];
				break;
			default:
				return false;
		}

		$extendView = [true, false];
		$maxStep = count($viewTypes) * count($generateTypes) * count($extendView);
		$errorMessages = [];
		$errorMessage = '';
		$lastUpdateTimestamp = time();
		$modelLastProcessed = ClassRegistry::init('LastProcessed');
		$lastUpdate = $modelLastProcessed->getLastUpdate(LAST_PROCESSED_EMPLOYEE);
		if (!empty($lastUpdate)) {
			$lastUpdateTimestamp = strtotime($lastUpdate);
		}
		$fileExportDir = $this->getPathExportDir();
		$modelExtendQueuedTask = ClassRegistry::init('CakeTheme.ExtendQueuedTask');
		$modelExtendQueuedTask->updateProgress($idTask, 0);
		foreach ($viewTypes as $viewType) {
			$fileExt = '';
			switch ($viewType) {
				case GENERATE_FILE_VIEW_TYPE_PDF:
					$fileExt = '.pdf';
					break;
				case GENERATE_FILE_VIEW_TYPE_EXCEL:
					$fileExt = '.xlsx';
					break;
			}
			foreach ($generateTypes as $generateType) {
				foreach ($extendView as $extendViewState) {
					$resItem = false;
					$fileNameHash = $this->expandTypeExportToFilename($generateType, $extendViewState, true);
					$fileNameString = $this->expandTypeExportToFilename($generateType, $extendViewState, false);
					$filePath = $fileExportDir . $fileNameHash . $fileExt;
					$downloadFileName = $fileNameString . $fileExt;
					if (!$forceUpdate && file_exists($filePath)) {
						$fileChangedTimestamp = filemtime($filePath);
						if (($fileChangedTimestamp !== false) &&
							($fileChangedTimestamp > $lastUpdateTimestamp)) {
							$resItem = null;
						}
					}
					if ($resItem !== null) {
						$resItem = $this->_generateExport($viewType, $generateType, $extendViewState);
					}
					$modelExtendQueuedTask->updateTaskProgress($idTask, $step, $maxStep);
					if ($resItem === null) {
						$errorMessages[] = ' * ' . __('Update file "%s" is not required', $downloadFileName);
					} elseif (!$resItem) {
						$errorMessages[] = ' * ' . __('Error on generating file "%s"', $downloadFileName);
						$result = false;
					}
				}
			}
		}
		if (!empty($idTask) && !empty($errorMessages)) {
			$errorMessage = __('Result of creating files') . ":\n" . implode("\n", $errorMessages);
			$modelExtendQueuedTask->updateTaskErrorMessage($idTask, $errorMessage);
		}

		return $result;
	}

/**
 * Put task of generating export files in the queue
 *
 * @param string $view Type of export view: PDF, Excel or All
 * @param string $type Export type
 * @param bool $forceUpdate Flag of forced update files
 * @throws InternalErrorException if $type or $view is invalid
 * @return bool|array Return False on failure. Otherwise, return array of
 *  created Job containing id, data.
 */
	public function putExportTask($view = null, $type = null, $forceUpdate = false) {
		if (empty($view)) {
			$view = GENERATE_FILE_VIEW_TYPE_ALL;
		} else {
			$view = mb_strtolower($view);
		}
		if (empty($type)) {
			$type = GENERATE_FILE_DATA_TYPE_ALL;
		} else {
			$type = mb_strtolower($type);
		}
		if (!in_array($view, [GENERATE_FILE_VIEW_TYPE_PDF, GENERATE_FILE_VIEW_TYPE_EXCEL, GENERATE_FILE_VIEW_TYPE_ALL]) ||
			!in_array($type, [GENERATE_FILE_DATA_TYPE_DEPART, GENERATE_FILE_DATA_TYPE_ALPH, GENERATE_FILE_DATA_TYPE_ALL])) {
			throw new InternalErrorException(__('Invalid export type'));
		}

		$taskParam = compact('view', 'type', 'forceUpdate');
		$modelExtendQueuedTask = ClassRegistry::init('CakeTheme.ExtendQueuedTask');

		return $modelExtendQueuedTask->createJob('Generate', $taskParam, null, 'export');
	}

/**
 * Return information about exported files
 *
 * @return array Return information about exported files.
 */
	public function getExportInfo() {
		$viewTypes = [GENERATE_FILE_VIEW_TYPE_PDF, GENERATE_FILE_VIEW_TYPE_EXCEL];
		$generateTypes = [GENERATE_FILE_DATA_TYPE_ALPH, GENERATE_FILE_DATA_TYPE_DEPART];
		$extendView = [true, false];
		$result = [];
		$pathExportDir = $this->getPathExportDir();
		foreach ($viewTypes as $viewType) {
			$fileExt = '';
			$fileType = '';
			switch ($viewType) {
				case GENERATE_FILE_VIEW_TYPE_PDF:
					$fileExt = 'pdf';
					$fileType = 'PDF';
					break;
				case GENERATE_FILE_VIEW_TYPE_EXCEL:
					$fileExt = 'xlsx';
					$fileType = 'Excel';
					break;
			}
			foreach ($generateTypes as $generateType) {
				foreach ($extendView as $extendViewState) {
					$fileCreate = null;
					$fileName = $this->expandTypeExportToFilename($generateType, $extendViewState, true);
					$downloadFileName = $this->expandTypeExportToFilename($generateType, $extendViewState, false);
					$filePath = $pathExportDir . $fileName . '.' . $fileExt;
					$fileExists = file_exists($filePath);
					if ($fileExists) {
						$fileCreate = filemtime($filePath);
					}
					$result[] = compact(
						'generateType',
						'viewType',
						'extendViewState',
						'downloadFileName',
						'fileExists',
						'fileCreate',
						'fileExt',
						'fileType'
					);
				}
			}
		}

		return $result;
	}

/**
 * Return plugin name.
 *
 * @return string Return plugin name for breadcrumb.
 */
	public function getPluginName() {
		$pluginName = null;

		return $pluginName;
	}

/**
 * Return name of group data.
 *
 * @return string Return name of group data
 */
	public function getGroupName() {
		$groupName = __('Employees');

		return $groupName;
	}

/**
 * Return name of data.
 *
 * @param int|string|array $id ID of record, or GUID, or 
 *   Distinguished Name of employee or array data for
 *   retrieving name.
 * @return string|bool Return name of data,
 *  or False on failure.
 */
	public function getName($id = null) {
		if (is_array($id)) {
			return parent::getName($id);
		}
		$conditions = $this->_getConditionsForEmployee($id);
		if (empty($conditions)) {
			return false;
		}
		$result = $this->field($this->displayField, $conditions);

		return $result;
	}
}
