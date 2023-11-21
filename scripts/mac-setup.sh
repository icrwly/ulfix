#!/bin/bash

if [ ! -d docroot/themes/custom/ul-com-theme ] ; then 
  git clone git@github.com:ULwebdev/ul-com-theme.git docroot/themes/custom/ul-com-theme 
  fi 
cp -n .docksal/example.docksal-local.env .docksal/docksal-local.env
cp -n .docksal/example.local.blt.yml .docksal/local.blt.yml
cp -n docroot/sites/default/settings/default.local.settings.php docroot/sites/default/settings/local.settings.php
cp -n docroot/sites/ul_base_profile/settings/default.local.settings.php docroot/sites/ul_base_profile/settings/local.settings.php
cp -n docroot/sites/ul_enterprise_profile/settings/default.local.settings.php docroot/sites/ul_enterprise_profile/settings/local.settings.php
cp -n docroot/sites/ul_guidelines_profile/settings/default.local.settings.php docroot/sites/ul_guidelines_profile/settings/local.settings.php
echo 'please update .docksal/docksal-local.env with your username and API key from https://www.dev-ulacsf.acsitefactory.com'
echo 'local file setup complete. Update the above-mentioned file, and you may next run: fin system start && fin init'
