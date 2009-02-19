<?php

require_once('spyc.php');

class Sprockets {
	
	static $loaded = array();
	static $contents = null;
	static $load_path = array();
	
	static function load($files) {
		$yaml = Spyc::YAMLLoad('config.yml'); // Load config from YAML file
		self::$load_path = $yaml[':load_path']; // Search path to find javascript sources
		$files = split(',', $files); // Split files by comma
		
		foreach ($files as $file) {
			$temp_contents = file_get_contents(dirname(__FILE__) . $file);
			self::$contents .= self::parse($temp_contents);
		}
		
		return self::$contents;
	}
	
	static function parse($contents) {
		if (preg_match_all('/\/\/= require <([a-zA-Z]+)>/', $contents, $matches)) {
			$contents = self::insert($contents, $matches);
		}
		return $contents;
	}
	
	static function insert($content, $matches) {
		$stack = null;
		foreach ($matches[1] as $match) {
			if (!in_array($match, self::$loaded)) {
				self::$loaded[] = $match;
				$stack = $stack . self::parse(file_get_contents(dirname(__FILE__) . '/js/' . $match . '.js')) . "\n";
			}
			$pattern = '/\/\/= require <' . $match . '>/';
			$content = preg_replace($pattern, null, $content);
		}
		$content = $stack . $content;
		
		return $content;
	}
	
}

$files = '/js/test.js';

echo Sprockets::load($files);

?>