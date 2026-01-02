<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_the7_AddGlob( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	if( isset( $ctx -> the7Glob ) )
		return;

	// wp-content/themes/dt-the7/js/above-the-fold.min.js: " mobile-"

	/*
	
	
	
	



	*/

	$itemCmnScript = $doc -> createElement( 'script' );
	if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
		$itemCmnScript -> setAttribute( 'type', 'text/javascript' );
	$itemCmnScript -> setAttribute( 'seraph-accel-crit', '1' );
	HtmlNd::SetValFromContent( $itemCmnScript, "
		var dtGlobalsLzl = {};
		(
			function( d, dtGlobals )
			{
				dtGlobals.isMobile = /(Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|windows phone)/.test( navigator.userAgent );
				dtGlobals.isAndroid = /(Android)/.test( navigator.userAgent );
				dtGlobals.isiOS = /(iPhone|iPod|iPad)/.test( navigator.userAgent );
				dtGlobals.isiPhone = /(iPhone|iPod)/.test( navigator.userAgent );
				dtGlobals.isiPad = /(iPad)/.test( navigator.userAgent );
				dtGlobals.isWindowsPhone = navigator.userAgent.match(/IEMobile/i);

				var ndHtmlCl = d.documentElement.classList;
				ndHtmlCl.add( \"mobile-\" + dtGlobals.isMobile );
				ndHtmlCl.add( dtGlobals.isiOS ? \"is-iOS\" : \"not-iOS\" );

				var ndBodyCl = d.body.classList;
				if( -1 != navigator.userAgent.indexOf( \"Safari\" ) && -1 == navigator.userAgent.indexOf( \"Chrome\" ) )
				{
					ndBodyCl.add( \"is-safari\" );
				}

				if( dtGlobals.isWindowsPhone )
				{
					ndBodyCl.add( \"ie-mobile\" );
					ndBodyCl.add( \"windows-phone\" );
				}

				if( !dtGlobals.isMobile )
				{
					ndBodyCl.add( \"no-mobile\" );
				}

				if( dtGlobals.isiPhone )
				{
					ndBodyCl.add( \"is-iphone\" );
					ndBodyCl.add( \"windows-phone\" );
				}
			}
		)( document, dtGlobalsLzl );
	" );
	$ctxProcess[ 'ndBody' ] -> insertBefore( $itemCmnScript, $ctxProcess[ 'ndBody' ] -> firstChild );

	$ctx -> the7Glob = true;
}

function _ProcessCont_Cp_the7MblHdr( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	$settTheme = null;
	foreach( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class))," the7-ver-")][not(self::node()[contains(concat(" ",normalize-space(@class)," ")," responsive-off ")])]//*[contains(concat(" ",normalize-space(@class)," ")," masthead ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( $settTheme === null )
		{
			$settTheme = array();
			if( $itemScrCfg = HtmlNd::FirstOfChildren( $xpath -> query( './/script[contains(text(),"dtLocal")][contains(text(),"dtShare")]' ) ) )
			{
				$posBegin = array();
				if( preg_match( '@\\svar\\s+dtLocal\\s+=\\s+{@', $itemScrCfg -> nodeValue, $posBegin, PREG_OFFSET_CAPTURE ) )
				{
					$posBegin = $posBegin[ 0 ][ 1 ] + strlen( $posBegin[ 0 ][ 0 ] ) - 1;
					$posEnd = Gen::JsonGetEndPos( $posBegin, $itemScrCfg -> nodeValue );
					if( $posEnd !== null )
						$settTheme[ 'dtLocal' ] = @json_decode( Gen::JsObjDecl2Json( substr( $itemScrCfg -> nodeValue, $posBegin, $posEnd - $posBegin ) ), true );
				}
			}
		}

		if( !$settTheme )
			continue;

		$desktopHeaderHeight = Gen::GetArrField( $settTheme, array( 'dtLocal', 'themeSettings', 'desktopHeader', 'height' ) );
		if( $desktopHeaderHeight && ( $itemStdHdr = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," header-bar ")]', $item ) ) ) )
			$itemStdHdr -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemStdHdr -> getAttribute( 'style' ) ), array( 'height' => ( string )$desktopHeaderHeight . 'px' ) ) ) );

		HtmlNd::AddRemoveAttrClass( $item, array( 'sticky-off' ) );

		$contMiniWidgets = '';
		{
			$a = array();
			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," near-logo-first-switch ")]', $item ) as $itemCloneFrom )
			{
				$itemCloneFrom = $itemCloneFrom -> cloneNode( true );
				HtmlNd::AddRemoveAttrClass( $itemCloneFrom, array( 'show-on-first-switch', 'js-lzl' ), array( 'near-logo-first-switch', 'near-logo-second-switch' ) );
				$a[] = $itemCloneFrom;
			}

			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," near-logo-second-switch ")]', $item ) as $itemCloneFrom )
			{
				$itemCloneFrom = $itemCloneFrom -> cloneNode( true );
				HtmlNd::AddRemoveAttrClass( $itemCloneFrom, array( 'show-on-second-switch', 'js-lzl' ), array( 'near-logo-first-switch', 'near-logo-second-switch' ) );
				$a[] = $itemCloneFrom;
			}

			foreach( $a as $itemCloneFrom )
				$contMiniWidgets .= HtmlNd::DeParse( $itemCloneFrom );
			unset( $a );
		}

		$contImgLogo = '';
		{
			if( !( $itemMixedHdr = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," mixed-header ")]' ) ) ) )
				$itemMixedHdr = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," masthead ")][not(self::node()[contains(concat(" ",normalize-space(@class)," ")," mixed-header ")])]' ) );
			if( $itemMixedHdr )
				foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," branding ")]/a|.//*[contains(concat(" ",normalize-space(@class)," ")," branding ")]/img', $itemMixedHdr ) as $itemMixedHdrSub )
				{
					$itemMixedHdrSub = $itemMixedHdrSub -> cloneNode( true );
					HtmlNd::AddRemoveAttrClass( $itemMixedHdrSub, array( 'js-lzl' ) );
					$contImgLogo .= HtmlNd::DeParse( $itemMixedHdrSub );
				}
		}

		$contMobileToggleCaption = Gen::GetArrField( $settTheme, array( 'dtLocal', 'themeSettings', 'mobileHeader', 'mobileToggleCaptionEnabled' ) ) != 'disabled' ? ( '<span class="menu-toggle-caption">' . Gen::GetArrField( $settTheme, array( 'dtLocal', 'themeSettings', 'mobileHeader', 'mobileToggleCaption' ) ) . '</span>' ) : '';

		if( !( $itemMblBar = HtmlNd::ParseAndImport( $doc, '<div class="mobile-header-bar js-lzl"><div class="mobile-navigation"><a href="#" class="dt-mobile-menu-icon js-lzl" aria-label="Mobile menu icon">' . $contMobileToggleCaption . '<div class="lines-button "><span class="menu-line"></span><span class="menu-line"></span><span class="menu-line"></span></div></a></div><div class="mobile-mini-widgets">' . $contMiniWidgets . '</div><div class="mobile-branding">' . $contImgLogo . '</div></div>' ) ) )
			continue;

		$item -> appendChild( $itemMblBar );

		$aLeft = array();
		$aRight = array();

		if( $itemLeftWidget = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," left-widgets ")]', $item ) ) )
		{
			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," in-top-bar ")]', $item ) as $itemCloneFrom )
			{
				$itemCloneFrom = $itemCloneFrom -> cloneNode( true );
				HtmlNd::AddRemoveAttrClass( $itemCloneFrom, array( 'hide-on-desktop', 'hide-on-first-switch', 'show-on-second-switch', 'js-lzl-ing' ) );
				$aLeft[] = $itemCloneFrom;
			}

			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," in-top-bar-left ")]', $item ) as $itemCloneFrom )
			{
				$itemCloneFrom = $itemCloneFrom -> cloneNode( true );
				HtmlNd::AddRemoveAttrClass( $itemCloneFrom, array( 'hide-on-desktop', 'show-on-first-switch', 'js-lzl-ing' ) );
				$aLeft[] = $itemCloneFrom;
			}
		}

		if( $itemRightWidget = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," right-widgets ")]', $item ) ) )
		{
			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," in-top-bar-right ")]', $item ) as $itemCloneFrom )
			{
				$itemCloneFrom = $itemCloneFrom -> cloneNode( true );
				HtmlNd::AddRemoveAttrClass( $itemCloneFrom, array( 'hide-on-desktop', 'show-on-first-switch', 'js-lzl-ing' ), array( 'select-type-menu', 'list-type-menu', 'select-type-menu-second-switch', 'list-type-menu-second-switch' ) );
				$aRight[] = $itemCloneFrom;
			}

		}

		foreach( $aLeft as $itemCloneFrom )
			$itemLeftWidget -> appendChild( $itemCloneFrom );
		foreach( $aRight as $itemCloneFrom )
			$itemRightWidget -> appendChild( $itemCloneFrom );
		unset( $aLeft, $aRight );

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_the7MblHdr_calcSizes(document.currentScript.parentNode);' );
			$item -> appendChild( $itemScript );
		}

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.top-bar-empty@' ] = true;

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '.masthead .mobile-header-bar:not(.js-lzl),
.masthead .mobile-header-bar.js-lzl > * > *:not(.js-lzl),
.masthead.fixed-masthead .js-lzl-ing,
.dt-mobile-header .js-lzl-ing,
.dt-mobile-header .js-lzl,
.masthead.masthead-mobile .mini-widgets > .js-lzl-ing,
body:not(.seraph-accel-js-lzl-ing) .masthead .mini-widgets > .js-lzl-ing {
	display: none !important;
}

/*@media screen and (max-width: ' . Gen::GetArrField( $settTheme, array( 'dtLocal', 'themeSettings', 'mobileHeader', 'secondSwitchPoint' ), 0 ) . 'px) {
	.masthead .mobile-header-bar .mobile-branding .js-lzl {
		display: inline-block;
	}
}*/' );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		{
			/*
			
			
			
			



			*/

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "
				function seraph_accel_cp_the7MblHdr_calcSizes( e )
				{
					function _UpdateChildren( eCont )
					{
						function isEmVisible( e )
						{
							if( !e.offsetParent )
								return( false );
							if( getComputedStyle( e ).visibility != \"visible\" )
								return( false );
							return( true );
						}

						if( !eCont )
							return( false );

						var eChFirst, eChLast;
						for( var eCh = eCont.firstElementChild; eCh; eCh = eCh.nextElementSibling )
						{
							eCh.classList.remove( \"first\" );
							eCh.classList.remove( \"last\" );
							if( !isEmVisible( eCh ) )
								continue;

							if( !eChFirst )
								eChFirst = eCh;
							eChLast = eCh;
						}

						if( !eChFirst )
							return( false );

						eChFirst.classList.add( \"first\" );
						eChLast.classList.add( \"last\" );
						return( true );
					}

					var eTopBar = e.querySelector( \".top-bar\" );
					if( eTopBar )
					{
						eTopBar.classList.remove( \"top-bar-empty\" );

						var bTopBar = false;
						eTopBar.querySelectorAll( \".mini-widgets\" ).forEach(
							function( eCont )
							{
								if( _UpdateChildren( eCont ) )
									bTopBar = true;
							}
						);

						if( !bTopBar )
							eTopBar.classList.add( \"top-bar-empty\" );
					}

					e.querySelectorAll( \".header-bar .mini-widgets\" ).forEach( _UpdateChildren );
					e.querySelectorAll( \".mobile-mini-widgets\" ).forEach( _UpdateChildren );
				}

				(
					function( d )
					{
						function OnEvt( evt )
						{
							d.querySelectorAll( \".masthead\" ).forEach( seraph_accel_cp_the7MblHdr_calcSizes );
						}

						d.addEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
						seraph_accel_lzl_bjs.add( function() { d.removeEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } ); } );
					}
				)( document );
			" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

function _ProcessCont_Cp_the7Ani( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	if( !( $ctxProcess[ 'mode' ] & 1 ) )
		return;

	if( HtmlNd::FirstOfChildren( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," the7-ver-")]//*[contains(concat(" ",normalize-space(@class)," ")," animate-element ")]' ) ) )
	{
		_ProcessCont_Cp_the7_AddGlob( $ctx, $ctxProcess, $settFrm, $doc, $xpath );

		$ctxProcess[ 'aCssCrit' ][ '@\\.mobile-false@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.mobile-true@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.start-animation@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.animation-triggered@' ] = true;

		// wp-content/themes/dt-the7/js/main.min.js: ".animation-at-the-same-time"

		/*
		
		
		
		



		*/

		$ctx -> aAniAppear[ '.skills:not(.js-lzl-start-ani)' ] = "function( e )
			{
				if( !dtGlobalsLzl.isMobile )
					return;

				e.classList.add( \"js-lzl-start-ani\" );
				seraph_accel_cp_the7Ani_skills( e );
			}";

		/*
		
		
		
		



		*/

		$ctx -> aAniAppear[ '.animation-at-the-same-time:not(.js-lzl-start-ani)' ] = "function( e )
			{
				if( dtGlobalsLzl.isMobile )
					return;

				e.classList.add( \"js-lzl-start-ani\" );

				e.querySelectorAll( \".animate-element:not(.start-animation)\" ).forEach(
					function( eChild )
					{
						eChild.classList.add( \"start-animation\" );
						eChild.classList.add( \"animation-triggered\" );
					}
				);
			}";

		/*
		
		
		
		



		*/

		$ctx -> aAniAppear[ '.animate-element:not(.start-animation)' ] = "function( e )
			{
				if( dtGlobalsLzl.isMobile )
					return;

				var eCl = e.classList;
				eCl.add( \"start-animation\" );
				eCl.add( \"animation-triggered\" );
				if( eCl.contains( \"skills\" ) )
					seraph_accel_cp_the7Ani_skills( e );
				return( 200 );
			}";

		// wp-content/themes/dt-the7/js/main.min.js: t.fn.animateSkills

		/*
		
		
		
		



		*/

		$itemCmnScript = $doc -> createElement( 'script' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$itemCmnScript -> setAttribute( 'type', 'text/javascript' );
		$itemCmnScript -> setAttribute( 'seraph-accel-crit', '1' );
		HtmlNd::SetValFromContent( $itemCmnScript, "
			function seraph_accel_cp_the7Ani_skills( e )
			{
				e.querySelectorAll( \".skill-value\" ).forEach(
					function( eChild )
					{
						eChild.style.setProperty( \"width\", eChild.getAttribute( \"data-width\" ) + \"%\" );
					}
				);
			}
		" );
		$ctxProcess[ 'ndBody' ] -> appendChild( $itemCmnScript );
	}
}

// #######################################################################
// #######################################################################

?>