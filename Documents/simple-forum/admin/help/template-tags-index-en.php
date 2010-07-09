<?php
/*
Simple:Press Forum
Template Tags Index - English
$LastChangedDate: 2009-04-29 02:41:47 +0100 (Wed, 29 Apr 2009) $
$Rev: 1819 $
*/
?>
				<fieldset class="sfhelpfieldset"><legend>Available Template Tags</legend>
					<?php $file = "template-tags"; ?>

					<p>Below is the list of currently available template tags that can be used outside of the
					forum pages - on a sidebar for example - to show recent posts, forum statistics or provide links
					to specific groups, forums or topics.<br />
					Template tag code is contained in the file 'sf-tags.php'</p>

					<ul>
						<li>Recent/Latest Posts
							<ul>
								<?php
								sfa_tag_help('sf_recent_posts_tag', $file);
								sfa_tag_help('sf_recent_posts_alt_tag', $file);
								sfa_tag_help('sf_latest_posts', $file);
								sfa_tag_help('sf_latest_posts_expanded', $file);
								sfa_tag_help('sf_new_post_announce', $file);
								sfa_tag_help('sf_author_posts', $file);
								?>
							</ul>
						</li>
						<li>Forum Statistics
							<ul>
								<?php
								sfa_tag_help('sf_stats_tag', $file);
								?>
							</ul>
						</li>
						<li>Links
							<ul>
								<?php
								sfa_tag_help('sf_group_link', $file);
								sfa_tag_help('sf_forum_link', $file);
								sfa_tag_help('sf_topic_link', $file);
								sfa_tag_help('sf_forum_dropdown', $file);
								sfa_tag_help('sf_add_new_topic_tag', $file);
								?>
							</ul>
						</li>
						<li>Linked Blog/Topic Posts
							<ul>
								<?php
								sfa_tag_help('sf_linked_topic_post_count', $file);
								sfa_tag_help('sf_blog_linked_tag', $file);
								?>
							</ul>
						</li>
						<li>Avatars
							<ul>
								<?php
								sfa_tag_help('sf_show_forum_avatar', $file);
								sfa_tag_help('sf_show_members_avatar', $file);
								sfa_tag_help('sf_show_avatar', $file);
								?>
							</ul>
						</li>
						<li>Private Messaging
							<ul>
								<?php
								sfa_tag_help('sf_pm_tag', $file);
								sfa_tag_help('sf_sendpm_tag', $file);
								?>
							</ul>
						</li>
						<li>Sidedash
							<ul>
								<?php
								sfa_tag_help('sf_sidedash_tag', $file);
								?>
							</ul>
						</li>
						<li>Post Ratings
							<ul>
								<?php
								sfa_tag_help('sf_highest_rated_posts', $file);
								sfa_tag_help('sf_most_rated_posts', $file);
								?>
							</ul>
						</li>
					</ul>
				</fieldset>
<?php
?>