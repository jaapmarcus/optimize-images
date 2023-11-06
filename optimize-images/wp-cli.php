<?php

if (!defined('ABSPATH')) {
		die();
}

if (!defined('WP_CLI')) return;

class WP_CLI_Optimize_Images extends WP_CLI_Command {
	
	public function __construct(){
		$this -> io = new OptimizeImages();
	}
	
	public function optimize(){
			$this -> io -> scan_for_images();
	}
		
}



WP_CLI::add_command('oi', 'WP_CLI_Optimize_Images');