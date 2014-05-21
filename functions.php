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
	register_activation_hook( __FILE__, array( 'TVJussieu', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'TVJussieu', 'deactivate' ) );

	$tvj = new TVJussieu();
}
