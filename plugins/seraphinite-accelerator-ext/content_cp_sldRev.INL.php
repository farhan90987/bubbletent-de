<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_sldRev( $ctx, &$ctxProcess, $settFrm, $doc, $xpath, $bDblLoadFix )
{
	$itemInitCmnScr = null;
	$engineVer = null;

	//$bDblLoadFix = false;

	$adjusted = false;
	$adjustedBubbles = false;
	foreach( HtmlNd::ChildrenAsArr( $xpath -> query( './/rs-module' ) ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( !$engineVer )
			$engineVer = _RevSld_GetEngineVer( $ctxProcess, $xpath );
		if( !$itemInitCmnScr )
			$itemInitCmnScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[contains(text(),".revolutionInit(")]' ) );

		$itemId = $item -> getAttribute( 'id' );
		if( $bDblLoadFix )
		{
			$itemIdOrig = $itemId;
			//$itemId = $itemId . '-lzl';
		}

		$prms = _RevSld_GetPrmsFromScr( $item, $itemInitCmnScr, $bDblLoadFix ? $itemId : null );
		if( !$prms )
			continue;

		$aItemSlide = HtmlNd::ChildrenAsArr( $xpath -> query( './rs-slides/rs-slide', $item ) );
		if( !$aItemSlide )
			continue;

		$nSlides = count( $aItemSlide );
		$itemFirstSlide = $aItemSlide[ 0 ];
		$nSwitchingLoadingTimeout = 0;
		$nSwitchingLoadingTimeoutMax = ( int )$item -> getAttribute( 'data-lzl-ing-tm' );
		if( !$nSwitchingLoadingTimeoutMax )
			$nSwitchingLoadingTimeoutMax = 4500;

		$aItemStyle = array( array(), array(), array(), array() );

		// gw - Grid Width
		$aGridWidth = Gen::GetArrField( $prms, array( 'start', 'gw' ), array() );
		if( count( $aGridWidth ) == 1 )
			$aGridWidth = array_fill( 0, count( $aItemStyle ), $aGridWidth[ 0 ] );

		// gh - Grid Height
		$aGridHeight = Gen::GetArrField( $prms, array( 'start', 'gh' ), array() );
		if( count( $aGridHeight ) == 1 )
			$aGridHeight = array_fill( 0, count( $aItemStyle ), $aGridHeight[ 0 ] );

		// rl - Responsive Levels
		$aWidth = array_reverse( Gen::GetArrField( $prms, array( 'start', 'rl' ), array() ) );
		if( count( $aWidth ) == 1 )
			$aWidth = array_fill( 0, count( $aItemStyle ), $aWidth[ 0 ] );

		// el
		$aElWidth = array_reverse( Gen::GetArrField( $prms, array( 'start', 'el' ), array() ) );
		if( count( $aElWidth ) == 1 )
			$aElWidth = array_fill( 0, count( $aItemStyle ), $aElWidth[ 0 ] );

		if( count( $aWidth ) != count( $aItemStyle ) )
			continue;

		HtmlNd::AddRemoveAttrClass( $item, array( 'js-lzl-nid' ) );

		$keepBPHeight = Gen::GetArrField( $prms, array( 'init', 'keepBPHeight' ) );
		$layout = Gen::GetArrField( $prms, array( 'init', 'sliderLayout' ), '' );
		$item -> setAttribute( 'data-lzl-widths', @json_encode( $aWidth ) );
		$item -> setAttribute( 'data-lzl-widths-g', @json_encode( array_reverse( $aGridWidth ) ) );
		$item -> setAttribute( 'data-lzl-heights-g', @json_encode( array_reverse( $aGridHeight ) ) );
		$item -> setAttribute( 'data-lzl-g-s', @version_compare( $engineVer, '6.6', '>=' ) ? ( $layout != 'fullscreen' ? false : !$keepBPHeight ) : ( $layout != 'fullscreen' ? $keepBPHeight : !$keepBPHeight ) );
		$item -> setAttribute( 'data-lzl-layout', $layout );

		$itemStyleCont = '';

		if( $layout != 'fullscreen' )
		{
			$heightProp = true ? 'height' : 'min-height';	// Look sldRev-0020 - arrows have wrong position if min-height used !!!!
			for( $i = 0; $i < count( $aItemStyle ); $i++ )
			{
				$h = ($aGridHeight[ $i ]??'0') . 'px';
				if( !$keepBPHeight )
					$h = 'calc(' . $h . '*var(--lzl-rs-scale))';
				$aItemStyle[ $i ][ '#' . $itemId . ':not(.revslider-initialised)' ][ $heightProp ] = $h . '!important';

				if( $bDblLoadFix )
				{
					if( $itemIdOrig != $itemId )
						$aItemStyle[ $i ][ '#' . $itemIdOrig . ':not(.revslider-initialised)' ][ $heightProp ] = $h . '!important';
				}
			}

			if( $bDblLoadFix )
				$itemStyleCont .= 'rs-module-wrap:has( > #' . $itemId . '.js-lzl-ing) { margin-top: calc(-1px * var(--lzl-rs-cy))!important; }';
		}

		{
			$v = Gen::GetArrField( $prms, array( 'start', 'offset' ) );
			if( !is_string( $v ) )
				$v = ( string )$v . 'px';
			else if( !strlen( $v ) )
				$v = '0px';
			else if( Gen::StrEndsWith( $v, '%' ) )
				$v = ( string ) ( ( float )$v / 100 ) . ' * var(--seraph-accel-dvh)';
			$item -> setAttribute( 'style', Ui::GetStyleAttr( Ui::MergeStyleAttr( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( '--lzl-rs-offs-y' => $v ) ) ) );
		}

		$aItemTop = array();

		// rs-slides
		{
			$itemSlidesTmp = $doc -> createElement( 'rs-slides-lzl' );
			HtmlNd::AddRemoveAttrClass( $itemSlidesTmp, array( 'rs-lzl-cont', 'js-lzl-ing' ) );
			HtmlNd::InsertAfter( $item, $itemSlidesTmp, $itemFirstSlide -> parentNode );
			$itemSlidesTmp -> setAttribute( 'style', Ui::GetStyleAttr( Ui::MergeStyleAttr( Ui::ParseStyleAttr( $itemSlidesTmp -> getAttribute( 'style' ) ), array( 'width' => '100%', 'height' => '100%' ) ) ) );

			$itemFirstSlideTmp = $itemFirstSlide -> cloneNode( true );
			$itemSlidesTmp -> appendChild( $itemFirstSlideTmp );
			$itemFirstSlideTmp -> setAttribute( 'style', Ui::GetStyleAttr( Ui::MergeStyleAttr( Ui::ParseStyleAttr( $itemFirstSlideTmp -> getAttribute( 'style' ) ), array( 'width' => '100%', 'height' => '100%' ) ) ) );

			$aItemTop[] = $itemFirstSlideTmp;

			if( $itemCurSlideIndex = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(text(),"{{current_slide_index}}")]', $itemFirstSlideTmp ) ) )
				if( $itemCurSlideIndex -> firstChild && $itemCurSlideIndex -> firstChild -> nodeType == XML_TEXT_NODE )
					$itemCurSlideIndex -> firstChild -> nodeValue = str_replace( '{{current_slide_index}}', '1', ( string )$itemCurSlideIndex -> firstChild -> nodeValue );

			if( $itemCurSlideIndex = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(text(),"{{total_slide_count}}")]', $itemFirstSlideTmp ) ) )
				if( $itemCurSlideIndex -> firstChild && $itemCurSlideIndex -> firstChild -> nodeType == XML_TEXT_NODE )
					$itemCurSlideIndex -> firstChild -> nodeValue = str_replace( '{{total_slide_count}}', ( string )$nSlides, ( string )$itemCurSlideIndex -> firstChild -> nodeValue );
		}

		// rs-static-layers
		if( $itemStaticLayers = HtmlNd::FirstOfChildren( $xpath -> query( './rs-static-layers', $item ) ) )
		{
			$itemStaticLayersTmp = HtmlNd::SetTag( $itemStaticLayers -> cloneNode( true ), 'rs-static-layers-lzl' );
			HtmlNd::AddRemoveAttrClass( $itemStaticLayersTmp, array( 'rs-lzl-cont', 'js-lzl-ing' ) );
			HtmlNd::InsertAfter( $item, $itemStaticLayersTmp, $itemStaticLayers );

			$aItemTop[] = $itemStaticLayersTmp;

			if( $itemCountTotal = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," count_total ")]', $itemStaticLayersTmp ) ) )
				if( $itemCountTotal -> firstChild && $itemCountTotal -> firstChild -> nodeType == XML_TEXT_NODE )
					$itemCountTotal -> firstChild -> nodeValue = sprintf( '%0' . strlen( trim( ( string )$itemCountTotal -> firstChild -> nodeValue ) ) . 'u', $nSlides );
		}

		foreach( $aItemTop as $itemFirstSlideTmp )
		{
			$iCurBubblesRand = 0;
			$slideMediaFilter = $itemFirstSlideTmp -> getAttribute( 'data-mediafilter' );
			$itemSlideChild = null;
			$itemSlideChildNext = null;
			$itemSlideBgContainer = null;
			$bBlendModeLighten = false;
			$bInColumn = false;
			$bInGroup = false;

			$attrAnim = _RevSld_GetAttrs( $itemFirstSlideTmp -> getAttribute( 'data-anim' ) );
			_RevSld_AdjustTimeoutByVal( $nSwitchingLoadingTimeout, $nSwitchingLoadingTimeoutMax, ($attrAnim[ 'ms' ]??null) );

			while( $itemSlideChild = ( $itemSlideChildNext ? $itemSlideChildNext : HtmlNd::GetNextTreeChild( $itemFirstSlideTmp, $itemSlideChild ) ) )
			{
				$itemSlideChildNext = null;
				if( $itemSlideChild -> nodeType != XML_ELEMENT_NODE )
					continue;

				/*$itemStyleCont .= '
					body rs-slides #' . $itemSlideChild -> getAttribute( 'id' ) . ' {
						transform: scale(1) rotate(0deg) translate(0, 0) !important;
						opacity:1 !important;
					}
				';*/

				{
					$id = ( string )$itemSlideChild -> getAttribute( 'id' );
					if( strlen( $id ) && strpos( $id, '-lzl' ) === false )
						$itemSlideChild -> setAttribute( 'id', $id . '-lzl' );
					unset( $id );
				}

				$aClass = HtmlNd::GetAttrClass( $itemSlideChild );

				$bResponsiveSizes = $itemSlideChild -> getAttribute( 'data-rsp_bd' ) !== 'off';	// Resize Between Devices
				$bResponsiveOffsets = $itemSlideChild -> getAttribute( 'data-rsp_o' ) !== 'off';
				$bResponsiveChildren = $itemSlideChild -> getAttribute( 'data-rsp_ch' ) === 'on';

				$baseAlign = $itemSlideChild -> getAttribute( 'data-basealign' );

				$isLayer = $itemSlideChild -> nodeName == 'rs-layer' || in_array( 'rs-layer', $aClass );
				$isContainer = $itemSlideChild -> nodeName == 'rs-row' || $itemSlideChild -> nodeName == 'rs-column' || $itemSlideChild -> nodeName == 'rs-group';

				$itemParent = $itemSlideChild -> parentNode;
				$itemInsertBefore = $itemSlideChild -> nextSibling;

				{
					// Reset
					if( $itemParent === $itemFirstSlideTmp )
					{
						$bInColumn = false;
						$bInGroup = false;
					}

					if( $itemSlideChild -> nodeName == 'rs-column' )
						$bInColumn = true;
					if( $itemSlideChild -> nodeName == 'rs-group' )
						$bInGroup = true;
				}

				if( $itemSlideChild -> nodeName == 'img' && in_array( 'rev-slidebg', $aClass ) )
				{
					$itemChildSelector = '#' . $itemId . ' ' . ( $itemFirstSlideTmp -> hasAttribute( 'data-key' ) ? ( '.js-lzl-ing [data-key="' . $itemFirstSlideTmp -> getAttribute( 'data-key' ) . '"]' ) : ( $itemFirstSlideTmp -> nodeName . '.js-lzl-ing' ) ) . ' rs-sbg:nth-child(' . ( ( $itemSlideBgContainer ? $itemSlideBgContainer -> childNodes -> length : 0 ) + 1 ) . ')';

					$itemSlideChildNext = HtmlNd::GetNextTreeChild( $itemFirstSlideTmp, $itemSlideChild );
					$attrPanZoom = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-panzoom' ) );

					$srcImg = $itemSlideChild -> getAttribute( 'data-lazyload' );
					if( !$srcImg )
						$srcImg = $itemSlideChild -> getAttribute( 'src' );
					$attrBg = Ui::MergeStyleAttr( array( 'p' => 'center' ), _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-bg' ) ) );

					$attrPanZoomDuration = ( int )Gen::GetArrField( $attrPanZoom, array( 'd' ), '0' );
					$attrPanZoomOffsetXY = explode( '/', Gen::GetArrField( $attrPanZoom, array( 'os' ), '0px/0px' ) );
					$attrPanZoomOffsetEndXY = explode( '/', Gen::GetArrField( $attrPanZoom, array( 'oe' ), '0px/0px' ) );
					$attrPanZoomScale = ( float )Gen::GetArrField( $attrPanZoom, array( 'ss' ), '100%' ) / 100;
					$attrPanZoomScaleEnd = ( float )Gen::GetArrField( $attrPanZoom, array( 'se' ), '100%' ) / 100;

					$attrBgPos = explode( ' ', Gen::GetArrField( $attrBg, array( 'p' ), '' ) );
					if( count( $attrBgPos ) < 2 )
						$attrBgPos[ 1 ] = $attrBgPos[ 0 ];

					switch( $attrBgPos[ 0 ] )
					{
					case 'left':					$attrBgPos[ 0 ] = '0%'; break;
					case 'middle':
					case 'center':					$attrBgPos[ 0 ] = '50%'; break;
					case 'right':					$attrBgPos[ 0 ] = '100%'; break;
					}

					switch( $attrBgPos[ 1 ] )
					{
					case 'top':						$attrBgPos[ 1 ] = '0%'; break;
					case 'middle':
					case 'center':					$attrBgPos[ 1 ] = '50%'; break;
					case 'bottom':					$attrBgPos[ 1 ] = '100%'; break;
					}

					$attrBgPosEnd[ 0 ] = 'calc(' . $attrBgPos[ 0 ] . ' + ' . _RevSld_GetSize( false, Gen::GetArrField( $attrPanZoomOffsetEndXY, array( 0 ), '0' ) ) . ' / ' . $attrPanZoomScaleEnd . ')';
					$attrBgPosEnd[ 1 ] = 'calc(' . $attrBgPos[ 1 ] . ' + ' . _RevSld_GetSize( false, Gen::GetArrField( $attrPanZoomOffsetEndXY, array( 1 ), '0' ) ) . ' / ' . $attrPanZoomScaleEnd . ')';
					$attrBgPos[ 0 ] = 'calc(' . $attrBgPos[ 0 ] . ' + ' . _RevSld_GetSize( false, Gen::GetArrField( $attrPanZoomOffsetXY, array( 0 ), '0' ) ) . ' / ' . $attrPanZoomScale . ')';
					$attrBgPos[ 1 ] = 'calc(' . $attrBgPos[ 1 ] . ' + ' . _RevSld_GetSize( false, Gen::GetArrField( $attrPanZoomOffsetXY, array( 1 ), '0' ) ) . ' / ' . $attrPanZoomScale . ')';

					$attrsStyle = array( 'width' => '100%', 'height' => '100%', 'background' => ( isset( $attrBg[ 'c' ] ) ? ( ( string )$attrBg[ 'c' ] . ( Gen::StrStartsWith( ( string )$attrBg[ 'c' ], array( '#', 'rgb', 'hsl' ) ) ? '' : ',' ) . ' ' ) : '' ) . implode( ' ', $attrBgPos ) . ' / cover no-repeat url(' . $srcImg . ')', 'transform' => 'scale(' . $attrPanZoomScale . ') rotate(' . Gen::GetArrField( $attrPanZoom, array( 'rs' ), '0deg' ) . ')' );
					if( $attrPanZoomDuration )
					{
						$attrsStyle[ 'transition-property' ] = 'transform, background-position !important';
						$attrsStyle[ 'transition-duration' ] = '' . $attrPanZoomDuration . 'ms !important';
						$attrsStyle[ 'transition-timing-function' ] = 'linear';
					}
					$itemSlideChildTmp = HtmlNd::CreateTag( $doc, 'div', array( /*'data-panzoom' => 'd:0;', 'class' => 'rev-slidebg', */'style' => $attrsStyle ) );
					$itemParent -> replaceChild( $itemSlideChildTmp, $itemSlideChild );
					$itemSlideChild = $itemSlideChildTmp;

					$itemSlideBgItem = HtmlNd::CreateTag( $doc, 'rs-sbg', array( 'class' => array( $slideMediaFilter ), 'style' => array( 'width' => '100%', 'height' => '100%' ) ), array( $itemSlideChild ) );

					if( $itemSlideBgContainer )
					{
						$itemSlideBgContainer -> appendChild( $itemSlideBgItem );
					}
					else
					{
						$itemSlideBgContainer = HtmlNd::CreateTag( $doc, 'rs-sbg-wrap', null, array( $itemSlideBgItem ) );
						$itemParent -> insertBefore( HtmlNd::CreateTag( $doc, 'rs-sbg-px', null, array( $itemSlideBgContainer ) ), $itemInsertBefore );
					}

					if( $attrPanZoomDuration )
					{
						$itemStyleCont .= '
								rs-module:not(.js-lzl-nid)' . $itemChildSelector . ' > div {
									transform: scale(' . $attrPanZoomScaleEnd . ') rotate(' . Gen::GetArrField( $attrPanZoom, array( 're' ), '0deg' ) . ') !important;
									background-position: ' . implode( ' ', $attrBgPosEnd ) . ' !important;
								}
							';
					}
				}
				else if( $itemSlideChild -> nodeName == 'rs-bgvideo' )
				{
					$itemChildSelector = '#' . $itemId . ' ' . ( $itemFirstSlideTmp -> hasAttribute( 'data-key' ) ? ( '.js-lzl-ing [data-key="' . $itemFirstSlideTmp -> getAttribute( 'data-key' ) . '"]' ) : ( $itemFirstSlideTmp -> nodeName . '.js-lzl-ing' ) ) . ' rs-bgvideo:nth-child(' . ( ( $itemSlideBgContainer ? $itemSlideBgContainer -> childNodes -> length : 0 ) + 1 ) . ')';

					HtmlNd::AddRemoveAttrClass( $itemSlideChild, array( $slideMediaFilter ) );

					$itemSlideChildNext = HtmlNd::GetNextTreeChild( $itemFirstSlideTmp, $itemSlideChild );

					$itemSlideBgItem = $itemSlideChild;
					$itemSlideBgItem -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'rs-fullvideo-cover' ) ) ) );
					$itemSlideBgItem -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'html5vid', 'rs_html5vidbasicstyles', 'fullcoveredvideo' ) ), array( HtmlNd::CreateTag( $doc, 'video', array( 'autoplay' => '', 'muted' => '', 'loop' => '', 'preload' => 'auto', 'style' => array( 'object-fit' => 'cover', 'background-size' => 'cover', 'opacity' => '0', 'width' => '100%', 'height' => '100%', 'position' => 'absolute', 'left' => '0px', 'top' => '0px' ) ), array( HtmlNd::CreateTag( $doc, 'source', array( 'src' => $itemSlideChild -> getAttribute( 'data-mp4' ), 'type' => array( 'video/mp4' ) ) ) ) ) ) ) );
					$itemSlideBgItem -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'tp-video-play-button' ) ), array( HtmlNd::CreateTag( $doc, 'i', array( 'class' => array( 'revicon-right-dir' ) ) ), HtmlNd::CreateTag( $doc, 'span', array( 'class' => array( 'tp-revstop' ) ), array( $doc -> createTextNode( ' ' ) ) ) ) ) );

					if( $itemSlideBgContainer )
					{
						$itemSlideBgContainer -> appendChild( $itemSlideBgItem );
					}
					else
					{
						$itemSlideBgContainer = HtmlNd::CreateTag( $doc, 'rs-sbg-wrap', null, array( $itemSlideBgItem ) );
						$itemParent -> insertBefore( HtmlNd::CreateTag( $doc, 'rs-sbg-px', null, array( $itemSlideBgContainer ) ), $itemInsertBefore );
					}
				}
				else if( $isLayer || $isContainer )
				{
					$id = $itemSlideChild -> getAttribute( 'id' );
					$itemIdWrap = $id . '-wrap';

					//
					if( $id == 'slider-42-slide-76-layer-4' )
					{
						$sfdvsdf = 0;
					}
					//

					$itemChildSelector = '.js-lzl-ing #' . $id;
					$itemChildSelectorWrap = '.js-lzl-ing #' . $itemIdWrap;

					$attrXy = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-xy' ), count( $aItemStyle ) );
					$attrDim = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-dim' ) );
					$attrText = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-text' ) );
					$attrPadding = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-padding' ) );
					$attrMargin = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-margin' ) );
					$attrBorder = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-border' ) );
					$attrBTrans = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-btrans' ), count( $aItemStyle ) );
					$attrTextStroke = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-tst' ) );
					$attrType = $itemSlideChild -> getAttribute( 'data-type' );
					$attrWrapperClass = $itemSlideChild -> getAttribute( 'data-wrpcls' );
					$attrVisibility = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-vbility' ) );
					$attrColumn = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-column' ) );
					$attrLoop = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-tloop' ) );

					$attrFrame0 = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-frame_0' ) );
					$attrFrame0Mask = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-frame_0_mask' ) );
					$attrFrame1 = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-frame_1' ) );

//					$bBaseAlignLayerArea = $baseAlign ? ( $baseAlign !== 'slide' ) : $keepBPHeight;
					$bBaseAlignLayerArea = ( $attrType != 'row' ) ? ( $baseAlign !== 'slide' ) : false;

					$attrColor = trim( ( string )$itemSlideChild -> getAttribute( 'data-color' ) );
					if( strlen( $attrColor ) )
						$attrColor = explode( '||', ( string )$attrColor );
					else
						$attrColor = array();

					$attrDisplay = $itemSlideChild -> getAttribute( 'data-disp' );
					if( !$attrDisplay )
						$attrDisplay = null;

					$attrPos = $itemSlideChild -> getAttribute( 'data-pos' );

					if( !isset( $attrText[ 'ls' ] ) )
						$attrText[ 'ls' ] = '0';

					if( !isset( $attrText[ 'l' ] ) && ( $attrType == 'text' || $attrType == 'column' ) )
						$attrText[ 'l' ] = '25px';

					$styleSeparated = array( 'color' => $attrColor ? null : '#fff', 'position' => ( $itemParent === $itemFirstSlideTmp || $itemParent -> nodeName == 'rs-group' || $attrPos == 'a' ) ? 'absolute' : 'relative', 'display' => $attrDisplay );
					$styleSeparatedWrap = array( 'position' => $styleSeparated[ 'position' ]/*, 'width' => '100%', 'height' => '100%'*/, 'display' => $attrDisplay, 'pointer-events' => 'auto' );

					if( ($attrLoop[ 'u' ]??null) != 'true' )
						if( !_RevSld_AdjustTimeoutByVal( $nSwitchingLoadingTimeout, $nSwitchingLoadingTimeoutMax, ($attrFrame1[ 'st' ]??null), ($attrFrame1[ 'sp' ]??null) ) && !( ($attrFrame0[ 'o' ]??null) == '1' && ( !$attrFrame0Mask || ($attrFrame0Mask[ 'u' ]??null) != 't' ) ) )
							$styleSeparated[ 'opacity' ] = '0!important';

					//if( $isLayer )
					//    $styleSeparated[ 'overflow' ] = 'hidden';

					if( /*$attrType != 'row' && */$attrType != 'column' && ( $attrPos == 'a' || !HtmlNd::FindUpBy( $itemSlideChild, function( $nd, $data ) { return( $nd -> nodeName == 'rs-column' ); } ) ) )
					{
						$bExtraX = ( $baseAlign !== 'slide' && ( $itemParent === $itemFirstSlideTmp || ( $itemParent -> nodeName == 'rs-zone' && $itemParent -> parentNode === $itemFirstSlideTmp ) ) );

						$a = array_fill( 0, count( $aItemStyle ), array() );
						$aW = array_fill( 0, count( $aItemStyle ), array() );
						for( $i = 0; $i < count( $aItemStyle ); $i++ )
						{
							$translate = array( 0, 0 );
							$offset = $attrType != 'row' ? array( Gen::GetArrField( $attrXy, array( 'xo', $i ), '0' ), Gen::GetArrField( $attrXy, array( 'yo', $i ), '0' ) ) : array( '0', '0' );
							$bBaseAlignLayerAreaI = $bBaseAlignLayerArea;

							{
								$prefix = null;
								$prefixSize = null;
								switch( $alignX = Gen::GetArrField( $attrXy, array( 'x', $i ), '' ) )
								{
								case 'c':
								case 'm':
									$translate[ 0 ] = '-50%';
									$prefix = '50% + ';
									break;

								case 'r':
									$translate[ 0 ] = '-100%';
									$prefix = '100% - ';
									if( $bExtraX )
									{
										$prefix = '-1px * var(--lzl-rs-extra-x) + ' . $prefix;
										$prefixSize = '-2px * var(--lzl-rs-extra-x) + ';
									}
									break;

								default:
									if( Gen::StrEndsWith( $alignX, 'px' ) )
										$offset[ 0 ] = $alignX;

									$prefix = '';
									if( $bExtraX )
									{
										$prefix = '1px * var(--lzl-rs-extra-x) + ' . $prefix;
										$prefixSize = '-2px * var(--lzl-rs-extra-x) + ';
									}
								}

								$aW[ $i ][ 'left' ] = _RevSld_GetSize( $bResponsiveOffsets, $offset[ 0 ], $prefix );
								$aW[ $i ][ 'width' ] = _RevSld_GetSize( false, '100%', $prefixSize );
							}

							{
								$prefix = null;
								switch( $alignY = Gen::GetArrField( $attrXy, array( 'y', $i ), '' ) )
								{
								case 'c':
								case 'm':
									if( $attrType != 'row' )
										$translate[ 1 ] = '-50%';
									$prefix = '50% + ';

									if( @version_compare( $engineVer, '6.6', '>=' ) && $bBaseAlignLayerAreaI )
										$bBaseAlignLayerAreaI = false;
									break;

								case 'b':
									if( $attrType != 'row' )
										$translate[ 1 ] = '-100%';
									$prefix = '100% - ';
									break;

								default:
									if( Gen::StrEndsWith( $alignY, 'px' ) )
										$offset[ 1 ] = $alignY;
								}

								$offsSuffix = null;
								if( $bBaseAlignLayerAreaI )
									$offsSuffix = ' + 1px * var(--lzl-rs-diff-y)';

								$aW[ $i ][ 'top' ] = _RevSld_GetSize( $bResponsiveOffsets, $offset[ 1 ], $prefix, $offsSuffix );
							}

							if( $translate[ 0 ] || $translate[ 1 ] )
								$a[ $i ][ 'transform' ] = 'translate(' . $translate[ 0 ] . ', ' . $translate[ 1 ] . ')!important';
						}
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						_RevSld_SetStyleAttr( $styleSeparatedWrap, $aItemStyle, $itemChildSelectorWrap, $aW );
					}

					$aSizeChild = array();
					$aSizeWrap = array();
					foreach( array( 'w' => 'width', 'maxw' => 'max-width', 'h' => 'height' ) as $f => $t )
					{
						$a = array();
						foreach( ( array )($attrDim[ $f ]??'auto') as $i => $v )
						{
							$v = $a[ $i ][ $t ] = _RevSld_GetSize( $bResponsiveSizes, $v . ( is_numeric( $v ) ? 'px' : '' ) );
							$aSizeChild[ $i ][ $t ] = $v == 'auto' ? 'auto' : null;
							$aSizeWrap[ $i ][ $t ] = Gen::StrEndsWith( ( string )$v, '%' ) ? '100%' : null;
						}
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					// Column
					if( isset( $attrColumn[ 'w' ] ) )
					{
						foreach( $aSizeWrap as $i => $v ) unset( $aSizeWrap[ $i ][ 'width' ] );
						$a = array(); foreach( ( array )$attrColumn[ 'w' ] as $i => $v ) $a[ $i ][ 'width' ] = _RevSld_GetSize( $bResponsiveOffsets, $v );
						_RevSld_SetStyleAttr( $styleSeparatedWrap, $aItemStyle, $itemChildSelectorWrap, $a );
					}

					if( $attrType != 'column' )
					{
						// white-space
						{
							$a = array();
							foreach( ( array )($attrDim[ 'w' ]??'auto') as $i => $vDim )
							{
								// ("normal" === d.whiteSpace && "auto" === d.width && (!l._incolumn && !l._ingroup || "relative" !== l.position) ? "nowrap" : d.whiteSpace)
								$v = isset( $attrText[ 'w' ] ) ? ( is_array( $attrText[ 'w' ] ) ? $attrText[ 'w' ][ $i ] : $attrText[ 'w' ] ) : 'nowrap';
								if( $v == 'normal' && $vDim == 'auto' && ( !$bInColumn && !$bInGroup || $styleSeparated[ 'position' ] !== 'relative' ) )
									$v = 'nowrap';
								$a[ $i ][ 'white-space' ] = $v;
							}
							_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						}

						// color
						if( $attrColor )
						{
							$a = array(); foreach( $attrColor as $i => $v ) $a[ $i ][ 'color' ] = $attrColor[ $i ];
							_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						}

						foreach( array( 'fw' => 'font-weight' ) as $f => $t )
						{
							$a = array(); foreach( ( array )($attrText[ $f ]??null) as $i => $v ) $a[ $i ][ $t ] = $v;
							_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						}

						foreach( array( 's' => 'font-size', 'ls' => 'letter-spacing' ) as $f => $t )
						{
							$a = array(); foreach( ( array )($attrText[ $f ]??null) as $i => $v ) if( $v !== null ) $a[ $i ][ $t ] = _RevSld_GetSize( $bResponsiveSizes, $v . ( Gen::StrEndsWith( $v, 'px' ) ? '' : 'px' ) );
							_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						}
					}

					foreach( array( 'l' => 'line-height' ) as $f => $t )
					{
						$a = array(); foreach( ( array )($attrText[ $f ]??null) as $i => $v ) if( $v !== null ) $a[ $i ][ $t ] = _RevSld_GetSize( $bResponsiveSizes, $v . ( Gen::StrEndsWith( $v, 'px' ) ? '' : 'px' ) );
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					foreach( array( 'a' => 'text-align' ) as $f => $t )
					{
						$a = array(); foreach( ( array )($attrText[ $f ]??null) as $i => $v ) $a[ $i ][ $t ] = $v;
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					foreach( array( 'f' => 'float' ) as $f => $t )
					{
						$a = array(); foreach( ( array )($attrText[ $f ]??null) as $i => $v ) $a[ $i ][ $t ] = $v;
						_RevSld_SetStyleAttr( $styleSeparatedWrap, $aItemStyle, $itemChildSelectorWrap, $a );
					}

					foreach( array( 'l' => 'padding-left', 'r' => 'padding-right', 't' => 'padding-top', 'b' => 'padding-bottom' ) as $f => $t )
					{
						$a = array(); foreach( ( array )($attrPadding[ $f ]??null) as $i => $v ) if( $v !== null ) $a[ $i ][ $t ] = _RevSld_GetSize( $bResponsiveSizes, $v . 'px' );
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					foreach( array( 'l' => 'margin-left', 'r' => 'margin-right', 't' => 'margin-top', 'b' => 'margin-bottom' ) as $f => $t )
					{
						if( $itemSlideChild -> nodeName == 'rs-row'/* || $itemSlideChild -> nodeName == 'rs-column'*/ )
							$t = str_replace( 'margin-', 'padding-', $t );
						$a = array(); foreach( ( array )($attrMargin[ $f ]??null) as $i => $v ) if( $v !== null ) $a[ $i ][ $t ] = _RevSld_GetSize( $bResponsiveSizes, $v . 'px' );
						_RevSld_SetStyleAttr( $styleSeparatedWrap, $aItemStyle, $itemChildSelectorWrap, $a );
					}

					foreach( array( 'bos' => 'border-style', 'boc' => 'border-color', 'bow' => 'border-width', 'bor' => 'border-radius' ) as $f => $t )
					{
						$a = array(); foreach( ( array )($attrBorder[ $f ]??null) as $i => $v ) $a[ $i ][ $t ] = ( $f == 'bow' ) ? _RevSld_GetSize( false, $v ) : $v;
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					foreach( array( 'w' => '-webkit-text-stroke-width', 'c' => '-webkit-text-stroke-color' ) as $f => $t )
					{
						$a = array(); foreach( ( array )($attrTextStroke[ $f ]??null) as $i => $v ) $a[ $i ][ $t ] = $v;
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					if( $attrVisibility )
					{
						//
						//LogWrite( 'SLD_REV - VISIBILITY!!! - ' . $ctxProcess[ 'siteRootUri' ] );
						//

						$a = array(); foreach( $attrVisibility[ '' ] as $i => $v ) if( $v === 'f' ) $a[ $i ][ 'display' ] = 'none'; else $a[ $i ][ '' ] = '';
						_RevSld_SetStyleAttr( $styleSeparatedWrap, $aItemStyle, $itemChildSelectorWrap, $a );
					}

					if( isset( $attrFrame0[ 'rZ' ] ) )
					{
						if( !is_array( $attrFrame0[ 'rZ' ] ) || isset( $attrFrame0[ 'rZ' ][ 'cyc' ] ) )
							$attrFrame0[ 'rZ' ] = array_fill( 0, count( $aItemStyle ), $attrFrame0[ 'rZ' ] );
						$attrBTrans[ 'rZ' ] = $attrFrame0[ 'rZ' ];
					}

					if( isset( $attrBTrans[ 'rZ' ] ) )
					{
						$a = array(); foreach( ( array )$attrBTrans[ 'rZ' ] as $i => $v ) $a[ $i ][ 'transform' ] = 'rotate(' . ( isset( $v[ 'cyc' ] ) ? _RevSld_GetIdxPropVal( $attrBTrans, array( 'cyc' ), 0, '0' ) : ( string )( int )$v  ). 'deg)!important';
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					//if( $attrType == 'button' )
					//    $styleSeparated[ 'white-space' ] = 'nowrap';

					if( $attrType == 'image' && ( $itemImg = HtmlNd::FirstOfChildren( $xpath -> query( './/img', $itemSlideChild ) ) ) )
					{
						HtmlNd::RenameAttr( $itemImg, 'data-lazyload', 'src' );

						$styleSeparatedImg = array();
						_RevSld_SetStyleAttr( $styleSeparatedImg, $aItemStyle, $itemChildSelector . ' > img', $aSizeChild );
						$itemImg -> setAttribute( 'style', Ui::GetStyleAttr( Ui::MergeStyleAttr( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), $styleSeparatedImg ) ) );
						unset( $styleSeparatedImg );
						//
						//LogWrite( 'SLD_REV - IMAGE!!! - ' . $ctxProcess[ 'siteRootUri' ] );
						//
					}

					if( $attrType == 'video' )
					{
						//
						//LogWrite( 'SLD_REV - VIDEO!!! - ' . $ctxProcess[ 'siteRootUri' ] );
						//

						$mp4Url = $itemSlideChild -> getAttribute( 'data-mp4' );

						$itemSlideChild -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'html5vid', 'rs_html5vidbasicstyles' ), 'style' => array( 'box-sizing' => 'content-box', 'border-color' => 'transparent', 'border-style' => 'none', 'left' => '0px', 'top' => '0px' ) ), array(
							HtmlNd::CreateTag( $doc, 'video', array( 'preload' => 'auto', 'style' => array( 'opacity' => '1', 'width' => '100%', 'height' => '100%', 'display' => 'block' ) ), array(
								HtmlNd::CreateTag( $doc, 'source', array( 'type' => 'video/mp4', 'src' => $mp4Url ) )
							) )
						) ) );
					}

					if( $posterUrl = $itemSlideChild -> getAttribute( 'data-poster' ) )
					{
						//
						//LogWrite( 'SLD_REV - POSTER!!! - ' . $ctxProcess[ 'siteRootUri' ] );
						//

						$itemSlideChild -> appendChild( HtmlNd::CreateTag( $doc, 'rs-poster', array( 'class' => 'noSwipe', 'style' => array( 'background-image' => 'url(' . $posterUrl . ')' ) ) ) );
					}

					if( $blendMode = $itemSlideChild -> getAttribute( 'data-blendmode' ) )
					{
						$styleSeparatedWrap[ 'mix-blend-mode' ] = $blendMode;
						if( $blendMode == 'lighten' && !$bBlendModeLighten )
						{
							$styleSeparated[ 'opacity' ] = '0!important';
							$bBlendModeLighten = true;
						}
					}

					if( $srcSvg = $itemSlideChild -> getAttribute( 'data-svg_src' ) )
					{
						HtmlNd::AddRemoveAttrClass( $itemSlideChild, 'rs-layer' );

						$imgSrc = new ImgSrc( $ctxProcess, $srcSvg, null, true/*null*/ );
						$imgSrc -> Init( $ctxProcess );

						if( $itemSvg = HtmlNd::LoadXML( $imgSrc -> GetCont() ) )
							if( $itemSvg = $doc -> importNode( $itemSvg, true ) )
								$itemSlideChild -> appendChild( $itemSvg );

						unset( $imgSrc, $itemSvg );

						$attrSvgI = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-svgi' ), count( $aItemStyle ), '||' );
						if( isset( $attrSvgI[ 'c' ] ) )
						{
							$a = array(); foreach( $attrSvgI[ 'c' ] as $i => $v ) $a[ $i ][ 'fill' ] = $attrSvgI[ 'c' ][ $i ];
							$styleSeparatedSvg = array();
							_RevSld_SetStyleAttr( $styleSeparatedSvg, $aItemStyle, $itemChildSelector . ' > svg', $a );
						}
					}

					// Action
					$actions = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-actions' ) );
					if( $actions )
					{
						HtmlNd::AddRemoveAttrClass( $itemSlideChild, array( 'rs-waction', 'rs-layer' ) );

						if( Gen::GetArrField( $actions, array( 'a' ) ) == 'startlayer' )
						{
							$idLayer = Gen::GetArrField( $actions, array( 'layer' ) );
							if( $idLayer && ( $itemLayerToHide = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@id="' . $idLayer . '"]', $itemSlidesTmp ) ) ) )
								HtmlNd::AddRemoveAttrClass( $itemLayerToHide, 'js-lzl-ing-disp-none' );
						}

						if( Gen::GetArrField( $actions, array( 'o' ) ) == 'click' )
						{
							HtmlNd::AddRemoveAttrClass( $itemSlideChild, 'rs-wclickaction' );
						}
					}

					// Chars
					$frameChars = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-frame_0_chars' ) );
					if( $frameChars )
					{
						$aSizeWrap = array( array( 'width' => '100%' ) );

						$frameChars1 = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-frame_1_chars' ) );
						if( $frameChars1 && ( ( int )Gen::GetArrField( $frameChars1, array( 'd' ) ) > ( int )Gen::GetArrField( $frameChars, array( 'd' ) ) ) )
							$frameChars = $frameChars1;
						unset( $frameChars1 );

						$aLines = array( '' );
						for( $item111 = $itemSlideChild -> firstChild; $item111; $item111 = $item111 -> nextSibling )
						{
							if( $item111 -> nodeType == XML_ELEMENT_NODE && $item111 -> nodeName == 'br' )
							{
								$aLines[] = "\n";
								$aLines[] = '';
								continue;
							}

							$aLines[ count( $aLines ) - 1 ] .= $item111 -> textContent;
						}

						HtmlNd::CleanChildren( $itemSlideChild );

						foreach( $aLines as $aChars )
						{
							if( $aChars === "\n" )
								$aItemWord[] = $doc -> createTextNode( "\xC2\xA0" );
							else
							{
								$aChars = trim( $aChars );
								$aChars = function_exists( 'mb_str_split' ) ? mb_str_split( $aChars ) : str_split( $aChars );

								$aItemWord = array();
								foreach( $aChars as $i => $char )
								{
									if( !$aItemWord || $char === ' ' )
									{
										if( $char === ' ' )
											$aItemWord[] = $doc -> createTextNode( "\n" );
										$aItemWord[] = HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'rs_splitted_words', 'style' => array( 'display' => 'inline-block' ) ) );
										if( $char === ' ' )
											continue;
									}

									$itemChar = HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'rs_splitted_chars', 'style' => array(
										'display' => 'inline-block',
										'transform-origin' => '50% 50%',
										'transform' => 'translate3d(' . _RevSld_GetIdxPropVal( $frameChars, array( 'x', 'cyc' ), $i, '0' ) . 'px, ' . _RevSld_GetIdxPropVal( $frameChars, array( 'y', 'cyc' ), $i, '0' ) . 'px, ' . _RevSld_GetIdxPropVal( $frameChars, array( 'z', 'cyc' ), $i, '0' ) . 'px) rotate(' . _RevSld_GetIdxPropVal( $frameChars, array( 'rZ', 'cyc' ), $i, '0' ) . 'deg)'
									) ), array( $doc -> createTextNode( $char ) ) );
									$aItemWord[ count( $aItemWord ) - 1 ] -> appendChild( $itemChar );
								}
							}

							$itemSlideChild -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'rs_splitted_lines', 'style' => array( 'white-space' => /*count( $aItemWord ) > 1 ? 'initial!important' : */null, 'text-align' => 'inherit' ) ), $aItemWord ) );
						}
					}

					// Bubble Morph
					$bubbleMorph = @json_decode( $itemSlideChild -> getAttribute( 'data-bubblemorph' ), true );
					if( $bubbleMorph )
					{
						// https://codepen.io/ktarantowicz/pen/RNdZJb
						// https://blog.avada.io/css/blob-effects#blob-effect-mikhail
						//$g_aBubblePosRand = array();
						//$N_VARIANTS = 8 * 50; $N_POINTS = 4; for( $i = 0; $i < $N_VARIANTS; $i++ ) { $a = array(); for( $j = 0; $j < $N_POINTS; $j++ ) $a[] = array( rand( 0, 100 ), rand( 0, 100 ) ); $g_aBubblePosRand[] = $a; }
						//echo( '<!-- ' . str_replace( array( "\r", "\n", " " ), '', var_export( $g_aBubblePosRand, true ) ) . ' -->' );
						static $g_aBubblePosRand = array(0=>array(0=>array(0=>82,1=>82,),1=>array(0=>92,1=>68,),2=>array(0=>66,1=>69,),3=>array(0=>30,1=>100,),),1=>array(0=>array(0=>86,1=>19,),1=>array(0=>73,1=>86,),2=>array(0=>16,1=>9,),3=>array(0=>12,1=>87,),),2=>array(0=>array(0=>37,1=>78,),1=>array(0=>27,1=>5,),2=>array(0=>55,1=>92,),3=>array(0=>40,1=>7,),),3=>array(0=>array(0=>87,1=>83,),1=>array(0=>44,1=>81,),2=>array(0=>46,1=>69,),3=>array(0=>69,1=>67,),),4=>array(0=>array(0=>75,1=>93,),1=>array(0=>67,1=>84,),2=>array(0=>42,1=>77,),3=>array(0=>14,1=>34,),),5=>array(0=>array(0=>8,1=>17,),1=>array(0=>4,1=>19,),2=>array(0=>29,1=>51,),3=>array(0=>60,1=>8,),),6=>array(0=>array(0=>87,1=>98,),1=>array(0=>49,1=>15,),2=>array(0=>89,1=>52,),3=>array(0=>21,1=>27,),),7=>array(0=>array(0=>38,1=>5,),1=>array(0=>27,1=>19,),2=>array(0=>7,1=>40,),3=>array(0=>7,1=>98,),),8=>array(0=>array(0=>43,1=>93,),1=>array(0=>24,1=>73,),2=>array(0=>66,1=>75,),3=>array(0=>14,1=>75,),),9=>array(0=>array(0=>99,1=>91,),1=>array(0=>38,1=>4,),2=>array(0=>64,1=>61,),3=>array(0=>78,1=>28,),),10=>array(0=>array(0=>1,1=>20,),1=>array(0=>46,1=>28,),2=>array(0=>42,1=>71,),3=>array(0=>23,1=>45,),),11=>array(0=>array(0=>54,1=>41,),1=>array(0=>39,1=>34,),2=>array(0=>21,1=>4,),3=>array(0=>85,1=>84,),),12=>array(0=>array(0=>1,1=>66,),1=>array(0=>61,1=>38,),2=>array(0=>82,1=>32,),3=>array(0=>12,1=>25,),),13=>array(0=>array(0=>29,1=>89,),1=>array(0=>79,1=>47,),2=>array(0=>63,1=>95,),3=>array(0=>78,1=>80,),),14=>array(0=>array(0=>48,1=>28,),1=>array(0=>82,1=>62,),2=>array(0=>56,1=>23,),3=>array(0=>74,1=>68,),),15=>array(0=>array(0=>22,1=>23,),1=>array(0=>20,1=>56,),2=>array(0=>87,1=>66,),3=>array(0=>93,1=>85,),),16=>array(0=>array(0=>40,1=>4,),1=>array(0=>97,1=>14,),2=>array(0=>76,1=>35,),3=>array(0=>97,1=>11,),),17=>array(0=>array(0=>42,1=>86,),1=>array(0=>87,1=>57,),2=>array(0=>16,1=>56,),3=>array(0=>73,1=>14,),),18=>array(0=>array(0=>7,1=>19,),1=>array(0=>43,1=>71,),2=>array(0=>16,1=>82,),3=>array(0=>62,1=>41,),),19=>array(0=>array(0=>95,1=>93,),1=>array(0=>29,1=>78,),2=>array(0=>45,1=>88,),3=>array(0=>10,1=>7,),),20=>array(0=>array(0=>40,1=>0,),1=>array(0=>14,1=>76,),2=>array(0=>40,1=>72,),3=>array(0=>53,1=>91,),),21=>array(0=>array(0=>19,1=>65,),1=>array(0=>58,1=>56,),2=>array(0=>85,1=>86,),3=>array(0=>1,1=>27,),),22=>array(0=>array(0=>14,1=>34,),1=>array(0=>91,1=>57,),2=>array(0=>49,1=>65,),3=>array(0=>60,1=>65,),),23=>array(0=>array(0=>95,1=>66,),1=>array(0=>100,1=>96,),2=>array(0=>46,1=>2,),3=>array(0=>55,1=>42,),),24=>array(0=>array(0=>19,1=>79,),1=>array(0=>60,1=>85,),2=>array(0=>99,1=>54,),3=>array(0=>79,1=>26,),),25=>array(0=>array(0=>66,1=>28,),1=>array(0=>62,1=>45,),2=>array(0=>81,1=>23,),3=>array(0=>52,1=>97,),),26=>array(0=>array(0=>76,1=>75,),1=>array(0=>95,1=>11,),2=>array(0=>3,1=>78,),3=>array(0=>61,1=>39,),),27=>array(0=>array(0=>53,1=>64,),1=>array(0=>19,1=>15,),2=>array(0=>78,1=>14,),3=>array(0=>67,1=>73,),),28=>array(0=>array(0=>1,1=>10,),1=>array(0=>58,1=>92,),2=>array(0=>54,1=>92,),3=>array(0=>82,1=>68,),),29=>array(0=>array(0=>48,1=>51,),1=>array(0=>8,1=>49,),2=>array(0=>48,1=>44,),3=>array(0=>34,1=>93,),),30=>array(0=>array(0=>94,1=>12,),1=>array(0=>65,1=>58,),2=>array(0=>52,1=>24,),3=>array(0=>67,1=>16,),),31=>array(0=>array(0=>18,1=>42,),1=>array(0=>77,1=>32,),2=>array(0=>97,1=>66,),3=>array(0=>33,1=>12,),),32=>array(0=>array(0=>37,1=>91,),1=>array(0=>44,1=>47,),2=>array(0=>89,1=>84,),3=>array(0=>20,1=>57,),),33=>array(0=>array(0=>5,1=>17,),1=>array(0=>71,1=>8,),2=>array(0=>75,1=>48,),3=>array(0=>29,1=>20,),),34=>array(0=>array(0=>25,1=>24,),1=>array(0=>11,1=>99,),2=>array(0=>98,1=>87,),3=>array(0=>76,1=>18,),),35=>array(0=>array(0=>10,1=>72,),1=>array(0=>48,1=>30,),2=>array(0=>49,1=>99,),3=>array(0=>47,1=>62,),),36=>array(0=>array(0=>30,1=>33,),1=>array(0=>67,1=>38,),2=>array(0=>61,1=>75,),3=>array(0=>40,1=>96,),),37=>array(0=>array(0=>81,1=>85,),1=>array(0=>30,1=>86,),2=>array(0=>14,1=>54,),3=>array(0=>9,1=>49,),),38=>array(0=>array(0=>94,1=>29,),1=>array(0=>34,1=>33,),2=>array(0=>45,1=>32,),3=>array(0=>38,1=>82,),),39=>array(0=>array(0=>98,1=>17,),1=>array(0=>40,1=>11,),2=>array(0=>5,1=>12,),3=>array(0=>26,1=>77,),),40=>array(0=>array(0=>81,1=>37,),1=>array(0=>58,1=>86,),2=>array(0=>40,1=>60,),3=>array(0=>10,1=>63,),),41=>array(0=>array(0=>0,1=>54,),1=>array(0=>90,1=>7,),2=>array(0=>22,1=>78,),3=>array(0=>3,1=>70,),),42=>array(0=>array(0=>87,1=>97,),1=>array(0=>50,1=>54,),2=>array(0=>85,1=>20,),3=>array(0=>82,1=>10,),),43=>array(0=>array(0=>56,1=>33,),1=>array(0=>92,1=>92,),2=>array(0=>23,1=>53,),3=>array(0=>82,1=>61,),),44=>array(0=>array(0=>83,1=>81,),1=>array(0=>78,1=>47,),2=>array(0=>29,1=>46,),3=>array(0=>3,1=>49,),),45=>array(0=>array(0=>53,1=>100,),1=>array(0=>59,1=>25,),2=>array(0=>47,1=>78,),3=>array(0=>83,1=>14,),),46=>array(0=>array(0=>30,1=>100,),1=>array(0=>34,1=>86,),2=>array(0=>22,1=>87,),3=>array(0=>69,1=>7,),),47=>array(0=>array(0=>97,1=>9,),1=>array(0=>61,1=>29,),2=>array(0=>50,1=>89,),3=>array(0=>83,1=>30,),),48=>array(0=>array(0=>75,1=>44,),1=>array(0=>71,1=>58,),2=>array(0=>62,1=>55,),3=>array(0=>88,1=>92,),),49=>array(0=>array(0=>77,1=>82,),1=>array(0=>68,1=>17,),2=>array(0=>86,1=>62,),3=>array(0=>28,1=>8,),),50=>array(0=>array(0=>70,1=>97,),1=>array(0=>5,1=>63,),2=>array(0=>65,1=>39,),3=>array(0=>52,1=>47,),),51=>array(0=>array(0=>37,1=>50,),1=>array(0=>36,1=>87,),2=>array(0=>44,1=>14,),3=>array(0=>79,1=>49,),),52=>array(0=>array(0=>32,1=>77,),1=>array(0=>95,1=>13,),2=>array(0=>100,1=>55,),3=>array(0=>85,1=>31,),),53=>array(0=>array(0=>45,1=>17,),1=>array(0=>91,1=>73,),2=>array(0=>84,1=>81,),3=>array(0=>28,1=>14,),),54=>array(0=>array(0=>71,1=>9,),1=>array(0=>60,1=>38,),2=>array(0=>50,1=>59,),3=>array(0=>61,1=>75,),),55=>array(0=>array(0=>66,1=>10,),1=>array(0=>71,1=>27,),2=>array(0=>47,1=>10,),3=>array(0=>78,1=>10,),),56=>array(0=>array(0=>50,1=>75,),1=>array(0=>38,1=>61,),2=>array(0=>11,1=>15,),3=>array(0=>100,1=>8,),),57=>array(0=>array(0=>13,1=>42,),1=>array(0=>55,1=>61,),2=>array(0=>97,1=>26,),3=>array(0=>89,1=>21,),),58=>array(0=>array(0=>50,1=>37,),1=>array(0=>0,1=>90,),2=>array(0=>48,1=>74,),3=>array(0=>95,1=>74,),),59=>array(0=>array(0=>50,1=>8,),1=>array(0=>76,1=>28,),2=>array(0=>54,1=>91,),3=>array(0=>53,1=>62,),),60=>array(0=>array(0=>77,1=>82,),1=>array(0=>30,1=>70,),2=>array(0=>53,1=>0,),3=>array(0=>35,1=>11,),),61=>array(0=>array(0=>80,1=>25,),1=>array(0=>13,1=>13,),2=>array(0=>80,1=>70,),3=>array(0=>34,1=>72,),),62=>array(0=>array(0=>39,1=>80,),1=>array(0=>62,1=>28,),2=>array(0=>83,1=>85,),3=>array(0=>8,1=>2,),),63=>array(0=>array(0=>12,1=>10,),1=>array(0=>60,1=>38,),2=>array(0=>61,1=>70,),3=>array(0=>90,1=>10,),),64=>array(0=>array(0=>81,1=>69,),1=>array(0=>93,1=>94,),2=>array(0=>94,1=>7,),3=>array(0=>35,1=>57,),),65=>array(0=>array(0=>78,1=>29,),1=>array(0=>47,1=>55,),2=>array(0=>40,1=>88,),3=>array(0=>54,1=>53,),),66=>array(0=>array(0=>38,1=>53,),1=>array(0=>47,1=>30,),2=>array(0=>25,1=>100,),3=>array(0=>21,1=>72,),),67=>array(0=>array(0=>31,1=>58,),1=>array(0=>53,1=>21,),2=>array(0=>56,1=>29,),3=>array(0=>92,1=>17,),),68=>array(0=>array(0=>34,1=>88,),1=>array(0=>17,1=>61,),2=>array(0=>28,1=>61,),3=>array(0=>52,1=>53,),),69=>array(0=>array(0=>73,1=>60,),1=>array(0=>19,1=>79,),2=>array(0=>90,1=>49,),3=>array(0=>20,1=>93,),),70=>array(0=>array(0=>21,1=>46,),1=>array(0=>47,1=>99,),2=>array(0=>31,1=>70,),3=>array(0=>84,1=>92,),),71=>array(0=>array(0=>4,1=>32,),1=>array(0=>25,1=>36,),2=>array(0=>91,1=>55,),3=>array(0=>31,1=>30,),),72=>array(0=>array(0=>38,1=>40,),1=>array(0=>52,1=>92,),2=>array(0=>47,1=>92,),3=>array(0=>7,1=>68,),),73=>array(0=>array(0=>77,1=>87,),1=>array(0=>9,1=>10,),2=>array(0=>80,1=>47,),3=>array(0=>16,1=>60,),),74=>array(0=>array(0=>11,1=>100,),1=>array(0=>96,1=>67,),2=>array(0=>4,1=>1,),3=>array(0=>68,1=>57,),),75=>array(0=>array(0=>47,1=>7,),1=>array(0=>19,1=>93,),2=>array(0=>88,1=>71,),3=>array(0=>29,1=>68,),),76=>array(0=>array(0=>20,1=>4,),1=>array(0=>21,1=>94,),2=>array(0=>59,1=>80,),3=>array(0=>77,1=>8,),),77=>array(0=>array(0=>18,1=>65,),1=>array(0=>35,1=>24,),2=>array(0=>65,1=>68,),3=>array(0=>37,1=>85,),),78=>array(0=>array(0=>50,1=>16,),1=>array(0=>80,1=>34,),2=>array(0=>16,1=>72,),3=>array(0=>98,1=>33,),),79=>array(0=>array(0=>64,1=>40,),1=>array(0=>74,1=>65,),2=>array(0=>35,1=>29,),3=>array(0=>70,1=>75,),),80=>array(0=>array(0=>53,1=>59,),1=>array(0=>49,1=>56,),2=>array(0=>88,1=>20,),3=>array(0=>35,1=>49,),),81=>array(0=>array(0=>51,1=>58,),1=>array(0=>67,1=>75,),2=>array(0=>70,1=>61,),3=>array(0=>37,1=>35,),),82=>array(0=>array(0=>30,1=>54,),1=>array(0=>46,1=>93,),2=>array(0=>97,1=>33,),3=>array(0=>92,1=>46,),),83=>array(0=>array(0=>53,1=>28,),1=>array(0=>46,1=>43,),2=>array(0=>12,1=>32,),3=>array(0=>8,1=>58,),),84=>array(0=>array(0=>14,1=>28,),1=>array(0=>23,1=>69,),2=>array(0=>52,1=>36,),3=>array(0=>59,1=>66,),),85=>array(0=>array(0=>17,1=>44,),1=>array(0=>46,1=>16,),2=>array(0=>27,1=>26,),3=>array(0=>90,1=>63,),),86=>array(0=>array(0=>23,1=>25,),1=>array(0=>17,1=>64,),2=>array(0=>76,1=>87,),3=>array(0=>7,1=>100,),),87=>array(0=>array(0=>50,1=>30,),1=>array(0=>41,1=>34,),2=>array(0=>25,1=>32,),3=>array(0=>86,1=>34,),),88=>array(0=>array(0=>93,1=>62,),1=>array(0=>74,1=>41,),2=>array(0=>51,1=>2,),3=>array(0=>86,1=>32,),),89=>array(0=>array(0=>7,1=>67,),1=>array(0=>58,1=>0,),2=>array(0=>19,1=>57,),3=>array(0=>92,1=>92,),),90=>array(0=>array(0=>17,1=>13,),1=>array(0=>87,1=>73,),2=>array(0=>91,1=>14,),3=>array(0=>64,1=>18,),),91=>array(0=>array(0=>70,1=>30,),1=>array(0=>78,1=>71,),2=>array(0=>87,1=>17,),3=>array(0=>76,1=>78,),),92=>array(0=>array(0=>18,1=>85,),1=>array(0=>29,1=>49,),2=>array(0=>94,1=>76,),3=>array(0=>85,1=>42,),),93=>array(0=>array(0=>2,1=>22,),1=>array(0=>51,1=>12,),2=>array(0=>13,1=>65,),3=>array(0=>14,1=>66,),),94=>array(0=>array(0=>94,1=>63,),1=>array(0=>87,1=>82,),2=>array(0=>17,1=>56,),3=>array(0=>3,1=>68,),),95=>array(0=>array(0=>75,1=>51,),1=>array(0=>98,1=>96,),2=>array(0=>18,1=>51,),3=>array(0=>7,1=>35,),),96=>array(0=>array(0=>32,1=>96,),1=>array(0=>65,1=>14,),2=>array(0=>5,1=>41,),3=>array(0=>31,1=>32,),),97=>array(0=>array(0=>26,1=>61,),1=>array(0=>27,1=>74,),2=>array(0=>78,1=>47,),3=>array(0=>10,1=>83,),),98=>array(0=>array(0=>64,1=>46,),1=>array(0=>12,1=>89,),2=>array(0=>0,1=>7,),3=>array(0=>69,1=>25,),),99=>array(0=>array(0=>65,1=>27,),1=>array(0=>91,1=>39,),2=>array(0=>87,1=>10,),3=>array(0=>57,1=>17,),),100=>array(0=>array(0=>38,1=>65,),1=>array(0=>5,1=>40,),2=>array(0=>64,1=>43,),3=>array(0=>34,1=>97,),),101=>array(0=>array(0=>12,1=>33,),1=>array(0=>23,1=>33,),2=>array(0=>15,1=>41,),3=>array(0=>94,1=>28,),),102=>array(0=>array(0=>2,1=>37,),1=>array(0=>42,1=>8,),2=>array(0=>40,1=>27,),3=>array(0=>97,1=>54,),),103=>array(0=>array(0=>45,1=>99,),1=>array(0=>24,1=>76,),2=>array(0=>18,1=>26,),3=>array(0=>37,1=>44,),),104=>array(0=>array(0=>69,1=>5,),1=>array(0=>47,1=>75,),2=>array(0=>79,1=>31,),3=>array(0=>96,1=>36,),),105=>array(0=>array(0=>30,1=>75,),1=>array(0=>66,1=>51,),2=>array(0=>92,1=>49,),3=>array(0=>52,1=>18,),),106=>array(0=>array(0=>54,1=>32,),1=>array(0=>32,1=>12,),2=>array(0=>33,1=>29,),3=>array(0=>7,1=>40,),),107=>array(0=>array(0=>25,1=>52,),1=>array(0=>96,1=>87,),2=>array(0=>57,1=>60,),3=>array(0=>64,1=>6,),),108=>array(0=>array(0=>77,1=>98,),1=>array(0=>93,1=>1,),2=>array(0=>61,1=>76,),3=>array(0=>8,1=>58,),),109=>array(0=>array(0=>75,1=>37,),1=>array(0=>85,1=>10,),2=>array(0=>27,1=>27,),3=>array(0=>39,1=>92,),),110=>array(0=>array(0=>5,1=>85,),1=>array(0=>91,1=>33,),2=>array(0=>98,1=>6,),3=>array(0=>60,1=>33,),),111=>array(0=>array(0=>38,1=>64,),1=>array(0=>31,1=>49,),2=>array(0=>48,1=>69,),3=>array(0=>57,1=>7,),),112=>array(0=>array(0=>64,1=>28,),1=>array(0=>24,1=>2,),2=>array(0=>36,1=>19,),3=>array(0=>42,1=>63,),),113=>array(0=>array(0=>1,1=>1,),1=>array(0=>72,1=>95,),2=>array(0=>70,1=>3,),3=>array(0=>83,1=>71,),),114=>array(0=>array(0=>33,1=>11,),1=>array(0=>35,1=>99,),2=>array(0=>31,1=>62,),3=>array(0=>69,1=>58,),),115=>array(0=>array(0=>95,1=>9,),1=>array(0=>40,1=>36,),2=>array(0=>49,1=>99,),3=>array(0=>0,1=>69,),),116=>array(0=>array(0=>24,1=>70,),1=>array(0=>11,1=>68,),2=>array(0=>41,1=>8,),3=>array(0=>83,1=>45,),),117=>array(0=>array(0=>71,1=>94,),1=>array(0=>97,1=>90,),2=>array(0=>38,1=>87,),3=>array(0=>100,1=>51,),),118=>array(0=>array(0=>17,1=>57,),1=>array(0=>20,1=>88,),2=>array(0=>28,1=>41,),3=>array(0=>36,1=>95,),),119=>array(0=>array(0=>94,1=>33,),1=>array(0=>58,1=>73,),2=>array(0=>75,1=>64,),3=>array(0=>24,1=>10,),),120=>array(0=>array(0=>54,1=>12,),1=>array(0=>59,1=>56,),2=>array(0=>98,1=>61,),3=>array(0=>39,1=>6,),),121=>array(0=>array(0=>50,1=>36,),1=>array(0=>9,1=>87,),2=>array(0=>74,1=>34,),3=>array(0=>75,1=>40,),),122=>array(0=>array(0=>3,1=>71,),1=>array(0=>92,1=>3,),2=>array(0=>47,1=>73,),3=>array(0=>48,1=>80,),),123=>array(0=>array(0=>64,1=>8,),1=>array(0=>58,1=>90,),2=>array(0=>85,1=>81,),3=>array(0=>72,1=>22,),),124=>array(0=>array(0=>48,1=>72,),1=>array(0=>69,1=>11,),2=>array(0=>5,1=>69,),3=>array(0=>82,1=>16,),),125=>array(0=>array(0=>99,1=>49,),1=>array(0=>47,1=>17,),2=>array(0=>74,1=>98,),3=>array(0=>56,1=>41,),),126=>array(0=>array(0=>89,1=>9,),1=>array(0=>91,1=>0,),2=>array(0=>53,1=>90,),3=>array(0=>12,1=>30,),),127=>array(0=>array(0=>98,1=>22,),1=>array(0=>2,1=>27,),2=>array(0=>84,1=>10,),3=>array(0=>73,1=>90,),),128=>array(0=>array(0=>17,1=>66,),1=>array(0=>6,1=>15,),2=>array(0=>23,1=>91,),3=>array(0=>58,1=>44,),),129=>array(0=>array(0=>79,1=>24,),1=>array(0=>7,1=>87,),2=>array(0=>41,1=>90,),3=>array(0=>33,1=>96,),),130=>array(0=>array(0=>89,1=>10,),1=>array(0=>32,1=>99,),2=>array(0=>35,1=>7,),3=>array(0=>72,1=>51,),),131=>array(0=>array(0=>44,1=>43,),1=>array(0=>32,1=>34,),2=>array(0=>10,1=>5,),3=>array(0=>49,1=>40,),),132=>array(0=>array(0=>63,1=>18,),1=>array(0=>79,1=>77,),2=>array(0=>78,1=>12,),3=>array(0=>61,1=>23,),),133=>array(0=>array(0=>39,1=>21,),1=>array(0=>5,1=>8,),2=>array(0=>41,1=>89,),3=>array(0=>63,1=>19,),),134=>array(0=>array(0=>5,1=>73,),1=>array(0=>67,1=>32,),2=>array(0=>7,1=>91,),3=>array(0=>44,1=>5,),),135=>array(0=>array(0=>5,1=>44,),1=>array(0=>87,1=>62,),2=>array(0=>38,1=>79,),3=>array(0=>63,1=>54,),),136=>array(0=>array(0=>56,1=>5,),1=>array(0=>81,1=>68,),2=>array(0=>10,1=>29,),3=>array(0=>100,1=>36,),),137=>array(0=>array(0=>92,1=>71,),1=>array(0=>90,1=>9,),2=>array(0=>65,1=>76,),3=>array(0=>26,1=>87,),),138=>array(0=>array(0=>11,1=>48,),1=>array(0=>56,1=>91,),2=>array(0=>93,1=>64,),3=>array(0=>99,1=>2,),),139=>array(0=>array(0=>7,1=>26,),1=>array(0=>60,1=>74,),2=>array(0=>65,1=>89,),3=>array(0=>76,1=>26,),),140=>array(0=>array(0=>3,1=>31,),1=>array(0=>48,1=>41,),2=>array(0=>64,1=>64,),3=>array(0=>63,1=>7,),),141=>array(0=>array(0=>54,1=>15,),1=>array(0=>94,1=>58,),2=>array(0=>61,1=>22,),3=>array(0=>33,1=>81,),),142=>array(0=>array(0=>86,1=>46,),1=>array(0=>76,1=>8,),2=>array(0=>15,1=>20,),3=>array(0=>65,1=>66,),),143=>array(0=>array(0=>80,1=>84,),1=>array(0=>56,1=>29,),2=>array(0=>75,1=>36,),3=>array(0=>73,1=>86,),),144=>array(0=>array(0=>71,1=>16,),1=>array(0=>13,1=>36,),2=>array(0=>4,1=>16,),3=>array(0=>72,1=>9,),),145=>array(0=>array(0=>55,1=>88,),1=>array(0=>4,1=>58,),2=>array(0=>19,1=>84,),3=>array(0=>62,1=>25,),),146=>array(0=>array(0=>73,1=>38,),1=>array(0=>43,1=>13,),2=>array(0=>30,1=>4,),3=>array(0=>73,1=>79,),),147=>array(0=>array(0=>17,1=>54,),1=>array(0=>33,1=>78,),2=>array(0=>14,1=>13,),3=>array(0=>97,1=>65,),),148=>array(0=>array(0=>27,1=>5,),1=>array(0=>15,1=>39,),2=>array(0=>38,1=>72,),3=>array(0=>18,1=>11,),),149=>array(0=>array(0=>78,1=>99,),1=>array(0=>54,1=>20,),2=>array(0=>71,1=>8,),3=>array(0=>4,1=>64,),),150=>array(0=>array(0=>58,1=>51,),1=>array(0=>69,1=>44,),2=>array(0=>33,1=>19,),3=>array(0=>67,1=>88,),),151=>array(0=>array(0=>69,1=>33,),1=>array(0=>22,1=>64,),2=>array(0=>30,1=>61,),3=>array(0=>75,1=>96,),),152=>array(0=>array(0=>38,1=>89,),1=>array(0=>96,1=>25,),2=>array(0=>43,1=>83,),3=>array(0=>20,1=>30,),),153=>array(0=>array(0=>87,1=>44,),1=>array(0=>84,1=>51,),2=>array(0=>1,1=>94,),3=>array(0=>92,1=>88,),),154=>array(0=>array(0=>43,1=>46,),1=>array(0=>37,1=>90,),2=>array(0=>5,1=>13,),3=>array(0=>58,1=>85,),),155=>array(0=>array(0=>37,1=>57,),1=>array(0=>98,1=>75,),2=>array(0=>90,1=>62,),3=>array(0=>3,1=>61,),),156=>array(0=>array(0=>25,1=>68,),1=>array(0=>30,1=>36,),2=>array(0=>10,1=>48,),3=>array(0=>44,1=>15,),),157=>array(0=>array(0=>8,1=>22,),1=>array(0=>91,1=>46,),2=>array(0=>80,1=>64,),3=>array(0=>72,1=>62,),),158=>array(0=>array(0=>96,1=>60,),1=>array(0=>89,1=>53,),2=>array(0=>78,1=>73,),3=>array(0=>70,1=>27,),),159=>array(0=>array(0=>42,1=>65,),1=>array(0=>51,1=>77,),2=>array(0=>98,1=>36,),3=>array(0=>53,1=>67,),),160=>array(0=>array(0=>19,1=>2,),1=>array(0=>70,1=>54,),2=>array(0=>45,1=>2,),3=>array(0=>1,1=>0,),),161=>array(0=>array(0=>3,1=>99,),1=>array(0=>58,1=>5,),2=>array(0=>26,1=>45,),3=>array(0=>15,1=>33,),),162=>array(0=>array(0=>88,1=>9,),1=>array(0=>50,1=>97,),2=>array(0=>46,1=>27,),3=>array(0=>50,1=>45,),),163=>array(0=>array(0=>94,1=>24,),1=>array(0=>62,1=>40,),2=>array(0=>52,1=>72,),3=>array(0=>10,1=>13,),),164=>array(0=>array(0=>33,1=>14,),1=>array(0=>6,1=>31,),2=>array(0=>16,1=>36,),3=>array(0=>20,1=>72,),),165=>array(0=>array(0=>43,1=>78,),1=>array(0=>76,1=>67,),2=>array(0=>49,1=>26,),3=>array(0=>94,1=>15,),),166=>array(0=>array(0=>5,1=>65,),1=>array(0=>11,1=>82,),2=>array(0=>20,1=>37,),3=>array(0=>12,1=>15,),),167=>array(0=>array(0=>47,1=>26,),1=>array(0=>97,1=>70,),2=>array(0=>22,1=>62,),3=>array(0=>60,1=>66,),),168=>array(0=>array(0=>39,1=>21,),1=>array(0=>23,1=>55,),2=>array(0=>76,1=>4,),3=>array(0=>76,1=>66,),),169=>array(0=>array(0=>77,1=>85,),1=>array(0=>77,1=>5,),2=>array(0=>82,1=>61,),3=>array(0=>7,1=>82,),),170=>array(0=>array(0=>16,1=>29,),1=>array(0=>54,1=>24,),2=>array(0=>60,1=>0,),3=>array(0=>12,1=>72,),),171=>array(0=>array(0=>81,1=>29,),1=>array(0=>62,1=>30,),2=>array(0=>11,1=>17,),3=>array(0=>69,1=>53,),),172=>array(0=>array(0=>92,1=>95,),1=>array(0=>2,1=>58,),2=>array(0=>1,1=>82,),3=>array(0=>73,1=>13,),),173=>array(0=>array(0=>33,1=>19,),1=>array(0=>90,1=>42,),2=>array(0=>32,1=>72,),3=>array(0=>25,1=>72,),),174=>array(0=>array(0=>19,1=>96,),1=>array(0=>60,1=>31,),2=>array(0=>7,1=>96,),3=>array(0=>11,1=>69,),),175=>array(0=>array(0=>51,1=>41,),1=>array(0=>27,1=>97,),2=>array(0=>39,1=>24,),3=>array(0=>85,1=>41,),),176=>array(0=>array(0=>48,1=>28,),1=>array(0=>71,1=>62,),2=>array(0=>22,1=>14,),3=>array(0=>69,1=>92,),),177=>array(0=>array(0=>5,1=>25,),1=>array(0=>18,1=>48,),2=>array(0=>2,1=>95,),3=>array(0=>3,1=>59,),),178=>array(0=>array(0=>96,1=>37,),1=>array(0=>50,1=>90,),2=>array(0=>27,1=>49,),3=>array(0=>3,1=>71,),),179=>array(0=>array(0=>74,1=>9,),1=>array(0=>55,1=>12,),2=>array(0=>19,1=>5,),3=>array(0=>97,1=>27,),),180=>array(0=>array(0=>33,1=>73,),1=>array(0=>15,1=>43,),2=>array(0=>88,1=>81,),3=>array(0=>21,1=>82,),),181=>array(0=>array(0=>39,1=>49,),1=>array(0=>73,1=>10,),2=>array(0=>47,1=>96,),3=>array(0=>37,1=>54,),),182=>array(0=>array(0=>21,1=>16,),1=>array(0=>54,1=>99,),2=>array(0=>84,1=>33,),3=>array(0=>97,1=>13,),),183=>array(0=>array(0=>34,1=>13,),1=>array(0=>78,1=>88,),2=>array(0=>42,1=>19,),3=>array(0=>57,1=>44,),),184=>array(0=>array(0=>18,1=>82,),1=>array(0=>12,1=>100,),2=>array(0=>73,1=>26,),3=>array(0=>60,1=>43,),),185=>array(0=>array(0=>66,1=>71,),1=>array(0=>71,1=>26,),2=>array(0=>15,1=>100,),3=>array(0=>24,1=>93,),),186=>array(0=>array(0=>95,1=>73,),1=>array(0=>74,1=>79,),2=>array(0=>22,1=>26,),3=>array(0=>58,1=>64,),),187=>array(0=>array(0=>94,1=>22,),1=>array(0=>80,1=>98,),2=>array(0=>48,1=>62,),3=>array(0=>92,1=>2,),),188=>array(0=>array(0=>63,1=>8,),1=>array(0=>40,1=>81,),2=>array(0=>83,1=>43,),3=>array(0=>29,1=>53,),),189=>array(0=>array(0=>18,1=>66,),1=>array(0=>26,1=>82,),2=>array(0=>93,1=>70,),3=>array(0=>29,1=>66,),),190=>array(0=>array(0=>61,1=>0,),1=>array(0=>24,1=>57,),2=>array(0=>31,1=>94,),3=>array(0=>34,1=>83,),),191=>array(0=>array(0=>31,1=>66,),1=>array(0=>31,1=>87,),2=>array(0=>62,1=>92,),3=>array(0=>2,1=>66,),),192=>array(0=>array(0=>28,1=>54,),1=>array(0=>65,1=>36,),2=>array(0=>90,1=>36,),3=>array(0=>76,1=>6,),),193=>array(0=>array(0=>16,1=>74,),1=>array(0=>69,1=>24,),2=>array(0=>34,1=>39,),3=>array(0=>32,1=>76,),),194=>array(0=>array(0=>89,1=>100,),1=>array(0=>49,1=>37,),2=>array(0=>40,1=>10,),3=>array(0=>67,1=>98,),),195=>array(0=>array(0=>59,1=>63,),1=>array(0=>71,1=>46,),2=>array(0=>1,1=>18,),3=>array(0=>53,1=>33,),),196=>array(0=>array(0=>12,1=>2,),1=>array(0=>81,1=>8,),2=>array(0=>36,1=>30,),3=>array(0=>62,1=>14,),),197=>array(0=>array(0=>73,1=>55,),1=>array(0=>30,1=>8,),2=>array(0=>59,1=>16,),3=>array(0=>54,1=>91,),),198=>array(0=>array(0=>34,1=>28,),1=>array(0=>90,1=>49,),2=>array(0=>100,1=>40,),3=>array(0=>80,1=>61,),),199=>array(0=>array(0=>25,1=>13,),1=>array(0=>69,1=>38,),2=>array(0=>99,1=>96,),3=>array(0=>31,1=>62,),),200=>array(0=>array(0=>16,1=>84,),1=>array(0=>0,1=>95,),2=>array(0=>58,1=>63,),3=>array(0=>59,1=>7,),),201=>array(0=>array(0=>51,1=>11,),1=>array(0=>74,1=>45,),2=>array(0=>39,1=>32,),3=>array(0=>24,1=>37,),),202=>array(0=>array(0=>34,1=>39,),1=>array(0=>83,1=>28,),2=>array(0=>52,1=>32,),3=>array(0=>46,1=>40,),),203=>array(0=>array(0=>45,1=>80,),1=>array(0=>99,1=>96,),2=>array(0=>51,1=>74,),3=>array(0=>8,1=>65,),),204=>array(0=>array(0=>3,1=>42,),1=>array(0=>78,1=>65,),2=>array(0=>84,1=>20,),3=>array(0=>62,1=>99,),),205=>array(0=>array(0=>32,1=>62,),1=>array(0=>56,1=>50,),2=>array(0=>60,1=>69,),3=>array(0=>10,1=>27,),),206=>array(0=>array(0=>40,1=>94,),1=>array(0=>49,1=>81,),2=>array(0=>94,1=>30,),3=>array(0=>54,1=>56,),),207=>array(0=>array(0=>40,1=>24,),1=>array(0=>48,1=>71,),2=>array(0=>62,1=>39,),3=>array(0=>44,1=>60,),),208=>array(0=>array(0=>18,1=>60,),1=>array(0=>78,1=>99,),2=>array(0=>9,1=>59,),3=>array(0=>74,1=>55,),),209=>array(0=>array(0=>83,1=>92,),1=>array(0=>83,1=>1,),2=>array(0=>42,1=>33,),3=>array(0=>10,1=>56,),),210=>array(0=>array(0=>86,1=>82,),1=>array(0=>70,1=>29,),2=>array(0=>89,1=>49,),3=>array(0=>47,1=>81,),),211=>array(0=>array(0=>0,1=>75,),1=>array(0=>58,1=>85,),2=>array(0=>66,1=>43,),3=>array(0=>86,1=>18,),),212=>array(0=>array(0=>85,1=>42,),1=>array(0=>6,1=>26,),2=>array(0=>58,1=>42,),3=>array(0=>0,1=>81,),),213=>array(0=>array(0=>76,1=>4,),1=>array(0=>94,1=>94,),2=>array(0=>85,1=>29,),3=>array(0=>97,1=>3,),),214=>array(0=>array(0=>67,1=>78,),1=>array(0=>94,1=>67,),2=>array(0=>13,1=>46,),3=>array(0=>64,1=>43,),),215=>array(0=>array(0=>96,1=>1,),1=>array(0=>63,1=>58,),2=>array(0=>50,1=>67,),3=>array(0=>88,1=>33,),),216=>array(0=>array(0=>43,1=>49,),1=>array(0=>55,1=>17,),2=>array(0=>92,1=>65,),3=>array(0=>0,1=>89,),),217=>array(0=>array(0=>3,1=>48,),1=>array(0=>45,1=>40,),2=>array(0=>3,1=>65,),3=>array(0=>97,1=>35,),),218=>array(0=>array(0=>51,1=>61,),1=>array(0=>82,1=>27,),2=>array(0=>93,1=>60,),3=>array(0=>0,1=>80,),),219=>array(0=>array(0=>44,1=>63,),1=>array(0=>51,1=>48,),2=>array(0=>98,1=>71,),3=>array(0=>17,1=>32,),),220=>array(0=>array(0=>20,1=>39,),1=>array(0=>49,1=>11,),2=>array(0=>56,1=>72,),3=>array(0=>18,1=>26,),),221=>array(0=>array(0=>74,1=>11,),1=>array(0=>19,1=>87,),2=>array(0=>79,1=>16,),3=>array(0=>80,1=>72,),),222=>array(0=>array(0=>31,1=>98,),1=>array(0=>32,1=>58,),2=>array(0=>99,1=>86,),3=>array(0=>27,1=>95,),),223=>array(0=>array(0=>20,1=>16,),1=>array(0=>68,1=>16,),2=>array(0=>81,1=>23,),3=>array(0=>83,1=>24,),),224=>array(0=>array(0=>79,1=>38,),1=>array(0=>45,1=>10,),2=>array(0=>4,1=>70,),3=>array(0=>36,1=>42,),),225=>array(0=>array(0=>82,1=>33,),1=>array(0=>76,1=>86,),2=>array(0=>64,1=>74,),3=>array(0=>13,1=>52,),),226=>array(0=>array(0=>9,1=>49,),1=>array(0=>78,1=>78,),2=>array(0=>71,1=>93,),3=>array(0=>27,1=>8,),),227=>array(0=>array(0=>14,1=>66,),1=>array(0=>84,1=>54,),2=>array(0=>22,1=>51,),3=>array(0=>9,1=>63,),),228=>array(0=>array(0=>75,1=>15,),1=>array(0=>92,1=>88,),2=>array(0=>29,1=>7,),3=>array(0=>68,1=>41,),),229=>array(0=>array(0=>75,1=>26,),1=>array(0=>74,1=>24,),2=>array(0=>25,1=>92,),3=>array(0=>75,1=>68,),),230=>array(0=>array(0=>78,1=>82,),1=>array(0=>89,1=>45,),2=>array(0=>76,1=>70,),3=>array(0=>45,1=>27,),),231=>array(0=>array(0=>62,1=>22,),1=>array(0=>88,1=>20,),2=>array(0=>15,1=>6,),3=>array(0=>71,1=>69,),),232=>array(0=>array(0=>69,1=>63,),1=>array(0=>77,1=>70,),2=>array(0=>8,1=>74,),3=>array(0=>41,1=>99,),),233=>array(0=>array(0=>52,1=>76,),1=>array(0=>57,1=>0,),2=>array(0=>55,1=>55,),3=>array(0=>15,1=>36,),),234=>array(0=>array(0=>41,1=>5,),1=>array(0=>5,1=>7,),2=>array(0=>79,1=>4,),3=>array(0=>24,1=>7,),),235=>array(0=>array(0=>52,1=>16,),1=>array(0=>19,1=>65,),2=>array(0=>26,1=>43,),3=>array(0=>80,1=>60,),),236=>array(0=>array(0=>25,1=>56,),1=>array(0=>97,1=>47,),2=>array(0=>44,1=>17,),3=>array(0=>90,1=>80,),),237=>array(0=>array(0=>60,1=>96,),1=>array(0=>79,1=>28,),2=>array(0=>72,1=>62,),3=>array(0=>86,1=>73,),),238=>array(0=>array(0=>72,1=>65,),1=>array(0=>63,1=>21,),2=>array(0=>86,1=>57,),3=>array(0=>37,1=>86,),),239=>array(0=>array(0=>75,1=>58,),1=>array(0=>65,1=>66,),2=>array(0=>33,1=>69,),3=>array(0=>82,1=>7,),),240=>array(0=>array(0=>1,1=>29,),1=>array(0=>44,1=>30,),2=>array(0=>36,1=>64,),3=>array(0=>60,1=>83,),),241=>array(0=>array(0=>87,1=>36,),1=>array(0=>86,1=>84,),2=>array(0=>24,1=>84,),3=>array(0=>50,1=>37,),),242=>array(0=>array(0=>84,1=>39,),1=>array(0=>67,1=>14,),2=>array(0=>84,1=>32,),3=>array(0=>33,1=>0,),),243=>array(0=>array(0=>27,1=>22,),1=>array(0=>21,1=>46,),2=>array(0=>26,1=>85,),3=>array(0=>83,1=>19,),),244=>array(0=>array(0=>72,1=>36,),1=>array(0=>80,1=>78,),2=>array(0=>56,1=>25,),3=>array(0=>38,1=>67,),),245=>array(0=>array(0=>92,1=>53,),1=>array(0=>5,1=>31,),2=>array(0=>77,1=>74,),3=>array(0=>91,1=>46,),),246=>array(0=>array(0=>84,1=>78,),1=>array(0=>18,1=>45,),2=>array(0=>56,1=>89,),3=>array(0=>99,1=>21,),),247=>array(0=>array(0=>37,1=>67,),1=>array(0=>52,1=>30,),2=>array(0=>3,1=>15,),3=>array(0=>55,1=>82,),),248=>array(0=>array(0=>97,1=>31,),1=>array(0=>44,1=>60,),2=>array(0=>17,1=>86,),3=>array(0=>56,1=>95,),),249=>array(0=>array(0=>13,1=>52,),1=>array(0=>33,1=>56,),2=>array(0=>44,1=>24,),3=>array(0=>55,1=>1,),),250=>array(0=>array(0=>4,1=>87,),1=>array(0=>83,1=>39,),2=>array(0=>78,1=>32,),3=>array(0=>29,1=>92,),),251=>array(0=>array(0=>4,1=>85,),1=>array(0=>95,1=>42,),2=>array(0=>90,1=>64,),3=>array(0=>7,1=>37,),),252=>array(0=>array(0=>12,1=>57,),1=>array(0=>48,1=>0,),2=>array(0=>95,1=>9,),3=>array(0=>34,1=>53,),),253=>array(0=>array(0=>16,1=>94,),1=>array(0=>44,1=>35,),2=>array(0=>66,1=>63,),3=>array(0=>43,1=>72,),),254=>array(0=>array(0=>32,1=>65,),1=>array(0=>30,1=>76,),2=>array(0=>38,1=>61,),3=>array(0=>8,1=>29,),),255=>array(0=>array(0=>58,1=>84,),1=>array(0=>18,1=>77,),2=>array(0=>95,1=>27,),3=>array(0=>12,1=>62,),),256=>array(0=>array(0=>25,1=>78,),1=>array(0=>55,1=>92,),2=>array(0=>93,1=>43,),3=>array(0=>47,1=>49,),),257=>array(0=>array(0=>1,1=>48,),1=>array(0=>93,1=>59,),2=>array(0=>20,1=>94,),3=>array(0=>81,1=>44,),),258=>array(0=>array(0=>64,1=>42,),1=>array(0=>11,1=>38,),2=>array(0=>17,1=>76,),3=>array(0=>100,1=>43,),),259=>array(0=>array(0=>64,1=>21,),1=>array(0=>34,1=>88,),2=>array(0=>98,1=>15,),3=>array(0=>16,1=>2,),),260=>array(0=>array(0=>2,1=>54,),1=>array(0=>38,1=>49,),2=>array(0=>40,1=>4,),3=>array(0=>6,1=>80,),),261=>array(0=>array(0=>2,1=>19,),1=>array(0=>48,1=>100,),2=>array(0=>26,1=>93,),3=>array(0=>1,1=>91,),),262=>array(0=>array(0=>88,1=>36,),1=>array(0=>98,1=>30,),2=>array(0=>78,1=>26,),3=>array(0=>78,1=>94,),),263=>array(0=>array(0=>26,1=>17,),1=>array(0=>36,1=>39,),2=>array(0=>6,1=>94,),3=>array(0=>58,1=>41,),),264=>array(0=>array(0=>63,1=>38,),1=>array(0=>81,1=>73,),2=>array(0=>89,1=>38,),3=>array(0=>98,1=>34,),),265=>array(0=>array(0=>11,1=>48,),1=>array(0=>1,1=>5,),2=>array(0=>25,1=>1,),3=>array(0=>20,1=>62,),),266=>array(0=>array(0=>92,1=>91,),1=>array(0=>34,1=>93,),2=>array(0=>7,1=>35,),3=>array(0=>88,1=>62,),),267=>array(0=>array(0=>97,1=>9,),1=>array(0=>17,1=>65,),2=>array(0=>36,1=>100,),3=>array(0=>60,1=>24,),),268=>array(0=>array(0=>70,1=>18,),1=>array(0=>31,1=>49,),2=>array(0=>70,1=>58,),3=>array(0=>98,1=>99,),),269=>array(0=>array(0=>95,1=>91,),1=>array(0=>25,1=>80,),2=>array(0=>69,1=>40,),3=>array(0=>48,1=>65,),),270=>array(0=>array(0=>56,1=>33,),1=>array(0=>1,1=>86,),2=>array(0=>41,1=>23,),3=>array(0=>93,1=>78,),),271=>array(0=>array(0=>78,1=>89,),1=>array(0=>13,1=>69,),2=>array(0=>77,1=>81,),3=>array(0=>21,1=>77,),),272=>array(0=>array(0=>82,1=>33,),1=>array(0=>22,1=>67,),2=>array(0=>79,1=>16,),3=>array(0=>62,1=>60,),),273=>array(0=>array(0=>64,1=>29,),1=>array(0=>42,1=>37,),2=>array(0=>12,1=>4,),3=>array(0=>27,1=>54,),),274=>array(0=>array(0=>100,1=>95,),1=>array(0=>91,1=>81,),2=>array(0=>66,1=>6,),3=>array(0=>27,1=>21,),),275=>array(0=>array(0=>63,1=>45,),1=>array(0=>37,1=>89,),2=>array(0=>54,1=>48,),3=>array(0=>13,1=>15,),),276=>array(0=>array(0=>87,1=>77,),1=>array(0=>7,1=>71,),2=>array(0=>73,1=>17,),3=>array(0=>84,1=>8,),),277=>array(0=>array(0=>47,1=>58,),1=>array(0=>23,1=>11,),2=>array(0=>32,1=>14,),3=>array(0=>70,1=>36,),),278=>array(0=>array(0=>27,1=>86,),1=>array(0=>52,1=>91,),2=>array(0=>31,1=>34,),3=>array(0=>42,1=>42,),),279=>array(0=>array(0=>2,1=>16,),1=>array(0=>25,1=>17,),2=>array(0=>26,1=>78,),3=>array(0=>12,1=>62,),),280=>array(0=>array(0=>13,1=>28,),1=>array(0=>3,1=>35,),2=>array(0=>79,1=>15,),3=>array(0=>95,1=>34,),),281=>array(0=>array(0=>48,1=>35,),1=>array(0=>5,1=>51,),2=>array(0=>85,1=>42,),3=>array(0=>36,1=>18,),),282=>array(0=>array(0=>21,1=>16,),1=>array(0=>20,1=>59,),2=>array(0=>77,1=>1,),3=>array(0=>85,1=>95,),),283=>array(0=>array(0=>0,1=>78,),1=>array(0=>98,1=>46,),2=>array(0=>37,1=>73,),3=>array(0=>3,1=>44,),),284=>array(0=>array(0=>5,1=>96,),1=>array(0=>48,1=>11,),2=>array(0=>43,1=>24,),3=>array(0=>42,1=>96,),),285=>array(0=>array(0=>99,1=>63,),1=>array(0=>62,1=>74,),2=>array(0=>57,1=>45,),3=>array(0=>5,1=>65,),),286=>array(0=>array(0=>9,1=>2,),1=>array(0=>28,1=>15,),2=>array(0=>52,1=>64,),3=>array(0=>47,1=>9,),),287=>array(0=>array(0=>40,1=>2,),1=>array(0=>22,1=>69,),2=>array(0=>41,1=>97,),3=>array(0=>6,1=>40,),),288=>array(0=>array(0=>65,1=>98,),1=>array(0=>90,1=>1,),2=>array(0=>67,1=>34,),3=>array(0=>30,1=>41,),),289=>array(0=>array(0=>47,1=>21,),1=>array(0=>63,1=>12,),2=>array(0=>61,1=>96,),3=>array(0=>12,1=>43,),),290=>array(0=>array(0=>26,1=>90,),1=>array(0=>73,1=>85,),2=>array(0=>32,1=>36,),3=>array(0=>0,1=>37,),),291=>array(0=>array(0=>41,1=>50,),1=>array(0=>40,1=>92,),2=>array(0=>44,1=>34,),3=>array(0=>39,1=>55,),),292=>array(0=>array(0=>20,1=>92,),1=>array(0=>63,1=>9,),2=>array(0=>8,1=>25,),3=>array(0=>41,1=>96,),),293=>array(0=>array(0=>33,1=>48,),1=>array(0=>33,1=>14,),2=>array(0=>70,1=>98,),3=>array(0=>22,1=>70,),),294=>array(0=>array(0=>80,1=>66,),1=>array(0=>22,1=>92,),2=>array(0=>51,1=>88,),3=>array(0=>38,1=>60,),),295=>array(0=>array(0=>79,1=>28,),1=>array(0=>53,1=>73,),2=>array(0=>3,1=>87,),3=>array(0=>28,1=>79,),),296=>array(0=>array(0=>71,1=>4,),1=>array(0=>89,1=>18,),2=>array(0=>21,1=>40,),3=>array(0=>28,1=>54,),),297=>array(0=>array(0=>24,1=>4,),1=>array(0=>86,1=>94,),2=>array(0=>95,1=>2,),3=>array(0=>71,1=>100,),),298=>array(0=>array(0=>99,1=>40,),1=>array(0=>97,1=>10,),2=>array(0=>87,1=>25,),3=>array(0=>46,1=>54,),),299=>array(0=>array(0=>49,1=>77,),1=>array(0=>66,1=>3,),2=>array(0=>39,1=>45,),3=>array(0=>2,1=>95,),),300=>array(0=>array(0=>54,1=>8,),1=>array(0=>33,1=>72,),2=>array(0=>7,1=>44,),3=>array(0=>79,1=>24,),),301=>array(0=>array(0=>89,1=>14,),1=>array(0=>0,1=>79,),2=>array(0=>69,1=>23,),3=>array(0=>82,1=>8,),),302=>array(0=>array(0=>55,1=>38,),1=>array(0=>63,1=>87,),2=>array(0=>12,1=>48,),3=>array(0=>56,1=>28,),),303=>array(0=>array(0=>60,1=>63,),1=>array(0=>72,1=>43,),2=>array(0=>27,1=>3,),3=>array(0=>79,1=>75,),),304=>array(0=>array(0=>76,1=>38,),1=>array(0=>47,1=>96,),2=>array(0=>97,1=>24,),3=>array(0=>70,1=>25,),),305=>array(0=>array(0=>4,1=>11,),1=>array(0=>10,1=>76,),2=>array(0=>25,1=>91,),3=>array(0=>56,1=>20,),),306=>array(0=>array(0=>41,1=>28,),1=>array(0=>66,1=>63,),2=>array(0=>50,1=>31,),3=>array(0=>21,1=>97,),),307=>array(0=>array(0=>9,1=>13,),1=>array(0=>21,1=>15,),2=>array(0=>62,1=>21,),3=>array(0=>43,1=>50,),),308=>array(0=>array(0=>85,1=>22,),1=>array(0=>45,1=>94,),2=>array(0=>7,1=>51,),3=>array(0=>46,1=>24,),),309=>array(0=>array(0=>85,1=>5,),1=>array(0=>27,1=>63,),2=>array(0=>49,1=>82,),3=>array(0=>44,1=>45,),),310=>array(0=>array(0=>54,1=>100,),1=>array(0=>9,1=>1,),2=>array(0=>45,1=>2,),3=>array(0=>99,1=>40,),),311=>array(0=>array(0=>36,1=>0,),1=>array(0=>24,1=>34,),2=>array(0=>55,1=>65,),3=>array(0=>39,1=>6,),),312=>array(0=>array(0=>27,1=>14,),1=>array(0=>18,1=>50,),2=>array(0=>9,1=>9,),3=>array(0=>56,1=>99,),),313=>array(0=>array(0=>83,1=>100,),1=>array(0=>95,1=>94,),2=>array(0=>81,1=>17,),3=>array(0=>88,1=>2,),),314=>array(0=>array(0=>30,1=>90,),1=>array(0=>28,1=>14,),2=>array(0=>44,1=>99,),3=>array(0=>50,1=>47,),),315=>array(0=>array(0=>50,1=>76,),1=>array(0=>41,1=>64,),2=>array(0=>17,1=>38,),3=>array(0=>40,1=>57,),),316=>array(0=>array(0=>10,1=>98,),1=>array(0=>78,1=>16,),2=>array(0=>42,1=>58,),3=>array(0=>53,1=>78,),),317=>array(0=>array(0=>5,1=>65,),1=>array(0=>90,1=>72,),2=>array(0=>12,1=>28,),3=>array(0=>30,1=>95,),),318=>array(0=>array(0=>28,1=>72,),1=>array(0=>55,1=>93,),2=>array(0=>21,1=>33,),3=>array(0=>100,1=>44,),),319=>array(0=>array(0=>18,1=>84,),1=>array(0=>21,1=>75,),2=>array(0=>44,1=>11,),3=>array(0=>6,1=>48,),),320=>array(0=>array(0=>44,1=>21,),1=>array(0=>91,1=>34,),2=>array(0=>57,1=>8,),3=>array(0=>34,1=>59,),),321=>array(0=>array(0=>44,1=>82,),1=>array(0=>3,1=>41,),2=>array(0=>6,1=>52,),3=>array(0=>22,1=>36,),),322=>array(0=>array(0=>6,1=>81,),1=>array(0=>97,1=>31,),2=>array(0=>31,1=>63,),3=>array(0=>53,1=>54,),),323=>array(0=>array(0=>34,1=>61,),1=>array(0=>23,1=>8,),2=>array(0=>59,1=>82,),3=>array(0=>100,1=>11,),),324=>array(0=>array(0=>5,1=>48,),1=>array(0=>99,1=>91,),2=>array(0=>13,1=>92,),3=>array(0=>9,1=>76,),),325=>array(0=>array(0=>40,1=>84,),1=>array(0=>85,1=>15,),2=>array(0=>54,1=>91,),3=>array(0=>75,1=>57,),),326=>array(0=>array(0=>39,1=>11,),1=>array(0=>36,1=>66,),2=>array(0=>44,1=>5,),3=>array(0=>11,1=>83,),),327=>array(0=>array(0=>62,1=>73,),1=>array(0=>86,1=>92,),2=>array(0=>40,1=>43,),3=>array(0=>92,1=>30,),),328=>array(0=>array(0=>61,1=>32,),1=>array(0=>82,1=>79,),2=>array(0=>49,1=>11,),3=>array(0=>42,1=>21,),),329=>array(0=>array(0=>97,1=>30,),1=>array(0=>96,1=>19,),2=>array(0=>73,1=>60,),3=>array(0=>56,1=>75,),),330=>array(0=>array(0=>58,1=>2,),1=>array(0=>68,1=>33,),2=>array(0=>27,1=>79,),3=>array(0=>45,1=>59,),),331=>array(0=>array(0=>46,1=>3,),1=>array(0=>67,1=>86,),2=>array(0=>63,1=>47,),3=>array(0=>45,1=>21,),),332=>array(0=>array(0=>65,1=>84,),1=>array(0=>4,1=>2,),2=>array(0=>9,1=>65,),3=>array(0=>58,1=>63,),),333=>array(0=>array(0=>64,1=>38,),1=>array(0=>51,1=>2,),2=>array(0=>83,1=>44,),3=>array(0=>80,1=>46,),),334=>array(0=>array(0=>98,1=>83,),1=>array(0=>41,1=>3,),2=>array(0=>69,1=>11,),3=>array(0=>72,1=>22,),),335=>array(0=>array(0=>81,1=>86,),1=>array(0=>88,1=>52,),2=>array(0=>91,1=>12,),3=>array(0=>71,1=>79,),),336=>array(0=>array(0=>65,1=>10,),1=>array(0=>19,1=>11,),2=>array(0=>14,1=>39,),3=>array(0=>0,1=>7,),),337=>array(0=>array(0=>10,1=>49,),1=>array(0=>94,1=>18,),2=>array(0=>71,1=>23,),3=>array(0=>59,1=>54,),),338=>array(0=>array(0=>81,1=>85,),1=>array(0=>100,1=>93,),2=>array(0=>26,1=>93,),3=>array(0=>22,1=>46,),),339=>array(0=>array(0=>78,1=>11,),1=>array(0=>48,1=>81,),2=>array(0=>38,1=>5,),3=>array(0=>33,1=>39,),),340=>array(0=>array(0=>88,1=>63,),1=>array(0=>42,1=>56,),2=>array(0=>15,1=>63,),3=>array(0=>20,1=>46,),),341=>array(0=>array(0=>86,1=>64,),1=>array(0=>42,1=>78,),2=>array(0=>9,1=>62,),3=>array(0=>36,1=>44,),),342=>array(0=>array(0=>0,1=>91,),1=>array(0=>8,1=>87,),2=>array(0=>90,1=>4,),3=>array(0=>6,1=>53,),),343=>array(0=>array(0=>2,1=>95,),1=>array(0=>94,1=>87,),2=>array(0=>53,1=>53,),3=>array(0=>36,1=>74,),),344=>array(0=>array(0=>44,1=>18,),1=>array(0=>53,1=>2,),2=>array(0=>33,1=>73,),3=>array(0=>65,1=>14,),),345=>array(0=>array(0=>69,1=>96,),1=>array(0=>43,1=>18,),2=>array(0=>71,1=>30,),3=>array(0=>78,1=>73,),),346=>array(0=>array(0=>3,1=>78,),1=>array(0=>0,1=>29,),2=>array(0=>3,1=>43,),3=>array(0=>49,1=>87,),),347=>array(0=>array(0=>51,1=>97,),1=>array(0=>51,1=>55,),2=>array(0=>7,1=>24,),3=>array(0=>64,1=>12,),),348=>array(0=>array(0=>80,1=>79,),1=>array(0=>1,1=>57,),2=>array(0=>18,1=>53,),3=>array(0=>15,1=>33,),),349=>array(0=>array(0=>31,1=>34,),1=>array(0=>6,1=>70,),2=>array(0=>35,1=>11,),3=>array(0=>71,1=>63,),),350=>array(0=>array(0=>37,1=>0,),1=>array(0=>92,1=>0,),2=>array(0=>44,1=>95,),3=>array(0=>19,1=>83,),),351=>array(0=>array(0=>30,1=>68,),1=>array(0=>39,1=>20,),2=>array(0=>97,1=>80,),3=>array(0=>69,1=>76,),),352=>array(0=>array(0=>37,1=>7,),1=>array(0=>13,1=>32,),2=>array(0=>39,1=>51,),3=>array(0=>97,1=>66,),),353=>array(0=>array(0=>53,1=>79,),1=>array(0=>48,1=>81,),2=>array(0=>53,1=>99,),3=>array(0=>70,1=>92,),),354=>array(0=>array(0=>81,1=>36,),1=>array(0=>36,1=>87,),2=>array(0=>14,1=>94,),3=>array(0=>93,1=>55,),),355=>array(0=>array(0=>44,1=>76,),1=>array(0=>21,1=>87,),2=>array(0=>5,1=>31,),3=>array(0=>51,1=>77,),),356=>array(0=>array(0=>26,1=>29,),1=>array(0=>59,1=>37,),2=>array(0=>85,1=>2,),3=>array(0=>22,1=>82,),),357=>array(0=>array(0=>9,1=>61,),1=>array(0=>12,1=>99,),2=>array(0=>84,1=>31,),3=>array(0=>26,1=>19,),),358=>array(0=>array(0=>85,1=>76,),1=>array(0=>63,1=>19,),2=>array(0=>99,1=>25,),3=>array(0=>93,1=>53,),),359=>array(0=>array(0=>11,1=>0,),1=>array(0=>80,1=>97,),2=>array(0=>60,1=>76,),3=>array(0=>87,1=>70,),),360=>array(0=>array(0=>13,1=>9,),1=>array(0=>7,1=>2,),2=>array(0=>58,1=>30,),3=>array(0=>47,1=>16,),),361=>array(0=>array(0=>40,1=>27,),1=>array(0=>12,1=>77,),2=>array(0=>5,1=>97,),3=>array(0=>36,1=>34,),),362=>array(0=>array(0=>76,1=>21,),1=>array(0=>41,1=>23,),2=>array(0=>99,1=>26,),3=>array(0=>75,1=>90,),),363=>array(0=>array(0=>66,1=>67,),1=>array(0=>12,1=>31,),2=>array(0=>14,1=>63,),3=>array(0=>33,1=>17,),),364=>array(0=>array(0=>19,1=>18,),1=>array(0=>85,1=>8,),2=>array(0=>37,1=>69,),3=>array(0=>35,1=>70,),),365=>array(0=>array(0=>58,1=>19,),1=>array(0=>57,1=>71,),2=>array(0=>31,1=>84,),3=>array(0=>7,1=>64,),),366=>array(0=>array(0=>17,1=>41,),1=>array(0=>36,1=>11,),2=>array(0=>69,1=>68,),3=>array(0=>40,1=>52,),),367=>array(0=>array(0=>64,1=>55,),1=>array(0=>23,1=>75,),2=>array(0=>64,1=>76,),3=>array(0=>36,1=>68,),),368=>array(0=>array(0=>75,1=>53,),1=>array(0=>2,1=>73,),2=>array(0=>60,1=>76,),3=>array(0=>73,1=>69,),),369=>array(0=>array(0=>21,1=>23,),1=>array(0=>61,1=>19,),2=>array(0=>0,1=>16,),3=>array(0=>51,1=>79,),),370=>array(0=>array(0=>98,1=>17,),1=>array(0=>44,1=>80,),2=>array(0=>21,1=>66,),3=>array(0=>86,1=>73,),),371=>array(0=>array(0=>36,1=>66,),1=>array(0=>68,1=>55,),2=>array(0=>11,1=>62,),3=>array(0=>53,1=>5,),),372=>array(0=>array(0=>73,1=>83,),1=>array(0=>96,1=>41,),2=>array(0=>87,1=>40,),3=>array(0=>69,1=>77,),),373=>array(0=>array(0=>61,1=>77,),1=>array(0=>90,1=>79,),2=>array(0=>99,1=>42,),3=>array(0=>62,1=>81,),),374=>array(0=>array(0=>54,1=>81,),1=>array(0=>9,1=>64,),2=>array(0=>100,1=>99,),3=>array(0=>7,1=>100,),),375=>array(0=>array(0=>33,1=>50,),1=>array(0=>75,1=>35,),2=>array(0=>3,1=>80,),3=>array(0=>30,1=>43,),),376=>array(0=>array(0=>39,1=>9,),1=>array(0=>10,1=>54,),2=>array(0=>99,1=>63,),3=>array(0=>33,1=>15,),),377=>array(0=>array(0=>58,1=>13,),1=>array(0=>10,1=>77,),2=>array(0=>75,1=>17,),3=>array(0=>42,1=>44,),),378=>array(0=>array(0=>51,1=>89,),1=>array(0=>46,1=>92,),2=>array(0=>6,1=>71,),3=>array(0=>43,1=>54,),),379=>array(0=>array(0=>62,1=>21,),1=>array(0=>80,1=>53,),2=>array(0=>50,1=>54,),3=>array(0=>59,1=>33,),),380=>array(0=>array(0=>21,1=>96,),1=>array(0=>90,1=>64,),2=>array(0=>32,1=>92,),3=>array(0=>23,1=>83,),),381=>array(0=>array(0=>64,1=>81,),1=>array(0=>72,1=>17,),2=>array(0=>55,1=>86,),3=>array(0=>2,1=>6,),),382=>array(0=>array(0=>53,1=>30,),1=>array(0=>60,1=>58,),2=>array(0=>14,1=>53,),3=>array(0=>89,1=>98,),),383=>array(0=>array(0=>39,1=>29,),1=>array(0=>21,1=>29,),2=>array(0=>47,1=>99,),3=>array(0=>3,1=>55,),),384=>array(0=>array(0=>91,1=>90,),1=>array(0=>20,1=>24,),2=>array(0=>44,1=>91,),3=>array(0=>69,1=>65,),),385=>array(0=>array(0=>19,1=>87,),1=>array(0=>0,1=>44,),2=>array(0=>19,1=>100,),3=>array(0=>15,1=>82,),),386=>array(0=>array(0=>85,1=>82,),1=>array(0=>93,1=>75,),2=>array(0=>13,1=>44,),3=>array(0=>96,1=>11,),),387=>array(0=>array(0=>33,1=>66,),1=>array(0=>37,1=>41,),2=>array(0=>36,1=>1,),3=>array(0=>69,1=>83,),),388=>array(0=>array(0=>96,1=>63,),1=>array(0=>19,1=>33,),2=>array(0=>77,1=>21,),3=>array(0=>67,1=>63,),),389=>array(0=>array(0=>53,1=>82,),1=>array(0=>34,1=>59,),2=>array(0=>96,1=>20,),3=>array(0=>85,1=>74,),),390=>array(0=>array(0=>30,1=>47,),1=>array(0=>9,1=>97,),2=>array(0=>76,1=>78,),3=>array(0=>88,1=>94,),),391=>array(0=>array(0=>29,1=>70,),1=>array(0=>20,1=>58,),2=>array(0=>59,1=>91,),3=>array(0=>43,1=>13,),),392=>array(0=>array(0=>85,1=>60,),1=>array(0=>34,1=>40,),2=>array(0=>18,1=>75,),3=>array(0=>82,1=>2,),),393=>array(0=>array(0=>99,1=>31,),1=>array(0=>68,1=>95,),2=>array(0=>48,1=>5,),3=>array(0=>64,1=>42,),),394=>array(0=>array(0=>60,1=>14,),1=>array(0=>86,1=>34,),2=>array(0=>77,1=>63,),3=>array(0=>20,1=>54,),),395=>array(0=>array(0=>3,1=>65,),1=>array(0=>91,1=>30,),2=>array(0=>37,1=>47,),3=>array(0=>100,1=>54,),),396=>array(0=>array(0=>60,1=>39,),1=>array(0=>60,1=>50,),2=>array(0=>98,1=>64,),3=>array(0=>43,1=>5,),),397=>array(0=>array(0=>97,1=>66,),1=>array(0=>87,1=>81,),2=>array(0=>22,1=>68,),3=>array(0=>81,1=>83,),),398=>array(0=>array(0=>1,1=>81,),1=>array(0=>69,1=>64,),2=>array(0=>28,1=>31,),3=>array(0=>36,1=>16,),),399=>array(0=>array(0=>78,1=>23,),1=>array(0=>26,1=>92,),2=>array(0=>49,1=>85,),3=>array(0=>3,1=>73,),),);

						$itemSvgDefs = HtmlNd::CreateTag( $doc, 'defs', array(), array(
							HtmlNd::CreateTag( $doc, 'filter', array( 'id' => $id . '-f-blur-sm', 'x' => '-100%', 'y' => '-100%', 'width' => '400%', 'height' => '400%' ), array(
								HtmlNd::CreateTag( $doc, 'feGaussianBlur', array( 'result' => 'blur', 'stdDeviation' => '2' ), array(
								) ),
								HtmlNd::CreateTag( $doc, 'feComponentTransfer', array(), array(
									HtmlNd::CreateTag( $doc, 'feFuncA', array( 'type' => 'linear', 'slope' => '180', 'intercept' => '-70' ) ),
								) ),
							) ),

							HtmlNd::CreateTag( $doc, 'filter', array( 'id' => $id . '-f-blur', 'x' => '-100%', 'y' => '-100%', 'width' => '400%', 'height' => '400%' ), array(
								HtmlNd::CreateTag( $doc, 'feGaussianBlur', array( 'result' => 'blur', 'stdDeviation' => '10' ), array(
								) ),
								HtmlNd::CreateTag( $doc, 'feComponentTransfer', array(), array(
									HtmlNd::CreateTag( $doc, 'feFuncA', array( 'type' => 'linear', 'slope' => '180', 'intercept' => '-70' ) ),
								) ),
							) ),
						) );

						$bg = Gen::GetArrField( $bubbleMorph, array( 'bg' ) );
						if( is_string( $bg ) && preg_match( '@^rgba\\(\\s*\\d+\\s*,\\s*\\d+\\s*,\\s*\\d+\\s*,\\s*0\\s*\\)$@', $bg ) )
							$bg = null;

						if( $bg )
						{
							if( is_array( $bg ) )
							{
								// https://developer.mozilla.org/en-US/docs/Web/SVG/Tutorial/Gradients
								// https://developer.mozilla.org/en-US/docs/Web/SVG/Element/linearGradient
								$type = Gen::GetArrField( $bg, array( 'type' ), '' );
								if( $type )
								{
									$attrs = array( 'id' => $id . '-bubbles-bg' );
									$angle = ( float )Gen::GetArrField( $bg, array( 'angle' ) );
									if( $angle )
										$attrs[ 'gradientTransform' ] = 'rotate(' . ( $angle - 90 ) . ')';

									$itemSvgDefs -> appendChild( $itemBg = HtmlNd::CreateTag( $doc, $type . 'Gradient', $attrs ) );
									foreach( Gen::GetArrField( $bg, array( 'colors' ), array() ) as $color )
									{
										$itemBg -> appendChild( HtmlNd::CreateTag( $doc, 'stop', array( 'offset' => ( string )Gen::GetArrField( $color, array( 'position' ), 0 ) . '%', 'stop-color' => Gen::GetArrField( $color, array( 'a' ), 1.0 ) !== 1.0 ? sprintf( 'rgba(%d,%d,%d,%d)', Gen::GetArrField( $color, array( 'r' ), 0 ), Gen::GetArrField( $color, array( 'g' ), 0 ), Gen::GetArrField( $color, array( 'b' ), 0 ), Gen::GetArrField( $color, array( 'a' ), 0.0 ) ) : sprintf( 'rgb(%d,%d,%d)', Gen::GetArrField( $color, array( 'r' ), 0 ), Gen::GetArrField( $color, array( 'g' ), 0 ), Gen::GetArrField( $color, array( 'b' ), 0 ) ) ) ) );
									}

									$bg = 'url(#' . $id . '-bubbles-bg)';
								}
							}
						}

						$itemSlideChild -> appendChild( $itemSvg = HtmlNd::CreateTag( $doc, 'svg', array( 'version' => '1.1', 'xmlns' => 'http://www.w3.org/2000/svg', 'overflow' => 'visible' ), array( $itemSvgDefs,  ) ) );

						$aSpeedX = array_map( function( $v ) { return( ( float )$v ); }, explode( '|', Gen::GetArrField( $bubbleMorph, array( 'speedx' ), '' ) ) );
						$aSpeedY = array_map( function( $v ) { return( ( float )$v ); }, explode( '|', Gen::GetArrField( $bubbleMorph, array( 'speedy' ), '' ) ) );
						$aBorderColor = explode( '|', Gen::GetArrField( $bubbleMorph, array( 'bordercolor' ), '' ) );
						$aBorderSize = explode( '|', Gen::GetArrField( $bubbleMorph, array( 'bordersize' ), '' ) );
						$nBubblesMax = 0;
						foreach( explode( '|', Gen::GetArrField( $bubbleMorph, array( 'num' ), '' ) ) as $i => $nBubbles )
						{
							$nBubbles = min( count( $g_aBubblePosRand ) - $iCurBubblesRand, $nBubbles );
							if( $nBubblesMax < $nBubbles )
								$nBubblesMax = $nBubbles;

							if( ( int )($aBorderSize[ $i ]??'') )
							{
								$itemSvgBorderSub1 = HtmlNd::CreateTag( $doc, 'g', array( 'class' => 'bubbles b-ext' ) );
								$itemSvgBorderSub2 = HtmlNd::CreateTag( $doc, 'g', array( 'class' => 'bubbles b-int' ) );
								$itemSvg -> appendChild( HtmlNd::CreateTag( $doc, 'mask', array( 'class' => 'v' . $i, 'id' => $id . '-bubbles-v' . $i . '-border', 'style' => array( 'display' => 'none' ) ), array( $itemSvgBorderSub1, $itemSvgBorderSub2 ) ) );
							}
							else
							{
								$itemSvgBorderSub1 = null;
								$itemSvgBorderSub2 = null;
							}

							if( $bg )
							{
								$itemSvgBody = HtmlNd::CreateTag( $doc, 'g', array( 'class' => 'bubbles body' ) );
								$itemSvg -> appendChild( HtmlNd::CreateTag( $doc, 'mask', array( 'class' => 'v' . $i, 'id' => $id . '-bubbles-v' . $i . '-body', 'style' => array( 'display' => 'none' ) ), array( $itemSvgBody ) ) );
							}
							else
								$itemSvgBody = null;

							for( $iBubble = 0; $iBubble < $nBubbles; $iBubble++ )
							{
								$dur = ( ($aSpeedX[ $i ]??0.0) + ($aSpeedY[ $i ]??0.0) ) / 2;
								$dur = $dur ? ( 2.5 / $dur ) : 50;

								// Pseudo randomizer for duration
								{
									$durShift = 0.3 * $dur * ( ( $iBubble + 1 ) / ( float )$nBubbles );
									if( $iBubble % 2 )
										$durShift *= -1;
									$dur += $durShift;
								}

								$keyTimes = ''; $valuesX = ''; $valuesY = '';
								$jn = count( $g_aBubblePosRand[ $iCurBubblesRand + $iBubble ] );
								for( $j = 0; $j < $jn; $j++ )
								{
									$keyTimes .= ( string )( ( float )$j / $jn ) . ';';
									$valuesX .= ( string )$g_aBubblePosRand[ $iCurBubblesRand + $iBubble ][ $j ][ 0 ] . '%;';
									$valuesY .= ( string )$g_aBubblePosRand[ $iCurBubblesRand + $iBubble ][ $j ][ 1 ] . '%;';
								}
								$keyTimes .= '1';
								$valuesX .= ( string )$g_aBubblePosRand[ $iCurBubblesRand + $iBubble ][ 0 ][ 0 ] . '%;';
								$valuesY .= ( string )$g_aBubblePosRand[ $iCurBubblesRand + $iBubble ][ 0 ][ 1 ] . '%;';

								$itemSvgBubble = HtmlNd::CreateTag( $doc, 'circle', array( 'class' => 'b' . $iBubble ), array(
									HtmlNd::CreateTag( $doc, 'animate', array( 'attributeName' => 'cx', 'keyTimes' => $keyTimes, 'values' => $valuesX, 'dur' => ( string )$dur . 's', 'repeatCount' => 'indefinite' ) ),
									HtmlNd::CreateTag( $doc, 'animate', array( 'attributeName' => 'cy', 'keyTimes' => $keyTimes, 'values' => $valuesY, 'dur' => ( string )$dur . 's', 'repeatCount' => 'indefinite' ) ),
								) );

								$bItemSvgBubbleNeedClone = false;
								foreach( array( $itemSvgBorderSub1, $itemSvgBorderSub2, $itemSvgBody ) as $itemSvgBubbleContainer )
								{
									if( !$itemSvgBubbleContainer )
										continue;

									if( $bItemSvgBubbleNeedClone )
										$itemSvgBubble = $itemSvgBubble -> cloneNode( true );
									else
										$bItemSvgBubbleNeedClone = true;

									$itemSvgBubbleContainer -> appendChild( $itemSvgBubble );
								}
							}

							if( $itemSvgBorderSub1 )
								$itemSvg -> appendChild( HtmlNd::CreateTag( $doc, 'rect', array( 'class' => 'v' . $i, 'mask' => 'url(#' . $id . '-bubbles-v' . $i . '-border)', 'fill' => ($aBorderColor[ $i ]??''), 'style' => array( 'display' => 'none' ) ), array() ) );
							if( $itemSvgBody )
								$itemSvg -> appendChild( HtmlNd::CreateTag( $doc, 'rect', array( 'class' => 'v' . $i, 'mask' => 'url(#' . $id . '-bubbles-v' . $i . '-body)', 'fill' => $bg, 'style' => array( 'display' => 'none' ) ), array() ) );

							_RevSld_SetStyleAttrEx( $aItemStyle, '#' . $id . ' .v' . $i, $i, array( 'display' => 'initial!important' ) );
						}

						$iCurBubblesRand += $nBubblesMax;

						{
							$a = array();
							foreach( explode( '|', Gen::GetArrField( $bubbleMorph, array( $f ), '' ) ) as $i => $v )
								$a[ $i ][ $t ] = $v;
							_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						}

						foreach( array( 'bufferx' => '--buffer-x', 'buffery' => '--buffer-y', 'bordersize' => '--border-size' ) as $f => $t )
						{
							$a = array(); foreach( explode( '|', Gen::GetArrField( $bubbleMorph, array( $f ), '0' ) ) as $i => $v ) $a[ $i ][ $t ] = _RevSld_GetSize( false, $v );
							_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						}

						{
							$itemScript = $doc -> createElement( 'script' );
							if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
								$itemScript -> setAttribute( 'type', 'text/javascript' );
							$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
							HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_sldRev_bubblemorph_calcSizes(document.currentScript.parentNode);' );
							$itemSlideChild -> insertBefore( $itemScript, $itemSlideChild -> firstChild );
						}

						$adjustedBubbles = true;
					}

					$styleSeparated = Ui::MergeStyleAttr( Ui::ParseStyleAttr( $itemSlideChild -> getAttribute( 'style' ) ), $styleSeparated );
					$styleSeparatedWrap[ 'z-index' ] = ($styleSeparated[ 'z-index' ]??null);

					//
					//if( is_array( $styleSeparated[ 'white-space' ] ) )
					//{
					//    $rfnbrfdg = 0;
					//}
					//

					$itemSlideChild -> setAttribute( 'style', Ui::GetStyleAttr( $styleSeparated ) );

					_RevSld_SetStyleAttr( $styleSeparatedWrap, $aItemStyle, $itemChildSelectorWrap, $aSizeWrap );

					$styleSeparatedLoopWrap = array( 'position' => $styleSeparated[ 'position' ], 'display' => $attrDisplay );
					_RevSld_SetStyleAttr( $styleSeparatedLoopWrap, $aItemStyle, $itemChildSelectorWrap . '>rs-loop-wrap', $aSizeWrap );

					$styleSeparatedMaskWrap = array( 'position' => $styleSeparated[ 'position' ], 'overflow' => 'visible', 'display' => $attrDisplay );
					_RevSld_SetStyleAttr( $styleSeparatedMaskWrap, $aItemStyle, $itemChildSelectorWrap . '>rs-loop-wrap>rs-mask-wrap', $aSizeWrap );

					$itemParent -> insertBefore( HtmlNd::CreateTag( $doc, $isLayer ? 'rs-layer-wrap' : ( $itemSlideChild -> nodeName . '-wrap' ), array( 'id' => $itemIdWrap, 'class' => array( 'rs-parallax-wrap', $attrWrapperClass, $itemSlideChild -> nodeName == 'rs-row' ? 'slider-row-wrap' : null ), 'style' => $styleSeparatedWrap ), array( HtmlNd::CreateTag( $doc, 'rs-loop-wrap', array( 'style' => $styleSeparatedLoopWrap ), array( HtmlNd::CreateTag( $doc, 'rs-mask-wrap', array( 'style' => $styleSeparatedMaskWrap ), array( $itemSlideChild ) ) ) ) ) ), $itemInsertBefore );
				}
			}
		}

		// Bullets
		if( Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'enable' ) ) && $nSlides )
		{
			$direction = Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'direction' ), 'horizontal' );
			$alignHor = Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'h_align' ), 'center' );
			$alignVer = Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'v_align' ), 'bottom' );
			$space = Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'space' ), 5 );

			$obj = new AnyObj();
			$obj -> cb =
				function( $obj, $m )
				{
					return( $obj -> itemSlide -> getAttribute( 'data-' . $m[ 1 ] ) );
				};

			$itemBulletsTmp = '';
			for( $i = 0; $i < $nSlides; $i++ )
			{
				$obj -> itemSlide = $aItemSlide[ $i ];

				$attrs = array( 'class' => 'tp-bullet ' . ( $i === 0 ? 'selected' : '' ), 'style' => array( 'position' => 'relative!important' ) );
				if( $direction == 'horizontal' )
				{
					if( $i )
						$attrs[ 'style' ][ 'margin-left' ] = ( string )$space . 'px';
					$attrs[ 'style' ][ 'display' ] = 'inline-block!important';
				}
				else
				{
					if( $i )
						$attrs[ 'style' ][ 'margin-top' ] = ( string )$space . 'px';
				}

				$itemBulletsTmp .= Ui::Tag( 'rs-bullet', preg_replace_callback( '@{{([^{}]+)}}@', array( $obj, 'cb' ), Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'tmp' ), '' ) ), $attrs );
			}

			unset( $obj );

			$attrs = array( 'class' => array( 'tp-bullets', 'js-lzl-ing', Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'style' ) ), $direction, 'nav-dir-' . $direction, 'nav-pos-hor-' . $alignHor, 'nav-pos-ver-' . $alignVer ), 'style' => array( 'display' => 'flex', 'flex-wrap' => 'wrap', 'z-index' => 1000, 'position' => 'absolute', 'counter-reset' => 'section' ) );
			if( $direction != 'horizontal' )
				$attrs[ 'style' ][ 'flex-direction' ] = 'column';

			{
				$translate = array( '0% + ', '0% + ' );

				{
					switch( $alignHor )
					{
					case 'center':
					case 'middle':					$translate[ 0 ] = '-50% + ';	$pos = '50%'; break;
					case 'right':					$translate[ 0 ] = '-100% - ';	$pos = '100%'; break;
					default:						$pos = '0%';
					}

					$attrs[ 'style' ][ 'left' ] = $pos;
				}

				{
					switch( $alignVer )
					{
					case 'center':
					case 'middle':					$translate[ 1 ] = '-50% + ';	$pos = '50%'; break;
					case 'bottom':					$translate[ 1 ] = '-100% - ';	$pos = '100%'; break;
					default:						$pos = '0%';
					}

					$attrs[ 'style' ][ 'top' ] = $pos;
				}

				$attrs[ 'style' ][ 'transform' ] = 'translate(' . _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'h_offset' ), 0 ), $translate[ 0 ] ) . ', ' . _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'v_offset' ), 20 ), $translate[ 1 ] ) . ')!important';
			}

			$itemBulletsTmp = HtmlNd::ParseAndImport( $doc, Ui::Tag( 'rs-bullets', $itemBulletsTmp, $attrs ) );
			$item -> appendChild( $itemBulletsTmp );

			_RevSld_HavHideMode( $itemStyleCont, $itemId, $prms, 'bullets', 'rs-bullets' );
			_RevSld_AdjustTimeoutByVal( $nSwitchingLoadingTimeout, $nSwitchingLoadingTimeoutMax, Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'animDelay' ) ) );
		}

		// Arrows
		if( Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', 'enable' ) ) && $nSlides )
		{
			foreach( array( 'left', 'right' ) as $type )
			{
				$attrs = array();
				$attrs[ 'class' ] = array( 'tp-' . $type . 'arrow', 'tparrows', 'js-lzl-ing', Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', 'style' ), '' ) );

				$translate = array( 0, 0 );

				$prefix = null;
				{
					switch( Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', $type, 'h_align' ), $type ) )
					{
					case 'center':
					case 'middle':					$translate[ 0 ] = '-50%';	$prefix = '50% + '; break;
					case 'right':					$translate[ 0 ] = '-100%';	$prefix = '100% - '; break;
					}

					$attrs[ 'style' ][ 'left' ] = _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', $type, 'h_offset' ), 20 ), $prefix );
				}

				$prefix = null;
				{
					switch( Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', $type, 'v_align' ), 'middle' ) )
					{
					case 'center':
					case 'middle':					$translate[ 1 ] = '-50%';		$prefix = '50% + '; break;
					case 'bottom':					$translate[ 1 ] = '-100%';		$prefix = '100% - '; break;
					}

					$attrs[ 'style' ][ 'top' ] = _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', $type, 'v_offset' ), 0 ), $prefix );
				}

				if( $translate[ 0 ] || $translate[ 1 ] )
					$attrs[ 'style' ][ 'transform' ] = 'translate(' . $translate[ 0 ] . ', ' . $translate[ 1 ] . ')!important';

				$itemStyleCont .= '#' . $itemId . ' .tp-' . $type . 'arrow.js-lzl-ing{' . Ui::GetStyleAttr( $attrs[ 'style' ], false ) . '}';
				unset( $attrs[ 'style' ] );

				$item -> appendChild( HtmlNd::ParseAndImport( $doc, Ui::Tag( 'rs-arrow', Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', 'tmp' ), '' ), $attrs ) ) );
			}

			_RevSld_HavHideMode( $itemStyleCont, $itemId, $prms, 'arrows', 'rs-arrow' );
			_RevSld_AdjustTimeoutByVal( $nSwitchingLoadingTimeout, $nSwitchingLoadingTimeoutMax, Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', 'animDelay' ) ) );
		}

		// Tabs, Thumbnails
		foreach( array( 'tabs' => array( 'sel' => 'tab', 'defs' => array( 'wrapper_padding' => 10, 'space' => 0 ) ), 'thumbnails' => array( 'sel' => 'thumb', 'defs' => array( 'wrapper_padding' => 2, 'space' => 0 ) ) ) as $type => $typeMeta )
		{
			if( !Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'enable' ) ) )
				continue;

			$contTabs = '';

			$obj = new AnyObj();
			$obj -> cb =
				function( $obj, $m )
				{
					if( count( $m ) == 3 && $m[ 1 ] == 'param' )
						return( $obj -> itemSlide -> getAttribute( 'data-p' . $m[ 2 ] ) );
					if( count( $m ) == 2 )
						return( $obj -> itemSlide -> getAttribute( 'data-' . $m[ 1 ] ) );

					if( $m[ 0 ] == 'class="tp-thumb-image"' )
						return( 'class="tp-thumb-image" style="background-image: url(&quot;' . $obj -> itemSlide -> getAttribute( 'data-thumb' ) . '&quot;);"' );

					return( $m[ 0 ] );
				};

			$visibleAmount = Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'visibleAmount' ), 5 );
			if( $visibleAmount > $nSlides )
				$visibleAmount = $nSlides;
			foreach( $aItemSlide as $i => $obj -> itemSlide )
			{
				if( $i == $visibleAmount )
					break;

				$contTab = Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'tmp' ) );
				$contTab = preg_replace_callback( '@{{([a-z\\-]+)(\\d+)}}@i', array( $obj, 'cb' ), $contTab );
				$contTab = preg_replace_callback( '@{{([\\w\\-]+)}}@', array( $obj, 'cb' ), $contTab );
				$contTab = preg_replace_callback( '@class="[\\w\\-]+"@', array( $obj, 'cb' ), $contTab );

				$contTabs .= Ui::Tag( 'rs-' . $typeMeta[ 'sel' ], $contTab
					, array(
						'data-liindex' => $i,
						'data-key' => $obj -> itemSlide -> getAttribute( 'data-key' ),
						'class' => array( 'tp-' . $typeMeta[ 'sel' ], $i === 0 ? 'selected' : '' ),
						'style' => array(
							'display' => 'inline-block!important',
							'flex-shrink' => '0',
							'position' => 'relative',
							'width' => '' . Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'width' ), 0 ) . 'px !important',
							'height' => '100%',
							'margin-right' => ( $i + 1 == $visibleAmount ) ? null : ( '' . Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'space' ), $typeMeta[ 'defs' ][ 'space' ] ) . 'px' ),
						),
					) );
			}

			unset( $obj );

			if( !$contTabs )
				continue;

			$widthTotal = $visibleAmount * Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'width' ), 0 ) + ( $visibleAmount - 1 ) * Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'space' ), $typeMeta[ 'defs' ][ 'space' ] ) + 2 * Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'mhoff' ), 0 );
			$height = Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'height' ), 0 );

			$padding = array_fill( 0, 4, '' . ( int )Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'wrapper_padding' ), $typeMeta[ 'defs' ][ 'wrapper_padding' ], null, false, false ) . 'px' );
			$translate = array( 0, 0 ); $prefix = array( null, null );
			{
				switch( Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'h_align' ), 'center' ) )
				{
				case 'center':
				case 'middle':													$prefix[ 0 ] = $padding[ 3 ] . ' + '; $padding[ 3 ] = 'calc(50% - (' . $widthTotal . 'px / 2) - ' . $padding[ 3 ] . ')'; break;
				case 'right':					$translate[ 0 ] = '-100%';		$prefix[ 0 ] = '100% - '; break;
				}

				switch( Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'v_align' ), 'bottom' ) )
				{
				case 'center':
				case 'middle':					$translate[ 1 ] = '-50%';		$prefix[ 1 ] = '50% + '; break;
				case 'bottom':					$translate[ 1 ] = '-100%';		$prefix[ 1 ] = '100% - '; break;
				}
			}

			$itemStyleCont .= '#' . $itemId . '_wrapper rs-' . $typeMeta[ 'sel' ] . 's.js-lzl-ing rs-' . $typeMeta[ 'sel' ] . 's-wrap{' . Ui::GetStyleAttr(
				array(
					'display' => 'flex',
					'max-height' => '' . $height . 'px' . '!important',
					'height' => '' . $height . 'px' . '!important',
				)
			, false ) . '}';

			$itemStyleCont .= '#' . $itemId . '_wrapper rs-' . $typeMeta[ 'sel' ] . 's.js-lzl-ing rs-navmask{' . Ui::GetStyleAttr(
				array(
					'padding' => '' . Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'mvoff' ), 0 ) . 'px ' . Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'mhoff' ), 0 ) . 'px' . '!important',
					'max-width' => 'unset !important;',
					'max-height' => 'unset !important;',
				)
			, false ) . '}';

			$itemStyleCont .= '#' . $itemId . '_wrapper rs-' . $typeMeta[ 'sel' ] . 's.js-lzl-ing{' . Ui::GetStyleAttr(
				array(
					'background' => Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'wrapper_color' ) ),
					'transform' => ( ( $translate[ 0 ] || $translate[ 1 ] ) && Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'position' ) ) != 'outer-horizontal' ) ? ( 'translate(' . $translate[ 0 ] . ', ' . $translate[ 1 ] . ')' . '!important' ) : null,
					'padding' => implode( ' ', $padding ) . '!important',
					'left' => _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'h_offset' ), 0 ), $prefix[ 0 ] ) . '!important',
					'top' => _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'v_offset' ), 20 ), $prefix[ 1 ] ) . '!important',
					'max-width' => 'unset !important;',
					'max-height' => 'unset !important;',
					'position' => ( Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'position' ) ) == 'outer-horizontal' ) ? 'relative' : null,
				)
			, false ) . '}';

			$contTabs = Ui::Tag( 'rs-' . $typeMeta[ 'sel' ] . 's',
				Ui::Tag( 'rs-navmask',
					Ui::Tag( 'rs-' . $typeMeta[ 'sel' ] . 's-wrap',
						$contTabs
					, array(
						'class' => array( 'tp-' . $typeMeta[ 'sel' ] . 's-inner-wrapper' ),
					) )
				, array(
					'class' => array( 'tp-' . $typeMeta[ 'sel' ] . '-mask' ),
				) )
			, array(
				'class' => array( 'js-lzl-ing', 'nav-dir-horizontal', 'nav-pos-ver-bottom', 'nav-pos-hor-center', 'rs-nav-element', 'tp-' . $typeMeta[ 'sel' ] . 's', 'tp-span-wrapper', 'inner', Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'style' ), '' ) ),
			) );

			if( Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'position' ) ) == 'outer-horizontal' )
				$item -> parentNode -> appendChild( HtmlNd::ParseAndImport( $doc, $contTabs ) );
			else
				$item -> appendChild( HtmlNd::ParseAndImport( $doc, $contTabs ) );
			//$itemStyleCont .= '#' . $itemId . ' rs-tabs{' . Ui::GetStyleAttr(
			//    array(
			//        'background' => Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'wrapper_color' ) ) . '!important',
			//        'transform' => 'translate(0, -100%)!important',
			//        'top' => _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'v_offset' ), 0 ), '100% - ' ) . '!important',
			//        'left' => '0' . '!important',
			//        'padding-left' => 'calc(50% - (' . $widthTotal . 'px / 2))' . '!important',
			//    )
			//) . '}';

			_RevSld_HavHideMode( $itemStyleCont, $itemId, $prms, $type, 'rs-' . $typeMeta[ 'sel' ] . 's' );

			_RevSld_AdjustTimeoutByVal( $nSwitchingLoadingTimeout, $nSwitchingLoadingTimeoutMax, Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'animDelay' ) ) );
		}

		$aWidthUnique = array();
		for( $iDevice = 0; $iDevice < count( $aWidth ); $iDevice++ )
		{
			$width = $aWidth[ count( $aWidth ) - 1 - $iDevice ];
			if( !isset( $aWidthUnique[ $width ] ) )
				$aWidthUnique[ $width ] = $iDevice;
		}
		$aWidthUnique = array_reverse( $aWidthUnique, true );

		$iWidth = 0;
		$widthPrev = 0;
		foreach( $aWidthUnique as $width => $iDevice )
		{
			if( $aItemStyle[ $iDevice ] )
			{
				$itemStyleCont .= '@media';
				if( $iWidth > 0 )
					$itemStyleCont .= ' (min-width: ' . ( $widthPrev ) . 'px)';
				if( $iWidth > 0 && $iWidth < count( $aWidthUnique ) - 1 )
					$itemStyleCont .= ' and';
				if( $iWidth < count( $aWidthUnique ) - 1 )
					$itemStyleCont .= ' (max-width: ' . ( $width - 1 ) . 'px)';

				$itemStyleCont .= '{' . Ui::GetStyleSels( $aItemStyle[ $iDevice ] ) . '}';
			}

			$iWidth++;
			$widthPrev = $width;
		}

		if( $itemStyleCont )
		{
			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
			$item -> parentNode -> insertBefore( $itemStyle, $item );
		}

		$item -> setAttribute( 'style', Ui::GetStyleAttr( Ui::MergeStyleAttr( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( '--lzl-rs-scale' => '1' ) ) ) );

		$itemOrig = null;
		if( $bDblLoadFix )
		{
			$itemWrap = $item -> parentNode;
			$itemWrapOrig = $itemWrap -> cloneNode( true );
			HtmlNd::InsertBefore( $itemWrap -> parentNode, $itemWrapOrig, $itemWrap );
			$itemOrig = HtmlNd::FirstOfChildren( $xpath -> query( './rs-module', $itemWrapOrig ) );
			//$itemWrap -> removeAttribute( 'id' );
		    HtmlNd::Remove( HtmlNd::ChildrenAsArr( $xpath -> query( './script', $itemWrap ) ) );
		}

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_sldRev_calcSizes_init(document,false)' );
			$item -> insertBefore( $itemScript, $item -> firstChild );
		}

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_sldRev_calcSizes_init(document,true)' );
			$item -> appendChild( $itemScript );
		}

		if( $bDblLoadFix )
		{
			$item -> setAttribute( 'id', $itemId );
			HtmlNd::AddRemoveAttrClass( $item -> parentNode, array( 'js-lzl-ing' ) );
			HtmlNd::AddRemoveAttrClass( $itemOrig -> parentNode, array( 'js-lzl-ing' ) );

			HtmlNd::AddRemoveAttrClass( $item, array( 'js-lzl-ing' ) );
			$itemOrig -> setAttribute( 'data-lzl-ing-t', ( string )$nSwitchingLoadingTimeout );

			HtmlNd::AddRemoveAttrClass( $itemOrig, '', array( 'js-lzl-nid' ) );

			// Clean orig item
			foreach( array( './/rs-slides-lzl', './/rs-static-layers-lzl', './/rs-bullets', './/rs-arrow', './/rs-progress', './/rs-tabs', './/rs-thumbs' ) as $selItem )
			    HtmlNd::Remove( HtmlNd::ChildrenAsArr( $xpath -> query( $selItem, $itemOrig -> parentNode ) ) );

			// Clean item
			foreach( array( './rs-slides', './rs-static-layers' ) as $selItem )
			    HtmlNd::Remove( HtmlNd::ChildrenAsArr( $xpath -> query( $selItem, $item ) ) );

			//if( 0 )
			{
				$itemNoScript = $doc -> createElement( 'noscript' );
				$itemNoScript -> setAttribute( 'data-lzl-bjs', '' );
				HtmlNd::MoveChildren( $itemNoScript, $itemOrig );
				ContNoScriptItemClear( $itemNoScript );
				$itemOrig -> appendChild( $itemNoScript );

				$ctx -> bBjs = true;
			}

			{
				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_sldRev_calcSizes_init(document,false)' );
				$itemOrig -> insertBefore( $itemScript, $itemOrig -> firstChild );
			}
		}
		else
		{
			foreach( $xpath -> query( './rs-slides//img', $item ) as $itemImg )
				HtmlNd::RenameAttr( $itemImg, 'src', 'data-lzl-src' );
		}

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.rev_break_columns@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@rs-fullwidth-wrap@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@rs-fw-forcer@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.js-lzl-nid@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@not\\(\\.js-lzl-ing@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@not\\(\\.seraph-accel-js-lzl-ing@' ] = true;

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle,
				".rs-lzl-cont.js-lzl-ing > rs-slide,\r\n.rs-lzl-cont.js-lzl-ing *:not(.tp-video-play-button),\r\n.rs-lzl-cont.js-lzl-ing > rs-slide *:not(.tp-video-play-button) {\r\n\tvisibility: visible !important;\r\n\topacity: 1 !important;\r\n}\r\n\r\nrs-module.revslider-initialised > rs-tabs.js-lzl-ing,\r\nrs-module:not([style*=lzl-rs-scale]) .rs-lzl-cont.js-lzl-ing {\r\n\tvisibility: hidden !important;\r\n}\r\n\r\nrs-module-wrap {\r\n\tvisibility: visible !important;\r\n\theight: unset !important;\r\n}\r\n\r\nrs-module.revslider-initialised > .rs-lzl-cont.js-lzl-ing,\r\nrs-module:not(.revslider-initialised) > rs-static-layers:not(.js-lzl-ing),\r\nrs-module.revslider-initialised > tp-bullets.js-lzl-ing,\r\nrs-module.revslider-initialised > rs-arrow.js-lzl-ing,\r\n.rs-lzl-cont.js-lzl-ing .html5vid:not(:has(>video)),\r\n.js-lzl-ing-disp-none,\r\nrs-module.js-lzl-nid rs-slides-lzl [data-cbreak] {\r\n\tdisplay: none !important;\r\n}\r\n\r\n.js-lzl-ing .rev_row_zone_middle {\r\n\ttransform: translate(0,-50%);\r\n\ttop: calc(50%);\r\n}\r\n\r\n.rs-lzl-cont.js-lzl-ing rs-layer[data-type=\"image\"] img,\r\n.rs-lzl-cont.js-lzl-ing .rs-layer[data-type=\"image\"] img {\r\n\tobject-fit: fill;\r\n\twidth: 100%;\r\n\theight: 100%;\r\n}\r\n\r\n.rs-lzl-cont.js-lzl-ing [data-bubblemorph] svg {\r\n\tposition: absolute;\r\n\tleft: calc(var(--sz) / 2 + var(--buffer-x));\r\n\ttop: calc(var(--sz) / 2 + var(--buffer-y));\r\n\twidth: calc(100% - var(--sz) - 2 * var(--buffer-x));\r\n\theight: calc(100% - var(--sz) - 2 * var(--buffer-y));\r\n}\r\n\r\n.rs-lzl-cont.js-lzl-ing [data-bubblemorph] .bubbles.b-ext > circle {\r\n\tr: calc(0.97 * var(--sz) / 2);\r\n\tfill: white;\r\n}\r\n\r\n.rs-lzl-cont.js-lzl-ing [data-bubblemorph] .bubbles.b-int > circle {\r\n\tr: calc(0.97 * var(--sz) / 2 - var(--border-size));\r\n\tfill: black;\r\n}\r\n\r\n.rs-lzl-cont.js-lzl-ing [data-bubblemorph] .bubbles.body > circle {\r\n\tr: calc(0.97 * var(--sz) / 2 - var(--border-size));\r\n\tfill: white;\r\n}\r\n\r\n.rs-lzl-cont.js-lzl-ing [data-bubblemorph] .bubbles {\r\n\t-webkit-filter: var(--flt);\r\n\tfilter: var(--flt);\r\n}\r\n\r\n.rs-lzl-cont.js-lzl-ing [data-bubblemorph] rect[mask] {\r\n\tx: calc(-1 * var(--sz) / 2);\r\n\ty: calc(-1 * var(--sz) / 2);\r\n\twidth: calc(100% + var(--sz));\r\n\theight: calc(100% + var(--sz));\r\n}"
				. ( $bDblLoadFix ?
					"rs-module-wrap.js-lzl-ing:has(rs-module:not(.js-lzl-ing)),\r\nrs-module-wrap:has(rs-module.js-lzl-ing-fin) {\r\n\topacity: 0 !important;\r\n}\r\n\r\nrs-module-wrap.js-lzl-ing:has(rs-module:not(.js-lzl-ing):not([data-lzl-layout=\"fullscreen\"])) {\r\n\theight: calc(1px * var(--lzl-rs-cy)) !important;\r\n}\r\n\r\nrs-module-wrap:not(.js-lzl-ing) {\r\n\ttransition: opacity 1000ms ease-in-out;\r\n}\r\n\r\nbody:not(.seraph-accel-js-lzl-ing) rs-module-wrap:has(rs-module:not(.js-lzl-ing)) {\r\n\tz-index: 10 !important;\r\n}\r\n\r\nbody:not(.seraph-accel-js-lzl-ing) rs-module-wrap:has(rs-module.js-lzl-ing) {\r\n\tz-index: 9 !important;\r\n}"
				: "" )
			);
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		{
			/*
			
			
			
			



			*/

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, str_replace( array( '_PRM__ADJUSTED_BUBBLES_', '_PRM_RESTORE_IMGSRC_' ), array( $adjustedBubbles ? '1' : '0', $bDblLoadFix ? '0' : '1' ), "function seraph_accel_cp_sldRev_calcSizes_init( d, stage )
{
	seraph_accel_cmn_calcSizes( d.documentElement );
	seraph_accel_cp_sldRev_calcSizes( d.currentScript.parentNode, stage );
}

function seraph_accel_cp_sldRev_calcSizes( e, stage = undefined )
{
	var aWidths = JSON.parse( e.getAttribute( \"data-lzl-widths\" ) );
	var aWidthsGrid = JSON.parse( e.getAttribute( \"data-lzl-widths-g\" ) );
	var aHeightsGrid = JSON.parse( e.getAttribute( \"data-lzl-heights-g\" ) );
	var bGridDiffScale = !!e.getAttribute( \"data-lzl-g-s\" );

	for( var j = 0; j < aWidths.length; j++ )
		if( window.innerWidth < aWidths[ j ] )
			break;

	if( j == aWidths.length )
		j = aWidths.length - 1;

	var nResponsiveLevel = aWidths.length - 1 - j;

	var nScale = e.clientWidth / aWidthsGrid[ j ];
	if( nScale > 1 )
		nScale = 1;

	var nDiff = ( e.clientHeight - aHeightsGrid[ j ] * ( bGridDiffScale ? nScale : 1 ) ) / 2;
	if( nDiff < 0 )
		nDiff = 0;

	var nExtra = ( e.clientWidth - aWidthsGrid[ j ] ) / 2;
	if( nExtra < 0 )
		nExtra = 0;

	e.style.setProperty( \"--lzl-rs-scale\", nScale );
	e.style.setProperty( \"--lzl-rs-diff-y\", nDiff );
	e.style.setProperty( \"--lzl-rs-extra-x\", nExtra );

	if( e.classList.contains( \"js-lzl-ing\" ) )
	{
		e.parentNode.style.setProperty( \"--lzl-rs-cy\", e.parentNode.clientHeight );

		var eWrapOrig = e.parentNode.parentNode.querySelector( \"#\" + e.parentNode.getAttribute( \"id\" ) + \":has(rs-module:not(.js-lzl-ing))\" );
		if( eWrapOrig )
			eWrapOrig.style.setProperty( \"--lzl-rs-cy\", e.parentNode.clientHeight );
	}

	if( stage === false )
		return;

	e.querySelectorAll( \"rs-slides-lzl [data-cbreak]\" ).forEach(
		function( eR )
		{
			if( parseInt( eR.getAttribute( \"data-cbreak\" ), 10 ) <= nResponsiveLevel )
				eR.classList.add( \"rev_break_columns\" );
			else
				eR.classList.remove( \"rev_break_columns\" );
		}
	);

	if( stage === true )
		e.classList.remove( \"js-lzl-nid\" );
}

function seraph_accel_cp_sldRev_bubblemorph_calcSizes( e )
{
	var sz = Math.max( e.clientWidth, e.clientHeight ) / 5;
	e.style.setProperty( \"--sz\", \"\" + sz + \"px\" );
	e.style.setProperty( \"--flt\", \"url(\\\"#\" + e.id + \"-f-blur\" + ( sz >= 30 ? \"\" : \"-sm\" ) + \"\\\")\" );
}

function seraph_accel_cp_sldRev_loadFinish( e, itemIdTmp, bUseTm = true )
{
	if( !e.hasAttribute( \"data-lzl-ing-t\" ) )
		return;

	var nWait = bUseTm ? parseInt( e.getAttribute( \"data-lzl-ing-t\" ), 10 ) : 0;
	e.removeAttribute( \"data-lzl-ing-t\" );

	var eIng = document./*getElementById( itemIdTmp )*/querySelector( \"#\" + itemIdTmp + \".js-lzl-ing\" );
	if( !eIng )
		return;

	setTimeout(
		function()
		{
			e.parentNode.classList.remove( \"js-lzl-ing\" );
			eIng.parentNode.classList.remove( \"js-lzl-ing\" );

			setTimeout(
				function()
				{
					eIng.classList.add( \"js-lzl-ing-fin\" );

					setTimeout(
						function()
						{
							setTimeout(
								function()
								{
									eIng.parentNode.remove();
								}
							, 0 );
						}
					, 1000 );
				}
			, 1000 );

		}
	, nWait );
}

(
	function( d )
	{
		function OnEvt( evt )
		{
			d.querySelectorAll( \"rs-module:not(.revslider-initialised)[data-lzl-widths]\" ).forEach( seraph_accel_cp_sldRev_calcSizes );
			if( _PRM__ADJUSTED_BUBBLES_ )
				d.querySelectorAll( \"rs-module:not(.revslider-initialised) .rs-lzl-cont.js-lzl-ing [data-bubblemorph]\" ).forEach( seraph_accel_cp_sldRev_bubblemorph_calcSizes );
		}

		d.addEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
		seraph_accel_lzl_bjs.add(
			function()
			{
				d.removeEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
				if( _PRM_RESTORE_IMGSRC_ )
					d.querySelectorAll( \"rs-slides img\" ).forEach( function( i ){ if( i.hasAttribute( \"data-lzl-src\" ) ) i.setAttribute( \"src\", i.getAttribute( \"data-lzl-src\" ) ); } );

				//d.querySelectorAll( \"rs-module-wrap.js-lzl-ing\" ).forEach(
				//    function( e )
				//    {
				//        setTimeout(
				//            function()
				//            {
				//                e.classList.remove( \"js-lzl-ing\" );
				//            }
				//        , 5000 );
				//    }
				//);
			}
		);
	}
)( document, _PRM__ADJUSTED_BUBBLES_, _PRM_RESTORE_IMGSRC_ );
" ) );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

// #######################################################################

function _ProcessCont_Cp_sldRev7( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( HtmlNd::ChildrenAsArr( $xpath -> query( './/sr7-module' ) ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@(?:^|\\W|\\.)sr7-@' ] = true;

		$ctxProcess[ 'aJsCrit' ][ 'src:@/revslider/public/js@' ] = true;
		$ctxProcess[ 'aJsCrit' ][ 'body:@(?:^|\\W)SR7\\.\\w@' ] = true;
	}
}

// #######################################################################

function _RevSld_GetEngineVer( &$ctxProcess, $xpath )
{
	$engineVer = '9999.9999';

	$itemEngineScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="revmin-js"]' ) );
	if( !$itemEngineScr )
		return( $engineVer );

	$src = $itemEngineScr -> getAttribute( 'src' );
	$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $src );

	$cont = null;
	if( ($srcInfo[ 'filePath' ]??null) )
	{
		$cont = @file_get_contents( $srcInfo[ 'filePath' ] );
		if( $cont === false && /*isset( $srcInfo[ 'filePathRoot' ] ) && */!Gen::DoesFileDirExist( $srcInfo[ 'filePath' ], $srcInfo[ 'filePathRoot' ] ) )
			$cont = null;
	}
	if( $cont === null )
		$cont = GetExtContents( $ctxProcess, ($srcInfo[ 'url' ]??null), $contMimeType );

	if( !is_string( $cont ) || !preg_match( '@"Slider\\sRevolution\\s([\\d\\.]+)"@', $cont, $m ) )
		return( $engineVer );

	$engineVer = $m[ 1 ];
	return( $engineVer );
}

function _RevSld_GetPrmsFromScr( $item, $itemInitCmnScr, $itemIdTmp )
{
	if( !$itemInitCmnScr )
		return( null );

	$prms = array();

	// MBI!!! script can be relocated so it is better to search in whole page body
	for( $itemInitScr = $item -> nextSibling; $itemInitScr; $itemInitScr = $itemInitScr -> nextSibling )
	{
		if( $itemInitScr -> nodeName != 'script' )
			continue;

		$m = array();
		if( !preg_match( '@^\\s*setREVStartSize\\(\\s*({[^}]*})@', $itemInitScr -> nodeValue, $m ) )
			continue;

		$m = @json_decode( Gen::JsObjDecl2Json( $m[ 1 ] ), true );
		if( !$m )
			return( null );

		$prms[ 'start' ] = $m;
		break;
	}

	if( !$itemInitScr )
		return( null );

	$cmdScrId = array();
	if( !preg_match( '@\\.\\s*RS_MODULES\\s*.\\s*modules\\s*\\[\\s*["\']([\\w\\-]+)["\']\\s*\\]@', $itemInitScr -> nodeValue, $cmdScrId ) )
		return( null );

	$cmdScrId = $cmdScrId[ 1 ];

	$posStart = array();
	if( !preg_match( '@\\WRS_MODULES\\s*.\\s*modules\\s*\\[\\s*["\']' . $cmdScrId . '["\']\\s*\\]\\s*=\\s*{@', $itemInitCmnScr -> nodeValue, $posStart, PREG_OFFSET_CAPTURE ) )
		return( null );

	$posStart = $posStart[ 0 ][ 1 ] + strlen( $posStart[ 0 ][ 0 ] );

	if( !preg_match( '@\\W(\\w+)\\.revolutionInit\\s*\\(\\s*@', $itemInitCmnScr -> nodeValue, $posStartInit, PREG_OFFSET_CAPTURE, $posStart ) )
		return( null );

	$posStart = $posStartInit[ 0 ][ 1 ] + strlen( $posStartInit[ 0 ][ 0 ] );
	$pos = Gen::JsonGetEndPos( $posStart, $itemInitCmnScr -> nodeValue );
	if( $pos === null )
		return( null );

	$prms[ 'init' ] = @json_decode( Gen::JsObjDecl2Json( substr( $itemInitCmnScr -> nodeValue, $posStart, $pos - $posStart ) ), true );

	//REVSLIDER_READY_TO_USE
	//revolution.slide.beforeredraw
	//revolution.slide.afterdraw
	//revolution.slide.firstrun
	//enterviewport
	//timeline_scroll_processed
	//stoptimer
	//revolution.slide.onloaded

	//revolution.slide.onbeforeswap
	//nulltimer
	//restarttimer
	//layerinitialised (несколько)
	//revolution.slideprepared
	//revolution.layeraction (несколько) - в аргументах {layer: e.<computed>, eventtype: 'enterstage', id: 'hero2', layerid: 'slider-20-slide-36-layer-18', layertype: 'row', Е}
	//timeline_scroll_processed
	//revolution.slide.onchange
	//revolution.slide.onafterswap
	//timeline_scroll_processed

	$aCssCleanSelLate = array( '.rs-lzl-cont.js-lzl-ing' );
	$aCssCleanSel = array( /*'.rs-lzl-cont.js-lzl-ing'*/ );
	if( Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'enable' ) ) )
		$aCssCleanSel[] = 'rs-bullets.js-lzl-ing';
	if( Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'enable' ) ) )
		$aCssCleanSel[] = 'rs-tabs.js-lzl-ing';
	if( Gen::GetArrField( $prms, array( 'init', 'navigation', 'thumbnails', 'enable' ) ) )
		$aCssCleanSel[] = 'rs-thumbs.js-lzl-ing';
	if( $aCssCleanSelLate || $aCssCleanSel || $itemIdTmp )
		$itemInitCmnScr -> nodeValue = substr_replace( $itemInitCmnScr -> nodeValue,
			( $aCssCleanSelLate && !$itemIdTmp ? ( $posStartInit[ 1 ][ 0 ] . '.on( "revolution.slide.onloaded", function(){' . /**/"\n" . /**/ 'jQuery(this).children("' . implode( ',', $aCssCleanSelLate ) . '").remove();});' ) : '' ) .
			( $aCssCleanSel && !$itemIdTmp ? ( $posStartInit[ 1 ][ 0 ] . '.on( "revolution.slide.afterdraw", function(){' . /**/"\n" . /**/ 'jQuery(this.parentNode).find("' . implode( ',', $aCssCleanSel ) . '").remove();});' ) : '' ) .
			( $itemIdTmp ? ( $posStartInit[ 1 ][ 0 ] . '.on( "revolution.slide.onchange", function(){' . /**/"\nconsole.log(\"DEBUG: revolution.slide.onchange\");" . /**/ 'seraph_accel_cp_sldRev_loadFinish(this,"' . $itemIdTmp . '");});' ) : '' ) .
			''
		, $posStartInit[ 1 ][ 1 ], 0 );

	return( $prms );
}

function _RevSld_GetAttrs( $data, $nValsForce = false, $valSep = ',' )
{
	$res = array();
	foreach( explode( ';', $data ) as $e )
	{
		if( !strlen( $e ) )
			continue;

		$e = explode( ':', $e );
		if( count( $e ) > 2 )
			continue;

		if( count( $e ) < 2 )
			array_splice( $e, 0, 0, array( '' ) );

		$iBracket = 0;
		for( $i = 0; $i < strlen( $e[ 1 ] ); $i++ )
		{
			$c = $e[ 1 ][ $i ];
			if( $c == '(' )
				$iBracket++;
			else if( $c == ')' )
				$iBracket--;
			else if( $iBracket > 0 && $c == ',' )
				$e[ 1 ][ $i ] = "\xFF";
		}

		if( strpos( $e[ 1 ], $valSep ) !== false )
		{
			$e[ 1 ] = array_map(
				function( $e )
				{
					$e = trim( $e, " \t\n\r\0\x0B[]'" );
					return( $e );
				}
			, explode( $valSep, $e[ 1 ] ) );
		}
		else if( Gen::StrStartsWith( $e[ 1 ], 'cyc(' ) )
			$e[ 1 ] = array( 'cyc' => array_map( 'trim', explode( '|', substr( $e[ 1 ], 4, -1 ) ) ) );
		else if( $nValsForce )
			$e[ 1 ] = array_fill( 0, $nValsForce, $e[ 1 ] );

		$e[ 1 ] = Gen::StrReplace( "\xFF", ',', $e[ 1 ] );

		$res[ $e[ 0 ] ] = $e[ 1 ];
	}

	return( $res );
}

function _RevSld_GetSize( $scaleInit, $sz, $prefix = '', $suffix = '' )
{
	if( $sz === null )
		return( null );

	$res = '';

	$szSuffix = array();
	if( preg_match( '@\\D+$@', $sz, $szSuffix ) )
	{
		$szSuffix = $szSuffix[ 0 ];
		$sz = substr( $sz, 0, -strlen( $szSuffix ) );
	}
	else
		$szSuffix = '';

	$scale = false;
	if( !$szSuffix )
		$szSuffix = 'px';

	if( $szSuffix == 'px' && ( float )$sz )
		$scale = $scaleInit;

	$calc = false;
	if( $scale || $prefix || $suffix )
		$calc = true;

	if( $calc )
		$res .= 'calc('/* . '('*/;
	if( $prefix )
		$res .= $prefix;
	$res .= $sz . $szSuffix;
	if( $scale )
		$res .= ' * var(--lzl-rs-scale)';
	if( $suffix )
		$res .= $suffix;
	if( $calc )
		$res .= /*') * 4.9406564584124654e-324 / 4.9406564584124654e-324' . */')';
	// https://stackoverflow.com/questions/37754542/css-calc-round-down-with-two-decimal-cases
	return( $res );
}

function _RevSld_SetStyleAttrEx( &$aItemStyle, $itemChildSelector, $i, $styles )
{
	$aDst = &$aItemStyle[ $i ][ $itemChildSelector ];

	if( !is_array( $aDst ) )
	{
		$aDst = $styles;
		return;
	}

	if( isset( $styles[ 'transform' ] ) && isset( $aDst[ 'transform' ] ) )
	{
		$aDst[ 'transform' ] = ( Gen::StrEndsWith( $aDst[ 'transform' ], '!important' ) ? substr( $aDst[ 'transform' ], 0, strlen( $aDst[ 'transform' ] ) - 10 ) : $aDst[ 'transform' ] ) . ' ' . $styles[ 'transform' ];
		unset( $styles[ 'transform' ] );
	}

	$aDst = array_merge( $aDst, $styles );
}

function _RevSld_SetStyleAttr( &$styleSeparated, &$aItemStyle, $itemChildSelector, $a )
{
	if( count( $a ) == 1 )
	{
		$styleSeparated = array_merge( $styleSeparated, $a[ 0 ] );
		return;
	}

	foreach( $a as $i => $styles )
		_RevSld_SetStyleAttrEx( $aItemStyle, $itemChildSelector, $i, $styles );
}

function _RevSld_GetIdxPropVal( $props, $path, $i, $vDef = null )
{
	$props = ( array )Gen::GetArrField( $props, $path );
	$v = Gen::GetArrField( $props, array( $i ) );
	if( $v === null && $i !== 0 )
		$v = Gen::GetArrField( $props, array( 0 ) );
	return( $v !== null ? $v : $vDef );
}

function _RevSld_HavHideMode( &$itemStyleCont, $itemId, $prms, $type, $sel )
{
	foreach( array( 'hide_under' => array( 'l' => 'max', 'o' => -1 ), 'hide_over' => array( 'l' => 'min', 'o' => 0 ) ) as $hideMode => $hideLim )
		if( $v = ( int )Gen::GetArrField( $prms, array( 'init', 'navigation', $type, $hideMode ) ) )
			$itemStyleCont .= '@media (' . $hideLim[ 'l' ] . '-width: ' . ( $v + $hideLim[ 'o' ] ) . 'px){#' . $itemId . ' ' . $sel . '.js-lzl-ing{display:none!important;}}';
}

function _RevSld_AdjustTimeoutByVal( &$nTimeout, $nTimeoutMax, $v, $vAdd = 0 )
{
	if( !is_int( $v ) )
	{
		if( is_array( $v ) )
		{
			$bApply = true;
			foreach( $v as $vI )
				if( !_RevSld_AdjustTimeoutByVal( $nTimeout, $nTimeoutMax, $vI ) )
					$bApply = false;
			return( $bApply );
		}
		else if( is_string( $v ) )
		{
			if( Gen::StrEndsWith( $v, 'ms' ) )
				$v = ( int )$v;
			else if( Gen::StrEndsWith( $v, 's' ) )
				$v = ( int )$v * 1000;
			else
				$v = ( int )$v;
		}
		else
			$v = 0;
	}

	if( $vAdd )
	{
		$vAdd2 = 0; _RevSld_AdjustTimeoutByVal( $vAdd2, null, $vAdd );
		$v = $v + $vAdd2;
	}

	if( $nTimeoutMax !== null && $v > $nTimeoutMax )
		return( false );

	if( $nTimeout < $v )
		$nTimeout = $v;

	return( true );
}

// #######################################################################
// #######################################################################

?>