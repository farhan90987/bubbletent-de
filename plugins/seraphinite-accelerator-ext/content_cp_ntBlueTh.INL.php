<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_ntBlueThRspnsv( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	if( !HtmlNd::FirstOfChildren( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," ninetheme-theme-name-NT ")]' ) ) )
		return;

	{
		// /wp-content/themes/nt-blue/js/custom@ver-1.0.js: .welcome_default
		if( HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," welcome_default ")]' ) ) )
			HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], array( 'default-version' ) );
		else
			HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], array(), array( 'default-version' ) );

		if( HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," welcome_rtl ")]' ) ) )
			HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], array( 'rtl_version' ) );
		else
			HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], array(), array( 'rtl_version' ) );
	}

	//if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
	//    continue;

	if( $itemMobIcon = HtmlNd::ParseAndImport( $doc, '<div class="mean-bar js-lzl-ing"><a href="#nav" class="meanmenu-reveal" style="right:0;left:auto;"><span><span><span></span></span></span></a></div>' ) )
		$ctxProcess[ 'ndBody' ] -> insertBefore( $itemMobIcon, $ctxProcess[ 'ndBody' ] -> firstChild );

	if( $ctxProcess[ 'mode' ] & 1 )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.mean-container@' ] = true;

		{
			$itemsCmnStyle = $doc->createElement('style');
			if (apply_filters('seraph_accel_jscss_addtype', false))
				$itemsCmnStyle->setAttribute('type', 'text/css');
			HtmlNd::SetValFromContent($itemsCmnStyle, 'body:not(.seraph-accel-js-lzl-ing) .mean-bar.js-lzl-ing,
body.mean-container.seraph-accel-js-lzl-ing .mainmenu nav,
body:not(.mean-container).seraph-accel-js-lzl-ing .mean-bar.js-lzl-ing {
{
	display: none;
}' );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		// /wp-content/themes/nt-blue/js/meanmenu@ver-1.0.js: showMeanMenu
		{
			/*
			
			
			
			



			*/

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "
				function seraph_accel_cp_ntBlueThRspnsv_calcSizes()
				{
					var currentWidth = window.innerWidth || document.documentElement.clientWidth;
					if( currentWidth <= 767 )
						document.body.classList.add( \"mean-container\" );
					else
						document.body.classList.remove( \"mean-container\" );
				}

				seraph_accel_cp_ntBlueThRspnsv_calcSizes();

				(
					function( d )
					{
						function OnEvt()
						{
							seraph_accel_cp_ntBlueThRspnsv_calcSizes();
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

// #######################################################################
// #######################################################################

?>