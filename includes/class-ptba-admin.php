<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PTBA_Admin {

    private $option_key = 'ptba_settings';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ) );
    }

    public function admin_footer_text( $text ) {
        $screen = get_current_screen();

        if ( $screen && 'settings_page_ptba-aftercare' === $screen->id ) {
            return 'Built by <a href="https://primitivetattoo.com" target="_blank">Primitive Tattoo Bali</a> | Thank you for creating with WordPress.';
        }

        return $text;
    }

    public static function get_defaults() {
        return array(
            // Studio info
            'studio_name'    => '',
            'studio_phone'   => '',
            'studio_email'   => '',
            'emergency_note' => 'If you notice excessive redness, swelling, pus, or fever, contact your tattoo artist or seek medical attention immediately.',

            // Display options
            'show_tracker'   => 1,
            'show_print'     => 1,
            'accent_color'   => '#C9A227',

            // Aftercare phases
            'phases' => array(
                array(
                    'title'    => 'First 2-4 Hours',
                    'icon'     => '🩹',
                    'day_start' => 0,
                    'day_end'   => 0,
                    'instructions' => array(
                        'Leave the bandage/wrap on for 2-4 hours (or as advised by your artist)',
                        'Wash your hands thoroughly before touching the tattoo',
                        'Gently remove the wrap and wash with lukewarm water and fragrance-free soap',
                        'Pat dry with a clean paper towel — never use cloth towels',
                        'Let it air dry for 15-20 minutes before applying anything',
                    ),
                ),
                array(
                    'title'    => 'Days 1-3',
                    'icon'     => '🧼',
                    'day_start' => 1,
                    'day_end'   => 3,
                    'instructions' => array(
                        'Wash the tattoo 2-3 times daily with lukewarm water and fragrance-free soap',
                        'Apply a thin layer of recommended aftercare ointment (Aquaphor, Hustle Butter, or similar)',
                        'Do NOT re-wrap the tattoo unless your artist specifically advises it',
                        'Wear loose, breathable clothing over the tattoo',
                        'Avoid sleeping directly on the tattoo — use clean sheets',
                    ),
                ),
                array(
                    'title'    => 'Days 4-14',
                    'icon'     => '✨',
                    'day_start' => 4,
                    'day_end'   => 14,
                    'instructions' => array(
                        'Continue washing 2x daily and moisturizing with fragrance-free lotion',
                        'The tattoo will start to peel and flake — this is completely normal',
                        'DO NOT pick, scratch, or peel flaking skin — let it shed naturally',
                        'Itching is normal — lightly slap or tap the area instead of scratching',
                        'Avoid swimming, baths, saunas, and hot tubs',
                        'Keep the tattoo out of direct sunlight',
                    ),
                ),
                array(
                    'title'    => 'Weeks 2-4',
                    'icon'     => '🌿',
                    'day_start' => 15,
                    'day_end'   => 28,
                    'instructions' => array(
                        'Continue moisturizing daily with fragrance-free lotion',
                        'The tattoo may look cloudy or dull — this is the deeper skin healing underneath',
                        'You can resume light exercise but keep the tattoo clean and dry afterward',
                        'Still avoid prolonged sun exposure and swimming',
                        'The outer skin has healed but deeper layers take 2-3 months',
                    ),
                ),
                array(
                    'title'    => 'Long-Term Care',
                    'icon'     => '☀️',
                    'day_start' => 29,
                    'day_end'   => 999,
                    'instructions' => array(
                        'Always apply SPF 30+ sunscreen to your tattoo when exposed to sun',
                        'UV rays are the #1 cause of tattoo fading — protect your investment',
                        'Keep skin moisturized for long-term vibrancy',
                        'Touch-ups are normal — contact your artist if lines need refreshing after full healing',
                    ),
                ),
            ),
        );
    }

    public static function get_settings() {
        $saved    = get_option( 'ptba_settings', array() );
        $defaults = self::get_defaults();
        $merged   = wp_parse_args( $saved, $defaults );

        // Deep merge: if saved phases have empty instructions, restore defaults
        if ( ! empty( $merged['phases'] ) ) {
            foreach ( $merged['phases'] as $i => $phase ) {
                if ( empty( $phase['instructions'] ) && isset( $defaults['phases'][ $i ] ) ) {
                    $merged['phases'][ $i ]['instructions'] = $defaults['phases'][ $i ]['instructions'];
                }
            }
        }

        return $merged;
    }

    public function add_menu() {
        add_options_page(
            __( 'Tattoo Aftercare', 'tattoo-aftercare' ),
            __( 'Tattoo Aftercare', 'tattoo-aftercare' ),
            'manage_options',
            'ptba-aftercare',
            array( $this, 'render_page' )
        );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( 'settings_page_ptba-aftercare' !== $hook ) {
            return;
        }
        wp_enqueue_style( 'ptba-admin', PTBA_URL . 'admin/admin.css', array(), PTBA_VERSION );
    }

    public function register_settings() {
        register_setting( 'ptba_settings_group', $this->option_key, array( $this, 'sanitize_settings' ) );
    }

    public function sanitize_settings( $input ) {
        $clean = array();

        $clean['studio_name']    = sanitize_text_field( wp_unslash( $input['studio_name'] ?? '' ) );
        $clean['studio_phone']   = sanitize_text_field( wp_unslash( $input['studio_phone'] ?? '' ) );
        $clean['studio_email']   = sanitize_email( wp_unslash( $input['studio_email'] ?? '' ) );
        $clean['emergency_note'] = sanitize_textarea_field( wp_unslash( $input['emergency_note'] ?? '' ) );
        $clean['show_tracker']   = ! empty( $input['show_tracker'] ) ? 1 : 0;
        $clean['show_print']     = ! empty( $input['show_print'] ) ? 1 : 0;
        $clean['accent_color']   = sanitize_hex_color( $input['accent_color'] ?? '#C9A227' ) ?: '#C9A227';

        $clean['phases'] = array();
        if ( ! empty( $input['phases'] ) && is_array( $input['phases'] ) ) {
            foreach ( $input['phases'] as $phase ) {
                $instructions = array();
                if ( ! empty( $phase['instructions'] ) ) {
                    $lines = explode( "\n", sanitize_textarea_field( wp_unslash( $phase['instructions'] ) ) );
                    foreach ( $lines as $line ) {
                        $line = trim( $line );
                        if ( '' !== $line ) {
                            $instructions[] = $line;
                        }
                    }
                }
                $clean['phases'][] = array(
                    'title'        => sanitize_text_field( wp_unslash( $phase['title'] ?? '' ) ),
                    'icon'         => sanitize_text_field( wp_unslash( $phase['icon'] ?? '' ) ),
                    'day_start'    => intval( $phase['day_start'] ?? 0 ),
                    'day_end'      => intval( $phase['day_end'] ?? 0 ),
                    'instructions' => $instructions,
                );
            }
        }

        return $clean;
    }

    public function render_page() {
        $s = self::get_settings();
        ?>
        <div class="wrap ptba-admin-wrap">
            <h1><?php esc_html_e( 'Tattoo Aftercare Settings', 'tattoo-aftercare' ); ?></h1>
            <p class="description"><?php esc_html_e( 'Configure aftercare phases and studio info for the [tattoo_aftercare] shortcode.', 'tattoo-aftercare' ); ?></p>

            <form method="post" action="options.php">
                <?php settings_fields( 'ptba_settings_group' ); ?>

                <!-- Studio Info -->
                <div class="ptba-admin-card">
                    <h2><?php esc_html_e( 'Studio Information', 'tattoo-aftercare' ); ?></h2>
                    <p class="description" style="margin-bottom: 8px;"><?php esc_html_e( 'Shown at the bottom of the aftercare guide so clients can reach you.', 'tattoo-aftercare' ); ?></p>
                    <table class="form-table">
                        <tr>
                            <th><label for="ptba_studio_name"><?php esc_html_e( 'Studio Name', 'tattoo-aftercare' ); ?></label></th>
                            <td><input type="text" id="ptba_studio_name" name="ptba_settings[studio_name]" value="<?php echo esc_attr( $s['studio_name'] ); ?>" class="regular-text" placeholder="Your Tattoo Studio"></td>
                        </tr>
                        <tr>
                            <th><label for="ptba_studio_phone"><?php esc_html_e( 'Phone / WhatsApp', 'tattoo-aftercare' ); ?></label></th>
                            <td><input type="text" id="ptba_studio_phone" name="ptba_settings[studio_phone]" value="<?php echo esc_attr( $s['studio_phone'] ); ?>" class="regular-text" placeholder="+1 234 567 8900"></td>
                        </tr>
                        <tr>
                            <th><label for="ptba_studio_email"><?php esc_html_e( 'Email', 'tattoo-aftercare' ); ?></label></th>
                            <td><input type="email" id="ptba_studio_email" name="ptba_settings[studio_email]" value="<?php echo esc_attr( $s['studio_email'] ); ?>" class="regular-text" placeholder="info@yourstudio.com"></td>
                        </tr>
                        <tr>
                            <th><label for="ptba_emergency_note"><?php esc_html_e( 'Emergency Note', 'tattoo-aftercare' ); ?></label></th>
                            <td>
                                <textarea id="ptba_emergency_note" name="ptba_settings[emergency_note]" rows="3" class="large-text"><?php echo esc_textarea( $s['emergency_note'] ); ?></textarea>
                                <p class="description"><?php esc_html_e( 'Warning message shown at the bottom about signs of infection.', 'tattoo-aftercare' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Display Options -->
                <div class="ptba-admin-card">
                    <h2><?php esc_html_e( 'Display Options', 'tattoo-aftercare' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e( 'Day Tracker', 'tattoo-aftercare' ); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="ptba_settings[show_tracker]" value="1" <?php checked( $s['show_tracker'], 1 ); ?>>
                                    <?php esc_html_e( 'Show "When did you get your tattoo?" date picker with personalized day count', 'tattoo-aftercare' ); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Print Button', 'tattoo-aftercare' ); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="ptba_settings[show_print]" value="1" <?php checked( $s['show_print'], 1 ); ?>>
                                    <?php esc_html_e( 'Show "Print Instructions" button', 'tattoo-aftercare' ); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="ptba_accent_color"><?php esc_html_e( 'Accent Color', 'tattoo-aftercare' ); ?></label></th>
                            <td>
                                <input type="color" id="ptba_accent_color" name="ptba_settings[accent_color]" value="<?php echo esc_attr( $s['accent_color'] ); ?>">
                                <code><?php echo esc_html( $s['accent_color'] ); ?></code>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Aftercare Phases -->
                <div class="ptba-admin-card">
                    <h2><?php esc_html_e( 'Aftercare Phases', 'tattoo-aftercare' ); ?></h2>
                    <p class="description" style="margin-bottom: 12px;"><?php esc_html_e( 'Each phase shows as a step in the timeline. Instructions are one per line.', 'tattoo-aftercare' ); ?></p>

                    <?php foreach ( $s['phases'] as $i => $phase ) : ?>
                    <div class="ptba-phase-card">
                        <div class="ptba-phase-header">
                            <input type="text" name="ptba_settings[phases][<?php echo intval( $i ); ?>][icon]" value="<?php echo esc_attr( $phase['icon'] ); ?>" class="ptba-input-icon" title="<?php esc_attr_e( 'Emoji icon', 'tattoo-aftercare' ); ?>">
                            <input type="text" name="ptba_settings[phases][<?php echo intval( $i ); ?>][title]" value="<?php echo esc_attr( $phase['title'] ); ?>" class="ptba-input-title" placeholder="<?php esc_attr_e( 'Phase title', 'tattoo-aftercare' ); ?>">
                            <label class="ptba-day-range">
                                <?php esc_html_e( 'Days:', 'tattoo-aftercare' ); ?>
                                <input type="number" name="ptba_settings[phases][<?php echo intval( $i ); ?>][day_start]" value="<?php echo intval( $phase['day_start'] ); ?>" min="0" class="ptba-input-day">
                                &ndash;
                                <input type="number" name="ptba_settings[phases][<?php echo intval( $i ); ?>][day_end]" value="<?php echo intval( $phase['day_end'] ); ?>" min="0" class="ptba-input-day">
                            </label>
                        </div>
                        <textarea name="ptba_settings[phases][<?php echo intval( $i ); ?>][instructions]" rows="5" class="large-text ptba-instructions" placeholder="<?php esc_attr_e( 'Enter one instruction per line, e.g.:', 'tattoo-aftercare' ); ?>&#10;<?php esc_attr_e( 'Wash gently with lukewarm water and fragrance-free soap', 'tattoo-aftercare' ); ?>&#10;<?php esc_attr_e( 'Pat dry with a clean paper towel', 'tattoo-aftercare' ); ?>&#10;<?php esc_attr_e( 'Apply a thin layer of aftercare ointment', 'tattoo-aftercare' ); ?>"><?php echo esc_textarea( implode( "\n", $phase['instructions'] ) ); ?></textarea>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php submit_button( __( 'Save Settings', 'tattoo-aftercare' ) ); ?>
            </form>
        </div>
        <?php
    }
}
