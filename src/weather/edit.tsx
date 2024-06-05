import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import ServerSideRender from "@wordpress/server-side-render";
import { PanelBody } from "@wordpress/components";
import "./editor.scss";
import React from "react";

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();

	return (
		<>
			<div {...blockProps}>
				<ServerSideRender block="spearfishing-stuff/weather" />
			</div>
			<InspectorControls>
				<PanelBody title="Block Settings" initialOpen={true}></PanelBody>
				{/* Add settings in the future here */}
			</InspectorControls>
		</>
	);
}
