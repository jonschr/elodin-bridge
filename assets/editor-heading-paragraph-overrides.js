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
	const { ToolbarDropdownMenu, ToolbarGroup } = wp.components;
	const { dispatch } = wp.data;
	const { __, sprintf } = wp.i18n;

	const allowedBlocks = new Set( [ 'core/paragraph', 'core/heading' ] );
	const managedClasses = [ 'p', 'h1', 'h2', 'h3', 'h4' ];
	const managedClassSet = new Set( managedClasses );

	function parseClasses( className ) {
		return ( className || '' ).split( /\s+/ ).filter( Boolean );
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

	const withElodinHeadingParagraphToolbar = createHigherOrderComponent(
		( BlockEdit ) => {
			return function WrappedBlockEdit( props ) {
				if ( ! props.isSelected || ! allowedBlocks.has( props.name ) ) {
					return el( BlockEdit, props );
				}

				const currentClassName = props.attributes?.className || '';
				const activeManagedClass = getActiveManagedClass( currentClassName );
				const activeManagedClassLabel = activeManagedClass
					? activeManagedClass.toUpperCase()
					: '';
				const dropdownLabel = activeManagedClass
					? sprintf(
							__( 'Typography override: %s', 'elodin-bridge' ),
							activeManagedClassLabel
					  )
					: __( 'Typography override', 'elodin-bridge' );

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
