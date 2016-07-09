# Attogram Framework Database Module v0.0.1

[![Build Status](https://travis-ci.org/attogram/attogram-database.svg?branch=master)](https://travis-ci.org/attogram/attogram-database)
[![Latest Stable Version](https://poser.pugx.org/attogram/attogram-database/v/stable)](https://packagist.org/packages/attogram/attogram-database)
[![Latest Unstable Version](https://poser.pugx.org/attogram/attogram-database/v/unstable)](https://packagist.org/packages/attogram/attogram-database)
[![Total Downloads](https://poser.pugx.org/attogram/attogram-database/downloads)](https://packagist.org/packages/attogram/attogram-database)
[![License](https://poser.pugx.org/attogram/attogram-database/license)](https://github.com/attogram/attogram-database/blob/master/LICENSE.md)
[`[CHANGELOG]`](https://github.com/attogram/attogram-database/blob/master/CHANGELOG.md)

This is the Database Module for the [Attogram Framework](https://github.com/attogram/attogram).

# Installing the Database Module
* You already installed the [Attogram Framework](https://github.com/attogram/attogram), didn't you?
* Goto the top level of your install, then run:
```
composer create-project attogram/attogram-database modules/_attogram_database
```

# Database Module contents

* Admin Actions:
 * `admin_actions/db-admin.php` - phpliteadmin database web admin
 * `admin_actions/db-tables.php` - display info about tables

* Configurations:
 * `configs\database_config.php` - config for db file

* Includes:
 * `includes\attogram_database.php` - Attogram database interface
 * `includes\sqlite_database.php` - SQLite helper Attogram database object
