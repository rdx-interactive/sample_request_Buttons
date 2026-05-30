<?php
/**
 * Elementor widget for a grid of sample-enabled products.
 *
 * @package SampleAvailableForWooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sample Product Grid Elementor widget.
 */
class SAW_Sample_Product_Grid_Elementor_Widget extends \Elementor\Widget_Base {
	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'saw_sample_product_grid';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Sample Product Grid', 'sample-available-for-woocommerce' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-products';
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
		return array( 'woocommerce', 'sample', 'product', 'grid', 'request' );
	}

	/**
	 * Register Elementor controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_query',
			array(
				'label' => __( 'Products', 'sample-available-for-woocommerce' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'products_per_page',
			array(
				'label'   => __( 'Products Per Page', 'sample-available-for-woocommerce' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 8,
				'min'     => 1,
				'max'     => 48,
				'step'    => 1,
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order By', 'sample-available-for-woocommerce' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'date',
				'options' => array(
					'date'       => __( 'Date', 'sample-available-for-woocommerce' ),
					'title'      => __( 'Title', 'sample-available-for-woocommerce' ),
					'menu_order' => __( 'Menu Order', 'sample-available-for-woocommerce' ),
					'rand'       => __( 'Random', 'sample-available-for-woocommerce' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => __( 'Order', 'sample-available-for-woocommerce' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => array(
					'DESC' => __( 'Descending', 'sample-available-for-woocommerce' ),
					'ASC'  => __( 'Ascending', 'sample-available-for-woocommerce' ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content',
			array(
				'label' => __( 'Content', 'sample-available-for-woocommerce' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_image',
			array(
				'label'        => __( 'Show Image', 'sample-available-for-woocommerce' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'sample-available-for-woocommerce' ),
				'label_off'    => __( 'Hide', 'sample-available-for-woocommerce' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_excerpt',
			array(
				'label'        => __( 'Show Short Description', 'sample-available-for-woocommerce' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'sample-available-for-woocommerce' ),
				'label_off'    => __( 'Hide', 'sample-available-for-woocommerce' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'excerpt_words',
			array(
				'label'     => __( 'Description Words', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 18,
				'min'       => 5,
				'max'       => 80,
				'step'      => 1,
				'condition' => array(
					'show_excerpt' => 'yes',
				),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'       => __( 'Button Text', 'sample-available-for-woocommerce' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Request a Sample', 'sample-available-for-woocommerce' ),
				'placeholder' => __( 'Request a Sample', 'sample-available-for-woocommerce' ),
			)
		);

		$this->add_control(
			'show_view_button',
			array(
				'label'        => __( 'Show View Product Button', 'sample-available-for-woocommerce' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'sample-available-for-woocommerce' ),
				'label_off'    => __( 'Hide', 'sample-available-for-woocommerce' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'view_button_text',
			array(
				'label'       => __( 'View Button Text', 'sample-available-for-woocommerce' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'View product', 'sample-available-for-woocommerce' ),
				'placeholder' => __( 'View product', 'sample-available-for-woocommerce' ),
				'condition'   => array(
					'show_view_button' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_grid_style',
			array(
				'label' => __( 'Grid', 'sample-available-for-woocommerce' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'sample-available-for-woocommerce' ),
				'type'           => \Elementor\Controls_Manager::SELECT,
				'default'        => '4',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				),
				'selectors'      => array(
					'{{WRAPPER}} .saw-sample-product-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->add_responsive_control(
			'column_gap',
			array(
				'label'      => __( 'Column Gap', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .saw-sample-product-grid' => 'column-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'row_gap',
			array(
				'label'      => __( 'Row Gap', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .saw-sample-product-grid' => 'row-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_card_style',
			array(
				'label' => __( 'Card', 'sample-available-for-woocommerce' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => __( 'Padding', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .saw-sample-product-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'card_background',
			array(
				'label'     => __( 'Background Color', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .saw-sample-product-card' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .saw-sample-product-card',
			)
		);

		$this->add_responsive_control(
			'card_border_radius',
			array(
				'label'      => __( 'Border Radius', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .saw-sample-product-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .saw-sample-product-card',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_text_style',
			array(
				'label' => __( 'Text', 'sample-available-for-woocommerce' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'content_align',
			array(
				'label'   => __( 'Alignment', 'sample-available-for-woocommerce' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => array(
					'left'   => array(
						'title' => __( 'Left', 'sample-available-for-woocommerce' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'sample-available-for-woocommerce' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'sample-available-for-woocommerce' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default' => 'left',
				'selectors' => array(
					'{{WRAPPER}} .saw-sample-product-card' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => __( 'Title Typography', 'sample-available-for-woocommerce' ),
				'selector' => '{{WRAPPER}} .saw-sample-product-title, {{WRAPPER}} .saw-sample-product-title a',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Title Color', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .saw-sample-product-title, {{WRAPPER}} .saw-sample-product-title a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'excerpt_typography',
				'label'    => __( 'Description Typography', 'sample-available-for-woocommerce' ),
				'selector' => '{{WRAPPER}} .saw-sample-product-excerpt',
			)
		);

		$this->add_control(
			'excerpt_color',
			array(
				'label'     => __( 'Description Color', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .saw-sample-product-excerpt' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_button_layout_style',
			array(
				'label' => __( 'Button Placement', 'sample-available-for-woocommerce' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'buttons_layout',
			array(
				'label'   => __( 'Layout', 'sample-available-for-woocommerce' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'stacked',
				'options' => array(
					'stacked' => __( 'Stacked', 'sample-available-for-woocommerce' ),
					'inline'  => __( 'Inline', 'sample-available-for-woocommerce' ),
				),
			)
		);

		$this->add_control(
			'buttons_order',
			array(
				'label'   => __( 'Order', 'sample-available-for-woocommerce' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'sample_first',
				'options' => array(
					'sample_first' => __( 'Request Sample First', 'sample-available-for-woocommerce' ),
					'view_first'   => __( 'View Product First', 'sample-available-for-woocommerce' ),
				),
			)
		);

		$this->add_responsive_control(
			'buttons_alignment',
			array(
				'label'   => __( 'Alignment', 'sample-available-for-woocommerce' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'stretch',
				'options' => array(
					'stretch' => __( 'Stretch', 'sample-available-for-woocommerce' ),
					'left'    => __( 'Left', 'sample-available-for-woocommerce' ),
					'center'  => __( 'Center', 'sample-available-for-woocommerce' ),
					'right'   => __( 'Right', 'sample-available-for-woocommerce' ),
				),
			)
		);

		$this->add_responsive_control(
			'buttons_gap',
			array(
				'label'      => __( 'Button Gap', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .saw-grid-product-actions' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_button_style',
			array(
				'label' => __( 'Request Sample Button', 'sample-available-for-woocommerce' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .saw-grid-sample-button, {{WRAPPER}} .saw-grid-sample-button .saw-button-text, {{WRAPPER}} .saw-grid-sample-button .elementor-button-text',
			)
		);

		$this->add_responsive_control(
			'button_margin',
			array(
				'label'      => __( 'Margin', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .saw-grid-sample-form' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .saw-grid-sample-button' => '--saw-button-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .saw-grid-sample-button' => '--saw-button-width: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .saw-grid-sample-button' => '--saw-button-min-height: {{SIZE}}{{UNIT}}; min-height: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .saw-grid-sample-button' => '--saw-button-text-color: {{VALUE}}; color: {{VALUE}};',
					'{{WRAPPER}} .saw-grid-sample-button .saw-button-text' => 'color: {{VALUE}};',
					'{{WRAPPER}} .saw-grid-sample-button .elementor-button-text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_background_color',
			array(
				'label'     => __( 'Background Color', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .saw-grid-sample-button' => '--saw-button-bg-color: {{VALUE}}; background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'button_border',
				'selector' => '{{WRAPPER}} .saw-grid-sample-button',
			)
		);

		$this->add_responsive_control(
			'button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .saw-grid-sample-button' => '--saw-button-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .saw-grid-sample-button',
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
					'{{WRAPPER}} .saw-grid-sample-button:hover, {{WRAPPER}} .saw-grid-sample-button:focus' => '--saw-button-hover-text-color: {{VALUE}}; color: {{VALUE}};',
					'{{WRAPPER}} .saw-grid-sample-button:hover .saw-button-text, {{WRAPPER}} .saw-grid-sample-button:focus .saw-button-text' => 'color: {{VALUE}};',
					'{{WRAPPER}} .saw-grid-sample-button:hover .elementor-button-text, {{WRAPPER}} .saw-grid-sample-button:focus .elementor-button-text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_hover_background_color',
			array(
				'label'     => __( 'Background Color', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .saw-grid-sample-button:hover, {{WRAPPER}} .saw-grid-sample-button:focus' => '--saw-button-hover-bg-color: {{VALUE}}; background-color: {{VALUE}};',
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
					'{{WRAPPER}} .saw-grid-sample-button:hover, {{WRAPPER}} .saw-grid-sample-button:focus' => '--saw-button-hover-border-color: {{VALUE}}; border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();

		$this->start_controls_section(
			'section_view_button_style',
			array(
				'label'     => __( 'View Product Button', 'sample-available-for-woocommerce' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_view_button' => 'yes',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'view_button_typography',
				'selector' => '{{WRAPPER}} .saw-grid-view-product-button, {{WRAPPER}} .saw-grid-view-product-button .elementor-button-text',
			)
		);

		$this->add_responsive_control(
			'view_button_margin',
			array(
				'label'      => __( 'Margin', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .saw-grid-view-product-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'view_button_padding',
			array(
				'label'      => __( 'Padding', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .saw-grid-view-product-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'view_button_width',
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
					'{{WRAPPER}} .saw-grid-view-product-button' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'view_button_min_height',
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
					'{{WRAPPER}} .saw-grid-view-product-button' => 'min-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'view_button_style_tabs' );

		$this->start_controls_tab(
			'view_button_normal_tab',
			array(
				'label' => __( 'Normal', 'sample-available-for-woocommerce' ),
			)
		);

		$this->add_control(
			'view_button_text_color',
			array(
				'label'     => __( 'Text Color', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .saw-grid-view-product-button' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'view_button_background_color',
			array(
				'label'     => __( 'Background Color', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .saw-grid-view-product-button' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'view_button_border',
				'selector' => '{{WRAPPER}} .saw-grid-view-product-button',
			)
		);

		$this->add_responsive_control(
			'view_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'sample-available-for-woocommerce' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .saw-grid-view-product-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'view_button_box_shadow',
				'selector' => '{{WRAPPER}} .saw-grid-view-product-button',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'view_button_hover_tab',
			array(
				'label' => __( 'Hover', 'sample-available-for-woocommerce' ),
			)
		);

		$this->add_control(
			'view_button_hover_text_color',
			array(
				'label'     => __( 'Text Color', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .saw-grid-view-product-button:hover, {{WRAPPER}} .saw-grid-view-product-button:focus' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'view_button_hover_background_color',
			array(
				'label'     => __( 'Background Color', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .saw-grid-view-product-button:hover, {{WRAPPER}} .saw-grid-view-product-button:focus' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'view_button_hover_border_color',
			array(
				'label'     => __( 'Border Color', 'sample-available-for-woocommerce' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'condition' => array(
					'view_button_border_border!' => '',
				),
				'selectors' => array(
					'{{WRAPPER}} .saw-grid-view-product-button:hover, {{WRAPPER}} .saw-grid-view-product-button:focus' => 'border-color: {{VALUE}};',
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
		if ( ! function_exists( 'wc_get_product' ) ) {
			return;
		}

		$settings = $this->get_settings_for_display();
		$query    = new WP_Query( $this->get_query_args( $settings ) );

		if ( ! $query->have_posts() ) {
			$this->render_editor_notice( __( 'No sample-enabled products found.', 'sample-available-for-woocommerce' ) );
			return;
		}

		echo '<div class="saw-sample-product-grid">';

		while ( $query->have_posts() ) {
			$query->the_post();

			$product = wc_get_product( get_the_ID() );

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			$this->render_product_card( $product, $settings );
		}

		wp_reset_postdata();

		echo '</div>';
	}

	/**
	 * Build the product query arguments.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	private function get_query_args( $settings ) {
		$orderby = ! empty( $settings['orderby'] ) ? sanitize_key( $settings['orderby'] ) : 'date';

		if ( ! in_array( $orderby, array( 'date', 'title', 'menu_order', 'rand' ), true ) ) {
			$orderby = 'date';
		}

		$order = ! empty( $settings['order'] ) && 'ASC' === strtoupper( $settings['order'] ) ? 'ASC' : 'DESC';

		return array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => ! empty( $settings['products_per_page'] ) ? min( 48, max( 1, absint( $settings['products_per_page'] ) ) ) : 8,
			'orderby'             => $orderby,
			'order'               => $order,
			'ignore_sticky_posts' => true,
			'meta_query'          => array(
				array(
					'key'     => SAW_Plugin::META_SAMPLE_AVAILABLE,
					'value'   => 'yes',
					'compare' => '=',
				),
			),
		);
	}

	/**
	 * Render one product card.
	 *
	 * @param WC_Product $product  Product object.
	 * @param array      $settings Widget settings.
	 * @return void
	 */
	private function render_product_card( $product, $settings ) {
		$product_id = absint( $product->get_id() );
		$permalink  = get_permalink( $product_id );
		$label      = ! empty( $settings['button_text'] ) ? $settings['button_text'] : __( 'Request a Sample', 'sample-available-for-woocommerce' );
		?>
		<article class="saw-sample-product-card">
			<?php if ( ! empty( $settings['show_image'] ) && 'yes' === $settings['show_image'] ) : ?>
				<a class="saw-sample-product-image" href="<?php echo esc_url( $permalink ); ?>">
					<?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ); ?>
				</a>
			<?php endif; ?>

			<h3 class="saw-sample-product-title">
				<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
			</h3>

			<?php if ( ! empty( $settings['show_excerpt'] ) && 'yes' === $settings['show_excerpt'] ) : ?>
				<div class="saw-sample-product-excerpt">
					<?php echo esc_html( $this->get_product_excerpt( $product, $settings ) ); ?>
				</div>
			<?php endif; ?>

			<?php
			$this->render_product_actions( $product_id, $permalink, $label, $settings );
			?>
		</article>
		<?php
	}

	/**
	 * Render the sample and view product buttons.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $permalink  Product permalink.
	 * @param string $label      Sample button label.
	 * @param array  $settings   Widget settings.
	 * @return void
	 */
	private function render_product_actions( $product_id, $permalink, $label, $settings ) {
		$sample_markup = SAW_Plugin::instance()->get_button_markup(
			$product_id,
			array(
				'label'           => $label,
				'wrapper_class'   => 'saw-grid-sample-form',
				'alignment_class' => 'saw-align-stretch',
				'class'           => 'saw-grid-sample-button elementor-button',
			)
		);
		$view_markup   = $this->get_view_product_button_markup( $permalink, $settings );

		if ( ! $sample_markup && ! $view_markup ) {
			return;
		}

		$classes = implode( ' ', $this->get_action_classes( $settings ) );

		echo '<div class="' . esc_attr( $classes ) . '">';

		if ( ! empty( $settings['buttons_order'] ) && 'view_first' === $settings['buttons_order'] ) {
			echo $view_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $sample_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo $sample_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $view_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '</div>';
	}

	/**
	 * Build the view product button markup.
	 *
	 * @param string $permalink Product permalink.
	 * @param array  $settings  Widget settings.
	 * @return string
	 */
	private function get_view_product_button_markup( $permalink, $settings ) {
		if ( empty( $settings['show_view_button'] ) || 'yes' !== $settings['show_view_button'] ) {
			return '';
		}

		$label = ! empty( $settings['view_button_text'] ) ? $settings['view_button_text'] : __( 'View product', 'sample-available-for-woocommerce' );

		return '<a class="saw-grid-view-product-button elementor-button" href="' . esc_url( $permalink ) . '"><span class="elementor-button-content-wrapper"><span class="elementor-button-text">' . esc_html( $label ) . '</span></span></a>';
	}

	/**
	 * Build action wrapper classes.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	private function get_action_classes( $settings ) {
		$layout    = ! empty( $settings['buttons_layout'] ) && 'inline' === $settings['buttons_layout'] ? 'inline' : 'stacked';
		$order     = ! empty( $settings['buttons_order'] ) && 'view_first' === $settings['buttons_order'] ? 'view-first' : 'sample-first';
		$alignment = ! empty( $settings['buttons_alignment'] ) ? sanitize_key( $settings['buttons_alignment'] ) : 'stretch';

		if ( ! in_array( $alignment, array( 'stretch', 'left', 'center', 'right' ), true ) ) {
			$alignment = 'stretch';
		}

		return array(
			'saw-grid-product-actions',
			'saw-grid-buttons-' . $layout,
			'saw-grid-buttons-' . $order,
			'saw-grid-buttons-align-' . $alignment,
		);
	}

	/**
	 * Get a trimmed product excerpt.
	 *
	 * @param WC_Product $product  Product object.
	 * @param array      $settings Widget settings.
	 * @return string
	 */
	private function get_product_excerpt( $product, $settings ) {
		$text  = $product->get_short_description() ? $product->get_short_description() : $product->get_description();
		$text  = wp_strip_all_tags( strip_shortcodes( $text ) );
		$words = ! empty( $settings['excerpt_words'] ) ? min( 80, max( 5, absint( $settings['excerpt_words'] ) ) ) : 18;

		return wp_trim_words( $text, $words, '' );
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
}
