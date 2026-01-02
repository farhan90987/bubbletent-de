<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_diviMvImg( $ctx, &$ctxProcess, $settFrm, $doc, $xpath, &$adjusted, &$bDynSize )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_module ")]' ) as $itemContainer )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $itemContainer ) || !ContentProcess_IsItemInFragments( $ctxProcess, $itemContainer ) )
			continue;

		$itemClassId = _Divi_GetClassId( $itemContainer, array( 'et_pb_image', 'et_pb_menu' ) );
		if( $itemClassId === null )
			continue;

		$item = HtmlNd::FirstOfChildren( $xpath -> query( './/img[@data-et-multi-view]', $itemContainer ) );
		if( !$item )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-et-multi-view' ), true );
		$views = Gen::GetArrField( $dataSett, array( 'schema', 'attrs' ), array() );
		if( !$views )
			continue;

		//$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );
		HtmlNd::AddRemoveAttrClass( $item, array(), array( 'et_multi_view_hidden_image' ) );

		foreach( $views as $viewId => $attrs )
		{
			if( !is_array( $attrs ) )
				continue;

			$itemContView = $viewId === 'desktop' ? $item : $item -> cloneNode( true );
			$itemContView -> setAttribute( 'data-et-multi-view-id', $viewId );

			foreach( $attrs as $attrKey => $attrVal )
				$itemContView -> setAttribute( $attrKey, $attrVal );

			$dataSettCopy = Gen::ArrCopy( $dataSett );
			Gen::SetArrField( $dataSettCopy, array( 'schema', 'attrs' ), array( $viewId => array() ) );
			$itemContView -> setAttribute( 'data-et-multi-view', @json_encode( $dataSettCopy ) );
			unset( $dataSettCopy );

			if( $item !== $itemContView )
				$item -> parentNode -> appendChild( $itemContView );
		}

		if( $itemStyleCont = _Divi_GetMultiViewStyle( $views, $itemClassId, false ) )
		{
			//$itemStyleCont .= '
			//    .et_pb_module.' . $itemClassId . ' [data-et-multi-view]
			//    { animation-name: none; }
			//';

			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
			$item -> parentNode -> insertBefore( $itemStyle, $item );
		}

		$adjusted = true;
	}
}

function _ProcessCont_Cp_diviMvText( $ctx, &$ctxProcess, $settFrm, $doc, $xpath, &$adjusted, &$bDynSize )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_module ")][contains(concat(" ",normalize-space(@class)," ")," et_pb_text ")]' ) as $itemContainer )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $itemContainer ) || !ContentProcess_IsItemInFragments( $ctxProcess, $itemContainer ) )
			continue;

		$itemClassId = _Divi_GetClassId( $itemContainer, 'et_pb_text' );
		if( $itemClassId === null )
			continue;

		$item = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-et-multi-view]', $itemContainer ) );
		if( !$item )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-et-multi-view' ), true );
		$views = Gen::GetArrField( $dataSett, array( 'schema', 'content' ), array() );
		if( !$views )
			continue;

		HtmlNd::CleanChildren( $item );

		foreach( $views as $viewId => $cont )
		{
			if( !is_string( $cont ) )
				continue;

			if( !( $itemContView = HtmlNd::ParseAndImport( $doc, Ui::Tag( 'div', $cont ) ) ) )
				continue;

			$itemContView -> setAttribute( 'data-et-multi-view-id', $viewId );
			$itemContView -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemContView -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );
			$item -> appendChild( $itemContView );
		}

		if( $itemStyleCont = _Divi_GetMultiViewStyle( $views, $itemClassId, true ) )
		{
			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
			$item -> parentNode -> insertBefore( $itemStyle, $item );
		}

		$adjusted = true;
	}
}

function _ProcessCont_Cp_diviMvSld( $ctx, &$ctxProcess, $settFrm, $doc, $xpath, &$adjusted, &$bDynSize )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_module ")][contains(concat(" ",normalize-space(@class)," ")," et_pb_slider ")]' ) as $itemContainer )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $itemContainer ) || !ContentProcess_IsItemInFragments( $ctxProcess, $itemContainer ) )
			continue;

		$itemClassId = _Divi_GetClassId( $itemContainer, 'et_pb_slider' );
		if( $itemClassId === null )
			continue;

		$item = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-et-multi-view]', $itemContainer ) );
		if( !$item )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-et-multi-view' ), true );
		$views = Gen::GetArrField( $dataSett, array( 'schema', 'content' ), array() );
		if( !$views )
			continue;

		HtmlNd::CleanChildren( $item );

		foreach( $views as $viewId => $cont )
		{
			if( !is_string( $cont ) )
				continue;

			if( !( $itemContView = HtmlNd::ParseAndImport( $doc, Ui::Tag( 'div', $cont ) ) ) )
				continue;

			$itemContView -> setAttribute( 'data-et-multi-view-id', $viewId );
			$itemContView -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemContView -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );
			$item -> appendChild( $itemContView );
		}

		if( $itemStyleCont = _Divi_GetMultiViewStyle( $views, $itemClassId, true ) )
		{
			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
			$item -> parentNode -> insertBefore( $itemStyle, $item );
		}

		$adjusted = true;
	}
}

function _ProcessCont_Cp_diviMvFwHdr( $ctx, &$ctxProcess, $settFrm, $doc, $xpath, &$adjusted, &$bDynSize )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_module ")][contains(concat(" ",normalize-space(@class)," ")," et_pb_fullwidth_header ")]' ) as $itemContainer )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $itemContainer ) || !ContentProcess_IsItemInFragments( $ctxProcess, $itemContainer ) )
			continue;

		$itemClassId = _Divi_GetClassId( $itemContainer, 'et_pb_fullwidth_header' );
		if( $itemClassId === null )
			continue;

		HtmlNd::AddRemoveAttrClass( $itemContainer, 'lzl_cs' );

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_divi_calcSizes(document.currentScript.parentNode);' );
			$itemContainer -> insertBefore( $itemScript, $itemContainer -> firstChild );
		}

		$adjusted = true;
		$bDynSize = true;
	}
}

function _ProcessCont_Cp_diviMv_Finalize( $ctx, &$ctxProcess, $settFrm, $doc, $xpath, $adjusted, $bDynSize )
{
	if( $adjusted )
	{
		if( stripos( $ctxProcess[ 'userAgent' ], 'mobile' ) !== false )
		{
			HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], array( 'et_mobile_device'/*, 'et_mobile_device_not_ipad'*/ ) );
			//if( stripos( $ctxProcess[ 'userAgent' ], 'ipad' ) === false )
			//    HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], 'et_mobile_device_not_ipad' );
		}
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $bDynSize )
	{
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '
					/* Full Width Header */
					.et_pb_module.et_pb_fullwidth_header.et_pb_fullscreen:not(.et_multi_view_swapped),
					.et_pb_module.et_pb_fullwidth_header.et_pb_fullscreen:not(.et_multi_view_swapped) .et_pb_fullwidth_header_container {
						min-height: calc(100vh - 1px*var(--lzl-corr-y));
					}
				' );
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
				function seraph_accel_cp_divi_calcSizes( e )
				{
					var dataMv; try { dataMv = JSON.parse( e.getAttribute( \"data-et-multi-view\" ) ); } catch( err ) {};

					if( dataMv && dataMv.schema && dataMv.schema.classes )
					{
						var viewid = e.clientWidth > 980 ? \"desktop\" : e.clientWidth > 767 ? \"tablet\" : \"phone\";

						var viewClasses = dataMv.schema.classes[ viewid ];
						if( viewClasses )
						{
							if( viewClasses.remove )
								e.classList.remove.apply( e.classList, viewClasses.remove );
							if( viewClasses.add )
								e.classList.add.apply( e.classList, viewClasses.add );
						}
					}

					e.style.setProperty( \"--lzl-corr-y\", e.getBoundingClientRect().y - e.ownerDocument.body.getBoundingClientRect().y );
				}

				(
					function( d )
					{
						function OnEvt( evt )
						{
							d.querySelectorAll( \".et_pb_module.lzl_cs\" ).forEach( seraph_accel_cp_divi_calcSizes );
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

// #######################################################################

function _ProcessCont_Cp_diviDsmGal( $ctx, &$ctxProcess, $settFrm, $doc, $xpath, &$adjusted, &$bDynSize )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," dsm-gallery ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$aImage = array();
		$itemImgContainerIdx = -1;
		foreach( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," grid-item ")]', $item ) as $itemImgContainer )
		{
			$itemImgContainerIdx++;

			$itemImg = HtmlNd::FirstOfChildren( $xpath -> query( './/img', $itemImgContainer ) );
			if( !$itemImg )
				continue;

			$aImage[] = ( object )array( 'nd' => $itemImgContainer, 'sz' => ( object )array( 'cx' => ( int )$itemImg -> getAttribute( 'width' ), 'cy' => ( int )$itemImg -> getAttribute( 'height' ) ), 'cssChildIdx' => $itemImgContainerIdx + 1 );
		}

		if( !$aImage )
			continue;

		$layout = 'masonry';

		if( $layout == 'masonry' )
		{
			$nCols = 3;
			$margin = 12;

			$aCol = array();
			for( $iCol = 0; $iCol < $nCols; $iCol++ )
				$aCol[ $iCol ] = array( 'a' => array(), 'cy' => 0 );

			$colDefWidth = 100;
			$iCol = 0;
			foreach( $aImage as $image )
			{
				//$iRow = count( $aCol[ $iCol ][ 'a' ] );
				$cy = $image -> sz -> cx ? ( int )round( ( $image -> sz -> cy ) * ( ( float )$colDefWidth / $image -> sz -> cx ) ) : 0;
				$aCol[ $iCol ][ 'a' ][] = array( 'image' => $image/*, 'cy' => $cy*/, 'y' => $aCol[ $iCol ][ 'cy' ] );
				$aCol[ $iCol ][ 'cy' ] += $cy;

				$iCol++;
				if( $iCol == $nCols )
					$iCol = 0;
			}

			$cyTotal = 0;
			foreach( $aCol as $col )
			{
				if( $col[ 'cy' ] > $cyTotal )
				{
					$cyTotal = $col[ 'cy' ];
				}
			}

			foreach( $aCol as $iCol => $col )
			{
				foreach( $col[ 'a' ] as $iRow => $row )
				{
					$row[ 'image' ] -> nd -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $row[ 'image' ] -> nd -> getAttribute( 'style' ) ), array( 'position' => 'absolute', 'left' => 'calc(' . ( ( float )$iCol * 100 / $nCols ) . '% + ' . ( $margin * $iCol / $nCols ) . 'px)', 'top' => ( ( float )$row[ 'y' ] * 100 / ( $cyTotal ? $cyTotal : 1 ) ) . '%' ) ) ) );
				}
			}

			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'position' => 'relative', 'padding-bottom' => ( float )$cyTotal * 100 / ( $colDefWidth * $nCols ) . '%' ) ) ) );
		}
	}
}

// #######################################################################

function _ProcessCont_Cp_diviMv( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[@data-et-multi-view]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_diviMv_calcSizes_init(document)' );
			$item -> appendChild( $itemScript );
		}

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.et_multi_view_swapped@' ] = true;

		{
			/*
			
			
			
			



			*/

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "function seraph_accel_cp_diviMv_calcSizes_init( d )
{
	var e = d.currentScript.parentNode; e.removeChild( d.currentScript );
	seraph_accel_cp_diviMv_calcSizes( e );
}

function seraph_accel_cp_diviMv_calcSizes( e )
{
	function getScreenMode()
	{
		var width = document.documentElement.clientWidth;
		if( width >= 980 + 1 )
			return( \"desktop\" );
		if( width >= 767 + 1 )
			return( \"tablet\" );
		return( \"phone\" );
	}

	function chooseMode( screenMode, data )
	{
		if( typeof( data[ screenMode ] ) === \"string\" )
			return( screenMode );

		if( screenMode == \"tablet\" )
			return( typeof( data[ \"desktop\" ] ) === \"string\" ? \"desktop\" : \"phone\" );
		if( screenMode == \"phone\" )
			return( typeof( data[ \"desktop\" ] ) === \"string\" ? \"tablet\" : \"desktop\" );
		return( typeof( data[ \"desktop\" ] ) === \"string\" ? \"tablet\" : \"phone\" );
	}
	
	var screenMode = getScreenMode();

	var dataMv; try { dataMv = JSON.parse( e.getAttribute( \"data-et-multi-view\" ) ); } catch( err ) {};
	//   console.log(e.getTag, dataMv);

	var dataMvContent = dataMv?.schema?.content;
	if( dataMvContent )
		e.innerHTML = dataMvContent[ chooseMode( screenMode, dataMvContent ) ];

	var dataMvAttrs = dataMv?.schema?.attrs ?? {};
	var a = dataMvAttrs[ chooseMode( screenMode, dataMvAttrs ) ];
	if( a && typeof( a ) === \"object\" )
		for( var name in a )
			e.setAttribute( name, a[ name ] );
	
	e.classList.add( \"et_multi_view_swapped\" );
}

(
	function( d )
	{
		function OnEvt( evt )
		{
			d.querySelectorAll( \"[data-et-multi-view]\" ).forEach( seraph_accel_cp_diviMv_calcSizes );
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
	}
}

function _ProcessCont_Cp_diviSld( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_module ")][contains(concat(" ",normalize-space(@class)," ")," et_pb_slider ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		HtmlNd::AddRemoveAttrClass( $item, array( 'js-lzl-ing' ) );

		$itemControllers = HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'et-pb-controllers', 'js-lzl' ) ) );
		$nSld = 0;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_slide ")]', $item ) as $itemSld )
		{
			$nSld ++;
			$itemController = HtmlNd::CreateTag( $doc, 'a', array( 'href' => '#', 'class' => $nSld == 1 ? 'et-pb-active-control' : null ) );
			$itemController -> appendChild( $doc -> createTextNode( ( string )$nSld ) );
			$itemControllers -> appendChild( $itemController );
		}

		if( $nSld > 1 )
		{
			//$item -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'et-pb-slider-arrows', 'js-lzl' ) ), array(
			//    HtmlNd::CreateTag( $doc, 'a', array( 'href' => '#', 'class' => 'et-pb-arrow-prev' ), array(  ) ),
			//) ) );
			$item -> appendChild( $itemControllers );
		}

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_diviSld_calcSizes_init(document)' );
			$item -> appendChild( $itemScript );
		}

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		//$ctxProcess[ 'aCssCrit' ][ '@\\.et_multi_view_swapped@' ] = true;

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle,
				".et_pb_slider.js-lzl-ing .et_pb_slides {\r\n\tdisplay: flex;\r\n}\r\n\r\n.et_pb_slider.js-lzl-ing .et_pb_slide {\r\n\tdisplay: block;\r\n}\r\n\r\n.et_pb_slider.js-lzl-ing .et_pb_slide:not(:first-child) {\r\n\tvisibility: hidden;\r\n}\r\n\r\n.et-pb-controllers.js-lzl ~ .et-pb-controllers {\r\n\tdisplay: none !important;\r\n}"
			);
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		{
			/*
			
			
			
			



			*/
			
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "function seraph_accel_cp_diviSld_calcSizes_init( d )
{
	var e = d.currentScript.parentNode; e.removeChild( d.currentScript );
	seraph_accel_cp_diviSld_calcSizes( e );
}

function seraph_accel_cp_diviSld_calcSizes( e )
{
	var h = e.getBoundingClientRect().height;

	e.querySelectorAll( \".et_pb_slide > .et_pb_container\" ).forEach(
		function( ec )
		{
			ec.style.setProperty( \"height\", \"\" + h + \"px\" );
		}
	);
}

(
	function( d, sel )
	{
		function OnEvt( evt )
		{
			d.querySelectorAll( sel ).forEach( seraph_accel_cp_diviSld_calcSizes );
		}

		d.addEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
		seraph_accel_lzl_bjs.add(
			function()
			{
				d.querySelectorAll( sel ).forEach( function( e ) { e.classList.remove( \"js-lzl-ing\" ); } );
				d.removeEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
			}
		);
	}
)( document, \".et_pb_module.et_pb_slider\" );
" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

// #######################################################################

function _ProcessCont_Cp_diviVidBox( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_video_box ")]/iframe' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( '--width' => $item -> getAttribute( 'width' ), '--height' => $item -> getAttribute( 'height' ) ) ) ) );
		HtmlNd::RenameAttr( $item, 'src', 'data-lzl-src' );
		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '
					.et_pb_video_box > iframe
					{
						height: 0;
						padding-top: calc(var(--height) / var(--width) * 100%);
					}
				' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, '
					seraph_accel_lzl_bjs.add(
						function()
						{
							document.querySelectorAll( ".et_pb_video_box>iframe" ).forEach( function( i ){ i.src = i.getAttribute( "data-lzl-src" ) } );
						}
					);
				' );
			$ctxProcess[ 'ndBody' ] -> appendChild( $itemScript );
		}
	}
}

function _ProcessCont_Cp_diviVidBg( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_section_video_bg ")]/video' ) as $item )
	{
		HtmlNd::AddRemoveAttrClass( $item -> parentNode, array( 'et_pb_section_video_bg_js_lzl' ), array( 'et_pb_section_video_bg' ) );
		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '
					.et_pb_section_video_bg_js_lzl video {
						width: 100%;
						object-fit: cover;
						height: 100%;
					}

					.et_pb_section_video_bg_js_lzl {
						position: absolute;
						top: 0;
						left: 0;
						width: 100%;
						height: 100%;
						overflow: hidden;
						display: block;
						pointer-events: none;
					}

					.iphone .et_pb_section_video_bg_js_lzl video::-webkit-media-controls-start-playback-button {
						display: none !important;
						-webkit-appearance: none;
					}

					.et_pb_column > .et_pb_section_video_bg_js_lzl {
						z-index: -1;
					}

					.et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_hover, .et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_phone, .et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_tablet, .et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_tablet_only {
						display: none;
					}

					.et_pb_section_video_on_hover:hover > .et_pb_section_video_bg_js_lzl {
						display: none;
					}

					@media (min-width: ' . ( 980 + 1 ) . 'px) {
						.et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_desktop_only {
							display: block;
						}
					}

					@media (max-width: ' . ( 980 ) . 'px) {
						.et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_tablet {
							display: block;
						}

						.et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_desktop_only {
							display: none;
						}
					}

					@media (min-width: ' . ( 767 + 1 ) . 'px) {
						.et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_desktop_tablet {
							display: block;
						}
					}

					@media (min-width: ' . ( 767 + 1 ) . 'px) and (max-width:' . ( 980 ) . 'px) {
						.et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_tablet_only {
							display: block;
						}
					}

					@media (max-width: ' . ( 767 ) . 'px) {
						.et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_phone {
							display: block;
						}

						.et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_desktop_tablet {
							display: none;
						}
					}
				' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _ProcessCont_Cp_diviVidFr( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_module ")]//*[not(self::node()[contains(concat(" ",normalize-space(@class)," ")," et_pb_video_box ")])]//iframe' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$size = ( int )$item -> getAttribute( 'width' );
		if( !$size )
			continue;

		if( in_array( 'et_pb_video_box', HtmlNd::GetAttrClass( $item -> parentNode ) ) )
			continue;

		$size = ( int )$item -> getAttribute( 'height' ) / $size;

		$itemWrapper = HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'fluid-width-video-wrapper' ), 'style' => array( 'padding-top' => ( string )( $size * 100 ) . '%' ) ) );
		$item -> parentNode -> insertBefore( $itemWrapper, $item );
		$itemWrapper -> appendChild( $item );
	}
}

function _ProcessCont_Cp_diviLzStls( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	if( $itemScr = HtmlNd::FirstOfChildren( $xpath -> query( './/body//script[contains(text(),"/et-divi-dynamic-")]' ) ) )
	{
		$styleInsertAfterId = '';
		if( preg_match( '@\\Wdocument\\s*\\.\\s*getElementById\\s*\\(\\s*[\'"]([^\'"]+)[\'"]@', $itemScr -> nodeValue, $m ) )
			$styleInsertAfterId = $m[ 1 ];
		$styleLazyId = '';
		if( preg_match( '@\\Wlink\\s*\\.\\s*id\\s*=\\s*[\'"]([^\'"]+)[\'"]@', $itemScr -> nodeValue, $m ) )
			$styleLazyId = $m[ 1 ];
		$styleLazyHref = '';
		if( preg_match( '@\\Wvar\\s*file\\s*=\\s*\\[\\s*[\'"]([^\'"]+)[\'"]@', $itemScr -> nodeValue, $m ) )
			$styleLazyHref = str_replace( '\\/', '/', $m[ 1 ] );

		if( $itemStyleInsertAfter = HtmlNd::FirstOfChildren( $xpath -> query( './/style[@id="' . $styleInsertAfterId . '"]' ) ) )
		{
			$itemStyleLazy = $doc -> createElement( 'link' );
			$itemStyleLazy -> setAttribute( 'rel', 'stylesheet' );
			$itemStyleLazy -> setAttribute( 'id', $styleLazyId );
			$itemStyleLazy -> setAttribute( 'href', $styleLazyHref );
			HtmlNd::InsertAfter( $itemStyleInsertAfter -> parentNode, $itemStyleLazy, $itemStyleInsertAfter );
			$itemScr -> parentNode -> removeChild( $itemScr );
		}

		unset( $itemScr );
	}
}

function _ProcessCont_Cp_diviAni( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_animation")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		HtmlNd::AddRemoveAttrClass( $item, array( 'et_pb_animation' ) );
		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.et-animated@' ] = true;

		{
			/*
			
			
			
			



			*/

			$ctx -> aAniAppear[ '.et_pb_animation:not(.et-animated)' ] = 'function( e )
				{
					e.classList.add( "et-animated" );
				}';
		}
	}
}

function _ProcessCont_Cp_diviDataAni( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$itemScrCfg = HtmlNd::FirstOfChildren( $xpath -> query( './/body//script[contains(text(),"et_animation_data")]' ) );
	if( !$itemScrCfg )
		return;

	@preg_match( '@var\\s+et_animation_data\\s+=\\s+(\\[.*?\\])@', $itemScrCfg -> nodeValue, $m );
	if( !$m )
		return;

	$cfg = @json_decode( $m[ 1 ], true );
	if( !$cfg )
		return;

	$contStyle = '';

	$adjusted = false;
	foreach( $cfg as $cfgI )
	{
		if( empty( $cfgI[ 'class' ] ) || empty( $cfgI[ 'style' ] ) || empty( $cfgI[ 'repeat' ] ) || empty( $cfgI[ 'duration'] ) || empty( $cfgI[ 'delay' ] ) || empty( $cfgI[ 'intensity' ] ) || empty( $cfgI[ 'starting_opacity' ] ) || empty( $cfgI[ 'speed_curve' ] ) )
			continue;

		$cfgI[ 'starting_opacity' ] = intval( $cfgI[ 'starting_opacity' ] ) / 100;
		$delay = intval( $cfgI[ 'duration' ] ) + intval( $cfgI[ 'delay' ] );

		$adjustedI = false;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ' . $cfgI[ 'class' ] . ' ")]' ) as $item )
		{
			if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			HtmlNd::AddRemoveAttrClass( $item, array( 'dani-lzl' ) );
			$item -> setAttribute( 'data-dani-lzl-dur', $delay );
			$adjustedI = true;
		}

		if( !$adjustedI )
			continue;

		$adjusted = true;

    	$contStyle .= '.' . $cfgI[ 'class' ] . '.ing' . ' { animation-name: ' . $cfgI[ 'class' ] . '-dani-lzl' . '; animation-duration:  ' . $cfgI[ 'duration' ] . '; animation-delay: ' . $cfgI[ 'delay' ] . '; animation-timing-function: ' . $cfgI[ 'speed_curve' ] . '; }
    	@keyframes ' . $cfgI[ 'class' ] . '-dani-lzl' . ' { 0% { transform: ';

		$i = 'none';
		$n = intval( $cfgI[ 'intensity' ] );
		preg_match( '@(slide|zoom|flip|fold|roll|fade|bounce)(top|bottom|right|left|)@i', strtolower( $cfgI[ 'style' ] ), $style );

		switch( @$style[ 1 ] )
		{
		case "slide":
		    switch( @$style[ 2 ] )
		    {
		    case "top":
		        $i = "translate3d(0, " . ( -2 * $n ) . "%, 0)";
		        break;
		    case "right":
		        $i = "translate3d(" . ( 2 * $n ) . "%, 0, 0)";
		        break;
		    case "bottom":
		        $i = "translate3d(0, " . ( 2 * $n ) . "%, 0)";
		        break;
		    case "left":
		        $i = "translate3d(" . ( -2 * $n ) . "%, 0, 0)";
		        break;
		    default:
		        $a = .01 * ( 100 - $n );
		        $i = "scale3d(" . $a . ", " . $a . ", " . $a . ")";
		        break;
		    }
		    break;

		case "zoom":
		    $a = .01 * ( 100 - $n );
		    $i = "scale3d(" . $a . ", " . $a . ", " . $a . ")";
		    break;

		case "flip":
		    switch ( @$style[ 2 ] )
		    {
		    case "right":
		        $o = ceil( .9 * $n );
		        $i = "perspective(2000px) rotateY(" . $o . "deg)";
		        break;
		    case "left":
		        $o = -1 * ceil(.9 * $n);
		        $i = "perspective(2000px) rotateY(" . $o . "deg)";
		        break;
		    case "bottom":
		        $o = -1 * ceil(.9 * $n);
		        $i = "perspective(2000px) rotateX(" . $o . "deg)";
		        break;
		    case "top":
		    default:
		        $o = ceil(.9 * $n);
		        $i = "perspective(2000px) rotateX(" . $o . "deg)";
		        break;
		    }
		    break;

		case "fold":
		    switch ( @$style[ 2 ] )
			{
		    case "top":
		        $o = -1 * ceil( .9 * $n );
		        $i = "perspective(2000px) rotateX(" . $o . "deg)";
		        break;
		    case "bottom":
		        $o = ceil(.9 * $n);
		        $i = "perspective(2000px) rotateX(" . $o . "deg)";
		        break;
		    case "left":
		        $o = ceil(.9 * $n);
		        $i = "perspective(2000px) rotateY(" . $o . "deg)";
		        break;
		    default:
		        $o = -1 * ceil(.9 * $n);
		        $i = "perspective(2000px) rotateY(" . $o . "deg)";
		        break;
		    }
		    break;

		case "roll":
		    switch ( @$style[ 2 ] )
			{
		    case "right":
		    case "bottom":
		        $o = -1 * ceil( 3.6 * $n );
		        $i = "rotateZ(" . $o . "deg)";
		        break;
		    case "top":
		    case "left":
		    default:
		        $o = ceil( 3.6 * $n );
		        $i = "rotateZ(" . $o . "deg)";
		        break;
		    }

		default:
			$i = "none";
			break;
		}

    	$contStyle .= $i . '; opacity: ' . $cfgI[ 'starting_opacity' ] . ';} 100% { transform: none; opacity: 1;} }';
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ "@\\.ing(?:[^\\-\\w]|$)@" ] = true;
		$ctxProcess[ 'aCssCrit' ][ "@\\.ed(?:[^\\-\\w]|$)@" ] = true;

		if( $contStyle )
		{
			$contStyle .= '.dani-lzl.ed, .lzl-sticky .dani-lzl { animation: none !important; transform: none !important; opacity: 1 !important; }';

			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $contStyle );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemStyle );
		}

		{
			/*
			
			
			
			



			*/

			$ctx -> aAniAppear[ '.dani-lzl:not(.ing,.ed)' ] = 'function( e )
{
	e.classList.add( "ing" );

	setInterval(
		function()
		{
			e.classList.add( "ed" );
			e.classList.remove( "ing" );
		}
	, parseInt( e.getAttribute( "data-dani-lzl-dur" ), 10 ) );
}';
		}
	}
}

function _ProcessCont_Cp_diviStck( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	if( !HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_sticky_module ")]' ) ) )
		return;

	$itemScrCfg = HtmlNd::FirstOfChildren( $xpath -> query( './/body//script[contains(text(),"et_pb_sticky_elements")]' ) );
	if( !$itemScrCfg )
		return;

	$posStart = array();
	if( !preg_match( '@var\\s+et_pb_sticky_elements\\s*=\\s*{@', $itemScrCfg -> nodeValue, $posStart, PREG_OFFSET_CAPTURE ) )
		return;

	$posStart = $posStart[ 0 ][ 1 ] + strlen( $posStart[ 0 ][ 0 ] ) - 1;
	$pos = Gen::JsonGetEndPos( $posStart, $itemScrCfg -> nodeValue );
	if( $pos === null )
		return;

	$cfg = @json_decode( Gen::JsObjDecl2Json( substr( $itemScrCfg -> nodeValue, $posStart, $pos - $posStart ) ), true );
	if( $cfg === null )
		return;

	$adjusted = false;
	foreach( Gen::GetArrField( $cfg, array( '' ), array() ) as $id => $cfgItem )
	{
		if( !$ctx -> cnvCssSel2Xpath )
			$ctx -> cnvCssSel2Xpath = StyleProcessor::createCnvCssSel2Xpath();

		$selItem = StyleProcessor::cssSelToXPathEx( $ctx -> cnvCssSel2Xpath, Gen::GetArrField( $cfgItem, array( 'selector' ), '' ) );
		if( !$selItem )
			continue;

		$item = HtmlNd::FirstOfChildren( $xpath -> query( $selItem ) );
		if( !$item )
			continue;

		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$item -> setAttribute( 'data-lzl-stck', $id );

		$itemSticky = $item -> cloneNode( true );
		foreach( HtmlNd::ChildrenAsArr( $itemSticky -> getElementsByTagName( 'script' ) ) as $itemScr )
			$itemScr -> parentNode -> removeChild( $itemScr );
		for( $itemStickyChild = null; $itemStickyChild = HtmlNd::GetNextTreeChild( $itemSticky, $itemStickyChild, true ); )
		{
			if( $itemStickyChild -> nodeType != XML_ELEMENT_NODE )
				continue;

			$itemStickyChild -> removeAttribute( 'data-order_class' );
			if( $itemStickyChild -> hasAttribute( 'id' ) )
				$itemStickyChild -> setAttribute( 'id', $itemStickyChild -> getAttribute( 'id' ) . '-lzl' );
		}
		HtmlNd::AddRemoveAttrClass( $itemSticky, array( 'js-lzl-ing' ) );
		HtmlNd::InsertBefore( $item -> parentNode, $itemSticky, $item );

		Gen::SetArrField( $cfg, array( $id, 'selector' ), Gen::GetArrField( $cfgItem, array( 'selector' ), '' ) . ':not(.js-lzl-ing)' );

		$adjusted = true;
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && $adjusted )
	{
		$itemScrCfg -> nodeValue = substr_replace( $itemScrCfg -> nodeValue, @json_encode( $cfg ), $posStart, $pos - $posStart );

		$ctxProcess[ 'aCssCrit' ][ '@\\.et_pb_sticky@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.lzl-sticky@' ] = true;

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '[data-lzl-stck].js-lzl-ing.lzl-sticky {
	position: fixed;
	width: 100%;
	margin-top: 0px;
	margin-bottom: 0px;
	top: 0px;
	z-index: 99;
}

[data-lzl-stck].js-lzl-ing:not(.lzl-sticky),
body:not(.seraph-accel-js-lzl-ing) [data-lzl-stck].js-lzl-ing {
	display: none !important;
}

body.seraph-accel-js-lzl-ing [data-lzl-stck]:not(.js-lzl-ing).lzl-sticky {
	visibility: hidden !important;
}' );

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
				function seraph_accel_cp_diviStck_calcSizes( e )
				{
					if( e.classList.contains( \"et_pb_sticky_placeholder\" ) )
						return;

					function _Activate( e, bActivate = true )
					{
						var cfgItem = et_pb_sticky_elements[ e.getAttribute( \"data-lzl-stck\" ) ];
						if( !cfgItem )
							return;

						var eSticky = e.previousElementSibling;
						if( !eSticky || !eSticky.classList.contains( \"js-lzl-ing\" ) )
							return;

						if( bActivate )
						{
							if( !e.classList.contains( \"lzl-sticky\" ) )
							{
								e.classList.add( \"lzl-sticky\" );

								eSticky.classList.add( \"lzl-sticky\" );
								eSticky.classList.add( \"et_pb_sticky\" );
								eSticky.classList.add( \"et_pb_sticky--\" + String( cfgItem.position ) );
							}
						}
						else
						{
							if( e.classList.contains( \"lzl-sticky\" ) )
							{
								e.classList.remove( \"lzl-sticky\" );

								eSticky.classList.remove( \"lzl-sticky\" );
								eSticky.classList.remove( \"et_pb_sticky\" );
								eSticky.classList.remove( \"et_pb_sticky--\" + String( cfgItem.position ) );
							}
						}
					}

					_Activate( e, e.getBoundingClientRect().top < 0 );
				}

				(
					function( d )
					{
						var bProcess = true;

						function OnEvt( evt )
						{
							d.querySelectorAll( \"[data-lzl-stck]:not(.js-lzl-ing)\" ).forEach(
								function( e )
								{
									seraph_accel_cp_diviStck_calcSizes( e );
								}
							);
						}

						d.addEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
						d.addEventListener( \"scroll\", OnEvt, { capture: true, passive: true } );

						d.addEventListener( \"seraph_accel_jsFinish\",
						    function( evt )
						    {
						        d.querySelectorAll( \"[data-lzl-stck].js-lzl-ing\" ).forEach(
						            function( eSticky )
						            {
						                eSticky.remove();
						            }
						        );

								d.removeEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
								d.removeEventListener( \"scroll\", OnEvt, { capture: true, passive: true } );
						    }
						, { capture: true, passive: true } );

						//seraph_accel_lzl_bjs.add(
						//    function()
						//    {
						//        // On \"DOMContentLoaded\" DIVI initializes sticky elements - so, use it to minimize drawing break
						//        d.addEventListener( \"DOMContentLoaded\",
						//            function()
						//            {
						//                d.querySelectorAll( \"[data-lzl-stck]\" ).forEach(
						//                    function( e )
						//                    {
						//                        e.classList.remove( \"js-lzl-ing\" );
						//                        e.classList.remove( \"lzl-sticky\" );
						//                    }
						//                );

						//                d.removeEventListener( \"seraph_accel_calcSizes\", OnEvt, { capture: true, passive: true } );
						//                d.removeEventListener( \"scroll\", OnEvt, { capture: true, passive: true } );
						//            }
						//        , { capture: true, passive: true } );
						//    }
						//);
			}
			)( document );
			" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}

		{
			$itemScrCfg -> setAttribute( 'seraph-accel-crit', '1' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemScrCfg );
		}
	}
}

function _ProcessCont_Cp_diviPrld( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	// /wp-content/_HtmlFullProcess_Temp.ORIG/hydrographics.ch/wp-content/themes/Divi/js/scripts.min@ver-4.23.4.js: function Wt()
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_preload")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		HtmlNd::AddRemoveAttrClass( $item, array(), array( 'et_pb_preload' ) );
	}
}

function _ProcessCont_Cp_diviHdr( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	if( ( $ctxProcess[ 'mode' ] & 1 ) && ( $item = HtmlNd::FirstOfChildren( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," et_divi_theme ")][contains(concat(" ",normalize-space(@class)," ")," et_fixed_nav ")]//*[@id="main-header"]' ) ) ) )
	{
		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_diviHdr_calcSizes(document);' );
			$item -> appendChild( $itemScript );
		}

		{
			/*$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '' );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );*/
		}

		{
			/*
			
			
			
			



			*/

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "
				function seraph_accel_cp_diviHdr_calcSizes( d )
				{
					var mainHeader = d.querySelector( \"#main-header\" );
					var topHeader = d.querySelector( \"#top-header\" );
					var pageContainer = d.querySelector( \"#page-container\" );

					mainHeader.style.setProperty( \"top\", ( topHeader ? topHeader.clientHeight : 0 ) + \"px\" );
					if( pageContainer )
						pageContainer.style.setProperty( \"padding-top\", ( ( mainHeader.clientHeight - mainHeader.clientHeight ) + ( topHeader ? topHeader.clientHeight : 0 ) + mainHeader.clientHeight ) + \"px\" );
				}

				(
					function( d )
					{
						function onEvt( e )
						{
							seraph_accel_cp_diviHdr_calcSizes( d );
						}

						d.addEventListener( \"seraph_accel_calcSizes\", onEvt, { capture: true, passive: true } );
						seraph_accel_lzl_bjs.add( function() { d.removeEventListener( \"seraph_accel_calcSizes\", onEvt, { capture: true, passive: true } ); } );
					}
				)( document );
			" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

// #######################################################################

function _Divi_GetClassId( $item, $aClassType )
{
	$classes = $item -> getAttribute( 'class' );
	if( !is_string( $classes ) )
		return( null );

	$classes = ' ' . $classes . ' ';

	$found = null;
	foreach( ( array )$aClassType as $classType )
	{
		$m = array();
		if( !@preg_match( '@\\s(' . $classType . '_\\d+[^\\s]*)\\s@', $classes, $m ) )
			continue;

		$found = $m[ 1 ];
		break;
	}

	return( $found );
}

function _Divi_GetMultiViewStyle( $views, $itemClassId, $full )
{
	$ctx = new AnyObj();
	$ctx -> itemClassId = $itemClassId;
	$ctx -> full = $full;
	$ctx -> cb =
		function( $ctx, $views, $viewId )
		{
			$res = '.et_pb_module.' . $ctx -> itemClassId;
			if( $ctx -> full )
				return( $res . ' [data-et-multi-view]:not(.et_multi_view_swapped), .et_pb_module.' . $ctx -> itemClassId . ' [data-et-multi-view]:not(.et_multi_view_swapped) > [data-et-multi-view-id="' . $viewId . '"]{ display:unset!important; }' );
			return( $res . ' [data-et-multi-view-id]:not([data-et-multi-view-id="' . $viewId . '"]){ display:none!important; }' );
		};

	return( _Divi_GetMultiViewStyleEx( $views, array( $ctx, 'cb' ) ) );
}

function _Divi_GetMultiViewStyleEx( $views, $cbStyle )
{
	static $g_aEtPbMaxSizes = array( 'phone' => 767, 'tablet' => 980 );

	$itemStyleCont = '';
	if( isset( $views[ 'phone' ] ) && isset( $views[ 'tablet' ] ) && isset( $views[ 'desktop' ] ) )
	{
		$itemStyleCont = '
			@media (max-width: ' . $g_aEtPbMaxSizes[ 'phone' ] . 'px)
			{
				' . call_user_func( $cbStyle, $views, 'phone' ) . '
			}

			@media (min-width: ' . ( $g_aEtPbMaxSizes[ 'phone' ] + 1 ) . 'px) and (max-width: ' . $g_aEtPbMaxSizes[ 'tablet' ] . 'px)
			{
				' . call_user_func( $cbStyle, $views, 'tablet' ) . '
			}

			@media (min-width: ' . ( $g_aEtPbMaxSizes[ 'tablet' ] + 1 ) . 'px)
			{
				' . call_user_func( $cbStyle, $views, 'desktop' ) . '
			}
		';
	}
	else if( isset( $views[ 'phone' ] ) && isset( $views[ 'desktop' ] ) )
	{
		$itemStyleCont = '
			@media (max-width: ' . $g_aEtPbMaxSizes[ 'phone' ] . 'px)
			{
				' . call_user_func( $cbStyle, $views, 'phone' ) . '
			}

			@media (min-width: ' . ( $g_aEtPbMaxSizes[ 'phone' ] + 1 ) . 'px)
			{
				' . call_user_func( $cbStyle, $views, 'desktop' ) . '
			}
		';
	}
	else if( isset( $views[ 'tablet' ] ) && isset( $views[ 'desktop' ] ) )
	{
		$itemStyleCont = '
			@media (max-width: ' . $g_aEtPbMaxSizes[ 'tablet' ] . 'px)
			{
				' . call_user_func( $cbStyle, $views, 'tablet' ) . '
			}

			@media (min-width: ' . ( $g_aEtPbMaxSizes[ 'tablet' ] + 1 ) . 'px)
			{
				' . call_user_func( $cbStyle, $views, 'desktop' ) . '
			}
		';
	}

	return( $itemStyleCont );
}

// #######################################################################
// #######################################################################

?>