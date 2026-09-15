<?php

namespace Sky_Addons\Modules\GradientText;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Core\DynamicTags\Dynamic_CSS;
use Sky_Addons\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Module extends Module_Base {

	private $element_selector = 'h1,h2,h3,h4,h5,h6,span,p,a,div';

	/**
	 * Re-entrancy flag for add_custom_selectors_css(). See the comment at its call site.
	 *
	 * @var bool
	 */
	private $is_parsing_css = false;

	public function __construct() {
		parent::__construct();
		$this->add_actions();
	}

	public function get_name() {
		return 'sky-gradient-text';
	}

	public function register_section( $element ) {
		$element->start_controls_section(
			'section_sa_gt_controls',
			[
				'tab'   => Controls_Manager::TAB_STYLE,
				'label' => esc_html__( 'Gradient Text', 'sky-elementor-addons' ) . sky_addons_get_icon(),
			]
		);
		$element->end_controls_section();
	}

	public function register_controls( $widget, $args ) {
		$widget->add_control(
			'sky_gr_enable',
			[
				'label'              => esc_html__( 'Enable Gradient Text', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SWITCHER,
				'label_on'           => esc_html__( 'Yes', 'sky-elementor-addons' ),
				'label_off'          => esc_html__( 'No', 'sky-elementor-addons' ),
				'return_value'       => 'yes',
				'frontend_available' => true,
			]
		);

		$widget->add_control(
			'sky_gr_selectors',
			[
				'label'              => esc_html__( 'Custom Selector', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::TEXTAREA,
				'placeholder'        => esc_html__( 'h1,h2,h3,h4,h5,h6,span,p,a,div', 'sky-elementor-addons' ),
				'label_block'        => true,
				// The full validation rules (character allowlist, balanced brackets, no leading or
				// trailing combinator) are enforced in sanitize_custom_selectors(). Spelling them
				// out here made the panel unreadable; an invalid entry silently falls back to the
				// placeholder list, which is the only outcome a user needs to know about.
				'description'        => esc_html__( 'Comma separated selectors, scoped to this element (e.g. h2, .my-highlight). Leave empty, or enter anything invalid, to use the list above.', 'sky-elementor-addons' ),
				'condition'          => [
					'sky_gr_enable' => 'yes',
				],
				'rows'               => 5,
			]
		);

		$widget->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'        => 'sky_gr_background',
				'label'       => esc_html__( 'Background', 'sky-elementor-addons' ),
				'fields_options' => [
					'background' => [
						'label'   => esc_html__( 'Text Color', 'sky-elementor-addons' ),
						'default' => 'gradient',
					],
					// Elementor defaults the first gradient stop to '' and only the second to
					// #f2295b, which builds `linear-gradient(180deg,  0%, #f2295b 100%)` — an
					// invalid value the browser drops, so switching the extension on painted
					// nothing until a colour was picked by hand. Both stops need a default.
					'color'      => [
						'default' => '#7C3AED',
					],
				],
				'render_type' => 'template',
				'condition' => [
					'sky_gr_enable' => 'yes',
				],
				'selector'    => $this->get_wrapped_selectors(),
			]
		);

		$widget->add_control(
			'sky_gr_prefix',
			[
				'type'         => Controls_Manager::HIDDEN,
				'default'      => 'yes',
				'render_type'  => 'template',
				'prefix_class' => 'sky-gr-selectors-',
				'condition'    => [
					'sky_gr_enable'     => 'yes',
					'sky_gr_selectors!' => '',
				],
			]
		);
		$widget->add_control(
			'sky_gr_output',
			[
				'type'        => Controls_Manager::HIDDEN,
				'default'     => '1',
				'selectors'   => [
					$this->get_wrapped_selectors() => '-webkit-background-clip: text; -webkit-text-fill-color: transparent; background-color: transparent;',
				],
				'render_type' => 'template',
				'condition'   => [
					'sky_gr_enable' => 'yes',
				],
			]
		);
	}

	/**
	 * The default element list, scoped to the wrapper but without the `:not(.sky-gr-selectors-yes)`
	 * guard, so it still applies once the prefix class is on the wrapper.
	 *
	 * @return string Comma separated, `{{WRAPPER}}` prefixed selector list.
	 */
	private function get_default_scoped_selectors() {
		$selectors = [];

		foreach ( explode( ',', $this->element_selector ) as $tag ) {
			$tag = trim( $tag );

			if ( '' !== $tag ) {
				$selectors[] = '{{WRAPPER}} ' . $tag;
			}
		}

		return implode( ', ', $selectors );
	}

	private function get_wrapped_selectors() {
		$selectors = [
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) h1',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) h2',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) h3',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) h4',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) h5',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) h6',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) span',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) p',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) a',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) div',
			'{{WRAPPER}}.sky-gr-selectors-yes .sky-gr-text',
		];
		return implode( ', ', array_map( 'trim', $selectors ) );
	}

	/**
	 * Turn the raw "Custom Selector" textarea value into a wrapper scoped selector list.
	 *
	 * Elementor `selectors` keys are written into the CSS file verbatim, so the value has to be
	 * sanitised before it can be used. Each comma separated segment is validated against an
	 * allowlist of characters that are legal inside a CSS selector — letters, digits, `_ - . # *`,
	 * the combinators `> + ~`, attribute brackets `[ ] =`, pseudo `: ( )` and whitespace.
	 * Everything else (braces, semicolons, `@`, slashes — so no `/*` comments, backslashes,
	 * quotes, angle brackets) fails the segment, and the segment is dropped. That is a stricter
	 * rule than a denylist and cannot be worked around by encoding tricks.
	 *
	 * The allowlist only validates characters, so three structural rules are enforced on top of it:
	 *
	 * 1. Brackets must be balanced and correctly nested. Per CSS Syntax L3 a `(` or `[` opens a
	 *    simple block that is consumed up to its matching closer *or EOF*, so an unbalanced `:not(`
	 *    would swallow its own declaration block and every rule Elementor writes after it in
	 *    post-N.css — one field would wipe the whole page's Elementor CSS.
	 * 2. A segment may not start with `~` or `+`. `{{WRAPPER}} ~ *` is the sibling combinator, not a
	 *    descendant, so it would paint every following sibling on the page instead of this element.
	 *    `>` is fine: `{{WRAPPER}} > h2` stays scoped.
	 * 3. A segment may not end with a combinator, which would make the whole emitted rule invalid
	 *    and take the valid segments down with it.
	 *
	 * The list is split on commas at bracket depth zero, so functional pseudo-classes that take a
	 * selector list (`:is(h1,h2)`, `:not(.a,.b)`) survive intact.
	 *
	 * @param string $raw Raw control value.
	 * @return string Comma separated, `{{WRAPPER}}` prefixed selector list. Empty when nothing survived.
	 */
	private function sanitize_custom_selectors( $raw ) {
		if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
			return '';
		}

		$selectors = [];

		foreach ( $this->split_selector_list( $raw ) as $selector ) {
			// Collapse newlines/tabs/multiple spaces into single spaces so the CSS stays on one line.
			$selector = trim( (string) preg_replace( '/\s+/', ' ', $selector ) );

			if ( '' === $selector ) {
				continue;
			}

			// A comma can only still be here if split_selector_list() found it inside a bracket pair,
			// e.g. `:is(h1,h2)`, and has_balanced_brackets() below proves that pair actually closes.
			if ( ! preg_match( '/^[A-Za-z0-9_\-.#*>+~\[\]=:(), ]+$/', $selector ) ) {
				continue;
			}

			// A leading `~`/`+` escapes the wrapper scope; a trailing combinator is invalid CSS.
			if ( preg_match( '/^[~+]|[>+~]$/', $selector ) ) {
				continue;
			}

			if ( ! $this->has_balanced_brackets( $selector ) ) {
				continue;
			}

			$selectors[] = '{{WRAPPER}} ' . $selector;
		}

		return implode( ', ', array_unique( $selectors ) );
	}

	/**
	 * Split a selector list on commas that sit at bracket depth zero.
	 *
	 * Splitting on every comma would tear `:is(h1,h2)` in half and leave two unbalanced fragments
	 * that the balance check would then reject, so a perfectly legal selector would stop working.
	 * Unmatched closers are treated as plain characters here — `has_balanced_brackets()` is the
	 * authority on whether a segment is well formed, this only decides where the cuts go.
	 *
	 * @param string $raw Raw control value.
	 * @return array List of raw segments.
	 */
	private function split_selector_list( $raw ) {
		$segments = [];
		$buffer   = '';
		$stack    = [];
		$openers  = [
			')' => '(',
			']' => '[',
		];
		$length   = strlen( $raw );

		for ( $i = 0; $i < $length; $i++ ) {
			$char = $raw[ $i ];

			if ( '(' === $char || '[' === $char ) {
				$stack[] = $char;
			} elseif ( isset( $openers[ $char ] ) && end( $stack ) === $openers[ $char ] ) {
				array_pop( $stack );
			} elseif ( ',' === $char && empty( $stack ) ) {
				$segments[] = $buffer;
				$buffer     = '';
				continue;
			}

			$buffer .= $char;
		}

		$segments[] = $buffer;

		return $segments;
	}

	/**
	 * Whether every `(` and `[` in a selector is closed, in the right order, and never closed early.
	 *
	 * Per-type counters are not enough: `([)]` balances both counts yet still leaves the paren block
	 * open to EOF, so a single stack is used to enforce nesting order as well as depth.
	 *
	 * @param string $selector Single selector segment.
	 * @return bool
	 */
	private function has_balanced_brackets( $selector ) {
		$stack   = [];
		$openers = [
			')' => '(',
			']' => '[',
		];
		$length  = strlen( $selector );

		for ( $i = 0; $i < $length; $i++ ) {
			$char = $selector[ $i ];

			if ( '(' === $char || '[' === $char ) {
				$stack[] = $char;
				continue;
			}

			if ( isset( $openers[ $char ] ) && array_pop( $stack ) !== $openers[ $char ] ) {
				return false;
			}
		}

		return empty( $stack );
	}

	/**
	 * Re-emit the gradient rules against the user's own selectors.
	 *
	 * The control definition is registered once for every element, so `{{WRAPPER}}` is the only
	 * placeholder Elementor substitutes inside a `selectors` key — a per element value can never be
	 * baked into it. The dynamic part is therefore produced here, at CSS generation time, by taking
	 * the already condition-filtered gradient controls and swapping their selector key for the
	 * user's list before handing them back to Elementor's own rule builder. No CSS is hand rolled,
	 * so the full Background group control (classic + gradient, all fields) keeps working.
	 *
	 * The static `{{WRAPPER}}.sky-gr-selectors-yes .sky-gr-text` rule in get_wrapped_selectors() is
	 * deliberately left in place: sites that adopted the hand authored `<span class="sky-gr-text">`
	 * workaround keep rendering unchanged, and it costs one extra selector.
	 *
	 * `sky_gr_prefix` puts `.sky-gr-selectors-yes` on the wrapper as soon as the textarea is
	 * non-empty — its condition can only test the *raw* value, because a control definition is
	 * shared by every element and Elementor evaluates conditions against stored settings. That
	 * kills the ten default `:not(.sky-gr-selectors-yes)` rules. So when nothing survives
	 * sanitising, this falls back to re-emitting the gradient against the default element list
	 * instead of bailing: a user who types something invalid ends up exactly where a user who
	 * typed nothing ends up, rather than with no gradient at all.
	 *
	 * @param \Elementor\Core\Files\CSS\Post $post_css Post CSS file.
	 * @param \Elementor\Element_Base        $element  Element being parsed.
	 */
	public function add_custom_selectors_css( $post_css, $element ) {
		if ( $post_css instanceof Dynamic_CSS ) {
			return;
		}

		// See the guard set around add_controls_stack_style_rules() at the end of this method.
		if ( $this->is_parsing_css ) {
			return;
		}

		if ( 'yes' !== $element->get_settings( 'sky_gr_enable' ) ) {
			return;
		}

		$raw = $element->get_settings( 'sky_gr_selectors' );

		// Empty textarea: the prefix class is off, so the default rules already apply as authored.
		if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
			return;
		}

		$custom_selectors = $this->sanitize_custom_selectors( $raw );

		if ( '' === $custom_selectors ) {
			$custom_selectors = $this->get_default_scoped_selectors();
		}

		$default_selectors = $this->get_wrapped_selectors();
		$gradient_controls = [];

		foreach ( $post_css->get_style_controls( $element, null, $element->get_parsed_dynamic_settings() ) as $name => $control ) {
			if ( 0 !== strpos( $name, 'sky_gr_' ) || empty( $control['selectors'] ) ) {
				continue;
			}

			$rewritten = [];

			foreach ( $control['selectors'] as $selector => $css_property ) {
				// If the key no longer contains the exact default list — Elementor's
				// `elementor/files/css/selectors` filter can rewrite it, and a group field using
				// `{{SELECTOR}} .suffix` would only suffix the last comma segment — str_replace()
				// would pass it through untouched and emit a duplicate of the default rule.
				if ( false === strpos( $selector, $default_selectors ) ) {
					continue;
				}

				$rewritten[ str_replace( $default_selectors, $custom_selectors, $selector ) ] = $css_property;
			}

			if ( empty( $rewritten ) ) {
				continue;
			}

			$control['selectors']       = $rewritten;
			$gradient_controls[ $name ] = $control;
		}

		if ( empty( $gradient_controls ) ) {
			return;
		}

		/*
		 * Re-entrancy guard. Post::add_controls_stack_style_rules() recurses into
		 * $element->get_children() and calls render_styles() on each, which re-fires
		 * `elementor/element/parse_css`. This is harmless today because the section is registered
		 * on the `common` stack, which is widget-only, and widgets carry no child elements. If it
		 * is ever registered on `container`/`section`, every gradient-enabled ancestor would
		 * re-render its entire subtree — duplicate rules and 2^depth work — without this flag.
		 */
		$this->is_parsing_css = true;

		try {
			$post_css->add_controls_stack_style_rules(
				$element,
				$gradient_controls,
				$element->get_settings(),
				[ '{{ID}}', '{{WRAPPER}}' ],
				[ $element->get_id(), $post_css->get_element_unique_selector( $element ) ]
			);
		} finally {
			$this->is_parsing_css = false;
		}
	}

	protected function add_actions() {
		add_action( 'elementor/element/common/_section_background/after_section_end', [ $this, 'register_section' ] );
		add_action( 'elementor/element/common/section_sa_gt_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/element/parse_css', [ $this, 'add_custom_selectors_css' ], 10, 2 );
	}
}
