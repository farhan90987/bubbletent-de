<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_prstPlr( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$aCmnStyle = array();
	$aCfgCmn = null;

	// Fixation for loading order
	$aCmnStyle[ 'presto_playlist_item' ] = false;
	$aCmnStyle[ 'presto_playlist_overlay' ] = false;
	$aCmnStyle[ 'presto_playlist_ui' ] = false;

	$adjusted = false;
	foreach( $xpath -> query( './/presto-player' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( $aCfgCmn === null )
			$aCfgCmn = _PrstPlr_GetPrmsCmnFromScr( $ctxProcess, HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="presto-components-js-extra"]' ) ) );

		$aCfg = _PrstPlr_GetPrmsFromItem( $item, HtmlNd::FirstOfChildren( $xpath -> query( './/script[contains(text(),".querySelector")]', $item -> parentNode -> parentNode ) ), 'player', array( 'preset' => 'preset', 'blockAttributes' => 'block-attributes', 'provider' => 'provider', 'skin' => 'skin' ) );

		if( $contPlr = _PrstPlr_Generate( $ctxProcess, $doc, $item -> getAttribute( 'src' ), $aCmnStyle, $aCfgCmn, $aCfg ) )
		{
			HtmlNd::AddRemoveAttrClass( $item, array( 'js-lzl' ) );
			HtmlNd::InsertAfter( $item -> parentNode, HtmlNd::ParseAndImport( $doc, Ui::Tag( 'div', _PrstPlr_Rpl( $contPlr ), array( 'class' => 'presto-js-lzl-ing' ) ) ), $item );
		}

		$adjusted = true;
	}

	$adjustedPl = false;
	foreach( $xpath -> query( './/presto-playlist' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( $aCfgCmn === null )
			$aCfgCmn = _PrstPlr_GetPrmsCmnFromScr( $ctxProcess, HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="presto-components-js-extra"]' ) ) );

		$aCfgPl = _PrstPlr_GetPrmsFromItem( $item, HtmlNd::FirstOfChildren( $xpath -> query( './/script[contains(text(),".querySelector")]', $item -> parentNode -> parentNode ) ), 'playlist', array( 'listTextSingular' => 'list-text-singular', 'listTextPlural' => 'list-text-plural', 'items' => 'items' ) );

		$aCfgItem = Gen::GetArrField( $aCfgPl, array( 'items' ), array() );
		if( !$aCfgItem )
			continue;

		HtmlNd::AddRemoveAttrClass( $item, array( 'js-lzl' ) );

		$iActive = 0;

		{
			$aCfg = Gen::GetArrField( $aCfgItem, array( $iActive, 'config' ), array() );
			if( !( $contPlr = _PrstPlr_Generate( $ctxProcess, $doc, Gen::GetArrField( $aCfg, array( 'src' ), '' ), $aCmnStyle, $aCfgCmn, $aCfg, array( 'slot' => 'preview' ) ) ) )
				continue;
		}

		$aItems = array();
		foreach( $aCfgItem as $i => $cfgItem )
			$aItems[] = '<presto-playlist-item slot="list" class="hydrated' . ( $i == $iActive ? ' active' : '' ) . '"><div class="playlist__item' . ( $i == $iActive ? ' playlist__item-is--active' : '' ) . '"><div class="playlist__title-wrap"><div class="playlist__play-icon"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" class="presto-icon-play"><path d="M5 4l10 6-10 6v-12z"></path></svg></div><span slot="item-title"><span>' . htmlentities( Gen::GetArrField( $cfgItem, array( 'title' ), '' ) ) . '</span></span></div><div class="playlist__time"><span slot="item-duration"><span>' . htmlentities( Gen::GetArrField( $cfgItem, array( 'duration' ), '' ) ) . '</span></span></div></div></presto-playlist-item>';
		$contPlr = '<presto-playlist class="hydrated"><presto-playlist-ui class="hydrated"><div class="playlist__base" part="base"><div class="playlist"><div class="playlist__preview">' . $contPlr . '</div><div class="playlist__info"><div class="playlist__heading"><div class="playlist__heading-title"><div>' . $item -> getAttribute( 'heading' ) . '</div></div><div class="playlist__heading-count"><div slot="count">' . ( string )count( $aItems ) . ' ' . htmlentities( Gen::GetArrField( $aCfgPl, array( ( count( $aItems ) % 10 ) != 1 ? 'listTextPlural' : 'listTextSingular' ), '' ) ) . '</div></div></div><div class="playlist__info--inner"><div class="playlist__list">' . implode( '', $aItems ) . '</div></div></div></div></div></presto-playlist-ui></presto-playlist>';

		HtmlNd::InsertAfter( $item -> parentNode, HtmlNd::ParseAndImport( $doc, Ui::Tag( 'div', _PrstPlr_Rpl( $contPlr ), array( 'class' => 'presto-js-lzl-ing' ) ) ), $item );

		$aCmnStyle[ 'presto_playlist_item' ] = true;
		$aCmnStyle[ 'presto_playlist_overlay' ] = true;
		$aCmnStyle[ 'presto_playlist_ui' ] = true;

		$adjustedPl = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && ( $adjusted || $adjustedPl ) )
	{
		if( $adjusted )
			$ctxProcess[ 'aCssCrit' ][ '@presto-player\\.ready@' ] = true;
		if( $adjustedPl )
			$ctxProcess[ 'aCssCrit' ][ '@presto-playlist\\.lzl-ready@' ] = true;

		{
			$contCmnStyle = '';

			if( $adjusted )
				$contCmnStyle .= "presto-player.js-lzl:not(.ready) {\r\n\tposition: absolute !important;\r\n}\r\n\r\n/*presto-player.js-lzl:not(.ready),*/\r\npresto-player.ready + .presto-js-lzl-ing /*,\r\npresto-player > .presto-iframe-fallback-container*/ {\r\n\tdisplay: none !important;\r\n}\r\n\r\n/*presto-player-js-lzl-ing .presto-player__wrapper.js-lzl-ing {\r\n\tvisibility: visible;\r\n}*/";

			if( $adjustedPl )
				$contCmnStyle .= "presto-playlist.js-lzl:not(.lzl-ready) {\r\n\tposition: absolute !important;\r\n}\r\n\r\npresto-playlist.lzl-ready + .presto-js-lzl-ing {\r\n\tdisplay: none !important;\r\n}\r\n\r\n/*presto-player-js-lzl-ing .presto-player__wrapper.js-lzl-ing {\r\n\tvisibility: visible;\r\n}*/";

			$contCmnStyle .= _Cp_CloneStyles( $ctxProcess, $xpath, '\\seraph_accel\\_PrstPlr_Rpl' );

			foreach( $aCmnStyle as $id => $bLoad )
			{
				if( !$bLoad )
					continue;

				$contCmnStyle .= _Cp_CloneStylesEx( _PrstPlr_Rpl( _PrstPlr_GetPrmsCmnCompCss( $aCfgCmn, $id ), true, true ),
					function( $sel, $bReplace )
					{
						if( !$bReplace )
							return( true );
						return( '.presto-js-lzl-ing ' . $sel );
					}
				);
			}

			$itemCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemCmnStyle, $contCmnStyle );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemCmnStyle );
		}

		if( $adjustedPl )
		{
			/*
			
			
			
			



			*/

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "(
	function( d )
	{
		function OnEvt( evt )
		{
			d.querySelectorAll( \"presto-playlist.js-lzl:not(.lzl-ready)\" ).forEach(
				function( e )
				{
					e.addEventListener( \"playerReady\", function() { var e = this; setTimeout( function() { e.classList.add( \"lzl-ready\" ); }, 500 /* compensating player's transition */ ); }, { capture: true, passive: true } );
				}
			);
		}

		seraph_accel_lzl_bjs.add(
			function()
			{
				OnEvt();
			}
		, 200 );
	}
)( document );
" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

function _PrstPlr_Generate( &$ctxProcess, $doc, $src, &$aCmnStyle, $aCfgCmn, $aCfg, $attrs = array() )
{
	$contCtl = '';
	switch( Gen::GetArrField( $aCfg, array( 'provider' ) ) )
	{
	case 'audio':
	{
		//HtmlNd::AddRemoveAttrClass( $item, array( 'hydrated' ) );

		{
			$seekPos = '0.00%';
			$seekTime = '00:00';

			$contCtl .= '<div part="wrapper" class="presto-player__wrapper fitvidsignore presto-video-id-' . Gen::GetArrField( $aCfg, array( 'blockAttributes', 'id' ), '' ) . ' skin-' . Gen::GetArrField( $aCfg, array( 'skin' ), '' ) . ' js-lzl-ing"><div>';
			//$contCtl .= '<slot name="player-start"></slot><slot name="player-before-video"></slot>';
			$contCtl .= '<presto-audio class="hydrated"><div class="presto-audio__wrapper breakpoint-large' . ( Gen::GetArrField( $aCfg, array( 'preset', 'play-large' ) ) ? ' has-play-large' : '' ) . '">';

			if( Gen::GetArrField( $aCfg, array( 'preset', 'play-large' ) ) )
				$contCtl .= '<div class="presto-audio__poster-wrapper"><div class="presto-audio__large-play-wrapper"><div class="presto-audio__large-play-button"><svg class="presto-audio__icon-play" width="16" height="18" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.5588 9.00005L0.117662 17.915L0.117662 0.0850823L15.5588 9.00005Z"></path></svg></div></div></div>';

			$contCtl .= '<div class="presto-audio__controls-wrapper">';
			{
				if( 1 )
					$contCtl .= '<div class="presto-audio__title">' . Gen::GetArrField( $aCfg, array( 'blockAttributes', 'title' ), '' ) . '</div>';

				$contCtl .= '<div tabindex="0" class="plyr plyr--full-ui plyr--audio plyr--html5 plyr--paused">';
				{
					_PrstPlr_AddPlyrControls( $contCtl, $aCfgCmn, $aCfg, $seekPos, $seekTime );
				}
				$contCtl .= '</div>';
			}
			$contCtl .= '</div>';

			$contCtl .= '</div></presto-audio>';
			//$contCtl .= '<slot name="player-after-video"></slot>';
			//$contCtl .= '<presto-email-overlay class="hydrated"><presto-email-overlay-controller class="hydrated" current-time="2.861176" duration="516.257938"></presto-email-overlay-controller></presto-email-overlay>';
			//$contCtl .= '<presto-' . Gen::GetArrField( $aCfg, array( 'skin' ), '' ) . '-skin class="hydrated"></presto-' . Gen::GetArrField( $aCfg, array( 'skin' ), '' ) . '-skin>';
			//$contCtl .= '<slot name="player-end"></slot>';
			$contCtl .= '</div></div>';
		}
	} break;

	case 'vimeo':
	case 'youtube':
	case 'bunny':
	{
		$urlVideoThumb = GetVideoThumbUrlFromUrl( $ctxProcess, $src );
		$seekPos = '0.00%';
		$seekTime = '00:00';

		{
			$contCtl .= '<div part="wrapper" class="presto-player__wrapper fitvidsignore presto-video-id-' . Gen::GetArrField( $aCfg, array( 'blockAttributes', 'id' ), '' ) . ' skin-' . Gen::GetArrField( $aCfg, array( 'skin' ), '' ) . ( Gen::GetArrField( $aCfg, array( 'blockAttributes', 'mutedPreview', 'enabled' ), false ) ? ' is-muted-overlay presto-player--playing' : '' ) . ' js-lzl-ing"><div>';
			$contCtl .= '<presto-' . Gen::GetArrField( $aCfg, array( 'provider' ), '' ) . ' class="hydrated">';
			{
				$contCtl .= '<div tabindex="0" class="plyr plyr--full-ui plyr--video plyr--' . Gen::GetArrField( $aCfg, array( 'provider' ), '' ) . ' plyr--fullscreen-enabled' . ( Gen::GetArrField( $aCfg, array( 'blockAttributes', 'mutedPreview', 'enabled' ), false ) ? ' plyr--playing' : ' plyr--paused plyr--stopped' ) . '">';
				{
					_PrstPlr_AddPlyrControls( $contCtl, $aCfgCmn, $aCfg, $seekPos, $seekTime );

					$contCtl .= '<div class="plyr__video-wrapper plyr__video-embed" style="aspect-ratio: 16 / 9;background: center / cover no-repeat url(' . $urlVideoThumb . ');"><div class="plyr__poster"></div></div>';

					if( Gen::GetArrField( $aCfg, array( 'preset', 'play-large' ) ) )
						$contCtl .= '<button type="button" class="plyr__control plyr__control--overlaid" data-plyr="play" aria-pressed="false" aria-label="Play"><svg aria-hidden="true" focusable="false"><use xlink:href="wp-content/plugins/presto-player/img/' . Gen::GetArrField( $aCfg, array( 'skin' ), '' ) . '.svg#plyr-play"></use></svg><span class="plyr__sr-only">Play</span></button>';
				}
				$contCtl .= '</div>';
			}
			$contCtl .= '</presto-' . Gen::GetArrField( $aCfg, array( 'provider' ), '' ) . '>';

			if( Gen::GetArrField( $aCfg, array( 'blockAttributes', 'mutedPreview', 'enabled' ), false ) )
				$contCtl .= '<presto-muted-overlay class="hydrated"><div class="presto-player__muted-overlay"><div class="plyr__control plyr__control--overlaid" data-plyr="play" aria-label="Play" part="muted-overlay-play"><svg id="plyr-play" viewBox="0 0 18 18"><path d="M15.562 8.1L3.87.225c-.818-.562-1.87 0-1.87.9v15.75c0 .9 1.052 1.462 1.87.9L15.563 9.9c.584-.45.584-1.35 0-1.8z"></path></svg><span class="plyr__sr-only">Play</span></div></div></presto-muted-overlay>';

			$contCtl .= '</div></div>';
		}
	} break;
	}

	if( !$contCtl )
		return( null );

	$aCmnStyle[ 'presto_' . Gen::GetArrField( $aCfg, array( 'provider' ), '' ) . '' ] = true;
	$aCmnStyle[ 'presto_' . Gen::GetArrField( $aCfg, array( 'skin' ), '' ) . '_skin' ] = true;
	$aCmnStyle[ 'presto_player' ] = true;

	return( Ui::Tag( 'presto-player', $contCtl, array_merge( $attrs, array( 'style' => Gen::GetArrField( $aCfg, array( 'styles' ), '' ), 'class' => Gen::GetArrField( $aCfg, array( 'playerClass' ), '' ) . ' hydrated js-lzl-ing', 'provider' => Gen::GetArrField( $aCfg, array( 'provider' ), '' ), 'host' => '' ) ) ) );
}

function _PrstPlr_AddPlyrControls( &$contCtl, $aCfgCmn, $aCfg, $seekPos, $seekTime )
{
	$skinSvgFile = $aCfgCmn[ '_skinSvgFilePath' ] . Gen::GetArrField( $aCfg, array( 'skin' ), '' ) . '.svg';

	$contCtl .= '<div class="plyr__controls">';

	if( Gen::GetArrField( $aCfg, array( 'preset', 'rewind' ) ) )
		$contCtl .= '<button class="plyr__controls__item plyr__control" type="button" data-plyr="rewind" aria-pressed="false"><svg aria-hidden="true" focusable="false"><use xlink:href="' . $skinSvgFile . '#plyr-rewind"></use></svg></button>';
	if( Gen::GetArrField( $aCfg, array( 'preset', 'play' ) ) )
		$contCtl .= '<button class="plyr__controls__item plyr__control" type="button" data-plyr="play" aria-pressed="false"><svg class="icon--pressed" aria-hidden="true" focusable="false"><use xlink:href="' . $skinSvgFile . '#plyr-pause"></use></svg><svg class="icon--not-pressed" aria-hidden="true" focusable="false"><use xlink:href="' . $skinSvgFile . '#plyr-play"></use></svg></button>';
	if( Gen::GetArrField( $aCfg, array( 'preset', 'fast-forward' ) ) )
		$contCtl .= '<button class="plyr__controls__item plyr__control" type="button" data-plyr="fast-forward" aria-pressed="false"><svg aria-hidden="true" focusable="false"><use xlink:href="' . $skinSvgFile . '#plyr-fast-forward"></use></svg></button>';
	if( Gen::GetArrField( $aCfg, array( 'preset', 'progress' ) ) )
		$contCtl .= '<div class="plyr__controls__item plyr__progress__container"><div class="plyr__progress"><input data-plyr="seek" type="range" min="0" max="100" step="0.01" value="0" autocomplete="off" role="slider" aria-valuemin="0" style="--value: ' . $seekPos . ';"><progress class="plyr__progress__buffer" min="0" max="100" role="progressbar" aria-hidden="true"></progress></div></div>';
	if( Gen::GetArrField( $aCfg, array( 'preset', 'current-time' ) ) )
		$contCtl .= '<div class="plyr__controls__item plyr__time--current plyr__time">' . $seekTime . '</div>';

	if( Gen::GetArrField( $aCfg, array( 'preset', 'volume' ) ) || Gen::GetArrField( $aCfg, array( 'preset', 'mute' ) ) )
	{
		$contCtl .= '<div class="plyr__controls__item plyr__volume">';
		if( Gen::GetArrField( $aCfg, array( 'preset', 'mute' ) ) )
			$contCtl .= '<button type="button" class="plyr__control" data-plyr="mute" aria-pressed="false"><svg class="icon--pressed" aria-hidden="true" focusable="false"><use xlink:href="' . $skinSvgFile . '#plyr-muted"></use></svg><svg class="icon--not-pressed" aria-hidden="true" focusable="false"><use xlink:href="' . $skinSvgFile . '#plyr-volume"></use></svg></button>';
		if( Gen::GetArrField( $aCfg, array( 'preset', 'volume' ) ) )
			$contCtl .= '<input data-plyr="volume" type="range" min="0" max="1" step="0.05" value="1" autocomplete="off" role="slider" aria-valuemin="0" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100.0%" style="--value: 100%;">';
		$contCtl .= '</div>';
	}

	if( Gen::GetArrField( $aCfg, array( 'preset', 'captions' ) ) )
		$contCtl .= '<button class="plyr__controls__item plyr__control" type="button" data-plyr="captions" aria-pressed="false"><svg class="icon--pressed" aria-hidden="true" focusable="false"><use xlink:href="wp-content/plugins/presto-player/img/' . Gen::GetArrField( $aCfg, array( 'skin' ), '' ) . '.svg#plyr-captions-on"></use></svg><svg class="icon--not-pressed" aria-hidden="true" focusable="false"><use xlink:href="wp-content/plugins/presto-player/img/' . Gen::GetArrField( $aCfg, array( 'skin' ), '' ) . '.svg#plyr-captions-off"></use></svg></button>';
	if( Gen::GetArrField( $aCfg, array( 'preset', 'pip' ) ) )
		$contCtl .= '<div class="plyr__controls__item plyr__menu"><button aria-haspopup="true" aria-expanded="false" type="button" class="plyr__control" data-plyr="settings" aria-pressed="false"><svg aria-hidden="true" focusable="false"><use xlink:href="wp-content/plugins/presto-player/img/' . Gen::GetArrField( $aCfg, array( 'skin' ), '' ) . '.svg#plyr-settings"></use></svg></button></div>';
	if( Gen::GetArrField( $aCfg, array( 'preset', 'fullscreen' ) ) )
		$contCtl .= '<button class="plyr__controls__item plyr__control" type="button" data-plyr="fullscreen" aria-pressed="false"><svg class="icon--pressed" aria-hidden="true" focusable="false"><use xlink:href="wp-content/plugins/presto-player/img/' . Gen::GetArrField( $aCfg, array( 'skin' ), '' ) . '.svg#plyr-exit-fullscreen"></use></svg><svg class="icon--not-pressed" aria-hidden="true" focusable="false"><use xlink:href="wp-content/plugins/presto-player/img/' . Gen::GetArrField( $aCfg, array( 'skin' ), '' ) . '.svg#plyr-enter-fullscreen"></use></svg></button>';

	$contCtl .= '</div>';
}

function _PrstPlr_Rpl( $sel, $bReplace = true, $bFull = false )
{
	static $g_aExpr = array(
		//'@(^|[^\\w/\\.\\-\\_])(presto-player)($|[^\\w/\\-\\_])@S',
		//'@(^|[^\\w/\\.\\-\\_])(presto-playlist)($|[^\\w/\\-\\_])@S',
		'@(^|</|[^\\w/\\.\\-\\_])(presto-(?:player|playlist|playlist-ui|playlist-item|audio|vimeo|youtube|bunny))($|[^\\w/\\-\\_])@S',
	);

	if( !$bReplace )
	{
		foreach( $g_aExpr as $e )
			if( preg_match( $e, $sel ) )
				return( true );

		return( false );
	}

	foreach( $g_aExpr as $e )
		$sel = preg_replace( $e, '${1}${2}-js-lzl-ing${3}', $sel );

	if( $bFull )
	{
		$sel = preg_replace_callback( '@::slotted\\(([^()]*)\\)@',
			function( $m )
			{
				$m[ 1 ] = trim( $m[ 1 ] );
				if( Gen::StrStartsWith( $m[ 1 ], '*' ) )
					$m[ 1 ] = ' ' . $m[ 1 ];
				return( '[slot]' . $m[ 1 ] );
			}
		, $sel );

		$sel = preg_replace_callback( '@:host\\(([^()]*)\\)@',
			function( $m )
			{
				$m[ 1 ] = trim( $m[ 1 ] );
				$m[ 1 ] = ' ' . $m[ 1 ];
				return( '[host]' . $m[ 1 ] );
			}
		, $sel );

		$sel = preg_replace( '@(^|[^:]):host($|[^\\w\\-\\(])@', '${1}[host]${2}', $sel );
	}

	return( $sel );
}

function _PrstPlr_GetPrmsCmnFromScr( &$ctxProcess, $itemInitScr )
{
	if( !$itemInitScr )
		return( array() );

	$prms = array( '_aJs' => array() );

	foreach( array( 'prestoComponents', 'prestoPlayer' ) as $id )
	{
		if( !preg_match( '@var\\s*' . preg_quote( $id ) . '\\s*=\\s*@', $itemInitScr -> nodeValue, $posStart, PREG_OFFSET_CAPTURE ) )
			continue;

		$posStart = $posStart[ 0 ][ 1 ] + strlen( $posStart[ 0 ][ 0 ] );
		$pos = Gen::JsonGetEndPos( $posStart, $itemInitScr -> nodeValue );
		if( $pos === null )
			continue;

		$prms[ $id ] = @json_decode( Gen::JsObjDecl2Json( substr( $itemInitScr -> nodeValue, $posStart, $pos - $posStart ) ), true );
	}

	// Read all JS
	$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, Gen::GetArrField( $prms, array( 'prestoComponents', 'url' ) ) );
	if( ($srcInfo[ 'filePath' ]??null) )
	{
		foreach( @glob( Gen::GetFileDir( Gen::GetNormalizedPath( ($srcInfo[ 'filePath' ]??null) ) ) . '/*.js' ) as $file )
		{
			if( !preg_match( '@^p-[\\w\\-]+(?:\\.entry|)$@', Gen::GetFileName( $file, true ) ) )
				continue;

			$file = str_replace( '\\', '/', $file );
			$prms[ '_aJs' ][ $file ] = ( string )@file_get_contents( $file );
		}
	}

	$prms[ '_skinSvgFilePath' ] = Gen::GetFileDir( Gen::GetArrField( $prms, array( 'prestoComponents', 'url' ) ), true, 4 ) . 'img/';

	return( $prms );
}

function _PrstPlr_GetPrmsCmnCompCss( $prms, $id )
{
	$aFile = array_keys( $prms[ '_aJs' ] );

	for( ;; )
	{
		$bRepeat = false;
		foreach( $aFile as $file )
		{
			$data = ($prms[ '_aJs' ][ $file ]??null);
			if( $data === null )
				continue;

			if( !preg_match( '@(?:^|\\W)export\\s*{(?:\\s*|[^}]+\\,\\s*)(\\w+)\\s+as\\s+' . preg_quote( $id ) . '\\s*(?:\\,[^}]+|)}(?:\\s*from\\s*[\'"]([\\w\\.\\-\\/]+)[\'"]|)@', $data, $m ) )
				continue;

			if( strlen( ($m[ 2 ]??'') ) )
			{
				$id = $m[ 1 ];
				$aFile = array( Gen::GetNormalizedPath( Gen::GetFileDir( $file, true ) . $m[ 2 ] ) );
				$bRepeat = true;
				break;
			}

			$key = $m[ 1 ];

			$m = array();
			$dir = null;
			foreach( array(
				array( '@\\W\\w+\\s*=\\s*(([\'"]).*(?2))\\s*,\\s*\\w+\\s*=\\s*\\w+\\s*,\\s*' . preg_quote( $key ) . '\\s*=\\s*class\\s*{@', -1 ),	 // 2.0.8
				array( '@\\W\\w+\\s*=\\s*(([\'"]).*(?2))\\s*,\\s*' . preg_quote( $key ) . '\\s*=\\s*class\\s*{@', -1 ),	 // 3.0.1
				array( '@\\W' . preg_quote( $key ) . '\\s*\\.\\s*style\\s*=\\s*(([\'"]).*(?2))\\s*;@', 1 ),	 // 2.0.5
				//array( '@\\W\\w+\\s*=\\s*(([\'"]).*(?2))\\s*;\\s*const\\s+' . preg_quote( $key ) . '\\s*=\\s*class\\s*{@', -1 ), // 2.0.5
			) as $aF )
			{
				if( preg_match( $aF[ 0 ], $data, $m ) )
				{
					$dir = $aF[ 1 ];
					break;
				}
			}
			if( !$m )
				continue;

			if( !strlen( $m[ 1 ] ) )
				return( '' );

			if( $dir > 0 )
			{
				$posEnd = Gen::JsonGetEndPos( 0, $m[ 1 ] );
				if( $posEnd === null )
					return( '' );
				$m[ 1 ] = substr( $m[ 1 ], 0, $posEnd );
			}
			else
			{
				$posStart = Gen::JsonGetStartPos( strlen( $m[ 1 ] ) - 1, $m[ 1 ] );
				if( $posStart === null )
					return( '' );
				$m[ 1 ] = substr( $m[ 1 ], $posStart );
			}

			/**/
			$m[ 1 ] = @json_decode( Gen::JsObjDecl2Json( $m[ 1 ] ) );
			if( !is_string( $m[ 1 ] ) )
			    return( '' );

			$m[ 1 ] = trim( $m[ 1 ] );
			/*/
			$m[ 1 ] = trim( substr( $m[ 1 ], 1, $posEnd - 2 ) );
			$m[ 1 ] = str_replace( '\\' . $m[ 2 ], $m[ 2 ], $m[ 1 ] );
			/**/

			_CssCutCharset( $m[ 1 ] );
			return( $m[ 1 ] );
		}

		if( !$bRepeat )
			break;
	}

	return( '' );
}

function _PrstPlr_GetPrmsFromItem( $item, $itemInitScr, $root, $aScope )
{
	$prms = array();

	foreach( $aScope as $id => $idAttr )
	{
		if( !$itemInitScr )
		{
			$v = ( string )$item -> getAttribute( $idAttr );
			if( !strlen( $v ) )
				continue;

			$prms[ $id ] = Gen::StrStartsWith( $v, '{' ) ? @json_decode( $v, true ) : $v;
			continue;
		}

		if( !preg_match( '@' . preg_quote( $root ) . '\\s*\\.\\s*' . preg_quote( $id ) . '\\s*=\\s*@s', $itemInitScr -> nodeValue, $posStart, PREG_OFFSET_CAPTURE ) )
			continue;

		$posStart = $posStart[ 0 ][ 1 ] + strlen( $posStart[ 0 ][ 0 ] );
		$pos = Gen::JsonGetEndPos( $posStart, $itemInitScr -> nodeValue );
		if( $pos === null )
			continue;

		$prms[ $id ] = @json_decode( Gen::JsObjDecl2Json( substr( $itemInitScr -> nodeValue, $posStart, $pos - $posStart ) ), true );
	}

	return( $prms );
}

// #######################################################################
// #######################################################################

?>