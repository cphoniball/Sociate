<?php
/* Options for Sociate */
?>

<div class="wrap sociate-options">
	<?php screen_icon(); ?>
	<h2>Sociate Options</h2>

	<div class="instructions"></div>

	<div class="settings">
		<form action="options.php" method="post">
			<?php
				settings_fields( 'sociate_options' );
				do_settings_sections( 'sociate_accounts' );
				do_settings_sections( 'sociate_services' );
				do_settings_sections( 'sociate_trending' );
				submit_button();
			?>
		</form>
	</div>

</div>