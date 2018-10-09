<?php
App::uses('AppCakeTestCase', 'Test');
App::uses('DeferredSaveBehavior', 'Model/Behavior');
App::uses('CakeText', 'Utility');

/**
 * DeferredSaveBehavior Test Case
 */
class DeferredSaveBehaviorTest extends AppCakeTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'core.cake_session',
        'app.deferred',
        'plugin.cake_ldap.employee',
        'plugin.cake_ldap.employee_ldap',
        'plugin.cake_ldap.department',
        'plugin.cake_ldap.othertelephone',
        'plugin.cake_ldap.othermobile'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        $this->setDefaultUserInfo($this->userInfo);
        parent::setUp();
        $this->_targetObject = ClassRegistry::init('Deferred');
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
     * testGetObjectDataModel method
     *
     * @return void
     */
    public function testGetObjectDataModel()
    {
        $result = $this->_targetObject->getObjectDataModel();
        $expected = ClassRegistry::init('EmployeeEdit');
        $this->assertData($expected, $result);
    }

    /**
     * testPrepareDataForDisplayEmptyData method
     *
     * @return void
     */
    public function testPrepareDataForDisplayEmptyData()
    {
        $data = [];
        $result = $this->_targetObject->prepareDataForDisplay($data);
        $expected = [];
        $this->assertData($expected, $result);
    }

    /**
     * testPrepareDataForDisplayNonProcData method
     *
     * @return void
     */
    public function testPrepareDataForDisplayNonProcData()
    {
        $data = [
            CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.'
        ];
        $result = $this->_targetObject->prepareDataForDisplay($data);
        $expected = [
            CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.'
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testPrepareDataForDisplaySuccess method
     *
     * @return void
     */
    public function testPrepareDataForDisplaySuccess()
    {
        $data = [
            CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОС',
            CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
            CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
        ];
        $result = $this->_targetObject->prepareDataForDisplay($data);
        $expected = [
            CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОС',
            CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => [
                'id' => '2',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Егоров Т.Г.',
                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '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',
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetDeferredDataEmptyData method
     *
     * @return void
     */
    public function testGetDeferredDataEmptyData()
    {
        $result = $this->_targetObject->getDeferredData();
        $expected = false;
        $this->assertData($expected, $result);
    }

    /**
     * testGetDeferredDataInvalidData method
     *
     * @return void
     */
    public function testGetDeferredDataInvalidData()
    {
        $data = ['BAD_MODEL' => ['id' => null]];
        $result = $this->_targetObject->getDeferredData($data);
        $expected = false;
        $this->assertData($expected, $result);
    }

    /**
     * testGetDeferredDataValidDataUser method
     *
     * User role: user.
     * @return void
     */
    public function testGetDeferredDataValidDataUser()
    {
        $data = [
            'EmployeeEdit' => [
                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => 'dd518c55-35ce-4a5c-85c5-b5fb762220bf',
                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Суханова',
                CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Лариса',
                CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Борисовна',
                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Группа №3',
                CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'Отдел связи',
                CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '203',
                CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000001',
                CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '507',
                CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'l.suhanova@fabrikam.com',
                CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0003',
                CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1602',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'НовТестОрг',
                CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1998-01-01',
                CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501203',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    '+375171000003',
                    '+375171000005'
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                    '+375291000001'
                ],
            ]
        ];
        $result = $this->_targetObject->getDeferredData($data, null);
        $expected = [
            'employee_id' => '3',
            'changed' => [
                'EmployeeEdit' => [
                    CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'Отдел связи',
                    CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                        '+375171000003',
                        '+375171000005',
                    ],
                    CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com'
                ]
            ],
            'current' => [
                'EmployeeEdit' => [
                    CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОС',
                    CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                        '+375171000003',
                        '+375171000005',
                        '+375171000004',
                    ],
                ]
            ]
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetDeferredDataValidDataNotUser method
     *
     * User role: Secretary, Human resources, Administrator.
     * @return void
     */
    public function testGetDeferredDataValidDataNotUser()
    {
        $userRoles = [
            USER_ROLE_SECRETARY,
            USER_ROLE_HUMAN_RESOURCES,
            USER_ROLE_ADMIN,
        ];
        $data = [
            'EmployeeEdit' => [
                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => 'dd518c55-35ce-4a5c-85c5-b5fb762220bf',
                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Суханова',
                CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Лариса',
                CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Борисовна',
                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'Отдел связи',
                CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '203',
                CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000001',
                CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '507',
                CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'l.suhanova@fabrikam.com',
                CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0003',
                CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '8502',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'НовТестОрг',
                CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1998-01-01',
                CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501203',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    '+375171000003',
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                    '+375291000001'
                ],
            ]
        ];
        $expected = [
            'employee_id' => '3',
            'changed' => [
                'EmployeeEdit' => [
                    CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'Отдел связи',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '',
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0003',
                    CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '8502',
                    CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1998-01-01',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                        '+375171000003',
                    ],
                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                        '+375291000001',
                    ],
                    CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com'
                ]
            ],
            'current' => [
                'EmployeeEdit' => [
                    CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОС',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => null,
                    CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0216',
                    CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1602',
                    CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-08-16',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                        '+375171000003',
                        '+375171000004',
                        '+375171000005',
                    ],
                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => []
                ]
            ]
        ];
        foreach ($userRoles as $userRole) {
            $result = $this->_targetObject->getDeferredData($data, $userRole);
            $this->assertData($expected, $result, __d('test', 'User role: %d', $userRole));
        }
    }

    /**
     * testBeforeValidateInvalidSerializedData method
     *
     * @return void
     */
    public function testBeforeValidateInvalidSerializedData()
    {
        $data = [
            'id' => '2',
            'employee_id' => '3',
            'internal' => false,
            'data' => 'a:2:{',
        ];
        $this->_targetObject->set($data);
        $result = $this->_targetObject->validates();
        $this->assertFalse($result);
    }

    /**
     * testBeforeValidateValidSerializedData method
     *
     * @return void
     */
    public function testBeforeValidateValidSerializedData()
    {
        $data = [
            'id' => '2',
            'employee_id' => '3',
            'internal' => false,
            'data' => 'a:2:{s:7:"changed";a:1:{s:12:"EmployeeEdit";a:6:{s:10:"' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT . '";s:21:"Отдел связи";s:7:"' . CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER . '";s:86:"CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com";s:14:"' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO . '";s:4072:"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";s:14:"' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER . '";a:2:{i:0;s:13:"+375171000003";i:1;s:13:"+375171000005";}s:11:"' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER . '";a:1:{i:0;s:13:"+375291000001";}s:2:"dn";s:81:"CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com";}}s:7:"current";a:1:{s:12:"EmployeeEdit";a:5:{s:10:"' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT . '";s:4:"ОС";s:7:"' . CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER . '";N;s:14:"' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO . '";s:5312:"/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAPAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQABgQEBAUEBgUFBgkGBQYJCwgGBggLDAoKCwoKDBAMDAwMDAwQDA4PEA8ODBMTFBQTExwbGxscHx8fHx8fHx8fHwEHBwcNDA0YEBAYGhURFRofHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8f/8AAEQgAyADIAwERAAIRAQMRAf/EAHUAAAIDAQEBAQAAAAAAAAAAAAAFAwQGBwIBCAEAAwEBAQAAAAAAAAAAAAAAAAECAwQFEAACAQMEAgEDBAMBAAAAAAAAAQIDBAURIRIGMSJBUYEyYaETFEIjFTMRAQEBAQEBAQEAAAAAAAAAAAABAhEDEgQT/9oADAMBAAIRAxEAPwD9UgAAAAAAAAB5ckgCGrcRivIgW3eThBPcD4Q33YqcNfYSpCC87dTi37i6qQqq90gn+f7k9V8vEe6Rb/P9w6fyu23cINr3Do+Tqy7RCWnuPqbk+s85Tnp7D6i5OLe+hNLcaeLsKqYySp6jAAAAAAAAAAAAAAAAABvQAiqVVEAo3N/CCe4unwgyWehTT9hdVxis122MOWkxdVIwWY7ro5aT/cSpGRv+6Sbfv+4lSFNTt9Rv8xLkFPtlTX8xKkMbTts017iP5aHG9wkmvcOlctjiO368dZjlZ3Lb4jssZqPsXKzuWusMpGoluVKiw4o1lJFJWE9QIAAAAAAAAAAAAADYBBWqqKAyXIZSNNPck5GOzPZ401L3F1pMufZ7uOnLSf7k9VxzrOdulJy9wPjE5LsVSbfsMElbLVJP8gNEr6bfkXFSp6V3P6isXF2jezXyTVwztcpUi1uI+NDjM/ODXsCbG6wPaJJxTmVGeo6Z17sKmo+xcY6jf4vIKpFblsrDylPVDSlAAAAAAAAAAAAAjqT0QAlyd8qcXuKqkc87J2JU1L2ItaSOU9j7W9ZJTJaSOdZjsk5uXsM2UvsrOo3uMqU1biUn5GlGpNsAnpJgqLtKDE0lWoRJ4qVNCTQuK6t0LmUWtw4On2LysoSj7DkRXRur598oLkVGWnYOs5b+SMdyoxre2NfnBbjQYReqGT6AAAAAAAAAA/ABRvKvGDEcYbs2U/jhPcVXI4v2/PtOa5ENY5LnMzOc5ewKZa5u5Tb3GXVKblIZPPBsZJIUmAW6NJgcXaVPRC4qVMloHFdDloHB9BVdA4PpYt7pxktw4XWt69lXGpHcOIrs/TctyUPYbOuvYS65047jRWhpS1Q0pAAAAAAAAAA8zewAmy1bjTkI45P3PIuMZ7/UmtI4R2zJSlOe4mkc9va8pze4cHVNU3JjJLG1b+Bh7Vm/oMJ6dm/oPgW6Vm/oHAm/rtLwHD6jqLQODqtOeg+F1E6gcHXyNZpi4OneHvHGpHcQdf6TkXrDf6CTXc+sXXKnDcbOttbS1ihpWAAAAAAAAAA8VfDAM5nZ6U5CVHFu812lU+4lxwrstVurMXF9ZOUHKY+Eu2tm5abDBlSxra8Bw0yxj+gwmpYx6+Bhdp4p6eBhHXxzivAwV3NrJa7CBbVoy+gDitOlIXS4gkpJgF7HVWqiJDqfSrl84b/QQrv3UK+tOH2Gzro1lLWCGldXgCAAAAAAAAHir+IBms9FunIDjiveab0qfcS44d2Kk/5ZBxTP0rfWfgfAfY6y102DgaC2xqaWw+GtRxWvwM1mjiN/AGv08R6/iMKt3idnsBEF7i93sKqkKquJbfgi1cyq1sTJLwT9D5LLrHyj8D6m5Q21JxqoEcdG6bJqpD7AVd/6ZP0h9hs66dj3/rQ0mK8AQAAAAAAAA8zWwAjzFLlTkBuQ91sm4z2+oKjh3ZLJqrLYZs5Rt9KngZtJi7dbbAbT2dstFsPgM6VnF/Azi9QsI/QSouKyio+ANSvLSOj2GCK6sYtvYmqir/y4t+DLTbMR1sMnH8TK1fyQZPE8U9hyo1ln5WfCr4LlY6jZdSg1VgUyrvfTNeEPsNnXUMd/5oaTJeAIAAAAAAAAfJLYAX39LlBgHOO247lCe31BTiHasY1UnsM2M/q8avgZneNhpoM2jtGkkURrbyWwHKa2/FpEVpFqSXEXV8LrteR9HCqrS1YrTkfaVum/Bjqt8xYlZpx8GFraQiy1guL2CVOsslc2H+3wa5rn1Gj6vZyVWOxpGGo7j0+g1CH2KY10rHx0ghpMV4AgAAAAAAAAAEFeHKLAMn2Cx505bAbj/bMPrKb4jOOcXmNcKr2GqPdrScWMzWg2tCi4Y29R6oVqpDi0qeDLVbZhjHeJn9NplWuKLY5ofClK1evgd0cylpWunwZarbOVh0PUwtayFORteSewpRYz9TFuVXwbZrn3lp+t4ZqpF8TaOTcde61Y8IR2Ljn029pDSKGhbQAAAAAAAAAAB5mtUAKslbKcHsAc97Lhual6jNzPMYRxnJ8RrhK7Bwl4DrSRPStn9B9P5WqVBpk2qmTO0g00YarfOTehDVIytbzKWVumEp8R/wBRfQro49K2S+CNVcjzUp6IyrSKFxb8vgUOx5tsVzmtjXNc+42OAw6i4vidGXH6Og4mzUILY0jk0fUo6IaEgAAAAAAAAAAAMAgr0+SYBn8tjlOL2A2BzmFWsnxGuMdeYzhJ7Ca5VY2uj8C60iWNukKqi1Qp6My1GuTO3WyMrG8q1FLQJD69cEVwuvMoomqlVqqMq0iONHlIUO04xuPTktjXLn3WzxFgopbHRlw+lai1pKMUaRzVdihofQAAAAAAAAAAAAPklqAU7qgpRewBmMxYJqWwKjCZaxUZPYTbNIKlLjIGkrxogPqSm1qTY0zV2hIi5ayrcZC4vr1zDhdeZTIsVKgk9WZWNZVq0o8pIUha01WJs/GxtmOT001thbKKWxtI4902pw0RbGpRkAAAAAAAAAAAAAAA8VI6oATZKgnF7AcYfN228thNJWOvYcZMGkqhOejGp8hV3BcXaFUmxtlcjU2JsWHVJJ4dQinK+werM7GkpzjKWskEiN6bTE2+y2Nsxx+mmltqaUUaxzWrcUUh9AAAAAAAAAAAAAAAAPklsAULynrFgbIZu21UtgVGEytFxkxNJWeuJNNjXEEar1Brldt6jCtsr1Ob0IrR7cmRU145EUli33kiFdabD09WipGW62+LppRRtlx7p7SWiLY1KMgAAAAAAAAAAAAAAAADAK9eOqYBnctbpxYKjBZu10ctgXKx19DSTE0lUYr2Brmr1tEVrbNMaUdiLWnXuS2ItCJvci0lm1fsiehqsNJaxLzWO23xklxRtmuXZ1SexbGpRkAAAAAAAAAAAAADVAHzkgDzKaQBXrVopPcAS5GtBxe4GxGbnB8gVGHyUlyYmkLYyXITSVftpLYVa5pjTmtCK1lfZzRFPqCUtyKcT20tJIi1XGkxVxxa3CaRrLZYy7Wi3Ns6cu8tBb3CaW5tK57lbjUTK6jj2pDJ9AAAAAAAA+OSQBFOtFfIEhneQXyPg6gnkYL5Dg6qV8vCP+QcHSq7zsFr7BwEOQz8Wn7CVIymUy6nruJcjLXt5yk9xLipCr7AuL1vWBcq/Tr7E2Lmkn8jZnYuV6jFsz00ielHRmGq1kNbKq4tEfR3LR4++46bmmdsN4aG0yK0W5vnbm1g0o36fybTTC5W6d1F/JcrOxYhVTKSlUkwD6AAB4nNJAFK5vIwT3HwrSO+zkKevsVIi6IrrtMIt+5Xyn6Krjt8Fr7j+S+iu57enr7/ALh8nNFNz2hy19yLGmSu4zsp6+xFbZhZcZGU9dyLWkijOu5MR8S0W2ygYUEwPq7T1FVSrVKDZnppFynSMdNsp409Dn06Mp6ezMrWnF+3uHHTcJpNyaW9+1pua52x15mNHJtfJvnbm35r9DKrbc3zpz6wZW+Ui9NzWVhYY0b6MvkpK3CvF/IBKpJgC+8ulCL3HCtZDN5yNJS9i5GWtOd5ztfFy0mbTLHW2LyHcJcn7mkwzuyat22bb9x/Bfas+yzk/wAhXK87SU8zKf8AkY6y6MaWoXzl8mGo6s1Iq2vyZ2NZUkHqxKX7aOugFw0oU9h9HF2lSJtXIuUaRnqtc5XadMw1W+Yl4mOm+YNDKtI9xloI+J4VmhyosSq6a+TTNY6y+/8ATcPk6cacvplLS7Aov8jpw4tm1l2SLa9jaRz2n9lm4T09h8H0c299Ga8i4fSDO5JU4S3KzEarlPas+480pG+cufenKs5nZynL2N85c+tMpdZOcpPc0kR9KbvZt+SuF9JKV3LXyK5VNGNteNabmOsN8ehrbXvjcw1h1Y9DKjdJ/JjrDoz6GFvUT0MrG00b2a10IrSQ5tqeyFauZX6VMi1pMrdKBna1mViKSMtNpH1tGdaR4c0RxXXz+VIXyPp8dzFfI/lF0iq30UvJcyy1ssu8oop7nRjLk9Nk9xnnCX5HZjLz/XYtu0OMl7nRMuPW2oxHatXFcx3JTbdYbPKoo+xFjSaJ+15TjGe5eYW64v2jLOU5+x05jk3XPshdSnN7m0jC0qm22Unrykyh1JDVBwdWaVRoVyqbXaN018mdw1z6GVteeNzHWHRj1O7G6Ta3ObeHZ5+jTY6onoc2o7cXrQWujSMa6swwppEVrImjJIVU+uukTcn9Ial5FfJPwX9FWpkIr5D+ab6q1TKRXyVPJnfZUrZhL/IueTHXuX3GbW/sa58XPv8AQTXma119jbPk5N+5Fd5WTb3OnOHJv1U45Sal5NplzXZ7iMzNTj7BcibdL6xl5S4+xnqNs6ee4ZB++4YjTdcfz1zKdSW505jl3WYrJykzSMah/iZSX1UWMuvSosZde1SYy6kjFoXDmlijOSZFy1zs3sLhpo594dnl6NZirnxucfpl6fjtq7GrrFHJqPR86ZRqaIzbdR1bpRXkqZZ62X3GTUddzSebDXsWXGY013Lnkw1+gurZl77lzxYa/Qo1sxL6mk8WOv0KNbLSfyXPJhr9CjWyM38lzzYa9lCtdzfyXMMr6KdWrJlzLO6Qpy1HxPTbFykpoVOV0vqlSWsDPTfNHabhyc9wzG+3NcnBymzeObRW7Zt+C2VfVafoND2rT9Bk9K0/QaX3+p+gyH9X9AMK3aBUq1bQcZIz1G+NNFi5tNHJ6Zej47bDGzbijg9I9by0aSqaRMeN7opvrtxT3N8Zcnr6M/eX8tXudefN5/p7FNa8m29zaebl17KlSvN/JUwxvqgnObK+Wd9EUlJhxF0jdKTDhdeJUGBI3bsDfI2z18ADTHW75omqjo3V6OjgZ6bZf//Z";s:14:"' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER . '";a:3:{i:0;s:13:"+375171000003";i:1;s:13:"+375171000005";i:2;s:13:"+375171000004";}s:11:"' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER . '";a:0:{}}}}',
        ];
        $this->_targetObject->set($data);
        $result = $this->_targetObject->validates();
        $this->assertTrue($result);

        $expected = [
            $this->_targetObject->alias => [
                'id' => '2',
                'employee_id' => '3',
                'internal' => false,
                'data' => 'a:2:{s:7:"changed";a:1:{s:12:"EmployeeEdit";a:6:{s:10:"' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT . '";s:21:"Отдел связи";s:7:"' . CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER . '";s:86:"CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com";s:14:"' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO . '";s:4072:"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";s:14:"' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER . '";a:2:{i:0;s:13:"+375171000003";i:1;s:13:"+375171000005";}s:11:"' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER . '";a:1:{i:0;s:13:"+375291000001";}s:2:"dn";s:81:"CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com";}}s:7:"current";a:1:{s:12:"EmployeeEdit";a:5:{s:10:"' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT . '";s:4:"ОС";s:7:"' . CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER . '";N;s:14:"' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO . '";s:5312:"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";s:14:"' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER . '";a:3:{i:0;s:13:"+375171000003";i:1;s:13:"+375171000005";i:2;s:13:"+375171000004";}s:11:"' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER . '";a:0:{}}}}'
            ]
        ];
        $result = $this->_targetObject->data;
        $this->assertData($expected, $result);
    }

    /**
     * testBeforeValidateValidData method
     *
     * @return void
     */
    public function testBeforeValidateValidData()
    {
        $data = [
            'id' => '2',
            'employee_id' => '3',
            'internal' => false,
            'data' => [
                'changed' => [
                    'EmployeeEdit' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'Отдел связи',
                        CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                        CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                            '+375171000003',
                            '+375171000005',
                        ],
                        CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                            '+375291000001',
                        ],
                        CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com'
                    ]
                ],
                'current' => [
                    'EmployeeEdit' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОС',
                        CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => null,
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                        CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                            '+375171000003',
                            '+375171000005',
                            '+375171000004',
                        ],
                        CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => []
                    ]
                ]
            ]
        ];
        $this->_targetObject->set($data);
        $result = $this->_targetObject->validates();
        $this->assertTrue($result);

        $expected = [
            $this->_targetObject->alias => [
                'id' => '2',
                'employee_id' => '3',
                'internal' => false,
                'data' => 'a:2:{s:7:"changed";a:1:{s:12:"EmployeeEdit";a:6:{s:10:"' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT . '";s:21:"Отдел связи";s:7:"' . CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER . '";s:86:"CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com";s:14:"' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO . '";s:4072:"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";s:14:"' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER . '";a:2:{i:0;s:13:"+375171000003";i:1;s:13:"+375171000005";}s:11:"' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER . '";a:1:{i:0;s:13:"+375291000001";}s:2:"dn";s:81:"CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com";}}s:7:"current";a:1:{s:12:"EmployeeEdit";a:5:{s:10:"' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT . '";s:4:"ОС";s:7:"' . CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER . '";N;s:14:"' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO . '";s:5312:"/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAPAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQABgQEBAUEBgUFBgkGBQYJCwgGBggLDAoKCwoKDBAMDAwMDAwQDA4PEA8ODBMTFBQTExwbGxscHx8fHx8fHx8fHwEHBwcNDA0YEBAYGhURFRofHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8f/8AAEQgAyADIAwERAAIRAQMRAf/EAHUAAAIDAQEBAQAAAAAAAAAAAAAFAwQGBwIBCAEAAwEBAQAAAAAAAAAAAAAAAAECAwQFEAACAQMEAgEDBAMBAAAAAAAAAQIDBAURIRIGMSJBUYEyYaETFEIjFTMRAQEBAQEBAQEAAAAAAAAAAAABAhEDEgQT/9oADAMBAAIRAxEAPwD9UgAAAAAAAAB5ckgCGrcRivIgW3eThBPcD4Q33YqcNfYSpCC87dTi37i6qQqq90gn+f7k9V8vEe6Rb/P9w6fyu23cINr3Do+Tqy7RCWnuPqbk+s85Tnp7D6i5OLe+hNLcaeLsKqYySp6jAAAAAAAAAAAAAAAAABvQAiqVVEAo3N/CCe4unwgyWehTT9hdVxis122MOWkxdVIwWY7ro5aT/cSpGRv+6Sbfv+4lSFNTt9Rv8xLkFPtlTX8xKkMbTts017iP5aHG9wkmvcOlctjiO368dZjlZ3Lb4jssZqPsXKzuWusMpGoluVKiw4o1lJFJWE9QIAAAAAAAAAAAAADYBBWqqKAyXIZSNNPck5GOzPZ401L3F1pMufZ7uOnLSf7k9VxzrOdulJy9wPjE5LsVSbfsMElbLVJP8gNEr6bfkXFSp6V3P6isXF2jezXyTVwztcpUi1uI+NDjM/ODXsCbG6wPaJJxTmVGeo6Z17sKmo+xcY6jf4vIKpFblsrDylPVDSlAAAAAAAAAAAAAjqT0QAlyd8qcXuKqkc87J2JU1L2ItaSOU9j7W9ZJTJaSOdZjsk5uXsM2UvsrOo3uMqU1biUn5GlGpNsAnpJgqLtKDE0lWoRJ4qVNCTQuK6t0LmUWtw4On2LysoSj7DkRXRur598oLkVGWnYOs5b+SMdyoxre2NfnBbjQYReqGT6AAAAAAAAAA/ABRvKvGDEcYbs2U/jhPcVXI4v2/PtOa5ENY5LnMzOc5ewKZa5u5Tb3GXVKblIZPPBsZJIUmAW6NJgcXaVPRC4qVMloHFdDloHB9BVdA4PpYt7pxktw4XWt69lXGpHcOIrs/TctyUPYbOuvYS65047jRWhpS1Q0pAAAAAAAAAA8zewAmy1bjTkI45P3PIuMZ7/UmtI4R2zJSlOe4mkc9va8pze4cHVNU3JjJLG1b+Bh7Vm/oMJ6dm/oPgW6Vm/oHAm/rtLwHD6jqLQODqtOeg+F1E6gcHXyNZpi4OneHvHGpHcQdf6TkXrDf6CTXc+sXXKnDcbOttbS1ihpWAAAAAAAAAA8VfDAM5nZ6U5CVHFu812lU+4lxwrstVurMXF9ZOUHKY+Eu2tm5abDBlSxra8Bw0yxj+gwmpYx6+Bhdp4p6eBhHXxzivAwV3NrJa7CBbVoy+gDitOlIXS4gkpJgF7HVWqiJDqfSrl84b/QQrv3UK+tOH2Gzro1lLWCGldXgCAAAAAAAAHir+IBms9FunIDjiveab0qfcS44d2Kk/5ZBxTP0rfWfgfAfY6y102DgaC2xqaWw+GtRxWvwM1mjiN/AGv08R6/iMKt3idnsBEF7i93sKqkKquJbfgi1cyq1sTJLwT9D5LLrHyj8D6m5Q21JxqoEcdG6bJqpD7AVd/6ZP0h9hs66dj3/rQ0mK8AQAAAAAAAA8zWwAjzFLlTkBuQ91sm4z2+oKjh3ZLJqrLYZs5Rt9KngZtJi7dbbAbT2dstFsPgM6VnF/Azi9QsI/QSouKyio+ANSvLSOj2GCK6sYtvYmqir/y4t+DLTbMR1sMnH8TK1fyQZPE8U9hyo1ln5WfCr4LlY6jZdSg1VgUyrvfTNeEPsNnXUMd/5oaTJeAIAAAAAAAAfJLYAX39LlBgHOO247lCe31BTiHasY1UnsM2M/q8avgZneNhpoM2jtGkkURrbyWwHKa2/FpEVpFqSXEXV8LrteR9HCqrS1YrTkfaVum/Bjqt8xYlZpx8GFraQiy1guL2CVOsslc2H+3wa5rn1Gj6vZyVWOxpGGo7j0+g1CH2KY10rHx0ghpMV4AgAAAAAAAAAEFeHKLAMn2Cx505bAbj/bMPrKb4jOOcXmNcKr2GqPdrScWMzWg2tCi4Y29R6oVqpDi0qeDLVbZhjHeJn9NplWuKLY5ofClK1evgd0cylpWunwZarbOVh0PUwtayFORteSewpRYz9TFuVXwbZrn3lp+t4ZqpF8TaOTcde61Y8IR2Ljn029pDSKGhbQAAAAAAAAAAB5mtUAKslbKcHsAc97Lhual6jNzPMYRxnJ8RrhK7Bwl4DrSRPStn9B9P5WqVBpk2qmTO0g00YarfOTehDVIytbzKWVumEp8R/wBRfQro49K2S+CNVcjzUp6IyrSKFxb8vgUOx5tsVzmtjXNc+42OAw6i4vidGXH6Og4mzUILY0jk0fUo6IaEgAAAAAAAAAAAMAgr0+SYBn8tjlOL2A2BzmFWsnxGuMdeYzhJ7Ca5VY2uj8C60iWNukKqi1Qp6My1GuTO3WyMrG8q1FLQJD69cEVwuvMoomqlVqqMq0iONHlIUO04xuPTktjXLn3WzxFgopbHRlw+lai1pKMUaRzVdihofQAAAAAAAAAAAAPklqAU7qgpRewBmMxYJqWwKjCZaxUZPYTbNIKlLjIGkrxogPqSm1qTY0zV2hIi5ayrcZC4vr1zDhdeZTIsVKgk9WZWNZVq0o8pIUha01WJs/GxtmOT001thbKKWxtI4902pw0RbGpRkAAAAAAAAAAAAAAA8VI6oATZKgnF7AcYfN228thNJWOvYcZMGkqhOejGp8hV3BcXaFUmxtlcjU2JsWHVJJ4dQinK+werM7GkpzjKWskEiN6bTE2+y2Nsxx+mmltqaUUaxzWrcUUh9AAAAAAAAAAAAAAAAPklsAULynrFgbIZu21UtgVGEytFxkxNJWeuJNNjXEEar1Brldt6jCtsr1Ob0IrR7cmRU145EUli33kiFdabD09WipGW62+LppRRtlx7p7SWiLY1KMgAAAAAAAAAAAAAAAADAK9eOqYBnctbpxYKjBZu10ctgXKx19DSTE0lUYr2Brmr1tEVrbNMaUdiLWnXuS2ItCJvci0lm1fsiehqsNJaxLzWO23xklxRtmuXZ1SexbGpRkAAAAAAAAAAAAADVAHzkgDzKaQBXrVopPcAS5GtBxe4GxGbnB8gVGHyUlyYmkLYyXITSVftpLYVa5pjTmtCK1lfZzRFPqCUtyKcT20tJIi1XGkxVxxa3CaRrLZYy7Wi3Ns6cu8tBb3CaW5tK57lbjUTK6jj2pDJ9AAAAAAAA+OSQBFOtFfIEhneQXyPg6gnkYL5Dg6qV8vCP+QcHSq7zsFr7BwEOQz8Wn7CVIymUy6nruJcjLXt5yk9xLipCr7AuL1vWBcq/Tr7E2Lmkn8jZnYuV6jFsz00ielHRmGq1kNbKq4tEfR3LR4++46bmmdsN4aG0yK0W5vnbm1g0o36fybTTC5W6d1F/JcrOxYhVTKSlUkwD6AAB4nNJAFK5vIwT3HwrSO+zkKevsVIi6IrrtMIt+5Xyn6Krjt8Fr7j+S+iu57enr7/ALh8nNFNz2hy19yLGmSu4zsp6+xFbZhZcZGU9dyLWkijOu5MR8S0W2ygYUEwPq7T1FVSrVKDZnppFynSMdNsp409Dn06Mp6ezMrWnF+3uHHTcJpNyaW9+1pua52x15mNHJtfJvnbm35r9DKrbc3zpz6wZW+Ui9NzWVhYY0b6MvkpK3CvF/IBKpJgC+8ulCL3HCtZDN5yNJS9i5GWtOd5ztfFy0mbTLHW2LyHcJcn7mkwzuyat22bb9x/Bfas+yzk/wAhXK87SU8zKf8AkY6y6MaWoXzl8mGo6s1Iq2vyZ2NZUkHqxKX7aOugFw0oU9h9HF2lSJtXIuUaRnqtc5XadMw1W+Yl4mOm+YNDKtI9xloI+J4VmhyosSq6a+TTNY6y+/8ATcPk6cacvplLS7Aov8jpw4tm1l2SLa9jaRz2n9lm4T09h8H0c299Ga8i4fSDO5JU4S3KzEarlPas+480pG+cufenKs5nZynL2N85c+tMpdZOcpPc0kR9KbvZt+SuF9JKV3LXyK5VNGNteNabmOsN8ehrbXvjcw1h1Y9DKjdJ/JjrDoz6GFvUT0MrG00b2a10IrSQ5tqeyFauZX6VMi1pMrdKBna1mViKSMtNpH1tGdaR4c0RxXXz+VIXyPp8dzFfI/lF0iq30UvJcyy1ssu8oop7nRjLk9Nk9xnnCX5HZjLz/XYtu0OMl7nRMuPW2oxHatXFcx3JTbdYbPKoo+xFjSaJ+15TjGe5eYW64v2jLOU5+x05jk3XPshdSnN7m0jC0qm22Unrykyh1JDVBwdWaVRoVyqbXaN018mdw1z6GVteeNzHWHRj1O7G6Ta3ObeHZ5+jTY6onoc2o7cXrQWujSMa6swwppEVrImjJIVU+uukTcn9Ial5FfJPwX9FWpkIr5D+ab6q1TKRXyVPJnfZUrZhL/IueTHXuX3GbW/sa58XPv8AQTXma119jbPk5N+5Fd5WTb3OnOHJv1U45Sal5NplzXZ7iMzNTj7BcibdL6xl5S4+xnqNs6ee4ZB++4YjTdcfz1zKdSW505jl3WYrJykzSMah/iZSX1UWMuvSosZde1SYy6kjFoXDmlijOSZFy1zs3sLhpo594dnl6NZirnxucfpl6fjtq7GrrFHJqPR86ZRqaIzbdR1bpRXkqZZ62X3GTUddzSebDXsWXGY013Lnkw1+gurZl77lzxYa/Qo1sxL6mk8WOv0KNbLSfyXPJhr9CjWyM38lzzYa9lCtdzfyXMMr6KdWrJlzLO6Qpy1HxPTbFykpoVOV0vqlSWsDPTfNHabhyc9wzG+3NcnBymzeObRW7Zt+C2VfVafoND2rT9Bk9K0/QaX3+p+gyH9X9AMK3aBUq1bQcZIz1G+NNFi5tNHJ6Zej47bDGzbijg9I9by0aSqaRMeN7opvrtxT3N8Zcnr6M/eX8tXudefN5/p7FNa8m29zaebl17KlSvN/JUwxvqgnObK+Wd9EUlJhxF0jdKTDhdeJUGBI3bsDfI2z18ADTHW75omqjo3V6OjgZ6bZf//Z";s:14:"' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER . '";a:3:{i:0;s:13:"+375171000003";i:1;s:13:"+375171000005";i:2;s:13:"+375171000004";}s:11:"' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER . '";a:0:{}}}}'
            ]
        ];
        $result = $this->_targetObject->data;
        $this->assertData($expected, $result);
    }

    /**
     * testBeforeSaveValidData method
     *
     * @return void
     */
    public function testBeforeSaveValidData()
    {
        $data = [
            'id' => '3',
            'employee_id' => '4',
            'internal' => false,
            'data' => [
                'changed' => [
                    'EmployeeEdit' => [
                        CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                        CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '123',
                        CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'a.dementeva@fabrikam.com',
                    ]
                ],
                'current' => [
                    'EmployeeEdit' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '216',
                        CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'a.s.dementeva@fabrikam.com',
                    ]
                ]
            ]
        ];

        $this->_targetObject->set($data);
        $result = $this->_targetObject->save();
        $this->assertTrue(is_array($result));
        $this->assertTrue(isset($result[$this->_targetObject->alias]['modified']));
        unset($result[$this->_targetObject->alias]['modified']);
        $expected = [
            $this->_targetObject->alias => [
                'id' => '3',
                'employee_id' => '4',
                'internal' => false,
                'data' => 'a:2:{s:7:"changed";a:1:{s:12:"EmployeeEdit";a:3:{s:2:"' . CAKE_LDAP_LDAP_DISTINGUISHED_NAME . '";s:96:"CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com";s:26:"' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME . '";s:3:"123";s:4:"' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL . '";s:24:"a.dementeva@fabrikam.com";}}s:7:"current";a:1:{s:12:"EmployeeEdit";a:2:{s:26:"' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME . '";s:3:"216";s:4:"' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL . '";s:26:"a.s.dementeva@fabrikam.com";}}}',
            ]
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testAfterFindInvalidData method
     *
     * @return void
     */
    public function testAfterFindInvalidData()
    {
        $this->_targetObject->recursive = -1;
        $result = $this->_targetObject->read(null, 4);
        $expected = [
            $this->_targetObject->alias => [
                'id' => '4',
                'employee_id' => '6',
                'internal' => false,
                'data' => false,
                'created' => '2017-11-16 14:48:22',
                'modified' => '2017-11-16 14:48:22',
            ]
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testAfterFindValidData method
     *
     * @return void
     */
    public function testAfterFindValidData()
    {
        $this->_targetObject->recursive = -1;
        $result = $this->_targetObject->read(null, 2);
        $expected = [
            $this->_targetObject->alias => [
                'id' => '2',
                'employee_id' => '3',
                'internal' => false,
                'data' => [
                    'changed' => [
                        'EmployeeEdit' => [
                            'dn' => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                            'title' => 'Ведущий инженер',
                            'division' => 'Группа №1',
                            'thumbnailphoto' => base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCABgAGADASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD+/iiiigAooooAKK+If2tP+Cgv7Jv7FkOmj9oH4saZ4X13XLc3/h/wPpFpq/inx7r9uGaze6svCPhuxvb8aUHAB1jVlj0lWQqX3hBX4g+L/wDg5y+EGk/EC7tvBX7Pvj/4g/C9bYC21+51XSvAfiq11EXhP2r+ydYbWLPVtIvLUKWD/wDCP6ppS71kjctuUA/qcor+afwl/wAHOP7JeqX0Np4y+A/7Q/gu2mX5tT0+18BeMLe27Za1svF1jeEjtiz5Gcg1+yf7Kv7df7L/AO2fod9rX7PnxZ0LxncaNBa3PiPwjP8Aa9E8e+FRdOojHiLwfrEdlrNnbtnA1REfSSzKqOxGSAfYtFFFABRRRQAUUUUAFfK/7W37XXwZ/Yt+Dur/ABq+OGs6lp3hPTr+10Ow07QtMOr+KPFGu6rvGn+H/DeiiS2e/wBVnZCUjVwoWOQFhJhT9UV/NB/wc66PeXX7JnwC1uOWY2WkftH2Vvd2JIW3ubjVPht8QLOyuGHTKMZCDng4wBhiQD+Ur9uP9pq8/bB/au+M37RclnqGj2HjvxJaf8IroOr3dndan4e8DaFpGn+GvB+j3xs2Nmc2lp9uZrRiSfvfN0+U6Mk9TmigAr1r4B/G/wCIf7N/xg+Hvxs+Fev6loPjH4feJLTV7SWwuzajVtK+241jw1rC/cvPDfiOzzo2s6MQoBGCMdfJaKAP9Oz9hL9sXwL+3T+zt4W+PvgTSrvw3DrF3quheKPB2oX1tfap4K8ZaBdiy1jw5fXtotqmpqzfZNY0rVYsx6tpOrRTYjLhD9qV/Nb/AMGydnqsP7H/AMcr+6lWbS7v9pnVv7Hh+VfszWnw5+H9lqtyScHLEjkjJwRzuGP6UqACiiigAooooAK/AD/g45TQ7z/gnnLHfajptnr2nfHL4Zaz4d0++u7O21LVfst3qOkawNFtL3/S74Wekay15dixBBABdlBBP7/1/M3/AMHN3w0v/Ef7LvwI+KdrB51p8LPjldaXrrZGLbS/iP4Q1LRre8IxkAaxpOjWrE5BMgB9aAP4o6KKKACiiigD+8L/AINzY9DsP+CesNnZato17r2o/HP4nax4k06wurO61LSrm8vNOs9KXV7Gyzd6Q97pGiLeWgv1UgMdpIDg/v8AV/M5/wAGyfwwvvDn7LXx4+Kl1AIbX4p/Gu00fSGyM3WlfC/whp2i3F2R1AbWNX1m0VgqjKYyccf0x0AFFFFABRRRQAV84/tUfs2eBf2uPgB8Rv2ePiJNf2Xhj4kaKun3OraP9kGveHdSs72z1fR/EWjvdxXdqmraNrFnZ30T7MOVKBgJNx+jqZJ9xvpQB/lE/GP4Z618F/i38TvhF4igWHXfhZ4+8WeANQM9n9lW9ufDGr6lpC3gswOftv2P7bwMkr90YATziv6Gv+DjP9lzS/hB+1f4Q/aB8Ny2sOlftN+G9Xv/ABJoMGRdWvxG+HFnoGi6xq43DP2LxL4b1fw6o6AayNYZTgqT/PLQAV6P8HPhnrfxo+L/AMK/hB4dK/298TvH/hPwBo5g/wBKWyuvE+r6fpH2z7GRj/Q/tZvOQOGGc4584r+hv/g3I/Zf0v4v/tZ+MP2gfEc1rLpf7MnhyzuPDejEf6VdfEb4kWmvaNo+rgBgfsvhzw3ZeL93Ya1rfzZBoA/sV/ZT/Zt8B/sj/s//AAz/AGdfhwb+Xwl8M9EOkWmp6x9kbW/EOo3F5eavrPiLV2tkVZNW1nV7281eVthZWkCHAQEfSFMRNv8AQen/ANf/AD3p9ABRRRQAUUUUAFMk+430pJJBGMn/AD/n8K/Lz9tz/grH+yR+xZo/iPTfEfxI0Hxr8arKwvBoPwU8E3beJvFVzrn2O8OlW3i46Mz2XgfSTfx41W98S3dp5MRZ03lVoA/mI/4L9ftv/BL9rT4x/B/wR8E9W1nxJH+zofiz4W8d6xfeH9Z0PSj4x13WfCFldaRo51kWN7qx0hfB94by6bRSqncqlvvH+f2t7xR4o1jxt4m8V+MvEc3neI/GHiPxF4w16WDH2W71/wAT6xqOr6xeE4G/beXoJ5wMEDnIrBoAK/fr/ggP+298D/2TfjF8YPA/xp1jWfDiftFH4TeF/AWsadoOs65oA8Y6Fq/iGybSNY/sYXt3pP8AbFz4wtP7F1Z7EhySW46fgLW94X8U6x4I8T+FvG3hyY2fiPwh4j8P+MNCmuD/AMeWveGNY07V9G2g9CbyzY44J5B2gEgA/wBZ6NxIufwIqSvy0/Yj/wCCsn7JP7aOj+G9M8N/EPQvBHxqvNPtBr/wa8c3TeG/FUGumzQ6tZ+EP7YZLTxtpS3p2WL+HLu43xRh3w3A/UhH3f0Pr/8AX/z2oAfRRRQBVleKNCTJ5QiHnnjPyjJORzkeoFfj3+2L/wAFtv2Lv2TDqvh2x8X/APC/vivZD7Ofhx8GLu01+20m+O3daeL/AB1vfwh4aAO75RJrHiMAFR4bKkOf5KP+CgX/AAVj/ab/AG0fG/jXw5pXj3xJ8Mv2bzrWraR4R+E/hDVrzwvbav4Xtb37JZ3vxJ1iwUaz4w1nWgw1e9s9Xu/+EbKjA0UAZH5SGMxZ8qHyov8Anhakjv6bVbPcncB6jmgD9g/2wv8Agt5+23+1h/a/h3RvFNt+zx8K9RbyP+EF+EN3e22v6rphBDWfiP4kMbPxJq+c7LwaN/wjGifOw2YLAfj3/wAtJpussw+0TT8g3l7yRkZPBzn5cgk7cjJpfLf+83/kP/4mjy3/ALzf+Of/ABNABRR5b/3m/wDHP/iaPLf+83/jn/xNABRR5b/3m/8AHP8A4mjy3/vN/wCOf/E0AGR5kMsXE0Vz9pil6XlldtliT2UDjjIJIJILYr9g/wBjz/gt5+2z+ygdI8Paz4n/AOGifhbp5s4W8CfF66vLnX9M0wKyEeEviRm88SaUFBKqdbPibRgMYJZVA/Hzy3/vN/45/wDE0eXJ/eb8dmOOmcAHH0IOOhFAH+hj+x1/wW6/Yq/a0Gk+HL/xfL8Avi3qS/Z0+HPxhvLPQrXVr4bibTwh45yvhHxIAdv7syaN4jYfL/wjqAA1+xKzRFEkEgMcoHlnswIyMHqeP/r81/kiPGZYhFLD50X/ADyuScj7oxkcjIDZIyeQo2jIH6uf8E/f+Cs37Tn7GfjvwVoOs+O/EnxN/Zu/trS7Dxj8JfGN5e+J7fSfC91eCz1jWPhxe3n/ABOvDetaOD/a40j/AJFzVs5XSM5IAP/Z'),
                        ]
                    ],
                    'current' => [
                        'EmployeeEdit' => [
                            'title' => 'Зам. начальника отдела - главный специалист',
                            'division' => '',
                            'thumbnailphoto' => base64_decode('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'),
                        ]
                    ],
                ],
                'created' => '2017-11-16 10:12:44',
                'modified' => '2017-11-16 10:12:44',
            ]
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testPaginateOrder method
     *
     * @return void
     */
    public function testPaginateOrder()
    {
        $result = $this->_targetObject->paginate(null, null, ['Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'desc'], 2, 1, 0, []);
        $expected = [
            [
                'Deferred' => [
                    'id' => '2',
                    'employee_id' => '3',
                    'internal' => false,
                    'data' => [
                        'changed' => [
                            'EmployeeEdit' => [
                                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                                CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Группа №1',
                                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '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',
                            ],
                        ],
                        'current' => [
                            'EmployeeEdit' => [
                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                                CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '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',
                            ],
                        ],
                    ],
                    'created' => '2017-11-16 10:12:44',
                    'modified' => '2017-11-16 10:12:44',
                ],
                'Employee' => [
                    'id' => '3',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'l.suhanova@fabrikam.com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => 'dd518c55-35ce-4a5c-85c5-b5fb762220bf',
                ],
            ],
            [
                'Deferred' => [
                    'id' => '1',
                    'employee_id' => '1',
                    'internal' => true,
                    'data' => [
                        'changed' => [
                            'EmployeeEdit' => [
                                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Миронов В.М.,OU=12-05,OU=УИЗ,OU=Пользователи,DC=fabrikam,DC=com',
                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Геолог',
                                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '',
                            ],
                        ],
                        'current' => [
                            'EmployeeEdit' => [
                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий геолог',
                                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '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',
                            ],
                        ],
                    ],
                    'created' => '2017-11-15 16:20:13',
                    'modified' => '2017-11-15 16:20:13',
                ],
                'Employee' => [
                    'id' => '1',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Миронов В.М.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий геолог',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'v.mironov@fabrikam.com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '1dde2cdc-5264-4286-9273-4a88b230237c',
                ]
            ]
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testPaginateConditions method
     *
     * @return void
     */
    public function testPaginateConditions()
    {
        $result = $this->_targetObject->paginate(['Deferred.internal' => false], null, null, 2, 2, 0, []);
        $expected = [
            [
                'Deferred' => [
                    'id' => '4',
                    'employee_id' => '6',
                    'internal' => false,
                    'data' => false,
                    'created' => '2017-11-16 14:48:22',
                    'modified' => '2017-11-16 14:48:22',
                ],
                'Employee' => [
                    'id' => '6',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Козловская Е.М.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Заведующий сектором',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'e.kozlovskaya@fabrikam.com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '81817f32-44a7-4b4a-8eff-b837ba387077',
                ],
            ],
            [
                'Deferred' => [
                    'id' => '5',
                    'employee_id' => '2',
                    'internal' => false,
                    'data' => [
                        'changed' => [
                            'EmployeeEdit' => [
                                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                                CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОРС',
                                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                                    '+375171000002',
                                    '+375171000007',
                                ],
                                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                                    '+375291000008',
                                ],
                            ],
                        ],
                        'current' => [
                            'EmployeeEdit' => [
                                CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОС',
                                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                                    '+375171000002',
                                ],
                                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [],
                            ],
                        ],
                    ],
                    'created' => '2017-11-17 09:33:19',
                    'modified' => '2017-11-17 09:33:19',
                ],
                'Employee' => [
                    'id' => '2',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Егоров Т.Г.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 't.egorov@fabrikam.com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '0010b7b8-d69a-4365-81ca-5f975584fe5c',
                ],
            ],
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetNameEmptyParam method
     *
     * @return void
     */
    public function testGetNameEmptyParam()
    {
        $result = $this->_targetObject->getName();
        $this->assertFalse($result);
    }

    /**
     * testGetNameInvalidParamId method
     *
     * @return void
     */
    public function testGetNameInvalidParamId()
    {
        $result = $this->_targetObject->getName('1000');
        $this->assertFalse($result);
    }

    /**
     * testGetNameInvalidParamArray method
     *
     * @return void
     */
    public function testGetNameInvalidParamArray()
    {
        $data = [
            'BadData' => []
        ];
        $result = $this->_targetObject->getName($data);
        $this->assertFalse($result);
    }

    /**
     * testGetNameIdSuccess method
     *
     * @return void
     */
    public function testGetNameIdSuccess()
    {
        $result = $this->_targetObject->getName(2);
        $expected = 'Суханова Л.Б.';
        $this->assertData($expected, $result);
    }

    /**
     * testGetNameArraySuccess method
     *
     * @return void
     */
    public function testGetNameArraySuccess()
    {
        $data = [
            'Employee' => [
                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Егоров Т.Г.'
            ]
        ];
        $result = $this->_targetObject->getName($data);
        $expected = 'Егоров Т.Г.';
        $this->assertData($expected, $result);
    }

    /**
     * testGetRefIdEmptyParam method
     *
     * @return void
     */
    public function testGetRefIdEmptyParam()
    {
        $result = $this->_targetObject->getRefId();
        $this->assertFalse($result);
    }

    /**
     * testGetRefIdInvalidParamId method
     *
     * @return void
     */
    public function testGetRefIdInvalidParamId()
    {
        $result = $this->_targetObject->getRefId('1000');
        $this->assertFalse($result);
    }

    /**
     * testGetRefIdIdSuccess method
     *
     * @return void
     */
    public function testGetRefIdIdSuccess()
    {
        $result = $this->_targetObject->getRefId(2);
        $expected = '3';
        $this->assertData($expected, $result);
    }

    /**
     * testGetBreadcrumbInfoEmptyParam method
     *
     * @return void
     */
    public function testGetBreadcrumbInfoEmptyParam()
    {
        $result = $this->_targetObject->getBreadcrumbInfo();
        $expected = [
            [
                CakeText::truncate(__('Employees'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                [
                    'plugin' => null,
                    'controller' => 'employees',
                    'action' => 'index'
                ]
            ],
            [
                CakeText::truncate(__('Deferred saves'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                [
                    'plugin' => null,
                    'controller' => 'deferred',
                    'action' => 'index'
                ]
            ]
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetBreadcrumbInfoInvalidParam method
     *
     * @return void
     */
    public function testGetBreadcrumbInfoInvalidParam()
    {
        $result = $this->_targetObject->getBreadcrumbInfo('1000', null);
        $expected = [
            [
                CakeText::truncate(__('Employees'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                [
                    'plugin' => null,
                    'controller' => 'employees',
                    'action' => 'index'
                ]
            ],
            [
                CakeText::truncate(__('Deferred saves'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                [
                    'plugin' => null,
                    'controller' => 'deferred',
                    'action' => 'index'
                ]
            ]
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetBreadcrumbInfoSuccessNotIncludeRoot method
     *
     * @return void
     */
    public function testGetBreadcrumbInfoSuccessNotIncludeRoot()
    {
        $result = $this->_targetObject->getBreadcrumbInfo('2', false);
        $expected = [
            [
                CakeText::truncate('Суханова Л.Б.', CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                [
                    '3',
                    'plugin' => null,
                    'controller' => 'employees',
                    'action' => 'view'
                ]
            ],
            [
                CakeText::truncate(__('Deferred saves'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                [
                    '2',
                    'plugin' => null,
                    'controller' => 'deferred',
                    'action' => 'view'
                ]
            ]
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetBreadcrumbInfoSuccessDefaultIncludeRoot method
     *
     * @return void
     */
    public function testGetBreadcrumbInfoSuccessDefaultIncludeRoot()
    {
        $result = $this->_targetObject->getBreadcrumbInfo(3, null);
        $expected = [
            [
                CakeText::truncate(__('Employees'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                [
                    'plugin' => null,
                    'controller' => 'employees',
                    'action' => 'index'
                ]
            ],
            [
                CakeText::truncate('Дементьева А.С.', CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                [
                    '4',
                    'plugin' => null,
                    'controller' => 'employees',
                    'action' => 'view'
                ]
            ],
            [
                CakeText::truncate(__('Deferred saves'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                [
                    3,
                    'plugin' => null,
                    'controller' => 'deferred',
                    'action' => 'view'
                ]
            ]
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetBreadcrumbInfoSuccessIncludeRoot method
     *
     * @return void
     */
    public function testGetBreadcrumbInfoSuccessIncludeRoot()
    {
        $result = $this->_targetObject->getBreadcrumbInfo(4, true);
        $expected = [
            [
                CakeText::truncate(__('Employees'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                [
                    'plugin' => null,
                    'controller' => 'employees',
                    'action' => 'index'
                ]
            ],
            [
                CakeText::truncate('Козловская Е.М.', CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                [
                    '6',
                    'plugin' => null,
                    'controller' => 'employees',
                    'action' => 'view'
                ]
            ],
            [
                CakeText::truncate(__('Deferred saves'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                [
                    4,
                    'plugin' => null,
                    'controller' => 'deferred',
                    'action' => 'view'
                ]
            ]
        ];
        $this->assertData($expected, $result);
    }
}
