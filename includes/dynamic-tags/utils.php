<?php

namespace Sky_Addons\Includes\Traits;

trait UtilsTrait {

	public function get_atomic_group(): string {
		$groups = $this->get_group();

		if ( is_array( $groups ) && ! empty( $groups ) ) {
			return (string) reset( $groups );
		}

		return is_string( $groups ) ? $groups : '';
	}

	public function get_editor_config() {
		$config = parent::get_editor_config();

		if ( ! $this->should_limit_atomic_to_advanced() ) {
			return $config;
		}

		// Keep Atomic conversion even when legacy-only controls exist.
		$config['force_convert_to_atomic'] = true;

		// In Atomic, show only Advanced controls for post-related tags.
		if ( $this->is_atomic_dynamic_tags_context() ) {
			$controls          = is_array( $config['controls'] ?? null ) ? $config['controls'] : [];
			$advanced_controls = [];

			foreach ( $controls as $name => $control ) {
				$section = $control['section'] ?? '';
				$type    = $control['type'] ?? '';

				if ( 'advanced' === $name || 'advanced' === $section || ( 'section' === $type && 'advanced' === $name ) ) {
					$advanced_controls[ $name ] = $control;
				}
			}

			$config['controls']          = $advanced_controls;
			$config['settings_required'] = ! empty( $advanced_controls );
		}

		return $config;
	}

	protected function should_limit_atomic_to_advanced(): bool {
		$groups = $this->get_group();

		$tag_name                = method_exists( $this, 'get_name' ) ? (string) $this->get_name() : '';
		$site_advanced_only_tags = [
			'sky-addons-site-title',
			'sky-addons-site-tagline',
		];

		if ( in_array( $tag_name, $site_advanced_only_tags, true ) ) {
			return true;
		}

		if ( ! is_array( $groups ) ) {
			return false;
		}

		return in_array( 'sky-addons-post', $groups, true );
	}

	protected function is_atomic_dynamic_tags_context(): bool {
		foreach ( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) as $frame ) {
			if (
				! empty( $frame['class'] ) &&
				'Elementor\\Modules\\AtomicWidgets\\DynamicTags\\Dynamic_Tags_Editor_Config' === $frame['class']
			) {
				return true;
			}
		}

		return false;
	}

	protected function advanced_controls() {
		$tag_name = $this->get_name();

		$this->start_controls_section(
			'advanced',
			[
				'label' => esc_html__( 'Advanced', 'sky-elementor-addons' ),
			]
		);

		// Dynamic hook: before core controls
		do_action( "sky_addons/advanced_section/{$tag_name}/before", $this );

		$this->add_control(
			'sky_word_limit',
			[
				'label'       => esc_html__( 'Word Limit', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'description' => esc_html__( '0 means no limit', 'sky-elementor-addons' ),
			]
		);

		$this->add_control('before', [
			'label'   => esc_html__( 'Before', 'sky-elementor-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '',
			'ai' => [
				'active' => false,
			],
		]);

		$this->add_control('after', [
			'label'   => esc_html__( 'After', 'sky-elementor-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '',
			'ai' => [
				'active' => false,
			],
		]);

		$this->add_control('fallback', [
			'label'   => esc_html__( 'Fallback', 'sky-elementor-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '',
			'ai' => [
				'active' => false,
			],
		]);

		// Dynamic hook: after core controls
		do_action( "sky_addons/advanced_section/{$tag_name}/after", $this );

		$this->end_controls_section();
	}


	protected function apply_word_limit( $text ) {
		$settings = $this->get_settings();
		$limit    = isset( $settings['sky_word_limit'] ) ? $settings['sky_word_limit'] : 0;
		if ( $limit > 0 ) {
			$limited = wp_trim_words( $text, $limit, '…' );
			return $limited;
		}
		return $text;
	}

	protected function common_product_controls() {
		$this->add_control(
			'sky_product_id',
			[
				'label'       => esc_html__( 'Search & Select Product', 'sky-elementor-addons' ),
				'type'        => \Sky_Addons\Includes\Controls\SelectInput\Dynamic_Select::TYPE,
				'multiple'    => false,
				'label_block' => true,
				'description' => esc_html__( 'Leave blank to use current product', 'sky-elementor-addons' ),
				'query_args' => [
					'query'     => 'posts',
					'post_type' => 'product',
				],
			]
		);
	}
	protected function common_post_controls() {
		$this->add_control(
			'sky_post_type',
			[
				'label'   => esc_html__( 'Post Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'current'  => esc_html__( 'Current Post', 'sky-elementor-addons' ),
					'selected' => esc_html__( 'Selected Post', 'sky-elementor-addons' ),
				],
				'default' => 'current',
			]
		);

		$this->add_control(
			'sky_posts_selected_id',
			[
				'label'       => esc_html__( 'Search & Select Post', 'sky-elementor-addons' ),
				'type'        => \Sky_Addons\Includes\Controls\SelectInput\Dynamic_Select::TYPE,
				'multiple'    => false,
				'label_block' => true,
				'query_args' => [
					'query' => 'posts',
				],
				'condition' => [
					'sky_post_type' => 'selected',
				],
			]
		);
	}

	protected function common_term_controls() {
		$this->add_control(
			'sky_selected_term_id',
			[
				'label'       => esc_html__( 'Search & Select Term', 'sky-elementor-addons' ),
				'type'        => \Sky_Addons\Includes\Controls\SelectInput\Dynamic_Select::TYPE,
				'multiple'    => false,
				'label_block' => true,
				'query_args' => [
					'query'     => 'terms',
					'post_type' => '_related_post_type',
				],
			]
		);
	}

	protected function common_user_controls() {
		$this->add_control(
			'sky_selected_user_id',
			[
				'label'       => esc_html__( 'Search & Select User', 'sky-elementor-addons' ),
				'type'        => \Sky_Addons\Includes\Controls\SelectInput\Dynamic_Select::TYPE,
				'multiple'    => false,
				'label_block' => true,
				'query_args' => [
					'query' => 'authors',
				],
			]
		);
	}

	protected function fallback_control() {
		$this->add_control(
			'fallback',
			[
				'label' => esc_html__( 'Fallback', 'sky-elementor-addons' ),
				'ai' => [
					'active' => false,
				],
			]
		);
	}

	protected function get_post_id() {
		$settings = $this->get_settings();
		if ( ! empty( $settings['sky_post_type'] ) && 'selected' === $settings['sky_post_type'] && ! empty( $settings['sky_posts_selected_id'] ) ) {
			return $settings['sky_posts_selected_id'];
		}
		return get_the_ID();
	}

	protected function get_term_id() {
		$settings = $this->get_settings();

		if ( empty( $settings['sky_selected_term_id'] ) ) {
			return;
		}

		return $settings['sky_selected_term_id'];
	}

	protected function get_user_id() {
		$user_id = $this->get_settings( 'sky_selected_user_id' );

		if ( empty( $user_id ) ) {
			return get_current_user_id();
		}

		return $user_id;
	}

	protected function get_product_id(): ?int {
		$settings   = $this->get_settings();
		$product_id = $settings['sky_product_id'] ?? null;

		if ( ! $product_id ) {
			$product_id = get_the_ID();
		}

		if ( ! $product_id || get_post_type( $product_id ) !== 'product' ) {
			return null;
		}

		return (int) $product_id;
	}

	/**
	 * @param array $types
	 *
	 * @return array
	 */
	public static function get_control_options( $types ) {
		// ACF >= 5.0.0
		if ( function_exists( 'acf_get_field_groups' ) ) {
			$acf_groups = acf_get_field_groups();
		} else {
			$acf_groups = apply_filters( 'acf/get_field_groups', [] );
		}

		$groups = [];

		$options_page_groups_ids = [];

		if ( function_exists( 'acf_options_page' ) ) {
			$pages = acf_options_page()->get_pages();
			foreach ( $pages as $slug => $page ) {
				$options_page_groups = acf_get_field_groups( [
					'options_page' => $slug,
				] );

				foreach ( $options_page_groups as $options_page_group ) {
					$options_page_groups_ids[] = $options_page_group['ID'];
				}
			}
		}

		foreach ( $acf_groups as $acf_group ) {
			// ACF >= 5.0.0
			if ( function_exists( 'acf_get_fields' ) ) {
				if ( isset( $acf_group['ID'] ) && ! empty( $acf_group['ID'] ) ) {
					$fields = acf_get_fields( $acf_group['ID'] );
				} else {
					$fields = acf_get_fields( $acf_group );
				}
			} else {
				$fields = apply_filters( 'acf/field_group/get_fields', [], $acf_group['id'] );
			}

			$options = [];

			if ( ! is_array( $fields ) ) {
				continue;
			}

			$has_option_page_location = in_array( $acf_group['ID'], $options_page_groups_ids, true );
			$is_only_options_page     = $has_option_page_location && 1 === count( $acf_group['location'] );

			foreach ( $fields as $field ) {
				if ( ! in_array( $field['type'], $types, true ) ) {
					continue;
				}

				// Use group ID for unique keys
				if ( $has_option_page_location ) {
					$key             = 'options:' . $field['name'];
					$options[ $key ] = esc_html__( 'Options', 'sky-elementor-addons' ) . ':' . $field['label'];
					if ( $is_only_options_page ) {
						continue;
					}
				}

				$key             = $field['key'] . ':' . $field['name'];
				$options[ $key ] = $field['label'];
			}

			if ( empty( $options ) ) {
				continue;
			}

			if ( 1 === count( $options ) ) {
				$options = [ -1 => ' -- ' ] + $options;
			}

			$groups[] = [
				'label'   => $acf_group['title'],
				'options' => $options,
			];
		} // End foreach().

		return $groups;
	}
}
