<?php
/**
 * Plugin Name: Sociate
 * Description: Custom social buttons and trending posts for SmallBusiness.com
 * Version: 0.0.1
 * Author: Chris Honiball
 * Author URI: http://chrishoniball.com
 * License: GPL2
 */

/*  Copyright 2013  Chris Honiball  (email : chris.p.honiball@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if ( ! class_exists( 'SOC_Sociate' ) ) {

    class SOC_Sociate {

        // private variables

        // begin functions

        public function __construct() {
            // load script and styles
            add_action( 'wp_enqueue_scripts', array( $this, 'load_client_scripts' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );

            // options
            add_action( 'admin_init', array( $this, 'register_sociate_options' ) );

            // Ajax functions
            // get
            add_action('wp_ajax_get_social', array( $this, 'get_social' ) );
            add_action('wp_ajax_nopriv_get_social', array( $this, 'get_social' ) );
            // update - used for admin side functionality to update specific posts
            add_action('wp_ajax_update_social', array( $this, 'update_social' ) );
            add_action('wp_ajax_nopriv_update_social', array( $this, 'update_social' ) );
            // get all posts - returns array of objects with props postid and posturl
            add_action('wp_ajax_get_all_posts', array( $this, 'get_all_posts' ) );
            add_action('wp_ajax_nopriv_get_all_posts', array( $this, 'get_all_posts' ) );

            // get all posts - returns array of objects with props postid and posturl
            add_action('wp_ajax_table_get_post_data', array( $this, 'table_get_post_data' ) );
            add_action('wp_ajax_nopriv_table_get_post_data', array( $this, 'table_get_post_data' ) );

            // Register menu and options
            add_action( 'admin_menu', array( $this, 'create_sociate_menu' ) );
            add_action( 'admin_menu', array( $this, 'create_sociate_graphs' ) );
            add_action( 'admin_menu', array( $this, 'create_sociate_options' ) );
        }

        public static function activate() {
            self::create_sociate_table();
        }

        public static function deactivate() {

        }

        public function load_client_scripts() {
            if ( defined( 'SMB_ENV' ) && SMB_ENV === 'development' ) {
                $min = '';
            } else {
               $min = '.min'; 
            }   
            wp_enqueue_script( 'sociate', plugins_url( '/js/sociate.jquery' . $min . '.js', __FILE__ ), array( 'jquery' ), false, true );
            wp_localize_script('sociate', 'Sociate_Ajax', array(
                'ajaxUrl' => admin_url( 'admin-ajax.php', 'http' )
            ));

            wp_enqueue_style( 'smb_social_buttons', plugins_url( '/css/sociate' . $min . '.css', __FILE__ ) );
        }

        public function load_admin_scripts() {
            // bootstrap, table css onlys
            if ( isset( $_GET['page'] ) ) { $page = $_GET['page']; } 
            else $page = false; 

            $sociate_pages = array( 'sociate-settings', 'soc-menu', 'sociate-graphs' );

            if ( $page && in_array( $page, $sociate_pages ) ) {
                wp_enqueue_style( 'bootstrap_tables', plugins_url( '/css/sociate-admin.css', __FILE__ ) );

                wp_register_script( 'sociate_jquery', plugins_url( '/packages/jquery/jquery.min.js', __FILE__) );
                wp_register_script( 'sociate', plugins_url( '/js/sociate.jquery.js', __FILE__ ), array( 'sociate_jquery' ) );
                wp_register_script( 'sociate_admin', plugins_url( '/js/sociate-admin.jquery.js', __FILE__ ), array( 'sociate' ) );

                wp_enqueue_script( 'sociate' ); // THIS IS THE PROBLEM SCRIPT 
                wp_enqueue_script( 'sociate_admin' );
                wp_localize_script('sociate', 'Sociate_Ajax', array(
                    'ajaxUrl' => admin_url( 'admin-ajax.php', 'http' )
                ));
            }
        }

        /**************************
        * CRUD operations for post metadata
        **************************/

        // Retrieves individual social meta fields and either returns as an array, or echoes if is an Ajax call
        public static function get_social( $postid = false ) {
            if ( ! $postid ) { $postid = $_POST['postid']; }

            $social['facebook'] = get_post_meta( $postid, 'sociate-facebook', true );
            $social['twitter'] = get_post_meta( $postid, 'sociate-twitter', true );
            $social['google-plus'] = get_post_meta( $postid, 'sociate-google-plus', true );
            $social['pinterest'] = get_post_meta( $postid, 'sociate-pinterest', true );
            $social['linkedin'] = get_post_meta( $postid, 'sociate-linkedin', true );
            $social['total'] = get_post_meta( $postid, 'sociate-total', true );
            $social['updated'] = get_post_meta( $postid, 'sociate-updated', true );
            $social['trending'] = get_post_meta( $postid, 'sociate-trending', true );

            // if is ajax
            if( isset($_POST['action']) && $_POST['action'] === 'get_social' ) {
                echo json_encode($social);
                die();
            } else { // non ajax
                return $social;
            }
        }

        // Updates the post meta for the social data
        public function update_social( $postid, $social_data = array() ) {
            $social_data = array(
                'facebook' => $_POST['facebook'],
                'google-plus' => $_POST['google-plus'],
                'pinterest' => $_POST['pinterest'],
                'linkedin' => $_POST['linkedin'],
                'twitter' => $_POST['twitter'],
            );

            // add the total to the array
            $social_data['total'] = $social_data['pinterest'] + $social_data['google-plus'] + $social_data['linkedin'] + $social_data['twitter'] + $social_data['facebook'];
            // add trending score
            //$social_data['trending'] = $this->update_trending( $postid, $social_data );

            /* if is ajax */
            if ( isset( $_POST[ 'action' ] ) ) { // is ajax request
                $this->save_social( $_POST['postid'], $social_data );
                die( json_encode( $social_data ) );
            } else { // non ajax
                $this->save_social( $postid, $social_data );
                return $social_data;
            }
        }


        // Function to do the actual meta information save
        // Should only be called through update_social method
        public function save_social( $postid, $social_data ) {
            update_post_meta( $postid, 'sociate-facebook', $social_data['facebook'] );
            update_post_meta( $postid, 'sociate-twitter', $social_data['twitter'] );
            update_post_meta( $postid, 'sociate-google-plus', $social_data['google-plus'] );
            update_post_meta( $postid, 'sociate-pinterest', $social_data['pinterest'] );
            update_post_meta( $postid, 'sociate-linkedin', $social_data['linkedin'] );
            update_post_meta( $postid, 'sociate-total', $social_data['total'] );
            update_post_meta( $postid, 'sociate-updated', time() );
            // update_post_meta( $postid, 'sociate-trending', $social_data['trending'] );

            $this->table_insert_data( $postid, $social_data );

            return $social_data;
        }


        public function update_trending( $postid, $social_data ) {
            // Use first entry timestamp as proxy for publish date
            $published = get_the_time( 'U', $postid );
            $total = $social_data['pinterest'] + $social_data['google-plus'] + $social_data['linkedin'] + $social_data['twitter'] + $social_data['facebook'];

            // trending score algorithim:
            // ln(total_media_shares) + ( time published - cur time ) / 12.5 hours
            if ( $total == 0 ) { $trending_score = 0; }
            else { $trending_score = log( (int) $total )  + ( ( $published - time() ) / 45000 ); }

            update_post_meta( $postid, 'sociate-trending', $trending_score );

            return $trending_score;
        }

        // ajax function - echos a list of all posts so javascript can make the api call to sharedcount
        public function get_all_posts() {
            $query = new WP_Query( array(
                'orderby' => 'ID',
                'nopaging' => true
            ) );

            $posts = array();

            if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
                $posts[] = array( 'postid' => get_the_ID(), 'posturl' => get_permalink() );
            endwhile; endif;

            echo json_encode($posts);
            die();
        }

        /**************************
        * Database table creation and deletion
        **************************/

        // Create sociate table
        function create_sociate_table() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'sociate';

            $sql = "CREATE TABLE $table_name (
                id bigint(40) PRIMARY KEY  NOT NULL AUTO_INCREMENT,
                postid bigint(20) NOT NULL,
                time timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
                total bigint(20) DEFAULT 0 NOT NULL,
                facebook bigint(20) DEFAULT 0 NOT NULL,
                twitter bigint(20) DEFAULT 0 NOT NULL,
                pinterest bigint(20) DEFAULT 0 NOT NULL,
                linkedin bigint(20) DEFAULT 0 NOT NULL,
                googleplus bigint(20) DEFAULT 0 NOT NULL
            );";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }


        /**************************
        * CRUD operations for database table
        * All functions can be called via Ajax or via the server
        **************************/

        // Add a new row to the  wp_sociate table. This runs whenever new post metadata is saved
        function table_insert_data( $postid, $social_data ) {
            global $wpdb;

            $wpdb->insert( $wpdb->prefix . 'sociate',
                array(
                    'postid' => $postid,
                    'total' => $social_data['total'],
                    'facebook' => $social_data['facebook'],
                    'twitter' => $social_data['twitter'],
                    'linkedin' => $social_data['linkedin'],
                    'pinterest' => $social_data['pinterest'],
                    'googleplus' => $social_data['google-plus']
                )
            );
        }

        function table_get_data( $postid ) {
            global $wpdb;


        }

        // get data over time for an individual post
        function table_get_post_data( $postid ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'sociate';

            if ( isset( $_POST['postid'] ) ) { $postid = $_POST['postid']; }

            $results = $wpdb->get_results("
                SELECT      *
                FROM        $table_name
                WHERE       postid = $postid
            ");

            if ( $_POST['action'] === 'table_get_post_data' ) {
                echo json_encode($results);
                die();
            } else {
                return $results;
            }
        }


        function table_delete_data() {

        }

        function table_get_total_shares() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'sociate';

            $totals = $wpdb->get_col( $wpdb->prepare("
                SELECT      total
                FROM        $table_name
                ORDER BY    time
            ") );

            return $totals;
        }

        // get shares for an individual network over time
        function table_get_network_shares() {

        }




        /**************************
        * Setting up admin options
        **************************/

        function get_sociate_option($option) {
            $options = get_option( 'sociate_options' );
            return $options[$option];
        }


        // top-level menu for social plugin
        function create_sociate_menu() {
            global $sociate_menu;

            $sociate_menu = add_menu_page(
                'Sociate',
                'Sociate',
                'manage_options',
                'soc-menu',
                array( $this, 'render_sociate_menu' ),
                null,
                30
            );

            add_action( 'load-' . $sociate_menu, array( $this, 'create_help_menu' ) );
        }

        function create_sociate_options() {
            $options = add_submenu_page( 'soc-menu', 'Sociate Settings', 'Sociate Settings', 'manage_options', 'sociate-settings', array( $this, 'render_sociate_options' ) );
        }

        function create_sociate_graphs() {
            $graphs = add_submenu_page( 'soc-menu', 'Sociate Graphs', 'Sociate Graphs', 'manage_options', 'sociate-graphs', array( $this, 'render_sociate_graphs' ) );
        }

        // echo content for social menu
        function render_sociate_menu() {
            include( sprintf( "%s/includes/menu.php", dirname(__FILE__) ) );
        }

        function render_sociate_options() {
            include( sprintf( "%s/includes/options.php", dirname(__FILE__) ) );
        }

        function render_sociate_graphs() {
            include( sprintf( "%s/includes/graphs.php", dirname(__FILE__) ) );
        }

        function create_help_menu() {
            global $sociate_menu;
            $screen = get_current_screen();

            $main = "<p>The Sociate Plugin takes advantage of the SharedCount API to help make managing and tracking your social media goals easier.</p>";

            $technical = "<p>Typically, every time a page is visited on your site, if you have included the social buttons on that page, a check will be made with your
            database to see if your social media statistics have been updated recently. If they have not been updated in the past 15 minutes, a new call to the SharedCount
            API will be made, and the new data will be added to the database. You may optionally refresh the data for each post manually on this page.</p>";

            if ($screen->id == $sociate_menu) {
                $screen->add_help_tab( array(
                    'id' => 'soc-help',
                    'title' => 'Main',
                    'content' => $main
                ) );

                $screen->add_help_tab( array(
                    'id' => 'soc-technical',
                    'title' => 'Technical Information',
                    'content' => $technical
                ) );
            }
        }

        // options

        function register_sociate_options() {
            register_setting( 'sociate_options', 'sociate_options', array( $this, 'sanitize_sociate_options' ) );

            // Social media accounts
            add_settings_section(
                'sociate_accounts',
                'Social Media Account Information',
                array( $this, 'print_sociate_accounts' ),
                'sociate_accounts'
            );

            add_settings_field(
                'twitter_account',
                'Twitter Account',
                array( $this, 'print_twitter_account' ),
                'sociate_accounts',
                'sociate_accounts'
            );

            // Whether or not to activate a given social service
            add_settings_section(
                'sociate_services',
                'Social Services',
                array( $this, 'print_sociate_services' ),
                'sociate_services',
                'sociate_services'
            );

            add_settings_field(
                'use_twitter',
                'Twitter',
                array( $this, 'print_use_twitter'),
                'sociate_services',
                'sociate_services'
            );

            add_settings_field(
                'use_facebook',
                'Facebook',
                array( $this, 'print_use_facebook'),
                'sociate_services',
                'sociate_services'
            );

            add_settings_field(
                'use_pinterest',
                'Pinterest',
                array( $this, 'print_use_pinterest'),
                'sociate_services',
                'sociate_services'
            );

            add_settings_field(
                'use_linkedin',
                'Linkedin',
                array( $this, 'print_use_linkedin'),
                'sociate_services',
                'sociate_services'
            );

            add_settings_field(
                'use_google_plus',
                'Google Plus',
                array( $this, 'print_use_google_plus'),
                'sociate_services',
                'sociate_services'
            );

            // Trending score options

            add_settings_section(
                'sociate_trending',
                'Trending score options',
                array( $this, 'print_sociate_trending' ),
                'sociate_trending',
                'sociate_trending'
            );


            add_settings_field(
                'trending_timeframe',
                'Trending score timeframe (in hours)',
                array( $this, 'print_trending_timeframe'),
                'sociate_trending',
                'sociate_trending'
            );
        }

        function sanitize_sociate_options( $input ) {
            $output = array();

            foreach( $input as $key => $value ) {
                if ( isset( $input[$key] ) ) {
                    $output[$key] = strip_tags( stripslashes( $input[$key] ) );
                }
            }

            return $output;
        }

        function print_sociate_accounts() {
            print('<p>Please include your social media account information below.</p>');
        }

        function print_twitter_account() {
            $options = get_option( 'sociate_options' );
            echo '<input type="text" id="twitter_account" name="sociate_options[twitter_account]" value="' . $options['twitter_account'] . '" />';
        }

        function print_sociate_services() {
            echo 'Select which social services you\'d like to have Sociate use.';
        }

        function print_use_twitter() {
            echo '<input type="checkbox" id="use_twitter" value="checked" name="sociate_options[use_twitter]" ' . $this->get_sociate_option('use_twitter') . '>';
        }

        function print_use_facebook() {
            echo '<input type="checkbox" id="use_facebook" value="checked" name="sociate_options[use_facebook]" ' . $this->get_sociate_option('use_facebook') . '>';
        }


        function print_use_pinterest() {
            echo '<input type="checkbox" id="use_pinterest" value="checked" name="sociate_options[use_pinterest]" ' . $this->get_sociate_option('use_pinterest') . '>';
        }


        function print_use_linkedin() {
            echo '<input type="checkbox" id="use_linkedin" value="checked" name="sociate_options[use_linkedin]" ' . $this->get_sociate_option('use_linkedin') . '>';
        }


        function print_use_google_plus() {
            echo '<input type="checkbox" id="use_google_plus" value="checked" name="sociate_options[use_google_plus]" ' . $this->get_sociate_option('use_google_plus') . '>';
        }

        function print_sociate_trending() {
            echo '<p>Sociate uses the total number of shares in a given time period to calculate the trending score for posts. Since sites vary widely in popularity and number of shares on a given post,
            you\'re able to set the time period to best suit your site. I typically recommend that users set this to seven days, but feel free to experiment to see which value works best for your blog.
            If you\'d like to rank with no time limit, set this option to 0.</p>';
        }

        function print_trending_timeframe() {
            echo '<input type="text" id="trending_timeframe" name="sociate_options[trending_timeframe]" value="' . $this->get_sociate_option( 'trending_timeframe' ) . '" /> ';
        }


    } // end class SOC_Sociate

} // end  if ! class_exists( 'SOC_Sociate' )


if ( class_exists( 'SOC_Sociate') ) {

    register_activation_hook(__FILE__, array('SOC_Sociate', 'activate'));
    register_deactivation_hook(__FILE__, array('SOC_Sociate', 'deactivate'));

    $soc_sociate = new SOC_Sociate();

    // Echos social buttons, where $buttons = an array with keys facebook, twitter, linkedin, pinterest, google-plus
    function SOC_echo_social_buttons( ) {
        include( sprintf( "%s/includes/buttons.php", dirname(__FILE__) ) );
    }

    function SOC_get_social_data( $postid ) {
        global $soc_sociate;
        return $soc_sociate->get_social( $postid );
    }

    function SOC_get_table_post_data( $postid ) {
        global $soc_sociate;
        return $soc_sociate->table_get_post_data( $postid );
    }

}