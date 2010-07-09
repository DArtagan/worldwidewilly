<?php
/*
Simple:Press Forum
Themes Index - English
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/
?>
				<fieldset class="sfhelpfieldset"><legend>Theme Problems</legend>
					<?php $file = "themes"; ?>
					
					<p>Virtually all minor display problems - such as misalignment of components, unwanted borders, 
					icons appearing in the wrong place etc., - are caused by a conflict with the forum styling and
					the WordPress theme being used.</p>
					
					<ul>
						<li>Theme Troubleshooting
							<ul>
								<?php
								sfa_tag_help('Misplaced Icons', $file);
								sfa_tag_help('Unwanted Image Borders', $file);
								sfa_tag_help('Forum Column Too Narrow', $file);
								?>
							</ul>
						</li>
					</ul>						
				</fieldset>
<?php
?>