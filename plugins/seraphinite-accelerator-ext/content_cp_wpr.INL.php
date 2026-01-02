<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_wprAniTxt( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	// /wp-content/plugins/royal-elementor-addons/assets/js/frontend.min.js: ".wpr-anim-text"

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wpr-anim-text ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$aClass = HtmlNd::GetAttrClass( $item );
		if( in_array( 'wpr-anim-text-type-typing', $aClass ) )
		{
		}
		else if( in_array( 'wpr-anim-text-letters', $aClass ) )
		{
		}
		else if( in_array( 'wpr-anim-text-type-clip', $aClass ) )
		{
		}
		else
		{
			if( $itemFirstChild = HtmlNd::FirstOfChildren( $xpath -> query( './/b', $item ) ) )
				HtmlNd::AddRemoveAttrClass( $itemFirstChild, array( 'wpr-anim-text-visible' ) );
		}
	}
}

function _ProcessCont_Cp_wprTabs( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wpr-tabs ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-options' ), true );
		$idActiveTab = Gen::GetArrField( $dataSett, array( 'activeTab' ), 1 );

		if( $itemFirstTabTitle = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wpr-tabs-wrap ")]//*[contains(concat(" ",normalize-space(@class)," ")," wpr-tab ")][@data-tab="' . $idActiveTab . '"]', $item ) ) )
		{
			HtmlNd::AddRemoveAttrClass( $itemFirstTabTitle, array( 'wpr-tab-active' ) );
		}

		if( $itemFirstTabBody = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wpr-tabs-content-wrap ")]//*[contains(concat(" ",normalize-space(@class)," ")," wpr-tab-content ")][@data-tab="' . $idActiveTab . '"]', $item ) ) )
		{
			HtmlNd::AddRemoveAttrClass( $itemFirstTabBody, array( 'wpr-tab-content-active', 'wpr-animation-enter' ) );
		}

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.wpr-tab-active@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.wpr-tab-content-active@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.wpr-animation-enter@' ] = true;
	}
}

// #######################################################################
// #######################################################################

?>