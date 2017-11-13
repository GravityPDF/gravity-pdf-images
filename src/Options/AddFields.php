<?php

namespace GFPDF\Plugins\Images\Options;

use GFPDF\Plugins\Images\Shared\DoesTemplateHaveGroup;
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
 * Class AddFields
 *
 * @package GFPDF\Plugins\Images\Options
 */
class AddFields implements Helper_Interface_Filters {

	/**
	 * @var DoesTemplateHaveGroup
	 *
	 * @since 0.1
	 */
	private $group_checker;

	/**
	 * AddFields constructor.
	 *
	 * @param DoesTemplateHaveGroup $group_checker
	 *
	 * @since 0.1
	 */
	public function __construct( DoesTemplateHaveGroup $group_checker ) {
		$this->group_checker = $group_checker;
	}


	/**
	 * Initialise our module
	 *
	 * @since 0.1
	 */
	public function init() {
		$this->add_filters();

		add_action( 'gfpdf_uploaded_images_js', function( $args ) {
			echo '<script type="text/javascript">' .
			     file_get_contents( __DIR__ . '/../Javascript/enhanced-images-settings.js' ) .
			     '</script>';
		} );
	}

	/**
	 * @since 0.1
	 */
	public function add_filters() {
		add_filter( 'gfpdf_form_settings_custom_appearance', [ $this, 'add_template_option' ], 9999 );
	}

	/**
	 * Include the field label settings for Core and Universal templates
	 *
	 * @param array $settings
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function add_template_option( $settings ) {
		$override          = apply_filters( 'gfpdf_override_enhanced_images_fields', false, $settings ); /* Change this to true to override the core / universal check */
		$exclude_templates = apply_filters( 'gfpdf_excluded_templates_enhanced_images', [], $settings, 'product-field' ); /* Exclude this option for specific templates */

		if ( ! in_array( $this->group_checker->get_template_name(), $exclude_templates ) && ( $override || $this->group_checker->has_group() ) ) {
			$settings['display_uploaded_images'] = [
				'id'      => 'display_uploaded_images',
				'name'    => esc_html__( 'Display Uploaded Images', 'gravity-pdf-images' ),
				'type'    => 'radio',
				'options' => [
					'Yes' => esc_html__( 'Yes', 'gravity-pdf-images' ),
					'No'  => esc_html__( 'No', 'gravity-pdf-images' ),
				],
				'std'     => 'No',
				'tooltip' => '<h6>' . esc_html__( 'Display Uploaded Images', 'gravity-pdf-images' ) . '</h6>' . esc_html__( 'When enabled, uploaded images will be displayed in the PDF using the image format defined below. Non-image files will continue to be displayed as links in the standard list format.', 'gravity-pdf-images' ),
			];

			$settings['display_uploaded_images_format'] = [
				'id'      => 'display_uploaded_images_format',
				'name'    => esc_html__( 'Image Format', 'gravity-pdf-images' ),
				'type'    => 'radio',
				'options' => [
					'1 Column' => '<img src="' . plugin_dir_url( GFPDF_PDF_IMAGES_FILE ) . 'assets/images/image-single-column.png" width="75" alt="' . esc_html__( '1 Column', 'gravity-pdf-images' ) . '" />',
					'2 Column' => '<img src="' . plugin_dir_url( GFPDF_PDF_IMAGES_FILE ) . 'assets/images/image-two-column.png" width="75" alt="' . esc_html__( '2 Columns', 'gravity-pdf-images' ) . '" />',
					'3 Column' => '<img src="' . plugin_dir_url( GFPDF_PDF_IMAGES_FILE ) . 'assets/images/image-three-column.png" width="75" alt="' . esc_html__( '3 Columns', 'gravity-pdf-images' ) . '" />',
					'4 Column' => '<img src="' . plugin_dir_url( GFPDF_PDF_IMAGES_FILE ) . 'assets/images/image-four-column.png" width="75" alt="' . esc_html__( '4 Columns', 'gravity-pdf-images' ) . '" />',
				],
				'std'     => '1 Column',
				'class'   => 'image-radio-buttons',
				'tooltip' => '<h6>' . esc_html__( 'Image Format', 'gravity-pdf-images' ) . '</h6>' . esc_html__( 'Choose to display uploaded images in one-, two- or three-column layouts.', 'gravity-pdf-images' ),
			];

			$settings['uploaded_images_max_height'] = [
				'id'    => 'uploaded_images_max_height',
				'name'  => esc_html__( 'Maximum Image Height', 'gravity-pdf-images' ),
				'desc'  => esc_html__( 'Images will be constrained to the set height.', 'gravity-pdf-images' ),
				'desc2' => 'px',
				'type'  => 'number',
				'size'  => 'small',
				'std'   => '300',
			];

			$settings['group_uploaded_images'] = [
				'id'      => 'group_uploaded_images',
				'name'    => esc_html__( 'Group Images?', 'gravity-pdf-images' ),
				'type'    => 'radio',
				'options' => [
					'Yes' => esc_html__( 'Yes', 'gravity-pdf-images' ),
					'No'  => esc_html__( 'No', 'gravity-pdf-images' ),
				],
				'std'     => 'No',
				'tooltip' => '<h6>' . esc_html__( 'Group Images', 'gravity-pdf-images' ) . '</h6>' . esc_html__( 'When enabled, any images in your upload fields are all grouped at the end of the PDF. This helps with the overall document readability and format.', 'gravity-pdf-images' ),
			];

			$settings['uploaded_images_js'] = [
				'id'    => 'uploaded_images_js',
				'type'  => 'hook',
				'class' => 'gfpdf-hidden',
			];
		}

		return $settings;
	}
}
