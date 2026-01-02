<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_ultRspnsv( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	// wp-content/plugins/Ultimate_VC_Addons/assets/min-js/ultimate.min.js
	$aView = array( 'large_screen' => 'min-width:1824px', 'tablet' => 'max-width:1199px', 'tablet_portrait' => 'max-width:991px', 'mobile_landscape' => 'max-width:767px', 'mobile' => 'max-width:479px' );
	$aSpacerToViewId = array( 'mobile' => 'mobile', 'mobile-landscape' => 'mobile_landscape', 'tab' => 'tablet', 'tab-portrait' => 'tablet_portrait' );

	// Fixing order of keys
	$aCss = array( '' => array() );
	foreach( $aView as $viewId => $spec )
		$aCss[ $viewId ] = array();

	$adjusted = false;

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ult-responsive ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$data = $item -> getAttribute( 'data-responsive-json-new' );
		$cssSelTarget = $item -> getAttribute( 'data-ultimate-target' );
		if( !$data || !$cssSelTarget )
			continue;

		$adjusted = true;
		HtmlNd::AddRemoveAttrClass( $item, array(), array( 'ult-responsive' ) );
		$item -> removeAttribute( 'data-responsive-json-new' );
		$item -> removeAttribute( 'data-ultimate-target' );

		if( $ctxProcess[ 'mode' ] & 1 )
		{
			foreach( ( array )@json_decode( $data, true ) as $ruleName => $ruleData )
				foreach( Gen::ParseProps( $ruleData, ';', ':' ) as $viewId => $ruleVal )
				{
					if( !isset( $aView[ $viewId ] ) )
						$viewId = '';
					$aCss[ $viewId ][ $cssSelTarget ][ $ruleName ] = $ruleVal;
				}
		}
	}

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ult-spacer ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$adjusted = true;

		$cssSelTarget = '.spacer-' . ( string )$item -> getAttribute( 'data-id' );
		HtmlNd::AddRemoveAttrClass( $item, array(), array( 'ult-spacer' ) );

		$aAttrDel = array();
		if( $item -> attributes )
			foreach( $item -> attributes as $attr )
			{
				if( Gen::StrStartsWith( $attr -> nodeName, 'data-height' ) )
				{
					if( $ctxProcess[ 'mode' ] & 1 )
					{
						$viewId = ltrim( substr( $attr -> nodeName, 11 ), '-' );
						$viewId = isset( $aSpacerToViewId[ $viewId ] ) ? $aSpacerToViewId[ $viewId ] : '';
						$aCss[ $viewId ][ $cssSelTarget ][ 'height' ] = ( string )$attr -> nodeValue . 'px';
					}

					$aAttrDel[] = $attr -> nodeName;
				}
			}

		foreach( $aAttrDel as $attrDel )
			$item -> removeAttribute( $attrDel );
	}

	if( !( $ctxProcess[ 'mode' ] & 1 ) )
		return;

	if( !$adjusted )
		return;

	$cont = '';
	foreach( $aCss as $viewId => $aCssSel )
	{
		if( isset( $aView[ $viewId ] ) )
			$cont .= '@media (' . $aView[ $viewId ] . ') {';
		$cont .= Ui::GetStyleSels( $aCssSel );
		if( isset( $aView[ $viewId ] ) )
			$cont .= '}';
	}

	$itemCmnStyle = $doc -> createElement( 'style' );
	if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
		$itemCmnStyle -> setAttribute( 'type', 'text/css' );
	HtmlNd::SetValFromContent( $itemCmnStyle, $cont );
	$ctxProcess[ 'ndHead' ] -> appendChild( $itemCmnStyle );
}

function _ProcessCont_Cp_ultVcHd( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	// wp-content/plugins/Ultimate_VC_Addons/assets/min-js/ultimate.min.js: ".uvc-heading"

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," uvc-heading ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$ctxProcess[ 'isRtl' ];

		$spacer = $item -> getAttribute( 'data-hspacer' );
		$line_width = $item -> getAttribute( 'data-hline_width' );
		$icon_type = $item -> getAttribute( 'data-hicon_type' );
		$align = $item -> getAttribute( 'data-halign' );

		if( $spacer == 'line_with_icon' )
		{
			// MBI!!!!!!!!!!!
		}
		else if( $spacer == 'line_only' )
		{
			if( $itemSub = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," uvc-heading-spacer ")]//*[contains(concat(" ",normalize-space(@class)," ")," uvc-headings-line ")]', $item ) ) )
			{
				if( $align == 'left' || $align == 'right' )
					$itemSub -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSub -> getAttribute( 'style' ) ), array( 'float' => $align ) ) ) );
				else
					$itemSub -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSub -> getAttribute( 'style' ) ), array( 'margin' => '0 auto' ) ) ) );
			}
		}
	}
}

function _ProcessCont_Cp_ultAni( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ult-animation ")][contains(concat(" ",normalize-space(@class)," ")," ult-animate-viewport ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$ctxProcess[ 'aCssCrit' ][ '@\\.' . $item -> getAttribute( 'data-animate' ) . '@' ] = true;

		//if( $item -> hasAttribute( 'data-animation-delay' ) )
		//    $item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'transition-delay' => $item -> getAttribute( 'data-animation-delay' ) . 's' ) ) ) );

		for( $itemChild = HtmlNd::GetFirstElement( $item ); $itemChild; $itemChild = HtmlNd::GetNextElementSibling( $itemChild ) )
		{
			$aStyle = Ui::ParseStyleAttr( $itemChild -> getAttribute( 'style' ) );

			if( $item -> hasAttribute( 'data-animation-delay' ) )
				$aStyle[ 'animation-delay' ] = $item -> getAttribute( 'data-animation-delay' ) . 's';
			if( $item -> hasAttribute( 'data-animation-duration' ) )
				$aStyle[ 'animation-duration' ] = $item -> getAttribute( 'data-animation-duration' ) . 's';
			if( $item -> hasAttribute( 'data-animation-iteration' ) )
				$aStyle[ 'animation-iteration-count' ] = $item -> getAttribute( 'data-animation-iteration' );

			$itemChild -> setAttribute( 'style', Ui::GetStyleAttr( $aStyle ) );
		}

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		{
			$ctxProcess[ 'aCssCrit' ][ '@\\.animated@' ] = true;
			$ctxProcess[ 'aCssCrit' ][ '@\\.char@' ] = true;
		}

		/*
		
		
		
		



		*/

		$ctx -> aAniAppear[ '.ult-animation.ult-animate-viewport>*:not(.animated)' ] = "function( e )
{
	var eCont = e.parentNode;

	if( eCont.hasAttribute( \"data-animation-delay\" ) )	// Must be at rinetime due to original script cheks exatclt for 'style=\"opacity:0;\"'
		eCont.style.setProperty( \"transition-delay\", eCont.getAttribute( \"data-animation-delay\" ) + \"s\" );

	var sAniName = eCont.getAttribute( \"data-animate\" );
	if( sAniName.indexOf( \" \" ) === -1 )
		e.classList.add( sAniName );
	e.classList.add( \"animated\" );

	eCont.style.setProperty( \"opacity\", \"1\" );
}";
	}
}

// #######################################################################
// #######################################################################

?>