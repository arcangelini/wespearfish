import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, RangeControl } from '@wordpress/components';

import './editor.scss';
import React from 'react';

export default function Edit( { attributes, setAttributes } ) {
	const { height, content } = attributes;
	const blockProps = useBlockProps( {
		style: { height: `${ height }px` },
	} );

	return (
		<>
			<div { ...blockProps }>
				<div className="wp-scale-section-content">
					<RichText
						tagName="h2"
						placeholder={ __( 'Add your heading…', 'wespearfish' ) }
						value={ content }
						onChange={ ( newContent ) =>
							setAttributes( { content: newContent } )
						}
					/>
				</div>
			</div>
			<InspectorControls>
				<PanelBody
					title={ __( 'Block Settings', 'wespearfish' ) }
					initialOpen={ true }
				>
					<RangeControl
						label={ __( 'Height', 'wespearfish' ) }
						value={ height }
						onChange={ ( newHeight ) =>
							setAttributes( { height: newHeight } )
						}
						min={ 100 }
						max={ 500 }
					/>
				</PanelBody>
			</InspectorControls>
		</>
	);
}
