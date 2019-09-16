@simply @order_filtration @simply_simple
Feature: Order Filters crud

  Background:
    Given the store operates on a channel identified by non-lowercase "C_STORE" code
    And Store has filter "Number contains 2" for "Number" selected "contains" with value "2"
    And Store has filter "Number contains 1" for "Number" selected "contains" with value "1"
    And I am logged in as an administrator

  @ui
  Scenario: Creates new order filter
    When I open administration dashboard
    And I follow "Order's filters"
    And I follow "Create"
    And I fill in "Filter name" with "New filter"
    And I select "Cart" from "Order state"
    And I select "Partially authorized" from "Payment state"
    And I select "Ready" from "Shipping state"
    And I press "Create"
    Then I should see "New filter"
    And I should see "Cart"
    And I should see "partially_authorized"
    And I should see "Ready"

  @ui
  Scenario: Edits order filter
    Given Store has filter "Total less than 50" for "Total less than" with value "50"
    When I open administration dashboard
    And I follow "Order's filters"
    And I follow "Edit"
    And I fill in "Total less than" with "60"
    And I press "Save changes"
    Then I should see "60"

  @ui
  Scenario: Removes filter
    Given Store has filter "Total less than 50" for "Total less than" with value "50"
    When I open administration dashboard
    And I follow "Order's filters"
    And I press "Delete"
    Then I should not see "Total less than 50"
