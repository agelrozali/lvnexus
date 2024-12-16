import { registerBlockType } from '@wordpress/blocks';
import { Icon, box } from '@wordpress/icons';

import { Edit } from './edit';
import metadata from './block.json';
registerBlockType(metadata, {
	icon: {
		src: <Icon icon={box} />,
	},
	edit: Edit,
});
