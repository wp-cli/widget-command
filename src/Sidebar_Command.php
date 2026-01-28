<?php

use WP_CLI\Utils;
use WP_CLI\Formatter;

/**
 * Manage registered sidebars.
 *
 * A [sidebar](https://developer.wordpress.org/themes/functionality/sidebars/)
 * is any widgetized area of your theme.
 */
class Sidebar_Command extends WP_CLI_Command {

	/**
	 * Default fields for sidebar output.
	 *
	 * @var array
	 */
	private $default_fields = [
		'name',
		'id',
		'description',
	];

	/**
	 * Lists registered sidebars.
	 *
	 * ## OPTIONS
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific object fields.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - yaml
	 *   - ids
	 *   - count
	 * ---
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each sidebar:
	 *
	 * * name
	 * * id
	 * * description
	 *
	 * These fields are optionally available:
	 *
	 * * class
	 * * before_widget
	 * * after_widget
	 * * before_title
	 * * after_title
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp sidebar list
	 *     $ wp sidebar list --fields=name,id
	 *     $ wp sidebar list --format=ids
	 *     $ wp sidebar list --format=count
	 *
	 * @subcommand list
	 * @when after_wp_load
	 */
	public function list_( $args, $assoc_args ) {
		global $wp_registered_sidebars;

		if ( function_exists( 'wp_register_unused_sidebar' ) ) {
			Utils\wp_register_unused_sidebar();
		}

		// Filter out wp_inactive_widgets from the display
		$sidebars = array_filter(
			$wp_registered_sidebars,
			function( $sidebar ) {
				return 'wp_inactive_widgets' !== $sidebar['id'];
			}
		);

		if ( isset( $assoc_args['format'] ) && 'ids' === $assoc_args['format'] ) {
			WP_CLI::line( implode( ' ', wp_list_pluck( $sidebars, 'id' ) ) );
			return;
		}

		if ( isset( $assoc_args['format'] ) && 'count' === $assoc_args['format'] ) {
			WP_CLI::line( count( $sidebars ) );
			return;
		}

		$formatter = new Formatter( $assoc_args, $this->default_fields );
		$formatter->display_items( $sidebars );
	}

	/**
	 * Get details about a specific sidebar.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The sidebar ID.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific object fields.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp sidebar get sidebar-1
	 *     $ wp sidebar get wp_inactive_widgets --format=json
	 *
	 * @when after_wp_load
	 */
	public function get( $args, $assoc_args ) {
		global $wp_registered_sidebars;

		if ( function_exists( 'wp_register_unused_sidebar' ) ) {
			Utils\wp_register_unused_sidebar();
		}

		$id = $args[0];

		if ( ! isset( $wp_registered_sidebars[ $id ] ) ) {
			WP_CLI::error( "Sidebar '{$id}' does not exist." );
		}

		$formatter = new Formatter( $assoc_args, $this->default_fields );
		$formatter->display_item( $wp_registered_sidebars[ $id ] );
	}

	/**
	 * Check if a sidebar exists.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The sidebar ID.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp sidebar exists sidebar-1
	 *     $ wp sidebar exists wp_inactive_widgets && echo "exists"
	 *
	 * @when after_wp_load
	 */
	public function exists( $args ) {
		global $wp_registered_sidebars;

		if ( function_exists( 'wp_register_unused_sidebar' ) ) {
			Utils\wp_register_unused_sidebar();
		}

		if ( isset( $wp_registered_sidebars[ $args[0] ] ) ) {
			WP_CLI::halt( 0 );
		}

		WP_CLI::halt( 1 );
	}

	/**
	 * List widgets assigned to a sidebar.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The sidebar ID.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - yaml
	 *   - ids
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp sidebar widgets sidebar-1
	 *     $ wp sidebar widgets wp_inactive_widgets --format=ids
	 *
	 * @when after_wp_load
	 */
	public function widgets( $args, $assoc_args ) {
		global $wp_registered_sidebars;

		if ( function_exists( 'wp_register_unused_sidebar' ) ) {
			Utils\wp_register_unused_sidebar();
		}

		$id = $args[0];

		if ( ! isset( $wp_registered_sidebars[ $id ] ) ) {
			WP_CLI::error( "Sidebar '{$id}' does not exist." );
		}

		$sidebars_widgets = wp_get_sidebars_widgets();
		$widget_ids       = isset( $sidebars_widgets[ $id ] ) ? $sidebars_widgets[ $id ] : [];

		if ( empty( $widget_ids ) ) {
			WP_CLI::warning( "No widgets found in sidebar '{$id}'." );
			return;
		}

		if ( isset( $assoc_args['format'] ) && 'ids' === $assoc_args['format'] ) {
			WP_CLI::line( implode( ' ', $widget_ids ) );
			return;
		}

		$items = array_map(
			function ( $widget_id ) {
				return [ 'id' => $widget_id ];
			},
			$widget_ids
		);

		$formatter = new Formatter( $assoc_args, [ 'id' ] );
		$formatter->display_items( $items );
	}
}
