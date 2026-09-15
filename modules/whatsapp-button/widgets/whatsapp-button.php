<?php

namespace Sky_Addons\Modules\WhatsappButton\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WhatsApp Button.
 *
 * ARCHITECTURE — read before editing.
 *
 * Everything that can be decided on the server is decided on the server: the wa.me
 * links, the pre-filled message placeholders, and the open/closed business-hours
 * state are all resolved in render(). The stylesheet consumes CSS custom properties
 * that the style controls write, so a control can never lose a specificity fight.
 *
 * The one thing PHP cannot own is business hours on a cached page: a full-page cache
 * freezes whatever state was true when the HTML was generated. So render() also
 * prints the schedule and the site UTC offset in data-settings, and the JS re-runs
 * the same comparison on load and flips the state class when the cached markup is
 * stale. PHP and JS share the identical minute-of-week algorithm, and the
 * {next_open} label is built from the stored "H:i" strings rather than a date
 * format, so the two can never disagree on wording.
 *
 * Floating is site furniture but this is a page-scoped widget, so a static guard
 * lets the first floating instance of the request render and turns every later one
 * into an editor-only notice.
 */
class Whatsapp_Button extends Widget_Base {

	/**
	 * True once a floating instance has rendered in this request.
	 *
	 * @var bool
	 */
	private static $floating_rendered = false;

	/**
	 * Everything render() resolved, shared with the partial render methods so they
	 * do not each have to be handed the same six arguments.
	 *
	 * @var array
	 */
	private $view = [];

	/**
	 * Agent rows dropped for having no phone number. Reported in the editor so a
	 * half-filled repeater cannot silently render fewer agents than were added.
	 *
	 * @var int
	 */
	private $skipped_agents = 0;

	/**
	 * Agent rows switched off through the Available toggle. Separate from
	 * $skipped_agents because this one is a deliberate choice, not a mistake.
	 *
	 * @var int
	 */
	private $disabled_agents = 0;

	public function get_name() {
		return 'sky-whatsapp-button';
	}

	public function get_title() {
		return esc_html__( 'WhatsApp Button', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-whatsapp-button';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'whatsapp', 'chat', 'button', 'support', 'contact', 'floating' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'sky-addons-styles' ];
		}

		return [ 'sa-whatsapp-button' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'sky-addons-scripts' ];
		}

		return [ 'sa-whatsapp-button' ];
	}

	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/widgets/whatsapp-button/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	/**
	 * Weekday keys in ISO order. Index is the day number used by the schedule maths.
	 */
	private static function day_options() {
		return [
			'mon' => esc_html__( 'Monday', 'sky-elementor-addons' ),
			'tue' => esc_html__( 'Tuesday', 'sky-elementor-addons' ),
			'wed' => esc_html__( 'Wednesday', 'sky-elementor-addons' ),
			'thu' => esc_html__( 'Thursday', 'sky-elementor-addons' ),
			'fri' => esc_html__( 'Friday', 'sky-elementor-addons' ),
			'sat' => esc_html__( 'Saturday', 'sky-elementor-addons' ),
			'sun' => esc_html__( 'Sunday', 'sky-elementor-addons' ),
		];
	}

	/**
	 * Half-hour options for the open/close selects.
	 *
	 * Elementor has no time control, and a free text field invites "9am" and "21.00".
	 * A select keeps every stored value parseable by both PHP and JS.
	 */
	private static function time_options() {
		$options = [];

		for ( $minutes = 0; $minutes < 1440; $minutes += 30 ) {
			$label             = sprintf( '%02d:%02d', intdiv( $minutes, 60 ), $minutes % 60 );
			$options[ $label ] = $label;
		}

		return $options;
	}

	protected function register_controls() {
		$this->register_whatsapp_controls();
		$this->register_agents_controls();
		$this->register_popup_controls();
		$this->register_hours_controls();
		$this->register_position_controls();
		$this->register_animation_controls();
		$this->register_tracking_controls();

		$this->register_button_style_controls();
		$this->register_popup_style_controls();
		$this->register_tooltip_style_controls();
	}

	private function register_whatsapp_controls() {

		$this->start_controls_section(
			'section_whatsapp',
			[
				'label' => esc_html__( 'WhatsApp Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'button_type',
			[
				'label'        => esc_html__( 'Button Type', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'floating',
				'options'      => [
					'standard' => esc_html__( 'Standard (inline)', 'sky-elementor-addons' ),
					'floating' => esc_html__( 'Floating', 'sky-elementor-addons' ),
				],
				'description'  => esc_html__( 'Only one floating button renders per page. Place it in a header or footer template so it follows the visitor everywhere.', 'sky-elementor-addons' ),
				'prefix_class' => 'sa-wa-type-',
				'render_type'  => 'template',
			]
		);

		$this->add_control(
			'button_style',
			[
				'label'        => esc_html__( 'Button Style', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'icon-text',
				'options'      => [
					'icon'      => esc_html__( 'Icon Only', 'sky-elementor-addons' ),
					'icon-text' => esc_html__( 'Icon + Text', 'sky-elementor-addons' ),
				],
				'condition'    => [ 'button_type' => 'standard' ],
				'prefix_class' => 'sa-wa-style-',
				'render_type'  => 'template',
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'       => esc_html__( 'Button Text', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Chat on WhatsApp', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
				'condition'   => [
					'button_type'  => 'standard',
					'button_style' => 'icon-text',
				],
			]
		);

		$this->add_control(
			'selected_icon',
			[
				'label' => esc_html__( 'Icon', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::ICONS,
				'default' => [
					'value'   => 'fab fa-whatsapp',
					'library' => 'fa-brands',
				],
			]
		);

		$this->add_control(
			'open_in',
			[
				'label'   => esc_html__( 'Open In', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '_blank',
				'options' => [
					'_blank' => esc_html__( 'New Tab', 'sky-elementor-addons' ),
					'_self'  => esc_html__( 'Same Tab', 'sky-elementor-addons' ),
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_agents_controls() {

		$this->start_controls_section(
			'section_agents',
			[
				'label' => esc_html__( 'Agents', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$agent = new Repeater();

		// Deliberately first: turning an agent off should be the obvious move when
		// someone leaves the team for a week, rather than deleting the row and
		// re-typing the number, avatar and hours when they come back.
		$agent->add_control(
			'agent_enabled',
			[
				'label'        => esc_html__( 'Available', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'No', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Off hides the agent from the chat without losing any of their settings.', 'sky-elementor-addons' ),
			]
		);

		$agent->add_control(
			'agent_name',
			[
				'label'       => esc_html__( 'Name', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Support', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$agent->add_control(
			'agent_role',
			[
				'label'       => esc_html__( 'Role', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Customer Care', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$agent->add_control(
			'agent_avatar',
			[
				'label' => esc_html__( 'Avatar', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::MEDIA,
			]
		);

		$agent->add_control(
			'agent_number',
			[
				'label'       => esc_html__( 'WhatsApp Number', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => '8801XXXXXXXXX',
				'description' => esc_html__( 'Country code first, digits only. Spaces, plus signs and dashes are ignored.', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		// Elementor cannot nest a repeater inside a repeater, so a full seven-row
		// schedule per agent is not buildable. One range plus a day multi-select is
		// flat, covers "Sales weekdays, Support weekends", and stays parseable by
		// the same slot maths the widget schedule uses.
		$agent->add_control(
			'agent_custom_hours',
			[
				'label'        => esc_html__( 'Own Hours', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'Off', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Overrides the weekly schedule for this agent. Off means the agent follows it.', 'sky-elementor-addons' ),
				'separator'    => 'before',
			]
		);

		$agent->add_control(
			'agent_days',
			[
				'label'       => esc_html__( 'Days', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => self::day_options(),
				'default'     => [ 'mon', 'tue', 'wed', 'thu', 'fri' ],
				'condition'   => [ 'agent_custom_hours' => 'yes' ],
			]
		);

		$agent->add_control(
			'agent_open_time',
			[
				'label'     => esc_html__( 'From', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '09:00',
				'options'   => self::time_options(),
				'condition' => [ 'agent_custom_hours' => 'yes' ],
			]
		);

		$agent->add_control(
			'agent_close_time',
			[
				'label'     => esc_html__( 'To', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '18:00',
				'options'   => self::time_options(),
				'condition' => [ 'agent_custom_hours' => 'yes' ],
			]
		);

		$agent->add_control(
			'agent_message',
			[
				'label'       => esc_html__( 'Pre-filled Message', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => esc_html__( 'Hi! I have a question about {page_title}', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
				'description' => esc_html__( 'Available placeholders: {page_title}, {post_title}, {site_title}, {page_url}', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'agents',
			[
				'label'       => esc_html__( 'Agents', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $agent->get_controls(),
				'default'     => [
					[
						'agent_name' => esc_html__( 'Support', 'sky-elementor-addons' ),
						'agent_role' => esc_html__( 'Customer Care', 'sky-elementor-addons' ),
					],
				],
				// Marks a switched-off row right in the panel list. The comparison is
				// against the empty string so a row saved before this control existed
				// is not mislabelled as off.
				'title_field' => '{{{ agent_name }}}<# if ( "" === agent_enabled ) { #> (off)<# } #>',
				'description' => esc_html__( 'One agent opens the chat straight away. Two or more turn the popup into a pick-an-agent list.', 'sky-elementor-addons' ),
			]
		);

		$this->end_controls_section();
	}

	private function register_popup_controls() {

		$this->start_controls_section(
			'section_popup',
			[
				'label'     => esc_html__( 'Chat Popup', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'button_type' => 'floating' ],
			]
		);

		// Without this the Chat Popup style section cannot be used at all: the card
		// is hidden until clicked, and in the editor Elementor swallows the click
		// to select the widget. Editor-only — the class is never added on the front
		// end, whatever this is left switched to.
		$this->add_control(
			'preview_popup',
			[
				'label'        => esc_html__( 'Keep Chat Open While Editing', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'No', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Holds the chat card open in the editor so it can be styled. Has no effect on the live site.', 'sky-elementor-addons' ),
				'render_type'  => 'template',
			]
		);

		$this->add_control(
			'popup_title',
			[
				'label'       => esc_html__( 'Popup Title', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Chat with us', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'popup_greeting',
			[
				'label'       => esc_html__( 'Greeting Message', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => esc_html__( "Hi there 👋\nHow can we help you today?", 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'show_typing',
			[
				'label'        => esc_html__( 'Typing Indicator', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'Hide', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'cta_text',
			[
				'label'       => esc_html__( 'Chat Button Text', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Start Chat', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'enable_tooltip',
			[
				'label'        => esc_html__( 'Tooltip', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'Hide', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'tooltip_text',
			[
				'label'       => esc_html__( 'Tooltip Text', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Need help? Chat with us', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
				'description' => esc_html__( 'Placed automatically on the side away from the screen edge.', 'sky-elementor-addons' ),
				'condition'   => [ 'enable_tooltip' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	private function register_hours_controls() {

		$this->start_controls_section(
			'section_hours',
			[
				'label' => esc_html__( 'Business Hours', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_hours',
			[
				'label'        => esc_html__( 'Business Hours', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'Off', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Times follow the site timezone in Settings → General.', 'sky-elementor-addons' ),
			]
		);

		$hours = new Repeater();

		$hours->add_control(
			'day',
			[
				'label'   => esc_html__( 'Day', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'mon',
				'options' => self::day_options(),
			]
		);

		$hours->add_control(
			'is_open',
			[
				'label'        => esc_html__( 'Open', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'No', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$hours->add_control(
			'open_time',
			[
				'label'     => esc_html__( 'From', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '09:00',
				'options'   => self::time_options(),
				'condition' => [ 'is_open' => 'yes' ],
			]
		);

		$hours->add_control(
			'close_time',
			[
				'label'       => esc_html__( 'To', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '18:00',
				'options'     => self::time_options(),
				'description' => esc_html__( 'A closing time earlier than the opening time runs past midnight into the next day.', 'sky-elementor-addons' ),
				'condition'   => [ 'is_open' => 'yes' ],
			]
		);

		$this->add_control(
			'business_hours',
			[
				'label'       => esc_html__( 'Weekly Schedule', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $hours->get_controls(),
				'default'     => [
					[ 'day' => 'mon' ],
					[ 'day' => 'tue' ],
					[ 'day' => 'wed' ],
					[ 'day' => 'thu' ],
					[ 'day' => 'fri' ],
					[ 'day' => 'sat' ],
					[
						'day'     => 'sun',
						'is_open' => '',
					],
				],
				'title_field' => '{{{ day }}}',
				'condition'   => [ 'enable_hours' => 'yes' ],
			]
		);

		$this->add_control(
			'online_text',
			[
				'label'       => esc_html__( 'Online Status Text', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Typically replies instantly', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'offline_text',
			[
				'label'       => esc_html__( 'Offline Status Text', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Offline — back {next_open}', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
				'description' => esc_html__( 'Use {next_open} for the next opening day and time.', 'sky-elementor-addons' ),
				'condition'   => [ 'enable_hours' => 'yes' ],
			]
		);

		$this->add_control(
			'offline_chat_text',
			[
				'label'       => esc_html__( 'Offline Chat Button Text', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Leave a message — we reply {next_open}', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
				'description' => esc_html__( 'WhatsApp keeps working outside business hours — messages wait in the inbox. This relabels the button so the visitor knows when to expect a reply. Use {next_open}.', 'sky-elementor-addons' ),
				'condition'   => [ 'enable_hours' => 'yes' ],
			]
		);

		$this->add_control(
			'offline_url',
			[
				'label'       => esc_html__( 'Offline Link', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'mailto:hello@example.com', 'sky-elementor-addons' ),
				'options'     => [ 'is_external', 'nofollow' ],
				'dynamic'     => [ 'active' => true ],
				'description' => esc_html__( 'Optional. Shown as a secondary line under the chat button outside business hours. WhatsApp stays the primary route.', 'sky-elementor-addons' ),
				'condition'   => [ 'enable_hours' => 'yes' ],
			]
		);

		$this->add_control(
			'offline_cta_text',
			[
				'label'       => esc_html__( 'Offline Button Text', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Send us an email', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
				'condition'   => [
					'enable_hours'      => 'yes',
					'offline_url[url]!' => '',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_position_controls() {

		$this->start_controls_section(
			'section_position',
			[
				'label'     => esc_html__( 'Position', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'button_type' => 'floating' ],
			]
		);

		$this->add_control(
			'button_position',
			[
				'label'        => esc_html__( 'Corner', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'end',
				'options'      => [
					'end'   => esc_html__( 'Bottom Right', 'sky-elementor-addons' ),
					'start' => esc_html__( 'Bottom Left', 'sky-elementor-addons' ),
				],
				'description'  => esc_html__( 'Mirrors automatically on right-to-left sites.', 'sky-elementor-addons' ),
				'prefix_class' => 'sa-wa-pos-',
				'render_type'  => 'template',
			]
		);

		$this->add_responsive_control(
			'offset_x',
			[
				'label'      => esc_html__( 'Side Offset', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
					'%'  => [
						'min' => 0,
						'max' => 50,
					],
					'vw' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 24,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-offset-x: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'offset_y',
			[
				'label'      => esc_html__( 'Bottom Offset', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vh' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
					'%'  => [
						'min' => 0,
						'max' => 50,
					],
					'vh' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 24,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-offset-y: {{SIZE}}{{UNIT}};',
				],
			]
		);

		// Editor-only escape hatch: the corner is prime real estate while designing
		// the rest of the page. Never affects the live site.
		$this->add_control(
			'hide_in_editor',
			[
				'label'        => esc_html__( 'Hide While Editing', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'No', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Takes the button out of the editor corner so it cannot get in the way. It still appears on the live site.', 'sky-elementor-addons' ),
				'render_type'  => 'template',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'z_index',
			[
				'label'   => esc_html__( 'Z-Index', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 0,
				'max'     => 99999,
				'default' => 9999,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-z-index: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_animation_controls() {

		$this->start_controls_section(
			'section_animation',
			[
				'label' => esc_html__( 'Animation', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_pulse',
			[
				'label'        => esc_html__( 'Pulse Ring', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'Off', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'prefix_class' => 'sa-wa-pulse-',
				'description'  => esc_html__( 'For an entrance animation use the Advanced tab — Elementor already provides one.', 'sky-elementor-addons' ),
			]
		);

		$this->end_controls_section();
	}

	private function register_tracking_controls() {

		$this->start_controls_section(
			'section_tracking',
			[
				'label' => esc_html__( 'Click Tracking', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_tracking',
			[
				'label'        => esc_html__( 'Track Clicks', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'Off', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Sends an event to Google Tag Manager, GA4 and the Meta pixel — whichever of them is already on the page.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'tracking_event',
			[
				'label'       => esc_html__( 'Event Name', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'sky_whatsapp_click',
				'label_block' => true,
				'condition'   => [ 'enable_tracking' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	private function register_button_style_controls() {

		$this->start_controls_section(
			'section_button_style',
			[
				'label' => esc_html__( 'Button', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'button_size',
			[
				'label'      => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 32,
						'max' => 120,
					],
					'em' => [
						'min' => 2,
						'max' => 8,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 60,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-btn-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 10,
						'max' => 64,
					],
					'em' => [
						'min' => 0.5,
						'max' => 4,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 28,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-icon-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-wa__toggle' => 'padding-block: {{TOP}}{{UNIT}} {{BOTTOM}}{{UNIT}}; padding-inline: {{LEFT}}{{UNIT}} {{RIGHT}}{{UNIT}};',
				],
				'condition'  => [
					'button_type'  => 'standard',
					'button_style' => 'icon-text',
				],
			]
		);

		$this->add_responsive_control(
			'button_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'      => '50',
					'right'    => '50',
					'bottom'   => '50',
					'left'     => '50',
					'unit'     => '%',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-btn-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_text_gap',
			[
				'label'      => esc_html__( 'Icon Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
					'em' => [
						'min' => 0,
						'max' => 4,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-btn-gap: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'button_type'  => 'standard',
					'button_style' => 'icon-text',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .sa-wa__label',
				'condition' => [
					'button_type'  => 'standard',
					'button_style' => 'icon-text',
				],
			]
		);

		$this->start_controls_tabs( 'button_tabs' );

		$this->start_controls_tab( 'button_tab_normal', [ 'label' => esc_html__( 'Normal', 'sky-elementor-addons' ) ] );

		$this->add_control(
			'button_color',
			[
				'label' => esc_html__( 'Icon & Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-btn-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-btn-bg: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pulse_color',
			[
				'label'     => esc_html__( 'Pulse Ring Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-pulse-color: {{VALUE}};',
				],
				'condition' => [ 'enable_pulse' => 'yes' ],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'button_border',
				'selector' => '{{WRAPPER}} .sa-wa__toggle',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_shadow',
				'selector' => '{{WRAPPER}} .sa-wa__toggle',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'button_tab_hover', [ 'label' => esc_html__( 'Hover', 'sky-elementor-addons' ) ] );

		$this->add_control(
			'button_color_hover',
			[
				'label' => esc_html__( 'Icon & Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-btn-color-hover: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_hover',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-btn-bg-hover: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_shadow_hover',
				'selector' => '{{WRAPPER}} .sa-wa__toggle:hover',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_popup_style_controls() {

		$this->start_controls_section(
			'section_popup_style',
			[
				'label'     => esc_html__( 'Chat Popup', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'button_type' => 'floating' ],
			]
		);

		$this->add_responsive_control(
			'popup_width',
			[
				'label'      => esc_html__( 'Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw' ],
				'range'      => [
					'px' => [
						'min' => 240,
						'max' => 480,
					],
					'vw' => [
						'min' => 50,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 320,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-panel-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'popup_bg',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-panel-bg: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'popup_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 40,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 16,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-panel-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'popup_shadow',
				'selector' => '{{WRAPPER}} .sa-wa__panel',
			]
		);

		$this->add_control(
			'popup_header_heading',
			[
				'label'     => esc_html__( 'Header', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'popup_header_bg',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-header-bg: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'popup_header_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-header-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'popup_title_typography',
				'label'    => esc_html__( 'Title Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-wa__title',
			]
		);

		$this->add_control(
			'status_online_color',
			[
				'label' => esc_html__( 'Online Dot', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-dot-online: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'status_offline_color',
			[
				'label' => esc_html__( 'Offline Dot', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-dot-offline: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'close_color',
			[
				'label' => esc_html__( 'Close Icon', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-close-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'close_bg',
			[
				'label' => esc_html__( 'Close Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-close-bg: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'close_color_hover',
			[
				'label' => esc_html__( 'Close Icon Hover', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-close-color-hover: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'close_bg_hover',
			[
				'label' => esc_html__( 'Close Background Hover', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-close-bg-hover: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'popup_body_heading',
			[
				'label'     => esc_html__( 'Body', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		// A full background group rather than a flat colour, so a chat-wallpaper
		// image is the site owner's own upload. Nothing is hotlinked from a
		// third-party host.
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'popup_body_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-wa__body',
			]
		);

		$this->add_control(
			'bubble_bg',
			[
				'label' => esc_html__( 'Bubble Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-bubble-bg: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'bubble_color',
			[
				'label' => esc_html__( 'Bubble Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-bubble-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'bubble_typography',
				'label'    => esc_html__( 'Bubble Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-wa__bubble',
			]
		);

		$this->add_control(
			'popup_cta_heading',
			[
				'label'     => esc_html__( 'Chat Button', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'cta_bg',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-cta-bg: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cta_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-cta-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'cta_typography',
				'selector' => '{{WRAPPER}} .sa-wa__cta',
			]
		);

		$this->end_controls_section();
	}

	private function register_tooltip_style_controls() {

		$this->start_controls_section(
			'section_tooltip_style',
			[
				'label' => esc_html__( 'Tooltip', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'button_type'    => 'floating',
					'enable_tooltip' => 'yes',
				],
			]
		);

		$this->add_control(
			'tooltip_bg',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-tooltip-bg: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tooltip_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-tooltip-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'tooltip_typography',
				'selector' => '{{WRAPPER}} .sa-wa__tooltip',
			]
		);

		$this->add_responsive_control(
			'tooltip_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-tooltip-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'tooltip_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 40,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 8,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-wa' => '--sa-wa-tooltip-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Agents that actually have a usable number, with links and messages resolved.
	 */
	private function get_agents( $settings, $widget_slots = [] ) {
		$agents = [];

		foreach ( (array) ( $settings['agents'] ?? [] ) as $agent ) {
			// Switched off on purpose — counted separately from a broken row so the
			// editor notice can tell a deliberate choice from a mistake.
			if ( 'yes' !== ( $agent['agent_enabled'] ?? 'yes' ) ) {
				++$this->disabled_agents;
				continue;
			}

			$number = preg_replace( '/\D/', '', (string) ( $agent['agent_number'] ?? '' ) );

			// No number means no wa.me link is possible, so the row cannot render.
			// It is counted rather than dropped in silence.
			if ( '' === $number ) {
				++$this->skipped_agents;
				continue;
			}

			$message = $this->clean_message( $this->parse_placeholders( (string) ( $agent['agent_message'] ?? '' ) ) );
			$link    = 'https://wa.me/' . $number;

			if ( '' !== $message ) {
				$link .= '?text=' . rawurlencode( $message );
			}

			// The agent's own hours win outright; without them it follows the
			// widget schedule. No schedule anywhere means always available.
			$own   = $this->get_agent_slots( $agent );
			$slots = ! empty( $own ) ? $own : $widget_slots;
			$state = ! empty( $slots )
				? $this->get_hours_state( $slots )
				: [
					'online'    => true,
					'next_open' => '',
					'gap'       => PHP_INT_MAX,
				];

			$agents[] = [
				'id'        => $agent['_id'] ?? '',
				'name'      => $this->decode_entities( $agent['agent_name'] ?? '' ),
				'role'      => $this->decode_entities( $agent['agent_role'] ?? '' ),
				'avatar'    => $this->get_avatar_url( $agent['agent_avatar'] ?? [] ),
				'link'      => $link,
				'slots'     => $slots,
				'online'    => (bool) $state['online'],
				'next_open' => (string) $state['next_open'],
				'gap'       => (int) $state['gap'],
			];
		}

		return $agents;
	}

	/**
	 * Avatar URL at a size worth downloading.
	 *
	 * The MEDIA control hands back the full-size URL, and both avatars are drawn at a
	 * size the CSS fixes — 44px in the header, 40px in the agent list — so a 1200x1200
	 * library image spent 115 KB to paint 38 px of screen, on every page a site-wide
	 * button appears. There is no image-size control to reach for because there is no
	 * display-size control either: the size is not the author's to choose. So the
	 * attachment is resolved to WP's 150x150 `thumbnail`, which still covers the
	 * largest avatar at 2x DPR (and beyond). Small uploads are left alone by WP
	 * itself — it returns the original when no thumbnail was generated.
	 *
	 * A media value with no attachment ID (an external URL) is used verbatim; there
	 * is nothing to resolve it against.
	 *
	 * @param array $media MEDIA control value.
	 * @return string
	 */
	private function get_avatar_url( $media ) {
		$url = (string) ( $media['url'] ?? '' );
		$id  = (int) ( $media['id'] ?? 0 );

		if ( '' === $url || ! $id ) {
			return $url;
		}

		$thumb = wp_get_attachment_image_url( $id, 'thumbnail' );

		return $thumb ? $thumb : $url;
	}

	/**
	 * Resolve HTML entities back to their characters.
	 *
	 * WordPress texturizes titles and the site name, so a title written with a
	 * plain dash comes back as "&#8211;". Left alone that entity is printed
	 * literally: rawurlencode() sends the seven characters to WhatsApp, and
	 * esc_html() turns it into "&amp;#8211;" on screen. Both show as raw markup to
	 * the visitor, so every dynamic-capable string is decoded before it is used.
	 */
	private function decode_entities( $text ) {
		return html_entity_decode( (string) $text, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * The wa.me ?text= payload is plain text, so markup has to go as well.
	 */
	private function clean_message( $text ) {
		return trim( wp_strip_all_tags( $this->decode_entities( $text ) ) );
	}

	/**
	 * Swap the message placeholders for the page they were rendered on.
	 *
	 * Elementor dynamic tags replace a whole field, so they cannot mix a sentence
	 * with the page title. These placeholders can.
	 */
	private function parse_placeholders( $text ) {
		if ( false === strpos( $text, '{' ) ) {
			return $text;
		}

		global $wp;

		$path = isset( $wp->request ) ? (string) $wp->request : '';
		$url  = home_url( '' !== $path ? user_trailingslashit( $path ) : '/' );

		return strtr(
			$text,
			[
				'{page_title}' => wp_get_document_title(),
				'{post_title}' => get_the_title(),
				'{site_title}' => get_bloginfo( 'name' ),
				'{page_url}'   => $url,
			]
		);
	}

	/**
	 * "09:00" → 540. Null when the value is not a stored time option.
	 */
	private function to_minutes( $time ) {
		if ( ! preg_match( '/^(\d{1,2}):(\d{2})$/', (string) $time, $m ) ) {
			return null;
		}

		$hours   = (int) $m[1];
		$minutes = (int) $m[2];

		if ( $hours > 23 || $minutes > 59 ) {
			return null;
		}

		return ( $hours * 60 ) + $minutes;
	}

	/**
	 * Weekly schedule as a flat list of slots.
	 *
	 * Each slot is [ start minute of the week, duration in minutes, day index,
	 * opening time string ]. A closing time at or before the opening time is read
	 * as running past midnight, which is why duration is stored rather than an end.
	 * The JS uses this exact shape.
	 */
	private function get_slots( $settings ) {
		$days  = array_keys( self::day_options() );
		$slots = [];

		foreach ( (array) ( $settings['business_hours'] ?? [] ) as $row ) {
			$index = array_search( $row['day'] ?? '', $days, true );

			if ( false === $index || 'yes' !== ( $row['is_open'] ?? '' ) ) {
				continue;
			}

			$open  = $this->to_minutes( $row['open_time'] ?? '' );
			$close = $this->to_minutes( $row['close_time'] ?? '' );

			if ( null === $open || null === $close ) {
				continue;
			}

			$duration = $close > $open ? $close - $open : ( 1440 - $open ) + $close;

			if ( $duration <= 0 ) {
				continue;
			}

			$slots[] = [ ( $index * 1440 ) + $open, $duration, $index, sprintf( '%02d:%02d', intdiv( $open, 60 ), $open % 60 ) ];
		}

		return $slots;
	}

	/**
	 * Whether now falls inside a slot, and the label for the next opening.
	 */
	private function get_hours_state( $slots ) {
		$now      = new \DateTimeImmutable( 'now', wp_timezone() );
		$day      = (int) $now->format( 'N' ) - 1;
		$minute   = ( $day * 1440 ) + ( (int) $now->format( 'G' ) * 60 ) + (int) $now->format( 'i' );
		$names    = array_values( self::day_options() );
		$online   = false;
		$nearest  = null;
		$next_gap = PHP_INT_MAX;

		foreach ( $slots as $slot ) {
			list( $start, $duration, $index ) = $slot;

			// Compare inside a 10080-minute week so a slot running past Sunday
			// midnight still matches early Monday.
			$offset = ( ( $minute - $start ) % 10080 + 10080 ) % 10080;

			if ( $offset < $duration ) {
				$online   = true;
				$next_gap = 0;
				break;
			}

			$gap = ( ( $start - $minute ) % 10080 + 10080 ) % 10080;

			if ( $gap < $next_gap ) {
				$next_gap = $gap;
				$nearest  = $names[ $index ] . ' ' . $slot[3];
			}
		}

		return [
			'online'    => $online,
			'next_open' => (string) $nearest,
			// How far away the next opening is, so the card can pick the soonest
			// reopening when several agents keep different hours.
			'gap'       => $next_gap,
		];
	}

	/**
	 * One agent's own schedule, in the same slot shape as the widget's.
	 *
	 * Elementor has no nested repeaters, so an agent gets a single time range and a
	 * multi-select of days rather than seven rows. That covers "Sales on weekdays,
	 * Support at the weekend", which is what per-agent hours are for. Returns an
	 * empty array when the agent has none, meaning it inherits the widget schedule.
	 */
	private function get_agent_slots( $agent ) {
		if ( 'yes' !== ( $agent['agent_custom_hours'] ?? '' ) ) {
			return [];
		}

		$days  = array_keys( self::day_options() );
		$open  = $this->to_minutes( $agent['agent_open_time'] ?? '' );
		$close = $this->to_minutes( $agent['agent_close_time'] ?? '' );

		if ( null === $open || null === $close ) {
			return [];
		}

		$duration = $close > $open ? $close - $open : ( 1440 - $open ) + $close;

		if ( $duration <= 0 ) {
			return [];
		}

		$label = sprintf( '%02d:%02d', intdiv( $open, 60 ), $open % 60 );
		$slots = [];

		foreach ( (array) ( $agent['agent_days'] ?? [] ) as $day ) {
			$index = array_search( $day, $days, true );

			if ( false !== $index ) {
				$slots[] = [ ( $index * 1440 ) + $open, $duration, $index, $label ];
			}
		}

		return $slots;
	}

	/**
	 * A floating root is `position: fixed`, which leaves the widget wrapper zero
	 * pixels tall and effectively unclickable on the canvas. This chip sits in the
	 * flow so the widget can still be selected and its place in the document is
	 * visible, without the button itself having to lie about where it lands.
	 */
	private function render_editor_chip() {
		if ( ! $this->view['floating'] || ! sky_addons_editor_mode() ) {
			return;
		}
		?>
		<span class="sa-wa-chip">
			<?php
			echo esc_html(
				'yes' === $this->view['settings']['hide_in_editor']
					? __( 'WhatsApp Button — hidden while editing, live on the site', 'sky-elementor-addons' )
					: __( 'WhatsApp Button — floating, shown in the corner', 'sky-elementor-addons' )
			);
			?>
		</span>
		<?php
	}

	/**
	 * Editor-only message. Nothing is printed on the front end.
	 */
	private function render_notice( $message ) {
		if ( ! sky_addons_editor_mode() ) {
			return;
		}
		?>
		<div class="sa-wa__notice"><?php echo esc_html( $message ); ?></div>
		<?php
	}

	/**
	 * Resolve settings into the handful of values the markup actually needs.
	 */
	private function build_view( $settings ) {
		// Decoded up front, so every render method and the data-settings payload
		// work with real characters rather than "&#8211;". Done before get_agents()
		// so the message placeholders inherit it too.
		$text_keys = [
			'popup_title',
			'popup_greeting',
			'cta_text',
			'button_text',
			'tooltip_text',
			'online_text',
			'offline_text',
			'offline_chat_text',
			'offline_cta_text',
		];

		foreach ( $text_keys as $key ) {
			if ( isset( $settings[ $key ] ) ) {
				$settings[ $key ] = $this->decode_entities( $settings[ $key ] );
			}
		}

		$hours_on = 'yes' === $settings['enable_hours'];
		$slots    = $hours_on ? $this->get_slots( $settings ) : [];
		$agents   = $this->get_agents( $settings, $slots );

		// The card speaks for the whole team: it is open while ANY agent is open,
		// and when everyone is away it quotes whichever of them returns first.
		$online    = false;
		$next_open = '';
		$best_gap  = PHP_INT_MAX;
		$scheduled = ! empty( $slots );

		foreach ( $agents as $agent ) {
			if ( ! empty( $agent['slots'] ) ) {
				$scheduled = true;
			}

			if ( $agent['online'] ) {
				$online = true;
				continue;
			}

			if ( $agent['gap'] < $best_gap ) {
				$best_gap  = $agent['gap'];
				$next_open = $agent['next_open'];
			}
		}

		return [
			'settings'    => $settings,
			'agents'      => $agents,
			'floating'    => 'floating' === $settings['button_type'],
			'target'      => '_self' === $settings['open_in'] ? '_self' : '_blank',
			'panel_id'    => 'sa-wa-panel-' . $this->get_id(),
			'online'      => $online,
			'offline_url' => $settings['offline_url']['url'] ?? '',
			'status_text' => $online
				? (string) $settings['online_text']
				: str_replace( '{next_open}', $next_open, (string) $settings['offline_text'] ),
			'offline_cta' => str_replace( '{next_open}', $next_open, (string) $settings['offline_chat_text'] ),
			'data'        => [
				'hours'           => $scheduled,
				'slots'           => $slots,
				// One slot list per rendered agent, in row order, so the JS can
				// re-check each independently against the visitor's clock.
				'agentSlots'      => array_column( $agents, 'slots' ),
				'days'            => array_values( self::day_options() ),
				'offset'          => (int) wp_timezone()->getOffset( new \DateTimeImmutable( 'now', wp_timezone() ) ),
				'onlineText'      => (string) $settings['online_text'],
				'offlineText'     => (string) $settings['offline_text'],
				'offlineChatText' => (string) $settings['offline_chat_text'],
				'track'           => 'yes' === $settings['enable_tracking'] ? (string) $settings['tracking_event'] : '',
			],
		];
	}

	/**
	 * Root classes. State classes are printed by PHP and corrected by the JS when a
	 * cached page is serving a stale business-hours state.
	 */
	private function get_root_classes() {
		$classes = [ 'sa-wa' ];

		if ( ! $this->view['online'] ) {
			$classes[] = 'sa-wa--offline';
		}

		if ( $this->view['floating'] && sky_addons_editor_mode() ) {
			if ( 'yes' === $this->view['settings']['hide_in_editor'] ) {
				$classes[] = 'sa-wa--editor-hidden';
			} elseif ( 'yes' === $this->view['settings']['preview_popup'] ) {
				// Reuses the open state rather than inventing a preview one, so the
				// editor shows the same markup, icon swap and animations the
				// visitor will get.
				$classes[] = 'sa-wa--open';
			}
		}

		return $classes;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( 'floating' === $settings['button_type'] ) {
			if ( self::$floating_rendered ) {
				$this->render_notice( esc_html__( 'A floating WhatsApp button is already on this page. Only the first one renders.', 'sky-elementor-addons' ) );
				return;
			}

			self::$floating_rendered = true;
		}

		$this->view = $this->build_view( $settings );

		if ( empty( $this->view['agents'] ) ) {
			$this->render_notice(
				$this->disabled_agents > 0 && 0 === $this->skipped_agents
					? esc_html__( 'WhatsApp Button: every agent is switched off, so nothing renders.', 'sky-elementor-addons' )
					: esc_html__( 'WhatsApp Button: add a phone number to an agent.', 'sky-elementor-addons' )
			);
			return;
		}

		if ( $this->skipped_agents > 0 ) {
			$this->render_notice(
				sprintf(
					/* translators: %d: number of agent rows without a phone number. */
					_n(
						'%d agent is missing a WhatsApp number and is not shown.',
						'%d agents are missing a WhatsApp number and are not shown.',
						$this->skipped_agents,
						'sky-elementor-addons'
					),
					$this->skipped_agents
				)
			);
		}

		if ( ! $this->view['floating'] && count( $this->view['agents'] ) > 1 ) {
			$this->render_notice( esc_html__( 'A standard button links to the first agent only. Switch to Floating to let visitors pick.', 'sky-elementor-addons' ) );
		}

		$this->add_render_attribute(
			[
				'whatsapp-button' => [
					'class'         => $this->get_root_classes(),
					'data-settings' => [ wp_json_encode( $this->view['data'] ) ],
				],
			]
		);

		// Anti-flash. In per-widget asset mode this widget's stylesheet is enqueued
		// through Elementor's dependency chain while the body renders, so the link
		// lands in the footer. Until it arrives the panel has no `visibility:hidden`
		// and the whole card paints in the content flow — the button looks like it
		// opened and then closed. `hidden` lets the UA sheet hide the widget on the
		// first paint; author CSS wins on origin, so `.sa-wa[hidden]` reveals it the
		// moment the stylesheet lands. The JS drops the attribute as well, so an
		// assistive technology that reads the attribute rather than the computed
		// style never sees a live dialog marked hidden. Skipped in the editor, where
		// the combined bundle is already in the head and an invisible widget would
		// only be in the way.
		if ( ! sky_addons_editor_mode() ) {
			$this->add_render_attribute( 'whatsapp-button', 'hidden', 'hidden' );
		}

		?>
		<?php $this->render_editor_chip(); ?>
		<div <?php $this->print_render_attribute_string( 'whatsapp-button' ); ?>>
			<?php
			if ( $this->view['floating'] ) {
				$this->render_panel();
			}

			$this->render_toggle();

			// After the toggle on purpose: CSS keys the tooltip off
			// `toggle:hover + tooltip`, so it reacts to the button alone rather
			// than to the whole widget. It is absolutely positioned, so the DOM
			// order does not change where it appears.
			if ( $this->view['floating'] ) {
				$this->render_tooltip();
			}
			?>
		</div>
		<?php
	}

	private function render_panel() {
		$settings = $this->view['settings'];
		?>
		<div class="sa-wa__panel" id="<?php echo esc_attr( $this->view['panel_id'] ); ?>" role="dialog" aria-modal="false" aria-label="<?php echo esc_attr( $settings['popup_title'] ); ?>">
			<?php
			$this->render_panel_header();
			$this->render_panel_body();
			$this->render_panel_footer();
			?>
		</div>
		<?php
	}

	/**
	 * Header identity.
	 *
	 * With exactly one agent the visitor is talking to a specific person, so the
	 * header names them and shows their role — a face with a generic title tells
	 * the visitor nothing about who is on the other end. With several agents nobody
	 * has been chosen yet, so the popup title does the job and the faces belong to
	 * the list rows. No switcher: the agent count decides, as it does elsewhere.
	 */
	private function render_panel_header() {
		$settings = $this->view['settings'];
		$agents   = $this->view['agents'];
		$solo     = 1 === count( $agents ) ? $agents[0] : null;

		$name   = $solo && '' !== trim( $solo['name'] ) ? $solo['name'] : (string) $settings['popup_title'];
		$avatar = $solo ? $solo['avatar'] : '';
		$role   = $solo ? $solo['role'] : '';
		$status = $this->view['status_text'];
		?>
		<div class="sa-wa__header">
			<?php if ( '' !== $avatar ) : ?>
				<span class="sa-wa__figure">
					<img class="sa-wa__avatar" src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>" width="44" height="44" loading="lazy" decoding="async">
					<?php if ( $this->view['data']['hours'] ) : ?>
						<span class="sa-wa__dot" aria-hidden="true"></span>
					<?php endif; ?>
				</span>
			<?php endif; ?>

			<span class="sa-wa__identity">
				<span class="sa-wa__title"><?php echo esc_html( $name ); ?></span>

				<?php if ( '' !== $role || '' !== trim( $status ) ) : ?>
					<span class="sa-wa__status">
						<?php // Fallback presence marker for a header with no avatar to pin it to. ?>
						<?php if ( '' === $avatar && $this->view['data']['hours'] ) : ?>
							<span class="sa-wa__dot sa-wa__dot--inline" aria-hidden="true"></span>
						<?php endif; ?>

						<?php if ( '' !== $role ) : ?>
							<span class="sa-wa__role"><?php echo esc_html( $role ); ?></span>
						<?php endif; ?>

						<span class="sa-wa__status-text"><?php echo esc_html( $status ); ?></span>
					</span>
				<?php endif; ?>
			</span>

			<a href="#" class="sa-wa__close" role="button" aria-label="<?php echo esc_attr__( 'Close chat', 'sky-elementor-addons' ); ?>">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
			</a>
		</div>
		<?php
	}

	private function render_panel_body() {
		$settings = $this->view['settings'];
		?>
		<div class="sa-wa__body">
			<?php if ( 'yes' === $settings['show_typing'] ) : ?>
				<span class="sa-wa__typing" aria-hidden="true"><i></i><i></i><i></i></span>
			<?php endif; ?>

			<?php if ( '' !== trim( (string) $settings['popup_greeting'] ) ) : ?>
				<p class="sa-wa__bubble"><?php echo nl2br( esc_html( $settings['popup_greeting'] ) ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	private function render_panel_footer() {
		?>
		<div class="sa-wa__footer">
			<?php
			if ( 1 === count( $this->view['agents'] ) ) {
				$this->render_cta();
			} else {
				$this->render_offline_note();
				$this->render_agent_list();
			}

			$this->render_offline_cta();
			?>
		</div>
		<?php
	}

	/**
	 * Outside business hours WhatsApp is still the right route — the message waits
	 * in the merchant's inbox. Only the label changes, to set the reply expectation.
	 * Both labels are printed and CSS picks one, so the JS can flip a cached page
	 * without touching the link.
	 */
	private function render_cta() {
		$agent = $this->view['agents'][0];
		?>
		<a class="sa-wa__cta" href="<?php echo esc_url( $agent['link'] ); ?>" target="<?php echo esc_attr( $this->view['target'] ); ?>" rel="noopener nofollow">
			<span class="sa-wa__cta-icon"><?php $this->render_glyph(); ?></span>
			<span class="sa-wa__cta-label"><?php echo esc_html( $this->view['settings']['cta_text'] ); ?></span>
			<?php if ( $this->view['data']['hours'] ) : ?>
				<span class="sa-wa__cta-label sa-wa__cta-label--offline"><?php echo esc_html( $this->view['offline_cta'] ); ?></span>
			<?php endif; ?>
		</a>
		<?php
	}

	/**
	 * The same expectation line for the multi-agent list, where there is no single
	 * button label to relabel.
	 */
	private function render_offline_note() {
		if ( ! $this->view['data']['hours'] ) {
			return;
		}
		?>
		<p class="sa-wa__offline-note"><?php echo esc_html( $this->view['offline_cta'] ); ?></p>
		<?php
	}

	/**
	 * Each row carries its own availability, because an agent may keep hours the
	 * rest of the team does not. `data-agent` is the row's index in the payload the
	 * JS re-checks, so a cached page can correct one row without touching the rest.
	 */
	private function render_agent_list() {
		$offline_template = (string) $this->view['settings']['offline_text'];
		?>
		<ul class="sa-wa__list">
			<?php foreach ( $this->view['agents'] as $index => $agent ) : ?>
				<?php
				$row_class = 'sa-wa__agent' . ( $agent['online'] ? '' : ' sa-wa__agent--offline' );
				$row_state = str_replace( '{next_open}', $agent['next_open'], $offline_template );
				?>
				<li class="sa-wa__list-item">
					<a class="<?php echo esc_attr( $row_class ); ?>" data-agent="<?php echo esc_attr( $index ); ?>" href="<?php echo esc_url( $agent['link'] ); ?>" target="<?php echo esc_attr( $this->view['target'] ); ?>" rel="noopener nofollow">
						<?php if ( '' !== $agent['avatar'] ) : ?>
							<span class="sa-wa__agent-figure">
								<img class="sa-wa__agent-avatar" src="<?php echo esc_url( $agent['avatar'] ); ?>" alt="<?php echo esc_attr( $agent['name'] ); ?>" width="40" height="40" loading="lazy" decoding="async">
								<?php if ( $this->view['data']['hours'] ) : ?>
									<span class="sa-wa__agent-dot" aria-hidden="true"></span>
								<?php endif; ?>
							</span>
						<?php endif; ?>

						<span class="sa-wa__agent-text">
							<span class="sa-wa__agent-name"><?php echo esc_html( $agent['name'] ); ?></span>

							<?php if ( '' !== $agent['role'] ) : ?>
								<span class="sa-wa__agent-role"><?php echo esc_html( $agent['role'] ); ?></span>
							<?php endif; ?>

							<?php if ( $this->view['data']['hours'] && '' !== trim( $row_state ) ) : ?>
								<span class="sa-wa__agent-state"><?php echo esc_html( $row_state ); ?></span>
							<?php endif; ?>
						</span>

						<span class="sa-wa__agent-icon"><?php $this->render_glyph(); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * Secondary route, never a replacement. Printed always and revealed by CSS when
	 * the offline state is on, so a cached page can flip without rewriting hrefs.
	 */
	private function render_offline_cta() {
		if ( '' === $this->view['offline_url'] ) {
			return;
		}

		$this->add_render_attribute( 'offline-link', 'class', 'sa-wa__alt-link' );
		$this->add_link_attributes( 'offline-link', $this->view['settings']['offline_url'] );
		?>
		<a <?php $this->print_render_attribute_string( 'offline-link' ); ?>>
			<span><?php echo esc_html( $this->view['settings']['offline_cta_text'] ); ?></span>
		</a>
		<?php
	}

	private function render_tooltip() {
		$settings = $this->view['settings'];

		if ( 'yes' !== $settings['enable_tooltip'] || '' === trim( (string) $settings['tooltip_text'] ) ) {
			return;
		}
		?>
		<span class="sa-wa__tooltip"><?php echo esc_html( $settings['tooltip_text'] ); ?></span>
		<?php
	}

	/**
	 * The toggle always carries a real wa.me href, so the button still works with
	 * JavaScript disabled. The JS only intercepts it when a chat card exists.
	 */
	private function render_toggle() {
		$settings = $this->view['settings'];
		$floating = $this->view['floating'];

		$this->add_render_attribute(
			'toggle',
			[
				'class'      => 'sa-wa__toggle',
				'href'       => $this->view['agents'][0]['link'],
				'target'     => $this->view['target'],
				'rel'        => 'noopener nofollow',
				'aria-label' => esc_attr__( 'Chat on WhatsApp', 'sky-elementor-addons' ),
			]
		);

		if ( $floating ) {
			$this->add_render_attribute(
				'toggle',
				[
					'aria-expanded' => 'false',
					'aria-controls' => $this->view['panel_id'],
				]
			);
		}
		?>
		<a <?php $this->print_render_attribute_string( 'toggle' ); ?>>
			<?php if ( 'yes' === $settings['enable_pulse'] ) : ?>
				<span class="sa-wa__pulse" aria-hidden="true"></span>
			<?php endif; ?>

			<span class="sa-wa__icon">
				<?php
				if ( ! empty( $settings['selected_icon']['value'] ) ) {
					Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] );
				} else {
					$this->render_glyph();
				}
				?>
			</span>

			<?php if ( ! $floating && 'icon-text' === $settings['button_style'] && '' !== trim( (string) $settings['button_text'] ) ) : ?>
				<span class="sa-wa__label"><?php echo esc_html( $settings['button_text'] ); ?></span>
			<?php endif; ?>

			<?php if ( $floating ) : ?>
				<span class="sa-wa__toggle-close" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
				</span>
			<?php endif; ?>
		</a>
		<?php
	}

	/**
	 * Inline WhatsApp glyph. Used wherever an icon is needed without asking the
	 * page to load an icon font it may not already have.
	 */
	private function render_glyph() {
		?>
		<svg viewBox="0 0 32 32" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M16 2.7C8.6 2.7 2.7 8.6 2.7 16c0 2.3.6 4.6 1.8 6.7L2.7 29.3l6.8-1.8c2 1.1 4.2 1.6 6.5 1.6 7.4 0 13.3-6 13.3-13.3S23.4 2.7 16 2.7Zm0 24.2c-2 0-3.9-.5-5.6-1.5l-.4-.3-4.1 1.1 1.1-4-.3-.4A11 11 0 0 1 4.9 16C4.9 9.9 9.9 4.9 16 4.9c3 0 5.8 1.2 7.9 3.3a11 11 0 0 1 3.2 7.8c0 6.1-5 11.1-11.1 11.1Zm6.1-8.3c-.3-.2-2-1-2.3-1.1-.3-.1-.5-.2-.7.2-.2.3-.9 1.1-1.1 1.3-.2.2-.4.3-.7.1-.3-.2-1.4-.5-2.7-1.7-1-.9-1.7-2-1.9-2.3-.2-.3 0-.5.2-.7l.5-.6c.2-.2.2-.3.3-.6.1-.2 0-.4 0-.6l-1-2.4c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.8s1.2 3.2 1.4 3.5c.2.2 2.4 3.6 5.7 5 .8.4 1.4.6 1.9.7.8.3 1.5.2 2.1.1.6-.1 2-.8 2.3-1.6.3-.8.3-1.4.2-1.6-.1-.1-.3-.2-.7-.4Z"/></svg>
		<?php
	}
}
