<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_lottGen( $ctx, &$ctxProcess, $settFrm, $settCache, $settImg, $settCdn, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/script[contains(text(),"bodymovin.loadAnimation(")]' ) as $itemScr )
	{
		$item = $itemScr -> parentNode;

		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( !preg_match( '@bodymovin\\.loadAnimation\\(\\s*{\\s*container\\s*:\\s*document\\.getElementById\\W@', $itemScr -> nodeValue ) )
			continue;

		$dataFile = array();
		if( preg_match( '@\\Wpath\\s*:\\s*[\'"]([\\w\\/\\.-]+)[\'"]@', $itemScr -> nodeValue, $dataFile, PREG_OFFSET_CAPTURE ) )
			$dataFile = $dataFile[ 1 ];

		if( !$dataFile )
			continue;

		$renderer = array();
		if( preg_match( '@\\Wrenderer\\s*:\\s*[\'"](\\w+)[\'"]@', $itemScr -> nodeValue, $renderer ) )
			$renderer = $renderer[ 1 ];

		$dataFileNew = $dataFile[ 0 ];
		$r = _ProcessCont_Cp_lottGen_AdjustItem( $ctx, $ctxProcess, $settFrm, $settCache, $settImg, $settCdn, $doc, $xpath, $item, $renderer, $dataFileNew );
		if( $r === false )
			return( false );

		if( !$r )
			continue;

		if( $dataFileNew != $dataFile[ 0 ] )
			$itemScr -> nodeValue = substr_replace( $itemScr -> nodeValue, $dataFileNew, $dataFile[ 1 ], strlen( $dataFile[ 0 ] ) );

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@svg\\.lottgen@' ] = true;

		$ctxProcess[ 'aJsCritSpec' ][ 'body:@\\Wbodymovin\\W@' ] = true;

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

function _ProcessCont_Cp_lottGen_AdjustItem( $ctx, &$ctxProcess, $settFrm, $settCache, $settImg, $settCdn, $doc, $xpath, $item, $renderer, &$srcData )
{
	if( $renderer != 'svg' )
		return( null );

	$src = $srcData;
	$cont = _Cp_GetScrContExEx( $ctxProcess, $src );

	$cont = @json_decode( ( string )$cont, true );
	if( !$cont )
		return( null );

	$item -> appendChild( HtmlNd::CreateTag( $doc, 'svg', array( 'xmlns' => 'http://www.w3.org/2000/svg', 'viewBox' => '0 0 ' . Gen::GetArrField( $cont, array( 'w' ), 0 ) . ' ' . Gen::GetArrField( $cont, array( 'h' ), 0 ) . '', 'width' => Gen::GetArrField( $cont, array( 'w' ), 0 ), 'height' => Gen::GetArrField( $cont, array( 'h' ), 0 ), 'class' => array( 'lottgen', 'js-lzl-ing' ), 'style' => 'width:100%;height:100%;transform:translate3d(0px,0px,0px);content-visibility:visible;' ), Gen::GetArrField( $settImg, array( 'lazy', 'load' ), false ) ? array( HtmlNd::CreateTag( $doc, 'image', array( 'href' => LazyLoad_SrcSubst( $ctxProcess, array( 'cx' => Gen::GetArrField( $cont, array( 'w' ), 0 ), 'cy' => Gen::GetArrField( $cont, array( 'h' ), 0 ) ), Gen::GetArrField( $settImg, array( 'lazy', 'plchRast' ), true ) ), 'width' => ( string )Gen::GetArrField( $cont, array( 'w' ), 0 ) . 'px', 'height' => ( string )Gen::GetArrField( $cont, array( 'h' ), 0 ) . 'px' ) ) ) : array() ) );

	$contAdjusted = false;
	foreach( Gen::GetArrField( $cont, array( 'assets' ), array() ) as $assetIdx => $asset )
	{
		$srcImg = Gen::GetArrField( $asset, array( 'p' ), '' );
		if( !$srcImg )
			continue;

		$imgSrc = new ImgSrc( $ctxProcess, $srcImg );

		$r = Images_ProcessSrc( $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn );
		if( $r === false )
			return( false );

		if( !$r )
			continue;

		Gen::SetArrField( $cont, array( 'assets', $assetIdx, 'p' ), $imgSrc -> src );
		$contAdjusted = true;
	}

	if( $contAdjusted && ( $cont = @json_encode( $cont ) ) )
	{
		if( !UpdateSrcChunk( $ctxProcess, $settCache, 'json', $cont, $src ) )
			return( false );

		$srcData = $src;
	}

	return( true );
}

// #######################################################################
// #######################################################################

?>