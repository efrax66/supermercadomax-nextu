<?php
/**
 * The sidebar containing the main widget area.
 *
 * @package shop-isle
 */

if ( ! is_active_sidebar( 'shop-isle-sidebar-shop-archive' ) ) {
	return;
}
?>

<aside id="secondary" class="widget-area" role="complementary">
	<?php dynamic_sidebar( 'shop-isle-sidebar-shop-archive' ); ?>
</aside><!-- #secondary -->
