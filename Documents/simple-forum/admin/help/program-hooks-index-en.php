<?php
/*
Simple:Press Forum
Program Hooks Index - English
$LastChangedDate: 2009-01-17 16:45:23 +0000 (Sat, 17 Jan 2009) $
$Rev: 1239 $
*/
?>
				<fieldset class="sfhelpfieldset"><legend>Program Hooks</legend>
					<?php $file = "program-hooks"; ?>

					<p>Program Hooks provide the opportunity for you to insert extra content (logos, advertisments, any other text or graphics)
					within specific areas of the forum pages. In almost all cases any content created in one of the hooks should be returned
					and not echoed to the screen. Simple:Press Forum will perform the display. Exceptions are noted in the notes for individual hooks.<br />
					Program hooks are defined in the file 'sf-hook-template.txt' which will need the extension re-naming to .php prior to use.</p>

					<ul>
						<li>All Views
							<ul>
								<?php
								sfa_tag_help('sf_hook_pre_content', $file);
								sfa_tag_help('sf_hook_post_content', $file);
								sfa_tag_help('sf_hook_footer_inside', $file);
								sfa_tag_help('sf_hook_footer_outside', $file);
								sfa_tag_help('sf_hook_post_loginstrip', $file);
								sfa_tag_help('sf_hook_post_breadcrumbs', $file);
								?>
							</ul>
						</li>
						<li>Groups/Forums View
							<ul>
								<?php
								sfa_tag_help('sf_hook_group_header', $file);
								sfa_tag_help('sf_hook_post_group', $file);
								sfa_tag_help('sf_hook_post_forum', $file);
								?>
							</ul>
						</li>
						<li>Forum/Topics View
							<ul>
								<?php
								sfa_tag_help('sf_hook_forum_header', $file);
								sfa_tag_help('sf_hook_post_topic', $file);
								?>
							</ul>
						</li>
						<li>Topic/Posts View
							<ul>
								<?php
								sfa_tag_help('sf_hook_topic_header', $file);
								sfa_tag_help('sf_hook_post_post', $file);
								sfa_tag_help('sf_hook_first_post', $file);
								sfa_tag_help('sf_hook_last_post', $file);
								sfa_tag_help('sf_hook_other_posts', $file);
								?>
							</ul>
						</li>
						<li>Profile View
							<ul>
								<?php
								sfa_tag_help('sf_hook_pre_profile', $file);
								sfa_tag_help('sf_hook_post_profile', $file);
								?>
							</ul>
						</li>
						<li>Special Post Hooks
							<ul>
								<?php
								sfa_tag_help('sf_hook_post_feedflare', $file);
								sfa_tag_help('sf_hook_pre_post_save', $file);
								sfa_tag_help('sf_hook_post_save', $file);
								sfa_tag_help('sf_hook_topic_delete', $file);
								sfa_tag_help('sf_hook_post_delete', $file);
								sfa_tag_help('sf_hook_profile_save', $file);
								?>
							</ul>
						</li>
					</ul>
				</fieldset>
<?php
?>