<?php
/**
 * Essence Pro.
 *
 * Onboarding config to load plugins and homepage content on theme activation.
 *
 * @package Essence Pro
 * @author  StudioPress
 * @license GPL-2.0-or-later
 * @link    https://my.studiopress.com/themes/essence/
 */

$essence_onboarding_config = [
	'dependencies'     => [
		'plugins' => [
			[
				'name'       => 'Roots of Plenty CMS Config',
				'slug'       => 'roots-config/roots-config.php',
				'public_url' => null,
			],
			[
				'name'       => 'Advanced Custom Fields',
				'slug'       => 'advanced-custom-fields-pro/acf.php',
				'public_url' => 'https://www.advancedcustomfields.com',
			],
			[
				'name'       => 'Admin Columns Pro',
				'slug'       => 'admin-columns-pro/admin-columns-pro.php',
				'public_url' => 'https://www.admincolumns.com/',
			],
			[
				'name'       => 'Custom Upload Dir',
				'slug'       => 'custom-upload-dir/custom_upload_dir.php',
				'public_url' => 'https://wordpress.org/plugins/custom-upload-dir/',
			],
			[
				'name'       => 'Venobox Lightbox',
				'slug'       => 'venobox-lightbox/venobox.php',
				'public_url' => 'https://veno.es/venobox/',
			],
			[
				'name'       => __( 'WPForms Lite (Third Party)', 'essence-pro' ),
				'slug'       => 'wpforms-lite/wpforms.php',
				'public_url' => 'https://wordpress.org/plugins/wpforms-lite/',
			],
			[
				'name'       => __( 'Genesis Blocks', 'essence-pro' ),
				'slug'       => 'genesis-blocks/genesis-blocks.php',
				'public_url' => 'https://wordpress.org/plugins/genesis-blocks/',
			],
		],
	],
];

return $essence_onboarding_config;