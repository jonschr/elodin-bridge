( function ( wp, window ) {
	if ( ! wp || ! wp.blocks || ! wp.domReady ) {
		return;
	}

	const { getBlockVariations, unregisterBlockVariation, registerBlockVariation } = wp.blocks;
	const defaults = window.elodinBridgeGenerateBlocksLayoutGapDefaults || {};
	if ( ! defaults.enabled ) {
		return;
	}

	function getStylesBuilderAtRule( key ) {
		const stylesBuilder = window.gb && window.gb.stylesBuilder ? window.gb.stylesBuilder : null;
		if ( ! stylesBuilder || 'function' !== typeof stylesBuilder.getAtRuleValue ) {
			return '';
		}

		const value = stylesBuilder.getAtRuleValue( key );
		return 'string' === typeof value ? value : '';
	}

	function getMediaStyleKeys( styles ) {
		return Object.keys( styles || {} ).filter( ( key ) => {
			return /^@media/i.test( key ) && styles[ key ] && 'object' === typeof styles[ key ];
		} );
	}

	function applyGapValues( styles, atRule, columnGap, rowGap ) {
		if ( ! atRule ) {
			return styles;
		}

		return Object.assign( {}, styles, {
			[ atRule ]: Object.assign(
				{},
				styles[ atRule ] && 'object' === typeof styles[ atRule ] ? styles[ atRule ] : {},
				{
					columnGap: columnGap,
					rowGap: rowGap,
				}
			),
		} );
	}

	wp.domReady( function () {
		if ( 'function' !== typeof getBlockVariations || 'function' !== typeof registerBlockVariation ) {
			return;
		}

		const variations = getBlockVariations( 'generateblocks/element' ) || [];
		const gridVariation = variations.find( ( variation ) => {
			return variation && 'generateblocks/grid' === variation.name;
		} );
		if ( ! gridVariation ) {
			return;
		}

		const originalAttributes =
			gridVariation.attributes && 'object' === typeof gridVariation.attributes
				? gridVariation.attributes
				: {};
		const originalStyles =
			originalAttributes.styles && 'object' === typeof originalAttributes.styles
				? originalAttributes.styles
				: {};

		let nextStyles = Object.assign( {}, originalStyles, {
			columnGap: defaults.columnGapDesktop || originalStyles.columnGap || 'var( --space-xl )',
			rowGap: defaults.rowGapDesktop || originalStyles.rowGap || 'var( --space-m )',
		} );

		const mediumAtRule = getStylesBuilderAtRule( 'mediumWidth' );
		const smallAtRule = getStylesBuilderAtRule( 'smallWidth' );

		if ( mediumAtRule ) {
			nextStyles = applyGapValues(
				nextStyles,
				mediumAtRule,
				defaults.columnGapTablet || defaults.columnGapDesktop || nextStyles.columnGap,
				defaults.rowGapTablet || defaults.rowGapDesktop || nextStyles.rowGap
			);
		}

		if ( smallAtRule ) {
			nextStyles = applyGapValues(
				nextStyles,
				smallAtRule,
				defaults.columnGapMobile || defaults.columnGapTablet || defaults.columnGapDesktop || nextStyles.columnGap,
				defaults.rowGapMobile || defaults.rowGapTablet || defaults.rowGapDesktop || nextStyles.rowGap
			);
		}

		const mediaKeys = getMediaStyleKeys( nextStyles );
		if ( ! mediumAtRule && mediaKeys.length > 1 ) {
			nextStyles = applyGapValues(
				nextStyles,
				mediaKeys[ 0 ],
				defaults.columnGapTablet || defaults.columnGapDesktop || nextStyles.columnGap,
				defaults.rowGapTablet || defaults.rowGapDesktop || nextStyles.rowGap
			);
		}

		if ( ! smallAtRule && mediaKeys.length > 0 ) {
			nextStyles = applyGapValues(
				nextStyles,
				mediaKeys[ mediaKeys.length - 1 ],
				defaults.columnGapMobile || defaults.columnGapTablet || defaults.columnGapDesktop || nextStyles.columnGap,
				defaults.rowGapMobile || defaults.rowGapTablet || defaults.rowGapDesktop || nextStyles.rowGap
			);
		}

		const replacementVariation = Object.assign( {}, gridVariation, {
			attributes: Object.assign( {}, originalAttributes, {
				styles: nextStyles,
			} ),
		} );

		if ( 'function' === typeof unregisterBlockVariation ) {
			unregisterBlockVariation( 'generateblocks/element', 'generateblocks/grid' );
		}

		registerBlockVariation( 'generateblocks/element', replacementVariation );
	} );
} )( window.wp, window );
