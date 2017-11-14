<?php

namespace GFPDF\Plugins\Images\Options;

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
 * Class GlobalSettings
 *
 * @package GFPDF\Plugins\Images\Options
 */
class GlobalSettings implements Helper_Interface_Filters {


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
		add_filter( 'gfpdf_settings_extensions', [ $this, 'add_global_settings' ] );
	}

	/**
	 * Add global extension settings
	 *
	 * @param array $settings
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function add_global_settings( $settings ) {

		$settings['uploaded_images_desc'] = [
			'id'    => 'uploaded_images_desc',
			'type'  => 'descriptive_text',
			'desc'  => '<h4 class="section-title">' . esc_html__( 'Images', 'gravity-forms-images' ) . '</h4>',
			'class' => 'gfpdf-no-padding',
		];

		$settings['uploaded_images_constrained_image_size'] = [
			'id'      => 'uploaded_images_constrained_image_size',
			'name'    => esc_html__( 'Constrained Image Size', 'gravity-pdf-images' ),
			'desc'    => esc_html__( 'Changing the size only effects newly-uploaded images.', 'gravity-pdf-images' ),
			'desc2'   => 'px',
			'type'    => 'number',
			'size'    => 'small',
			'std'     => '1000',
			'tooltip' => '<h6>' . esc_html__( 'Constrained Image Size', 'gravity-forms-images' ) . '</h6>' . esc_html__( 'Uploaded images will be resized and have the width and height constrained. For better image quality increase the value, and for smaller file sizes decrease the value.', 'gravity-forms-images' ),
		];

		return $settings;
	}

}
