<?php
/**
 * This file is the controller file of the application. Used for
 *  management the deferred saves.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Controller
 */

App::uses('AppController', 'Controller');
App::uses('Hash', 'Utility');

/**
 * The controller is used for management deferred saves.
 *
 * This controller allows to perform the following operations:
 *  - to obtain and edit deferred saves;
 *  - to delete, approve or reject deferred saves.
 *
 * @package app.Controller
 */
class DeferredController extends AppController {

/**
 * The name of this controller. Controller names are plural, named after the model they manipulate.
 *
 * @var string
 * @link http://book.cakephp.org/2.0/en/controllers.html#controller-attributes
 */
	public $name = 'Deferred';

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
	];

/**
 * An array containing the class names of models this controller uses.
 *
 * @var mixed
 * @link http://book.cakephp.org/2.0/en/controllers.html#components-helpers-and-uses
 */
	public $uses = [
		'Deferred'
	];

/**
 * An array containing the names of helpers this controller uses. The array elements should
 * not contain the "Helper" part of the class name.
 *
 * @var mixed
 * @link http://book.cakephp.org/2.0/en/controllers.html#components-helpers-and-uses
 */
	public $helper = [
		'Time',
		'Deferred'
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
			'Deferred.id',
			'Deferred.employee_id',
			'Deferred.data',
			'Deferred.created',
			'Deferred.modified',
		],
		'order' => [
			'Deferred.modified' => 'asc'
		],
		'contain' => [
			'Employee'
		]
	];

/**
 * Base of action `index`. Used to view a complete list of deferred saves.
 *
 * @return void
 */
	protected function _index() {
		$this->view = 'index';
		$groupActions = [
			GROUP_ACTION_DEFERRED_SAVE_DELETE => __('Delete data group'),
			GROUP_ACTION_DEFERRED_SAVE_APPROVE => __('Approve data group'),
			GROUP_ACTION_DEFERRED_SAVE_REJECT => __('Reject data group'),
		];
		$conditions = $this->Filter->getFilterConditions();
		if ($this->request->is('post')) {
			$groupAction = $this->Filter->getGroupAction(array_keys($groupActions));
			$resultGroupProcess = $this->Deferred->processGroupAction($groupAction, $conditions);
			if ($resultGroupProcess !== null) {
				if ($resultGroupProcess) {
					$conditions = null;
					if (in_array($groupAction, [GROUP_ACTION_DEFERRED_SAVE_APPROVE, GROUP_ACTION_DEFERRED_SAVE_REJECT])) {
						$this->Flash->success(__('Processing of selected tasks put in queue...'));
						$this->ViewExtension->setProgressSseTask('DeferredSave');
					} else {
						$this->Flash->success(__('Selected tasks has been processed.'));
					}
				} else {
					$this->Flash->error(__('Selected tasks could not be processed. Please, try again.'));
				}
			}
		}
		$this->Paginator->settings = $this->paginate;
		if (empty($conditions)) {
			$conditions = [];
		}
		$conditions['Deferred.internal'] = false;
		$deferredSaves = $this->Paginator->paginate('Deferred', $conditions);
		if (empty($deferredSaves)) {
			$this->Flash->information(__('Deferred saves not found'));
		}

		$fieldsLabel = $this->Deferred->Employee->getListFieldsLabel([], false);
		$fieldsConfig = $this->Deferred->Employee->getFieldsConfig();
		$pageHeader = __('Index of deferred saves');
		$breadCrumbs = $this->Deferred->getBreadcrumbInfo();
		$breadCrumbs[] = __('Index');
		$this->ViewExtension->setRedirectUrl(true, 'deferredSave');

		$this->set(compact('deferredSaves', 'groupActions', 'fieldsLabel', 'fieldsConfig', 'pageHeader', 'breadCrumbs'));
	}

/**
 * Action `index`. Used to view a complete list of deferred saves.
 *  User role - human resources.
 *
 * @return void
 */
	public function hr_index() {
		$this->_index();
	}

/**
 * Action `index`. Used to view a complete list of deferred saves.
 *  User role - administrator.
 *
 * @return void
 */
	public function admin_index() {
		$this->_index();
	}

/**
 * Base of action `view`. Used to view information about deferred save.
 *
 * @param int|string $id ID of record for viewing.
 * @return void
 */
	protected function _view($id = null) {
		$this->view = 'view';
		if (!$this->Deferred->exists($id)) {
			return $this->ViewExtension->setExceptionMessage(new NotFoundException(__('Invalid ID for deferred save')));
		}

		$deferredSave = $this->Deferred->get($id, false);
		$fieldsLabel = $this->Deferred->Employee->getListFieldsLabel([], false);
		$fieldsConfig = $this->Deferred->Employee->getFieldsConfig();
		$pageHeader = __('Information of deferred save');
		$headerMenuActions = [
			[
				'fas fa-pencil-alt',
				__('Edit deferred save'),
				['controller' => 'deferred', 'action' => 'edit', $deferredSave['Deferred']['id']],
				['title' => __('Editing information of this deferred save')]
			],
			[
				'far fa-trash-alt',
				__('Delete deferred save'),
				['controller' => 'deferred', 'action' => 'delete', $deferredSave['Deferred']['id']],
				[
					'title' => __('Delete deferred save'), 'action-type' => 'confirm-post',
					'data-confirm-msg' => __('Are you sure you wish to delete this deferred save?')
				]
			],
			[
				'fas fa-check',
				__('Approve deferred save data'),
				['controller' => 'deferred', 'action' => 'approve', $deferredSave['Deferred']['id']],
				[
					'title' => __('Approve deferred save data'), 'action-type' => 'confirm-post',
					'data-confirm-msg' => __('Are you sure you wish to approve this deferred save?'),
				]
			],
			[
				'fas fa-times',
				__('Reject deferred save data'),
				['controller' => 'deferred', 'action' => 'reject', $deferredSave['Deferred']['id']],
				[
					'title' => __('Reject deferred save data'), 'action-type' => 'confirm-post',
					'data-confirm-msg' => __('Are you sure you wish to reject this deferred save?'),
				]
			],
		];
		$breadCrumbs = $this->Deferred->getBreadcrumbInfo($id);
		$breadCrumbs[] = __('Viewing');
		$this->ViewExtension->setRedirectUrl(true, 'deferredSave');

		$this->set(compact('deferredSave', 'fieldsLabel', 'fieldsConfig', 'pageHeader', 'headerMenuActions', 'breadCrumbs'));
	}

/**
 * Action `view`. Used to view information about deferred save.
 * User role - human resources.
 *
 * @param int|string $id ID of record for viewing
 * @return void
 */
	public function hr_view($id = null) {
		$this->_view($id);
	}

/**
 * Action `view`. Used to view information about deferred save.
 * User role - administrator.
 *
 * @param int|string $id ID of record for viewing
 * @return void
 */
	public function admin_view($id = null) {
		$this->_view($id);
	}

/**
 * Base of action `edit`. Used to edit information of deferred save.
 *
 * POST Data:
 *  - EmployeeEdit: array data of employee
 *  - EmployeePhoto: array data for changing photo employee
 *
 * @param int|string $id ID of record for editing
 * @throws InternalErrorException if data of deferred save is not array
 * @return void
 */
	protected function _edit($id = null) {
		$this->view = 'edit';
		$userRole = $this->UserInfo->getUserField('role');
		$useLdap = $this->Setting->getConfig('UseLdapOnEdit');
		$deferredSave = $this->Deferred->get($id, true, $userRole, 'id', $useLdap);
		if (empty($deferredSave)) {
			return $this->ViewExtension->setExceptionMessage(new NotFoundException(__('Invalid ID for deferred save')));
		}
		if (!is_array($deferredSave['Deferred']['data'])) {
			throw new InternalErrorException(__('Data of deferred save is broken'));
		}

		$this->loadModel('EmployeeEdit');
		$forceDeferred = true;
		$employeePhoto = null;
		$employeeManager = null;
		$dn = null;
		$employeeInfo = $deferredSave['Deferred']['data']['changed'];
		$guid = $deferredSave['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID];
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
			$result = $this->EmployeeEdit->saveInformation($this->request->data, $userRole, true, true);
			if ($result !== false) {
				$this->Flash->success(__('The deferred save has been saved.'));

				return $this->ViewExtension->redirectByUrl(['controller' => 'deferred', 'action' => 'view', $id], 'deferredSave');
			} else {
				$this->Flash->error(__('The deferred save not be saved. Please, try again.'));
			}
		} else {
			$this->ViewExtension->setRedirectUrl(null, 'deferredSave');
			$this->request->data = $employeeInfo;
			$this->request->data('EmployeePhoto.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID, $guid);
			$this->request->data('EmployeePhoto.force_deferred', $forceDeferred);
			$this->EmployeeEdit->createValidationRules($userRole);
		}
		$managers = [];
		if (!empty($employeeManager)) {
			$fullListManagers = $this->EmployeeEdit->getListManagers();
			if (isset($fullListManagers[$employeeManager])) {
				$managers[$employeeManager] = $fullListManagers[$employeeManager];
			}
		}

		$departments = $this->Deferred->Employee->Department->getListDepartmentsWithExtension();
		$fieldsLabel = $this->EmployeeEdit->getListFieldsLabel(false, $userRole);
		$fieldsLabelAlt = $this->EmployeeEdit->getListFieldsLabel(true, $userRole);
		$fieldsInputMask = $this->EmployeeEdit->getListFieldsInputMask();
		$fieldsInputTooltip = $this->EmployeeEdit->getListFieldsInputTooltip();
		$readOnlyFields = $this->EmployeeEdit->getListReadOnlyFields();
		$maxfilesize = $this->EmployeeEdit->getLimitPhotoSize();
		$acceptfiletypes = $this->EmployeeEdit->getAcceptFileTypes();
		$maxLinesMultipleValue = $this->EmployeeEdit->getLimitLinesMultipleValue();
		$changedFields = $deferredSave['ChangedFields'];
		$pageHeader = __('Editing deferred save');
		$breadCrumbs = $this->Deferred->getBreadcrumbInfo($id);
		$breadCrumbs[] = __('Editing');

		$this->set(compact(
			'id',
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
			'changedFields',
			'forceDeferred',
			'pageHeader',
			'breadCrumbs'
		));
	}

/**
 * Action `edit`. Used to edit information of deferred save.
 *  User role - human resources.
 *
 * @param int|string $id ID of record for editing
 * @return void
 */
	public function hr_edit($id = null) {
		$this->_edit($id);
	}

/**
 * Action `edit`. Used to edit information of deferred save.
 *  User role - administrator.
 *
 * @param int|string $id ID of record for editing
 * @return void
 */
	public function admin_edit($id = null) {
		$this->_edit($id);
	}

/**
 * Base of action `delete`. Used to delete deferred save.
 *
 * @param int|string $id ID of record for deleting
 * @throws MethodNotAllowedException if request is not `POST` or `DELETE`
 * @return void
 */
	protected function _delete($id = null) {
		$this->Deferred->id = $id;
		if (!$this->Deferred->exists()) {
			return $this->ViewExtension->setExceptionMessage(new NotFoundException(__('Invalid ID for deferred save')));
		}

		$this->request->allowMethod('post', 'delete');
		$this->ViewExtension->setRedirectUrl(null, 'deferredSave');
		if ($this->Deferred->delete()) {
			$this->Flash->success(__('The deferred save has been deleted.'));
		} else {
			$this->Flash->error(__('The deferred save could not be deleted. Please, try again.'));
		}

		return $this->ViewExtension->redirectByUrl(null, 'deferredSave');
	}

/**
 * Action `delete`. Used to delete deferred save.
 *  User role - human resources.
 *
 * @param int|string $id ID of record for deleting
 * @return void
 */
	public function hr_delete($id = null) {
		$this->_delete($id);
	}

/**
 * Action `delete`. Used to delete deferred save.
 *  User role - administrator.
 *
 * @param int|string $id ID of record for deleting
 * @return void
 */
	public function admin_delete($id = null) {
		$this->_delete($id);
	}

/**
 * Base of action `approve` and `reject`. Used to approve or reject
 *  deferred saves.
 *
 * @param int|string $id ID of record for process
 * @param bool $approve If true, approve data of deferred save, reject otherwise.
 * @throws MethodNotAllowedException if request is not `POST`
 * @return void
 */
	protected function _process($id = null, $approve = false) {
		if (!$this->Deferred->exists($id)) {
			return $this->ViewExtension->setExceptionMessage(new NotFoundException(__('Invalid ID for deferred save')));
		}

		$this->request->allowMethod('post');
		$this->ViewExtension->setRedirectUrl(null, 'deferredSave');
		$conditions = [
			'Deferred.id' => $id
		];
		$action = __('rejected');
		if ($approve) {
			$action = __('approved');
		}
		if ($this->Deferred->processDeferredSave($conditions, $approve)) {
			$this->Flash->success(__('The deferred save has been %s.', $action));
		} else {
			$this->Flash->error(__('The deferred save could not be %s. Please, try again.', $action));
		}

		return $this->ViewExtension->redirectByUrl(['controller' => 'deferred', 'action' => 'index'], 'deferredSave');
	}

/**
 * Action `approve`. Used to approve deferred save.
 *  User role - human resources.
 *
 * @param int|string $id ID of record for approving
 * @return void
 */
	public function hr_approve($id = null) {
		$this->_process($id, true);
	}

/**
 * Action `approve`. Used to approve deferred save.
 *  User role - administrator.
 *
 * @param int|string $id ID of record for approving
 * @return void
 */
	public function admin_approve($id = null) {
		$this->_process($id, true);
	}

/**
 * Action `reject`. Used to reject deferred save.
 *  User role - human resources.
 *
 * @param int|string $id ID of record for rejecting
 * @return void
 */
	public function hr_reject($id = null) {
		$this->_process($id, false);
	}

/**
 * Action `reject`. Used to reject deferred save.
 *  User role - admin.
 *
 * @param int|string $id ID of record for rejecting
 * @return void
 */
	public function admin_reject($id = null) {
		$this->_process($id, false);
	}
}
