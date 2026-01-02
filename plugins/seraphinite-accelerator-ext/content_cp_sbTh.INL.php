<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_sbThAni( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	// /wp-content/themes/phlox-pro/js/scripts.js

	$adjusted = false;
	foreach( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," sandbox-theme")]//*[@data-cue]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$ctxProcess[ 'aCssCrit' ][ '@' . $item -> getAttribute( 'data-cue' ) . '@' ] = true;

		$adjusted = true;
	}

	if( $adjusted && ( $ctxProcess[ 'mode' ] & 1 ) )
	{
		{
			/*
			
			
			
			



			*/

			$ctx -> aAniAppear[ '[data-cue]:not([data-show=true])' ] = 'function( e )
				{
					e.style.setProperty( "animation-name", e.getAttribute( "data-cue" ) );
					e.style.setProperty( "animation-duration", e.getAttribute( "data-duration" ) + "ms" );
					e.style.setProperty( "animation-delay", e.getAttribute( "data-delay" ) + "ms" );
					e.style.setProperty( "animation-timing-function", "ease" );
					e.style.setProperty( "animation-direction", "normal" );
					e.style.setProperty( "animation-fill-mode", "both" );

					e.setAttribute( "data-show", "true" );
			}';
		}
	}
}

// #######################################################################
// #######################################################################

?>