<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_jqVide( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	foreach( $xpath -> query( './/*[@data-vide-bg]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$bg = Gen::ParseProps( $item -> getAttribute( 'data-vide-bg' ), ',', ':' );
		$options = array_merge( array( 'volume' => '1', 'playbackRate' => '1', 'muted' => 'true', 'loop' => 'true', 'autoplay' => 'true', 'posterType' => 'detect', 'position' => '50% 50%', 'resizing' => 'true', 'bgColor' => 'transparent' ), Gen::ParseProps( $item -> getAttribute( 'data-vide-options' ), ',', ':' ) );

		$item -> removeAttribute( 'data-vide-bg' );
		$item -> removeAttribute( 'data-vide-options' );

		$aStyle = array(
			'position'				=> 'absolute',
			'z-index'				=> -1,
			'left'					=> 0,
			'right'					=> 0,
			'top'					=> 0,
			'bottom'				=> 0,
			'overflow'				=> 'hidden',
			'background-size'		=> 'cover',
			'background-position'	=> $options[ 'position' ],
			'background-color'		=> $options[ 'bgColor' ],
			'background-repeat'		=> 'no-repeat',
		);

		$urlPoster = null;
		if( $options[ 'posterType' ] == 'detect' )
		{
			foreach( array( 'gif', 'jpg', 'jpeg', 'png' ) as $posterProbe )
			{
				$posterProbe = ($bg[ 'poster' ]??'') . '.' . $posterProbe;
				$imgSrc = new ImgSrc( $ctxProcess, $posterProbe, null, true/*null*/ );
				if( $imgSrc -> GetCont() === false )
					continue;

				unset( $imgSrc );
				$urlPoster = $posterProbe;
				break;
			}
		}
		else if( $options[ 'posterType' ] != 'none' )
			$urlPoster = ($bg[ 'poster' ]??'') . '.' . $options[ 'posterType' ];

		$aStyle[ 'background-image' ] = ( $urlPoster !== null ) ? ( 'url("' . ($bg[ 'poster' ]??'') . '.' . $options[ 'posterType' ] . '")' ) : 'none';

		$aAttrVid = array(
			'autoplay'				=> $options[ 'autoplay' ],
			'loop'					=> $options[ 'loop' ],
			'volume'				=> $options[ 'volume' ],
			'muted'					=> $options[ 'muted' ],
			'defaultMuted'			=> $options[ 'muted' ],
			'playbackRate'			=> $options[ 'playbackRate' ],
			'defaultPlaybackRate'	=> $options[ 'playbackRate' ],

			'style' => array(
				'position'			=> 'absolute',
				'z-index'			=> -1,
				'object-fit'		=> 'cover',
				'object-position'	=> $options[ 'position' ],
				'width'				=> '100%',
				'height'			=> '100%',
			),
		);

		$aVidChild = array();
		foreach( array( 'mp4', 'webm', 'ogv' ) as $vidType )
			if( ($bg[ $vidType ]??null) )
				$aVidChild[] = HtmlNd::CreateTag( $doc, 'source', array( 'src' => $bg[ $vidType ] . '.' . $vidType, 'type' => 'video/' . $vidType ) );

		$item -> insertBefore( HtmlNd::CreateTag( $doc, 'div', array( 'class' => ($options[ 'className' ]??null), 'style' => $aStyle ), array( HtmlNd::CreateTag( $doc, 'video', $aAttrVid, $aVidChild ) ) ), $item -> firstChild );
	}
}

function _ProcessCont_Cp_jqSldNivo( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$bScrFound = null;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," nivoSlider ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( !$item -> parentNode || !$item -> parentNode -> parentNode )
			continue;

		if( $bScrFound === null )
			$bScrFound = false;

		// Find and apply initialization script
		$prms = array();
		for( $itemProbe = null; $itemProbe = HtmlNd::GetNextTreeChild( $item -> parentNode -> parentNode, $itemProbe ); )
		{
			if( $itemProbe -> nodeType != XML_ELEMENT_NODE || $itemProbe -> nodeName != 'script' || HtmlNd::DoesContain( $item, $itemProbe ) )
				continue;

			$m = array();
			if( !@preg_match( '@(jQuery\\([\'"]#' . $item -> getAttribute( 'id' ) . '[\'"]\\))\\.nivoSlider\\(\\s*@', $itemProbe -> nodeValue, $m, PREG_OFFSET_CAPTURE ) )
				continue;

			{
				$posStart = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
				$posEnd = Gen::JsonGetEndPos( $posStart, $itemProbe -> nodeValue );

				if( $posEnd !== null )
					$prms = @json_decode( Gen::JsObjDecl2Json( substr( $itemProbe -> nodeValue, $posStart, $posEnd - $posStart ) ), true );
				if( !$prms )
					$prms = array();
			}

			$itemProbe -> nodeValue = substr_replace( $itemProbe -> nodeValue, 'var c=' . $m[ 1 ][ 0 ] . ';c.parent().find( ".js-lzl-ing" ).remove();c', $m[ 1 ][ 1 ], strlen( $m[ 1 ][ 0 ] ) );
			$bScrFound = true;
			break;
		}

		{
			$aNav = array();
			for( $itemSlide = HtmlNd::GetFirstElement( $item ), $i = 0; $itemSlide; $itemSlide = HtmlNd::GetNextElementSibling( $itemSlide ), $i++ )
				$aNav[] = HtmlNd::CreateTag( $doc, 'a', array( 'class' => array( 'nivo-control', Gen::GetArrField( $prms, array( 'startSlide' ), 0 ) == $i ? 'active' : null ) ), array( $doc -> createTextNode( ( string )( $i + 1 ) ) ) );
			$item -> parentNode -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'nivo-controlNav', 'js-lzl-ing' ), 'style' => array() ), $aNav ) );
		}

		$sldImgSrc = null;
		$sldCaption = null;
		if( $itemFirtSlide = HtmlNd::FirstOfChildren( $xpath -> query( '(.//img)[' . ( string )( Gen::GetArrField( $prms, array( 'startSlide' ), 0 ) + 1 ) . ']', $item ) ) )
		{
			$sldImgSrc = $itemFirtSlide -> getAttribute( 'src' );
			$sldCaption = ( string )$itemFirtSlide -> getAttribute( 'title' );
			if( Gen::StrStartsWith( $sldCaption, '#' ) )
			{
				if( $itemFirtSlideCaption = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@id="' . substr( $sldCaption, 1 ) . '"]' ) ) )
				{
					$sldCaption = array();
					for( $itemFirtSlideCaptionChild = HtmlNd::GetFirstElement( $itemFirtSlideCaption ); $itemFirtSlideCaptionChild; $itemFirtSlideCaptionChild = HtmlNd::GetNextElementSibling( $itemFirtSlideCaptionChild ) )
						$sldCaption[] = $itemFirtSlideCaptionChild -> cloneNode( true );
				}
				else
					$sldCaption = null;
			}
			else
				$sldCaption = HtmlNd::ParseAndImportAll( $doc, ( string )$sldCaption );
		}

		if( $sldImgSrc )
		{
			{
				$itemNoScript = $doc -> createElement( 'noscript' );
				$itemNoScript -> setAttribute( 'data-lzl-bjs', '' );
				HtmlNd::MoveChildren( $itemNoScript, $item );
				$item -> appendChild( $itemNoScript );
				ContNoScriptItemClear( $itemNoScript );

				$ctx -> bBjs = true;
			}

			$item -> appendChild( HtmlNd::CreateTag( $doc, 'img', array( 'class' => array( 'nivo-main-image', 'js-lzl-ing' ), 'style' => array( 'display' => 'inline' ), 'src' => $sldImgSrc ) ) );
			$item -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'nivo-caption', 'js-lzl-ing' ), 'style' => array( 'display' => 'block' ) ), $sldCaption ) );
		}

		$item -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'nivo-directionNav', 'js-lzl-ing' ), 'style' => array() ), array( HtmlNd::CreateTag( $doc, 'a', array( 'class' => array( 'nivo-prevNav' ) ), array( $doc -> createTextNode( Gen::GetArrField( $prms, array( 'prevText' ), '' ) ) ) ), HtmlNd::CreateTag( $doc, 'a', array( 'class' => array( 'nivo-nextNav' ) ), array( $doc -> createTextNode( Gen::GetArrField( $prms, array( 'nextText' ), '' ) ) ) ) ) ) );
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $bScrFound === false )
	{
		{
			/*
			
			
			
			



			*/

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "(
	function( d, w )
	{
		var fnInitScrCustomPrev = w.seraph_accel_js_lzl_initScrCustom;
		w.seraph_accel_js_lzl_initScrCustom =
			function()
			{
				if( fnInitScrCustomPrev )
					fnInitScrCustomPrev();

				if( !w.jQuery || !w.jQuery.fn.nivoSlider || w.jQuery.fn.seraph_accel_nivoSlider )
					return;

				w.jQuery.fn.seraph_accel_nivoSlider = w.jQuery.fn.nivoSlider;
				w.jQuery.fn.nivoSlider =
					function( options )
					{
						this.each(
							function()
							{
								this.parentNode.querySelectorAll( \".js-lzl-ing\" ).forEach(
									function( e )
									{
										e.remove();
									}
								);
							} );

						return( w.jQuery.fn.seraph_accel_nivoSlider.call( this, options ) );
					};
				w.jQuery.fn.nivoSlider.defaults = w.jQuery.fn.seraph_accel_nivoSlider.defaults;
			};
	}
)( document, window );
" );
			$ctxProcess[ 'ndBody' ] -> appendChild( $itemScript );
		}
	}
}

// #######################################################################
// #######################################################################

?>