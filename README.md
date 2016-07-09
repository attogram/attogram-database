# Attogram Framework Database Module v0.0.1

[![Build Status](https://travis-ci.org/attogram/attogram-database.svg?branch=master)](https://travis-ci.org/attogram/attogram-database)
[![Latest Stable Version](https://poser.pugx.org/attogram/attogram-database/v/stable)](https://packagist.org/packages/attogram/attogram-database)
[![Latest Unstable Version](https://poser.pugx.org/attogram/attogram-database/v/unstable)](https://packagist.org/packages/attogram/attogram-database)
[![Total Downloads](https://poser.pugx.org/attogram/attogram-database/downloads)](https://packagist.org/packages/attogram/attogram-database)
[![License](https://poser.pugx.org/attogram/attogram-database/license)](https://github.com/attogram/attogram-database/blob/master/LICENSE.md)
[![Code Climate](https://codeclimate.com/github/attogram/attogram-database/badges/gpa.svg)](https://codeclimate.com/github/attogram/attogram-database)
[![Issue Count](https://codeclimate.com/github/attogram/attogram-database/badges/issue_count.svg)](https://codeclimate.com/github/attogram/attogram-database)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/504e180dee5e460db61335319b5de859)](https://www.codacy.com/app/attogram-project/attogram-database?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=attogram/attogram-database&amp;utm_campaign=Badge_Grade)
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
 * `admin_actions/events.php` - Event log viewer

* Configurations:
 * `configs/database_config.php` - config for db file

* Includes:
 * `includes/attogram_database.php` - Attogram database interface
 * `includes/sqlite_database.php` - SQLite helper Attogram database object

* Database Tables:
 * `tables/event.sql` - Event log table  

 * Misc:
  * `tests/` - phpunit tests
