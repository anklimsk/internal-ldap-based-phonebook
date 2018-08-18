<?php
/**
 * This file is the controller file of the plugin.
 * Process search information request and show result
 *
 * CakeSearchInfo: Search information in project database
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.Controller
 */

App::uses('CakeSearchInfoAppController', 'CakeSearchInfo.Controller');

/**
 * The controller is used for process search request and show
 *  result of search.
 *
 * @package plugin.Controller
 */
class SearchController extends CakeSearchInfoAppController
{

    /**
     * The name of this controller. Controller names are plural, named after the model they manipulate.
     *
     * @var string
     * @link http://book.cakephp.org/2.0/en/controllers.html#controller-attributes
     */
    public $name = 'Search';

    /**
     * Array containing the names of components this controller uses. Component names
     * should not contain the "Component" portion of the class name.
     *
     * Example: `public $components = array('Session', 'RequestHandler', 'Acl');`
     *
     * @var array
     * @link http://book.cakephp.org/2.0/en/controllers/components.html
     */
    public $components = [
        'Paginator',
        'CakeSearchInfo.SearchFilter'
    ];

    /**
     * An array containing the names of helpers this controller uses. The array elements should
     * not contain the "Helper" part of the class name.
     *
     * Example: `public $helpers = array('Html', 'Js', 'Time', 'Ajax');`
     *
     * @var mixed
     * @link http://book.cakephp.org/2.0/en/controllers.html#components-helpers-and-uses
     */
    public $helpers = [
        'Text',
        'Number',
        'Paginator',
        'CakeSearchInfo.Search',
        'CakeTheme.ViewExtension',
    ];

    /**
     * Settings for component 'Paginator'
     *
     * @var array
     */
    public $paginate = [
        'page' => 1,
        'limit' => 10,
        'maxLimit' => 250,
    ];

    /**
     * Called before the controller action. You can use this method to configure and customize components
     * or perform logic that needs to happen before each controller action.
     *
     * Actions:
     *  - Configure components;
     *
     * @return void
     * @link http://book.cakephp.org/2.0/en/controllers.html#request-life-cycle-callbacks
     */
    public function beforeFilter()
    {
        $this->Auth->allow('autocomplete');
        $this->Security->unlockedActions = ['autocomplete'];

        parent::beforeFilter();
    }

    /**
     * Action `index`. Used to begin search.
     *
     * @return void
     */
    public function index()
    {
        $search_urlActionSearch = null;
        $this->set(compact('search_urlActionSearch'));
    }

    /**
     * Action `search`. Used to view a result of search.
     *
     * @return void
     */
    public function search()
    {
        $this->SearchFilter->search();
    }

    /**
     * Action `autocomplete`. Is used to autocomplte input fields.
     *
     * @return void
     */
    public function autocomplete()
    {
        $this->SearchFilter->autocomplete();
    }
}
