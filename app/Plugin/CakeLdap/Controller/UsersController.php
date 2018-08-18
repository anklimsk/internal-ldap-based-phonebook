<?php
/**
 * This file is the controller file of the plugin.
 * Used for for management users.
 *
 * CakeLdap: Authentication of users by member group of Active Directory.
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.Controller
 */

App::uses('CakeLdapAppController', 'CakeLdap.Controller');

/**
 * The controller is used for management users.
 *
 * This controller allows to perform the following operations:
 *  - login;
 *  - logout.
 * @package plugin.Controller
 */
class UsersController extends CakeLdapAppController
{

    /**
     * The name of this controller. Controller names are plural, named after the model they manipulate.
     *
     * @var string
     * @link http://book.cakephp.org/2.0/en/controllers.html#controller-attributes
     */
    public $name = 'Users';

    /**
     * An array containing the class names of models this controller uses.
     *
     * Example: `public $uses = array('Product', 'Post', 'Comment');`
     *
     * Can be set to several values to express different options:
     *
     * - `true` Use the default inflected model name.
     * - `array()` Use only models defined in the parent class.
     * - `false` Use no models at all, do not merge with parent class either.
     * - `array('Post', 'Comment')` Use only the Post and Comment models. Models
     *   Will also be merged with the parent class.
     *
     * The default value is `true`.
     *
     * @var mixed
     * @link http://book.cakephp.org/2.0/en/controllers.html#components-helpers-and-uses
     */
    public $uses = ['CakeLdap.User'];

    /**
     * The name of the layout file to render the view inside of. The name specified
     * is the filename of the layout in /app/View/Layouts without the .ctp
     * extension.
     *
     * @var string
     */
    public $layout = 'CakeLdap.login';

    /**
     * Called before the controller action. You can use this method to configure and customize components
     * or perform logic that needs to happen before each controller action.
     *
     * Actions:
     *  - Configure Auth component;
     *
     * @return void
     * @link http://book.cakephp.org/2.0/en/controllers.html#request-life-cycle-callbacks
     */
    public function beforeFilter()
    {
        if ($this->Auth->loggedIn()) {
            return parent::beforeFilter();
        }

        Configure::write('debug', 0);
        $this->Auth->authError = false;
        $key = 'auth';
        if (isset($this->Auth->flash['key']) && !empty($this->Auth->flash['key'])) {
            $key = $this->Auth->flash['key'];
        }
        if ($this->Session->check('Message.' . $key)) {
            $this->Session->delete('Message.' . $key);
        }

        parent::beforeFilter();
    }

    /**
     * Action `login`. Used to login user.
     *
     * @return void
     */
    public function login()
    {
        $externalAuth = false;
        if (isset($this->Auth->authenticate['CakeLdap.Ldap']['externalAuth'])) {
            $externalAuth = $this->Auth->authenticate['CakeLdap.Ldap']['externalAuth'];
        }

        if ($this->request->is('post') || ($externalAuth === true)) {
            if ($this->Auth->login()) {
                return $this->redirect($this->Auth->redirectUrl());
            }
            $this->Flash->error(__d('cake_ldap', 'Invalid username or password, try again'));
        }
    }

    /**
     * Action `logout`. Used to logout user.
     *
     * @return void
     */
    public function logout()
    {
        $this->redirect($this->Auth->logout());
    }
}
