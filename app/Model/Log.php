<?php
/**
 * This file is the model file of the application. Used for
 *  management logs.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Model
 */

App::uses('AppModel', 'Model');
App::uses('UserInfo', 'CakeLdap.Utility');
App::uses('ClassRegistry', 'Utility');

/**
 * The model is used to management logs.
 *
 * This model allows:
 * - Retrieve information about log;
 * - Create record of log.
 * - Clear logs.
 *
 * @package app.Model
 */
class Log extends AppModel
{

    /**
     * Name of the model.
     *
     * @var string
     * @link http://book.cakephp.org/2.0/en/models/model-attributes.html#name
     */
    public $name = 'Log';

    /**
     * Custom database table name, or null/false if no table association is desired.
     *
     * @var string
     */
    public $useTable = 'logs';

    /**
     * List of behaviors to load when the model object is initialized. Settings can be
     * passed to behaviors by using the behavior name as index.
     *
     * @var array
     * @link http://book.cakephp.org/2.0/en/models/behaviors.html#using-behaviors
     */
    public $actsAs = [
        'Containable',
        'DeferredSave',
        'CakeTheme.BreadCrumb'
    ];

    /**
     * List of validation rules. It must be an array with the field name as key and using
     * as value one of the following possibilities
     *
     * @var array
     * @link http://book.cakephp.org/2.0/en/models/model-attributes.html#validate
     * @link http://book.cakephp.org/2.0/en/models/data-validation.html
     */
    public $validate = [
        'id' => [
            'naturalNumber' => [
                'rule' => ['naturalNumber'],
                'message' => 'Incorrect primary key',
                'allowEmpty' => false,
                'required' => true,
                'last' => true,
                'on' => 'update'
            ],
        ],
        'user_id' => [
            'naturalNumber' => [
                'rule' => ['naturalNumber'],
                'message' => 'Incorrect foreign key',
                'allowEmpty' => true,
                'required' => true,
                'last' => true,
            ],
        ],
        'employee_id' => [
            'naturalNumber' => [
                'rule' => ['naturalNumber'],
                'message' => 'Incorrect foreign key',
                'allowEmpty' => true,
                'required' => true,
                'last' => true,
            ],
        ],
        'data' => [
            'notBlank' => [
                'rule' => ['notBlank'],
                'message' => 'Incorrect data of log',
                'allowEmpty' => false,
                'required' => true,
                'last' => true
            ],
        ],
    ];

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = [
        'Employee' => [
            'className' => 'Employee',
            'foreignKey' => 'employee_id',
            'conditions' => '',
            'fields' => [
                'Employee.id',
                'Employee.block',
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE,
            ],
            'order' => ''
        ],
        'User' => [
            'className' => 'Employee',
            'foreignKey' => 'user_id',
            'conditions' => '',
            'fields' => [
                'User.id',
                'User.block',
                'User.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
                'User.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE,
            ],
            'order' => ''
        ]
    ];

    /**
     * Return log record.
     *
     * @param int $id Record ID for retrieve information.
     * @param bool $prepareDataForDisplay If True, preparing information
     *  of log save for displaying.
     * @return array|bool Return information about employee,
     *  or False on failure.
     */
    public function get($id = null, $prepareDataForDisplay = false)
    {
        if (empty($id)) {
            return false;
        }

        $fields = [
            $this->alias . '.id',
            $this->alias . '.user_id',
            $this->alias . '.employee_id',
            $this->alias . '.data',
            $this->alias . '.created',
        ];
        $conditions = [
            $this->alias . '.id' => $id
        ];
        $contain = [
            'Employee',
            'User',
        ];

        $result = $this->find('first', compact('fields', 'conditions', 'contain'));
        if (empty($result) || !$prepareDataForDisplay) {
            return $result;
        }

        if (is_array($result[$this->alias]['data'])) {
            $objDataModel = $this->getObjectDataModel();
            $result[$this->alias]['data']['changed'][$objDataModel->alias] = $this->prepareDataForDisplay($result[$this->alias]['data']['changed'][$objDataModel->alias]);
            $result[$this->alias]['data']['current'][$objDataModel->alias] = $this->prepareDataForDisplay($result[$this->alias]['data']['current'][$objDataModel->alias]);
        }

        return $result;
    }

    /**
     * Create log record from data.
     *
     * @param array $data Data of employee to creation log record
     * @return bool|null Return Null, if information about employee is not changed,
     *  otherwise return True, on success or False on failure.
     */
    public function createRecord($data = null)
    {
        $deferredData = $this->getDeferredData($data, USER_ROLE_HUMAN_RESOURCES);
        if (empty($deferredData)) {
            return false;
        }

        $employeeId = $deferredData['employee_id'];
        unset($deferredData['employee_id']);

        $userInfoLib = new UserInfo();
        $userId = $userInfoLib->getUserField('id');
        $dataToSave = [
            $this->alias => [
                'user_id' => $userId,
                'employee_id' => $employeeId,
                'data' => $deferredData,
            ]
        ];
        $this->create();

        return (bool)$this->save($dataToSave);
    }

    /**
     * Clear logs
     *
     * @return bool Success
     */
    public function clearLogs()
    {
        $ds = $this->getDataSource();

        return $ds->truncate($this);
    }

    /**
     * Process group action
     *
     * @param string $groupAction Name of group action for processing
     * @param array $conditions Conditions of group action for processing
     * @return null|bool Return Null, on failure. If success, return True,
     *  False otherwise.
     */
    public function processGroupAction($groupAction = null, $conditions = null)
    {
        if (($groupAction === false) || empty($conditions)) {
            return null;
        }

        $result = false;
        switch ($groupAction) {
            case GROUP_ACTION_LOG_DELETE:
                $result = $this->deleteAll($conditions, false);
                break;
        }

        return $result;
    }

    /**
     * Restore data from log.
     *
     * @param int|string $id ID of record for restoring
     * @param int $userRole User role.
     *  Return Null on success create deferred save, otherwise return
     *  False on failure.
     * @return bool|null Return True on success create deferred save with auto apply.
     *  Return Null on success create deferred save, otherwise return
     *  False on failure.
     * @see Deferred::createDeferredSave();
     */
    public function restoreData($id = null, $userRole = null)
    {
        $log = $this->get($id, false);
        if (empty($log)) {
            return false;
        }

        if (!$log['Log']['data']) {
            return false;
        }

        if (!isset($log['Log']['data']['current']['EmployeeEdit']) ||
            empty($log['Log']['data']['current']['EmployeeEdit']) ||
            !isset($log['Log']['data']['changed']['EmployeeEdit'][CAKE_LDAP_LDAP_DISTINGUISHED_NAME]) ||
            empty($log['Log']['data']['changed']['EmployeeEdit'][CAKE_LDAP_LDAP_DISTINGUISHED_NAME])) {
            return false;
        }

        $dataToRestore = $log['Log']['data']['current'];
        $dataToRestore['EmployeeEdit'][CAKE_LDAP_LDAP_DISTINGUISHED_NAME] = $log['Log']['data']['changed']['EmployeeEdit'][CAKE_LDAP_LDAP_DISTINGUISHED_NAME];
        $modelDeferred = ClassRegistry::init('Deferred');

        return $modelDeferred->createDeferredSave($dataToRestore, $userRole, false, false);
    }

    /**
     * Return name of group data.
     *
     * @return string Return name of group data
     */
    public function getGroupName() {
        $groupName = __('Logs');

        return $groupName;
    }
}
