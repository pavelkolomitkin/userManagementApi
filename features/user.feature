Feature: Test user and groups management

  Scenario: Create user group
    Given I create user group with name "General"
    Then I should have group with name "General"
    Given I get group list
    Then I should see in group list group with name "General"

  Scenario: Modify user group
    Given I create user group with name "General"
    Then I should have group with name "General"
    Given I modify group to "General1"
    Given I get group list
    Then I should see in group list group with name "General1"

  Scenario: Create user in group
    Given I create user group with name "General"
    Then I create user with email "test@test.com", first name "Test", last name "Testov" and status "active" and add them to group
    Then I should have user with email "test@test.com", first name "Test", last name "Testov" and status "active"
    Given I get user list
    Then I should see in user list user with email "test@test.com"

  Scenario: Modify user info
    Given I create user group with name "General"
    Then I create user with email "test@test.com", first name "Test", last name "Testov" and status "active" and add them to group
    Then I should have user with email "test@test.com", first name "Test", last name "Testov" and status "active"
    Then I modify user with email "test@test.com" to first name "Other", last name "Otherov" and status "disable"
    Then I should have user with email "test@test.com", first name "Other", last name "Otherov" and status "disable"

  Scenario: Add and remove user from second group
    Given I create user group with name "General"
    Given I create user group with name "Second"
    Then I create user with email "test@test.com", first name "Test", last name "Testov" and status "active" and add them to group
    Then I add user to group
    Then I should see "OK"
    Given I get user list by group
    Then I should see in user list user with email "test@test.com"
    Given I remove user from group
    Then I should see "OK"
    Given I get user list by group
    Then I should not see user with email "test@test.com" in user list
