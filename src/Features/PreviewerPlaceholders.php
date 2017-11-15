<?php

namespace GFPDF\Plugins\Images\Features;

use GFPDF\Plugins\Images\Shared\ImageInfo;
use GFPDF\Helper\Helper_Interface_Filters;

/**
 * @package     Gravity PDF Images
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
 * Class PreviewerPlaceholders
 *
 * @package GFPDF\Plugins\Images\Features
 */
class PreviewerPlaceholders implements Helper_Interface_Filters {

	/**
	 * @var ImageInfo
	 *
	 * @since 0.1
	 */
	protected $image_info;

	/**
	 * @param ImageInfo $image_info
	 *
	 * @since 0.1
	 */
	public function __construct( ImageInfo $image_info ) {
		$this->image_info = $image_info;
	}

	/**
	 * Initialise our module
	 *
	 * @since 0.1
	 */
	public function init() {
		$this->add_filters();
	}

	/**
	 * @since 0.1
	 */
	public function add_filters() {
		add_filter( 'gfpdf_field_value', [ $this, 'maybe_override_images_when_using_previewer' ], 10, 5 );
	}

	/**
	 * Replace images with placeholders when generating PDFs for Previewer.
	 * This is needed because images haven't been resized yet and can cause resource overload attempting to use the full-
	 * sized images.
	 *
	 * @param string $value
	 * @param object $field
	 * @param array  $entry
	 * @param array  $form
	 * @param object $pdf_field
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function maybe_override_images_when_using_previewer( $value, $field, $entry, $form, $pdf_field ) {

		/* Allow feature to be easily disabled */
		$prevent_placeholder = apply_filters( 'gfpdf_prevent_previewer_image_placeholder', false, $value, $field, $entry, $form, $pdf_field );

		/*
		 * Ensure this is a REST request and that the settings method in our PDF field class exists
		 * Note: right now only the special Image PDF field classes include these PDF settings methods so we need to check
		 * they exist
		 */
		if ( ! $prevent_placeholder && isset( $GLOBALS['wp']->query_vars['rest_route'] ) && method_exists( $pdf_field, 'get_pdf_settings' ) ) {
			$settings = $pdf_field->get_pdf_settings();
			$route    = $GLOBALS['wp']->query_vars['rest_route'];

			/* Ensure the REST route is for generating the PDF, the Show Image settings are enabled and we are handling file fields */
			if ( strpos( $route, 'gravity-pdf-previewer/v1/generator' ) !== false &&
			     isset( $settings['display_uploaded_images'] ) && $settings['display_uploaded_images'] === 'Yes' &&
			     ( $field->get_input_type() === 'fileupload' || $field->get_input_type() === 'post_image' ) ) {

				/* Prepare multi-upload and single upload fields to be in the same format */
				$field_files_array = ( $field->multipleFiles ) ? (array) json_decode( stripslashes( $value ), true ) : [ $value ];

				/* Replace all image references with a placeholder */
				foreach ( $field_files_array as $key => $field_files ) {
					if ( $this->image_info->does_file_have_image_extension( $field_files ) ) {
						$field_files_array[ $key ] = plugin_dir_url( GFPDF_PDF_IMAGES_FILE ) . 'assets/images/placeholder.png';
					}
				}

				/* Put multi-upload and single upload fields back in the correct format */
				$value = ( $field->multipleFiles ) ? json_encode( $field_files_array ) : $field_files_array[0];
			}
		}

		return $value;
	}
}