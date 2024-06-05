<?php
/**
 * Plugin Name: Spearfishing Stuff
 * Plugin URI: https://wespearfish.com
 * Description: All the stuff needed to make We Spear Fish happen.
 * Version: 1.0
 * Author: arcangelinis
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 */
function spearfishing_stuff_register_blocks() {
	require_once __DIR__ . '/weather-block-render.php';

	/* Weather Block */
	register_block_type( __DIR__ . '/build/weather', array(
		'render_callback' => 'render_weather_block',
	) );

	/* Scale Section Block */
	register_block_type( __DIR__ . '/build/scale-section' );
}

add_action( 'init', 'spearfishing_stuff_register_blocks' );
