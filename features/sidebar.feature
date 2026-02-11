Feature: Manage WordPress sidebars
  Background:
    Given a WP install
    And I try `wp theme delete twentytwelve --force`
    And I run `wp theme install twentytwelve --activate`

  Scenario: List available sidebars
    When I run `wp sidebar list --fields=name,id`
    Then STDOUT should be a table containing rows:
      | name                          | id                  |
      | Main Sidebar                  | sidebar-1           |
      | First Front Page Widget Area  | sidebar-2           |
      | Second Front Page Widget Area | sidebar-3           |
      | Inactive Widgets              | wp_inactive_widgets |
    When I run `wp sidebar list --format=ids`
    Then STDOUT should be:
      """
      sidebar-1 sidebar-2 sidebar-3 wp_inactive_widgets
      """
    When I run `wp sidebar list --format=count`
    Then STDOUT should be:
      """
      4
      """

  Scenario: Get sidebar details
    When I run `wp sidebar get sidebar-1`
    Then STDOUT should contain:
      """
      sidebar-1
      """

  Scenario: Sidebar exists command returns success
    When I run `wp sidebar exists sidebar-1`
    Then the return code should be 0

  Scenario: Sidebar exists command returns failure
    When I try `wp sidebar exists does-not-exist`
    Then the return code should be 1

  Scenario: Get non-existing sidebar returns error
    When I try `wp sidebar get does-not-exist`
    Then STDERR should contain:
      """
      does not exist
      """
    And the return code should be 1
    