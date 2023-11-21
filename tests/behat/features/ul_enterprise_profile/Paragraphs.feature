Feature: Add basic_content component
  In order to add Paragraph basic_content on node

  @api @ulenterprise
  Scenario Outline: A super user should be able to add basic_content
    Given I am logged in as a user with the "<role>" role
    When I go to "node/add/tool"
    Then I should see "Add Basic Content"
    Then I should see "Add Content Accordion"

    Examples:
      | role                  |
      | administrator         |

  @api @ulenterprise
  Scenario: A Basic Content component should allow you to add content in Paragraph
    Then the "paragraph" entity type "basic_content" should have field "field_basic_content_content" of type "text_long"
    Then the "paragraph" entity type "accordion" should have field "field_accrdn_heading" of type "text"
