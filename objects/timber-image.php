<?php
	
	class TimberImage extends TimberCore {

		var $_can_edit;
		var $abs_url;
		var $PostClass = 'TimberPost';

		function __construct($iid){
			$this->init($iid);
		}

		function get_url(){
			if (isset($this->abs_url)){
				return $this->abs_url;
			}
			if (!isset($this->file) && isset($this->_wp_attached_file)){
				$this->file = $this->_wp_attached_file;
			}
			if (isset($this->file)){
				return '/wp-content/uploads/'.$this->file;
			}
			return false;
		}

		function get_path(){
			if (strlen($this->abs_url)){
				return $this->abs_url;
			}
			return get_permalink($this->ID);
		}

		function get_parent(){
			if (!$this->post_parent){
				return false;
			}
			return new $this->PostClass($this->post_parent);
		}

		function init($iid){
			
			if (!is_numeric($iid) && is_string($iid)){
				if (strstr($iid, '://')){
					$this->init_with_url($iid);
					return;
				} else if (strstr(strtolower($iid), '.jpg')){
					$this->init_with_url($iid);
				}
			}	
			$image_info = $iid;
			if(is_numeric($iid)){
				$image_info = wp_get_attachment_metadata($iid);
				if (!is_array($image_info)){
					$image_info = array();
				}
				$image_custom = get_post_custom($iid);
				$basic = get_post($iid);
				$this->caption = $basic->post_excerpt;
				$image_info = array_merge($image_info, $image_custom, get_object_vars($basic));


			} else if (is_array($image_info) && isset($image_info['image'])){
				$image_info = $image_info['image'];
			} else if (is_object($image_info)){
				$image_info = get_object_vars($image_info);
			}
			$this->import($image_info);
			if (isset($image_info['id'])){
				$this->ID = $image_info['id'];
			} else if (is_numeric($iid)){
				$this->ID = $iid;
			}
			if (isset($this->ID)){
				$custom = get_post_custom($this->ID);
				foreach($custom as $key=>$value){
					$this->$key = $value[0];
				}
			} else {
				error_log('iid='.$iid);
			}
		}

		function init_with_url($url){
			$this->abs_url = $url;
		}

		function can_edit(){
			if (isset($this->_can_edit)){
				return $_can_edit;
			}
			$this->_can_edit = false;
			if (current_user_can('edit_post', $this->ID)){
				$this->_can_edit = true;
			}
			return $this->_can_edit;
		}
	}