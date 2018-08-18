<?php
App::uses('AppControllerTestCase', 'Test');
App::uses('DepartmentsController', 'Controller');

/**
 * DepartmentsView Test Case
 */
class DepartmentsView extends AppControllerTestCase
{

    /**
     * Target Controller name
     *
     * @var string
     */
    public $targetController = 'Departments';

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'core.cake_session',
        'app.deferred',
        'app.department_extension',
        'plugin.cake_ldap.department',
        'plugin.cake_ldap.employee',
        'plugin.cake_ldap.employee_ldap',
        'plugin.queue.queued_task',
    ];

    /**
     * testIndex method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testIndexForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'contents',
        ];
        $expected = 7;
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->generateMockedController();
            $url = [
                'controller' => 'departments',
                'action' => 'index',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $view = $this->testAction($url, $opt);
            $numTableRows = $this->getNumberItemsByCssSelector($view, 'div#content div.container table > tbody > tr');
            $this->assertData($expected, $numTableRows);
        }
    }

    /**
     * testViewSuccessForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testViewSuccessForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'contents',
        ];
        $expected = 1;
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->generateMockedController();
            $url = [
                'controller' => 'departments',
                'action' => 'view',
                '2',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $view = $this->testAction($url, $opt);
            $numDl = $this->getNumberItemsByCssSelector($view, 'div#content div.container dl.dl-horizontal');
            $this->assertData($expected, $numDl);
        }
    }

    /**
     * testAddGetSuccessForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testAddGetSuccessForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'contents',
        ];
        $expected = 1;
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->generateMockedController();
            $url = [
                'controller' => 'departments',
                'action' => 'add',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $view = $this->testAction($url, $opt);
            $numForm = $this->getNumberItemsByCssSelector($view, 'div#content div.container form[action$="/departments/add"]');
            $this->assertData($expected, $numForm);
        }
    }

    /**
     * testEditGetSuccessForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testEditGetSuccessForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'contents',
        ];
        $expected = 1;
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->generateMockedController();
            $url = [
                'controller' => 'departments',
                'action' => 'edit',
                '2',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $view = $this->testAction($url, $opt);
            $numForm = $this->getNumberItemsByCssSelector($view, 'div#content div.container form[action$="/departments/edit/2"]');
            $this->assertData($expected, $numForm);
        }
    }

    /**
     * testCheckUnsuccessForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testCheckUnsuccessForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'contents',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $this->Controller->Department->DepartmentExtension->id = 2;
        $result = (bool)$this->Controller->Department->DepartmentExtension->saveField('rght', null);
        $this->assertTrue($result);

        $url = '/admin/departments/check';
        $view = $this->testAction($url, $opt);
        $numTableRows = $this->getNumberItemsByCssSelector($view, 'div#content div.container table > tbody > tr');
        $expected = 2;
        $this->assertData($expected, $numTableRows);
    }

    /**
     * testCheckSuccessForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testCheckSuccessForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'contents',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/admin/departments/check';
        $view = $this->testAction($url, $opt);
        $numAlert = $this->getNumberItemsByCssSelector($view, 'div#content div.container div.alert-success');
        $expected = 1;
        $this->assertData($expected, $numAlert);
    }
}
