<?php
App::uses('CakeSession', 'Model/Datasource');
App::uses('ModelBehavior', 'Model');
App::uses('Utility', 'Utility');
App::uses('ShimModel', 'Shim.Model');

if (!defined('CLASS_USER')) {
	define('CLASS_USER', 'User');
}

/**
 * Logs saves and deletes of any model
 *
 * Requires the following to work as intended :
 *
 * - "Log" model ( empty but for a order variable [created DESC]
 * - "logs" table with these fields required :
 *   - id	[int]
 *   - title [string] : automagically filled with the display field of the model that was modified.
 *   - created [date/datetime] : filled by cake in normal way
 *
 * - actsAs = array("Tools.Logable"); on models that should be logged
 *
 * Optional extra table fields for the "logs" table :
 *
 * - "description" 	[string] : Fill with a descriptive text of what, who and to which model/row :
 * 								"Contact "John Smith"(34) added by User "Administrator"(1).
 *
 * or if u want more detail, add any combination of the following :
 *
 * - "" 	[string] : automagically filled with the class name of the model that generated the activity.
 * - "foreign_id" 	[int]	 : automagically filled with the primary key of the model that was modified.
 * - "action" 	[string] : automagically filled with what action is made (add/edit/delete)
 * - "user_id" 	[int] : populated with the supplied user info. (May be renamed. See bellow.)
 * - "change" 	[string] : depending on setting either :
 * 							[name (alek) => (Alek), age (28) => (29)] or [name, age]
 *
 * - "version_id"	[int]	 : cooperates with RevisionBehavior to link the the shadow table (thus linking to old data)
 *
 * Remember that Logable behavior needs to be added after RevisionBehavior. In fact, just put it last to be safe.
 *
 * Optionally register what user was responsable for the activity :
 *
 * - Supply configuration only if defaults are wrong. Example given with defaults :
 *
 * 		public $actsAs = array('Tools.Logable' => array('userModel' => 'User', 'userKey' => 'user_id'));
 *
 * - In AppController (or single controller if only needed once) add these lines to beforeFilter :
 *
 * 	if (count($this->uses) && $this->{$this->modelClass}->Behaviors->loaded('Logable')) {
 *			$this->{$this->modelClass}->setUserData($this->activeUser);
 *		}
 *
 * Where "$activeUser" should be an array in the standard format for the User model used :
 *
 * $activeUser = array( $UserModel->alias => array( $UserModel->primaryKey => 123, $UserModel->displayField => 'Alexander'));
 * // any other key is just ignored by this behaviour.
 *
 * @author Alexander Morland (alexander#maritimecolours.no)
 * @co-author Eskil Mjelva Saatvedt
 * @co-author Ronny Vindenes
 * @co-author Carl Erik Fyllingen
 * @contributor Miha
 * @category Behavior
 * @version 2.2
 * @modified 3.june 2009 by Miha
 * @modified 2011-11-17 ms (mark scherer) cake2.0 ready
 *
 */
class LogableBehavior extends ModelBehavior {

	public $user = null;

	public $old = null;

	public $UserModel = null;

	protected $_defaultConfig = [
		'enabled' => true,
		'on' => 'save', // On validate/save
		'userModel' => CLASS_USER,
		'logModel' => 'Tools.Log',
		'userKey' => 'user_id',
		'change' => 'list',
		'descriptionIds' => true,
		'skip' => [],
		'ignore' => [],
		'classField' => 'model',
		'foreignKey' => 'foreign_id',
		'autoRelation' => false, // Attach relation to the model (hasMany Log)
	];

	/**
	 * Config options are :
	 * - userModel : 'User'. Class name of the user model you want to use (User by default), if you want to save User in log
	 * - userKey : 'user_id'. The field for saving the user to (user_id by default).
	 * - change : 'list' > [name, age]. Set to 'full' for [name (alek) => (Alek), age (28) => (29)]
	 * - descriptionIds : TRUE. Set to false to not include model id and user id in the title field
	 * - skip: array(). String array of actions to not log.
	 * - ignore: array(). Fields to ignore. The primary key will always be ignored.
	 *
	 * @param Model $Model
	 * @param array $config
	 * @return void
	 */
	public function setup(Model $Model, $config = []) {
		$config += (array)Configure::read('Logable');
		$this->settings[$Model->alias] = $config + $this->_defaultConfig;
		$this->settings[$Model->alias]['ignore'][] = $Model->primaryKey;

		$this->Log = ClassRegistry::init($this->settings[$Model->alias]['logModel']);

		if ($this->settings[$Model->alias]['userModel'] !== $Model->alias) {
			$this->UserModel = ClassRegistry::init($this->settings[$Model->alias]['userModel']);
		} else {
			$this->UserModel = $Model;
		}
	}

	/**
	 * LogableBehavior::enableLog()
	 *
	 * @param Model $Model
	 * @param bool $enable
	 * @return bool Current enabled status
	 */
	public function enableLog(Model $Model, $enable = null) {
		if ($enable !== null) {
			$this->settings[$Model->alias]['enabled'] = $enable;
		}
		return $this->settings[$Model->alias]['enabled'];
	}

	/**
	 * Useful for getting logs for a model, takes params to narrow find.
	 * This method can actually also be used to find logs for all models or
	 * even another model. Using no params will return all activities for
	 * the models it is called from.
	 *
	 * Possible params :
	 * 'model' 		: mixed (null) String with className, null to get current or false to get everything
	 * 'action' 	: string (null) String with action (add/edit/delete), null gets all
	 * 'order' 		: string ('created DESC') String with custom order
	 * 'conditions : array (array()) Add custom conditions
	 * 'foreign_id'	: int	 (null) Add a int
	 *
	 * (remember to use your own user key if you're not using 'user_id')
	 * 'user_id' 	: int 	 (null) Defaults to all users, supply id if you want for only one User
	 *
	 * @param Model $Model
	 * @param array $params
	 * @return array
	 */
	public function findLog(Model $Model, $params = []) {
		$defaults = [
			 $this->settings[$Model->alias]['classField'] => null,
			 'action' => null,
			 'order' => $this->Log->alias . '.id DESC',
			 $this->settings[$Model->alias]['userKey'] => null,
			 'conditions' => [],
			 $this->settings[$Model->alias]['foreignKey'] => null,
			 'fields' => [],
			 'limit' => 50,
		];

		$params += $defaults;
		$options = ['order' => $params['order'], 'conditions' => $params['conditions'], 'fields' => $params['fields'], 'limit' => $params['limit']];
		if ($params[$this->settings[$Model->alias]['classField']] === null) {
			$params[$this->settings[$Model->alias]['classField']] = $Model->alias;
		}
		if ($params[$this->settings[$Model->alias]['classField']]) {
			if ($this->Log->hasField($this->settings[$Model->alias]['classField'])) {
				$options['conditions'][$this->settings[$Model->alias]['classField']] = $params[$this->settings[$Model->alias]['classField']];
			} elseif ($this->Log->hasField('description')) {
				$options['conditions']['description LIKE '] = $params[$this->settings[$Model->alias]['classField']] . '%';
			} else {
				return [];
			}
		}
		if ($params['action'] && $this->Log->hasField('action')) {
			$options['conditions']['action'] = $params['action'];
		}
		if ($params[$this->settings[$Model->alias]['userKey']] && $this->UserModel && is_numeric($params[$this->settings[$Model->alias]['userKey']])) {
			$options['conditions'][$this->settings[$Model->alias]['userKey']] = $params[$this->settings[$Model->alias]['userKey']];
		}
		if ($params[$this->settings[$Model->alias]['foreignKey']] && is_numeric($params[$this->settings[$Model->alias]['foreignKey']])) {
			$options['conditions'][$this->settings[$Model->alias]['foreignKey']] = $params[$this->settings[$Model->alias]['foreignKey']];
		}
		return $this->Log->find('all', $options);
	}

	/**
	 * Get list of actions for one user.
	 * Params for getting (one line) activity descriptions
	 * and/or for just one model
	 *
	 * @example $this->Model->findUserActions(301, array('model' => 'BookTest'));
	 * @example $this->Model->findUserActions(301, array('events' => true));
	 * @example $this->Model->findUserActions(301, array('fields' => array('id','model'),'model' => 'BookTest');
	 * @param Model $Model
	 * @param int $userId
	 * @param array $params
	 * @return array
	 */
	public function findUserActions(Model $Model, $userId, $params = []) {
		if (!$this->UserModel) {
			return [];
		}
		// if logged in user is asking for her own log, use the data we allready have
		if (isset($this->user) && isset($this->user[$this->UserModel->alias][$this->UserModel->primaryKey]) && $userId == $this->user[$this->
			UserModel->alias][$this->UserModel->primaryKey] && isset($this->user[$this->UserModel->alias][$this->UserModel->displayField])) {
			$username = $this->user[$this->UserModel->alias][$this->UserModel->displayField];
		} else {
			$user = $this->UserModel->find('first', ['recursive' => -1, 'conditions' => [$this->UserModel->primaryKey => $userId]]);
			$username = $user[$this->UserModel->alias][$this->UserModel->displayField];
		}
		$fields = [];
		if (isset($params['fields'])) {
			if (is_array($params['fields'])) {
				$fields = $params['fields'];
			} else {
				$fields = [$params['fields']];
			}
		}
		$conditions = [$this->settings[$Model->alias]['userKey'] => $userId];
		if (isset($params[$this->settings[$Model->alias]['classField']])) {
			$conditions[$this->settings[$Model->alias]['classField']] = $params[$this->settings[$Model->alias]['classField']];
		}
		$order = [$this->Log->alias . '.id' => 'DESC'];
		if (isset($params['order'])) {
			$order = $params['order'];
		}

		$data = $this->Log->find('all', [
			'conditions' => $conditions,
			'recursive' => -1,
			'fields' => $fields,
			'order' => $order
		]);
		if (!isset($params['events']) || (isset($params['events']) && $params['events'] == false)) {
			return $data;
		}
		$result = [];
		foreach ($data as $key => $row) {
			$one = $row[$this->Log->alias];
			$result[$key][$this->Log->alias]['id'] = $one['id'];
			$result[$key][$this->Log->alias]['event'] = $username;
			// have all the detail models and change as list :
			if (isset($one[$this->settings[$Model->alias]['classField']]) && isset($one['action']) && isset($one['change']) && isset($one[$this->
				settings[$Model->alias]['foreignKey']])) {
				if ($one['action'] === 'edit') {
					$result[$key][$this->Log->alias]['event'] .= ' edited ' . $one['change'] . ' of ' . strtolower($one[$this->settings[$Model->alias]['classField']]) .
						'(id ' . $one[$this->settings[$Model->alias]['foreignKey']] . ')';
				} elseif ($one['action'] === 'add') {
					$result[$key][$this->Log->alias]['event'] .= ' added a ' . strtolower($one[$this->settings[$Model->alias]['classField']]) . '(id ' . $one[$this->
						settings[$Model->alias]['foreignKey']] . ')';
				} elseif ($one['action'] === 'delete') {
					$result[$key][$this->Log->alias]['event'] .= ' deleted the ' . strtolower($one[$this->settings[$Model->alias]['classField']]) . '(id ' . $one[$this->
						settings[$Model->alias]['foreignKey']] . ')';
				}
			} elseif (isset($one[$this->settings[$Model->alias]['classField']]) && isset($one['action']) && isset($one[$this->settings[$Model->alias]['foreignKey']])) { // have model,foreign_id and action
				if ($one['action'] === 'edit') {
					$result[$key][$this->Log->alias]['event'] .= ' edited ' . strtolower($one[$this->settings[$Model->alias]['classField']]) . '(id ' . $one[$this->
						settings[$Model->alias]['foreignKey']] . ')';
				} elseif ($one['action'] === 'add') {
					$result[$key][$this->Log->alias]['event'] .= ' added a ' . strtolower($one[$this->settings[$Model->alias]['classField']]) . '(id ' . $one[$this->
						settings[$Model->alias]['foreignKey']] . ')';
				} elseif ($one['action'] === 'delete') {
					$result[$key][$this->Log->alias]['event'] .= ' deleted the ' . strtolower($one[$this->settings[$Model->alias]['classField']]) . '(id ' . $one[$this->
						settings[$Model->alias]['foreignKey']] . ')';
				}
			} else { // only description field exist
				$result[$key][$this->Log->alias]['event'] = $one['description'];
			}
		}
		return $result;
	}

	/**
	 * Use this to supply a model with the data of the logged in User.
	 * Intended to be called in AppController::beforeFilter like this :
	 *
	 * 	if ($this->{$this->modelClass}->Behaviors->loaded('Logable')) {
	 *			$this->{$this->modelClass}->setUserData($activeUser);/
	 *		}
	 *
	 * The $userData array is expected to look like the result of a
	 * User::find(array('id'=>123));
	 *
	 * @param Model $Model
	 * @param array $userData
	 * @return void
	 */
	public function setUserData(Model $Model, $userData = null) {
		if ($userData === null && isset($Model->Session)) {
			$userData = (array)$Model->Session->read('Auth');
		} elseif ($userData === null && class_exists('CakeSession')) {
			$userData = (array)CakeSession::read('Auth');
		}

		if ($userData !== null) {
			$this->user = $userData;
		}
	}

	/**
	 * Used for logging custom actions that arent crud, like login or download.
	 *
	 * @example $this->Boat->customLog('ship', 66, array('title' => 'Titanic heads out'));
	 * @param Model $Model
	 * @param string $action name of action that is taking place (dont use the crud ones)
	 * @param int $id id of the logged item (ie foreign_id in logs table)
	 * @param array $logData optional other values for your logs table
	 * @return mixed Success
	 */
	public function customLog(Model $Model, $action, $id = null, $logData = []) {
		if ($id === null) {
			$id = $Model->id;
		}
		if ($this->Log->hasField($this->settings[$Model->alias]['foreignKey']) && is_numeric($id)) {
			$logData[$this->settings[$Model->alias]['foreignKey']] = $id;
		}
		$title = null;
		if (isset($logData['title'])) {
			$title = $logData['title'];
			unset($logData['title']);
		}
		$logData['action'] = $action;
		return $this->_saveLog($Model, $logData, $title);
	}

	/**
	 * LogableBehavior::clearUserData()
	 *
	 * @param Model $Model
	 * @return void
	 */
	public function clearUserData(Model $Model) {
		$this->user = null;
	}

	/**
	 * LogableBehavior::setUserIp()
	 *
	 * @param Model $Model
	 * @param mixed $userIP
	 * @return void
	 */
	public function setUserIp(Model $Model, $userIP = null) {
		if ($userIP === null) {
			$userIP = Utility::getClientIp();
		}
		$this->userIP = $userIP;
	}

	/**
	 * LogableBehavior::beforeDelete()
	 *
	 * @param Model $Model
	 * @param bool $cascade
	 * @return bool Success
	 */
	public function beforeDelete(Model $Model, $cascade = true) {
		$this->setUserData($Model);
		if (!$this->settings[$Model->alias]['enabled']) {
			return true;
		}
		if (isset($this->settings[$Model->alias]['skip']['delete']) && $this->settings[$Model->alias]['skip']['delete']) {
			return true;
		}
		$Model->read();
		return true;
	}

	/**
	 * LogableBehavior::afterDelete()
	 *
	 * @param Model $Model
	 * @return bool
	 */
	public function afterDelete(Model $Model) {
		if (!$this->settings[$Model->alias]['enabled']) {
			return true;
		}
		if (isset($this->settings[$Model->alias]['skip']['delete']) && $this->settings[$Model->alias]['skip']['delete']) {
			return true;
		}

		$logData = [];
		if ($this->Log->hasField('description')) {
			$logData['description'] = $Model->alias;
			if (isset($Model->data[$Model->alias][$Model->displayField]) && $Model->displayField != $Model->primaryKey) {
				$logData['description'] .= ' "' . $Model->data[$Model->alias][$Model->displayField] . '"';
			}
			if ($this->settings[$Model->alias]['descriptionIds']) {
				$logData['description'] .= ' (' . $Model->id . ') ';
			}
			$logData['description'] .= __d('tools', 'deleted');
		}
		$logData['action'] = 'delete';
		if (!$this->_saveLog($Model, $logData)) {
			throw new RuntimeException('Logging failed');
		}
	}

	/**
	 * LogableBehavior::beforeValidate()
	 *
	 * @param Model $Model
	 * @param array $options
	 * @return bool
	 */
	public function beforeValidate(Model $Model, $options = []) {
		if (!$this->settings[$Model->alias]['enabled'] || $this->settings[$Model->alias]['on'] !== 'validate') {
			return true;
		}
		$this->_prepareLog($Model);
		return true;
	}

	/**
	 * LogableBehavior::beforeSave()
	 *
	 * @param Model $Model
	 * @param array $options
	 * @return bool
	 */
	public function beforeSave(Model $Model, $options = []) {
		if (!$this->settings[$Model->alias]['enabled'] || $this->settings[$Model->alias]['on'] !== 'save') {
			return true;
		}
		$this->_prepareLog($Model);
		return true;
	}

	/**
	 * LogableBehavior::afterSave()
	 *
	 * @param Model $Model
	 * @param bool $created
	 * @param array $options
	 * @return bool
	 */
	public function afterSave(Model $Model, $created, $options = []) {
		if (!$this->settings[$Model->alias]['enabled']) {
			return true;
		}
		if (!empty($this->settings[$Model->alias]['skip']['add']) && $created) {
			return true;
		} elseif (!empty($this->settings[$Model->alias]['skip']['edit']) && !$created) {
			return true;
		}
		$keys = array_keys($Model->data[$Model->alias]);
		$diff = array_diff($keys, $this->settings[$Model->alias]['ignore']);
		if (count($diff) === 0 && empty($Model->logableAction)) {
			return false;
		}

		$logData = [];

		if ($Model->id) {
			$id = $Model->id;
		} elseif ($Model->insertId) {
			$id = $Model->insertId;
		}
		if (!empty($id) && $this->Log->hasField($this->settings[$Model->alias]['foreignKey'])) {
			$logData[$this->settings[$Model->alias]['foreignKey']] = $id;
		}
		if ($this->Log->hasField('description')) {
			$logData['description'] = $Model->alias . ' ';
			if (isset($Model->data[$Model->alias][$Model->displayField]) && $Model->displayField != $Model->primaryKey) {
				$logData['description'] .= '"' . $Model->data[$Model->alias][$Model->displayField] . '" ';
			}

			if (!empty($id) && $this->settings[$Model->alias]['descriptionIds']) {
				$logData['description'] .= '(' . $id . ') ';
			}

			if ($created) {
				$logData['description'] .= __d('tools', 'added');
			} else {
				$logData['description'] .= __d('tools', 'updated');
			}
		}
		if ($this->Log->hasField('action')) {
			if ($created) {
				$logData['action'] = 'add';
			} else {
				$logData['action'] = 'edit';
			}
		}
		if ($this->Log->hasField('change')) {
			$logData['change'] = '';
			$dbFields = array_keys($Model->schema());
			$changedFields = [];
			foreach ($Model->data[$Model->alias] as $key => $value) {
				if (isset($Model->data[$Model->alias][$Model->primaryKey]) && !empty($this->old) && isset($this->old[$Model->alias][$key])) {
					$old = $this->old[$Model->alias][$key];
				} else {
					$old = '';
				}
				if ($key !== 'modified' && !in_array($key, $this->settings[$Model->alias]['ignore']) && $value != $old && in_array($key, $dbFields)) {
					if ($this->settings[$Model->alias]['change'] === 'full') {
						$changedFields[] = $key . ' (' . $old . ') => (' . $value . ')';
					} elseif ($this->settings[$Model->alias]['change'] === 'serialize') {
						$changedFields[$key] = ['old' => $old, 'value' => $value];
					} else {
						$changedFields[] = $key;
					}
				}
			}
			$changes = count($changedFields);
			if (!$changes) {
				return true;
			}
			if ($this->settings[$Model->alias]['change'] === 'serialize') {
				$logData['change'] = serialize($changedFields);
			} else {
				$logData['change'] = implode(', ', $changedFields);
			}
			$logData['changes'] = $changes;
		}

		if (empty($logData)) {
			return true;
		}
		return $this->_saveLog($Model, $logData);
	}

	/**
	 * LogableBehavior::settings()
	 *
	 * @param mixed $Model
	 * @return array
	 * @deprecated Directly use settings instead.
	 */
	public function settings(Model $Model) {
		return $this->settings[$Model->alias];
	}

	/**
	 * LogableBehavior::_prepareLog()
	 *
	 * @param Model $Model
	 * @return void
	 */
	protected function _prepareLog(Model $Model) {
		if ($this->user === null) {
			$this->setUserData($Model);
		}
		if ($Model->id && empty($this->old)) {
			$options = ['conditions' => [$Model->primaryKey => $Model->id], 'recursive' => -1];
			$this->old = $Model->find('first', $options);
		}
	}

	/**
	 * Does the actual saving of the Log model. Also adds the special field if possible.
	 *
	 * If model field in table, add the Model->alias
	 * If action field is NOT in table, remove it from dataset
	 * If the userKey field in table, add it to dataset
	 * If userData is supplied to model, add it to the title
	 *
	 * @param Model $Model
	 * @param array $logData
	 * @return mixed Success
	 */
	protected function _saveLog(Model $Model, $logData, $title = null) {
		if ($title !== null) {
			$logData['title'] = $title;
		} elseif ($Model->displayField == $Model->primaryKey) {
			$logData['title'] = $Model->alias . ' (' . $Model->id . ')';
		} elseif (!empty($Model->data[$Model->alias][$Model->displayField])) {
			$logData['title'] = $Model->data[$Model->alias][$Model->displayField];
		} elseif ($Model->id && $title = $this->_getField($Model)) {
			$logData['title'] = $title;
		} elseif (!empty($logData[$this->settings[$Model->alias]['foreignKey']])) {
			$options = [
				'conditions' => $logData[$this->settings[$Model->alias]['foreignKey']],
				'recursive' => -1
			];
			$record = $Model->find('first', $options);
			if ($record) {
				$logData['title'] = $record[$Model->alias][$Model->displayField];
			}
		}

		if ($this->Log->hasField($this->settings[$Model->alias]['classField'])) {
			$logData[$this->settings[$Model->alias]['classField']] = $Model->name;
		}

		if ($this->Log->hasField($this->settings[$Model->alias]['foreignKey']) && !isset($logData[$this->settings[$Model->alias]['foreignKey']])) {
			if ($Model->id) {
				$logData[$this->settings[$Model->alias]['foreignKey']] = $Model->id;
			} elseif ($Model->insertId) {
				$logData[$this->settings[$Model->alias]['foreignKey']] = $Model->insertId;
			}
		}

		if (!$this->Log->hasField('action')) {
			unset($logData['action']);
		} elseif (isset($Model->logableAction) && !empty($Model->logableAction)) {
			$logData['action'] = implode(',', $Model->logableAction);
		}

		if ($this->Log->hasField('version_id') && isset($Model->versionId)) {
			$logData['version_id'] = $Model->versionId;
		}

		if ($this->Log->hasField('ip') && $this->userIP) {
			$logData['ip'] = $this->userIP;
		}

		if ($this->Log->hasField($this->settings[$Model->alias]['userKey']) && $this->user && isset($this->user[$this->UserModel->alias])) {
			$logData[$this->settings[$Model->alias]['userKey']] = $this->user[$this->UserModel->alias][$this->UserModel->primaryKey];
		}

		if ($this->Log->hasField('description')) {
			if (empty($logData['description'])) {
				$logData['description'] = __d('tools', 'Custom action');
			}
			if ($this->user && $this->UserModel && isset($this->user[$this->UserModel->alias])) {
				$logData['description'] .= ' ' . __d('tools', 'by') . ' ' . $this->settings[$Model->alias]['userModel'] . ' "' . $this->user[$this->UserModel->alias][$this->UserModel->displayField] . '"';
				if ($this->settings[$Model->alias]['descriptionIds']) {
					$logData['description'] .= ' (' . $this->user[$this->UserModel->alias][$this->UserModel->primaryKey] . ')';
				}
			} else {
				// UserModel is active, but the data hasnt been set. Assume system action.
				$logData['description'] .= ' ' . __d('tools', 'by System');
			}
			$logData['description'] .= '.';
		}

		if (empty($logData['title'])) {
			// Fallback in case the title is null - add the action + ed
			$logData['title'] = $Model->alias . ' ' . $logData['action'] . 'ed';
		}

		$this->Log->create($logData);
		return $this->Log->save(null, ['validate' => false, 'callbacks' => false]);
	}

	/**
	 * @param \Model $Model
	 * @return string|false
	 */
	protected function _getField($Model) {
		if ($Model instanceof ShimModel) {
			return $Model->fieldByConditions($Model->displayField, ['id' => $Model->id]);
		}
		return $Model->field($Model->displayField);
	}

}
