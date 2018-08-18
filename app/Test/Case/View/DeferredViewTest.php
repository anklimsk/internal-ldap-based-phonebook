<?php
App::uses('AppControllerTestCase', 'Test');
App::uses('DeferredController', 'Controller');

/**
 * DeferredViewTest Test Case
 */
class DeferredViewTest extends AppControllerTestCase
{

    /**
     * Target Controller name
     *
     * @var string
     */
    public $targetController = 'Deferred';

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'core.cake_session',
        'app.deferred',
        'app.log',
        'plugin.cake_ldap.department',
        'plugin.cake_ldap.employee',
        'plugin.cake_ldap.employee_ldap',
        'plugin.cake_ldap.othermobile',
        'plugin.cake_ldap.othertelephone',
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
        $expected = 4;
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
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
        $expected = 2;
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
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
                'controller' => 'deferred',
                'action' => 'edit',
                '2',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $view = $this->testAction($url, $opt);
            $numForm = $this->getNumberItemsByCssSelector($view, 'div#content div.container form[action$="/deferred/edit/2"]');
            $this->assertData($expected, $numForm);
        }
    }
}
