<?php
/**
Plugin Name: TablePress Restrict Authors
Plugin URI: https://github.com/fisherab/tablepress-restrict-authors
Description: Table Press "Extension" that prevents users unable to edit or delete other people's posts to perform that operation on a table.
Version: 2020-07-31
Author: Steve Fisher
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class TablePress_Restrict_Authors {

    public static $instance;

    public function __construct() { 
        // Run at priority 11 to be sure to have access to TablePress::$model_table.
        add_action( 'init', array( $this, 'init_restrict_authors' ), 11 );
    }

    public function init_restrict_authors() {
        add_filter( 'tablepress_map_meta_caps', array( $this, 'check_access_rights' ), 10, 4 );
    }

    public function check_access_rights( $caps, $cap, $user_id, $args ) {
        // Accept all operations other than edit and delete table
        if ( ! in_array( $cap, ['tablepress_edit_table', 'tablepress_delete_table'], true )) return $caps;

        // With edit_other_posts permission you can edit any table
        if ($cap == 'tablepress_edit_table' and current_user_can('edit_others_posts')) return $caps;

        // With delete_other_posts permission you can delete any table
        if ($cap == 'tablepress_delete_table' and current_user_can('delete_others_posts')) return $caps;

        // Check if the user is the author
        $table = TablePress::$model_table->load($args[0], false, false);
        if ($table['author'] != $user_id) $caps[] = 'do_not_allow';

        return $caps;
    }

}

// Initialize the plugin.
TablePress_Restrict_Authors::$instance = new TablePress_Restrict_Authors();
