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
 * Class FormData
 *
 * @package GFPDF\Plugins\Images\Options
 */
class FormData implements Helper_Interface_Filters {

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
		add_filter( 'gfpdf_form_data', [ $this, 'add_image_order_key' ] );
	}

	/**
	 * Put the `images` key at the end of the form_data array
	 *
	 * @param array $form_data
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function add_image_order_key( $form_data ) {
		if ( isset( $form_data['images'] ) ) {
			$images = $form_data['images'];
			unset( $form_data['images'] );

			$form_data['images'] = $images;
		}

		return $form_data;
	}
}