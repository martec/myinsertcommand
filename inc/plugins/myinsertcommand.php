<?php
/**
 * My Insert Command
 * https://github.com/martec
 *
 * Copyright (C) 2015-2015, Martec
 *
 * My Insert Command is licensed under the GPL Version 3, 29 June 2007 license:
 *	http://www.gnu.org/copyleft/gpl.html
 *
 * @fileoverview My Insert Command - Insert new command in Sceditor for Mybb
 * @author Martec
 * @requires jQuery and Mybb
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

define('MIC_PLUGIN_VER', '1.0.2');

function myinsertcommand_info()
{
	global $lang;

	return array(
		'name'			=> 'My Insert Command',
		'description'	=> $lang->myinsertcommand_plug_desc,
		'website'		=> '',
		'author'		=> 'martec',
		'authorsite'	=> '',
		'version'		=> MIC_PLUGIN_VER,
		'compatibility' => '18*'
	);

}

function myinsertcommand_install()
{
	global $db, $lang;

	$lang->load('config_myinsertcommand');

	$query	= $db->simple_select("settinggroups", "COUNT(*) as counts");
	$dorder = $db->fetch_field($query, 'counts') + 1;

	$groupid = $db->insert_query('settinggroups', array(
		'name'		=> 'myinsertcommand',
		'title'		=> 'My Insert Command',
		'description'	=> $lang->myinsertcommand_sett_desc,
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

	$query = $db->simple_select("settinggroups", "COUNT(*) as counts", "name = 'myinsertcommand'");
	$counts  = $db->fetch_field($query, 'counts');

	return ($counts > 0);
}

function myinsertcommand_uninstall()
{
	global $db;

	$db->write_query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name IN('myinsertcommand_rules')");
	$db->delete_query("settinggroups", "name = 'myinsertcommand'");
}

function myinsertcommand_activate()
{
	global $db, $plugins_cache;

	require_once MYBB_ROOT."inc/adminfunctions_templates.php";

	find_replace_templatesets(
		'codebuttons',
		'#' . preg_quote('<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/editor_themes/{$theme[\'editortheme\']}" type="text/css" media="all" />') . '#i',
		'<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/editor_themes/{$theme[\'editortheme\']}" type="text/css" media="all" />
<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/myinsertcommand/myinsertcommand.css" type="text/css" media="all" />
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/sceditor/myinsertcommand/insertcommand.js?ver='.MIC_PLUGIN_VER.'"></script>'
	);

	find_replace_templatesets(
		'codebuttons',
		'#' . preg_quote('<script type="text/javascript">') . '#i',
		"<script type=\"text/javascript\">
var insertbutton = '';
if (!'{\$mybb->settings['myinsertcommand_rules']}'.trim() == ''){
	insertcommand('{\$mybb->settings['myinsertcommand_rules']}');
	insertbutton = 'insert|';
}"
	);

	find_replace_templatesets(
		'codebuttons',
		'#' . preg_quote('maximize,') . '#i',
		'"+insertbutton+"maximize,'
	);

	if ($plugins_cache['active']['quickadveditorplus'] or $plugins_cache['active']['quickadveditor']) {
		find_replace_templatesets(
			'codebutquick',
			'#' . preg_quote('<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/editor_themes/{$theme[\'editortheme\']}" type="text/css" media="all" />') . '#i',
			'<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/editor_themes/{$theme[\'editortheme\']}" type="text/css" media="all" />
<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/myinsertcommand/myinsertcommand.css" type="text/css" media="all" />
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/sceditor/myinsertcommand/insertcommand.js?ver='.MIC_PLUGIN_VER.'"></script>'
		);

		find_replace_templatesets(
			'codebutquick',
			'#' . preg_quote('<script type="text/javascript">') . '#i',
			"<script type=\"text/javascript\">
var insertbutton = '';
if (!'{\$mybb->settings['myinsertcommand_rules']}'.trim() == ''){
	insertcommand('{\$mybb->settings['myinsertcommand_rules']}');
	insertbutton = 'insert|';
}"
		);

		find_replace_templatesets(
			'codebutquick',
			'#' . preg_quote('maximize,') . '#i',
			'"+insertbutton+"maximize,'
		);

		find_replace_templatesets(
			'codebutquick_pm',
			'#' . preg_quote('<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/editor_themes/{$theme[\'editortheme\']}" type="text/css" media="all" />') . '#i',
			'<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/editor_themes/{$theme[\'editortheme\']}" type="text/css" media="all" />
<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/myinsertcommand/myinsertcommand.css" type="text/css" media="all" />
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/sceditor/myinsertcommand/insertcommand.js?ver='.MIC_PLUGIN_VER.'"></script>'
		);

		find_replace_templatesets(
			'codebutquick_pm',
			'#' . preg_quote('<script type="text/javascript">') . '#i',
			"<script type=\"text/javascript\">
var insertbutton = '';
if (!'{\$mybb->settings['myinsertcommand_rules']}'.trim() == ''){
	insertcommand('{\$mybb->settings['myinsertcommand_rules']}');
	insertbutton = 'insert|';
}"
		);

		find_replace_templatesets(
			'codebutquick_pm',
			'#' . preg_quote('maximize,') . '#i',
			'"+insertbutton+"maximize,'
		);
	}
}

function myinsertcommand_deactivate()
{
	global $db, $plugins_cache;
	include_once MYBB_ROOT."inc/adminfunctions_templates.php";

	find_replace_templatesets(
		'codebuttons',
		'#' . preg_quote('<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/editor_themes/{$theme[\'editortheme\']}" type="text/css" media="all" />
<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/myinsertcommand/myinsertcommand.css" type="text/css" media="all" />
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/sceditor/myinsertcommand/insertcommand.js?ver='.MIC_PLUGIN_VER.'"></script>') . '#i',
		'<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/editor_themes/{$theme[\'editortheme\']}" type="text/css" media="all" />'
	);

	find_replace_templatesets(
		'codebuttons',
		'#' . preg_quote("<script type=\"text/javascript\">
var insertbutton = '';
if (!'{\$mybb->settings['myinsertcommand_rules']}'.trim() == ''){
	insertcommand('{\$mybb->settings['myinsertcommand_rules']}');
	insertbutton = 'insert|';
}") . '#i',
		'<script type="text/javascript">'
	);

	find_replace_templatesets(
		'codebuttons',
		'#' . preg_quote('"+insertbutton+"maximize,') . '#i',
		'maximize,'
	);

	if ($plugins_cache['active']['quickadveditorplus'] or $plugins_cache['active']['quickadveditor']) {
		find_replace_templatesets(
			'codebutquick',
			'#' . preg_quote('<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/editor_themes/{$theme[\'editortheme\']}" type="text/css" media="all" />
<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/myinsertcommand/myinsertcommand.css" type="text/css" media="all" />
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/sceditor/myinsertcommand/insertcommand.js?ver='.MIC_PLUGIN_VER.'"></script>') . '#i',
			'<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/editor_themes/{$theme[\'editortheme\']}" type="text/css" media="all" />'
		);

		find_replace_templatesets(
			'codebutquick',
			'#' . preg_quote("<script type=\"text/javascript\">
var insertbutton = '';
if (!'{\$mybb->settings['myinsertcommand_rules']}'.trim() == ''){
	insertcommand('{\$mybb->settings['myinsertcommand_rules']}');
	insertbutton = 'insert|';
}") . '#i',
			'<script type="text/javascript">'
		);

		find_replace_templatesets(
			'codebutquick',
			'#' . preg_quote('"+insertbutton+"maximize,') . '#i',
			'maximize,'
		);

		find_replace_templatesets(
			'codebutquick_pm',
			'#' . preg_quote('<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/editor_themes/{$theme[\'editortheme\']}" type="text/css" media="all" />
<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/myinsertcommand/myinsertcommand.css" type="text/css" media="all" />
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/sceditor/myinsertcommand/insertcommand.js?ver='.MIC_PLUGIN_VER.'"></script>') . '#i',
			'<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/sceditor/editor_themes/{$theme[\'editortheme\']}" type="text/css" media="all" />'
		);

		find_replace_templatesets(
			'codebutquick_pm',
			'#' . preg_quote("<script type=\"text/javascript\">
var insertbutton = '';
if (!'{\$mybb->settings['myinsertcommand_rules']}'.trim() == ''){
	insertcommand('{\$mybb->settings['myinsertcommand_rules']}');
	insertbutton = 'insert|';
}") . '#i',
			'<script type="text/javascript">'
		);

		find_replace_templatesets(
			'codebutquick_pm',
			'#' . preg_quote('"+insertbutton+"maximize,') . '#i',
			'maximize,'
		);		
	}
}
?>
