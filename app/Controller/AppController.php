<?php
/**
 * This file is the application level Controller
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Controller
 */

App::uses('Controller', 'Controller');

/**
 * Application level Controller
 *
 * @package app.Controller
 */
class AppController extends Controller
{

    /**
     * An array containing the class names of models this controller uses.
     *
     * @var mixed
     * @link http://book.cakephp.org/2.0/en/controllers.html#components-helpers-and-uses
     */
    public $uses = [
        'Setting',
    ];

    /**
     * Array containing the names of components this controller uses. Component names
     * should not contain the "Component" portion of the class name.
     *
     * @var array
     * @link http://book.cakephp.org/2.0/en/controllers/components.html
     */
    public $components = [
        'Auth',
        'Session',
        'Security',
        'Flash',
        'RequestHandler',
        'CakeTheme.ViewExtension',
        'CakeLdap.UserInfo',
        'CakeSearchInfo.SearchFilter',
        'CakeInstaller.Installer' => [
            'ConfigKey' => PROJECT_CONFIG_NAME
        ],
        'CakeSettingsApp.Settings',
        'CakeTheme.Theme'
    ];

    /**
     * An array containing the names of helpers this controller uses. The array elements should
     * not contain the "Helper" part of the class name.
     *
     * @var mixed
     * @link http://book.cakephp.org/2.0/en/controllers.html#components-helpers-and-uses
     */
    public $helpers = [
        'CakeTheme.ActionScript',
        'CakeTheme.ViewExtension',
        'CakeTheme.Filter',
        'CakeLdap.UserInfo',
        'CakeSearchInfo.Search',
        'AssetCompress.AssetCompress',
        'Session',
        'Html',
        'Form' => [
            'className' => 'CakeTheme.ExtBs3Form'
        ],
        'Number',
        'Text'
    ];

    /**
     * Check if the provided user is authorized.
     *
     * Uses to check whether or not a user is authorized.
     * @param array $user The user to check the authorization of.
     * @return bool True if $user is authorized, otherwise false
     */
    public function isAuthorized($user)
    {
        $plugin = $this->request->param('plugin');
        $controller = $this->request->param('controller');
        switch ($plugin) {
            case 'cake_ldap':
                if ($controller === 'users') {
                    return true;
                }
                // no break
            case 'cake_search_info':
            case 'cake_settings_app':
                if ($this->UserInfo->checkUserRole(USER_ROLE_ADMIN, true, $user)) {
                    return true;
                }
                break;
            default:
                if ($this->UserInfo->isAuthorized($user) || $this->UserInfo->checkUserRole(USER_ROLE_ADMIN, true, $user)) {
                    return true;
                }
        }

        return false;
    }

    /**
     * Called before the controller action. You can use this method to configure and customize components
     * or perform logic that needs to happen before each controller action.
     *
     * Actions:
     *  - Configure components;
     *  - Configure CakeSearchInfo plugin;
     *  - Set global variables for View.
     *
     * @return void
     * @link http://book.cakephp.org/2.0/en/controllers.html#request-life-cycle-callbacks
     */
    public function beforeFilter()
    {
        $authGroups = [
            USER_ROLE_USER => 'default'
        ];
        $authGroupsList = $this->Setting->getAuthGroupsList();
        $authPrefixes = $this->Setting->getAuthPrefixesList();
        foreach ($authGroupsList as $userRole => $fieldName) {
            $userGroup = Configure::read(PROJECT_CONFIG_NAME . '.' . $fieldName);
            if (!empty($userGroup)) {
                $authGroups[$userRole] = $userGroup;
            }
        }

        $isExternalAuth = false;
        if ((bool)Configure::read(PROJECT_CONFIG_NAME . '.ExternalAuth') == true) {
            $isExternalAuth = $this->UserInfo->isExternalAuth();
        }

        $this->Auth->authenticate = [
            'CakeLdap.Ldap' => [
                'externalAuth' => $isExternalAuth,
                'groups' => $authGroups,
                'prefixes' => $authPrefixes,
                'includeFields' => CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID,
                'bindFields' => [
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => 'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID
                ]
            ]
        ];
        $this->Auth->authorize = ['Controller'];
        $this->Auth->flash = [
            'element' => 'warning',
            'key' => 'auth',
            'params' => []
        ];
        $this->Auth->loginAction = '/users/login';

        $plugin = (string)$this->request->param('plugin');
        $controller = (string)$this->request->param('controller');
        $action = (string)$this->request->param('action');
        if (($plugin === 'cake_search_info') ||
            (empty($plugin) && ($controller === 'employees') &&
            (mb_strpos($action, 'search') !== false))) {
            $this->loadModel('Employee');
            $userRole = $this->UserInfo->getUserField('role');
            $targetModels = $this->Employee->getSearchTargetModels($userRole);
            $includeFields = $this->Employee->getSearchIncludeFields($userRole);
            Configure::write('CakeSearchInfo.TargetModels', $targetModels);
            Configure::write('CakeSearchInfo.IncludeFields', $includeFields);
        } elseif (($plugin === 'cake_theme') && ($controller === 'tours')) {
            $this->loadModel('Tour');
            $userInfo = $this->Auth->user();
            $tourSteps = $this->Tour->getListSteps($userInfo);
            Configure::write('CakeTheme.TourApp.Steps', $tourSteps);
        }
        if (!$this->ViewExtension->isHtml()) {
            return parent::beforeFilter();
        }

        $emailContact = $this->Setting->getConfig('EmailContact');
        $emailSubject = $this->Setting->getConfig('EmailSubject');
        $showSearchForm = true;

        $this->loadModel('Deferred');
        $countDeferredSaves = $this->Deferred->getNumberOf();
        $useNavbarContainerFluid = false;

        $search_urlActionSearch = ['controller' => 'employees', 'action' => 'search'];
        $userPrefix = $this->UserInfo->getUserField('prefix');
        if (!empty($userPrefix)) {
            $search_urlActionSearch[$userPrefix] = true;
        }
        $showSearchForm = false;
        $projectName = __d('project', PROJECT_NAME);

        $this->set(compact(
            'isExternalAuth',
            'emailContact',
            'emailSubject',
            'showSearchForm',
            'countDeferredSaves',
            'useNavbarContainerFluid',
            'search_urlActionSearch',
            'showSearchForm',
            'projectName'
        ));
        parent::beforeFilter();
    }

    /**
     * Called after the controller action is run, but before the view is rendered. You can use this method
     * to perform logic or set view variables that are required on every request.
     *
     * Actions:
     *  - Set global variables $pageTitlePrefix and $pageTitlePostfix for View.
     *
     * @return void
     * @link http://book.cakephp.org/2.0/en/controllers.html#request-life-cycle-callbacks
     */
    public function beforeRender()
    {
        if (!$this->ViewExtension->isHtml()) {
            return parent::beforeRender();
        }

        $pageTitlePrefix = __d('project', PROJECT_PAGE_TITLE) . '::';
        $pageTitlePostfix = '';
        $role = $this->Auth->user('role');
        $roleName = $this->Setting->getAuthRoleName($role);
        if (!empty($roleName)) {
            $pageTitlePostfix .= '::' . mb_ucfirst($roleName);
        }

        $this->set(compact('pageTitlePrefix', 'pageTitlePostfix'));
        parent::beforeRender();
    }
}
