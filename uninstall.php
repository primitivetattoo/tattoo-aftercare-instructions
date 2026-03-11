<?php
/**
 * Uninstall handler for Tattoo Aftercare Instructions.
 * Removes all plugin data from the database.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'ptba_settings' );
