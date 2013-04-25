<?php 
$admin = dirname( __FILE__ ) ;
$admin = substr( $admin , 0 , strpos( $admin , 'wp-content' ) );
require_once( $admin . 'wp-admin/admin.php' );

$sendola_button = sendola_get_buttons();
?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	
	<style>
		table {
		  max-width: 100%;
		  background-color: transparent;
		  border-collapse: collapse;
		  border-spacing: 0;
		  width: 100%;
		  margin-top: 30px;
		  margin-bottom: 20px;
		}
		.table-hover tbody tr:hover > td,
		.table-hover tbody tr:hover > th {
		  background-color: #f5f5f5;
		}
		.buttons {
			text-align: center;
			/*font-family: lato, sans-serif;*/
			font-size:14px;
		}
		.buttons-logo {
			height: 50px;
			width: 50px;
		}
		td, th {
			padding: 10px;
			vertical-align: middle;
			border-bottom: 1px solid #d9d9d9;
			font-size: 14px !important;
		}
		td:hover {
			cursor: pointer;
		}
		a.button, input.button, button {
			float: right;
			background-color:#9bad50;
			background-image:none;
			border:none;
			-webkit-border-radius:3px;
			border-radius:3px;
			color:#ffffff !important;
			display:inline-block;
			font-family:lato;
			font-size:16px;
			padding:13px 17px;
			text-decoration:none;
			text-shadow: 1px 1px 1px #5c5c5c;
			filter: dropshadow(color=#5c5c5c, offx=1, offy=1);
			-webkit-box-shadow: 0px 0px 1px 0px rgba(1, 1, 1, 0.5);
			box-shadow: 0px 0px 1px 0px rgba(1, 1, 1, 0.5);
			transition: background-color 0.2s ease 0s;
			-moz-transition: background-color 0.2s ease 0s;
			-webkit-transition: background-color 0.2s ease 0s;
			-o-transition: background-color 0.2s ease 0s;
			width:auto;
		}	
		a.button:hover, input.button:hover, button:hover {
			background-color:#859540;
			cursor:pointer;
			text-decoration:none;
			
		}
		a.button:active, input.button:active, button:active {
			position:relative;
			top:1px;
		}
	</style>
</head>
<body class="buttons">
	<table class="table-hover">
		<tr>
			<th></th>
			<th>Company name</th>
			<th>Domain name</th>
			<th>Description</th>
			<th>Shortcode</th>
		</tr>
		<?php foreach ( $sendola_buttons as $button ) : ?>
		<tr class="button-row" data-id="<?php echo $button->id ?>">
			<td><img src="<?php echo $button->image ?>" alt="logo" class="buttons-logo"></td>
			<td><?php echo $button->name ?></td>
			<td><?php echo $button->domain ?></td>
			<td><?php echo $button->description ?></td>
			<td>[sendola id=<?php echo $button->id ?>]</td>
		</tr>
		<?php endforeach; ?>
	</table>
	<a href="https://admin.sendola.com/web_buttons/add_new?utm_source=Add%2Bnew%2Bbutton&utm_medium=Button%2Blink&utm_campaign=Wordpress%2BPlugin" target="_blank" class="button">Add more</a>

	<script type="text/javascript" src="<?php echo get_option( 'siteurl' ) ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script type="text/javascript" src="<?php echo get_option( 'siteurl' ) ?>/wp-includes/js/jquery/jquery.js"></script>
	<script>
		(function ($) {
			$('.button-row').click(function () {
				// use attr, because jQuery 1.4.2 (which is used in wp 3.0) doesn't support html5 `data-` attributes
				var shortcode = '[sendola id=' + $(this).attr('data-id') + ']';
				tinyMCE.activeEditor.execCommand( 'mceInsertContent', false, shortcode );
				tinyMCEPopup.close();
			});
		}(jQuery));
	</script>
</body>
</html>
