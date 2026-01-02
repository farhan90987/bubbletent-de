<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_beThAni( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	if( !( $ctxProcess[ 'mode' ] & 1 ) )
		return;

	if( !HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="mfn-animations-js"]' ) ) )
		return;

	{
		$adjusted = false;
		foreach( $xpath -> query( './/*[@data-anim-type][contains(concat(" ",normalize-space(@class)," ")," animate ")]' ) as $item )
		{
			$ctxProcess[ 'aCssCrit' ][ '@\\.' . $item -> getAttribute( 'data-anim-type' ) . '@' ] = true;
			$adjusted = true;
		}

		if( $adjusted )
		{
			/*
			
			
			
			



			*/

			$ctx -> aAniAppear[ '[data-anim-type].animate:not(.lzl-ad)' ] = "function( e, api )
{
	e.classList.add( \"lzl-ad\" );

	setTimeout(
		function()
		{
			e.classList.add( e.getAttribute( \"data-anim-type\" ) );
		}
	, parseInt( e.getAttribute( \"data-anim-delay\" ), 10 ) );
}";
		}
	}
}

// #######################################################################
// #######################################################################

?>