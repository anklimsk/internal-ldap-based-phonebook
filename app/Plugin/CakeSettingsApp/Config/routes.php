<?php
    /**
     * Routes configuration
     *
     * CakeSettingsApp: Manage settings of application.
     * @copyright Copyright 2016, Andrey Klimov.
     * @package plugin.Config
     */

    Router::connect(
        '/settings',
        ['controller' => 'settings', 'action' => 'index', 'plugin' => 'cake_settings_app']
    );
    Router::connect(
        '/settings/:action/*',
        ['controller' => 'settings', 'plugin' => 'cake_settings_app']
    );

    Router::parseExtensions();
    Router::setExtensions('json');
