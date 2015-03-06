function insertcommand(newcmd) {
	/**********************
	 * Add insert command *
	 **********************/
	$.sceditor.command.set('insert', {
		_dropDown: function (editor, caller, html) {
			var content, insertype, description;

			content = $(
				'<div>' +
					'<label for="insertype">' + editor._('Insert...') + '</label> ' +
					'<select id="insertype">' +
						'<option value="null" data-desc="no">' + editor._('---------') + '</option>' +
					'</select>' +
				'</div>' +
				'<div id="desc" style="display: none">' +
					'<label for="des">' + editor._('Description (optional):') + '</label> ' +
					'<input type="text" id="des" />' +
				'</div>' +
				'<div><input type="button" class="button" value="' + editor._('Insert') + '" /></div>'
			);
			
			icm_cmd_rls = newcmd.split(";");
			for (var i = icm_cmd_rls.length-1; i >= 0; i--) {
				spl_icm_cmd_rls = icm_cmd_rls[i].split(",");
				content.find('#insertype').append('<option value="'+spl_icm_cmd_rls[1]+'" data-desc="'+spl_icm_cmd_rls[2]+'">' + editor._(spl_icm_cmd_rls[0]) + '</option> +');
			}

			content.change(function () {
				$('select option:selected').each(function() {
					if ($(this).attr('data-desc') === 'no') {
						$('#desc').hide();
					}
					else {
						$('#desc').show();
					}
				});
			}).change();

			content.find('.button').click(function (e) {
				insertype = content.find('#insertype').val();
				description = content.find('#des').val();
				before = '[' + insertype + ']';
				end = '[/' + insertype + ']';

				if (insertype === "null") {
					editor.closeDropDown(true);
					return;
				}

				if (description) {
					descriptionAttr = '=' + description + '';
					before = '[' + insertype + ''+ descriptionAttr +']';
				}

				if (html) {
					before = before + html + end;
					end	   = null;
				}

				editor.insert(before, end);
				editor.closeDropDown(true);
				e.preventDefault();
			});

			editor.createDropDown(caller, 'insertcommand', content);
		},
		exec: function (caller) {
			$.sceditor.command.get('insert')._dropDown(this, caller);
		},
		txtExec: function (caller) {
			$.sceditor.command.get('insert')._dropDown(this, caller);
		},
		tooltip: 'Insert...'
	});
}