<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_fsnAni( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," fusion-animated ")][@data-animationtype]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$ctxProcess[ 'aCssCrit' ][ '@\\.' . $item -> getAttribute( 'data-animationtype' ) . '@' ] = true;

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.do-animate@' ] = true;

		{
			/*
			
			
			
			



			*/

			$ctx -> aAniAppear[ '.fusion-animated[data-animationtype]:not([style*=visibility])' ] = 'function( e )
				{
					e.classList.add( "animated" );

					function _apply()
					{
						//data-animationoffset
						e.ownerDocument.body.classList.add( "do-animate" );
						e.classList.add( e.getAttribute( "data-animationtype" ) );
						e.style.setProperty( "animation-duration", e.getAttribute( "data-animationduration" ) + "s" );

						e.style.setProperty( "visibility", "visible" );
					}

					var delay = e.getAttribute( "data-animationdelay" );
					delay ? setTimeout( _apply, parseInt( delay, 10 ) ) : _apply();
				}';
		}
	}
}

// #######################################################################
// #######################################################################

?>