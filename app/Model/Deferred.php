<?php
/**
 * This file is the model file of the application. Used for
 *  management deferred savings.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.Model
 */

App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');
App::uses('Hash', 'Utility');
App::uses('CakeText', 'Utility');
App::uses('UserInfo', 'CakeLdap.Utility');
App::uses('CakeSession', 'Model/Datasource');

/**
 * The model is used for management deferred savings.
 *
 * This model allows:
 * - Retrieve, Edit, Delete, Approve or Reject information about deferred savings;
 * - Send E-mail notifications by list.
 *
 * @package app.Model
 */
class Deferred extends AppModel
{

    /**
     * The name of the DataSource connection that this Model uses
     *
     * @var string
     */
    public $useDbConfig = 'default';

    /**
     * The name of the primary key field for this model.
     *
     * @var string
     */
    public $primaryKey = 'id';

    /**
     * Custom database table name, or null/false if no table association is desired.
     *
     * @var string
     */
    public $useTable = 'deferred';

    /**
     * List of behaviors to load when the model object is initialized.
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
     * List of validation rules.
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
        'employee_id' => [
            'naturalNumber' => [
                'rule' => ['naturalNumber'],
                'message' => 'Incorrect foreign key',
                'allowEmpty' => false,
                'required' => true,
                'last' => true,
            ],
            'isUnique' => [
                'rule' => ['isUnique'],
                'message' => 'Deferred save for employee is not unique',
                'allowEmpty' => false,
                'required' => true,
                'last' => true
            ],
        ],
        'data' => [
            'notBlank' => [
                'rule' => ['notBlank'],
                'message' => 'Incorrect data of deferred save',
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
            'dependent' => false,
            'fields' => [
                'Employee.id',
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE,
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL,
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID,
            ],
        ]
    ];

    /**
     * Called after each successful save operation.
     *
     * Actions:
     *  - Clear cache.
     *
     * @param bool $created True if this save created a new record
     * @param array $options Options passed from Model::save().
     * @return void
     * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#aftersave
     * @see Model::save()
     */
    public function afterSave($created, $options = [])
    {
        Cache::delete('number_of_deferred_saves', CACHE_KEY_STATISTICS_INFO);
    }

    /**
     * Called after every deletion operation.
     *
     * Actions:
     *  - Clear cache.
     *
     * @return void
     * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#afterdelete
     */
    public function afterDelete()
    {
        Cache::delete('number_of_deferred_saves', CACHE_KEY_STATISTICS_INFO);
    }

    /**
     * Return the number of deferred saves.
     *
     * @return int Number of deferred saves.
     */
    public function getNumberOf()
    {
        $cachePath = 'number_of_deferred_saves';
        $cached = Cache::read($cachePath, CACHE_KEY_STATISTICS_INFO);
        if ($cached !== false) {
            return $cached;
        }

        $conditions = [$this->alias . '.internal' => false];
        $result = (int)$this->find('count', compact('conditions'));
        Cache::write($cachePath, $result, CACHE_KEY_STATISTICS_INFO);

        return $result;
    }

    /**
     * Create deferred save from data.
     *
     * @param array $data Data of employee to creation deferred save
     * @param int $userRole User role.
     * @param bool $includeExistsDeferredSaveInfo If True, include deferred
     *  save information of employee, if exists.
     * @param bool $keepId If True, keep record ID, if exists.
     * @return bool|null Return True on success create deferred save with auto apply.
     *  Return Null on success create deferred save, otherwise return
     *  False on failure.
     */
    public function createDeferredSave($data = null, $userRole = null, $includeExistsDeferredSaveInfo = false, $keepId = true)
    {
        if (empty($userRole)) {
            $userRole = USER_ROLE_USER;
        }

        $id = null;
        $internal = null;
        $deferredSave = null;
        $objDataModel = $this->getObjectDataModel();
        if (!isset($data[$objDataModel->alias][CAKE_LDAP_LDAP_DISTINGUISHED_NAME]) ||
            empty($data[$objDataModel->alias][CAKE_LDAP_LDAP_DISTINGUISHED_NAME])) {
            return false;
        }

        $dn = $data[$objDataModel->alias][CAKE_LDAP_LDAP_DISTINGUISHED_NAME];
        if ($keepId || $includeExistsDeferredSaveInfo) {
            $deferredSave = $this->get($dn, false, $userRole, 'dn');
        }
        if (!empty($deferredSave)) {
            if ($keepId) {
                $id = $deferredSave[$this->alias]['id'];
            }
            if ($includeExistsDeferredSaveInfo) {
                $internal = $deferredSave[$this->alias]['internal'];
                if (is_array($deferredSave[$this->alias]['data'])) {
                    $data[$objDataModel->alias] = array_merge($deferredSave[$this->alias]['data']['changed'][$objDataModel->alias], $data[$objDataModel->alias]);
                }
            }
        }
        if (!$keepId) {
            $this->validator()->remove('employee_id', 'isUnique');
        }
        $deferredData = $this->getDeferredData($data, $userRole);
        if (empty($deferredData)) {
            return false;
        }

        $employeeId = $deferredData['employee_id'];
        unset($deferredData['employee_id']);
        if ($internal === null) {
            $internal = false;
            $userInfoLib = new UserInfo();
            $userInfoData = ['role' => (int)$userRole];
            if ($userInfoLib->checkUserRole([USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN], true, $userInfoData)) {
                $internal = true;
            }
        }

        $dataToSave = [
            $this->alias => [
                'employee_id' => $employeeId,
                'data' => $deferredData,
                'internal' => $internal,
            ]
        ];
        if (empty($id)) {
            $this->create();
        } else {
            $dataToSave[$this->alias]['id'] = $id;
        }
        $result = (bool)$this->save($dataToSave);
        if ($result) {
            if ($internal) {
                $conditions = [$this->alias . '.internal' => true];
                $result = (bool)$this->processGroupAction(GROUP_ACTION_DEFERRED_SAVE_INTERNAL_APPROVE, $conditions);
            } else {
                $result = null;
            }
        }

        return $result;
    }

    /**
     * Return information of deferred save.
     *
     * @param int|string $id ID of record deferred save, or GUID of employee,
     *  employee ID.
     * @param bool $includeLdapEmployeeInfo If True, include current
     *  information of employee from LDAP
     * @param int $userRole User role for including current
     *  information of employee from LDAP.
     * @param string $foreignKeyType Type foreign key. Must be one of:
     *  - `id`: use deferred save ID;
     *  - `employee_id`: use employee ID;
     *  - `guid`: use employee GUID;
     *  - `dn`: use employee Distinguished name.
     * @param bool $useLdap If True, use information from LDAP server.
     *  Otherwise use database.
     * @return array|bool Return information of deferred save, or False on failure.
     */
    public function get($id = null, $includeLdapEmployeeInfo = false, $userRole = null, $foreignKeyType = null, $useLdap = false)
    {
        if (empty($id)) {
            return false;
        }

        if (!empty($foreignKeyType)) {
            $foreignKeyType = strtolower($foreignKeyType);
        }
        $fields = [
            $this->alias . '.id',
            $this->alias . '.employee_id',
            $this->alias . '.internal',
            $this->alias . '.data',
            $this->alias . '.created',
            $this->alias . '.modified',
        ];
        $conditions = [];
        switch ($foreignKeyType) {
            case 'employee_id':
                $conditions[$this->alias . '.employee_id'] = $id;
                break;
            case 'guid':
                $conditions['Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID] = $id;
                break;
            case 'dn':
                $conditions['Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME] = $id;
                break;
            case 'id':
            default:
                $conditions[$this->alias . '.id'] = $id;
        }
        $contain = [
            'Employee'
        ];
        $result = $this->find('first', compact('fields', 'conditions', 'contain'));
        if (empty($result)) {
            return false;
        }

        $objDataModel = $this->getObjectDataModel();
        if (!empty($result) && !$includeLdapEmployeeInfo && is_array($result[$this->alias]['data'])) {
            $result[$this->alias]['data']['changed'][$objDataModel->alias] = $this->prepareDataForDisplay($result[$this->alias]['data']['changed'][$objDataModel->alias]);
            $result[$this->alias]['data']['current'][$objDataModel->alias] = $this->prepareDataForDisplay($result[$this->alias]['data']['current'][$objDataModel->alias]);
        }
        if (empty($result) || !$includeLdapEmployeeInfo) {
            return $result;
        }

        $result['ChangedFields'] = [];
        if (!is_array($result[$this->alias]['data'])) {
            return $result;
        }

        $employeeInfoLdap = $objDataModel->get($result['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID], $userRole, $useLdap);
        $result['ChangedFields'] = array_keys($result[$this->alias]['data']['changed'][$objDataModel->alias]);
        if (!empty($result['ChangedFields'])) {
            $result['ChangedFields'] = array_values(array_diff($result['ChangedFields'], [CAKE_LDAP_LDAP_DISTINGUISHED_NAME]));
        }
        array_walk($result['ChangedFields'], create_function('&$v,$k,$a', '$v = "$a.$v";'), $objDataModel->alias);
        if (!empty($employeeInfoLdap)) {
            $result[$this->alias]['data']['changed'][$objDataModel->alias] += $employeeInfoLdap[$objDataModel->alias];
        }

        return $result;
    }

    /**
     * Process group action
     *
     * @param string $groupAction Name of group action for processing
     * @param array $conditions Conditions of group action for processing
     * @return null|bool|array Return Null, on invalid inputs parameters.
     *  If failure, return False. On success, return array of
     *   created Job containing id, data, ...
     */
    public function processGroupAction($groupAction = null, $conditions = null)
    {
        if (($groupAction === false) || empty($conditions)) {
            return null;
        }

        $result = false;
        $approve = null;
        $internal = false;
        switch ($groupAction) {
            case GROUP_ACTION_DEFERRED_SAVE_DELETE:
                $result = $this->deleteAll($conditions, false, true);
                break;
            case GROUP_ACTION_DEFERRED_SAVE_INTERNAL_APPROVE:
                $internal = true;
                // no break
            case GROUP_ACTION_DEFERRED_SAVE_APPROVE:
                $approve = true;
                // no break
            case GROUP_ACTION_DEFERRED_SAVE_REJECT:
                if (is_null($approve)) {
                    $approve = false;
                }

                $userInfoLib = new UserInfo();
                $userId = $userInfoLib->getUserField('id');
                $taskParam = compact('conditions', 'approve', 'userId', 'internal');
                $modelExtendQueuedTask = ClassRegistry::init('CakeTheme.ExtendQueuedTask');
                $result = $modelExtendQueuedTask->createJob('DeferredSave', $taskParam, null, 'deferred');
                break;
            default:
                return null;
        }

        return $result;
    }

    /**
     * Return informations of deferred saves for group processing.
     *
     * @param array $conditions Conditions of group action for processing
     * @param int|string $limit Limit for result
     * @return array|bool Return information of deferred saves, or False on failure.
     */
    protected function _getGroupProcessData($conditions = null, $limit = DEFERRED_SAVE_GROUP_PROCESS_LIMIT)
    {
        if (empty($conditions)) {
            return false;
        }

        $fields = [
            $this->alias . '.id',
            $this->alias . '.internal',
            $this->alias . '.data',
            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL,
            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
        ];
        $contain = ['Employee'];

        return $this->find('all', compact('conditions', 'fields', 'contain', 'limit'));
    }

    /**
     * Processing group of deferred saves
     *
     * @param array $conditions Conditions of group action for processing
     * @param bool $approve If true, approve data of deferred save, reject otherwise.
     * @param int $userId User ID, initiating the process
     * @param int $idTask The ID of the QueuedTask
     * @return bool Success
     */
    public function processDeferredSave($conditions = null, $approve = false, $userId = null, $idTask = null)
    {
        $dataToProcess = $this->_getGroupProcessData($conditions);
        if (empty($dataToProcess)) {
            return false;
        }

        set_time_limit(DEFERRED_SAVE_GROUP_PROCESS_TIME_LIMIT);
        $step = 0;
        $maxStep = count($dataToProcess);
        $errorMessages = [];
        $result = true;
        $successCount = 0;
        $mailInfo = [];
        $dataToDelete = [];
        $objDataModel = $this->getObjectDataModel();
        $modelExtendQueuedTask = ClassRegistry::init('CakeTheme.ExtendQueuedTask');
        $modelExtendQueuedTask->updateProgress($idTask, 0);
        if (!empty($userId) && (PHP_SAPI === 'cli')) {
            CakeSession::write('Auth.User.id', $userId);
        }
        foreach ($dataToProcess as $dataToProcessItem) {
            $modelExtendQueuedTask->updateTaskProgress($idTask, $step, $maxStep);
            $id = Hash::get($dataToProcessItem, 'Deferred.id');
            $internal = Hash::get($dataToProcessItem, 'Deferred.internal');
            $data = Hash::get($dataToProcessItem, 'Deferred.data');
            $mail = Hash::get($dataToProcessItem, 'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL);
            $name = Hash::get($dataToProcessItem, 'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME);
            if (empty($data)) {
                $result = false;
                $errorMessages[] = __('Invalid data of deferred save for employee: "%s".', $name);
                continue;
            }

            if ($approve) {
                $objDataModel->clear();
                if ($objDataModel->save($data['changed'], false)) {
                    $successCount++;
                    if (!empty($id)) {
                        $dataToDelete[$this->alias . '.id'] = $id;
                    }
                } else {
                    $result = false;
                    if (empty($idTask)) {
                        continue;
                    }

                    $employeeDn = Hash::get($data, 'changed.' . $objDataModel->alias . '.' . CAKE_LDAP_LDAP_DISTINGUISHED_NAME);
                    if (!empty($employeeDn)) {
                        $errorMessages[] = __('Error on approving deferred save with employee DN: "%s".', $employeeDn);
                    }
                }
            } elseif (!empty($id)) {
                $dataToDelete[$this->alias . '.id'] = $id;
            }

            if (empty($mail) || $internal) {
                continue;
            }

            $mailInfo[] = compact('data', 'mail', 'name');
            $maxStep++;
        }
        if (!empty($dataToDelete)) {
            if ($this->deleteAll($dataToDelete, false, true)) {
                $successCount++;
            } else {
                $result = false;
                if ($approve) {
                    $errorMessages[] = __('Error on deleting deferred saves after approving');
                } else {
                    $errorMessages[] = __('Error on rejecting deferred saves');
                }
            }
        }
        if ($successCount > 0) {
            $notbefore = date('Y-m-d H:i:s', strtotime('+' . DEFERRED_SAVE_SYNC_DELAY . ' second'));
            $modelExtendQueuedTask->createJob('SyncEmployee', null, $notbefore, 'sync');
        }

        if (empty($mailInfo)) {
            if (!empty($idTask) && !empty($errorMessages)) {
                $modelExtendQueuedTask->updateTaskErrorMessage($idTask, implode("\n", $errorMessages));
            }

            return $result;
        }

        $modelSendEmail = ClassRegistry::init('CakeNotify.SendEmail');
        $config = 'smtp';
        $domain = $modelSendEmail->getDomain();
        $from = ['noreply@' . $domain, __d('project', PROJECT_NAME)];
        $subject = __('Changing information of phone book');
        $template = 'deferredSaveReject';
        if ($approve) {
            $template = 'deferredSaveApprove';
        }
        $helpers = [
            'Deferred'
        ];
        $projectName = __dx('project', 'mail', PROJECT_NAME);
        $fieldsLabel = $this->Employee->getListFieldsLabel([], false);
        $fieldsConfig = $this->Employee->getFieldsConfig();
        foreach ($mailInfo as $mailInfoItem) {
            if (!empty($mailInfoItem['name'])) {
                $to = [$mailInfoItem['mail'], $mailInfoItem['name']];
            } else {
                $to = $mailInfoItem['mail'];
            }
            $deferredSave = Hash::get($mailInfoItem, 'data.changed.' . $objDataModel->alias);
            $deferredSave = $this->prepareDataForDisplay($deferredSave);
            $vars = compact('deferredSave', 'fieldsLabel', 'fieldsConfig', 'projectName');
            if (!$modelSendEmail->putQueueEmail(compact('config', 'from', 'to', 'subject', 'template', 'vars', 'helpers'))) {
                $errorMessages[] = __('Error on putting sending e-mail for "%s" in queue...', $to);
            }
            $modelExtendQueuedTask->updateTaskProgress($idTask, $step, $maxStep);
        }
        if (!empty($idTask) && !empty($errorMessages)) {
            $modelExtendQueuedTask->updateTaskErrorMessage($idTask, implode("\n", $errorMessages));
        }

        return $result;
    }

    /**
     * Return informations of last processed deferred saves.
     *
     * @param bool $returnList If True, return list of new deferred saves,
     *  otherwise return statistics information about new deferred saves.
     * @param int|string $limit Limit for result.
     * @return array Return information of last processed deferred saves.
     */
    protected function _getInfoNewDeferredSave($returnList = false, $limit = null)
    {
        $modelLastProcessed = ClassRegistry::init('LastProcessed');
        $lastProcessedId = $modelLastProcessed->getLastProcessed(LAST_PROCESSED_DEFERRED_SAVE);

        $conditions = [$this->alias . '.internal' => false];
        $contain = [];
        if ($returnList) {
            $fields = [
                $this->alias . '.id',
                $this->alias . '.employee_id',
                $this->alias . '.data',
                $this->alias . '.created',
                $this->alias . '.modified',
            ];
            $contain[] = 'Employee';
            if (empty($limit)) {
                $limit = DEFERRED_SAVE_CHECK_NEW_EMAIL_LIST_LIMIT;
            }
            $findType = 'all';
        } else {
            $fields = [
                'COUNT(*) AS Count',
                'MAX(' . $this->alias . '.id) AS LastId',
            ];
            $limit = null;
            $findType = 'first';
        }
        if (!empty($lastProcessedId)) {
            $conditions[$this->alias . '.id >'] = $lastProcessedId;
        }

        return $this->find($findType, compact('conditions', 'fields', 'limit', 'contain'));
    }

    /**
     * Return list of e-mail for users, that are members of a
     *  security group in Active Directory for administrators phonebook.
     *
     * @param string $fieldConfig Config field contain DN of Active Directory
     *  security group.
     * @return array List of email addresses for the users who are
     *  members of a security group in Active Directory.
     */
    public function getListEmployeesEmailForAdGroup($fieldConfig = null)
    {
        $result = [];
        if (empty($fieldConfig)) {
            return $result;
        }

        $modelSetting = ClassRegistry::init('Setting');
        $modelLdap = ClassRegistry::init('CakeSettingsApp.Ldap');
        $adGroupDn = $modelSetting->getConfig($fieldConfig);

        return $modelLdap->getListGroupEmail($adGroupDn);
    }

    /**
     * Checking for available new unprocessed deferred saves.
     *
     * @return bool Success.
     */
    public function checkNewDeferredSave()
    {
        $infoNewDeferredSave = $this->_getInfoNewDeferredSave(false);
        if (!isset($infoNewDeferredSave[0])) {
            return false;
        }

        $countNewDeferredSave = $infoNewDeferredSave[0]['Count'];
        $lastId = $infoNewDeferredSave[0]['LastId'];
        if ($countNewDeferredSave == 0) {
            return true;
        }

        $deferredSavesInfo = $this->_getInfoNewDeferredSave(true);
        $modelLastProcessed = ClassRegistry::init('LastProcessed');
        if (!$modelLastProcessed->setLastProcessed(LAST_PROCESSED_DEFERRED_SAVE, $lastId)) {
            return false;
        }

        $prefix = null;
        $fieldConfig = null;
        $modelSetting = ClassRegistry::init('CakeSettingsApp.Setting');
        $userRole = $modelSetting->getConfig('ManagerGroupDeferredSave');
        $listAuthGroups = $modelSetting->getAuthGroupsList();
        $listgetAuthPrefixesList = $modelSetting->getAuthPrefixesList();
        if (!empty($userRole) && isset($listgetAuthPrefixesList[$userRole])) {
            $prefix = $listgetAuthPrefixesList[$userRole];
        }

        if (!empty($userRole) && isset($listAuthGroups[$userRole])) {
            $fieldConfig = $listAuthGroups[$userRole];
        }

        $listEmails = $this->getListEmployeesEmailForAdGroup($fieldConfig);
        if (empty($listEmails)) {
            return true;
        }

        $objDataModel = $this->getObjectDataModel();
        $deferredSaves = [];
        if (!empty($deferredSavesInfo)) {
            foreach ($deferredSavesInfo as $deferredSavesInfoItem) {
                if (!is_array($deferredSavesInfoItem['Deferred']['data'])) {
                    continue;
                }

                $deferredSavesEmployee = Hash::get($deferredSavesInfoItem, 'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME);
                if (empty($deferredSavesEmployee)) {
                    $deferredSavesEmployee = Hash::get($deferredSavesInfoItem, 'Deferred.data.changed.' . $objDataModel->alias . '.' . CAKE_LDAP_LDAP_DISTINGUISHED_NAME);
                }
                if (empty($deferredSavesEmployee)) {
                    continue;
                }

                $deferredSavesData = Hash::get($deferredSavesInfoItem, 'Deferred.data.changed.' . $objDataModel->alias);
                if (empty($deferredSavesData)) {
                    continue;
                }

                $deferredSavesData = $this->prepareDataForDisplay($deferredSavesData);
                $deferredSaves[$deferredSavesEmployee] = $deferredSavesData;
            }
        }
        $modelSendEmail = ClassRegistry::init('CakeNotify.SendEmail');
        $projectName = __dx('project', 'mail', PROJECT_NAME);
        $fieldsLabel = $this->Employee->getListFieldsLabel([], false);
        $fieldsConfig = $this->Employee->getFieldsConfig();
        $config = 'smtp';
        $domain = $modelSendEmail->getDomain();
        $from = ['noreply@' . $domain, __d('project', PROJECT_NAME)];
        $subject = __('Found new deferred saves');
        $template = 'deferredSaveCheck';
        $helpers = ['Number', 'CakeTheme.ViewExtension'];
        $vars = compact(
            'deferredSaves',
            'countNewDeferredSave',
            'fieldsLabel',
            'fieldsConfig',
            'projectName',
            'prefix'
        );
        $result = true;
        foreach ($listEmails as $email => $name) {
            $to = [$email, $name];
            if (!$modelSendEmail->putQueueEmail(compact('config', 'from', 'to', 'subject', 'template', 'vars', 'helpers'))) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Return controller name.
     *
     * @return string Return controller name for breadcrumb.
     */
    public function getControllerName() {
        $controllerName = 'deferred';

        return $controllerName;
    }

    /**
     * Return name of group data.
     *
     * @return string Return name of group data
     */
    public function getGroupName() {
        $groupName = __('Deferred saves');

        return $groupName;
    }
}
