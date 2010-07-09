<?php
/*
Simple:Press Forum
Admin Tools - English
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/
?>
				<fieldset class="sfhelpfieldset"><legend>Admin Tools</legend>
					<?php $file = "admin-tools"; ?>
					
					<p>The forum administrator - and other users if granted the appropriate permissions - 
					have a special set of tools available for forum maintenance. Both 'topic' views and
					'post' views allow for a set of tool icons to be made visible on the riht hand side of
					each entry.</p>
					
					<ul>
						<li>Admin Tools
							<ul>
								<?php
								sfa_tag_help('Turning the Tools On', $file);
								sfa_tag_help('Using the Topic Tools', $file);
								sfa_tag_help('Using the Post Tools', $file);
								?>
							</ul>
						</li>
					</ul>						
				</fieldset>
<?php
?>