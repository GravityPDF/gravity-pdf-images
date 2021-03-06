<?php

namespace GFPDF\Plugins\Images\Fields;

use GFPDF\Plugins\Images\Shared\ImageInfo;
use GFPDF\Helper\Fields\Field_Fileupload;
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
 * Controls the display and output of the File Upload field
 *
 * @since 0.1
 */
class ImageUploads extends Field_Fileupload {

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
	 * Mark field as empty if the Group Images setting is enabled and there are no non-image uploads. Otherwise, let the
	 * parent class determine if the field is empty.
	 *
	 * @return bool
	 *
	 * @since 0.1
	 */
	public function is_empty() {
		$uploads             = $this->value();
		$non_image_uploads   = $this->get_non_images( $uploads );
		$should_group_images = ( isset( $this->pdf_settings['group_uploaded_images'] ) ) ? $this->pdf_settings['group_uploaded_images'] : 'No';

		if ( $should_group_images === 'Yes' && count( $non_image_uploads ) === 0 ) {
			return true;
		}

		return parent::is_empty();
	}

	/**
	 * If the field has uploaded images and they've been resized already we'll include the resized image URL and path
	 * in the $form_data['images'] array.
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function form_data() {
		if ( $this->has_images() ) {
			$image_uploads = $this->get_images( $this->value() );

			$field_id = $this->field->id;
			$data     = [];

			foreach ( $image_uploads as $file ) {
				$path               = $this->misc->convert_url_to_path( $file );
				$resized_image_path = ( $path !== false ) ? $this->image_info->get_image_resized_filepath( $path ) : false;

				/* Only include the new form_data info if the resized image exists */
				if ( is_file( $resized_image_path ) ) {
					$resized_image_url = $this->image_info->get_image_resized_filepath( $file );

					$data[ $field_id ] = [
						'url'  => $resized_image_url,
						'path' => $resized_image_path,
					];
				}
			}

			/* Merge the new `image` data with this fields standard form_data */
			return array_merge(
				parent::form_data(),
				[ 'images' => $data ]
			);
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
		$uploads       = $this->value();
		$image_uploads = $this->get_images( $uploads );

		return count( $image_uploads ) > 0;
	}

	/**
	 * Handle the field's HTML output for both images and non-images.
	 *
	 * @param string $value
	 * @param bool   $label
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function html( $value = '', $label = true ) {
		$uploads             = $this->value();
		$image_uploads       = $this->get_images( $uploads );
		$non_image_uploads   = $this->get_non_images( $uploads );
		$should_group_images = ( isset( $this->pdf_settings['group_uploaded_images'] ) ) ? $this->pdf_settings['group_uploaded_images'] : 'No';

		/* Let the parent class handle the output if no images have been uploaded */
		if ( count( $image_uploads ) === 0 ) {
			return parent::html( $value, $label );
		}

		/* Don't display any content if images are grouped at the end of the document and there's no non-images uploaded (handles "Show Empty Fields" edge case) */
		if ( count( $non_image_uploads ) === 0 && count( $image_uploads ) > 0 && $should_group_images === 'Yes' ) {
			return '';
		}

		/* Generate images and non-images markup */
		$html = '';
		$html .= $this->get_non_image_html( $non_image_uploads );

		if ( $should_group_images === 'No' ) {
			$html .= $this->get_image_html( $image_uploads );
		}

		return Helper_Abstract_Fields::html( $html );
	}

	/**
	 * Generate the field's image-only markup (used when the Group Images setting is enabled)
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function group_html() {
		$uploads       = $this->value();
		$image_uploads = $this->get_images( $uploads );

		return Helper_Abstract_Fields::html(
			$this->get_image_html( $image_uploads )
		);
	}

	/**
	 * Returns an array containing the URL to all uploaded images
	 *
	 * @param array $uploads
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function get_images( $uploads ) {
		return array_filter( $uploads, function( $path ) {
			return $this->image_info->does_file_have_image_extension( $path );
		} );
	}

	/**
	 * Returns an array containing the URL to all uploaded non-images
	 *
	 * @param $uploads
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function get_non_images( $uploads ) {
		return array_filter( $uploads, function( $path ) {
			return ! $this->image_info->does_file_have_image_extension( $path );
		} );
	}

	/**
	 * Returns the markup used to display non-images
	 *
	 * @param $uploads
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function get_non_image_html( $uploads ) {
		if ( count( $uploads ) == 0 ) {
			return '';
		}

		ob_start();
		?>
        <ul class="bulleted fileupload-non-image-container">
			<?php foreach ( $uploads as $i => $file ): ?>
                <li id="field-<?php echo $this->field->id; ?>-non-image-option-<?php echo $i; ?>">
                    <a href="<?php echo esc_url( $file ); ?>">
						<?php echo basename( $file ); ?>
                    </a>
                </li>
			<?php endforeach; ?>
        </ul>
		<?php

		return ob_get_clean();
	}

	/**
	 * Returns the markup used to display images
	 *
	 * @param $uploads
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function get_image_html( $uploads ) {
		if ( count( $uploads ) === 0 ) {
			return '';
		}

		$max_image_height = ( isset( $this->pdf_settings['uploaded_images_max_height'] ) ) ? $this->pdf_settings['uploaded_images_max_height'] : '300';

		ob_start();
		?>
        <div class="fileupload-images-container <?php echo $this->get_image_column_class(); ?>">
			<?php foreach ( $uploads as $i => $file ):
				$path = $this->misc->convert_url_to_path( $file );
				$resized_image = ( $path !== false ) ? $this->image_info->get_image_resized_filepath( $path ) : false;

				if ( is_file( $resized_image ) ) {
					$img_string = $resized_image;
				} elseif ( $path ) {
					$img_string = $path;
				} else {
					$img_string = $file;
				}
				?>

                <div id="field-<?php echo $this->field->id; ?>-image-option-<?php echo $i; ?>"
                     class="fileupload-images">
                    <a href="<?php echo esc_url( $file ); ?>">
                        <img src="<?php echo $img_string; ?>" style="max-height: <?php echo $max_image_height; ?>px" />
                    </a>
                </div>
			<?php endforeach; ?>
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