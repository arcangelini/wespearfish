import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';

import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	return (
		<div { ...useBlockProps() }>
			<div className="wp-scale-section-content">
				<RichText
					tagName="h2"
					placeholder={ __( 'Add your heading…', 'scale-section' ) }
					value={ attributes.content }
					onChange={ ( content ) => setAttributes( { content } ) }
				/>
			</div>
		</div>
	);
}
