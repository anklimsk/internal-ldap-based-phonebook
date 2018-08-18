<?php
    /**
     * Routes configuration
     *
     * CakeLdap: Authentication of users by member group of Active Directory.
     * @copyright Copyright 2016, Andrey Klimov.
     * @package plugin.Config
     */

    Router::connect(
        '/users/login',
        ['controller' => 'users', 'action' => 'login', 'plugin' => 'cake_ldap']
    );
    Router::connect(
        '/users/logout',
        ['controller' => 'users', 'action' => 'logout', 'plugin' => 'cake_ldap']
    );
    Router::connect(
        '/users',
        ['controller' => 'employees', 'action' => 'index', 'plugin' => 'cake_ldap']
    );
    Router::connect(
        '/users/:action/*',
        ['controller' => 'employees', 'plugin' => 'cake_ldap']
    );

    Router::parseExtensions();
    Router::setExtensions('json');
