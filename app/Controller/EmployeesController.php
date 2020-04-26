<?php
/**
 * This file is the controller file of the application. Used for
 *  management information about the employees.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2020, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Controller
 */

App::uses('AppController', 'Controller');
App::uses('Hash', 'Utility');
App::uses('CakeTime', 'Utility');

/**
 * The controller is used for management information about the employees.
 *
 * This controller allows to perform the following operations:
 *  - viewing information of employees;
 *  - viewing information of tree subordinate employees;
 *  - to synchronize information of employees with AD;
 *  - reorder, checking and reocvery state tree of subordinate employees;
 *  - to move and change manager of employee using drag and drop;
 *  - display notifications about employees birthday.
 *
 * @package app.Controller
 */
class EmployeesController extends AppController {

/**
 * The name of this controller. Controller names are plural, named after the model they manipulate.
 *
 * @var string
 * @link http://book.cakephp.org/2.0/en/controllers.html#controller-attributes
 */
	public $name = 'Employees';

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
		'CakeTheme.Move' => ['model' => 'CakeLdap.SubordinateDb'],
		'CakeTheme.Upload' => ['uploadDir' => UPLOAD_DIR],
		'CakeLdap.EmployeeAction',
		'CakeSearchInfo.SearchFilter',
		'RequestHandler' => [
			'viewClassMap' => [
				'xlsx' => 'CakeSpreadsheet.Spreadsheet',
				'pdf' => 'CakeTCPDF.Pdf'
			]
		],
	];

/**
 * An array containing the class names of models this controller uses.
 *
 * @var mixed
 * @link http://book.cakephp.org/2.0/en/controllers.html#components-helpers-and-uses
 */
	public $uses = [
		'Employee',
		'EmployeeEdit',
		'CakeSearchInfo.Search'
	];

/**
 * An array containing the names of helpers this controller uses. The array elements should
 * not contain the "Helper" part of the class name.
 *
 * @var mixed
 * @link http://book.cakephp.org/2.0/en/controllers.html#components-helpers-and-uses
 */
	public $helpers = [
		'EmployeeInfo',
		'Tools.Tree',
		'Time',
		'Number',
	];

/**
 * Settings for component 'Paginator'
 *
 * @var array
 */
	public $paginate = [
		'page' => '1',
		'limit' => '20',
		'maxLimit' => 250,
	];

/**
 * Check if the provided user is authorized.
 *  Uses to check whether or not a user is authorized.
 *
 * @param array $user The user to check the authorization of.
 * @return bool True if $user is authorized, otherwise false
 */
	public function isAuthorized($user = []) {
		$action = $this->request->param('action');
		$allowedActions = ['index', 'view'];
		if ($this->UserInfo->checkUserRole(USER_ROLE_USER, true, $user) &&
			in_array($action, $allowedActions)) {
			return true;
		}

		return parent::isAuthorized($user);
	}

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
			'hr_move', 'admin_move',
			'hr_drop', 'admin_drop',
			'upload', 'secret_upload', 'hr_upload', 'admin_upload',
			'managers', 'secret_managers', 'hr_managers', 'admin_managers'
		];

		$isExternalAuth = $this->_isExternalAuth();
		if (!$isExternalAuth && $this->Setting->getConfig('AllowAnonymAccess')) {
			$allowActions = [
				'index',
				'search',
				'view',
				'tree',
				'gallery',
				'export',
				'download'
			];
			$this->Auth->allow($allowActions);
		}

		parent::beforeFilter();
	}

/**
 * Base of action `index`. Used to start searching employee information.
 *
 * @return void
 */
	protected function _index() {
		$this->view = 'index';
		$this->disableCache();

		$showBreadcrumb = false;
		$this->loadModel('LastProcessed');
		$lastUpdate = $this->LastProcessed->getLastUpdate(LAST_PROCESSED_EMPLOYEE);
		$countEmployees = $this->Employee->getNumberOf();
		$birthdays = $this->Employee->getListBirthday(null, BIRTHDAY_LIST_LIMIT);
		$pageTitle = __('Information retrieval');
		$this->set(compact(
			'showBreadcrumb',
			'lastUpdate',
			'countEmployees',
			'birthdays',
			'pageTitle'
		));
		$this->set('search_targetFields', null);
	}

/**
 * Action `index`. Used to start searching employee information.
 *  User role - user.
 *
 * @return void
 */
	public function index() {
		$this->_index();
	}

/**
 * Action `index`. Used to start searching employee information.
 *  User role - secretary.
 *
 * @return void
 */
	public function secret_index() {
		$this->_index();
	}

/**
 * Action `index`. Used to start searching employee information.
 *  User role - human resources.
 *
 * @return void
 */
	public function hr_index() {
		$this->_index();
	}

/**
 * Action `index`. Used to start searching employee information.
 *  User role - administrator.
 *
 * @return void
 */
	public function admin_index() {
		$this->_index();
	}

/**
 * Base of action `search`. Is used to searching employee information.
 *
 * @return void
 */
	protected function _search() {
		$this->view = 'search';
		$showBreadcrumb = false;
		$showSearchForm = true;
		$specificJS = 'index';
		$userRole = $this->UserInfo->getUserField('role');
		$paginatorOptions = $this->Employee->getPaginatorOptions($userRole);
		$targetModels = $this->Employee->getSearchTargetModels($userRole);
		$fieldsConfig = $this->Employee->getFieldsConfig();
		$whitelist = [];
		$employeeFields = Hash::extract($targetModels, 'Employee.fields');
		$order = Hash::get($targetModels, 'Employee.order');
		if (!empty($employeeFields)) {
			$whitelist = array_keys($employeeFields);
		}
		if (!empty($order)) {
			$this->paginate['order'] = $order;
		}
		$this->SearchFilter->search($whitelist);
		$pageTitle = __('Search information');
		$this->set(compact(
			'showSearchForm',
			'specificJS',
			'showBreadcrumb',
			'paginatorOptions',
			'fieldsConfig',
			'pageTitle'
		));
	}

/**
 * Action `search`. Used to searching employee information.
 *  User role - user.
 *
 * @return void
 */
	public function search() {
		$this->_search();
	}

/**
 * Action `search`. Used to searching employee information.
 *  User role - secretary.
 *
 * @return void
 */
	public function secret_search() {
		$this->_search();
	}

/**
 * Action `search`. Used to searching employee information.
 *  User role - human resources.
 *
 * @return void
 */
	public function hr_search() {
		$this->_search();
	}

/**
 * Action `search`. Used to searching employee information.
 *  User role - administrator.
 *
 * @return void
 */
	public function admin_search() {
		$this->_search();
	}

/**
 * Base of action `view`. Used to view information about employee.
 *
 * @param int|string $id ID of record for viewing.
 * @return void
 */
	protected function _view($id = null) {
		$this->view = 'view';
		$userRole = $this->UserInfo->getUserField('role');
		$excludeFields = $this->Employee->getListExcludeFieldsDb($userRole);
		$excludeFieldsLabel = $this->Employee->getListExcludeFieldsLabel($userRole);
		$contain = [
			'DepartmentExtension'
		];
		$this->EmployeeAction->actionView($id, $excludeFields, $excludeFieldsLabel, $contain);
		$employee = (array)Hash::get($this->viewVars, 'employee');
		$isTreeReady = Hash::get($this->viewVars, 'isTreeReady');
		$pageHeader = __('Information of employees');
		$headerMenuActions = [];
		$userId = $this->UserInfo->getUserField('id');
		if (isset($employee['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID]) &&
			$this->UserInfo->checkUserRole([USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN], true)) {
			$headerMenuActions[] = [
				'fas fa-pencil-alt',
				__('Edit information'),
				['controller' => 'employees', 'action' => 'edit', $employee['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID]],
				['title' => __('Edit information of this employee')]
			];
			$headerMenuActions[] = [
				'fas fa-sync-alt',
				__('Synchronize information'),
				['controller' => 'employees', 'action' => 'sync', $employee['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID]],
				[
					'title' => __('Synchronize information of this employee with LDAP server'),
					'data-toggle' => 'request-only'
				]
			];
		} elseif (!empty($userId) && isset($employee['Employee']['id']) &&
			($employee['Employee']['id'] === $userId)) {
			$headerMenuActions[] = [
				'fas fa-pencil-alt',
				__('Edit information'),
				['controller' => 'employees', 'action' => 'edit', $employee['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID]],
				['title' => __('Edit information of this employee')]
			];
		}
		if ($isTreeReady) {
			$headerMenuActions[] = [
				'fas fa-sitemap',
				__('Tree of subordinate'),
				['controller' => 'employees', 'action' => 'tree', $employee['Employee']['id']],
				['title' => __('Edit tree of subordinate employee')]
			];
		}
		$breadCrumbs = $this->Employee->getBreadcrumbInfo($id);
		$breadCrumbs[] = __('Viewing');
		$this->set(compact('pageHeader', 'headerMenuActions', 'breadCrumbs'));
	}

/**
 * Action `view`. Used to view information about employee.
 * User role - user.
 *
 * @param int|string $id ID of record for viewing
 * @return void
 */
	public function view($id = null) {
		$this->_view($id);
	}

/**
 * Action `view`. Used to view information about employee.
 * User role - secretary.
 *
 * @param int|string $id ID of record for viewing
 * @return void
 */
	public function secret_view($id = null) {
		$this->_view($id);
	}

/**
 * Action `view`. Used to view information about employee.
 * User role - human resources.
 *
 * @param int|string $id ID of record for viewing
 * @return void
 */
	public function hr_view($id = null) {
		$this->_view($id);
	}

/**
 * Action `view`. Used to view information about employee.
 * User role - administrator.
 *
 * @param int|string $id ID of record for viewing
 * @return void
 */
	public function admin_view($id = null) {
		$this->_view($id);
	}

/**
 * Base of action `edit`. Used to edit information of employee.
 *
 * POST Data:
 *  - EmployeeEdit: array data of employee
 *  - EmployeePhoto: array data for changing photo employee
 *
 * @param string $guid GUID of employee for editing
 * @throws MethodNotAllowedException if user GUID is not equal parameter $guid
 * @return void
 */
	protected function _edit($guid = null) {
		$this->view = 'edit';
		$userRole = $this->UserInfo->getUserField('role');
		$useLdap = $this->Setting->getConfig('UseLdapOnEdit');
		$this->loadModel('Deferred');
		$deferredSaveData = $this->Deferred->get($guid, true, $userRole, 'guid', $useLdap);
		if (!empty($deferredSaveData) && is_array($deferredSaveData['Deferred']['data'])) {
			$employeeInfo = ['EmployeeEdit' => $deferredSaveData['Deferred']['data']['changed']['EmployeeEdit']];
		} else {
			$employeeInfo = $this->EmployeeEdit->get($guid, $userRole, $useLdap);
			if (empty($employeeInfo)) {
				return $this->ViewExtension->setExceptionMessage(new NotFoundException(__('Invalid GUID for employee')));
			}
		}

		if (!$this->UserInfo->checkUserRole([USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN], true)) {
			$guidField = 'includedFields.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID;
			$userGuid = $this->UserInfo->getUserField($guidField);
			if ($userGuid !== $guid) {
				throw new MethodNotAllowedException(__('GUID of user is not equal GUID of employee'));
			}
		}

		$forceDeferred = false;
		$employeePhoto = null;
		$employeeManager = null;
		$dn = null;
		if (isset($employeeInfo['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO])) {
			$employeePhoto = $employeeInfo['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO];
		}
		if (isset($employeeInfo['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER])) {
			$employeeManager = $employeeInfo['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER];
		}
		if (isset($employeeInfo['EmployeeEdit'][CAKE_LDAP_LDAP_DISTINGUISHED_NAME])) {
			$dn = $employeeInfo['EmployeeEdit'][CAKE_LDAP_LDAP_DISTINGUISHED_NAME];
		}
		if ($this->request->is(['post', 'put'])) {
			if ($employeePhoto !== null) {
				$this->request->data('EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO, $employeePhoto);
			}
			$result = $this->EmployeeEdit->saveInformation($this->request->data, $userRole, true, false);
			$redirecturl = $this->ViewExtension->getRedirectUrl(null, 'employee');
			if ($result === true) {
				$this->Flash->success(__(
					'Deferred saving with an updated employee information was created.<br />Information on LDAP server will be updated by queue.<br />Information in phonebook will be updated %s after processing.',
					CakeTime::timeAgoInWords(strtotime('+' . DEFERRED_SAVE_SYNC_DELAY . ' second'), ['accuracy' => ['second' => 'minute']])
				));
				$this->ViewExtension->setProgressSseTask('DeferredSave');

				return $this->redirect($redirecturl);
			} elseif ($result === null) {
				$this->Flash->warning(__('Deferred saving with an updated employee information was created.<br />Information will be updated after approval by the administrator.'));

				return $this->redirect($redirecturl);
			} else {
				$this->Flash->error(__('Information about the employee could not be been saved. Please, try again.'));
			}
		} else {
			$this->ViewExtension->setRedirectUrl(null, 'employee');
			$this->request->data = $employeeInfo;
			$this->request->data('EmployeePhoto.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID, $guid);
			$this->request->data('EmployeePhoto.force_deferred', $forceDeferred);
			$this->EmployeeEdit->createValidationRules($userRole);
		}
		$changedFields = [];
		if (!empty($deferredSaveData) && isset($deferredSaveData['ChangedFields'])) {
			$changedFields = $deferredSaveData['ChangedFields'];
		}
		$managers = [];
		if (!empty($employeeManager)) {
			$fullListManagers = $this->EmployeeEdit->getListManagers();
			if (isset($fullListManagers[$employeeManager])) {
				$managers[$employeeManager] = $fullListManagers[$employeeManager];
			}
		}

		$departments = $this->Employee->Department->getListDepartmentsWithExtension();
		$fieldsLabel = $this->EmployeeEdit->getListFieldsLabel(false, $userRole);
		$fieldsLabelAlt = $this->EmployeeEdit->getListFieldsLabel(true, $userRole);
		$fieldsInputMask = $this->EmployeeEdit->getListFieldsInputMask();
		$fieldsInputTooltip = $this->EmployeeEdit->getListFieldsInputTooltip();
		$readOnlyFields = $this->EmployeeEdit->getListReadOnlyFields();
		$maxfilesize = $this->EmployeeEdit->getLimitPhotoSize();
		$acceptfiletypes = $this->EmployeeEdit->getAcceptFileTypes();
		$maxLinesMultipleValue = $this->EmployeeEdit->getLimitLinesMultipleValue();
		$pageHeader = __('Editing employee');
		$breadCrumbs = $this->Employee->getBreadcrumbInfo($guid);
		$breadCrumbs[] = __('Editing');

		$this->set(compact(
			'dn',
			'guid',
			'managers',
			'departments',
			'fieldsLabel',
			'fieldsLabelAlt',
			'fieldsInputMask',
			'fieldsInputTooltip',
			'readOnlyFields',
			'maxfilesize',
			'acceptfiletypes',
			'maxLinesMultipleValue',
			'employeePhoto',
			'forceDeferred',
			'changedFields',
			'pageHeader',
			'breadCrumbs'
		));
	}

/**
 * Action `edit`. Used to edit information of employee.
 *  User role - user.
 *
 * @param string $guid GUID of employee for editing
 * @return void
 */
	public function edit($guid = null) {
		$this->_edit($guid);
	}

/**
 * Action `edit`. Used to edit information of employee.
 *  User role - secretary.
 *
 * @param string $guid GUID of employee for editing
 * @return void
 */
	public function secret_edit($guid = null) {
		$this->_edit($guid);
	}

/**
 * Action `edit`. Used to edit information of employee.
 *  User role - human resources.
 *
 * @param string $guid GUID of employee for editing
 * @return void
 */
	public function hr_edit($guid = null) {
		$this->_edit($guid);
	}

/**
 * Action `edit`. Used to edit information of employee.
 *  User role - administrator.
 *
 * @param string $guid GUID of employee for editing
 * @return void
 */
	public function admin_edit($guid = null) {
		$this->_edit($guid);
	}

/**
 * Base of action `sync`. Used to to synchronization information of employee
 *  with Active Directory.
 *
 * @param string|null $guid GUID of employee for synchronization
 * @return void
 */
	protected function _sync($guid = null) {
		$this->view = 'sync';
		$this->EmployeeAction->actionSync($guid);
	}

/**
 * Action `sync`. Is used to synchronization information of employee
 *  with Active Directory.
 *  User role - human resources.
 *
 * @param string|null $guid GUID of employee for synchronization
 * @return void
 */
	public function hr_sync($guid = null) {
		$this->_sync($guid);
	}

/**
 * Action `sync`. Is used to synchronization information of employee
 *  with Active Directory.
 *  User role - administrator.
 *
 * @param string|null $guid GUID of employee for synchronization
 * @return void
 */
	public function admin_sync($guid = null) {
		$this->_sync($guid);
	}

/**
 * Base of action `gallery`. Used to view gallery of employees.
 *
 * @throws MethodNotAllowedException if display photo of employee not allowed
 * @return void
 */
	protected function _gallery() {
		$this->view = 'gallery';
		$userRole = $this->UserInfo->getUserField('role');
		$paginatorOptions = $this->Employee->getPaginatorOptionsGallery($userRole);
		if ($paginatorOptions === false) {
			throw new MethodNotAllowedException(__('Display photo of employee not allowed'));
		}

		$this->Paginator->settings = $paginatorOptions;
		$conditions = $this->Filter->getFilterConditions();
		$conditions['Employee.block'] = false;
		$employeesGallery = $this->Paginator->paginate('Employee', $conditions);
		if (empty($employeesGallery)) {
			$this->Flash->information(__('Employees not found'));
		}

		$emptyDepartmentName = $this->Employee->getEmptyDepartmentName();
		$pageHeader = __('Gallery of employees');
		$breadCrumbs = $this->Employee->getBreadcrumbInfo();
		$breadCrumbs[] = __('Gallery');
		$this->ViewExtension->setRedirectUrl(true, 'employee');

		$this->set(compact('employeesGallery', 'emptyDepartmentName', 'pageHeader', 'breadCrumbs'));
	}

/**
 * Action `gallery`. Used to view gallery of employees.
 *  User role - user.
 *
 * @return void
 */
	public function gallery() {
		$this->_gallery();
	}

/**
 * Action `gallery`. Used to view gallery of employees.
 *  User role - secretary.
 *
 * @return void
 */
	public function secret_gallery() {
		$this->_gallery();
	}

/**
 * Action `gallery`. Used to view gallery of employees.
 *  User role - human resources.
 *
 * @return void
 */
	public function hr_gallery() {
		$this->_gallery();
	}

/**
 * Action `gallery`. Used to view gallery of employees.
 *  User role - administrator.
 *
 * @return void
 */
	public function admin_gallery() {
		$this->_gallery();
	}

/**
 * Base of action `tree`. Used to view tree of subordinate employees.
 *
 * @param int $id ID of employee for viewing tree of subordinate
 * @param bool $useMove If True, show controls for moving employee
 * @return void
 */
	protected function _tree($id = null, $useMove = false) {
		$this->view = 'tree';
		$includeFields = null;
		if ($useMove) {
			$includeFields = ['Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID];
			$this->ViewExtension->setRedirectUrl(null, 'employee');
		}
		$this->EmployeeAction->actionTree($id, false, $includeFields);
		$expandAll = false;
		$isTreeDraggable = false;
		if ($useMove) {
			$expandAll = true;
			$isTreeDraggable = $this->UserInfo->checkUserRole([USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN], true);
		}
		$pageHeader = __('Tree view information of employees');
		$headerMenuActions = [];
		if ($this->UserInfo->checkUserRole(USER_ROLE_ADMIN, false)) {
			$headerMenuActions = [
				[
					'fas fa-sync-alt',
					__('Synchronizing information with LDAP server'),
					['controller' => 'employees', 'action' => 'sync'],
					[
						'title' => __('Synchronize information of employees with LDAP server'),
						'data-toggle' => 'request-only',
					]
				],
				[
					'fas fa-check',
					__('Check state tree of employees'),
					['controller' => 'employees', 'action' => 'check'],
					[
						'title' => __('Check state tree of employees'),
						'data-toggle' => 'modal'
					]
				]
			];
			if (!$useMove) {
				$idLink = $id;
				if (empty($idLink)) {
					$idLink = 0;
				}
				$headerMenuActions[] = [
					'fas fa-pencil-alt',
					__('Editing tree of employees'),
					['controller' => 'employees', 'action' => 'tree', $idLink, true],
					['title' => __('Editing tree of employees')]
				];
			}
		}
		if ($this->UserInfo->checkUserRole([USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN])) {
			$headerMenuActions[] = [
				'fas fa-sort-alpha-down',
				__('Order tree of employees'),
				['controller' => 'employees', 'action' => 'order'],
				[
					'title' => __('Order tree of employees by alphabet'),
					'action-type' => 'confirm-post',
					'data-confirm-msg' => __('Are you sure you wish to re-order tree of employees?')
				]
			];
		}
		$breadCrumbs = $this->Employee->getBreadcrumbInfo();
		$breadCrumbs[] = __('Tree viewing');

		$this->set(compact('id', 'isTreeDraggable', 'expandAll', 'pageHeader', 'headerMenuActions', 'breadCrumbs'));
	}

/**
 * Action `tree`. Used to view tree of subordinate employees.
 *  User role - user.
 *
 * @param int $id ID of employee for viewing tree of subordinate
 * @return void
 */
	public function tree($id = null) {
		$this->_tree($id, false);
	}

/**
 * Action `tree`. Used to view tree of subordinate employees.
 *  User role - secretary.
 *
 * @param int $id ID of employee for viewing tree of subordinate
 * @return void
 */
	public function secret_tree($id = null) {
		$this->_tree($id, false);
	}

/**
 * Action `tree`. Used to view tree of subordinate employees.
 *  User role - human resources.
 *
 * @param int $id ID of employee for viewing tree of subordinate
 * @param bool $useMove If True, show controls for moving employee
 * @return void
 */
	public function hr_tree($id = null, $useMove = false) {
		$this->_tree($id, $useMove);
	}

/**
 * Action `tree`. Used to view tree of subordinate employees.
 *  User role - administrator.
 *
 * @param int $id ID of employee for viewing tree of subordinate
 * @param bool $useMove If True, show controls for moving employee
 * @return void
 */
	public function admin_tree($id = null, $useMove = false) {
		$this->_tree($id, $useMove);
	}

/**
 * Action `move`. Used to move employee to new position.
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
 * Action `move`. Used to move employee to new position.
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
 * Action `drop`. Used to drag and drop department to new position,
 *  include new manager.
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
 * Action `drop`. Used to drag and drop department to new position,
 *  include new manager.
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
 * Base of action `check`. Used to check state tree of subordinate employees.
 *
 * @return void
 */
	protected function _check() {
		$this->view = 'check';
		$this->EmployeeAction->actionCheck();
		$treeState = Hash::get($this->viewVars, 'treeState');
		$pageHeader = __('Checking state tree of employees');
		$headerMenuActions = [];
		if ($treeState !== true) {
			$headerMenuActions[] = [
				'fas fa-redo-alt',
				__('Recovery state of tree'),
				['controller' => 'employees', 'action' => 'recover'],
				[
					'title' => __('Recovery state of tree'),
					'data-toggle' => 'request-only',
				]
			];
		}
		$breadCrumbs = $this->Employee->getBreadcrumbInfo();
		$breadCrumbs[] = __('Checking tree');

		$this->set(compact('pageHeader', 'headerMenuActions', 'breadCrumbs'));
	}

/**
 * Action `check`. Used to check state tree of subordinate employees.
 *  User role - administrator.
 *
 * @return void
 */
	public function admin_check() {
		$this->_check();
	}

/**
 * Action `order`. Used to reorder tree of subordinate employees.
 *  User role - human resources.
 *
 * @throws MethodNotAllowedException if request is not POST
 * @return void
 */
	public function hr_order() {
		$this->EmployeeAction->actionOrder();
	}

/**
 * Action `order`. Used to reorder tree of subordinate employees.
 *  User role - administrator.
 *
 * @throws MethodNotAllowedException if request is not POST
 * @return void
 */
	public function admin_order() {
		$this->EmployeeAction->actionOrder();
	}

/**
 * Action `check`. Used to recover state tree of subordinate employees.
 *  User role - administrator.
 *
 * @return void
 */
	public function admin_recover() {
		$this->EmployeeAction->actionRecover();
	}

/**
 * Base of action `delete_photo`. Used to delete photo of employee.
 *
 * @param string $guid GUID of employee for deleting photo
 * @param bool $forceDeferred If true, create deferred save.
 *  Otherwise save information on LDAP server.
 * @throws MethodNotAllowedException if request is not `POST` or `DELETE`
 * @return void
 */
	protected function _deletePhoto($guid = null, $forceDeferred = false) {
		$dn = $this->EmployeeEdit->getDnEmployee($guid);
		if (empty($dn)) {
			return $this->ViewExtension->setExceptionMessage(new NotFoundException(__('Invalid GUID for employee')));
		}

		$this->request->allowMethod('post', 'delete');
		$this->ViewExtension->setRedirectUrl(null, 'employee');
		$userRole = USER_ROLE_SECRETARY;
		if (!$forceDeferred) {
			$userRole = $this->UserInfo->getUserField('role');
		}
		$useLdap = $this->Setting->getConfig('UseLdapOnEdit');
		$result = $this->EmployeeEdit->clearPhoto($guid, $userRole, $useLdap);
		if ($result === true) {
			$this->Flash->success(__(
				'Deferred saving with an deleted employee photo was created.<br />Information on LDAP server will be updated by queue.<br />Information in phonebook will be updated %s after processing.',
				CakeTime::timeAgoInWords(strtotime('+' . DEFERRED_SAVE_SYNC_DELAY . ' second'), ['accuracy' => ['second' => 'minute']])
			));
			$this->ViewExtension->setProgressSseTask('DeferredSave');
		} elseif ($result === null) {
			$this->Flash->warning(__('Deferred saving with an deleted employee photo was created.<br />Information will be updated after approval by the administrator.'));
		} else {
			$this->Flash->error(__('The photo of employee could not be deleted. Please, try again.'));
		}

		return $this->ViewExtension->redirectByUrl(['controller' => 'employees', 'action' => 'view', $dn], 'employee');
	}

/**
 * Action `delete`. Used to delete photo of employee.
 *  User role - human resources.
 *
 * @param string $guid GUID of employee for deleting photo
 * @param bool $forceDeferred If true, create deferred save.
 *  Otherwise save information on LDAP server.
 * @return void
 */
	public function hr_delete_photo($guid = null, $forceDeferred = false) {
		$this->_deletePhoto($guid, $forceDeferred);
	}

/**
 * Action `delete`. Used to delete photo of employee.
 *  User role - administrator.
 *
 * @param string $guid GUID of employee for deleting photo
 * @param bool $forceDeferred If true, create deferred save.
 *  Otherwise save information on LDAP server.
 * @return void
 */
	public function admin_delete_photo($guid = null, $forceDeferred = false) {
		$this->_deletePhoto($guid, $forceDeferred);
	}

/**
 * Base of action `upload`. Used to upload file of employee photo.
 *
 * POST Data:
 *  - EmployeePhoto: array data for changing photo employee
 *
 * @throws BadRequestException if request is not `AJAX` or not `JSON`
 * @throws NotFoundException if record for data $guid was not found
 * @return void
 */
	protected function _upload() {
		Configure::write('debug', 0);
		if (!$this->request->is('ajax') || !$this->RequestHandler->prefers('json')) {
			throw new BadRequestException(__('Invalid request'));
		}

		$this->request->allowMethod('post');
		$guid = $this->request->data('EmployeePhoto.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID);
		$dn = $this->EmployeeEdit->getDnEmployee($guid);
		if (empty($dn)) {
			throw new NotFoundException(__('Invalid GUID for employee'));
		}

		$forceDeferred = $this->request->data('EmployeePhoto.force_deferred');
		$uploadDir = $this->Upload->getUploadDir();
		$maxFileSize = $this->EmployeeEdit->getLimitPhotoSize();
		$filePhotoContent = null;
		$acceptfiletypes = $this->EmployeeEdit->getAcceptFileTypes(true);
		$data = $this->Upload->upload($maxFileSize, $acceptfiletypes);
		if (!isset($data['files'][0])) {
			$this->set(compact('data'));
			$this->set('_serialize', 'data');

			return;
		}

		$oFile = $data['files'][0];
		$fileName = $uploadDir . $oFile->name;
		if (!file_exists($fileName)) {
			$this->set(compact('data'));
			$this->set('_serialize', 'data');

			return;
		}

		$oFile->url = '';
		$userRole = USER_ROLE_USER;
		if (!$forceDeferred) {
			$userRole = $this->UserInfo->getUserField('role');
		}
		$useLdap = $this->Setting->getConfig('UseLdapOnEdit');
		$resultUpdatePhoto = $this->EmployeeEdit->updatePhoto($guid, $fileName, $maxFileSize, $userRole, $useLdap);
		if ($resultUpdatePhoto === true) {
			$this->Flash->success(__(
				'Deferred saving with an updated employee photo was created.<br />Information on LDAP server will be updated by queue.<br />Information in phonebook will be updated %s after processing.',
				CakeTime::timeAgoInWords(strtotime('+' . DEFERRED_SAVE_SYNC_DELAY . ' second'), ['accuracy' => ['second' => 'minute']])
			));
			$this->ViewExtension->setProgressSseTask('DeferredSave');
		} elseif ($resultUpdatePhoto === null) {
			$this->Flash->warning(__('Deferred saving with an updated employee photo was created.<br />Information will be updated after approval by the administrator.'));
		} else {
			$oFile->error = __('Unable to update photo of employee.');
		}
		$data['files'][0] = $oFile;

		$this->set(compact('data'));
		$this->set('_serialize', 'data');
	}

/**
 * Action `upload`. Used to upload file of employee photo.
 *  User role - user.
 *
 * @return void
 */
	public function upload() {
		$this->_upload();
	}

/**
 * Action `upload`. Used to upload file of employee photo.
 *  User role - secretary.
 *
 * @return void
 */
	public function secret_upload() {
		$this->_upload();
	}

/**
 * Action `upload`. Used to upload file of employee photo.
 *  User role - human resources.
 *
 * @return void
 */
	public function hr_upload() {
		$this->_upload();
	}

/**
 * Action `upload`. Used to upload file of employee photo.
 *  User role - administrator.
 *
 * @return void
 */
	public function admin_upload() {
		$this->_upload();
	}

/**
 * Base of action `managers`. Used to search managers in list.
 *
 * POST Data:
 *  - q: query data to search
 *
 * @throws BadRequestException if request is not `AJAX` or not `JSON`
 * @return void
 */
	protected function _managers() {
		Configure::write('debug', 0);
		if (!$this->request->is('ajax') || !$this->RequestHandler->prefers('json')) {
			throw new BadRequestException(__('Invalid request'));
		}

		$this->request->allowMethod('post');
		$query = (string)$this->request->data('q');
		$dn = (string)$this->request->data('dn');
		$data = $this->EmployeeEdit->getListManagersByQuery($query, $dn);

		$this->set(compact('data'));
		$this->set('_serialize', 'data');
	}

/**
 * Action `managers`. Used to search managers in list.
 *  User role - user.
 *
 * @return void
 */
	public function managers() {
		$this->_managers();
	}

/**
 * Action `managers`. Used to search managers in list.
 *  User role - secretary.
 *
 * @return void
 */
	public function secret_managers() {
		$this->_managers();
	}

/**
 * Action `managers`. Used to search managers in list.
 *  User role - human resources.
 *
 * @return void
 */
	public function hr_managers() {
		$this->_managers();
	}

/**
 * Action `managers`. Used to search managers in list.
 *  User role - administrator.
 *
 * @return void
 */
	public function admin_managers() {
		$this->_managers();
	}

/**
 * Base of action `export`. Used to view a complete list of
 *  phone book files.
 *
 * @return void
 */
	protected function _export() {
		$this->view = 'export';
		$exportInfo = $this->Employee->getExportInfo();
		$pageHeader = __('Index of phone book files');
		$headerMenuActions = [];
		if ($this->UserInfo->checkUserRole([USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN], true)) {
			$headerMenuActions[] = [
				'fas fa-sync-alt',
				__('Refresh all files'),
				[
					'controller' => 'employees',
					'action' => 'generate',
					GENERATE_FILE_VIEW_TYPE_ALL,
					GENERATE_FILE_DATA_TYPE_ALL,
					true
				],
				[
					'title' => __('Refresh all exported files'),
					'data-toggle' => 'request-only',
				]
			];
		}
		$breadCrumbs = $this->Employee->getBreadcrumbInfo();
		$breadCrumbs[] = __('Export phone book');

		$this->set(compact('exportInfo', 'pageHeader', 'headerMenuActions', 'breadCrumbs'));
	}

/**
 * Action `upload`. Used to view a complete list of
 *  phone book files.
 *  User role - user.
 *
 * @return void
 */
	public function export() {
		$this->_export();
	}

/**
 * Action `upload`. Used to view a complete list of
 *  phone book files.
 *  User role - secretary.
 *
 * @return void
 */
	public function secret_export() {
		$this->_export();
	}

/**
 * Action `upload`. Used to view a complete list of
 *  phone book files.
 *  User role - human resources.
 *
 * @return void
 */
	public function hr_export() {
		$this->_export();
	}

/**
 * Action `upload`. Used to view a complete list of
 *  phone book files.
 *  User role - administrator.
 *
 * @return void
 */
	public function admin_export() {
		$this->_export();
	}

/**
 * Base of action `download`. Used to download PDF or Excel file
 *
 * @param string $type Type of export:
 *  - `alph` - by alphabet;
 *  - `depart` - by department.
 * @param bool $extendView If true, include to export file extended fields.
 * @throws InternalErrorException if argument $type is not `alph` or not `depart`
 * @throws InternalErrorException if export extension is not `.xlsx` or not `.pdf`
 * @return void
 */
	protected function _download($type = null, $extendView = false) {
		$type = mb_strtolower($type);
		$fileExt = mb_strtolower((string)$this->RequestHandler->prefers());
		if (!in_array($type, [GENERATE_FILE_DATA_TYPE_ALPH, GENERATE_FILE_DATA_TYPE_DEPART])) {
			throw new InternalErrorException(__('Unknown type for generation %s.', mb_strtoupper($fileExt)));
		}

		$fileExportDir = $this->Employee->getPathExportDir();
		$fileName = $this->Employee->expandTypeExportToFilename($type, $extendView, true);
		$downloadFileName = $this->Employee->expandTypeExportToFilename($type, $extendView, false);
		$downloadFileNameFull = $downloadFileName . '.' . $fileExt;
		$filePath = $fileExportDir . $fileName . '.' . $fileExt;

		if ($fileExt === 'xlsx') {
			$view = GENERATE_FILE_VIEW_TYPE_EXCEL;
		} elseif ($fileExt === 'pdf') {
			$view = GENERATE_FILE_VIEW_TYPE_PDF;
		} else {
			throw new InternalErrorException(__('Invalid export type'));
		}

		if (!file_exists($filePath)) {
			if ($this->Employee->putExportTask($view, $type)) {
				$this->Flash->information(__('Generation file "%s" put in queue...', $downloadFileNameFull));
			} else {
				$this->Flash->error(__('Generation file "%s" put in queue unsucsessfully...', $downloadFileNameFull));
			}

			return $this->redirect(['controller' => 'employees', 'action' => 'export']);
		}
		if ($this->request->is('msie')) {
			$downloadFileNameFull = rawurlencode($downloadFileNameFull);
		}
		$this->response->file($filePath, ['download' => true, 'name' => $downloadFileNameFull]);

		return $this->response;
	}

/**
 * Action `download`. Used to download PDF or Excel file.
 *  User role - user.
 *
 * @param string $type Type of export:
 *  - `alph` - by alphabet;
 *  - `depart` - by department.
 * @return void
 */
	public function download($type = null) {
		return $this->_download($type, false);
	}

/**
 * Action `download`. Used to download PDF or Excel file.
 *  User role - secretary.
 *
 * @param string $type Type of export:
 *  - `alph` - by alphabet;
 *  - `depart` - by department.
 * @param bool $extendView If true, include to export file extended fields.
 * @return void
 */
	public function secret_download($type = null, $extendView = null) {
		return $this->_download($type, $extendView);
	}

/**
 * Action `download`. Used to download PDF or Excel file.
 *  User role - human resources.
 *
 * @param string $type Type of export:
 *  - `alph` - by alphabet;
 *  - `depart` - by department.
 * @param bool $extendView If true, include to export file extended fields.
 * @return void
 */
	public function hr_download($type = null, $extendView = null) {
		return $this->_download($type, $extendView);
	}

/**
 * Action `download`. Used to download PDF or Excel file.
 *  User role - administrator.
 *
 * @param string $type Type of export:
 *  - `alph` - by alphabet;
 *  - `depart` - by department.
 * @param bool $extendView If true, include to export file extended fields.
 * @return void
 */
	public function admin_download($type = null, $extendView = null) {
		return $this->_download($type, $extendView);
	}

/**
 * Base of action `generate`. Used to put task generation exported files in queue
 *
 * @param string $view View of export:
 *  - `excel` - MS Excel;
 *  - `pdf` - PDF.
 * @param string $type Type of export:
 *  - `alph` - by alphabet;
 *  - `depart` - by department.
 * @param bool $forceUpdate Flag of forced update files
 * @return void
 */
	protected function _generate($view = null, $type = null, $forceUpdate = false) {
		if ($this->Employee->putExportTask($view, $type, $forceUpdate)) {
			$this->Flash->success(__('Generation of exported files put in queue...'));
			$this->ViewExtension->setProgressSseTask('Generate');
		} else {
			$this->Flash->error(__('Generation export files put in queue unsucsessfully...'));
		}
		$this->ViewExtension->setRedirectUrl(null, 'employee');

		return $this->ViewExtension->redirectByUrl(['controller' => 'employees', 'action' => 'export'], 'employee');
	}

/**
 * Action `generate`. Used to put task generation exported files in queue.
 *  User role - human resources.
 *
 * @param string $view View of export:
 *  - `excel` - MS Excel;
 *  - `pdf` - PDF.
 * @param string $type Type of export:
 *  - `alph` - by alphabet;
 *  - `depart` - by department.
 * @param bool $forceUpdate Flag of forced update files
 * @return void
 */
	public function hr_generate($view = null, $type = null, $forceUpdate = false) {
		$this->_generate($view, $type, $forceUpdate);
	}

/**
 * Action `generate`. Used to put task generation exported files in queue.
 *  User role - administrator.
 *
 * @param string $view View of export:
 *  - `excel` - MS Excel;
 *  - `pdf` - PDF.
 * @param string $type Type of export:
 *  - `alph` - by alphabet;
 *  - `depart` - by department.
 * @param bool $forceUpdate Flag of forced update files
 * @return void
 */
	public function admin_generate($view = null, $type = null, $forceUpdate = false) {
		$this->_generate($view, $type, $forceUpdate);
	}
}
