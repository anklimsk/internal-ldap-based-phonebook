<?php
/**
 * This file is the model file of the application. Used for
 *  saving information about employees.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.Model
 */

App::uses('EmployeeLdap', 'CakeLdap.Model');
App::uses('ClassRegistry', 'Utility');
App::uses('Hash', 'Utility');
App::uses('CakeNumber', 'Utility');
App::uses('PhoneNumber', 'Utility');
App::uses('UserInfo', 'CakeLdap.Utility');

/**
 * The model is used for operation save information about employees.
 *
 * This model allows to perform the following operations:
 *  - validation information before saving;
 *  - prepare save data;
 *  - resize image file *.jpg;
 *  - update and clear employee photo;
 *  - change manager of employee.
 *
 * @package app.Model
 */
class EmployeeEdit extends EmployeeLdap
{

    /**
     * Name of the validation string domain to use when translating validation errors.
     *
     * @var array
     */
    public $validationDomain = 'cake_ldap_validation_errors';

    /**
     * List of validation rules. It must be an array with the field name as key and using
     * as value one of the following possibilities
     *
     * @var array
     * @link http://book.cakephp.org/2.0/en/models/model-attributes.html#validate
     * @link http://book.cakephp.org/2.0/en/models/data-validation.html
     */
    public $validate = [
        CAKE_LDAP_LDAP_DISTINGUISHED_NAME => [
            'rule' => 'notBlank',
            'message' => 'Incorrect distinguished name of employee',
            'required' => false,
            'allowEmpty' => false,
        ],
        CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
            'rule' => ['validTelephonenumber', false],
            'message' => 'This field must contain a valid local telephone number.',
            'required' => false,
            'allowEmpty' => true,
        ],
        CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [
            'rule' => ['validTelephonenumber', true],
            'message' => 'This field must contain a valid mobile telephone number.',
            'required' => false,
            'allowEmpty' => true,
        ],
        CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
            'rule' => ['validTelephonenumber', true],
            'message' => 'This field must contain a valid mobile telephone number.',
            'required' => false,
            'allowEmpty' => true,
        ],
    ];

    /**
     * Object of model `Employee`
     *
     * @var object
     */
    protected $_modelEmployee = null;

    /**
     * Object of model `Setting`
     *
     * @var object
     */
    protected $_modelSetting = null;

    /**
     * Constructor. Binds the model's database table to the object.
     *
     * @param bool|int|string|array $id Set this ID for this model on startup,
     * can also be an array of options, see above.
     * @param string $table Name of database table to use.
     * @param string $ds DataSource connection name.
     */
    public function __construct($id = false, $table = null, $ds = null)
    {
        $this->_modelEmployee = ClassRegistry::init('Employee');
        $this->_modelSetting = ClassRegistry::init('Setting');
        parent::__construct($id, $table, $ds);
    }

    /**
     * Check telephone number is fixed line or is mobile number.
     *
     * @param string|array $data Telephone number.
     * @param bool $isMobileNumber If True, checks the phone number as mobile.
     *  Otherwise, checks as fixed line.
     * @return bool Success
     */
    public function validTelephonenumber($data = null, $isMobileNumber = false)
    {
        if (empty($data)) {
            $data = [];
        } elseif (!is_array($data)) {
            $data = [$data];
        }

        $data = array_shift($data);
        if (empty($data)) {
            return true;
        }

        if (!is_array($data)) {
            $data = [$data];
        }

        $phoneNumber = new PhoneNumber();
        $countryCode = $this->_modelSetting->getConfig('CountryCode');
        $methodName = 'isFixedLineNumber';
        if ($isMobileNumber) {
            $methodName = 'isMobileNumber';
        }

        foreach ($data as $number) {
            if (empty($number)) {
                continue;
            }

            if (!$phoneNumber->$methodName((string)$number, $countryCode)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Called during validation operations, before validation. Please note that custom
     * validation rules can be defined in $validate.
     *
     * Actions:
     *  Create validation rules for user role.
     *
     * @param array $options Options passed from Model::save().
     * @return bool True if validate operation should continue, false to abort
     * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforevalidate
     * @see Model::save()
     */
    public function beforeValidate($options = [])
    {
        $userRole = null;
        if (isset($options['userRole']) && !empty($options['userRole'])) {
            $userRole = $options['userRole'];
        }
        $this->createValidationRules($userRole);

        return true;
    }

    /**
     * Called before every deletion operation.
     *
     * Actions:
     * - Disabling deleting data.
     *
     * @param bool $cascade If true records that depend on this record will also be deleted
     * @return bool True if the operation should continue, false if it should abort
     * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforedelete
     */
    public function beforeDelete($cascade = true)
    {
        return false;
    }

    /**
     * Called after each successful save operation.
     *
     * Actions:
     *  Create record in log.
     *
     * @param bool $created True if this save created a new record
     * @param array $options Options passed from Model::save().
     * @return void
     * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#aftersave
     * @see Model::save()
     */
    public function afterSave($created, $options = [])
    {
        $modelLog = ClassRegistry::init('Log');
        $modelLog->createRecord($this->data);
    }

    /**
     * Create validation rules by table fields
     *
     * @param int $userRole Bit mask of user role for
     *  create validation rules.
     * @return bool Return success.
     */
    public function createValidationRules($userRole = null)
    {
        $validationRules = $this->_getValidationRules($userRole);
        if (empty($validationRules)) {
            return false;
        }

        $validator = $this->validator();
        foreach ($validationRules as $validationField => $validationRule) {
            $validator[$validationField] = $validationRule;
        }

        return true;
    }

    /**
     * Return list of validation fules
     *
     * @param int $userRole Bit mask of user role for
     *  retrieve validation rules.
     * @return array Return list of validation rules.
     */
    protected function _getValidationRules($userRole = null)
    {
        $result = [];
        $ldapFields = $this->_modelEmployee->getLdapFieldsInfoForUserRole($userRole);
        if (empty($ldapFields)) {
            return $result;
        }

        $readOnlyFields = $this->_modelEmployee->getListReadOnlyFieldsLdap();
        if (!empty($readOnlyFields)) {
            $ldapFields = array_diff_key($ldapFields, array_flip($readOnlyFields));
        }
        foreach ($ldapFields as $ldapFieldName => $ldapFieldInfo) {
            if (!isset($ldapFieldInfo['rules']) || empty($ldapFieldInfo['rules'])) {
                continue;
            }

            $result[$ldapFieldName] = $ldapFieldInfo['rules'];
        }

        return $result;
    }

    /**
     * Return Distinguished Name of employee by GUID
     *
     * @param string $guid GUID of employee
     * @return string|bool Return Distinguished Name of employee,
     *  or False on failure.
     */
    public function getDnEmployee($guid = null)
    {
        if (empty($guid)) {
            return false;
        }

        $conditions = [
            CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => $guid
        ];
        $fields = [
            CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME
        ];
        $data = $this->find('first', compact('conditions', 'fields'));
        if (empty($data)) {
            return false;
        }

        $result = Hash::get($data, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME);
        if (empty($result)) {
            return false;
        }

        return $result;
    }

    /**
     * Return information about employee by GUID or Distinguished Name.
     *
     * @param string $guid GUID or Distinguished Name of employee
     * @param int $userRole Bit mask of user role for
     *  retrieve information.
     * @param bool $useLdap If True, use information from LDAP server.
     *  Otherwise use database.
     * @return array|bool Return information about employee,
     *  or False on failure.
     */
    public function get($guid = null, $userRole = null, $useLdap = false)
    {
        if (empty($guid)) {
            return false;
        }

        if (empty($userRole)) {
            $userRole = USER_ROLE_USER;
        }
        $guid = (string)$guid;
        $userRole = (int)$userRole;
        $useLdap = (bool)$useLdap;

        if (!$useLdap) {
            $result = [];
            $excludeFields = $this->_modelEmployee->getListExcludeFieldsDb($userRole);
            $contain = [];
            if (!in_array('manager_id', $excludeFields)) {
                $contain['Manager']['fields'] = [CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME];
            }
            $data = $this->_modelEmployee->get($guid, $excludeFields, false, null, $contain);
            if (empty($data) || $data[$this->_modelEmployee->alias]['block']) {
                return $result;
            }

            $modelConfigSync = ClassRegistry::init('CakeLdap.ConfigSync');
            $localFieldsInfo = $modelConfigSync->getLocalFieldsInfo();
            $result[$this->alias] = array_diff_key($data[$this->_modelEmployee->alias], $localFieldsInfo);
            if (isset($result[$this->alias][CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME])) {
                $result[$this->alias][CAKE_LDAP_LDAP_DISTINGUISHED_NAME] = $result[$this->alias][CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME];
            }
            foreach ($data as $bindModelName => $bindData) {
                switch ($bindModelName) {
                    case 'Department':
                        $result[$this->alias][CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT] = (string)Hash::get($bindData, 'value');
                        break;
                    case 'Manager':
                        $result[$this->alias][CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER] = (string)Hash::get($bindData, CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME);
                        break;
                    case 'Othertelephone':
                        $result[$this->alias][CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER] = Hash::extract($bindData, '{n}.value');
                        break;
                    case 'Othermobile':
                        $result[$this->alias][CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER] = Hash::extract($bindData, '{n}.value');
                        break;
                }
            }

            return $result;
        }

        $cachePath = 'employee_info_' . md5(serialize(compact('guid', 'userRole', 'useLdap')));
        $cached = Cache::read($cachePath, CACHE_KEY_EMPLOYEES_LOCAL_INFO);
        if ($cached !== false) {
            return $cached;
        }

        $ldapFields = $this->_modelEmployee->getLdapFieldsInfoForUserRole($userRole);
        if (empty($ldapFields)) {
            return false;
        }

        $fields = array_keys($ldapFields);
        $conditions = [];
        if (isGuid($guid)) {
            $conditions[CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID] = $guid;
        } else {
            $conditions[CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME] = $guid;
        }
        $this->recursive = -1;
        $result = $this->find('first', compact('fields', 'conditions'));
        if (empty($result)) {
            return $result;
        }

        Cache::write($cachePath, $result, CACHE_KEY_EMPLOYEES_LOCAL_INFO);

        return $result;
    }

    /**
     * Return list of read only fields include model name.
     *
     * @return array Return list of read only fields.
     */
    public function getListReadOnlyFields()
    {
        return $this->_modelEmployee->getListReadOnlyFieldsLdap($this->alias);
    }

    /**
     * Return limit of size for upload photo.
     *
     * @return int Return limit of size photo, bytes.
     */
    public function getLimitPhotoSize()
    {
        $result = (int)UPLOAD_FILE_SIZE_LIMIT;

        return $result;
    }

    /**
     * Return allowed extensions of files for upload (PCRE).
     *
     * @param bool $returnServer If True, return result for server.
     *  Otherwise return for client.
     * @return string Return Allowed extensions of files for upload.
     */
    public function getAcceptFileTypes($returnServer = false)
    {
        if ($returnServer) {
            $result = (string)UPLOAD_FILE_TYPES_SERVER;
        } else {
            $result = (string)UPLOAD_FILE_TYPES_CLIENT;
        }

        return $result;
    }

    /**
     * Return limit of lines for multiple value fields.
     *
     * @return int Return limit of lines.
     */
    public function getLimitLinesMultipleValue()
    {
        $result = (int)MULTIPLE_VALUE_FIELD_ROWS_LIMIT;
        $multipleValueLimit = (int)$this->_modelSetting->getConfig('MultipleValueLimit');
        if (!empty($multipleValueLimit)) {
            $result = $multipleValueLimit;
        }

        return $result;
    }

    /**
     * Return list of managers employee in format:
     *  - `key`: distinguished name of employee;
     *  - `value`: name and position of employee.
     *
     * @return array Return list of managers employee.
     */
    public function getListManagers()
    {
        $cachePath = 'list_of_managers';
        $cached = Cache::read($cachePath, CACHE_KEY_EMPLOYEES_LOCAL_INFO);
        if ($cached !== false) {
            return $cached;
        }

        $fields = [
            CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME,
            CAKE_LDAP_LDAP_ATTRIBUTE_TITLE,
            CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
        ];
        $conditions = [
            CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => '*',
            CAKE_LDAP_LDAP_ATTRIBUTE_NAME => '*',
        ];
        $order = CAKE_LDAP_LDAP_ATTRIBUTE_NAME;
        $company = $this->_modelSetting->getConfig('Company');
        if (!empty($company)) {
            $conditions[CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY] = $company;
        }
        $this->recursive = -1;
        $data = $this->find('all', compact('fields', 'conditions', 'order'));
        $result = [];
        if (empty($data)) {
            return $result;
        }

        $result = [];
        foreach ($data as $dataItem) {
            $employeeInfo = $dataItem[$this->alias][CAKE_LDAP_LDAP_ATTRIBUTE_NAME];
            if (!empty($dataItem[$this->alias][CAKE_LDAP_LDAP_ATTRIBUTE_TITLE])) {
                $employeeInfo .= ' - ' . $dataItem[$this->alias][CAKE_LDAP_LDAP_ATTRIBUTE_TITLE];
            }
            $result[$dataItem[$this->alias][CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME]] = $employeeInfo;
        }
        Cache::write($cachePath, $result, CACHE_KEY_EMPLOYEES_LOCAL_INFO);

        return $result;
    }

    /**
     * Return list of labels for fields
     *
     * @param bool $useAlternative If True, use alternative label,
     *  or normanl label otherwise.
     * @param int $userRole Bit mask of user role for
     *  retrieve list of labels.
     * @return array Return list of labels for fields.
     */
    public function getListFieldsLabel($useAlternative = false, $userRole = null)
    {
        if (empty($userRole)) {
            $userRole = USER_ROLE_USER;
        }
        $useAlternative = (bool)$useAlternative;
        $userRole = (int)$userRole;
        $cachePath = 'employee_fields_label_' . md5(serialize(compact('useAlternative', 'userRole')));
        $cached = Cache::read($cachePath, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);
        if ($cached !== false) {
            return $cached;
        }

        $result = [];
        $fields = $this->_modelEmployee->getLdapFieldsInfoForUserRole($userRole);
        if (empty($fields)) {
            return $result;
        }

        $labelField = 'label';
        if ($useAlternative) {
            $labelField = 'altLabel';
        }
        foreach ($fields as $fieldName => $fieldInfo) {
            $label = $fieldName;
            if (isset($fieldInfo[$labelField]) && !empty($fieldInfo[$labelField])) {
                $label = $fieldInfo[$labelField];
            }
            $result[$this->alias . '.' . $fieldName] = $label;
        }
        Cache::write($cachePath, $result, CAKE_LDAP_CACHE_KEY_LDAP_SYNC_DB);

        return $result;
    }

    /**
     * Preparing data for saving
     *
     * Actions:
     *  - Excluding read only fields;
     *  - Strip whitespace from the beginning and end of a string value;
     *  - Removing field of photo if empty value;
     *  - Convert the case;
     *  - Converting telephone numbers in format E164.
     *
     * @param array $data Data to prepare.
     * @return bool Success
     */
    public function prepareDataForSave(&$data)
    {
        if (empty($data) || !is_array($data) || !isset($data[$this->alias]) ||
            !isset($data[$this->alias][CAKE_LDAP_LDAP_DISTINGUISHED_NAME]) ||
            empty($data[$this->alias][CAKE_LDAP_LDAP_DISTINGUISHED_NAME])) {
            return false;
        }

        $excludeFields = $this->getListReadOnlyFields();
        foreach ($excludeFields as $excludeField) {
            $data = Hash::remove($data, $excludeField);
        }

        $countryCode = $this->_modelSetting->getConfig('CountryCode');
        $phoneNumber = new PhoneNumber();
        $orderFields = array_flip([
            CAKE_LDAP_LDAP_DISTINGUISHED_NAME,
            CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME,
            CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME,
            CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME,
            CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS,
            CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
        ]);
        $orderFields = array_intersect_key($orderFields, $data[$this->alias]);
        $data[$this->alias] = array_merge($orderFields, $data[$this->alias]);
        foreach ($data[$this->alias] as $field => &$value) {
            if (is_string($value)) {
                $value = trim($value);
            }
        }
        unset($value);
        foreach ($data[$this->alias] as $field => &$value) {
            switch ($field) {
                case CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME:
                case CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME:
                case CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME:
                    if (!empty($value)) {
                        $value = mb_convert_case(mb_strtolower($value), MB_CASE_TITLE);
                    }
                    break;
                case CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS:
                    if (!empty($value)) {
                        continue;
                    }

                    $givenName = Hash::get($data, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME);
                    $middleName = Hash::get($data, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME);
                    if (empty($value) && (empty($givenName) || empty($middleName))) {
                        continue;
                    }

                    $initials = mb_substr($givenName, 0, 1) . '.' . mb_substr($middleName, 0, 1) . '.';
                    $value = mb_strtoupper($initials);
                    break;
                case CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME:
                    if (!empty($value)) {
                        continue;
                    }

                    $name = Hash::get($data, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME);
                    $surName = Hash::get($data, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME);
                    $initials = Hash::get($data, $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS);
                    if (!empty($surName) && !empty($initials)) {
                        $value = $surName . ' ' . $initials;
                    } elseif (!empty($name)) {
                        $value = $name;
                    }
                    break;
                case CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT:
                case CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION:
                    if (empty($value) || ($value !== mb_strtolower($value))) {
                        continue;
                    }

                    if (count(preg_split('/[^\p{L}\p{N}\']+/u', $value)) == 1) {
                        $value = mb_strtoupper($value);
                    } else {
                        $value = mb_ucfirst(mb_strtolower($value));
                    }
                    break;
                case CAKE_LDAP_LDAP_ATTRIBUTE_TITLE:
                    if (!empty($value)) {
                        $value = mb_ucfirst(mb_strtolower($value));
                    }
                    break;
                case CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER:
                case CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER:
                case CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER:
                    if (!empty($value)) {
                        if (is_array($value)) {
                            foreach ($value as $i => &$valueItem) {
                                $valueItem = $phoneNumber->format($valueItem, $countryCode, 'E164');
                                if (empty($valueItem)) {
                                    unset($value[$i]);
                                }
                            }
                            unset($valueItem);
                            $value = array_values($value);
                        } else {
                            $value = $phoneNumber->format($value, $countryCode, 'E164');
                        }
                    }
                    break;
            }
        }
        unset($value);

        return true;
    }

    /**
     * Saving information in LDAP or creation deferred save based on user role
     *
     * @param array $data Data for saving
     * @param int $userRole Bit mask of user role for
     *  create validation rules and checking allow saving in LDAP.
     * @param bool $validate If True, uses validation of data before saving.
     *  Otherwise saving without validation of data.
     * @param bool $includeExistsDeferredSaveInfo If True, include deferred
     *  save information of employee, if exists.
     * @return bool|null Return True on success create deferred save with auto apply.
     *  Return Null on success create deferred save, otherwise return
     *  False on failure.
     */
    public function saveInformation($data = null, $userRole = null, $validate = true, $includeExistsDeferredSaveInfo = false)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        if (empty($userRole)) {
            $userRole = USER_ROLE_USER;
        }

        $this->set($data);
        if ($validate && !$this->validates(compact('userRole'))) {
            return false;
        }

        if (!$this->prepareDataForSave($this->data)) {
            return false;
        }

        $modelDeferred = ClassRegistry::init('Deferred');

        return $modelDeferred->createDeferredSave($this->data, $userRole, $includeExistsDeferredSaveInfo, true);
    }

    /**
     * Update field in LDAP or creation deferred save based on user role
     *
     * @param string $guid GUID or Distinguished Name of employee
     * @param string $fieldName Name of field for update
     * @param mixed $fieldValue Value of field for update
     * @param int $userRole Bit mask of user role for
     *  retrieve information and checking allow saving in LDAP.
     * @param bool $useLdap If True, use information from LDAP server.
     *  Otherwise use database.
     * @throws NotFoundException if record for parameter $guid was not found
     * @return bool|null Return True on success create deferred save with auto apply.
     *  Return Null on success create deferred save, otherwise return
     *  False on failure.
     */
    protected function _updateField($guid = null, $fieldName = null, $fieldValue = null, $userRole = null, $useLdap = false)
    {
        $employeeInfo = $this->get($guid, $userRole, $useLdap);
        if (empty($employeeInfo)) {
            throw new NotFoundException(__('Invalid the GUID or Distinguished name of an LDAP entity'));
        }

        if (empty($fieldName)) {
            return false;
        }

        $employeeInfo[$this->alias][$fieldName] = $fieldValue;
        $includeFields = [
            CAKE_LDAP_LDAP_DISTINGUISHED_NAME,
            CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
            $fieldName,
        ];
        $employeeInfo[$this->alias] = array_intersect_key($employeeInfo[$this->alias], array_flip($includeFields));

        return $this->saveInformation($employeeInfo, $userRole, false, true);
    }

    /**
     * Resize image file *.jpg
     *
     * @param string $filename File name for resize.
     * @param int $tn_w New width of image file.
     * @param int $tn_h New height of image file.
     * @param int $quality Quality ranges from 0 (worst quality,
     *  smaller file) to 100 (best quality, biggest file).
     * @return bool True, if resize image file successfully, false otherwise.
     * @link http://stackoverflow.com/a/4478236
     */
    public function resizePhoto($filename = null, $tn_w = PHOTO_WIDTH, $tn_h = PHOTO_HEIGHT, $quality = 75)
    {
        $quality = (int)$quality;
        if ($quality < 0) {
            $quality = 75;
        }

        if (empty($filename) || !file_exists($filename) ||
            !is_file($filename) || empty($tn_w) || empty($tn_h)) {
            return false;
        }

        if (exif_imagetype($filename) !== IMAGETYPE_JPEG) {
            return false;
        }

        $source = imagecreatefromjpeg($filename);
        $src_w = imagesx($source);
        $src_h = imagesy($source);

        $x_ratio = $tn_w / $src_w;
        $y_ratio = $tn_h / $src_h;

        if (($src_w <= $tn_w) && ($src_h <= $tn_h)) {
            $new_w = $src_w;
            $new_h = $src_h;
        } elseif (($x_ratio * $src_h) < $tn_h) {
            $new_h = ceil($x_ratio * $src_h);
            $new_w = $tn_w;
        } else {
            $new_w = ceil($y_ratio * $src_w);
            $new_h = $tn_h;
        }

        $newpic = imagecreatetruecolor(round($new_w), round($new_h));
        imagecopyresampled($newpic, $source, 0, 0, 0, 0, $new_w, $new_h, $src_w, $src_h);
        $final = imagecreatetruecolor($tn_w, $tn_h);
        $backgroundColor = imagecolorallocate($final, 255, 255, 255);
        imagefill($final, 0, 0, $backgroundColor);
        imagecopy($final, $newpic, (($tn_w - $new_w) / 2), (($tn_h - $new_h) / 2), 0, 0, $new_w, $new_h);

        return imagejpeg($final, $filename, $quality);
    }

    /**
     * Update photo of employee.
     *
     * @param string $guid GUID or Distinguished Name of employee
     * @param string $fileName Path to file of photo
     * @param int $maxFileSize Limit of size udated photo
     * @param int $userRole Bit mask of user role for
     *  checking allow saving in LDAP.
     * @param bool $useLdap If True, use information from LDAP server.
     *  Otherwise use database.
     * @return bool|null Return True on success create deferred save with auto apply.
     *  Return Null on success create deferred save, otherwise return
     *  False on failure.
     */
    public function updatePhoto($guid = null, $fileName = null, $maxFileSize = UPLOAD_FILE_SIZE_LIMIT, $userRole = null, $useLdap = false)
    {
        $result = false;
        if (empty($fileName) || !file_exists($fileName) ||
            !is_file($fileName)) {
            $result = __('Invalid file for update.');

            return $result;
        }

        $fileSize = filesize($fileName);
        if (($fileSize !== false) && ($fileSize > $maxFileSize)) {
            $result = __(
                'File size is %s. Limit - %s.',
                CakeNumber::toReadableSize($fileSize),
                CakeNumber::toReadableSize($maxFileSize)
            );

            return $result;
        }

        if (exif_imagetype($fileName) !== IMAGETYPE_JPEG) {
            $result = __('The uploaded photo is not a jpeg');

            return $result;
        }

        if (!$this->resizePhoto($fileName, PHOTO_WIDTH, PHOTO_HEIGHT)) {
            $result = __('Error resizing file photo.');

            return $result;
        }

        $filePhotoContent = file_get_contents($fileName);
        if ($filePhotoContent === false) {
            $result = __('Error reading file photo.');

            return $result;
        }

        return $this->_updateField($guid, CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO, $filePhotoContent, $userRole, $useLdap);
    }

    /**
     * Clear photo of employee.
     *
     * @param string $guid GUID or Distinguished Name of employee
     * @param int $userRole Bit mask of user role for
     *  checking allow saving in LDAP.
     * @param bool $useLdap If True, use information from LDAP server.
     *  Otherwise use database.
     * @return bool|null Return True on success create deferred save with auto apply.
     *  Return Null on success create deferred save, otherwise return
     *  False on failure.
     */
    public function clearPhoto($guid = null, $userRole = null, $useLdap = false)
    {
        return $this->_updateField($guid, CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO, '', $userRole, $useLdap);
    }

    /**
     * Change manager of employee.
     *
     * @param string $employeeDn Distinguished Name of employee
     * @param string|null $managerDn Distinguished Name of manager
     * @param int $userRole Bit mask of user role for
     *  checking allow saving in LDAP.
     * @param bool $useLdap If True, use information from LDAP server.
     *  Otherwise use database.
     * @return bool|null Return True on success create deferred save with auto apply.
     *  Return Null on success create deferred save, otherwise return
     *  False on failure.
     */
    public function changeManager($employeeDn = null, $managerDn = null, $userRole = null, $useLdap = false)
    {
        if (!empty($employeeDn) && !empty($managerDn) &&
            ($employeeDn === $managerDn)) {
                return false;
        }

        return $this->_updateField($employeeDn, CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER, (string)$managerDn, $userRole, $useLdap);
    }

    /**
     * Change department of employee.
     *
     * @param string $employeeDn Distinguished Name of employee
     * @param string|null $departmentName Name of department
     * @param int $userRole Bit mask of user role for
     *  checking allow saving in LDAP.
     * @param bool $useLdap If True, use information from LDAP server.
     *  Otherwise use database.
     * @return bool|null Return True on success create deferred save with auto apply.
     *  Return Null on success create deferred save, otherwise return
     *  False on failure.
     */
    public function changeDepartment($employeeDn = null, $departmentName = null, $userRole = null, $useLdap = false)
    {
        return $this->_updateField($employeeDn, CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT, (string)$departmentName, $userRole, $useLdap);
    }

    /**
     * Return list of employees by department name
     *
     * @param string $name Name of department
     * @param int|string $limit Limit for result
     * @return array Return list of employees Distinguished Name.
     */
    public function getListEmployeesByDepartmentName($name = null, $limit = CAKE_LDAP_SYNC_AD_LIMIT)
    {
        $result = [];
        if (empty($name)) {
            return $result;
        }
        $fields = [
            CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME,
        ];
        $conditions = [CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => $name];
        $company = $this->_modelSetting->getConfig('Company');
        if (!empty($company)) {
            $conditions[CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY] = $company;
        }
        $order = CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME;
        $data = $this->find('all', compact('fields', 'conditions', 'order', 'limit'));
        if (empty($data)) {
            return $result;
        }
        $result = Hash::extract($data, '{n}.' . $this->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME);

        return $result;
    }

    /**
     * Return list of value option input for fields
     *
     * @param string $optionName Name of option for retrieve list
     * @return array Return list of value option input for fields.
     */
    protected function _getListFieldsOption($optionName = null)
    {
        $result = [];
        if (empty($optionName)) {
            return $result;
        }

        $modelConfigSync = ClassRegistry::init('CakeLdap.ConfigSync');
        $ldapFieldsInfo = $modelConfigSync->getLdapFieldsInfo();
        if (empty($ldapFieldsInfo)) {
            return $result;
        }

        foreach ($ldapFieldsInfo as $fieldName => $fieldInfo) {
            if (!isset($fieldInfo[$optionName]) || empty($fieldInfo[$optionName])) {
                continue;
            }

            $result[$this->alias . '.' . $fieldName] = $fieldInfo[$optionName];
        }

        return $result;
    }

    /**
     * Return list of input mask for fields
     *
     * @return array Return list of input mask for fields.
     * @link https://github.com/RobinHerbots/Inputmask
     */
    public function getListFieldsInputMask()
    {
        $cachePath = 'list_fields_inputmask';
        $cached = Cache::read($cachePath, CAKE_LDAP_CACHE_KEY_CONFIG);
        if ($cached !== false) {
            return $cached;
        }

        $result = $this->_getListFieldsOption('inputmask');
        Cache::write($cachePath, $result, CAKE_LDAP_CACHE_KEY_CONFIG);

        return $result;
    }

    /**
     * Return list of tooltip input for fields
     *
     * @return array Return list of tooltip input for fields.
     */
    public function getListFieldsInputTooltip()
    {
        $cachePath = 'list_fields_input_tooltip';
        $cached = Cache::read($cachePath, CAKE_LDAP_CACHE_KEY_CONFIG);
        if ($cached !== false) {
            return $cached;
        }

        $result = $this->_getListFieldsOption('tooltip');
        Cache::write($cachePath, $result, CAKE_LDAP_CACHE_KEY_CONFIG);

        return $result;
    }

    /**
     * Return list of managers for select input
     *
     * @param string $query Query string
     * @param string $excludeDn Distinguished name for exclude
     *  from result
     * @return array Return list of managers for select input
     */
    public function getListManagersByQuery($query = null, $excludeDn = null)
    {
        $result = [];
        $query = trim($query);
        if (empty($query)) {
            return $result;
        }

        $managers = $this->getListManagers();
        if (empty($managers)) {
            return $result;
        }

        foreach ($managers as $managerDn => $managerName) {
            if (($excludeDn === $managerDn) ||
                (mb_stripos($managerName, $query) === false)) {
                continue;
            }

            $result[] = [
                'value' => $managerDn,
                'text' => $managerName,
            ];
        }

        return $result;
    }
}
