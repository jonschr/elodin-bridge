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

	const withElodinHeadingParagraphToolbar = createHigherOrderComponent(
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
				const canShowTypeControls = enableTypeOverrides;
				const canShowBalancedControl = enableBalancedText;
				if ( ! canShowTypeControls && ! canShowBalancedControl ) {
					return el( BlockEdit, props );
				}

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
