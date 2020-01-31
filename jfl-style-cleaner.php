<?php
/**
 * Style Cleaner for Elementor
 *
 * Remove all the hard coded styling of all the elements on a page.
 *
 * @copyright Copyright (C) 2020, JFLagarde - Consultant et stratège web - jflagarde@jflagarde.com
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * @wordpress-plugin
 * Plugin Name: Style Cleaner for Elementor
 * Plugin URI:  https://jflagarde.com/
 * Description: Remove all the hard coded styling of all the elements on a page.
 * Version:     0.0.1
 * Author:      Jean-François Lagarde <jflagarde@jflagarde.com>
 * Author URI:  https://jflagarde.com/
 * Text Domain: jfl-style-cleaner
 * Domain Path: /languages
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Style Cleaner for Elementor incorporates code from:
 * - Dynamic Content for Elementor, Copyright (c) 2018-2020 Ovation S.r.l., License: GPL-3.0+, https://www.dynamic.ooo/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Main Style Cleaner for Elementor Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 0.0.1
 */
final class Elementor_Test_Extension {

    /**
     * Plugin Version
     *
     * @since 0.0.1
     *
     * @var string The plugin version.
     */
    const VERSION = '0.0.1';

    /**
     * Minimum Elementor Version
     *
     * @since 0.0.1
     *
     * @var string Minimum Elementor version required to run the plugin.
     */
    const MINIMUM_ELEMENTOR_VERSION = '2.8.5';

    /**
     * Minimum PHP Version
     *
     * @since 0.0.1
     *
     * @var string Minimum PHP version required to run the plugin.
     */
    const MINIMUM_PHP_VERSION = '7.0';

    /**
     * Instance
     *
     * @since 0.0.1
     *
     * @access private
     * @static
     *
     * @var Elementor_Test_Extension The single instance of the class.
     */
    private static $_instance = null;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @since 0.0.1
     *
     * @access public
     * @static
     *
     * @return Elementor_Test_Extension An instance of the class.
     */
    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;

    }

    /**
     * Constructor
     *
     * @since 0.0.1
     *
     * @access public
     */
    public function __construct() {

        add_action( 'init', [ $this, 'i18n' ] );
        add_action( 'plugins_loaded', [ $this, 'init' ] );

    }

    /**
     * Load Textdomain
     *
     * Load plugin localization files.
     *
     * Fired by `init` action hook.
     *
     * @since 0.0.1
     *
     * @access public
     */
    public function i18n() {

        load_plugin_textdomain( 'jfl-style-cleaner' );

    }

    /**
     * Initialize the plugin
     *
     * Load the plugin only after Elementor (and other plugins) are loaded.
     * Checks for basic plugin requirements, if one check fail don't continue,
     * if all check have passed load the files required to run the plugin.
     *
     * Fired by `plugins_loaded` action hook.
     *
     * @since 0.0.1
     *
     * @access public
     */
    public function init() {

        // Check if Elementor installed and activated
        if ( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
            return;
        }

        // Check for required Elementor version
        if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
            return;
        }

        // Check for required PHP version
        if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
            return;
        }

        // Enqueue Editor Scripts
        add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ] );
    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have Elementor installed or activated.
     *
     * @since 0.0.1
     *
     * @access public
     */
    public function admin_notice_missing_main_plugin() {

        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'jfl-style-cleaner' ),
            '<strong>' . esc_html__( 'Style Cleaner for Elementor', 'jfl-style-cleaner' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'jfl-style-cleaner' ) . '</strong>'
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required Elementor version.
     *
     * @since 0.0.1
     *
     * @access public
     */
    public function admin_notice_minimum_elementor_version() {

        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'jfl-style-cleaner' ),
            '<strong>' . esc_html__( 'Style Cleaner for Elementor', 'jfl-style-cleaner' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'jfl-style-cleaner' ) . '</strong>',
             self::MINIMUM_ELEMENTOR_VERSION
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required PHP version.
     *
     * @since 0.0.1
     *
     * @access public
     */
    public function admin_notice_minimum_php_version() {

        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'jfl-style-cleaner' ),
            '<strong>' . esc_html__( 'Style Cleaner for Elementor', 'jfl-style-cleaner' ) . '</strong>',
            '<strong>' . esc_html__( 'PHP', 'jfl-style-cleaner' ) . '</strong>',
             self::MINIMUM_PHP_VERSION
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

    }


    public function enqueue_editor_scripts() {

        wp_register_script( 'jfl-style-cleaner', plugins_url( 'assets/js/jfl-style-cleaner.js', __FILE__ ), [ 'jquery' ] );
        wp_enqueue_script('jfl-style-cleaner');

    }

}

Elementor_Test_Extension::instance();