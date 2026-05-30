<?php
/**
 * Elementor widget for the Product Info PDF download button.
 *
 * @package SampleAvailableForWooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Info PDF Elementor widget.
 */
class SAW_Product_Info_Elementor_Widget extends \Elementor\Widget_Base {
	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'saw_product_info_pdf_button';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Product Info PDF Button', 'sample-available-for-woocommerce' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-button';
	}

	/**
	 * Widget categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'woocommerce-elements', 'general' );
	}

	/**
	 * Search keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'woocommerce', 'pdf', 'download', 'product info', 'button' );
	}

	/**
	 * Register Elementor controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => __( 'Content', 'sample-available-for-woocommerce' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'product_source',
			array(
				'label'   => __( 'Product Source', 'sample-available-for-woocommerce' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'current',
				'options' => array(
					'current'  => __( 'Current Product', 'sample-available-for-woocommerce' ),
					'selected' => __( 'Selected Product ID', 'sample-available-for-woocommerce' ),
				),
			)
		);

		$this->add_control(
			'product_id',
			array(
				'label'     => __( 'Product ID', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 1,
				'step'      => 1,
				'condition' => array(
					'product_source' => 'selected',
				),
			)
		);

		$this->add_control(
			'show_button',
			array(
				'label'        => __( 'Show Button', 'sample-available-for-woocommerce' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'sample-available-for-woocommerce' ),
				'label_off'    => __( 'Hide', 'sample-available-for-woocommerce' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'       => __( 'Button Text', 'sample-available-for-woocommerce' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Download Product Info', 'sample-available-for-woocommerce' ),
				'placeholder' => __( 'Download Product Info', 'sample-available-for-woocommerce' ),
				'condition'   => array(
					'show_button' => 'yes',
				),
			)
		);

		$this->add_control(
			'selected_icon',
			array(
				'label'            => __( 'Icon', 'sample-available-for-woocommerce' ),
				'type'             => \Elementor\Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'condition'        => array(
					'show_button' => 'yes',
				),
			)
		);

		$this->add_control(
			'icon_position',
			array(
				'label'     => __( 'Icon Position', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'before',
				'options'   => array(
					'before' => __( 'Before', 'sample-available-for-woocommerce' ),
					'after'  => __( 'After', 'sample-available-for-woocommerce' ),
				),
				'condition' => array(
					'show_button'           => 'yes',
					'selected_icon[value]!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'     => __( 'Alignment', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => array(
					'left'    => array(
						'title' => __( 'Left', 'sample-available-for-woocommerce' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'  => array(
						'title' => __( 'Center', 'sample-available-for-woocommerce' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'   => array(
						'title' => __( 'Right', 'sample-available-for-woocommerce' ),
						'icon'  => 'eicon-text-align-right',
					),
					'stretch' => array(
						'title' => __( 'Stretch', 'sample-available-for-woocommerce' ),
						'icon'  => 'eicon-h-align-stretch',
					),
				),
				'default'   => 'left',
				'condition' => array(
					'show_button' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label'     => __( 'Button', 'sample-available-for-woocommerce' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_button' => 'yes',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'typography',
					'selector' => '{{WRAPPER}} .saw-product-info-elementor-button, {{WRAPPER}} .saw-product-info-elementor-button .saw-button-text, {{WRAPPER}} .saw-product-info-elementor-button .elementor-button-text',
				)
			);

		$this->add_responsive_control(
			'icon_spacing',
			array(
				'label'      => __( 'Icon Spacing', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .saw-product-info-elementor-button .saw-button-content' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => __( 'Padding', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .saw-product-info-elementor-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_margin',
			array(
				'label'      => __( 'Margin', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .saw-product-info-elementor-form' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_width',
			array(
				'label'      => __( 'Width', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 800,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .saw-product-info-elementor-button' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_min_width',
			array(
				'label'      => __( 'Minimum Width', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 800,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .saw-product-info-elementor-button' => 'min-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_min_height',
			array(
				'label'      => __( 'Minimum Height', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .saw-product-info-elementor-button' => 'min-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'button_style_tabs' );

		$this->start_controls_tab(
			'button_normal_tab',
			array(
				'label' => __( 'Normal', 'sample-available-for-woocommerce' ),
			)
		);

		$this->add_control(
			'button_text_color',
			array(
				'label'     => __( 'Text Color', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .saw-product-info-elementor-button' => 'color: {{VALUE}};',
						'{{WRAPPER}} .saw-product-info-elementor-button .saw-button-text' => 'color: {{VALUE}};',
						'{{WRAPPER}} .saw-product-info-elementor-button .elementor-button-text' => 'color: {{VALUE}};',
						'{{WRAPPER}} .saw-product-info-elementor-button svg' => 'fill: {{VALUE}};',
					),
				)
		);

		$this->add_control(
			'button_background_color',
			array(
				'label'     => __( 'Background Color', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .saw-product-info-elementor-button' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'button_border',
				'selector' => '{{WRAPPER}} .saw-product-info-elementor-button',
			)
		);

		$this->add_responsive_control(
			'button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .saw-product-info-elementor-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .saw-product-info-elementor-button',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'button_hover_tab',
			array(
				'label' => __( 'Hover', 'sample-available-for-woocommerce' ),
			)
		);

		$this->add_control(
			'button_hover_text_color',
			array(
				'label'     => __( 'Text Color', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .saw-product-info-elementor-button:hover, {{WRAPPER}} .saw-product-info-elementor-button:focus' => 'color: {{VALUE}};',
						'{{WRAPPER}} .saw-product-info-elementor-button:hover .saw-button-text, {{WRAPPER}} .saw-product-info-elementor-button:focus .saw-button-text' => 'color: {{VALUE}};',
						'{{WRAPPER}} .saw-product-info-elementor-button:hover .elementor-button-text, {{WRAPPER}} .saw-product-info-elementor-button:focus .elementor-button-text' => 'color: {{VALUE}};',
						'{{WRAPPER}} .saw-product-info-elementor-button:hover svg, {{WRAPPER}} .saw-product-info-elementor-button:focus svg' => 'fill: {{VALUE}};',
					),
				)
		);

		$this->add_control(
			'button_hover_background_color',
			array(
				'label'     => __( 'Background Color', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .saw-product-info-elementor-button:hover, {{WRAPPER}} .saw-product-info-elementor-button:focus' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_hover_border_color',
			array(
				'label'     => __( 'Border Color', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'condition' => array(
					'button_border_border!' => '',
				),
				'selectors' => array(
					'{{WRAPPER}} .saw-product-info-elementor-button:hover, {{WRAPPER}} .saw-product-info-elementor-button:focus' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_hover_transition',
			array(
				'label'     => __( 'Transition Duration', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 3,
						'step' => 0.1,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .saw-product-info-elementor-button' => 'transition-duration: {{SIZE}}s;',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();
	}

	/**
	 * Render widget output.
	 *
	 * @return void
	 */
	protected function render() {
		$settings   = $this->get_settings_for_display();
		$product_id = $this->get_widget_product_id( $settings );

		if ( ! $product_id ) {
			$this->render_editor_notice( __( 'Choose a product context or enter a product ID.', 'sample-available-for-woocommerce' ) );
			return;
		}

		if ( isset( $settings['show_button'] ) && 'yes' !== $settings['show_button'] ) {
			return;
		}

		$align = ! empty( $settings['align'] ) ? $settings['align'] : 'left';
		$markup = SAW_Plugin::instance()->get_product_info_button_markup(
			$product_id,
			array(
				'label'           => ! empty( $settings['button_text'] ) ? $settings['button_text'] : __( 'Download Product Info', 'sample-available-for-woocommerce' ),
				'class'           => 'saw-product-info-elementor-button elementor-button',
				'wrapper_class'   => 'saw-product-info-elementor-form',
				'alignment_class' => 'saw-align-' . sanitize_html_class( $align ),
				'icon_html'       => $this->get_icon_html( $settings ),
				'icon_position'   => ! empty( $settings['icon_position'] ) ? $settings['icon_position'] : 'before',
				'visible'         => isset( $settings['show_button'] ) ? $settings['show_button'] : 'yes',
			)
		);

		if ( $markup ) {
			echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		$this->render_editor_notice( __( 'Upload a Product Info PDF for this product to show the button.', 'sample-available-for-woocommerce' ) );
	}

	/**
	 * Resolve product ID from widget settings.
	 *
	 * @param array $settings Widget settings.
	 * @return int
	 */
	private function get_widget_product_id( $settings ) {
		if ( ! empty( $settings['product_source'] ) && 'selected' === $settings['product_source'] ) {
			return ! empty( $settings['product_id'] ) ? absint( $settings['product_id'] ) : 0;
		}

		return SAW_Plugin::instance()->get_current_product_id();
	}

	/**
	 * Render a preview-only notice inside Elementor.
	 *
	 * @param string $message Notice text.
	 * @return void
	 */
	private function render_editor_notice( $message ) {
		if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			return;
		}

		echo '<div class="saw-editor-notice">' . esc_html( $message ) . '</div>';
	}

	/**
	 * Get sanitized icon HTML.
	 *
	 * @param array $settings Widget settings.
	 * @return string
	 */
	private function get_icon_html( $settings ) {
		if ( empty( $settings['selected_icon']['value'] ) ) {
			return '';
		}

		ob_start();
		\Elementor\Icons_Manager::render_icon(
			$settings['selected_icon'],
			array(
				'aria-hidden' => 'true',
					'class'       => 'saw-button-icon elementor-button-icon',
				)
			);

		return ob_get_clean();
	}
}
