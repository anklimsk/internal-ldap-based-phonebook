# Internal LDAP-based phonebook
[![Build Status](https://travis-ci.com/anklimsk/internal-ldap-based-phonebook.svg?branch=master)](https://travis-ci.com/anklimsk/internal-ldap-based-phonebook)
[![codecov](https://codecov.io/gh/anklimsk/internal-ldap-based-phonebook/branch/master/graph/badge.svg)](https://codecov.io/gh/anklimsk/internal-ldap-based-phonebook)
[![Latest Unstable Version](https://poser.pugx.org/anklimsk/internal-ldap-based-phonebook/v/unstable)](https://packagist.org/packages/anklimsk/internal-ldap-based-phonebook)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Internal LDAP-based phonebook

## Installation

1. Install using composer: `composer create-project anklimsk/internal-ldap-based-phonebook /var/www/phonebook`,
  where `/var/www/phonebook` - path to the document root directory.
2. Navigate to the directory `app` application, and run the following command:
  `sudo ./Console/cake CakeInstaller` - To start interactive shell of installer.
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
