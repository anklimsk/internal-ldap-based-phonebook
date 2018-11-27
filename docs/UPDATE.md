# Update phonebook

1. Make backup the following files:
- `app/Config/config.php`;
- `app/Config/core.php`;
- `app/Config/database.php`;
- `app/Config/cakeldap.php` (if changed).
2. Install new phonebook using composer:
  `composer create-project anklimsk/internal-ldap-based-phonebook /path/to/phonebook --stability beta`.
3. Restore from backup files to path `/path/to/phonebook/app/Config`.
4. Navigate to the directory `app` application (/path/to/phonebook/app),
  and run the following command: `sudo ./Console/cake CakeInstaller install`
  for re-install phonebook.
