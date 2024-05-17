import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const { content, height } = attributes;
	const blockProps = useBlockProps.save( {
		style: { height: `${ height }px` },
	} );

	return (
		<div { ...blockProps }>
			<div className="wp-scale-section-content">
				<RichText.Content tagName="h2" value={ content } />
			</div>
		</div>
	);
}
