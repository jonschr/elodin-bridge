( function () {
	'use strict';

	const featureToggles = document.querySelectorAll( '.elodin-bridge-admin__feature-toggle' );
	featureToggles.forEach( ( toggle ) => {
		const syncFeatureState = () => {
			const feature = toggle.closest( '.elodin-bridge-admin__feature' );
			if ( feature ) {
				feature.classList.toggle( 'is-enabled', !! toggle.checked );
			}
		};

		syncFeatureState();
		toggle.addEventListener( 'change', syncFeatureState );
	} );

	const template = document.getElementById( 'elodin-bridge-image-size-row-template' );
	if ( ! template ) {
		return;
	}

	const builders = document.querySelectorAll( '.elodin-bridge-admin__image-size-builder' );
	if ( ! builders.length ) {
		return;
	}

	builders.forEach( ( builder ) => {
		const tableBody = builder.querySelector( '.elodin-bridge-admin__custom-image-sizes' );
		const addButton = builder.querySelector( '.elodin-bridge-admin__add-image-size' );
		if ( ! tableBody || ! addButton ) {
			return;
		}

		let nextIndex = parseInt( builder.getAttribute( 'data-next-index' ) || '0', 10 );
		if ( Number.isNaN( nextIndex ) || nextIndex < 0 ) {
			nextIndex = 0;
		}

		addButton.addEventListener( 'click', () => {
			const html = template.innerHTML.split( '__INDEX__' ).join( String( nextIndex ) );
			nextIndex += 1;
			tableBody.insertAdjacentHTML( 'beforeend', html );
			builder.setAttribute( 'data-next-index', String( nextIndex ) );
		} );

		tableBody.addEventListener( 'click', ( event ) => {
			const target = event.target;
			if ( ! target || ! target.classList || ! target.classList.contains( 'elodin-bridge-admin__remove-image-size' ) ) {
				return;
			}

			const row = target.closest( '.elodin-bridge-admin__image-size-row' );
			if ( row ) {
				row.remove();
			}
		} );
	} );
} )();
