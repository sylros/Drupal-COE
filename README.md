Cloning the demo to your local environment
====================================

## Requirements

* [Drush][drush]
* [Mysql][mysql]

## Installation

Copy the repo to the web root and run the following commands at the root of the repo

* Create the sites folder

```
cp -R sites/default/ <docker volume>/
```

* Create the settings.php file

```
cp sites/default/default.settings.php <docker volume>/settings.php
```

* Change settings.php permission for the install process

```
chmod 777 <docker volume>/settings.php
```

* Run the site install process

```
drush si
```

* Import the database

```
drush sql-cli < ../db.sql
```

* Import the configuration changes

```
drush cim -y
```

* Set the admin password

```
drush upwd admin <password>
```

* Reset the file permission for settings.php 

```
chmod 664 sites/default/settings.php
```

## Notes

Until we have our own docker image for DCOE, you will need to install the php zip extension manually from the docker image. Run the following commands inside the PHP container and restart the docker container

```
apt-get update
docker-php-ext-install zip
```

## Maintainers

* [Patrick Gohard] - patrick.gohard@canada.ca

[drush]:                    https://docs.drush.org/en/9.x/install/
[mysql]:                    https://www.drupal.org/docs/8/install/step-3-create-a-database
