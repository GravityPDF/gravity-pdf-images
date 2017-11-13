<?php

namespace GFPDF\Plugins\Images\Styles;

use GFPDF\Helper\Helper_Interface_Actions;

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
 * Class AddStyles
 *
 * @package GFPDF\Plugins\EnhancedOptions\Style
 */
class AddStyles implements Helper_Interface_Actions {

	/**
	 * Initialise our module
	 *
	 * @since 0.1
	 */
	public function init() {
		$this->add_actions();
	}

	/**
	 * @since 0.1
	 */
	public function add_actions() {
		add_action( 'gfpdf_core_template', [ $this, 'add_styles' ] );
	}

	/**
	 * Include global CSS styles for Gravity PDF templates
	 *
	 * @since 0.1
	 */
	public function add_styles() {
		echo '<style>' .
		     file_get_contents( __DIR__ . '/enhanced-images-pdf-styles.css' ) .
		     '</style>';
	}
}