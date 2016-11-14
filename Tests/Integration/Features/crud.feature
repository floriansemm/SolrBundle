Feature: I can index entities to a Solr instance

  Scenario: I index one entity
    Given I have a Doctrine entity
    When I add this entity to Solr
    Then should no error occur

  Scenario: I can update one entity
    Given I have a Doctrine entity
    When I add this entity to Solr
    Then should no error occur
    When I update one attribute
    And I add this entity to Solr
    Then the index should be updated

  Scenario: I can delete a entity
    Given I have a Doctrine entity
    When I add this entity to Solr
    Then should no error occur
    When I delete the entity
    Then I should not find the entity in Solr