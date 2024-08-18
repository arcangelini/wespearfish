import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import ServerSideRender from "@wordpress/server-side-render";
import { PanelBody, RadioControl } from "@wordpress/components";
import "./editor.scss";
import React from "react";

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();
	const { time } = attributes;

	return (
		<>
			<div {...blockProps}>
				<ServerSideRender block="wespearfish/weather" attributes={{time: time}}/>
			</div>
			<InspectorControls>
				<PanelBody title="Data" initialOpen={true}>
					<RadioControl
						label="Weather type"
						selected={time}
						options={[
							{ label: "Current", value: "current" },
							{ label: "Forecast", value: "forecast" },
						]}
						onChange={(value) => setAttributes({ time: value })}
					/>
				</PanelBody>
			</InspectorControls>
		</>
	);
}
