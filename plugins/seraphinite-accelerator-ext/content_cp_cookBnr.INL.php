<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_cookBrlbs( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	if( !( $ctxProcess[ 'mode' ] & 1 ) )
		return;

	$item = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@id="BorlabsCookieBox"]' ) );
	if( !$item )
		return;

	{
		/*
		
		
		
		



		*/
	}
}

// #######################################################################
// #######################################################################

?>