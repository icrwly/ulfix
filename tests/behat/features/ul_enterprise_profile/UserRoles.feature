Feature: User Role Tests
  Different roles have different access permissions.
  Testing the access permissions for different content types and pages.

  # View content overview page.
 @api @ulenterprise
 Scenario Outline: Make sure that the right roles can view the content overview and workbench page.
   Given I am logged in as a user with the <role> role
   And I visit "admin/cm-workbench"
   Then I should get a 200 HTTP response

   Examples:
     | role             |
     | "Content Approver" |
     | "Content Author" |
     | "Site Manager" |


  @api @ulenterprise
  Scenario Outline: Make sure that the right roles can view the content overview and workbench page.
    Given I am logged in as a user with the <role> role
    And I visit "admin/content"
    Then I should get a 200 HTTP response
    And I should see the link "Add content"

    Examples:
      | role             |
      | "Content Author" |
      | "Site Manager" |


  @api @ulenterprise
  Scenario Outline: Make sure that the right roles can create Pages, Events, Articles, Offerings, Notices and Tools.
    Given I am logged in as a user with the <role> role
    And I visit <path>
    Then I should get a 200 HTTP response
    And I should see the button "Save and Create New Draft"
    And I should see the button "Save and Request Review"
    And I should not see the button "Save and Publish"

    Examples:
      | role             | path                |
      | "Content Author" | "node/add/page"     |
      | "Content Author" | "node/add/event"    |
      | "Content Author" | "node/add/offering" |

# This feature is OK for behat test.
