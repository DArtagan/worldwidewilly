<?php
/**
 * Displays metatags for frontend.
 */
?>
<script type="text/javascript" src="<?php echo RELATIVE_PATH; ?>/flowplayer/flowplayer.min.js"></script>
<link rel="stylesheet" href="<?php echo RELATIVE_PATH; ?>/css/flowplayer.css" type="text/css" media="screen" />
<!--[if lt IE 7.]>
<script defer type="text/javascript" src="<?php echo RELATIVE_PATH; ?>/js/pngfix.js"></script>
<![endif]-->
<script type="text/javascript">	
	/*<![CDATA[*/
		function fp_replay(hash) {
			var fp = document.getElementById('wpfp_'+hash);
			var popup = document.getElementById('wpfp_'+hash+'_popup');
			fp.removeChild(popup);
			flowplayer('wpfp_'+hash).play();
		}
	
		function fp_share(hash) {
			var cp = document.getElementById('wpfp_'+hash+'_custom_popup');
			cp.innerHTML = '<div style="margin-top: 10px; text-align: center;"><label for="permalink" style="color: white;">Permalink to this page:</label><input onclick="this.select();" id="permalink" name="permalink" type="text" value="<?php echo 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; ?>" /></div>';
		}
	/*]]>*/
</script>

