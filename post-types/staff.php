<?php

if ( !class_exists( 'TVJussieu_Staff' ) ) {

	class TVJussieu_Staff
	{
		const POST_TYPE = 'staff';
		const SLUG = 'staff';

		public function __construct()
		{
			add_action( 'init', array($this, 'init') );
			add_action( 'admin_init', array($this, 'admin_init') );
		}

		public function init()
		{
			add_filter( 'wp_insert_post_data', array($this, 'pre_save_post'), '99', 2 );
			add_action( 'save_post', array($this, 'save_post') );

			add_filter( 'post_type_link', array($this, 'staff_link'), 10, 3 );
			add_rewrite_rule(
				self::SLUG . '/(([^/0-9]+)\-([^/0-9]+))/?$', 'index.php?post_type=' . self::POST_TYPE . '&name=$matches[1]', 'top'
			);

			$this->create_taxonomies();
			$this->create_post_type();
		}

		public function admin_init()
		{
			add_action( 'add_meta_boxes', array($this, 'add_meta_boxes') );
			add_action( 'wp_insert_post_data', array( $this, 'validate_meta' ), 99, 2 );
		}

		public function create_post_type()
		{
			register_post_type( self::POST_TYPE, array(
				'label' => __( 'Staff', 'tvjussieu' ),
				'description' => __( 'Un staff de TV Jussieu', 'tvjussieu' ),
				'labels' => array(
					'name' => __( 'Staffs', 'tvjussieu' ),
					'singular_name' => __( 'Staff', 'tvjussieu' ),
					'menu_name' => __( 'Staff', 'tvjussieu' ),
					//'parent_item_colon'   => __( 'Parent Item:', 'tvjussieu' ),
					'all_items' => __( 'Tous les staffs', 'tvjussieu' ),
					'view_item'           => __( 'Voir le staff', 'tvjussieu' ),
					'add_new_item' => __( 'Ajouter un nouveau staff', 'tvjussieu' ),
					'add_new' => __( 'Ajouter un staff', 'tvjussieu' ),
				'edit_item'           => __( 'Modifier le staff', 'tvjussieu' ),
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
				self::POST_TYPE . '_meta_box', __( 'Détail du staff', 'tvjussieu' ), array($this, 'add_detail_meta_box'), self::POST_TYPE
			);
		}

		public function add_detail_meta_box( $post )
		{
			$all_promos = get_terms( self::POST_TYPE . '_promo', array('hide_empty' => false) );
			$current_promos = get_the_terms( $post->ID, self::POST_TYPE . '_promo' );
			if ( !is_wp_error( $current_promos ) && !empty( $current_promos ) && is_object( reset( $current_promos ) ) ) {
				$current_promos = array_map( function($v) {
					return $v->slug;
				}, $current_promos );
			} else {
				$current_promos = array();
			}

			$firstname = get_post_meta( $post->ID, 'staff_firstname', true );
			$lastname = get_post_meta( $post->ID, 'staff_lastname', true );
			$nickname = get_post_meta( $post->ID, 'staff_nickname', true );
			$role = get_post_meta( $post->ID, 'staff_role', true );
			$facebook = get_post_meta( $post->ID, 'staff_facebook', true );

			include( get_stylesheet_directory() . '/partials/detail_meta_box-' . self::POST_TYPE . '.php');
		}

		public function validate_meta( $data, $postarr )
		{
			if (!is_admin() || !isset( $data['post_type'] ) || self::POST_TYPE !== $data['post_type'] || 'trash' === $data['post_status'] ) {
				return $data;
			}

			$firstname = isset( $postarr['staff_firstname'] ) ? trim( $postarr['staff_firstname'] ) : '';
			$lastname = isset( $postarr['staff_lastname'] ) ? trim( $postarr['staff_lastname'] ) : '';

			if ( empty( $firstname ) || empty( $lastname ) ) {
				$data['post_status'] = 'draft';
			}

			return $data;
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

			wp_delete_object_term_relationships( $post_id, self::POST_TYPE . '_promo' );
			if ( isset( $_POST[self::POST_TYPE . '_promo'] ) ) {
				wp_set_post_terms( $post_id, implode( ',', $_POST[self::POST_TYPE . '_promo'] ), self::POST_TYPE . '_promo' );
			}

			update_post_meta( $post_id, 'staff_firstname', $_POST['staff_firstname'] );
			update_post_meta( $post_id, 'staff_lastname', $_POST['staff_lastname'] );
			update_post_meta( $post_id, 'staff_nickname', $_POST['staff_nickname'] );
			update_post_meta( $post_id, 'staff_role', $_POST['staff_role'] );
			update_post_meta( $post_id, 'staff_facebook', $_POST['staff_facebook'] );
		}

		public function create_taxonomies()
		{
			$this->create_promo_taxonomy();
		}

		protected function create_promo_taxonomy()
		{
			register_taxonomy( self::POST_TYPE . '_promo', self::POST_TYPE, array(
				'labels' => array(
					'name' => __( 'Promos', 'tvjussieu' ),
					'singular_name' => __( 'Promo', 'tvjussieu' ),
					'menu_name' => __( 'Les promos', 'tvjussieu' ),
					'all_items'                  => __( 'Toutes les promos', 'tvjussieu' ),
					//'parent_item'                => __( 'Parent Item', 'tvjussieu' ),
					//'parent_item_colon'          => __( 'Parent Item:', 'tvjussieu' ),
					'new_item_name' => __( 'Nouvelle promo', 'tvjussieu' ),
					'add_new_item' => __( 'Ajouter une promo', 'tvjussieu' ),
					'edit_item' => __( 'Modifier la promo', 'tvjussieu' ),
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
				'capabilities' => array(
					'manage_terms' => 'manage_options',
					'edit_terms' => 'manage_options',
					'delete_terms' => 'manage_options',
					'assign_terms' => 'edit_posts',
				),
				'rewrite' => array(
					'slug' => self::POST_TYPE,
					'with_front' => true,
				//'hierarchical' => true,
				),
				'meta_box_cb' => false, //array($this, 'add_tv_show_meta_boxes')
			) );
			add_rewrite_tag( '%staff_promo%', '([0-9]+\-[0-9]+)' );
		}

		public function staff_link( $link, $post = 0 )
		{
			if ( $post->post_type == self::POST_TYPE ) {
				$link = home_url( self::SLUG . '/' . $post->post_name );
			}
			return $link;
		}
	}
}