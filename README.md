Cloning the demo to your local environment
====================================

## Requirements

* [Drush][drush]
* [Mysql][mysql]

## Installation

* Copy the repo to the web root and run the following commands at the root of the repo

* ```
cp sites/default/default.settings.php sites/default/settings.php
```
* ```
chmod 777 sites/default/settings.php
```
* ```
drush si
```
* ```
drush sql-cli < ../db.sql
```
* ```
drush cim -y
```
* ```
drush upwd admin <password>
```
* ```
chmod 664 sites/default/settings.php
```

## Maintainers

* [Patrick Gohard] - patrick.gohard@canada.ca

[drush]:                    https://docs.drush.org/en/9.x/install/
[mysql]:                    https://www.drupal.org/docs/8/install/step-3-create-a-database
