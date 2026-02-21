( function ( wp ) {
	if (
		! wp ||
		! wp.hooks ||
		! wp.compose ||
		! wp.element ||
		! wp.blockEditor ||
		! wp.components ||
		! wp.data ||
		! wp.i18n
	) {
		return;
	}

	const { addFilter } = wp.hooks;
	const { createHigherOrderComponent } = wp.compose;
	const { createElement: el, Fragment } = wp.element;
	const { BlockControls, store: blockEditorStore } = wp.blockEditor;
	const { ToolbarDropdownMenu, ToolbarGroup, ToolbarButton } = wp.components;
	const { dispatch } = wp.data;
	const { __, sprintf } = wp.i18n;

	const toolbarConfig = window.elodinBridgeTypographyToolbar || {};
	const enableTypeOverrides = toolbarConfig.enableTypeOverrides !== false;
	const enableBalancedText = !! toolbarConfig.enableBalancedText;

	const allowedBlocks = new Set( [ 'core/paragraph', 'core/heading' ] );
	const managedClasses = [ 'p', 'h1', 'h2', 'h3', 'h4' ];
	const managedClassSet = new Set( managedClasses );
	const marginTopClasses = [ 'elodin-mt-0', 'elodin-mt-s', 'elodin-mt-m' ];
	const legacyMarginTopClass = 'elodin-mt';
	const marginTopClassSet = new Set( marginTopClasses.concat( legacyMarginTopClass ) );
	const balancedClass = 'balanced';

	function parseClasses( className ) {
		return ( className || '' ).split( /\s+/ ).filter( Boolean );
	}

	function hasClass( className, targetClass ) {
		return parseClasses( className ).includes( targetClass );
	}

	function toggleClass( className, targetClass ) {
		const classes = parseClasses( className );
		const nextClasses = hasClass( className, targetClass )
			? classes.filter( ( candidate ) => candidate !== targetClass )
			: classes.concat( targetClass );

		return nextClasses.length ? nextClasses.join( ' ' ) : undefined;
	}

	function getNextClassName( currentClassName, targetClass ) {
		const currentClasses = parseClasses( currentClassName );
		const hasTargetClass = currentClasses.includes( targetClass );
		const preservedClasses = currentClasses.filter(
			( className ) => ! managedClassSet.has( className )
		);
		const nextClasses = hasTargetClass
			? preservedClasses
			: preservedClasses.concat( targetClass );

		return nextClasses.length ? nextClasses.join( ' ' ) : undefined;
	}

	function getActiveManagedClass( className ) {
		const classes = parseClasses( className );
		return managedClasses.find( ( candidate ) => classes.includes( candidate ) ) || '';
	}

	function getActiveMarginTopClass( className ) {
		const classes = parseClasses( className );
		const matchedClass = marginTopClasses.find( ( candidate ) => classes.includes( candidate ) );
		if ( matchedClass ) {
			return matchedClass;
		}

		return classes.includes( legacyMarginTopClass ) ? 'elodin-mt-0' : '';
	}

	function getNextMarginTopClassName( currentClassName, targetClass ) {
		const currentClasses = parseClasses( currentClassName );
		const activeMarginTopClass =
			marginTopClasses.find( ( className ) => currentClasses.includes( className ) ) ||
			( currentClasses.includes( legacyMarginTopClass ) ? legacyMarginTopClass : '' );
		const preservedClasses = currentClasses.filter(
			( className ) => ! marginTopClassSet.has( className )
		);
		const nextClasses = activeMarginTopClass === targetClass
			? preservedClasses
			: preservedClasses.concat( targetClass );

		return nextClasses.length ? nextClasses.join( ' ' ) : undefined;
	}

	function getMarginTopLabel( className ) {
		if ( 'elodin-mt-0' === className ) {
			return __( '0', 'elodin-bridge' );
		}

		if ( 'elodin-mt-s' === className ) {
			return 'var( --space-s )';
		}

		return 'var( --space-m )';
	}

	function getTypeIcon( isActive ) {
		return el(
			'span',
			{
				className: 'elodin-bridge-type-icon' + ( isActive ? ' is-active' : '' ),
				'aria-hidden': 'true',
			},
			el( 'strong', null, 'A' ),
			el( 'span', null, 'A' )
		);
	}

	function getControlLabel( className ) {
		if ( 'p' === className ) {
			return __( 'Paragraph style', 'elodin-bridge' );
		}

		return sprintf( __( '%s style', 'elodin-bridge' ), className.toUpperCase() );
	}

	function buildControls( props ) {
		const currentClassName = props.attributes?.className || '';
		const activeManagedClass = getActiveManagedClass( currentClassName );

		return managedClasses.map( ( className ) => ( {
			title: getControlLabel( className ),
			isActive: className === activeManagedClass,
			onClick: () => {
				const nextClassName = getNextClassName( currentClassName, className );
				dispatch( blockEditorStore ).updateBlockAttributes( props.clientId, {
					className: nextClassName,
				} );
			},
		} ) );
	}

	function buildMarginTopControls( props ) {
		const currentClassName = props.attributes?.className || '';
		const activeMarginTopClass = getActiveMarginTopClass( currentClassName );
		const clearControl = {
			title: __( 'No margin-top override', 'elodin-bridge' ),
			isActive: '' === activeMarginTopClass,
			onClick: () => {
				const nextClasses = parseClasses( currentClassName ).filter(
					( className ) => ! marginTopClassSet.has( className )
				);
				dispatch( blockEditorStore ).updateBlockAttributes( props.clientId, {
					className: nextClasses.length ? nextClasses.join( ' ' ) : undefined,
				} );
			},
		};

		return [
			clearControl,
			...marginTopClasses.map( ( className ) => ( {
				title: sprintf(
					__( 'Margin top: %s', 'elodin-bridge' ),
					getMarginTopLabel( className )
				),
				isActive: className === activeMarginTopClass,
				onClick: () => {
					dispatch( blockEditorStore ).updateBlockAttributes( props.clientId, {
						className: getNextMarginTopClassName( currentClassName, className ),
					} );
				},
			} ) ),
		];
	}

	function isMarginTopControlAvailable( props ) {
		if ( 'core/paragraph' === props.name ) {
			return true;
		}

		if ( 'core/heading' !== props.name ) {
			return false;
		}

		const headingLevel = Number( props.attributes?.level || 2 );
		return headingLevel >= 1 && headingLevel <= 4;
	}

	const withElodinHeadingParagraphToolbar = createHigherOrderComponent(
		( BlockEdit ) => {
			return function WrappedBlockEdit( props ) {
				if ( ! props.isSelected || ! allowedBlocks.has( props.name ) ) {
					return el( BlockEdit, props );
				}

				const currentClassName = props.attributes?.className || '';
				const activeManagedClass = getActiveManagedClass( currentClassName );
				const activeMarginTopClass = getActiveMarginTopClass( currentClassName );
				const isBalanced = hasClass( currentClassName, balancedClass );
				const activeManagedClassLabel = activeManagedClass
					? activeManagedClass.toUpperCase()
					: '';
				const canShowTypeControls = enableTypeOverrides;
				const canShowMarginTopControl = enableTypeOverrides && isMarginTopControlAvailable( props );
				const canShowBalancedControl = enableBalancedText;
				if ( ! canShowTypeControls && ! canShowMarginTopControl && ! canShowBalancedControl ) {
					return el( BlockEdit, props );
				}

				const dropdownLabel = activeManagedClass
					? sprintf(
							__( 'Typography override: %s', 'elodin-bridge' ),
							activeManagedClassLabel
					  )
					: __( 'Typography override', 'elodin-bridge' );
				const marginTopDropdownLabel = activeMarginTopClass
					? sprintf(
							__( 'Margin-top override: %s', 'elodin-bridge' ),
							getMarginTopLabel( activeMarginTopClass )
					  )
					: __( 'Margin-top override', 'elodin-bridge' );

				return el(
					Fragment,
					null,
					el( BlockEdit, props ),
					el(
						BlockControls,
						{ group: 'block' },
						el(
							ToolbarGroup,
							null,
							canShowTypeControls &&
								el( ToolbarDropdownMenu, {
									icon: getTypeIcon( !! activeManagedClass ),
									label: dropdownLabel,
									text: null,
									controls: buildControls( props ),
									popoverProps: {
										className: 'elodin-bridge-type-menu',
									},
									toggleProps: {
										isPressed: !! activeManagedClass,
										showTooltip: true,
										className: 'elodin-bridge-toolbar-toggle elodin-bridge-toolbar-toggle--type',
									},
								} ),
							canShowMarginTopControl &&
								el( ToolbarDropdownMenu, {
									icon: 'arrow-up-alt2',
									label: marginTopDropdownLabel,
									text: null,
									controls: buildMarginTopControls( props ),
									popoverProps: {
										className: 'elodin-bridge-type-menu',
									},
									toggleProps: {
										isPressed: !! activeMarginTopClass,
										showTooltip: true,
										className: 'elodin-bridge-toolbar-toggle elodin-bridge-toolbar-toggle--margin',
									},
								} ),
							canShowBalancedControl &&
								el( ToolbarButton, {
									icon: isBalanced ? 'editor-justify' : 'editor-alignleft',
									label: isBalanced
										? __( 'Disable balanced text', 'elodin-bridge' )
										: __( 'Enable balanced text', 'elodin-bridge' ),
									isPressed: isBalanced,
									showTooltip: true,
									className: 'elodin-bridge-toolbar-toggle elodin-bridge-toolbar-toggle--balanced',
									onClick: () => {
										dispatch( blockEditorStore ).updateBlockAttributes( props.clientId, {
											className: toggleClass( currentClassName, balancedClass ),
										} );
									},
								} )
						)
					)
				);
			};
		},
		'withElodinHeadingParagraphToolbar'
	);

	addFilter(
		'editor.BlockEdit',
		'elodin-bridge/heading-paragraph-toolbar',
		withElodinHeadingParagraphToolbar
	);
} )( window.wp );
