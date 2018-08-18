<?php
    /**
     * Routes configuration
     *
     * CakeInstaller: Installer of CakePHP web application.
     * @copyright Copyright 2016, Andrey Klimov.
     * @package plugin.Config
     */

    Router::connect(
        '/installer',
        ['controller' => 'installer', 'action' => 'index', 'plugin' => 'cake_installer']
    );
