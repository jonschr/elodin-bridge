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
	const defaultTypeOverrideControls = [
		{
			className: 'p',
			label: __( 'Paragraph style', 'elodin-bridge' ),
		},
		{
			className: 'h1',
			label: __( 'H1 style', 'elodin-bridge' ),
		},
		{
			className: 'h2',
			label: __( 'H2 style', 'elodin-bridge' ),
		},
		{
			className: 'h3',
			label: __( 'H3 style', 'elodin-bridge' ),
		},
		{
			className: 'h4',
			label: __( 'H4 style', 'elodin-bridge' ),
		},
	];
	const configuredTypeOverrideControls = Array.isArray( toolbarConfig.typeOverrideControls )
		? toolbarConfig.typeOverrideControls
				.map( ( control ) => {
					if ( ! control || 'object' !== typeof control ) {
						return null;
					}

					const className = String( control.className || '' ).trim();
					const label = String( control.label || '' ).trim();
					if ( ! className || ! label || ! /^[a-z0-9-]+$/i.test( className ) ) {
						return null;
					}

					return {
						className: className,
						label: label,
					};
				} )
				.filter( Boolean )
		: [];
	const typeOverrideControls =
		configuredTypeOverrideControls.length > 0
			? configuredTypeOverrideControls
			: defaultTypeOverrideControls;
	const managedClasses = typeOverrideControls.map( ( control ) => control.className );
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

	function buildControls( props ) {
		const currentClassName = props.attributes?.className || '';
		const activeManagedClass = getActiveManagedClass( currentClassName );

		return typeOverrideControls.map( ( control ) => ( {
			title: control.label,
			isActive: control.className === activeManagedClass,
			onClick: () => {
				const nextClassName = getNextClassName( currentClassName, control.className );
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
				const activeManagedControl =
					typeOverrideControls.find( ( control ) => control.className === activeManagedClass ) ||
					null;
				const activeManagedClassLabel = activeManagedClass
					? activeManagedControl && activeManagedControl.label
						? activeManagedControl.label
						: activeManagedClass.toUpperCase()
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
