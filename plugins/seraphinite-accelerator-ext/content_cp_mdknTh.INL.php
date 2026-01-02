<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_mdknThRspnsv( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	if( !HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," mediken-header-top ")]' ) ) )
		return;

	//if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
	//    continue;

	$itemHdr = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," menu_area ")][contains(concat(" ",normalize-space(@class)," ")," mobile-menu ")]' ) );
	if( !$itemHdr )
		return;

	HtmlNd::AddRemoveAttrClass( $itemHdr, array( 'mean-container' ) );

	if( $itemMobHdr = HtmlNd::ParseAndImport( $doc, '<div class="mean-bar js-lzl-ing"><a href="#nav" class="meanmenu-reveal" style="background:;color:;right:0;left:auto;"><span></span><span></span><span></span></a></div>' ) )
		HtmlNd::InsertBefore( $itemHdr, $itemMobHdr, $itemHdr -> firstChild );

	if( $ctxProcess[ 'mode' ] & 1 )
	{
		{
			$itemsCmnStyle = $doc->createElement('style');
			if (apply_filters('seraph_accel_jscss_addtype', false))
				$itemsCmnStyle->setAttribute('type', 'text/css');
			HtmlNd::SetValFromContent($itemsCmnStyle, 'body:not(.seraph-accel-js-lzl-ing) .mean-bar.js-lzl-ing,
body.seraph-accel-js-lzl-ing .menu_area.mobile-menu > nav,
body.seraph-accel-js-lzl-ing .mean-bar:not(.js-lzl-ing) {
{
	display: none;
}' );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

// #######################################################################
// #######################################################################

?>