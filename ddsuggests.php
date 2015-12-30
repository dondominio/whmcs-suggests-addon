<?php

/**
 * The DonDominio Domain Suggestions Addon for WHMCS.
 * Compatible with WHMCS 6.1.*
 * @author DonDominio <http://www.dondominio.com>
 * @link https://github.com/dondominio/whmcs-suggests-addon
 * @package DonDominioWHMCSSuggestionsAddon
 * @license GNU LESSER GENERAL PUBLIC LICENSE <https://raw.githubusercontent.com/dondominio/whmcs-suggestions/master/LICENSE>
 */

if( !defined( 'WHMCS' )){
	die( 'This file cannot be accessed directly.' );
}

require_once( __DIR__ . '/ddsuggests_utils.php' );

/**
 * Configuration array for WHMCS Addon Manager.
 *
 * @return Array
 */
function ddsuggests_config()
{
	$config = array(
		'name'			=> 'DonDominio Domain Suggestions',
		'description'	=> 'Suggests more domains to purchase',
		'version'		=> '1.0',
		'author'		=> 'DonDominio',
		'language'		=> 'English',
		'fields'		=> array()
	);
	
	return $config;
}

function ddsuggests_version_check()
{
	$localVersionInfo = file_get_contents( __DIR__ . '/version.json' );
	$githubVersionInfo = file_get_contents( 'https://raw.githubusercontent.com/dondominio/whmcs-suggests-addon/master/version.json' );
	
	// Have we retrieved anything?
	if( empty( $localVersionInfo ) || empty( $githubVersionInfo )){
		return false;
	}
	
	$localJson = json_decode( $localVersionInfo, true );
	$githubJson = json_decode( $githubVersionInfo, true );
	
	// Have we decoded the JSONs correctly?
	if( !is_array( $localJson ) || !is_array( $githubJson )){
		return false;
	}
	
	// Comparing the versions found on the JSONs
	if( version_compare( $localJson['version'], $githubJson['version'] ) < 0 ){
		return $githubJson;
	}
	
	return false;
}

function ddsuggests_activate()
{
	if( is_array( $result = ddsuggests_do_query("
		CREATE TABLE IF NOT EXISTS `mod_ddsuggests_settings`
		(
			`key` VARCHAR(32) NOT NULL PRIMARY KEY,
			`value` VARCHAR(256) NULL
		)
	"))){
		return $result;
	}
	
	if( is_array( $result = ddsuggests_do_query("
		INSERT INTO `mod_ddsuggests_settings` (
			`key`,
			`value`
		) VALUES
			('api_username', ''),
			('api_password', ''),
			('tlds', 'com,net,tv,es'),
			('language', 'en')
	"))){
		return $result;
	}
}

function ddsuggests_deactivate()
{
	//Removing mod_dondominio_pricing
	if( is_array( $result = ddsuggests_do_query("DROP TABLE IF EXISTS `mod_ddsuggests_settings`" ))){
		return $result;
	}
}

/**
 * Main handler for Admin Area output.
 *
 * @param Array $vars Parameters from WHMCS
 */
function ddsuggests_output( $vars )
{
	$LANG = $vars['_lang'];
	
	echo "
	<h2>" . $LANG['settings_title'] . "</h2>
	";
	
	// Save changes made to the settings.
	if( array_key_exists( 'save', $_POST )){
		ddsuggests_set( 'api_username', $_POST['api_username'] );
		ddsuggests_set( 'api_password', $_POST['api_password'] );
		ddsuggests_set( 'language', $_POST['language'] );
		ddsuggests_set( 'tlds', implode( ',', $_POST['tlds'] ));
		
		echo "
			<div class=\"successbox\">
				<h2>" . $LANG['info_settings_saved'] . "</h2>
			</div>
		";
	}
	
	// Choose selected language
	$lang_selected[ ddsuggests_get('language') ] = "selected=\"selected\"";
	
	// Choose selected TLDs
	$tlds = explode( ',', ddsuggests_get( 'tlds' ));
	
	foreach( $tlds as $selected_tld ){
		$tlds_selected[ $selected_tld ] = "selected=\"selected\"";
	}
		
	// Check current version
	if( ddsuggests_version_check()){
		echo "
			<div class=\"infobox\">
				<h2>" . $LANG['info_new_version'] . "</h2>
				<p>" . $LANG['info_new_version_current'] . " " . ddsuggests_getVersion() . "<br />
				" . $LANG['info_new_version_available'] . " " . ddsuggests_version_check() . "</p>
				
				<p>
				<a class=\"btn btn-default\" href=\"https://github.com/dondominio/whmcs-suggests-addon/\">" . $LANG['info_new_version_download'] . "</a>
			</div>
		";
	}
	
	// Settings form
	echo "
	<form id=\"settings\" method=\"post\" action=\"\">
		<input type=\"hidden\" name=\"save\" value=\"1\" />
		
		<div id=\"tab0box\" class=\"tabbox\">
			<div id=\"tab_content\">
				<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
					<tbody>
						<tr>
							<td class=\"fieldlabel\">
								" . $LANG['settings_api_user'] . "
							</td>
							
							<td class=\"fieldarea\">
								<input type=\"text\" name=\"api_username\" required=\"required\" value=\"" . ddsuggests_get('api_username') . "\" />
							</td>
						</tr>
						
						<tr>
							<td class=\"fieldlabel\">
								" . $LANG['settings_api_password'] . "
							</td>
							
							<td class=\"fieldarea\">
								<input type=\"password\" name=\"api_password\" required=\"required\" value=\"" . ddsuggests_get('api_password') . "\" />
							</td>
						</tr>
						
						<tr>
							<td class=\"fieldlabel\">
								" . $LANG['settings_lang'] . "
							</td>
							
							<td class=\"fieldarea\">
								<select name=\"language\" required=\"required\">
									<option value=\"en\" " . $lang_selected['en'] . ">" . $LANG['settings_lang_en'] . "</option>
									<option value=\"es\" " . $lang_selected['es'] . ">" . $LANG['settings_lang_es'] . "</option>
									<option value=\"zh\" " . $lang_selected['zh'] . ">" . $LANG['settings_lang_zh'] . "</option>
									<option value=\"fr\" " . $lang_selected['fr'] . ">" . $LANG['settings_lang_fr'] . "</option>
									<option value=\"de\" " . $lang_selected['de'] . ">" . $LANG['settings_lang_de'] . "</option>
									<option value=\"kr\" " . $lang_selected['kr'] . ">" . $LANG['settings_lang_kr'] . "</option>
									<option value=\"pt\" " . $lang_selected['pt'] . ">" . $LANG['settings_lang_pt'] . "</option>
									<option value=\"tr\" " . $lang_selected['tr'] . ">" . $LANG['settings_lang_tr'] . "</option>
								</select>
							</td>
						</tr>
						
						<tr>
							<td class=\"fieldlabel\">
								" . $LANG['settings_tlds'] . "
							</td>
							
							<td class=\"fieldarea\">
								<select multiple=\"multiple\" name=\"tlds[]\" required=\"required\">
									<option value=\"com\" " . $tlds_selected['com'] . ">.com</option>
									<option value=\"net\" " . $tlds_selected['net'] . ">.net</option>
									<option value=\"tv\" " . $tlds_selected['tv'] . ">.tv</option>
									<option value=\"cc\" " . $tlds_selected['cc'] . ">.cc</option>
									<option value=\"es\" " . $tlds_selected['es'] . ">.es</option>
									<option value=\"org\" " . $tlds_selected['org'] . ">.org</option>
									<option value=\"info\" " . $tlds_selected['info'] . ">.info</option>
									<option value=\"biz\" " . $tlds_selected['biz'] . ">.biz</option>
									<option value=\"eu\" " . $tlds_selected['eu'] . ">.eu</option>
								</select>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		
		
		<p align='center'>
			<button action='submit' name='submit_button' id='settings_submit' class='btn'>" . $LANG['btn_save'] . "</button>
		</p>
	</form>
	";
}