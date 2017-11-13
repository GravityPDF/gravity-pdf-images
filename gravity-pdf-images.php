<?php

/**
 * Plugin Name:     Gravity PDF Images
 * Plugin URI:      https://gravitypdf.com/shop/images-add-on/
 * Description:     TODO
 * Author:          Gravity PDF
 * Author URI:      https://gravitypdf.com
 * Text Domain:     gravity-pdf-images
 * Domain Path:     /languages
 * Version:         0.1
 */

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

define( 'GFPDF_PDF_IMAGES_FILE', __FILE__ );
define( 'GFPDF_PDF_IMAGES_VERSION', '1.0.2' );

/**
 * Class GPDF_Images_Checks
 *
 * @since 0.1
 */
class GPDF_Images_Checks {

	/**
	 * Holds any blocker error messages stopping plugin running
	 *
	 * @var array
	 *
	 * @since 0.1
	 */
	private $notices = [];

	/**
	 * @var string
	 *
	 * @since 0.1
	 */
	private $required_gravitypdf_version = '5.0.0-beta1';

	/**
	 * Run our pre-checks and if it passes bootstrap the plugin
	 *
	 * @return void
	 *
	 * @since 0.1
	 */
	public function init() {

		/* Test the minimum version requirements are met */
		$this->check_gravitypdf_version();

		/* Check if any errors were thrown, enqueue them and exit early */
		if ( sizeof( $this->notices ) > 0 ) {
			add_action( 'admin_notices', [ $this, 'display_notices' ] );

			return null;
		}

		add_action( 'gfpdf_fully_loaded', function() {
			require_once __DIR__ . '/src/bootstrap.php';
        } );
	}

	/**
	 * Check if the current version of Gravity PDF is compatible with this add-on
	 *
	 * @return bool
	 *
	 * @since 0.1
	 */
	public function check_gravitypdf_version() {

		/* Check if the Gravity PDF Minimum version requirements are met */
		if ( defined( 'PDF_EXTENDED_VERSION' ) &&
		     version_compare( PDF_EXTENDED_VERSION, $this->required_gravitypdf_version, '>=' )
		) {
			return true;
		}

		/* Throw error */
		$this->notices[] = sprintf( esc_html__( 'Gravity PDF Version %s or higher is required to use this add-on. Please install/upgrade Gravity PDF to the latest version.', 'gravity-pdf-images' ), $this->required_gravitypdf_version );
	}

	/**
	 * Helper function to easily display error messages
	 *
	 * @return void
	 *
	 * @since 0.1
	 */
	public function display_notices() {
		?>
        <div class="error">
            <p>
                <strong><?php esc_html_e( 'Gravity PDF Images Installation Problem', 'gravity-pdf-images' ); ?></strong>
            </p>

            <p><?php esc_html_e( 'The minimum requirements for the Gravity PDF Images plugin have not been met. Please fix the issue(s) below to continue:', 'gravity-pdf-images' ); ?></p>
            <ul style="padding-bottom: 0.5em">
				<?php foreach ( $this->notices as $notice ) : ?>
                    <li style="padding-left: 20px;list-style: inside"><?php echo $notice; ?></li>
				<?php endforeach; ?>
            </ul>
        </div>
		<?php
	}
}

/* Initialise the software */
add_action( 'plugins_loaded', function() {
	$gravitypdf_images = new GPDF_Images_Checks();
	$gravitypdf_images->init();
} );