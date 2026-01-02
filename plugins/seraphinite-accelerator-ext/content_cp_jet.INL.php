<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_jetMobMenu( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," jet-mobile-menu ")][@data-menu-options]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-menu-options' ), true );
		$itemToggleClosedIcon = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," jet-mobile-menu__refs ")]/*[@ref="toggleClosedIcon"]', $item ) );
		if( !$itemToggleClosedIcon )
			continue;

		$toggleText = Gen::GetArrField( $dataSett, array( 'toggleText' ), '' );

		$itemToggle = HtmlNd::Parse( '<div class="jet-mobile-menu__instance jet-mobile-menu__instance--' . Gen::GetArrField( $dataSett, array( 'menuLayout' ), '' ) . '-layout ' . Gen::GetArrField( $dataSett, array( 'menuPosition' ), '' ) . '-container-position ' . Gen::GetArrField( $dataSett, array( 'togglePosition' ), '' ) . '-toggle-position js-lzl-ing"><div tabindex="1" class="jet-mobile-menu__toggle"><div class="jet-mobile-menu__toggle-icon">' . HtmlNd::DeParse( $itemToggleClosedIcon, false ) . '</div>' . ( $toggleText ? '<span class="jet-mobile-menu__toggle-text">' . $toggleText . '</span>' : '' ) . '</div></div>' );
		if( $itemToggle && $itemToggle -> firstChild )
			if( $itemToggle = $doc -> importNode( $itemToggle -> firstChild, true ) )
			{
				$item -> insertBefore( $itemToggle, $item -> firstChild );
				$adjusted = true;
			}
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$itemsCmnStyle = $doc -> createElement( 'style' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
		HtmlNd::SetValFromContent( $itemsCmnStyle, 'body:not(.seraph-accel-js-lzl-ing) .jet-mobile-menu__instance.js-lzl-ing{display:none!important;}body.seraph-accel-js-lzl-ing .jet-mobile-menu__instance:not(.js-lzl-ing){display:none!important;}' );
		$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
	}
}

function _ProcessCont_Cp_jetCrsl( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	_ProcessCont_Cp__jetCrsl( $ctx, $ctxProcess, $settFrm, $doc, $xpath, array( 'item' => 'elementor-widget-jet-carousel', 'itemSlk' => 'elementor-slick-slider', 'itemSlkChld' => 'jet-carousel__item', 'settViewPrefix' => 'slides_to_show' ) );
}

function _ProcessCont_Cp_jetCrslPst( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	_ProcessCont_Cp__jetCrsl( $ctx, $ctxProcess, $settFrm, $doc, $xpath, array( 'item' => 'elementor-widget-jet-posts', 'itemSlk' => 'jet-posts', 'itemSlkChld' => 'jet-posts__item', 'settViewPrefix' => 'columns' ) );
}

function _ProcessCont_Cp__jetCrsl( $ctx, &$ctxProcess, $settFrm, $doc, $xpath, array $aPrm )
{
	$adjusted = false;
	$idNext = 0;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ' . $aPrm[ 'item' ] . ' ")][@data-settings]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
		if( !$dataSett )
			continue;

		if( !( $itemJet = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," jet-carousel ")][@data-slider_options]', $item ) ) ) )
			continue;

		$dataSettJet = @json_decode( $itemJet -> getAttribute( 'data-slider_options' ), true );
		if( !$dataSettJet )
			continue;

		$sld = _SlickSld_PrepareCont( $ctx, $doc, $xpath, HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ' . $aPrm[ 'itemSlk' ] . ' ")]', $itemJet ) ), $aPrm[ 'itemSlkChld' ], ($dataSettJet[ 'dots' ]??null) );
		if( !$sld )
			continue;

		if( $ctx -> cfgElmntrFrontend === null )
			$ctx -> cfgElmntrFrontend = _Elmntr_GetFrontendCfg( $xpath );

		$classId = 'lzl-' . $idNext++;

		HtmlNd::AddRemoveAttrClass( $itemJet, array( $classId ) );

		$aViews = Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views' ), array() );

		$itemStyleCont = '';
		$maxWidthPrev = null;
		foreach( $aViews as $viewId => $view )
		{
			$nShow = ( int )Gen::GetArrField( $dataSett, array( $aPrm[ 'settViewPrefix' ] . ( $viewId == 'desktop' ? '' : ( '_' . $viewId ) ) ) );
			if( !$nShow )
				continue;

			$maxWidth = $view[ 'cxMax' ];
			if( $maxWidth == 2147483647 )
				$maxWidth = null;

			if( $maxWidthPrev || $maxWidth )
				$itemStyleCont .= '@media ' . ( $maxWidthPrev ? ( '(min-width: ' . ( $maxWidthPrev + 1 ) . 'px)' ) : '' ) . ( $maxWidthPrev && $maxWidth ? ' and ' : '' ) . ( $maxWidth ? ( '(max-width: ' . $maxWidth . 'px)' ) : '' ) . ' {' . "\n";

			$itemStyleCont .= '.jet-carousel.' . $classId . ' .' . $aPrm[ 'itemSlk' ] . ':not(.slick-initialized)' . ( $sld -> bSimpleCont ? '' : ' ' ) . '.lzl-c > * {width: calc(100% / ' . $nShow . ');}' . "\n";
			$itemStyleCont .= '.jet-carousel.' . $classId . ' .' . $aPrm[ 'itemSlk' ] . ':not(.slick-initialized)' . ( $sld -> bSimpleCont ? '' : ' ' ) . '.lzl-c > *:nth-child(n+' . ( $nShow + 1 ) . ') {visibility:hidden!important;}' . "\n";

			// Navigation
			{
				$nDots = _SlickSld_GetDotsCount( array( 'slideCount' => $sld -> nSlides, 'slidesToShow' => $nShow, 'slidesToScroll' => ( int )($dataSettJet[ 'slidesToScroll' ]??null), 'infinite' => ( bool )($dataSettJet[ 'infinite' ]??null), 'centerMode' => false, 'asNavFor' => false ) );
				$itemStyleCont .= '.jet-carousel.' . $classId . ' .' . $aPrm[ 'itemSlk' ] . ':not(.slick-initialized) .jet-slick-dots' . ( $nDots ? ' > *:nth-child(n+' . ( $nDots + 1 ) . ')' : '' ) . ' {display:none;}' . "\n";
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

		//$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'overflow' => 'hidden' ) ) ) );

		if( ($dataSettJet[ 'dots' ]??null) )
			_SlickSld_AddDots( $doc, $sld -> itemSlides, 'jet-slick-dots', $sld -> nSlides, function( $sld, $i ) { return( '<li tabindex="0"><span>' . ( $i + 1 ) . '</span></li>' ); } );

		$adjusted = true;
	}

	if( $adjusted && ( $ctxProcess[ 'mode' ] & 1 ) )
	{
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, _SlickSld_GetGlobStyle( '.jet-carousel .' . $aPrm[ 'itemSlk' ], $aPrm[ 'itemSlkChld' ] ) . '.jet-carousel .' . $aPrm[ 'itemSlk' ] . ':not(.slick-initialized) > *,
.jet-carousel .' . $aPrm[ 'itemSlk' ] . ':not(.slick-initialized) ~ .jet-arrow {
	visibility: visible !important;
}
.jet-carousel .' . $aPrm[ 'itemSlk' ] . ':not(.slick-initialized) > .jet-slick-dots {
	width: 100%;
}' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		_SlickSld_InitGlob( $ctx, $ctxProcess, $doc, '.jet-carousel .' . $aPrm[ 'itemSlk' ] );
	}
}

function _ProcessCont_Cp_jetLott( $ctx, &$ctxProcess, $settFrm, $settCache, $settImg, $settCdn, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," jet-lottie ")][@data-settings]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
		if( !$dataSett )
			continue;

		$dataFile = Gen::GetArrField( $dataSett, array( 'path' ), '' );
		if( !$dataFile )
			continue;

		$itemCont = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," jet-lottie__elem ")]', $item ) );
		if( !$itemCont )
			continue;

		$r = _ProcessCont_Cp_lottGen_AdjustItem( $ctx, $ctxProcess, $settFrm, $settCache, $settImg, $settCdn, $doc, $xpath, $itemCont, Gen::GetArrField( $dataSett, array( 'renderer' ), '' ), $dataFile );
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
			HtmlNd::SetValFromContent( $itemScript, str_replace( array( 'PRM_PATH', 'PRM_RENDERER', 'PRM_LOOP', 'PRM_AUTOPLAY' ), array( $dataFile, Gen::GetArrField( $dataSett, array( 'renderer' ), '' ), Gen::GetArrField( $dataSett, array( 'loop' ) ) ? 'true' : 'false', Gen::GetArrField( $dataSett, array( 'action_start' ), '' ) == 'autoplay' ? 'true' : 'false' ), "
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
			HtmlNd::InsertAfter( $itemCont, $itemScript, null, true );
		}

		// Prevent double loading
		Gen::UnsetArrField( $dataSett, array( 'path' ) );
		$item -> setAttribute( 'data-settings', @json_encode( $dataSett ) );

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@svg\\.lottgen@' ] = true;

		$ctxProcess[ 'aJsCritSpec' ][ 'id:@^jet-lottie-js$@' ] = true;
		$ctxProcess[ 'aJsCritSpec' ][ 'body:@bodymovin\\.loadAnimation\\(\\s*{\\s*container\\s*:\\s*document\\.currentScript\\.parentNode\\W@' ] = true;

		if( $itemScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="jet-lottie-js"]' ) ) )
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

// #######################################################################

function _ProcessCont_Cp_elmntrWdgtJetSldr( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-jet-slider ")][@data-settings]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;
	}
}

// #######################################################################
// #######################################################################

?>