<?php
namespace phpsgex\includes;

final class Language {
	private static $lang= null;
	
	public static function Init($lang){
		if( !isset($_SESSION["language"]) ) $_SESSION["language"]= LANG;
		if( isset($_REQUEST["SetLanguage"]) ) $_SESSION["language"]= $_REQUEST["SetLanguage"];
		self::Load( $_SESSION["language"] );
	}
	
	private static function Load($lang){
		require(__ROOT__."languages/en.php");
		if( $lang =="en" ) return;
		
		$c= array_clone($lang);
		require(__ROOT__."languages/$lang.php");
		
		foreach( $c as $key => $_ ){
			if( array_key_exists( $key, $lang ) )
				$c[$key]= $lang[$key];
		}
		
		self::$lang= $c;
	}
	
	public static function Get($str){
		return self::$lang[$str];
	}
}