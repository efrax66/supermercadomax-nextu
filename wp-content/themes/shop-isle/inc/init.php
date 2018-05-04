<?php
/**
 * The init file.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

if ( ! defined( 'ELEMENTOR_PARTNER_ID' ) ) {
	define( 'ELEMENTOR_PARTNER_ID', 2112 );
}

add_filter( 'image_size_names_choose', 'shop_isle_media_uploader_custom_sizes' );
/**
 * Media uploader custom sizes.
 *
 * @param string $sizes The image sizes.
 *
 * @return array
 */
function shop_isle_media_uploader_custom_sizes( $sizes ) {
	return array_merge(
		$sizes, array(
			'shop_isle_banner_homepage'    => esc_html__( 'Banners section', 'shop-isle' ),
			'shop_isle_category_thumbnail' => esc_html__( 'Categories Section', 'shop-isle' ),
		)
	);
}



/**
 * Setup.
 * Enqueue styles, register widget regions, etc.
 */
require get_template_directory() . '/inc/functions/setup.php';

/**
 * Setup.
 * Enqueue styles, register widget regions, etc.
 */
require get_template_directory() . '/inc/page-builder-extras.php';

/**
 * Structure.
 * Template functions used throughout the theme.
 */
require get_template_directory() . '/inc/structure/hooks.php';
require get_template_directory() . '/inc/structure/post.php';
require get_template_directory() . '/inc/structure/page.php';
require get_template_directory() . '/inc/structure/header.php';
require get_template_directory() . '/inc/structure/footer.php';
require get_template_directory() . '/inc/structure/comments.php';
require get_template_directory() . '/inc/structure/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/functions/extras.php';

/**
 * Include translation functions.
 */
$translation_path = get_template_directory() . '/inc/translations/functions.php';
if ( file_exists( $translation_path ) ) {
	require $translation_path;
}

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer/customizer-repeater/functions.php';
require get_template_directory() . '/inc/customizer/customizer.php';
require get_template_directory() . '/inc/customizer/functions.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack/jetpack.php';

/**
 * Load WooCommerce compatibility files.
 */
if ( is_woocommerce_activated() ) {
	require get_template_directory() . '/inc/woocommerce/hooks.php';
	require get_template_directory() . '/inc/woocommerce/functions.php';
	require get_template_directory() . '/inc/woocommerce/template-tags.php';
	require get_template_directory() . '/inc/woocommerce/integrations.php';
}

/**
 * Load Dokan compatibility file
 */
if ( class_exists( 'WeDevs_Dokan' ) ) {
	require get_template_directory() . '/inc/dokan/functions.php';
}

/**
 * Checkout page
 * Move the coupon fild and message info after the order table
 **/
function shop_isle_coupon_after_order_table_js() {
	wc_enqueue_js(
		'
		$( $( ".woocommerce-info, .checkout_coupon" ).detach() ).appendTo( "#shop-isle-checkout-coupon" );
	'
	);
}
add_action( 'woocommerce_before_checkout_form', 'shop_isle_coupon_after_order_table_js' );

/**
 * Add coupon after order table.
 */
function shop_isle_coupon_after_order_table() {
	echo '<div id="shop-isle-checkout-coupon"></div><div style="clear:both"></div>';
}
add_action( 'woocommerce_checkout_order_review', 'shop_isle_coupon_after_order_table' );


// Ensure cart contents update when products are added to the cart via AJAX )
add_filter( 'woocommerce_add_to_cart_fragments', 'shop_isle_woocommerce_header_add_to_cart_fragment' );

/**
 * Add to cart to header.
 *
 * @param string $fragments The fragments.
 *
 * @return mixed
 */
function shop_isle_woocommerce_header_add_to_cart_fragment( $fragments ) {
	ob_start();
	?>

		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" title="<?php esc_html_e( 'View your shopping cart', 'shop-isle' ); ?>" class="cart-contents">
			<span class="icon-basket"></span>
			<span class="cart-item-number"><?php echo esc_html( trim( WC()->cart->get_cart_contents_count() ) ); ?></span>
		</a>

	<?php

	$fragments['a.cart-contents'] = ob_get_clean();

	return $fragments;
}

/**
 * Migrate section order.
 */
function shop_isle_migrate() {
	$old_order      = get_theme_mod( 'shop_isle_sections_control' );
	$sections_order = get_theme_mod( 'sections_order' );

	if ( empty( $sections_order ) ) {
		if ( ! empty( $old_order ) ) {
			$new_order = array();
			$old_order = json_decode( $old_order, 'true' );
			foreach ( $old_order as $key => $iterator ) {
				$iterator = reset( $iterator );

				/* Update control display */
				$hide_control_name = str_replace( 'section', 'hide', $iterator['section_id'] );
				set_theme_mod( $hide_control_name, ! (bool) $iterator['show'] );

				/* Create json for new sections order */
				if ( $iterator['section_id'] !== 'shop_isle_slider_section' ) {
					$new_order[ $iterator['section_id'] ] = ( $key + 2 ) * 5;
				}
			}

			set_theme_mod( 'sections_order', json_encode( $new_order ) );
		}
	}
	update_option( 'shop_isle_section_order_migrate', 'yes' );
}

$migrate = get_option( 'shop_isle_section_order_migrate', 'no' );
if ( isset( $migrate ) && 'no' == $migrate ) {
	add_action( 'wp_footer', 'shop_isle_migrate' );
}

/**
 * Filter the read more button text ( from the read more tag in admin ) to match the theme's read more text
 */
function shop_isle_more_link( $more_link, $more_link_text ) {
	return str_replace( $more_link_text, esc_html__( 'Read more', 'shop-isle' ), $more_link );
}
add_filter( 'the_content_more_link', 'shop_isle_more_link', 10, 2 );




