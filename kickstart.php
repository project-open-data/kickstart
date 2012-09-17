<?php
/*
Plugin Name: Kickstart
Description: Kickstart open data
Author: Benjamin J. Balter
Version: 0.1
Author URI: http://ben.balter.com
License: GPLv3 or Later
*/

/* Kickstart
 *
 * Provides users that would not normally be able to edit a post with the ability to submit revisions. 
 * This can be users on a site without the `edit_post` or `edit_published_post` capabilities, 
 * or can be members of the general public. Also  allows post authors to edit published posts 
 * without their changes going appearing publicly until published.
 *
 * Copyright (C) 2012 Benjamin J. Balter ( Ben@Balter.com | http://ben.balter.com )
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
 * @copyright 2012
 * @license GPL v3
 * @version 0.1
 * @package kickstart
 * @author Benjamin J. Balter <ben@balter.com>
 */
 
//load templating functions
include dirname( __FILE__ ) . '/templating.php';
 
class Kickstart {
	
	public $cpt = 'dataset';
	public $meta_key = 'kickstart_score';
	public $comment_type = 'kickstart_vote';
	public $version = '0.1';
	static $instance;
	
	/**
	 * Hook into WordPress core
	 */
	function __construct() {
	
		self::$instance = &$this;
		
		add_action( 'init', array( $this, 'register_cpt' ) );
		add_action( 'init', array( $this, 'register_cts' ) );
		add_action( 'wp_ajax_kickstart_vote', array( $this, 'ajax_vote_handler' ) );
		add_action( 'wp_ajax_nopriv_kickstart_vote', array( $this, 'ajax_must_login_handler' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_js' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_css' ) );
		add_filter( 'the_content', array( $this, 'content_filter' ) );
		add_action( 'template_redirect', array( $this, 'home_redirect' ), 999 );
		add_filter( 'bloginfo_url', array( $this, 'home_url_filter' ), 10, 2 );
		add_filter( 'feed_link', array( $this, 'feed_link_filter' ) );
		add_filter( 'parse_request', array( $this, 'request_filter' ) );
		add_action( 'save_post', array( $this, 'calculate_score_on_save' ) );
		add_action( 'login_message', array( $this, 'login_message' ) );

		register_activation_hook( __FILE__, 'flush_rewrite_rules' );

	}

	/**
	 * Register Dataset Custom Post Type
	 */
	function register_cpt() {
		

   		$labels = array( 
   		    'name'               => _x( 'Datasets', 'dataset' ),
   		    'singular_name'      => _x( 'Dataset', 'dataset' ),
   		    'add_new'            => _x( 'Add New', 'dataset' ),
   		    'add_new_item'       => _x( 'Add New Dataset', 'dataset' ),
   		    'edit_item'          => _x( 'Edit Dataset', 'dataset' ),
   		    'new_item'           => _x( 'New Dataset', 'dataset' ),
   		    'view_item'          => _x( 'View Dataset', 'dataset' ),
   		    'search_items'       => _x( 'Search Datasets', 'dataset' ),
   		    'not_found'          => _x( 'No Datasets found', 'dataset' ),
   		    'not_found_in_trash' => _x( 'No Datasets found in Trash', 'dataset' ),
   		    'parent_item_colon'  => _x( 'Parent Dataset:', 'dataset' ),
   		    'menu_name'          => _x( 'Datasets', 'dataset' ),
   		);
   		
   		$args = array( 
   		    'labels'              => $labels,
   		    'hierarchical'        => true,
   		    'supports'            => array( 'title', 'editor', 'custom-fields', 'comments', 'revisions' ),
   		    'taxonomies'          => array( 'post_tag', 'agencies' ),
   		    'public'              => true,
   		    'show_ui'             => true,
   		    'show_in_menu'        => true,
   		    'show_in_nav_menus'   => true,
   		    'publicly_queryable'  => true,
   		    'exclude_from_search' => false,
   		    'has_archive'         => 'datasets',
   		    'query_var'           => true,
   		    'can_export'          => true,
   		    'rewrite'             => array( 'slug' => 'datasets' ),
   		    'capability_type'     => 'post'
   		);

   		register_post_type( $this->cpt, $args );
		
	}
	
	/**
	 * Register agency and status custom taxonomies
	 */
	function register_cts() {
	
   		 $labels = array( 
   		     'name'                       => _x( 'Agencies', 'agencies' ),
   		     'singular_name'              => _x( 'Agency', 'agencies' ),
   		     'search_items'               => _x( 'Search Agencies', 'agencies' ),
   		     'popular_items'              => _x( 'Popular Agencies', 'agencies' ),
   		     'all_items'                  => _x( 'All Agencies', 'agencies' ),
   		     'parent_item'                => _x( 'Parent Agency', 'agencies' ),
   		     'parent_item_colon'          => _x( 'Parent Agency:', 'agencies' ),
   		     'edit_item'                  => _x( 'Edit Agency', 'agencies' ),
   		     'update_item'                => _x( 'Update Agency', 'agencies' ),
   		     'add_new_item'               => _x( 'Add New Agency', 'agencies' ),
   		     'new_item_name'              => _x( 'New Agency', 'agencies' ),
   		     'separate_items_with_commas' => _x( 'Separate agencies with commas', 'agencies' ),
   		     'add_or_remove_items'        => _x( 'Add or remove agencies', 'agencies' ),
   		     'choose_from_most_used'      => _x( 'Choose from the most used agencies', 'agencies' ),
   		     'menu_name'                  => _x( 'Agencies', 'agencies' ),
   		 );
   		
   		 $args = array( 
   		     'labels'            => $labels,
   		     'public'            => true,
   		     'show_in_nav_menus' => true,
   		     'show_ui'           => true,
   		     'show_tagcloud'     => true,
   		     'hierarchical'      => true,
   		     'rewrite'           => true,
   		     'query_var'         => true
   		 );
   		
   		 register_taxonomy( 'agencies', array( $this->cpt ), $args );
		
   		 $labels = array( 
   		     'name'                       => _x( 'Statuses', 'statuses' ),
   		     'singular_name'              => _x( 'Status', 'statuses' ),
   		     'search_items'               => _x( 'Search Statuses', 'statuses' ),
   		     'popular_items'              => _x( 'Popular Statuses', 'statuses' ),
   		     'all_items'                  => _x( 'All Statuses', 'statuses' ),
   		     'parent_item'                => _x( 'Parent Status', 'statuses' ),
   		     'parent_item_colon'          => _x( 'Parent Status:', 'statuses' ),
   		     'edit_item'                  => _x( 'Edit Status', 'statuses' ),
   		     'update_item'                => _x( 'Update Status', 'statuses' ),
   		     'add_new_item'               => _x( 'Add New Status', 'statuses' ),
   		     'new_item_name'              => _x( 'New Status', 'statuses' ),
   		     'separate_items_with_commas' => _x( 'Separate statuses with commas', 'statuses' ),
   		     'add_or_remove_items'        => _x( 'Add or remove Statuses', 'statuses' ),
   		     'choose_from_most_used'      => _x( 'Choose from most used Statuses', 'statuses' ),
   		     'menu_name'                  => _x( 'Statuses', 'statuses' ),
   		 );
   		 
   		 $args = array( 
   		     'labels'            => $labels,
   		     'public'            => true,
   		     'show_in_nav_menus' => true,
   		     'show_ui'           => true,
   		     'show_tagcloud'     => true,
   		     'hierarchical'      => false,
   		     'rewrite'           => true,
   		     'query_var'         => true,
   		 );
   		 
   		 register_taxonomy( 'statuses', array( $this->cpt ), $args );
	
	}
	
	/**
	 * Returns the score (upvotes - downvotes) for the given post
	 * @param int|obj $post the post object or ID (optional -- defaults to global $post)
	 * @return int the score
	 */
	function calculate_score( $post = null ) {
		global $wpdb;
		$post = get_post( $post );
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT SUM( comment_karma ) FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_type = %s", $post->ID, $this->comment_type ) );
	}
	
	/**
	 * Get a post's score ( up votes - down votes )
	 * @return int the score
	 */
	function get_score( $post = null ) {
		
		$post = get_post( $post );
		$meta = get_post_meta( $post->ID, $this->meta_key, true );
		return (int) $meta;
		
	}
	
	/**
	 * Cache a post's score to post_meta
	 */
	function store_score( $post = null ) {
		
		$post = get_post( $post );
		$score = $this->calculate_score( $post );
		update_post_meta( $post->ID, $this->meta_key, $score );
		return $score;
		
	}
	
	/**
	 * Given a vote type string (up, down) returns the numeric value it represents
	 * @param string $type the vote type string
	 * @return int the karma value
	 */
	function get_karma_value( $type ) {
		
		$type = strtolower( $type );
		if ( $type == 'up' )
			return 1;
			
		if ( $type == 'down' )
			return -1;
			
		return 0;
		
	}
	
	/**
	 * Record an up or down vote
	 */
	function vote( $type = 'up' , $post = null, $userID = null ) {
		
		$post = get_post( $post );

		if ( $userID == null )
			$userID = get_current_user_id();
			
		if ( $this->has_voted( $userID, $post, $type ) )
			return $this->get_score( $post );
		
		//clear old votes in case previously voted the other way
		$this->delete_vote( $userID, $post );
			
		$comment = array( 
			'comment_type' => $this->comment_type,
			'comment_karma' => $this->get_karma_value( $type ),
			'comment_post_ID' => $post->ID,
			'user_id' => $userID,
		);

		$commentID = wp_insert_comment( $comment );
		
		//update score cache
		return $this->store_score( $post );
				
	}
	
	/**
	 * Check if a user has already voted on a given dataset
	 */
	function has_voted( $userID = null, $post = null, $direction = null ) {
		
		global $wpdb;
		
		$post = get_post( $post );
		
		if ( $userID == null )
			$userID = get_current_user_id();
		
		$direction_query = '';
		
		if ( $direction )
			$direction_query = "AND comment_karma = '{$this->get_karma_value( $direction )}'";
			
		$num = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = %d AND user_id = %d AND comment_type = %s {$direction_query}", $post->ID, $userID, $this->comment_type ) );
		
		return ( $num != 0 );
		
	}
	
	/**
	 * Remove a previously recorded vote
	 */
	function delete_vote( $userID = null, $post = null, $direction = null ) {
		
		global $wpdb;
		
		$post = get_post( $post );
		
		if ( $userID == null )
			$userID = get_current_user_id();
		
		$direction_query = '';
		
		if ( $direction )
			$direction_query = "WHERE comment_karma = '{$this->get_karma_value( $direction )}'";
		
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->comments WHERE comment_post_ID = %d AND user_id = %d AND comment_type = %s {$direction_query}", $post->ID, $userID, $this->comment_type ) );

	}
	
	/**
	 * Callback to process voting
	 */
	function ajax_vote_handler() {
		
		//note: user is already logged in or this hook would not fire
				
		//sanitize type, if it's not "down", they just up voted the dataset	
		$type = ( $_GET['direction'] == 'down' ) ? 'down' : 'up';
		
		//force post to integer
		$post = (int) $_GET['post_ID'];
		
		$userID = get_current_user_id();
		
		//return the new score
		echo $this->vote( $type, $post, $userID );
		exit();
		
	}
	
	/**
	 * Add our JS to the page
	 */
	function enqueue_js() {
		
		global $post;
		
		$js = ( WP_DEBUG ) ? 'js/js.dev.js' : 'js/js.js';
		wp_enqueue_script( 'kickstart', plugins_url( $js, __FILE__ ), array( 'jquery' ), $this->version, true );
				
		$l10n = array( 
			'ajaxEndpoint' => admin_url( 'admin-ajax.php' ),
			'loginURL' => add_query_arg( 'message', 'kickstart_login_required', wp_login_url( esc_url( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) ) ),
		);
		
		if ( isset( $post->ID ) ) 
			$l10n['post_ID'] = $post->ID;

		wp_localize_script( 'kickstart', 'kickstart', $l10n );
	
	}
	
	/**
	 * Add our CSS to the page
	 */
	function enqueue_css() {
		
		wp_enqueue_style( 'kickstart', plugins_url( 'css/style.css' , __FILE__ ), null, $this->version );
		
	}
	
	/**
	 * Ballot template tag, renders the ballot
	 */
	function ballot( $post = null ) {
		
		$post = get_post( $post );
		ob_start();
		include dirname( __FILE__ ) . '/templates/ballot.php';
		return ob_get_clean();
	
	}
	
	/**
	 * Inject ballot into content field
	 */
	function content_filter( $content ) {
		
		if ( get_post_type() != $this->cpt )
			return $content;
			
		if ( !apply_filters( 'kickstart_auto_ballot', true ) )
			return $content;
			
		if ( is_feed() )
			return $content;
			
		return $this->ballot() . $content;
		
	}
	
	/**
	 * Redirect root URL to /datasets with 301 header
	 */
	function home_redirect() {
	
		if ( !is_home() )
			return;
	
		if ( get_query_var( 'json' ) )
			return;
	
		wp_redirect( home_url( '/datasets/' ), '301' );
		exit();
	
	}
	
	/**
	 * Whenever home url is queried, return /questions/
	 */
	function home_url_filter( $output, $show ) {
	
		return ( $show == 'url' ) ? $output . '/datsets' : $output;
	
	}
	
   	/**
    * Rewrites feed to datasets post type by default
    */
   function feed_link_filter( $feed ) {
   
   	if ( strpos( $feed, '/datasets' ) !== false || strpos( $feed, 'comments' ) !== false )
   		return $feed;
   
   	return str_replace( '/feed', '/datasets/feed', $feed );
   
   }
   
   /**
    * Force all requests for the datasets archive to be sorted by score
    */
   function request_filter( $request ) {
   
   		if ( !isset( $request->query_vars['post_type'] ) )
   			return $request;
   			
   		if ( $request->query_vars['post_type'] != $this->cpt )
			return $request;
		
		if ( isset( $request->query_vars[ $this->cpt ] ) )
			return $request;
	
		if ( isset( $request->query_vars[ 'orderby' ] ) )
			return $request;
					
		$request->query_vars['orderby'] = 'meta_value_num';
		$request->query_vars['meta_key'] = $this->meta_key;
		$request->query_vars['order'] = 'DESC';
		
		return $request;
		
	}
	
	/**
	 * Force all datasets to have our score postmeta
	 * Otherwise, sorting would fail b/c the join wouldn't work
	 */
	function calculate_score_on_save( $post ) {
		
		if ( wp_is_post_revision() || wp_is_post_autosave() )
			return;
			
		if ( get_post_type( $post ) != $this->cpt )
			return; 
			
		$this->store_score( $post );
		
	}
	
	function ajax_must_login_handler() {
		
		status_header( 403 );
		die( -1 );
		
	}
	
	function login_message( $message ) {
		
		if ( !$_GET['message'] )
			return $message;
			
		if ( $_GET['message'] != 'kickstart_login_required' )
			return $message;
			
		$msg .= '<div class="error" style="margin-bottom: 20px; font-weight: bold; padding: 5px; ">' . __( 'You must login or register below before you can vote or comment', 'kickstart' ) . '</div>';
		
		return $msg;
		
	}
	
}

$kickstart = new Kickstart();
