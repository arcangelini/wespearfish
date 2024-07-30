<?php
/**
 * Parses the latest data from the Puerto API.
 * Takes the keys and matches them with the values to create an associative array of the most recent data.
 * @param array $data The data to parse.
 * @return array The parsed data
 */

$DEFAULT_DATA = [
	'direction_degree' => 'north',
	'direction'        => 0,
	'speed'            => 0,
	'height'           => 0,
	'temperature'      => 0,
];

function parse_latest_data($data) {
	$keys = $data['content'][0];
	$length = count($data['content'][1]);
	$values = $data['content'][1][$length - 1];

	$transformed_data = array_reduce(array_keys($values), function($acc, $index) use ($keys, $values) {
		$the_key = strtolower(explode('(', $keys[$index])[0]);
		$the_value = is_array($values[$index]) ? $values[$index][0] : $values[$index];
		$acc[$the_key] = $the_value;
		return $acc;
	}, []);

	return $transformed_data;
}

/**
 * Gets the wind data from the Puerto API.
 * @return array The wind data.
 */
function get_wind_data() {
	$url = 'https://movil.puertos.es/cma2/app/CMA/adhoc/station_data?station=4752&params=AirTemp,WindSpeed,WindSpeedMax,WindDir,WindDirMax';
	$ch  = curl_init($url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
	]);

	$response = curl_exec($ch);

	// Check for errors
	if($response === false) {
		$error_message = curl_error($ch);
		curl_close($ch);

		error_log('Spearfishing Weather cUrl error: ' . $error_message);
		return $DEFAULT_DATA;
	}

	$data = json_decode($response, true);
	curl_close($ch);

	// Check for content
	if ( $data['status'] !== 0) {
		error_log('Spearfishing Weather: No content in response');
		return [
			'direction_degree' => 'north',
			'direction'        => 0,
			'speed'            => 0,
			'temperature'      => 0,
		];
	}

	[ 'winddir' => $winddir, 'windspeed' => $windspeed, 'windspeedmax' => $windspeedmax, 'airtemp' => $airtemp ] = parse_latest_data($data);

	return [
		'direction_degree'      => convert_from_degree( $winddir ),
		'direction_from_degree' => $winddir,
		'direction'             => convert_degree_to_direction( $winddir ),
		'speed'                 => round( $windspeed, 1 ),
		'speed_max'             => round( $windspeedmax, 1 ),
		'temperature'           => round( $airtemp, 1 ),
	];}

/**
 * Gets the water data from the Puerto API.
 * @return array The water data.
 */
function get_water_data() {
	$url = 'https://movil.puertos.es/cma2/app/CMA/adhoc/station_data?station=1731&params=WaterTemp,Hm0,Hmax,Tm02,Tp,MeanDir';
	$ch  = curl_init($url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
	]);

	$response = curl_exec($ch);

	// Check for errors
	if($response === false) {
		$error_message = curl_error($ch);
		curl_close($ch);

		error_log('Spearfishing Weather cUrl error: ' . $error_message);
		return $DEFAULT_DATA;
	}

	$data = json_decode($response, true);

	curl_close($ch);

	// Check for content
	if ( $data['status'] !== 0) {
		error_log('Spearfishing Weather: No content in response');
		return [
			'direction_degree' => 'north',
			'direction'        => 0,
			'speed'            => 0,
			'temperature'      => 0,
		];
	}

	[ 'meandir' => $meandir, 'hmax' => $hmax, 'hm0' => $hm0, 'watertemp' => $watertemp ] = parse_latest_data($data);

	return [
		'direction_degree'      => convert_from_degree( $meandir ),
		'direction_from_degree' => $meandir,
		'direction'             => convert_degree_to_direction( $meandir ),
		'height'                => round( $hm0, 1 ),
		'height_max'            => round( $hmax, 1 ),
		'temperature'           => round( $watertemp, 1 ),
	];
}

/**
 * Converts a degree to a direction.
 * @param int $degree The degree to convert.
 * @return string The direction.
 */
function convert_degree_to_direction($degree) {
	$directions = [
		"north",
		"nne",
		"northeast",
		"ene",
		"east",
		"ese",
		"southeast",
		"sse",
		"south",
		"ssw",
		"southwest",
		"wsw",
		"west",
		"wnw",
		"northwest",
		"nnw"
	];

	$index = round($degree / 22.5) % 16;
	return $directions[$index];
}

/**
 * Converts a directional degree coming FROM to directional degree going TO.
 * @param int $degree The degree to convert.
 * @return int The inverted degree.
 */
function convert_from_degree($degree) {
	return ($degree + 180) % 360;
}

/**
 * Renders the weather block.
 * @return string The rendered block.
 */
function render_weather_block( $block_attributes, $content ) {
	require_once __DIR__ . '/weather-api.php';

	$weather_data = 'forecast' === $block_attributes['time'] ? get_forecast_weather_data() : get_current_weather_data();

	if ( $block_attributes['time'] === 'forecast' ) {
		$output = '';

		if ( count( $weather_data ) === 4  ) {
			$first_day = array_shift( $weather_data );			
		}

		foreach ( $weather_data as $day => $data ) {
			$output .= '<div class="forecast-day"><h2>' . htmlspecialchars($day) . '</h2><div class="forecast-data">';
		
			$day = array_reduce(array_keys($data), function($carry, $key) use ($data) {
				$time = $data[$key];

				$carry .= <<<HTML
					<div class="forecast-hour" data-rating="{$time['rating']}">
						<strong>{$key}</strong>
						<div class='wind' data-speed="{$time['wind_speed']}" >
							<p>{$time['wind_speed']}<span class="unit">mph</span></p>
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="25" height="25" style="transform: rotate({$time['wind_direction_to_degree']}deg);">
								<path d="M214.6 41.4c-12.5-12.5-32.8-12.5-45.3 0l-160 160c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 141.2V448c0 17.7 14.3 32 32 32s32-14.3 32-32V141.2L329.4 246.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-160-160z"/>
							</svg>
						</div>
						<div class='wave' data-height="{$time['wave_height']}">
							<p>{$time['wave_height']}<span class="unit">m</span></p>
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="25" height="25" style="transform: rotate({$time['wave_direction_to_degree']}deg);">
								<path d="M214.6 41.4c-12.5-12.5-32.8-12.5-45.3 0l-160 160c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 141.2V448c0 17.7 14.3 32 32 32s32-14.3 32-32V141.2L329.4 246.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-160-160z"/>
							</svg>
						</div>
					</div>
					HTML;
				return $carry;
			}, '');
		
			$output .= $day . '</div></div>';
		}

		return <<<HTML
			<div class='wp-block-spearfishing-stuff-weather'>
				<div class='wp-spearfishing-weather-content forecast'>
					$output
				</div>
			</div>
			HTML;
	}

	if ( $block_attributes['time'] === 'current' ) {
		return <<<HTML
			<div tabindex='0' class='block-editor-block-list__block wp-block is-selected wp-block-spearfishing-stuff-weather' id='block-c1c77b42-4262-4198-b235-3c79e3b8ae38' role='document' aria-label='Block: Fishing Weather' data-block='c1c77b42-4262-4198-b235-3c79e3b8ae38' data-type='spearfishing-stuff/weather' data-title='Fishing Weather'>
				<div class='wp-spearfishing-weather-content current'>
					<div class='weather-data wind'>
						<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' width='45' height='45' aria-hidden='true' focusable='false' style="transform: rotate({$weather_data['wind_direction_to_degree']}deg);">
							<path d='M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM385 231c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-71-71V376c0 13.3-10.7 24-24 24s-24-10.7-24-24V193.9l-71 71c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9L239 119c9.4-9.4 24.6-9.4 33.9 0L385 231z'></path>
						</svg>
						<p>{$weather_data['wind_direction']}</p>
						<h3>Wind</h3>
						<p class='speed'>{$weather_data['wind_speed']} | {$weather_data['wind_speed_max']}<span class="unit">mph</span></p>
						<p class='temp'>{$weather_data['wind_temperature']}°<span class="unit">c</span></p>
						
					</div>
					<div class='weather-data wave'>
						<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' width='45' height='45' aria-hidden='true' focusable='false' style="transform: rotate({$weather_data['wave_direction_to_degree']}deg);">
							<path d='M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM385 231c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-71-71V376c0 13.3-10.7 24-24 24s-24-10.7-24-24V193.9l-71 71c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9L239 119c9.4-9.4 24.6-9.4 33.9 0L385 231z'></path>
						</svg>
						<p>{$weather_data['wave_direction']}</p>
						<h3>Waves</h3>
						<p class='height'>{$weather_data['wave_height']} | {$weather_data['wave_height_max']}<span class="unit">m</span></p>
						<p class='temp'>{$weather_data['wave_temperature']}°<span class="unit">c</span></p>
					</div>
				</div>
			</div>
			HTML;
	}

}
