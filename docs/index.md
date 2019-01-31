# Internal LDAP-based phonebook
[![Build Status](https://travis-ci.com/anklimsk/internal-ldap-based-phonebook.svg?branch=master)](https://travis-ci.com/anklimsk/internal-ldap-based-phonebook)
[![codecov](https://codecov.io/gh/anklimsk/internal-ldap-based-phonebook/branch/master/graph/badge.svg)](https://codecov.io/gh/anklimsk/internal-ldap-based-phonebook)
[![Latest Stable Version](https://poser.pugx.org/anklimsk/internal-ldap-based-phonebook/v/stable)](https://packagist.org/packages/anklimsk/internal-ldap-based-phonebook)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Internal LDAP-based phonebook

## This phonebook provides next features:

- Authenticating users by membership in the LDAP security group for next roles:
  * `user` - read-only access without extended fields, allows to change
    information about yourself;
  * `secretary` - read-only access include extended fields, allows to change
    information about yourself;
  * `human resources` - full access include extended fields, allows to approve
    changes to information from users;
  * `administrator` - full access include extended fields, allows to approve
    changes to information from users and manage phonebook.
- Changing user information after approval;
- Ability to change the list of extended fields and read-only fields;
- Changing the display format of the phone number:
  * `E164`;
  * `INTERNATIONAL`;
  * `NATIONAL`;
  * `RFC3966`.
- Export phonebook information in PDF and XLSX format alphabetically and by department.
  For each user role. Without breaks in table headers at the end of the page for PDF.
- Ability to change the logo of the organization on the title page PDF;
- Ability to change order of the columns in the result table;
- Ability to change label of fields, tooltip, input mask and validation rules and
  flag of truncate long text in result table;
- Ability to change the order of departments in the exported files PDF and XLSX;
- Ability to use the full and abbreviated name of the department;
- Notice of birthdays of employees;
- View employees as a subordination tree or gallery;
- Logging changes in employee information with the ability to recover;
- Resize photo up to 200x200 px on upload;
- Support for keyboard layout corrections for the Russian language;
- Customizable list of fields for synchronization;
- Synchronizing information from LDAP to database.

## Requirements

- Apache module `mod_rewrite`;
- PHP 5.4.0 or greater.

## Installation

1. Install phonebook using composer:
  `composer create-project anklimsk/internal-ldap-based-phonebook /path/to/phonebook`.
2. Copy applicaton files from `/path/to/phonebook`
  to VirtualHost document root directory, e.g.: `/var/www/phonebook`.
3. Navigate to the directory `app` application (`/var/www/phonebook/app`),
  and run the following command: `sudo ./Console/cake CakeInstaller`
  to start interactive shell of installer.
4. After the installation process is complete, in your browser go to the link
  `http://phonebook.fabrikam.com/settings` to change settings of application,
  where `http://phonebook.fabrikam.com` - base URL of installited Phonebook.
5. Fill in the fields in the `Authentication` group settings and click the `Save` button.
6. Login with user group member `Administrator` or `Human resources` and choose menu item
  `Employees` -> `Synchronizing information with LDAP server`.
  For synchronization subordinate employees repeat choose menu item.
7. To start a tour of the phonebook, select the menu item "?" on the home page.

## Using

[Using this phonebook](using.md)

## Project icon

Author: [Andy Gongea](http://www.iconarchive.com/icons/graphicrating/quartz/readme.txt)

## License

MIT License

Copyright (c) 2018 Andrey Klimov

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
