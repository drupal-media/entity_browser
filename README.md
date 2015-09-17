# Entity Browser Module

[![Build Status](https://travis-ci.org/drupal-media/entity_browser.svg?branch=8.x-1.x)](https://travis-ci.org/drupal-media/entity_browser) [![Scrutinizer](https://img.shields.io/scrutinizer/g/drupal-media/entity_browser.svg)](https://scrutinizer-ci.com/g/drupal-media/entity_browser)

Provides standardized interface to list, create and select entities.

## Requirements

* Latest dev release of Drupal 8.x.

## Configuration

There is no UI to configure entity browsers ATM. In order to test this module 
you need to import yml files using drush or configuration management admin pages
(admin/config/development/configuration/single/import). 

We also provided a module that will create:
 - content type with two entity reference fields
 - two entity browsers (listing files and nodes)
 - a view that is used on nodes entity browser
 - form display configuration for entity reference fields to use entity browsers
 
In order to use this configuration for testing or to help you contribute just 
enable "Entity Browser example" module (entity_browser_example).

## Technical details

Architecture details can be found on [architecture meta-issue.](https://www.drupal.org/node/2289821).

## Maintainers
 - Janez Urevc (@slashrsm) drupal.org/user/744628
 - Primož Hmeljak (@primsi) drupal.org/user/282629
