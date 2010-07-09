<?php
/**
 * Displays administrator backend.
 */
?>
<div class="wrap">
<table>
	<tr>
		<td style="width: 450px;">
			<form id="wpfp_options" method="post" action="">
			<div id="icon-options-general" class="icon32"></div>
			<h2>FV Wordpress Flowplayer</h2>
			<?php echo flowplayer_check_errors($fp); ?>
			<h3>Default Flowplayer Options:</h3>
			<table>
				<tr>
					<td>AutoPlay: </td>
					<td>
					 	<select name="autoplay">
						<?php echo flowplayer_bool_select($fp->conf['autoplay']); ?>
					 	</select>
					 </td>
				</tr>
				<tr style="position: absolute; top: -9999em; left: -9999em;">
					<td>Commercial License Key: </td>
					<td>
						<input type="text" size="20" name="key" id="key" value="<?php echo $fp->conf['key']; ?>" />	
					</td>
				</tr>	
				<tr>
					<td>Auto Buffering:</td>
					<td><select name="autobuffer">

					<?php echo flowplayer_bool_select($fp->conf['autobuffer']); ?>

					</select></td>
				</tr>
				<tr>
					<td>Popup Box:</td>
					<td><select name="popupbox">

					<?php echo flowplayer_bool_select($fp->conf['popupbox']); ?>

					</select></td>
				</tr>

					<?php echo include dirname( __FILE__ ) . '/../view/colours.php'; ?>

				<tr>
					<td>
					</td>
					<td>
						<input type="submit" name="submit" class="button-primary" value="Apply Changes" style="margin-top: 2ex;"/>
					</td>
				</tr>

				<tr>
					<td colspan="2" style="text-align: justify;">
					<h3>Description:</h3>
					<ul>
						<li>FV Wordpress Flowplayer is a completely non-commercial solution for embedding video on Wordpress websites.</li>
						<li>It contains opensource version of Flowplayer, with removed logo and copyright notice. </li>
						<li>Supported video formats are <strong>FLV</strong>, <strong>H.264</strong>, and <strong>MP4</strong>. Multiple videos can be displayed in ope post or page.</li>
						<li>Default options for all the embedded videos can be set in the menu above.</li>
					</ul>
					<h3>Usage:</h3>
					<p>
					To embed video "example.flv", simply include the following code inside any post or page: 
					<code>[flowplayer src=example.flv]</code>
					</p>
					<p>
					<code>src</code> is the only compulsory parameter, specifying the video file. Its value can be either a full URL of the file, 
					or just a filename, if it is located in the /videos/ directory in the root of the web.
					</p>
					<h4>Optional parameters:</h4>
					<ul style="text-align: left;">
						<li><code>width</code> and <code>height</code> specify the dimensions of played video in pixels. If they are not set, the default size is 320x240.<br/>
						<i>Example</i>:<br/><code>[flowplayer src=example.flv, width=640, height=480]</code></li>
						<li><code>splash</code> parameter can be used to display a custom splash image before the video is started. Just like in case of <code>src</code> 
						parameter, its value can be either complete URL, or filename of an image located in /videos/ folder.<br/>
						<i>Example</i>:<br/><code>[flowplayer src=example.flv, splash=image.jpg]</code></li>
						<li><code>popup</code> parameter can be used to display any HTML code after the video finishes (ideal for advertisment or links to similar videos). 
						Content you want to display must be between simgle quotes (<code>''</code>).<br/>
						<i>Example</i>:<br/><code>[flowplayer src=example.flv, popup='&lt;p&gt;some HTML content&lt;/p&gt;']</code></li>
					</ul>
					</td>
					<td></td>
				</tr>
			</table>
			</form>
		</td>
		<td style="padding: 3em; vertical-align: top;">
			<a id="player" class="flowplayer_div" style="display:block;width:400px;height:300px;"></a>
		</td>
	</tr>
</table>

<script defer="defer" language="Javascript" type="text/javascript">

		//load player
		$f("player", "<?php echo PLAYER; ?>", {
				<?php echo (isset($fp->conf['key'])&&strlen($fp->conf['key'])>0?'key:\''.$fp->conf['key'].'\',':''); ?>
				plugins: {
  					 controls: {    					
      					buttonOverColor: '<?php echo $fp->conf['buttonOverColor']; ?>',
      					sliderColor: '<?php echo $fp->conf['sliderColor']; ?>',
      					bufferColor: '<?php echo $fp->conf['bufferColor']; ?>',
      					sliderGradient: 'none',
      					progressGradient: 'medium',
      					durationColor: '<?php echo $fp->conf['durationColor']; ?>',
      					progressColor: '<?php echo $fp->conf['progressColor']; ?>',
      					backgroundColor: '<?php echo $fp->conf['backgroundColor']; ?>',
      					timeColor: '<?php echo $fp->conf['timeColor']; ?>',
      					buttonColor: '<?php echo $fp->conf['buttonColor']; ?>',
      					backgroundGradient: 'none',
      					bufferGradient: 'none',
   						opacity:1.0
   						}
				},
				clip: {
					url:'<?php echo RELATIVE_PATH; ?>/flowplayer/example.flv',
					autoPlay: '<?php if (isset($fp->conf["autoplay"])) { echo $fp->conf["autoplay"]; } else { echo "false"; } ?>',
	       				autoBuffering: '<?php if (isset($fp->conf["autobuffer"])) { echo $fp->conf["autobuffer"]; } else { echo "false"; } ?>'
				},
	

<?php 	
if($fp->conf['logoenable'] == 'true'){
	echo 'logo: {url: \'http://'.$fp->conf['logo'].'\', fullscreenOnly: '.$fp->conf['fullscreenonly'].', displayTime: 0, linkUrl: \'http://'.$fp->conf['logolink'].'\'},';
}
?>

				canvas: {
					backgroundColor:'<?php echo $fp->conf["canvas"]; ?>'
				},
				onLoad: function() {
					$(":input[name=tgt]").removeAttr("disabled");		
				},
				onUnload: function() {
					$(":input[name=tgt]").attr("disabled", true);		
				}
			});

</script>

</div>

<?php 
	if(isset($_POST['submit'])) {
		/**
		 *  Write the configuration into file, if the form was submitted.
		 */
		$fp->_set_conf();
		/**
		 *  Refresh the page.
		 */
		?>
		<script type="text/JavaScript">
		<!--
			window.location = window.location;
		//   -->
		</script>
		<?php
	}
?>



