# Internal LDAP-based phonebook
[![Build Status](https://travis-ci.com/anklimsk/internal-ldap-based-phonebook.svg?branch=master)](https://travis-ci.com/anklimsk/internal-ldap-based-phonebook)
[![codecov](https://codecov.io/gh/anklimsk/internal-ldap-based-phonebook/branch/master/graph/badge.svg)](https://codecov.io/gh/anklimsk/internal-ldap-based-phonebook)
[![Latest Unstable Version](https://poser.pugx.org/anklimsk/internal-ldap-based-phonebook/v/unstable)](https://packagist.org/packages/anklimsk/internal-ldap-based-phonebook)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Internal LDAP-based phonebook

## Installation

1. Install using composer: `composer create-project anklimsk/internal-ldap-based-phonebook /var/www/phonebook --stability beta`,
  where `/var/www/phonebook` - path to the document root directory.
2. Navigate to the directory `app` application, and run the following command:
  `sudo ./Console/cake CakeInstaller` - to start interactive shell of installer.
3. Go to the link `http://phonebook.fabrikam.com/settings` to change settings of application,
  where `http://phonebook.fabrikam.com` - base URL of installited Phonebook.
4. Fill in the fields in the `Authentication` group settings and click the `Save` button.
5. Login with user group member `Administrator` or `Human resources` and choose menu item
  `Employees` -> `Synchronizing information with LDAP server`.
  For synchronization subordinate employees repeat choose menu item.

## Project icon

**Author:** *Andy Gongea*

### Links

- http://www.iconarchive.com/show/quartz-icons-by-graphicrating/Book-phones-icon.html
- http://www.iconarchive.com/icons/graphicrating/quartz/readme.txt

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
