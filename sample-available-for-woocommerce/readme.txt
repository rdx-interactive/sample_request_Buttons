=== Sample Available for WooCommerce ===
Contributors: codex
Tags: woocommerce, elementor, sample request, product button
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.18
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a Sample Available checkbox, optional product PDF downloads, and customizable Elementor buttons for WooCommerce product pages.

== Description ==

Sample Available for WooCommerce lets store admins mark products as sample-requestable. When enabled, customers see a Request a Sample button on the single product page. Clicking the button adds a zero-cost sample request line item to the WooCommerce cart with the original product name, SKU, product link, and featured image.

Admins can also upload or select a Product Info PDF for each product. When a PDF is available, customers can download it through the Product Info PDF Button Elementor widget, the Product Info hook shortcode, or the optional automatic frontend button.

The plugin also includes Elementor widgets named Request a Sample, Product Info PDF Button, and Sample Product Grid. The single-product widgets can use the current product context or a selected product ID. The Sample Product Grid widget lists products where Sample Available is enabled and shows a Request a Sample button plus an optional View product button for each product.

The automatic single product buttons can be controlled from WooCommerce > Sample Request. Request a Sample is enabled by default, while the default Product Info PDF button is disabled by default so Elementor widget users can place it manually. Request button style can be managed globally from WooCommerce > Sample Request or per product from the Sample Button Style box on the product edit screen.

Hook editors can use these customizable shortcodes:

`[saw_sample_button text="Request a Sample" margin="12px 0 0 0" padding="12px 18px" width="auto" min_width="220px" min_height="42px" text_color="#ffffff" background_color="#1f2937" border_color="#1f2937" border_width="1px" border_style="solid" border_radius="4px" font_size="16px" font_weight="600" hover_text_color="#ffffff" hover_background_color="#111827" hover_border_color="#111827"]`

`[saw_product_info_button text="Download Product Info" icon="fa fa-download" icon_position="before" margin="12px 0 0 0" padding="12px 18px" width="auto" min_width="220px" min_height="42px" text_color="#1f2937" background_color="#ffffff" border_color="#1f2937" border_width="1px" border_style="solid" border_radius="4px" font_size="16px" font_weight="600" hover_text_color="#ffffff" hover_background_color="#1f2937" hover_border_color="#1f2937"]`

== Installation ==

1. Upload the plugin zip from Plugins > Add New > Upload Plugin.
2. Activate the plugin.
3. Open a WooCommerce product and check Sample Available in the Product data > General tab.
4. Upload or select a product PDF from the Product Info PDF box when needed.
5. Style the automatic button globally from WooCommerce > Sample Request or per product from Sample Button Style.
6. Use the default frontend buttons, add the Request a Sample and Product Info PDF Button Elementor widgets to a product template, or use the Sample Product Grid widget on a separate sample-order page.

== Security Notes ==

The plugin uses WordPress nonces for request submission, a hidden honeypot field, rate limiting, capability checks for admin saves, sanitized input, escaped output, PDF MIME validation for media-library attachments, and a hidden zero-cost WooCommerce placeholder product for cart entries. It does not include remote calls, dynamic code execution, or executable asset generation.

== Changelog ==

= 1.0.18 =
* Removed Product Details from the visible cart/checkout sample request item data.
* Replaced the hidden placeholder thumbnail with the original product featured image for sample request cart items.

= 1.0.17 =
* Fixed Sample Product Grid request buttons returning to the product page by adding a validated return URL to sample request forms.
* Improved Sample Product Grid typography/style selectors so Elementor text formatting applies to visible titles and button text.
* Added Elementor-compatible button text wrappers for Request Sample, View Product, and Product Info button markup.

= 1.0.16 =
* Removed price display from the Sample Product Grid widget.
* Added a View product button that links each grid card to its product page.
* Added Elementor controls for grid button layout, order, alignment, spacing, and separate Request Sample/View Product button styles.

= 1.0.15 =
* Added a Sample Product Grid Elementor widget.
* The grid lists only products with Sample Available enabled and shows a Request a Sample button for each product.
* Added grid, card, text, query, and button style controls.

= 1.0.14 =
* Repaired sample add-to-cart handling when the hidden placeholder product is stale, out of stock, or blocked as non-purchasable.
* Added a guarded WooCommerce cart validation override only for verified plugin sample request submissions.

= 1.0.13 =
* Stopped the Request a Sample Elementor widget from also rendering the Download Product Info button.
* Kept Product Info PDF output limited to the dedicated Product Info PDF Button widget, Product Info shortcode, or optional automatic setting.

= 1.0.12 =
* Added a separate Automatic Product Info PDF Button setting under WooCommerce > Sample Request.
* Disabled the default frontend Download Product Info button by default while keeping Elementor widget and shortcode output available.

= 1.0.11 =
* Added a separate Product Info PDF Button Elementor widget.
* Added full Elementor editing controls for the standalone Product Info PDF button.

= 1.0.10 =
* Added a Product Info PDF hook shortcode: [saw_product_info_button].
* Added shortcode controls for Product Info PDF button text, icon, icon position, visibility, margin, padding, width, colors, border, radius, font, hover colors, and alignment.

= 1.0.9 =
* Added a per-product Product Info PDF upload/select field.
* Added a Download Product Info frontend button when a valid product PDF is available.
* Added Elementor controls for the download button visibility, text, icon, position, spacing, border, radius, colors, typography, shadow, and hover styles.

= 1.0.8 =
* Replaced the WooCommerce information notice with a single frontend popup after a sample request is added.

= 1.0.7 =
* Kept customers on the same product page after adding a sample request.
* Changed the success response to an informational WooCommerce notice.

= 1.0.6 =
* Added a fully customizable hook-editor shortcode: [saw_sample_button].
* Added shortcode controls for margin, padding, width, height, colors, border, radius, font, text, hover colors, and alignment.
* Added width, minimum width, and minimum height controls to the Elementor widget Style tab.

= 1.0.5 =
* Added Sample Button Style controls directly on the WooCommerce product edit screen.
* Added per-product style overrides for button text, font, size, colors, border, radius, padding, width, and hover colors.

= 1.0.4 =
* Removed the fallback product-summary button placement to prevent duplicate frontend buttons.
* Added an Automatic Button setting so Elementor template users can disable the automatic button.

= 1.0.3 =
* Added WooCommerce > Sample Request settings for automatic button styling.
* Added controls for button text, font, font size, color, border, radius, padding, width, and hover colors.

= 1.0.2 =
* Moved the default frontend button to display directly after the WooCommerce add-to-cart form.
* Added a guarded fallback placement for classic product templates without duplicate buttons.

= 1.0.1 =
* Added a visible product sidebar checkbox fallback for Add New Product screens.
* Registered the product meta for modern editor and REST compatibility.

= 1.0.0 =
* Initial release.
