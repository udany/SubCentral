<?php

class VueParser {
	private static $path = 'static/vue/';
	public static $regexTemplate = '#<template>([\s\S]*)</template>#';
	public static $regexStyle = '#<style>([\s\S]*)</style>#';
	public static $regexScript = '#<script>([\s\S]*)</script>#';
	
	
	public static function Parse($file, $path=null){
		if ($path===null){
			$path = GetProjectDirectory().self::$path;
			$sharedPath = GetProjectDirectory('_shared').self::$path;

			if (!file_exists($path.$file.'.vue') && file_exists($sharedPath.$file.'.vue')){
				$path = $sharedPath;
			}
		}
		$data = file_get_contents($path.$file.'.vue');

		$template = self::matchSection(self::$regexTemplate, $data);
		$style = self::matchSection(self::$regexStyle, $data);
		$script = self::matchSection(self::$regexScript, $data);

		return new VueFile($file, $template, $style, $script);
	}
	private static function matchSection($regex, $source){
		$matches = [];
		preg_match($regex, $source, $matches);
		if (count($matches)){
			return $matches[1];
		}else{
			return '';
		}
	} 
	
	public static function Inline($file, $path=null){
		if (is_array($file)){
			$r = [];
			foreach ($file as $f){
				array_push($r, self::Inline($f, $path));
			}

			return implode("\n\n", $r);
		}

		return self::Parse($file, $path)->getInlineCode();
	}
}

class VueFile {
	public $Name;
	public $Script;
	public $Style;
	public $Template;

	public function __construct($name, $template, $style, $script){
		$this->Name = $name;
		$this->Template = $template;
		$this->Style = $style;
		$this->Script = $script;
	}

	public function getInlineCode(){
		$name = $this->Name;
		$template = $this->Template;
		$style = $this->Style;
		$script = $this->Script;

		$safeName = str_ireplace('/', '-', $name);

		$id = '#'.$safeName.'-template';

		$script = str_ireplace('template: ""', "template: \"$id\"", $script);

		return ($template ? "<script type='text/vue-template' id='$safeName-template'>$template</script>" : '').
		       ($style ? "<style>$style</style>" : '').
		       ($script ? "<script type='text/javascript'>$script</script>" : '');
	}
}