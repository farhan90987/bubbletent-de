<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_brcksAni( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/body[contains(@class,"bricks")]//*[contains(@data-interactions,"animationType")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = ( array )@json_decode( $item -> getAttribute( 'data-interactions' ), true );
		foreach( $dataSett as $dataSettI )
		{
			if( Gen::GetArrField( $dataSettI, array( 'trigger' ), '' ) != 'enterView' )
				continue;

			$sAniName = Gen::GetArrField( $dataSettI, array( 'animationType' ), '' );

			$item -> setAttribute( 'data-lzl-an', $sAniName );
			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'animation-duration' => Gen::GetArrField( $dataSettI, array( 'animationDuration' ) ), 'animation-delay' => Gen::GetArrField( $dataSettI, array( 'animationDelay' ) ) ) ) ) );

			if( $ctxProcess[ 'mode' ] & 1 )
				$ctxProcess[ 'aCssCrit' ][ '@\\.brx-animate-' . $sAniName . '@' ] = true;

			$adjusted = true;
			break;
		}
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.brx-animated@' ] = true;

		{
			/*
			
			
			
			



			 */

			$ctx -> aAniAppear[ '[data-lzl-an]:not(.brx-animated)' ] = 'function( e )
				{
					setTimeout(
						function()
						{
							e.classList.add( "brx-animate-" + e.getAttribute( "data-lzl-an" ) );
							e.classList.add( "brx-animated" );
						}
					);
				}';
		}
	}
}

// #######################################################################
// #######################################################################

?>