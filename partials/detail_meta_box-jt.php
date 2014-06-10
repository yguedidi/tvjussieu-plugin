<p><strong><?php _e( 'Type de JT', 'tvjussieu' ); ?></strong></p>
<p>
	<?php foreach ( $all_types as $t ): ?>
		<label>
			<input type="radio" name="jt_type" <?php checked( $t->slug, $type ); ?> value="<?php echo esc_attr( $t->slug ); ?>" />
			<?php echo $t->name; ?>
		</label><br/>
	<?php endforeach; ?>
</p>
<p><strong><?php _e( 'Saison', 'tvjussieu' ); ?></strong></p>
<p>
	<select name="jt_season">
	<?php foreach ( $all_seasons as $s ): ?>
		<option <?php selected( $s->slug, $season ); ?> value="<?php echo esc_attr( $s->slug ); ?>" />
			<?php echo $s->name; ?>
		</option>
	<?php endforeach; ?>
	</select>
</p>
<p><strong><?php _e( 'JT n°', 'tvjussieu' ); ?></strong></p>
<p><input type="number" name="jt_n" value="<?php echo esc_attr( $n ); ?>" /></p>
<p><strong><?php _e( 'Lien Dailymotion', 'tvjussieu' ); ?></strong></p>
<p><input type="url" name="jt_dailymotion" value="<?php echo esc_attr( $dailymotion ); ?>" /></p>
<p><strong><?php _e( 'Lien YouTube', 'tvjussieu' ); ?></strong></p>
<p><input type="url" name="jt_youtube" value="<?php echo esc_attr( $youtube ); ?>" /></p>
