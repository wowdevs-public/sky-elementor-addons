<?php
/**
 * Menu Duplicator feature.
 *
 * Adds a "Duplicate Menu" button to Appearance → Menus (nav-menus.php) that
 * clones a nav menu with all its items, hierarchy and item meta.
 *
 * Advanced feature — gated by Managers::is_advanced_feature_active( 'menu-duplicator' ).
 * Ships enabled: the gate uses the inactive-pattern, so a slug absent from
 * sky_addons_advanced_settings['inactive'] counts as ACTIVE.
 *
 * @package Sky_Addons
 */

namespace Sky_Addons\Features;

defined( 'ABSPATH' ) || exit;

class Menu_Duplicator {

	private static $instance = null;

	private function __construct() {
		add_action( 'admin_action_sky_addons_duplicate_menu', [ $this, 'duplicate_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_menu_scripts' ] );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Inject the duplicate button JS/CSS on the nav-menus screen.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_menu_scripts( $hook ) {
		if ( 'nav-menus.php' !== $hook || ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		// No external files — small admin-only script/style, kept inline to avoid
		// coupling with the widget asset build pipeline.
		wp_register_script( 'sky-addons-menu-duplicator', false, [], SKY_ADDONS_VERSION, true );
		wp_enqueue_script( 'sky-addons-menu-duplicator' );

		$data = [
			'nonce'       => wp_create_nonce( 'sky_addons_duplicate_menu_' . get_current_user_id() ),
			'buttonText'  => esc_html__( 'Duplicate Menu', 'sky-elementor-addons' ),
			'confirmText' => esc_html__( 'Are you sure you want to duplicate this menu?', 'sky-elementor-addons' ),
		];
		wp_add_inline_script( 'sky-addons-menu-duplicator', 'window.skyMenuDuplicate = ' . wp_json_encode( $data ) . ';', 'before' );
		wp_add_inline_script( 'sky-addons-menu-duplicator', $this->inline_js() );

		wp_register_style( 'sky-addons-menu-duplicator', false );
		wp_enqueue_style( 'sky-addons-menu-duplicator' );
		wp_add_inline_style( 'sky-addons-menu-duplicator', $this->inline_css() );
	}

	/**
	 * Button-injection script for the nav-menus screen.
	 *
	 * @return string
	 */
	private function inline_js() {
		return <<<'JS'
document.addEventListener('DOMContentLoaded', function () {
	var data = window.skyMenuDuplicate || {};

	function addDuplicateButton() {
		var existing = document.querySelector('.sa-duplicate-menu-btn');
		if (existing) { existing.remove(); }

		var selectMenu = document.getElementById('select-menu-to-edit');
		if (!selectMenu) { return; }

		var currentMenuId = selectMenu.value;
		if (!currentMenuId || currentMenuId === '0' || currentMenuId === '-1') { return; }

		var btn = document.createElement('a');
		btn.href = '#';
		btn.className = 'button sa-duplicate-menu-btn';
		btn.setAttribute('data-menu-id', currentMenuId);
		btn.setAttribute('data-nonce', data.nonce || '');
		btn.textContent = data.buttonText || 'Duplicate Menu';
		btn.style.marginLeft = '10px';

		var container = selectMenu.closest('.menu-edit');
		if (container) {
			var submit = container.querySelector('.button-secondary');
			if (submit && submit.nextSibling) {
				submit.parentNode.insertBefore(btn, submit.nextSibling);
			} else if (submit) {
				submit.parentNode.appendChild(btn);
			} else {
				selectMenu.parentNode.appendChild(btn);
			}
		} else {
			selectMenu.parentNode.insertBefore(btn, selectMenu.nextSibling);
		}
	}

	function handleClick(e) {
		if (!e.target || !e.target.classList.contains('sa-duplicate-menu-btn')) { return; }
		e.preventDefault();
		var menuId = e.target.getAttribute('data-menu-id');
		var nonce = e.target.getAttribute('data-nonce');
		if (window.confirm(data.confirmText || 'Are you sure you want to duplicate this menu?')) {
			var adminUrl = window.location.origin + window.location.pathname.replace('/nav-menus.php', '/admin.php');
			window.location.href = adminUrl + '?action=sky_addons_duplicate_menu&menu_id=' + menuId + '&duplicate_nonce=' + nonce;
		}
	}

	addDuplicateButton();

	var selectMenu = document.getElementById('select-menu-to-edit');
	if (selectMenu) {
		selectMenu.addEventListener('change', addDuplicateButton);
	}

	document.addEventListener('click', handleClick);
	setTimeout(addDuplicateButton, 500);
});
JS;
	}

	/**
	 * Duplicate button styles.
	 *
	 * @return string
	 */
	private function inline_css() {
		return '.sa-duplicate-menu-btn{background:#0073aa;border:1px solid #0073aa;color:#fff;text-decoration:none;display:inline-block;padding:5px 10px;border-radius:3px;cursor:pointer;transition:all .2s ease}.sa-duplicate-menu-btn:hover{background:#005a87;border-color:#005a87;color:#fff;text-decoration:none}.sa-duplicate-menu-btn:focus{box-shadow:0 0 0 1px #fff,0 0 0 3px #0073aa;outline:none}';
	}

	/**
	 * Handle the duplicate request.
	 */
	public function duplicate_menu() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( esc_html__( 'You don\'t have permission to duplicate menus!', 'sky-elementor-addons' ) );
		}

		if ( ! isset( $_GET['menu_id'], $_GET['duplicate_nonce'] ) ) {
			wp_die( esc_html__( 'Invalid request!', 'sky-elementor-addons' ) );
		}

		$menu_id = absint( $_GET['menu_id'] );
		$nonce   = sanitize_text_field( wp_unslash( $_GET['duplicate_nonce'] ) );

		if ( ! wp_verify_nonce( $nonce, 'sky_addons_duplicate_menu_' . get_current_user_id() ) ) {
			wp_die( esc_html__( 'Security check failed!', 'sky-elementor-addons' ) );
		}

		$original_menu = wp_get_nav_menu_object( $menu_id );

		if ( ! $original_menu ) {
			wp_die( esc_html__( 'Menu not found!', 'sky-elementor-addons' ) );
		}

		$new_menu_id = $this->duplicate_menu_items( $original_menu );

		if ( $new_menu_id ) {
			wp_safe_redirect( admin_url( 'nav-menus.php?action=edit&menu=' . $new_menu_id . '&duplicated=1' ) );
			exit;
		}

		wp_die( esc_html__( 'Failed to duplicate menu!', 'sky-elementor-addons' ) );
	}

	/**
	 * Duplicate a menu and all of its items, preserving hierarchy and meta.
	 *
	 * @param object $original_menu Source menu object.
	 * @return int|false New menu ID, or false on failure.
	 */
	private function duplicate_menu_items( $original_menu ) {
		/* translators: %s: original menu name */
		$new_menu_name = sprintf( esc_html__( '%s - [Duplicated]', 'sky-elementor-addons' ), $original_menu->name );

		$new_menu = wp_create_nav_menu( $new_menu_name );

		if ( is_wp_error( $new_menu ) ) {
			return false;
		}

		$new_menu_id = $new_menu;
		$menu_items  = wp_get_nav_menu_items( $original_menu->term_id );

		if ( ! $menu_items ) {
			return $new_menu_id;
		}

		usort(
			$menu_items,
			function ( $a, $b ) {
				return $a->menu_order - $b->menu_order;
			}
		);

		$item_id_mapping = [];

		// First pass: create items with no parent set.
		foreach ( $menu_items as $item ) {
			$new_item_data = [
				'menu-item-object-id'   => $item->object_id,
				'menu-item-object'      => $item->object,
				'menu-item-parent-id'   => 0,
				'menu-item-position'    => $item->menu_order,
				'menu-item-type'        => $item->type,
				'menu-item-title'       => $item->title,
				'menu-item-url'         => $item->url,
				'menu-item-description' => $item->description,
				'menu-item-attr-title'  => $item->attr_title,
				'menu-item-target'      => $item->target,
				'menu-item-classes'     => is_array( $item->classes ) ? implode( ' ', $item->classes ) : $item->classes,
				'menu-item-xfn'         => $item->xfn,
				'menu-item-status'      => 'publish',
			];

			$new_item_id = wp_update_nav_menu_item( $new_menu_id, 0, $new_item_data );

			if ( ! is_wp_error( $new_item_id ) ) {
				$item_id_mapping[ $item->ID ] = $new_item_id;
				$this->copy_all_menu_item_meta( $item->ID, $new_item_id );
				$this->refresh_menu_item_data( $new_item_id, $item );
			}
		}

		// Second pass: wire up parent relationships.
		foreach ( $menu_items as $item ) {
			$parent_id = get_post_meta( $item->ID, '_menu_item_menu_item_parent', true );

			if ( $parent_id && '0' !== $parent_id && isset( $item_id_mapping[ $item->ID ], $item_id_mapping[ $parent_id ] ) ) {
				wp_update_nav_menu_item(
					$new_menu_id,
					$item_id_mapping[ $item->ID ],
					[ 'menu-item-parent-id' => $item_id_mapping[ $parent_id ] ]
				);
			}
		}

		wp_cache_delete( 'nav_menu_items', 'posts' );
		clean_post_cache( $new_menu_id );

		return $new_menu_id;
	}

	/**
	 * Copy every meta row of a menu item (except the parent link, handled separately).
	 *
	 * @param int $original_item_id Source item ID.
	 * @param int $new_item_id      New item ID.
	 */
	private function copy_all_menu_item_meta( $original_item_id, $new_item_id ) {
		global $wpdb;

		$meta_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d",
				$original_item_id
			)
		);

		if ( $meta_data ) {
			foreach ( $meta_data as $meta ) {
				if ( '_menu_item_menu_item_parent' === $meta->meta_key ) {
					continue;
				}
				update_post_meta( $new_item_id, $meta->meta_key, maybe_unserialize( $meta->meta_value ) );
			}
		}

		$original_item = wp_setup_nav_menu_item( get_post( $original_item_id ) );

		if ( $original_item ) {
			$essential_meta = [
				'_menu_item_type'      => $original_item->type,
				'_menu_item_object'    => $original_item->object,
				'_menu_item_object_id' => $original_item->object_id,
				'_menu_item_url'       => $original_item->url,
				'_menu_item_target'    => $original_item->target,
				'_menu_item_xfn'       => $original_item->xfn,
			];

			foreach ( $essential_meta as $meta_key => $meta_value ) {
				if ( ! empty( $meta_value ) ) {
					update_post_meta( $new_item_id, $meta_key, $meta_value );
				}
			}
		}
	}

	/**
	 * Force title/url/meta refresh so labels resolve correctly per item type.
	 *
	 * @param int    $new_item_id   New item ID.
	 * @param object $original_item Source item object.
	 */
	private function refresh_menu_item_data( $new_item_id, $original_item ) {
		wp_update_post(
			[
				'ID'           => $new_item_id,
				'post_title'   => $original_item->title,
				'post_content' => $original_item->description,
			]
		);

		switch ( $original_item->type ) {
			case 'custom':
				update_post_meta( $new_item_id, '_menu_item_url', $original_item->url );
				if ( ! empty( $original_item->title ) ) {
					update_post_meta( $new_item_id, '_menu_item_title', $original_item->title );
				}
				break;

			case 'post_type':
			case 'taxonomy':
				if ( $original_item->object_id ) {
					update_post_meta( $new_item_id, '_menu_item_object_id', $original_item->object_id );
					update_post_meta( $new_item_id, '_menu_item_object', $original_item->object );
				}
				break;
		}

		update_post_meta( $new_item_id, '_menu_item_type', $original_item->type );
		update_post_meta( $new_item_id, '_menu_item_target', $original_item->target );
		update_post_meta( $new_item_id, '_menu_item_classes', is_array( $original_item->classes ) ? implode( ' ', $original_item->classes ) : $original_item->classes );
		update_post_meta( $new_item_id, '_menu_item_xfn', $original_item->xfn );
	}
}
