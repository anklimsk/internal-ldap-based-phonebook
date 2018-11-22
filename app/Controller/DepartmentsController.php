<?php
/**
 * This file is the controller file of the application. Used for
 *  management information about the departments.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Controller
 */

App::uses('AppController', 'Controller');
App::uses('Hash', 'Utility');

/**
 * The controller is used for management information about the departments.
 *
 * This controller allows to perform the following operations:
 *  - to obtain, edit and changing position of departments.
 *  - reorder, checking and reocvery state tree (list) of department;
 *
 * @package app.Controller
 */
class DepartmentsController extends AppController {

/**
 * The name of this controller. Controller names are plural, named after the model they manipulate.
 *
 * @var string
 * @link http://book.cakephp.org/2.0/en/controllers.html#controller-attributes
 */
	public $name = 'Departments';

/**
 * Array containing the names of components this controller uses. Component names
 * should not contain the "Component" portion of the class name.
 *
 * @var array
 * @link http://book.cakephp.org/2.0/en/controllers/components.html
 */
	public $components = [
		'Paginator',
		'CakeTheme.Filter',
		'CakeTheme.Move' => ['model' => 'DepartmentExtension'],
	];

/**
 * An array containing the names of helpers this controller uses. The array elements should
 * not contain the "Helper" part of the class name.
 *
 * @var mixed
 * @link http://book.cakephp.org/2.0/en/controllers.html#components-helpers-and-uses
 */
	public $helper = [
		'Number'
	];

/**
 * Settings for component 'Paginator'
 *
 * @var array
 */
	public $paginate = [
		'page' => 1,
		'limit' => 20,
		'maxLimit' => 250,
		'fields' => [
			'Department.id',
			'Department.value',
			'Department.block',
			'DepartmentExtension.id',
			'DepartmentExtension.department_id',
			'DepartmentExtension.name',
			'DepartmentExtension.lft',
			'DepartmentExtension.rght',
		],
		'order' => [
			'DepartmentExtension.lft' => 'asc'
		],
		'contain' => [
			'DepartmentExtension',
		]
	];

/**
 * Called before the controller action. You can use this method to configure and customize components
 * or perform logic that needs to happen before each controller action.
 *
 * Actions:
 *  - Configure components.
 *
 * @return void
 * @link http://book.cakephp.org/2.0/en/controllers.html#request-life-cycle-callbacks
 */
	public function beforeFilter() {
		$this->Security->unlockedActions = [
			'hr_drop', 'admin_drop',
			'hr_move', 'admin_move',
		];

		parent::beforeFilter();
	}

/**
 * Base of action `index`. Used to view a complete list of departments.
 *
 * @return void
 */
	protected function _index() {
		$this->view = 'index';
		$this->Paginator->settings = $this->paginate;
		$conditions = $this->Filter->getFilterConditions();

		$departments = $this->Paginator->paginate('Department', $conditions);
		if (empty($departments)) {
			$this->Flash->information(__('Departments not found'));
		}
		$pageHeader = __('Index of departments');
		$headerMenuActions = [
			[
				'fas fa-plus',
				__('Add department'),
				['controller' => 'departments', 'action' => 'add'],
				[
					'title' => __('Add department'),
					'action-type' => 'modal',
				]
			],
			[
				'fas fa-sort-alpha-down',
				__('Order list of departments'),
				['controller' => 'departments', 'action' => 'order'],
				[
					'title' => __('Order list of departments by alphabet'),
					'action-type' => 'confirm-post',
					'data-confirm-msg' => __('Are you sure you wish to re-order list of departments?')
				]
			],
			'divider',
			[
				'fas fa-check',
				__('Check state list'),
				['controller' => 'departments', 'action' => 'check'],
				[
					'title' => __('Check state list of departments'),
					'data-toggle' => 'modal'
				]
			]
		];
		$breadCrumbs = $this->Department->getBreadcrumbInfo();
		$breadCrumbs[] = __('Index');
		$this->ViewExtension->setRedirectUrl(true, 'department');

		$this->set(compact('departments', 'pageHeader', 'headerMenuActions', 'breadCrumbs'));
	}

/**
 * Action `index`. Used to view a complete list of departments.
 *  User role - human resources.
 *
 * @return void
 */
	public function hr_index() {
		$this->_index();
	}

/**
 * Action `index`. Used to view a complete list of departments.
 *  User role - administrator.
 *
 * @return void
 */
	public function admin_index() {
		$this->_index();
	}

/**
 * Base of action `view`. Used to view information about department.
 *
 * @param int|string $id ID of record for viewing.
 * @throws NotFoundException if record for parameter $id was not found
 * @return void
 */
	protected function _view($id = null) {
		$this->view = 'view';
		if (!$this->Department->exists($id)) {
			return $this->ViewExtension->setExceptionMessage(new NotFoundException(__('Invalid ID for department')));
		}

		$department = $this->Department->get($id);
		$pageHeader = __('Information of department');
		$headerMenuActions = [
			[
				'fas fa-pencil-alt',
				__('Editing department'),
				['controller' => 'departments', 'action' => 'edit', $department['DepartmentExtension']['id']],
				['title' => __('Editing information of this department')]
			],
		];
		if ($department['Department']['block']) {
			$headerMenuActions[] = [
				'far fa-trash-alt',
				__('Delete department'),
				['controller' => 'departments', 'action' => 'delete', $department['DepartmentExtension']['id']],
				[
					'title' => __('Delete department'), 'action-type' => 'confirm-post',
					'data-confirm-msg' => __('Are you sure you wish to delete this department?'),
				]
			];
		}
		$breadCrumbs = $this->Department->getBreadcrumbInfo($id);
		$breadCrumbs[] = __('Viewing');
		$this->ViewExtension->setRedirectUrl(true, 'department');

		$this->set(compact('department', 'pageHeader', 'headerMenuActions', 'breadCrumbs'));
	}

/**
 * Action `view`. Used to view information about department.
 * User role - human resources.
 *
 * @param int|string $id ID of record for viewing
 * @return void
 */
	public function hr_view($id = null) {
		$this->_view($id);
	}

/**
 * Action `view`. Used to view information about department.
 * User role - administrator.
 *
 * @param int|string $id ID of record for viewing
 * @return void
 */
	public function admin_view($id = null) {
		$this->_view($id);
	}

/**
 * Base of action `add`. Used to add department.
 *
 * POST Data:
 *  - Department: array data of department
 *  - DepartmentExtension: array extended data of department
 *
 * @return void
 */
	protected function _add() {
		$this->view = 'add';
		if ($this->request->is('post')) {
			$this->Department->create();
			$this->request->data('Department.block', true);
			if ($this->Department->saveAll($this->request->data, ['deep' => true])) {
				$this->Flash->success(__('Department has been saved.'));

				return $this->ViewExtension->redirectByUrl(null, 'department');
			} else {
				$this->Flash->error(__('Department could not be saved. Please, try again.'));
			}
		} else {
			$this->ViewExtension->setRedirectUrl(null, 'department');
		}
		$this->loadModel('EmployeeEdit');
		$fieldsInputMask = $this->EmployeeEdit->getListFieldsInputMask();
		$fieldInputMask = [];
		if (isset($fieldsInputMask['EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT])) {
			$fieldInputMask = (array)$fieldsInputMask['EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT];
		}
		$pageHeader = __('Adding department');
		$breadCrumbs = $this->Department->getBreadcrumbInfo();
		$breadCrumbs[] = __('Adding');
		$isAddAction = true;

		$this->set(compact('fieldInputMask', 'pageHeader', 'breadCrumbs', 'isAddAction'));
	}

/**
 * Action `add`. Used to add department.
 *  User role - human resources.
 *
 * @return void
 */
	public function hr_add() {
		$this->_add();
	}

/**
 * Action `add`. Used to add department.
 *  User role - administrator.
 *
 * @return void
 */
	public function admin_add() {
		$this->_add();
	}

/**
 * Base of action `edit`. Used to edit information about department.
 *
 * POST Data:
 *  - Department: array data of department
 *  - DepartmentExtension: array extended data of department
 *
 * @param int|string $id ID of record for editing
 * @throws NotFoundException if record for parameter $id was not found
 * @return void
 */
	protected function _edit($id = null) {
		$this->view = 'edit';
		$department = $this->Department->get($id);
		if (empty($department)) {
			return $this->ViewExtension->setExceptionMessage(new NotFoundException(__('Invalid ID for department')));
		}

		if ($this->request->is(['post', 'put'])) {
			$oldDepartmentName = Hash::get($department, 'Department.value');
			$newDepartmentName = $this->request->data('Department.value');
			if (!$department['Department']['block'] && !empty($oldDepartmentName) && !empty($newDepartmentName) &&
				($newDepartmentName !== $oldDepartmentName)) {
				$userRole = $this->UserInfo->getUserField('role');
				$userId = $this->UserInfo->getUserField('id');
				$useLdap = $this->Setting->getConfig('UseLdapOnEdit');
				if ($this->Department->putRenameDepartmentTask($oldDepartmentName, $newDepartmentName, $userRole, $userId, $useLdap)) {
					$this->Flash->information(__('Renaming department from "%s" to "%s" put in queue...', $oldDepartmentName, $newDepartmentName));
					$this->ViewExtension->setProgressSseTask('RenameDepartment');

					return $this->ViewExtension->redirectByUrl(null, 'department');
				}
			} elseif ($this->Department->saveAll($this->request->data, ['deep' => true])) {
				$this->Flash->success(__('Department has been saved.'));

				return $this->ViewExtension->redirectByUrl(null, 'department');
			} else {
				$this->Flash->error(__('Department could not be saved. Please, try again.'));
			}
		} else {
			$this->ViewExtension->setRedirectUrl(null, 'department');
			$this->request->data = $department;
		}
		$this->loadModel('EmployeeEdit');
		$fieldsInputMask = $this->EmployeeEdit->getListFieldsInputMask();
		$fieldInputMask = [];
		if (isset($fieldsInputMask['EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT])) {
			$fieldInputMask = (array)$fieldsInputMask['EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT];
		}
		$pageHeader = __('Editing department');
		$breadCrumbs = $this->Department->getBreadcrumbInfo($id);
		$breadCrumbs[] = __('Editing');

		$this->set(compact('fieldInputMask', 'pageHeader', 'breadCrumbs'));
	}

/**
 * Action `edit`. Used to edit information about department.
 *  User role - human resources.
 *
 * @param int|string $id ID of record for editing
 * @return void
 */
	public function hr_edit($id = null) {
		$this->_edit($id);
	}

/**
 * Action `edit`. Used to edit information about department.
 *  User role - administrator.
 *
 * @param int|string $id ID of record for editing
 * @return void
 */
	public function admin_edit($id = null) {
		$this->_edit($id);
	}

/**
 * Base of action `delete`. Used to delete department.
 *
 * @param int|string $id ID of record for deleting
 * @throws NotFoundException if record for parameter $id was not found
 * @throws MethodNotAllowedException if request is not `POST` or `DELETE`
 * @return void
 */
	protected function _delete($id = null) {
		$this->Department->id = $id;
		if (!$this->Department->exists()) {
			return $this->ViewExtension->setExceptionMessage(new NotFoundException(__('Invalid ID for department')));
		}

		$this->request->allowMethod('post', 'delete');
		$this->ViewExtension->setRedirectUrl(null, 'department');
		if ($this->Department->delete()) {
			$this->Flash->success(__('The department has been deleted.'));
		} else {
			$this->Flash->error(__('The department could not be deleted. Please, try again.'));
		}

		return $this->ViewExtension->redirectByUrl(null, 'department');
	}

/**
 * Action `delete`. Used to delete department.
 *  User role - human resources.
 *
 * @param int|string $id ID of record for deleting
 * @return void
 */
	public function hr_delete($id = null) {
		$this->_delete($id);
	}

/**
 * Action `delete`. Used to delete department.
 *  User role - administrator.
 *
 * @param int|string $id ID of record for deleting
 * @return void
 */
	public function admin_delete($id = null) {
		$this->_delete($id);
	}

/**
 * Action `move`. Used to move department to new position.
 *  User role - human resources.
 *
 * @param string $direct Direction for moving: `up`, `down`, `top`, `bottom`
 * @param int $id ID of record for moving
 * @param int $delta Delta for moving
 * @throws MethodNotAllowedException if request is not POST
 * @return void
 */
	public function hr_move($direct = null, $id = null, $delta = 1) {
		$this->Move->moveItem($direct, $id, $delta);
	}

/**
 * Action `move`. Used to move department to new position.
 *  User role - administrator.
 *
 * @param string $direct Direction for moving: `up`, `down`, `top`, `bottom`
 * @param int $id ID of record for moving
 * @param int $delta Delta for moving
 * @throws MethodNotAllowedException if request is not POST
 * @return void
 */
	public function admin_move($direct = null, $id = null, $delta = 1) {
		$this->Move->moveItem($direct, $id, $delta);
	}

/**
 * Action `drop`. Used to drag and drop department to new position.
 *  User role - human resources.
 *
 * POST Data:
 *  - `target` The ID of the item to moving to new position;
 *  - `parent` New parent ID of item;
 *  - `parentStart` Old parent ID of item;
 *  - `tree` Array of ID subtree for item.
 *
 * @throws BadRequestException if request is not AJAX, POST or JSON.
 * @throws MethodNotAllowedException if request is not POST
 * @return void
 */
	public function hr_drop() {
		$this->Move->dropItem();
	}

/**
 * Action `drop`. Used to drag and drop department to new position.
 *  User role - administrator.
 *
 * POST Data:
 *  - `target` The ID of the item to moving to new position;
 *  - `parent` New parent ID of item;
 *  - `parentStart` Old parent ID of item;
 *  - `tree` Array of ID subtree for item.
 *
 * @throws BadRequestException if request is not AJAX, POST or JSON.
 * @throws MethodNotAllowedException if request is not POST
 * @return void
 */
	public function admin_drop() {
		$this->Move->dropItem();
	}

/**
 * Base of action `check`. Used to check state tree of departments.
 *
 * @return void
 */
	protected function _check() {
		$this->view = 'check';
		set_time_limit(CHECK_TREE_DEPARTMENT_EXTENSION_TIME_LIMIT);
		$treeState = $this->Department->DepartmentExtension->verify();
		$pageHeader = __('Checking state list of departments');
		$headerMenuActions = [];
		if ($treeState !== true) {
			$headerMenuActions[] = [
				'fas fa-redo-alt',
				__('Recovery state of list'),
				['controller' => 'departments', 'action' => 'recover'],
				[
					'title' => __('Recovery state of list'),
					'data-toggle' => 'pjax',
				]
			];
		}
		$breadCrumbs = $this->Department->getBreadcrumbInfo();
		$breadCrumbs[] = __('Checking the list');

		$this->set(compact('treeState', 'pageHeader', 'headerMenuActions', 'breadCrumbs'));
	}

/**
 * Action `check`. Used to check state tree (list) of departments.
 *  User role - administrator.
 *
 * @return void
 */
	public function admin_check() {
		$this->_check();
	}

/**
 * Base of action `recover`. Used to recover state tree (list) of departments.
 *
 * @return void
 */
	protected function _recover() {
		$this->loadModel('Queue.QueuedTask');
		if ((bool)$this->QueuedTask->createJob('RecoveryDepartment', null, null, 'recovery')) {
			$this->Flash->success(__('Recovering list of departments put in queue...'));
			$this->ViewExtension->setProgressSseTask('RecoveryDepartment');
		} else {
			$this->Flash->error(__('Recovering list of departments put in queue unsuccessfully'));
		}
		$redirectUrl = ['controller' => 'departments', 'action' => 'check'];

		return $this->redirect($redirectUrl);
	}

/**
 * Action `recover`. Used to recover state tree (list) of departments.
 *  User role - administrator.
 *
 * @return void
 */
	public function admin_recover() {
		$this->_recover();
	}

/**
 * Base of action `order`. Used to reorder tree (list) of departments.
 *
 * @throws MethodNotAllowedException if request is not POST
 * @return void
 */
	protected function _order() {
		$this->request->allowMethod('post');
		$this->loadModel('Queue.QueuedTask');
		if ((bool)$this->QueuedTask->createJob('OrderDepartment', null, null, 'order')) {
			$this->Flash->success(__('Ordering list of departments put in queue...'));
			$this->ViewExtension->setProgressSseTask('OrderDepartment');
		} else {
			$this->Flash->error(__('Ordering list of departments put in queue unsuccessfully'));
		}
		$redirectUrl = ['controller' => 'departments', 'action' => 'index'];

		return $this->redirect($redirectUrl);
	}

/**
 * Action `order`. Used to reorder tree (list) of departments.
 *  User role - human resources.
 *
 * @return void
 */
	public function hr_order() {
		$this->_order();
	}

/**
 * Action `order`. Used to reorder tree (list) of departments.
 *  User role - administrator.
 *
 * @return void
 */
	public function admin_order() {
		$this->_order();
	}
}
