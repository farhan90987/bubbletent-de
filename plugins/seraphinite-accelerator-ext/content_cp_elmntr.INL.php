<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_elmntrAni( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	$cmnStyles = '';
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-element ")][not(self::node()[@data-lzl-trx])][contains(@data-settings,"animation")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = ( array )@json_decode( $item -> getAttribute( 'data-settings' ), true );

		foreach( array( '', '_' ) as $attrPrefix )
		{
			if( $ctx -> cfgElmntrFrontend === null )
				$ctx -> cfgElmntrFrontend = _Elmntr_GetFrontendCfg( $xpath );

			foreach( array_merge( array( '' => null ), Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views' ), array() ) ) as $viewId => $view )
			{
				$attrSrch = array( 'an' => $attrPrefix . 'animation' . ( $viewId ? '_' . $viewId : '' ), 'ad' => $attrPrefix . 'animation_delay' . ( $viewId ? '_' . $viewId : '' ) );

				$sAniName = Gen::GetArrField( $dataSett, array( $attrSrch[ 'an' ] ), '' );
				if( !$sAniName || $sAniName == 'none' )
					continue;

				if( $viewId )
				{
					$dataId = $item -> getAttribute( 'data-id' );
					if( !$dataId )
						continue;

					$cmnStyles .= '@media ' . ( $view[ 'cxMin' ] != 0 ? ( '(min-width: ' . $view[ 'cxMin' ] . 'px)' ) : '' ) . ( $view[ 'cxMin' ] != 0 && $view[ 'cxMax' ] != 2147483647 ? ' and ' : '' ) . ( $view[ 'cxMax' ] != 2147483647 ? ( '(max-width: ' . $view[ 'cxMax' ] . 'px)' ) : '' ) . ' {' . "\n";
					$cmnStyles .= '.elementor-element-' . $dataId . ' {';
					$cmnStyles .= '--lzl-an: ' . $sAniName . ';';
					if( isset( $dataSett[ $attrSrch[ 'ad' ] ] ) )
						$cmnStyles .= '--lzl-ad: ' . ( string )Gen::GetArrField( $dataSett, array( $attrSrch[ 'ad' ] ) ) . ';';
					$cmnStyles .= '}' . "\n";
					$cmnStyles .= '}' . "\n";

					$item -> setAttribute( 'data-lzl-an', '' );
				}
				else
				{
					$item -> setAttribute( 'data-lzl-an', $sAniName );
					if( isset( $dataSett[ $attrSrch[ 'ad' ] ] ) )
						$item -> setAttribute( 'data-lzl-ad', ( string )Gen::GetArrField( $dataSett, array( $attrSrch[ 'ad' ] ) ) );
				}

				$ctxProcess[ 'aCssCrit' ][ '@\\.' . $sAniName . '@' ] = true;

				$adjusted = true;

				if( !$viewId )
					break;
			}
		}
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.animated@' ] = true;

		{
			/*
			
			
			
			



			*/

			$ctx -> aAniAppear[ '.elementor-element[data-lzl-an]:not(.animated)' ] = 'function( e, api )
{
	var an = e.getAttribute( "data-lzl-an" );
	var delay = e.getAttribute( "data-lzl-ad" );
	if( !an )
	{
		var styles = getComputedStyle( e );

		an = styles.getPropertyValue( "--lzl-an" );
		delay = styles.getPropertyValue( "--lzl-ad" );
	}

	if( !an )
		return;

	e.classList.add( "animated" );

	function _apply()
	{
		e.classList.add( an );
		e.classList.remove( "elementor-invisible" );

		// Preventing animation repeat after full JS loading
		var styles = getComputedStyle( e );
		e.style.setProperty( "animation-name", styles.getPropertyValue( "animation-name" ) );
		setTimeout( function() { e.classList.add( "lzl-an-ed" ); }, api.GetDurationTime( styles.getPropertyValue( "animation-delay" ), "max" ) + api.GetDurationTime( styles.getPropertyValue( "animation-duration" ), "max" ) );
	}

	delay ? setTimeout( _apply, parseInt( delay, 10 ) ) : _apply();
}';
		}

		{
			$cmnStyles .= ".animated.lzl-an-ed[data-lzl-an] {\r\n\tanimation-duration: 0s !important;\r\n}";

			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, $cmnStyles );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _ProcessCont_Cp_elmntrWdgtAniHdln( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-animated-headline ")][@data-settings]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.e-animated@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.elementor-headline-dynamic-wrapper\\s+path@' ] = true;

		{
			/*
			
			
			
			



			*/

			$ctx -> aAniAppear[ '.elementor-widget-animated-headline:has(.elementor-headline:not(.e-animated))' ] = "function( e, api )\r\n{\r\n\tvar data; try { data = JSON.parse( e.getAttribute( \"data-settings\" ) ); } catch( err ) {}; if( !data ) data = {};\r\n\r\n\tvar eHdln = e.querySelector( \".elementor-headline\" );\r\n\tvar eHdlnWrp = e.querySelector( \".elementor-headline-dynamic-wrapper\" );\r\n\r\n\tif( !eHdln || !eHdlnWrp )\r\n\t\treturn;\r\n\r\n\tif( data.headline_style == \"highlight\" )\r\n\t{\r\n\t\teHdln.classList.remove( \"e-hide-highlight\" );\r\n\t\teHdln.classList.add( \"e-animated\" );\r\n\r\n\t\t// /wp-content/plugins/elementor-pro/assets/js/animated-headline.588a0449647bd4f113f3.bundle.min.js\r\n\t\tconst g_aSvgPaths =\r\n\t\t{\r\n\t\t\tcircle:\t\t\t\t\t[ \"M325,18C228.7-8.3,118.5,8.3,78,21C22.4,38.4,4.6,54.6,5.6,77.6c1.4,32.4,52.2,54,142.6,63.7 c66.2,7.1,212.2,7.5,273.5-8.3c64.4-16.6,104.3-57.6,33.8-98.2C386.7-4.9,179.4-1.4,126.3,20.7\" ],\r\n\t\t\tunderline_zigzag:\t\t[ \"M9.3,127.3c49.3-3,150.7-7.6,199.7-7.4c121.9,0.4,189.9,0.4,282.3,7.2C380.1,129.6,181.2,130.6,70,139 c82.6-2.9,254.2-1,335.9,1.3c-56,1.4-137.2-0.3-197.1,9\" ],\r\n\t\t\tx:\t\t\t\t\t\t[ \"M497.4,23.9C301.6,40,155.9,80.6,4,144.4\", \"M14.1,27.6c204.5,20.3,393.8,74,467.3,111.7\" ],\r\n\t\t\tstrikethrough:\t\t\t[ \"M3,75h493.5\" ],\r\n\t\t\tcurly:\t\t\t\t\t[ \"M3,146.1c17.1-8.8,33.5-17.8,51.4-17.8c15.6,0,17.1,18.1,30.2,18.1c22.9,0,36-18.6,53.9-18.6 c17.1,0,21.3,18.5,37.5,18.5c21.3,0,31.8-18.6,49-18.6c22.1,0,18.8,18.8,36.8,18.8c18.8,0,37.5-18.6,49-18.6c20.4,0,17.1,19,36.8,19 c22.9,0,36.8-20.6,54.7-18.6c17.7,1.4,7.1,19.5,33.5,18.8c17.1,0,47.2-6.5,61.1-15.6\" ],\r\n\t\t\tdiagonal:\t\t\t\t[ \"M13.5,15.5c131,13.7,289.3,55.5,475,125.5\" ],\r\n\t\t\tdouble:\t\t\t\t\t[ \"M8.4,143.1c14.2-8,97.6-8.8,200.6-9.2c122.3-0.4,287.5,7.2,287.5,7.2\", \"M8,19.4c72.3-5.3,162-7.8,216-7.8c54,0,136.2,0,267,7.8\" ],\r\n\t\t\tdouble_underline:\t\t[ \"M5,125.4c30.5-3.8,137.9-7.6,177.3-7.6c117.2,0,252.2,4.7,312.7,7.6\", \"M26.9,143.8c55.1-6.1,126-6.3,162.2-6.1c46.5,0.2,203.9,3.2,268.9,6.4\" ],\r\n\t\t\tunderline:\t\t\t\t[ \"M7.7,145.6C109,125,299.9,116.2,401,121.3c42.1,2.2,87.6,11.8,87.3,25.7\" ]\r\n\t\t};\r\n\r\n\t\tvar contAdd = \"<svg xmlns=\\\"http://www.w3.org/2000/svg\\\" viewBox=\\\"0 0 500 150\\\" preserveAspectRatio=\\\"none\\\" class=\\\"js-lzl-ing\\\">\", aSvgPath = g_aSvgPaths[data.marker];\r\n\t\tif( aSvgPath )\r\n\t\t\tfor( var i in aSvgPath )\r\n\t\t\t\tcontAdd += \"<path d=\\\"\" + aSvgPath[ i ] + \"\\\"></path>\";\r\n\t\tcontAdd += \"</svg>\";\r\n\r\n\t\teHdlnWrp.innerHTML += contAdd;\r\n\r\n\t\t//function _apply()\r\n\t\t//{\r\n\t\t//}\r\n\r\n\t\t//setTimeout( _apply, ( data.highlight_animation_duration || 1200 ) + ( data.highlight_iteration_delay || 8e3 ) );\r\n\t}\r\n}";
		}

		{
			$cmnStyles = ".elementor-headline-dynamic-wrapper svg.js-lzl-ing:has(~ svg) {\r\n\tdisplay: none !important;\r\n}";

			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, $cmnStyles );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _ProcessCont_Cp_elmntrTrxAni( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	$bTrxScr = null;
	foreach( array( array( 'animation', 'animation_delay' ), array( '_animation', '_animation_delay' ) ) as $attrSrch )
	{
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-element ")][contains(concat(" ",normalize-space(@class)," ")," elementor-widget-trx_")][contains(@data-settings,\'"' . $attrSrch[ 0 ] . '":\')]' ) as $itemContainer )
		{
			if( $bTrxScr === null )
				$bTrxScr = !!HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="trx_addons-js"][contains(@src,"trx_addons/js/__scripts.js")]' ) );
			if( !$bTrxScr )
				continue;

			if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $itemContainer ) || !ContentProcess_IsItemInFragments( $ctxProcess, $itemContainer ) )
				continue;

			$widgetClass = ( string )$itemContainer -> getAttribute( 'data-widget_type' );
			if( !Gen::StrStartsWith( $widgetClass, 'trx_' ) )
				continue;

			$widgetClass = substr( $widgetClass, 4 );
			$widgetClass = explode( '.', $widgetClass )[ 0 ] . '_item';

			$dataSett = ( array )@json_decode( $itemContainer -> getAttribute( 'data-settings' ), true );

			$sAniName = Gen::GetArrField( $dataSett, array( $attrSrch[ 0 ] ), '' );
			if( !$sAniName || $sAniName == 'none' )
				continue;

			$ctxProcess[ 'aCssCrit' ][ '@\\.' . $sAniName . '@' ] = true;

			$itemContainer -> setAttribute( 'data-lzl-trx', '1' );
			$aItem = HtmlNd::ChildrenAsArr( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ' . $widgetClass . ' ")]', $itemContainer ) );
			if( !$aItem )
				$aItem[] = $itemContainer;
			foreach( $aItem as $item )
			{
				$item -> setAttribute( 'data-lzl-trxan', $sAniName );
				if( isset( $dataSett[ $attrSrch[ 1 ] ] ) )
					$item -> setAttribute( 'data-lzl-trxad', ( string )Gen::GetArrField( $dataSett, array( $attrSrch[ 1 ] ) ) );
				$adjusted = true;
			}
		}
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.animated@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.animated-item@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.trx_addons_invisible@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.elementor-invisible@' ] = true;

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '.trx_addons_invisible.animated {
	visibility: visible;
	opacity: 1;
}' );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		/*
		
		
		
		



		 */

		$ctx -> aAniAppear[ '[data-lzl-trxan]:not(.animated)' ] = 'function( e )
		{
		function _apply()
		{
		e.classList.add( e.getAttribute( "data-lzl-trxan" ) );
		e.classList.add( "animated" );
		e.classList.add( "animated-item" );
		e.style.setProperty( "animation-name", getComputedStyle( e ).getPropertyValue( "animation-name" ) );	// Preventing animation repeat after full JS loading

		for( var eParent = e; eParent; eParent = eParent.parentNode )
		{
		if( eParent.getAttribute && eParent.getAttribute( "data-lzl-trx" ) == "1" )
		{
		eParent.classList.remove( "elementor-invisible" );
		break;
		}
		}
		}

		var delay = e.getAttribute( "data-lzl-trxad" );
		delay ? setTimeout( _apply, parseInt( delay, 10 ) ) : _apply();
		}';
	}
}

function _ProcessCont_Cp_elmntrStck( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( HtmlNd::ChildrenAsArr( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-element ")][contains(@data-settings,"sticky")]' ) ) as $item )
	{
		$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
		if( !Gen::GetArrField( $dataSett, array( 'sticky' ), '' ) && !Gen::GetArrField( $dataSett, array( 'ekit_sticky' ), '' ) )
			continue;

		if( $ctx -> cfgElmntrFrontend === null )
			$ctx -> cfgElmntrFrontend = _Elmntr_GetFrontendCfg( $xpath );

		$aStickyOn = array();
		foreach( array_merge( Gen::GetArrField( $dataSett, array( 'sticky_on' ), array() ), explode( '_', Gen::GetArrField( $dataSett, array( 'ekit_sticky_on' ), '' ) ) ) as $stickyOnViewId )
		{
			if( !$stickyOnViewId )
				continue;

			if( $view = Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', $stickyOnViewId ) ) )
				$aStickyOn[] = array( $view[ 'cxMin' ], $view[ 'cxMax' ] );
		}

		$item -> setAttribute( 'data-lzl-sticky-widths', @json_encode( $aStickyOn ) );
		HtmlNd::AddRemoveAttrClass( $item, array( 'js-lzl-ing' ) );

		$itemStickySpacer = HtmlNd::CreateTag( $doc, $item -> nodeName, array( 'class' => array( 'lzl-sticky-spacer' ), 'style' => array( 'display' => 'none' ) ) );
		HtmlNd::InsertAfter( $item -> parentNode, $itemStickySpacer, $item );

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_elmntrStck_calcSizes(document.currentScript.parentNode);' );
			$item -> appendChild( $itemScript );
		}

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.jet-sticky-transition-in@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.jet-sticky-section--stuck@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.ekit-sticky@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.lzl-sticky@' ] = true;

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '[data-lzl-sticky-widths].js-lzl-ing.elementor-element.lzl-sticky {
	position: fixed;
	width: 100%;
	margin-top: 0px;
	margin-bottom: 0px;
	top: 0px;
	z-index: 99;
}

[data-lzl-sticky-widths].js-lzl-ing:is(.elementor-sticky__spacer,.the7-e-sticky-spacer) {
	display: none!important;
}

[data-lzl-sticky-widths].js-lzl-ing.elementor-element.lzl-sticky + .lzl-sticky-spacer {
	display: block!important;
	width: 100%;
}' );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		{
			/*
			
			
			
			



			 */

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "
			function seraph_accel_cp_elmntrStck_calcSizes( e )
			{
			//
			//console.log( \"seraph_accel_cp_elmntrStck_calcSizes\" );
			//
			if( e.classList.contains( \"elementor-sticky__spacer\" ) || e.classList.contains( \"the7-e-sticky-spacer\" ) )
			return( false );

			var eSpacer = e.nextElementSibling;
			if( eSpacer && eSpacer.classList.contains( \"lzl-sticky-spacer\" ) )
			{
			eSpacer.style.setProperty( \"height\", \"\" + e.getBoundingClientRect().height + \"px\" );
			if( eSpacer.getBoundingClientRect().top > 0 )
			e.classList.remove( \"lzl-sticky\" );
			}

			var data; try { data = JSON.parse( e.getAttribute( \"data-settings\" ) ); } catch( err ) {}; if( !data ) data = {};
			if( data.sticky == \"top\" || data.ekit_sticky == \"top\" )
			{
			var dataWidths; try { dataWidths = JSON.parse( e.getAttribute( \"data-lzl-sticky-widths\" ) ); } catch( err ) {}; if( !dataWidths ) dataWidths = [];
			var bInWidth = false;
			for( var w in dataWidths )
			{
			if( window.innerWidth >= dataWidths[ w ][ 0 ] && window.innerWidth <= dataWidths[ w ][ 1 ] )
			{
			bInWidth = true;
			break;
			}
			}

			if( bInWidth )
			{
			if( data.sticky_offset !== undefined && !data.sticky_offset )
			{
			if( e.getBoundingClientRect().top <= 0 )
			e.classList.add( \"lzl-sticky\" );
			}
			else
			{
			if( data.jet_sticky_section == \"yes\" )
			{
			if( window.scrollY )
			{
			e.classList.add( \"jet-sticky-section--stuck\" );
			e.classList.add( \"jet-sticky-transition-in\" );
			}
			else
			{
			e.classList.remove( \"jet-sticky-section--stuck\" );
			e.classList.remove( \"jet-sticky-transition-in\" );
			}
			}

			if( data.ekit_sticky_offset && data.ekit_sticky_offset.unit == \"px\" )
			{
			if( window.scrollY >= data.ekit_sticky_offset.size )
			{
			e.style.setProperty( \"position\", \"fixed\" );
			e.style.setProperty( \"top\", \"0\" );
			e.style.setProperty( \"width\", \"100%\" );
			e.classList.add( \"ekit-sticky\" );
			e.classList.add( \"ekit-sticky--active\" );
			}
			else
			{
			e.style.removeProperty( \"position\", \"fixed\" );
			e.style.removeProperty( \"top\", \"0\" );
			e.classList.remove( \"ekit-sticky\" );
			e.classList.remove( \"ekit-sticky--active\" );
			}
			}

			if( data.ekit_sticky_effect_offset && data.ekit_sticky_effect_offset.unit == \"px\" )
			{
			if( window.scrollY >= data.ekit_sticky_effect_offset.size )
			{
			e.classList.add( \"ekit-sticky--effects\" );
			}
			else
			{
			e.classList.remove( \"ekit-sticky--effects\" );
			}
			}
			}
			}
			}

			return( true );
			}

			(
			function( d )
			{
			function OnEvt( evt )
			{
			var bApply = true;
			d.querySelectorAll( \"[data-lzl-sticky-widths]\" ).forEach(
			function( e )
			{
			if( !seraph_accel_cp_elmntrStck_calcSizes( e ) )
			bApply = false;
			}
			);

			if( !bApply )
			{
			d.querySelectorAll( \"[data-lzl-sticky-widths]\" ).forEach(
			function( e )
			{
			e.classList.remove( \"js-lzl-ing\" );
			e.classList.remove( \"lzl-sticky\" );
			}
			);

			d.removeEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
			d.removeEventListener( \"scroll\", OnEvt, { capture: true, passive: true } );
			}
			}

			d.addEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
			d.addEventListener( \"scroll\", OnEvt, { capture: true, passive: true } );

			//d.addEventListener( \"seraph_accel_jsFinish\",
			//    function( evt )
			//    {
			//        d.removeEventListener( \"scroll\", OnEvt, { capture: true, passive: true } );
			//        OnEvt( evt );
			//    }
			//, { capture: true, passive: true } );

			//seraph_accel_lzl_bjs.add(
			//	function() {
			//		d.removeEventListener( \"scroll\", OnEvt, { capture: true, passive: true } );
			//	}
			//);
			}
			)( document );
			" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

function _ProcessCont_Cp_elmntrWdgtLott( $ctx, &$ctxProcess, $settFrm, $settCache, $settImg, $settCdn, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-lottie ")][@data-settings]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
		if( !$dataSett )
			continue;

		$itemPlacehldr = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," e-lottie__animation ")]', $item ) );
		if( !$itemPlacehldr )
			continue;

		$renderer = ($dataSett[ 'renderer' ]??null);
		$dataFile = ($dataSett[ 'source_json' ][ 'url' ]??null);
		if( !$dataFile )
			continue;

		$r = _ProcessCont_Cp_lottGen_AdjustItem( $ctx, $ctxProcess, $settFrm, $settCache, $settImg, $settCdn, $doc, $xpath, $itemPlacehldr, $renderer, $dataFile );
		if( $r === false )
			return( false );

		$dataSett[ 'source_json' ][ 'url' ] = $dataFile;

		if( !$r )
			continue;

		if( 0 )
		{
			/*
			
			
			
			



			*/
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			HtmlNd::SetValFromContent( $itemScript, str_replace( array( 'PRM_PATH', 'PRM_RENDERER', 'PRM_LOOP', 'PRM_AUTOPLAY' ), array( $dataFile, $renderer, 'true', 'true' ), "bodymovin.loadAnimation(
	{
		container: document.currentScript.parentNode,
		path: \"PRM_PATH\",
		renderer: \"PRM_RENDERER\",
		loop: PRM_LOOP,
		autoplay: PRM_AUTOPLAY
	}
);
" ) );
			$itemPlacehldr -> insertBefore( $itemScript, $itemPlacehldr -> firstChild );
		}

		//$dataSett[ 'source' ] = 'none';
		//unset( $dataSett[ 'source_json' ] );
		$item -> setAttribute( 'data-settings', @json_encode( $dataSett ) );
		//$item -> removeAttribute( 'data-settings' );

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@svg\\.lottgen@' ] = true;
		$ctxProcess[ 'aJsCritSpec' ][ 'id:@^lottie-js$@' ] = true;
		//$ctxProcess[ 'aJsCritSpec' ][ 'body:@bodymovin\\.loadAnimation\\(\\s*{\\s*container\\s*:\\s*document\\.currentScript\\.parentNode\\W@' ] = true;

		if( $itemScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="lottie-js"]' ) ) )
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemScr );

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, "svg.lottgen.js-lzl-ing:has(+ svg), .e-lottie__animation > svg:not(.lottgen) ~ * {\r\n\tdisplay: none !important;\r\n}" );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
		
		
		{
			/*
			
			
			
			



			*/
			$ctx -> aAniAppear[ '.elementor-widget-lottie:not(.js-lzl-ed)' ] = 'function( e )
{
	if( !window.bodymovin )
		return;

	var ePlacehldr = e.querySelector( ".e-lottie__animation" );
	if( !ePlacehldr )
		return;

	e.classList.add( "js-lzl-ed" );

	var data; try { data = JSON.parse( e.getAttribute( "data-settings" ) ); } catch( err ) {}; if( !data ) data = {};
	bodymovin.loadAnimation(
		{
			container: ePlacehldr,
			path: data.source_json.url,
			renderer: data.renderer,
			loop: true,
			autoplay: true
		}
	);
	delete data.source_json;
	e.setAttribute( "data-settings", JSON.stringify( data ) );
}';
		}
	}
}

function _ProcessCont_Cp_elmntrWdgtPrmLott( $ctx, &$ctxProcess, $settFrm, $settCache, $settImg, $settCdn, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-premium-lottie ")][@data-settings]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
		if( !$dataSett )
			continue;

		$itemPlacehldr = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," premium-lottie-animation ")]', $item ) );
		if( !$itemPlacehldr )
			continue;

		$renderer = $itemPlacehldr-> getAttribute( 'data-lottie-render' );
		$dataFile = $itemPlacehldr-> getAttribute( 'data-lottie-url' );
		if( !$dataFile )
			continue;

		$r = _ProcessCont_Cp_lottGen_AdjustItem( $ctx, $ctxProcess, $settFrm, $settCache, $settImg, $settCdn, $doc, $xpath, $itemPlacehldr, $renderer, $dataFile );
		if( $r === false )
			return( false );

		if( !$r )
			continue;

		$dataSett[ 'lottie_file' ][ 'url' ] = $dataFile;
		$itemPlacehldr-> setAttribute( 'data-lottie-url', $dataFile );

		$item -> setAttribute( 'data-settings', @json_encode( $dataSett ) );

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@svg\\.lottgen@' ] = true;
		$ctxProcess[ 'aJsCritSpec' ][ 'id:@^lottie-js-lzl$@' ] = true;
		//$ctxProcess[ 'aJsCritSpec' ][ 'body:@bodymovin\\.loadAnimation\\(\\s*{\\s*container\\s*:\\s*document\\.currentScript\\.parentNode\\W@' ] = true;

		//if( $itemScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="lottie-js-js"]' ) ) )
		//    $ctxProcess[ 'ndHead' ] -> appendChild( $itemScr );

		{
			$itemScr = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScr -> setAttribute( 'type', 'application/js' );
			$itemScr -> setAttribute( 'src', 'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js' );
			$itemScr -> setAttribute( 'id', 'lottie-js-lzl' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemScr );
		}

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, "svg.lottgen.js-lzl-ing:has(+ svg), .e-lottie__animation > svg:not(.lottgen) ~ * {\r\n\tdisplay: none !important;\r\n}" );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
		
		
		{
			/*
			
			
			
			



			*/
			$ctx -> aAniAppear[ '.elementor-widget-premium-lottie:not(.js-lzl-ed)' ] = 'function( e )
{
	if( !window.bodymovin )
		return;

	var ePlacehldr = e.querySelector( ".premium-lottie-animation" );
	if( !ePlacehldr )
		return;

	e.classList.add( "js-lzl-ed" );

	bodymovin.loadAnimation(
		{
			container: ePlacehldr,
			path: ePlacehldr.getAttribute( "data-lottie-url" ),
			renderer: ePlacehldr.getAttribute( "data-lottie-render" ),
			loop: ePlacehldr.getAttribute( "data-lottie-loop" ) === "true",
			autoplay: true
		}
	);

	ePlacehldr.removeAttribute( "data-lottie-url" );
	//delete data.source_json;
	//e.setAttribute( "data-settings", JSON.stringify( data ) );
}';
		}
	}
}

function _ProcessCont_Cp_nktrLott( $ctx, &$ctxProcess, $settFrm, $settCache, $settImg, $settCdn, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," nectar-lottie ")][@data-lottie-settings]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-lottie-settings' ), true );
		if( !$dataSett )
			continue;

		$dataFile = ($dataSett[ 'json_url' ]??null);
		if( !$dataFile )
			continue;

		$r = _ProcessCont_Cp_lottGen_AdjustItem( $ctx, $ctxProcess, $settFrm, $settCache, $settImg, $settCdn, $doc, $xpath, $item, 'svg', $dataFile );
		if( $r === false )
			return( false );

		if( !$r )
			continue;

		$dataSett[ 'json_url' ] = $dataFile;

		$item -> setAttribute( 'data-lottie-settings', @json_encode( $dataSett ) );

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@svg\\.lottgen@' ] = true;
		$ctxProcess[ 'aJsCritSpec' ][ 'id:@^lottie-js-lzl$@' ] = true;
		//$ctxProcess[ 'aJsCritSpec' ][ 'body:@bodymovin\\.loadAnimation\\(\\s*{\\s*container\\s*:\\s*document\\.currentScript\\.parentNode\\W@' ] = true;

		//if( $itemScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="lottie-js-js"]' ) ) )
		//    $ctxProcess[ 'ndHead' ] -> appendChild( $itemScr );

		{
			$itemScr = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScr -> setAttribute( 'type', 'application/js' );
			$itemScr -> setAttribute( 'src', 'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js' );
			$itemScr -> setAttribute( 'id', 'lottie-js-lzl' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemScr );
		}

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, "svg.lottgen.js-lzl-ing:has(+ svg), .e-lottie__animation > svg:not(.lottgen) ~ * {\r\n\tdisplay: none !important;\r\n}" );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
		
		
		{
			/*
			
			
			
			



			*/
			$ctx -> aAniAppear[ '.nectar-lottie:not(.js-lzl-ed)' ] = 'function( e )
{
	if( !window.bodymovin )
		return;

	e.classList.add( "js-lzl-ed" );

	var data; try { data = JSON.parse( e.getAttribute( "data-lottie-settings" ) ); } catch( err ) { }; if( !data ) data = {};
	bodymovin.loadAnimation(
		{
			container: e,
			path: data.json_url,
			renderer: "svg",
			loop: true,
			autoplay: true
		}
	);

	delete data.json_url;
	e.setAttribute( "data-lottie-settings", JSON.stringify( data ) );
}';
		}
	}
}

function _ProcessCont_Cp_elmsKitLott( $ctx, &$ctxProcess, $settFrm, $settCache, $settImg, $settCdn, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ekit_lottie ")][@data-renderer]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$renderer = $item -> getAttribute( 'data-renderer' );
		$dataFile = $item -> getAttribute( 'data-path' );
		if( !$dataFile )
			continue;

		$r = _ProcessCont_Cp_lottGen_AdjustItem( $ctx, $ctxProcess, $settFrm, $settCache, $settImg, $settCdn, $doc, $xpath, $item, $renderer, $dataFile );
		if( $r === false )
			return( false );

		if( !$r )
			continue;

		{
			/*
			
			
			
			



			 */

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			HtmlNd::SetValFromContent( $itemScript, str_replace( array( 'PRM_PATH', 'PRM_RENDERER', 'PRM_LOOP', 'PRM_AUTOPLAY' ), array( $dataFile, $renderer, $item -> getAttribute( 'data-loop' ), $item -> getAttribute( 'data-autoplay' ) ), "
			bodymovin.loadAnimation(
			{
			container: document.currentScript.parentNode,
			path: 'PRM_PATH',
			renderer: 'PRM_RENDERER',
			loop: PRM_LOOP,
			autoplay: PRM_AUTOPLAY,
			}
			);
			" ) );
			HtmlNd::InsertAfter( $item, $itemScript, null, true );
		}

		$item -> removeAttribute( 'data-path' );

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@svg\\.lottgen@' ] = true;

		$ctxProcess[ 'aJsCritSpec' ][ 'id:@^lottie-js$@' ] = true;
		$ctxProcess[ 'aJsCritSpec' ][ 'body:@bodymovin\\.loadAnimation\\(\\s*{\\s*container\\s*:\\s*document\\.currentScript\\.parentNode\\W@' ] = true;

		if( $itemScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="lottie-js"]' ) ) )
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemScr );

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, 'svg.lottgen.js-lzl-ing:has(+ svg) {
	display: none!important;
}' );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _ProcessCont_Cp_elmntrWdgtAvoShcs( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-avo-showcase ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," bg-img ")][@data-background]', $item ) as $itemBg )
		{
			$itemBg -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemBg -> getAttribute( 'style' ) ), array( 'background-image' => 'url("' . $itemBg -> getAttribute( 'data-background' ) . '")' ) ) ) );
			$itemBg -> removeAttribute( 'data-background' );
		}
	}
}

function _ProcessCont_Cp_elmntrShe( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$bDynamic = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-element ")][contains(concat(" ",normalize-space(@class)," ")," she-header-yes ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );

		if( Gen::GetArrField( $dataSett, array( 'transparent_header_show' ), '' ) != 'yes' )
			continue;

		$aTransparentOn = Gen::GetArrField( $dataSett, array( 'transparent_on' ), array() );
		if( count( $aTransparentOn ) == 3  )	// Suppose all 3 "desktop", "tablet", "mobile"
		{
			HtmlNd::AddRemoveAttrClass( $item, array( 'she-header-transparent-yes' ) );
			continue;
		}

		// /wp-content/plugins/sticky-header-effects-for-elementor/assets/js/she-header@ver-1.6.9.js: sheHeader()
		static $g_aTransparentOnWidth = array( 'desktop' => array( 1025, 2147483647 ), 'tablet' => array( 768, 1024 ), 'mobile' => array( 0, 767 ) );

		$aStickyOn = array();
		foreach( $aTransparentOn as $stickyOnViewId )
			if( isset( $g_aTransparentOnWidth[ $stickyOnViewId ] ) )
				$aStickyOn[] = $g_aTransparentOnWidth[ $stickyOnViewId ];

		$item -> setAttribute( 'data-lzl-trnsp-widths', @json_encode( $aStickyOn ) );

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_elmntrShe_calcSizes(document.currentScript.parentNode);' );
			$item -> appendChild( $itemScript );
		}

		$bDynamic = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $bDynamic )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.she-header-transparent-yes@' ] = true;

		{
			/*
			
			
			
			



			 */

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "
			function seraph_accel_cp_elmntrShe_calcSizes( e )
			{
			var bInWidth = false;
			var dataWidths; try { dataWidths = JSON.parse( e.getAttribute( \"data-lzl-trnsp-widths\" ) ); } catch( err ) {}; if( !dataWidths ) dataWidths = [];
			for( var w in dataWidths )
			{
			if( window.innerWidth >= dataWidths[ w ][ 0 ] && window.innerWidth <= dataWidths[ w ][ 1 ] )
			{
			bInWidth = true;
			break;
			}
			}

			if( bInWidth )
			e.classList.add( \"she-header-transparent-yes\" );
			else
			e.classList.remove( \"she-header-transparent-yes\" );
			}

			(
			function( d )
			{
			function OnEvt( evt )
			{
			d.querySelectorAll( \".elementor-element.she-header-yes[data-lzl-trnsp-widths]\" ).forEach(
			function( e )
			{
			seraph_accel_cp_elmntrShe_calcSizes( e );
			}
			);
			}

			d.addEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
			seraph_accel_lzl_bjs.add( function() { d.removeEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } ); } );
			}
			)( document );
			" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

function _ProcessCont_Cp_elmntrPremCrsl( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	$idNext = 0;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," premium-carousel-wrapper ")][@data-settings]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
		if( !$dataSett )
			continue;

		$sld = _SlickSld_PrepareCont( $ctx, $doc, $xpath, HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," premium-carousel-inner ")]', $item ) ), 'premium-carousel-template', ($dataSett[ 'dots' ]??null) || ($dataSett[ 'arrows' ]??null) );
		if( !$sld )
			continue;

		HtmlNd::AddRemoveAttrClass( $item, '', array( 'premium-carousel-hidden' ) );

		$selId = $item -> getAttribute( 'id' );
		if( $selId )
			$selId = '#' . $selId;
		else
		{
			$selId = 'lzl-' . $idNext++;
			HtmlNd::AddRemoveAttrClass( $item, array( $selId ) );
			$selId = '.' . $selId;
		}

		$aViews = array( 'slidesMob' => ( int )($dataSett[ 'mobileBreak' ]??null) - 1, 'slidesTab' => ( int )($dataSett[ 'tabletBreak' ]??null) - 1, isset( $dataSett[ 'slidesDesk' ] ) ? 'slidesDesk' : 'slidesToShow' => null );

		$itemStyleCont = '';
		$maxWidthPrev = null;
		foreach( $aViews as $optId => $maxWidth )
		{
			$nShow = ( int )Gen::GetArrField( $dataSett, array( $optId ) );
			if( !$nShow )
				continue;

			if( $maxWidth <= 0 )
				$maxWidth = null;

			if( $maxWidthPrev || $maxWidth )
				$itemStyleCont .= '@media ' . ( $maxWidthPrev ? ( '(min-width: ' . ( $maxWidthPrev + 1 ) . 'px)' ) : '' ) . ( $maxWidthPrev && $maxWidth ? ' and ' : '' ) . ( $maxWidth ? ( '(max-width: ' . $maxWidth . 'px)' ) : '' ) . ' {' . "\n";

			$itemStyleCont .= '.premium-carousel-wrapper' . $selId . ' .premium-carousel-inner:not(.slick-initialized)' . ( $sld -> bSimpleCont ? '' : ' ' ) . '.lzl-c > * {width: calc(100% / ' . $nShow . ');}' . "\n";
			$itemStyleCont .= '.premium-carousel-wrapper' . $selId . ' .premium-carousel-inner:not(.slick-initialized)' . ( $sld -> bSimpleCont ? '' : ' ' ) . '.lzl-c > *:nth-child(n+' . ( $nShow + 1 ) . ') {visibility:hidden!important;}' . "\n";

			// Navigation
			{
				$nDots = _SlickSld_GetDotsCount( array( 'slideCount' => $sld -> nSlides, 'slidesToShow' => $nShow, 'slidesToScroll' => ( int )($dataSett[ 'slidesToScroll' ]??null), 'infinite' => ( bool )($dataSett[ 'infinite' ]??null), 'centerMode' => ( bool )($dataSett[ 'centerMode' ]??null), 'asNavFor' => false ) );
				$itemStyleCont .= '.premium-carousel-wrapper' . $selId . ' .premium-carousel-inner:not(.slick-initialized) .slick-dots' . ( $nDots ? ' > *:nth-child(n+' . ( $nDots + 1 ) . ')' : '' ) . ' {display:none;}' . "\n";
			}

			if( $maxWidthPrev || $maxWidth )
				$itemStyleCont .= '}' . "\n";

			if( $maxWidth )
				$maxWidthPrev = $maxWidth;
		}

		{
			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
			$item -> parentNode -> insertBefore( $itemStyle, $item );
		}

		if( ($dataSett[ 'arrows' ]??null) )
		{
			if( $itemPrev = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," premium-carousel-nav-arrow-prev ")]/*[1]', $item ) ) )
			{
				$itemPrev = $itemPrev -> cloneNode( true );
				HtmlNd::AddRemoveAttrClass( $itemPrev, array( 'slick-arrow' ) );
				$sld -> itemSlides -> insertBefore( $itemPrev, $sld -> itemSlides -> firstChild );
			}

			if( $itemNext = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," premium-carousel-nav-arrow-next ")]/*[1]', $item ) ) )
			{
				$itemNext = $itemNext -> cloneNode( true );
				HtmlNd::AddRemoveAttrClass( $itemNext, array( 'slick-arrow' ) );
				$sld -> itemSlides -> appendChild( $itemNext );
			}
		}

		if( ($dataSett[ 'dots' ]??null) )
		{
			if( ($dataSett[ 'carouselNavigation' ]??null) == 'dots' )
				$sld -> dotTpl = ( string )HtmlNd::DeParse( HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," premium-carousel-nav-dot ")]', $item ) ), false );

			if( !$sld -> dotTpl )
				$sld -> dotTpl = 'X';

			_SlickSld_AddDots( $doc, $sld -> itemSlides, 'slick-dots', $sld -> nSlides, function( $sld, $i ) { return( '<li role="presentation">' . $sld -> dotTpl . '</li>' ); }, $sld );
		}

		$adjusted = true;
	}

	if( $adjusted && ( $ctxProcess[ 'mode' ] & 1 ) )
	{
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, _SlickSld_GetGlobStyle( '.premium-carousel-inner', 'premium-carousel-template' ) . '' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		_SlickSld_InitGlob( $ctx, $ctxProcess, $doc, '.premium-carousel-inner' );
	}
}

function _ProcessCont_Cp_elmntrWdgtImgCrsl( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-image-carousel ")][@data-settings]|.//*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-n-carousel ")][@data-settings]|.//*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-loop-carousel ")][@data-settings]|.//*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-media-carousel ")][@data-settings]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( $ctx -> cfgElmntrFrontend === null )
			$ctx -> cfgElmntrFrontend = _Elmntr_GetFrontendCfg( $xpath );

		$itemCssSel = '.elementor-element-' . $item -> getAttribute( 'data-id' );

		$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
		$itemStyleCont = '';

		foreach( array( array( 'type' => '', 'widthAlign' => 767, 'cxMin' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'desktop', 'cxMin' ), 0 ) ), array( 'type' => '_tablet', 'widthAlign' => 767, 'cxMin' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'tablet', 'cxMin' ), 0 ), 'cxMax' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'tablet', 'cxMax' ), 0 ) ), array( 'type' => '_mobile', 'widthAlign' => 766, 'cxMax' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'mobile', 'cxMax' ), 0 ) ) ) as $view )
		{
			$nSlidesShow = ( int )Gen::GetArrField( $dataSett, array( 'slides_to_show' . $view[ 'type' ] ) );
			if( !$nSlidesShow )
				$nSlidesShow = ( int )Gen::GetArrField( $dataSett, array( 'slides_to_show' ) );

			$sImageSpacingCustom = ( string )Gen::GetArrField( $dataSett, array( 'image_spacing_custom' . $view[ 'type' ], 'size' ) );
			if( strlen( $sImageSpacingCustom ) )
				$sImageSpacingCustom .= ( string )Gen::GetArrField( $dataSett, array( 'image_spacing_custom' . $view[ 'type' ], 'unit' ) );
			else
				$sImageSpacingCustom = ( string )Gen::GetArrField( $dataSett, array( 'image_spacing_custom', 'size' ) ) . ( string )Gen::GetArrField( $dataSett, array( 'image_spacing_custom', 'unit' ) );

			if( !strlen( $sImageSpacingCustom ) )
				$sImageSpacingCustom = '0';

			if( isset( $view[ 'cxMax' ] ) )
				$itemStyleCont .= '@media (max-width: ' . $view[ 'cxMax' ] . 'px) {';
			$itemStyleCont .= '
					' . $itemCssSel . ' .swiper:not(.swiper-initialized) .swiper-slide, ' . $itemCssSel . ' .swiper-container:not(.swiper-container-initialized) .swiper-slide
					{
						width: calc((100% - (' . ( $nSlidesShow - 1 ) . ')*' . $sImageSpacingCustom . ')/' . $nSlidesShow . ');
						margin-right: ' . $sImageSpacingCustom . ';
					}
				';

			if( isset( $view[ 'cxMax' ] ) )
				$itemStyleCont .= '}';
		}

		$itemStyleCont .= '
				' . $itemCssSel . ' .swiper:not(.swiper-initialized) > .swiper-wrapper, ' . $itemCssSel . ' .swiper-container:not(.swiper-container-initialized) > .swiper-wrapper
				{
					gap: 0;
				}
			';

		if( ( $ctxProcess[ 'mode' ] & 1 ) && $itemStyleCont )
		{
			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
			$item -> parentNode -> insertBefore( $itemStyle, $item );
		}
	}
}

function _ProcessCont_Cp_elmntrWdgtCntr( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-counter ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		/*
		
		
		
		



		*/

		$ctx -> aAniAppear[ '.elementor-widget-counter:not(.lzl-cntr-ad)' ] = 'function( e )
{
	var eCntr = e.querySelector( ".elementor-counter-number" );
	if( !eCntr )
		return;

	e.classList.add( "lzl-cntr-ad" );

	const nTick = 10;

	var nDur = parseInt( eCntr.getAttribute( "data-duration" ), 10 );
	var nFrom = parseInt( eCntr.getAttribute( "data-from-value" ), 10 );
	var nTo = parseInt( eCntr.getAttribute( "data-to-value" ), 10 );
	var sDelim = eCntr.getAttribute( "data-delimiter" );

	eCntr.setAttribute( "data-from-value", nTo );
	eCntr.setAttribute( "data-duration", 0 );

	var nStep = ( nTo - nFrom ) / ( nDur / nTick );

	var idTmr = setInterval(
		function()
		{
			nFrom = nFrom + nStep;
			if( nFrom >= nTo )
			{
				nFrom = nTo;
				clearInterval( idTmr );
			}

			var sV = "" + Math.round( nFrom );
			if( sDelim )
				sV = sV.replace( /\B(?=(\d{3})+(?!\d))/g, sDelim );

			eCntr.textContent = sV;
		}
	, nTick );
}';
	}
}

function _ProcessCont_Cp_elmntrWdgtCntdwn( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-countdown ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$adjusted = true;

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_elmntrWdgtCntdwn_Init(document.currentScript.parentNode);document.currentScript.parentNode.removeChild(document.currentScript)' );
			$item -> appendChild( $itemScript );
		}
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		// /wp-content/_HtmlFullProcess_Temp/feelkalm.com%5Eshop%5Emindhack/wp-content/plugins/elementor-pro/assets/js/countdown.6e87ca40d36793d92aea.bundle.js

		/*
		
		
		
		



		*/

		$itemScript = $doc -> createElement( 'script' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$itemScript -> setAttribute( 'type', 'text/javascript' );
		$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
		HtmlNd::SetValFromContent( $itemScript, "function seraph_accel_cp_elmntrWdgtCntdwn_Init( e )
{
	e.classList.add( \"lzl-cntr-ed\" );

	var eCntr = e.querySelector( \".elementor-countdown-wrapper\" );
	if( !eCntr )
		return;

	function _update()
	{
		function _setTime( e, tm )
		{
			function _setDigit( e, v )
			{
				if( !e )
					return;
				v = parseInt( v, 10 );
				e.innerText = v < 10 ? \"0\" + v : v;
			}

			_setDigit( e.querySelector( \".elementor-countdown-digits.elementor-countdown-days\" ), ( tm / 60 / 60 / 24 ) % 365 );
			_setDigit( e.querySelector( \".elementor-countdown-digits.elementor-countdown-hours\" ), ( tm / 60 / 60 ) % 24 );
			_setDigit( e.querySelector( \".elementor-countdown-digits.elementor-countdown-minutes\" ), ( tm / 60 ) % 60 );
			_setDigit( e.querySelector( \".elementor-countdown-digits.elementor-countdown-seconds\" ), tm % 60 );
		}

		var tmNow = Date.now();
		if( tmEnd <= tmNow )
		{
			_setTime( eCntr, 0 );
			clearInterval( idTmr );
		}
		else
		{
			var tmDiff = Math.round( ( tmEnd - tmNow ) / 1000 );
			_setTime( eCntr, tmDiff );
		}
	}

	function _getEvergreenDate( eCntr, interval )
	{
		const id = eCntr.getAttribute( \"data-id\" ),
		dueDateKey = id + '-evergreen_due_date',
		intervalKey = id + '-evergreen_interval',

		localData =
		{
			dueDate: localStorage.getItem( dueDateKey ),
			interval: localStorage.getItem( intervalKey )
		},

		initEvergreen = function()
		{
			var evergreenDueDate = new Date();
			var endTime = evergreenDueDate.setSeconds(evergreenDueDate.getSeconds() + interval);
			localStorage.setItem( dueDateKey, endTime );
			localStorage.setItem( intervalKey, interval );
			return( endTime );
		};

		if( null === localData.dueDate && null === localData.interval )
			return( initEvergreen() );
		if( null !== localData.dueDate && interval !== parseInt( localData.interval, 10 ) )
			return( initEvergreen() );

		if( localData.dueDate > 0 && parseInt( localData.interval, 10 ) === interval )
			return( localData.dueDate );
	}
	
	var tmEnd = parseInt( eCntr.getAttribute( \"data-date\" ), 10 ) * 1000;
	var tmEndEg = parseInt( eCntr.getAttribute( \"data-evergreen-interval\" ), 10 );
	if( tmEndEg > 0 )
		tmEnd = _getEvergreenDate( eCntr, tmEndEg );
	var tmEnd = new Date( tmEnd );

	_update();
	var idTmr = setInterval( _update, 1000 );
	seraph_accel_lzl_bjs.add( function() { clearInterval( idTmr ); } );
}

(
	function( d )
	{
		function onEvt()
		{
			d.querySelectorAll( \".elementor-widget-countdown:not(.lzl-cntr-ed)\" ).forEach(
				function( e )
				{
					seraph_accel_cp_elmntrWdgtCntdwn_Init( e );
				}
			);
		}

		d.addEventListener( \"seraph_accel_freshPartsDone\", onEvt, { capture: true, passive: true } );
	}
)( document );
" );
		$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
	}
}

function _ProcessCont_Cp_elmntrWdgtEaelCntdwn( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-eael-countdown ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$adjusted = true;

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_elmntrWdgtEaelCntdwn_Init(document.currentScript.parentNode);document.currentScript.parentNode.removeChild(document.currentScript)' );
			$item -> appendChild( $itemScript );
		}
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		// /wp-content/uploads/essential-addons-elementor/eael-12@ver-1741986413.js

		/*
		
		
		
		



		*/

		$itemScript = $doc -> createElement( 'script' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$itemScript -> setAttribute( 'type', 'text/javascript' );
		$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
		HtmlNd::SetValFromContent( $itemScript, "function seraph_accel_cp_elmntrWdgtEaelCntdwn_Init( e )
{
	e.classList.add( \"lzl-cntr-ed\" );

	var eCntr = e.querySelector( \".eael-countdown-items\" );
	if( !eCntr )
		return;

	function _update()
	{
		function _setTime( e, tm )
		{
			function _setDigit( e, v )
			{
				v = parseInt( v, 10 );
				e.innerText = v < 10 ? \"0\" + v : v;
			}

			_setDigit( e.querySelector( \".eael-countdown-digits[data-hours]\" ), ( tm / 60 / 60 ) % 24 );
			_setDigit( e.querySelector( \".eael-countdown-digits[data-minutes]\" ), ( tm / 60 ) % 60 );
			_setDigit( e.querySelector( \".eael-countdown-digits[data-seconds]\" ), tm % 60 );
		}

		var tmNow = Date.now();
		if( tmEnd <= tmNow )
		{
			_setTime( eCntr, 0 );
			clearInterval( idTmr );

			var eCntrWrp = e.querySelector( \".eael-countdown-wrapper\" );
			if( eCntrWrp )
			{
				var eFinal = e.querySelector( \"#eael-countdown-\" + eCntrWrp.getAttribute( \"data-countdown-id\" ) );
				if( eFinal )
				{
					switch( eCntrWrp.getAttribute( \"data-expire-type\" ) )
					{
					case \"text\":
						eFinal.innerHTML = '<div class=\"eael-countdown-finish-message\"><h4 class=\"expiry-title\">' + eCntrWrp.getAttribute( \"data-expiry-title\" ) + '</h4><div class=\"eael-countdown-finish-text\">' + eCntrWrp.getAttribute( \"data-expiry-text\" ) + \"</div></div>\";
						break;

					case \"template\":
						var sTpl = eCntrWrp.querySelector( \".eael-countdown-expiry-template\" );
						if( sTpl )
							sTpl = sTpl.innerHTML;
						else
							sTpl = \"\";
						eFinal.innerHTML = sTpl;
						break;
					}
				}
			}
		}
		else
		{
			var tmDiff = Math.round( ( tmEnd - tmNow ) / 1000 );
			_setTime( eCntr, tmDiff );
		}
	}
	
	var tmEnd = new Date( eCntr.getAttribute( \"data-date\" ) );

	_update();
	var idTmr = setInterval( _update, 1000 );
	seraph_accel_lzl_bjs.add( function() { clearInterval( idTmr ); } );
}

(
	function( d )
	{
		function onEvt()
		{
			d.querySelectorAll( \".elementor-widget-eael-countdown:not(.lzl-cntr-ed)\" ).forEach(
				function( e )
				{
					seraph_accel_cp_elmntrWdgtEaelCntdwn_Init( e );
				}
			);
		}

		d.addEventListener( \"seraph_accel_freshPartsDone\", onEvt, { capture: true, passive: true } );
	}
)( document );
" );
		$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
	}
}

function _ProcessCont_Cp_elmntrWdgtTmFnfctCntr( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-tm-ele-funfact-counter ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		/*
		
		
		
		



		*/

		$ctx -> aAniAppear[ '.elementor-widget-tm-ele-funfact-counter:not(.lzl-cntr-ad)' ] = 'function( e )
{
	var eCntr = e.querySelector( ".animate-number" );
	if( !eCntr )
		return;

	e.classList.add( "lzl-cntr-ad" );

	const startValue = parseInt( eCntr.textContent, 10 ) || 0;
	const endValue = parseInt( eCntr.dataset.value, 10 ) || 0;
	const duration = parseInt( eCntr.dataset.animationDuration, 10 ) || 1000;

	function animateCount( element, start, end, duration )
	{
		const startTime = performance.now();

		function update( currentTime )
		{
			const elapsed = currentTime - startTime;
			const progress = Math.min( elapsed / duration, 1 );

			const value = Math.floor( start + ( end - start ) * progress );
			element.textContent = value;

			if( progress < 1 )
				requestAnimationFrame( update );
		}

		requestAnimationFrame( update );
	}

	animateCount( eCntr, startValue, endValue, duration );
}';
	}
}

function _ProcessCont_Cp_elmntrStrtch( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-section ")][contains(concat(" ",normalize-space(@class)," ")," elementor-section-stretched ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		HtmlNd::AddRemoveAttrClass( $item -> parentNode, array( 'lzl-strtch-owner' ) );

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		{
			/*
			
			
			
			



			*/

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "(
	function( d )
	{
		function OnEvt( evt )
		{
			var bRtl = d.documentElement.getAttribute( \"dir\" ) == \"rtl\";
			d.querySelectorAll( \".lzl-strtch-owner\" ).forEach(
				function( e )
				{
					var eRc = e.getBoundingClientRect();
					e.style.setProperty( \"--lzl-strtch-offs-x\", \"\" + ( bRtl ? ( d.documentElement.clientWidth - eRc.right ) : eRc.left ) + \"px\" );
				}
			);
		}

		d.addEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
	}
)( document );
" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

// #######################################################################

function _SlickSld_PrepareCont( $ctx, $doc, $xpath, $itemSlides, $classSlide, $bInsideCtls = false )
{
	if( !$itemSlides )
		return( null );

	$sld = new AnyObj();
	$sld -> itemSlides = $itemSlides;

	//$sld -> bSimpleCont = !$bInsideCtls;
	//if( $sld -> bSimpleCont && HtmlNd::FirstOfChildren( $xpath -> query( './*[not(self::node()[contains(concat(" ",normalize-space(@class)," ")," ' . $classSlide . ' ")])]', $itemSlides ) ) )
		$sld -> bSimpleCont = false;

	if( !$sld -> bSimpleCont )
	{
		$sld -> itemSlides = $itemSlides -> cloneNode( false );
		HtmlNd::AddRemoveAttrClass( $sld -> itemSlides, array( 'js-lzl-ing' ) );
		$itemSlidesContTmp = HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'slick-track', 'lzl-c' ) ) );
	}
	else
		HtmlNd::AddRemoveAttrClass( $itemSlides, array( 'lzl-c' ) );

	$sld -> nSlides = 0;
	foreach( $xpath -> query( './*', $itemSlides ) as $itemSlide )
	{
		if( !in_array( $classSlide, HtmlNd::GetAttrClass( $itemSlide ) ) )
		{
			if( !$sld -> bSimpleCont )
				$sld -> itemSlides -> appendChild( $itemSlide -> cloneNode( true ) );
			continue;
		}

		$sld -> nSlides++;

		if( !$sld -> bSimpleCont )
		{
			$itemSlide = $itemSlide -> cloneNode( true );
			$itemSlidesContTmp -> appendChild( $itemSlide );
		}

		if( $sld -> nSlides == 1 )
			HtmlNd::AddRemoveAttrClass( $itemSlide, array( 'slick-current' ) );
	}

	if( !$sld -> nSlides )
		return( null );

	if( !$sld -> bSimpleCont )
	{
		//{
		//    $itemScript = $doc -> createElement( 'script' );
		//    if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
		//        $itemScript -> setAttribute( 'type', 'text/javascript' );
		//    $itemScript -> setAttribute( 'seraph-accel-crit', '1' );
		//    HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_slikInit(window,document.currentScript.parentNode);' );
		//    $itemSlides -> appendChild( $itemScript );
		//}

		$sld -> itemSlides -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'slick-list' ) ), array( $itemSlidesContTmp ) ) );
		$itemSlides -> parentNode -> appendChild( $sld -> itemSlides );

		//if( 0 )
		{
			$itemNoScript = $doc -> createElement( 'noscript' );
			$itemNoScript -> setAttribute( 'data-lzl-bjs', '' );
			$itemSlides -> parentNode -> insertBefore( $itemNoScript, $itemSlides );
			$itemNoScript -> appendChild( $itemSlides );
			ContNoScriptItemClear( $itemSlides );

			$ctx -> bBjs = true;
		}
	}

	HtmlNd::AddRemoveAttrClass( $sld -> itemSlides, array( 'slick-slider' ) );

	return( $sld );
}

function _SlickSld_GetGlobStyle( $selSlides, $classSlide )
{
	return( '' . $selSlides . ':not(.slick-initialized).lzl-c, ' . $selSlides . ':not(.slick-initialized) .lzl-c {
	flex-wrap: nowrap;
	display: flex;
}
' . $selSlides . ':not(.slick-initialized).lzl-c > *, ' . $selSlides . ':not(.slick-initialized) .lzl-c > * {
	flex-shrink: 0;
}
' . $selSlides . ':not(.slick-initialized):not(.lzl-c):not(.js-lzl-ing),
' . $selSlides . '.slick-initialized + ' . $selSlides . '.js-lzl-ing,
' . $selSlides . '.slick-initialized.js-lzl-ing {
	display: none !important;
}' );
}

function _SlickSld_InitGlob( $ctx, &$ctxProcess, $doc, $selSlides )
{
	/*
	
	
	
	



	*/

	$itemScript = $doc -> createElement( 'script' );
	if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
		$itemScript -> setAttribute( 'type', 'text/javascript' );
	$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
	HtmlNd::SetValFromContent( $itemScript, str_replace( '_PRM_SELSLIDES_', $selSlides, "
		(
			function( d, w )
			{
				seraph_accel_lzl_bjs.add(
					function()
					{
						d.querySelectorAll( \"_PRM_SELSLIDES_:not(.slick-initialized):not(.lzl-c):not(.js-lzl-ing)\" ).forEach(
							function( e )
							{
								if( !w.MutationObserver )
									return;

								function OnCheckChange()
								{
									if( !e.obsedgvsd )
										return;

									if( !e.classList.contains( \"slick-initialized\" ) )
										return;

									e.obsedgvsd.disconnect();
									delete e.obsedgvsd;

									if( e.slick )
										e.slick.refresh();
								}

								e.obsedgvsd = new w.MutationObserver( OnCheckChange );
								e.obsedgvsd.observe( e, { attributes: true, attributeFilter: [ \"class\" ] } );
							}
						);
					}
				, 110 );
			}
		)( document, window );
	" ) );
	$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
}

function _SlickSld_AddDots( $doc, $itemSlides, $class, $n, $cbItemTpl, $cbCtx = null )
{
	HtmlNd::AddRemoveAttrClass( $itemSlides, array( 'slick-dotted' ) );

	$itemCtl = HtmlNd::ParseAndImport( $doc, '<ul class="' . $class . '" role="tablist"></ul>' );
	if( !$itemCtl )
		return;

	for( $i = 0; $i < $n; $i++ )
	{
		$itemDot = HtmlNd::ParseAndImport( $doc, ( string )call_user_func( $cbItemTpl, $cbCtx, $i ) );
		if( !$itemDot )
			return;

		if( !$i )
			HtmlNd::AddRemoveAttrClass( $itemDot, array( 'slick-active' ) );
		$itemCtl -> appendChild( $itemDot );
	}

	$itemSlides -> appendChild( $itemCtl );
}

function _SlickSld_GetDotsCount( $aPrm = array( 'slideCount' => 1, 'slidesToShow' => 1, 'slidesToScroll' => 1, 'infinite' => false, 'centerMode' => false, 'asNavFor' => false ) )
{
	$e = 0;
	$t = 0;
	$o = 0;

	if( !$aPrm[ 'slidesToScroll' ] )
		$aPrm[ 'slidesToScroll' ] = 1;
	if( $aPrm[ 'slidesToScroll' ] > $aPrm[ 'slidesToShow' ] )
		$aPrm[ 'slidesToScroll' ] = $aPrm[ 'slidesToShow' ];

    if( $aPrm[ 'infinite' ] )
	{
        if( $aPrm[ 'slideCount' ] <= $aPrm[ 'slidesToShow' ] )
            ++$o;
        else
		{
            for( ; $e < $aPrm[ 'slideCount' ]; )
			{
                ++$o;
                $e = $t + $aPrm[ 'slidesToScroll' ];
                $t += $aPrm[ 'slidesToScroll' ] <= $aPrm[ 'slidesToShow' ] ? $aPrm[ 'slidesToScroll' ] : $aPrm[ 'slidesToShow' ];
			}
		}
	}
    else if( $aPrm[ 'centerMode' ] )
	{
        $o = $aPrm[ 'slideCount' ];
	}
    else if( $aPrm[ 'asNavFor' ] )
	{
        for( ; $e < $aPrm[ 'slideCount' ]; )
		{
            ++$o;
            $e = $t + $aPrm[ 'slidesToScroll' ];
            $t += $aPrm[ 'slidesToScroll' ] <= $aPrm[ 'slidesToShow' ] ? $aPrm[ 'slidesToScroll' ] : $aPrm[ 'slidesToShow' ];
		}
	}
    else
	{
        $o = 1 + ceil( ( $aPrm[ 'slideCount' ] - $aPrm[ 'slidesToShow' ] ) / $aPrm[ 'slidesToScroll' ] );
	}

	return( $o > 1 ? $o : 0 );
}

// #######################################################################

function _Elmntr_GetFrontendCfg( $xpath )
{
	$raw = _Elmntr_GetFrontendCfgEx( HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="elementor-frontend-js-before"]' ) ) );

	$prms = array(
		'views' => array(
			'mobile' => array(
				'cxMin' => 0,
				'cxMax' => ( Gen::GetArrField( $raw, array( 'views', 'mobile' ), 0 ) - 1 ),
			),

			'tablet' => array(
				'cxMin' => Gen::GetArrField( $raw, array( 'views', 'mobile' ), 0 ),
				'cxMax' => ( Gen::GetArrField( $raw, array( 'views', 'tablet' ), 0 ) - 1 ),
			),

			'desktop' => array(
				'cxMin' => Gen::GetArrField( $raw, array( 'views', 'tablet' ), 0 ),
				'cxMax' => 2147483647,
			)
		)
	);

	return( $prms );
}

function _Elmntr_GetFrontendCfgEx( $itemInitCmnScr )
{
	if( !$itemInitCmnScr )
		return( null );

	$m = array();
	if( !preg_match( '@\\WelementorFrontendConfig\\s*\\=\\s*@', $itemInitCmnScr -> nodeValue, $m, PREG_OFFSET_CAPTURE ) )
		return( null );

	$posStart = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
	$pos = Gen::JsonGetEndPos( $posStart, $itemInitCmnScr -> nodeValue );
	if( $pos === null )
		return;

	$prms = @json_decode( Gen::JsObjDecl2Json( substr( $itemInitCmnScr -> nodeValue, $posStart, $pos - $posStart ) ), true );
	if( !$prms )
		return( null );

	foreach( array( 'mobile' => 767, 'tablet' => 1024 ) as $k => $def )
	{
		$nMax = Gen::GetArrField( $prms, array( 'responsive', 'breakpoints', $k, 'value' ), 0 );
		if( !$nMax )
			$nMax = Gen::GetArrField( $prms, array( 'responsive', 'breakpoints', $k, 'default_value' ), 0 );
		if( !$nMax )
			$nMax = $def;
		$prms[ 'views' ][ $k ] = $nMax;
	}

	return( $prms );
}

// #######################################################################
// #######################################################################

?>