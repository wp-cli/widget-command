Feature: Manage WordPress sidebars

  Scenario: List available sidebars
    Given a WP install
    When I run `wp eval '
      register_sidebar([
        "id" => "sidebar-1",
        "name" => "Main Sidebar"
      ]);
      register_sidebar([
        "id" => "sidebar-2",
        "name" => "First Front Page Widget Area"
      ]);
      register_sidebar([
        "id" => "sidebar-3",
        "name" => "Second Front Page Widget Area"
      ]);
    '`

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
    Given a WP install
    When I run `wp eval 'register_sidebar(["id"=>"sidebar-1","name"=>"Test Sidebar"]);'`
    And I run `wp sidebar get sidebar-1`
    Then STDOUT should contain:
      """
      sidebar-1
      """

  Scenario: Sidebar exists command returns success
    Given a WP install
    When I run `wp eval 'register_sidebar(["id"=>"sidebar-1","name"=>"Test Sidebar"]);'`
    And I run `wp sidebar exists sidebar-1`
    Then the return code should be 0

  Scenario: Sidebar exists command returns failure
    Given a WP install
    When I try `wp sidebar exists does-not-exist`
    Then the return code should be 1

  Scenario: Get non-existing sidebar returns error
    Given a WP install
    When I try `wp sidebar get does-not-exist`
    Then STDERR should contain:
      """
      does not exist
      """