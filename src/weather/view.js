/**
 * On DOM Ready
 * @param {Object} options
 */

document.addEventListener( 'DOMContentLoaded', () => {
	const forecastDays = document.querySelectorAll( '.forecast-day' );

	forecastDays.forEach( ( day ) => {
		// Add the toggle class to the forecast-day element
		day.addEventListener( 'click', () => day.classList.toggle( 'open' ) );

		// Sort the hours by data-rating
		const hours = day.querySelectorAll( '.forecast-hour' );
		const hoursArray = Array.from( hours );
		const sortedHours = hoursArray.sort( ( a, b ) => a.dataset.rating - b.dataset.rating );
		sortedHours[0].classList.add( 'best-hour' );
		sortedHours[1].classList.add( 'best-hour' );
		
		// Tag the best and worst wind and wave forecasts
		const wind = day.querySelectorAll( '.wind' );
		const windArray = Array.from( wind );
		windArray.map( ( wind ) => {
			if ( 3.9 >= wind.dataset.speed ) {
				wind.classList.add( 'good' );
			} else if ( 10 <= wind.dataset.speed ) {
				wind.classList.add( 'bad' );
			}
		} );

		const wave = day.querySelectorAll( '.wave' );
		const waveArray = Array.from( wave );
		waveArray.map( ( wave ) => {
			if ( .3 >= wave.dataset.height ) {
				wave.classList.add( 'good' );
			} else if ( 1 <= wave.dataset.height ) {
				wave.classList.add( 'bad' );
			}
		} );
	} );

});
