	<?php /* K2 Hook */ do_action('template_after_content'); ?>

	<div class="clear"></div>

<hr />

<?php /* K2 Hook */ do_action('template_before_footer'); ?>

<div id="footer" role="contentinfo">
<span>&copy; William H. Weiskopf IV 2009</span>
<div class="translate">
	<a onClick="javascript: translator('en|es')"><img src="/images/flags/spanish.jpg" /></a>
	<a onClick="javascript: translator('en|fr')"><img src="/images/flags/french.jpg" /></a>
	<a onClick="javascript: translator('en|de')"><img src="/images/flags/german.jpg" /></a>
	<a onClick="javascript: translator('en|zh-CN')"><img src="/images/flags/chinese.jpg" /></a>
	<a onClick="javascript: translator('en|ja')"><img src="/images/flags/japanese.jpg" /></a>
	<a onClick="javascript: translator('en|ar')"><img src="/images/flags/arabic.png" /></a>
	<a onClick="javascript: translator('en|it')"><img src="/images/flags/italian.jpg" /></a>
	<a onClick="javascript: translator('en|pt')"><img src="/images/flags/portuguese.jpg" /></a>
	<a onClick="javascript: translator('en|ru')"><img src="/images/flags/russian.png" /></a>
	<a onClick="javascript: translator('en|ko')"><img src="/images/flags/korean.jpg" /></a>
</div>
<br />
	<?php locate_template( array('blocks/k2-footer.php'), true ); ?>

	<?php /* K2 Hook */ do_action('template_footer'); ?>
</div><!-- #footer -->

<?php wp_footer(); ?>

</div> <!-- Close Page -->

</body>
</html> 
