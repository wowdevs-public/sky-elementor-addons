<?php

namespace Sky_Addons\Features;

defined( 'ABSPATH' ) || exit;

/**
 * Video Link
 *
 * Adds a "Video Link" field to the post editor, stored in the `sky_video_link_meta`
 * post meta. Post widgets read it through the Video Source control, which defaults
 * to this very key — so the video feature works out of the box, with no custom
 * field plugin required.
 *
 * Disable the "Video Link" advanced feature to remove the field entirely; existing
 * values stay in the database and keep rendering.
 */
class Video_Link {

	/**
	 * Post meta key. Also the default value of the widgets' Video Source control.
	 */
	const META_KEY = 'sky_video_link_meta';

	const NONCE_ACTION = 'sky_addons_video_link_save';
	const NONCE_NAME   = 'sky_addons_video_link_nonce';

	private static $instance = null;

	/**
	 * Memoized post type list.
	 *
	 * @var string[]|null
	 */
	private $post_types = null;

	private function __construct() {
		$this->init();
	}

	public function init() {
		add_action( 'init', [ $this, 'register_meta' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		add_action( 'save_post', [ $this, 'save_meta_box' ], 10, 2 );
	}

	/**
	 * Post types that get the field.
	 *
	 * Every public post type except attachments. Filter to narrow or widen it:
	 *
	 *     add_filter( 'sky_addons/video_link/post_types', function( $post_types ) {
	 *         return [ 'post', 'movie' ];
	 *     } );
	 *
	 * @return string[]
	 */
	public function get_post_types() {
		if ( null !== $this->post_types ) {
			return $this->post_types;
		}

		$post_types = get_post_types( [ 'public' => true ], 'names' );
		unset( $post_types['attachment'] );

		/**
		 * Filters the post types that show the Video Link field.
		 *
		 * @param string[] $post_types Post type names.
		 */
		$post_types = apply_filters( 'sky_addons/video_link/post_types', array_values( $post_types ) );

		$this->post_types = array_filter( array_map( 'strval', (array) $post_types ) );

		return $this->post_types;
	}

	/**
	 * Register the meta so it is available over the REST API and to the block editor.
	 */
	public function register_meta() {
		foreach ( $this->get_post_types() as $post_type ) {
			register_post_meta(
				$post_type,
				self::META_KEY,
				[
					'type'              => 'string',
					'description'       => esc_html__( 'Video URL shown by Sky Addons post widgets.', 'sky-elementor-addons' ),
					'single'            => true,
					'default'           => '',
					'show_in_rest'      => true,
					'sanitize_callback' => [ $this, 'sanitize' ],
					'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
						return current_user_can( 'edit_post', $post_id );
					},
				]
			);
		}
	}

	public function add_meta_box() {
		foreach ( $this->get_post_types() as $post_type ) {
			add_meta_box(
				'sky_addons_video_link',
				esc_html__( 'Video Link', 'sky-elementor-addons' ),
				[ $this, 'render_meta_box' ],
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Meta box markup.
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$value = get_post_meta( $post->ID, self::META_KEY, true );
		$value = is_string( $value ) ? $value : '';
		?>
		<p>
			<label class="screen-reader-text" for="sky-addons-video-link">
				<?php esc_html_e( 'Video Link', 'sky-elementor-addons' ); ?>
			</label>
			<input type="text" inputmode="url" class="widefat" id="sky-addons-video-link"
				name="<?php echo esc_attr( self::META_KEY ); ?>"
				value="<?php echo esc_attr( $value ); ?>"
				placeholder="https://www.youtube.com/watch?v=XHOmBV4js_E">
		</p>
		<p class="description">
			<?php esc_html_e( 'YouTube, Vimeo or self-hosted video URL. Post widgets with Show Video enabled turn this into a play button.', 'sky-elementor-addons' ); ?>
			<?php if ( current_user_can( 'manage_options' ) ) : ?>
				<br>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=sky-addons#advanced' ) ); ?>">
					<?php esc_html_e( 'Turn this field off in Sky Addons → Advanced', 'sky-elementor-addons' ); ?>
				</a>
			<?php endif; ?>
		</p>
		<?php
	}

	/**
	 * Persist the field.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function save_meta_box( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) );

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! in_array( $post->post_type, $this->get_post_types(), true ) ) {
			return;
		}

		$value = isset( $_POST[ self::META_KEY ] ) ? $this->sanitize( wp_unslash( $_POST[ self::META_KEY ] ) ) : '';

		if ( '' === $value ) {
			delete_post_meta( $post_id, self::META_KEY );
		} else {
			update_post_meta( $post_id, self::META_KEY, $value );
		}
	}

	/**
	 * Sanitize a submitted video URL.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public function sanitize( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		return esc_url_raw( trim( $value ) );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
