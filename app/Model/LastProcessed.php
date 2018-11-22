<?php
/**
 * This file is the model file of the application. Used for
 *  management information about last processed tasks.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Model
 */

App::uses('AppModel', 'Model');

/**
 * The model is used for management information about
 *  last processed tasks.
 *
 * This model allows:
 * - Retrieve information about last processed tasks;
 * - Update information about last processed tasks.
 *
 * @package app.Model
 */
class LastProcessed extends AppModel {

/**
 * Name of the model.
 *
 * @var string
 */
	public $name = 'LastProcessed';

/**
 * Custom database table name, or null/false if no table association is desired.
 *
 * @var string
 */
	public $useTable = 'last_processed';

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
		'last_proc_id' => [
			'naturalNumber' => [
				'rule' => ['naturalNumber'],
				'message' => 'Incorrect ID of last processed record',
				'allowEmpty' => true,
				'required' => true,
				'last' => true,
				'on' => 'update'
			],
		],
	];

/**
 * Set ID of last processed task.
 *
 * @param int $id Task ID for update last processed
 *  record ID.
 * @param int $lastProcessedId ID of last processed
 *  record.
 * @return bool Success
 */
	public function setLastProcessed($id = null, $lastProcessedId = null) {
		if (empty($id) || empty($lastProcessedId) ||
			!in_array($id, constsVals('LAST_PROCESSED_'))) {
			return false;
		}

		if (!$this->exists($id)) {
			$this->create();
		}
		$dataToSave = [
			$this->alias => [
				'id' => (int)$id,
				'last_proc_id' => (int)$lastProcessedId,
			]
		];

		return (bool)$this->save($dataToSave);
	}

/**
 * Return ID of record for last processed task.
 *
 * @param int $id Task ID for retrieve last processed
 *  record ID.
 * @return string|bool Return ID of record for last processed task,
 *  or False on failure.
 */
	public function getLastProcessed($id = null) {
		if (empty($id)) {
			return false;
		}

		$this->id = $id;

		return $this->field('last_proc_id');
	}

/**
 * Return date and time of last update for task.
 *
 * @param int $id Task ID for retrieve date and time
 *  of last processed record task.
 * @return string|bool Return date and time of last update, or
 *  False on failure.
 */
	public function getLastUpdate($id = null) {
		if (empty($id)) {
			return false;
		}

		$this->id = $id;

		return $this->field('modified');
	}
}
