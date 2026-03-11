<?php
/**
 * Plugin Name: Tattoo Aftercare Instructions
 * Description: Interactive aftercare timeline with personalized day tracker and printable instructions. Use shortcode [tattoo_aftercare].
 * Version: 1.0.1
 * Author: Primitive Tattoo Bali
 * Author URI: https://primitivetattoo.com
 * Plugin URI: https://github.com/primitivetattoo/tattoo-aftercare-instructions
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tattoo-aftercare
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PTBA_VERSION', '1.0.1' );
define( 'PTBA_URL', plugin_dir_url( __FILE__ ) );
define( 'PTBA_PATH', plugin_dir_path( __FILE__ ) );

// Admin settings page
if ( is_admin() ) {
    require_once PTBA_PATH . 'includes/class-ptba-admin.php';
    new PTBA_Admin();
}

/**
 * Get merged settings.
 */
function ptba_get_settings() {
    require_once PTBA_PATH . 'includes/class-ptba-admin.php';
    return PTBA_Admin::get_settings();
}

/**
 * Enqueue frontend assets when shortcode is present.
 */
function ptba_enqueue_assets() {
    global $post;

    if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'tattoo_aftercare' ) ) {
        return;
    }

    wp_enqueue_style( 'ptba-aftercare', PTBA_URL . 'assets/aftercare.css', array(), PTBA_VERSION );
    wp_enqueue_script( 'ptba-aftercare', PTBA_URL . 'assets/aftercare.js', array(), PTBA_VERSION, true );

    $s = ptba_get_settings();
    wp_localize_script( 'ptba-aftercare', 'ptbaConfig', array(
        'phases'      => $s['phases'],
        'studioName'  => $s['studio_name'],
        'studioPhone' => $s['studio_phone'],
        'studioEmail' => $s['studio_email'],
        'emergencyNote' => $s['emergency_note'],
        'showTracker' => (bool) $s['show_tracker'],
        'showPrint'   => (bool) $s['show_print'],
        'accentColor' => $s['accent_color'],
    ) );
}
add_action( 'wp_enqueue_scripts', 'ptba_enqueue_assets' );

/**
 * Shortcode handler.
 */
function ptba_render_shortcode( $atts ) {
    return '<div id="ptba-aftercare-root" class="ptba-aftercare-wrap"></div>';
}
add_shortcode( 'tattoo_aftercare', 'ptba_render_shortcode' );
