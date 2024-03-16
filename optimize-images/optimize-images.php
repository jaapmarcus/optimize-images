<?php
/*
Plugin Name:Optimize Images
Plugin URI: https://eris.nu
Description:
Version: 1.2.0
Author: Jaap Marcus
Author URI:  https://eris.nu
Text Domain: -
*/

class OptimizeImages {
	public function __construct(){
		// Action to delete a webp image when original image is deleted
		add_action('delete_attachment', array($this, 'delete_webp_image'));
		add_action('publish_post', array($this, 'scan_post'),  10, 3 );
		add_filter('wp_get_attachment_image_src',  array($this,'alterImageSrc'), 10, 4);
		add_filter('wp_calculate_image_srcset',array($this,'setImageSrcSet'));
	}
	
	function setImageSrcSet($sources){
		$sr = array();
		foreach($sources as $source) 
		{       
				$upload_dir = wp_upload_dir();
				$base_dir = $upload_dir['basedir'];
				$base_url = $upload_dir['baseurl'];
				$imagepath= str_replace($base_url, $base_dir, $source['url'].'.webp');
				if ( file_exists( $imagepath) ){
					$source['url'] = $source['url'].'.webp';
					$sr[] = $source;
				}        
		}     
		return $sr;
	}
	
	function alterImageSrc($image, $attachment_id, $size, $icon)
	{        
			$upload_dir = wp_upload_dir();
			$base_dir = $upload_dir['basedir'];
			$base_url = $upload_dir['baseurl'];
			if(is_bool($image)){
				return $image;
			}
			$imagepath= str_replace($base_url, $base_dir, $image[0].'.webp');
			if ( file_exists( $imagepath) ){
				$image[0] = $image[0].'.webp';
			}
			return $image;
	}
	
	function scan_post($post_id){
		$post = get_post($post_id);
		preg_match_all('/<img\s+[^>]*?>/i', $post -> post_content, $matches);
		$img_tags = $matches[0];
			foreach($img_tags as $img_tag){
				if (preg_match('/src=["\']([^"\']+)/i', $img_tag, $src_match)) {
					$image_url = $src_match[1];
					$upload_dir = wp_upload_dir();
					$base_dir = $upload_dir['basedir'];
					$base_url = $upload_dir['baseurl'];
					$imagepath= str_replace($base_url, $base_dir, $image_url.'.webp');
					if(file_exists($imagepath)){
						
						$post -> post_content = str_replace($image_url, $image_url.'.webp',  $post -> post_content);
					}
				}
			}
		remove_action('publish_post', array($this, 'scan_post'));
		wp_update_post($post);
		return $post_id;
	}
	
	function delete_webp_image($post_id)
	{
		$metadata = wp_get_attachment_metadata($post_id);
	
		$file = get_attached_file($post_id);
		$webp_file = "{$file}.webp";
	
		// Check and delete webp file
		if (file_exists($webp_file)) {
			if (!unlink($webp_file)) {
				error_log("Failed to delete WebP file '{$webp_file}'.");
			}
		}
	
		// Check and delete webp files for all image sizes
		if(!empty($metadata['sizes'])){
			foreach ($metadata['sizes'] as $size => $value) {
				$upload_folder = wp_upload_dir();
				$image = image_get_intermediate_size($post_id, $size);
				$file = path_join($upload_folder['basedir'], $image['path']);
		
				$webp_file = "{$file}.webp";
		
				if (file_exists($webp_file)) {
					if (!unlink($webp_file)) {
						error_log("Failed to delete WebP file '{$webp_file}'.");
					}
				}
			}
		}
	}
	
	function scan_for_images(){
		$upload_folder = wp_upload_dir();
		$files = scandir($upload_folder['path']);
		foreach ($files as $file){
			if(!is_dir($upload_folder['path'] . '/'. $file)){
				$ext = pathinfo($upload_folder['path'] . '/'. $file, PATHINFO_EXTENSION);
				if(in_array($ext, array('png','jpg','jpeg'))){
					if(!file_exists($upload_folder['path'] . '/'. $file.'.webp')){
						$image = wp_get_image_editor($upload_folder['path'] . '/'. $file);
						
						if (!is_wp_error($image)) {
							$image->set_quality(90);
							$image->resize(0, 1200);
							$image->save($upload_folder['path'] . '/'. $file.'.webp', 'image/webp');
						}
					}
				}
			}
		}
		
	}	
}





$class = new OptimizeImages();

if (defined('WP_CLI') && WP_CLI) {
		include(__DIR__.'/wp-cli.php');
}