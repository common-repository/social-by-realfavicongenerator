var sbrfgInitSocialEditor = function(editorContainer, imageId, imageUrl, settings, siteUrl) {
	var fileFrame;

	var editorContainer;
	var sbrfgEditor;

	var sbrfgScaleMin, sbrfgScaleMax;

	var fb;

	initImageSelection();

	initFormSerialization();

	if (imageId && imageUrl) {
		// There is something to show right now
		initAndShowEditor();
	}
	else {
		// Nothing to do: image selection button is displayed by default
	}

	function initAndShowEditor() {
		initEditor();

		editorContainer.find('.sbrfg-image-selection-container.sbrfg-no-existing-image').hide();
		editorContainer.find('.sbrfg-image-selection-container.sbrfg-existing-image').show();

		editorContainer.find('.sbrfg-editor-overall-container').fadeIn();
	}


	function initEditor() {
		fb = new RFGFacebook({
			apiKey: 'TODO',
			element: jQuery('#sbrfg-editor .sbrfg-facebook-editor'),
			master_image_src: imageUrl,
			format: 'wide',
			title: 'A title you should change',
			description: 'A description you should change, too',
			url: siteUrl,
			apiRequest: settings
		});

		if (settings) {
			// As there is en existing API request, initialize the form with the
			// editor's data
			editorContainer.find('input[name="sbrfg-title"]').val(fb.getTitle());
			editorContainer.find('textarea[name="sbrfg-description"]').val(fb.getDescription());
		}

		// Scale setting
		fb.on('scaleChange', function(e, currentScale, minScale, maxScale) {
			var range = editorContainer.find('input[name="sbrfg-scale"]');
			currentScale = RFGComponent.transposeInterval(currentScale, minScale, maxScale,
				range.attr('min'), range.attr('max'));
			range.val(currentScale);
		});

		fb.render();

		// Init UI

		// Platform
		var platforms = fb.getAvailablePlatforms();
		editorContainer.find('.sbrfg-platform-switcher-container').html('');
		Object.keys(platforms).forEach(function(platform) {
				var button = jQuery(
					'<button href="#" class="button-secondary" data-platform="' + platform + '" ' +
						((platform == fb.getPlatform()) ? ' disabled="disabled"' : '') + '>' +
					platforms[platform] + '</button>');
				button.click(function(e) {
					editorContainer.find('.sbrfg-platform-switcher-container button').removeAttr('disabled');
					button.attr('disabled', 'disabled');
					fb.setPlatform(platform);
					e.preventDefault();
				});
				editorContainer.find('.sbrfg-platform-switcher-container').append(button);
		});
		editorContainer.find('.sbrfg-platform-switcher-container input[name="platform"]').change(function() {
			var newPlatform = editorContainer.find('.sbrfg-platform-switcher-container input[name="platform"]:checked').val();
			fb.setPlatform(newPlatform);
		});

		// Format
		var formats = fb.getAvailableFormats();
		editorContainer.find('.format-radios-container').html('');
		var freeDuringTheBeta = ' <em>Free during the beta</em>';
		Object.keys(formats).forEach(function(format) {
			editorContainer.find('.format-radios-container').append('<label>'
					+ '<input type="radio" name="format" value="'
					+ format + '"'
					+ ((format == fb.getFormat()) ? ' checked' : '')
					+ '>' + formats[format]
					+ (format == 'wide' ? '' : freeDuringTheBeta)
				+ '</label>');
		});
		editorContainer.find('.format-radios-container input[name="format"]').change(function() {
			var newFormat = editorContainer.find('.format-radios-container input[name="format"]:checked').val();
			fb.setFormat(newFormat);
		});

		// Title
		editorContainer.find('input[name="sbrfg-title"]').on('input', function(val) {
			fb.setTitle(jQuery('input[name="sbrfg-title"]').val());
		});

		// Description
		editorContainer.find('textarea[name="sbrfg-description"]').on('input propertychange', function(val) {
			fb.setDescription(jQuery('textarea[name="sbrfg-description"]').val());
		});

		// Scale
		var is = editorContainer.find('input[name="sbrfg-scale"]');
		is.bind('propertychange change click keyup input paste', function() {
			var range = jQuery(this);
			fb.setScale(range.val(), range.attr('min'), range.attr('max'));
		});
	}

	function initImageSelection() {
		jQuery('.sbrfg-image-selection-button').live('click', function(event) {
			event.preventDefault();

			if (fileFrame) {
				fileFrame.open();
				return;
			}

			// Create the media frame.
			fileFrame = wp.media.frames.file_frame = wp.media({
				title: jQuery(this).data('uploader_title'),
				button: {
					text: jQuery(this).data('uploader_button_text'),
				},
				multiple: false
			});

			fileFrame.on('select', function() {
				attachment = fileFrame.state().get('selection').first().toJSON();
				imageId = attachment.id;
				imageUrl = attachment.url;

				initAndShowEditor();
			});

			fileFrame.open();
		});
	}

	function initFormSerialization() {
		var postForm = jQuery.find('#post');
		jQuery(document).on('submit', postForm, function() {
			if (fb) {
				var data = {};
				data = fb.getApiFaviconDesign();
				editorContainer.find('input[name="sbrfg-og-serialized-data"]').val(
					JSON.stringify(data));
				editorContainer.find('input[name="sbrfg-og-image-id"]').val(imageId);
			}
		});
	}

	/*
	function sbrfgInitEditors(settings, imageId, imageUrl) {
		sbrfgEditor.openGraphEditor();

		imageId = imageId;

		var allFormats = sbrfgEditor.getAllFormats();
		var radioContainer = sbrfgContainer.find('.format-radios-container');
		radioContainer.html('');
		jQuery.each(allFormats, function(format) {
			radioContainer.append('<p><input type="radio" name="sbrfg-format" value="' +
				format + '"> ' + allFormats[format] + '</p>');
		});

		sbrfgInitEditor();


		function sbrfgInitEditor() {
			sbrfgEditor.initComponent({
				master_img_src: imageUrl,
				serialized_data: settings.open_graph.facebook_open_graph,
				onScaleChange: function(e, min, max, current) {
					sbrfgScaleMin = min;
					sbrfgScaleMax = max;
					var range = sbrfgEditor.addScaleAmplitude(100, sbrfgScaleMin, sbrfgScaleMax, current);
					sbrfgContainer.find('input[name="sbrfg-scale"]').attr('min', range[0]);
					sbrfgContainer.find('input[name="sbrfg-scale"]').attr('max', range[1]);
					sbrfgContainer.find('input[name="sbrfg-scale"]').val(range[2]);
				},
				onInit: function() {
					sbrfgContainer.find('.sbrfg-platform-switcher-container').html('');
					var platforms = sbrfgEditor.getAllPlatforms();
					platforms.forEach(function(p) {
						var button = $(
							'<button href="#" class="button-secondary" data-platform="' + p + '">' +
							sbrfgEditor.getPlatformName(p) +
							'</button>');
						button.click(function(e) {
							sbrfgContainer.find('.sbrfg-platform-switcher-container button').removeAttr('disabled');
							button.attr('disabled', 'disabled');
							sbrfgEditor.setPlatform(p);
							e.preventDefault()
						});
						sbrfgContainer.find('.sbrfg-platform-switcher-container').append(button);
					});

					sbrfgEditor.setUrl(location.protocol + "//" +  window.location.hostname);

					sbrfgContainer.find('input[name="sbrfg-title"]').val(sbrfgEditor.getTitle());
					sbrfgContainer.find('input[name="sbrfg-title"]').bind('propertychange change click keyup input paste', function() {
				    sbrfgEditor.setTitle($(this).val());
				  });

					sbrfgContainer.find('textarea[name="sbrfg-description"]').val(sbrfgEditor.getDescription());
					sbrfgContainer.find('textarea[name="sbrfg-description"]').bind('propertychange change click keyup input paste', function() {
				    sbrfgEditor.setDescription($(this).val());
				  });

					var format = sbrfgEditor.getFormat();
					sbrfgContainer.find('input[name="sbrfg-format"][value="' + format + '"]').attr('checked', 'checked');
					sbrfgContainer.find('input[name="sbrfg-format"]').bind('propertychange change click keyup input paste', function() {
						sbrfgEditor.setFormat(sbrfgContainer.find('input[name="sbrfg-format"]:checked').val());
					});

					sbrfgContainer.find('input[name="sbrfg-scale"]').bind('propertychange change click keyup input paste', function() {
						var scale = sbrfgEditor.removeScaleAmplitude(100, sbrfgScaleMin, sbrfgScaleMax, $(this).val());
						sbrfgEditor.setScale(scale);
					});


					objectTypeField = sbrfgContainer.find('select[name="sbrfg-object-type"]');
					allTypes = sbrfgEditor.getAllObjectTypes();
					var currentType = sbrfgEditor.getObjectType() || 'article';
					jQuery.each(allTypes, function(t) {
						var selected = (t == currentType) ? ' selected' : '';
						objectTypeField.append('<option value="' + t + '"' + selected + '>'
							+ allTypes[t] + '</option>');
					});
					objectTypeField.bind('propertychange change click keyup input paste', function() {
				    sbrfgEditor.setObjectType($(this).find('option:selected').val());
				  });

					var postForm = jQuery.find('#post');
					$(document).on('submit', postForm, function() {
						var data = {};
						data.facebook_open_graph = sbrfgEditor.serializeForAPIRequest();
						sbrfgContainer.find('input[name="sbrfg-og-serialized-data"]').val(
							JSON.stringify(data));
						sbrfgContainer.find('input[name="sbrfg-og-image-id"]').val(imageId);
					});
				}
			});
		}
	}
	*/
}
