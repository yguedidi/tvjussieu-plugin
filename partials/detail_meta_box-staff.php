<p><strong><?php echo __( 'Prénom', 'tvjussieu' ); ?>*</strong></p>
<p><input type="text" name="staff_firstname" value="<?php echo esc_attr( $firstname ); ?>" required /></p>
<p><strong><?php echo __( 'Nom', 'tvjussieu' ); ?>*</strong></p>
<p><input type="text" name="staff_lastname" value="<?php echo esc_attr( $lastname ); ?>" required /></p>
<p><strong><?php echo __( 'Surnom', 'tvjussieu' ); ?></strong></p>
<p><input type="text" name="staff_nickname" value="<?php echo esc_attr( $nickname ); ?>" /></p>
<p><strong><?php echo __( 'Promo', 'tvjussieu' ); ?></strong></p>
<p>
	<?php foreach ( $all_promos as $promo ): ?>
		<label>
			<input type="checkbox" name="staff_promo[]" <?php checked( in_array( $promo->slug, $current_promos ) ); ?> value="<?php echo esc_attr( $promo->slug ); ?>" />
			<?php echo $promo->name; ?>
		</label><br/>
	<?php endforeach; ?>
</p>
<p><strong><?php echo __( 'Rôle', 'tvjussieu' ); ?></strong></p>
<p><input type="text" name="staff_role" value="<?php echo esc_attr( $role ); ?>" /></p>
<p><strong><?php echo __( 'Lien Facebook', 'tvjussieu' ); ?></strong></p>
<p><input type="text" name="staff_facebook" value="<?php echo esc_attr( $facebook ); ?>" /></p>