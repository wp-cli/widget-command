Feature: Reset WordPress sidebars

  Background:
    Given a WP install
    And I try `wp theme delete twentytwelve --force`
    And I run `wp theme install twentytwelve --activate`
    And I try `wp widget reset --all`
    And I try `wp widget delete wp_inactive_widgets $(wp widget list wp_inactive_widgets --format=ids)`

  Scenario: Reset sidebar
    Given I run `wp widget add text sidebar-1 --title="Text"`

    When I run `wp widget list sidebar-1 --format=count`
    # The count should be non-zero (= the sidebar contains widgets)
    Then STDOUT should match /^\s*[1-9][0-9]*\s*$/

    When I run `wp widget reset sidebar-1`
    And I run `wp widget list sidebar-1 --format=count`
    Then STDOUT should be:
      """
      0
      """

    When I try `wp widget reset`
    Then STDERR should be:
      """
      Error: Please specify one or more sidebars, or use --all or --inactive.
      """

    When I try `wp widget reset sidebar-1`
    Then STDERR should be:
      """
      Warning: Sidebar 'sidebar-1' is already empty.
      """
    And STDOUT should be:
      """
      Success: Sidebar already reset.
      """
    And the return code should be 0

    When I try `wp widget reset non-existing-sidebar-id`
    Then STDERR should be:
      """
      Warning: Invalid sidebar: non-existing-sidebar-id
      Error: No sidebars reset.
      """
    And the return code should be 1

    When I run `wp widget add calendar sidebar-1 --title="Calendar"`
    Then STDOUT should not be empty

    When I run `wp widget list sidebar-1 --format=count`
    Then STDOUT should be:
      """
      1
      """

    When I run `wp widget add search sidebar-2 --title="Quick Search"`
    Then STDOUT should not be empty

    When I run `wp widget list sidebar-2 --format=count`
    # The count should be non-zero (= the sidebar contains widgets)
    Then STDOUT should match /^\s*[1-9][0-9]*\s*$/

    When I try `wp widget reset sidebar-1 sidebar-2 non-existing-sidebar-id`
    Then STDERR should be:
      """
      Warning: Invalid sidebar: non-existing-sidebar-id
      Error: Only reset 2 of 3 sidebars.
      """
    And the return code should be 1

    When I run `wp widget list sidebar-1 --format=count`
    Then STDOUT should be:
      """
      0
      """

    When I run `wp widget list sidebar-2 --format=count`
    Then STDOUT should be:
      """
      0
      """

  Scenario: Reset all sidebars
    When I run `wp widget add calendar sidebar-1 --title="Calendar"`
    Then STDOUT should not be empty
    When I run `wp widget add search sidebar-2 --title="Quick Search"`
    Then STDOUT should not be empty
    When I run `wp widget add text sidebar-3 --title="Text"`
    Then STDOUT should not be empty

    When I run `wp widget reset --all`
    Then STDOUT should be:
      """
      Sidebar 'sidebar-1' reset.
      Sidebar 'sidebar-2' reset.
      Sidebar 'sidebar-3' reset.
      Success: Reset 3 of 3 sidebars.
      """
    And the return code should be 0

    When I run `wp widget list sidebar-1 --format=count`
    Then STDOUT should be:
      """
      0
      """
    When I run `wp widget list sidebar-2 --format=count`
    Then STDOUT should be:
      """
      0
      """
    When I run `wp widget list sidebar-3 --format=count`
    Then STDOUT should be:
      """
      0
      """
    When I run `wp widget list wp_inactive_widgets --format=ids`
    Then STDOUT should contain:
      """
      calendar-1
      """
    And STDOUT should contain:
      """
      search-1
      """
    And STDOUT should contain:
      """
      text-1
      """

  Scenario: Testing movement of widgets while reset
    When I run `wp widget add calendar sidebar-2 --title="Calendar"`
    Then STDOUT should not be empty

    When I run `wp widget add search sidebar-2 --title="Quick Search"`
    Then STDOUT should not be empty

    When I run `wp widget list sidebar-2 --format=ids`
    Then STDOUT should contain:
      """
      calendar-1 search-1
      """
    When I run `wp widget list wp_inactive_widgets --format=ids`
    Then STDOUT should be empty

    When I run `wp widget reset sidebar-2`
    And I run `wp widget list sidebar-2 --format=ids`
    Then STDOUT should be empty

    When I run `wp widget list wp_inactive_widgets --format=ids`
    And STDOUT should contain:
      """
      calendar-1 search-1
      """

  Scenario: Reset inactive sidebars
    When I try `wp widget reset --inactive`
    Then STDERR should be:
      """
      Error: No inactive sidebars found.
      """
    And the return code should be 1

    When I run `wp widget add calendar sidebar-1 --title="Calendar"`
    Then STDOUT should not be empty

    # Simulate an inactive (unregistered) sidebar by moving widget data to an orphaned key
    When I run `wp eval '$w = wp_get_sidebars_widgets(); $w["orphaned-sidebar-1"] = $w["sidebar-1"]; $w["sidebar-1"] = []; update_option( "sidebars_widgets", $w );'`

    And I run `wp sidebar list --inactive --fields=id --format=ids`
    Then STDOUT should be:
      """
      orphaned-sidebar-1
      """

    When I run `wp widget reset --inactive`
    Then STDOUT should be:
      """
      Sidebar 'orphaned-sidebar-1' reset.
      Success: Reset 1 of 1 sidebars.
      """
    And the return code should be 0

    When I run `wp widget list wp_inactive_widgets --format=ids`
    Then STDOUT should contain:
      """
      calendar-1
      """
