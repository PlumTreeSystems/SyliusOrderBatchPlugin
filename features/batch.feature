@simply @batch @simply_selenium
Feature: batch must contain all order functionality except batch creation which is in orders list.

  Background:
    Given the store operates on a channel identified by non-lowercase "C_STORE" code
    And the store has a customer group "customer"
    And the store has a product "T-shirt banana" priced at "$30.54" on the "C_STORE" channel
    And the store has a product "T-shirt apple" priced at "$200.54" on the "C_STORE" channel
    And the store has a product "T-shirt car" priced at "$450.00" on the "C_STORE" channel
    And the store has a user, with role "customer", with name "Joffrey Baratheon", with email "joffreybaratheon@mailinator.com" and with "1234" password
    And this customer has placed an order "000123" buying a single "T-shirt banana" product for "$30.54" on the "C_STORE" channel with paid state
    And this customer has placed an order "000102" buying a single "T-shirt apple" product for "$200.54" on the "C_STORE" channel with paid state
    And this customer has placed an order "000023" buying a single "T-shirt car" product for "$450.00" on the "C_STORE" channel with paid state
    And Store has filter "Number contains 3" for "Number" selected "contains" with value "3"
    And Store has filter "Number contains 1" for "Number" selected "contains" with value "1"
    And Store has filter "Number contains 10" for "Number" selected "contains" with value "10"
    And Store has filter "Number contains 123" for "Number" selected "contains" with value "123"
    And there is an administrator "admin@sylius.com" identified by "123456789"
    When I go to "/admin"
    And I fill in "Username" with "admin@sylius.com"
    And I fill in "Password" with "123456789"
    And I press "Login"

  @ui @mink:chrome
  Scenario: Create batch, adds more orders and check if it contains correct orders.
    When I go to "/admin"
    And I follow "Orders"
    Then I should see "Filter"
    When I search for a "Number contains 10" filter
    And I select first option from autocomplete
    And I press "Filter"
    Then I should not see "000123"
    And I should see "000102"
    And I should not see "000023"
    When I press "Save batch"
    And I capture a screenshot
    And I fill in "Batch name" with "New batch 123"
    And I capture a screenshot
    And I press save in new batch modal
    Then I should see "New batch 123"
    And I should not see "000123"
    And I should see "000102"
    And I should not see "000023"
    When I follow "Orders"
    And I search for a "Number contains 123" filter
    And I select first option from autocomplete
    And I press "Filter"
    And I press "Save batch"
    And I fill in "Batch name" with "New batch 123"
    And I press save in new batch modal
    Then I should see "New batch 123"
    And I should see "000123"
    And I should see "000102"
    And I should not see "000023"
    When I follow "Orders"
    And I press "Save batch"
    And I fill in "Batch name" with "New batch 123"
    And I press save in new batch modal
    Then I should see "New batch 123"
    And I should see "000123"
    And I should see "000102"
    And I should see "000023"

  @ui @mink:chrome
  Scenario: Try to create batch without name
    When I follow "Orders"
    Then I should see "Filter"
    When I search for a "Number contains 1" filter
    And I select first option from autocomplete
    And I press "Filter"
    Then I should see "000123"
    And I should see "000102"
    And I should not see "000023"
    When I press "Save batch"
    And I press save in new batch modal
    Then I should see "Batch name can't be blank"

  @ui @mink:chrome
  Scenario: Creates new filter in batch and check if filters were applied.
    Given There is a batch named "Some batch" containing orders "000123, 000102"
    When I follow "Other batches"
    And I follow "Show"
    Then I should see "Some batch"
    And I should see "000123"
    And I should see "000102"
    When I fill in "Total | Greater than" with "300"
    And I press "Save filter"
    And I fill in "Filter name" with "Total more than 55"
    And I press "Save"
    Then I should see "Some batch"
    And I should not see "000123"
    And I should not see "000102"
    And I should see "Total more than 55"
    When I remove filter
    And I press "Filter"
    Then I should see "000123"
    And I should see "000102"
    And I should not see "Total more than 55"
    When I search for a "Total" filter
    And I select first option from autocomplete
    Then I should see "Total more than 55"
    And I press "Filter"
    Then I should not see "000123"
    And I should not see "000102"
    And I should see "Total more than 55"

  @ui @mink:chrome
  Scenario: Apply filter to batch and check if results were filtered.
    Given There is a batch named "Some batch" containing orders "000123, 000102"
    When I follow "Other batches"
    And I follow "Show"
    Then I should see "000123"
    And I should see "000102"
    When I search for a "Number contains 1" filter
    And I select first option from autocomplete
    And I search for a "Number contains 3" filter
    And I select first option from autocomplete
    Then I should see "Number contains 1"
    And I should see "Number contains 3"
    When I press "Filter"
    Then I should see "000123"
    And I should not see "000102"

  @ui @mink:chrome
  Scenario: Remove order from batch
    Given There is a batch named "Some batch" containing orders "000123, 000102"
    When I follow "Other batches"
    And I follow "Show"
    Then I should see "000123"
    And I should see "000102"
    When I press "Remove"
    And I follow "Proceed"
    Then I should not see "000123"
    And I should see "000102"

  @ui @mink:chrome
  Scenario: Go from batch to order and back using back button
    Given There is a batch named "Some batch" containing orders "000123, 000102"
    When I follow "Other batches"
    And I follow "Show"
    And I follow "Show"
    Then I should see "Order #000123"
    When move backward one page
    Then I should see "#000123"