<?php

if ( !class_exists( 'TVJussieu_Staff' ) ) {

	class TVJussieu_Staff
	{
		const POST_TYPE = 'staff';
		const SLUG = 'staff';

		public function __construct()
		{
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
		}

		public function init()
		{
			//add_rewrite_tag('%jt%','(jt)','post_type=');
			//add_rewrite_tag( '%jt_season%', '(saison-[0-9]+)' );
			//add_rewrite_tag( '%jt_n%', '([0-9]+)' );
			//add_permastruct( 'tvj_staff_period', self::SLUG . '/%staff_period%' );
			//add_permastruct('tvj_jt_archive', self::SLUG . '/%year%/%monthnum%/%day%');
			//add_permastruct( 'tvj_jt_season', self::SLUG . '/%jt_season%' );
			//add_permastruct( 'tvj_jt_season', self::SLUG . '/%jt_season%/%jt_type%-%jt_n%' );
			//add_action( 'pre_get_posts', array( $this, 'handle_jt_query' ) );

			add_filter( 'wp_insert_post_data', array( $this, 'pre_save_post' ), '99', 2 );
			add_action( 'save_post', array( $this, 'save_post' ) );

			add_filter( 'post_type_link', array( $this, 'staff_link' ), 10, 3 );
			//add_filter( 'term_link', array( $this, 'jt_season_link' ), 10, 3 );
			add_rewrite_rule(
				self::SLUG . '/(([^/0-9]+)\-([^/0-9]+))/?$', 'index.php?post_type=' . self::POST_TYPE . '&name=$matches[1]', 'top'
			);

			$this->create_taxonomies();
			$this->create_post_type();
			add_rewrite_tag( '%staff_period%', '([0-9]+\-[0-9]+)' );
		}

		public function handle_jt_query( WP_Query $query )
		{
			if ( $query->is_main_query() && !is_admin() ) {
				//$query->set('numberposts', 1);
				if ( isset( $query->query_vars['jt_season'] ) && isset( $query->query_vars['jt_type'] ) && isset( $query->query_vars['jt_n'] ) ) {
					$query->set( 'post_type', self::POST_TYPE );
					//$query->set( 'post_name', $query->query_vars['jt_season'] . '-' . $query->query_vars['jt_type'] . '-' . $query->query_vars['jt_n'] );

					/* $query->set( 'tax_query', array(
					  array(
					  'taxonomy' => 'tvj_jt_season',
					  'field' => 'slug',
					  'terms' => $query->query_vars['jt_season'],
					  ),
					  ) );

					  if ( isset( $query->query_vars['jt_number'] ) ) {
					  //$query->set( 'meta_key', 'tvj_jt_n' );
					  //$query->set( 'meta_value', $query->query_vars['jt_number'] );
					  $query->set( 'meta_query', array(
					  array(
					  'key' => 'tvj_jt_n',
					  'value' => $query->query_vars['jt_number'],
					  'type' => 'NUMERIC',
					  'compare' => '='
					  )
					  ) );
					  } */
				}
			}
			return $query;
		}

		public function admin_init()
		{
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		}

		public function create_post_type()
		{
			register_post_type( self::POST_TYPE, array(
				'label' => __( 'Staff', 'tvjussieu' ),
				'description' => __( 'A Staff in TV Jussieu', 'tvjussieu' ),
				'labels' => array(
					'name' => __( 'Staffs', 'tvjussieu' ),
					'singular_name' => __( 'Staff', 'tvjussieu' ),
					'menu_name' => __( 'Staff', 'tvjussieu' ),
					//'parent_item_colon'   => __( 'Parent Item:', 'tvjussieu' ),
					'all_items' => __( 'All Staffs', 'tvjussieu' ),
					//'view_item'           => __( 'View Item', 'tvjussieu' ),
					'add_new_item' => __( 'Add a new Staff', 'tvjussieu' ),
					'add_new' => __( 'Add a Staff', 'tvjussieu' ),
				//'edit_item'           => __( 'Edit Item', 'tvjussieu' ),
				//'update_item'         => __( 'Update Item', 'tvjussieu' ),
				//'search_items'        => __( 'Search Item', 'tvjussieu' ),
				//'not_found'           => __( 'Not found', 'tvjussieu' ),
				//'not_found_in_trash'  => __( 'Not found in Trash', 'tvjussieu' ),
				),
				'hierarchical' => false,
				'public' => true,
				'supports' => array(
					'title',
					'excerpt',
					'editor',
					//'author',
					'thumbnail',
					'comments',
				),
				'show_ui' => true,
				'show_in_menu' => true,
				'show_in_nav_menus' => true,
				'show_in_admin_bar' => true,
				//'menu_position' => 2,
				//'menu_icon'           => '',
				'can_export' => true,
				'has_archive' => true,
				'exclude_from_search' => false,
				'publicly_queryable' => true,
				'rewrite' => true,
				/* 'rewrite' => array(
				  'slug' => self::SLUG,
				  'with_front' => true,
				  'pages' => true,
				  'feeds' => true,
				  ), */
				'capability_type' => 'post',
			) );
		}

		public function add_meta_boxes()
		{
			add_meta_box(
				self::POST_TYPE . '_meta_box', __( 'Staff detail', 'tvjussieu' ), array( $this, 'add_detail_meta_box' ), self::POST_TYPE
			);
		}

		public function add_detail_meta_box( $post )
		{
			$all_periods = get_terms( self::POST_TYPE . '_period', array( 'hide_empty' => false ) );
			$current_periods = get_the_terms( $post->ID, self::POST_TYPE . '_period' );
			if ( !is_wp_error( $current_periods ) && !empty( $current_periods ) && is_object( reset( $current_periods ) ) ) {
				$current_periods = array_map( function($v) {
					return $v->slug;
				}, $current_periods );
			} else {
				$current_periods = array();
			}

			$firstname = get_post_meta( $post->ID, 'staff_firstname', true );
			$lastname = get_post_meta( $post->ID, 'staff_lastname', true );
			$role = get_post_meta( $post->ID, 'staff_role', true );
			$facebook = get_post_meta( $post->ID, 'staff_facebook', true );

			include( get_stylesheet_directory() . '/partials/detail_meta_box-' . self::POST_TYPE . '.php');
		}

		public function pre_save_post( $data, $postarr )
		{
			if ( !isset( $data['post_type'] ) || self::POST_TYPE !== $data['post_type'] ) {
				return $data;
			}

			$data['post_name'] = remove_accents( strtolower( $postarr['staff_firstname'] . '-' . $postarr['staff_lastname'] ) );

			return $data;
		}

		public function save_post( $post_id )
		{
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( !current_user_can( 'edit_post', $post_id ) || !isset( $_POST['post_type'] ) || self::POST_TYPE !== $_POST['post_type'] ) {
				return;
			}

			if ( isset( $_POST[self::POST_TYPE . '_period'] ) ) {
				wp_delete_object_term_relationships( $post_id, self::POST_TYPE . '_period' );
				wp_set_post_terms( $post_id, implode( ',', $_POST[self::POST_TYPE . '_period'] ), self::POST_TYPE . '_period' );
			}

			update_post_meta( $post_id, 'staff_firstname', $_POST['staff_firstname'] );
			update_post_meta( $post_id, 'staff_lastname', $_POST['staff_lastname'] );
			update_post_meta( $post_id, 'staff_role', $_POST['staff_role'] );
			update_post_meta( $post_id, 'staff_facebook', $_POST['staff_facebook'] );
		}

		public function create_taxonomies()
		{
			$this->create_period_taxonomy();
			//$this->create_type_taxonomy();
		}

		protected function create_period_taxonomy()
		{
			register_taxonomy( self::POST_TYPE . '_period', self::POST_TYPE, array(
				'labels' => array(
					'name' => __( 'Periods', 'tvjussieu' ),
					'singular_name' => __( 'Period', 'tvjussieu' ),
					'menu_name' => __( 'Period', 'tvjussieu' ),
					//'all_items'                  => __( 'All Items', 'tvjussieu' ),
					//'parent_item'                => __( 'Parent Item', 'tvjussieu' ),
					//'parent_item_colon'          => __( 'Parent Item:', 'tvjussieu' ),
					'new_item_name' => __( 'New period', 'tvjussieu' ),
					'add_new_item' => __( 'Add a period', 'tvjussieu' ),
				//'edit_item'                  => __( 'Edit Item', 'tvjussieu' ),
				//'update_item'                => __( 'Update Item', 'tvjussieu' ),
				//'separate_items_with_commas' => __( 'Separate items with commas', 'tvjussieu' ),
				//'search_items'               => __( 'Search Items', 'tvjussieu' ),
				//'add_or_remove_items'        => __( 'Add or remove items', 'tvjussieu' ),
				//'choose_from_most_used'      => __( 'Choose from the most used items', 'tvjussieu' ),
				//'not_found'                  => __( 'Not Found', 'tvjussieu' ),
				),
				'hierarchical' => false,
				'public' => true,
				'show_ui' => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'show_tagcloud' => true,
				//'rewrite' => true,
				'rewrite' => array(
					'slug' => 'staff', // self::POST_TYPE, // . '/%' . self::POST_TYPE . '_season%',
					'with_front' => true,
				//'hierarchical' => true,
				),
				'meta_box_cb' => false, //array($this, 'add_tv_show_meta_boxes')
			) );
		}

		protected function create_type_taxonomy()
		{
			register_taxonomy( self::POST_TYPE . '_type', self::POST_TYPE, array(
				'labels' => array(
					'name' => __( 'Types', 'tvjussieu' ),
					'singular_name' => __( 'Type', 'tvjussieu' ),
					'menu_name' => __( 'Types', 'tvjussieu' ),
				//'all_items'                  => __( 'All Items', 'tvjussieu' ),
				//'parent_item'                => __( 'Parent Item', 'tvjussieu' ),
				//'parent_item_colon'          => __( 'Parent Item:', 'tvjussieu' ),
				//'new_item_name' => __( 'New type', 'tvjussieu' ),
				//'add_new_item' => __( 'Add a type', 'tvjussieu' ),
				//'edit_item'                  => __( 'Edit Item', 'tvjussieu' ),
				//'update_item'                => __( 'Update Item', 'tvjussieu' ),
				//'separate_items_with_commas' => __( 'Separate items with commas', 'tvjussieu' ),
				//'search_items'               => __( 'Search Items', 'tvjussieu' ),
				//'add_or_remove_items'        => __( 'Add or remove items', 'tvjussieu' ),
				//'choose_from_most_used'      => __( 'Choose from the most used items', 'tvjussieu' ),
				//'not_found'                  => __( 'Not Found', 'tvjussieu' ),
				),
				'hierarchical' => false,
				'public' => true,
				'show_ui' => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'show_tagcloud' => true,
				//'rewrite' => true,
				'rewrite' => array(
					'slug' => 'video',
					'with_front' => true,
					'hierarchical' => true,
				),
				'meta_box_cb' => false, //array($this, 'add_tv_show_meta_boxes')
			) );
		}

		public function staff_link( $link, $post = 0 )
		{
			if ( $post->post_type == self::POST_TYPE ) {
				/* $types = get_the_terms( $post->ID, self::POST_TYPE . '_type' );
				  if ( !is_wp_error( $types ) && !empty( $types ) && is_object( reset( $types ) ) ) {
				  $type = reset( $types )->slug;
				  } else {
				  $type = 'jt';
				  }

				  $seasons = get_the_terms( $post->ID, self::POST_TYPE . '_season' );
				  if ( !is_wp_error( $seasons ) && !empty( $seasons ) && is_object( reset( $seasons ) ) ) {
				  $season = reset( $seasons )->slug;
				  } else {
				  $season = 'no-season';
				  }

				  $n = get_post_meta( $post->ID, 'jt_n', true ); */

				$link = home_url( self::SLUG . '/' . $post->post_name );
			}
			return $link;
		}

		public function jt_season_link( $url, $term, $taxonomy )
		{
			if ( $taxonomy === self::POST_TYPE . '_season' ) {
				return home_url( self::SLUG . '/' . $term->slug );
			}

			return $url;
		}
	}

}