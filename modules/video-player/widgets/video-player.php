<?php

namespace Sky_Addons\Modules\VideoPlayer\Widgets;

use Elementor\Embed;
use Elementor\Repeater;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Video_Player extends Widget_Base {

	public function get_name() {
		return 'sky-video-player';
	}

	public function get_title() {
		return esc_html__( 'Video Player', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-video-player';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'video', 'player', 'youtube', 'vimeo', 'mp4', 'html5', 'chapters', 'sticky', 'lazy' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'plyr', 'sky-addons-scripts' ];
		}

		return [ 'plyr', 'sa-video-player' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'plyr', 'sky-addons-styles' ];
		}

		return [ 'plyr', 'sa-video-player' ];
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {
		$this->register_source_controls();
		$this->register_playback_controls();
		$this->register_player_controls();
		$this->register_chapters_controls();
		$this->register_sticky_controls();
		$this->register_schema_controls();

		$this->register_player_style();
		$this->register_controls_style();
		$this->register_progress_style();
		$this->register_tooltip_style();
		$this->register_menu_style();
		$this->register_captions_style();
		$this->register_poster_style();
		$this->register_play_button_style();
		$this->register_segments_style();
		$this->register_chapter_overlay_style();
		$this->register_chapters_style();
		$this->register_sticky_style();
	}

	/* ─────────────────────────────────────────────────────────────
	 * CONTENT — Video
	 * ──────────────────────────────────────────────────────────── */

	protected function register_source_controls() {
		$this->start_controls_section(
			'section_source',
			[
				'label' => esc_html__( 'Video', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'source_type',
			[
				'label'   => esc_html__( 'Source', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'youtube',
				'options' => [
					'youtube'  => esc_html__( 'YouTube', 'sky-elementor-addons' ),
					'vimeo'    => esc_html__( 'Vimeo', 'sky-elementor-addons' ),
					'hosted'   => esc_html__( 'Self Hosted', 'sky-elementor-addons' ),
					'external' => esc_html__( 'External URL', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'youtube_url',
			[
				'label'       => esc_html__( 'YouTube URL', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => 'https://youtu.be/aqz-KE-bpKQ',
				'placeholder' => 'https://youtu.be/aqz-KE-bpKQ',
				'dynamic'     => [
					'active'     => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					],
				],
				'condition'   => [ 'source_type' => 'youtube' ],
			]
		);

		$this->add_control(
			'vimeo_url',
			[
				'label'       => esc_html__( 'Vimeo URL', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => 'https://vimeo.com/235215203',
				'placeholder' => 'https://vimeo.com/235215203',
				'dynamic'     => [
					'active'     => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					],
				],
				'condition'   => [ 'source_type' => 'vimeo' ],
			]
		);

		$this->add_control(
			'hosted_url',
			[
				'label'      => esc_html__( 'Choose Video File', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::MEDIA,
				'media_type' => 'video',
				'dynamic'    => [
					'active'     => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::MEDIA_CATEGORY,
					],
				],
				'condition'  => [ 'source_type' => 'hosted' ],
			]
		);

		$this->add_control(
			'external_url',
			[
				'label'         => esc_html__( 'External URL', 'sky-elementor-addons' ),
				'description'   => esc_html__( 'Direct link to an MP4, WebM or Ogg file.', 'sky-elementor-addons' ),
				'type'          => Controls_Manager::URL,
				'show_external' => false,
				'options'       => false,
				'label_block'   => true,
				'placeholder'   => 'https://example.com/video.mp4',
				'dynamic'       => [
					'active'     => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					],
				],
				'condition'     => [ 'source_type' => 'external' ],
			]
		);

		$this->add_control(
			'poster',
			[
				'label'       => esc_html__( 'Poster', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Leave empty to use the provider thumbnail for YouTube and Vimeo.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => [ 'active' => true ],
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'start_time',
			[
				'label'       => esc_html__( 'Start Time', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Seconds, or mm:ss / hh:mm:ss.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '0:00',
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'end_time',
			[
				'label'       => esc_html__( 'End Time', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Seconds, or mm:ss / hh:mm:ss. Empty plays to the end.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '0:00',
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * CONTENT — Playback
	 * ──────────────────────────────────────────────────────────── */

	protected function register_playback_controls() {
		$this->start_controls_section(
			'section_playback',
			[
				'label' => esc_html__( 'Playback', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'aspect_ratio',
			[
				'label'   => esc_html__( 'Aspect Ratio', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '16:9',
				'options' => [
					'16:9'   => '16:9',
					'4:3'    => '4:3',
					'21:9'   => '21:9',
					'1:1'    => '1:1',
					'9:16'   => '9:16',
					'custom' => esc_html__( 'Custom', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'aspect_ratio_custom',
			[
				'label'       => esc_html__( 'Custom Ratio', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Width and height separated by a colon, e.g. 5:2.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '5:2',
				'condition'   => [ 'aspect_ratio' => 'custom' ],
			]
		);

		$this->add_control(
			'video_fit',
			[
				'label'       => esc_html__( 'Fit', 'sky-elementor-addons' ),
				'description' => esc_html__( 'How the video fills the ratio above when it does not match. Applies to self-hosted video — YouTube and Vimeo letterbox inside their own player. The cover photo always fills the frame.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'contain',
				'options'     => [
					'contain' => esc_html__( 'Contain (letterbox)', 'sky-elementor-addons' ),
					'cover'   => esc_html__( 'Cover (crop)', 'sky-elementor-addons' ),
				],
				'selectors'   => [
					// Video only. The poster used to share this variable so the two could
					// never disagree, but that letterboxed a set cover photo by default —
					// see the note on `.sa-vp-poster` in the stylesheet for the tradeoff.
					'{{WRAPPER}} .sa-video-player' => '--sa-vp-fit: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'lazy_load',
			[
				'label'        => esc_html__( 'Lazy Load', 'sky-elementor-addons' ),
				'description'  => esc_html__( 'Show only the poster until the visitor clicks. No iframe, provider script or video data is requested before that.', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'        => esc_html__( 'Autoplay', 'sky-elementor-addons' ),
				'description'  => esc_html__( 'Browsers only allow autoplay when the video is muted. Overrides Lazy Load.', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'play_on_view',
			[
				'label'        => esc_html__( 'Play On Viewport', 'sky-elementor-addons' ),
				'description'  => esc_html__( 'Starts playing when the player scrolls into view. Overrides Lazy Load.', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => [ 'autoplay!' => 'yes' ],
			]
		);

		$this->add_control(
			'muted',
			[
				'label'        => esc_html__( 'Muted', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'loop',
			[
				'label'        => esc_html__( 'Loop', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'reset_on_end',
			[
				'label'        => esc_html__( 'Reset On End', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => [ 'loop!' => 'yes' ],
			]
		);

		$this->add_control(
			'remember_position',
			[
				'label'        => esc_html__( 'Remember Settings', 'sky-elementor-addons' ),
				'description'  => esc_html__( 'Lets the player store volume, speed and quality choices in the browser.', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'volume',
			[
				'label'   => esc_html__( 'Default Volume', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => 0.05,
					],
				],
				'default' => [ 'size' => 1 ],
			]
		);

		$this->add_control(
			'preload',
			[
				'label'     => esc_html__( 'Preload', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'metadata',
				'options'   => [
					'none'     => esc_html__( 'None', 'sky-elementor-addons' ),
					'metadata' => esc_html__( 'Metadata', 'sky-elementor-addons' ),
					'auto'     => esc_html__( 'Auto', 'sky-elementor-addons' ),
				],
				'condition' => [ 'source_type' => [ 'hosted', 'external' ] ],
			]
		);

		$this->add_control(
			'frame_capture',
			[
				'label'       => esc_html__( 'Frame Capture', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Decodes real frames out of the video for chapter thumbnails and the seek-bar preview. Self-hosted only — YouTube and Vimeo keep their frames inside a cross-origin iframe. Capturing downloads video data, so "On First Play" keeps Lazy Load honest.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'view',
				'options'     => [
					'view' => esc_html__( 'When Scrolled Into View', 'sky-elementor-addons' ),
					'play' => esc_html__( 'On First Play', 'sky-elementor-addons' ),
					''     => esc_html__( 'Off', 'sky-elementor-addons' ),
				],
				'separator'   => 'before',
				'condition'   => [ 'source_type' => [ 'hosted', 'external' ] ],
			]
		);

		$this->add_control(
			'privacy_mode',
			[
				'label'        => esc_html__( 'Privacy Mode', 'sky-elementor-addons' ),
				'description'  => esc_html__( 'Uses youtube-nocookie.com and asks Vimeo not to track.', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [ 'source_type' => [ 'youtube', 'vimeo' ] ],
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * CONTENT — Player Controls
	 * ──────────────────────────────────────────────────────────── */

	protected function register_player_controls() {
		$this->start_controls_section(
			'section_player_controls',
			[
				'label' => esc_html__( 'Player Controls', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_controls',
			[
				'label'        => esc_html__( 'Show Controls', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		// Every entry here maps 1:1 onto a Plyr control name in get_control_list().
		$switchers = [
			'control_play_large'   => [ esc_html__( 'Big Play Button', 'sky-elementor-addons' ), 'yes' ],
			'control_play'         => [ esc_html__( 'Play / Pause', 'sky-elementor-addons' ), 'yes' ],
			'control_restart'      => [ esc_html__( 'Restart', 'sky-elementor-addons' ), '' ],
			'control_rewind'       => [ esc_html__( 'Rewind', 'sky-elementor-addons' ), '' ],
			'control_fast_forward' => [ esc_html__( 'Fast Forward', 'sky-elementor-addons' ), '' ],
			'control_progress'     => [ esc_html__( 'Progress Bar', 'sky-elementor-addons' ), 'yes' ],
			'control_current_time' => [ esc_html__( 'Current Time', 'sky-elementor-addons' ), 'yes' ],
			'control_duration'     => [ esc_html__( 'Duration', 'sky-elementor-addons' ), 'yes' ],
			'control_mute'         => [ esc_html__( 'Mute', 'sky-elementor-addons' ), 'yes' ],
			'control_volume'       => [ esc_html__( 'Volume', 'sky-elementor-addons' ), 'yes' ],
			'control_captions'     => [ esc_html__( 'Captions', 'sky-elementor-addons' ), 'yes' ],
			'control_settings'     => [ esc_html__( 'Settings', 'sky-elementor-addons' ), 'yes' ],
			'control_airplay'      => [ esc_html__( 'AirPlay', 'sky-elementor-addons' ), '' ],
			'control_fullscreen'   => [ esc_html__( 'Fullscreen', 'sky-elementor-addons' ), 'yes' ],
		];

		foreach ( $switchers as $key => $data ) {
			$this->add_control(
				$key,
				[
					'label'        => $data[0],
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => $data[1],
					'condition'    => [ 'show_controls' => 'yes' ],
				]
			);
		}

		// Plyr has no provider-side support for these two — hide them rather than
		// ship a button that does nothing on YouTube and Vimeo.
		$this->add_control(
			'control_pip',
			[
				'label'        => esc_html__( 'Picture in Picture', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => [
					'show_controls' => 'yes',
					'source_type'   => [ 'hosted', 'external' ],
				],
			]
		);

		$this->add_control(
			'control_download',
			[
				'label'        => esc_html__( 'Download', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => [
					'show_controls' => 'yes',
					'source_type'   => [ 'hosted', 'external' ],
				],
			]
		);

		$this->add_control(
			'seek_time',
			[
				'label'     => esc_html__( 'Seek Interval (s)', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 60,
				'default'   => 10,
				'separator' => 'before',
				'condition' => [ 'show_controls' => 'yes' ],
			]
		);

		$this->add_control(
			'invert_time',
			[
				'label'        => esc_html__( 'Show Time Remaining', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => [
					'show_controls'        => 'yes',
					'control_current_time' => 'yes',
				],
			]
		);

		$this->add_control(
			'settings_menu',
			[
				'label'       => esc_html__( 'Settings Menu', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'default'     => [ 'captions', 'speed' ],
				// No `quality` entry: Plyr builds that menu from multiple <source>
				// elements carrying a `size`, and this widget takes one source. Adding
				// it would ship a menu that can never have anything in it.
				'options'     => [
					'captions' => esc_html__( 'Captions', 'sky-elementor-addons' ),
					'speed'    => esc_html__( 'Speed', 'sky-elementor-addons' ),
					'loop'     => esc_html__( 'Loop', 'sky-elementor-addons' ),
				],
				'separator'   => 'before',
				'condition'   => [
					'show_controls'    => 'yes',
					'control_settings' => 'yes',
				],
			]
		);

		$this->add_control(
			'speed_options',
			[
				'label'       => esc_html__( 'Speed Options', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Comma separated. YouTube and Vimeo ignore anything outside 0.5 – 2.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '0.5, 0.75, 1, 1.25, 1.5, 2',
				'condition'   => [
					'show_controls'    => 'yes',
					'control_settings' => 'yes',
					'settings_menu'    => 'speed',
				],
			]
		);

		$this->add_control(
			'autohide',
			[
				'label'        => esc_html__( 'Auto Hide Controls', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
				'condition'    => [ 'show_controls' => 'yes' ],
			]
		);

		$this->add_control(
			'click_to_play',
			[
				'label'        => esc_html__( 'Click To Play', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'keyboard_global',
			[
				'label'        => esc_html__( 'Global Keyboard Shortcuts', 'sky-elementor-addons' ),
				'description'  => esc_html__( 'Off means shortcuts only work once the player has focus. Keep it off when a page has more than one player.', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'tooltip_controls',
			[
				'label'        => esc_html__( 'Control Tooltips', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => [ 'show_controls' => 'yes' ],
			]
		);

		$this->add_control(
			'tooltip_seek',
			[
				'label'        => esc_html__( 'Seek Tooltip', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'show_controls'    => 'yes',
					'control_progress' => 'yes',
				],
			]
		);

		$this->add_control(
			'scrub_preview',
			[
				'label'        => esc_html__( 'Hover Frame Preview', 'sky-elementor-addons' ),
				'description'  => esc_html__( 'Shows the actual frame at the second under the cursor while hovering the seek bar. Needs Frame Capture, so self-hosted only.', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'show_controls'    => 'yes',
					'control_progress' => 'yes',
					'source_type'      => [ 'hosted', 'external' ],
					'frame_capture!'   => '',
				],
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * CONTENT — Chapters
	 * ──────────────────────────────────────────────────────────── */

	protected function register_chapters_controls() {
		$this->start_controls_section(
			'section_chapters',
			[
				'label' => esc_html__( 'Chapters', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_chapters',
			[
				'label'        => esc_html__( 'Enable Chapters', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'chapters_source',
			[
				'label'     => esc_html__( 'Chapters From', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'repeater',
				'options'   => [
					'repeater' => esc_html__( 'Chapter List', 'sky-elementor-addons' ),
					'paste'    => esc_html__( 'Paste Timestamps', 'sky-elementor-addons' ),
				],
				'condition' => [ 'enable_chapters' => 'yes' ],
			]
		);

		$this->add_control(
			'chapters_text',
			[
				'label'       => esc_html__( 'Timestamps', 'sky-elementor-addons' ),
				'description' => esc_html__( 'One chapter per line, timestamp first — the same block you already have in a YouTube description. Lines without a timestamp are ignored. Per-chapter thumbnails are only available in the Chapter List mode.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 10,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => "0:00 Intro\n1:24 Getting started\n5:40 - The good part\n12:03 – Wrapping up",
				'condition'   => [
					'enable_chapters' => 'yes',
					'chapters_source' => 'paste',
				],
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'chapter_time',
			[
				'label'       => esc_html__( 'Time', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Seconds, or mm:ss / hh:mm:ss.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '0:00',
				'placeholder' => '0:00',
			]
		);

		$repeater->add_control(
			'chapter_label',
			[
				'label'       => esc_html__( 'Label', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Chapter', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'chapter_image',
			[
				'label'       => esc_html__( 'Thumbnail', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Optional. Overrides the auto-captured frame.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'chapters',
			[
				'label'       => esc_html__( 'Chapter List', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ chapter_time }}} — {{{ chapter_label }}}',
				'default'     => [
					[
						'chapter_time'  => '0:00',
						'chapter_label' => esc_html__( 'Intro', 'sky-elementor-addons' ),
					],
				],
				'condition'   => [
					'enable_chapters' => 'yes',
					'chapters_source' => 'repeater',
				],
			]
		);

		$this->add_control(
			'chapter_progress_mode',
			[
				'label'       => esc_html__( 'On The Progress Bar', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Segments split the seek bar into one block per chapter and work on every source. Markers are Plyr\'s own single-pixel ticks.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'segments',
				'options'     => [
					'segments' => esc_html__( 'Segmented Bar', 'sky-elementor-addons' ),
					'markers'  => esc_html__( 'Markers', 'sky-elementor-addons' ),
					'none'     => esc_html__( 'Nothing', 'sky-elementor-addons' ),
				],
				'separator'   => 'before',
				'condition'   => [ 'enable_chapters' => 'yes' ],
			]
		);

		$this->add_control(
			'chapter_overlay_title',
			[
				'label'       => esc_html__( 'Chapter Name On Video', 'sky-elementor-addons' ),
				'description' => esc_html__( 'On Hover follows the control bar — it appears when the controls do and fades with them during playback.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'hover',
				'options'     => [
					'hover'  => esc_html__( 'On Hover', 'sky-elementor-addons' ),
					'always' => esc_html__( 'Always Visible', 'sky-elementor-addons' ),
					''       => esc_html__( 'Hidden', 'sky-elementor-addons' ),
				],
				'condition'   => [ 'enable_chapters' => 'yes' ],
			]
		);

		$this->add_control(
			'chapter_list',
			[
				'label'        => esc_html__( 'Show List Below Player', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
				'condition'    => [ 'enable_chapters' => 'yes' ],
			]
		);

		$this->add_control(
			'chapter_layout',
			[
				'label'     => esc_html__( 'Layout', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'list',
				'options'   => [
					'list' => esc_html__( 'List', 'sky-elementor-addons' ),
					'rail' => esc_html__( 'Rail', 'sky-elementor-addons' ),
					'grid' => esc_html__( 'Grid', 'sky-elementor-addons' ),
				],
				'condition' => [
					'enable_chapters' => 'yes',
					'chapter_list'    => 'yes',
				],
			]
		);

		$this->add_control(
			'chapter_item_content',
			[
				'label'       => esc_html__( 'Show In Each Item', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Thumbnail only leaves the items blank unless Thumbnails is on.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'both',
				'options'     => [
					'both'  => esc_html__( 'Time + Label', 'sky-elementor-addons' ),
					'label' => esc_html__( 'Label Only', 'sky-elementor-addons' ),
					'time'  => esc_html__( 'Time Only', 'sky-elementor-addons' ),
					'none'  => esc_html__( 'Thumbnail Only', 'sky-elementor-addons' ),
				],
				'condition'   => [
					'enable_chapters' => 'yes',
					'chapter_list'    => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'chapter_columns',
			[
				'label'          => esc_html__( 'Columns', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => [
					'1' => esc_html__( '1 Column', 'sky-elementor-addons' ),
					'2' => esc_html__( '2 Columns', 'sky-elementor-addons' ),
					'3' => esc_html__( '3 Columns', 'sky-elementor-addons' ),
					'4' => esc_html__( '4 Columns', 'sky-elementor-addons' ),
					'5' => esc_html__( '5 Columns', 'sky-elementor-addons' ),
					'6' => esc_html__( '6 Columns', 'sky-elementor-addons' ),
				],
				// Desktop mirrors the stylesheet's own `repeat(3, …)` on
				// `.sa-vp-chapters--grid`. Tablet and mobile drop to 2 here rather than in a
				// LESS breakpoint block on purpose: a stylesheet rule would be invisible to
				// the panel, so a user raising the desktop count could not see why the narrow
				// views ignored it. Stated as control defaults they show in the responsive
				// tabs and can be overridden per device.
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '2',
				'selectors'      => [
					'{{WRAPPER}} .sa-vp-chapters--grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				],
				'condition'      => [
					'enable_chapters' => 'yes',
					'chapter_list'    => 'yes',
					'chapter_layout'  => 'grid',
				],
			]
		);

		$this->add_control(
			'chapter_progress_fill',
			[
				'label'        => esc_html__( 'Per-Chapter Progress', 'sky-elementor-addons' ),
				'description'  => esc_html__( 'Fills each item as the playhead moves through that chapter.', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'enable_chapters' => 'yes',
					'chapter_list'    => 'yes',
				],
			]
		);

		$this->add_control(
			'chapter_thumbnails',
			[
				'label'        => esc_html__( 'Thumbnails', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
				'condition'    => [
					'enable_chapters' => 'yes',
					'chapter_list'    => 'yes',
				],
			]
		);

		$this->add_control(
			'chapter_frame_note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Empty thumbnails are filled with the real frame at each chapter time when Frame Capture is on — see Playback. Self-hosted video only.', 'sky-elementor-addons' ),
				'content_classes' => 'elementor-descriptor',
				'condition'       => [
					'enable_chapters'    => 'yes',
					'chapter_list'       => 'yes',
					'chapter_thumbnails' => 'yes',
					'source_type'        => [ 'hosted', 'external' ],
				],
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * CONTENT — Sticky
	 * ──────────────────────────────────────────────────────────── */

	protected function register_sticky_controls() {
		$this->start_controls_section(
			'section_sticky',
			[
				'label' => esc_html__( 'Sticky On Scroll', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_sticky',
			[
				'label'        => esc_html__( 'Enable Sticky', 'sky-elementor-addons' ),
				'description'  => esc_html__( 'While playing, the player shrinks into a corner once it scrolls out of view.', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'sticky_position',
			[
				'label'     => esc_html__( 'Position', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'bottom-right',
				'options'   => [
					'top-left'     => esc_html__( 'Top Left', 'sky-elementor-addons' ),
					'top-right'    => esc_html__( 'Top Right', 'sky-elementor-addons' ),
					'bottom-left'  => esc_html__( 'Bottom Left', 'sky-elementor-addons' ),
					'bottom-right' => esc_html__( 'Bottom Right', 'sky-elementor-addons' ),
				],
				'condition' => [ 'enable_sticky' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'sticky_width',
			[
				'label'      => esc_html__( 'Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw' ],
				'range'      => [
					'px' => [
						'min' => 180,
						'max' => 640,
					],
					'vw' => [
						'min' => 20,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 360,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-video-player' => '--sa-vp-sticky-width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'enable_sticky' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'sticky_offset',
			[
				'label'      => esc_html__( 'Edge Offset', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 120,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-video-player' => '--sa-vp-sticky-offset: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'enable_sticky' => 'yes' ],
			]
		);

		$this->add_control(
			'sticky_close',
			[
				'label'        => esc_html__( 'Close Button', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [ 'enable_sticky' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * CONTENT — Video SEO
	 * ──────────────────────────────────────────────────────────── */

	protected function register_schema_controls() {
		$this->start_controls_section(
			'section_schema',
			[
				'label' => esc_html__( 'Video SEO', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_schema',
			[
				'label'        => esc_html__( 'VideoObject Schema', 'sky-elementor-addons' ),
				'description'  => esc_html__( 'Outputs JSON-LD so search engines can show the video as a rich result. Title, poster and upload date are all required — nothing is printed until each is filled.', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'schema_name',
			[
				'label'       => esc_html__( 'Title', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
				'condition'   => [ 'enable_schema' => 'yes' ],
			]
		);

		$this->add_control(
			'schema_description',
			[
				'label'     => esc_html__( 'Description', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::TEXTAREA,
				'rows'      => 3,
				'dynamic'   => [ 'active' => true ],
				'condition' => [ 'enable_schema' => 'yes' ],
			]
		);

		$this->add_control(
			'schema_upload_date',
			[
				'label'          => esc_html__( 'Upload Date', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::DATE_TIME,
				'picker_options' => [ 'enableTime' => false ],
				'condition'      => [ 'enable_schema' => 'yes' ],
			]
		);

		$this->add_control(
			'schema_duration',
			[
				'label'       => esc_html__( 'Duration', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Seconds, or mm:ss / hh:mm:ss. Output as ISO 8601.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '3:45',
				'condition'   => [ 'enable_schema' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * STYLE — Player
	 * ──────────────────────────────────────────────────────────── */

	protected function register_player_style() {
		$this->start_controls_section(
			'section_style_player',
			[
				'label' => esc_html__( 'Player', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'player_max_width',
			[
				'label'      => esc_html__( 'Max Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 200,
						'max' => 1600,
					],
				],
				'description' => esc_html__( 'Caps the player and the chapter list together, so the two stay aligned.', 'sky-elementor-addons' ),
				'selectors'  => [
					// A variable, not `max-width` on the frame: the chapter list is a
					// sibling of the frame, not a child, so capping the frame alone
					// left the list spanning the full widget width while the player
					// sat centred and narrower. LESS applies this to both.
					'{{WRAPPER}} .sa-video-player' => '--sa-vp-max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'player_align',
			[
				'label'     => esc_html__( 'Alignment', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'flex-start' => [
						'title' => esc_html__( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'     => [
						'title' => esc_html__( 'Center', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-center',
					],
					'flex-end'   => [
						'title' => esc_html__( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'player_background',
			[
				'label'     => esc_html__( 'Letterbox Background', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-video-background: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'player_border',
				'selector' => '{{WRAPPER}} .sa-vp-frame',
			]
		);

		$this->add_responsive_control(
			'player_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-vp-frame' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'player_shadow',
				'selector' => '{{WRAPPER}} .sa-vp-frame',
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * STYLE — Controls
	 * ──────────────────────────────────────────────────────────── */

	protected function register_controls_style() {
		$this->start_controls_section(
			'section_style_controls',
			[
				'label'     => esc_html__( 'Controls', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_controls' => 'yes' ],
			]
		);

		$this->add_control(
			'accent_color',
			[
				'label'     => esc_html__( 'Accent Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-color-main: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'controls_bar_background',
				'label'    => esc_html__( 'Bar Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'selector' => '{{WRAPPER}} .plyr--video .plyr__controls',
			]
		);

		$this->start_controls_tabs( 'tabs_controls_colors' );

		$this->start_controls_tab(
			'tab_controls_normal',
			[ 'label' => esc_html__( 'Normal', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'controls_icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-video-control-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_controls_hover',
			[ 'label' => esc_html__( 'Hover', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'controls_icon_color_hover',
			[
				'label'     => esc_html__( 'Icon Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-video-control-color-hover: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'controls_icon_background_hover',
			[
				'label'     => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-video-control-background-hover: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'controls_icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 10,
						'max' => 40,
					],
				],
				'separator'  => 'before',
				'selectors'  => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-control-icon-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'controls_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 40,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-control-spacing: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'controls_radius',
			[
				'label'      => esc_html__( 'Button Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 40,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-control-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'controls_bar_padding',
			[
				'label'      => esc_html__( 'Bar Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .plyr--video .plyr__controls' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'controls_time_color',
			[
				'label'     => esc_html__( 'Time Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .plyr--video .plyr__time' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'controls_time_typography',
				'selector' => '{{WRAPPER}} .plyr--video .plyr__time',
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * STYLE — Progress Bar
	 * ──────────────────────────────────────────────────────────── */

	protected function register_progress_style() {
		$this->start_controls_section(
			'section_style_progress',
			[
				'label'     => esc_html__( 'Progress Bar', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_controls'    => 'yes',
					'control_progress' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'progress_height',
			[
				'label'      => esc_html__( 'Track Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 2,
						'max' => 20,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-range-track-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'progress_track_color',
			[
				'label'     => esc_html__( 'Track Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-video-range-track-background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'progress_buffer_color',
			[
				'label'     => esc_html__( 'Buffered Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-video-progress-buffered-background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'progress_fill_color',
			[
				'label'       => esc_html__( 'Fill Color', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Defaults to the accent color.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-range-fill-background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'progress_thumb_heading',
			[
				'label'     => esc_html__( 'Handle', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'progress_thumb_size',
			[
				'label'      => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 6,
						'max' => 30,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-range-thumb-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'progress_thumb_color',
			[
				'label'     => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-range-thumb-background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'preview_heading',
			[
				'label'     => esc_html__( 'Hover Frame Preview', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'scrub_preview' => 'yes',
					'source_type'   => [ 'hosted', 'external' ],
				],
			]
		);

		$this->add_responsive_control(
			'preview_width',
			[
				'label'      => esc_html__( 'Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 80,
						'max' => 320,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 160,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-video-player' => '--sa-vp-preview-width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'scrub_preview' => 'yes',
					'source_type'   => [ 'hosted', 'external' ],
				],
			]
		);

		$this->add_responsive_control(
			'preview_radius',
			[
				'label'      => esc_html__( 'Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 24,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-vp-preview' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'scrub_preview' => 'yes',
					'source_type'   => [ 'hosted', 'external' ],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'preview_border',
				'selector'  => '{{WRAPPER}} .sa-vp-preview',
				'condition' => [
					'scrub_preview' => 'yes',
					'source_type'   => [ 'hosted', 'external' ],
				],
			]
		);

		$this->add_control(
			'progress_marker_heading',
			[
				'label'     => esc_html__( 'Chapter Markers', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'enable_chapters' => 'yes',
					'chapter_progress_mode' => 'markers',
				],
			]
		);

		$this->add_control(
			'progress_marker_color',
			[
				'label'     => esc_html__( 'Marker Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-progress-marker-background: {{VALUE}};',
				],
				'condition' => [
					'enable_chapters' => 'yes',
					'chapter_progress_mode' => 'markers',
				],
			]
		);

		$this->add_responsive_control(
			'progress_marker_width',
			[
				'label'      => esc_html__( 'Marker Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 10,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-progress-marker-width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'enable_chapters' => 'yes',
					'chapter_progress_mode' => 'markers',
				],
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * STYLE — Tooltip
	 * ──────────────────────────────────────────────────────────── */

	protected function register_tooltip_style() {
		$this->start_controls_section(
			'section_style_tooltip',
			[
				'label'      => esc_html__( 'Tooltip', 'sky-elementor-addons' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'show_controls',
							'operator' => '===',
							'value'    => 'yes',
						],
						[
							'relation' => 'or',
							'terms'    => [
								[
									'name'     => 'tooltip_controls',
									'operator' => '===',
									'value'    => 'yes',
								],
								[
									'name'     => 'tooltip_seek',
									'operator' => '===',
									'value'    => 'yes',
								],
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'tooltip_background',
			[
				'label'     => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-tooltip-background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tooltip_color',
			[
				'label'     => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-tooltip-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'tooltip_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 30,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-tooltip-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'tooltip_arrow_size',
			[
				'label'      => esc_html__( 'Arrow Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 16,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-tooltip-arrow-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * STYLE — Settings Menu
	 * ──────────────────────────────────────────────────────────── */

	protected function register_menu_style() {
		$this->start_controls_section(
			'section_style_menu',
			[
				'label'     => esc_html__( 'Settings Menu', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_controls'    => 'yes',
					'control_settings' => 'yes',
				],
			]
		);

		$this->add_control(
			'menu_background',
			[
				'label'     => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-menu-background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'menu_color',
			[
				'label'     => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-menu-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'menu_arrow_color',
			[
				'label'     => esc_html__( 'Arrow Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-menu-arrow-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 30,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-menu-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * STYLE — Captions
	 * ──────────────────────────────────────────────────────────── */

	protected function register_captions_style() {
		$this->start_controls_section(
			'section_style_captions',
			[
				'label'     => esc_html__( 'Captions', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_controls'    => 'yes',
					'control_captions' => 'yes',
				],
			]
		);

		$this->add_control(
			'captions_background',
			[
				'label'     => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-captions-background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'captions_color',
			[
				'label'     => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--plyr-captions-text-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'captions_typography',
				'selector' => '{{WRAPPER}} .plyr__caption',
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * STYLE — Play Button
	 *
	 * One section for BOTH play buttons, because to a visitor they are the same
	 * button. `.sa-vp-play` is the one painted over the poster before Plyr is
	 * built (lazy load); `.plyr__control--overlaid` is Plyr's own, painted once
	 * the player exists and the video is paused. They are never on screen at the
	 * same time, so two style sections read as one control that does nothing.
	 *
	 * Every control below writes both, and every clause carries its own
	 * {{WRAPPER}} — Elementor substitutes that token by plain string replace, so
	 * a second clause without it would leak to every video player on the page.
	 * ──────────────────────────────────────────────────────────── */

	protected function register_play_button_style() {
		$this->start_controls_section(
			'section_style_play_button',
			[
				'label'      => esc_html__( 'Play Button', 'sky-elementor-addons' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				// Shown when either button can appear: the poster one (lazy load),
				// or Plyr's own (controls on *and* the big play button enabled).
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'lazy_load',
							'operator' => '===',
							'value'    => 'yes',
						],
						[
							'relation' => 'and',
							'terms'    => [
								[
									'name'     => 'show_controls',
									'operator' => '===',
									'value'    => 'yes',
								],
								[
									'name'     => 'control_play_large',
									'operator' => '===',
									'value'    => 'yes',
								],
							],
						],
					],
				],
			]
		);

		$this->add_responsive_control(
			'play_button_icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 10,
						'max' => 100,
					],
				],
				// Plyr's own `--plyr-control-icon-size` default. Emitted always, so
				// the control bar's Icon Size can no longer reach this button — that
				// shared variable was the reason a bar setting moved the big circle.
				'default'    => [
					'unit' => 'px',
					'size' => 18,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-vp-play svg'              => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .plyr__control--overlaid svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'play_button_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-vp-play'              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .plyr__control--overlaid' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'play_button_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-vp-play'              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .plyr__control--overlaid' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_play_button' );

		$this->start_controls_tab(
			'tab_play_button_normal',
			[ 'label' => esc_html__( 'Normal', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'play_button_color',
			[
				'label'     => esc_html__( 'Icon Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-vp-play'              => 'color: {{VALUE}};',
					'{{WRAPPER}} .plyr__control--overlaid' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'play_button_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'selector' => '{{WRAPPER}} .sa-vp-play, {{WRAPPER}} .plyr__control--overlaid',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_play_button_hover',
			[ 'label' => esc_html__( 'Hover', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'play_button_color_hover',
			[
				'label'     => esc_html__( 'Icon Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					// The poster button reacts to hovering the whole cover, not just
					// the circle — the cover is what carries the click handler.
					'{{WRAPPER}} .sa-vp-overlay:hover .sa-vp-play' => 'color: {{VALUE}};',
					'{{WRAPPER}} .plyr__control--overlaid:hover'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .plyr__control--overlaid:focus'   => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'          => 'play_button_background_hover',
				'label'         => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'         => [ 'classic', 'gradient' ],
				'exclude'       => [ 'image' ],
				'selector'      => '{{WRAPPER}} .sa-vp-overlay:hover .sa-vp-play, {{WRAPPER}} .plyr__control--overlaid:hover, {{WRAPPER}} .plyr__control--overlaid:focus',
				'fields_options' => [
					// A gradient in the Normal tab paints `background-image`, and the
					// stock classic field only writes `background-color` — so a flat
					// hover colour would sit *under* the gradient and never show.
					// Clearing the image here is what makes gradient → flat work.
					// Harmless on the gradient type: its own field writes
					// `background-image` after this one and wins on order.
					'color' => [
						'selectors' => [
							'{{SELECTOR}}' => 'background-color: {{VALUE}}; background-image: none;',
						],
					],
				],
			]
		);

		$this->add_control(
			'play_button_scale_hover',
			[
				'label'     => esc_html__( 'Scale', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 1,
						'max'  => 2,
						'step' => 0.05,
					],
				],
				'default'   => [ 'size' => 1.1 ],
				'selectors' => [
					'{{WRAPPER}} .sa-vp-overlay:hover .sa-vp-play' => 'transform: scale({{SIZE}});',
					// Plyr centres this button with a translate. Dropping it here
					// would shift the button by half its own size on hover.
					'{{WRAPPER}} .plyr__control--overlaid:hover'   => 'transform: translate(-50%, -50%) scale({{SIZE}});',
					'{{WRAPPER}} .plyr__control--overlaid:focus'   => 'transform: translate(-50%, -50%) scale({{SIZE}});',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'play_button_shadow',
				'selector'  => '{{WRAPPER}} .sa-vp-play, {{WRAPPER}} .plyr__control--overlaid',
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * STYLE — Poster
	 *
	 * The lazy-load cover only: the poster image and the tint laid over it. The
	 * play button sitting on top is styled in the Play Button section above,
	 * together with Plyr's own.
	 * ──────────────────────────────────────────────────────────── */

	protected function register_poster_style() {
		$this->start_controls_section(
			'section_style_poster',
			[
				'label'     => esc_html__( 'Poster', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'lazy_load' => 'yes' ],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'overlay_background',
				'label'    => esc_html__( 'Tint', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				// Painted on a pseudo element, which keeps the tint independent of the
				// filters applied to the poster image below it.
				'selector' => '{{WRAPPER}} .sa-vp-overlay::before',
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'      => 'overlay_filters',
				'label'     => esc_html__( 'Image Filters', 'sky-elementor-addons' ),
				'selector'  => '{{WRAPPER}} .sa-vp-poster',
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * STYLE — Chapter Segments
	 * ──────────────────────────────────────────────────────────── */

	protected function register_segments_style() {
		$this->start_controls_section(
			'section_style_segments',
			[
				'label'     => esc_html__( 'Chapter Segments', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_chapters'       => 'yes',
					'chapter_progress_mode' => 'segments',
					'show_controls'         => 'yes',
					'control_progress'      => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'segment_gap',
			[
				'label'      => esc_html__( 'Gap', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 12,
						'step' => 0.5,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 2,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-video-player' => '--sa-vp-seg-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'segment_radius',
			[
				'label'      => esc_html__( 'Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 10,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 2,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-video-player' => '--sa-vp-seg-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'segment_hover_scale',
			[
				'label'       => esc_html__( 'Hover Grow', 'sky-elementor-addons' ),
				'description' => esc_html__( 'How much the hovered segment thickens, as a multiplier of the track height.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'min'  => 1,
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'default'     => [ 'size' => 1.8 ],
				'selectors'   => [
					'{{WRAPPER}} .sa-video-player' => '--sa-vp-seg-hover-scale: {{SIZE}};',
				],
			]
		);

		$this->add_control(
			'segment_track_color',
			[
				'label'     => esc_html__( 'Segment Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-video-player' => '--sa-vp-seg-track: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'segment_fill_color',
			[
				'label'       => esc_html__( 'Played Color', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Defaults to the accent color.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => [
					'{{WRAPPER}} .sa-video-player' => '--sa-vp-seg-fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'segment_tooltip_heading',
			[
				'label'     => esc_html__( 'Hover Label', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'segment_tooltip_background',
			[
				'label'     => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-vp-seg-tip' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'segment_tooltip_color',
			[
				'label'     => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-vp-seg-tip' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'segment_tooltip_typography',
				'selector' => '{{WRAPPER}} .sa-vp-seg-tip',
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * STYLE — Chapter Title Overlay
	 * ──────────────────────────────────────────────────────────── */

	protected function register_chapter_overlay_style() {
		$this->start_controls_section(
			'section_style_chapter_overlay',
			[
				'label'     => esc_html__( 'Chapter Title Overlay', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_chapters'       => 'yes',
					'chapter_overlay_title!' => '',
				],
			]
		);

		$this->add_control(
			'chapter_overlay_position',
			[
				'label'     => esc_html__( 'Position', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'flex-start' => [
						'title' => esc_html__( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'     => [
						'title' => esc_html__( 'Center', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-center',
					],
					'flex-end'   => [
						'title' => esc_html__( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'   => 'flex-start',
				'selectors' => [
					'{{WRAPPER}} .sa-vp-chapter-overlay' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'chapter_overlay_offset',
			[
				'label'      => esc_html__( 'Offset', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default'    => [
					'top'      => 16,
					'right'    => 16,
					'bottom'   => 0,
					'left'     => 16,
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-vp-chapter-overlay' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'chapter_overlay_color',
			[
				'label'     => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-vp-chapter-overlay > span' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'chapter_overlay_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'selector' => '{{WRAPPER}} .sa-vp-chapter-overlay > span',
			]
		);

		$this->add_responsive_control(
			'chapter_overlay_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-vp-chapter-overlay > span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'chapter_overlay_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-vp-chapter-overlay > span' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'chapter_overlay_typography',
				'selector' => '{{WRAPPER}} .sa-vp-chapter-overlay > span',
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * STYLE — Chapter List
	 * ──────────────────────────────────────────────────────────── */

	protected function register_chapters_style() {
		$this->start_controls_section(
			'section_style_chapters',
			[
				'label'     => esc_html__( 'Chapter List', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_chapters' => 'yes',
					'chapter_list'    => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'chapters_max_height',
			[
				'label'      => esc_html__( 'Max Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'range'      => [
					'px' => [
						'min' => 80,
						'max' => 800,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-vp-chapters' => 'max-height: {{SIZE}}{{UNIT}}; overflow-y: auto;',
				],
			]
		);

		$this->add_responsive_control(
			'chapters_gap',
			[
				'label'      => esc_html__( 'Gap From Player', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 80,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-vp-chapters' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'chapters_item_gap',
			[
				'label'      => esc_html__( 'Gap Between Items', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 8,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-vp-chapters' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'chapter_thumb_width',
			[
				'label'      => esc_html__( 'Thumbnail Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 48,
						'max' => 320,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 120,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-video-player' => '--sa-vp-thumb-width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'chapter_thumbnails' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'chapter_thumb_radius',
			[
				'label'      => esc_html__( 'Thumbnail Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-vp-chapter-thumb' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [ 'chapter_thumbnails' => 'yes' ],
			]
		);

		$this->add_control(
			'chapter_fill_color',
			[
				'label'       => esc_html__( 'Progress Fill', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Defaults to the accent color.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => [
					'{{WRAPPER}} .sa-video-player' => '--sa-vp-chapter-fill: {{VALUE}};',
				],
				'condition'   => [ 'chapter_progress_fill' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'chapter_padding',
			[
				'label'      => esc_html__( 'Item Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-vp-chapter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'chapter_radius',
			[
				'label'      => esc_html__( 'Item Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-vp-chapter' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'chapter_typography',
				'selector' => '{{WRAPPER}} .sa-vp-chapter',
			]
		);

		$this->start_controls_tabs( 'tabs_chapters' );

		$this->start_controls_tab(
			'tab_chapter_normal',
			[ 'label' => esc_html__( 'Normal', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'chapter_color',
			[
				'label'     => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-vp-chapter' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'chapter_time_color',
			[
				'label'     => esc_html__( 'Time Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-vp-chapter-time' => 'color: {{VALUE}};',
				],
				'condition' => [ 'chapter_item_content' => [ 'both', 'time' ] ],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'chapter_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'selector' => '{{WRAPPER}} .sa-vp-chapter',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_chapter_active',
			[ 'label' => esc_html__( 'Active', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'chapter_color_active',
			[
				'label'     => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-vp-chapter:hover, {{WRAPPER}} .sa-vp-chapter.sa-active' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'chapter_time_color_active',
			[
				'label'     => esc_html__( 'Time Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-vp-chapter:hover .sa-vp-chapter-time, {{WRAPPER}} .sa-vp-chapter.sa-active .sa-vp-chapter-time' => 'color: {{VALUE}};',
				],
				'condition' => [ 'chapter_item_content' => [ 'both', 'time' ] ],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'           => 'chapter_background_active',
				'label'          => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'          => [ 'classic', 'gradient' ],
				'exclude'        => [ 'image' ],
				'selector'       => '{{WRAPPER}} .sa-vp-chapter:hover, {{WRAPPER}} .sa-vp-chapter.sa-active',
				// See the Play Button hover group — clears a Normal-tab gradient so a
				// flat active colour is not painted underneath it.
				'fields_options' => [
					'color' => [
						'selectors' => [
							'{{SELECTOR}}' => 'background-color: {{VALUE}}; background-image: none;',
						],
					],
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * STYLE — Sticky Player
	 * ──────────────────────────────────────────────────────────── */

	protected function register_sticky_style() {
		$this->start_controls_section(
			'section_style_sticky',
			[
				'label'     => esc_html__( 'Sticky Player', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'enable_sticky' => 'yes' ],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'sticky_shadow',
				'selector' => '{{WRAPPER}} .sa-vp-is-sticky .sa-vp-frame',
			]
		);

		$this->add_responsive_control(
			'sticky_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-vp-is-sticky .sa-vp-frame' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'sticky_close_heading',
			[
				'label'     => esc_html__( 'Close Button', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ 'sticky_close' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'sticky_close_size',
			[
				'label'      => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 16,
						'max' => 60,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-vp-close' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'sticky_close' => 'yes' ],
			]
		);

		$this->add_control(
			'sticky_close_color',
			[
				'label'     => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-vp-close' => 'color: {{VALUE}};',
				],
				'condition' => [ 'sticky_close' => 'yes' ],
			]
		);

		$this->add_control(
			'sticky_close_background',
			[
				'label'     => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-vp-close' => 'background-color: {{VALUE}};',
				],
				'condition' => [ 'sticky_close' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	/* ─────────────────────────────────────────────────────────────
	 * Data helpers
	 * ──────────────────────────────────────────────────────────── */

	/**
	 * "90" | "1:30" | "01:02:03" → seconds. Anything unparseable → 0.
	 */
	protected function parse_time( $value ) {
		$value = trim( (string) $value );

		if ( '' === $value ) {
			return 0;
		}

		$parts   = array_reverse( explode( ':', $value ) );
		$seconds = 0;

		foreach ( $parts as $index => $part ) {
			if ( $index > 2 ) {
				break;
			}
			$seconds += (int) $part * pow( 60, $index );
		}

		return max( 0, $seconds );
	}

	/**
	 * Seconds → "1:23" / "1:02:03", for the chapter list.
	 */
	protected function format_time( $seconds ) {
		$seconds = (int) $seconds;
		$hours   = floor( $seconds / 3600 );
		$minutes = floor( ( $seconds % 3600 ) / 60 );
		$rest    = $seconds % 60;

		if ( $hours ) {
			return sprintf( '%d:%02d:%02d', $hours, $minutes, $rest );
		}

		return sprintf( '%d:%02d', $minutes, $rest );
	}

	/**
	 * Seconds → ISO 8601 duration (225 → PT3M45S), the shape schema.org expects.
	 */
	protected function iso_duration( $seconds ) {
		$seconds = (int) $seconds;

		if ( $seconds < 1 ) {
			return '';
		}

		$hours    = floor( $seconds / 3600 );
		$minutes  = floor( ( $seconds % 3600 ) / 60 );
		$rest     = $seconds % 60;
		$duration = 'PT';

		if ( $hours ) {
			$duration .= $hours . 'H';
		}
		if ( $minutes ) {
			$duration .= $minutes . 'M';
		}
		if ( $rest || 'PT' === $duration ) {
			$duration .= $rest . 'S';
		}

		return $duration;
	}

	/**
	 * Normalize the source controls into what render() and the JS both need:
	 * provider + embed id for YouTube/Vimeo, a file URL for everything else.
	 */
	protected function get_video_data( $settings ) {
		$type = $settings['source_type'];
		$data = [
			'provider' => 'html5',
			'video_id' => '',
			'url'      => '',
		];

		if ( 'hosted' === $type ) {
			$data['url'] = $settings['hosted_url']['url'] ?? '';
			return $data;
		}

		if ( 'external' === $type ) {
			$data['url'] = $settings['external_url']['url'] ?? '';
			return $data;
		}

		$url = trim( (string) ( ( 'vimeo' === $type ) ? $settings['vimeo_url'] : $settings['youtube_url'] ) );

		if ( ! $url ) {
			return $data;
		}

		// Elementor's own parser — same one the core Video widget uses.
		$properties = Embed::get_video_properties( $url );

		$data['provider'] = $properties['provider'] ?? $type;
		$data['video_id'] = $properties['video_id'] ?? '';
		$data['url']      = $url;

		return $data;
	}

	/**
	 * "16:9" → "16 / 9" for the CSS `aspect-ratio` on the frame.
	 *
	 * The frame needs its own ratio while lazy loading: until Plyr is built there is
	 * nothing inside it with height (the provider placeholder is an empty div, and a
	 * `preload="none"` video reports none either), so the poster overlay would
	 * collapse to zero. Anything unparseable falls back to 16:9.
	 */
	protected function get_css_ratio( $settings ) {
		$ratio = $settings['aspect_ratio'];

		if ( 'custom' === $ratio ) {
			$ratio = trim( (string) $settings['aspect_ratio_custom'] );
		}

		$parts = explode( ':', $ratio );

		if ( 2 !== count( $parts ) ) {
			return '16 / 9';
		}

		$width  = (float) trim( $parts[0] );
		$height = (float) trim( $parts[1] );

		if ( $width <= 0 || $height <= 0 ) {
			return '16 / 9';
		}

		return $width . ' / ' . $height;
	}

	/**
	 * The chosen poster, or the provider thumbnail when none was picked.
	 *
	 * YouTube gets `maxresdefault` on the optimistic assumption it exists. It does
	 * not for every video, and the browser settles that at paint time rather than
	 * PHP settling it at render time — see get_poster_fallback().
	 */
	protected function get_poster_url( $settings, $video ) {
		$poster = $settings['poster']['url'] ?? '';

		if ( $poster ) {
			return $poster;
		}

		if ( ! $video['video_id'] ) {
			return '';
		}

		if ( 'youtube' === $video['provider'] ) {
			return $this->youtube_thumbnail_url( $video['video_id'], 'maxresdefault' );
		}

		if ( 'vimeo' === $video['provider'] ) {
			return 'https://vumbnail.com/' . rawurlencode( $video['video_id'] ) . '.jpg';
		}

		return '';
	}

	/**
	 * The thumbnail to drop back to when the optimistic one turns out not to exist.
	 *
	 * `maxresdefault` is the only YouTube size served at full 16:9, but YouTube only
	 * generates it for videos uploaded above 720p. For the rest it answers 404 with a
	 * 120x90 grey placeholder, which still decodes — so the poster would paint as a
	 * dead grey player rather than failing loudly.
	 *
	 * Which sizes exist could be probed here with a HEAD request, but that is a
	 * blocking round trip on every uncached render, sitting in front of the first
	 * byte of HTML, and a host with outbound HTTP blocked pays the full timeout every
	 * time. The browser downloads the image regardless; the JS only has to read the
	 * decoded width and swap. `hqdefault` is the floor — YouTube generates it for
	 * every video, which is also why the JSON-LD uses it: a `thumbnailUrl` Google
	 * cannot fetch invalidates the whole VideoObject.
	 *
	 * Empty for anything that is not a YouTube video showing its provider thumbnail.
	 */
	protected function get_poster_fallback( $settings, $video ) {
		if ( ! empty( $settings['poster']['url'] ) ) {
			return '';
		}

		if ( 'youtube' !== $video['provider'] || ! $video['video_id'] ) {
			return '';
		}

		return $this->youtube_thumbnail_url( $video['video_id'], 'hqdefault' );
	}

	/**
	 * `https://img.youtube.com/vi/{id}/{size}.jpg`.
	 *
	 * The id arrives from Elementor's own URL parser, so it is already a bare id.
	 * Encoding it is belt and braces against that parser one day handing back
	 * something carrying a slash or a query string.
	 *
	 * @param string $id   YouTube video id.
	 * @param string $size maxresdefault | sddefault | hqdefault | mqdefault.
	 * @return string
	 */
	protected function youtube_thumbnail_url( $id, $size ) {
		return 'https://img.youtube.com/vi/' . rawurlencode( $id ) . '/' . $size . '.jpg';
	}

	/**
	 * Line numbers the paste parser could not read. Filled by get_chapters(),
	 * surfaced in the editor only.
	 *
	 * @var int[]
	 */
	protected $chapter_skips = [];

	/**
	 * A pasted timestamp block → chapter rows.
	 *
	 * Mirrors how YouTube itself reads a description: the timestamp has to open the
	 * line, `M:SS` / `MM:SS` / `H:MM:SS`, then a space, dash, en/em dash or colon,
	 * then the label. Everything else on the line is ignored, which is what lets an
	 * author paste a whole description and get only the chapters out of it.
	 *
	 * Two deliberate departures from YouTube, both because this is a paste box and
	 * its job is to accept what people actually have:
	 *
	 *  - Leading list noise (bullets, dashes, parens) is stripped first. YouTube
	 *    rejects those lines outright; podcast show notes are full of them.
	 *  - Rows are sorted afterwards, so an out-of-order paste still works.
	 *
	 * A colon is required. Without it "12 Days of Christmas" reads as a chapter at
	 * twelve seconds, and that class of false positive is worse than making someone
	 * type `0:12`.
	 */
	protected function parse_chapters_text( $text ) {
		$text = (string) $text;

		// What a real-world copy-paste drags along and a naive parser chokes on:
		// a non-breaking space (never matches \s), a zero-width space, a BOM.
		$text = str_replace(
			[ "\xC2\xA0", "\xE2\x80\x8B", "\xEF\xBB\xBF" ],
			[ ' ', '', '' ],
			$text
		);

		$lines    = preg_split( '/\r\n|\r|\n/', $text );
		$chapters = [];

		foreach ( $lines as $index => $line ) {
			$line = trim( $line );

			if ( '' === $line ) {
				continue;
			}

			// Bullets, dashes, parens and brackets ahead of the timestamp.
			$candidate = preg_replace( '/^[\s\x{2022}\x{00B7}\x{25CF}\x{2043}\-\x{2013}\x{2014}*\(\[]+/u', '', $line );

			// `：` is the full-width colon CJK keyboards produce — without it, a
			// Chinese or Japanese author's paste yields zero chapters.
			//
			// The lookahead is load-bearing: without it a bare `8:00` backtracks to
			// `8:0` so the label group can claim the final `0`, and a label-less line
			// silently becomes a chapter called "0".
			$matched = preg_match(
				'/^(\d{1,3}(?:[:\x{FF1A}]\d{1,2}){1,2})(?=[\s\)\]\-\x{2013}\x{2014}:\x{FF1A}.|]|$)[\)\]]?\s*(?:[-\x{2013}\x{2014}:\x{FF1A}.|]\s*)?(.+)$/u',
				$candidate,
				$parts
			);

			if ( ! $matched ) {
				$this->chapter_skips[] = $index + 1;
				continue;
			}

			$label = trim( $parts[2] );

			if ( '' === $label ) {
				$this->chapter_skips[] = $index + 1;
				continue;
			}

			$chapters[] = [
				'time'  => $this->parse_time( str_replace( '：', ':', $parts[1] ) ),
				'label' => $label,
				'image' => '',
			];
		}

		return $chapters;
	}

	/**
	 * Chapters from whichever source is selected, empty rows dropped, sorted by time.
	 */
	protected function get_chapters( $settings ) {
		// Reset before the early return — the same widget instance can render more
		// than once, and stale skips would be reported against the wrong content.
		$this->chapter_skips = [];

		if ( 'yes' !== $settings['enable_chapters'] ) {
			return [];
		}

		if ( 'paste' === $settings['chapters_source'] ) {
			$chapters = $this->parse_chapters_text( $settings['chapters_text'] ?? '' );
		} else {
			$chapters = [];

			foreach ( (array) ( $settings['chapters'] ?? [] ) as $item ) {
				$label = trim( (string) ( $item['chapter_label'] ?? '' ) );

				if ( '' === $label ) {
					continue;
				}

				$chapters[] = [
					'time'  => $this->parse_time( $item['chapter_time'] ?? '' ),
					'label' => $label,
					'image' => $item['chapter_image']['url'] ?? '',
				];
			}
		}

		usort(
			$chapters,
			function ( $a, $b ) {
				return $a['time'] <=> $b['time'];
			}
		);

		return $chapters;
	}

	/**
	 * The Plyr `controls` array, built from the per-control switchers.
	 * Order here is the order they appear in the bar.
	 */
	protected function get_control_list( $settings ) {
		if ( 'yes' !== $settings['show_controls'] ) {
			return [];
		}

		$map = [
			'control_play_large'   => 'play-large',
			'control_restart'      => 'restart',
			'control_rewind'       => 'rewind',
			'control_play'         => 'play',
			'control_fast_forward' => 'fast-forward',
			'control_progress'     => 'progress',
			'control_current_time' => 'current-time',
			'control_duration'     => 'duration',
			'control_mute'         => 'mute',
			'control_volume'       => 'volume',
			'control_captions'     => 'captions',
			'control_settings'     => 'settings',
			'control_pip'          => 'pip',
			'control_airplay'      => 'airplay',
			'control_download'     => 'download',
			'control_fullscreen'   => 'fullscreen',
		];

		$controls = [];

		foreach ( $map as $key => $name ) {
			if ( 'yes' === ( $settings[ $key ] ?? '' ) ) {
				$controls[] = $name;
			}
		}

		return $controls;
	}

	/**
	 * Speed options: a comma list of positive floats, deduped, always including 1.
	 */
	protected function get_speed_options( $settings ) {
		$speeds = [];

		foreach ( explode( ',', (string) $settings['speed_options'] ) as $piece ) {
			$value = (float) trim( $piece );

			if ( $value > 0 ) {
				$speeds[] = $value;
			}
		}

		if ( ! in_array( 1.0, $speeds, true ) ) {
			$speeds[] = 1.0;
		}

		$speeds = array_values( array_unique( $speeds, SORT_NUMERIC ) );
		sort( $speeds );

		return $speeds;
	}

	/**
	 * The `data-settings` payload handed to the JS handler.
	 */
	protected function get_js_settings( $settings, $video, $chapters ) {
		$ratio = $settings['aspect_ratio'];

		if ( 'custom' === $ratio ) {
			$ratio = trim( (string) $settings['aspect_ratio_custom'] );
		}

		$autoplay = 'yes' === $settings['autoplay'];
		$on_view  = 'yes' === $settings['play_on_view'];

		// Lazy load means "build nothing until a click" — both auto-start modes contradict it.
		$lazy_load = 'yes' === $settings['lazy_load'] && ! $autoplay && ! $on_view;

		$plyr = [
			'controls'     => $this->get_control_list( $settings ),
			'settings'     => array_values( (array) $settings['settings_menu'] ),
			'autoplay'     => $autoplay,
			'muted'        => $autoplay || 'yes' === $settings['muted'],
			'loop'         => [ 'active' => 'yes' === $settings['loop'] ],
			'resetOnEnd'   => 'yes' === $settings['reset_on_end'],
			'clickToPlay'  => 'yes' === $settings['click_to_play'],
			'hideControls' => 'yes' === $settings['autohide'],
			'invertTime'   => 'yes' === $settings['invert_time'],
			'seekTime'     => (int) $settings['seek_time'],
			'volume'       => (float) ( $settings['volume']['size'] ?? 1 ),
			// Plyr's default storage key is a single global "plyr" shared by every
			// instance on the site, so a mute set in one widget leaks into all the
			// others. Scope it to this widget instead.
			'storage'      => [
				'enabled' => 'yes' === $settings['remember_position'],
				'key'     => 'sky-video-player',
			],
			'keyboard'     => [
				'focused' => true,
				'global'  => 'yes' === $settings['keyboard_global'],
			],
			'tooltips'     => [
				'controls' => 'yes' === $settings['tooltip_controls'],
				'seek'     => 'yes' === $settings['tooltip_seek'],
			],
			'speed'        => [
				'selected' => 1,
				'options'  => $this->get_speed_options( $settings ),
			],
			'youtube'      => [
				'noCookie'       => 'yes' === $settings['privacy_mode'],
				'rel'            => 0,
				'showinfo'       => 0,
				'iv_load_policy' => 3,
				'modestbranding' => 1,
			],
			'vimeo'        => [
				'dnt' => 'yes' === $settings['privacy_mode'],
			],
		];

		if ( $ratio ) {
			$plyr['ratio'] = $ratio;
		}

		if ( $chapters && 'markers' === $settings['chapter_progress_mode'] ) {
			$plyr['markers'] = [
				'enabled' => true,
				'points'  => $chapters,
			];
		}

		return [
			'provider'   => $video['provider'],
			'lazyLoad'   => $lazy_load,
			'playOnView' => $on_view,
			'startTime'  => $this->parse_time( $settings['start_time'] ),
			'endTime'    => $this->parse_time( $settings['end_time'] ),
			'sticky'     => [
				'enabled'  => 'yes' === $settings['enable_sticky'],
				'position' => $settings['sticky_position'],
			],
			'chapters'   => $chapters,
			'chapterUi'  => [
				'segments' => 'segments' === $settings['chapter_progress_mode'],
				'overlay'  => '' !== $settings['chapter_overlay_title'],
				'fill'     => 'yes' === $settings['chapter_progress_fill'],
			],
			// One probe feeds both chapter thumbnails and the hover preview. It only
			// ever runs against a same-origin (or CORS-enabled) file — providers keep
			// their frames inside a cross-origin iframe.
			'frames'     => [
				'timing'   => 'html5' === $video['provider'] ? $settings['frame_capture'] : '',
				'chapters' => 'yes' === $settings['chapter_thumbnails'] && 'yes' === $settings['chapter_list'],
				'scrub'    => 'yes' === $settings['scrub_preview'] && 'yes' === $settings['show_controls'] && 'yes' === $settings['control_progress'],
			],
			'plyr'       => $plyr,
		];
	}

	/* ─────────────────────────────────────────────────────────────
	 * Render
	 * ──────────────────────────────────────────────────────────── */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$video    = $this->get_video_data( $settings );

		// Nothing playable yet — nudge in the editor, stay silent on the front end.
		if ( ! $video['url'] && ! $video['video_id'] ) {
			if ( sky_addons_editor_mode() ) {
				echo '<div class="sa-vp-notice">' . esc_html__( 'Add a video URL to start.', 'sky-elementor-addons' ) . '</div>';
			}
			return;
		}

		$chapters = $this->get_chapters( $settings );
		$poster   = $this->get_poster_url( $settings, $video );
		$fallback = $this->get_poster_fallback( $settings, $video );
		$js       = $this->get_js_settings( $settings, $video, $chapters );

		$classes = [ 'sa-video-player' ];

		if ( $js['lazyLoad'] ) {
			$classes[] = 'sa-vp-lazy';
		}

		if ( $js['sticky']['enabled'] ) {
			$classes[] = 'sa-vp-sticky-' . $js['sticky']['position'];
		}

		$this->add_render_attribute(
			'wrapper',
			[
				'class'         => $classes,
				'style'         => '--sa-vp-ratio: ' . $this->get_css_ratio( $settings ) . ';',
				'data-settings' => wp_json_encode( $js ),
			]
		);
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="sa-vp-frame">
				<?php
				$this->render_media( $settings, $video, $poster, $js['lazyLoad'] );
				$this->render_title_overlay( $settings, $chapters );
				$this->render_overlay( $poster, $fallback, $js['lazyLoad'] );
				$this->render_sticky_close( $settings );
				?>
			</div>
			<?php
			$this->render_chapter_list( $settings, $chapters, $poster, $fallback );
			$this->render_chapter_skips();
			$this->render_schema( $settings, $video, $poster, $fallback );
			?>
		</div>
		<?php
	}

	/**
	 * The element Plyr attaches to. Self-hosted gets a real `<video>` so the file stays
	 * reachable without JS; providers get the placeholder Plyr expands into an iframe.
	 */
	protected function render_media( $settings, $video, $poster, $lazy_load ) {
		if ( 'html5' !== $video['provider'] ) {
			$this->add_render_attribute(
				'media',
				[
					'class'              => 'sa-vp-media',
					'data-plyr-provider' => $video['provider'],
					'data-plyr-embed-id' => $video['video_id'],
				]
			);
			?>
			<div <?php $this->print_render_attribute_string( 'media' ); ?>></div>
			<?php
			return;
		}

		$this->add_render_attribute(
			'media',
			[
				'class'       => 'sa-vp-media',
				'playsinline' => 'playsinline',
				'controls'    => 'controls',
				// Lazy load must not let the browser reach for the file before the click.
				'preload'     => $lazy_load ? 'none' : $settings['preload'],
			]
		);

		if ( $poster ) {
			$this->add_render_attribute( 'media', 'poster', esc_url( $poster ) );
		}

		if ( 'yes' === $settings['muted'] || 'yes' === $settings['autoplay'] ) {
			$this->add_render_attribute( 'media', 'muted', 'muted' );
		}
		?>
		<video <?php $this->print_render_attribute_string( 'media' ); ?>>
			<source src="<?php echo esc_url( $video['url'] ); ?>" />
		</video>
		<?php
	}

	/**
	 * Poster + play button shown while the player is still un-built (lazy load).
	 */
	protected function render_overlay( $poster, $fallback, $lazy_load ) {
		if ( ! $lazy_load ) {
			return;
		}
		?>
		<div class="sa-vp-overlay" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Play video', 'sky-elementor-addons' ); ?>">
			<?php if ( $poster ) : ?>
				<img class="sa-vp-poster" src="<?php echo esc_url( $poster ); ?>"<?php echo $fallback ? ' data-sa-fallback="' . esc_url( $fallback ) . '"' : ''; ?> alt="" loading="lazy" />
			<?php endif; ?>
			<span class="sa-vp-play">
				<?php
				/*
				 * Plyr's own play glyph, inlined. It cannot be pulled from the sprite
				 * with `<use href="plyr.svg#plyr-play">` — external references in
				 * `<use>` only work in Firefox — and the sprite is not in the document
				 * yet anyway, because Plyr injects it and Plyr has not been built.
				 * Copied verbatim from `src/vendor/svg/plyr.svg`; re-copy it on a
				 * vendor bump so this button and the built player stay identical.
				 */
				?>
				<svg viewBox="0 0 18 18" aria-hidden="true" focusable="false"><path d="M15.562 8.1 3.87.225c-.818-.562-1.87 0-1.87.9v15.75c0 .9 1.052 1.462 1.87.9L15.563 9.9c.584-.45.584-1.35 0-1.8"/></svg>
			</span>
		</div>
		<?php
	}

	protected function render_sticky_close( $settings ) {
		if ( 'yes' !== $settings['enable_sticky'] || 'yes' !== $settings['sticky_close'] ) {
			return;
		}
		?>
		<button type="button" class="sa-vp-close" aria-label="<?php esc_attr_e( 'Close sticky player', 'sky-elementor-addons' ); ?>">
			<span aria-hidden="true">&times;</span>
		</button>
		<?php
	}

	/**
	 * Current chapter name floating on the video. Visibility is tied to Plyr's own
	 * `plyr--hide-controls` class in CSS, so it appears and fades with the control
	 * bar and needs no timers of its own.
	 */
	protected function render_title_overlay( $settings, $chapters ) {
		if ( ! $chapters || '' === $settings['chapter_overlay_title'] ) {
			return;
		}

		$class = 'sa-vp-chapter-overlay';

		if ( 'always' === $settings['chapter_overlay_title'] ) {
			$class .= ' sa-vp-chapter-overlay--always';
		}
		?>
		<div class="<?php echo esc_attr( $class ); ?>" aria-hidden="true"></div>
		<?php
	}

	/**
	 * One markup tree, three layouts — `list`, `rail` and `grid` are CSS modes on the
	 * same `<ul>`. Thumbnails fall back poster-ward; JS may replace `src` later with a
	 * frame captured from the video.
	 */
	protected function render_chapter_list( $settings, $chapters, $poster, $fallback ) {
		if ( ! $chapters || 'yes' !== $settings['chapter_list'] ) {
			return;
		}

		$layout  = $settings['chapter_layout'];
		$thumbs  = 'yes' === $settings['chapter_thumbnails'];
		$content = $settings['chapter_item_content'];

		$this->add_render_attribute(
			'chapters',
			'class',
			[
				'sa-vp-chapters',
				'sa-vp-chapters--' . $layout,
				$thumbs ? 'sa-vp-chapters--thumbs' : 'sa-vp-chapters--no-thumbs',
			]
		);

		if ( 'yes' === $settings['chapter_progress_fill'] ) {
			$this->add_render_attribute( 'chapters', 'class', 'sa-vp-chapters--fill' );
		}
		?>
		<ul <?php $this->print_render_attribute_string( 'chapters' ); ?>>
			<?php foreach ( $chapters as $index => $chapter ) : ?>
				<li class="sa-vp-chapter" role="button" tabindex="0"
					data-index="<?php echo esc_attr( $index ); ?>"
					data-time="<?php echo esc_attr( $chapter['time'] ); ?>">

					<?php if ( $thumbs ) : ?>
						<span class="sa-vp-chapter-thumb">
							<?php
							// The <img> is always present, even with nothing to show yet:
							// frame capture needs a target to write into, and a src-less
							// img renders as nothing rather than a broken-image icon.
							$image = $chapter['image'] ? $chapter['image'] : $poster;

							// Only the shared poster can be the optimistic YouTube size;
							// a per-chapter image the author picked is always real.
							$thumb_fallback = ( $fallback && ! $chapter['image'] ) ? $fallback : '';
							?>
							<img<?php echo $image ? ' src="' . esc_url( $image ) . '"' : ''; ?><?php echo $thumb_fallback ? ' data-sa-fallback="' . esc_url( $thumb_fallback ) . '"' : ''; ?> alt="" loading="lazy" />
						</span>
					<?php endif; ?>

					<?php if ( 'none' !== $content ) : ?>
						<span class="sa-vp-chapter-body">
							<?php if ( 'label' !== $content ) : ?>
								<span class="sa-vp-chapter-time"><?php echo esc_html( $this->format_time( $chapter['time'] ) ); ?></span>
							<?php endif; ?>
							<?php if ( 'time' !== $content ) : ?>
								<span class="sa-vp-chapter-label"><?php echo esc_html( $chapter['label'] ); ?></span>
							<?php endif; ?>
						</span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * Which pasted lines were unreadable. Editor only — a visitor should never see
	 * plumbing, but an author who pastes 12 lines and gets 10 chapters needs to know
	 * straight away rather than hearing it from a client.
	 */
	protected function render_chapter_skips() {
		if ( ! $this->chapter_skips || ! sky_addons_editor_mode() ) {
			return;
		}
		?>
		<div class="sa-vp-notice">
			<?php
			printf(
				/* translators: %s: comma separated list of line numbers. */
				esc_html__( 'No timestamp found on line %s — those lines were skipped.', 'sky-elementor-addons' ),
				esc_html( implode( ', ', $this->chapter_skips ) )
			);
			?>
		</div>
		<?php
	}

	/**
	 * VideoObject JSON-LD. Only emitted once name, thumbnail and uploadDate are all
	 * present — Google rejects a VideoObject missing any of them, and a rejected
	 * blob on the page is worse than no blob.
	 */
	protected function render_schema( $settings, $video, $poster, $fallback ) {
		if ( 'yes' !== $settings['enable_schema'] ) {
			return;
		}

		// Google fetches this URL itself and drops the whole VideoObject if it 404s,
		// so schema takes the size YouTube always generates rather than the optimistic
		// one the browser is still checking.
		if ( $fallback ) {
			$poster = $fallback;
		}

		// Schema values are data, not markup. `wp_json_encode` escapes `/`, so a
		// `</script>` can't break the block, but tags left in `name` are junk for the
		// search engine reading it.
		$name        = trim( wp_strip_all_tags( (string) $settings['schema_name'] ) );
		$upload_date = trim( (string) $settings['schema_upload_date'] );

		if ( ! $name || ! $poster || ! $upload_date ) {
			return;
		}

		$schema = [
			'@context'     => 'https://schema.org',
			'@type'        => 'VideoObject',
			'name'         => $name,
			'thumbnailUrl' => $poster,
			'uploadDate'   => $upload_date,
		];

		$description = trim( wp_strip_all_tags( (string) $settings['schema_description'] ) );

		if ( $description ) {
			$schema['description'] = $description;
		}

		$duration = $this->iso_duration( $this->parse_time( $settings['schema_duration'] ) );

		if ( $duration ) {
			$schema['duration'] = $duration;
		}

		if ( 'html5' === $video['provider'] ) {
			$schema['contentUrl'] = $video['url'];
		} else {
			$schema['embedUrl'] = $video['url'];
		}
		?>
		<script type="application/ld+json"><?php echo wp_json_encode( $schema ); ?></script>
		<?php
	}
}
