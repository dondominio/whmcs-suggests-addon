<?php

/**
 * Hook for the Client Area Footer for WHMCS.
 * Adds the HTML and JS code to the WHMCS template to make the suggestions addon work.
 * @author DonDominio <http://www.dondominio.com>
 * @link https://github.com/dondominio/whmcs-suggests-addon
 * @package DonDominioWHMCSSuggestionsAddon
 * @license GNU LESSER GENERAL PUBLIC LICENSE <https://raw.githubusercontent.com/dondominio/whmcs-addon/master/LICENSE>
 */

add_hook( 'ClientAreaFooterOutput', 1, function( $vars )
{
	$current_language = $vars['language'];
	$lang_file = __DIR__ . '/lang/' . $current_language . '.php';
	
	if( file_exists( $lang_file )){
		include( $lang_file );
	}else{
		require( __DIR__ . '/lang/english.php' );
	}
	
	$LANG = $_ADDONLANG;
	
	$currency = ( array_key_exists( 'currency', $_GET )) ? $_GET['currency'] : '1';
	
	$html = "
	<input type=\"hidden\" name=\"currency\" id=\"currency\" value=\"" . $currency . "\" />
	
	<script id=\"suggestions_template\" type=\"text/html\">
	";
	
	/*
	 * Modify the `template.html` file to match your templates as you need.
	 */
	$html .= include( __DIR__ . '/template.php' );
	
	$html .= "
    </script>
	
	<script src='modules/addons/ddsuggests/suggests.js'></script>
	";
	
	return $html;
});