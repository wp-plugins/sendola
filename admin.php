<?php
add_action( 'admin_menu', 'sendola_admin_menu' );

sendola_admin_warnings();

function sendola_admin_init() {
	global $wp_version;

	// all admin functions are disabled in old versions
	if ( ! function_exists( 'is_multisite' ) && version_compare( $wp_version, '3.0', '<' )) {
		function sendola_version_warning()
		{
			echo "<div id='sendola-warning' class='updated fade'><p><strong>".sprintf( __( 'Sendola %s requires WordPress 3.0 or higher.' ), SENDOLA_VERSION ) ."</strong> ".sprintf(__('Please <a href="%s">upgrade WordPress</a> to a current version</a>.'), 'http://codex.wordpress.org/Upgrading_WordPress'). "</p></div>";
		}
		add_action( 'admin_notices', 'sendola_version_warning' ); 

		return;
	}
}
add_action( 'admin_init', 'sendola_admin_init' );


function sendola_load_js_and_css() {
	wp_register_style( 'sendola.css', SENDOLA_PLUGIN_URL . 'sendola.css', array(), '1.0.0' );
	wp_enqueue_style( 'sendola.css');
}
add_action( 'admin_enqueue_scripts', 'sendola_load_js_and_css' );

/**
 * Add sendola config submenu in settings
 */
function sendola_admin_menu() {
	add_options_page( 'Sendola Options', 'Sendola', 'manage_options', 'sendola-options', 'sendola_conf' );
}

function sendola_admin_warnings() {
	// if the registration is not finished then always show warning
	if ( ! get_option( 'sendola_api_key' ) &&  ! isset( $_POST['submit'] ) ) {
		function sendola_warning()
		{
			echo "<div id='sendola-warning' class='updated fade'><p><strong>".__( 'Sendola is almost ready.' )."</strong> ".sprintf( __( 'You must <a href="%1$s">enter your Sendola Plugin key</a> for it to work.' ), "admin.php?page=sendola-options" )."</p></div>";
		}
		add_action('admin_notices', 'sendola_warning');
		return;
	}
	// if user has no buttons then always show warning
	else if ( ! sendola_has_buttons() &&  ! isset( $_POST['submit'] ) ) {
		function sendola_warning()
		{
			echo "<div id='sendola-warning' class='updated fade'><p><strong>".__( 'You don\'t have any Sendola buttons for this domain.' )."</strong> ".sprintf( __( '<a target="_blank" href="%1$s">Add some</a>' ), "https://admin.sendola.com/web_buttons?utm_source=Add%2Bbutton&utm_medium=Text%2Blink&utm_campaign=Wordpress%2BPlugin
" )."</p></div>";
		}
		add_action( 'admin_notices', 'sendola_warning' );
		return;
	}
}

function sendola_nonce_field( $action = -1 ) {
	return wp_nonce_field( $action );
}
$sendola_nonce = 'sendola-update-key';


function sendola_conf() {
	global $sendola_nonce;

	$ms = array();

	// new key submited
	if ( isset( $_POST['submit'] ) ) {
		// prevent cheating
		if ( function_exists( 'current_user_can' ) &&  ! current_user_can( 'manage_options' ) )
			die(__('Cheatin&#8217; uh?'));

		check_admin_referer( $sendola_nonce );
		$sendola_api_key = preg_replace( '/[^a-h0-9]/i', '', $_POST['sendola_api_key'] );

		if ( empty( $sendola_api_key ) ) {
			$key_status = 'empty';
			$ms[] = 'new_key_empty';
			delete_option( 'sendola_api_key' );
		} else {
			$key_status = sendola_verify_key( $sendola_api_key );
			if ( $key_status == 'valid' ) {
				// all ok, save the key
				update_option( 'sendola_api_key', $sendola_api_key );
				$ms[] = 'new_key_valid';
			} else {
				$ms[] = 'new_key_invalid';
			}
		}
	} else {
		$sendola_api_key = get_option( 'sendola_api_key' );
		if ( empty( $sendola_api_key ) ) {
			$key_status = 'empty';
		} else {
			$key_status = sendola_verify_key( $sendola_api_key );
			if ( $key_status == 'valid' ) {
				$ms[] = 'key_valid';
			} else {
				$ms[] = 'key_invalid';
			}
		}
	}

	$sendola_buttons = sendola_get_buttons(true);

	$messages = array(
		'new_key_empty'   => array('color' => 'aa0', 'text' => __('Plugin key has been cleared.')),
		'new_key_valid'   => array('color' => '00ff00', 'text' => __('Plugin key has been verified.')),
		'new_key_invalid' => array('color' => 'ffff00', 'text' => __('Plugin key invalid')),
		'key_valid'       => array('color' => '00ff00', 'text' => __('Plugin key is valid.')),
		'key_invalid'     => array('color' => 'ffff00', 'text' => __('Plugin key is invalid.')),
	);
	?>


	<div class="wrap">
		<h2><?php _e('Sendola Settings'); ?></h2>

		<div class="sendola-settings">
			<form action="" method="post" id="sendola-conf" class="sendola-api-block">

				<?php if ( ! $sendola_api_key || $key_status != 'valid') : ?>
					<h3><label for="sendola_api_key">Enter your Sendola Plugin Key</label></h3>
					<p>
						You can find your Plugin Key in your Sendola account.<br />
						Don't have an account? Create a button to get started.
					</p>
					<a href="https://admin.sendola.com/auth/login?utm_source=Login&utm_medium=Button%2Blink&utm_campaign=Wordpress%2BPlugin" class="sendola-button sendola-button-login" target="_blank">Login</a>
					<a href="http://www.sendola.com/register?utm_source=Create%2Baccount&utm_medium=Button%2Blink&utm_campaign=Wordpress%2BPlugin" class="sendola-button sendola-button-create" target="_blank">Create your button</a>

					<p style="margin-top: 40px;">Your key is located on the <a href="https://admin.sendola.com/plugins?utm_source=Plugins%2Blink&utm_medium=Text%2Blink&utm_campaign=Wordpress%2BPlugin
" target="_blank">Plugins</a> page of your Sendola account.</p>
				<?php else : ?>
					<p>Your <a href="http://www.sendola.com/?utm_source=Sendola%2Blink&utm_medium=Text%2Blink&utm_campaign=Wordpress%2BPlugin
" target="_blank">Sendola</a> account has been successfully linked.</p>
				<?php endif; ?>

				<input type="text" id="sendola_api_key" name="sendola_api_key" class="sendola-textinput" maxlength="50" value="<?php echo $sendola_api_key; ?>" />
				<?php foreach ( $ms as $m ) : ?>
					<p class="sendola-messages" style="background-color: #<?php echo $messages[$m]['color']; ?>;"><?php echo $messages[$m]['text']; ?></p>
				<?php endforeach; ?>

				<?php sendola_nonce_field( $sendola_nonce ) ?>
				<input type="submit" name="submit" value="Update key" class="sendola-button sendola-button-update">

			</form>

			<?php if ( $sendola_api_key  && $key_status == 'valid' ) : ?>
			<div class="sendola-buttons-block">
				<h3>Your buttons</h3>
				<a href="https://admin.sendola.com/web_buttons/add_new?utm_source=Add%2Bnew%2Bbutton&utm_medium=Button%2Blink&utm_campaign=Wordpress%2BPlugin" target="_blank" class="sendola-button sendola-add-new">Add a new button</a>
				<div class="clearfix"></div>
				<?php if ( sendola_has_buttons() ) : ?>
					<table>
						<tr>
							<th></th>
							<th>Company name</th>
							<th>Domain name</th>
							<th>Description</th>
							<th>Shortcode</th>
							<th></th>
						</tr>
						<?php foreach ( $sendola_buttons as $button ) : ?>
						<tr>
							<td><img src="<?php echo $button->image ?>" alt="logo" class="sendola-buttons-logo"></td>
							<td><?php echo $button->name ?></td>
							<td><?php echo $button->domain ?></td>
							<td><?php echo $button->description ?></td>
							<td>[sendola id=<?php echo $button->id ?>]</td>
							<td><a href="https://admin.sendola.com/web_buttons/edit/card_id/<?php echo $button->id ?>?utm_source=Edit%2Bbutton&utm_medium=Button%2Blink&utm_campaign=Wordpress%2BPlugin" target="_blank"><img src="https://admin.sendola.com/img/edit.png" alt="Edit this button" title="Edit this button"></a></td>
						</tr>
						<?php endforeach; ?>
					</table>
				<?php else : ?>
				
				<p>You don't have any Sendola buttons for this domain.</p>

				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>

<?php } ?>