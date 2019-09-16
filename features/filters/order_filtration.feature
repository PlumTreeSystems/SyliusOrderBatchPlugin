@simply @order_filtration @simply_simple
Feature: Order filtration

  Background:
    Given the store operates on a channel identified by non-lowercase "C_STORE" code
    And the store has a customer group "customer"
    And the store has a product "T-shirt banana" priced at "$30.54" on the "C_STORE" channel
    And the store has a product "T-shirt apple" priced at "$200.54" on the "C_STORE" channel
    And the store has a product "T-shirt car" priced at "$450.00" on the "C_STORE" channel
    And there is a customer "Ned Flanders" identified by an email "nedflanders@mailinator.com" and a password "123456"
    And this customer has placed an order "000123" buying a single "T-shirt banana" product for "$30.54" on the "C_STORE" channel with paid state
    And this customer has placed an order "000102" buying a single "T-shirt apple" product for "$200.54" on the "C_STORE" channel with paid state
    And this customer has placed an order "000023" buying a single "T-shirt car" product for "$450.00" on the "C_STORE" channel with paid state
    And Store has filter "Number contains 3" for "Number" selected "contains" with value "3"
    And Store has filter "Number contains 1" for "Number" selected "contains" with value "1"
    And I am logged in as an administrator

  @ui
  Scenario: Check customer filter contains
    Given there is a customer "Lisa Simpson" identified by an email "lisaSimpson@mailinator.com" and a password "asd123"
    And this customer has placed an order "000200" buying a single "T-shirt apple" product for "$350.54" on the "C_STORE" channel with paid state
    When I open administration dashboard
    And I follow "Orders"
    Then I should see "000123"
    And I should see "000102"
    And I should see "000023"
    And I should see "000200"
    When I select "Contains" from "Customer"
    And I fill in "criteria[customer][value]" with "Ned Fla"
    And I press "Filter"
    Then I should see "000123"
    And I should see "000102"
    And I should see "000023"
    And I should not see "000200"
    When I fill in "criteria[customer][value]" with "Ned Sim"
    And I press "Filter"
    Then I should not see "000123"
    And I should not see "000102"
    And I should not see "000023"
    And I should not see "000200"

  @ui
  Scenario: Check customer filter not contains
    Given there is a customer "Lisa Simpson" identified by an email "lisaSimpson@mailinator.com" and a password "asd123"
    And this customer has placed an order "000200" buying a single "T-shirt apple" product for "$350.54" on the "C_STORE" channel with paid state
    When I open administration dashboard
    And I follow "Orders"
    Then I should see "000123"
    And I should see "000102"
    And I should see "000023"
    And I should see "000200"
    When I select "Not contains" from "Customer"
    And I fill in "criteria[customer][value]" with "Ned Fla"
    And I press "Filter"
    Then I should not see "000123"
    And I should not see "000102"
    And I should not see "000023"
    And I should see "000200"

  @ui
  Scenario: Check customer filter equal
    Given there is a customer "Lisa Simpson" identified by an email "lisaSimpson@mailinator.com" and a password "asd123"
    And this customer has placed an order "000200" buying a single "T-shirt apple" product for "$350.54" on the "C_STORE" channel with paid state
    When I open administration dashboard
    And I follow "Orders"
    Then I should see "000123"
    And I should see "000102"
    And I should see "000023"
    And I should see "000200"
    When I select "Equal" from "Customer"
    And I fill in "criteria[customer][value]" with "Ned Flanders"
    And I press "Filter"
    Then I should see "000123"
    And I should see "000102"
    And I should see "000023"
    And I should not see "000200"
    When I fill in "criteria[customer][value]" with "Ned Simpson"
    And I press "Filter"
    Then I should not see "000123"
    And I should not see "000102"
    And I should not see "000023"
    And I should not see "000200"
    When I fill in "criteria[customer][value]" with "Ned Fla"
    And I press "Filter"
    Then I should not see "000123"
    And I should not see "000102"
    And I should not see "000023"
    And I should not see "000200"

  @ui
  Scenario: Check customer filter not equal
    Given there is a customer "Lisa Simpson" identified by an email "lisaSimpson@mailinator.com" and a password "asd123"
    And this customer has placed an order "000200" buying a single "T-shirt apple" product for "$350.54" on the "C_STORE" channel with paid state
    When I open administration dashboard
    And I follow "Orders"
    Then I should see "000123"
    And I should see "000102"
    And I should see "000023"
    And I should see "000200"
    When I select "Not equal" from "Customer"
    And I fill in "criteria[customer][value]" with "Ned Flanders"
    And I press "Filter"
    Then I should not see "000123"
    And I should not see "000102"
    And I should not see "000023"
    And I should see "000200"

  @ui
  Scenario: Check customer filter empty
    Given there is a customer "Lisa Simpson" identified by an email "lisaSimpson@mailinator.com" and a password "asd123"
    And this customer has placed an order "000200" buying a single "T-shirt apple" product for "$350.54" on the "C_STORE" channel with paid state
    When I open administration dashboard
    And I follow "Orders"
    Then I should see "000123"
    And I should see "000102"
    And I should see "000023"
    And I should see "000200"
    When I select "Empty" from "Customer"
    And I press "Filter"
    Then I should not see "000123"
    And I should not see "000102"
    And I should not see "000023"
    And I should not see "000200"

  @ui
  Scenario: Check customer filter not empty
    Given there is a customer "Lisa Simpson" identified by an email "lisaSimpson@mailinator.com" and a password "asd123"
    And this customer has placed an order "000200" buying a single "T-shirt apple" product for "$350.54" on the "C_STORE" channel with paid state
    When I open administration dashboard
    And I follow "Orders"
    Then I should see "000123"
    And I should see "000102"
    And I should see "000023"
    And I should see "000200"
    When I select "Not empty" from "Customer"
    And I press "Filter"
    Then I should see "000123"
    And I should see "000102"
    And I should see "000023"
    And I should see "000200"

  @ui
  Scenario: Check customer filter starts with
    Given there is a customer "Lisa Simpson" identified by an email "lisaSimpson@mailinator.com" and a password "asd123"
    And this customer has placed an order "000200" buying a single "T-shirt apple" product for "$350.54" on the "C_STORE" channel with paid state
    When I open administration dashboard
    And I follow "Orders"
    Then I should see "000123"
    And I should see "000102"
    And I should see "000023"
    And I should see "000200"
    When I select "Starts with" from "Customer"
    And I fill in "criteria[customer][value]" with "Ne Flan"
    And I press "Filter"
    Then I should see "000123"
    And I should see "000102"
    And I should see "000023"
    And I should not see "000200"
    When I fill in "criteria[customer][value]" with "ed rs"
    And I press "Filter"
    Then I should not see "000123"
    And I should not see "000102"
    And I should not see "000023"
    And I should not see "000200"

  @ui
  Scenario: Check customer filter ends with
    Given there is a customer "Lisa Simpson" identified by an email "lisaSimpson@mailinator.com" and a password "asd123"
    And this customer has placed an order "000200" buying a single "T-shirt apple" product for "$350.54" on the "C_STORE" channel with paid state
    When I open administration dashboard
    And I follow "Orders"
    Then I should see "000123"
    And I should see "000102"
    And I should see "000023"
    And I should see "000200"
    When I select "Ends with" from "Customer"
    And I fill in "criteria[customer][value]" with "ed rs"
    And I press "Filter"
    Then I should see "000123"
    And I should see "000102"
    And I should see "000023"
    And I should not see "000200"
    When I fill in "criteria[customer][value]" with "Ne Fla"
    And I press "Filter"
    Then I should not see "000200"
    And I should not see "000123"
    And I should not see "000102"
    And I should not see "000023"
