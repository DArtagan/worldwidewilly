<?php
/*
Simple:Press Forum
Filters Index - English
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/
?>
				<fieldset class="sfhelpfieldset"><legend>Filters</legend>
					<?php $file = "filters"; ?>
					
					<p>WordPress has a system of 'filters' that allow plugin authors to alter various content both before
					it is saved and before it is displayed. Simple:Press Forum uses many of these filters and also defines two
					that can be used on forum post content by users.</p>
					
					<ul>
						<li>Post Content Filters
							<ul>
								<?php
								sfa_tag_help('sf_save_post_content', $file);
								sfa_tag_help('sf_show_post_content', $file);
								?>
							</ul>
						</li>
					</ul>						
				</fieldset>
<?php
?>