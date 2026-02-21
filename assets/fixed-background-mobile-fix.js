( function () {
	'use strict';

	const config = window.elodinBridgeMobileFixedBackgroundRepair || {};
	const mobileQuery = ( typeof config.mobileQuery === 'string' && config.mobileQuery.trim() )
		? config.mobileQuery
		: '(max-width: 768px)';
	const mediaQueryList = window.matchMedia( mobileQuery );

	const PATCHED_ATTR = 'data-elodin-bridge-mobile-fixed-bg-patched';
	const ORIGINAL_VALUE_ATTR = 'data-elodin-bridge-mobile-fixed-bg-original';
	const ORIGINAL_PRIORITY_ATTR = 'data-elodin-bridge-mobile-fixed-bg-priority';
	const EMPTY_VALUE = '__elodin_bridge_empty__';
	const scheduleFrame = typeof window.requestAnimationFrame === 'function'
		? window.requestAnimationFrame.bind( window )
		: ( callback ) => window.setTimeout( callback, 16 );
	let queued = false;

	const isMobileView = () => mediaQueryList.matches;

	const hasFixedAttachment = ( value ) => {
		if ( ! value ) {
			return false;
		}

		return String( value )
			.split( ',' )
			.some( ( part ) => part.trim() === 'fixed' );
	};

	const rememberOriginalInlineAttachment = ( element ) => {
		if ( element.hasAttribute( ORIGINAL_VALUE_ATTR ) ) {
			return;
		}

		const inlineValue = element.style.getPropertyValue( 'background-attachment' );
		const inlinePriority = element.style.getPropertyPriority( 'background-attachment' );
		element.setAttribute( ORIGINAL_VALUE_ATTR, inlineValue === '' ? EMPTY_VALUE : inlineValue );
		element.setAttribute( ORIGINAL_PRIORITY_ATTR, inlinePriority === '' ? EMPTY_VALUE : inlinePriority );
	};

	const patchElement = ( element ) => {
		if ( ! ( element instanceof HTMLElement ) || element.hasAttribute( PATCHED_ATTR ) ) {
			return;
		}

		const computed = window.getComputedStyle( element );
		if ( ! computed || computed.backgroundImage === 'none' || ! hasFixedAttachment( computed.backgroundAttachment ) ) {
			return;
		}

		rememberOriginalInlineAttachment( element );
		element.style.setProperty( 'background-attachment', 'scroll', 'important' );
		element.setAttribute( PATCHED_ATTR, '1' );
	};

	const restorePatchedElements = () => {
		const patchedElements = document.querySelectorAll( `[${ PATCHED_ATTR }]` );
		patchedElements.forEach( ( element ) => {
			if ( ! ( element instanceof HTMLElement ) ) {
				return;
			}

			const inlineValue = element.getAttribute( ORIGINAL_VALUE_ATTR );
			const inlinePriority = element.getAttribute( ORIGINAL_PRIORITY_ATTR );

			if ( inlineValue === null || inlineValue === EMPTY_VALUE ) {
				element.style.removeProperty( 'background-attachment' );
			} else {
				element.style.setProperty(
					'background-attachment',
					inlineValue,
					inlinePriority && inlinePriority !== EMPTY_VALUE ? inlinePriority : ''
				);
			}

			element.removeAttribute( PATCHED_ATTR );
			element.removeAttribute( ORIGINAL_VALUE_ATTR );
			element.removeAttribute( ORIGINAL_PRIORITY_ATTR );
		} );
	};

	const patchAllCandidates = () => {
		// Only real elements are patched; pseudo-elements are intentionally excluded.
		if ( document.documentElement instanceof HTMLElement ) {
			patchElement( document.documentElement );
		}
		if ( document.body instanceof HTMLElement ) {
			patchElement( document.body );
		}

		const allElements = document.querySelectorAll( '*' );
		allElements.forEach( ( element ) => patchElement( element ) );
	};

	const applyRepairPass = () => {
		queued = false;

		if ( ! isMobileView() ) {
			restorePatchedElements();
			return;
		}

		patchAllCandidates();
	};

	const queueRepairPass = () => {
		if ( queued ) {
			return;
		}

		queued = true;
		scheduleFrame( applyRepairPass );
	};

	if ( typeof MutationObserver === 'function' ) {
		const observer = new MutationObserver( () => {
			if ( isMobileView() ) {
				queueRepairPass();
			}
		} );

		observer.observe( document.documentElement, {
			childList: true,
			subtree: true,
			attributes: true,
			attributeFilter: [ 'class', 'style' ],
		} );
	}

	if ( typeof mediaQueryList.addEventListener === 'function' ) {
		mediaQueryList.addEventListener( 'change', queueRepairPass );
	} else if ( typeof mediaQueryList.addListener === 'function' ) {
		mediaQueryList.addListener( queueRepairPass );
	}

	window.addEventListener( 'load', queueRepairPass, { passive: true } );
	window.addEventListener( 'orientationchange', queueRepairPass, { passive: true } );
	document.addEventListener( 'DOMContentLoaded', queueRepairPass );

	queueRepairPass();
} )();
