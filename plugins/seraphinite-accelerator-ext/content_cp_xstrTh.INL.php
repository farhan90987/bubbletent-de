<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_xstrThSwpr( $ctx, &$ctxProcess, $settFrm, $settCache, $settImg, $settCdn, $doc, $xpath )
{
	$contCmnStyle = '';

	$adjusted = false;
	$iItem = 0;
	foreach( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," wp-theme-xstore ")]//*[contains(concat(" ",normalize-space(@class)," ")," swiper-container ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		// /wp-content/themes/xstore/js/modules/swiper.min.js

		$aPrm = array( 'breakpoints' => array() );

		HtmlNd::AddRemoveAttrClass( $item, array( 'lzl-js', 'lzl-id-' . $iItem ) );

		$aPrm[ 'cssIdSelPrefix' ] = '.lzl-id-' . $iItem;

		if( $item -> hasAttribute( 'data-space' ) )
			$aPrm[ 'space' ] = ( int )$item -> getAttribute( 'data-space' );

		$bVer = false;
		if( in_array( 'swiper-control-top', HtmlNd::GetAttrClass( $item ) ) )
		{
			if( in_array( 'swiper-vertical-images', HtmlNd::GetAttrClass( $item -> parentNode ) ) )
				$bVer = true;
		}
		else
		{
			$aPrm[ 'cssSelNavNext' ] = '.swiper-custom-right';
			$aPrm[ 'cssSelNavPrev' ] = '.swiper-custom-left';

			HtmlNd::AddRemoveAttrClass( HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," swiper-wrapper ")][contains(concat(" ",normalize-space(@class)," ")," thumbnails-list ")]//*[contains(concat(" ",normalize-space(@class)," ")," swiper-slide ")][1]', $item ) ), array( 'active-thumbnail' ) );
		}

		if( !$bVer )
		{
			foreach( array( array( 0, 'data-xs-slides' ), array( 481, 'data-sm-slides' ), array( 1199, 'data-lt-slides' ) ) as $m )
				if( $item -> hasAttribute( $m[ 1 ] ) )
					$aPrm[ 'breakpoints' ][] = array( 'minWidth' => $m[ 0 ], 'slidesPerView' => ( int )$item -> getAttribute( $m[ 1 ] ) );
		}
		else if( $item -> hasAttribute( 'data-breakpoints' ) )
		{
			foreach( array( array( 0, 'data-xs-slides' ), array( 640, 'data-sm-slides' ), array( 1024, 'data-md-slides' ), array( 1370, 'data-lt-slides' ) ) as $m )
				if( $item -> hasAttribute( $m[ 1 ] ) )
					$aPrm[ 'breakpoints' ][] = array( 'minWidth' => $m[ 0 ], 'slidesPerView' => ( int )$item -> getAttribute( $m[ 1 ] ) );
		}

		$contCmnStyle .= _ProcessCont_Cp_swiper_AdjustItem( $item, $aPrm, $ctx, $ctxProcess, $doc, $xpath );

		$adjusted = true;
		$iItem++;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		//$ctxProcess[ 'aCssCrit' ][ '@\\.aos-animate@' ] = true;

		{
			$contCmnStyle .= '.lzl-js.swiper-container.swiper-control-bottom:not(.second-initialized) li.thumbnail-item { visibility: visible; }';

			$contCmnStyle .= _Cp_CloneStyles( $ctxProcess, $xpath,
				function( $sel, $bReplace = true, $bFull = false )
				{
					static $g_aExpr = array(
						'@\\.swiper-container\\.swiper-control-bottom\\.second-initialized@S' => 'body .swiper-container.swiper-control-bottom.lzl-js',
					);

					if( !$bReplace )
					{
						foreach( $g_aExpr as $eF => $r )
							if( preg_match( $eF, $sel ) )
								return( true );

						return( false );
					}

					foreach( $g_aExpr as $eF => $r )
						$sel = preg_replace( $eF, $r, $sel );

					return( $sel );
				}
			);

			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, $contCmnStyle );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

// #######################################################################
// #######################################################################

?>