<?php
/**
 * Copyright (C) 2011-2015 Toni Hermoso Pulido <toniher@cau.cat>
 * http://www.cau.cat
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 */

if ( !defined( 'MEDIAWIKI' ) ) {
		echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
		die( 1 );
}

//self executing anonymous function to prevent global scope assumptions
call_user_func( function() {
		
		$GLOBALS['wgExtensionCredits']['other'][] = array(
				'path' => __FILE__,
				'name' => 'LinksHere',
				'version' => '0.2', 
				'author' => array( 'Toniher' ), 
				'url' => 'https://mediawiki.org/wiki/User:Toniher',
				'description' => 'This extension is used for keeping a list of linked pages',
		);
		
		
		$GLOBALS['wgAutoloadClasses']['LinksHere'] = dirname(__FILE__) . '/LinksHere_body.php';
		
		$GLOBALS['wgExtensionFunctions'][] = "wfSetupLinksHere";
		
		$GLOBALS['wgHooks']['LanguageGetMagic'][] = 'wfSetupLinksHereLanguageGetMagic';
		
});
		
function wfSetupLinksHere() {

		global $wgParser;
		global $wgLinksHereFunctions;
		$wgLinksHereFunctions = new LinksHere();
		$wgParser->setFunctionHook( 'LinksHere', array( &$wgLinksHereFunctions, 'executeLinksHere' ) );
		$wgParser->setFunctionHook( 'LinksOut', array( &$wgLinksHereFunctions, 'executeLinksOut' ) );
		$wgParser->setFunctionHook( 'LinksInOut', array( &$wgLinksHereFunctions, 'executeLinksInOut' ) );
		$wgParser->setFunctionHook( 'LinksInOrOut', array( &$wgLinksHereFunctions, 'executeLinksInOrOut' ) );
}

function wfSetupLinksHereLanguageGetMagic( &$magicWords, $langCode ) {
		
		switch ( $langCode ) {
		default:
				$magicWords['LinksHere']    = array( 0, 'LinksHere' );
				$magicWords['LinksOut']    = array( 0, 'LinksOut' );
				$magicWords['LinksInOut']    = array( 0, 'LinksInOut' );
				$magicWords['LinksInOrOut']    = array( 0, 'LinksInOrOut' );
		}
		
		return true;
}

?>
