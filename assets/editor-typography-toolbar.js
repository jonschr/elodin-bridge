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
	const { ToolbarGroup, ToolbarDropdownMenu, ToolbarButton } = wp.components;
	const { dispatch } = wp.data;
	const { __, sprintf } = wp.i18n;

	const allowedBlocks = new Set( [ 'core/paragraph', 'core/heading' ] );
	const managedClasses = [ 'p', 'h1', 'h2', 'h3', 'h4' ];
	const managedClassSet = new Set( managedClasses );
	const balancedClass = 'balanced';
	const toolbarSettings = window.elodinBridgeToolbarSettings || {};
	const enableHeadingParagraphOverrides =
		false !== toolbarSettings.enableHeadingParagraphOverrides;
	const enableBalancedText = false !== toolbarSettings.enableBalancedText;

	if ( ! enableHeadingParagraphOverrides && ! enableBalancedText ) {
		return;
	}

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

	function getControlLabel( className ) {
		if ( 'p' === className ) {
			return __( 'Paragraph style', 'elodin-bridge' );
		}

		return sprintf( __( '%s style', 'elodin-bridge' ), className.toUpperCase() );
	}

	function buildControls( props ) {
		const currentClassName = props.attributes?.className || '';
		const activeManagedClass = getActiveManagedClass( currentClassName );

		const controls = managedClasses.map( ( className ) => {
			const isActive = className === activeManagedClass;

				return {
					title: getControlLabel( className ),
					isActive,
					onClick: () => {
						const nextClassName = getNextClassName( currentClassName, className );
						dispatch( blockEditorStore ).updateBlockAttributes( props.clientId, {
						className: nextClassName,
					} );
				},
			};
		} );

		return controls;
	}

	const withElodinTypographyToolbar = createHigherOrderComponent(
		( BlockEdit ) => {
			return function WrappedBlockEdit( props ) {
				if ( ! props.isSelected || ! allowedBlocks.has( props.name ) ) {
					return el( BlockEdit, props );
				}

				const currentClassName = props.attributes?.className || '';
				const activeManagedClass = getActiveManagedClass( currentClassName );
				const isBalanced = hasClass( currentClassName, balancedClass );
				const activeManagedClassLabel = activeManagedClass
					? activeManagedClass.toUpperCase()
					: '';
				const dropdownLabel = activeManagedClass
					? sprintf(
							__( 'Typography override: %s', 'elodin-bridge' ),
							activeManagedClassLabel
					  )
					: __( 'Typography override', 'elodin-bridge' );
				const toolbarControls = [];

						if ( enableHeadingParagraphOverrides ) {
							toolbarControls.push(
								el( ToolbarDropdownMenu, {
									icon: null,
									label: dropdownLabel,
									text: activeManagedClass ? activeManagedClassLabel : 'Type',
									controls: buildControls( props ),
								popoverProps: {
									className: 'elodin-bridge-type-menu',
								},
								toggleProps: {
									isPressed: !! activeManagedClass,
								},
							} )
						);
				}

				if ( enableBalancedText ) {
					toolbarControls.push(
							el( ToolbarButton, {
								icon: isBalanced ? 'editor-justify' : 'editor-alignleft',
								label: isBalanced
									? __( 'Disable balanced text', 'elodin-bridge' )
									: __( 'Enable balanced text', 'elodin-bridge' ),
								isPressed: isBalanced,
								showTooltip: true,
								onClick: () => {
									dispatch( blockEditorStore ).updateBlockAttributes( props.clientId, {
									className: toggleClass( currentClassName, balancedClass ),
								} );
							},
						} )
					);
				}

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
							...toolbarControls
						)
					)
				);
			};
		},
		'withElodinTypographyToolbar'
	);

	addFilter(
		'editor.BlockEdit',
		'elodin-bridge/typography-toolbar',
		withElodinTypographyToolbar
	);
} )( window.wp );
