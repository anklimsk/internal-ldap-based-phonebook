<?php
/**
 * This file is the controller file of the application. Used for
 *  management the logs.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Controller
 */

App::uses('AppController', 'Controller');
App::uses('CakeTime', 'Utility');

/**
 * The controller is used for management logs.
 *
 * This controller allows to perform the following operations:
 *  - to obtain and delete logs.
 *
 * @package app.Controller
 */
class LogsController extends AppController
{

    /**
     * The name of this controller. Controller names are plural, named after the model they manipulate.
     *
     * @var string
     * @link http://book.cakephp.org/2.0/en/controllers.html#controller-attributes
     */
    public $name = 'Logs';

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
            'Log.id',
            'Log.user_id',
            'Log.employee_id',
            'Log.data',
            'Log.created',
        ],
        'order' => [
            'Log.created' => 'desc'
        ],
        'contain' => [
            'Employee',
            'User',
        ]
    ];

    /**
     * Base of action `index`. Used to view a complete list of logs.
     *
     * @return void
     */
    protected function _index()
    {
        $this->view = 'index';
        $groupActions = [
            GROUP_ACTION_LOG_DELETE => __('Delete data group'),
        ];
        $conditions = $this->Filter->getFilterConditions();
        if ($this->request->is('post')) {
            $groupAction = $this->Filter->getGroupAction(array_keys($groupActions));
            $resultGroupProcess = $this->Log->processGroupAction($groupAction, $conditions);
            if ($resultGroupProcess !== null) {
                if ($resultGroupProcess) {
                    $conditions = null;
                    $this->Flash->success(__('Selected tasks has been processed.'));
                } else {
                    $this->Flash->error(__('Selected tasks could not be processed. Please, try again.'));
                }
            }
        }
        $this->Paginator->settings = $this->paginate;
        $logs = $this->Paginator->paginate('Log', $conditions);
        if (empty($logs)) {
            $this->Flash->information(__('Logs not found'));
        }

        $fieldsLabel = $this->Log->Employee->getListFieldsLabel([], false);
        $fieldsConfig = $this->Log->Employee->getFieldsConfig();
        $pageHeader = __('Index of logs');
        $headerMenuActions = [];
        if (!empty($logs)) {
            $headerMenuActions[] = [
                'fas fa-trash-alt',
                __('Clear logs'),
                ['controller' => 'logs', 'action' => 'clear'],
                [
                    'title' => __('Clear logs'),
                    'action-type' => 'confirm-post',
                    'data-confirm-msg' => __('Are you sure you wish to clear logs?'),
                ]
            ];
        }
        $breadCrumbs = $this->Log->getBreadcrumbInfo();
        $breadCrumbs[] = __('Index');
        $this->ViewExtension->setRedirectUrl(true, 'log');

        $this->set(compact('logs', 'groupActions', 'fieldsLabel', 'fieldsConfig', 'pageHeader', 'headerMenuActions', 'breadCrumbs'));
    }

    /**
     * Action `index`. Used to view a complete list of logs.
     *  User role - administrator.
     *
     * @return void
     */
    public function admin_index()
    {
        $this->_index();
    }

    /**
     * Base of action `view`. Used to view information about log record.
     *
     * @param int|string $id ID of record for viewing.
     * @return void
     */
    protected function _view($id = null)
    {
        $this->view = 'view';
        if (!$this->Log->exists($id)) {
            return $this->ViewExtension->setExceptionMessage(new NotFoundException(__('Invalid ID for record of log')));
        }

        $log = $this->Log->get($id, true);
        $fieldsLabel = $this->Log->Employee->getListFieldsLabel([], false);
        $fieldsConfig = $this->Log->Employee->getFieldsConfig();
        $pageHeader = __('Information of log record');
        $headerMenuActions = [
            [
                'fas fa-undo-alt',
                __('Restore data from log'),
                ['controller' => 'logs', 'action' => 'restore', $log['Log']['id']],
                [
                    'title' => __('Restore data from log'), 'action-type' => 'confirm-post',
                    'data-confirm-msg' => __('Are you sure you wish to restore this data from log?'),
                ]
            ],
            [
                'far fa-trash-alt',
                __('Delete record of log'),
                ['controller' => 'logs', 'action' => 'delete', $log['Log']['id']],
                [
                    'title' => __('Delete record of log'), 'action-type' => 'confirm-post',
                    'data-confirm-msg' => __('Are you sure you wish to delete this record of log?'),
                ]
            ],
        ];
        $breadCrumbs = $this->Log->getBreadcrumbInfo($id);
        $breadCrumbs[] = __('Viewing');
        $this->ViewExtension->setRedirectUrl(true, 'log');

        $this->set(compact('log', 'fieldsLabel', 'fieldsConfig', 'pageHeader', 'headerMenuActions', 'breadCrumbs'));
    }

    /**
     * Action `view`. Used to view information about log record.
     * User role - administrator.
     *
     * @param int|string $id ID of record for viewing
     * @return void
     */
    public function admin_view($id = null)
    {
        $this->_view($id);
    }

    /**
     * Base of action `delete`. Used to delete log record.
     *
     * @param int|string $id ID of record for deleting
     * @throws MethodNotAllowedException if request is not `POST` or `DELETE`
     * @return void
     */
    protected function _delete($id = null)
    {
        $this->Log->id = $id;
        if (!$this->Log->exists()) {
            return $this->ViewExtension->setExceptionMessage(new NotFoundException(__('Invalid ID for record of log')));
        }

        $this->request->allowMethod('post', 'delete');
        $this->ViewExtension->setRedirectUrl(null, 'log');
        if ($this->Log->delete()) {
            $this->Flash->success(__('The log record has been deleted.'));
        } else {
            $this->Flash->error(__('The log record could not be deleted. Please, try again.'));
        }

        return $this->ViewExtension->redirectByUrl(null, 'log');
    }

    /**
     * Action `delete`. Used to delete log record.
     *  User role - administrator.
     *
     * @param int|string $id ID of record for deleting
     * @return void
     */
    public function admin_delete($id = null)
    {
        $this->_delete($id);
    }

    /**
     * Base of action `restore`. Used to restore data from log.
     *
     * @param int|string $id ID of record for restoring
     * @throws MethodNotAllowedException if request is not `POST`
     * @return void
     */
    protected function _restore($id = null)
    {
        if (!$this->Log->exists($id)) {
            return $this->ViewExtension->setExceptionMessage(new NotFoundException(__('Invalid ID for record of log')));
        }

        $this->request->allowMethod('post');
        $userRole = $this->UserInfo->getUserField('role');
        $this->ViewExtension->setRedirectUrl(null, 'log');
        if ($this->Log->restoreData($id, $userRole)) {
            $this->Flash->success(__(
                'Deferred saving with an restored employee information was created.<br />Information on LDAP server will be updated by queue.<br />Information in phonebook will be updated %s after processing.',
                CakeTime::timeAgoInWords(strtotime('+' . DEFERRED_SAVE_SYNC_DELAY . ' second'), ['accuracy' => ['second' => 'minute']])
            ));
            $this->ViewExtension->setProgressSseTask('DeferredSave');
        } else {
            $this->Flash->error(__('The data could not be restored from log record. Please, try again.'));
        }

        return $this->ViewExtension->redirectByUrl(null, 'log');
    }

    /**
     * Action `delete`. Used to restore data from log.
     *  User role - administrator.
     *
     * @param int|string $id ID of record for restoring
     * @return void
     */
    public function admin_restore($id = null)
    {
        $this->_restore($id);
    }

    /**
     * Base of action `clear`. Used to clear logs.
     *
     * @throws MethodNotAllowedException if request is not `POST` or `DELETE`
     * @return void
     */
    protected function _clear()
    {
        $this->request->allowMethod('post', 'delete');
        if ($this->Log->clearLogs()) {
            $this->Flash->success(__('The logs has been cleared.'));
        } else {
            $this->Flash->error(__('The logs could not be cleared. Please, try again.'));
        }

        return $this->redirect(['controller' => 'logs', 'action' => 'index']);
    }

    /**
     * Action `delete`. Used to clear logs.
     *  User role - administrator.
     *
     * @return void
     */
    public function admin_clear()
    {
        $this->_clear();
    }
}
