<?php
/**
 * Pro Designs and Plugins Feed
 *
 * @package Woo Product Slider and Carousel with category
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Action to add menu
add_action('admin_menu', 'wcpscwc_register_design_page');

/**
 * Register plugin design page in admin menu
 * 
 * @package Woo Product Slider and Carousel with category
 * @since 1.0.0
 */
function wcpscwc_register_design_page() {
	add_submenu_page( 'edit.php?post_type='.WCPSCW_POST_TYPE, __('How it works, our plugins and offers', 'woo-product-slider-and-carousel-with-category'), __('Product Slider - How It Works', 'woo-product-slider-and-carousel-with-category'), 'manage_options', 'wcpscwc-designs', 'wcpscwc_designs_page' );
}

/**
 * Function to display plugin design HTML
 * 
 * @package Woo Product Slider and Carousel with category
 * @since 1.0.0
 */
function wcpscwc_designs_page() {

	$wpos_feed_tabs = wcpscwc_help_tabs();
	$active_tab 	= isset($_GET['tab']) ? $_GET['tab'] : 'how-it-work';
?>
		
	<div class="wrap wcpscwc-wrap">

		<h2 class="nav-tab-wrapper">
			<?php
			foreach ($wpos_feed_tabs as $tab_key => $tab_val) {
				$tab_name	= $tab_val['name'];
				$active_cls = ($tab_key == $active_tab) ? 'nav-tab-active' : '';
				$tab_link 	= add_query_arg( array( 'post_type' => WCPSCW_POST_TYPE, 'page' => 'wcpscwc-designs', 'tab' => $tab_key), admin_url('edit.php') );
			?>

			<a class="nav-tab <?php echo $active_cls; ?>" href="<?php echo $tab_link; ?>"><?php echo $tab_name; ?></a>

			<?php } ?>
		</h2>
		
		<div class="wcpscwc-tab-cnt-wrp">
		<?php
			if( isset($active_tab) && $active_tab == 'how-it-work' ) {
				wcpscwc_howitwork_page();
			}
			else if( isset($active_tab) && $active_tab == 'plugins-feed' ) {
				echo wcpscwc_get_plugin_design( 'plugins-feed' );
			} else {
				echo wcpscwc_get_plugin_design( 'offers-feed' );
			}
		?>
		</div>

	</div>

<?php
}

/**
 * Gets the plugin design part feed
 *
 * @package Woo Product Slider and Carousel with category
 * @since 1.0.0
 */
function wcpscwc_get_plugin_design( $feed_type = '' ) {
	
	$active_tab = isset($_GET['tab']) ? $_GET['tab'] : '';
	
	// If tab is not set then return
	if( empty($active_tab) ) {
		return false;
	}

	// Taking some variables
	$wpos_feed_tabs = wcpscwc_help_tabs();
	$transient_key 	= isset($wpos_feed_tabs[$active_tab]['transient_key']) 	? $wpos_feed_tabs[$active_tab]['transient_key'] 	: 'wcpscwc_' . $active_tab;
	$url 			= isset($wpos_feed_tabs[$active_tab]['url']) 			? $wpos_feed_tabs[$active_tab]['url'] 				: '';
	$transient_time = isset($wpos_feed_tabs[$active_tab]['transient_time']) ? $wpos_feed_tabs[$active_tab]['transient_time'] 	: 172800;
	$cache 			= get_transient( $transient_key );
	
	if ( false === $cache ) {
		
		$feed 			= wp_remote_get( esc_url_raw( $url ), array( 'timeout' => 120, 'sslverify' => false ) );
		$response_code 	= wp_remote_retrieve_response_code( $feed );
		
		if ( ! is_wp_error( $feed ) && $response_code == 200 ) {
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$cache = wp_remote_retrieve_body( $feed );
				set_transient( $transient_key, $cache, $transient_time );
			}
		} else {
			$cache = '<div class="error"><p>' . __( 'There was an error retrieving the data from the server. Please try again later.', 'woo-product-slider-and-carousel-with-category' ) . '</div>';
		}
	}
	return $cache;	
}

/**
 * Function to get plugin feed tabs
 *
 * @package Woo Product Slider and Carousel with category
 * @since 1.0.0
 */
function wcpscwc_help_tabs() {
	$wpos_feed_tabs = array(
						'how-it-work' 	=> array(
													'name' => __('How It Works', 'woo-product-slider-and-carousel-with-category'),
												),
						'plugins-feed' 	=> array(
													'name' 				=> __('Our Plugins', 'woo-product-slider-and-carousel-with-category'),
													'url'				=> 'http://wponlinesupport.com/plugin-data-api/plugins-data.php',
													'transient_key'		=> 'wpos_plugins_feed',
													'transient_time'	=> 172800
												),
						'offers-feed' 	=> array(
													'name'				=> __('WPOS Offers', 'woo-product-slider-and-carousel-with-category'),
													'url'				=> 'http://wponlinesupport.com/plugin-data-api/wpos-offers.php',
													'transient_key'		=> 'wpos_offers_feed',
													'transient_time'	=> 86400,
												)
					);
	return $wpos_feed_tabs;
}

/**
 * Function to get 'How It Works' HTML
 *
 * @package Woo Product Slider and Carousel with category
 * @since 1.0.0
 */
function wcpscwc_howitwork_page() { ?>
	
	<style type="text/css">
		.wpos-pro-box .hndle{background-color:#0073AA; color:#fff;}
		.wpos-pro-box .postbox{background:#dbf0fa none repeat scroll 0 0; border:1px solid #0073aa; color:#191e23;}
		.postbox-container .wpos-list li:before{font-family: dashicons; content: "\f139"; font-size:20px; color: #0073aa; vertical-align: middle;}
		.wcpscwc-wrap .wpos-button-full{display:block; text-align:center; box-shadow:none; border-radius:0;}
		.wcpscwc-shortcode-preview{background-color: #e7e7e7; font-weight: bold; padding: 2px 5px; display: inline-block; margin:0 0 2px 0;}
	</style>

	<div class="post-box-container">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
			
				
				<div id="post-body-content">
					<div class="metabox-holder">
						<div class="meta-box-sortables ui-sortable">
							<div class="postbox">
								
								<h3 class="hndle">
									<span><?php _e( 'How It Works - Display and shortcode', 'woo-product-slider-and-carousel-with-category' ); ?></span>
								</h3>
								
								<div class="inside">
									<table class="form-table">
										<tbody>
											<tr>
												<th>
													<label><?php _e('Geeting Started with Product Slider', 'woo-product-slider-and-carousel-with-category'); ?>:</label>
												</th>
												<td>
													<ul>
														<li><?php _e('Step-1. This plugin is use to slide Products with 3 option' , 'woo-product-slider-and-carousel-with-category'); ?></li>
														<li><?php _e('Step-2. A) Product Slider, B) Best Selling Product in slider, C) Featured Product in slider', 'woo-product-slider-and-carousel-with-category'); ?></li>
														<li><?php _e('Step-3. A) Product Slider : Display all latest products ', 'woo-product-slider-and-carousel-with-category'); ?></li>
														<li><?php _e('Step-4. B) Best Selling Product in slider : Display all best selling products', 'woo-product-slider-and-carousel-with-category'); ?></li>
														<li><?php _e('Step-5. C) Featured Product in slider : Display all Featured selling products. To select a product as a featured click on STAR button on product list.', 'woo-product-slider-and-carousel-with-category'); ?></li>
														<li><?php _e('Step-6. You can also use <b>Category ID with all 3 shortcode</b> to filter the products category wise.', 'woo-product-slider-and-carousel-with-category'); ?></li>
														
													</ul>
												</td>
											</tr>

											<tr>
												<th>
													<label><?php _e('How Shortcode Works', 'woo-product-slider-and-carousel-with-category'); ?>:</label>
												</th>
												<td>
													<ul>
														<li><?php _e('Step-1. Create a page like Product Slider OR Best Selling Products.', 'woo-product-slider-and-carousel-with-category'); ?></li>
														<li><?php _e('Step-2. Put below shortcode as per your need.', 'woo-product-slider-and-carousel-with-category'); ?></li>
													</ul>
												</td>
											</tr>

											<tr>
												<th>
													<label><?php _e('All Shortcodes', 'woo-product-slider-and-carousel-with-category'); ?>:</label>
												</th>
												<td>
													<span class="wcpscwc-shortcode-preview">[products_slider]</span> – <?php _e('Product in slider / carousel Shortcode', 'woo-product-slider-and-carousel-with-category'); ?> <br />
													<span class="wcpscwc-shortcode-preview">[bestselling_products_slider]</span> – <?php _e('Best Selling Product in slider Shortcode', 'woo-product-slider-and-carousel-with-category'); ?> <br /> 
													<span class="wcpscwc-shortcode-preview">[featured_products_slider]</span> – <?php _e('Featured Product in slider Shortcode', 'woo-product-slider-and-carousel-with-category'); ?> 
												
												</td>
											</tr>						
												
											<tr>
												<th>
													<label><?php _e('Need Support?', 'woo-product-slider-and-carousel-with-category'); ?></label>
												</th>
												<td>
													<p><?php _e('Check plugin document for shortcode parameters and demo for designs.', 'woo-product-slider-and-carousel-with-category'); ?></p> <br/>
													<a class="button button-primary" href="https://wordpress.org/plugins/woo-product-slider-and-carousel-with-category/" target="_blank"><?php _e('Documentation', 'woo-product-slider-and-carousel-with-category'); ?></a>								
													<a class="button button-primary" href="http://demo.wponlinesupport.com/product-slider-and-carousel-demo/?utm_source=hp&event=demo" target="_blank"><?php _e('Demo for Designs', 'woo-product-slider-and-carousel-with-category'); ?></a>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				
				<div id="postbox-container-1" class="postbox-container">
					<div class="metabox-holder wpos-pro-box">
						<div class="meta-box-sortables ui-sortable">
							<div class="postbox" style="">
									
								<h3 class="hndle">
									<span><?php _e( 'Upgrate to Pro', 'woo-product-slider-and-carousel-with-category' ); ?></span>
								</h3>
								<div class="inside">										
									<ul class="wpos-list">
										<li>15+ cool designs</li>
										<li>3 shortcodes with various parameters</li>										
										<li>Display products in grid</li>	
										<li>3 Widgets</li>										
										<li>Custom CSS</li>
										<li>Fully responsive</li>
										<li>100% Multi language</li>
									</ul>
									<a class="button button-primary wpos-button-full" href="https://www.wponlinesupport.com/wp-plugin/woo-product-slider-carousel-category/?utm_source=hp&event=go_premium" target="_blank"><?php _e('Go Premium ', 'woo-product-slider-and-carousel-with-category'); ?></a>	
									<p><a class="button button-primary wpos-button-full" href="http://demo.wponlinesupport.com/prodemo/woo-product-slider-and-carousel-with-category-pro/?utm_source=hp&event=pro_demo" target="_blank"><?php _e('View PRO Demo ', 'woo-product-slider-and-carousel-with-category'); ?></a>			</p>								
								</div>
							</div>
						</div>
					</div>

					
					<div class="metabox-holder">
						<div class="meta-box-sortables ui-sortable">
							<div class="postbox">
									<h3 class="hndle">
										<span><?php _e( 'Help to improve this plugin!', 'woo-product-slider-and-carousel-with-category' ); ?></span>
									</h3>									
									<div class="inside">										
										<p>Enjoyed this plugin? You can help by rate this plugin <a href="https://wordpress.org/support/plugin/woo-product-slider-and-carousel-with-category/reviews/?filter=5" target="_blank">5 stars!</a></p>
									</div>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
<?php }