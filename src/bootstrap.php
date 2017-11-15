<?php

namespace GFPDF\Plugins\Images;

use GFPDF\Plugins\Images\Features\FormData;
use GFPDF\Plugins\Images\Features\PreviewerPlaceholders;
use GFPDF\Plugins\Images\Options\GlobalSettings;
use GFPDF\Plugins\Images\Shared\DoesTemplateHaveGroup;
use GFPDF\Plugins\Images\Shared\ImageInfo;
use GFPDF\Plugins\Images\Options\AddFields;
use GFPDF\Plugins\Images\ImageManipulation\Resize;
use GFPDF\Plugins\Images\Features\DisplayImages;
use GFPDF\Plugins\Images\Styles\AddStyles;

use GFPDF\Helper\Licensing\EDD_SL_Plugin_Updater;
use GFPDF\Helper\Helper_Abstract_Addon;
use GFPDF\Helper\Helper_Singleton;
use GFPDF\Helper\Helper_Logger;
use GFPDF\Helper\Helper_Notices;

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

/* Load Composer */
require_once( __DIR__ . '/../vendor/autoload.php' );

/**
 * Class Bootstrap
 *
 * @package GFPDF\Plugins\Images
 */
class Bootstrap extends Helper_Abstract_Addon {

	/**
	 * Initialise the plugin classes and pass them to our parent class to
	 * handle the rest of the bootstrapping (licensing ect)
	 *
	 * @param array $classes An array of classes to store in our singleton
	 *
	 * since 0.1
	 */
	public function init( $classes = [] ) {
		/* Create new intances of the plugin's classes */
		$options = GPDFAPI::get_options_class();
		$options->set_plugin_settings();

		$group_checker    = new DoesTemplateHaveGroup( GPDFAPI::get_mvc_class( 'Model_Form_Settings' ), GPDFAPI::get_templates_class(), $this->log );
		$image_constraint = $options->get_option( 'uploaded_images_constrained_image_size', 1000 );

		$classes = array_merge( $classes, [
			new AddFields( $group_checker ),
			new Resize( new ImageInfo(), $image_constraint ),
			new DisplayImages( new ImageInfo() ),
			new AddStyles(),
			new FormData(),
			new GlobalSettings(),
			new PreviewerPlaceholders( new ImageInfo() )
		] );

		/* Run the setup */
		parent::init( $classes );
	}

	/**
	 * Check the plugin's license is active and initialise the EDD Updater
	 *
	 * since 0.1
	 */
	public function plugin_updater() {

		/* Skip over this addon if license status isn't active */
		$license_info = $this->get_license_info();

		new EDD_SL_Plugin_Updater(
			$this->data->store_url,
			$this->get_main_plugin_file(),
			[
				'version'   => $this->get_version(),
				'license'   => $license_info['license'],
				'item_name' => $this->get_short_name(),
				'author'    => $this->get_author(),
				'beta'      => false,
			]
		);

		$this->log->notice( sprintf( '%s plugin updater initialised', $this->get_name() ) );
	}
}

/* Use the filter below to replace and extend our Bootstrap class if needed */
$name = 'Gravity PDF Images';
$slug = 'gravity-pdf-images';

$plugin = apply_filters( 'gfpdf_images_initialise', new Bootstrap(
	$slug,
	$name,
	'Gravity PDF',
	GFPDF_PDF_IMAGES_VERSION,
	GFPDF_PDF_IMAGES_FILE,
	GPDFAPI::get_data_class(),
	GPDFAPI::get_options_class(),
	new Helper_Singleton(),
	new Helper_Logger( $slug, $name ),
	new Helper_Notices()
) );

$plugin->set_edd_download_id( '' );
$plugin->set_addon_documentation_slug( 'shop-plugin-images-add-on' );
$plugin->init();

/* Use the action below to access our Bootstrap class, and any singletons saved in $plugin->singleton */
do_action( 'gfpdf_images_bootrapped', $plugin );