<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_wooTabs( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-tabs ")][contains(concat(" ",normalize-space(@class)," ")," wc-tabs-wrapper ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( $itemFirstTabTitle = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wc-tabs ")]/*[1]', $item ) ) )
			HtmlNd::AddRemoveAttrClass( $itemFirstTabTitle, array( 'active' ) );

		$bFirstTab = true;
		foreach( HtmlNd::ChildrenAsArr( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-Tabs-panel ")]', $item ) ) as $itemTabBody )
		{
			if( !$bFirstTab )
				$itemTabBody -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemTabBody -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );
			$bFirstTab = false;
		}

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.woocommerce-tabs.*.active@' ] = true;
	}
}

function _ProcessCont_Cp_wooPrdGallSld( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	return;

	// /wp-content/plugins/woo-product-gallery-slider/assets/js/wpgs.js@ver-2.2.11.js

	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-gallery ")][contains(concat(" ",normalize-space(@class)," ")," woo-product-gallery-slider ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
	}
}

function _ProcessCont_Cp_wooOuPrdGal( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	$aPrm = null;

	$cmnStyle = '';

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ouwoo-product-gallery ")][contains(concat(" ",normalize-space(@class)," ")," product-images-slider ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( !( $ctxProcess[ 'mode' ] & 1 ) )
			continue;

		if( $aPrm === null )
		{
			$contScr = _Cp_GetScriptContent( $ctxProcess, $xpath, './/script[@id="ouwoo-product-images-js"][contains(@src,"/plugins/oxyultimate-woo/")]' );
			if( !is_string( $contScr ) )
				return;

			$aPrm = array();

			if( preg_match( '@s\\s*=\\s*{[^{]*(spaceBetween):\\s*(\\d+)\\s*,@', $contScr, $m ) )
				$aPrm[ $m[ 1 ] ] = $m[ 2 ];

			$aPrm[ 'breakpoints' ] = array();
			if( preg_match_all( '@\\.breakpoints\\[(\\d+)\\]\\s*=\\s*{\\s*slidesPerView:\\s*e\\.getAttribute\\("([\\w-]+)"\\)@', $contScr, $m ) )
				foreach( $m[ 1 ] as $i => $width )
					$aPrm[ 'breakpoints' ][] = array( $width, $m[ 2 ][ $i ] );

			usort( $aPrm[ 'breakpoints' ], function( $a, $b ) { return( $a[ 0 ] <=> $b[ 0 ] ); } );

			unset( $contScr );
		}

		$sClassId = null;
		foreach( HtmlNd::GetAttrClass( $item ) as $sClass )
		{
			if( Gen::StrStartsWith( $sClass, 'gallery-wrapper-' ) )
			{
				$sClassId = $sClass;
				break;
			}
		}

		if( !$sClassId )
			continue;

		$nColsMax = ( int )$item -> getAttribute( 'data-cols-desktop' );
		foreach( $aPrm[ 'breakpoints' ] as $i => $dim )
		{
			$nCols = ( int )$item -> getAttribute( $dim[ 1 ] );
			if( $nCols > $nColsMax )
				$nCols = $nColsMax;

			$cmnStyle .= '@media ' . Ui::StyleMediaMinMax( $dim[ 0 ], $i + 1 == count( $aPrm[ 'breakpoints' ] ) ? null : ( $aPrm[ 'breakpoints' ][ $i + 1 ][ 0 ] - 1 ) ) . ' { ';

			$cmnStyle .= '.ouwoo-product-gallery.' . $sClassId . ' { --ouwoo-cols: ' . ( string )$nCols . '; }';
			$cmnStyle .= '.ouwoo-product-gallery.' . $sClassId . ' .product-thumbnail-images:not(.swiper-container-initialized) .swiper-slide:nth-child(n+' . ( string )( $nCols + 1 ) . ') { display: none; }';

			$cmnStyle .= ' }';
		}

		$cmnStyle .= '.ouwoo-product-gallery.' . $sClassId . ' { --ouwoo-spsbtwn: ' . ( string )($aPrm[ 'spaceBetween' ]??0) . 'px; }';
		$cmnStyle .= '.ouwoo-product-gallery.' . $sClassId . ' .product-images:not(.swiper-container-initialized) > .swiper-button-prev, .ouwoo-product-gallery.' . $sClassId . ' .product-thumbnail-images:not(.swiper-container-initialized) > .swiper-button-prev { display: none !important; }';

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		//$ctxProcess[ 'aCssCrit' ][ '@\\.woocommerce-tabs.*.active@' ] = true;

		if( $cmnStyle )
		{
			$cmnStyle .= '.ouwoo-product-gallery .product-thumbnail-images:not(.swiper-container-initialized) .swiper-slide { margin-right: var(--ouwoo-spsbtwn); width: calc((100% - var(--ouwoo-spsbtwn) * (var(--ouwoo-cols) - 1)) / var(--ouwoo-cols)); }';

			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, $cmnStyle );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _ProcessCont_Cp_wooGsPrdGal( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$contCmnStyle = '';
	$aPrm = null;

	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wcgs-woocommerce-product-gallery ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		// /wp-content/plugins/woo-gallery-slider-pro/public/js/woo-gallery-slider-public.js
		// wcgs_object.wcgs_settings = wcgs_object.wcgs_settings; => $aPrm[ 'wcgs_settings' ]
		// setupInitialState()
		// initializeThumbnailSlider()
		// initializeMainSlider()
		// updateSummaryWidth

		if( $aPrm === null )
		{
			$itemScrCfg = HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="woo-gallery-slider-pro-js-extra"]' ) );

			$contScr = _Cp_GetScrContEx( $ctxProcess, $itemScrCfg );
			if( !is_string( $contScr ) )
				return;

			$aPrm = array();

			$posStart = array();
			if( !preg_match( '@var\\s+wcgs_object\\s*=\\s*{@', $contScr, $posStart, PREG_OFFSET_CAPTURE ) )
				return;

			$posStart = $posStart[ 0 ][ 1 ] + strlen( $posStart[ 0 ][ 0 ] ) - 1;
			$pos = Gen::JsonGetEndPos( $posStart, $contScr );
			if( $pos === null )
				return;

			$aPrm = @json_decode( Gen::JsObjDecl2Json( substr( $contScr, $posStart, $pos - $posStart ) ), true );
			if( $aPrm === null )
				return;

			$itemScrCfg -> setAttribute( 'seraph-accel-crit', '1' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemScrCfg );

			unset( $itemScrCfg, $contScr, $posStart, $pos );

			if( is_string( $contScr = _Cp_GetScrCont( $ctxProcess, $xpath, './/script[@id="woo-gallery-slider-pro-js"]' ) ) )
			{
				$aPrm[ 'wcgs_settings' ][ 'lightbox_icon_tag' ] = 'button';
				if( preg_match( '@\\(\\s*[\'"]#wpgs-gallery\\s+\\.wcgs-carousel[\'"]\\s*\\)\\s*\\.\\s*append\\s*\\(\\s*`<(\\w+)\\s*class=[\'"]wcgs-lightbox@', $contScr, $m ) )
					$aPrm[ 'wcgs_settings' ][ 'lightbox_icon_tag' ] = $m[ 1 ];
			}

			unset( $contScr, $m );
		}

		if( Gen::GetArrField( $aPrm, array( 'wcgs_settings', 'gallery_layout_on_mobile' ) ) == '1' )	// Temporary skip that case (it requires dynamiv hor/vert styles, setupInitialState() )
			continue;

		HtmlNd::AddRemoveAttrClass( $item, array( 'lzl-js' ) );

		$itemThumbnails = null;
		if( Gen::GetArrField( $aPrm, array( 'wcgs_settings', 'gallery_layout' ) ) != 'hide_thumb' && $itemThumbnails = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," gallery-navigation-carousel ")][contains(concat(" ",normalize-space(@class)," ")," spswiper ")]', $item ) ) )
		{
			$aThumbs = HtmlNd::ChildrenAsArr( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," spswiper-slide ")]', $itemThumbnails ) );
			if( count( $aThumbs ) > 1 )
			{
				$isMultiRowThumbsLayout = Gen::GetArrField( $aPrm, array( 'wcgs_settings', 'gallery_layout' ) ) == 'multi_row_thumb';
				$galleryLayout = ( Gen::GetArrField( $aPrm, array( 'wcgs_settings', 'gallery_layout' ) ) == 'vertical' || Gen::GetArrField( $aPrm, array( 'wcgs_settings', 'gallery_layout' ) ) == 'vertical_right' ) ? 'vertical' : 'horizontal';
				$thumbnailsItemShowType = Gen::GetArrField( $aPrm, array( 'wcgs_settings', 'thumbnails_item_show_type' ), 'auto' );
				$thumbnailsItemToShow = ( int )Gen::GetArrField( $aPrm, array( 'wcgs_settings', 'thumbnails_item_to_show' ) );
				$thumbnails_sliders_space = ( int )Gen::GetArrField( $aPrm, array( 'wcgs_settings', 'thumbnails_sliders_space', 'width' ), 6, null, false, false );
				if( $thumbnailsItemShowType == 'auto' && $galleryLayout == 'vertical' )
					$thumbnailsItemToShow = 'auto';

				if( $isMultiRowThumbsLayout )
					$contCmnStyle .= _ProcessCont_Cp_swiper_AdjustItem( $itemThumbnails, array( 'cssIdSelPrefix' => '.gallery-navigation-carousel.spswiper', 'cssSelContainer' => '', 'cssSelContainerInited' => '.spswiper-initialized', 'cssSelSlide' => '.spswiper-slide', 'cssSelWrapper' => '.spswiper-wrapper', 'breakpoints' => array( array( 'minWidth' => 0, 'slidesPerView' => 1 ) ), 'space' => 0 ), $ctx, $ctxProcess, $doc, $xpath );
				else
					$contCmnStyle .= _ProcessCont_Cp_swiper_AdjustItem( $itemThumbnails, array( 'cssIdSelPrefix' => '.gallery-navigation-carousel.spswiper', 'cssSelContainer' => '', 'cssSelContainerInited' => '.spswiper-initialized', 'cssSelSlide' => '.spswiper-slide', 'cssSelWrapper' => '.spswiper-wrapper', 'breakpoints' => array( array( 'minWidth' => 0, 'slidesPerView' => $thumbnailsItemToShow ) ), 'space' => $thumbnails_sliders_space, 'isVert' => $galleryLayout == 'vertical' ), $ctx, $ctxProcess, $doc, $xpath );

				HtmlNd::AddRemoveAttrClass( $aThumbs[ 0 ], array( 'spswiper-slide-thumb-active' ) );

				if( $thumbnailsItemToShow >= count( $aThumbs ) )
					HtmlNd::AddRemoveAttrClass( $itemThumbnails, array( 'lzl-js-noarrows' ) );
					//foreach( HtmlNd::ChildrenAsArr( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wcgs-spswiper-arrow ")]', $itemThumbnails ) ) as $itemThumbnailsArrow )
					//    $itemThumbnailsArrow -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemThumbnailsArrow -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );
			}
			else
				$itemThumbnails = null;
		}

		if( !$itemThumbnails )
			$contCmnStyle .= '.lzl-js.wcgs-woocommerce-product-gallery .gallery-navigation-carousel.spswiper:not(.spswiper-initialized) { display: none !important; }';

		if( $itemMain = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wcgs-carousel ")][contains(concat(" ",normalize-space(@class)," ")," spswiper ")]', $item ) ) )
		{
			$contCmnStyle .= '.wcgs-carousel.spswiper' . '.spswiper-container' . ':not(' . '.spswiper-initialized' . ') ' . '.spswiper-slide' . ':nth-child(n+' . 2 . ') { visibility: hidden; }';
			//$contCmnStyle .= _ProcessCont_Cp_swiper_AdjustItem( $itemMain, array( 'cssIdSelPrefix' => '.wcgs-carousel.spswiper', 'cssSelContainer' => '.spswiper-container', 'cssSelContainerInited' => '.spswiper-initialized', 'cssSelSlide' => '.spswiper-slide', 'breakpoints' => array( array( 'minWidth' => 0, 'slidesPerView' => 1 ) ), 'space' => 1 ), $ctx, $ctxProcess, $doc, $xpath );
			HtmlNd::AddRemoveAttrClass( HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," spswiper-slide ")][1]', $itemMain ) ), array( 'spswiper-slide-active' ) );

			$aSldBullets = array(); $iBN = count( HtmlNd::ChildrenAsArr( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," spswiper-slide ")]', $itemMain ) ) );
			for( $iB = 0; $iB < $iBN; $iB++ )
				$aSldBullets[] = HtmlNd::CreateTag( $doc, 'span', array( 'class' => array( 'spswiper-pagination-bullet', !$iB ? 'spswiper-pagination-bullet-active' : null ) ), array( HtmlNd::CreateTag( $doc, 'span', array( 'class' => array( 'number' ) ), array( $doc -> createTextNode( '' . ( $iB + 1 ) ) ) ) ) );

			$itemPagination = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," spswiper-pagination ")]', $itemMain ) );
			if( !$itemPagination )
				$itemMain -> appendChild( $itemPagination = HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'spswiper-pagination' ) ) ) );
			HtmlNd::AddRemoveAttrClass( $itemPagination, array( 'wcgs-pagination', 'bullets', 'spswiper-pagination-clickable', 'spswiper-pagination-bullets', 'spswiper-pagination-horizontal' ) );
			HtmlNd::Append( $itemPagination, $aSldBullets );

			if( Gen::GetArrField( $aPrm, array( 'wcgs_settings', 'lightbox' ) ) == '1' )
			{
				// getLightboxIcon()
				$sIcon = Gen::GetArrField( $aPrm, array( 'wcgs_settings', 'lightbox_icon' ), 'search-plus' );
				HtmlNd::InsertChild( $itemMain, 0, HtmlNd::ParseAndImport( $doc, '<' . Gen::GetArrField( $aPrm, array( 'wcgs_settings', 'lightbox_icon_tag' ), 'button' ) . ' class="wcgs-lightbox grid-lightbox ' . Gen::GetArrField( $aPrm, array( 'wcgs_settings', 'lightbox_icon_position' ), 'top_right' ) . '" aria-label="Lightbox"><span class="sp-wgsp-icon-' . Gen::GetArrField( array( 'search-plus' => 'zoom-in-1', 'angle-right' => 'right-open-3', 'arrows-alt' => 'resize-full-2', 'expand' => 'resize-full' ), array( $sIcon ), $sIcon ) . '"></span></button>' ) );
			}
		}

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, '(function(s){var eCont=s.parentNode;eCont.removeChild(s);seraph_accel_cp_wooGsPrdGal_calcSizes(eCont.querySelector(".wcgs-woocommerce-product-gallery"));})(document.currentScript)' );
			$item -> parentNode -> appendChild( $itemScript );
		}

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.wcgs-mobile-layout@' ] = true;

		{
			/*
			
			
			
			



			*/

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "function seraph_accel_cp_wooGsPrdGal_calcSizes( e )
{
	//function querySelectorSafe( e, sel )
	//{
	//	// Fallback methods
	//	var eFind = e.querySelector( sel );
	//	return( eFind ? eFind : eFind.do );
	//}

	function emToPx( em )
	{
		// Fallback methods
		var fontSize = document.documentElement.style.fontSize || window.getComputedStyle( document.documentElement ).fontSize || '16px';
		return( em * parseFloat( fontSize ) );
	}

	function getInnerWidth( e )
	{
		var w = e.offsetWidth;

		var s = getComputedStyle( e );
		return( w - parseInt( s.getPropertyValue( \"padding-left\" ), 10 ) - parseInt( s.getPropertyValue( \"padding-right\" ), 10 ) );
	}

	function calculateGalleryWidth( ctx )
	{
		var eBody = document.querySelector( \"body\" );
		var eTmp;

		var gallery_w = wcgs_object.wcgs_settings.gallery_width;
		var woocommerce_single_wrapper_width = getInnerWidth( document.querySelector( wcgs_object.wcgs_product_wrapper ) );
		var gallery_parent_product_width = getInnerWidth( ctx.e.parentNode );
		var woocommerce_single_product_width = gallery_parent_product_width > woocommerce_single_wrapper_width ? woocommerce_single_wrapper_width : gallery_parent_product_width;
		var widthUnit = \"%\";
		// Special handling for Flatsome theme.
		if( eBody.classList.contains( \"theme-flatsome\" ) )
			woocommerce_single_product_width = getInnerWidth( document.querySelector( \".single-product .product .row.content-row\" ) );

		// Handle vertical/hide thumb layouts.
		if( wcgs_object.wcgs_settings.gallery_layout == 'vertical' ||
			wcgs_object.wcgs_settings.gallery_layout == 'vertical_right' ||
			wcgs_object.wcgs_settings.gallery_layout == 'hide_thumb' ||
			gallery_w > 100 )
		{
			gallery_w = ( ( gallery_w * woocommerce_single_product_width ) / 100 );
			// Specific width in pixel for desktop.
			if( wcgs_object.wcgs_settings.gallery_width > 100 )
				gallery_w = wcgs_object.wcgs_settings.gallery_width;
		}

		// Hestia theme support
		if( ctx.e.parentNode.querySelectorAll( \".hestia-product-image-wrap\" ).length )
		{
			eTmp = document.querySelector( \".wcgs-wcgs-woocommerce-product-gallery\" );
			if( eTmp && eTmp.parentNode )
			{
				eTmp = eTmp.parentNode.querySelector( \".hestia-product-image-wrap\" );
				if( eTmp )
					gallery_w = getInnerWidth( e1 );
			}
		}

		// Divi builder width issue
		if( eBody.classList.contains( \"et_divi_builder\" ) || eBody.classList.contains( \"theme-Divi\" ) )
		{
			eTmp = document.querySelector( \".et-db #et-boc .et-l .et_pb_row .et_pb_column:has(#wpgs-gallery)\" );
			if( eTmp )
				gallery_w = getInnerWidth( eTmp );
		}

		if( !document.querySelectorAll( '#wpgs-gallery ~ .summary' ).length )
			gallery_w = ctx.e.parentNode.getBoundingClientRect().width;	// ???

		// Responsive widths
		if( ctx.windowWidth < 992 )
		{
			if( wcgs_object.wcgs_settings.gallery_responsive_width.width > 0 )
			{
				gallery_w = wcgs_object.wcgs_settings.gallery_responsive_width.width;
				widthUnit = wcgs_object.wcgs_settings.gallery_responsive_width.unit;
			}
		}

		if( ctx.windowWidth < 768 )
		{
			gallery_w = wcgs_object.wcgs_settings.gallery_responsive_width.height;
			widthUnit = wcgs_object.wcgs_settings.gallery_responsive_width.unit;
		}

		if( ctx.windowWidth < 480 )
		{
			gallery_w = wcgs_object.wcgs_settings.gallery_responsive_width.height2;
			widthUnit = wcgs_object.wcgs_settings.gallery_responsive_width.unit;
		}

		if( widthUnit == 'em' )
			gallery_w = emToPx( gallery_w );

		return( gallery_w );
	}

	function setGalleryWidth( ctx, width )
	{
		var widthUnit = '%';
		const isVerticalLayout = wcgs_object.wcgs_settings.gallery_layout == 'vertical' || wcgs_object.wcgs_settings.gallery_layout == 'vertical_right';
		const needsPixelWidth = isVerticalLayout || wcgs_object.wcgs_settings.gallery_layout == 'hide_thumb' || width > 100;
		if( needsPixelWidth )
		{
			widthUnit = 'px';
			var currentGLWidth = ctx.e.parentNode.getBoundingClientRect().width;
			width = currentGLWidth > width ? width : currentGLWidth;
			ctx.e.style.setProperty( \"max-width\", '100%' );
		}
		// Set gallery width.
		ctx.e.style.setProperty( 'min-width', 'auto' );
		ctx.e.style.setProperty( 'width', \"\" + width + widthUnit );

		/*const wcgs_img_count = ctx.e.find('.wcgs-carousel .wcgs-slider-image').length;
		if (wcgs_img_count == 1 || wcgs_object.wcgs_settings.gallery_layout == 'hide_thumb') {
			this.initializeHideThumbLayout();
							ctx.e.find('.wcgs-carousel').css('width', '100%');
			if (wcgs_img_count == 1) {
								ctx.e.find(\".wcgs-spswiper-arrow\").hide()
			} else {
								ctx.e.find(\".wcgs-spswiper-arrow\").show()
			}
		} else {
							ctx.e.find('.gallery-navigation-carousel, .gallery-navigation-carousel-wrapper').show();
			// Calculate and set vertical thumbnails width if needed.
			if (isVerticalLayout) {
				const verticalThumbsWidth = typeof wcgs_object.wcgs_settings.vertical_thumbs_width != 'undefined' ? parseInt(wcgs_object.wcgs_settings.vertical_thumbs_width) : 20;
				const verticalThumbWidth = (width / 100) * verticalThumbsWidth;
				const verticalGap = typeof wcgs_object.wcgs_settings.thumbnails_sliders_space.height != 'undefined' ? parseInt(wcgs_object.wcgs_settings.thumbnails_sliders_space.height) : 6;
				// $('.wcgs-carousel.spswiper')
				// 	.css('maxWidth', width - verticalThumbWidth - verticalGap)
				// 	.css('width', width - verticalThumbWidth - verticalGap);
				// $('.gallery-navigation-carousel-wrapper.vertical')
				// 	.css('width', verticalThumbWidth);
			}
		}*/

		updateSummaryWidth( ctx, width, widthUnit );
	}

	function calculateSummaryWidth( ctx, parentWidth, galleryWidth, widthUnit )
	{
		if( parentWidth > ( getInnerWidth( ctx.e ) + 100 ) )
		{
			const summaryWidth = parentWidth - galleryWidth;
			if( summaryWidth > 150 )
				return( ( summaryWidth - 50 ) + widthUnit );
		}

		return( '' );
	}

	function updateSummaryWidth( ctx, galleryWidth, widthUnit )
	{
		var parentWrapper = ctx.e.parentNode;

		var summaryWidth;
		if( widthUnit == '%')
		{
			summaryWidth = ( 100 - galleryWidth );
			summaryWidth = summaryWidth > 20 ? ( \"calc(\" + summaryWidth + \"% - 50px)\" ) : \"\";
		}
		else
		{
			const parentWidth = getInnerWidth( parentWrapper );
			summaryWidth = calculateSummaryWidth( ctx, parentWidth, galleryWidth, widthUnit );
		}

		var eSummary = parentWrapper.querySelector( \".summary\" );
		if( eSummary )
			eSummary.style.setProperty( \"max-width\", summaryWidth );
	}

	function calculateGalleryHeights( ctx, gallery_w )
	{
		const wcgs_img_count = ctx.eNav.querySelectorAll( '.wcgs-thumb' ).length;
		const vertical_thumbs_width = wcgs_object.wcgs_settings.vertical_thumbs_width || 20;
		const vertical_thumb_width = ( gallery_w / 100 ) * vertical_thumbs_width;
		const thumbnails_space = ( wcgs_object.wcgs_settings.thumbnails_sliders_space && wcgs_object.wcgs_settings.thumbnails_sliders_space.width ) ? wcgs_object.wcgs_settings.thumbnails_sliders_space.width : 6;
		if( wcgs_object.wcgs_settings.gallery_layout == 'horizontal' || wcgs_object.wcgs_settings.gallery_layout == 'horizontal_top' || wcgs_object.wcgs_settings.gallery_layout == 'multi_row_thumb' )
			/*this.handleHorizontalLayout()*/;
		else if( wcgs_object.wcgs_settings.gallery_layout == 'hide_thumb' )
		{
			ctx.eCrsl.style.setProperty( 'width', \"\" + gallery_w + \"px\" );
			ctx.eNavWrp.style.setProperty( 'display', \"none\" );
		}
		else
			handleVerticalLayout( ctx, gallery_w, wcgs_img_count, vertical_thumb_width, thumbnails_space );
	}

	function handleVerticalLayout( ctx, gallery_w, wcgs_img_count, vertical_thumb_width, thumbnails_space )
	{
		if( wcgs_img_count <= 1 )
		{
			ctx.eNavWrp.style.setProperty( 'display', \"none\" );
			return;
		}

		var vertical_gap = ( wcgs_object.wcgs_settings.thumbnails_sliders_space && wcgs_object.wcgs_settings.thumbnails_sliders_space.height ) ? wcgs_object.wcgs_settings.thumbnails_sliders_space.height : 6;
		// Set carousel width.

		ctx.eCrsl.style.setProperty( 'max-width', \"\" + ( gallery_w - vertical_thumb_width - vertical_gap ) + \"px\" );
		ctx.eCrsl.style.setProperty( 'width', \"\" + ( gallery_w - vertical_thumb_width - vertical_gap ) + \"px\" );
		ctx.eNavWrp.style.setProperty( 'width', \"\" + vertical_thumb_width + \"px\" );

		var maxHeight = getMaxImageHeight( ctx );
		// Calculate and set heights.
		//if( wcgs_object.wcgs_settings.slide_orientation == 'vertical' )
		//	e.querySelector( '.wcgs-carousel .spswiper-slide, .wcgs-carousel' ).style.setProperty( 'min-height', maxHeight );
		// Set navigation wrapper styles.
		ctx.eNavWrp.style.setProperty( 'max-height', \"\" + maxHeight + \"px\" );

		// Add vertically-center class if needed.
		if( wcgs_object.wcgs_settings.image_crop_size && wcgs_object.wcgs_settings.image_crop_size.unit !== 'Hard-crop' )
			ctx.eCrsl.classList.add( 'vertically-center' );

		// Set slide height.
		ctx.eCrsl.style.setProperty( 'height', \"\" + maxHeight + \"px\" );
		ctx.eCrsl.querySelectorAll( '.spswiper-slide' ).forEach(
			function( eSld )
			{
				eSld.style.setProperty( 'height', \"\" + maxHeight + \"px\" );
				eSld.style.setProperty( 'width', \"\" + ( gallery_w - vertical_thumb_width - vertical_gap ) + \"px\" );
			}
		);

		ctx.eNav.style.setProperty( \"--lzl-swpr-sz\", \"\" + ctx.eNav.offsetHeight + \"px\" );
	}

	function getMaxImageHeight( ctx )
	{
		var maxHeight = 0;
		if( wcgs_object.wcgs_settings.slider_height_type && wcgs_object.wcgs_settings.slider_height_type == 'fix_height' )
			return( getResponsiveHeight( ctx ) );

		ctx.eCrsl.querySelectorAll( 'img' ).forEach(
			function( eImg )
			{
				var attrWidth = eImg.getAttribute( \"width\" );
				var attrHeight = eImg.getAttribute( \"height\" );

				// Check if height is set in attributes.
				if( attrWidth && attrHeight )
				{
					attrWidth = parseInt( attrWidth, 10 );
					attrHeight = parseInt( attrHeight, 10 );

					if( attrWidth && attrHeight )
					{
						const ratio = attrHeight / attrWidth;
						const actualWidth = eImg.offsetWidth;
						// Rendered width on screen.
						const estimatedHeight = actualWidth * ratio;
						if( estimatedHeight > maxHeight )
							maxHeight = estimatedHeight;
					}
				}
				else
				{
					if( eImg.offsetHeight > maxHeight )
						maxHeight = eImg.offsetHeight;
				}
			}
		);

		return( maxHeight );
	}

	function getResponsiveHeight( ctx )
	{
		const slider_height = wcgs_object.wcgs_settings.slider_height || {};

		if( ctx.windowWidth < 520 && slider_height.height2 )
			return ( slider_height.height2 || 500 );
		if( ctx.windowWidth < 736 && slider_height.height )
			return ( slider_height.height || 500 );
		return ( slider_height.width || 500 );
	}

	var ctx =
	{
		e:				e,
		windowWidth:	window.innerWidth,
	};

	ctx.eCrsl = e.querySelector( '.wcgs-carousel' );
	ctx.eNavWrp = e.querySelector( \".gallery-navigation-carousel-wrapper\" );
	ctx.eNav = ctx.eNavWrp.querySelector( \".gallery-navigation-carousel\" );

	var isMobileDevice = ctx.windowWidth < 768;
	if( wcgs_object.wcgs_settings.gallery_layout_on_mobile && wcgs_object.wcgs_settings.gallery_layout_on_mobile == '1' && ( wcgs_object.wcgs_settings.gallery_layout == 'vertical' || wcgs_object.wcgs_settings.gallery_layout == 'vertical_right' ) )
	{
		if( isMobileDevice )
		{
			wcgs_object.wcgs_settings.gallery_layout = 'horizontal';

			ctx.eCrsl.classList.remove( \"vertical\" );
			ctx.eCrsl.classList.add( \"horizontal\" );

			ctx.eNav.classList.remove( \"vertical\" );
			ctx.eNav.classList.add( \"horizontal\" );

			ctx.e.classList.remove( \"vertical\" );
			ctx.e.classList.add( \"horizontal\" );
		}
	}
	else if( isMobileDevice && wcgs_object.wcgs_settings.slider_layout_on_mobile && wcgs_object.wcgs_settings.slider_layout_on_mobile == '1' )
	{
		wcgs_object.wcgs_settings.gallery_layout = 'hide_thumb';

		ctx.e.classList.remove( \"grid\", \"wcgs_vertical_scroll_nav\", \"vertical\" );

		ctx.eCrsl.classList.remove( \"vertical\" );
		ctx.eCrsl.classList.add( \"wcgs-mobile-layout\", \"horizontal\" );
	}

	var gallery_w = calculateGalleryWidth( ctx );
	setGalleryWidth( ctx, gallery_w );

	var currentGLWidth = e.parentNode.getBoundingClientRect().width;
	gallery_w = currentGLWidth > gallery_w ? gallery_w : currentGLWidth;
	calculateGalleryHeights( ctx, gallery_w );
}

(
	function( d )
	{
		function OnEvt( evt )
		{
			d.querySelectorAll( \".wcgs-woocommerce-product-gallery\" ).forEach( seraph_accel_cp_wooGsPrdGal_calcSizes );
		}

		d.addEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
		seraph_accel_lzl_bjs.add(
			function()
			{
				d.removeEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
			}
		);
	}
)( document );
" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}

		if( $contCmnStyle )
		{
			$contCmnStyle .= '.lzl-js.wcgs-woocommerce-product-gallery .wcgs-carousel.spswiper:not(.spswiper-initialized), .lzl-js.wcgs-woocommerce-product-gallery .gallery-navigation-carousel:not(.spswiper-initialized) { visibility: visible !important; opacity: 1 !important; }';
			$contCmnStyle .= '.lzl-js.wcgs-woocommerce-product-gallery .wcgs-carousel.spswiper:not(.spswiper-initialized) { display: block !important; }';
			$contCmnStyle .= '.lzl-js.wcgs-woocommerce-product-gallery .wcgs-carousel:not(.spswiper-initialized).wcgs-mobile-layout .wcgs-spswiper-arrow, .lzl-js.wcgs-woocommerce-product-gallery .wcgs-carousel:not(.wcgs-mobile-layout) .wcgs-pagination { display: none !important; }';
			$contCmnStyle .= '.lzl-js.wcgs-woocommerce-product-gallery .gallery-navigation-carousel.lzl-js-noarrows .wcgs-spswiper-arrow { display: none !important; }';

			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, $contCmnStyle );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _ProcessCont_Cp_wooPrdGall( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$aPrm = null;
	$adjusted = false;

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-gallery ")][contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-gallery--with-images ")][count(./*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-gallery__wrapper ")]/*[@data-thumb]) > 0]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( $aPrm === null )
		{
			$aPrm = array();

			$contScr = ( string )_Cp_GetScriptContent( $ctxProcess, $xpath, './/script[@id="wc-single-product-js-extra"]' );
			if( preg_match( '@var\\s+wc_single_product_params\\s*=\\s*{@', $contScr, $m, PREG_OFFSET_CAPTURE ) )
			{
				$posStart = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] ) - 1;
				$pos = Gen::JsonGetEndPos( $posStart, $contScr );
				if( $pos !== null )
					$aPrm = ( array )@json_decode( Gen::JsObjDecl2Json( substr( $contScr, $posStart, $pos - $posStart ) ), true );
			}

			unset( $contScr, $m, $posStartm, $pos );

			if( !Gen::GetArrField( $aPrm, array( 'flexslider_enabled' ) ) )
				break;

			// /wp-content/plugins/woocommerce/assets/js/flexslider/jquery.flexslider.js: flexslider.defaults
			if( Gen::GetArrField( $aPrm, array( 'flexslider', 'animation' ), 'fade' ) != 'fade' )
				$aPrm[ '_bFlexViewport' ] = true;
			if( Gen::GetArrField( $aPrm, array( 'flexslider', 'animation' ), 'fade' ) == 'fade' || ( Gen::GetArrField( $aPrm, array( 'flexslider', 'smoothHeight' ), false ) && Gen::GetArrField( $aPrm, array( 'flexslider', 'direction' ), 'horizontal' ) != 'vertical' ) )
				$aPrm[ '_bSmoothHeight' ] = true;
		}

		$itemWrp = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-gallery__wrapper ")]', $item ) );
		if( !$itemWrp )
			continue;

		$itemWrpParent = $itemWrp -> parentNode;
		$itemWrpClone = null;
		if( Gen::GetArrField( $aPrm, array( 'flexslider_enabled' ) ) )
		{
			if( ($aPrm[ '_bFlexViewport' ]??null) )
			{
				$itemWrpClone = $itemWrp -> cloneNode();
				HtmlNd::AddRemoveAttrClass( $itemWrpClone, array( 'woocommerce-product-gallery__wrapper-js-lzl-ing' ), array( 'woocommerce-product-gallery__wrapper' ) );
			}
		}

		$aThumbs = array();
		{
			$bFirstImg = true;
			for( $itemWrpChild = HtmlNd::GetFirstElement( $itemWrp ); $itemWrpChild; $itemWrpChild = HtmlNd::GetNextElementSibling( $itemWrpChild ) )
			{
				if( $src = $itemWrpChild -> getAttribute( 'data-thumb' ) )
				{
					if( $bFirstImg && $itemWrpClone )
					{
						$itemWrpClone -> appendChild( $itemWrpChild -> cloneNode( true ) );
						$bFirstImg = false;
					}

					$aThumbs[] = HtmlNd::CreateTag( $doc, 'li', null, array( HtmlNd::CreateTag( $doc, 'img', array( 'src' => $src, 'alt' => Gen::NullIfEmpty( $itemWrpChild -> getAttribute( 'data-thumb-alt' ) ) ) ) ) );
				}
			}
			unset( $bFirstImg );
		}

		if( count( $aThumbs ) <= 1 )
		{
			unset( $itemWrpClone, $aThumbs );
			continue;
		}

		if( $itemWrpClone )
		{
			$itemViewport = HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'flex-viewport-js-lzl-ing' ) ) );
			$itemViewport -> appendChild( $itemWrpClone );
			$itemWrpParent -> appendChild( $itemViewport );

			{
				$itemNoScript = $doc -> createElement( 'noscript' );
				$itemNoScript -> setAttribute( 'data-lzl-bjs', '' );
				$itemWrpParent -> insertBefore( $itemNoScript, $itemWrp );
				$itemNoScript -> appendChild( $itemWrp );
				ContNoScriptItemClear( $itemNoScript );

				$ctx -> bBjs = true;
			}
		}

		{
			$aPrm[ '_bThumbs' ] = true;

			HtmlNd::AddRemoveAttrClass( HtmlNd::GetFirstElement( $aThumbs[ 0 ] ), array( 'flex-active' ) );
			$itemWrpParent -> appendChild( HtmlNd::CreateTag( $doc, 'ol', array( 'class' => array( 'flex-control-thumbs-js-lzl-ing', 'flex-control-nav-js-lzl-ing' ) ), $aThumbs ) );
		}

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.flex-control-thumbs@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.flex-control-nav@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.flex-viewport@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.woocommerce-product-gallery__wrapper@' ] = true;
		//$ctxProcess[ 'aCssCrit' ][ '@.@' ] = true;

		{
			/*
			
			
			
			



			*/

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, str_replace( array( '_E_FADE_' ), array( Gen::GetArrField( $aPrm, array( 'flexslider', 'animation' ), 'fade' ) == 'fade' ? '1' : '0' ), "(
	function( d )
	{
		function OnPostClean( evt )
		{
			var e = evt.target;

			e.querySelectorAll( \".woocommerce-product-gallery__wrapper-js-lzl-ing,.flex-viewport-js-lzl-ing,.flex-control-thumbs-js-lzl-ing\" ).forEach(
				function( eSub )
				{
					eSub.parentNode.removeChild( eSub );
				}
			);
		}

		seraph_accel_lzl_bjs.add(
			function()
			{
				d.addEventListener( \"DOMContentLoaded\", function() { if( window.jQuery ) jQuery( d ).on( \"wc-product-gallery-after-init\", OnPostClean ); }, { capture: true, passive: true } );
			}
		);
	}
)( document );
" ) );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}

		if( ($aPrm[ '_bSmoothHeight' ]??null) )
		{
			/*
			
			
			
			



			*/

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, str_replace( array( '_E_FADE_' ), array( Gen::GetArrField( $aPrm, array( 'flexslider', 'animation' ), 'fade' ) == 'fade' ? '1' : '0' ), "function seraph_accel_cp_wooPrdGall_calcSizes( e )
{
	var eTarget = e;
	if( !_E_FADE_ )
	{
		eTarget = e.querySelector( \".flex-viewport\" );
		if( !eTarget )
			eTarget = e.querySelector( \".flex-viewport-js-lzl-ing\" );

		if( !eTarget )
			return;
	}

	var eVwp = e.querySelector( \".flex-viewport > :first-child > :first-child > img\" );
	if( !eVwp )
		eVwp = e.querySelector( \".flex-viewport-js-lzl-ing > :first-child > :first-child img\" );
	if( !eVwp )
		eVwp = e.querySelector( \".woocommerce-product-gallery__wrapper > :first-child img\" );

	if( !eVwp )
		return;

	var h = eVwp.getBoundingClientRect().height;
	e.style.setProperty( \"--lzl-vp-h\", \"\" + h + \"px\" );
	eTarget.style.setProperty( \"height\", \"var(--lzl-vp-h)\" );
}

(
	function( d )
	{
		function OnEvt( evt )
		{
			d.querySelectorAll( \".woocommerce-product-gallery.woocommerce-product-gallery--with-images\" ).forEach( seraph_accel_cp_wooPrdGall_calcSizes );
		}

		d.addEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
		seraph_accel_lzl_bjs.add(
			function()
			{
				d.removeEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
			}
		);
	}
)( document );
" ) );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}

		{
			$cmnStyle = '';

			if( ($aPrm[ '_bFlexViewport' ]??null) )
			{
				$cmnStyle .=
					'
					.woocommerce-product-gallery .woocommerce-product-gallery__wrapper-js-lzl-ing
					{
						width: 100% !important;
						display: flex;
						overflow: hidden !important;
					}

					.woocommerce-product-gallery .woocommerce-product-gallery__wrapper-js-lzl-ing > *
					{
						flex-shrink: 0 !important;
						width: 100% !important;
					}

					.woocommerce-product-gallery > .flex-viewport-js-lzl-ing {
						overflow: hidden !important;
						position: relative;
					}
					';
			}
			else
			{
				$cmnStyle .=
					'
					.woocommerce-product-gallery__wrapper:not([style]) > * {
						width: 100%;
						float: left;
						margin-right: -100%;
						position: relative;
					}

					.woocommerce-product-gallery__wrapper:not([style]) > *:not(:first-child) {
						opacity: 0;
					}
					';
			}

			$cmnStyle .=
				'
				.woocommerce-product-gallery .flex-viewport:not([style*="height:"]) .woocommerce-product-gallery__wrapper {
					width: 100% !important;
				}

				' . ( ($aPrm[ '_bFlexViewport' ]??null) ? '.woocommerce-product-gallery > .woocommerce-product-gallery__wrapper,' : '' ) . '
				.woocommerce-product-gallery .flex-viewport:not([style*="height:"]) .woocommerce-product-gallery__wrapper > :not(:first-child),
				.woocommerce-product-gallery:has(.flex-control-thumbs) > .flex-control-thumbs-js-lzl-ing,
				.woocommerce-product-gallery:has(.flex-viewport) > .flex-viewport-js-lzl-ing,
				.woocommerce-product-gallery > *:not(.js-lzl-ing) .flex-control-thumbs-js-lzl-ing,
				.woocommerce-product-gallery > *:not(.js-lzl-ing) .flex-viewport-js-lzl-ing {
					display: none !important;
				}
				';

			if( ($aPrm[ '_bThumbs' ]??null) || ($aPrm[ '_bFlexViewport' ]??null) )
			{
				$ctx = new AnyObj();
				$ctx -> aFind = array();
				$ctx -> aReplace = array();

				$ctx -> cb =
					function( $ctx, $sel, $bReplace )
					{
						if( Gen::StrPosArr( $sel, $ctx -> aFind ) === false )
							return( false );
						if( !$bReplace )
							return( true );
						return( str_replace( $ctx -> aFind, $ctx -> aReplace, $sel ) );
					};

				if( ($aPrm[ '_bThumbs' ]??null) )
				{
					$ctx -> aFind = array_merge( $ctx -> aFind, array( '.flex-control-thumbs', '.flex-control-nav' ) );
					$ctx -> aReplace = array_merge( $ctx -> aReplace, array( '.flex-control-thumbs-js-lzl-ing', '.flex-control-nav-js-lzl-ing' ) );
				}

				if( ($aPrm[ '_bFlexViewport' ]??null) )
				{
					$ctx -> aFind = array_merge( $ctx -> aFind, array( '.flex-viewport', '.woocommerce-product-gallery__wrapper' ) );
					$ctx -> aReplace = array_merge( $ctx -> aReplace, array( '.flex-viewport-js-lzl-ing', '.woocommerce-product-gallery__wrapper-js-lzl-ing' ) );
				}

				$cmnStyle .= _Cp_CloneStyles( $ctxProcess, $xpath, array( $ctx, 'cb' ) );
			}

			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, $cmnStyle );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _ProcessCont_Cp_wooPrdGallAstrThmbsHeight( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ast-product-gallery-layout-vertical ")]//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-gallery ")][contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-gallery--with-images ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		//$ctxProcess[ 'aCssCrit' ][ '@\\.flex-control-thumbs@' ] = true;

		// /wp-content/uploads/astra-addon/astra-addon-6761820aaf30a6-17548546@ver-3.9.2.js: flex-control-nav

		{
			/*
			
			
			
			



			*/

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "function seraph_accel_cp_wooPrdGallAstrThmbsHeight_calcSizes( e )
{
	//var eNav = e.querySelector( \".flex-control-nav\" );
	//if( !eNav )
	//	eNav = e.querySelector( \".flex-control-nav-js-lzl-ing\" );

	//if( !eNav )
	//	return;

	var eVwp = e.querySelector( \".flex-viewport\" );
	if( !eVwp )
		eVwp = e.querySelector( \".flex-viewport-js-lzl-ing\" );

	if( !eVwp )
		return;

	var h = eVwp.getBoundingClientRect().height;
	//var h2 = eNav.getBoundingClientRect().height;
	//if( h2 <= h + 50 )
	//	h = h2;

	e.style.setProperty( \"--lzl-ast-fcn-h\", \"\" + h + \"px\" );
}

(
	function( d )
	{
		function OnEvt( evt )
		{
			d.querySelectorAll( \".ast-product-gallery-layout-vertical .woocommerce-product-gallery.woocommerce-product-gallery--with-images\" ).forEach( seraph_accel_cp_wooPrdGallAstrThmbsHeight_calcSizes );
		}

		d.addEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
		seraph_accel_lzl_bjs.add(
			function()
			{
				//d.removeEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
			}
		);
	}
)( document );
" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
		
		{
			$cmnStyle =
				"@media (min-width: 768px) {\r\n\t.ast-product-gallery-layout-vertical .woocommerce-product-gallery .flex-control-nav/*:not([style*=\"max-height\"])*/,\r\n\t.ast-product-gallery-layout-vertical .woocommerce-product-gallery .flex-control-nav-js-lzl-ing {\r\n\t\tmax-height: var(--lzl-ast-fcn-h) !important;\r\n\t\t/*paddine-right: 2px;*/\r\n\t}\r\n\t\r\n\t.ast-product-gallery-layout-vertical .woocommerce-product-gallery .flex-control-nav/*:not([style*=\"max-height\"])*/ {\r\n\t\toverflow: hidden auto !important;\r\n\t}\r\n}";

			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, $cmnStyle );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _ProcessCont_Cp_wooPrdGallFltsmThmbs( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$aPrm = null;

	$adjusted = false;
	foreach( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," theme-flatsome ")]//*[contains(concat(" ",normalize-space(@class)," ")," product-gallery ")][count(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-gallery ")]) > 0]//*[contains(concat(" ",normalize-space(@class)," ")," product-thumbnails ")][@data-flickity-options][count(.//*[contains(concat(" ",normalize-space(@class)," ")," col ")]) > 0]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( $aPrm === null )
		{
			$aPrm = array( 'breakDim' => 850 );

			$contScr = ( string )_Cp_GetScrCont( $ctxProcess, $xpath, './/link[@rel="stylesheet"][@id="flatsome-shop-css"]', 'href' );
			if( preg_match( '@\\@media[^{]+\\(min-width:\\s*(\\d+)px\\)\\s*{\\s*.vertical-thumbnails\\s*{@', $contScr, $m ) )
				$aPrm[ 'breakDim' ] = ( int )$m[ 1 ];

			unset( $contScr, $m );
		}

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		//$ctxProcess[ 'aCssCrit' ][ '@\\.flex-control-thumbs@' ] = true;

		$cmnStyle =
			'
			body.theme-flatsome .product-gallery:has(.woocommerce-product-gallery) .vertical-thumbnails > .product-thumbnails:not(.flickity-enabled) {
				display: flex;
				opacity: 1;
			}

			@media screen and (min-width: ' . ( $aPrm[ 'breakDim' ] ) . 'px) {
				body.theme-flatsome .product-gallery:has(.woocommerce-product-gallery) .vertical-thumbnails > .product-thumbnails:not(.flickity-enabled) {
					position: absolute;
					margin-right: 0;
					flex-direction: column;
				}
			}

			@media screen and (max-width: ' . ( $aPrm[ 'breakDim' ] - 1 ) . 'px) {
				body.theme-flatsome .product-gallery:has(.woocommerce-product-gallery) .vertical-thumbnails > .product-thumbnails:not(.flickity-enabled) {
					flex-wrap: nowrap;
				}

				body.theme-flatsome .product-gallery:has(.woocommerce-product-gallery) .vertical-thumbnails > .product-thumbnails:not(.flickity-enabled) .col {
					flex-shrink: 0;
				}
			}
			';

		$itemsCmnStyle = $doc -> createElement( 'style' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
		HtmlNd::SetValFromContent( $itemsCmnStyle, $cmnStyle );
		$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
	}
}

function _ProcessCont_Cp_wooPrdGallCrftThmbs( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," theme-carafity ")]//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-gallery ")][contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-gallery--with-images ")][count(.//*[contains(concat(" ",normalize-space(@class)," ")," flex-control-thumbs-js-lzl-ing ")]) > 0]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( !( $itemThumbs = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," flex-control-thumbs-js-lzl-ing ")]', $item ) ) ) )
			continue;

		$sSwprOrient = null;
		$aClass = HtmlNd::GetAttrClass( $item );
		if( in_array( 'woocommerce-product-gallery-vertical', $aClass ) )
			$sSwprOrient = 'vertical';
		else if( in_array( 'woocommerce-product-gallery-horizontal', $aClass ) )
			$sSwprOrient = 'horizontal';

		if( !$sSwprOrient )
			continue;

		HtmlNd::AddRemoveAttrClass( $itemThumbs, array( 'swiper-wrapper' ) );
		$itemSwpr = HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'swiper swiper-thumbs-' . $sSwprOrient . ' swiper-initialized swiper-' . $sSwprOrient . ' swiper-pointer-events swiper-autoheight swiper-backface-hidden js-lzl-ing' ) ) );
		$itemThumbs -> parentNode -> appendChild( $itemSwpr );
		$itemSwpr -> appendChild( $itemThumbs );

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		//$ctxProcess[ 'aCssCrit' ][ '@\\.flex-control-thumbs@' ] = true;

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
		function OnPostClean( evt )
		{
			var e = evt.target;

			e.querySelectorAll( \".swiper.js-lzl-ing\" ).forEach(
				function( eSub )
				{
					eSub.parentNode.removeChild( eSub );
				}
			);
		}

		seraph_accel_lzl_bjs.add(
			function()
			{
				d.addEventListener( \"DOMContentLoaded\", function() { if( window.jQuery ) jQuery( d ).on( \"wc-product-gallery-after-init\", OnPostClean ); }, { capture: true, passive: true } );
			}
		);
	}
)( document );
" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

// #######################################################################
// #######################################################################

?>