<?php
/**
 * This file is the model file of the application. Used for
 *  management extended information about departments.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.Model
 */

App::uses('AppModel', 'Model');
App::uses('Hash', 'Utility');

/**
 * The model is used for management extended information about departments.
 *
 * @package app.Model
 */
class DepartmentExtension extends AppModel
{

    /**
     * Custom display field name.
     *
     * @var string
     * @link http://book.cakephp.org/2.0/en/models/model-attributes.html#displayfield
     */
    public $displayField = 'name';

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
        'department_id' => [
            'naturalNumber' => [
                'rule' => ['naturalNumber'],
                'message' => 'Incorrect foreign key',
                'allowEmpty' => false,
                'required' => true,
                'last' => true,
                'on' => 'update'
            ],
        ],
        'name' => [
            'notBlank' => [
                'rule' => ['notBlank'],
                'message' => 'Incorrect extended name of department',
                'allowEmpty' => false,
                'required' => true,
                'last' => true
            ],
        ],
    ];

    /**
     * List of behaviors to load when the model object is initialized.
     *
     * @var array
     * @link http://book.cakephp.org/2.0/en/models/behaviors.html#using-behaviors
     */
    public $actsAs = [
        'Tree' => [
            'recursive' => -1
        ],
        'CakeTheme.Move'
    ];

    /**
     * Create extended information about department from data.
     *
     * @param array $data Data to creation extended information about department
     * @return bool Success
     */
    public function createDepartmentExtension($data = null)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        $id = Hash::get($data, 'id');
        $name = Hash::get($data, 'name');
        if (empty($id) || empty($name)) {
            return false;
        }

        $dataToSave = [$this->alias => [
            'name' => $name,
            'department_id' => $id
        ]];
        $this->create();

        return (bool)$this->save($dataToSave);
    }

    /**
     * Recover a corrupted tree (list)
     *
     * @param bool $verify Whether or not to verify the tree (list) before recover.
     * @return bool true on success, false on failure
     */
    public function recoverDepartmentList($verify = true)
    {
        set_time_limit(RECOVER_TREE_DEPARTMENT_EXTENSION_TIME_LIMIT);
        if ($verify && ($this->verify() === true)) {
            return true;
        }

        $dataSource = $this->getDataSource();
        $dataSource->begin();
        $result = $this->recover('parent');
        if ($result) {
            $dataSource->commit();
            $event = new CakeEvent('Model.afterUpdateTree', $this);
            $this->getEventManager()->dispatch($event);
        } else {
            $dataSource->rollback();
        }

        return $result;
    }

    /**
     * Reorder tree (list) of extended information about departments.
     *
     * @param bool $verify Whether or not to verify the tree (list) before reorder.
     * @return bool true on success, false on failure
     */
    public function reorderDepartmentList($verify = true)
    {
        set_time_limit(REORDER_TREE_DEPARTMENT_EXTENSION_TIME_LIMIT);
        if ($verify && ($this->verify() !== true)) {
            return false;
        }

        $dataSource = $this->getDataSource();
        $dataSource->begin();
        $result = $this->reorder(['verify' => false]);
        if ($result) {
            $dataSource->commit();
            $event = new CakeEvent('Model.afterUpdateTree', $this);
            $this->getEventManager()->dispatch($event);
        } else {
            $dataSource->rollback();
        }

        return $result;
    }
}
