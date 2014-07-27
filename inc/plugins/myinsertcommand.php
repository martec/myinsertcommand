<?php
// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if(!defined("PLUGINLIBRARY"))
{
	define("PLUGINLIBRARY", MYBB_ROOT."inc/plugins/pluginlibrary.php");
}

function myinsertcommand_info()
{
	global $mybb, $plugins_cache;

	$info = array(
		'name'			=> 'My Insert Command',
		'description'	=> 'Insert new command in Sceditor',
		'website'		=> '',
		'author'		=> 'martec',
		'authorsite'	=> '',
		'version'		=> '0.1.1',
		'guid'			=> '',
		'compatibility' => '17*,18*'
	);

	if(myinsertcommand_is_installed() && $plugins_cache['active']['myinsertcommand'])
	{
		global $PL;
		$PL or require_once PLUGINLIBRARY;

		$editcode = $PL->url_append("index.php?module=config-plugins", array("myinsertcommand" => "edit", "my_post_key" => $mybb->post_code));
		$undocode = $PL->url_append("index.php", array("module" => "config-plugins", "myinsertcommand" => "undo", "my_post_key" => $mybb->post_code));

		$editcode = "index.php?module=config-plugins&amp;myinsertcommand=edit&amp;my_post_key=".$mybb->post_code;
		$undocode = "index.php?module=config-plugins&amp;myinsertcommand=undo&amp;my_post_key=".$mybb->post_code;

		$info["description"] .= "<br /><a href=\"{$editcode}\">Make edits to inc/functions.php</a>.";
		$info["description"] .= "	 | <a href=\"{$undocode}\">Undo edits to inc/functions.php</a>.";
	}

	return $info;
}

function myinsertcommand_install()
{
	global $db, $lang, $PL;

	if(!file_exists(PLUGINLIBRARY))
	{
		flash_message("PluginLibrary is missing.", "error");
		admin_redirect("index.php?module=config-plugins");
	}

	$lang->load('config_myinsertcommand');

	$query	= $db->simple_select("settinggroups", "COUNT(*) as rows");
	$dorder = $db->fetch_field($query, 'rows') + 1;

	$groupid = $db->insert_query('settinggroups', array(
		'name'		=> 'myinsertcommand',
		'title'		=> 'My Insert Command',
		'description'	=> 'Settings related to the My Insert Command.',
		'disporder'	=> $dorder,
		'isdefault'	=> '0'
	));

	$db->insert_query('settings', array(
		'name'		=> 'myinsertcommand_rules',
		'title'		=> $lang->myinsertcommand_rules_title,
		'description'	=> $lang->myinsertcommand_rules_desc,
		'optionscode'	=> 'textarea',
		'value'		=> '',
		'disporder'	=> '1',
		'gid'		=> $groupid
	));

	rebuild_settings();
}

function myinsertcommand_is_installed()
{
	global $db;

	$query = $db->simple_select("settinggroups", "COUNT(*) as rows", "name = 'myinsertcommand'");
	$rows  = $db->fetch_field($query, 'rows');

	return ($rows > 0);
}

function myinsertcommand_uninstall()
{
	global $db;

	$db->write_query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name IN('myinsertcommand_rules')");
	$db->delete_query("settinggroups", "name = 'myinsertcommand'");

	global $PL;
	$PL or require_once PLUGINLIBRARY;

	$PL->edit_core("myinsertcommand", "inc/functions.php", array(), true);
}

function myinsertcommand_activate()
{
	global $db;

	if(!file_exists(PLUGINLIBRARY))
	{
		flash_message("PluginLibrary is missing.", "error");
		admin_redirect("index.php?module=config-plugins");
	}

	include_once MYBB_ROOT."inc/adminfunctions_templates.php";

	find_replace_templatesets(
		'codebuttons',
		'#' . preg_quote('<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/editor_themes/{$theme[\'editortheme\']}" type="text/css" media="all" />') . '#i',
		'<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/editor_themes/{$theme[\'editortheme\']}" type="text/css" media="all" />
<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/myinsertcommand/myinsertcommand.css" type="text/css" media="all" />'
	);

	find_replace_templatesets(
		'codebuttons',
		'#' . preg_quote('<script type="text/javascript">') . '#i',
		'{$insert_func}
<script type="text/javascript">'
	);

	find_replace_templatesets(
		'codebuttons',
		'#' . preg_quote('quote|') . '#i',
		'quote|{$insert}'
	);

	$template = array(
		"tid"		=> NULL,
		"title"		=> "insert_button",
		"template"	=> "<script type=\"text/javascript\">
	/**********************
	 * Add insert command *
	 **********************/
	$.sceditor.command.set(\'insert\', {
		_dropDown: function (editor, caller, html) {
			var content, insertype, description;

			content = \$(
				\'<div>\' +
					\'<label for=\"insertype\">\' + editor._(\'Insert...\') + \'</label> \' +
					\'<select id=\"insertype\">\' +
						\'<option value=\"null\" data-desc=\"no\">\' + editor._(\'---------\') + \'</option>\' +
						{\$command}
					\'</select>\' +
				\'</div>\' +
				\'<div id=\"desc\" style=\"display: none\">\' +
					\'<label for=\"des\">\' + editor._(\'Description (optional):\') + \'</label> \' +
					\'<input type=\"text\" id=\"des\" />\' +
				\'</div>\' +
				\'<div><input type=\"button\" class=\"button\" value=\"\' + editor._(\'Insert\') + \'\" /></div>\'
			);

			content.change(function () {
				\$(\'select option:selected\').each(function() {
					if (\$(this).attr(\'data-desc\') === \'no\') {
						\$(\'#desc\').hide();
					}
					else {
						\$(\'#desc\').show();
					}
				});
			}).change();

			content.find(\'.button\').click(function (e) {
				insertype = content.find(\'#insertype\').val();
				description = content.find(\'#des\').val();
				before = \'[\' + insertype + \']\';
				end = \'[/\' + insertype + \']\';

				if (insertype === \"null\") {
					editor.closeDropDown(true);
					return;
				}

				if (description) {
					descriptionAttr = \'=\' + description + \'\';
					before = \'[\' + insertype + \'\'+ descriptionAttr +\']\';
				}

				if (html) {
					before = before + html + end;
					end	   = null;
				}

				editor.insert(before, end);
				editor.closeDropDown(true);
				e.preventDefault();
			});

			editor.createDropDown(caller, \'insertcommand\', content);
		},
		exec: function (caller) {
			$.sceditor.command.get(\'insert\')._dropDown(this, caller);
		},
		txtExec: function (caller) {
			$.sceditor.command.get(\'insert\')._dropDown(this, caller);
		},
		tooltip: \'Insert...\'
	});
</script>",
		"sid"		=> "-1"
	);
	$db->insert_query("templates", $template);

	global $PL;
	$PL or require_once PLUGINLIBRARY;

	$PL->edit_core("myinsertcommand", "inc/functions.php",
		array(	'search' => array('$sourcemode = "MyBBEditor.sourceMode(true);";', '}'),
				'after' => array(
				'
				$insert = "";
				if(!empty($mybb->settings[\'myinsertcommand_rules\']))
				{
					$insert = "insert|";
					$replace_arr = explode("\n", $mybb->settings[\'myinsertcommand_rules\']);
					foreach ($replace_arr as $newcommand)
					{
						preg_match_all(\'/(?:\[name]|\[command]|\[description])(.*?)(?:\[\/name]|\[\/command]|\[\/description])/i\', $newcommand, $matches);
						$command .= "\'<option value=\"".$matches[1][1]."\" data-desc=\"".$matches[1][2]."\">\' + editor._(\'".$matches[1][0]."\') + \'</option>\' +";
					}
					eval("\$insert_func = \"".$templates->get("insert_button")."\";");
				}'
				),
		),
		true
	);
}

function myinsertcommand_deactivate()
{
	global $db;
	include_once MYBB_ROOT."inc/adminfunctions_templates.php";

	find_replace_templatesets(
		'codebuttons',
		'#' . preg_quote('<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/editor_themes/{$theme[\'editortheme\']}" type="text/css" media="all" />
<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/myinsertcommand/myinsertcommand.css" type="text/css" media="all" />') . '#i',
		'<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/editor_themes/{$theme[\'editortheme\']}" type="text/css" media="all" />'
	);

	find_replace_templatesets(
		'codebuttons',
		'#' . preg_quote('{$insert_func}
<script type="text/javascript">') . '#i',
		'<script type="text/javascript">'
	);

	find_replace_templatesets(
		'codebuttons',
		'#' . preg_quote('quote|{$insert}') . '#i',
		'quote|'
	);

	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='insert_button'");

	global $PL;
	$PL or require_once PLUGINLIBRARY;

	$PL->edit_core("myinsertcommand", "inc/functions.php", array(), true);
}

$plugins->add_hook("admin_config_plugins_begin", "myinsertcommand_edit");
function myinsertcommand_edit()
{
	global $mybb;

	if($mybb->input['my_post_key'] != $mybb->post_code)
	{
		return;
	}

	global $PL;
	$PL or require_once PLUGINLIBRARY;

	if($mybb->input['myinsertcommand'] == 'edit')
	{
		$result = $PL->edit_core("myinsertcommand", "inc/functions.php",
			array(	'search' => array('$sourcemode = "MyBBEditor.sourceMode(true);";', '}'),
					'after' => array(
					'
					$insert = "";
					if(!empty($mybb->settings[\'myinsertcommand_rules\']))
					{
						$insert = "insert|";
						$replace_arr = explode("\n", $mybb->settings[\'myinsertcommand_rules\']);
						foreach ($replace_arr as $newcommand)
						{
							preg_match_all(\'/(?:\[name]|\[command]|\[description])(.*?)(?:\[\/name]|\[\/command]|\[\/description])/i\', $newcommand, $matches);
							$command .= "\'<option value=\"".$matches[1][1]."\" data-desc=\"".$matches[1][2]."\">\' + editor._(\'".$matches[1][0]."\') + \'</option>\' +";
						}
						eval("\$insert_func = \"".$templates->get("insert_button")."\";");
					}'
					),
			),
			true
		);
	}

	else if($mybb->input['myinsertcommand'] == 'undo')
	{
		$result = $PL->edit_core("myinsertcommand", "inc/functions.php", array(), true);
	}

	else
	{
		return;
	}

	if($result === true)
	{
		flash_message("The file inc/functions.php was modified successfully.", "success");
		admin_redirect("index.php?module=config-plugins");
	}

	else
	{
		flash_message("The file inc/functions.php could not be edited. Are the CHMOD settings correct?", "error");
		admin_redirect("index.php?module=config-plugins");
	}
}

?>