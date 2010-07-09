<?php
/*
Simple:Press Forum
Installation Index - English
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/
?>
				<fieldset class="sfhelpfieldset"><legend>Installation Troubleshooting</legend>
					<?php $file = "installation"; ?>
					
					<p>If you have any problems following installation - such as errors loading the forum or saving posts - 
					please take a look at the topics below which may help.</p>
					
					<ul>
						<li>Forum Display and Save Problems
							<ul>
								<?php
								sfa_tag_help('Check the WordPress Page', $file);
								sfa_tag_help('Only the Front Page Displays', $file);
								sfa_tag_help('Nothing Displays', $file);
								sfa_tag_help('Unable to Save Posts', $file);
								sfa_tag_help('Using WP-Cache', $file);
								sfa_tag_help('Plugin Conflicts', $file);
								sfa_tag_help('Cannot Upload Avatars', $file);
								sfa_tag_help('Cannot Upload Smileys', $file);
								sfa_tag_help('No Login/Registrations Icons', $file);
								?>
							</ul>
						</li>
					</ul>						
				</fieldset>
<?php
?>