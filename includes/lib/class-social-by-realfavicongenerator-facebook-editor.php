<?php

class Social_by_RealFaviconGenerator_Facebook_Editor {

	public static function facebook_editor( $post ) {

		$openGraphSerializedData =
			get_post_meta( $post->ID,
				Social_by_RealFaviconGenerator::OPTION_OG_SERIALIZED_DATA, true );

		$imageId =
			get_post_meta( $post->ID,
				Social_by_RealFaviconGenerator::OPTION_OG_IMAGE_ID, true );
		if ($imageId) {
			$imageUrl = wp_get_attachment_url( $imageId );
		}

		ob_start();
?>
	<div class="social-by-rfg-wrap custom-field-panel sbrfg-editor" id="sbrfg-editor">
    <div class="notice notice-warning">
      <h3>Social by RealFaviconGenerator is now Resoc Social Editor</h3>

      <p>
        Resoc Social Editor has been written by the same author,
        supports WordPress 5 / Gutenberg and has more features.
      </p>

      <p>
        To migrate, 
        <a
          href="<?php echo admin_url( "plugin-install.php?tab=search&type=term&s=Resoc+Social+Editor" ) ?>"
          target="_blank"
        >
          install and activate Resoc Social Editor
        </a>.
        Then, 
        <a
          href="<?php echo admin_url( "plugins.php" ) ?>"
          target="_blank"
        >
          deactivate and delete Social by RealFaviconGenerator
        </a>.
      </p>
    </div>

		<div>
			<h3>By <a href="https://realfavicongenerator.net/social" target="_blank">RealFaviconGenerator</a></h3>
		</div>

		<div class="sbrfg-editor-overall-container" <?php echo $imageId ? '' : 'style="display:none"' ?>>
			<div class="sbrfg-preview-container">
				<div class="sbrfg-platform-switcher-container"></div>
				<div class="sbrfg-facebook-editor"></div>
			</div>

			<div class="sbrfg-fields">
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="sbrfg-title">Title</label></th>
						<td><input type="text" name="sbrfg-title" placeholder="A title you should change"></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="sbrfg-description">Description</label></th>
						<td><textarea rows="3" name="sbrfg-description" placeholder="A description you should change, too"></textarea></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="sbrfg-format">Format</label></th>
						<td class="format-radios-container">
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="sbrfg-scale">Scale</label></th>
						<td><input type="range" name="sbrfg-scale" min="0" max="100"></td>
					</tr>
				</table>
			</div>
			<div class="sbrfg-clear-fix"></div>
			<input type="hidden" name="sbrfg-og-serialized-data">
			<input type="hidden" name="sbrfg-og-image-id">
		</div>

		<div>
			<div class="sbrfg-image-selection-container sbrfg-existing-image" <?php echo $imageId ? '' : 'style="display:none"' ?>>
				<button class="sbrfg-image-selection-button button-secondary">Select another Facebook image</button>
			</div>
			<div class="sbrfg-image-selection-container sbrfg-no-existing-image" <?php echo $imageId ? 'style="display:none"' : '' ?>>
				<p>Choose how your visitors will see your post when they share it on Facebook.</p>
				<button class="sbrfg-image-selection-button button-primary">Select Facebook image</button>
			</div>
		</div>

	</div>

	<div id="sbrfg-upgrade-notice" style="display:none">
		Your version of the plugin is outdated.
		Please <a href="<?php echo get_site_url( null, '/wp-admin/plugins.php' ) ?>" target="_blank">
			visit your plugins page</a> and update <strong>Social by RealFaviconGenerator</strong>.
	</div>

	<script>
		jQuery(document).ready(function(e) {
			var imageId = <?php echo $imageId ? $imageId : 'undefined' ?>;
			var imageUrl = <?php echo $imageUrl ? '"' . $imageUrl . '"' : 'undefined' ?>;
			sbrfgInitSocialEditor(
				jQuery('#sbrfg-editor'),
				imageId, imageUrl,
				<?php echo $openGraphSerializedData ? $openGraphSerializedData : 'undefined' ?>,
				'<?php echo get_site_url() ?>');
		});
	</script>
<?php
		return ob_get_clean();
	}
}
