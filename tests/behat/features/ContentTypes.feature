# Feature: Content Type Tests
#   As an Administrator
#   I should be able to create nodes of all Content Types


# # Gung: split this global feature into the seperate profiles.


#  @api @ulenterprise
#  Scenario: Make sure we can create a Basic Page
#    Given I am logged in as a user with the "administrator" role
#    And I visit "node/add/page"
#    When I fill in "Title" with "BDD TEST BASIC PAGE TITLE"
#    And I fill in "Body" with "BDD TEST BASIC PAGE BODY"
#    And I fill in "Marketing Support Ticket Number" with "1234"
#    # And I fill in "edit-field-shared-mktg-support-ticket-0-value" with "1234"
#    And I fill in "edit-field-shared-ref-description-0-value" with "BDD TEST BASIC PAGE SUMMARY"
#    And I fill in "edit-revision-log-0-value" with "TEST REVISION LOG MESSAGE"
#    And I press "Save and Publish"
#    And I see the text "Basic page BDD TEST BASIC PAGE TITLE has been created"

# Behat does not like this test!
#  @api @ulenterprise
#  Scenario: Make sure we can create an Event Page
#    Given I am logged in as a user with the "administrator" role
#    Given "event_types" terms:
#      | name      |
#      | testEvent |
#    Given "content_domain" terms:
#      | name      |
#      | testDomain |
#    Given "timezones" terms:
#      | name      |
#      | CDT |
#    And I visit "node/add/event"
#    When I fill in "Title" with "BDD TEST EVENT TITLE"
#    And I fill in "edit-field-event-date-0-value-date" with "2022-01-01"
#    And I fill in "edit-field-event-date-0-value-time" with "12:00:00"
#    And I fill in "edit-field-event-date-0-end-value-date" with "2022-01-02"
#    And I fill in "edit-field-event-date-0-end-value-time" with "12:00:00"
#    And I select "CDT" from "Timezone"
#    And I fill in "Event Link" with "http://www.example.com"
#    And I fill in "edit-field-shared-ref-description-0-value" with "Test Description"
#    And I fill in "Marketing Support Ticket Number" with "1234"
#    And I fill in "edit-revision-log-0-value" with "TEST REVISION LOG MESSAGE"
#    And I select "testEvent" from "Event Type"
#    And I select "testDomain" from "Content Domain"
#    And I press "Save and Publish"
#    And I see the text "Event BDD TEST EVENT TITLE has been created"

  # Behat does not support required paragraph fields. Disabling the test.
  #  @api @ulenterprise
  #  Scenario: Make sure we can create a Tool
  #  Given I am logged in as a user with the "administrator" role
  #  Given "tool_types" terms:
  #    | name      |
  #    | testTool |
  #  And I visit "node/add/tool"
  #  When I fill in "Name" with "BDD TEST TOOL TITLE"
  #  When I fill in "Description" with "BDD TEST TOOL DESCRIPTION"
  #  And I fill in "edit-field-shared-ref-description-0-value" with "BDD TEST TOOL SUMMARY"
  #  And I select "testTool" from "Tool Type"
  #  And I fill in "edit-revision-log-0-value" with "TEST REVISION LOG MESSAGE"
  #  And I press "Save and Publish"
  #  And I see the text "Tool BDD TEST TOOL TITLE has been created"


  # @api @ulguidelines
  # Scenario: Make sure we can create a Basic Page
  #   Given I am logged in as a user with the "administrator" role
  #   And I visit "node/add/page"
  #   When I fill in "Title" with "BDD TEST BASIC PAGE TITLE"
  #   And I fill in "Body" with "BDD TEST BASIC PAGE BODY"
  #   And I fill in "edit-revision-log-0-value" with "TEST REVISION LOG MESSAGE"
  #   And I press "Save and Publish"
  #   And I see the text "Basic page BDD TEST BASIC PAGE TITLE has been created"

  # @api @ulguidelines
  # Scenario: Make sure we can create a Guideline Page
  #   Given I am logged in as a user with the "administrator" role
  #   And I visit "node/add/guideline"
  #   When I fill in "Title" with "BDD TEST GUIDELINE TITLE"
  #   And I fill in "edit-revision-log-0-value" with "TEST REVISION LOG MESSAGE"
  #   And I press "Save and Publish"
  #   And I see the text "Guideline BDD TEST GUIDELINE TITLE has been created"

  # @api @ulguidelines
  # Scenario: Make sure we can create a Homepage
  #   Given I am logged in as a user with the "administrator" role
  #   And I visit "node/add/homepage"
  #   When I fill in "Title" with "BDD TEST HOMEPAGE TITLE"
  #   When I fill in "Subtitle" with "BDD TEST HOMEPAGE SUBTITLE"
  #   And I fill in "edit-revision-log-0-value" with "TEST REVISION LOG MESSAGE"
  #   And I press "Save and Publish"
  #   And I see the text "Homepage BDD TEST HOMEPAGE TITLE has been created"

  # @api @ultopic
  # Scenario: Make sure we can create a Standards
  #   Given I am logged in as a user with the "administrator" role
  #   And I visit "node/add/standards"
  #   When I fill in "Title" with "BDD TEST STANDARDS TITLE"
  #   And I fill in "edit-revision-log-0-value" with "TEST REVISION LOG MESSAGE"
  #   And I press "Save and Publish"
  #   And I see the text "Standards BDD TEST STANDARDS TITLE has been created"

#  @api @ulenterprise
#  Scenario: Make sure that content hub entities are configured correctly.
#    Given I am logged in as a user with the "administrator" role
#    And I visit "admin/config/services/acquia-contenthub/configuration"
#    Then the "edit-entities-node-event-enable-index" checkbox should be checked
#    Then the "edit-entities-node-help-enable-index" checkbox should be checked
#    Then the "edit-entities-node-knowledge-enable-index" checkbox should be checked
#    Then the "edit-entities-node-news-enable-index" checkbox should be checked
#    Then the "edit-entities-node-offering-enable-index" checkbox should be checked
#    Then the "edit-entities-node-page-enable-index" checkbox should be checked
#    Then the "edit-entities-node-person-enable-index" checkbox should be checked
#    Then the "edit-entities-node-tool-enable-index" checkbox should be checked
#    Then the "edit-entities-paragraph-basic-content-enable-index" checkbox should be checked
#    Then the "edit-entities-ul-legal-hold-ul-legal-hold-enable-index" checkbox should be checked
    # Then the "edit-entities-crc-asset-crc-asset-enable-index" checkbox should be checked
