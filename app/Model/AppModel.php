<?php
/**
 * This file is the application level Model
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.Model
 */

App::uses('Model', 'Model');

/**
 * Application level Model
 *
 * @package app.Model
 */
class AppModel extends Model
{

    /**
     * Name of the validation string domain to use when translating validation errors.
     *
     * @var array
     */
    public $validationDomain = 'validation_errors';

    /**
     * List of behaviors to load when the model object is initialized. Settings can be
     * passed to behaviors by using the behavior name as index. Eg:
     *
     * public $actsAs = array('Translate', 'MyBehavior' => array('setting1' => 'value1'))
     *
     * @var array
     * @link http://book.cakephp.org/2.0/en/models/behaviors.html#using-behaviors
     */
    public $actsAs = ['Containable'];

    /**
     * Called before each find operation. Return false if you want to halt the find
     * call, otherwise return the (modified) query data.
     *
     * Actions:
     *  - Set global limit if needed.
     *
     * @param array $query Data used to execute this query, i.e. conditions, order, etc.
     * @return mixed true if the operation should continue, false if it should abort; or, modified
     *  $query to continue with new $query
     * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforefind
     */
    public function beforeFind($query)
    {
        if (isset($query['limit'])) {
            return parent::beforeFind($query);
        }

        $query['limit'] = GLOBAL_QUERY_LIMIT;
        $parentQuery = parent::beforeFind($query);
        if (!$parentQuery) {
            return false;
        } elseif ($parentQuery !== true) {
            $query = $parentQuery;
        }

        return $query;
    }

    /**
     * Saves model data (based on white-list, if supplied) to the database. By
     * default, validation occurs before save. Passthrough method to _doSave() with
     * transaction handling.
     *
     * Actions:
     *  - Clear modified field value before each save
     * @param array $data Data to save.
     * @param bool|array $validate Either a boolean, or an array.
     *   If a boolean, indicates whether or not to validate before saving.
     *   If an array, can have following keys:
     *
     *   - atomic: If true (default), will attempt to save the record in a single transaction.
     *   - validate: Set to true/false to enable or disable validation.
     *   - fieldList: An array of fields you want to allow for saving.
     *   - callbacks: Set to false to disable callbacks. Using 'before' or 'after'
     *     will enable only those callbacks.
     *   - `counterCache`: Boolean to control updating of counter caches (if any).
     *
     * @param array $fieldList List of fields to allow to be saved
     * @return mixed On success Model::$data if its not empty or true, false on failure
     * @throws Exception
     * @throws PDOException
     * @triggers Model.beforeSave $this, array($options)
     * @triggers Model.afterSave $this, array($created, $options)
     * @link http://book.cakephp.org/2.0/en/models/saving-your-data.html
     */
    public function save($data = null, $validate = true, $fieldList = [])
    {
        $this->set($data);
        if (isset($this->data[$this->alias]['modified'])) {
            unset($this->data[$this->alias]['modified']);
        }

        return parent::save($this->data, $validate, $fieldList);
    }
}
