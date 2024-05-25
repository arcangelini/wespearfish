import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody } from "@wordpress/components";

import { useWeatherData } from "./utils";
import { Waves, Wind, Temperature } from "./icons";
import "./editor.scss";
import React, { useEffect } from "react";

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();
	const { data, isLoading } = useWeatherData();

	return (
		<>
			<div {...blockProps}>
				<div className="wp-weather-content">
					<div className="weather-data wind">
						<Wind />
						<div className="wind-speed">
							<p>{__("Wind\nSpeed", "wespearfish")}</p>
							{isLoading ? (
								<p>{__("Loading...", "wespearfish")}</p>
							) : (
								<p>{data.wind.windspeed} m</p>
							)}
						</div>
					</div>
					<div className="weather-data waves">
						<Waves />
						<div className="wave-height">
							<p>{__("Wave\nHeight", "wespearfish")}</p>
							{isLoading ? (
								<p>{__("Loading...", "wespearfish")}</p>
							) : (
								<p>{data.buoy.hm0} m</p>
							)}
						</div>
					</div>
					<div className="weather-data temperature">
						<Temperature />
						<div className="water-temperature">
							<p>{__("Water\nTemperature", "wespearfish")}</p>
							{isLoading ? (
								<p>{__("Loading...", "wespearfish")}</p>
							) : (
								<p>{data.buoy.watertemp} m</p>
							)}
						</div>
					</div>
				</div>
			</div>
			<InspectorControls>
				<PanelBody
					title={__("Block Settings", "wespearfish")}
					initialOpen={true}
				></PanelBody>
			</InspectorControls>
		</>
	);
}
