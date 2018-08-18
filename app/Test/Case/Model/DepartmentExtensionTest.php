<?php
App::uses('AppCakeTestCase', 'Test');
App::uses('DepartmentExtension', 'Model');

/**
 * DepartmentExtension Test Case
 */
class DepartmentExtensionTest extends AppCakeTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.department_extension',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_targetObject = ClassRegistry::init('DepartmentExtension');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->_targetObject);

        parent::tearDown();
    }

    /**
     * testCreateDepartmentExtension method
     *
     * @return void
     */
    public function testCreateDepartmentExtension()
    {
        $params = [
            [
                null, // $data
            ], // Params for step 1
            [
                [
                    'name' => '',
                    'id' => '1'
                ], // $data
            ], // Params for step 2
            [
                [
                    'name' => 'Some department',
                    'id' => '3'
                ], // $data
            ], // Params for step 3
        ];
        $expected = [
            false, // Result of step 1
            false, // Result of step 2
            true, // Result of step 3
        ];
        $this->runClassMethodGroup('createDepartmentExtension', $params, $expected);
    }

    /**
     * testRecoverDepartmentListWoVerify method
     *
     * @return void
     */
    public function testRecoverDepartmentListWoVerify()
    {
        $this->_targetObject->id = 4;
        $result = $this->_targetObject->saveField('rght', null);
        $expected = [
            'DepartmentExtension' => [
                'id' => 4,
                'rght' => null
            ]
        ];
        $this->assertData($expected, $result);

        $result = $this->_targetObject->recoverDepartmentList(false);
        $this->assertTrue($result);
    }

    /**
     * testRecoverDepartmentListWithVerify method
     *
     * @return void
     */
    public function testRecoverDepartmentListWithVerify()
    {
        $this->_targetObject->id = 4;
        $result = $this->_targetObject->saveField('rght', null);
        $expected = [
            'DepartmentExtension' => [
                'id' => 4,
                'rght' => null
            ]
        ];
        $this->assertData($expected, $result);

        $result = $this->_targetObject->recoverDepartmentList(true);
        $this->assertTrue($result);
    }

    /**
     * testReorderDepartmentListWoVerify method
     *
     * @return void
     */
    public function testReorderDepartmentListWoVerify()
    {
        $this->_targetObject->id = 2;
        $result = $this->_targetObject->saveField('rght', null);
        $expected = [
            'DepartmentExtension' => [
                'id' => 2,
                'rght' => null
            ]
        ];
        $this->assertData($expected, $result);

        $result = $this->_targetObject->reorderDepartmentList(false);
        $this->assertTrue($result);
    }

    /**
     * testReorderDepartmentListWithVerify method
     *
     * @return void
     */
    public function testReorderDepartmentListWithVerify()
    {
        $this->_targetObject->id = 2;
        $result = $this->_targetObject->saveField('rght', null);
        $expected = [
            'DepartmentExtension' => [
                'id' => 2,
                'rght' => null
            ]
        ];
        $this->assertData($expected, $result);

        $result = $this->_targetObject->reorderDepartmentList(true);
        $this->assertFalse($result);
    }
}
