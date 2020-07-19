<div class="wrap omise">
	<style>
		.omise-notice-testmode {
			background: #ffce00;
			color: #575D66;
			border: 1px solid #efc200;
			border-left-width: 4px;
		}
	</style>
	<h1><?php echo $title; ?></h1>

	<?php $this->display_messages(); ?>
	
	<h2>Schedule Setting</h2>
	<p>This setting is to schedule a runner to execute this script to check unpaid orders<br/>
	Unit: minutes (0 = disabled)</p>

	<form method="POST">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="schedule_time">Schedule for every (minutes):</label></th>
					<td>
						<fieldset>
							<input name="schedule_time" type="number" id="schedule_time" value="<?php echo $this->settings['schedule_time']; ?>" class="regular-text" placeholder="minutes" />
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>

		<hr />

		<input type="hidden" name="omise_order_handler_setting_page_nonce" value="<?= wp_create_nonce( 'omise-order-handler-setting' ); ?>" />
		<?php submit_button( __( 'Save Settings', 'omise' ) ); ?>

	</form>
</div>
