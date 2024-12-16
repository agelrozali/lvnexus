import {
	useBlockProps,
} from '@wordpress/block-editor';
import { CheckboxControl } from '@woocommerce/blocks-checkout';
import { getSetting } from '@woocommerce/settings';
import './styles.css';

const { optInText, optInEnabled, optInPreselected } = getSetting( 'omnisend_consent_data', '' );

export const Edit = () => {
	const blockProps = useBlockProps();

	if(!optInEnabled) {
		return <div></div>;
	}

	return (
		<div { ...blockProps } id="omnisend-subscribe-block">
				<CheckboxControl style={{ marginTop: 0, lineHeight: 'normal' }}  id="newsletter-text" checked={ optInPreselected } disabled={ true }>
				{optInText}
				</CheckboxControl>
		</div>
	);
};
