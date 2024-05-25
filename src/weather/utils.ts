import { useState, useEffect } from "react";

// Get the current UTC date in the format YYYYMMDD@HH00
export const getCurrentDate = () => {
	const date = new Date();
	const year = date.getUTCFullYear();
	const month = date.getUTCMonth() + 1;
	const day = date.getUTCDate();
	const hour = date.getUTCHours();

	return "20240520@1000";
	return `${year}${month.toString().padStart(2, "0")}${day
		.toString()
		.padStart(2, "0")}@${hour.toString().padStart(2, "0")}00`;
};

type BuoyData = {
	utc: number;
	watertemp: number;
	hm0: number;
	hmax: number;
	tm02: number;
	tp: number;
	meandir: number;
};

const getBuoyData = (data): BuoyData => {
	const keys = data.content[0];
	const values = data.content[1][0];

	const transformedData = values.reduce((acc, value, index) => {
		const theKey = keys[index].toLowerCase().split("(")[0];
		const theValue = Array.isArray(value) ? value[0] : value;
		acc[theKey] = theValue;
		return acc;
	}, {});

	return transformedData;
};

export const useWeatherData = () => {
	const [data, setData] = useState<any>();
	const [isLoading, setIsLoading] = useState(true);
	const [error, setError] = useState();

	const fetchData = async () => {
		try {
			// Get the water data
			const buoyResponse = await fetch(
				`https://movil.puertos.es/cma2/app/CMA/adhoc/station_data?station=1731&params=WaterTemp,Hm0,Hmax,Tm02,Tp,MeanDir&from=${getCurrentDate()}`,
			);
			const buoyData = await buoyResponse.json();

			// Get the wind data
			const windResponse = await fetch(
				`https://movil.puertos.es/cma2/app/CMA/adhoc/station_data?station=4753&params=WindSpeed,WindSpeedMax,WindDir,WindDirMax&from=${getCurrentDate()}`,
			);
			const windData = await windResponse.json();

			setData({ buoy: getBuoyData(buoyData), wind: getBuoyData(windData)});
			setIsLoading(false);
		} catch (error) {
			setError(error);
		}
	};

	useEffect(() => {
		fetchData();
	}, []);

	return { data, isLoading, error };
};
