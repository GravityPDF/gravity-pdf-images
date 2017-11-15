<?php

namespace GFPDF\Plugins\Images\Features;

use GFPDF\Helper\Helper_Field_Container;
use GFPDF\Plugins\Images\Shared\ImageInfo;
use GFPDF\Helper\Helper_Interface_Actions;
use GFPDF\Helper\Helper_Interface_Filters;

use GPDFAPI;

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
 * Class DisplayImages
 *
 * @package GFPDF\Plugins\Images\Features
 */
class DisplayImages implements Helper_Interface_Actions, Helper_Interface_Filters {

	/**
	 * @var ImageInfo
	 *
	 * @since 0.1
	 */
	protected $image_info;

	/**
	 * @var array The current PDF Settings
	 *
	 * @since 0.1
	 */
	private $settings;

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
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * @since 0.1
	 */
	public function add_actions() {
		add_action( 'gfpdf_pre_pdf_generation', [ $this, 'save_settings' ], 10, 3 );
		add_action( 'gfpdf_post_pdf_generation', [ $this, 'reset_settings' ], 10 );

		add_action( 'gfpdf_post_html_fields', [ $this, 'maybe_group_images' ], 5, 2 );
	}

	/**
	 * @since 0.1
	 */
	public function add_filters() {
		add_filter( 'gfpdf_field_class', [ $this, 'maybe_autoload_class' ], 10, 3 );
	}

	/**
	 * Save the PDF Settings for later use
	 *
	 * @param array $form
	 * @param array $entry
	 * @param array $settings
	 *
	 * @since 0.1
	 */
	public function save_settings( $form, $entry, $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Get the current saved PDF settings
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Remove the current saved PDF Settings
	 *
	 * @since 0.1
	 */
	public function reset_settings() {
		$this->settings = null;
	}

	/**
	 * If the Image Display setting is enabled, override the `fileupload` and `post_image` field types with our own
	 * custom class.
	 *
	 * @param Helper_Abstract_Fields $class
	 * @param object                 $field
	 * @param array                  $entry
	 *
	 * @return Helper_Abstract_Fields
	 *
	 * @since 0.1
	 */
	public function maybe_autoload_class( $class, $field, $entry ) {

		/* Ensure the settings have been set and we aren't too early in the process */
		if ( isset( $this->settings['display_uploaded_images'] ) && $this->settings['display_uploaded_images'] === 'Yes' ) {
			if ( $field->get_input_type() === 'fileupload' ) {
				$field_class = 'GFPDF\Plugins\Images\Fields\ImageUploads';
			}

			if ( $field->get_input_type() === 'post_image' ) {
				$field_class = 'GFPDF\Plugins\Images\Fields\PostImageUploads';
			}

			if ( isset( $field_class ) ) {
				$image_class = new $field_class( $field, $entry, GPDFAPI::get_form_class(), GPDFAPI::get_misc_class() );
				$image_class->set_image_helper( $this->image_info );
				$image_class->set_pdf_settings( $this->settings );

				return $image_class;
			}
		}

		return $class;
	}

	/**
	 * Group `fileupload` and `post_image` images at the end of the PDF if the Group Images setting is enabled
	 *
	 * @param $entry
	 *
	 * @since 0.1
	 */
	public function maybe_group_images( $entry ) {
		$should_group_images = ( isset( $this->settings['group_uploaded_images'] ) ) ? $this->settings['group_uploaded_images'] : 'No';

		if ( $should_group_images === 'Yes' ) {
			$gform     = GPDFAPI::get_form_class();
			$container = new Helper_Field_Container();
			$form      = $gform->get_form( $entry['form_id'] );

			foreach ( $form['fields'] as $field ) {
				if ( $field->get_input_type() === 'fileupload' || $field->get_input_type() === 'post_image' ) {
					$field->cssClass = ''; /* Disable CSS Ready classes because grouped images are outside the normal document flow */
					$image_class     = $this->maybe_autoload_class( null, $field, $entry );

					if ( $image_class->has_images() ) {
						$container->generate( $field );
						echo $image_class->group_html();
						$container->close();
					}
				}
			}
		}
	}
}