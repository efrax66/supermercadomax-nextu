<?php
/**
 * Customizer functionality for the Footer.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

/**
 * Hook controls for Footer to Customizer.
 */
function shop_isle_footer_customize_register( $wp_customize ) {

	$selective_refresh = isset( $wp_customize->selective_refresh ) ? 'postMessage' : 'refresh';

	/*  Footer */

	$wp_customize->add_section(
		'shop_isle_footer_section', array(
			'title'    => __( 'Footer', 'shop-isle' ),
			'priority' => 50,
		)
	);

	/* Copyright */
	$wp_customize->add_setting(
		'shop_isle_copyright', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'transport'         => $selective_refresh,
		)
	);

	$wp_customize->add_control(
		'shop_isle_copyright', array(
			'label'    => __( 'Copyright', 'shop-isle' ),
			'section'  => 'shop_isle_footer_section',
			'priority' => 1,
		)
	);

	/* Hide site info */
	$wp_customize->add_setting(
		'shop_isle_site_info_hide', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'shop_isle_sanitize_text',
		)
	);

	$wp_customize->add_control(
		'shop_isle_site_info_hide',
		array(
			'type'     => 'checkbox',
			'label'    => __( 'Hide site info?', 'shop-isle' ),
			'section'  => 'shop_isle_footer_section',
			'priority' => 2,
		)
	);

	/* socials */
	$wp_customize->add_setting(
		'shop_isle_socials', array(
			'transport'         => $selective_refresh,
			'sanitize_callback' => 'shop_isle_sanitize_repeater',
		)
	);

	$wp_customize->add_control(
		new Shop_Isle_Repeater_Controler(
			$wp_customize, 'shop_isle_socials', array(
				'label'                         => __( 'Add new social', 'shop-isle' ),
				'section'                       => 'shop_isle_footer_section',
				'active_callback'               => 'is_front_page',
				'priority'                      => 3,
				'shop_isle_image_control'       => false,
				'shop_isle_link_control'        => true,
				'shop_isle_text_control'        => false,
				'shop_isle_subtext_control'     => false,
				'shop_isle_label_control'       => false,
				'shop_isle_icon_control'        => true,
				'shop_isle_description_control' => false,
				'shop_isle_box_label'           => __( 'Social', 'shop-isle' ),
				'shop_isle_box_add_label'       => __( 'Add new social', 'shop-isle' ),
			)
		)
	);
}

add_action( 'customize_register', 'shop_isle_footer_customize_register' );


/**
 * Add selective refresh for footer copyright and socials
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function shop_isle_footer_copyright_register_partials( $wp_customize ) {
	// Abort if selective refresh is not available.
	if ( ! isset( $wp_customize->selective_refresh ) ) {
		return;
	}

	$wp_customize->selective_refresh->add_partial(
		'shop_isle_copyright', array(
			'selector'            => '.copyright',
			'render_callback'     => 'shop_isle_footer_copyright_callback',
			'container_inclusive' => false,
		)
	);

	$wp_customize->selective_refresh->add_partial(
		'shop_isle_socials', array(
			'selector'            => '.footer-social-links',
			'render_callback'     => 'shop_isle_footer_display_socials',
			'container_inclusive' => true,
		)
	);
}

add_action( 'customize_register', 'shop_isle_footer_copyright_register_partials' );

/**
 * Callback function for footer copyright
 *
 * @return string - footer copyright value
 */
function shop_isle_footer_copyright_callback() {
	return get_theme_mod( 'shop_isle_copyright' );
}

/**
 * Render function for footer social icons
 */
function shop_isle_footer_display_socials() {

	$shop_isle_socials = get_theme_mod( 'shop_isle_socials' );

	if ( ! empty( $shop_isle_socials ) ) :

		$shop_isle_socials_decoded = json_decode( $shop_isle_socials );

		if ( ! empty( $shop_isle_socials_decoded ) ) :

			echo '<div class="footer-social-links">';

			foreach ( $shop_isle_socials_decoded as $shop_isle_social ) :

				$icon_value = ! empty( $shop_isle_social->icon_value ) ? apply_filters( 'shop_isle_translate_single_string', $shop_isle_social->icon_value, 'Footer socials' ) : '';
				$link       = ! empty( $shop_isle_social->link ) ? apply_filters( 'shop_isle_translate_single_string', $shop_isle_social->link, 'Footer socials' ) : '';

				if ( ! empty( $icon_value ) && ! empty( $link ) ) {
					echo '<a href="' . esc_url( $link ) . '" target="_blank"><span class="' . esc_attr( $icon_value ) . '"></span></a>';
				}
			endforeach;

			echo '</div>';

		else :

			if ( is_customize_preview() ) {
				echo '<div class="footer-social-links"></div>';
			}

		endif;

	endif;
}
