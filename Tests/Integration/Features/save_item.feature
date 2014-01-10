Feature: I can index entities to a Solr instance

  Scenario: I index one entity to Solr
    Given I have a Doctrine entity
    When I add this entity to solr
    Then should no error occurre
