#!/bin/sh

site=$1
target_env=$2
drush_alias=$site'.dev'

# Execute a standard drush command.
drush @$drush_alias deploy

