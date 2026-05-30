<?php
/**
 * Main plugin class.
 *
 * @package SampleAvailableForWooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles WooCommerce product settings, sample cart requests, and Elementor setup.
 */
final class SAW_Plugin {
	const META_SAMPLE_AVAILABLE = '_saw_sample_available';
	const META_PRODUCT_INFO_PDF = '_saw_product_info_pdf_id';
	const PLACEHOLDER_OPTION    = 'saw_placeholder_product_id';
	const RATE_LIMIT_PREFIX     = 'saw_sample_rl_';
	const SETTINGS_GROUP        = 'saw_sample_request_settings';
	const OPTION_PREFIX         = 'saw_';
	const PRODUCT_STYLE_PREFIX  = '_saw_button_style_';

	/**
	 * Singleton instance.
	 *
	 * @var SAW_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Tracks frontend buttons already printed for the current request.
	 *
	 * @var array
	 */
	private $rendered_buttons = array();

	/**
	 * Get singleton instance.
	 *
	 * @return SAW_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Activation callback.
	 *
	 * @return void
	 */
	public static function activate() {
		update_option( 'saw_version', SAW_VERSION, false );
	}

	/**
	 * Register hooks.
	 */
	private function __construct() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
		}

		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_button_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'init', array( $this, 'register_product_meta' ) );
		add_action( 'add_meta_boxes_product', array( $this, 'register_sample_meta_box' ) );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'render_admin_checkbox' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save_admin_checkbox' ) );
		add_action( 'save_post_product', array( $this, 'save_sample_meta_box' ), 10, 2 );
		add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'render_default_product_button' ), 20 );
		add_action( 'wp_loaded', array( $this, 'maybe_add_sample_to_cart' ), 20 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'force_sample_price' ), 20 );
		add_filter( 'woocommerce_cart_item_name', array( $this, 'filter_cart_item_name' ), 10, 3 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'add_cart_item_display_data' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_order_item_meta' ), 10, 4 );
		add_shortcode( 'saw_sample_button', array( $this, 'render_sample_button_shortcode' ) );
		add_shortcode( 'sample_request_button', array( $this, 'render_sample_button_shortcode' ) );
		add_shortcode( 'saw_product_info_button', array( $this, 'render_product_info_button_shortcode' ) );
		add_shortcode( 'saw_product_info_pdf_button', array( $this, 'render_product_info_button_shortcode' ) );
		add_shortcode( 'product_info_pdf_button', array( $this, 'render_product_info_button_shortcode' ) );

		add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widget' ) );
	}

	/**
	 * Declare compatibility with WooCommerce HPOS.
	 *
	 * @return void
	 */
	public static function declare_hpos_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', SAW_PLUGIN_FILE, true );
		}
	}

	/**
	 * Show an admin notice when WooCommerce is inactive.
	 *
	 * @return void
	 */
	public function woocommerce_missing_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p>';
		echo esc_html__( 'Sample Available for WooCommerce requires WooCommerce to be installed and active.', 'sample-available-for-woocommerce' );
		echo '</p></div>';
	}

	/**
	 * Enqueue frontend CSS.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		wp_enqueue_style(
			'saw-frontend',
			SAW_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			SAW_VERSION
		);

		wp_add_inline_style( 'saw-frontend', $this->get_button_custom_css() );

		wp_register_script( 'saw-frontend', false, array(), SAW_VERSION, true );
		wp_enqueue_script( 'saw-frontend' );
		wp_add_inline_script( 'saw-frontend', $this->get_frontend_popup_script() );
	}

	/**
	 * Enqueue admin assets for product PDF upload.
	 *
	 * @param string $hook_suffix Current admin hook.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || 'product' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script(
			'saw-admin',
			SAW_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			SAW_VERSION,
			true
		);
		wp_localize_script(
			'saw-admin',
			'sawAdmin',
			array(
				'mediaTitle'    => __( 'Select Product Info PDF', 'sample-available-for-woocommerce' ),
				'mediaButton'   => __( 'Use this PDF', 'sample-available-for-woocommerce' ),
				'invalidPdf'    => __( 'Please choose a PDF file.', 'sample-available-for-woocommerce' ),
				'noPdfSelected' => __( 'No PDF selected.', 'sample-available-for-woocommerce' ),
			)
		);
	}

	/**
	 * Add the plugin settings page under WooCommerce.
	 *
	 * @return void
	 */
	public function register_settings_page() {
		add_submenu_page(
			'woocommerce',
			__( 'Sample Request', 'sample-available-for-woocommerce' ),
			__( 'Sample Request', 'sample-available-for-woocommerce' ),
			'manage_woocommerce',
			'saw-sample-request',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register frontend button style settings.
	 *
	 * @return void
	 */
	public function register_button_settings() {
		foreach ( $this->get_button_setting_fields() as $key => $field ) {
			register_setting(
				self::SETTINGS_GROUP,
				self::OPTION_PREFIX . $key,
				array(
					'type'              => 'string',
					'sanitize_callback' => isset( $field['sanitize'] ) ? $field['sanitize'] : 'sanitize_text_field',
					'default'           => $field['default'],
				)
			);
		}
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$fields = $this->get_button_setting_fields();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Sample Request Button', 'sample-available-for-woocommerce' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( self::SETTINGS_GROUP ); ?>
				<table class="form-table" role="presentation">
					<tbody>
						<?php foreach ( $fields as $key => $field ) : ?>
							<?php
							$option_name = self::OPTION_PREFIX . $key;
							$value       = $this->get_button_setting( $key );
							$type        = isset( $field['type'] ) ? $field['type'] : 'text';
							?>
							<tr>
								<th scope="row">
									<label for="<?php echo esc_attr( $option_name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
								</th>
								<td>
									<?php if ( 'checkbox' === $type ) : ?>
										<label>
											<input type="hidden" name="<?php echo esc_attr( $option_name ); ?>" value="no">
											<input
												type="checkbox"
												id="<?php echo esc_attr( $option_name ); ?>"
												name="<?php echo esc_attr( $option_name ); ?>"
												value="yes"
												<?php checked( 'yes', $value ); ?>
											>
											<?php echo esc_html( isset( $field['checkbox_label'] ) ? $field['checkbox_label'] : __( 'Show automatically after Add to Cart', 'sample-available-for-woocommerce' ) ); ?>
										</label>
									<?php else : ?>
										<input
											type="<?php echo esc_attr( $type ); ?>"
											id="<?php echo esc_attr( $option_name ); ?>"
											name="<?php echo esc_attr( $option_name ); ?>"
											value="<?php echo esc_attr( $value ); ?>"
											class="regular-text"
											<?php echo isset( $field['placeholder'] ) ? 'placeholder="' . esc_attr( $field['placeholder'] ) . '"' : ''; ?>
										>
									<?php endif; ?>
									<?php if ( ! empty( $field['description'] ) ) : ?>
										<p class="description"><?php echo esc_html( $field['description'] ); ?></p>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Get configurable button fields.
	 *
	 * @return array
	 */
	private function get_button_setting_fields() {
		return array(
			'auto_display'            => array(
				'label'          => __( 'Automatic Request a Sample Button', 'sample-available-for-woocommerce' ),
				'type'           => 'checkbox',
				'default'        => 'yes',
				'sanitize'       => array( $this, 'sanitize_yes_no_meta' ),
				'checkbox_label' => __( 'Show Request a Sample automatically after Add to Cart', 'sample-available-for-woocommerce' ),
				'description'    => __( 'Disable this when you place the Request a Sample Elementor widget in your single product template.', 'sample-available-for-woocommerce' ),
			),
			'auto_display_download'   => array(
				'label'          => __( 'Automatic Product Info PDF Button', 'sample-available-for-woocommerce' ),
				'type'           => 'checkbox',
				'default'        => 'no',
				'sanitize'       => array( $this, 'sanitize_yes_no_meta' ),
				'checkbox_label' => __( 'Show Download Product Info automatically after Add to Cart', 'sample-available-for-woocommerce' ),
				'description'    => __( 'Enable this only if you want the default Product Info PDF button on the single product page. Elementor widget and shortcode buttons still work when this is disabled.', 'sample-available-for-woocommerce' ),
			),
			'button_text'             => array(
				'label'       => __( 'Button Text', 'sample-available-for-woocommerce' ),
				'default'     => __( 'Request a Sample', 'sample-available-for-woocommerce' ),
				'sanitize'    => 'sanitize_text_field',
				'description' => __( 'Text shown on the automatic single product button.', 'sample-available-for-woocommerce' ),
			),
			'font_family'             => array(
				'label'       => __( 'Font Family', 'sample-available-for-woocommerce' ),
				'default'     => '',
				'sanitize'    => array( $this, 'sanitize_css_font_family' ),
				'placeholder' => 'inherit',
				'description' => __( 'Example: Arial, Helvetica, sans-serif. Leave blank to inherit your theme font.', 'sample-available-for-woocommerce' ),
			),
			'font_size'               => array(
				'label'       => __( 'Font Size', 'sample-available-for-woocommerce' ),
				'default'     => '16px',
				'sanitize'    => array( $this, 'sanitize_css_size' ),
				'placeholder' => '16px',
			),
			'font_weight'             => array(
				'label'       => __( 'Font Weight', 'sample-available-for-woocommerce' ),
				'default'     => '600',
				'sanitize'    => array( $this, 'sanitize_font_weight' ),
				'placeholder' => '600',
			),
			'margin'                  => array(
				'label'       => __( 'Margin', 'sample-available-for-woocommerce' ),
				'default'     => '12px 0 0 0',
				'sanitize'    => array( $this, 'sanitize_css_box_value' ),
				'placeholder' => '12px 0 0 0',
			),
			'width'                   => array(
				'label'       => __( 'Width', 'sample-available-for-woocommerce' ),
				'default'     => 'auto',
				'sanitize'    => array( $this, 'sanitize_css_size_or_auto' ),
				'placeholder' => 'auto',
			),
			'min_height'              => array(
				'label'       => __( 'Minimum Height', 'sample-available-for-woocommerce' ),
				'default'     => '42px',
				'sanitize'    => array( $this, 'sanitize_css_size' ),
				'placeholder' => '42px',
			),
			'text_color'              => array(
				'label'       => __( 'Text Color', 'sample-available-for-woocommerce' ),
				'type'        => 'color',
				'default'     => '#ffffff',
				'sanitize'    => array( $this, 'sanitize_hex_color_with_fallback' ),
			),
			'background_color'        => array(
				'label'    => __( 'Background Color', 'sample-available-for-woocommerce' ),
				'type'     => 'color',
				'default'  => '#1f2937',
				'sanitize' => array( $this, 'sanitize_hex_color_with_fallback' ),
			),
			'border_color'            => array(
				'label'    => __( 'Border Color', 'sample-available-for-woocommerce' ),
				'type'     => 'color',
				'default'  => '#1f2937',
				'sanitize' => array( $this, 'sanitize_hex_color_with_fallback' ),
			),
			'border_width'            => array(
				'label'       => __( 'Border Width', 'sample-available-for-woocommerce' ),
				'default'     => '1px',
				'sanitize'    => array( $this, 'sanitize_css_size' ),
				'placeholder' => '1px',
			),
			'border_style'            => array(
				'label'       => __( 'Border Style', 'sample-available-for-woocommerce' ),
				'default'     => 'solid',
				'sanitize'    => array( $this, 'sanitize_border_style' ),
				'placeholder' => 'solid',
			),
			'border_radius'           => array(
				'label'       => __( 'Shape / Border Radius', 'sample-available-for-woocommerce' ),
				'default'     => '4px',
				'sanitize'    => array( $this, 'sanitize_css_size' ),
				'placeholder' => '4px',
				'description' => __( 'Use 0px for square, 4px for slight rounding, or 999px for pill shape.', 'sample-available-for-woocommerce' ),
			),
			'padding'                 => array(
				'label'       => __( 'Padding', 'sample-available-for-woocommerce' ),
				'default'     => '12px 18px',
				'sanitize'    => array( $this, 'sanitize_css_box_value' ),
				'placeholder' => '12px 18px',
			),
			'min_width'               => array(
				'label'       => __( 'Minimum Width', 'sample-available-for-woocommerce' ),
				'default'     => '220px',
				'sanitize'    => array( $this, 'sanitize_css_size' ),
				'placeholder' => '220px',
			),
			'hover_text_color'        => array(
				'label'    => __( 'Hover Text Color', 'sample-available-for-woocommerce' ),
				'type'     => 'color',
				'default'  => '#ffffff',
				'sanitize' => array( $this, 'sanitize_hex_color_with_fallback' ),
			),
			'hover_background_color'  => array(
				'label'    => __( 'Hover Background Color', 'sample-available-for-woocommerce' ),
				'type'     => 'color',
				'default'  => '#111827',
				'sanitize' => array( $this, 'sanitize_hex_color_with_fallback' ),
			),
			'hover_border_color'      => array(
				'label'    => __( 'Hover Border Color', 'sample-available-for-woocommerce' ),
				'type'     => 'color',
				'default'  => '#111827',
				'sanitize' => array( $this, 'sanitize_hex_color_with_fallback' ),
			),
		);
	}

	/**
	 * Get a saved button setting with fallback.
	 *
	 * @param string $key Setting key.
	 * @return string
	 */
	private function get_button_setting( $key ) {
		$fields = $this->get_button_setting_fields();

		if ( empty( $fields[ $key ] ) ) {
			return '';
		}

		$value = get_option( self::OPTION_PREFIX . $key, $fields[ $key ]['default'] );

		if ( '' === $value || false === $value ) {
			return $fields[ $key ]['default'];
		}

		return (string) $value;
	}

	/**
	 * Get fields that can be overridden per product.
	 *
	 * @return array
	 */
	private function get_product_style_fields() {
		$fields = $this->get_button_setting_fields();
		unset( $fields['auto_display'] );
		unset( $fields['auto_display_download'] );

		foreach ( $fields as $key => $field ) {
			if ( 'button_text' === $key ) {
				$fields[ $key ]['description'] = __( 'Leave blank to use the global button text.', 'sample-available-for-woocommerce' );
				continue;
			}

			if ( empty( $fields[ $key ]['description'] ) ) {
				$fields[ $key ]['description'] = __( 'Leave blank to use the global style value.', 'sample-available-for-woocommerce' );
			}
		}

		return $fields;
	}

	/**
	 * Save per-product style fields.
	 *
	 * @param int $post_id Product ID.
	 * @return void
	 */
	private function save_product_style_fields( $post_id ) {
		$fields = $this->get_product_style_fields();
		$values = isset( $_POST['saw_product_style'] ) && is_array( $_POST['saw_product_style'] ) ? wp_unslash( $_POST['saw_product_style'] ) : array();

		foreach ( $fields as $key => $field ) {
			$meta_key = self::PRODUCT_STYLE_PREFIX . $key;
			$value    = isset( $values[ $key ] ) ? $values[ $key ] : '';
			$value    = is_scalar( $value ) ? (string) $value : '';

			if ( '' === trim( $value ) ) {
				delete_post_meta( $post_id, $meta_key );
				continue;
			}

			$sanitize = isset( $field['sanitize'] ) ? $field['sanitize'] : 'sanitize_text_field';
			$value    = call_user_func( $sanitize, $value );

			if ( '' === $value ) {
				delete_post_meta( $post_id, $meta_key );
				continue;
			}

			update_post_meta( $post_id, $meta_key, $value );
		}
	}

	/**
	 * Save the per-product PDF attachment.
	 *
	 * @param int $post_id Product ID.
	 * @return void
	 */
	private function save_product_info_pdf( $post_id ) {
		$attachment_id = isset( $_POST[ self::META_PRODUCT_INFO_PDF ] ) ? absint( wp_unslash( $_POST[ self::META_PRODUCT_INFO_PDF ] ) ) : 0;

		if ( ! $attachment_id ) {
			delete_post_meta( $post_id, self::META_PRODUCT_INFO_PDF );
			return;
		}

		if ( ! $this->is_pdf_attachment( $attachment_id ) ) {
			delete_post_meta( $post_id, self::META_PRODUCT_INFO_PDF );
			return;
		}

		update_post_meta( $post_id, self::META_PRODUCT_INFO_PDF, $attachment_id );
	}

	/**
	 * Check whether an attachment is a PDF.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool
	 */
	private function is_pdf_attachment( $attachment_id ) {
		$attachment_id = absint( $attachment_id );

		if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
			return false;
		}

		return 'application/pdf' === get_post_mime_type( $attachment_id );
	}

	/**
	 * Get a validated product info PDF attachment ID.
	 *
	 * @param int $product_id Product ID.
	 * @return int
	 */
	public function get_product_info_pdf_id( $product_id ) {
		$attachment_id = absint( get_post_meta( absint( $product_id ), self::META_PRODUCT_INFO_PDF, true ) );

		return $this->is_pdf_attachment( $attachment_id ) ? $attachment_id : 0;
	}

	/**
	 * Get a validated product info PDF URL.
	 *
	 * @param int $product_id Product ID.
	 * @return string
	 */
	public function get_product_info_pdf_url( $product_id ) {
		$attachment_id = $this->get_product_info_pdf_id( $product_id );

		return $attachment_id ? wp_get_attachment_url( $attachment_id ) : '';
	}

	/**
	 * Get a per-product style setting with global fallback.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $key        Setting key.
	 * @return string
	 */
	private function get_product_button_setting( $product_id, $key ) {
		$product_id = absint( $product_id );
		$fields     = $this->get_product_style_fields();

		if ( ! $product_id || empty( $fields[ $key ] ) ) {
			return $this->get_button_setting( $key );
		}

		$value = get_post_meta( $product_id, self::PRODUCT_STYLE_PREFIX . $key, true );

		if ( '' === $value || false === $value ) {
			return $this->get_button_setting( $key );
		}

		return (string) $value;
	}

	/**
	 * Build inline CSS custom properties for per-product button styles.
	 *
	 * @param int $product_id Product ID.
	 * @return string
	 */
	private function get_product_button_style_vars( $product_id ) {
		$product_id = absint( $product_id );

		if ( ! $product_id ) {
			return '';
		}

		$map = array(
			'text_color'             => '--saw-button-text-color',
			'background_color'       => '--saw-button-bg-color',
			'border_color'           => '--saw-button-border-color',
			'border_width'           => '--saw-button-border-width',
			'border_style'           => '--saw-button-border-style',
			'border_radius'          => '--saw-button-border-radius',
			'padding'                => '--saw-button-padding',
			'min_width'              => '--saw-button-min-width',
			'width'                  => '--saw-button-width',
			'min_height'             => '--saw-button-min-height',
			'font_size'              => '--saw-button-font-size',
			'font_weight'            => '--saw-button-font-weight',
			'hover_text_color'       => '--saw-button-hover-text-color',
			'hover_background_color' => '--saw-button-hover-bg-color',
			'hover_border_color'     => '--saw-button-hover-border-color',
		);

		$style = array();

		foreach ( $map as $key => $property ) {
			$value = get_post_meta( $product_id, self::PRODUCT_STYLE_PREFIX . $key, true );

			if ( '' !== $value && false !== $value ) {
				$style[] = $property . ':' . $value;
			}
		}

		$font_family = get_post_meta( $product_id, self::PRODUCT_STYLE_PREFIX . 'font_family', true );

		if ( '' !== $font_family && false !== $font_family ) {
			$style[] = 'font-family:' . $font_family;
		}

		return $style ? implode( ';', $style ) . ';' : '';
	}

	/**
	 * Build inline CSS custom properties for per-product wrapper styles.
	 *
	 * @param int $product_id Product ID.
	 * @return string
	 */
	private function get_product_button_wrapper_style_vars( $product_id ) {
		$product_id = absint( $product_id );

		if ( ! $product_id ) {
			return '';
		}

		$margin = get_post_meta( $product_id, self::PRODUCT_STYLE_PREFIX . 'margin', true );

		return '' !== $margin && false !== $margin ? '--saw-button-form-margin:' . $margin . ';' : '';
	}

	/**
	 * Build frontend CSS from saved button settings.
	 *
	 * @return string
	 */
	private function get_button_custom_css() {
		$font_family = $this->get_button_setting( 'font_family' );
		$font_rule   = $font_family ? 'font-family:' . $font_family . ';' : '';

		return sprintf(
			'.saw-default-form{--saw-button-form-margin:%14$s;}.saw-default-button.saw-sample-request-button{--saw-button-text-color:%1$s;--saw-button-bg-color:%2$s;--saw-button-border-color:%3$s;--saw-button-border-width:%4$s;--saw-button-border-style:%15$s;--saw-button-border-radius:%5$s;--saw-button-padding:%6$s;--saw-button-min-width:%7$s;--saw-button-width:%16$s;--saw-button-min-height:%17$s;--saw-button-font-size:%8$s;--saw-button-font-weight:%9$s;--saw-button-hover-text-color:%11$s;--saw-button-hover-bg-color:%12$s;--saw-button-hover-border-color:%13$s;color:var(--saw-button-text-color);background-color:var(--saw-button-bg-color);border-color:var(--saw-button-border-color);border-style:var(--saw-button-border-style);border-width:var(--saw-button-border-width);border-radius:var(--saw-button-border-radius);padding:var(--saw-button-padding);min-width:var(--saw-button-min-width);width:var(--saw-button-width);min-height:var(--saw-button-min-height);font-size:var(--saw-button-font-size);font-weight:var(--saw-button-font-weight);%10$s}.saw-default-button.saw-sample-request-button:hover,.saw-default-button.saw-sample-request-button:focus{color:var(--saw-button-hover-text-color);background-color:var(--saw-button-hover-bg-color);border-color:var(--saw-button-hover-border-color);}',
			$this->get_button_setting( 'text_color' ),
			$this->get_button_setting( 'background_color' ),
			$this->get_button_setting( 'border_color' ),
			$this->get_button_setting( 'border_width' ),
			$this->get_button_setting( 'border_radius' ),
			$this->get_button_setting( 'padding' ),
			$this->get_button_setting( 'min_width' ),
			$this->get_button_setting( 'font_size' ),
			$this->get_button_setting( 'font_weight' ),
			$font_rule,
			$this->get_button_setting( 'hover_text_color' ),
			$this->get_button_setting( 'hover_background_color' ),
			$this->get_button_setting( 'hover_border_color' ),
			$this->get_button_setting( 'margin' ),
			$this->get_button_setting( 'border_style' ),
			$this->get_button_setting( 'width' ),
			$this->get_button_setting( 'min_height' )
		);
	}

	/**
	 * Sanitize hex colors and fall back to an empty value when invalid.
	 *
	 * @param string $value Color.
	 * @return string
	 */
	public function sanitize_hex_color_with_fallback( $value ) {
		$color = sanitize_hex_color( $value );

		return $color ? $color : '';
	}

	/**
	 * Sanitize a CSS size value.
	 *
	 * @param string $value CSS size.
	 * @return string
	 */
	public function sanitize_css_size( $value ) {
		$value = trim( sanitize_text_field( wp_unslash( $value ) ) );

		if ( '0' === $value || preg_match( '/^\d+(\.\d+)?(px|em|rem|%)$/', $value ) ) {
			return $value;
		}

		return '';
	}

	/**
	 * Sanitize a CSS size value or auto.
	 *
	 * @param string $value CSS size.
	 * @return string
	 */
	public function sanitize_css_size_or_auto( $value ) {
		$value = trim( sanitize_text_field( wp_unslash( $value ) ) );

		if ( 'auto' === strtolower( $value ) ) {
			return 'auto';
		}

		return $this->sanitize_css_size( $value );
	}

	/**
	 * Sanitize CSS shorthand spacing values.
	 *
	 * @param string $value CSS box value.
	 * @return string
	 */
	public function sanitize_css_box_value( $value ) {
		$value = trim( sanitize_text_field( wp_unslash( $value ) ) );
		$parts = preg_split( '/\s+/', $value );

		if ( empty( $parts ) || count( $parts ) > 4 ) {
			return '';
		}

		foreach ( $parts as $part ) {
			if ( '0' !== $part && ! preg_match( '/^\d+(\.\d+)?(px|em|rem|%)$/', $part ) ) {
				return '';
			}
		}

		return implode( ' ', $parts );
	}

	/**
	 * Sanitize a CSS font-family list.
	 *
	 * @param string $value Font family list.
	 * @return string
	 */
	public function sanitize_css_font_family( $value ) {
		$value = sanitize_text_field( wp_unslash( $value ) );

		return trim( preg_replace( '/[^a-zA-Z0-9\s,\-_"\'\.]/', '', $value ) );
	}

	/**
	 * Sanitize font weight.
	 *
	 * @param string $value Font weight.
	 * @return string
	 */
	public function sanitize_font_weight( $value ) {
		$value   = trim( sanitize_text_field( wp_unslash( $value ) ) );
		$allowed = array( 'normal', 'bold', 'bolder', 'lighter', '100', '200', '300', '400', '500', '600', '700', '800', '900' );

		return in_array( $value, $allowed, true ) ? $value : '';
	}

	/**
	 * Sanitize CSS border style.
	 *
	 * @param string $value Border style.
	 * @return string
	 */
	public function sanitize_border_style( $value ) {
		$value   = strtolower( trim( sanitize_text_field( wp_unslash( $value ) ) ) );
		$allowed = array( 'none', 'solid', 'dashed', 'dotted', 'double', 'groove', 'ridge', 'inset', 'outset' );

		return in_array( $value, $allowed, true ) ? $value : '';
	}

	/**
	 * Register product meta so the value is available to modern editors and REST saves.
	 *
	 * @return void
	 */
	public function register_product_meta() {
		register_post_meta(
			'product',
			self::META_SAMPLE_AVAILABLE,
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => 'no',
				'show_in_rest'      => true,
				'sanitize_callback' => array( $this, 'sanitize_yes_no_meta' ),
				'auth_callback'     => array( $this, 'can_edit_product_meta' ),
			)
		);

		register_post_meta(
			'product',
			self::META_PRODUCT_INFO_PDF,
			array(
				'type'              => 'integer',
				'single'            => true,
				'default'           => 0,
				'show_in_rest'      => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => array( $this, 'can_edit_product_meta' ),
			)
		);
	}

	/**
	 * Sanitize the sample availability meta value.
	 *
	 * @param mixed $value Meta value.
	 * @return string
	 */
	public function sanitize_yes_no_meta( $value ) {
		return 'yes' === $value ? 'yes' : 'no';
	}

	/**
	 * Check whether the current user can edit product meta.
	 *
	 * @param bool   $allowed Whether editing is allowed.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id  Product ID.
	 * @param int    $user_id  User ID.
	 * @param string $cap      Capability.
	 * @param array  $caps     Primitive capabilities.
	 * @return bool
	 */
	public function can_edit_product_meta( $allowed, $meta_key, $post_id, $user_id = 0, $cap = '', $caps = array() ) {
		$post_id = absint( $post_id );

		return $post_id ? current_user_can( 'edit_product', $post_id ) : current_user_can( 'edit_products' );
	}

	/**
	 * Add a visible fallback meta box on product edit screens.
	 *
	 * @return void
	 */
	public function register_sample_meta_box() {
		add_meta_box(
			'saw-sample-request-settings',
			__( 'Sample Request', 'sample-available-for-woocommerce' ),
			array( $this, 'render_sample_meta_box' ),
			'product',
			'side',
			'default'
		);

		add_meta_box(
			'saw-sample-button-style',
			__( 'Sample Button Style', 'sample-available-for-woocommerce' ),
			array( $this, 'render_sample_style_meta_box' ),
			'product',
			'normal',
			'default'
		);

		add_meta_box(
			'saw-product-info-pdf',
			__( 'Product Info PDF', 'sample-available-for-woocommerce' ),
			array( $this, 'render_product_info_pdf_meta_box' ),
			'product',
			'side',
			'default'
		);
	}

	/**
	 * Render the product sidebar checkbox.
	 *
	 * @param WP_Post $post Product post.
	 * @return void
	 */
	public function render_sample_meta_box( $post ) {
		$product_id = isset( $post->ID ) ? absint( $post->ID ) : 0;
		$value      = $product_id ? get_post_meta( $product_id, self::META_SAMPLE_AVAILABLE, true ) : 'no';

		wp_nonce_field( 'saw_save_sample_meta_box', 'saw_sample_meta_box_nonce' );
		?>
		<p>
			<label for="saw_sample_available_sidebar">
				<input
					type="checkbox"
					id="saw_sample_available_sidebar"
					name="<?php echo esc_attr( self::META_SAMPLE_AVAILABLE ); ?>"
					value="yes"
					<?php checked( 'yes', $value ); ?>
				>
				<?php echo esc_html__( 'Sample Available', 'sample-available-for-woocommerce' ); ?>
			</label>
		</p>
		<p class="description">
			<?php echo esc_html__( 'Show a Request a Sample button for this product.', 'sample-available-for-woocommerce' ); ?>
		</p>
		<?php
	}

	/**
	 * Render per-product button style controls.
	 *
	 * @param WP_Post $post Product post.
	 * @return void
	 */
	public function render_sample_style_meta_box( $post ) {
		$product_id = isset( $post->ID ) ? absint( $post->ID ) : 0;
		$fields     = $this->get_product_style_fields();

		wp_nonce_field( 'saw_save_sample_meta_box', 'saw_sample_meta_box_nonce' );
		?>
		<p class="description">
			<?php echo esc_html__( 'Optional per-product overrides. Leave any field blank to use WooCommerce > Sample Request global styling.', 'sample-available-for-woocommerce' ); ?>
		</p>
		<table class="form-table" role="presentation">
			<tbody>
				<?php foreach ( $fields as $key => $field ) : ?>
					<?php
					$meta_key = self::PRODUCT_STYLE_PREFIX . $key;
					$value    = $product_id ? get_post_meta( $product_id, $meta_key, true ) : '';
					$type     = isset( $field['type'] ) && 'color' !== $field['type'] ? $field['type'] : 'text';
					?>
					<tr>
						<th scope="row">
							<label for="saw_product_style_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
						</th>
						<td>
							<input
								type="<?php echo esc_attr( $type ); ?>"
								id="saw_product_style_<?php echo esc_attr( $key ); ?>"
								name="saw_product_style[<?php echo esc_attr( $key ); ?>]"
								value="<?php echo esc_attr( $value ); ?>"
								class="regular-text"
								<?php echo isset( $field['placeholder'] ) ? 'placeholder="' . esc_attr( $field['placeholder'] ) . '"' : ''; ?>
							>
							<?php if ( ! empty( $field['description'] ) ) : ?>
								<p class="description"><?php echo esc_html( $field['description'] ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render the product info PDF upload field.
	 *
	 * @param WP_Post $post Product post.
	 * @return void
	 */
	public function render_product_info_pdf_meta_box( $post ) {
		$product_id    = isset( $post->ID ) ? absint( $post->ID ) : 0;
		$attachment_id = $product_id ? $this->get_product_info_pdf_id( $product_id ) : 0;
		$file_name     = $attachment_id ? basename( get_attached_file( $attachment_id ) ) : __( 'No PDF selected.', 'sample-available-for-woocommerce' );

		wp_nonce_field( 'saw_save_sample_meta_box', 'saw_sample_meta_box_nonce' );
		?>
		<div class="saw-product-info-pdf-field">
			<input
				type="hidden"
				class="saw-product-info-pdf-id"
				name="<?php echo esc_attr( self::META_PRODUCT_INFO_PDF ); ?>"
				value="<?php echo esc_attr( $attachment_id ); ?>"
			>
			<p class="saw-product-info-pdf-name"><?php echo esc_html( $file_name ); ?></p>
			<p>
				<button type="button" class="button saw-upload-pdf-button">
					<?php echo esc_html__( 'Upload / Select PDF', 'sample-available-for-woocommerce' ); ?>
				</button>
				<button type="button" class="button saw-remove-pdf-button" <?php echo $attachment_id ? '' : 'style="display:none;"'; ?>>
					<?php echo esc_html__( 'Remove', 'sample-available-for-woocommerce' ); ?>
				</button>
			</p>
			<p class="description">
				<?php echo esc_html__( 'Only PDF media-library files are accepted. The download button appears below the Request a Sample button.', 'sample-available-for-woocommerce' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Add the Sample Available checkbox to product admin data.
	 *
	 * @return void
	 */
	public function render_admin_checkbox() {
		if ( ! function_exists( 'woocommerce_wp_checkbox' ) ) {
			return;
		}

		global $product_object;

		$value = $product_object instanceof WC_Product ? $product_object->get_meta( self::META_SAMPLE_AVAILABLE ) : 'no';

		woocommerce_wp_checkbox(
			array(
				'id'          => self::META_SAMPLE_AVAILABLE,
				'label'       => esc_html__( 'Sample Available', 'sample-available-for-woocommerce' ),
				'description' => esc_html__( 'Show a Request a Sample button for this product.', 'sample-available-for-woocommerce' ),
				'desc_tip'    => true,
				'value'       => 'yes' === $value ? 'yes' : 'no',
			)
		);
	}

	/**
	 * Save the Sample Available checkbox value.
	 *
	 * @param WC_Product $product Product object.
	 * @return void
	 */
	public function save_admin_checkbox( $product ) {
		if ( ! $product instanceof WC_Product || ! current_user_can( 'edit_product', $product->get_id() ) ) {
			return;
		}

		$nonce = isset( $_POST['woocommerce_meta_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'woocommerce_save_data' ) ) {
			return;
		}

		$value = isset( $_POST[ self::META_SAMPLE_AVAILABLE ] ) ? 'yes' : 'no';
		$product->update_meta_data( self::META_SAMPLE_AVAILABLE, $value );
	}

	/**
	 * Save the sidebar checkbox fallback.
	 *
	 * @param int     $post_id Product ID.
	 * @param WP_Post $post    Product post.
	 * @return void
	 */
	public function save_sample_meta_box( $post_id, $post ) {
		$post_id = absint( $post_id );

		if ( ! $post_id || ( $post instanceof WP_Post && 'product' !== $post->post_type ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_product', $post_id ) ) {
			return;
		}

		$meta_box_nonce = isset( $_POST['saw_sample_meta_box_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['saw_sample_meta_box_nonce'] ) ) : '';
		$wc_nonce       = isset( $_POST['woocommerce_meta_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $meta_box_nonce, 'saw_save_sample_meta_box' ) && ! wp_verify_nonce( $wc_nonce, 'woocommerce_save_data' ) ) {
			return;
		}

		$value = isset( $_POST[ self::META_SAMPLE_AVAILABLE ] ) ? 'yes' : 'no';
		update_post_meta( $post_id, self::META_SAMPLE_AVAILABLE, $value );

		$this->save_product_style_fields( $post_id );
		$this->save_product_info_pdf( $post_id );
	}

	/**
	 * Render the default button on checked single product pages.
	 *
	 * @return void
	 */
	public function render_default_product_button() {
		$show_sample_button   = 'yes' === $this->get_button_setting( 'auto_display' );
		$show_download_button = 'yes' === $this->get_button_setting( 'auto_display_download' );

		if ( ! $show_sample_button && ! $show_download_button ) {
			return;
		}

		$product_id = $this->get_current_product_id();

		if ( ! $product_id || ! empty( $this->rendered_buttons[ $product_id ] ) ) {
			return;
		}

		$sample_markup = $show_sample_button ? $this->get_button_markup(
			$product_id,
			array(
				'label'         => $this->get_product_button_setting( $product_id, 'button_text' ),
				'wrapper_class' => 'saw-default-form',
				'class'         => 'saw-default-button button alt',
				'style'         => $this->get_product_button_style_vars( $product_id ),
				'wrapper_style' => $this->get_product_button_wrapper_style_vars( $product_id ),
			)
		) : '';
		$download_markup = $show_download_button ? $this->get_product_info_button_markup(
			$product_id,
			array(
				'label'         => __( 'Download Product Info', 'sample-available-for-woocommerce' ),
				'wrapper_class' => 'saw-default-download-form',
				'class'         => 'saw-default-download-button button',
			)
		) : '';

		if ( ! $sample_markup && ! $download_markup ) {
			return;
		}

		$this->rendered_buttons[ $product_id ] = true;

		echo $sample_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $download_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render a fully customizable sample button shortcode for hook editors.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_sample_button_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'product_id'             => '',
				'text'                   => '',
				'class'                  => '',
				'align'                  => 'left',
				'margin'                 => '',
				'padding'                => '',
				'width'                  => '',
				'min_width'              => '',
				'min_height'             => '',
				'font_family'            => '',
				'font_size'              => '',
				'font_weight'            => '',
				'text_color'             => '',
				'background_color'       => '',
				'border_color'           => '',
				'border_width'           => '',
				'border_style'           => '',
				'border_radius'          => '',
				'hover_text_color'       => '',
				'hover_background_color' => '',
				'hover_border_color'     => '',
				'icon_gap'               => '',
			),
			(array) $atts,
			'saw_sample_button'
		);

		$product_id = ! empty( $atts['product_id'] ) ? absint( $atts['product_id'] ) : $this->get_current_product_id();

		if ( ! $product_id ) {
			return '';
		}

		$align = sanitize_key( $atts['align'] );

		if ( ! in_array( $align, array( 'left', 'center', 'right', 'stretch' ), true ) ) {
			$align = 'left';
		}

		$label = ! empty( $atts['text'] ) ? sanitize_text_field( $atts['text'] ) : $this->get_product_button_setting( $product_id, 'button_text' );

		return $this->get_button_markup(
			$product_id,
			array(
				'label'           => $label,
				'wrapper_class'   => 'saw-hook-form',
				'alignment_class' => 'saw-align-' . $align,
				'class'           => 'saw-hook-button button alt ' . sanitize_text_field( $atts['class'] ),
				'style'           => $this->get_shortcode_button_style_vars( $atts ),
				'wrapper_style'   => $this->get_shortcode_wrapper_style_vars( $atts ),
			)
		);
	}

	/**
	 * Render a fully customizable Product Info PDF button shortcode for hook editors.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_product_info_button_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'product_id'             => '',
				'text'                   => __( 'Download Product Info', 'sample-available-for-woocommerce' ),
				'class'                  => '',
				'align'                  => 'left',
				'visible'                => 'yes',
				'icon'                   => '',
				'icon_text'              => '',
				'icon_position'          => 'before',
				'margin'                 => '',
				'padding'                => '',
				'width'                  => '',
				'min_width'              => '',
				'min_height'             => '',
				'font_family'            => '',
				'font_size'              => '',
				'font_weight'            => '',
				'text_color'             => '',
				'background_color'       => '',
				'border_color'           => '',
				'border_width'           => '',
				'border_style'           => '',
				'border_radius'          => '',
				'hover_text_color'       => '',
				'hover_background_color' => '',
				'hover_border_color'     => '',
				'icon_gap'               => '',
			),
			(array) $atts,
			'saw_product_info_button'
		);

		$product_id = ! empty( $atts['product_id'] ) ? absint( $atts['product_id'] ) : $this->get_current_product_id();

		if ( ! $product_id ) {
			return '';
		}

		$align = sanitize_key( $atts['align'] );

		if ( ! in_array( $align, array( 'left', 'center', 'right', 'stretch' ), true ) ) {
			$align = 'left';
		}

		$visible       = 'no' === sanitize_key( $atts['visible'] ) ? 'no' : 'yes';
		$icon_position = 'after' === sanitize_key( $atts['icon_position'] ) ? 'after' : 'before';
		$label         = ! empty( $atts['text'] ) ? sanitize_text_field( $atts['text'] ) : __( 'Download Product Info', 'sample-available-for-woocommerce' );

		return $this->get_product_info_button_markup(
			$product_id,
			array(
				'label'           => $label,
				'wrapper_class'   => 'saw-hook-download-form',
				'alignment_class' => 'saw-align-' . $align,
				'class'           => 'saw-hook-download-button button ' . sanitize_text_field( $atts['class'] ),
				'style'           => $this->get_shortcode_button_style_vars( $atts ),
				'wrapper_style'   => $this->get_shortcode_wrapper_style_vars( $atts, '--saw-download-form-margin' ),
				'icon_html'       => $this->get_shortcode_icon_html( $atts ),
				'icon_position'   => $icon_position,
				'visible'         => $visible,
			)
		);
	}

	/**
	 * Build shortcode wrapper style vars.
	 *
	 * @param array  $atts     Shortcode attributes.
	 * @param string $property CSS custom property.
	 * @return string
	 */
	private function get_shortcode_wrapper_style_vars( $atts, $property = '--saw-button-form-margin' ) {
		$margin = ! empty( $atts['margin'] ) ? $this->sanitize_css_box_value( $atts['margin'] ) : '';

		return $margin ? sanitize_key( $property ) . ':' . $margin . ';' : '';
	}

	/**
	 * Build shortcode button style vars.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	private function get_shortcode_button_style_vars( $atts ) {
		$fields = array(
			'padding'                => array( '--saw-button-padding', array( $this, 'sanitize_css_box_value' ) ),
			'width'                  => array( '--saw-button-width', array( $this, 'sanitize_css_size_or_auto' ) ),
			'min_width'              => array( '--saw-button-min-width', array( $this, 'sanitize_css_size_or_auto' ) ),
			'min_height'             => array( '--saw-button-min-height', array( $this, 'sanitize_css_size' ) ),
			'font_size'              => array( '--saw-button-font-size', array( $this, 'sanitize_css_size' ) ),
			'font_weight'            => array( '--saw-button-font-weight', array( $this, 'sanitize_font_weight' ) ),
			'text_color'             => array( '--saw-button-text-color', array( $this, 'sanitize_hex_color_with_fallback' ) ),
			'background_color'       => array( '--saw-button-bg-color', array( $this, 'sanitize_hex_color_with_fallback' ) ),
			'border_color'           => array( '--saw-button-border-color', array( $this, 'sanitize_hex_color_with_fallback' ) ),
			'border_width'           => array( '--saw-button-border-width', array( $this, 'sanitize_css_size' ) ),
			'border_style'           => array( '--saw-button-border-style', array( $this, 'sanitize_border_style' ) ),
			'border_radius'          => array( '--saw-button-border-radius', array( $this, 'sanitize_css_size' ) ),
			'hover_text_color'       => array( '--saw-button-hover-text-color', array( $this, 'sanitize_hex_color_with_fallback' ) ),
			'hover_background_color' => array( '--saw-button-hover-bg-color', array( $this, 'sanitize_hex_color_with_fallback' ) ),
			'hover_border_color'     => array( '--saw-button-hover-border-color', array( $this, 'sanitize_hex_color_with_fallback' ) ),
			'icon_gap'               => array( '--saw-button-icon-gap', array( $this, 'sanitize_css_size' ) ),
		);

		$style = array();

		foreach ( $fields as $key => $field ) {
			if ( empty( $atts[ $key ] ) ) {
				continue;
			}

			$value = call_user_func( $field[1], $atts[ $key ] );

			if ( '' !== $value ) {
				$style[] = $field[0] . ':' . $value;
			}
		}

		if ( ! empty( $atts['font_family'] ) ) {
			$font_family = $this->sanitize_css_font_family( $atts['font_family'] );

			if ( $font_family ) {
				$style[] = '--saw-button-font-family:' . $font_family;
			}
		}

		return $style ? implode( ';', $style ) . ';' : '';
	}

	/**
	 * Build safe shortcode icon markup.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	private function get_shortcode_icon_html( $atts ) {
		$icon_classes = ! empty( $atts['icon'] ) ? $this->sanitize_class_list( $atts['icon'] ) : array();

		if ( $icon_classes ) {
			$icon_classes[] = 'saw-button-icon';

			return '<i class="' . esc_attr( implode( ' ', array_unique( $icon_classes ) ) ) . '" aria-hidden="true"></i>';
		}

		if ( empty( $atts['icon_text'] ) ) {
			return '';
		}

		return '<span class="saw-button-icon saw-button-icon-text" aria-hidden="true">' . esc_html( sanitize_text_field( $atts['icon_text'] ) ) . '</span>';
	}

	/**
	 * Get the current product ID.
	 *
	 * @return int
	 */
	public function get_current_product_id() {
		global $product;

		if ( $product instanceof WC_Product ) {
			return absint( $product->get_id() );
		}

		$queried_id = get_queried_object_id();

		return 'product' === get_post_type( $queried_id ) ? absint( $queried_id ) : 0;
	}

	/**
	 * Build the request button form markup.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $args       Markup arguments.
	 * @return string
	 */
	public function get_button_markup( $product_id, $args = array() ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return '';
		}

		$product_id = absint( $product_id );
		$product    = $product_id ? wc_get_product( $product_id ) : false;

		if ( ! $product || ! $this->is_sample_available( $product_id ) ) {
			return '';
		}

		$defaults = array(
			'label'           => __( 'Request a Sample', 'sample-available-for-woocommerce' ),
			'class'           => '',
			'wrapper_class'   => '',
			'alignment_class' => '',
			'icon_html'       => '',
			'icon_position'   => 'before',
			'style'           => '',
			'wrapper_style'   => '',
		);
		$args     = wp_parse_args( $args, $defaults );

		$form_classes = array_merge(
			array( 'saw-sample-request-form' ),
			$this->sanitize_class_list( $args['wrapper_class'] ),
			$this->sanitize_class_list( $args['alignment_class'] )
		);

		$button_classes = array_merge(
			array( 'saw-sample-request-button' ),
			$this->sanitize_class_list( $args['class'] )
		);

		$icon_html   = $this->sanitize_icon_html( $args['icon_html'] );
		$before_icon = 'before' === $args['icon_position'] ? $icon_html : '';
		$after_icon  = 'after' === $args['icon_position'] ? $icon_html : '';

		ob_start();
		?>
		<form method="post" class="<?php echo esc_attr( implode( ' ', $form_classes ) ); ?>" style="<?php echo esc_attr( $args['wrapper_style'] ); ?>">
			<?php wp_nonce_field( 'saw_add_sample_' . $product_id, 'saw_sample_nonce' ); ?>
			<input type="hidden" name="saw_product_id" value="<?php echo esc_attr( $product_id ); ?>">
			<input type="hidden" name="saw_sample_action" value="add_sample_request">
			<input type="text" name="saw_company_website" value="" class="saw-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">
			<button type="submit" name="saw_add_sample_to_cart" value="1" class="<?php echo esc_attr( implode( ' ', $button_classes ) ); ?>" style="<?php echo esc_attr( $args['style'] ); ?>">
				<span class="saw-button-content">
					<?php echo $before_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="saw-button-text"><?php echo esc_html( $args['label'] ); ?></span>
					<?php echo $after_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>
			</button>
		</form>
		<?php

		return trim( ob_get_clean() );
	}

	/**
	 * Build the product info PDF download button markup.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $args       Markup arguments.
	 * @return string
	 */
	public function get_product_info_button_markup( $product_id, $args = array() ) {
		$product_id = absint( $product_id );
		$pdf_url    = $product_id ? $this->get_product_info_pdf_url( $product_id ) : '';

		if ( ! $pdf_url ) {
			return '';
		}

		$defaults = array(
			'label'           => __( 'Download Product Info', 'sample-available-for-woocommerce' ),
			'class'           => '',
			'wrapper_class'   => '',
			'alignment_class' => '',
			'icon_html'       => '',
			'icon_position'   => 'before',
			'style'           => '',
			'wrapper_style'   => '',
			'visible'         => 'yes',
		);
		$args     = wp_parse_args( $args, $defaults );

		if ( 'yes' !== $args['visible'] ) {
			return '';
		}

		$wrapper_classes = array_merge(
			array( 'saw-product-info-download-form' ),
			$this->sanitize_class_list( $args['wrapper_class'] ),
			$this->sanitize_class_list( $args['alignment_class'] )
		);

		$button_classes = array_merge(
			array( 'saw-product-info-download-button' ),
			$this->sanitize_class_list( $args['class'] )
		);

		$icon_html   = $this->sanitize_icon_html( $args['icon_html'] );
		$before_icon = 'before' === $args['icon_position'] ? $icon_html : '';
		$after_icon  = 'after' === $args['icon_position'] ? $icon_html : '';

		ob_start();
		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" style="<?php echo esc_attr( $args['wrapper_style'] ); ?>">
			<a href="<?php echo esc_url( $pdf_url ); ?>" class="<?php echo esc_attr( implode( ' ', $button_classes ) ); ?>" style="<?php echo esc_attr( $args['style'] ); ?>" download>
				<span class="saw-button-content">
					<?php echo $before_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="saw-button-text"><?php echo esc_html( $args['label'] ); ?></span>
					<?php echo $after_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>
			</a>
		</div>
		<?php

		return trim( ob_get_clean() );
	}

	/**
	 * Sanitize Elementor icon HTML.
	 *
	 * @param string $icon_html Icon markup.
	 * @return string
	 */
	private function sanitize_icon_html( $icon_html ) {
		if ( empty( $icon_html ) ) {
			return '';
		}

		return wp_kses(
			$icon_html,
			array(
				'i'   => array(
					'class'       => true,
					'aria-hidden' => true,
				),
				'svg' => array(
					'class'       => true,
					'aria-hidden' => true,
					'focusable'   => true,
					'role'        => true,
					'viewBox'     => true,
					'viewbox'     => true,
					'xmlns'       => true,
					'width'       => true,
					'height'      => true,
				),
				'path' => array(
					'd'         => true,
					'fill'      => true,
					'fill-rule' => true,
					'clip-rule' => true,
				),
				'use'  => array(
					'href'       => true,
					'xlink:href' => true,
				),
			)
		);
	}

	/**
	 * Sanitize one or more CSS classes.
	 *
	 * @param string|array $classes Class names.
	 * @return array
	 */
	private function sanitize_class_list( $classes ) {
		if ( empty( $classes ) ) {
			return array();
		}

		if ( is_string( $classes ) ) {
			$classes = preg_split( '/\s+/', $classes );
		}

		$classes = array_map( 'sanitize_html_class', (array) $classes );

		return array_values( array_filter( $classes ) );
	}

	/**
	 * Check whether samples are enabled for a product.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function is_sample_available( $product_id ) {
		return 'yes' === get_post_meta( absint( $product_id ), self::META_SAMPLE_AVAILABLE, true );
	}

	/**
	 * Handle frontend sample request submissions.
	 *
	 * @return void
	 */
	public function maybe_add_sample_to_cart() {
		$submit = isset( $_POST['saw_add_sample_to_cart'] ) ? absint( wp_unslash( $_POST['saw_add_sample_to_cart'] ) ) : 0;
		$action = isset( $_POST['saw_sample_action'] ) ? sanitize_text_field( wp_unslash( $_POST['saw_sample_action'] ) ) : '';

		if ( ! $submit || 'add_sample_request' !== $action ) {
			return;
		}

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		$product_id = isset( $_POST['saw_product_id'] ) ? absint( wp_unslash( $_POST['saw_product_id'] ) ) : 0;
		$nonce      = isset( $_POST['saw_sample_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['saw_sample_nonce'] ) ) : '';
		$honeypot   = isset( $_POST['saw_company_website'] ) ? sanitize_text_field( wp_unslash( $_POST['saw_company_website'] ) ) : '';

		if ( $honeypot ) {
			wc_add_notice( __( 'We could not process this sample request. Please try again.', 'sample-available-for-woocommerce' ), 'error' );
			return;
		}

		if ( ! $product_id || ! wp_verify_nonce( $nonce, 'saw_add_sample_' . $product_id ) ) {
			wc_add_notice( __( 'Security check failed. Please refresh the page and try again.', 'sample-available-for-woocommerce' ), 'error' );
			return;
		}

		$source_product = wc_get_product( $product_id );

		if ( ! $source_product || ! $this->is_sample_available( $product_id ) ) {
			wc_add_notice( __( 'Samples are not available for this product.', 'sample-available-for-woocommerce' ), 'error' );
			return;
		}

		if ( ! $this->rate_limit_allows( $product_id ) ) {
			wc_add_notice( __( 'Too many sample requests were submitted. Please wait a few minutes and try again.', 'sample-available-for-woocommerce' ), 'error' );
			return;
		}

		$placeholder_product_id = $this->get_placeholder_product_id();

		if ( ! $placeholder_product_id ) {
			wc_add_notice( __( 'The sample request product could not be prepared. Please contact the store owner.', 'sample-available-for-woocommerce' ), 'error' );
			return;
		}

		$payload = $this->build_source_product_payload( $source_product );
		$added   = WC()->cart->add_to_cart(
			$placeholder_product_id,
			1,
			0,
			array(),
			array_merge(
				$payload,
				array(
					'saw_is_sample_request' => true,
					'saw_unique_key'        => wp_generate_uuid4(),
				)
			)
		);

		if ( $added ) {
			wp_safe_redirect( $this->get_sample_return_url( $source_product ) );
			exit;
		}

		wc_add_notice( __( 'The sample request could not be added to the cart. Please try again.', 'sample-available-for-woocommerce' ), 'error' );
	}

	/**
	 * Get the return URL after a sample request is added.
	 *
	 * @param WC_Product $product Source product.
	 * @return string
	 */
	private function get_sample_return_url( $product ) {
		$referer = wp_get_referer();
		$args    = array( 'saw_sample_added' => '1' );

		if ( $referer ) {
			return add_query_arg( $args, remove_query_arg( array( 'add-to-cart', 'quantity', 'saw_sample_added' ), $referer ) );
		}

		return add_query_arg( $args, get_permalink( $product->get_id() ) );
	}

	/**
	 * Build frontend popup script.
	 *
	 * @return string
	 */
	private function get_frontend_popup_script() {
		$sample_added = isset( $_GET['saw_sample_added'] ) ? absint( wp_unslash( $_GET['saw_sample_added'] ) ) : 0;

		if ( ! $sample_added ) {
			return '';
		}

		$message = __( 'Added to cart your request.', 'sample-available-for-woocommerce' );

		return 'document.addEventListener("DOMContentLoaded",function(){var toast=document.createElement("div");toast.className="saw-sample-popup";toast.setAttribute("role","status");toast.setAttribute("aria-live","polite");toast.textContent=' . wp_json_encode( $message ) . ';document.body.appendChild(toast);window.setTimeout(function(){toast.classList.add("is-visible");},30);window.setTimeout(function(){toast.classList.remove("is-visible");},3200);window.setTimeout(function(){if(toast&&toast.parentNode){toast.parentNode.removeChild(toast);}},3800);if(window.history&&window.history.replaceState){var url=new URL(window.location.href);url.searchParams.delete("saw_sample_added");window.history.replaceState({},document.title,url.toString());}});';
	}

	/**
	 * Build safe cart item data from the source product.
	 *
	 * @param WC_Product $product Source product.
	 * @return array
	 */
	private function build_source_product_payload( $product ) {
		$details = $product->get_short_description() ? $product->get_short_description() : $product->get_description();
		$details = wp_strip_all_tags( strip_shortcodes( $details ) );
		$details = html_entity_decode( $details, ENT_QUOTES, get_bloginfo( 'charset' ) );
		$details = wp_trim_words( $details, 80, '' );

		return array(
			'saw_source_product_id'      => absint( $product->get_id() ),
			'saw_source_product_name'    => sanitize_text_field( $product->get_name() ),
			'saw_source_product_sku'     => sanitize_text_field( $product->get_sku() ),
			'saw_source_product_details' => sanitize_textarea_field( $details ),
			'saw_source_product_url'     => esc_url_raw( get_permalink( $product->get_id() ) ),
		);
	}

	/**
	 * Basic request throttling by user ID or remote IP.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	private function rate_limit_allows( $product_id ) {
		$identity = is_user_logged_in() ? 'user_' . get_current_user_id() : $this->get_remote_address();
		$hash     = hash_hmac( 'sha256', $identity . '|' . absint( $product_id ), wp_salt( 'nonce' ) );
		$key      = self::RATE_LIMIT_PREFIX . substr( $hash, 0, 32 );
		$count    = absint( get_transient( $key ) );

		if ( $count >= 8 ) {
			return false;
		}

		set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );

		return true;
	}

	/**
	 * Get a sanitized remote address for rate limiting.
	 *
	 * @return string
	 */
	private function get_remote_address() {
		$address = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : 'unknown';

		return preg_replace( '/[^0-9a-fA-F:\.]/', '', (string) $address );
	}

	/**
	 * Get or create the hidden zero-cost placeholder product used for sample requests.
	 *
	 * @return int
	 */
	private function get_placeholder_product_id() {
		$stored_id = absint( get_option( self::PLACEHOLDER_OPTION ) );

		if ( $stored_id && 'product' === get_post_type( $stored_id ) ) {
			return $stored_id;
		}

		if ( ! class_exists( 'WC_Product_Simple' ) ) {
			return 0;
		}

		$product = new WC_Product_Simple();
		$product->set_name( __( 'Sample Request', 'sample-available-for-woocommerce' ) );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'hidden' );
		$product->set_virtual( true );
		$product->set_regular_price( '0' );
		$product->set_price( '0' );
		$product->set_sold_individually( false );
		$product->set_manage_stock( false );
		$product->set_stock_status( 'instock' );
		$product->set_description( __( 'Hidden product used to collect sample requests in the cart.', 'sample-available-for-woocommerce' ) );
		$product->set_short_description( __( 'Sample request placeholder.', 'sample-available-for-woocommerce' ) );
		$product->update_meta_data( '_saw_placeholder_product', 'yes' );

		$product_id = absint( $product->save() );

		if ( $product_id ) {
			update_option( self::PLACEHOLDER_OPTION, $product_id, false );
		}

		return $product_id;
	}

	/**
	 * Keep sample request cart items free.
	 *
	 * @param WC_Cart $cart Cart object.
	 * @return void
	 */
	public function force_sample_price( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item ) {
			if ( ! empty( $cart_item['saw_is_sample_request'] ) && isset( $cart_item['data'] ) && $cart_item['data'] instanceof WC_Product ) {
				$cart_item['data']->set_price( 0 );
			}
		}
	}

	/**
	 * Replace cart item title with the original product name.
	 *
	 * @param string $name          Cart item name.
	 * @param array  $cart_item     Cart item data.
	 * @param string $cart_item_key Cart item key.
	 * @return string
	 */
	public function filter_cart_item_name( $name, $cart_item, $cart_item_key ) {
		if ( empty( $cart_item['saw_is_sample_request'] ) || empty( $cart_item['saw_source_product_name'] ) ) {
			return $name;
		}

		$product_name = esc_html( $cart_item['saw_source_product_name'] );
		$product_url  = ! empty( $cart_item['saw_source_product_url'] ) ? esc_url( $cart_item['saw_source_product_url'] ) : '';
		$title        = $product_url ? '<a href="' . $product_url . '">' . $product_name . '</a>' : $product_name;

		return '<strong>' . esc_html__( 'Sample Request:', 'sample-available-for-woocommerce' ) . '</strong> ' . $title;
	}

	/**
	 * Add original product details to the cart line item display.
	 *
	 * @param array $item_data Cart item data for display.
	 * @param array $cart_item Cart item.
	 * @return array
	 */
	public function add_cart_item_display_data( $item_data, $cart_item ) {
		if ( empty( $cart_item['saw_is_sample_request'] ) ) {
			return $item_data;
		}

		if ( ! empty( $cart_item['saw_source_product_sku'] ) ) {
			$item_data[] = array(
				'key'   => __( 'SKU', 'sample-available-for-woocommerce' ),
				'value' => esc_html( $cart_item['saw_source_product_sku'] ),
			);
		}

		if ( ! empty( $cart_item['saw_source_product_details'] ) ) {
			$item_data[] = array(
				'key'   => __( 'Product Details', 'sample-available-for-woocommerce' ),
				'value' => esc_html( $cart_item['saw_source_product_details'] ),
			);
		}

		$item_data[] = array(
			'key'   => __( 'Request Type', 'sample-available-for-woocommerce' ),
			'value' => esc_html__( 'Sample', 'sample-available-for-woocommerce' ),
		);

		return $item_data;
	}

	/**
	 * Persist sample request details to order item meta.
	 *
	 * @param WC_Order_Item_Product $item          Order item.
	 * @param string                $cart_item_key Cart item key.
	 * @param array                 $values        Cart item values.
	 * @param WC_Order              $order         Order object.
	 * @return void
	 */
	public function add_order_item_meta( $item, $cart_item_key, $values, $order ) {
		if ( empty( $values['saw_is_sample_request'] ) ) {
			return;
		}

		$item->add_meta_data( __( 'Sample Request', 'sample-available-for-woocommerce' ), __( 'Yes', 'sample-available-for-woocommerce' ), true );

		if ( ! empty( $values['saw_source_product_id'] ) ) {
			$item->add_meta_data( __( 'Source Product ID', 'sample-available-for-woocommerce' ), absint( $values['saw_source_product_id'] ), true );
		}

		if ( ! empty( $values['saw_source_product_name'] ) ) {
			$item->add_meta_data( __( 'Source Product', 'sample-available-for-woocommerce' ), sanitize_text_field( $values['saw_source_product_name'] ), true );
		}

		if ( ! empty( $values['saw_source_product_sku'] ) ) {
			$item->add_meta_data( __( 'Source SKU', 'sample-available-for-woocommerce' ), sanitize_text_field( $values['saw_source_product_sku'] ), true );
		}

		if ( ! empty( $values['saw_source_product_details'] ) ) {
			$item->add_meta_data( __( 'Source Product Details', 'sample-available-for-woocommerce' ), sanitize_textarea_field( $values['saw_source_product_details'] ), true );
		}
	}

	/**
	 * Register the Elementor widget when Elementor is loaded.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 * @return void
	 */
	public function register_elementor_widget( $widgets_manager ) {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		require_once SAW_PLUGIN_DIR . 'includes/class-saw-elementor-widget.php';
		require_once SAW_PLUGIN_DIR . 'includes/class-saw-product-info-elementor-widget.php';

		$widgets_manager->register( new SAW_Elementor_Widget() );
		$widgets_manager->register( new SAW_Product_Info_Elementor_Widget() );
	}
}
