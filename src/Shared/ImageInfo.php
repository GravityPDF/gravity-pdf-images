<?php

namespace GFPDF\Plugins\Images\Shared;

use GFFormsModel;

/**
 * @package     Gravity PDF Images
 * @copyright   Copyright (c) 2017, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
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
 * Class ImageInfo
 *
 * @package GFPDF\Plugins\Images\Shared
 */
class ImageInfo {

	/**
	 * Get the image resized filepath
	 *
	 * @Internal All resized images include an md5 hash that's shortened 6 characters
	 *
	 * @param string $image Can be an absolute path, URL or file stream (i.e vfs://path/to/image.jpg)
	 *
	 * @return string
	 *
	 * @since    1.0
	 */
	public function get_image_resized_filepath( $image ) {

		$file_info = pathinfo( $image );
		$dirname   = $file_info['dirname'];

		/* Calculate the resized image hash */
		$hash = substr( md5( $file_info['filename'] ), 0, 6 );

		/* Correctly handle stream dirnames when building the resized image filepath */
		$virtual_fs = substr( $image, 0, strlen( $file_info['dirname'] ) + 2 ) === substr( $file_info['dirname'], 0, -1 ) . '://';
		$dirname    .= ( $virtual_fs ) ? '//' : '/';

		/* Return the resized image filepath */
		return $dirname . $file_info['filename'] . '-resized-' . $hash . '.' . $file_info['extension'];
	}

	/**
	 * Return the current image name
	 *
	 * @param $image Can be an absolute path, URL or file stream (i.e vfs://path/to/image.jpg)
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function get_image_name( $image ) {
		return pathinfo( $image, PATHINFO_BASENAME );
	}

	/**
	 * Check if the file contains an aloud image extension
	 *
	 * @param string $file Can be an absolute path, URL or file stream (i.e vfs://path/to/image.jpg)
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function does_file_have_image_extension( $file ) {
		$allowed_extensions = [ 'jpg', 'jpeg', 'gif', 'png' ];

		if ( in_array( strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ), $allowed_extensions ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns what should be the file path
	 *
	 * @param $url The file URL
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function get_file_path( $url ) {
		return GFFormsModel::get_physical_file_path( $url );
	}
}