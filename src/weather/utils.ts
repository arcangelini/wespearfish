import { useQuery } from "@tanstack/react-query";

// Get the current date in the format YYYYMMDD
const getTimestamps = () => {
	const now = new Date();
	const year = now.getUTCFullYear();
	const month = (now.getUTCMonth() + 1).toString().padStart(2, "0");
	const day = Number(now.getUTCDate().toString().padStart(2, "0"));

	return `${year}${month}${day - 1}@0000&to=${year}${month}${day + 1}@0000`;
};

type BuoyData = {
	utc: number;
	watertemp: number;
	hm0: number;
	hmax: number;
	tm02: number;
	tp: number;
	meandir: number;
	windspeed: number;
};

type WindData = {
	temperature: number;
	windspeed: number;
	windspeedmax: number;
	winddir: number;
	winddirmax: number;
}

const getLatestData = (data): BuoyData | WindData => {
	const keys = data.content[0];
	const length = data.content[1].length;
	const values = data.content[1][length - 1];

	const transformedData = values.reduce((acc, value, index) => {
		const theKey = keys[index].toLowerCase().split("(")[0];
		const theValue = Array.isArray(value) ? value[0] : value;
		acc[theKey] = theValue;
		return acc;
	}, {});

	return transformedData;
};

const getWindData = () => {
	return useQuery({
		queryKey: ["windData"],
		queryFn: async () => {
			const response = await fetch(
				`https://movil.puertos.es/cma2/app/CMA/adhoc/station_data?station=4753&params=WindSpeed,WindSpeedMax,WindDir,WindDirMax&from=${getTimestamps()}`,
			);
			const data = await response.json();
			return getLatestData(data) as WindData;
		},
	});
};

const getWaveData = () => {
	return useQuery({
		queryKey: ["waveData"],
		queryFn: async () => {
			const response = await fetch(
				`https://movil.puertos.es/cma2/app/CMA/adhoc/station_data?station=1731&params=WaterTemp,Hm0,Hmax,Tm02,Tp,MeanDir&from=${getTimestamps()}`,
			);
			const data = await response.json();
			return getLatestData(data) as BuoyData;
		},
	});
};

export const useWeatherData = () => {
	return { windData: getWindData(), waveData: getWaveData() };
};
