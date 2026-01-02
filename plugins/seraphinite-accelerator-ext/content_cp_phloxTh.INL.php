<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_phloxThRspnsv( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	if( !HtmlNd::FirstOfChildren( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," theme-phlox")]' ) ) )
		return;

	// /wp-content/themes/phlox-pro/js/scripts.min@ver-5.14.0.js: aux-dom-ready
	HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], array( 'aux-dom-ready' ), array( 'aux-dom-unready' ) );

	if( $ctxProcess[ 'mode' ] & 1 )
	{
		$aSwitch = array();
		foreach( $xpath -> query( './/*[@data-switch-width]' ) as $item )
			$aSwitch[ $item -> getAttribute( 'data-switch-width' ) ][] = 'body.seraph-accel-js-lzl-ing #' . $item -> getAttribute( 'id' );

		if( $aSwitch )
		{
			$cont = '';
			foreach( $aSwitch as $width => $aSw )
				$cont .= '@media (max-width: ' . $width . 'px){' . implode( ',', $aSw ) . '{display:none}}';

			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, $cont );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _ProcessCont_Cp_phloxThAni( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	// /wp-content/themes/phlox-pro/js/scripts.js

	if( ( $ctxProcess[ 'mode' ] & 1 ) && HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," aux-appear-watch-animation ")]' ) ) )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.aux-animated@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.aux-animated-once@' ] = true;

		{
			/*
			
			
			
			



			*/

			$ctx -> aAniAppear[ '.aux-appear-watch-animation:not(.aux-animated)' ] = 'function( e )
				{
					e.classList.add( "aux-animated" );
					e.classList.add( "aux-animated-once" );
				}';
		}
	}
}

// #######################################################################
// #######################################################################

?>