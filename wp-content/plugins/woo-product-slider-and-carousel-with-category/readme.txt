=== Woo Product Slider and Carousel with category ===
Contributors: wponlinesupport, anoopranawat
Tags: best selling products, best selling products slider, slick slider, best selling products by category, shortcode, template code, featured product, featured product slider, Featured product by category, autoplay slider, best product slider, best product slider for woo shop, carousel, clean woo product slider, multiple product slider, product carousel,  product content slider, product contents carousel, product slider, product slider carousel for woo, products slider,  responsive product slider, responsive product carousel, slider, smooth product slider woo product slider,  advance slider, woo best selling products, woo category slider, latest products, most selling products, product carousel slider, recent product carousel, recent product slider
Requires at least: 3.1
Tested up to: 4.9.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Woocommerce Product, Best Selling Product, Featured Product Slider/Carousel with category.


== Description ==
WooCommerce Product Slider/Carousel is an best product slider to slide your WooCommerce Product, Best Selling Product and Featured Product. You can easily display this product slider in your theme using shortcode.

You can sort product by category by adding category ID in the shortcode as a shortcode parameter.

Plugin add a sub tab under "Products --> Product Slider – How It Works" for more details.

WooCommerce product slider / carousel allows you to showcase your products in a nice sliding manner.

This plugin using the original loop form WooCommerce thats means it will display your product design from your theme plus style.

View [DEMO](http://wponlinesupport.com/wp-plugin/woo-product-slider-carousel-category/) | [PRO DEMO and Features](http://wponlinesupport.com/wp-plugin/woo-product-slider-carousel-category/) for additional information.

Checkout our new plugin - [PowerPack - Need of Every Website](https://wordpress.org/plugins/powerpack-lite/)

= This plugin contain 3 shortcode: =
1) Display WooCommerce **product in slider / carousel** view

<code>[products_slider] OR [products_slider cats="CATEGORY-ID"]</code>

2) Display WooCommerce **Best Selling Product in slider / carousel view**

<code>[bestselling_products_slider] OR [bestselling_products_slider cats="CATEGORY-ID"]</code>

3) Display WooCommerce **Featured Product in slider / carousel view**

<code>[featured_products_slider] OR [featured_products_slider cats="CATEGORY-ID"]</code>

= Stunning Features: =

* Featured products slider
* Displaying Latest/Recent Products Slider
* Best Selling Product slider
* Sort by category 
* 100% Mobile & Tablet Responsive
* Awesome Touch-Swipe Enabled
* Added a custom design
* Translation Ready
* Work in any WordPress Theme
* Created with Slick Slider
* Lightweight, Fast & Powerful
* Set Number of Columns you want to show
* Slider AutoPlay on/off
* Navigation show/hide options
* Pagination show/hide options
* Unlimited slider anywhere
* And more features coming soon!


= You can use Following parameters with shortcode =
* **design:** 
design="design-1" (Added a design parameter for custom designing. By default it will take design from wooCommerce OR from your theme.  )
* **Display Product by category:** 
cats="category-ID" 
* **limit:**
limit="5" ( ie Display 5 product at time. By defoult value is -1 ie all )
* **Display number of products at time:**
slide_to_show="2" (Display no of products in a slider )
* **Number of products slides at a time:**
slide_to_scroll="2" (Controls number of products rotate at a time)
* **Pagination and arrows:**
dots="false" arrows="false" (Hide/Show pagination and arrows. By defoult value is "true". Values are true OR false)
* **Autoplay and Autoplay Speed:**
autoplay="true" autoplay_speed="1000"
* **Slide Speed:**
speed="3000" (Control the speed of the slider)
* **slider_cls:**
slider_cls="products" (This parameter target the wooCommerce default class for product looping. If your slider is not working please check your theme product looping class and add that class in this parameter)


= PRO Features : =
> <strong>Premium Version</strong><br>
>
> * 3 shortcodes with various parameters.
> <code>[products_slider] OR [products_slider cats="CATEGORY-ID"]</code>
> <code>[bestselling_products_slider] OR [bestselling_products_slider cats="CATEGORY-ID"]</code>
> <code>[featured_products_slider] OR [featured_products_slider cats="CATEGORY-ID"]</code>
> * 3 Widgets.
> * 15 Designs 
> View [PRO DEMO and Features](http://wponlinesupport.com/wp-plugin/woo-product-slider-carousel-category/) for additional information.
>

= How to install : =
[youtube https://www.youtube.com/watch?v=6R5JvYBk0jU] 

== Installation ==
1. Upload the 'woocommerce-product-slider-and-carousel-with-category' folder to the '/wp-content/plugins/' directory.
2. Activate the "woocommerce-product-slider-and-carousel-with-category" list plugin through the 'Plugins' menu in WordPress.

= How to install : =
[youtube https://www.youtube.com/watch?v=6R5JvYBk0jU] 


= This plugin contain there shortcode: =
1) Display WooCommerce product in slider / carousel view
<code>[products_slider] OR [products_slider cats="CATEGORY-ID"]</code>

2) Display WooCommerce Best Selling Product in slider / carousel view
<code>[bestselling_products_slider] OR [bestselling_products_slider cats="CATEGORY-ID"]</code>

3) Display WooCommerce Featured Product in slider / carousel view
<code>[featured_products_slider] OR [featured_products_slider cats="CATEGORY-ID"]</code>

== Frequently Asked Questions ==

= My slider is not working =

We have targeted <code><ul class="products"></code> as you can check wooCommerce default class for product looping BUT in your theme i think you have changed the class name from <code><ul class="products"> to <ul class="YOUR CLASS NAME"></code>

File under templates-->loop--> loop-start.php

There are simple solution with shortcode parameter

* **slider_cls:**
slider_cls="products" (This parameter target the wooCommerce default class for product looping. If your slider is not working please check your theme product looping class and add that class in this parameter)

== Screenshots ==

1. Display WooCommerce product in slider / carousel view
2. Display WooCommerce Best Selling Product in slider / carousel view
3. Display WooCommerce Featured Product in slider / carousel view
4. Shortcodes

== Changelog ==

= 1.2.1 (9-3-2018) =
* [*] Tested with WooCommerce version 3.3

= 1.2 (30-10-2017) =
* [*] Fix featured product by category id issue.

= 1.1.6 (10-10-2017) =
* [*] Updated Slick.min.js file to latest version
* [*] Fix issue with StoreFront theme and now plugin working well.
* [*] Fixed some css issues

= 1.1.5 =
* [*] Updated slider JS to latest version.
* [*] Fix Json error when plugin is used with SiteOrigin page builder widget.

= 1.1.4 =
* Fixed RTL issue.

= 1.1.3 =
* Fixed featured product slider issue.

= 1.1.2 =
* [+] Tested with WooCommerce 3.0.3 and older version
* [+] Compatible with WooCommerce 3.0.3 and older version

= 1.1.1 =
* [+] Added "How it works" tab.
* [+] Added Product Slider – How It Works tab under products

= 1.1 =
* Added 2 new shortcode paremeres design and slider_cls.
* Added a custom design ie design="design-1"
* Fixed some css bug

= 1.0 =
* Initial release.


== Upgrade Notice ==

= 1.2 (30-10-2017) =
* [*] Fix featured product by category id issue.

= 1.1.6 (10-10-2017) =
* [*] Updated Slick.min.js file to latest version
* [*] Fix issue with StoreFront theme and now plugin working well.
* [*] Fixed some css issues

= 1.1.5 =
* [*] Updated slider JS to latest version.
* [*] Fix Json error when plugin is used with SiteOrigin page builder widget.

= 1.1.4 =
* Fixed RTL issue.

= 1.1.3 =
* Fixed featured product slider issue.

= 1.1.2 =
* [+] Tested with WooCommerce 3.0.3 and older version
* [+] Compatible with WooCommerce 3.0.3 and older version

= 1.1.1 =
* [+] Added "How it works" tab.
* [+] Added Product Slider – How It Works tab under products

= 1.1 =
* Added 2 new shortcode paremeres design and slider_cls.
* Added a custom design ie design="design-1"
* Fixed some css bug

= 1.0 =
* Initial release.
