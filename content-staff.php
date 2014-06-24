<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php //twentyfourteen_post_thumbnail(); ?>

	<header class="entry-header">
		<?php if ( in_array( 'category', get_object_taxonomies( get_post_type() ) ) && twentyfourteen_categorized_blog() ) : ?>
		<div class="entry-meta">
			<span class="cat-links"><?php echo get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'twentyfourteen' ) ); ?></span>
		</div>
		<?php
			endif;

			if ( is_single() ) :
				the_title( '<h1 class="entry-title">', '</h1>' );
			else :
				the_title( '<h1 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h1>' );
			endif;
		?>

		<div class="entry-meta">
			<?php
				if ( 'post' == get_post_type() )
					twentyfourteen_posted_on();

				if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) :
			?>
			<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'twentyfourteen' ), __( '1 Comment', 'twentyfourteen' ), __( '% Comments', 'twentyfourteen' ) ); ?></span>
			<?php
				endif;

				edit_post_link( __( 'Edit', 'twentyfourteen' ), '<span class="edit-link">', '</span>' );
			?>
		</div><!-- .entry-meta -->
	</header><!-- .entry-header -->
	<div class="entry-content">
	<?php if ( is_singular() ) :?>
		<div>
		<?php the_post_thumbnail(); ?>
		</div>
	<?php else : ?>
		<a href="<?php the_permalink(); ?>">
		<?php the_post_thumbnail(); ?>
		</a>
	<?php endif; // End is_singular() ?>
	</div>

	<?php if ( is_search() || is_post_type_archive( TVJussieu_Staff::POST_TYPE ) ) : ?>
	<div class="entry-summary">
		<?php the_excerpt(); ?>
	</div><!-- .entry-summary -->
	<?php else : ?>
	<div class="entry-content">
		<?php echo do_shortcode( '[facebook_like_button]' ); ?><br/><br/>
		<?php
		$firstname = get_post_meta( get_the_ID(), 'staff_firstname', true );
		$lastname = get_post_meta( get_the_ID(), 'staff_lastname', true );
		$nickname = get_post_meta( get_the_ID(), 'staff_nickname', true );
		$role = get_post_meta( get_the_ID(), 'staff_role', true );
		$facebook = get_post_meta( get_the_ID(), 'staff_facebook', true );
		?>
		<p><strong><?php echo $firstname . ' ' . $lastname; ?></strong><?php
		if ( $nickname ) {
			_e( sprintf( ', a.k.a. %s', $nickname ), 'tvjussieu' );
		}
		echo '<br/>';
		if ( $role ) {
			_e( sprintf( '<strong>Rôle</strong> : %s<br/>', $role ), 'tvjussieu' );
		}
		if ( get_the_terms( get_the_ID(), 'staff_promo' ) ) {
			_e( sprintf( '<strong>Présent(e) en</strong> : %s<br/>', get_the_term_list( get_the_ID(), 'staff_promo', '', ', ', '' ) ), 'tvjussieu' );
		}
		if ( $facebook ) {
			_e( sprintf( '<a href="%s">Aller sur son Facebook</a><br/>', esc_attr( $facebook ) ), 'tvjussieu' );
		}
		?></p>
		<?php
			the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'twentyfourteen' ) );
			wp_link_pages( array(
				'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'twentyfourteen' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
			) );
		?>
	</div><!-- .entry-content -->
	<?php endif; ?>

	<?php the_tags( '<footer class="entry-meta"><span class="tag-links">', '', '</span></footer>' ); ?>
</article><!-- #post-## -->
