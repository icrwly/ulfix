Feature: User Role Tests
  Different roles have different access permissions.
  Testing the access permissions for different content types and pages.


  @api @ulguidelines
  Scenario Outline: Make sure Author can create a Basic Page, but cannot publish.
    Given I am logged in as a user with the <role> role
    And I visit <path>
    Then I should get a 200 HTTP response
    And I should see the button "Save and Create New Draft"
    And I should see the button "Save and Request Review"
    And I should not see the button "Save and Publish"

    Examples:
      | role             | path                 |
      | "Content Author" | "node/add/page"      |

  @api @ulguidelines
  Scenario Outline: Make sure Approver and Editor can create and publish a Basic Page
    Given I am logged in as a user with the <role> role
    And I visit <path>
    Then I should get a 200 HTTP response
    And I should see the button "Save and Create New Draft"
    And I should see the button "Save and Request Review"
    And I should see the button "Save and Publish"

    Examples:
      | role             | path                 |
      | "Content Approver" | "node/add/page" |
      | "Content Editor" | "node/add/page" |

  @api @ulguidelines
  Scenario Outline: Make sure users can add and publish Guideline content to a book.
    Given I am logged in as a user with the <role> role
    And I visit <path>
    Then I should get a 200 HTTP response
    And I should see the button "Save and Create New Draft"
    And I should see the button "Save and Request Review"
    And I should see the button "Save and Publish"

    Examples:
      | role             | path                 |
      | "Content Approver" | "node/add/guideline" |
      | "Content Editor" | "node/add/guideline" |
      | "Guidelines Approver" | "node/add/guideline" |
      | "Guidelines Editor" | "node/add/guideline" |

  @api @ulguidelines
  Scenario Outline: Make sure users can add Guideline content to a book, but not publish.
    Given I am logged in as a user with the <role> role
    And I visit <path>
    Then I should get a 200 HTTP response
    And I should see the button "Save and Create New Draft"
    And I should see the button "Save and Request Review"
    And I should not see the button "Save and Publish"

    Examples:
      | role             | path                 |
      | "Content Author" | "node/add/guideline" |
      | "Guidelines Author" | "node/add/guideline" |

  @api @ulguidelines
  Scenario Outline: Make sure users can add and publish a Guideline List.
    Given I am logged in as a user with the <role> role
    And I visit <path>
    Then I should get a 200 HTTP response
    And I should see the button "Save and Create New Draft"
    And I should see the button "Save and Request Review"
    And I should see the button "Save and Publish"

    Examples:
      | role             | path                 |
      | "Content Approver" | "node/add/guidelines_list" |
      | "Content Editor" | "node/add/guidelines_list" |
      | "Guidelines Approver" | "node/add/guidelines_list" |
      | "Guidelines Editor" | "node/add/guidelines_list" |

  @api @ulguidelines
  Scenario Outline: Make sure users can add a Guideline List, but not publish it.
    Given I am logged in as a user with the <role> role
    And I visit <path>
    Then I should get a 200 HTTP response
    And I should see the button "Save and Create New Draft"
    And I should see the button "Save and Request Review"
    And I should not see the button "Save and Publish"

    Examples:
      | role             | path                 |
      | "Content Author" | "node/add/guidelines_list" |
      | "Guidelines Author" | "node/add/guidelines_list" |

  @api @ulguidelines
  Scenario Outline: Make sure some roles cannot create homepage content.
    Given I am logged in as a user with the <role> role
    When I am on <path>
    Then I should see "Access denied"
    And I should get a 403 HTTP response

    Examples:
      | role             | path                 |
      | "Guidelines Author" | "node/add/homepage"  |
      | "Guidelines Approver" | "node/add/homepage"  |

  @api @ulguidelines
  Scenario Outline: Make sure none of the roles have access to the Book content type.
    Given I am logged in as a user with the <role> role
    And I am on "node/add"
    Then I should not see the link "Book page"

    Examples:
      | role               |
      | "Content Author"  |
      | "Content Approver" |
      | "Content Editor"  |
      | "Guidelines Author" |
      | "Guidelines Approver" |
      | "Guidelines Editor" |

  @api @ulguidelines
  Scenario Outline: Make sure users can add Guideline Author content.
    Given I am logged in as a user with the <role> role
    And I visit <path>
    Then I should get a 200 HTTP response
    And I should see the button "Save and Create New Draft"
    And I should see the button "Save and Request Review"

    Examples:
      | role             | path                 |
      | "Content Author" | "node/add/guideline_author" |
      | "Content Approver" | "node/add/guideline_author" |
      | "Content Editor" | "node/add/guideline_author" |
      | "Guidelines Author" | "node/add/guideline_author" |
      | "Guidelines Approver" | "node/add/guideline_author" |
      | "Guidelines Editor" | "node/add/guideline_author" |

@api @ulguidelines
  Scenario Outline: Make sure users can publish Guideline Author content.
    Given I am logged in as a user with the <role> role
    And I visit <path>
    Then I should get a 200 HTTP response
    And I should see the button "Save and Publish"

    Examples:
      | role             | path                 |
      | "Content Approver" | "node/add/guideline_author" |
      | "Content Editor" | "node/add/guideline_author" |
      | "Guidelines Approver" | "node/add/guideline_author" |
      | "Guidelines Editor" | "node/add/guideline_author" |
