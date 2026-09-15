<?php
/**
 * HTML rows source for the Table widget.
 *
 * Users paste `<tr>` markup; this turns it into the same cell matrix every other
 * source produces.
 *
 * SECURITY: the pasted markup is **never echoed**. It is parsed into a matrix and
 * each cell's inner HTML is run through wp_kses() with a small inline allowlist,
 * so the widget's normal escaped render path is the only thing that reaches the
 * page. Scripts, styles, iframes, event handlers, form fields and shortcodes
 * cannot survive. Do not "simplify" this into an echo — that is precisely the
 * hole other addon plugins ship.
 *
 * @package Sky_Addons
 */

namespace Sky_Addons\Modules\Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Html_Source {

	/** Hard ceiling on a single cell's span, matching the repeater controls. */
	const MAX_SPAN = 50;

	/**
	 * Elements removed whole — node and text alike — before a cell is read.
	 *
	 * wp_kses() already strips these tags, but it keeps their text content, so a
	 * pasted `<script>alert(1)</script>` would render as the literal string
	 * "alert(1)". Harmless, but garbage on the page.
	 */
	const STRIP_ELEMENTS = [
		'script',
		'style',
		'iframe',
		'object',
		'embed',
		'form',
		'input',
		'select',
		'textarea',
		'button',
		'link',
		'meta',
		'noscript',
		'svg',
		'template',
	];

	/**
	 * Parse pasted rows into a head/body matrix.
	 *
	 * A `<thead>`, or a first row made entirely of `<th>`, becomes the header.
	 *
	 * @param string $html Raw markup from the textarea.
	 * @return array{head: array, body: array}
	 */
	public static function parse( $html ) {
		$html = trim( (string) $html );

		if ( '' === $html || ! class_exists( '\DOMDocument' ) ) {
			return Data_Source::empty_matrix();
		}

		$document = new \DOMDocument();
		$previous = libxml_use_internal_errors( true );

		// LIBXML_NONET blocks network access during parsing; no DTD is loaded, so
		// external entities cannot be pulled in either.
		$document->loadHTML(
			'<?xml encoding="utf-8" ?><table>' . $html . '</table>',
			LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
		);

		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		self::strip_elements( $document );

		$rows    = $document->getElementsByTagName( 'tr' );
		$wrapper = $document->getElementsByTagName( 'table' )->item( 0 );

		if ( ! $rows->length || ! $wrapper ) {
			return Data_Source::empty_matrix();
		}

		$head = [];
		$body = [];

		foreach ( $rows as $row ) {
			// wp_kses_post() permits a nested <table> inside a cell, and
			// getElementsByTagName is document-wide — without this check those
			// inner rows would be hoisted into the outer table and duplicated.
			if ( ! self::belongs_to( $row, $wrapper ) ) {
				continue;
			}

			$cells = self::read_row( $row );

			if ( empty( $cells ) ) {
				continue;
			}

			// First row only: promote it to the header when it is inside a
			// <thead> or is made entirely of <th> cells.
			if ( empty( $head ) && empty( $body ) && self::is_header_row( $row ) ) {
				$head = $cells;
				continue;
			}

			$body[] = $cells;
		}

		return [
			'head' => $head,
			'body' => Data_Source::cap( $body ),
		];
	}

	/**
	 * Whether a row's nearest table ancestor is our own wrapper, rather than a
	 * table nested inside one of the cells.
	 *
	 * @param \DOMElement $row     Row node.
	 * @param \DOMElement $wrapper The outermost table.
	 * @return bool
	 */
	private static function belongs_to( $row, $wrapper ) {
		for ( $node = $row->parentNode; $node; $node = $node->parentNode ) {
			if ( 'table' === strtolower( $node->nodeName ) ) {
				return $node->isSameNode( $wrapper );
			}
		}

		return false;
	}

	/**
	 * Remove dangerous elements node and text alike, before any cell is read.
	 *
	 * @param \DOMDocument $document Parsed document.
	 */
	private static function strip_elements( $document ) {
		foreach ( self::STRIP_ELEMENTS as $tag ) {
			$nodes = $document->getElementsByTagName( $tag );

			// getElementsByTagName returns a live list — collect first, then
			// remove, or the iteration skips every other node.
			$doomed = [];

			foreach ( $nodes as $node ) {
				$doomed[] = $node;
			}

			foreach ( $doomed as $node ) {
				if ( $node->parentNode ) {
					$node->parentNode->removeChild( $node );
				}
			}
		}
	}

	/**
	 * Whether a row should be treated as the table header.
	 *
	 * @param \DOMElement $row Row node.
	 * @return bool
	 */
	private static function is_header_row( $row ) {
		if ( $row->parentNode && 'thead' === strtolower( $row->parentNode->nodeName ) ) {
			return true;
		}

		$has_cell = false;

		foreach ( $row->childNodes as $child ) {
			if ( XML_ELEMENT_NODE !== $child->nodeType ) {
				continue;
			}

			$name = strtolower( $child->nodeName );

			if ( 'td' === $name ) {
				return false;
			}

			if ( 'th' === $name ) {
				$has_cell = true;
			}
		}

		return $has_cell;
	}

	/**
	 * Read one row's cells.
	 *
	 * @param \DOMElement $row Row node.
	 * @return array
	 */
	private static function read_row( $row ) {
		$cells = [];

		foreach ( $row->childNodes as $child ) {
			if ( XML_ELEMENT_NODE !== $child->nodeType ) {
				continue;
			}

			$name = strtolower( $child->nodeName );

			if ( 'td' !== $name && 'th' !== $name ) {
				continue;
			}

			$cells[] = [
				'label'   => self::inner_html( $child ),
				'colspan' => self::span( $child, 'colspan' ),
				'rowspan' => self::span( $child, 'rowspan' ),
			];
		}

		return $cells;
	}

	/**
	 * A cell's inner markup, reduced to the inline allowlist.
	 *
	 * @param \DOMElement $cell Cell node.
	 * @return string
	 */
	private static function inner_html( $cell ) {
		$html = '';

		foreach ( $cell->childNodes as $child ) {
			$html .= $cell->ownerDocument->saveHTML( $child );
		}

		/**
		 * Filter the markup allowed inside an HTML-source table cell.
		 *
		 * Default is wp_kses_post(), matching what the repeater cells already
		 * render through — so lists, headings and inline formatting behave the
		 * same in both sources. Return an array to use a stricter wp_kses()
		 * allowlist instead.
		 *
		 * @param array|null $allowed wp_kses allowlist, or null for wp_kses_post().
		 */
		$allowed = apply_filters( 'sky-addons/table/allowed-cell-html', null );

		return trim( is_array( $allowed ) ? wp_kses( $html, $allowed ) : wp_kses_post( $html ) );
	}

	/**
	 * Read and clamp a span attribute.
	 *
	 * @param \DOMElement $cell Cell node.
	 * @param string      $name Attribute name.
	 * @return int
	 */
	private static function span( $cell, $name ) {
		$value = absint( $cell->getAttribute( $name ) );

		return min( $value, self::MAX_SPAN );
	}
}
