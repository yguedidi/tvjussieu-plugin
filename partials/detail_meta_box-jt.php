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
<div id="jt_number_field">
<p><strong><?php _e( 'JT n°', 'tvjussieu' ); ?></strong></p>
<p><input type="number" name="jt_n" value="<?php echo esc_attr( $n ); ?>" /></p>
</div>
<p><strong><?php _e( 'Lien vidéo', 'tvjussieu' ); ?>*</strong></p>
<p><input type="url" name="jt_video" value="<?php echo esc_attr( $video ); ?>" required /></p>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('input[name=jt_type]').change(function(e) {
			isJT = 'jt' === $('input[name=jt_type]:checked').val();
			$('#jt_number_field').toggle(isJT);
			$('#jt_number_field input[name=jt_n]').prop('required', isJT);
		}).change();

		$('input[name=jt_type], select[name=jt_season]').change(function(e) {
			isJTSpecial = 'jt-special' === $('input[name=jt_type]:checked').val();
			isHorsSaison = 'hors-saison' === $('select[name=jt_season]').val();
			$('input[name=post_title]').prop('required', isHorsSaison || isJTSpecial);
		}).change();
	});
</script>