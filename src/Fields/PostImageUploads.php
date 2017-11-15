<?php

namespace GFPDF\Plugins\Images\Fields;

use GFPDF\Plugins\Images\Shared\ImageInfo;
use GFPDF\Helper\Fields\Field_Post_Image;
use GFPDF\Helper\Helper_Abstract_Fields;

/**
 * Gravity Forms Field
 *
 * @package     Gravity PDF
 * @copyright   Copyright (c) 2017, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
    This file is part of Gravity PDF Images.

    Copyright (C) 2017, Blue Liquid Designs

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * Controls the display and output of the Post Image field
 *
 * @since 0.1
 */
class PostImageUploads extends Field_Post_Image {

	/**
	 * @var ImageInfo
	 *
	 * @since 0.1
	 */
	protected $image_info;

	/**
	 * @var array The current Gravity PDF settings
	 *
	 * @since 0.1
	 */
	protected $pdf_settings;

	/**
	 * image_info Setter
	 *
	 * @param ImageInfo $image_info
	 *
	 * @since 0.1
	 */
	public function set_image_helper( ImageInfo $image_info ) {
		$this->image_info = $image_info;
	}

	/**
	 * pdf_settings Setter
	 *
	 * @param $settings
	 *
	 * @since 0.1
	 */
	public function set_pdf_settings( $settings ) {
		$this->pdf_settings = $settings;
	}

	/**
	 * pdf_settings Getter
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function get_pdf_settings() {
		return $this->pdf_settings;
	}

	/**
	 * Mark field as empty if the Group Images setting is enabled. Otherwise, let the parent class determine if the field is empty.
	 *
	 * @return bool
	 *
	 * @since 0.1
	 */
	public function is_empty() {
		$should_group_images = ( isset( $this->pdf_settings['group_uploaded_images'] ) ) ? $this->pdf_settings['group_uploaded_images'] : 'No';

		if ( $should_group_images === 'Yes' ) {
			return true;
		}

		return parent::is_empty();
	}

	/**
	 * If the field has an uploaded image and it has been resized already we'll include the resized image URL and path
	 * in the $form_data['images'] array.
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function form_data() {
		if ( $this->has_images() ) {
			$image = $this->value();

			$field_id = $this->field->id;
			$data     = [];

			$resized_image_path = $this->image_info->get_image_resized_filepath( $image['path'] );

			/* Only include the new form_data info if the resized image exists */
			if ( is_file( $resized_image_path ) ) {
				$resized_image_url = $this->image_info->get_image_resized_filepath( $image['url'] );

				$data[ $field_id ] = [
					'url'  => $resized_image_url,
					'path' => $resized_image_path,
				];

				/* Merge the new `image` data with this fields standard form_data */
				return array_merge(
					parent::form_data(),
					[ 'images' => $data ]
				);
			}
		}

		/* No images uploaded so let the parent class handle the output */
		return parent::form_data();
	}

	/**
	 * Determine if an image was uploaded for this field
	 *
	 * @return bool
	 *
	 * @since 0.1
	 */
	public function has_images() {
		$image = $this->value();

		return isset( $image['path'] ) && is_file( $image['path'] );
	}

	/**
	 * Handle the field's HTML output for the image
	 *
	 * @param string $value
	 * @param bool   $label
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function html( $value = '', $label = true ) {
		$image = $this->value();
		$html  = '';

		/* Generate image markup if images aren't grouped at the end of the PDF */
		$should_group_images = ( isset( $this->pdf_settings['group_uploaded_images'] ) ) ? $this->pdf_settings['group_uploaded_images'] : 'No';
		if ( $should_group_images === 'No' && isset( $image['path'] ) && is_file( $image['path'] ) ) {
			$html .= $this->get_image_html( $image );
		}

		/* Don't display any content if images are grouped at the end of the document (handles "Show Empty Fields" edge case) */
		if ( $should_group_images === 'Yes' ) {
			return '';
		}

		return Helper_Abstract_Fields::html( $html );
	}

	/**
	 * Generate the field's image markup (used when the Group Images setting is enabled)
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function group_html() {
		$image = $this->value();
		$html  = $this->get_image_html( $image );

		return Helper_Abstract_Fields::html( $html );
	}

	/**
	 * Returns the markup used to display images
	 *
	 * @param array $image
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function get_image_html( $image ) {
		$max_image_height = ( isset( $this->pdf_settings['uploaded_images_max_height'] ) ) ? $this->pdf_settings['uploaded_images_max_height'] : '300';
		$resized_image    = $this->image_info->get_image_resized_filepath( $image['path'] );
		$img_string       = ( is_file( $resized_image ) ) ? $resized_image : $image['path'];

		ob_start();
		?>
        <div class="fileupload-images-container <?php echo $this->get_image_column_class(); ?>">
            <div id="field-<?php echo $this->field->id; ?>-post-image"
                 class="fileupload-images">
                <a href="<?php echo esc_url( $image['url'] ); ?>">
                    <img src="<?php echo $img_string; ?>" style="max-height: <?php echo $max_image_height; ?>px" />

					<?php if ( ! empty( $image['title'] ) ): ?>
                        <div class="gfpdf-post-image-title"><?php echo $image['title']; ?></div>
					<?php endif; ?>

					<?php if ( ! empty( $image['caption'] ) ): ?>
                        <div class="gfpdf-post-image-caption"><?php echo $image['caption']; ?></div>
					<?php endif; ?>

					<?php if ( ! empty( $image['description'] ) ): ?>
                        <div class="gfpdf-post-image-description"><?php echo $image['description']; ?></div>
					<?php endif; ?>
                </a>
            </div>
        </div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get the correct CSS class name based on the Image Format setting
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	protected function get_image_column_class() {
		/* Determine how the images should be displayed */
		$img_format = ( isset( $this->pdf_settings['display_uploaded_images_format'] ) ) ? $this->pdf_settings['display_uploaded_images_format'] : '1 Column';
		switch ( $img_format ) {
			case '2 Column':
				$img_format_css = 'fileupload-images-two-col';
			break;

			case '3 Column':
				$img_format_css = 'fileupload-images-three-col';
			break;

			case '4 Column':
				$img_format_css = 'fileupload-images-four-col';
			break;

			default:
				$img_format_css = 'fileupload-images-one-col';
			break;
		}

		return $img_format_css;
	}
}