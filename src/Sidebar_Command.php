<?php

use WP_CLI\Utils;
use WP_CLI\Formatter;

/**
 * Lists registered sidebars.
 *
 * A [sidebar](https://developer.wordpress.org/themes/functionality/sidebars/) is any widgetized area of your theme.
 *
 * ## EXAMPLES
 *
 *     # List sidebars
 *     $ wp sidebar list --fields=name,id --format=csv
 *     name,id
 *     "Widget Area",sidebar-1
 *     "Inactive Widgets",wp_inactive_widgets
 */
class Sidebar_Command extends WP_CLI_Command {

	/**
	 * @var string[]
	 */
	private $fields = [
		'name',
		'id',
		'description',
	];

	/**
	 * Lists registered sidebars.
	 *
	 * ## OPTIONS
	 *
	 * [--inactive]
	 * : If set, only inactive sidebars will be listed.
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
	 *   - ids
	 *   - count
	 *   - yaml
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
	 *     $ wp sidebar list --fields=name,id --format=csv
	 *     name,id
	 *     "Widget Area",sidebar-1
	 *     "Inactive Widgets",wp_inactive_widgets
	 *
	 *     $ wp sidebar list --inactive --fields=id --format=csv
	 *     id
	 *     old-sidebar-1
	 *
	 * @subcommand list
	 */
	public function list_( $args, $assoc_args ) {
		global $wp_registered_sidebars;

		Utils\wp_register_unused_sidebar();

		$inactive = Utils\get_flag_value( $assoc_args, 'inactive', false );

		if ( $inactive ) {
			$sidebars = [];
			foreach ( self::get_inactive_sidebar_ids() as $sidebar_id ) {
				$sidebars[ $sidebar_id ] = [
					'name'          => $sidebar_id,
					'id'            => $sidebar_id,
					'description'   => '',
					'class'         => '',
					'before_widget' => '',
					'after_widget'  => '',
					'before_title'  => '',
					'after_title'   => '',
				];
			}
		} else {
			$sidebars = $wp_registered_sidebars;
		}

		if ( ! empty( $assoc_args['format'] ) && 'ids' === $assoc_args['format'] ) {
			$sidebars = wp_list_pluck( $sidebars, 'id' );
		}

		$formatter = new Formatter( $assoc_args, $this->fields );
		$formatter->display_items( $sidebars );
	}

	/**
	 * Returns the IDs of sidebars that exist in the database but are not currently registered.
	 *
	 * @return string[]
	 */
	public static function get_inactive_sidebar_ids() {
		global $wp_registered_sidebars;

		$sidebars_widgets = get_option( 'sidebars_widgets', [] );
		if ( ! is_array( $sidebars_widgets ) ) {
			$sidebars_widgets = [];
		}
		if ( isset( $sidebars_widgets['array_version'] ) ) {
			unset( $sidebars_widgets['array_version'] );
		}

		$registered_ids = array_keys( $wp_registered_sidebars );

		return array_values(
			array_filter(
				array_diff( array_keys( $sidebars_widgets ), $registered_ids ),
				static function ( $id ) {
					return 'wp_inactive_widgets' !== $id;
				}
			)
		);
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
	 *   - csv
	 *   - json
	 *   - ids
	 *   - count
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

		Utils\wp_register_unused_sidebar();

		$id = $args[0];

		if ( ! array_key_exists( $id, $wp_registered_sidebars ) ) {
			WP_CLI::error( "Sidebar '{$id}' does not exist." );
		}

		$formatter = new Formatter( $assoc_args, $this->fields );
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

		Utils\wp_register_unused_sidebar();

		if ( array_key_exists( $args[0], $wp_registered_sidebars ) ) {
			WP_CLI::halt( 0 );
		}

		WP_CLI::halt( 1 );
	}
}
