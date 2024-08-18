<?php
/**
 * Converts a degree to a direction.
 * @param int $degree The degree to convert.
 * @return string The direction.
 */
function degree_to_direction($degree) {
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

function fetch_current_weather_data() {
	$station_url  = 'https://movil.puertos.es/cma2/app/CMA/adhoc/station_data?station=';

	$urls = [
		'barcelona_buoy' => $station_url . '1731&params=WaterTemp,Hm0,Hmax,MeanDir180,MeanDir',
		'barcelona_port' => $station_url . '4752&params=AirTemp,WindSpeed,WindSpeedMax,WindDir180,WindDir',
	];

	$mh       = curl_multi_init();
	$requests = [];

	foreach ( $urls as $location => $url ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		$requests[$location] = $ch;
		curl_multi_add_handle( $mh, $ch );
	}

	$active = null;
	do {
		$status = curl_multi_exec( $mh, $active );
		if ( $active ) {
			curl_multi_select( $mh );
		}
	} while ( $active && $status == CURLM_OK );

	$responses = [];
	foreach ( $requests as $location => $ch ) {
		if ( curl_errno( $ch ) ) {
			error_log( 'Spearfishing Weather cUrl error: ' . $location . ' - ' . curl_error( $ch ) );
		} else {
			$responses[$location] = json_decode( curl_multi_getcontent( $ch ), true );
		}

		curl_multi_remove_handle( $mh, $ch );
		curl_close( $ch );
	}
	
	curl_multi_close( $mh );

	$current_weather_data = [];
	foreach ( $responses as $location => $response ) {
		$latest_data = end( $response['content'][1] );

		switch ( $location ) {
			case 'barcelona_buoy' :
				$current_weather_data['wave_temperature']           = round( $latest_data[1][0], 1 );
				$current_weather_data['wave_height']                = round( $latest_data[2][0], 1 );
				$current_weather_data['wave_height_max']            = round( $latest_data[3][0], 1 );
				$current_weather_data['wave_direction_to_degree']   = $latest_data[4][0] ?? null;
				$current_weather_data['wave_direction_from_degree'] = $latest_data[5][0] ?? null;
				$current_weather_data['wave_direction']             = degree_to_direction( $latest_data[5][0] ?? null );
				break;

			case 'barcelona_port' :
				$current_weather_data['wind_temperature']           = round( $latest_data[1][0], 1  );
				$current_weather_data['wind_speed']                 = round( $latest_data[2][0] * 2.237, 1 ); // M/S converted to MPH
				$current_weather_data['wind_speed_max']             = round( $latest_data[3][0] * 2.237, 1 ); // M/S converted to MPH
				$current_weather_data['wind_direction_to_degree']   = $latest_data[4][0] ?? null;
				$current_weather_data['wind_direction_from_degree'] = $latest_data[5][0] ?? null;
				$current_weather_data['wind_direction']             = degree_to_direction( $latest_data[5][0] ?? null );
				break;
		}
	}

	return $current_weather_data;
}

/**
 * Gets the current weather data and caches it for 10 minutes.
 * @return array The current weather data.
 */
function get_current_weather_data() {
	$cache_key       = 'spearfishing_current_weather_data';
	$cached_response = wp_cache_get( $cache_key );

	if ( $cached_response ) {
		$current_weather_data = fetch_current_weather_data();
		wp_cache_set( $cache_key, $current_weather_data, '', 600 );

		return $current_weather_data;
	}

	return $cached_response;
}

function fetch_forecast_weather_data() {
	$forecast_url = 'https://movil.puertos.es/simo/item/';
	$urls = [
		'wave'     => $forecast_url . 'Siwana/712018014/forecast_view?fields=Datetime,Hm0,MeanDir180,MeanDir',
		'wind'     => $forecast_url . 'Atmosfera/712018014/forecast_view?fields=Datetime,WindSpeed,WindDir180,WindDir',
	];

	$mh       = curl_multi_init();
	$requests = [];

	foreach ( $urls as $type => $url ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		$requests[$type] = $ch;
		curl_multi_add_handle( $mh, $ch );
	}

	$active = null;
	do {
		$status = curl_multi_exec( $mh, $active );
		if ( $active ) {
			curl_multi_select( $mh );
		}
	} while ( $active && $status == CURLM_OK );

	$responses = [];
	foreach ( $requests as $type => $ch ) {
		if ( curl_errno( $ch ) ) {
			error_log( 'Spearfishing Weather cUrl error: ' . $type . ' - ' . curl_error( $ch ) );
		} else {
			$responses[$type] = json_decode( curl_multi_getcontent( $ch ), true );
		}

		curl_multi_remove_handle( $mh, $ch );
		curl_close( $ch );
	}
	
	curl_multi_close( $mh );

	$forecast_weather_data = [];
	$barcelona_timezone    = new DateTimeZone( 'Europe/Madrid' );
	foreach ( $responses as $type => $response ) {
		foreach ( $response['content'] as $data ) {
			$timestamp  = $data[0];
			$epoch_time = new DateTime( "@$timestamp", $barcelona_timezone );
			$hour 	    = $epoch_time->format('H');
			$day        = $epoch_time->format('j-n');

			// Only get data for 6am to 9pm
			if ( $hour < 7 || $hour > 20 ) {
				continue;
			}

			if ( ! isset( $forecast_weather_data[$day][$hour] ) ) {
				$forecast_weather_data[$day][$hour] = [];
			}

			switch ( $type ) {
				case 'wind':
					$forecast_weather_data[$day][$hour]['wind_speed']                 = number_format( round( $data[1] * 2.237, 1), 1, '.', ''); // M/S converted to MPH
					$forecast_weather_data[$day][$hour]['wind_direction_to_degree']   = $data[2];
					$forecast_weather_data[$day][$hour]['wind_direction_from_degree'] = $data[3];
					break;
	
				case 'wave':
					$forecast_weather_data[$day][$hour]['wave_height']                = number_format( round( $data[1], 1 ), 1, '.', '');
					$forecast_weather_data[$day][$hour]['wave_direction_to_degree']   = $data[2];
					$forecast_weather_data[$day][$hour]['wave_direction_from_degree'] = $data[3];
					break;
			}
		}
	}

	$filtered_forecast_weather_data = calculate_hour_rating( $forecast_weather_data );

	return $filtered_forecast_weather_data;
}

function calculate_hour_rating( $data ) {
    return array_map(
        fn( $hours ) => array_map(
            fn( $hour_data ) => array_merge( $hour_data, ['rating' => $hour_data['wind_speed'] + $hour_data['wave_height'] * 10]),
            $hours
        ),
        $data
    );
}

/**
 * Gets the forecast weather data and caches it for one hour.
 * @return array The forecast weather data.
 */
function get_forecast_weather_data() {
	$cache_key       = 'spearfishing_forecast_weather_data';
	$cached_response = wp_cache_get( $cache_key );

	if ( ! $cached_response ) {
		$forecast_weather_data = fetch_forecast_weather_data();
		wp_cache_set( $cache_key, $forecast_weather_data, '', MINUTE_IN_SECONDS * 30 );

		return $forecast_weather_data;
	}

	return $cached_response;
}