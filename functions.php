<?php

if ( !class_exists( 'TVJussieu' ) ) {

	class TVJussieu
	{
		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
			if ( !class_exists( 'TVJussieu_JT' ) ) {
				require_once dirname( __FILE__ ) . '/post-types/jt.php';
			}

			if ( !class_exists( 'TVJussieu_Staff' ) ) {
				require_once dirname( __FILE__ ) . '/post-types/staff.php';
			}

			if ( class_exists( 'TVJussieu_JT' ) ) {
				$tvj_jt = new TVJussieu_JT();
			}

			if ( class_exists( 'TVJussieu_Staff' ) ) {
				$tvj_staff = new TVJussieu_Staff();
			}

			add_filter( 'fb_meta_tags', array($this, 'facebook_og_metas') );
		}

		public function facebook_og_metas( $metas )
		{
			if ( is_front_page() ) {
				$metas['http://ogp.me/ns#type'] = 'video.tv_show';
			}

			return $metas;
		}

		/**
		 * Activate the plugin
		 */
		public static function activate()
		{
			//flush_rewrite_rules();
		}

		/**
		 * Deactivate the plugin
		 */
		public static function deactivate()
		{
			//flush_rewrite_rules();
		}

	}

}

if ( class_exists( 'TVJussieu' ) ) {
	register_activation_hook( __FILE__, array('TVJussieu', 'activate') );
	register_deactivation_hook( __FILE__, array('TVJussieu', 'deactivate') );

	$tvj = new TVJussieu();
}

function redirect_to_post(){
    global $wp_query;
    if( is_archive() && $wp_query->post_count == 1 ){
        the_post();
        $post_url = get_permalink();
        wp_redirect( $post_url );
    }
}
add_action('template_redirect', 'redirect_to_post');
