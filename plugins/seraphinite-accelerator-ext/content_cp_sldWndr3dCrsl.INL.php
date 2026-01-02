<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_sldWndr3dCrsl( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wonderplugin3dcarousel ")]' ) as $item )
	{
	    if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
	        continue;
	}

/*	if( ( $ctxProcess[ 'mode' ] & 1 ) && ( $item = HtmlNd::FirstOfChildren( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," et_divi_theme ")][contains(concat(" ",normalize-space(@class)," ")," et_fixed_nav ")]//*[@id="main-header"]' ) ) ) )
	{
		$itemsCmnStyle = $doc -> createElement( 'style' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
		HtmlNd::SetValFromContent( $itemsCmnStyle, '#page-container:not([style*=padding]) {
	padding-top: var(--divi-hdr-lzl-height) !important;
}

#main-header:not([style*="top"]) {
	top: var(--divi-top-lzl-height) !important;
}' );

		$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
	}*/
}

// #######################################################################
// #######################################################################

?>