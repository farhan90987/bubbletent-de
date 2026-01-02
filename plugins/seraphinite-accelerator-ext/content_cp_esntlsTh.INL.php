<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_esntlsThAni( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	if( !( $ctxProcess[ 'mode' ] & 1 ) )
		return;

	if( !HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="pix-main-essentials-js"]' ) ) )
		return;

	{
		$adjusted = false;
		foreach( $xpath -> query( './/*[@data-anim-type][contains(concat(" ",normalize-space(@class)," ")," animate-in ")]' ) as $item )
		{
			$ctxProcess[ 'aCssCrit' ][ '@\\.' . $item -> getAttribute( 'data-anim-type' ) . '@' ] = true;
			$adjusted = true;
		}

		if( $adjusted )
		{
			$ctxProcess[ 'aCssCrit' ][ '@\\.animating@' ] = true;
			$ctxProcess[ 'aCssCrit' ][ '@\\.animated@' ] = true;
			$ctxProcess[ 'aCssCrit' ][ '@\\.pix-animate@' ] = true;

			/*
			
			
			
			



			*/

			$ctx -> aAniAppear[ '[data-anim-type].animate-in' ] = "function( e, api )
{
	e.classList.remove( \"animate-in\" );

	setTimeout(
		function()
		{
			e.classList.add( e.getAttribute( \"data-anim-type\" ) );
			e.classList.add( \"animating\" );
			e.classList.add( \"pix-animate\" );

			var style = getComputedStyle( e );
			setTimeout(
				function()
				{
					e.classList.remove( \"animating\" );
					e.classList.add( \"animated\" );
				}
			, api.GetDurationTime( style.getPropertyValue( \"animation-delay\" ) + \",\" + style.getPropertyValue( \"transition-delay\" ), \"max\" ) + api.GetDurationTime( style.getPropertyValue( \"animation-duration\" ) + \",\" + style.getPropertyValue( \"transition-duration\" ), \"max\" ) );
		}
	, parseInt( e.getAttribute( \"data-anim-delay\" ), 10 ) );
}";
		}
	}

	if( HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," pix-intro-img ")]' ) ) )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.animated@' ] = true;

		/*
		
		
		
		



		*/

		$ctx -> aAniAppear[ '.pix-intro-img:not(.animated)' ] = "function( e )
{
	e.classList.add( \"animated\" );
}";
	}
}

// #######################################################################
// #######################################################################

?>