<?php

if ( !class_exists( 'TVJussieu_JT' ) ) {

	class TVJussieu_JT
	{
		const POST_TYPE = 'jt';
		const SLUG = 'jt';

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
			//add_permastruct('tvj_jt_archive', self::SLUG . '/%year%/%monthnum%/%day%');
			//add_permastruct( 'tvj_jt_season', self::SLUG . '/%jt_season%' );
			//add_permastruct( 'tvj_jt_season', self::SLUG . '/%jt_season%/%jt_type%-%jt_n%' );
			//add_action( 'pre_get_posts', array( $this, 'handle_jt_query' ) );

			add_filter( 'wp_insert_post_data', array( $this, 'pre_save_post' ), '99', 2 );
			add_action( 'save_post', array( $this, 'save_post' ) );

			add_filter( 'post_type_link', array( $this, 'jt_link' ), 10, 3 );
			add_filter( 'term_link', array( $this, 'jt_season_link' ), 10, 3 );
			add_rewrite_rule(
				self::SLUG . '/([^/]+)/([^/]+)-([0-9]+)?$', 'index.php?post_type=' . self::POST_TYPE . '&name=$matches[1]-$matches[2]-$matches[3]', 'top'
			);

			$this->create_taxonomies();
			$this->create_post_type();
			add_rewrite_tag( '%jt_season%', '(.*saison.*)' );
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
				'label' => __( 'JT', 'tvjussieu' ),
				'description' => __( 'A JT published by TV Jussieu', 'tvjussieu' ),
				'labels' => array(
					'name' => __( 'JTs', 'tvjussieu' ),
					'singular_name' => __( 'JT', 'tvjussieu' ),
					'menu_name' => __( 'JTs', 'tvjussieu' ),
					//'parent_item_colon'   => __( 'Parent Item:', 'tvjussieu' ),
					'all_items' => __( 'All JTs', 'tvjussieu' ),
					//'view_item'           => __( 'View Item', 'tvjussieu' ),
					'add_new_item' => __( 'Add a new JT', 'tvjussieu' ),
					'add_new' => __( 'Add a JT', 'tvjussieu' ),
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
				'rewrite' => array(
					'slug' => self::SLUG,
					'with_front' => true,
					'pages' => true,
					'feeds' => true,
				),
				'capability_type' => 'post',
			) );
		}

		public function add_meta_boxes()
		{
			add_meta_box(
				self::POST_TYPE . '_meta_box', __( 'JT detail', 'tvjussieu' ), array( $this, 'add_detail_meta_box' ), self::POST_TYPE
			);
		}

		public function add_detail_meta_box( $post )
		{
			$all_seasons = get_terms( self::POST_TYPE . '_season', array( 'hide_empty' => false ) );
			$current_seasons = get_the_terms( $post->ID, self::POST_TYPE . '_season' );
			if ( !is_wp_error( $current_seasons ) && !empty( $current_seasons ) && is_object( reset( $current_seasons ) ) ) {
				$current_season = reset( $current_seasons )->slug;
			} else {
				$current_season = 'hors-saison';
			}

			$all_types = get_terms( self::POST_TYPE . '_type', array( 'hide_empty' => false ) );
			$current_types = get_the_terms( $post->ID, self::POST_TYPE . '_type' );
			if ( !is_wp_error( $current_types ) && !empty( $current_types ) && is_object( reset( $current_types ) ) ) {
				$current_type = reset( $current_types )->slug;
			} else {
				$current_type = 'jt';
			}

			$n = get_post_meta( $post->ID, 'jt_n', true );
			$dailymotion = get_post_meta( $post->ID, 'jt_dailymotion', true );
			$youtube = get_post_meta( $post->ID, 'jt_youtube', true );

			include( get_stylesheet_directory() . '/partials/detail_meta_box-' . self::POST_TYPE . '.php');
		}

		public function pre_save_post( $data, $postarr )
		{
			if ( !isset( $data['post_type'] ) || self::POST_TYPE !== $data['post_type'] ) {
				return $data;
			}

			$data['post_name'] = $postarr['jt_season'] . '-' . $postarr['jt_type'] . '-' . $postarr['jt_n'];

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

			if ( isset( $_POST[self::POST_TYPE . '_type'] ) ) {
				wp_delete_object_term_relationships( $post_id, self::POST_TYPE . '_type' );
				wp_set_post_terms( $post_id, $_POST[self::POST_TYPE . '_type'], self::POST_TYPE . '_type' );
			}

			if ( isset( $_POST[self::POST_TYPE . '_season'] ) ) {
				wp_delete_object_term_relationships( $post_id, self::POST_TYPE . '_season' );
				wp_set_post_terms( $post_id, $_POST[self::POST_TYPE . '_season'], self::POST_TYPE . '_season' );
			}

			update_post_meta( $post_id, 'jt_n', $_POST['jt_n'] );
			update_post_meta( $post_id, 'jt_dailymotion', $_POST['jt_dailymotion'] );
			update_post_meta( $post_id, 'jt_youtube', $_POST['jt_youtube'] );
		}

		public function create_taxonomies()
		{
			$this->create_season_taxonomy();
			$this->create_type_taxonomy();
		}

		protected function create_season_taxonomy()
		{
			register_taxonomy( self::POST_TYPE . '_season', self::POST_TYPE, array(
				'labels' => array(
					'name' => __( 'Seasons', 'tvjussieu' ),
					'singular_name' => __( 'Season', 'tvjussieu' ),
					'menu_name' => __( 'Seasons', 'tvjussieu' ),
					//'all_items'                  => __( 'All Items', 'tvjussieu' ),
					//'parent_item'                => __( 'Parent Item', 'tvjussieu' ),
					//'parent_item_colon'          => __( 'Parent Item:', 'tvjussieu' ),
					'new_item_name' => __( 'New season', 'tvjussieu' ),
					'add_new_item' => __( 'Add a season', 'tvjussieu' ),
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
					'slug' => 'jt', // self::POST_TYPE, // . '/%' . self::POST_TYPE . '_season%',
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

		public function jt_link( $link, $post = 0 )
		{
			if ( $post->post_type == self::POST_TYPE ) {
				$types = get_the_terms( $post->ID, self::POST_TYPE . '_type' );
				if ( !is_wp_error( $types ) && !empty( $types ) && is_object( reset( $types ) ) ) {
					$type = reset( $types )->slug;
				} else {
					$type = 'jt';
				}

				$seasons = get_the_terms( $post->ID, self::POST_TYPE . '_season' );
				if ( !is_wp_error( $seasons ) && !empty( $seasons ) && is_object( reset( $seasons ) ) ) {
					$season = reset( $seasons )->slug;
				} else {
					$season = 'hors-saison';
				}

				$n = get_post_meta( $post->ID, 'jt_n', true );

				$link = home_url( self::SLUG . '/' . $season . '/' . $type . '-' . $n );
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

		/* public function add_tv_show_meta_boxes($post)
		  {
		  include(dirname(__FILE__) . '/../templates/' . self::POST_TYPE . '_meta_box.php');
		  } */
	}

}