<?php
/**
 * Omnisend Product Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Product {
	public $title;
	public $status;
	public $description;
	public $currency;
	public $tags     = array();
	public $images   = array();
	public $variants = array();
	// phpcs:disable WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
	public $productID;
	public $productUrl;
	public $categoryIDs;
	public $createdAt;
	public $updatedAt;
	// phpcs:enable
	/**
	 * @var bool
	 */
	public $published;

	public static function create( $id, $view = '' ) {
		try {
			return new Omnisend_Product( $id, $view );
		} catch ( Omnisend_Empty_Required_Fields_Exception $exception ) {
			return null;
		}
	}

	/**
	 * @throws Omnisend_Empty_Required_Fields_Exception
	 */
	private function __construct( $id, $view ) {
		$wc_product = wc_get_product( $id );
		if ( empty( $wc_product ) ) {
			throw new Omnisend_Empty_Required_Fields_Exception();
		}

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->productID = '' . $id;
		$this->title     = $wc_product->get_name();
		$this->published = $wc_product->get_status() == 'publish';

		if ( $view != 'picker' ) {
			if ( $wc_product->get_status() != 'publish' || $wc_product->get_catalog_visibility() == 'hidden' || get_post_status( $id ) == 'auto-draft' ) {
				$this->status = 'notAvailable';
			} elseif ( $wc_product->get_manage_stock() == 'yes' ) {
				if ( $wc_product->get_stock_quantity() > 0 ) {
					$this->status = 'inStock';
				} else {
					$this->status = 'outOfStock';
				}
			} elseif ( $wc_product->get_stock_status() == 'instock' ) {
				$this->status = 'inStock';
			} else {
				$this->status = 'outOfStock';
			}

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$this->createdAt = empty( $wc_product->get_date_created() ) ? gmdate( DATE_ATOM, time() ) : $wc_product->get_date_created()->format( DATE_ATOM );
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$this->updatedAt = empty( $wc_product->get_date_modified() ) ? gmdate( DATE_ATOM, time() ) : $wc_product->get_date_modified()->format( DATE_ATOM );

			$product_tags = get_the_terms( $id, 'product_tag' );
			if ( ! empty( $product_tags ) && ! is_wp_error( $product_tags ) ) {
				foreach ( $product_tags as $term ) {
					array_push( $this->tags, $term->name );
				}
			}

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$this->categoryIDs = array_map( 'strval', $wc_product->get_category_ids() );
		}
		$this->description = implode( ' ', array_slice( explode( ' ', preg_replace( '#\[[^\]]+\]#', '', $wc_product->get_description() ) ), 0, 30 ) );
		$this->currency    = get_woocommerce_currency();
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->productUrl = get_permalink( $id );

		$images        = array();
		$main_image_id = '';
		$image_counter = 0;

		if ( ! empty( $wc_product->get_image_id() ) && $wc_product->get_image_id() != 0 && wp_get_attachment_url( $wc_product->get_image_id() ) ) {
			$url                                   = esc_url( wp_get_attachment_url( $wc_product->get_image_id() ) );
			$images[ $wc_product->get_image_id() ] = array(
				'imageID'    => '' . $wc_product->get_image_id(),
				'url'        => $url,
				'isDefault'  => true,
				'variantIDs' => array( '' . $id ),
			);
			++$image_counter;
			$main_image_id = $wc_product->get_image_id();
		}

		$main_variant = array();

		$main_variant['variantID'] = '' . $id;
		$main_variant['title']     = $this->title;
		$sku                       = $wc_product->get_sku();
		if ( $sku != '' ) {
			$main_variant['sku'] = $sku;
		}

		$main_variant['status'] = $this->status;
		$main_variant['price']  = $wc_product->get_price();
		if ( $main_variant['price'] == '' ) {
			$main_variant['price'] = 0;
		} else {
			$main_variant['price'] = Omnisend_Helper::price_to_cents( $main_variant['price'] );
		}
		if ( $wc_product->is_on_sale() && Omnisend_Helper::price_to_cents( $wc_product->get_regular_price() ) > 0 ) {
			$main_variant['oldPrice'] = Omnisend_Helper::price_to_cents( $wc_product->get_regular_price() );
		}

		$main_variant['imageID'] = '' . $main_image_id;

		if ( $view == 'picker' ) {
			$main_variant['imageUrl'] = '';
			if ( $main_variant['imageID'] != '' ) {
				$main_variant['imageUrl'] = $images[ $main_variant['imageID'] ]['url'];
			} else {
				$main_variant['imageUrl'] = wc_placeholder_img_src();
			}

			$this->variants[ $main_variant['variantID'] ] = $main_variant;
		} else {
			array_push( $this->variants, $main_variant );
		}

		if ( $wc_product->is_type( 'variable' ) ) {
			$variations = $this->get_unhidden_variations( $wc_product );
			foreach ( $variations as $variation ) {
				$variant = array();

				$variant['variantID'] = '' . $variation['variation_id'];
				$variant['title']     = $this->title;
				if ( $variation['sku'] != '' ) {
					$variant['sku'] = $variation['sku'];
				}

				if ( $variation['is_in_stock'] ) {
					$variant['status'] = 'inStock';
				} else {
					$variant['status'] = 'outOfStock';
				}
				$variant['price'] = $variation['display_price'];
				if ( $variant['price'] === '' ) {
					$variant['price'] = 0;
				} else {
					$variant['price'] = Omnisend_Helper::price_to_cents( $variant['price'] );
				}
				if ( $variation['display_price'] != $variation['display_regular_price'] ) {
					$variant['oldPrice'] = Omnisend_Helper::price_to_cents( $variation['display_regular_price'] );
				}

				$variant['imageID'] = '';
				if ( $variation['image_id'] != '' ) {
					if ( isset( $images[ $variation['image_id'] ] ) ) {
						$variant['imageID'] = '' . $variation['image_id'];
					} elseif ( count( $images ) > 9 && $view != 'picker' ) {
						$variant['imageID'] = '' . $main_image_id;
					} elseif ( wp_get_attachment_url( $variation['image_id'] ) ) {
						$default = false;
						if ( $main_image_id == '' ) {
							$main_image_id = $variation['image_id'];
							$default       = true;
						}
						$images[ $variation['image_id'] ] = array(
							'imageID'   => '' . $variation['image_id'],
							'url'       => esc_url( wp_get_attachment_url( $variation['image_id'] ) ),
							'isDefault' => $default,
						);
						$variant['imageID']               = '' . $variation['image_id'];
						++$image_counter;
					} else {
						$variant['imageID'] = '' . $main_image_id;
					}
				} else {
					$variant['imageID'] = '' . $main_image_id;
				}

				if ( $view == 'picker' ) {
					if ( $variant['imageID'] != '' && array_key_exists( $variant['imageID'], $images ) ) {
						$variant['imageUrl'] = $images[ $variant['imageID'] ]['url'];
					} else {
						$variant['imageUrl'] = wc_placeholder_img_src();
					}
				}

				if ( $variation['weight'] != '' ) {
					$variant['customFields']['weight'] = $variation['weight_html'];
				}

				if ( ! empty( $variation['attributes'] ) ) {
					foreach ( $variation['attributes'] as $key => $attribute ) {
						if ( $attribute != '' ) {
							$variant['customFields'][ $key ] = $attribute;
						}
					}
				}

				if ( $variant['imageID'] != '' && array_key_exists( $variant['imageID'], $images ) ) {
					$images[ $variant['imageID'] ]['variantIDs'][] = '' . $variation['variation_id'];
				}
				if ( $view == 'picker' ) {
					$this->variants[ $variant['variantID'] ] = $variant;
				} else {
					array_push( $this->variants, $variant );
				}
			}
		}

		if ( $image_counter < 10 && $wc_product->get_gallery_image_ids() ) {
			foreach ( $wc_product->get_gallery_image_ids() as $gallery_image_id ) {
				if ( wp_get_attachment_url( $gallery_image_id ) && $gallery_image_id != $main_image_id ) {
					$default = false;
					$url     = esc_url( wp_get_attachment_url( $gallery_image_id ) );
					if ( $main_image_id == '' || $main_image_id == 0 ) {
						$default       = true;
						$main_image_id = $gallery_image_id;
					}

					$images[ $gallery_image_id ] = array(
						'imageID'   => '' . $gallery_image_id,
						'url'       => $url,
						'isDefault' => $default,
					);
					++$image_counter;
				}
				if ( $image_counter > 9 ) {
					break;
				}
			}
		}

		if ( count( $images ) > 0 ) {
			$v_column = array_column( $this->variants, 'variantID' );
			foreach ( $images as $ki => &$image ) {
				if ( array_key_exists( 'variantIDs', $image ) ) {
					foreach ( $image['variantIDs'] as $k => $variant_id ) {
						if ( ! in_array( $variant_id, $v_column ) ) {
							unset( $images[ $ki ]['variantIDs'][ $k ] );
						}
					}
				}
			}
		}

		if ( $view != 'picker' && count( $images ) > 0 ) {
			$this->images = array_values( $images );
		}

		if ( $view == 'picker' ) {
			// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( empty( $this->productID ) || empty( $this->title ) || empty( $this->currency )
				|| empty( $this->productUrl ) || empty( $this->variants ) ) {
				throw new Omnisend_Empty_Required_Fields_Exception();
			}
		} elseif ( empty( $this->productID ) || empty( $this->title ) || empty( $this->status ) || empty( $this->currency )
			|| empty( $this->productUrl ) || empty( $this->variants ) ) {
			throw new Omnisend_Empty_Required_Fields_Exception();
			// phpcs:enable
		}
	}
	public static function product_picker() {
		global $product;
		if ( $product != null ) {
			$p = self::create( $product->get_id(), 'picker' );
			if ( ! empty( $p ) ) {
				echo "<script type='text/javascript'> \n
                        omnisend_product = " . wp_json_encode( $p ) . " \n
                    </script> \n";
			}
		}
	}

	public function get_unhidden_variations( $wc_product ) {
		$available_variations = array();

		foreach ( $wc_product->get_children() as $child_id ) {
			$variation              = wc_get_product( $child_id );
			$available_variations[] = $wc_product->get_available_variation( $variation );
		}
		$available_variations = array_filter( $available_variations );

		return $available_variations;
	}
}
