Feature: Install Profiles
  In order to verify that profiles are working
  As a user
  I should be able to install Drupal
  With a given install profile name

  @api @ulenterprise
  Scenario: Install the UL Enterprise profile
    Given I run drush "status"
    Then drush output should contain "ul_enterprise_profile"
    # Then drush output should contain "ul_enterprise_theme"

  @api @ulguidelines
  Scenario: Install the UL Guidelines profile
    Given I run drush "status"
    Then drush output should contain "ul_guidelines_profile"
    # Then drush output should contain "ul_guidelines_profile"
