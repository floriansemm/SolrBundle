Feature: I can index entities to different cores

  Scenario: Index entities to two cores
    Given I have a Doctrine entity for "core0"
    And I have a Doctrine entity for "core1"
    When I add these entities to Solr
    Then both entities should be in different cores

