<p><strong><?php echo __( 'Type', 'tvjussieu' ); ?></strong></p>
<p>
<?php foreach ( $all_types as $type ): ?>
	<label>
		<input type="radio" name="jt_type" <?php checked( $type->slug, $current_type ); ?> value="<?php echo $type->slug; ?>" />
		<?php echo $type->name; ?>
	</label><br/>
<?php endforeach; ?>
</p>
<p><strong><?php echo __( 'Season', 'tvjussieu' ); ?></strong></p>
<p>
<?php foreach ( $all_seasons as $season ): ?>
	<label>
		<input type="radio" name="jt_season" <?php checked( $season->slug, $current_season ); ?> value="<?php echo $season->slug; ?>" />
		<?php echo $season->name; ?>
	</label><br/>
<?php endforeach; ?>
</p>
<p><strong><?php echo __( 'JT number', 'tvjussieu' ); ?></strong></p>
<p><input type="number" name="jt_n" value="<?php echo $n; ?>" /></p>
<p><strong><?php echo __( 'Dailymotion link', 'tvjussieu' ); ?></strong></p>
<p><input type="url" name="jt_dailymotion" value="<?php echo $dailymotion; ?>" /></p>
<p><strong><?php echo __( 'YouTube link', 'tvjussieu' ); ?></strong></p>
<p><input type="url" name="jt_youtube" value="<?php echo $youtube; ?>" /></p>