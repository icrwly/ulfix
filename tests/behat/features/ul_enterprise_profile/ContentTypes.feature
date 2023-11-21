Feature: Content Type Tests
  As an Administrator
  I should be able to create nodes of all Content Types

# Gung: this scenario is good, ignore it temperoriely.
 @api @ulenterprise
 Scenario: Make sure we can create a Basic Page
   Given I am logged in as a user with the "administrator" role

   Given "content_owner" terms:
     | name      | value |
     | testOwner | 1112  |
   Given "customer_operating_unit" terms:
     | name      | value |
     | testCou   | 1113  |

   And I visit "node/add/page"
   When I fill in "Title" with "BDD TEST BASIC PAGE TITLE"
   And I fill in "Body" with "BDD TEST BASIC PAGE BODY"
   And I fill in "Marketing Support Ticket Number" with "1234"

   And I fill in "edit-field-shared-ref-description-0-value" with "BDD TEST BASIC PAGE SUMMARY"
   And I fill in "edit-revision-log-0-value" with "TEST REVISION LOG MESSAGE"

   And I select "testOwner" from "Content Owner"
   And I select "testCou" from "Customer Operating Unit (COU)"

   And I press "Save and Publish"
   And I see the text "Basic pages BDD TEST BASIC PAGE TITLE has been created"


# Gung: this scenario is good, ignore it temperoriely.
 @api @ulenterprise
 Scenario: Make sure we can create an Event Page
   Given I am logged in as a user with the "administrator" role
   Given "event_types" terms:
     | name      |
     | testEvent |
   Given "content_domain" terms:
     | name      |
     | testDomain |
   Given "timezones" terms:
     | name      |
     | CDT |

   Given "content_owner" terms:
     | name      | value |
     | testOwner | 1112  |
   Given "customer_operating_unit" terms:
     | name      | value |
     | testCou   | 1113  |

   And I visit "node/add/event"
   When I fill in "Title" with "BDD TEST EVENT TITLE"
   And I fill in "edit-field-event-date-0-value-date" with "2022-01-01"
   And I fill in "edit-field-event-date-0-value-time" with "12:00:00"
   And I fill in "edit-field-event-date-0-end-value-date" with "2022-01-02"
   And I fill in "edit-field-event-date-0-end-value-time" with "12:00:00"
   And I select "CDT" from "Timezone"
   And I fill in "Event Link" with "http://www.example.com"
  #  And I fill in "edit-field-shared-ref-description-0-value" with "Test Description"
  And I fill in "Summary" with "Test Description summary"
  #  And I fill in "Summary" with "Test Description"
   And I fill in "Marketing Support Ticket Number" with "1234"
   And I fill in "edit-revision-log-0-value" with "TEST REVISION LOG MESSAGE"

   And I select "testEvent" from "Event Type"
   And I select "testDomain" from "Content Domain"

  #  And I fill in "Content Owner" with "5251"
  #  And I fill in "Customer Operating Unit (COU)" with "4701"
   And I select "testOwner" from "Content Owner"
   And I select "testCou" from "Customer Operating Unit (COU)"

   And I press "Save and Publish"
   And I see the text "Events BDD TEST EVENT TITLE has been created"


 # Behat does not support required paragraph fields. Disabling the test.
  @api @ulenterprise
  Scenario: Make sure we can create a node of Tools and a paragraph in it
    Given I am logged in as a user with the "administrator" role
    Given "tool_types" terms:
      | name      |
      | testTool  |
    Given "content_owner" terms:
     | name      | value |
     | testOwner | 1112  |
   Given "customer_operating_unit" terms:
     | name      | value |
     | testCou   | 1113  |


    And I visit "node/add/tool"
    When I fill in "Name" with "BDD TEST TOOL TITLE"
    And I fill in "Description" with "BDD TEST TOOL DESCRIPTION"
    And I fill in "edit-field-shared-ref-description-0-value" with "BDD TEST TOOL SUMMARY"
    And I select "testTool" from "Tool Type"
    And I fill in "edit-revision-log-0-value" with "TEST REVISION LOG MESSAGE"

    And I select "testOwner" from "Content Owner"
    And I select "testCou" from "Customer Operating Unit (COU)"

    And the "input[name=field_tool_content_basic_content_add_more]" element should exist
    When I press "Add Basic Content"
    And the ".form-item-field-tool-content-0-subform-field-basic-content-content-0-value textarea.form-textarea.required" element should exist

    Then I fill in wysiwyg on field ".form-item-field-tool-content-0-subform-field-basic-content-content-0-value textarea.form-textarea.required" with "TEST Basic Content Paragraph"

    And I press "Save and Publish"
    # And I see the text "Tools BDD TEST TOOL TITLE has been created"
    And I see the text "field is required"
    And the ".messages--error" element should exist

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
#    Then the "edit-entities-crc-asset-crc-asset-enable-index" checkbox should be checked
