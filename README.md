
Drupal Module Tracker
=

This simple tool allows users to download a listing of all modules on all Drupal sites being managed and displays them within a dashboard.

## Prerequisites

* Drush should be available somewhere on the system.
* PHP extensions for either MySQL or SQLite should be installed.

## Installation

The tool can be cloned, and dependencies installed with [Composer](http://getcomposer.org).

The config file should be copied from `config/config.yml.example` to config/config.yml and then further configured. The tool defaults to using a local SQLite database but the database driver can be altered to MySQL if preferred.

Drush aliases can then be added to the config file for use.

## Usage

Basic commands create the tables and download modules to the database.

```bash
// Create the database table required for the tool.
./bin/dmt modules:createtable

// Download lists of modules from all configured Drush aliases
./bin/dmt modules:find

```

## Viewing the dashboard

Apache can be configured to point to the `app` directory. Alternatively, for slim local usage, the PHP built-in server can be used.

```bash

php -S 127.0.0.1:8080 -t app/

```
