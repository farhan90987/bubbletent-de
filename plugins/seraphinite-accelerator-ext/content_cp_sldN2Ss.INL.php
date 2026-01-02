<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_sldN2Ss( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$itemInitCmnScr = null;

	// Templates
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-section-smartslider ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$tplApplied = false;
		foreach( $xpath -> query( './/template[@data-loading-type]', $item ) as $itemTpl )
		{
			HtmlNd::MoveChildren( $itemTpl -> parentNode, $itemTpl );
			$itemTpl -> parentNode -> removeChild( $itemTpl );
			$tplApplied = true;
		}

		if( $tplApplied )
			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'height' => null ) ) ) );
	}

	$bRtScript = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-slider ")]' ) as $itemSld )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $itemSld ) )
			continue;

		if( !$itemInitCmnScr )
			$itemInitCmnScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[contains(text(),"_N2.r(")]' ) );

		$cfg = _ProcessCont_Cp_sldN2Ss_GetMeta( $itemSld -> getAttribute( 'id' ), $itemInitCmnScr );

		// Bullets
		if( $itemBulletTpl = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-bullet ")][1]', $itemSld ) ) )
		{
			$itemBulletTpl -> removeAttribute( 'style' );

			$i = 0;
			foreach( $xpath -> query( './/*[@data-slide-public-id]', $itemSld ) as $item )
			{
				$itemBullet = $itemBulletTpl -> cloneNode( true );
				$itemBulletCont = $doc -> createElement( 'div' );
				$itemBulletCont -> appendChild( $itemBullet );
				$itemBulletTpl -> parentNode -> appendChild( $itemBulletCont );

				if( $i === 0 )
					HtmlNd::AddRemoveAttrClass( $itemBullet, array( 'n2-active' ) );

				$i++;
			}

			$itemBulletTpl -> parentNode -> removeChild( $itemBulletTpl );
		}

		$idFirstSlide = '1';
		{
			$itemFirstSlide = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-slide-public-id][@data-first="1"]', $itemSld ) );
			if( $itemFirstSlide )
			{
				$idFirstSlide = $itemFirstSlide -> getAttribute( 'data-slide-public-id' );
				$itemFirstSlide -> setAttribute( 'data-lzl-first', '1' );
			}
			else if( $itemFirstSlide = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-slide-public-id="1"]', $itemSld ) ) )
				$itemFirstSlide -> setAttribute( 'data-lzl-first', '1' );
		}

		if( $itemShowcaseCont = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-showcase-slides ")]', $itemSld ) ) )
			HtmlNd::AddRemoveAttrClass( $itemShowcaseCont, array( 'n2-ss-showcase-slides--ready' ) );

		if( $itemFirstBg = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-slide-backgrounds ")]//*[@data-public-id="' . $idFirstSlide . '"]', $itemSld ) ) )
		{
			$itemFirstBg -> setAttribute( 'data-lzl-first', '1' );
			if( $itemFirstBgVideo = HtmlNd::FirstOfChildren( $xpath -> query( './/video[contains(concat(" ",normalize-space(@class)," ")," n2-ss-slide-background-video ")]', $itemFirstBg ) ) )
			{
				$itemFirstBgVideo -> setAttribute( 'preload', '1' );
				$itemFirstBgVideo -> setAttribute( 'autoplay', '1' );
			}
		}

		// Layers
		$bResponsive = false;
		$items = HtmlNd::ChildrenAsArr( $xpath -> query( './/*[@data-slide-public-id="' . $idFirstSlide . '"]//*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-layer ")][contains(concat(" ",normalize-space(@class)," ")," n-uc-")]', $itemSld ) );

		$itemsNeedClone = array();
		foreach( $items as $item )
		{
			$idParent = $item -> getAttribute( 'data-parentid' );
			if( !$idParent )
				continue;

			$itemParent = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@id="' . $idParent . '"]', $itemSld ) );
			if( !$itemParent || $itemParent -> parentNode !== $item -> parentNode )
				continue;

			$itemsNeedClone[] = $itemParent;
			$itemsNeedClone[] = $item;
		}

		$fnGetClone = function( $fnGetClone, $xpath, $itemSld, $item )
		{
			$idParent = $item -> getAttribute( 'data-parentid' );
			if( $idParent )
			{
				$itemParent = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-id-ex="' . $idParent . '"]', $itemSld ) );
				if( !$itemParent )
					if( $itemParent = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@id="' . $idParent . '"]', $itemSld ) ) )
						$itemParent = $fnGetClone( $fnGetClone, $xpath, $itemSld, $itemParent );
					else
						$itemParent = $item -> parentNode;
			}
			else
				$itemParent = $item -> parentNode;

			$id = $item -> getAttribute( 'id' );
			if( $id )
				if( $itemClone = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-id-ex="' . $id . '"]', $itemSld ) ) )
					return( $itemClone );

			HtmlNd::AddRemoveAttrClass( $item, 'js-lzl-n-ing' );
			$itemClone = $item -> cloneNode( true );
			$itemParent -> appendChild( $itemClone );
			HtmlNd::AddRemoveAttrClass( $itemClone, 'js-lzl-ing', 'js-lzl-n-ing' );
			HtmlNd::RenameAttr( $itemClone, 'id', 'data-id-ex' );
			HtmlNd::RenameAttr( $itemClone, 'data-parentid', 'data-parentid-ex' );
			return( $itemClone );
		};

		foreach( $items as $item )
		{
			$layerSelectorEx = '';
			if( in_array( $item, $itemsNeedClone, true ) )
			{
				$item = $fnGetClone( $fnGetClone, $xpath, $itemSld, $item );
				$layerSelectorEx = '.js-lzl-ing';
			}

			$layerSelectorUnique = '';
			foreach( Ui::ParseClassAttr( $item -> getAttribute( 'class' ) ) as $class )
				if( Gen::StrStartsWith( $class, 'n-uc-' ) )
				{
					$layerSelectorUnique = '.' . $class;
					break;
				}

			$rotation = $item -> getAttribute( 'data-rotation' );
			$responsiveposition = $item -> getAttribute( 'data-responsiveposition' );
			$responsivesize = $item -> getAttribute( 'data-responsivesize' );
			$bHasParent = !!$item -> getAttribute( 'data-parentid-ex' );

			if( $responsiveposition || $responsivesize )
				$bResponsive = true;

			{
				$style = Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) );

				if( $itemSld -> getAttribute( 'data-ss-legacy-font-scale' ) && $item -> getAttribute( 'data-sstype' ) == 'layer' )
				{
					$style[ 'font-size' ] = $bHasParent ? '100%' : 'calc(100%*var(--ss-responsive-scale)*var(--ssfont-scale))';
				}

				if( $style )
					$item -> setAttribute( 'style', Ui::GetStyleAttr( $style ) );
			}

			$stylesSeparated = array( 'desktop' => array(), 'tablet' => array(), 'mobile' => array() );

			foreach( $stylesSeparated as $view => &$styleSeparated )
			{
				if( ( $v = $item -> getAttribute( 'data-' . $view . 'portraitwidth' ) ) !== null )
					$styleSeparated[ 'width' ] = is_numeric( $v ) ? ( 'calc(' . $v . 'px' . ( $responsivesize ? ' * var(--ss-responsive-scale))' : '' ) ) : ( $v == 'auto' ? '100%' : $v );
				if( ( $v = $item -> getAttribute( 'data-' . $view . 'portraitheight' ) ) !== null )
					$styleSeparated[ 'height' ] = is_numeric( $v ) ? ( 'calc(' . $v . 'px' . ( $responsivesize ? ' * var(--ss-responsive-scale))' : '' ) ) : $v;

				$left = $item -> getAttribute( 'data-' . $view . 'portraitleft' );
				$top = $item -> getAttribute( 'data-' . $view . 'portraittop' );
				$translate = array( 0, 0 );

				switch( $item -> getAttribute( 'data-' . $view . 'portraitalign' ) )
				{
					case 'center':
						$translate[ 0 ] = '-50%';
						break;

					case 'right':
						$translate[ 0 ] = '-100%';
						break;

					default:
						break;
				}
				switch( $item -> getAttribute( $bHasParent ? 'data-' . $view . 'portraitparentalign' : 'data-' . $view . 'portraitalign' ) )
				{
					case 'center':
						$styleSeparated[ 'left' ] = 'calc(50%' . ( $left !== null ? ( ' + ' . $left . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) ) : '' ) . ')';
						break;

					case 'right':
						$styleSeparated[ 'left' ] = 'calc(100%' . ( $left !== null ? ( ' + ' . $left . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) ) : '' ) . ')';
						break;

					default:
						if( $left )
							$styleSeparated[ 'left' ] = 'calc(' . $left . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) . ')';
						break;
				}

				switch( $item -> getAttribute( 'data-' . $view . 'portraitvalign' ) )
				{
					case 'middle':
						$translate[ 1 ] = '-50%';
						break;

					case 'bottom':
						$translate[ 1 ] = '-100%';
						break;

					default:
						break;
				}
				switch( $item -> getAttribute( $bHasParent ? 'data-' . $view . 'portraitparentvalign' : 'data-' . $view . 'portraitvalign' ) )
				{
					case 'middle':
						$styleSeparated[ 'top' ] = 'calc(50%' . ( $top !== null ? ( ' + ' . $top . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) ) : '' ) . ')';
						break;

					case 'bottom':
						$styleSeparated[ 'top' ] = 'calc(100%' . ( $top !== null ? ( ' + ' . $top . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) ) : '' ) . ')';
						break;

					default:
						if( $top )
							$styleSeparated[ 'top' ] = 'calc(' . $top . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) . ')';
						break;
				}

				if( $translate[ 0 ] || $translate[ 1 ] )
				{
					$styleSeparated[ 'transform' ] = 'translate(' . $translate[ 0 ] . ', ' . $translate[ 1 ] . ')';
					if( $rotation )
						$styleSeparated[ 'transform' ] .= ' rotate(' . $rotation . 'deg)';
					$styleSeparated[ 'transform' ] .= '!important';
				}
			}
			unset( $styleSeparated );

			{
				$cont = '';
				foreach( $stylesSeparated as $view => $styleSeparated )
				{
					if( !$styleSeparated )
						continue;

					if( $view == 'tablet' )
						$cont .= '@media (orientation: landscape) and (max-width: 1199px) and (min-width: 901px), (orientation: portrait) and (max-width: 1199px) and (min-width: 701px) {' . "\n";
					else if( $view == 'mobile' )
						$cont .= '@media (orientation: landscape) and (max-width: 900px), (orientation: portrait) and (max-width: 700px) {' . "\n";

					$cont .= '.n2-ss-slider:not(.n2-ss-loaded) .n2-ss-layer' . $layerSelectorEx . $layerSelectorUnique . '{' . Ui::GetStyleAttr( $styleSeparated ) . '}' . "\n";

					if( $view != 'desktop' )
						$cont .= '}' . "\n";
				}

				if( $cont )
				{
					$itemStyle = $doc -> createElement( 'style' );
					if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
						$itemStyle -> setAttribute( 'type', 'text/css' );
					HtmlNd::SetValFromContent( $itemStyle, $cont );
					$item -> parentNode -> insertBefore( $itemStyle, $item );
				}
			}
		}

		if( $bResponsive )
		{
			$maxWidth = Gen::GetArrField( $cfg, array( 'responsive', 'base', 'slideOuterWidth' ) );
			if( !$maxWidth )
				$maxWidth = '1200';

			/*
			if( !$itemSld -> hasAttribute( 'data-ss-max-width-def' ) )
				$itemSld -> setAttribute( 'data-ss-max-width-def', $maxWidth );

			$itemSizeLimiter = null;
			foreach( $xpath -> query( './/svg[contains(concat(" ",normalize-space(@class)," ")," n2-ss-slide-limiter ")]', $itemSld ) as $itemSizeLimit )
			{
				$viewBox = $itemSizeLimit -> getAttribute( 'viewBox' );
				if( !$viewBox )
					$viewBox = $itemSizeLimit -> getAttribute( 'viewbox' );

				$m = array();
				if( $viewBox && preg_match( '@^\\s*\\d+\\s+\\d+\\s+(\\d+)\\s+(\\d+)\\s*$@', $viewBox, $m ) )
				{
					$maxWidth = $m[ 1 ];
					$itemSizeLimiter = $itemSizeLimit;
					//$itemSld -> setAttribute( 'max-height', $m[ 2 ] );
				}
			}
			*/

			//				$itemSld -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSld -> getAttribute( 'style' ) ), array( '--ss-responsive-scale' => '1.0' ) ) ) );

			if( !$itemSld -> hasAttribute( 'data-ss-max-width' ) )
				$itemSld -> setAttribute( 'data-ss-max-width', $maxWidth );

			/*
			if( $itemSizeLimiter )
			{
				HtmlNd::AddRemoveAttrClass( $itemSizeLimiter, array( 'ss-size-limiter' ) );
				unset( $itemSizeLimiter );
			}
			*/

			//if(0)
			//{
			//    $itemScript = $doc -> createElement( 'script' );
			//    if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			//        $itemScript -> setAttribute( 'type', 'text/javascript' );
			//    $itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			//    HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_sldN2Ss_calcSizes(document.currentScript.parentNode);' );
			//    $itemSld -> insertBefore( $itemScript, $itemSld -> firstChild );
			//}

			$bRtScript = true;
		}

		if( ($cfg[ 'initType' ]??null) == 'SmartSliderCarousel' )
		{
			foreach( HtmlNd::ChildrenAsArr( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-slide ")]', $itemSld ) ) as $itemIdx => $itemSlide )
				$itemSlide -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSlide -> getAttribute( 'style' ) ), array( '--slide-group-index' => ( string )$itemIdx ) ) ) );

			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," nextend-bullet-bar ")]/*', $itemSld ) as $itemBullet )
				$itemBullet -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemBullet -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );

			if( $itemPane = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-slider-pane ")]', $itemSld ) ) )
				$itemPane -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemPane -> getAttribute( 'style' ) ), array( 'width' => '100%' ) ) ) );

			$itemSld -> setAttribute( 'data-ss-carousel', @json_encode( array( 'slideOuterWidth' => Gen::GetArrField( $cfg, array( 'responsive', 'base', 'slideOuterWidth' ) ), 'minSlideGap' => Gen::GetArrField( $cfg, array( 'responsive', 'minimumSlideGap' ) ) ) ) );
			$bRtScript = true;
		}
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $bRtScript )
	{
		/*
		
		
		
		



		*/

		$itemScript = $doc -> createElement( 'script' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$itemScript -> setAttribute( 'type', 'text/javascript' );
		$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
		HtmlNd::SetValFromContent( $itemScript, "function seraph_accel_cp_sldN2Ss_calcSizes( e )
{
	var nMaxWidth = parseInt( e.getAttribute( \"data-ss-max-width\" ), 10 );
	if( nMaxWidth )
	{
		var eSizer = undefined;
		e.querySelectorAll( \".n2-ss-slide-limiter\" ).forEach(
			function( eSizerProbe )
			{
				if( !eSizer && getComputedStyle( eSizerProbe ).getPropertyValue( \"display\" ) != \"none\" )
					eSizer = eSizerProbe;
			}
		);
		if( !eSizer )
			eSizer = e;

		var nScale = eSizer.clientWidth / nMaxWidth;
		var nScaleLim = parseInt( e.getAttribute( \"data-ss-legacy-font-scale\" ), 10 ) ? (1+1/6) : 1;
		e.style.setProperty( \"--ss-responsive-scale\", nScale > nScaleLim ? nScaleLim : nScale );
	}

	var data; try { data = JSON.parse( e.getAttribute( \"data-ss-carousel\" ) ); } catch( err ) {};
	if( data )
	{
		var pane = e.querySelector( \".n2-ss-slider-pane\" );
		var paneWidth = pane.getBoundingClientRect().width;
		//console.log( \"paneWidth clientRect\", pane.getBoundingClientRect() );

		var nSalidesInGroup = Math.max( 1, Math.floor( paneWidth / ( data.slideOuterWidth + data.minSlideGap ) ) );
		var marginSide = Math.floor( ( paneWidth - nSalidesInGroup * data.slideOuterWidth ) / nSalidesInGroup / 2 );
		if( marginSide < 0 )
			marginSide = 0;

		pane.style.setProperty( \"--slide-margin-side\", \"\" + marginSide + \"px\" );
		pane.style.setProperty( \"--slide-transform-offset\", \"0!important\" );
		pane.style.setProperty( \"--self-side-margin\", \"none!important\" );

		var bullets = Array.from( e.querySelectorAll( \".nextend-bullet-bar>*\" ) );
		var nBulletsDisplay = nSalidesInGroup ? ( nSalidesInGroup != bullets.length ? Math.ceil( bullets.length / nSalidesInGroup ) : 0 ) : bullets.length;
		for( var i = 0; i < bullets.length; i++ )
		{
			if( i + 1 > nBulletsDisplay )
				bullets[ i ].style.setProperty( \"display\", \"none\" );
			else
				bullets[ i ].style.removeProperty( \"display\" );
		}
	}
}

(
	function( d )
	{
		function OnEvt( evt )
		{
			d.querySelectorAll( \".n2-ss-slider:not(.n2-ss-loaded)\" ).forEach( seraph_accel_cp_sldN2Ss_calcSizes );
		}

		d.addEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
		seraph_accel_lzl_bjs.add( function() { d.removeEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } ); } );
	}
)( document );
" );
		$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
	}
}

function _ProcessCont_Cp_sldN2Ss_GetMeta( $id, $itemInitCmnScr )
{
	$prms = array();

	if( !$itemInitCmnScr )
		return( $prms );

	if( !preg_match( '@\\Wnew\\s+_N2\\s*\\.\\s*(\\w+)\\(\\s*\'' . $id . '\'\\s*,\\s*@', $itemInitCmnScr -> nodeValue, $m, PREG_OFFSET_CAPTURE ) )
		return( $prms );

	$prms[ 'initType' ] = $m[ 1 ][ 0 ];	/* SmartSliderSimple, SmartSliderCarousel, ... */

	$posStart = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
	$posEnd = Gen::JsonGetEndPos( $posStart, $itemInitCmnScr -> nodeValue );
	if( $posEnd === null )
		return( $prms );

	$data = substr( $itemInitCmnScr -> nodeValue, $posStart, $posEnd - $posStart );

	while( preg_match( '@\\"(\\w+)\\"\\s*:\\s*function\\(\\)\\s*@', $data, $m, PREG_OFFSET_CAPTURE ) )
	{
		$posStart = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
		$posEnd = Gen::JsonGetEndPos( $posStart, $data );

		$dataSub = '';

		switch( $m[ 1 ][ 0 ] )
		{
		case 'initCallbacks':
			if( preg_match( '@\\Wnew\\s+_N2\\s*.\\s*SmartSliderWidgetBulletTransition\\(\\s*this\\s*,\\s*@', $data, $mSub, PREG_OFFSET_CAPTURE, $posStart ) )
			{
				if( strlen( $dataSub ) )
					$dataSub .= ',';

				$posStartSub = $mSub[ 0 ][ 1 ] + strlen( $mSub[ 0 ][ 0 ] );
				$posEndSub = Gen::JsonGetEndPos( $posStartSub, $data );

				$dataSub .= '"SmartSliderWidgetBulletTransition":' . substr( $data, $posStartSub, $posEndSub - $posStartSub );
			}
			break;
		}

		$data = substr_replace( $data, '"' . $m[ 1 ][ 0 ] . '":{' . $dataSub . '}', $m[ 0 ][ 1 ], $posEnd - $m[ 0 ][ 1 ] );
	}

	$prms = array_merge_recursive( Gen::GetArrField( @json_decode( Gen::JsObjDecl2Json( $data ), true ), array( '' ), array() ), $prms );
	return( $prms );
}

// #######################################################################
// #######################################################################

?>