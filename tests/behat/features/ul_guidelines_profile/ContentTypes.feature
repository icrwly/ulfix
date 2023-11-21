Feature: Content Type Tests
  As an Administrator
  I should be able to create nodes of all Content Types

# Background:
#   And I set the configuration item "simplesamlphp_auth.settings" with key "activate" to "false"

  @api @ulguidelines
  Scenario: Make sure we can create a Basic Page
    Given I am logged in as a user with the "administrator" role
    And I visit "node/add/page"
    When I fill in "Title" with "BDD TEST BASIC PAGE TITLE"
    And I fill in "Body" with "BDD TEST BASIC PAGE BODY"
    And I fill in "edit-revision-log-0-value" with "TEST REVISION LOG MESSAGE"
    And I press "Save and Publish"
    And I see the text "Basic page BDD TEST BASIC PAGE TITLE has been created"



  @api @ulguidelines
  Scenario: Make sure we can create a Guideline Page
    Given I am logged in as a user with the "administrator" role
    And I visit "node/add/guideline"
    When I fill in "Title" with "BDD TEST GUIDELINE TITLE"
    And I fill in "edit-revision-log-0-value" with "TEST REVISION LOG MESSAGE"
    And I press "Save and Publish"
    And I see the text "Guideline BDD TEST GUIDELINE TITLE has been created"

  @api @ulguidelines
  Scenario: Make sure we can create a Homepage
    Given I am logged in as a user with the "administrator" role
    And I visit "node/add/homepage"
    When I fill in "Title" with "BDD TEST HOMEPAGE TITLE"
    When I fill in "Subtitle" with "BDD TEST HOMEPAGE SUBTITLE"
    And I fill in "edit-revision-log-0-value" with "TEST REVISION LOG MESSAGE"
    And I press "Save and Publish"
    And I see the text "Homepage BDD TEST HOMEPAGE TITLE has been created"
