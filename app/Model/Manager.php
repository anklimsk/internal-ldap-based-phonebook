<?php
/**
 * This file is the model file of the application. Used for
 *  management managers.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.Model
 */

App::uses('Employee', 'Model');

/**
 * The model is used to obtain information about managers.
 *
 * @package app.Model
 */
class Manager extends Employee
{

    /**
     * Name of the model.
     *
     * @var string
     * @link http://book.cakephp.org/2.0/en/models/model-attributes.html#name
     */
    public $name = 'Manager';

    /**
     * Custom database table name, or null/false if no table association is desired.
     *
     * @var string
     */
    public $useTable = 'employees';
}
