<?php
/**
 * This file is the view file of the application. Used for render
 *  navigation bar.
 *
 * CakeTheme: Set theme for application.
 * @copyright Copyright 2016-2019, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package plugin.View.Elements
 */

if (!isset($isExternalAuth)) {
	$isExternalAuth = false;
}

if (!isset($emailContact)) {
	$emailContact = '';
}

if (!isset($emailSubject)) {
	$emailSubject = '';
}

if (!isset($showSearchForm)) {
	$showSearchForm = true;
}

if (!isset($useNavbarContainerFluid)) {
	$useNavbarContainerFluid = $this->UserInfo->checkUserRole([USER_ROLE_ADMIN]);
}

if (!isset($showMainMenu)) {
	$showMainMenu = true;
}

if (!isset($countDeferredSaves)) {
	$countDeferredSaves = 0;
}

if (!isset($projectName)) {
	$projectName = __d('project', PROJECT_NAME);
}
$projectLogo = PROJECT_LOGO_IMAGE_SMALL;
$iconList = [];

if (!$showMainMenu) {
	echo $this->element('CakeTheme.barNavBase', compact('showSearchForm', 'useNavbarContainerFluid',
		'projectName', 'projectLogo', 'iconList'));

	return;
}

$menuItems = [
	$this->ViewExtension->menuActionLink(
		'fas fa-users',
		__('Employees'),
		['controller' => 'employees', 'action' => 'search', 'plugin' => null],
		['title' => __('Manage information about employees')]
	),
	$this->ViewExtension->menuActionLink(
		'fas fa-sitemap',
		__('Tree of employees'),
		['controller' => 'employees', 'action' => 'tree', 'plugin' => null],
		['title' => __('Tree view of employess')]
	),
	$this->ViewExtension->menuActionLink(
		'fas fa-user-circle',
		__('Gallery of employees'),
		['controller' => 'employees', 'action' => 'gallery', 'plugin' => null],
		['title' => __('Gallery of employees')]
	)
];
$menuItems[] = 'divider';
if ($this->UserInfo->checkUserRole([USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN])) {
	$menuItems[] = $this->ViewExtension->menuActionLink(
		'fas fa-sync-alt',
		__('Refresh all files'),
		['controller' => 'employees', 'action' => 'generate', 'plugin' => null],
		[
			'title' => __('Refresh all exported files'),
			'data-toggle' => 'request-only'
		]
	);
}
$menuItems[] = $this->ViewExtension->menuActionLink(
	'fas fa-file-download',
	__('Export phone book'),
	['controller' => 'employees', 'action' => 'export', 'plugin' => null],
	['title' => __('Saving a local copy of the phone book')]
);

if ($this->UserInfo->checkUserRole([USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN])) {
	$menuItems[] = 'divider';
	$menuItems[] = $this->ViewExtension->menuActionLink(
		'fas fa-sync-alt',
		__('Synchronizing information with LDAP server'),
		['controller' => 'employees', 'action' => 'sync', 'plugin' => null],
		[
			'title' => __('Synchronize information of employees with LDAP server'),
			'data-toggle' => 'request-only'
		]
	);
	if ($this->UserInfo->checkUserRole(USER_ROLE_ADMIN)) {
		$menuItems[] = $this->ViewExtension->menuActionLink(
			'fas fa-book',
			__('Logs'),
			['controller' => 'logs', 'action' => 'index', 'plugin' => null],
			['title' => __('View logs of changing information')]
		);
		$menuItems[] = $this->ViewExtension->menuActionLink(
			'fas fa-check',
			__('Check state tree'),
			['controller' => 'employees', 'action' => 'check', 'plugin' => null],
			['title' => __('Check state tree of employees')]
		);
	}
}

$iconList[] = [$this->ViewExtension->menuItemLink(
	'far fa-address-book fa-lg',
	__('Informations of employees'),
	null,
	['class' => 'app-tour-main-menu-employees']
) => $menuItems];

if ($this->UserInfo->checkUserRole([USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN])) {
	$menuItems = [
		$this->ViewExtension->menuActionLink(
			'fas fa-th-list',
			__('Index of departments'),
			['controller' => 'departments', 'action' => 'index', 'plugin' => null],
			['title' => __('Manage information about departments')]
		)
	];

	if ($this->UserInfo->checkUserRole(USER_ROLE_ADMIN)) {
		$menuItems[] = 'divider';
		$menuItems[] = $this->ViewExtension->menuActionLink(
			'fas fa-check',
			__('Check state list'),
			['controller' => 'departments', 'action' => 'check', 'plugin' => null],
			['title' => __('Check state list of departments')]
		);
	}

	$iconList[] = [$this->ViewExtension->menuItemLink(
		'fas fa-list fa-lg',
		__('Informations of departments'),
		null,
		['class' => 'app-tour-main-menu-departments']
	) => $menuItems];

	$iconList[] = $this->ViewExtension->menuItemLink(
		'fas fa-hourglass-start fa-lg',
		__('Deferred saves'),
		['controller' => 'deferred', 'action' => 'index', 'plugin' => null],
		['title' => __('Manage deferred saves'), 'class' => 'app-tour-main-menu-deferred'],
		$countDeferredSaves
	);
}

if ($this->UserInfo->checkUserRole(USER_ROLE_ADMIN)) {
	$menuItems = [
		$this->ViewExtension->menuActionLink(
			'fas fa-cog',
			__('Application settings'),
			['controller' => 'settings', 'action' => 'index', 'plugin' => 'cake_settings_app', 'prefix' => false],
			['title' => __('Application settings')]
		),
		$this->ViewExtension->menuActionLink(
			'fas fa-tasks',
			__('Queue of tasks'),
			['controller' => 'queues', 'action' => 'index', 'plugin' => 'cake_settings_app', 'prefix' => false],
			['title' => __('Task queue list')]
		)
	];
	$iconList[] = [$this->ViewExtension->menuItemLink(
		'fas fa-cogs fa-lg',
		__('Application settings'),
		null,
		['class' => 'app-tour-main-menu-settings']
	) => $menuItems];
}

if (!$isExternalAuth) {
	if ((bool)$this->UserInfo->getUserField('role')) {
		$iconList[] = $this->ViewExtension->menuItemLink(
			'fas fa-sign-out-alt fa-lg',
			__('Logout'),
			['controller' => 'users', 'action' => 'logout', 'plugin' => 'cake_ldap', 'prefix' => false]
		);
	} else {
		$iconList[] = $this->ViewExtension->menuItemLink(
			'fas fa-sign-in-alt fa-lg',
			__('Login'),
			['controller' => 'users', 'action' => 'login', 'plugin' => 'cake_ldap', 'prefix' => false]
		);
	}
}

if ($this->request->here() === '/') {
	if (!empty($emailContact)) {
		$iconList[] = $this->ViewExtension->menuItemLink(
			'far fa-envelope fa-lg',
			__('Contact administrator'),
			sprintf('mailto:%s?subject=%s', h($emailContact), rawurlencode(h($emailSubject)))
		);
	}

	$iconList[] = $this->ViewExtension->menuItemLink(
		'fas fa-question fa-lg',
		__d('tour_app', 'Start the tour of the application'),
		'#',
		['data-toggle' => 'start-app-tour']
	);
}

echo $this->element('CakeTheme.barNavBase', compact('showSearchForm', 'useNavbarContainerFluid', 'projectName', 'projectLogo', 'iconList'));
