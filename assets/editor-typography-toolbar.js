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
	const { ToolbarGroup, ToolbarDropdownMenu } = wp.components;
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

	function clearManagedClasses( currentClassName ) {
		const currentClasses = parseClasses( currentClassName );
		const nextClasses = currentClasses.filter(
			( className ) => ! managedClassSet.has( className )
		);

		return nextClasses.length ? nextClasses.join( ' ' ) : undefined;
	}

	function getActiveManagedClass( className ) {
		const classes = parseClasses( className );
		return managedClasses.find( ( candidate ) => classes.includes( candidate ) ) || '';
	}

	function getControlLabel( className ) {
		if ( 'p' === className ) {
			return __( 'Use paragraph typography (p)', 'elodin-bridge' );
		}

		return sprintf(
			__( 'Use %s typography', 'elodin-bridge' ),
				className.toUpperCase()
			);
	}

	function getControlIcon( isActive ) {
		const slotStyle = {
			display: 'inline-flex',
			width: '16px',
			justifyContent: 'center',
			alignItems: 'center',
		};
		const boxStyle = {
			width: '11px',
			height: '11px',
			border: isActive ? '1px solid currentColor' : '1px solid transparent',
			borderRadius: '2px',
			display: 'inline-flex',
			alignItems: 'center',
			justifyContent: 'center',
			fontSize: '8px',
			lineHeight: '1',
			fontWeight: '700',
		};

		return el(
			'span',
			{
				'aria-hidden': 'true',
				style: slotStyle,
			},
			el(
				'span',
				{
					style: boxStyle,
				},
				isActive ? '\u2713' : ''
			)
		);
	}

	function buildControls( props ) {
		const currentClassName = props.attributes?.className || '';
		const activeManagedClass = getActiveManagedClass( currentClassName );

		const controls = managedClasses.map( ( className ) => {
			const isActive = className === activeManagedClass;

			return {
				title: getControlLabel( className ),
				icon: getControlIcon( isActive ),
				isActive,
				onClick: () => {
					const nextClassName = getNextClassName( currentClassName, className );
					dispatch( blockEditorStore ).updateBlockAttributes( props.clientId, {
						className: nextClassName,
					} );
				},
			};
		} );

		controls.push( {
			title: __( 'Clear typography override', 'elodin-bridge' ),
			isDisabled: ! activeManagedClass,
			onClick: () => {
				dispatch( blockEditorStore ).updateBlockAttributes( props.clientId, {
					className: clearManagedClasses( currentClassName ),
				} );
			},
		} );

		return controls;
	}

	const withElodinTypographyToolbar = createHigherOrderComponent(
		( BlockEdit ) => {
			return function WrappedBlockEdit( props ) {
				if ( ! props.isSelected || ! allowedBlocks.has( props.name ) ) {
					return el( BlockEdit, props );
				}

					const activeManagedClass = getActiveManagedClass(
						props.attributes?.className || ''
					);
					const activeManagedClassLabel = activeManagedClass
						? activeManagedClass.toUpperCase()
						: '';
						const label = activeManagedClass
							? sprintf(
									__( 'Typography override: %s', 'elodin-bridge' ),
									activeManagedClassLabel
							  )
						: __( 'Typography override', 'elodin-bridge' );
					const buttonText = activeManagedClass
						? activeManagedClassLabel
						: 'Type';
					const buttonIcon = activeManagedClass
						? 'yes-alt'
						: 'editor-textcolor';

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
									icon: buttonIcon,
									label,
									text: buttonText,
									controls: buildControls( props ),
								} )
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
