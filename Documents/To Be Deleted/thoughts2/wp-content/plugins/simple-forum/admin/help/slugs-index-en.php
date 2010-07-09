<?php
/*
Simple:Press Forum 
Custom Index - English
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/
?>
				<fieldset class="sfhelpfieldset"><legend>Forum and Topic Slugs</legend>
					<?php $file = "slugs"; ?>
					
					<p>Simple:Press Forum uses 'slugs' to create page url's. This section aims to help troubleshoot
					any problems encountered with slugs.</p>
					
					<ul>
						<li>Slugs
							<ul>
								<?php
								sfa_tag_help('Forum Slugs', $file);
								sfa_tag_help('Topic Slugs', $file);
								?>
							</ul>
						</li>
					</ul>						
				</fieldset>
<?php
?>