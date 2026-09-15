<?php
/**
 * Table widget.
 *
 * Builds a real <table> from column + row repeaters, a CSV file/URL, or a public
 * Google Sheet. Sorting, search, pagination and export are driven by a small
 * hand-rolled handler — no DataTables, no vendor weight.
 *
 * @package Sky_Addons
 */

namespace Sky_Addons\Modules\Table\Widgets;

use Elementor\Widget_Base;
use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Image_Size;
use Elementor\Control_Media;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Sky_Addons\Modules\Table\Data_Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Table extends Widget_Base {

	/**
	 * Plain-text column labels, indexed by column position. Used for the stacked
	 * mobile view's data-label attributes and for export headings.
	 *
	 * @var string[]
	 */
	private $column_labels = [];

	public function get_name() {
		return 'sky-table';
	}

	public function get_title() {
		return esc_html__( 'Table', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-table';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'table', 'data', 'grid', 'csv', 'sheet', 'sort', 'spreadsheet', 'sky' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'sky-addons-styles' ];
		}
		return [ 'sa-table' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'sky-addons-scripts' ];
		}
		return [ 'sa-table' ];
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	/**
	 * Remote sources must never be served from Elementor's element cache — the
	 * whole point of the TTL control is that the data can change underneath us.
	 *
	 * This is called on the widget *type* while building the editor config, where
	 * no element data exists — reading settings here fatals. So it answers for the
	 * widget as a whole, not per instance. Data_Source does the real caching.
	 */
	public function is_dynamic_content(): bool {
		return true;
	}

	protected function register_controls() {
		$this->register_source_section();
		$this->register_head_section();
		$this->register_body_section();
		$this->register_features_section();
		$this->register_settings_section();

		$this->register_table_style();
		$this->register_head_style();
		$this->register_row_style();
		$this->register_stacked_style();
		$this->register_cell_style();
		$this->register_toolbar_style();
	}

	/*
	---------------------------------------------------------------------
	 * Controls — Content
	 * ------------------------------------------------------------------ */

	/**
	 * Where the rows come from: the repeaters below, a CSV, or a Google Sheet.
	 */
	private function register_source_section() {

		$this->start_controls_section(
			'section_table_source',
			[
				'label' => esc_html__( 'Data Source', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'table_source',
			[
				'label'   => esc_html__( 'Source', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'repeater',
				// Extensions add their own sources through this filter and resolve
				// rows via `sky-addons/table/data`.
				'options' => Data_Source::sources(),
			]
		);

		$this->add_control(
			'html_rows',
			[
				'label'       => esc_html__( 'Table Rows', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 14,
				'dynamic'     => [ 'active' => true ],
				'default'     => $this->default_html_rows(),
				'description' => esc_html__( 'Paste one <tr> per row. A first row of <th> cells becomes the header. Only inline formatting (links, bold, italic, images) is kept inside a cell — scripts, styles and shortcodes are stripped.', 'sky-elementor-addons' ),
				'condition'   => [ 'table_source' => 'html' ],
			]
		);

		$this->add_control(
			'csv_file',
			[
				'label'       => esc_html__( 'CSV File', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'media_types' => [ 'text' ],
				'default'     => [
					'url' => SKY_ADDONS_ASSETS_URL . 'others/table-sample-data.csv',
				],
				'condition'   => [ 'table_source' => 'csv' ],
			]
		);

		$this->add_control(
			'csv_url',
			[
				'label'       => esc_html__( 'CSV URL', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => 'https://example.com/data.csv',
				'default'     => SKY_ADDONS_ASSETS_URL . 'others/table-sample-data.csv',
				'description' => esc_html__( 'Fetched server-side and cached. Local and private network addresses are blocked, so this will not resolve on a local development site.', 'sky-elementor-addons' ),
				'condition'   => [ 'table_source' => 'csv_url' ],
			]
		);

		$this->add_control(
			'sheet_url',
			[
				'label'       => esc_html__( 'Google Sheet URL', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => 'https://docs.google.com/spreadsheets/d/...',
				'default'     => 'https://docs.google.com/spreadsheets/d/1fIWXIkBuH6UsF25xdb84EpKZ0Uf0JvbSYH7PBUgERLk/edit?usp=sharing',
				'description' => esc_html__( 'Share the sheet as "Anyone with the link can view". No API key needed.', 'sky-elementor-addons' ),
				'condition'   => [ 'table_source' => 'google_sheet' ],
			]
		);

		$this->add_control(
			'sheet_name',
			[
				'label'       => esc_html__( 'Sheet Tab', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Sheet1', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Leave empty to use the first tab.', 'sky-elementor-addons' ),
				'condition'   => [ 'table_source' => 'google_sheet' ],
			]
		);

		$this->add_control(
			'sheet_range',
			[
				'label'       => esc_html__( 'Range', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'A1:E20',
				'description' => esc_html__( 'Leave empty to use the whole sheet.', 'sky-elementor-addons' ),
				'condition'   => [ 'table_source' => 'google_sheet' ],
			]
		);

		$this->add_control(
			'remote_cache_ttl',
			[
				'label'     => esc_html__( 'Cache Lifetime', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => HOUR_IN_SECONDS,
				'options'   => [
					0     => esc_html__( 'No cache (slow)', 'sky-elementor-addons' ),
					300   => esc_html__( '5 Minutes', 'sky-elementor-addons' ),
					900   => esc_html__( '15 Minutes', 'sky-elementor-addons' ),
					3600  => esc_html__( '1 Hour', 'sky-elementor-addons' ),
					21600 => esc_html__( '6 Hours', 'sky-elementor-addons' ),
					86400 => esc_html__( '24 Hours', 'sky-elementor-addons' ),
				],
				'condition' => [ 'table_source' => [ 'csv_url', 'google_sheet' ] ],
			]
		);

		$this->add_control(
			'csv_delimiter',
			[
				'label'     => esc_html__( 'Delimiter', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => ',',
				'options'   => [
					','   => esc_html__( 'Comma ( , )', 'sky-elementor-addons' ),
					';'   => esc_html__( 'Semicolon ( ; )', 'sky-elementor-addons' ),
					'tab' => esc_html__( 'Tab', 'sky-elementor-addons' ),
					'|'   => esc_html__( 'Pipe ( | )', 'sky-elementor-addons' ),
				],
				'condition' => [ 'table_source' => [ 'csv', 'csv_url', 'google_sheet' ] ],
			]
		);

		$this->add_control(
			'csv_has_header',
			[
				'label'        => esc_html__( 'First Row Is Header', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [ 'table_source' => [ 'csv', 'csv_url', 'google_sheet' ] ],
			]
		);

		/**
		 * Fires at the end of the Table widget's Data Source section.
		 *
		 * Extensions add controls for their own sources here.
		 *
		 * @param \Elementor\Widget_Base $widget The Table widget.
		 */
		do_action( 'sky-addons/table/source-controls', $this );

		$this->end_controls_section();
	}

	/**
	 * Column repeater. Control names are frozen so existing tables keep working.
	 */
	private function register_head_section() {

		$this->start_controls_section(
			'section_table_column',
			[
				'label'     => esc_html__( 'Table Head', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'table_source' => 'repeater' ],
			]
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs( '_tabs_column' );

		$repeater->start_controls_tab(
			'_tab_column_content',
			[ 'label' => esc_html__( 'Content', 'sky-elementor-addons' ) ]
		);

		$repeater->add_control(
			'column_name',
			[
				'label'       => esc_html__( 'Title', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__( 'Column Name', 'sky-elementor-addons' ),
				'default'     => esc_html__( 'Column One', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'column_span',
			[
				'label' => esc_html__( 'Col Span', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::NUMBER,
				'min'   => 0,
				'max'   => 50,
				'step'  => 1,
			]
		);

		$repeater->add_control(
			'column_media',
			[
				'label'       => esc_html__( 'Media', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'toggle'      => false,
				'default'     => 'none',
				'options'     => [
					'none'  => [
						'title' => esc_html__( 'None', 'sky-elementor-addons' ),
						'icon'  => 'eicon-editor-close',
					],
					'icon'  => [
						'title' => esc_html__( 'Icon', 'sky-elementor-addons' ),
						'icon'  => 'eicon-info-circle',
					],
					'image' => [
						'title' => esc_html__( 'Image', 'sky-elementor-addons' ),
						'icon'  => 'eicon-image-bold',
					],
				],
			]
		);

		$repeater->add_control(
			'column_icons',
			[
				'label'            => esc_html__( 'Icon', 'sky-elementor-addons' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'column_icon',
				'label_block'      => true,
				'condition'        => [ 'column_media' => 'icon' ],
			]
		);

		$repeater->add_control(
			'column_image',
			[
				'label'     => esc_html__( 'Image', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => [ 'url' => Utils::get_placeholder_image_src() ],
				'dynamic'   => [ 'active' => true ],
				'condition' => [ 'column_media' => 'image' ],
			]
		);

		$repeater->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'      => 'column_thumbnail',
				'default'   => 'thumbnail',
				'separator' => 'none',
				'condition' => [ 'column_media' => 'image' ],
			]
		);

		$repeater->add_control(
			'column_sortable',
			[
				'label'        => esc_html__( 'Sortable', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Only applies when sorting is enabled in the Features section.', 'sky-elementor-addons' ),
			]
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab(
			'_tabs_column_style',
			[ 'label' => esc_html__( 'Style', 'sky-elementor-addons' ) ]
		);

		$repeater->add_responsive_control(
			'column_width',
			[
				'label'      => esc_html__( 'Column Width', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 40,
						'max' => 800,
					],
					'%'  => [
						'min' => 5,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} {{CURRENT_ITEM}}.sa-table__head-column-cell' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$repeater->add_control(
			'head_custom_color',
			[
				'label'     => esc_html__( 'Icon Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [ 'column_media' => 'icon' ],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .sa-table__head-column-cell-icon i' => 'color: {{VALUE}}',
					'{{WRAPPER}} {{CURRENT_ITEM}} .sa-table__head-column-cell-icon svg' => 'fill: {{VALUE}}',
				],
			]
		);

		$repeater->end_controls_tab();
		$repeater->end_controls_tabs();

		$this->add_control(
			'columns_data',
			[
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ column_name }}}',
				'default'     => [
					[ 'column_name' => esc_html__( 'WordPress', 'sky-elementor-addons' ) ],
					[ 'column_name' => esc_html__( 'Elementor', 'sky-elementor-addons' ) ],
					[ 'column_name' => esc_html__( 'Sky Addons', 'sky-elementor-addons' ) ],
				],
			]
		);

		$this->add_responsive_control(
			'head_align',
			[
				'label'     => esc_html__( 'Alignment', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::CHOOSE,
				'separator' => 'before',
				'options'   => [
					'left'   => [
						'title' => esc_html__( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'   => 'left',
				'toggle'    => false,
				'selectors' => [
					'{{WRAPPER}} .sa-table__head-column-cell' => '--sa-table-head-align: {{VALUE}};',
				],
			]
		);

		// Not responsive: this drives a prefix_class, and Elementor does not make
		// prefix_class responsive — the per-breakpoint variants would be dead UI.
		$this->add_control(
			'icon_position',
			[
				'label'        => esc_html__( 'Icon Position', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => [
					'left'   => [
						'title' => esc_html__( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-right',
					],
					'top'    => [
						'title' => esc_html__( 'Top', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-top',
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'default'      => 'right',
				'toggle'       => false,
				'prefix_class' => 'sa-column-icon-',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Row repeater. A `row` item opens a row; every `column` item after it is a
	 * cell in that row — Elementor has no nested repeaters, so this marker is the
	 * only workable authoring shape.
	 */
	private function register_body_section() {

		$this->start_controls_section(
			'section_table_row',
			[
				'label'     => esc_html__( 'Table Row / Body', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'table_source' => 'repeater' ],
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'row_column_type',
			[
				'label'   => esc_html__( 'Row/Column', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'row',
				'options' => [
					'row'    => esc_html__( 'Row', 'sky-elementor-addons' ),
					'column' => esc_html__( 'Column', 'sky-elementor-addons' ),
				],
			]
		);

		$repeater->start_controls_tabs( '_tabs_row' );

		$repeater->start_controls_tab(
			'_tab_row_content',
			[
				'label'     => esc_html__( 'Content', 'sky-elementor-addons' ),
				'condition' => [ 'row_column_type' => 'column' ],
			]
		);

		$repeater->add_control(
			'cell_type',
			[
				'label'     => esc_html__( 'Cell Type', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'text',
				'options'   => [
					'text'     => esc_html__( 'Text', 'sky-elementor-addons' ),
					'badge'    => esc_html__( 'Badge', 'sky-elementor-addons' ),
					'button'   => esc_html__( 'Button', 'sky-elementor-addons' ),
					'progress' => esc_html__( 'Progress Bar', 'sky-elementor-addons' ),
					'rating'   => esc_html__( 'Star Rating', 'sky-elementor-addons' ),
				],
				'condition' => [ 'row_column_type' => 'column' ],
			]
		);

		$repeater->add_control(
			'cell_name',
			[
				'label'       => esc_html__( 'Title', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'label_block' => true,
				'placeholder' => esc_html__( 'Cell Name', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Accepts the same safe HTML as a post — links, bold, lists, images. Scripts and styles are stripped.', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
				'condition'   => [ 'row_column_type' => 'column' ],
			]
		);

		$repeater->add_control(
			'cell_value',
			[
				'label'       => esc_html__( 'Value', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 100,
				'step'        => 0.1,
				'default'     => 60,
				'description' => esc_html__( 'Progress: 0-100. Rating: 0-5.', 'sky-elementor-addons' ),
				'condition'   => [
					'row_column_type' => 'column',
					'cell_type'       => [ 'progress', 'rating' ],
				],
			]
		);

		$repeater->add_control(
			'cell_link',
			[
				'label'       => esc_html__( 'Link', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://example.com',
				'dynamic'     => [ 'active' => true ],
				'condition'   => [
					'row_column_type' => 'column',
					'cell_type'       => [ 'text', 'badge', 'button' ],
				],
			]
		);

		$repeater->add_control(
			'row_column_span',
			[
				'label'     => esc_html__( 'Col Span', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 0,
				'max'       => 50,
				'step'      => 1,
				'condition' => [ 'row_column_type' => 'column' ],
			]
		);

		$repeater->add_control(
			'row_span',
			[
				'label'     => esc_html__( 'Row Span', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 0,
				'max'       => 50,
				'step'      => 1,
				'condition' => [ 'row_column_type' => 'column' ],
			]
		);

		$repeater->add_control(
			'row_media',
			[
				'label'       => esc_html__( 'Media', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'toggle'      => false,
				'default'     => 'none',
				'condition'   => [
					'row_column_type' => 'column',
					'cell_type!'      => [ 'progress', 'rating' ],
				],
				'options'     => [
					'none'  => [
						'title' => esc_html__( 'None', 'sky-elementor-addons' ),
						'icon'  => 'eicon-editor-close',
					],
					'icon'  => [
						'title' => esc_html__( 'Icon', 'sky-elementor-addons' ),
						'icon'  => 'eicon-info-circle',
					],
					'image' => [
						'title' => esc_html__( 'Image', 'sky-elementor-addons' ),
						'icon'  => 'eicon-image-bold',
					],
				],
			]
		);

		$repeater->add_control(
			'row_icons',
			[
				'label'            => esc_html__( 'Icon', 'sky-elementor-addons' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'row_icon',
				'label_block'      => true,
				'condition'        => [
					'row_media'       => 'icon',
					'row_column_type' => 'column',
				],
			]
		);

		$repeater->add_control(
			'row_image',
			[
				'label'   => esc_html__( 'Image', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [ 'url' => Utils::get_placeholder_image_src() ],
				'dynamic' => [ 'active' => true ],
				'condition' => [
					'row_media'       => 'image',
					'row_column_type' => 'column',
				],
			]
		);

		$repeater->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'      => 'row_thumbnail',
				'default'   => 'thumbnail',
				'separator' => 'none',
				'exclude'   => [ 'custom' ],
				'condition' => [
					'row_media'       => 'image',
					'row_column_type' => 'column',
				],
			]
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab(
			'_tabs_row_style',
			[
				'label'     => esc_html__( 'Style', 'sky-elementor-addons' ),
				'condition' => [ 'row_column_type' => 'column' ],
			]
		);

		$repeater->add_control(
			'row_custom_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [ 'row_column_type' => 'column' ],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}.sa-table__body-row-cell' => 'background-color: {{VALUE}}',
				],
			]
		);

		$repeater->add_control(
			'row_custom_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [ 'row_column_type' => 'column' ],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .sa-table__body-row-cell-text' => 'color: {{VALUE}}',
				],
			]
		);

		$repeater->add_control(
			'row_custom_icon_color',
			[
				'label' => esc_html__( 'Icon Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'condition' => [
					'row_column_type' => 'column',
					'row_media'       => 'icon',
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .sa-table__body-row-cell-icon i' => 'color: {{VALUE}}',
					'{{WRAPPER}} {{CURRENT_ITEM}} .sa-table__body-row-cell-icon svg' => 'fill: {{VALUE}}',
				],
			]
		);

		$repeater->add_responsive_control(
			'row_custom_icon_size',
			[
				'label'     => esc_html__( 'Icon/Image Size', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'condition' => [ 'row_column_type' => 'column' ],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .sa-table__body-row-cell-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} {{CURRENT_ITEM}} .sa-table__body-row-cell-icon img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} {{CURRENT_ITEM}} .sa-table__body-row-cell-icon svg' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$repeater->add_control(
			'cell_accent_color',
			[
				'label' => esc_html__( 'Accent Color', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'  => Controls_Manager::COLOR,
				'condition' => [
					'row_column_type' => 'column',
					'cell_type!'      => 'text',
				],
				// Each cell type reads its own token, so overriding the shared
				// accent alone would not reach them — set all four.
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => '--sa-table-badge-bg: {{VALUE}}; --sa-table-btn-bg: {{VALUE}}; --sa-table-btn-bg-hover: {{VALUE}}; --sa-table-progress-color: {{VALUE}}; --sa-table-rating-fill: {{VALUE}};',
				],
			]
		);

		$repeater->end_controls_tab();
		$repeater->end_controls_tabs();

		$this->add_control(
			'rows_data',
			[
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '<# print( ( row_column_type == "column" ) ? cell_name : "Row Starts" ) #>',
				// Four rows rather than one, so the starting table demonstrates
				// striping, sorting and the stacked mobile layout straight away.
				'default'     => [
					[ 'row_column_type' => 'row' ],
					[
						'row_column_type' => 'column',
						'cell_name'       => esc_html__( 'Top CMS', 'sky-elementor-addons' ),
					],
					[
						'row_column_type' => 'column',
						'cell_name'       => esc_html__( 'Website Builder', 'sky-elementor-addons' ),
					],
					[
						'row_column_type' => 'column',
						'cell_name'       => esc_html__( 'Creative Addons', 'sky-elementor-addons' ),
					],
					[ 'row_column_type' => 'row' ],
					[
						'row_column_type' => 'column',
						'cell_name'       => esc_html__( 'Open Source', 'sky-elementor-addons' ),
					],
					[
						'row_column_type' => 'column',
						'cell_name'       => esc_html__( 'Drag & Drop', 'sky-elementor-addons' ),
					],
					[
						'row_column_type' => 'column',
						'cell_name'       => esc_html__( '90+ Widgets', 'sky-elementor-addons' ),
					],
					[ 'row_column_type' => 'row' ],
					[
						'row_column_type' => 'column',
						'cell_name'       => esc_html__( 'Plugins & Themes', 'sky-elementor-addons' ),
					],
					[
						'row_column_type' => 'column',
						'cell_name'       => esc_html__( 'Theme Builder', 'sky-elementor-addons' ),
					],
					[
						'row_column_type' => 'column',
						'cell_name'       => esc_html__( 'Extensions', 'sky-elementor-addons' ),
					],
					[ 'row_column_type' => 'row' ],
					[
						'row_column_type' => 'column',
						'cell_name'       => esc_html__( 'Free', 'sky-elementor-addons' ),
					],
					[
						'row_column_type' => 'column',
						'cell_name'       => esc_html__( 'Free & Pro', 'sky-elementor-addons' ),
					],
					[
						'row_column_type' => 'column',
						'cell_name'       => esc_html__( 'Free & Pro', 'sky-elementor-addons' ),
					],
				],
			]
		);

		$this->add_responsive_control(
			'row_align',
			[
				'label'     => esc_html__( 'Alignment', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::CHOOSE,
				'separator' => 'before',
				'options'   => [
					'left'   => [
						'title' => esc_html__( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'   => 'left',
				'toggle'    => false,
				'selectors' => [
					'{{WRAPPER}} .sa-table__body-row-cell' => '--sa-table-row-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'row_icon_position',
			[
				'label'        => esc_html__( 'Icon Position', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => [
					'left'   => [
						'title' => esc_html__( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-right',
					],
					'top'    => [
						'title' => esc_html__( 'Top', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-top',
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'default'      => 'right',
				'toggle'       => false,
				'prefix_class' => 'sa-row-icon-',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Sorting, search, pagination and export — the DataTables replacement.
	 */
	private function register_features_section() {

		$this->start_controls_section(
			'section_table_features',
			[
				'label' => esc_html__( 'Features', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_sorting',
			[
				'label'        => esc_html__( 'Sorting', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'default_sort_column',
			[
				'label'       => esc_html__( 'Default Sort Column', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 1,
				'step'        => 1,
				'placeholder' => esc_html__( 'None', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Column number, starting at 1. Leave empty for no initial sort.', 'sky-elementor-addons' ),
				'condition'   => [ 'enable_sorting' => 'yes' ],
			]
		);

		$this->add_control(
			'default_sort_order',
			[
				'label'     => esc_html__( 'Default Sort Order', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'asc',
				'options'   => [
					'asc'  => esc_html__( 'Ascending', 'sky-elementor-addons' ),
					'desc' => esc_html__( 'Descending', 'sky-elementor-addons' ),
				],
				'condition' => [ 'enable_sorting' => 'yes' ],
			]
		);

		$this->add_control(
			'enable_search',
			[
				'label'        => esc_html__( 'Search', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'search_placeholder',
			[
				'label'     => esc_html__( 'Search Placeholder', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Search…', 'sky-elementor-addons' ),
				'condition' => [ 'enable_search' => 'yes' ],
			]
		);

		$this->add_control(
			'enable_pagination',
			[
				'label'        => esc_html__( 'Pagination', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'rows_per_page',
			[
				'label'     => esc_html__( 'Rows Per Page', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 200,
				'step'      => 1,
				'default'   => 10,
				'condition' => [ 'enable_pagination' => 'yes' ],
			]
		);

		$this->add_control(
			'page_size_options',
			[
				'label'       => esc_html__( 'Rows Per Page Choices', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '10, 25, 50, 100',
				'description' => esc_html__( 'Comma separated. Leave empty to hide the selector.', 'sky-elementor-addons' ),
				'condition'   => [ 'enable_pagination' => 'yes' ],
			]
		);

		$this->add_control(
			'show_entries_info',
			[
				'label'        => esc_html__( 'Entries Info', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [ 'enable_pagination' => 'yes' ],
			]
		);

		$this->add_control(
			'enable_export',
			[
				'label'        => esc_html__( 'Export Buttons', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'export_buttons',
			[
				'label'     => esc_html__( 'Buttons', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT2,
				'multiple'  => true,
				'default'   => [ 'csv', 'print' ],
				'options'   => [
					'csv'   => esc_html__( 'CSV', 'sky-elementor-addons' ),
					'copy'  => esc_html__( 'Copy', 'sky-elementor-addons' ),
					'print' => esc_html__( 'Print', 'sky-elementor-addons' ),
				],
				'condition' => [ 'enable_export' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Structure + responsive behaviour.
	 */
	private function register_settings_section() {

		$this->start_controls_section(
			'section_table_settings',
			[
				'label' => esc_html__( 'Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'table_caption',
			[
				'label'       => esc_html__( 'Caption', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
				'description' => esc_html__( 'Describes the table for screen readers and search engines.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'caption_display',
			[
				'label'     => esc_html__( 'Caption Display', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'visible',
				'options'   => [
					'visible' => esc_html__( 'Visible', 'sky-elementor-addons' ),
					'hidden'  => esc_html__( 'Screen Readers Only', 'sky-elementor-addons' ),
				],
				'condition' => [ 'table_caption!' => '' ],
			]
		);

		$this->add_control(
			'first_column_header',
			[
				'label'        => esc_html__( 'First Column Is A Header', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Renders the leading cell as a row header, so screen readers can announce each row.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'enable_footer',
			[
				'label'        => esc_html__( 'Repeat Head As Footer', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'sticky_header',
			[
				'label'        => esc_html__( 'Sticky Header', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'prefix_class' => 'sa-table-sticky-head-',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'sticky_header_offset',
			[
				'label'     => esc_html__( 'Sticky Offset', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 300,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table' => '--sa-table-sticky-top: {{SIZE}}px;',
				],
				'condition' => [ 'sticky_header' => 'yes' ],
			]
		);

		$this->add_control(
			'sticky_first_column',
			[
				'label'        => esc_html__( 'Sticky First Column', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'prefix_class' => 'sa-table-sticky-col-',
			]
		);

		$this->add_control(
			'show_responsive_scroll_view',
			[
				'label'        => esc_html__( 'Horizontal Scroll View', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'no',
				'prefix_class' => 'sa-table-responsive-scroll-',
				'separator'    => 'before',
				'description'  => esc_html__( 'Scroll the table sideways on small screens instead of stacking it.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'responsive_breakpoint',
			[
				'label'        => esc_html__( 'Switch Below', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'tablet',
				'options'      => [
					'tablet' => esc_html__( 'Tablet (1024px)', 'sky-elementor-addons' ),
					'mobile' => esc_html__( 'Mobile (767px)', 'sky-elementor-addons' ),
					'none'   => esc_html__( 'Never', 'sky-elementor-addons' ),
				],
				'prefix_class' => 'sa-table-switch-',
			]
		);

		$this->add_control(
			'disable_word_wrap',
			[
				'label'        => esc_html__( 'Disable Word Break', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'prefix_class' => 'sa-table-disable-wordwrap-',
				'condition'    => [ 'show_responsive_scroll_view' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	/*
	---------------------------------------------------------------------
	 * Controls — Style
	 * ------------------------------------------------------------------ */

	private function register_table_style() {

		$this->start_controls_section(
			'section_table_style',
			[
				'label' => esc_html__( 'Table', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'table_border',
				'selector' => '{{WRAPPER}} .sa-table',
			]
		);

		$this->add_control(
			'table_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'selectors' => [
					// The scroll wrapper clips the table, so it has to match or the
					// corners get shaved.
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-radius: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .sa-table'       => 'border-radius: {{SIZE}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_control(
			'table_divider_color',
			[
				'label' => esc_html__( 'Divider Color', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'sticky_col_bg',
			[
				'label'       => esc_html__( 'Sticky Column Background', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '#ffffff',
				'description' => esc_html__( 'A sticky column must be opaque, or the rows it scrolls over show through.', 'sky-elementor-addons' ),
				'selectors'   => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-sticky-col-bg: {{VALUE}};',
				],
				'condition'   => [ 'sticky_first_column' => 'yes' ],
			]
		);

		$this->add_control(
			'table_transition',
			[
				'label'      => esc_html__( 'Transition Duration', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 's' ],
				'range'      => [
					's' => [
						'min'  => 0,
						'max'  => 2,
						'step' => .05,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-transition: {{SIZE}}s;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'table_background_color',
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'selector' => '{{WRAPPER}} .sa-table',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'table_box_shadow',
				'selector' => '{{WRAPPER}} .sa-table',
			]
		);

		$this->add_control(
			'_heading_caption',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Caption', 'sky-elementor-addons' ),
				'separator' => 'before',
				'condition' => [
					'table_caption!'  => '',
					'caption_display' => 'visible',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'caption_typography',
				'selector' => '{{WRAPPER}} .sa-table__caption',
				'condition' => [
					'table_caption!'  => '',
					'caption_display' => 'visible',
				],
			]
		);

		$this->add_control(
			'caption_spacing',
			[
				'label' => esc_html__( 'Spacing', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'  => Controls_Manager::SLIDER,
				'default' => [
					'size' => 10,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table__caption' => 'padding-block-end: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'table_caption!'  => '',
					'caption_display' => 'visible',
				],
			]
		);

		$this->add_control(
			'caption_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__caption' => 'color: {{VALUE}}',
				],
				'condition' => [
					'table_caption!'  => '',
					'caption_display' => 'visible',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_head_style() {

		$this->start_controls_section(
			'section_table_head',
			[
				'label' => esc_html__( 'Table Head', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'table_head_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-head-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'head_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-head-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'head_nowrap',
			[
				'label'   => esc_html__( 'Title Wrapping', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'nowrap',
				'options' => [
					'nowrap' => esc_html__( 'Single Line', 'sky-elementor-addons' ),
					'normal' => esc_html__( 'Allow Wrapping', 'sky-elementor-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-head-wrap: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'head_border',
				'selector' => '{{WRAPPER}} .sa-table .sa-table__head-column-cell',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'head_background_color',
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'selector' => '{{WRAPPER}} .sa-table .sa-table__head-column-cell',
			]
		);

		$this->add_control(
			'_heading_title',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Title', 'sky-elementor-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'head_typography',
				'selector' => '{{WRAPPER}} .sa-table .sa-table__head-column-cell-text',
				'global'   => [ 'default' => Global_Typography::TYPOGRAPHY_TEXT ],
			]
		);

		$this->add_control(
			'head_text_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table .sa-table__head-column-cell-wrap' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'head_sort_color',
			[
				'label'     => esc_html__( 'Sort Indicator Color', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-sort-color: {{VALUE}};',
				],
				'condition' => [ 'enable_sorting' => 'yes' ],
			]
		);

		$this->add_control(
			'head_sort_size',
			[
				'label'     => esc_html__( 'Sort Indicator Size', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 4,
						'max' => 20,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-sort-size: {{SIZE}}px;',
				],
				'condition' => [ 'enable_sorting' => 'yes' ],
			]
		);

		$this->add_control(
			'head_sort_opacity',
			[
				'label'     => esc_html__( 'Sort Indicator Idle Opacity', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => .05,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-sort-opacity: {{SIZE}};',
				],
				'condition' => [ 'enable_sorting' => 'yes' ],
			]
		);

		$this->add_control(
			'_heading_icon',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Icon/Image', 'sky-elementor-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'head_icon_gap',
			[
				'label' => esc_html__( 'Gap From Title', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-head-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_spacing',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-table .sa-table__head-column-cell-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'head_icon',
			[
				'label' => esc_html__( 'Icon Size', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .sa-table .sa-table__head-column-cell-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .sa-table .sa-table__head-column-cell-icon svg' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .sa-table .sa-table__head-column-cell-icon img' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'column_image_border_radius',
			[
				'label' => esc_html__( 'Image Border Radius', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .sa-table .sa-table__head-column-cell-icon img' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'head_icon_color',
			[
				'label' => esc_html__( 'Icon Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__head-column-cell-icon i' => 'color: {{VALUE}}',
					'{{WRAPPER}} .sa-table__head-column-cell-icon svg' => 'fill: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'column_color_notice',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => esc_html__( 'A per-column Icon Color set in the repeater overrides this.', 'sky-elementor-addons' ),
			]
		);

		$this->end_controls_section();
	}

	private function register_row_style() {

		$this->start_controls_section(
			'section_table_row_style',
			[
				'label' => esc_html__( 'Table Row', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'table_row_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default'    => [
					'top'    => 10,
					'right'  => 10,
					'bottom' => 10,
					'left'   => 10,
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-cell-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'row_icon_gap',
			[
				'label' => esc_html__( 'Icon Gap From Text', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-row-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'row_border',
				'selector' => '{{WRAPPER}} .sa-table__body .sa-table__body-row-cell',
			]
		);

		$this->start_controls_tabs( '_tabs_rows' );

		$this->start_controls_tab(
			'_tab_head_row',
			[ 'label' => esc_html__( 'Normal', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'row_background_color_odd',
			[
				'label' => esc_html__( 'Background Color (Odd)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table' => '--sa-table-row-bg-odd: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'row_background_color_even',
			[
				'label' => esc_html__( 'Background Color (Even)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table' => '--sa-table-row-bg-even: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'row_color_odd',
			[
				'label' => esc_html__( 'Color (Odd)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table' => '--sa-table-row-color-odd: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'row_color_even',
			[
				'label' => esc_html__( 'Color (Even)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table' => '--sa-table-row-color-even: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'row_text_link_color',
			[
				'label' => esc_html__( 'Link Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__body .sa-table__body-row-cell-text a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'_tab_row',
			[ 'label' => esc_html__( 'Hover', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'row_hover_background_color_odd',
			[
				'label' => esc_html__( 'Background Color (Odd)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table' => '--sa-table-row-bg-odd-hover: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'row_hover_background_color_even',
			[
				'label' => esc_html__( 'Background Color (Even)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table' => '--sa-table-row-bg-even-hover: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'row_hover_color_odd',
			[
				'label' => esc_html__( 'Color (Odd)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table' => '--sa-table-row-color-odd-hover: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'row_hover_color_even',
			[
				'label' => esc_html__( 'Color (Even)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table' => '--sa-table-row-color-even-hover: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'row_text_link_hover_color',
			[
				'label' => esc_html__( 'Link Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__body .sa-table__body-row-cell-text a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'_row_title',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Title', 'sky-elementor-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'row_text_typography',
				'selector' => '{{WRAPPER}} .sa-table__body .sa-table__body-row-cell-text',
				'global'   => [ 'default' => Global_Typography::TYPOGRAPHY_TEXT ],
			]
		);

		$this->add_control(
			'_heading_row_label',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Stacked Label (Mobile)', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'row_label_typography',
				'selector' => '{{WRAPPER}} .sa-table__body-row-cell::before',
			]
		);

		$this->add_control(
			'row_label_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table' => '--sa-table-label-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'_row_icon',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Icon/Image', 'sky-elementor-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'row_icon_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-table__body .sa-table__body-row-cell-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'row_icon_size',
			[
				'label' => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .sa-table__body-row-cell-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .sa-table__body-row-cell-icon img' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .sa-table__body-row-cell-icon svg' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'row_image_fit',
			[
				'label'   => esc_html__( 'Image Fit', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'cover',
				'options' => [
					'cover'      => esc_html__( 'Cover', 'sky-elementor-addons' ),
					'contain'    => esc_html__( 'Contain', 'sky-elementor-addons' ),
					'fill'       => esc_html__( 'Fill', 'sky-elementor-addons' ),
					'scale-down' => esc_html__( 'Scale Down', 'sky-elementor-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-image-fit: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'row_image_border_radius',
			[
				'label' => esc_html__( 'Image Border Radius', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .sa-table__body .sa-table__body-row-cell-icon img' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'row_icon_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__body-row-cell-icon i' => 'color: {{VALUE}}',
					'{{WRAPPER}} .sa-table__body-row-cell-icon svg' => 'fill: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'row_style_notice',
			[
				'type'      => Controls_Manager::RAW_HTML,
				'separator' => 'before',
				'raw'       => esc_html__( 'Per-cell colors set in the repeater override the values above for that cell.', 'sky-elementor-addons' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * The stacked card layout used below the responsive breakpoint. These only
	 * do anything when Horizontal Scroll View is off.
	 */
	private function register_stacked_style() {

		$this->start_controls_section(
			'section_table_stacked_style',
			[
				'label' => esc_html__( 'Stacked (Mobile)', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_responsive_scroll_view!' => 'yes',
					'responsive_breakpoint!'       => 'none',
				],
			]
		);

		$this->add_control(
			'stacked_notice',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => esc_html__( 'These apply below the breakpoint set in the Settings section, where each row becomes a card.', 'sky-elementor-addons' ),
			]
		);

		$this->add_responsive_control(
			'stacked_card_gap',
			[
				'label' => esc_html__( 'Gap Between Cards', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 80,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-card-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'stacked_card_radius',
			[
				'label' => esc_html__( 'Card Radius', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-card-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'stacked_card_border_width',
			[
				'label' => esc_html__( 'Card Border Width', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-card-border-width: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'_heading_stacked_label',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Column Label', 'sky-elementor-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'stacked_label_width',
			[
				'label'      => esc_html__( 'Label Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range'      => [
					'%' => [
						'min' => 15,
						'max' => 70,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-label-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'stacked_label_gap',
			[
				'label' => esc_html__( 'Gap From Value', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-label-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Badge / button / progress / rating cell types.
	 */
	private function register_cell_style() {

		$this->start_controls_section(
			'section_table_cell_style',
			[
				'label' => esc_html__( 'Cell Types', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'cell_accent',
			[
				'label'       => esc_html__( 'Accent Color', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				'description' => esc_html__( 'Base colour for every cell type. Each one can override it below.', 'sky-elementor-addons' ),
				'selectors'   => [
					// On the wrapper, not the table — the toolbar and pagination
					// live outside .sa-table and read this token too.
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-accent: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'_heading_badge',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Badge', 'sky-elementor-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'badge_typography',
				'selector' => '{{WRAPPER}} .sa-table__badge',
			]
		);

		$this->add_control(
			'badge_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-badge-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'badge_bg',
			[
				'label' => esc_html__( 'Background Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-badge-bg: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'badge_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-badge-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'badge_radius',
			[
				'label' => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-badge-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'_heading_button',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Button', 'sky-elementor-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'cell_button_typography',
				'selector' => '{{WRAPPER}} .sa-table__btn',
			]
		);

		$this->add_responsive_control(
			'cell_button_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-btn-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'cell_button_radius',
			[
				'label' => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-btn-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( '_tabs_cell_button' );

		$this->start_controls_tab(
			'_tab_cell_button_normal',
			[ 'label' => esc_html__( 'Normal', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'cell_button_text_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-btn-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cell_button_bg',
			[
				'label' => esc_html__( 'Background Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-btn-bg: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'_tab_cell_button_hover',
			[ 'label' => esc_html__( 'Hover', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'cell_button_text_color_hover',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-btn-color-hover: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cell_button_bg_hover',
			[
				'label' => esc_html__( 'Background Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-btn-bg-hover: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'_heading_progress',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Progress Bar', 'sky-elementor-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'progress_bar_color',
			[
				'label' => esc_html__( 'Bar Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-progress-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'progress_track_color',
			[
				'label' => esc_html__( 'Track Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-progress-track: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'progress_height',
			[
				'label' => esc_html__( 'Height', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 2,
						'max' => 40,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-progress-height: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'progress_width',
			[
				'label'      => esc_html__( 'Minimum Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 40,
						'max' => 400,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-progress-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'progress_radius',
			[
				'label' => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-progress-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'progress_label_typography',
				'label'    => esc_html__( 'Label Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-table__progress-label',
			]
		);

		$this->add_control(
			'progress_label_color',
			[
				'label' => esc_html__( 'Label Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__progress-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'_heading_rating',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Star Rating', 'sky-elementor-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'rating_size',
			[
				'label' => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 8,
						'max' => 48,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table__rating' => 'font-size: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'rating_spacing',
			[
				'label' => esc_html__( 'Star Spacing', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 20,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-rating-spacing: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'rating_fill_color',
			[
				'label' => esc_html__( 'Filled Star Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-rating-fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'rating_track_color',
			[
				'label' => esc_html__( 'Empty Star Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-rating-track: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Search box, page-size selector, export buttons, pagination, info line.
	 */
	private function register_toolbar_style() {

		$this->start_controls_section(
			'section_table_toolbar_style',
			[
				'label' => esc_html__( 'Toolbar & Pagination', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'  => 'enable_search',
							'value' => 'yes',
						],
						[
							'name'  => 'enable_pagination',
							'value' => 'yes',
						],
						[
							'name'  => 'enable_export',
							'value' => 'yes',
						],
					],
				],
			]
		);

		$this->add_responsive_control(
			'toolbar_spacing',
			[
				'label' => esc_html__( 'Spacing From Table', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 80,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-gap: {{SIZE}}px; --sa-table-gap-row: {{SIZE}}px;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'toolbar_typography',
				'selector' => '{{WRAPPER}} .sa-table__toolbar, {{WRAPPER}} .sa-table__footer',
			]
		);

		$this->add_control(
			'toolbar_text_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-ui-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'_heading_toolbar_controls',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Search, Select & Buttons', 'sky-elementor-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'toolbar_control_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-ui-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'toolbar_control_radius',
			[
				'label' => esc_html__( 'Radius', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-ui-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'toolbar_search_width',
			[
				'label'      => esc_html__( 'Search Width', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 100,
						'max' => 600,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-search-width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'enable_search' => 'yes' ],
			]
		);

		$this->start_controls_tabs( '_tabs_toolbar_controls' );

		$this->start_controls_tab(
			'_tab_toolbar_normal',
			[ 'label' => esc_html__( 'Normal', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'toolbar_control_bg',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-ui-bg: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'toolbar_control_border',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-ui-border: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'_tab_toolbar_hover',
			[ 'label' => esc_html__( 'Hover', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'toolbar_hover_bg',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-ui-hover-bg: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'toolbar_hover_border',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-ui-hover-border: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'toolbar_hover_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-ui-hover-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'_heading_pagination',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Pagination', 'sky-elementor-addons' ),
				'separator' => 'before',
				'condition' => [ 'enable_pagination' => 'yes' ],
			]
		);

		$this->add_control(
			'pagination_gap',
			[
				'label'     => esc_html__( 'Gap Between Buttons', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 40,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-page-gap: {{SIZE}}px;',
				],
				'condition' => [ 'enable_pagination' => 'yes' ],
			]
		);

		$this->add_control(
			'pagination_min_width',
			[
				'label'     => esc_html__( 'Button Minimum Width', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-page-min: {{SIZE}}px;',
				],
				'condition' => [ 'enable_pagination' => 'yes' ],
			]
		);

		$this->add_control(
			'pagination_active_bg',
			[
				'label'     => esc_html__( 'Active Background', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-page-active-bg: {{VALUE}};',
				],
				'condition' => [ 'enable_pagination' => 'yes' ],
			]
		);

		$this->add_control(
			'pagination_active_color',
			[
				'label'     => esc_html__( 'Active Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-page-active-color: {{VALUE}};',
				],
				'condition' => [ 'enable_pagination' => 'yes' ],
			]
		);

		$this->add_control(
			'_heading_info',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Entries Info & Empty State', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'info_typography',
				'selector' => '{{WRAPPER}} .sa-table__info, {{WRAPPER}} .sa-table__empty td',
			]
		);

		$this->add_control(
			'info_color',
			[
				'label' => esc_html__( 'Entries Info Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__info' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'empty_color',
			[
				'label' => esc_html__( 'No Results Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-empty-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'empty_padding',
			[
				'label'      => esc_html__( 'No Results Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-table__wrap' => '--sa-table-empty-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/*
	---------------------------------------------------------------------
	 * Render
	 * ------------------------------------------------------------------ */

	protected function render() {

		$settings = $this->get_settings_for_display();
		$matrix   = $this->get_matrix( $settings );

		if ( empty( $matrix['head'] ) && empty( $matrix['body'] ) ) {
			return;
		}

		$this->column_labels = $this->build_column_labels( $matrix['head'] );

		$this->add_render_attribute(
			'wrap',
			[
				'class'         => 'sa-table__wrap',
				'data-settings' => wp_json_encode( $this->get_js_settings( $settings ) ),
			]
		);
		?>
		<div <?php $this->print_render_attribute_string( 'wrap' ); ?>>
			<?php $this->render_toolbar( $settings ); ?>

			<div class="sa-table__scroll">
				<table class="sa-table"<?php echo $this->is_stacked( $settings ) ? ' role="table"' : ''; ?>>
					<?php $this->render_caption( $settings ); ?>
					<?php $this->render_head( $matrix['head'], $settings ); ?>
					<?php $this->render_body( $matrix['body'], $settings ); ?>
					<?php $this->render_foot( $matrix['head'], $settings ); ?>
				</table>
			</div>

			<?php $this->render_footer_bar( $settings ); ?>
		</div>
		<?php
	}

	/**
	 * Normalise whichever source is active into one shape:
	 * `[ 'head' => [ cell, … ], 'body' => [ [ cell, … ], … ] ]`.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	private function get_matrix( $settings ) {
		if ( 'repeater' !== $settings['table_source'] ) {
			return $this->matrix_from_remote( Data_Source::get( $settings ) );
		}

		return [
			'head' => $this->head_from_repeater( $settings ),
			'body' => $this->body_from_repeater( $settings ),
		];
	}

	/**
	 * Wrap the flat strings a remote source returns in the same cell shape the
	 * repeater produces, so one renderer serves both.
	 *
	 * @param array $data Head/body string matrix.
	 * @return array
	 */
	private function matrix_from_remote( $data ) {
		$head = [];
		$body = [];

		foreach ( $data['head'] as $cell ) {
			$head[] = $this->make_cell( $this->normalise_cell( $cell ) );
		}

		foreach ( $data['body'] as $row ) {
			$cells = [];

			foreach ( $row as $cell ) {
				$cells[] = $this->make_cell( $this->normalise_cell( $cell ) );
			}

			$body[] = $cells;
		}

		return [
			'head' => $head,
			'body' => $body,
		];
	}

	/**
	 * A source may hand back a bare string (CSV) or an array carrying spans
	 * (HTML rows). Accept both.
	 *
	 * @param string|array $cell Source cell.
	 * @return array
	 */
	private function normalise_cell( $cell ) {
		if ( ! is_array( $cell ) ) {
			return [ 'label' => (string) $cell ];
		}

		return [
			'label'   => isset( $cell['label'] ) ? (string) $cell['label'] : '',
			'colspan' => isset( $cell['colspan'] ) ? (int) $cell['colspan'] : 0,
			'rowspan' => isset( $cell['rowspan'] ) ? (int) $cell['rowspan'] : 0,
		];
	}

	/**
	 * Starter markup for the HTML Rows source — a working table the moment the
	 * source is picked, and a worked example of the accepted shape.
	 *
	 * @return string
	 */
	private function default_html_rows() {
		return "<tr>\n"
			. "\t<th>" . esc_html__( 'Plan', 'sky-elementor-addons' ) . "</th>\n"
			. "\t<th>" . esc_html__( 'Storage', 'sky-elementor-addons' ) . "</th>\n"
			. "\t<th>" . esc_html__( 'Price', 'sky-elementor-addons' ) . "</th>\n"
			. "</tr>\n"
			. "<tr>\n"
			. "\t<td><strong>" . esc_html__( 'Starter', 'sky-elementor-addons' ) . "</strong></td>\n"
			. "\t<td>10 GB</td>\n"
			. "\t<td>\$9</td>\n"
			. "</tr>\n"
			. "<tr>\n"
			. "\t<td><strong>" . esc_html__( 'Growth', 'sky-elementor-addons' ) . "</strong></td>\n"
			. "\t<td>100 GB</td>\n"
			. "\t<td>\$29</td>\n"
			. "</tr>\n"
			. "<tr>\n"
			. "\t<td><strong>" . esc_html__( 'Business', 'sky-elementor-addons' ) . "</strong></td>\n"
			. "\t<td>1 TB</td>\n"
			. "\t<td>\$79</td>\n"
			. "</tr>\n"
			. "<tr>\n"
			. "\t<td><strong>" . esc_html__( 'Enterprise', 'sky-elementor-addons' ) . "</strong></td>\n"
			. "\t<td>" . esc_html__( 'Unlimited', 'sky-elementor-addons' ) . "</td>\n"
			. "\t<td>" . esc_html__( 'Contact us', 'sky-elementor-addons' ) . "</td>\n"
			. '</tr>';
	}

	/**
	 * Column cells from the head repeater.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	private function head_from_repeater( $settings ) {
		$head = [];

		foreach ( (array) $settings['columns_data'] as $index => $column ) {
			$head[] = $this->make_cell(
				[
					'label'    => $this->setting( $column, 'column_name' ),
					'colspan'  => (int) $this->setting( $column, 'column_span', 0 ),
					'media'    => $this->setting( $column, 'column_media', 'none' ),
					'icons'    => $this->setting( $column, 'column_icons', [] ),
					'image'    => $this->setting( $column, 'column_image', [] ),
					'thumb'    => $column,
					'thumb_id' => 'column_thumbnail',
					'image_id' => 'column_image',
					'sortable' => 'no' !== $this->setting( $column, 'column_sortable', 'yes' ),
					'item_id'  => $this->setting( $column, '_id' ),
					'index'    => $index,
				]
			);
		}

		return $head;
	}

	/**
	 * Body rows from the flat row repeater.
	 *
	 * A `row` item opens a row; `column` items fill it. Single pass, indexed by
	 * row number — no ID matching, no nested rescans.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	private function body_from_repeater( $settings ) {
		$rows  = [];
		$index = -1;

		foreach ( (array) $settings['rows_data'] as $item ) {
			if ( 'row' === $this->setting( $item, 'row_column_type', 'row' ) ) {
				++$index;
				$rows[ $index ] = [];
				continue;
			}

			// A cell before the first "Row Starts" marker still needs a home.
			if ( $index < 0 ) {
				$index          = 0;
				$rows[ $index ] = [];
			}

			$rows[ $index ][] = $this->make_cell(
				[
					'label'    => $this->setting( $item, 'cell_name' ),
					'type'     => $this->setting( $item, 'cell_type', 'text' ),
					'value'    => (float) $this->setting( $item, 'cell_value', 0 ),
					'link'     => $this->setting( $item, 'cell_link', [] ),
					'colspan'  => (int) $this->setting( $item, 'row_column_span', 0 ),
					'rowspan'  => (int) $this->setting( $item, 'row_span', 0 ),
					'media'    => $this->setting( $item, 'row_media', 'none' ),
					'icons'    => $this->setting( $item, 'row_icons', [] ),
					'image'    => $this->setting( $item, 'row_image', [] ),
					'thumb'    => $item,
					'thumb_id' => 'row_thumbnail',
					'image_id' => 'row_image',
					'item_id'  => $this->setting( $item, '_id' ),
				]
			);
		}

		// Drop only a dangling trailing "Row Starts" marker. Filtering the whole
		// set would silently delete deliberate empty rows in the middle.
		while ( ! empty( $rows ) && empty( end( $rows ) ) ) {
			array_pop( $rows );
		}

		return array_values( $rows );
	}

	/**
	 * One cell shape, so head/body/remote all render through the same helpers.
	 *
	 * @param array $cell Partial cell.
	 * @return array
	 */
	private function make_cell( $cell ) {
		return wp_parse_args(
			$cell,
			[
				'label'    => '',
				'type'     => 'text',
				'value'    => 0,
				'link'     => [],
				'colspan'  => 0,
				'rowspan'  => 0,
				'media'    => 'none',
				'icons'    => [],
				'image'    => [],
				'thumb'    => [],
				'thumb_id' => '',
				'image_id' => '',
				'sortable' => true,
				'item_id'  => '',
				'index'    => 0,
			]
		);
	}

	/* ------------------------------------------------------------------ */

	private function render_caption( $settings ) {
		if ( '' === $settings['table_caption'] ) {
			return;
		}

		$classes = 'sa-table__caption';

		if ( 'hidden' === $settings['caption_display'] ) {
			$classes .= ' sa--screen-reader-text';
		}
		?>
		<caption class="<?php echo esc_attr( $classes ); ?>"><?php echo wp_kses_post( $settings['table_caption'] ); ?></caption>
		<?php
	}

	/**
	 * @param array $head     Head cells.
	 * @param array $settings Widget settings.
	 */
	private function render_head( $head, $settings ) {
		if ( empty( $head ) ) {
			return;
		}

		$stacked  = $this->is_stacked( $settings );
		$sortable = 'yes' === $settings['enable_sorting'];
		$column   = 0;
		?>
		<thead class="sa-table__head"<?php echo $stacked ? ' role="rowgroup"' : ''; ?>>
			<tr class="sa-table__head-column"<?php echo $stacked ? ' role="row"' : ''; ?>>
				<?php
				foreach ( $head as $cell ) {
					$this->render_head_cell( $cell, $column, $sortable, $stacked );
					$column += max( 1, $cell['colspan'] );
				}
				?>
			</tr>
		</thead>
		<?php
	}

	/**
	 * @param array $cell     Head cell.
	 * @param int   $column   Column index.
	 * @param bool  $sortable Sorting enabled widget-wide.
	 * @param bool  $stacked  Stacked responsive mode active.
	 */
	private function render_head_cell( $cell, $column, $sortable, $stacked ) {
		$key = 'head_cell_' . $column;

		$this->add_render_attribute(
			$key,
			[
				'class'       => $this->cell_classes( 'sa-table__head-column-cell', $cell ),
				'scope'       => 'col',
				'data-column' => (string) $column,
			]
		);

		if ( $cell['colspan'] > 1 ) {
			$this->add_render_attribute( $key, 'colspan', (string) $cell['colspan'] );
		}

		if ( $stacked ) {
			$this->add_render_attribute( $key, 'role', 'columnheader' );
		}

		$is_sortable = $sortable && $cell['sortable'] && $cell['colspan'] < 2;

		if ( $is_sortable ) {
			$this->add_render_attribute( $key, 'aria-sort', 'none' );
		}
		?>
		<th <?php $this->print_render_attribute_string( $key ); ?>>
			<?php if ( $is_sortable ) : ?>
				<button type="button" class="sa-table__sort">
					<?php $this->render_head_content( $cell ); ?>
					<span class="sa-table__sort-icon" aria-hidden="true"></span>
					<span class="sa--screen-reader-text"><?php echo esc_html__( 'Sort by this column', 'sky-elementor-addons' ); ?></span>
				</button>
			<?php else : ?>
				<?php $this->render_head_content( $cell ); ?>
			<?php endif; ?>
		</th>
		<?php
	}

	private function render_head_content( $cell ) {
		?>
		<span class="sa-table__head-column-cell-wrap">
			<span class="sa-table__head-column-cell-text"><?php echo wp_kses_post( $cell['label'] ); ?></span>
			<?php $this->render_media( $cell, 'sa-table__head-column-cell-icon' ); ?>
		</span>
		<?php
	}

	/**
	 * @param array $body     Body rows.
	 * @param array $settings Widget settings.
	 */
	private function render_body( $body, $settings ) {
		$stacked = $this->is_stacked( $settings );
		$body    = $this->apply_column_index( $body );
		$managed = $this->is_managed( $body, $settings );

		// Sort and paginate server-side so the first paint is already the final
		// view. Doing it in JS instead paints every row, then collapses to page
		// one — a visible jump on any sizeable table.
		if ( $managed ) {
			$body = $this->sort_rows( $body, $settings );
		}

		$per_page = $managed && 'yes' === $settings['enable_pagination'] ? max( 1, absint( $settings['rows_per_page'] ) ) : 0;
		?>
		<tbody class="sa-table__body<?php echo $managed ? ' sa-table__body--managed' : ''; ?>"<?php echo $stacked ? ' role="rowgroup"' : ''; ?>>
			<?php foreach ( $body as $row_index => $cells ) : ?>
				<tr class="<?php echo esc_attr( $this->row_classes( $row_index, $per_page ) ); ?>"<?php echo $stacked ? ' role="row"' : ''; ?>>
					<?php
					foreach ( $cells as $cell_index => $cell ) {
						$this->render_body_cell(
							$cell,
							'body_cell_' . $row_index . '_' . $cell_index,
							$cell['column'],
							0 === $cell_index,
							$settings,
							$stacked
						);
					}
					?>
				</tr>
			<?php endforeach; ?>
		</tbody>
		<?php

		if ( $per_page > 0 && count( $body ) > $per_page ) {
			// Without JS the pre-hidden rows would stay hidden forever.
			echo '<noscript><style>.sa-table__body-row--hidden{display:table-row}</style></noscript>';
		}
	}

	/**
	 * Row classes, including the first-page window and its zebra parity.
	 *
	 * @param int $row_index Row position.
	 * @param int $per_page  Rows per page, or 0 when pagination is off.
	 * @return string
	 */
	private function row_classes( $row_index, $per_page ) {
		$classes = [ 'sa-table__body-row' ];

		if ( $per_page > 0 && $row_index >= $per_page ) {
			$classes[] = 'sa-table__body-row--hidden';

			return implode( ' ', $classes );
		}

		$classes[] = 0 === $row_index % 2 ? 'sa-table__body-row--odd' : 'sa-table__body-row--even';

		return implode( ' ', $classes );
	}

	/**
	 * Stamp each cell with its resolved column index, so rows can be reordered
	 * afterwards without invalidating a positional lookup table.
	 *
	 * @param array $body Body rows.
	 * @return array
	 */
	private function apply_column_index( $body ) {
		$map = $this->column_index_map( $body );

		foreach ( $body as $row_index => $cells ) {
			foreach ( $cells as $cell_index => $cell ) {
				$body[ $row_index ][ $cell_index ]['column'] = isset( $map[ $row_index ][ $cell_index ] )
					? $map[ $row_index ][ $cell_index ]
					: $cell_index;
			}
		}

		return $body;
	}

	/**
	 * Interactive features need rows that can be reordered and hidden freely. A
	 * rowspan makes a row inseparable from its neighbour, so the whole table
	 * falls back to a plain static render.
	 *
	 * @param array $body     Body rows.
	 * @param array $settings Widget settings.
	 * @return bool
	 */
	private function is_managed( $body, $settings ) {
		$enabled = 'yes' === $settings['enable_sorting']
			|| 'yes' === $settings['enable_search']
			|| 'yes' === $settings['enable_pagination'];

		if ( ! $enabled ) {
			return false;
		}

		foreach ( $body as $cells ) {
			foreach ( $cells as $cell ) {
				if ( $cell['rowspan'] > 1 ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Apply the configured default sort, using the same comparison rule the JS
	 * uses, so the server's first page matches what a click would produce.
	 *
	 * @param array $body     Body rows.
	 * @param array $settings Widget settings.
	 * @return array
	 */
	private function sort_rows( $body, $settings ) {
		if ( 'yes' !== $settings['enable_sorting'] || '' === $settings['default_sort_column'] ) {
			return $body;
		}

		$column     = absint( $settings['default_sort_column'] ) - 1;
		$descending = 'desc' === $settings['default_sort_order'];
		$keys       = [];

		foreach ( $body as $row_index => $cells ) {
			$keys[ $row_index ] = $this->row_sort_key( $cells, $column );
		}

		// Sort the keys, not the rows — equal values keep their original order.
		uasort(
			$keys,
			function ( $a, $b ) use ( $descending ) {
				$result = ( is_numeric( $a ) && is_numeric( $b ) )
					? ( (float) $a <=> (float) $b )
					: strcmp( (string) $a, (string) $b );

				return $descending ? -$result : $result;
			}
		);

		$sorted = [];

		foreach ( array_keys( $keys ) as $row_index ) {
			$sorted[] = $body[ $row_index ];
		}

		return $sorted;
	}

	/**
	 * The sort value of whichever cell occupies the given column in this row.
	 *
	 * @param array $cells  Row cells.
	 * @param int   $column Column index.
	 * @return string
	 */
	private function row_sort_key( $cells, $column ) {
		foreach ( $cells as $cell ) {
			if ( $cell['column'] === $column ) {
				return $this->sort_value( $cell );
			}
		}

		return '';
	}

	/**
	 * @param array  $cell     Body cell.
	 * @param string $key      Stable render-attribute key for this cell.
	 * @param int    $column   Resolved column index (colspan/rowspan aware).
	 * @param bool   $is_first First cell in the row.
	 * @param array  $settings Widget settings.
	 * @param bool   $stacked  Stacked responsive mode active.
	 */
	private function render_body_cell( $cell, $key, $column, $is_first, $settings, $stacked ) {
		$is_header = $is_first && 'yes' === $settings['first_column_header'];
		$tag       = $is_header ? 'th' : 'td';
		$label     = isset( $this->column_labels[ $column ] ) ? $this->column_labels[ $column ] : '';

		$this->add_render_attribute(
			$key,
			[
				'class'       => $this->cell_classes( 'sa-table__body-row-cell', $cell ),
				'data-column' => (string) $column,
				'data-sort'   => $this->sort_value( $cell ),
			]
		);

		if ( '' !== $label ) {
			$this->add_render_attribute( $key, 'data-label', $label );
		}

		if ( $cell['colspan'] > 1 ) {
			$this->add_render_attribute( $key, 'colspan', (string) $cell['colspan'] );
		}

		if ( $cell['rowspan'] > 1 ) {
			$this->add_render_attribute( $key, 'rowspan', (string) $cell['rowspan'] );
		}

		if ( $is_header ) {
			$this->add_render_attribute( $key, 'scope', 'row' );
		}

		if ( $stacked ) {
			$this->add_render_attribute( $key, 'role', $is_header ? 'rowheader' : 'cell' );
		}
		?>
		<<?php echo esc_html( $tag ); ?> <?php $this->print_render_attribute_string( $key ); ?>>
			<span class="sa-table__body-row-cell-wrap">
				<?php $this->render_cell_content( $cell, $key ); ?>
				<?php $this->render_media( $cell, 'sa-table__body-row-cell-icon' ); ?>
			</span>
		</<?php echo esc_html( $tag ); ?>>
		<?php
	}

	/**
	 * Dispatch on cell type. Each branch stays small on purpose.
	 *
	 * @param array  $cell Body cell.
	 * @param string $key  Unique render-attribute key for this cell.
	 */
	private function render_cell_content( $cell, $key ) {
		switch ( $cell['type'] ) {
			case 'progress':
				$this->render_progress( $cell );
				return;

			case 'rating':
				$this->render_rating( $cell );
				return;

			case 'button':
				$this->render_button( $cell, $key );
				return;

			case 'badge':
				?>
				<span class="sa-table__body-row-cell-text">
					<span class="sa-table__badge"><?php echo wp_kses_post( $cell['label'] ); ?></span>
				</span>
				<?php
				return;
		}
		?>
		<span class="sa-table__body-row-cell-text">
			<?php if ( ! empty( $cell['link']['url'] ) ) : ?>
				<?php $this->add_link_attributes( $key . '_link', $cell['link'] ); ?>
				<a <?php $this->print_render_attribute_string( $key . '_link' ); ?>><?php echo wp_kses_post( $cell['label'] ); ?></a>
			<?php else : ?>
				<?php echo wp_kses_post( $cell['label'] ); ?>
			<?php endif; ?>
		</span>
		<?php
	}

	private function render_progress( $cell ) {
		$value = max( 0, min( 100, (float) $cell['value'] ) );
		?>
		<span class="sa-table__body-row-cell-text">
			<span class="sa-table__progress" role="progressbar" aria-valuenow="<?php echo esc_attr( $value ); ?>" aria-valuemin="0" aria-valuemax="100">
				<span class="sa-table__progress-bar" style="width:<?php echo esc_attr( $value ); ?>%"></span>
			</span>
			<span class="sa-table__progress-label"><?php echo esc_html( '' !== $cell['label'] ? $cell['label'] : $value . '%' ); ?></span>
		</span>
		<?php
	}

	private function render_rating( $cell ) {
		$value = max( 0, min( 5, (float) $cell['value'] ) );
		$text  = sprintf(
			/* translators: %s: rating value out of five. */
			esc_html__( '%s out of 5 stars', 'sky-elementor-addons' ),
			$value
		);
		?>
		<span class="sa-table__body-row-cell-text">
			<span class="sa-table__rating" role="img" aria-label="<?php echo esc_attr( $text ); ?>">
				<span class="sa-table__rating-fill" style="width:<?php echo esc_attr( $value * 20 ); ?>%"></span>
			</span>
		</span>
		<?php
	}

	private function render_button( $cell, $key ) {
		if ( empty( $cell['link']['url'] ) ) {
			?>
			<span class="sa-table__body-row-cell-text">
				<span class="sa-table__btn"><?php echo wp_kses_post( $cell['label'] ); ?></span>
			</span>
			<?php
			return;
		}

		$this->add_link_attributes( $key . '_btn', $cell['link'] );
		$this->add_render_attribute( $key . '_btn', 'class', 'sa-table__btn' );
		?>
		<span class="sa-table__body-row-cell-text">
			<a <?php $this->print_render_attribute_string( $key . '_btn' ); ?>><?php echo wp_kses_post( $cell['label'] ); ?></a>
		</span>
		<?php
	}

	/**
	 * Icon or image — gated on the cell's media choice, so a placeholder default
	 * never leaks into a cell the user set to "None".
	 *
	 * @param array  $cell  Cell.
	 * @param string $class Wrapper class.
	 */
	private function render_media( $cell, $class ) {
		if ( 'icon' === $cell['media'] && ! empty( $cell['icons']['value'] ) ) {
			?>
			<span class="<?php echo esc_attr( $class ); ?>">
				<?php Icons_Manager::render_icon( $cell['icons'], [ 'aria-hidden' => 'true' ] ); ?>
			</span>
			<?php
			return;
		}

		if ( 'image' !== $cell['media'] || empty( $cell['image']['url'] ) ) {
			return;
		}

		$image_html = '';

		if ( ! empty( $cell['thumb_id'] ) && ! empty( $cell['image']['id'] ) ) {
			$image_html = Group_Control_Image_Size::get_attachment_image_html( $cell['thumb'], $cell['thumb_id'], $cell['image_id'] );
		}

		if ( '' === $image_html ) {
			$image_html = sprintf(
				'<img src="%1$s" alt="%2$s" />',
				esc_url( $cell['image']['url'] ),
				esc_attr( Control_Media::get_image_alt( $cell['image'] ) )
			);
		}
		// An explicit allowlist rather than wp_kses_post(), which drops srcset,
		// sizes, loading and decoding on older WordPress versions.
		$allowed = [
			'img' => [
				'src'      => true,
				'srcset'   => true,
				'sizes'    => true,
				'alt'      => true,
				'title'    => true,
				'class'    => true,
				'width'    => true,
				'height'   => true,
				'loading'  => true,
				'decoding' => true,
			],
		];
		?>
		<span class="<?php echo esc_attr( $class ); ?>"><?php echo wp_kses( $image_html, $allowed ); ?></span>
		<?php
	}

	/**
	 * Head repeated as a footer — useful on long tables.
	 *
	 * @param array $head     Head cells.
	 * @param array $settings Widget settings.
	 */
	private function render_foot( $head, $settings ) {
		if ( 'yes' !== $settings['enable_footer'] || empty( $head ) ) {
			return;
		}
		?>
		<tfoot class="sa-table__foot">
			<tr class="sa-table__head-column">
				<?php foreach ( $head as $cell ) : ?>
					<th class="sa-table__head-column-cell" scope="col"<?php echo $cell['colspan'] > 1 ? ' colspan="' . esc_attr( $cell['colspan'] ) . '"' : ''; ?>>
						<span class="sa-table__head-column-cell-wrap">
							<span class="sa-table__head-column-cell-text"><?php echo wp_kses_post( $cell['label'] ); ?></span>
						</span>
					</th>
				<?php endforeach; ?>
			</tr>
		</tfoot>
		<?php
	}

	/**
	 * Search / page-size / export. PHP owns this markup; JS only wires it up.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_toolbar( $settings ) {
		$has_search = 'yes' === $settings['enable_search'];
		$has_length = 'yes' === $settings['enable_pagination'] && '' !== trim( $settings['page_size_options'] );
		$has_export = 'yes' === $settings['enable_export'] && ! empty( $settings['export_buttons'] );

		if ( ! $has_search && ! $has_length && ! $has_export ) {
			return;
		}
		?>
		<div class="sa-table__toolbar">
			<?php if ( $has_length ) : ?>
				<label class="sa-table__length">
					<span class="sa-table__length-text"><?php echo esc_html__( 'Show', 'sky-elementor-addons' ); ?></span>
					<select class="sa-table__length-select">
						<?php foreach ( $this->page_sizes( $settings ) as $size ) : ?>
							<option value="<?php echo esc_attr( $size ); ?>" <?php selected( (int) $settings['rows_per_page'], $size ); ?>><?php echo esc_html( $size ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			<?php endif; ?>

			<?php if ( $has_export ) : ?>
				<div class="sa-table__export">
					<?php foreach ( $this->export_labels() as $action => $label ) : ?>
						<?php if ( in_array( $action, (array) $settings['export_buttons'], true ) ) : ?>
							<button type="button" class="sa-table__export-btn" data-export="<?php echo esc_attr( $action ); ?>"><?php echo esc_html( $label ); ?></button>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $has_search ) : ?>
				<div class="sa-table__search">
					<input type="search" class="sa-table__search-input" placeholder="<?php echo esc_attr( $settings['search_placeholder'] ); ?>" aria-label="<?php echo esc_attr__( 'Search table', 'sky-elementor-addons' ); ?>">
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Entries info + the pagination shell. JS fills the page buttons, because
	 * their count depends on the filtered row total.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_footer_bar( $settings ) {
		$has_info = 'yes' === $settings['enable_pagination'] && 'yes' === $settings['show_entries_info'];
		$has_page = 'yes' === $settings['enable_pagination'];

		if ( ! $has_info && ! $has_page ) {
			return;
		}
		?>
		<div class="sa-table__footer">
			<?php if ( $has_info ) : ?>
				<div class="sa-table__info" aria-live="polite"></div>
			<?php endif; ?>

			<?php if ( $has_page ) : ?>
				<nav class="sa-table__pagination" aria-label="<?php echo esc_attr__( 'Table pagination', 'sky-elementor-addons' ); ?>"></nav>
			<?php endif; ?>
		</div>
		<?php
	}

	/*
	---------------------------------------------------------------------
	 * Render helpers
	 * ------------------------------------------------------------------ */

	/**
	 * Resolve every cell's true column index, honouring colspan and rowspan, so
	 * the stacked mobile label always matches its real column.
	 *
	 * @param array $body Body rows.
	 * @return array<int, array<int, int>> row => cell => column index
	 */
	private function column_index_map( $body ) {
		$map     = [];
		$blocked = [];

		foreach ( $body as $row_index => $cells ) {
			$current = $blocked;
			$blocked = [];
			$column  = 0;

			foreach ( $cells as $cell_index => $cell ) {
				while ( isset( $current[ $column ] ) ) {
					++$column;
				}

				$map[ $row_index ][ $cell_index ] = $column;

				$colspan = max( 1, (int) $cell['colspan'] );
				$rowspan = max( 1, (int) $cell['rowspan'] );

				if ( $rowspan > 1 ) {
					for ( $offset = 0; $offset < $colspan; $offset++ ) {
						$blocked[ $column + $offset ] = $rowspan - 1;
					}
				}

				$column += $colspan;
			}

			// Carry columns still spanned by an earlier rowspan into the next row.
			foreach ( $current as $index => $remaining ) {
				if ( $remaining > 1 ) {
					$blocked[ $index ] = $remaining - 1;
				}
			}
		}

		return $map;
	}

	/**
	 * Plain-text column labels for data-label and export headings.
	 *
	 * @param array $head Head cells.
	 * @return string[]
	 */
	private function build_column_labels( $head ) {
		$labels = [];
		$column = 0;

		foreach ( $head as $cell ) {
			$span = max( 1, $cell['colspan'] );

			for ( $offset = 0; $offset < $span; $offset++ ) {
				$labels[ $column + $offset ] = wp_strip_all_tags( $cell['label'] );
			}

			$column += $span;
		}

		return $labels;
	}

	/**
	 * Comparable value for JS sorting. Numeric cells sort numerically; everything
	 * else sorts as lowercase text. Computed server-side so the client never has
	 * to guess a locale or parse markup.
	 *
	 * @param array $cell Body cell.
	 * @return string
	 */
	private function sort_value( $cell ) {
		if ( in_array( $cell['type'], [ 'progress', 'rating' ], true ) ) {
			return (string) $cell['value'];
		}

		$text = trim( wp_strip_all_tags( $cell['label'] ) );

		// Tolerate currency symbols and thousands separators.
		$numeric = preg_replace( '/[^0-9.\-]/', '', str_replace( ',', '', $text ) );

		if ( '' !== $numeric && is_numeric( $numeric ) ) {
			return $numeric;
		}

		return strtolower( $text );
	}

	/**
	 * Config handed to the JS handler.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	private function get_js_settings( $settings ) {
		$sort_column = '' === $settings['default_sort_column'] ? -1 : absint( $settings['default_sort_column'] ) - 1;

		return [
			'sorting'    => 'yes' === $settings['enable_sorting'],
			'sortColumn' => 'yes' === $settings['enable_sorting'] ? $sort_column : -1,
			'sortOrder'  => $settings['default_sort_order'],
			'search'     => 'yes' === $settings['enable_search'],
			'pagination' => 'yes' === $settings['enable_pagination'],
			'perPage'    => max( 1, absint( $settings['rows_per_page'] ) ),
			'info'       => 'yes' === $settings['show_entries_info'],
			'fileName'   => sanitize_title( '' !== $settings['table_caption'] ? $settings['table_caption'] : 'table' ),
			'columns'    => array_values( $this->column_labels ),
			'i18n'       => [
				'info'     => esc_html__( 'Showing {start} to {end} of {total} entries', 'sky-elementor-addons' ),
				'empty'    => esc_html__( 'No matching records found', 'sky-elementor-addons' ),
				'prev'     => esc_html__( 'Previous', 'sky-elementor-addons' ),
				'next'     => esc_html__( 'Next', 'sky-elementor-addons' ),
				'page'     => esc_html__( 'Page {page}', 'sky-elementor-addons' ),
				'sortAsc'  => esc_html__( 'ascending', 'sky-elementor-addons' ),
				'sortDesc' => esc_html__( 'descending', 'sky-elementor-addons' ),
				'copied'   => esc_html__( 'Copied', 'sky-elementor-addons' ),
			],
		];
	}

	/**
	 * Page-size choices, always including the configured default.
	 *
	 * @param array $settings Widget settings.
	 * @return int[]
	 */
	private function page_sizes( $settings ) {
		$sizes   = array_filter( array_map( 'absint', explode( ',', $settings['page_size_options'] ) ) );
		$sizes[] = max( 1, absint( $settings['rows_per_page'] ) );

		$sizes = array_unique( $sizes );
		sort( $sizes );

		return $sizes;
	}

	/**
	 * @return array<string, string>
	 */
	private function export_labels() {
		return [
			'csv'   => esc_html__( 'CSV', 'sky-elementor-addons' ),
			'copy'  => esc_html__( 'Copy', 'sky-elementor-addons' ),
			'print' => esc_html__( 'Print', 'sky-elementor-addons' ),
		];
	}

	/**
	 * Stacked mode swaps the table to flex, which drops native table semantics —
	 * that's when the explicit ARIA roles have to be emitted.
	 *
	 * @param array $settings Widget settings.
	 * @return bool
	 */
	private function is_stacked( $settings ) {
		return 'yes' !== $settings['show_responsive_scroll_view'] && 'none' !== $settings['responsive_breakpoint'];
	}

	/**
	 * Base class plus the repeater-item hook, which only exists for repeater rows —
	 * remote sources have no `_id` and must not get an empty hook class.
	 *
	 * @param string $base Base class.
	 * @param array  $cell Cell.
	 * @return string[]
	 */
	private function cell_classes( $base, $cell ) {
		$classes = [ $base ];

		if ( '' !== $cell['item_id'] ) {
			$classes[] = 'elementor-repeater-item-' . $cell['item_id'];
		}

		return $classes;
	}

	/**
	 * Repeater/array value with a fallback — legacy rows saved before a control
	 * existed simply fall back instead of raising a notice.
	 *
	 * @param array  $source   Settings array.
	 * @param string $key      Key.
	 * @param mixed  $fallback Default.
	 * @return mixed
	 */
	private function setting( $source, $key, $fallback = '' ) {
		return isset( $source[ $key ] ) && '' !== $source[ $key ] ? $source[ $key ] : $fallback;
	}
}
