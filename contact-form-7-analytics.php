<?php
/**
 * Plugin Name: Contact Form 7 Analytics
 * Plugin URI: https://github.com/maheshwaghmare/contact-form-7-analytics/
 * Description: Analytics of contact form 7.
 * Version: 1.0.0
 * Author: Mahesh M. Waghmare
 * Author URI: https://maheshwaghmare.wordpress.com/
 * Text Domain: contact-form-7-analytics
 *
 * @package Contact Form 7 Analytics
 */

/**
 * Set constants.
 */
define( 'CF7_ANALYTICS_VER', '1.0.0' );
define( 'CF7_ANALYTICS_FILE', __FILE__ );
define( 'CF7_ANALYTICS_BASE', plugin_basename( CF7_ANALYTICS_FILE ) );
define( 'CF7_ANALYTICS_DIR', plugin_dir_path( CF7_ANALYTICS_FILE ) );
define( 'CF7_ANALYTICS_URI', plugins_url( '/', CF7_ANALYTICS_FILE ) );

add_action( 'admin_enqueue_scripts', function() {
	wp_enqueue_script( 'chartjs', CF7_ANALYTICS_URI . 'js/Chart.js', array( 'jquery' ));
}, 10 );
add_action( 'admin_menu', 'wpcf7_analytics_admin_menu', 11 );

function wpcf7_analytics_admin_menu() {

	add_submenu_page( 'wpcf7', __( 'Analytics with Other Services', 'contact-form-7' ),
		__( 'Analytics', 'contact-form-7' ),
		'wpcf7_manage_integration', 'manage_options',
		'wpcf7_admin_analytics_page'
	);
}

function wpcf7_admin_analytics_page() {
?>
<div class="wrap">
	<h1><?php echo esc_html( __( 'Integration with Other Services', 'contact-form-7' ) ); ?></h1>
	<?php
	$defaults = array(
		'post_type'    => 'wpcf7_contact_form',
		'posts_per_page' => -1,
	);

	$q = new WP_Query();
	$posts = $q->query( $defaults );

	foreach ( (array) $posts as $post ) {
		vl( $post->ID );
	}

	?>
	<div style="width: 50%;">
		<canvas id="myChart"></canvas>
	</div>
	<script type="text/javascript">
	var ctx = document.getElementById("myChart").getContext('2d');
	var myChart = new Chart(ctx, {
	    type: 'line',
	    data:  {
			labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
			datasets: [{
				label: 'Form 1',
				borderColor: 'rgba(255, 99, 132, 0.2)',
				backgroundColor: 'rgba(54, 162, 235, 0.2)',
				fill: false,
				data: [
					10, 40,20
				],
				yAxisID: 'y-axis-1',
			}, {
				label: 'Form 2',
				borderColor: 'rgba(255, 99, 132, 0.2)',
				backgroundColor: 'rgba(54, 162, 235, 0.2)',
				fill: false,
				data: [
					10, 20, 40
				],
				yAxisID: 'y-axis-2'
			}]
		},
	    options: {
			responsive: true,
			hoverMode: 'index',
			stacked: false,
			title: {
				display: true,
				text: 'Impressions'
			},
			scales: {
				yAxes: [{
					type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
					display: true,
					position: 'left',
					id: 'y-axis-1',
				}, {
					type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
					display: true,
					position: 'right',
					id: 'y-axis-2',

					// grid line settings
					gridLines: {
						drawOnChartArea: false, // only want the grid lines for one axis to show up
					},
				}],
			}
		}
	});
	</script>
</div>
<?php 
}

add_action( 'wpcf7_contact_form', function( $self ) {
	if( $self->id() ) {

		$old_impressions = get_post_meta( $self->id(), 'impressions', 1 );
		$new_impressions = $old_impressions + 1;
		update_post_meta( $self->id(), 'impressions', $new_impressions );
		// vl( $new_impressions );
	}
	// vl( get_post_meta() );
	// vl( get_post_meta( get_the_ID() ) );
	// vl( get_post_meta( get_the_ID(), 'meta_key', true ) );
} );

add_filter( 'wpcf7_editor_panels', function( $panels ) {
	
	$panels['analytics'] = array(
		'title' => __( 'Analytics', 'contact-form-7' ),
		'callback' => 'analytics_callback',
	);

	return $panels;

});

function analytics_callback( $post ) {
	
	// Calculate conversion rate.
	$impressions = get_post_meta( $post->id(), 'impressions', true );
	if( empty( $impressions ) ) {
		$impressions = 0;
	}

	$conversions = get_post_meta( $post->id(), 'conversions', true );
	if( empty( $conversions ) ) {
		$conversions = 0;
	}
	
	$conversion_rate = ( $conversions / $impressions );
	$conversion_rate = number_format( (float) $conversion_rate, 2, '.', '' ) * 100;
	if( $conversion_rate ) {
		$conversion_rate = $conversion_rate . '%';
	} else {
		$conversion_rate = 'NA';
	}
	?>
	<style type="text/css">
		.cp-popup-container {
		    display: inline-block;
		    width: 100%;
		}
		.cp-col-3 {
		    position: relative;
		    width: calc(100% / 3 - 30px);
		    margin: 15px;
		    float: left;
		}
		.cp-stat-v2 {
		    position: relative;
		    padding: 0;
		    background: #fff;
		    box-shadow: 0 1px 3px rgba(0, 0, 0, .10), 0 4px 18px rgba(0, 0, 0, .15);
		    -webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, .10), 0 4px 18px rgba(0, 0, 0, .15);
		}
		.cp-impression-info {
		    display: block;
		    padding: 36px 40px;
		    text-decoration: none;
		    text-align: right;
		    color: #1da5e5;
		}
		.cp-impression-info > .cp-visual {
		    position: absolute;
		    left: -12px;
		    bottom: 0;
		}
		.cp-impression-count, .cp-impression-title {
		    color: #0073aa;
		}
		.cp-impression-count {
		    font-size: 50px;
		    line-height: 1.2em;
		}
		.cp-visual > i {
		    color: rgba(0, 115, 170, 0.65);
		    font-size: 125px;
		    line-height: 154px;
		    width: 1em;
		    height: 1em;
		    opacity: .1;
		}
	</style>
	<div class="cp-popup-container">
	<div class="cp-col-3 cp-stat-v2">
		<div class="cp-impression-info">
			<div class="cp-visual">
				<i class="dashicons dashicons-chart-pie"></i>
			</div>
			<div class="cp-impression-count"><?php echo $impressions; ?></div>
			<div class="cp-impression-title">Impressions</div>
		</div>
	</div> 
	<div class="cp-col-3 cp-stat-v2">
		<div class="cp-impression-info">
			<div class="cp-visual">
				<i class="dashicons dashicons-chart-bar"></i>
			</div>
			<div class="cp-impression-count"><?php echo $conversions; ?></div>
			<div class="cp-impression-title">Conversions</div>
		</div>
	</div>
	<div class="cp-col-3 cp-stat-v2">
		<div class="cp-impression-info">
			<div class="cp-visual">
				<i class="dashicons dashicons-groups"></i>
			</div>
			<div class="cp-impression-count"><?php echo $conversion_rate; ?></div>
			<div class="cp-impression-title">Conversion Rate</div>
		</div>
	</div>
</div>
	<?php
}



// @todo Check `Real-time Analytics` from site https://www.convertplug.com/plus/features/

// wpcf7_mail_sent
// wpcf7_mail_failed


// // Add the custom columns to the book post type:
// add_filter( 'manage_toplevel_page_wpcf7_columns', 'set_custom_edit_wpcf7_columns' );
// function set_custom_edit_wpcf7_columns($columns) {
//     unset( $columns['author'] );
//     $columns['wpcf7_author'] = __( 'Author', 'your_text_domain' );
//     $columns['publisher'] = __( 'Publisher', 'your_text_domain' );

//     return $columns;
// }

// // Add the data to the custom columns for the wpcf7 post type:
// add_action( 'manage_toplevel_page_wpcf7_custom_column' , 'custom_book_column', 10, 2 );
// function custom_book_column( $column, $post_id ) {
//     switch ( $column ) {

//         case 'book_author' :
//             $terms = get_the_term_list( $post_id , 'book_author' , '' , ',' , '' );
//             if ( is_string( $terms ) )
//                 echo $terms;
//             else
//                 _e( 'Unable to get author(s)', 'your_text_domain' );
//             break;

//         case 'publisher' :
//             echo get_post_meta( $post_id , 'publisher' , true ); 
//             break;

//     }
// }