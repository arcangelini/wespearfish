import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { PanelBody } from "@wordpress/components";
import { useWeatherData } from "./utils";
import { Waves, Wind } from "./icons";
import "./editor.scss";
import React from "react";

const queryClient = new QueryClient();

function TheEdit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();
	const { windData, waveData } = useWeatherData();
	const { data: windDataData, isLoading: windDataIsLoading } = windData;
	const { data: waveDataData, isLoading: waveDataIsLoading } = waveData;

	return (
		<>
			<div {...blockProps}>
				<div className="wp-weather-content">
					<div className="weather-data heading">
						<h2>Current Weather Conditions</h2>
					</div>
					<div className="weather-data wind">
						<Wind />
						{windDataIsLoading ? (
							<h2>Loading...</h2>
						) : (
							<div className="data">
								<p>Speed <span className="meters">{windDataData?.windspeed}</span></p>
								<p>Temperature <span className="degrees">{windDataData?.temperature}</span></p>
								<p>Direction <span className="degrees">{windDataData?.winddir}</span></p>
							</div>
						)}
					</div>
					<div className="weather-data water">
						<Waves />
						{waveDataIsLoading ? (
							<h2>Loading...</h2>
						) : (
							<div className="data">
								<p>Height <span className="meters">{waveDataData?.hm0}</span></p>
								<p>Temperature <span className="degrees">{waveDataData?.watertemp}</span></p>
								<p>Direction <span className="degrees">{waveDataData?.meandir}</span></p>
							</div>
						)}
					</div>
				</div>
			</div>
			<InspectorControls>
				<PanelBody title='Block Settings' initialOpen={true}></PanelBody>
			</InspectorControls>
		</>
	);
}

export default function Edit({ attributes, setAttributes }) {
	return (
		<QueryClientProvider client={queryClient}>
			<TheEdit attributes={attributes} setAttributes={setAttributes} />
		</QueryClientProvider>
	);
}
