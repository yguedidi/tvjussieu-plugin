<p><strong><?php echo __( 'Period', 'tvjussieu' ); ?></strong></p>
<p>
<?php foreach ( $all_periods as $period ): ?>
	<label>
		<input type="checkbox" name="staff_period[]" <?php checked( in_array($period->slug, $current_periods ) ); ?> value="<?php echo esc_attr( $period->slug ); ?>" />
		<?php echo $period->name; ?>
	</label><br/>
<?php endforeach; ?>
</p>
<p><strong><?php echo __( 'First Name', 'tvjussieu' ); ?></strong></p>
<p><input type="text" name="staff_firstname" value="<?php echo esc_attr( $firstname ); ?>" /></p>
<p><strong><?php echo __( 'Last Name', 'tvjussieu' ); ?></strong></p>
<p><input type="text" name="staff_lastname" value="<?php echo esc_attr( $lastname ); ?>" /></p>
<p><strong><?php echo __( 'Role', 'tvjussieu' ); ?></strong></p>
<p><input type="text" name="staff_role" value="<?php echo esc_attr( $role ); ?>" /></p>
<p><strong><?php echo __( 'Facebook Profile', 'tvjussieu' ); ?></strong></p>
<p><input type="text" name="staff_facebook" value="<?php echo esc_attr( $facebook ); ?>" /></p>