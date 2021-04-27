<?php

/*
This file is part of Theme Patcher
Theme Patcher is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
Theme Patcher is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with Theme Patcher.  If not, see <http://www.gnu.org/licenses/>.
*/

if (!defined("IN_MYBB")) {
	die("Hacking Attempt.");
}

function theme_fixer_info()
{
	global $lang, $cache;
	$lang->load("theme_fixer");

	$activePlugins = $cache->read("plugins");
	$description   = $lang->theme_fixer_desc;

	if (in_array("theme_fixer", $activePlugins['active'])) {
		$description = $lang->theme_fixer_desc_active;
	}

	return array(
		'name'			=> $lang->theme_fixer,
		'description'	=> $description,
		'website'		=> 'http://community.mybb.com',
		'author'		=> 'Darth Apple',
		'authorsite'	=> 'http://www.makestation.net',
		'version'		=> '1.0',
		"compatibility"	=> "18*"
	);
}


function theme_fixer_activate()
{
	global $lang, $db, $mybb;
	include MYBB_ROOT . 'inc/adminfunctions_templates.php';

	// This is our replacement form header compliant with MyBB 1.8.17+
	$replacement = '<form method="post" action="{$mybb->settings[\'bburl\']}/member.php">
		<input name="my_post_key" type="hidden" value="{$mybb->post_code}" />';

	// Create a list of templates to fix. We will iterate through each one.
	$templates_to_fix = array('error_nopermission', 'header_welcomeblock_guest', 'member_login', 'portal_welcome_guesttext');

	// Create an array to detect all sorts of form statements.
	// This plugin will attempt to detect any of the following possibilities, accounting for a wide variety of themes.

	$find_array = array(
		// These are the most common implementations:
		'<form method=\'post\' action=\'{$mybb->settings["bburl"]}/member.php\'>',
		'<form method=\'post\' action="{$mybb->settings[\'bburl\']}/member.php">',
		'<form method="post" action=\'{$mybb->settings["bburl"]}/member.php\'>',
		'<form method="post" action="{$mybb->settings[\'bburl\']}/member.php">',

		// Some very old themes do this:
		'<form method=\'post\' action=\'member.php\'>',
		'<form method="post" action="member.php">',
		'<form action=\'member.php\' method=\'post\'>',
		'<form action="member.php" method="post">',

		// Account for possible versions with extra whitespace between attributes or closing brackets
		'<form method=\'post\'  action=\'{$mybb->settings["bburl"]}/member.php\'>',
		'<form method=\'post\'  action="{$mybb->settings[\'bburl\']}/member.php">',
		'<form method="post"  action=\'{$mybb->settings["bburl"]}/member.php\'>',
		'<form method="post"  action="{$mybb->settings[\'bburl\']}/member.php">',
		'<form method=\'post\' action=\'{$mybb->settings["bburl"]}/member.php\' >',
		'<form method=\'post\' action="{$mybb->settings[\'bburl\']}/member.php" >',
		'<form method="post" action=\'{$mybb->settings["bburl"]}/member.php\' >',
		'<form method="post" action="{$mybb->settings[\'bburl\']}/member.php" >',
		'<form method=\'post\'  action=\'{$mybb->settings["bburl"]}/member.php\' >',
		'<form method=\'post\'  action="{$mybb->settings[\'bburl\']}/member.php" >',
		'<form method="post"  action=\'{$mybb->settings["bburl"]}/member.php\' >',
		'<form method="post"  action="{$mybb->settings[\'bburl\']}/member.php" >',

		// Account for possible reversed attribute order
		'<form action=\'{$mybb->settings["bburl"]}/member.php\' method=\'post\'>',
		'<form action="{$mybb->settings[\'bburl\']}/member.php" method=\'post\'>',
		'<form action=\'{$mybb->settings["bburl"]}/member.php\' method="post">',
		'<form action="{$mybb->settings[\'bburl\']}/member.php" method="post">',

		// Account for XHTML style tags. (< form .... />)
		'<form method=\'post\' action=\'{$mybb->settings["bburl"]}/member.php\' />',
		'<form method=\'post\' action="{$mybb->settings[\'bburl\']}/member.php" />',
		'<form method="post" action=\'{$mybb->settings["bburl"]}/member.php\' />',
		'<form method="post" action="{$mybb->settings[\'bburl\']}/member.php" />',
		'<form method=\'post\' action=\'{$mybb->settings["bburl"]}/member.php\'/>',
		'<form method=\'post\' action="{$mybb->settings[\'bburl\']}/member.php"/>',
		'<form method="post" action=\'{$mybb->settings["bburl"]}/member.php\'/>',
		'<form method="post" action="{$mybb->settings[\'bburl\']}/member.php"/>',

		// reversed order XHTML-style tags.
		'<form action=\'{$mybb->settings["bburl"]}/member.php\' method=\'post\' />',
		'<form action="{$mybb->settings[\'bburl\']}/member.php" method=\'post\' />',
		'<form action=\'{$mybb->settings["bburl"]}/member.php\' method="post" />',
		'<form action="{$mybb->settings[\'bburl\']}/member.php" method="post" />',
		'<form action=\'{$mybb->settings["bburl"]}/member.php\' method=\'post\'/>',
		'<form action="{$mybb->settings[\'bburl\']}/member.php" method=\'post\'/>',
		'<form action=\'{$mybb->settings["bburl"]}/member.php\' method="post"/>',
		'<form action="{$mybb->settings[\'bburl\']}/member.php" method="post"/>',

		// For error_nopermission (follows different convention on default theme:
		'<form action="member.php" method="post">',
		'<form action=\'member.php\' method=\'post\'>',
		'<form action="member.php" method="post" >', // Whitespace variations
		'<form action=\'member.php\' method=\'post\' >',
		'<form action="member.php"  method="post" >',
		'<form action=\'member.php\'  method=\'post\' >',
		'<form action="member.php"  method="post">',
		'<form action=\'member.php\'  method=\'post\'>', // More whitespace variations
		'<form action="member.php" method="post" />',
		'<form action="member.php" method="post"/>',
		'<form action=\'member.php\' method=\'post\' />',
		'<form action=\'member.php\' method=\'post\'/>',

		// Reverse attribute order
		'<form method="post" action="member.php">',
		'<form method=\'post\' action=\'member.php\'>',
		'<form method="post" action="member.php" >',
		'<form method=\'post\' action=\'member.php\' >',
		'<form method="post"  action="member.php">',
		'<form method=\'post\'  action=\'member.php\'>',
		'<form method="post" action="member.php" />',
		'<form method="post" action="member.php"/>',
		'<form method=\'post\' action=\'member.php\' />',
		'<form method=\'post\' action=\'member.php\'/>'
	);

	// ^^^^^^^ This, ladies and gentlemen, is why Regex exists. ^^^^^^^^^
	// (As ugly as the above code is, we're purposefully avoiding regex to ensure that only specific changes are made. As amazing as Regex is, it loves to match things. And we don't want it to match blindly; We'd inadvertently risk breaking a theme with malformed tags! )

	// Finally, run the replacements.
	foreach ($templates_to_fix as $t) {
		theme_fixer_replace_templates($t, $find_array, $replacement);
	}
}


function theme_fixer_deactivate()
{
	return; // Patches have no undo functionality. We don't want to "unpatch" a theme.
}


/*
     This is a new version of the template editing function included with MyBB. (See adminfunctions_templates.php)
     We are using str_replace instead of preg_replace because the strings are extremely specific.
     This reduces the risk of inadvertently editing aspects of the theme that are unrelated to the post code.

     More importantly, however, we check to see if the theme already has the {$post-code} before...
     ... we add it to the template. If so, we skip it. This prevents us from adding double
     ... input tags for the post codes.

     While this technically could have been done with the built in functions (and a lot of extremely complex regex),
     ...I am no regex genius, and this was FAR easier to test reliably. :)
	*/

function theme_fixer_replace_templates($title, $find_array, $replace, $autocreate = 1, $sid = FALSE, $limit = -1)
{
	global $db, $mybb;

	$return        = FALSE;
	$template_sets = array(-2, -1);

	// Select all templates with that title (including global) if not working on a specific template set
	$sqlwhere  = '>0 OR sid=-1';
	$sqlwhere2 = '>0';

	// Otherwise select just templates from that specific set
	if ($sid !== FALSE) {
		$sid       = (int)$sid;
		$sqlwhere2 = $sqlwhere = "=$sid";
	}

	// Select all other modified templates with that title
	$query = $db->simple_select("templates", "tid, sid, template", "title = '" . $db->escape_string($title) . "' AND (sid{$sqlwhere})");

	while ($template = $db->fetch_array($query)) {
		$template_sets[] = $template['sid']; // Keep track of which templates sets have a modified version of this template already
		$new_template    = theme_fixer_do_fix($template['template'], $find_array, $replace); // Perform replacements.

		if ($new_template == $template['template']) {
			continue;
		}

		// The template is a custom template.  Replace as normal.
		$updated_template = array(
			"template" => $db->escape_string($new_template)
		);

		$db->update_query("templates", $updated_template, "tid='{$template['tid']}'");
		$return = TRUE;
	}

	// Add any new templates if we need to and are allowed to
	if ($autocreate != 0) {
		// Select our master template with that title
		$query           = $db->simple_select("templates", "title, template", "title='" . $db->escape_string($title) . "' AND sid='-2'", array('limit' => 1));
		$master_template = $db->fetch_array($query);

		$master_template['new_template'] = theme_fixer_do_fix($master_template['template'], $find_array, $replace);

		if ($master_template['new_template'] != $master_template['template']) {
			// Update the rest of our template sets that are currently inheriting this template from our master set
			$query = $db->simple_select("templatesets", "sid", "sid NOT IN (" . implode(',', $template_sets) . ") AND (sid{$sqlwhere2})");
			while ($template = $db->fetch_array($query)) {
				$insert_template = array(
					"title" => $db->escape_string($master_template['title']),
					"template" => $db->escape_string($master_template['new_template']),
					"sid" => $template['sid'],
					"version" => $mybb->version_code,
					"status" => '',
					"dateline" => TIME_NOW
				);
				$db->insert_query("templates", $insert_template);
				$return = TRUE;
			}
		}
	}

	return $return;
}

// Because the primary template replacing function does replacements multiple times for different templates, we've abstracted the actual code for performing the replacements.
function theme_fixer_do_fix($template, $find_array, $replace)
{
	// Do some detection on the theme to see if we need to fix it. Set a flag to keep track.
	$template_fixed = FALSE;
	$new_template   = $template; // initialize to compare later.

	// An array of possible matches to determine if the template is already fixed.
	// We can search for the post code variable specifically. No unfixed themes have this variable,
	// So we can safely assume that if it's present, the theme has already been fixed.

	$fixed_lines = array(
		'<input name="my_post_key" type="hidden" value="{$mybb->post_code}" />', // Default "correct" value
		'<input name=\'my_post_key\' type=\'hidden\' value=\'{$mybb->post_code}\' />', // Detect alternative apostrophe version.
		'<input name="my_post_key" type="hidden" value="{$mybb->post_code}"/>', // removed whitespace
		'<input name=\'my_post_key\' type=\'hidden\' value=\'{$mybb->post_code}\'/>', // alt apostrophe, remove closing whitespace
		'<input name="my_post_key" type="hidden" value="{$mybb->post_code}">', // Non XHTML-style
		'<input name=\'my_post_key\' type=\'hidden\' value=\'{$mybb->post_code}\'>', // Non XHTML

		// Catch all phrases for other versions of fixed themes
		'value=\'{$mybb->post_code}\'',
		'value="{$mybb->post_code}"',
		'value = \'{$mybb->post_code}\'',
		'value = "{$mybb->post_code}"',

		// Additional whitespace variations (just in case)
		'value= \'{$mybb->post_code}\'',
		'value= "{$mybb->post_code}"',
		'value =\'{$mybb->post_code}\'',
		'value ="{$mybb->post_code}"'
	);

	// Go through each possible match and search. Check if we've fixed the template already.
	foreach ($fixed_lines as $fixed_line) {
		if (strpos($template, $fixed_line, 0) !== FALSE) {
			$template_fixed = TRUE; // This template is already fixed. We don't need to do anything else.
			break;
		}
	}

	// If we haven't fixed it yet, we will fix it now.
	if ($template_fixed != TRUE) {
		// Loop through possible FIND values to locate the opening form tag.
		foreach ($find_array as $find) {
			// Attempt to replace the form tag with a new form tag and add MyBB post code.
			$new_template = str_replace($find, $replace, $template);

			// If the replacement was successful, we will break. The template was fixed.
			// If not, we will keep going with alternative strings for locating the proper form tag.
			if ($new_template != $template) {
				break;
			}
		}
	}

	return $new_template;
}
