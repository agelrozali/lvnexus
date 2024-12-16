import { useEffect, useState } from '@wordpress/element';
import { CheckboxControl } from '@woocommerce/blocks-checkout';
import { getSetting } from '@woocommerce/settings';
import './styles.css';

const { optInText, optInEnabled, optInPreselected } = getSetting( 'omnisend_consent_data', '' );

const Block = ( { checkoutExtensionData } ) => {
	const [ checked, setChecked ] = useState( optInPreselected );
	const { setExtensionData } = checkoutExtensionData;

	useEffect( () => {
		setExtensionData( 'omnisend_consent', 'optin', checked );
	}, [
		checked,
		setExtensionData,
	] );

	if (!optInEnabled) {
		return null;
	}

	return (
		<div id="omnisend-subscribe-block">
			<CheckboxControl
				id="subscribe-to-newsletter"
				checked={ checked }
				onChange={ setChecked }
			>
				{ optInText }
			</CheckboxControl>
		</div>
	);
};

export default Block;
