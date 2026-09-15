<?php

namespace Sky_Addons\Includes\DynamicTags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Dynamic_Tag_Manager {


	/**
	 * List of dynamic tags
	 * Add new tags here to automatically register them
	 */
	private $dynamic_tags_text = [
		// Post Tags
		'post-title'           => 'Dynamic_Tag_Post_Title',
		'post-id'              => 'Dynamic_Tag_Post_ID',
		'post-slug'            => 'Dynamic_Tag_Post_Slug',
		'post-date'            => 'Dynamic_Tag_Post_Date',
		'post-time'            => 'Dynamic_Tag_Post_Time',
		'post-author'          => 'Dynamic_Tag_Post_Author',
		'post-comments'        => 'Dynamic_Tag_Post_Comments',
		'post-excerpt'         => 'Dynamic_Tag_Post_Excerpt',
		'post-content'         => 'Dynamic_Tag_Post_Content',
		'post-terms'           => 'Dynamic_Tag_Post_Terms',
		'post-status'          => 'Dynamic_Tag_Post_Status',
		'post-type'            => 'Dynamic_Tag_Post_Type',
		'post-custom-field'    => 'Dynamic_Tag_Post_Custom_Field',
		'post-featured-image'  => 'Dynamic_Tag_Post_Featured_Image',
		// Site Tags
		'site-title'           => 'Dynamic_Tag_Site_Title',
		'site-tagline'         => 'Dynamic_Tag_Site_Tagline',
		'current-date-time'    => 'Dynamic_Tag_Current_Date_Time',
		'request-parameter'    => 'Dynamic_Tag_Request_Parameter',
		'shortcode'            => 'Dynamic_Tag_Shortcode',
		// Archive Tags
		'archive-title'        => 'Dynamic_Tag_Archive_Title',
		'archive-description'  => 'Dynamic_Tag_Archive_Description',
		'archive-meta'         => 'Dynamic_Tag_Archive_Meta',
		// Term Tags
		'term-id'              => 'Dynamic_Tag_Term_ID',
		'term-title'           => 'Dynamic_Tag_Term_Title',
		'term-description'     => 'Dynamic_Tag_Term_Description',
		'term-slug'            => 'Dynamic_Tag_Term_Slug',
		'term-count'           => 'Dynamic_Tag_Term_Count',
		'term-meta'            => 'Dynamic_Tag_Term_Meta',
		// User Tags
		'user-info'            => 'Dynamic_Tag_User_Info',
		'user-meta'            => 'Dynamic_Tag_User_Meta',
		// Search
		'search-query'         => 'Dynamic_Tag_Search_Query',
		'search-results-count' => 'Dynamic_Tag_Search_Results_Count',
	];

	private $dynamic_tags_url = [
		// Post Tags
		'post-url'                => 'Dynamic_Tag_Post_URL',
		'post-terms-url'          => 'Dynamic_Tag_Post_Terms_URL',
		'post-author-url'         => 'Dynamic_Tag_Post_Author_URL',
		'post-comments-url'       => 'Dynamic_Tag_Post_Comments_URL',
		'post-featured-image-url' => 'Dynamic_Tag_Post_Featured_Image_URL',
		'post-navigation-url'     => 'Dynamic_Tag_Post_Navigation_URL',
		// Archive Tags
		'archive-url'             => 'Dynamic_Tag_Archive_URL',
		// Site Tags
		'site-url'                => 'Dynamic_Tag_Site_URL',
		// Term Tags
		'term-url'                => 'Dynamic_Tag_Term_URL',
		// User Tags
		'user-url'                => 'Dynamic_Tag_User_URL',
		'login-logout-url'        => 'Dynamic_Tag_Login_Logout_URL',
		// Media Tags
		'attachment-url'          => 'Dynamic_Tag_Attachment_URL',

	];

	private $dynamic_tags_image = [
		// Post Tags
		'post-featured-image'     => 'Dynamic_Tag_Post_Featured_Image_Data',
		'post-author-avatar'      => 'Dynamic_Tag_Post_Author_Avatar',
		'post-custom-field-image' => 'Dynamic_Tag_Post_Custom_Field_Image',

		// Archive Tags
		'archive-meta-image'      => 'Dynamic_Tag_Archive_Meta_Image',

		// Site Tags
		'site-logo'               => 'Dynamic_Tag_Site_Logo',
		'site-icon'               => 'Dynamic_Tag_Site_Icon',
		// User Tags
		'user-avatar'             => 'Dynamic_Tag_User_Avatar',
	];

	private $dynamic_tags_woocommerce_text = [
		// Product Tags
		'product-attribute'     => 'Dynamic_Tag_Product_Attribute',
		'product-description'   => 'Dynamic_Tag_Product_Description',
		'product-price'         => 'Dynamic_Tag_Product_Price',
		'product-purchase-note' => 'Dynamic_Tag_Product_Purchase_Note',
		'product-rating'        => 'Dynamic_Tag_Product_Rating',
		'product-sale'          => 'Dynamic_Tag_Product_Sale',
		'product-shipping'      => 'Dynamic_Tag_Product_Shipping',
		'product-sku'           => 'Dynamic_Tag_Product_SKU',
		'product-stock'         => 'Dynamic_Tag_Product_Stock',
		'product-terms'         => 'Dynamic_Tag_Product_Terms',
		'product-title'         => 'Dynamic_Tag_Product_Title',
		'product-type'          => 'Dynamic_Tag_Product_Type',
	];
	private $dynamic_tags_woocommerce_url  = [
		'product-url'              => 'Dynamic_Tag_Product_URL',
		'product-add-to-cart-url'  => 'Dynamic_Tag_Product_Add_To_Cart_URL',
		'product-term-url'         => 'Dynamic_Tag_Product_Term_URL',
		'product-review-url'       => 'Dynamic_Tag_Product_Review_URL',
		'product-checkout-url'     => 'Dynamic_Tag_Product_Checkout_URL',
		'product-back-to-shop-url' => 'Dynamic_Tag_Product_Back_To_Shop_URL',
		'product-archive-url'      => 'Dynamic_Tag_Product_Archive_URL',
		'product-term-archive-url' => 'Dynamic_Tag_Product_Term_Archive_URL',
	];

	private $dynamic_tags_woocommerce_image = [
		'product-featured-image' => 'Dynamic_Tag_Product_Featured_Image',
		'product-gallery-image'  => 'Dynamic_Tag_Product_Gallery_Image',
		'product-term-image'     => 'Dynamic_Tag_Product_Term_Image',
	];

	private $dynamic_tags_acf = [
		'acf-text'      => 'Dynamic_Tag_ACF_Text',
		'acf-url'       => 'Dynamic_Tag_ACF_URL',
		'acf-image'     => 'Dynamic_Tag_ACF_Image',
		'acf-gallery'   => 'Dynamic_Tag_ACF_Gallery',
		'acf-date-time' => 'Dynamic_Tag_ACF_Date_Time',
	];

	public function __construct() {
		add_action( 'elementor/dynamic_tags/register', [ $this, 'register_dynamic_tag_group' ], 1 );
		add_action( 'elementor/dynamic_tags/register', [ $this, 'register_dynamic_tag' ] );
	}

	public function register_dynamic_tag_group( $dynamic_tags_manager ) {
		$dynamic_tags_manager->register_group(
			'sky-addons-post',
			[
				'title' => esc_html__( 'Sky Addons - Post', 'sky-elementor-addons' ),
			]
		);

		$dynamic_tags_manager->register_group(
			'sky-addons-site',
			[
				'title' => esc_html__( 'Sky Addons - Site', 'sky-elementor-addons' ),
			]
		);

		$dynamic_tags_manager->register_group(
			'sky-addons-archive',
			[
				'title' => esc_html__( 'Sky Addons - Archive', 'sky-elementor-addons' ),
			]
		);

		$dynamic_tags_manager->register_group(
			'sky-addons-term',
			[
				'title' => esc_html__( 'Sky Addons - Term', 'sky-elementor-addons' ),
			]
		);

		$dynamic_tags_manager->register_group(
			'sky-addons-user',
			[
				'title' => esc_html__( 'Sky Addons - User', 'sky-elementor-addons' ),
			]
		);

		$dynamic_tags_manager->register_group(
			'sky-addons-search',
			[
				'title' => esc_html__( 'Sky Addons - Search', 'sky-elementor-addons' ),
			]
		);

		$dynamic_tags_manager->register_group(
			'sky-addons-media',
			[
				'title' => esc_html__( 'Sky Addons - Media', 'sky-elementor-addons' ),
			]
		);

		if ( class_exists( 'WooCommerce' ) ) {
			$dynamic_tags_manager->register_group(
				'sky-addons-woocommerce',
				[
					'title' => esc_html__( 'Sky Addons - WooCommerce', 'sky-elementor-addons' ),
				]
			);
		}

		if ( class_exists( 'ACF' ) ) {
			$dynamic_tags_manager->register_group(
				'sky-addons-acf',
				[
					'title' => esc_html__( 'Sky Addons - ACF', 'sky-elementor-addons' ),
				]
			);
		}
	}

	public function register_dynamic_tag( $dynamic_tags_manager ) {
		require_once SKY_ADDONS_PATH . 'includes/dynamic-tags/utils.php';
		$this->register_text_tags( $dynamic_tags_manager );
		$this->register_url_tags( $dynamic_tags_manager );
		$this->register_image_tags( $dynamic_tags_manager );
		$this->register_woocommerce_text_tags( $dynamic_tags_manager );
		$this->register_woocommerce_url_tags( $dynamic_tags_manager );
		$this->register_woocommerce_image_tags( $dynamic_tags_manager );
		$this->register_acf_tags( $dynamic_tags_manager );
	}

	private function register_text_tags( $dynamic_tags_manager ) {
		foreach ( $this->dynamic_tags_text as $tag => $class ) {
			$class = __NAMESPACE__ . '\\Tags\\Text\\' . $class;
			// Include the tag file
			$file = SKY_ADDONS_PATH . 'includes/dynamic-tags/tags/text/' . $tag . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
				if ( class_exists( $class ) ) {
					$dynamic_tags_manager->register( new $class() );
				}
			}
		}
	}

	private function register_url_tags( $dynamic_tags_manager ) {
		foreach ( $this->dynamic_tags_url as $tag => $class ) {
			$class = __NAMESPACE__ . '\\Tags\\Url\\' . $class;
			$file  = SKY_ADDONS_PATH . 'includes/dynamic-tags/tags/url/' . $tag . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
				if ( class_exists( $class ) ) {
					$dynamic_tags_manager->register( new $class() );
				}
			}
		}
	}

	private function register_image_tags( $dynamic_tags_manager ) {
		foreach ( $this->dynamic_tags_image as $tag => $class ) {
			$class = __NAMESPACE__ . '\\Tags\\Image\\' . $class;
			$file  = SKY_ADDONS_PATH . 'includes/dynamic-tags/tags/image/' . $tag . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
				if ( class_exists( $class ) ) {
					$dynamic_tags_manager->register( new $class() );
				}
			}
		}
	}

	private function register_woocommerce_text_tags( $dynamic_tags_manager ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		foreach ( $this->dynamic_tags_woocommerce_text as $tag => $class ) {
			$class = __NAMESPACE__ . '\\Tags\\Woocommerce\\Text\\' . $class;
			$file  = SKY_ADDONS_PATH . 'includes/dynamic-tags/tags/woocommerce/text/' . $tag . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
				if ( class_exists( $class ) ) {
					$dynamic_tags_manager->register( new $class() );
				}
			}
		}
	}

	private function register_woocommerce_url_tags( $dynamic_tags_manager ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		foreach ( $this->dynamic_tags_woocommerce_url as $tag => $class ) {
			$class = __NAMESPACE__ . '\\Tags\\Woocommerce\\Url\\' . $class;
			$file  = SKY_ADDONS_PATH . 'includes/dynamic-tags/tags/woocommerce/url/' . $tag . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
				if ( class_exists( $class ) ) {
					$dynamic_tags_manager->register( new $class() );
				}
			}
		}
	}

	private function register_woocommerce_image_tags( $dynamic_tags_manager ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		foreach ( $this->dynamic_tags_woocommerce_image as $tag => $class ) {
			$class = __NAMESPACE__ . '\\Tags\\Woocommerce\\Image\\' . $class;
			$file  = SKY_ADDONS_PATH . 'includes/dynamic-tags/tags/woocommerce/image/' . $tag . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;

				if ( class_exists( $class ) ) {
					$dynamic_tags_manager->register( new $class() );
				}
			}
		}
	}

	private function register_acf_tags( $dynamic_tags_manager ) {
		if ( ! class_exists( 'ACF' ) ) {
			return;
		}

		foreach ( $this->dynamic_tags_acf as $tag => $class ) {
			$class = __NAMESPACE__ . '\\Tags\\Acf\\' . $class;
			$file  = SKY_ADDONS_PATH . 'includes/dynamic-tags/tags/acf/' . $tag . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
				if ( class_exists( $class ) ) {
					$dynamic_tags_manager->register( new $class() );
				}
			}
		}
	}
}

new Dynamic_Tag_Manager();
