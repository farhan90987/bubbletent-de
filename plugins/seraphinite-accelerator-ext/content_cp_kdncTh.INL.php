<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_kdncThAni( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," theme-kadence ")]//*[@data-aos]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;
		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.aos-animate@' ] = true;

		{
			/*
			
			
			
			



			 */

			$ctx -> aAniAppear[ '[data-aos]:not(.aos-animate)' ] = 'function( e )
				{
					e.classList.add( "aos-animate" );
				}';
		}
	}
}

// #######################################################################
// #######################################################################

?>