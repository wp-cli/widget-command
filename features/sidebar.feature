Feature: Manage WordPress sidebars

  Scenario: List available sidebars
    Given a WP install
    When I run `wp theme install twentytwelve --activate`
    
    # Register sidebars for the test
    And I run `wp eval 'register_sidebar(["name" => "Main Sidebar", "id" => "sidebar-1"]); register_sidebar(["name" => "First Front Page Widget Area", "id" => "sidebar-2"]); register_sidebar(["name" => "Second Front Page Widget Area", "id" => "sidebar-3"]);'`

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
      When I run `wp theme install twentytwelve --activate`
      And I run `wp eval 'register_sidebar(["name" => "Main Sidebar", "id" => "sidebar-1"]);'`
      And I run `wp sidebar get sidebar-1`
      Then STDOUT should contain "Main Sidebar"
      And STDOUT should contain "sidebar-1"

  Scenario: Sidebar exists command returns success
    Given a WP install
    When I run `wp theme install twentytwelve --activate`
    And I run `wp eval 'register_sidebar(["name" => "Main Sidebar", "id" => "sidebar-1"]);'`
    And I run `wp sidebar exists sidebar-1`
    Then the command should succeed

  Scenario: Sidebar exists command returns failure
    Given a WP install
    When I run `wp theme install twentytwelve --activate`
    And I run `wp sidebar exists non_existing_sidebar`
    Then the command should fail

  Scenario: Get non-existing sidebar returns error
    Given a WP install
    When I run `wp theme install twentytwelve --activate`
    And I run `wp sidebar get non_existing_sidebar`
    Then the command should fail