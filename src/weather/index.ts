
import { registerBlockType } from '@wordpress/blocks';

import './style.scss';

import Edit from './edit';
import metadata from './block.json';
import { BlockIcon } from './icons';

// @ts-expect-error - TS doesn't know about the block.json
registerBlockType( metadata.name, {
	icon: BlockIcon,
	/**
	 * @see ./edit.js
	 */
	edit: Edit,
} );
