<?php get_header(); ?>

<?php get_sidebar(); ?>

<div class="content">
	
<div id="primary-wrapper">
	<div id="primary" role="main">
		<div id="notices"></div>
		<a name="startcontent" id="startcontent"></a>

		<?php /* K2 Hook */ do_action('template_primary_begin'); ?>

		<?php if ( '1' == get_option('k2rollingarchives') ): ?>
		<div id="dynamic-content">

			<?php include(TEMPLATEPATH . '/app/display/rollingarchive.php'); ?>

		</div> <!-- #dynamic-content -->
		<?php else: ?>
		<div id="current-content" class="hfeed">

			<?php include(TEMPLATEPATH . '/app/display/theloop.php'); ?>

		</div> <!-- #current-content -->

		<div id="dynamic-content"></div>
		<?php endif; ?>

		<?php /* K2 Hook */ do_action('template_primary_end'); ?>
	</div> <!-- #primary -->
</div> <!-- #primary-wrapper -->
	<?php get_footer(); ?>
	
</div> <!-- .content -->