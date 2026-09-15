<?php

namespace Sky_Addons\Modules\Changelog\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Widget_Base;
use Sky_Addons\Includes\Parsedown;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Changelog extends Widget_Base {

	public function get_name() {
		return 'sky-changelog';
	}

	public function get_title() {
		return esc_html__( 'Changelog', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-changelog';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'changelog' ];
	}
	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'sky-addons-styles' ];
		}

		return [ 'sa-changelog' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'sky-addons-scripts' ];
		}

		return [ 'sa-changelog' ];
	}


	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_settings',
			[
				'label' => esc_html__( 'Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'remote_url',
			[
				'label'   => esc_html__( 'Remote URL', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			]
		);

		$this->add_control(
			'cache_data',
			[
				'label'        => esc_html__( 'Cache', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'Off', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'cache_time',
			[
				'label'   => esc_html__( 'Cache Time (Days)', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 3,
				'condition' => [
					'cache_data' => 'yes',
				],
			]
		);

		$this->add_control(
			'versions_to_show',
			[
				'label'       => esc_html__( 'Initial Versions', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 3,
				'min'         => 1,
				'separator'   => 'before',
				'description' => esc_html__( 'Versions shown on load. Rest hidden behind Load More.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'load_step',
			[
				'label'   => esc_html__( 'Versions Per Load', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 3,
				'min'     => 1,
			]
		);

		$this->add_control(
			'load_more_text',
			[
				'label'   => esc_html__( 'Load More Text', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Load More Versions', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'support_url',
			[
				'label'       => esc_html__( 'Support URL', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'        => Controls_Manager::URL,
				'separator'   => 'before',
				'placeholder' => 'https://example.com/support',
				'options'     => [ 'url' ],
			]
		);

		$this->add_control(
			'support_label',
			[
				'label'   => esc_html__( 'Support Label', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Get Support', 'sky-elementor-addons' ),
			]
		);

		$this->end_controls_section();

		/**
		 * Style: Version Heading
		 */
		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Heading', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-changelog-version p' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'heading_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-changelog-version p',
			]
		);

		$this->end_controls_section();

		/**
		 * Style: Version Card
		 */
		$this->start_controls_section(
			'section_version_card_style',
			[
				'label' => esc_html__( 'Version Card', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'version_card_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-changelog-version',
			]
		);

		$this->add_responsive_control(
			'version_card_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-changelog-version' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'version_card_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-changelog-version' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'version_card_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'selector' => '{{WRAPPER}} .sa-changelog-version',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'version_card_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'selector' => '{{WRAPPER}} .sa-changelog-version',
			]
		);

		$this->add_responsive_control(
			'version_card_spacing',
			[
				'label'      => esc_html__( 'Spacing Between Versions', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-changelog-version' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->end_controls_section();

		/**
		 * Style: Labels
		 */
		$this->start_controls_section(
			'section_label_style',
			[
				'label' => esc_html__( 'Labels', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'label_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-label-changelog',
			]
		);

		$this->add_control(
			'heading_fixed',
			[
				'label'     => esc_html__( 'Fixed', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'fixed_color',
			[
				'label' => esc_html__( 'Fixed Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-label-changelog.sa-fixed' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'fixed_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-label-changelog.sa-fixed',
			]
		);

		$this->add_control(
			'heading_added',
			[
				'label'     => esc_html__( 'Added', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'added_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-label-changelog.sa-added' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'added_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-label-changelog.sa-added',
			]
		);

		$this->add_control(
			'heading_updated',
			[
				'label'     => esc_html__( 'Updated', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'updated_color',
			[
				'label' => esc_html__( 'Updated Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-label-changelog.sa-updated' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'updated_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-label-changelog.sa-updated',
			]
		);

		$this->add_control(
			'heading_note',
			[
				'label'     => esc_html__( 'Note', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'note_color',
			[
				'label' => esc_html__( 'Note Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-label-changelog.sa-note' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'note_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-label-changelog.sa-note',
			]
		);

		$this->add_control(
			'heading_changed',
			[
				'label'     => esc_html__( 'Changed', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'changed_color',
			[
				'label' => esc_html__( 'Changed Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-label-changelog.sa-changed' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'changed_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-label-changelog.sa-changed',
			]
		);

		$this->add_control(
			'heading_removed',
			[
				'label'     => esc_html__( 'Removed', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'removed_color',
			[
				'label' => esc_html__( 'Removed Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-label-changelog.sa-removed' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'removed_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-label-changelog.sa-removed',
			]
		);

		$this->end_controls_section();

		/**
		 * Style: Items
		 */
		$this->start_controls_section(
			'section_items_style',
			[
				'label' => esc_html__( 'Items', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'item_text_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-changelog-version li' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'item_spacing',
			[
				'label'      => esc_html__( 'Item Spacing', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 40,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-changelog-version li' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'item_separator_color',
			[
				'label' => esc_html__( 'Separator Color', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-changelog-version li' => 'border-bottom-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}


	protected function parse_data( $response_data ) {
		$parsedown             = new Parsedown();
		$parsedown->addTag     = '<span class="sa-label sa-added">' . esc_html__( 'Added:', 'sky-elementor-addons' ) . '</span>';
		$parsedown->removeTag  = '<span class="sa-label sa-remove">' . esc_html__( 'Removed', 'sky-elementor-addons' ) . '</span>';
		$parsedown->updateTag  = '<span class="sa-label sa-update">' . esc_html__( 'Updated', 'sky-elementor-addons' ) . '</span>';
		$parsedown->changedTag = '<span class="sa-label sa-changed">' . esc_html__( 'Changed', 'sky-elementor-addons' ) . '</span>';
		$parsedown->fixedTag   = '<span class="sa-label sa-fixed">' . esc_html__( 'Fixed', 'sky-elementor-addons' ) . '</span>';
		$parsedown->noteTag    = '<span class="sa-label sa-note">' . esc_html__( 'Note', 'sky-elementor-addons' ) . '</span>';

		$parsedown = $parsedown->text( $response_data );

		$data = $parsedown;

		return $data;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$id       = 'tf_changelog_' . $this->get_id();
		require_once SKY_ADDONS_INC_PATH . 'class-parsedown.php';

		$transient_key = $id . '_data';
		$response_data = false;

		$api_url = ! empty( $settings['remote_url'] ) ? $settings['remote_url'] : '';
		if ( empty( $api_url ) ) {
			echo 'URL End Point Missing.';
			return;
		}

		if ( 'yes' === $settings['cache_data'] ) {
			$response_data = get_transient( $transient_key );
		} else {
			delete_transient( $transient_key );
		}

		if ( ! $response_data ) {
			$response      = wp_safe_remote_request( $api_url, [] );
			$response_data = wp_remote_retrieve_body( $response );
			$response_data = $this->parse_data( $response_data );

			if ( 'yes' === $settings['cache_data'] ) {
				$cache_time = ! empty( $settings['cache_time'] ) ? $settings['cache_time'] : 3;
				set_transient( $transient_key, $response_data, apply_filters( 'sky-addons/changelog/cached-time', DAY_IN_SECONDS * $cache_time ) );
			}
		}

		$final_response_data = $response_data;

		if ( ! is_array( $final_response_data ) && is_wp_error( $final_response_data ) ) {
			echo 'Data not found.';
			return;
		}

		// Strip leading/trailing = from version heading paragraphs only (e.g. "= 3.3.3 =" → "3.3.3")
		$final_response_data = preg_replace( '/<p>=\s*(.*?)\s*=<\/p>/', '<p>$1</p>', $final_response_data );
		// Strip date brackets from version headings (e.g. "[29th April 2026]" → "(29th April 2026)")
		$final_response_data = preg_replace_callback( '/<p>(.*?)\[(.*?)\](.*?)<\/p>/', function ( $m ) {
			return '<p>' . $m[1] . '(' . $m[2] . ')' . $m[3] . '</p>';
		}, $final_response_data );
		// Open all links in new tab
		$final_response_data = str_replace( '<a ', '<a target="_blank" rel="noopener noreferrer" ', $final_response_data );
		$final_response_data = str_replace( 'Added:', '<span class="sa-label-changelog sa-added">Added:</span> ', $final_response_data );
		$final_response_data = str_replace( 'Updated:', '<span class="sa-label-changelog sa-updated">Updated:</span> ', $final_response_data );
		$final_response_data = str_replace( 'Improved:', '<span class="sa-label-changelog sa-updated">Improved:</span> ', $final_response_data );
		$final_response_data = str_replace( 'Fixed:', '<span class="sa-label-changelog sa-fixed">Fixed:</span> ', $final_response_data );
		$final_response_data = str_replace( 'Removed:', '<span class="sa-label-changelog sa-removed">Removed:</span> ', $final_response_data );
		$final_response_data = str_replace( 'Changed:', '<span class="sa-label-changelog sa-changed">Changed:</span> ', $final_response_data );
		$final_response_data = str_replace( 'Note:', '<span class="sa-label-changelog sa-note">Note:</span> ', $final_response_data );

		$final_response_data = preg_replace_callback(
			'/<p>(.*?)<\/p>\s*<ul>(.*?)<\/ul>/s',
			function ( $m ) {
				return '<div class="sa-changelog-version"><p>' . $m[1] . '</p><ul>' . $m[2] . '</ul></div>';
			},
			$final_response_data
		);

		$versions_to_show = absint( ! empty( $settings['versions_to_show'] ) ? $settings['versions_to_show'] : 3 );
		$load_step        = absint( ! empty( $settings['load_step'] ) ? $settings['load_step'] : 3 );
		$load_more_text   = ! empty( $settings['load_more_text'] ) ? $settings['load_more_text'] : esc_html__( 'Load More Versions', 'sky-elementor-addons' );

		$this->add_render_attribute( 'changelog_wrapper', [
			'class'               => 'sa-changelog-wrapper',
			'data-versions-limit' => $versions_to_show,
			'data-load-step'      => $load_step,
			'data-load-more-text' => $load_more_text,
		] );

		echo '<div ';
		$this->print_render_attribute_string( 'changelog_wrapper' );
		echo '>';
		echo wp_kses_post( $final_response_data );
		echo '</div>';

		if ( ! empty( $settings['support_url']['url'] ) ) {
			$support_label = ! empty( $settings['support_label'] ) ? $settings['support_label'] : __( 'Get Support', 'sky-elementor-addons' );
			$this->add_render_attribute( 'support_link', [
				'class'  => 'sa-changelog-support',
				'href'   => esc_url( $settings['support_url']['url'] ),
				'target' => '_blank',
				'rel'    => 'noopener noreferrer',
			] );
			echo '<a ';
			$this->print_render_attribute_string( 'support_link' );
			echo '>';
			echo '<span class="sa-cl-support-icon"></span>';
			echo '<span class="sa-cl-support-text">' . esc_html( $support_label ) . '</span>';
			echo '<span class="sa-cl-support-arrow"></span>';
			echo '</a>';
		}
	}
}
