<?php

// https://www.erosdoll.com/wp-content/plugins/greenshift-animation-and-page-builder-blocks/libs/aos/aoslight.js?ver=3.4

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_grnshftPbAosAniEx( $ctx, &$ctxProcess, $settFrm, $doc, $xpath, $prop )
{
	$adjusted = false;

	foreach( $xpath -> query( './/*[@' . $prop . ']' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		if( $prop == 'data-aos' && ( $itemBody = HtmlNd::FirstOfChildren( $xpath -> query( './/body' ) ) ) )
			if( !$itemBody -> hasAttribute( 'data-aos-duration' ) )
				$itemBody -> setAttribute( 'data-aos-duration', '1000' );

		$ctxProcess[ 'aCssCrit' ][ '@\\.aos-animate@' ] = true;

		/*
		
		
		
		



		*/

		$ctx -> aAniAppear[ '[' . $prop . ']:not(.aos-animate)' ] = 'function( e )
{
	e.classList.add( "aos-animate" );
}';
	}
}

// #######################################################################
// #######################################################################

?>