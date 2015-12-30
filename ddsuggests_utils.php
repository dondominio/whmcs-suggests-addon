<?php

/**
 * Utils library for the DonDominio Suggestions Addon for WHMCS.
 * Compatible with WHMCS 6.1.*
 * @author DonDominio <http://www.dondominio.com>
 * @link https://github.com/dondominio/whmcs-suggests-addon
 * @package DonDominioWHMCSSuggestionsAddon
 * @license GNU LESSER GENERAL PUBLIC LICENSE <https://raw.githubusercontent.com/dondominio/whmcs-suggestions/master/LICENSE>
 */

/**
 * Retrieve the current WHMCS version.
 *
 * @return integer
 */
function ddsuggests_get_whmcs_version()
{
	$q_version = full_query( "SELECT value FROM tblconfiguration WHERE setting = 'version'" );
	
	list( $version ) = mysql_fetch_row( $q_version );
	
	$version_components = explode( '.', $version );
	
	return intval( $version_components[0] );
}

function ddsuggests_getVersion()
{
	$versionFile = __DIR__ . '/version.json';
	
	if( !file_exists( $versionFile )){
		return 'unknown';
	}
	
	$json = @file_get_contents( $versionFile );
	
	if( empty( $json )){
		return 'unknown';
	}
	
	$versionInfo = json_decode( $json, true );
	
	if( !is_array( $versionInfo ) || !array_key_exists( 'version', $versionInfo )){
		return 'unknown';
	}
	
	return $versionInfo['version'];
}

/**
 * Retrieve a settings value from the database.
 *
 * @param string $key Key for the value to retrieve
 * @return string
 */
function ddsuggests_get( $key )
{
	$settings = full_query("
		SELECT
			`value`
		FROM `mod_ddsuggests_settings`
		WHERE
			`key` = '$key'
	");
	
	$result = mysql_fetch_array( $settings, MYSQL_ASSOC );
	
	return $result['value'];
}

/**
 * Modify, or create, a setting in the database.
 *
 * @param string $key Key for the setting
 * @param string $value Value for the settings
 * @return boolean
 */
function ddsuggests_set( $key, $value )
{
	$exists = ddsuggests_get( $key );
	
	if( empty( $exists )){
		$create = full_query("
			INSERT INTO `mod_ddsuggests_settings` (
				`key`,
				`value`
			) VALUES (
				'$key',
				'$value'
			)"
		);
		
		return $create;
	}
	
	$update = full_query("
		UPDATE `mod_ddsuggests_settings`
		SET
			`value` = '$value'
		WHERE
			`key` = '$key'
	");
	
	return $update;
}

/**
 * Perform a query against the WHMCS database and return the results.
 * This function is primarily used to setup the tables needed for the addon.
 * @param string $sql SQL query
 * @return Array
 */
function ddsuggests_do_query( $sql )
{
	$result = full_query( $sql );
	
	if( !$result ){
		return array( 'status' => 'error', 'description' => 'There was a problem activating the DonDominio Domain Suggestions Addon. Please contact support.' );
	}
	
	return true;
}

?>