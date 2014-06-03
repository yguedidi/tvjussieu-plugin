<?php

if ( !class_exists( 'TVJussieu_JT' ) ) {
	class TVJussieu_JT
	{
		const POST_TYPE = 'jt';
		const SLUG = 'jt';

		public function __construct()
		{
			add_action( 'init', array($this, 'init') );
			add_action( 'admin_init', array($this, 'admin_init') );
		}

		public function init()
		{
			add_filter( 'post_type_link', array($this, 'jt_link'), 10, 3 );
			add_rewrite_rule(
				self::SLUG . '/([^/]+)/([^/]+)-([0-9]+)/?$', 'index.php?post_type=' . self::POST_TYPE . '&name=$matches[1]-$matches[2]-$matches[3]', 'top'
			);

			add_permastruct('jt_perma', self::SLUG . '/%jt_season%/%jt_type%');

			$this->create_taxonomies();
			$this->create_post_type();

			add_filter( 'fb_meta_tags', array($this, 'facebook_og_metas') );

			add_filter( 'the_title', array( $this, 'jt_title' ), 10, 2 );
		}

		public function admin_init()
		{
			add_filter( 'wp_insert_post_data', array($this, 'pre_save_post'), '99', 2 );
			add_action( 'save_post', array($this, 'save_post') );

			add_action( 'add_meta_boxes', array($this, 'add_meta_boxes') );

			add_filter( 'manage_edit-jt_columns', array($this, 'reorder_edit_column'), 10, 1 );
			add_filter( 'manage_edit-jt_sortable_columns', array($this, 'sortable_jt_name_column') );
			add_action( 'manage_jt_posts_custom_column', array($this, 'fill_jt_column'), 10, 2 );
			add_action( 'pre_get_posts', array($this, 'sort_by_jt_name') );
			add_action( 'admin_head', array($this, 'resize_jt_name_column') );
		}

		public function reorder_edit_column( $columns )
		{
			$columns['jt_name'] = __( 'JT', 'tvjussieu' );
			unset( $columns['taxonomy-jt_season'] );
			unset( $columns['taxonomy-jt_type'] );
			$order = array_flip( array('cb', 'jt_name', 'title', 'comments', 'date') );
			return array_merge($order, $columns);
		}

		public function sortable_jt_name_column( $columns ) {
			$columns['jt_name'] = 'jt_name';
			return $columns;
		}

		public function fill_jt_column( $column, $post_id )
		{
			global $post;
			switch ($column) {
				case 'jt_name':
					$season = get_the_terms( $post_id, self::POST_TYPE . '_season' );
					if ( !is_wp_error( $season ) && !empty( $season ) && is_object( reset( $season ) ) ) {
						$season = reset( $season );
						$season = sprintf( '<a href="%s">%s</a>',
							esc_url( add_query_arg( array( 'post_type' => self::POST_TYPE, 'jt_season' => $season->slug ), 'edit.php' ) ),
							esc_html( sanitize_term_field( 'name', $season->name, $season->term_id, 'jt_season', 'display' ) )
						);
					} else {
						$season = 'hors-saison';
					}

					$type = get_the_terms( $post_id, self::POST_TYPE . '_type' );
					if ( !is_wp_error( $type ) && !empty( $type ) && is_object( reset( $type ) ) ) {
						$type = reset( $type );
						$type = sprintf( '<a href="%s">%s</a>',
							esc_url( add_query_arg( array( 'post_type' => self::POST_TYPE, 'jt_type' => $type->slug ), 'edit.php' ) ),
							esc_html( sanitize_term_field( 'name', $type->name, $type->term_id, 'jt_type', 'display' ) )
						);
					} else {
						$type = 'jt';
					}

					echo $season . ' - ' . $type . ' - n°' . get_post_meta( $post_id, 'jt_n', true );
					break;
			}
		}

		public function sort_by_jt_name( WP_Query $query )
		{
			if ( !is_admin() || self::POST_TYPE != $query->get( 'post_type') ) {
				return;
			}

			if ( 'jt_name' == $query->get( 'orderby') ) {
				$query->set('orderby','name');
			}
		}

		public function resize_jt_name_column()
		{
			echo '<style>.widefat th.column-jt_name { width: 200px; }</style>';
		}

		public function jt_title( $title, $post_id = 0 )
		{
			$post = get_post($post_id);
			if ( !in_the_loop() || self::POST_TYPE != $post->post_type ) {
				return $title;
			}

			$title = $post->post_title;
			if ( !empty($title) ) {
				$title = ' - ' . $title;
			}

			$n = get_post_meta( $post->ID, 'jt_n', true );

			$types = get_the_terms( $post->ID, self::POST_TYPE . '_type' );
			if ( !is_wp_error( $types ) && !empty( $types ) && is_object( reset( $types ) ) ) {
				$type = reset( $types )->name;
			} else {
				$type = 'jt';
			}
			$title = $type .= ' n°' . $n . $title;

			if ( is_singular(self::POST_TYPE) || is_post_type_archive(self::POST_TYPE) ) {
				$seasons = get_the_terms( $post->ID, self::POST_TYPE . '_season' );
				if ( !is_wp_error( $seasons ) && !empty( $seasons ) && is_object( reset( $seasons ) ) ) {
					$season = reset( $seasons )->name;
				} else {
					$season = 'hors-saison';
				}
				$title = $season . ' - ' . $title;
			}

			return $title;
		}

		public function facebook_og_metas( $metas )
		{
			global $post;
			if ( $post && $post->post_type == self::POST_TYPE ) {
				$metas['http://ogp.me/ns#type'] = 'video.episode';

				$videos = array();
				$youtube = get_post_meta( $post->ID, self::POST_TYPE . '_youtube', true );
				$dailymotion = get_post_meta( $post->ID, self::POST_TYPE . '_dailymotion', true );
				if ($youtube) {
					preg_match('#^https?:\/\/www\.youtube\.com\/watch\?v\=(.*)#', $youtube, $matches);
					$videos[] = array(
						'url' => 'http://www.youtube.com/embed/' . $matches[1] . '?autoplay=1&rel=0',
						'secure_url' => 'https://www.youtube.com/embed/' . $matches[1] . '?autoplay=1&rel=0',
						'type' => 'text/html',
					);
					$videos[] = array(
						'url' => 'http://www.youtube.com/v/' . $matches[1] . '?autohide=1&amp;version=31',
						'secure_url' => 'https://www.youtube.com/v/' . $matches[1] . '?autohide=1&amp;version=31',
						'type' => 'application/x-shockwave-flash',
					);
					$metas['http://ogp.me/ns#image'] = 'http://img.youtube.com/vi/' . $matches[1] . '/sddefault.jpg';
				}

				if ($dailymotion) {
					preg_match('#^https?:\/\/www\.dailymotion\.com\/video\/([^_]+).*#', $dailymotion, $matches);
					$videos[] = array(
						'url' => 'http://www.dailymotion.com/embed/video/' . $matches[1],
						'secure_url' => 'https://www.dailymotion.com/embed/video/' . $matches[1],
						'type' => 'text/html',
					);
					$videos[] = array(
						'url' => 'http://www.dailymotion.com/swf/video/' . $matches[1] . '?autoPlay=1',
						'secure_url' => 'https://www.dailymotion.com/swf/video/' . $matches[1] . '?autoPlay=1',
						'type' => 'application/x-shockwave-flash',
					);
					$metas['http://ogp.me/ns#image'] = 'http://www.dailymotion.com/thumbnail/video/' . $matches[1];
				}

				if (!empty($videos)) {
					$metas['http://ogp.me/ns#video'] = $videos;
				}
			}

			return $metas;
		}

		public function create_post_type()
		{
			register_post_type( self::POST_TYPE, array(
				'label' => __( 'JT', 'tvjussieu' ),
				'description' => __( 'Un JT de TV Jussieu', 'tvjussieu' ),
				'labels' => array(
					'name' => __( 'JTs', 'tvjussieu' ),
					'singular_name' => __( 'JT', 'tvjussieu' ),
					'menu_name' => __( 'JTs', 'tvjussieu' ),
					//'parent_item_colon'   => __( 'Parent Item:', 'tvjussieu' ),
					'all_items' => __( 'Tous les JTs', 'tvjussieu' ),
					'view_item'           => __( 'Afficher le JT', 'tvjussieu' ),
					'add_new_item' => __( 'Ajouter un nouveau JT', 'tvjussieu' ),
					'add_new' => __( 'Ajouter un JT', 'tvjussieu' ),
				'edit_item'           => __( 'Modifier le JT', 'tvjussieu' ),
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

		public function jt_link( $link, $post = 0 )
		{
			if ( !$post instanceof WP_Post || $post->post_type !== self::POST_TYPE ) {
				return $link;
			}

			$types = get_the_terms( $post->ID, self::POST_TYPE . '_type' );
			$type = 'jt';
			if ( !is_wp_error( $types ) && !empty( $types ) && is_object( reset( $types ) ) ) {
				$type = reset( $types )->slug;
			}

			$seasons = get_the_terms( $post->ID, self::POST_TYPE . '_season' );
			$season = 'hors-saison';
			if ( !is_wp_error( $seasons ) && !empty( $seasons ) && is_object( reset( $seasons ) ) ) {
				$season = reset( $seasons )->slug;
			}

			$n = get_post_meta( $post->ID, 'jt_n', true );

			return home_url( self::SLUG . '/' . $season . '/' . $type . '-' . $n .'/' );
		}

		public function pre_save_post( $data, $postarr )
		{
			if ( !isset( $data['post_type'] ) || self::POST_TYPE !== $data['post_type'] ) {
				return $data;
			}

			$data['post_name'] = $postarr['jt_season'] . '-' . $postarr['jt_type'] . '-' . ( (int) $postarr['jt_n'] );

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

		public function add_meta_boxes()
		{
			add_meta_box(
				self::POST_TYPE . '_meta_box', __( 'Détails du JT', 'tvjussieu' ), array($this, 'add_detail_meta_box'), self::POST_TYPE
			);
		}

		public function add_detail_meta_box( $post )
		{
			$all_seasons = get_terms( self::POST_TYPE . '_season', array('hide_empty' => false) );
			$season = get_the_terms( $post->ID, self::POST_TYPE . '_season' );
			if ( !is_wp_error( $season ) && !empty( $season ) && is_object( reset( $season ) ) ) {
				$season = reset( $season )->slug;
			} else {
				$season = 'hors-saison';
			}

			$all_types = get_terms( self::POST_TYPE . '_type', array('hide_empty' => false) );
			$type = get_the_terms( $post->ID, self::POST_TYPE . '_type' );
			if ( !is_wp_error( $type ) && !empty( $type ) && is_object( reset( $type ) ) ) {
				$type = reset( $type )->slug;
			} else {
				$type = 'jt';
			}

			$n = get_post_meta( $post->ID, self::POST_TYPE . '_n', true );
			$dailymotion = get_post_meta( $post->ID, self::POST_TYPE . '_dailymotion', true );
			$youtube = get_post_meta( $post->ID, self::POST_TYPE . '_youtube', true );

			include( get_stylesheet_directory() . '/partials/detail_meta_box-' . self::POST_TYPE . '.php');
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
					'name' => __( 'Saisons', 'tvjussieu' ),
					'singular_name' => __( 'Saison', 'tvjussieu' ),
					'menu_name' => __( 'Saisons', 'tvjussieu' ),
					//'all_items'                  => __( 'All Items', 'tvjussieu' ),
					//'parent_item'                => __( 'Parent Item', 'tvjussieu' ),
					//'parent_item_colon'          => __( 'Parent Item:', 'tvjussieu' ),
					'new_item_name' => __( 'Nouvelle saison', 'tvjussieu' ),
					'add_new_item' => __( 'Ajouter une saison', 'tvjussieu' ),
				'edit_item'                  => __( 'Modifier la saison', 'tvjussieu' ),
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
				'rewrite' => array(
					'slug' => self::SLUG,
					'with_front' => true,
					'hierarchical' => false,
				),
				'meta_box_cb' => false,
			) );

			//add_filter( 'term_link', array( $this, 'jt_season_link' ), 10, 3 );
			add_rewrite_tag( '%jt_season%', '(.*saison.*)' );
		}

		/* public function jt_season_link( $url, $term, $taxonomy )
		  {
		  if ( $taxonomy === self::POST_TYPE . '_season' ) {
		  return home_url( self::SLUG . '/' . $term->slug );
		  }

		  return $url;
		  } */
		protected function create_type_taxonomy()
		{
			register_taxonomy( self::POST_TYPE . '_type', self::POST_TYPE, array(
				'labels' => array(
					'name' => __( 'Types de JT', 'tvjussieu' ),
					'singular_name' => __( 'Type de JT', 'tvjussieu' ),
					'menu_name' => __( 'Types de JT', 'tvjussieu' ),
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
				'rewrite' => true,
				/*'rewrite' => array(
					'slug' => 'jt-type',
					'with_front' => true,
					'hierarchical' => false,
				),*/
				'meta_box_cb' => false,
			) );
		}

	}

}