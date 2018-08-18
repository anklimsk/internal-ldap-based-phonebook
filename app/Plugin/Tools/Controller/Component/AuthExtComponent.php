<?php

if (!defined('USER_ROLE_KEY')) {
	define('USER_ROLE_KEY', 'Role');
}
if (!defined('CLASS_USER')) {
	define('CLASS_USER', 'User');
}

App::uses('AuthComponent', 'Controller/Component');

/**
 * Extends AuthComponent with the following addons:
 * - allows multiple roles per user
 * - auto-raises login counter and sets last_login date
 * - preps the session data according to completeAuth() method (adds parent data etc)
 * - dynamic login scope validation
 *
 * @author Mark Scherer
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class AuthExtComponent extends AuthComponent {

	public $intermediateModel = 'RoleUser';

	public $roleModel = 'Role';

	public $fieldKey = 'role_id';

	public $loginAction = ['controller' => 'account', 'action' => 'login', 'admin' => false, 'plugin' => false];

	public $loginRedirect = ['controller' => 'overview', 'action' => 'home', 'admin' => false, 'plugin' => false];

	public $autoRedirect = false;

	public $loginError = null;

	protected $_defaultConfig = [
		'multi' => null, # null=auto - yes/no multiple roles (HABTM table between users and roles)
		'parentModelAlias' => USER_ROLE_KEY,
		'userModel' => CLASS_USER //TODO: allow plugin syntax
	];

	/**
	 * Merge in Configure::read('Auth') settings
	 *
	 * @param ComponentCollection $Collection
	 * @param array $config
	 */
	public function __construct(ComponentCollection $Collection, $config = []) {
		$defaults = (array)Configure::read('Auth') + $this->_defaultConfig;
		$config += $defaults;

		parent::__construct($Collection, $config);
	}

	public function initialize(Controller $Controller) {
		$this->Controller = $Controller;

		parent::initialize($Controller);
	}

	/**
	 * AuthExtComponent::login()
	 *
	 * @overwrite
	 * @param mixed $user
	 * @return bool Success
	 */
	public function login($user = null) {
		$Model = $this->getModel();
		$this->_setDefaults();

		if (empty($user)) {
			$user = $this->identify($this->Controller->request, $this->Controller->response);
		}
		$user = $this->completeAuth($user);

		if (empty($user)) {
			$this->loginError = __d('tools', 'invalidLoginCredentials');
			return false;
		}

		// custom checks
		if (isset($user['active'])) {
			if (empty($user['active'])) {
				$this->loginError = __d('tools', 'Account not active yet');
				return false;
			}
			if (!empty($user['suspended'])) {
				$this->loginError = __d('tools', 'Account temporarily locked');
				if (!empty($user['suspended_reason'])) {
					$this->loginError .= BR . BR . __d('tools', 'Reason') . ':' . BR . nl2br(h($user['suspended_reason']));
				}
				return false;
			}
		} else {
			if (isset($user['status']) && empty($user['status'])) {
				$this->loginError = __d('tools', 'Account not active yet');
				return false;
			}
			if (isset($user['status']) && defined('User::STATUS_PENDING') && $user['status'] == User::STATUS_PENDING) {
				$this->loginError = __d('tools', 'Account not active yet');
				return false;
			}
			if (isset($user['status']) && defined('User::STATUS_SUSPENDED') && $user['status'] == User::STATUS_SUSPENDED) {
				$this->loginError = __d('tools', 'Account temporarily locked');
				if (!empty($user['suspended_reason'])) {
					$this->loginError .= BR . BR . __d('tools', 'Reason') . ':' . BR . nl2br(h($user['suspended_reason']));
				}
				return false;
			}
			if (isset($user['status']) && defined('User::STATUS_DEL') && $user['status'] == User::STATUS_DEL) {
				$this->loginError = __d('tools', 'Account deleted');
				if (!empty($user['suspended_reason'])) {
					$this->loginError .= BR . BR . __d('tools', 'Reason') . ':' . BR . nl2br(h($user['suspended_reason']));
				}
				return false;
			}
			if (isset($user['status']) && defined('User::STATUS_ACTIVE') && $user['status'] != User::STATUS_ACTIVE) {
				$this->loginError = __d('tools', 'Unknown Error');
				return false;
			}
		}
		if (isset($user['email_confirmed']) && empty($user['email_confirmed'])) {
			$this->loginError = __d('tools', 'Email not active yet');
			return false;
		}

		if ($user) {
			// update login counter
			if (isset($user['logins'])) {
				$user['logins'] = $user['logins'] + 1;
				if (method_exists($Model, 'loginUpdate')) {
					$Model->loginUpdate($user);
				}
			}

			$this->Session->renew();
			$this->Session->write(static::$sessionKey, $user);
		}
		return $this->loggedIn();
	}

	/**
	 * Gather session data.
	 *
	 * @return array User
	 */
	public function completeAuth($user) {
		$Model = $this->getModel();
		$userArray = $user;
		if (!is_array($userArray)) {
			$user = $Model->get($user, ['noException' => true]);
			if (!$user) {
				return [];
			}
			$userArray = array_shift($user);
		}

		if (isset($Model->hasAndBelongsToMany[$this->roleModel]['className'])) {
			$with = $Model->hasAndBelongsToMany[$this->roleModel]['className'];
		} elseif (isset($Model->hasMany[$this->intermediateModel]['className'])) {
			$with = $Model->hasMany[$this->intermediateModel]['className'];
		} elseif (isset($Model->belongsTo[$this->roleModel]['className'])) {
			$with = $Model->belongsTo[$this->roleModel]['className'];
		}
		if (empty($with) && $this->settings['parentModelAlias'] !== false) {
			trigger_error('No relation from user to role found');
			return $user;
		}

		// roles
		if (!empty($with)) {
			list($plugin, $withModel) = pluginSplit($with);
			if (!isset($this->{$withModel})) {
				$this->{$withModel} = ClassRegistry::init($with);
			}
			// only for multi
			if ($this->settings['multi'] || !isset($userArray['role_id'])) {
				$parentModelAlias = $this->settings['parentModelAlias'];
				$userArray[$parentModelAlias] = []; # default: no roles!
				$roles = $this->{$withModel}->find('list', ['fields' => [$withModel . '.role_id'], 'conditions' => [$withModel . '.user_id' => $userArray['id']]]);
				if (!empty($roles)) {
					// add the suplemental roles id under the Auth session key
					$userArray[$parentModelAlias] = $roles;
				}
			}
		}

		if (method_exists($Model, 'completeAuth')) {
			$userArray = $Model->completeAuth($userArray);
		}
		return $userArray;
	}

	/**
	 * Main execution method. Handles redirecting of invalid users, and processing
	 * of login form data.
	 *
	 * @overwrite
	 * @param Controller $controller A reference to the instantiating controller object
	 * @return bool
	 */
	public function startup(Controller $controller) {
		if ($controller->name === 'CakeError') {
			return true;
		}

		$methods = array_flip(array_map('strtolower', $controller->methods));
		// fix: reverse camelCase first
		$action = strtolower(Inflector::underscore($controller->request->params['action']));

		$isMissingAction = (
			$controller->scaffold === false &&
			!isset($methods[$action])
		);

		if ($isMissingAction) {
			return true;
		}

		if (!$this->_setDefaults()) {
			return false;
		}

		if ($this->_isAllowed($controller)) {
			return true;
		}

		if (!$this->_getUser()) {
			return $this->_unauthenticated($controller);
		}

		if (empty($this->authorize) || $this->isAuthorized($this->user())) {
			return true;
		}

		return $this->_unauthorized($controller);
	}

	/**
	 * Returns the current User model
	 *
	 * @return object User
	 */
	public function getModel() {
		$model = $this->settings['userModel'];
		return ClassRegistry::init($model);
	}

}
