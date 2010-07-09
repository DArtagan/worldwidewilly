<?php

/**
 * Extension of original flowplayer class intended for frontend.
 */
class flowplayer_frontend extends flowplayer
{

	/**
	 * Builds the HTML and JS code of single flowplayer instance on a page/post.
	 * @param string $media URL or filename (in case it is in the /videos/ directory) of video file to be played.
	 * @param array $args Array of arguments (name => value).
	 * @return Returns array with 2 elements - 'html' => html code displayed anywhere on page/post, 'script' => javascript code displayed before </body> tag
	 */
	function build_min_player($media,$args = array()) {
			
			// returned array with new player's html and javascript content
			$ret = array('html' => '', 'script' => '');
			
			if(strpos($media,'http://') === false) {
				$media = VIDEO_PATH.$media;
			}
			
			// unique coe for this player
			$hash = md5($media.$this->_salt());
			
			// setting argument values
			$width = 320;
			$height = 240;
			$popup = '';
			if (isset($args['width'])) $width = $args['width'];
			if (isset($args['height'])) $height = $args['height'];

			// if allowed by configuration file, set the popup box js code and content
			if ($this->conf['popupbox'] != 'false') {
				if (isset($args['popup'])) {
					$popup = $args['popup'];
					$popup = html_entity_decode(str_replace("_"," ",substr($popup,1,strlen($popup)-2)));
				} else {
					$popup = '<div style="margin-top: 10px;">Would you like to replay the video or share the link to it with your friends?</div>';
				}
				$popup_controls = '<div class="popup_controls"><a title="Replay video" href="javascript:fp_replay(\''.$hash.'\');"><img src="'.RELATIVE_PATH.'/images/replay.png" alt="Replay video" /></a><a title="Share video" href="javascript:fp_share(\''.$hash.'\');"><img src="'.RELATIVE_PATH.'/images/share.png" alt="Share video" /></a></div>';
				$popup_contents = "\n".'<span id="popup_contents_'.$hash.'" class="popup_contents">'.$popup_controls.'<span id="wpfp_'.$hash.'_custom_popup" class="wpfp_custom_popup">'.$popup.'</span></span>';
				// replace href attribute by javascript function
				$popup_contents = str_replace("href=\"","onClick=\"javascript:window.location=this.href\" href=\"",$popup_contents);
				$popup_code = "
				window.flowplayer('wpfp_$hash').onFinish(function() {
					var fp = document.getElementById('wpfp_$hash');
					var popup = document.createElement('div');
					var popup_contents = document.getElementById('popup_contents_$hash');
					popup.className = 'flowplayer_popup';
					popup.id = 'wpfp_".$hash."_popup';
					popup.innerHTML = popup_contents.innerHTML;
					fp.appendChild(popup);
				});
				window.flowplayer('wpfp_$hash').onStart(function() {
					var popup = document.getElementById('wpfp_".$hash."_popup');
					var fp = document.getElementById('wpfp_$hash');
					fp.removeChild(popup);
				});
				";
			}
			
			if (isset($args['splash']) && !empty($args['splash'])) {
				if(strpos($args['splash'],'http://') === false) {
					$splash_img = VIDEO_PATH.$args['splash'];
				} else {
					$splash_img = $args['splash'];
				}
				$splash = '<img src="'.$splash_img.'" alt="" class="splash" /><img width="83" height="83" src="'.RELATIVE_PATH.'/images/play.png" alt="" class="splash_play_button" style="top: '.round($height/2-45).'px;" />';
				// overriding the "autoplay" configuration - video should start immediately after click on the splash image
				$this->conf['autoplay'] = 'true';
			}
			
			

			 // set the output JavaScript (which will be added to document head)
			$ret['script'] = '
				if (document.getElementById(\'wpfp_'.$hash.'\') != null) {
					flowplayer("wpfp_'.$hash.'", {src: "'.PLAYER.'", wmode: \'opaque\'}, {
	'.(isset($this->conf['key'])&&strlen($this->conf['key'])>0?'key:\''.$this->conf['key'].'\',':'').'
						plugins: {
							controls: {		
								autoHide: \'always\',
			      					buttonOverColor: \''.$this->conf['buttonOverColor'].'\',
			      					sliderColor: \''.$this->conf['sliderColor'].'\',
			      					bufferColor: \''.$this->conf['bufferColor'].'\',
			      					sliderGradient: \'none\',
			      					progressGradient: \'medium\',
			      					durationColor: \''.$this->conf['durationColor'].'\',
			      					progressColor: \''.$this->conf['progressColor'].'\',
			      					backgroundColor: \''.$this->conf['backgroundColor'].'\',
			      					timeColor: \''.$this->conf['timeColor'].'\',
			      					buttonColor: \''.$this->conf['buttonColor'].'\',
			      					backgroundGradient: \'none\',
			      					bufferGradient: \'none\',
		   						opacity:1.0
	   						}
						},
						clip: { 
							url: \''.$media.'\', 
							autoPlay: '.(isset($this->conf['autoplay'])?$this->conf['autoplay']:'false').',
							autoBuffering: '.(isset($this->conf['autobuffer'])?$this->conf['autobuffer']:'false').'
						}, 
						canvas: {
							backgroundColor:\''.$this->conf['canvas'].'\'
						}
					});
				};
			'.$popup_code;

			 // set the output HTML (which will be printed into document body)
			$ret['html'] .= '<a id="wpfp_'.$hash.'" style="width:'.$width.'px; height:'.$height.'px;" class="flowplayer_container">'.$splash.'</a>'.$popup_contents;
		
		// return new player's html and script
		return $ret;
	}

	/**
	 * Displays the elements that need to be added to frontend.
	 */
	function flowplayer_head() {
		include dirname( __FILE__ ) . '/../view/frontend-head.php';
	}


}

?>
