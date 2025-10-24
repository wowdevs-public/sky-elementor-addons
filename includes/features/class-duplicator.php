<?php

namespace Sky_Addons\Features;

use Elementor\Core\Files\CSS\Post as Post_CSS;

defined( 'ABSPATH' ) || exit;

class Duplicator {
	private static $instance = null;

	private function __construct() {
		add_action( 'admin_action_sky_duplicate_as_draft', [ $this, 'duplicate_as_draft' ] );
		/**
		 * Post
		 */
		add_filter( 'post_row_actions', [ $this, 'duplicate_post_link' ], 10, 2 );
		/**
		 * Page
		 */
		add_filter( 'page_row_actions', [ $this, 'duplicate_post_link' ], 10, 2 );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function duplicate_as_draft() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'You don\'t have permission to duplicate it; please go back!' );
		}

		if ( ! ( isset( $_GET['post'] ) || isset( $_POST['post'] ) || ( isset( $_REQUEST['action'] ) && 'sky_duplicate_as_draft' === $_REQUEST['action'] ) ) ) {
			wp_die( 'No post to duplicate has been supplied!' );
		}

    // phpcs:ignore
		if ( ! isset( $_GET['duplicate_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['duplicate_nonce'] ), basename( __FILE__ ) ) ) {
			return;
		}

		$post_id = ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
		$post = get_post( $post_id );
		$current_user_id = get_current_user_id();

		if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_others_posts' ) ) {
			$this->duplicate_edit_post( $post_id );
		} elseif ( current_user_can( 'edit_posts' ) && $post->post_author === $current_user_id ) {
			$this->duplicate_edit_post( $post_id );
		} else {
			wp_die( 'You don\'t have permission to duplicate it; please go back!' );
		}
	}

	public function duplicate_edit_post( $post_id ) {
		global $wpdb;
		$post = get_post( $post_id );
		$current_user = wp_get_current_user();
		$new_post_author = $current_user->ID;

		if ( isset( $post ) && null !== $post ) {
			$args = [
				'post_status'    => 'draft',
				// translators: %1$s: Original post title.
				'post_title'     => sprintf( __( '%1$s - [Duplicated]', 'sky-elementor-addons' ), $post->post_title ),
				'post_type'      => $post->post_type,
				'post_name'      => $post->post_name,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_author'    => $new_post_author,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'menu_order'     => $post->menu_order,
				'to_ping'        => $post->to_ping,
			];

			$new_post_id = wp_insert_post( $args );
			$taxonomies = get_object_taxonomies( $post->post_type );

			foreach ( $taxonomies as $taxonomy ) {
				$post_terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'slugs' ] );
				wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
			}

      // phpcs:ignore
			$post_meta_infos = $wpdb->get_results( $wpdb->prepare(
				"SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d",
				$post_id
			) );

			if ( is_array( $post_meta_infos ) ) {
				$sql_query = "INSERT INTO {$wpdb->postmeta} ( post_id, meta_key, meta_value ) VALUES ";
				$sql_query_sel = [];

				foreach ( $post_meta_infos as $meta_info ) {
					$meta_value = wp_slash( $meta_info->meta_value );
					$sql_query_sel[] = "( $new_post_id, '{$meta_info->meta_key}', '{$meta_value}' )";
				}

				$sql_query .= implode( ', ', $sql_query_sel ) . ';';
        // phpcs:ignore
				$wpdb->query( $sql_query );

				$source_type = get_post_meta( $post_id, '_elementor_template_type', true );
				delete_post_meta( $new_post_id, '_elementor_template_type' );
				update_post_meta( $new_post_id, '_elementor_template_type', $source_type );
			}

			$css = Post_CSS::create( $new_post_id );
			$css->update();

			$all_post_types = get_post_types( [], 'names' );
			$current_post_type = get_post_type( $post_id );

			if ( in_array( $current_post_type, $all_post_types ) ) {
				wp_redirect( admin_url( 'edit.php?post_type=' . $current_post_type ) );
			}

			exit;
		} else {
			wp_die( 'Failed. Not Found Post: ' . esc_html( $post_id ) );
		}
	}

	public function duplicate_post_link( $actions, $post ) {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_others_posts' ) ) {
			if ( 'post' === $post->post_type ) {
				$actions['duplicate'] = '<a href="' . wp_nonce_url( 'admin.php?action=sky_duplicate_as_draft&post=' . $post->ID, basename( __FILE__ ), 'duplicate_nonce' ) . '" title="Duplicate this post" rel="permalink">' . esc_html_x( 'Duplicate Post', 'Admin String', 'sky-elementor-addons' ) . '</a>';
			} elseif ( 'page' === $post->post_type ) {
				$actions['duplicate'] = '<a href="' . wp_nonce_url( 'admin.php?action=sky_duplicate_as_draft&post=' . $post->ID, basename( __FILE__ ), 'duplicate_nonce' ) . '" title="Duplicate this page" rel="permalink">' . esc_html_x( 'Duplicate Page', 'Admin String', 'sky-elementor-addons' ) . '</a>';
			} elseif ( 'elementor_library' === $post->post_type ) {
				$actions['duplicate'] = '<a href="' . wp_nonce_url( 'admin.php?action=sky_duplicate_as_draft&post=' . $post->ID, basename( __FILE__ ), 'duplicate_nonce' ) . '" title="Duplicate this template" rel="permalink">' . esc_html_x( 'Duplicate Template', 'Admin String', 'sky-elementor-addons' ) . '</a>';
			}
		}
		return $actions;
	}
}
