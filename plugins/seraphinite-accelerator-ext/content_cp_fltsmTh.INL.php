<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_fltsmThBgFill( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	foreach( $xpath -> query( './/body[contains(concat(normalize-space(@class)," "),"flatsome ")]//*[contains(concat(" ",normalize-space(@class)," ")," bg ")][contains(concat(" ",normalize-space(@class)," ")," fill ")][contains(concat(" ",normalize-space(@class)," ")," bg-fill ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		HtmlNd::AddRemoveAttrClass( $item, array( 'bg-loaded' ) );
	}
}

function _ProcessCont_Cp_fltsmThAni( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/body[contains(concat(normalize-space(@class)," "),"flatsome ")]//*[@data-animate]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\[data-animate-transform@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\[data-animate-transition@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\[data-animated@' ] = true;

		/*
		
		
		
		



		 */

		$ctx -> aAniAppear[ '[data-animate]:not([data-animated])' ] = "function( e, api )
			{
				e.setAttribute( \"data-animate-transform\", \"true\" );
				e.setAttribute( \"data-animate-transition\", \"true\" );
				e.setAttribute( \"data-animated\", \"true\" );

				var style = getComputedStyle( e );
				setTimeout(
					function()
					{
						e.removeAttribute( \"data-animate\" );
					}
					, api.GetDurationTime( style.getPropertyValue( \"animation-delay\" ) + \",\" + style.getPropertyValue( \"transition-delay\" ), \"max\" ) + api.GetDurationTime( style.getPropertyValue( \"transition-duration\" ), \"max\" )
				);
			}";
	}
}

// #######################################################################
// #######################################################################

?>