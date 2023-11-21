# This directory contains customized behat tests.

**We try to set up the Behat test for both local Env and Acquia Pipeline**

1. the default Acquia Pipeline test as "pipeline";
2. the "local" test in Docksal Develop Environment is configured in the `local.yml` file.

## 1. All Scenarios stored in `.feature` files are under the `tests/behat/features/` directory.

## 3. How to setup the local behat test?

- On the branch develop or develop-20220713

- In "tests/behat/" directory: copy the local.default.yml to local.yml (Override the old one)
- In ".docksal/" directory: copy the default.docksal-local.yml to docksal-local.yml

- fin system stop
- fin up  // Restart Docksal container to install 'selenium/standalone-chrome'
- fin composer install  // if you haven't done it recently

- fin drush @local.enterprise updb -y
- fin drush @local.enterprise cim -y
- fin drush @local.guidelines updb -y
- fin drush @local.gudelines cim -y

- Recommend to run the command to test all:
  - `fin run-tests --run-all --verbose --ignore-files`

- Only use them to debug the Scenarios on each profile:
  - `fin blt tests:behat -D behat.profile=ul_enterprise_profile`
  - `fin blt tests:behat -D behat.profile=ul_guidelines_profile`

## 4. Notice on the local Env.

  - The samlauth and ul_samlauth modules (Guidelines site) are not working on local behat test.
  - Therefore, the script `run-tests` will make a backup of DB for the Guidelines site, `guidelines-db-backup-behat-test.sql`, then, uninstall them from the website.
  - In order to make the SSO login working again, please restore this DB dump file `.sql` after the behat test is done.

## Documents
  - Behat scenarios & features, : https://behat.org/en/latest/user_guide.html
  - Acquia Automated Testing: https://docs.acquia.com/blt/developer/testing/
  - How Behat is used in Acquia projects: https://allierays.gitbook.io/drupal-project-resources/development-resources/behat/how-behat-is-used-in-acquia-projects
