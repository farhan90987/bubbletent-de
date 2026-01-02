<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_swiper_AdjustItem( $item, $aPrm, $ctx, &$ctxProcess, $doc, $xpath )
{
	$cmnStyle = '';

	$nItemsMax = ( int )($aPrm[ 'itemsMax' ]??0);
	foreach( $aPrm[ 'breakpoints' ] as $i => $dim )
	{
		$nItems = $dim[ 'slidesPerView' ];
		if( $nItems == 'auto' )
			continue;
		
		if( $nItemsMax && $nItems > $nItemsMax )
			$nItems = $nItemsMax;

		if( count( $aPrm[ 'breakpoints' ] ) > 1 || $dim[ 'minWidth' ] )
			$cmnStyle .= '@media ' . Ui::StyleMediaMinMax( $dim[ 'minWidth' ], $i + 1 == count( $aPrm[ 'breakpoints' ] ) ? null : ( $aPrm[ 'breakpoints' ][ $i + 1 ][ 'minWidth' ] - 1 ) ) . ' { ';

		$cmnStyle .= $aPrm[ 'cssIdSelPrefix' ] . ($aPrm[ 'cssSelContainer' ]??'.swiper-container') . ' { --lzl-swpr-n: ' . ( string )$nItems . '; }';
		$cmnStyle .= $aPrm[ 'cssIdSelPrefix' ] . ($aPrm[ 'cssSelContainer' ]??'.swiper-container') . ':not(' . ($aPrm[ 'cssSelContainerInited' ]??'.swiper-container-initialized') . ') ' . ($aPrm[ 'cssSelSlide' ]??'.swiper-slide') . ':nth-child(n+' . ( string )( $nItems + 1 ) . ') { display: none; }';

		if( count( $aPrm[ 'breakpoints' ] ) > 1 || $dim[ 'minWidth' ] )
			$cmnStyle .= ' }';
	}

	$cmnStyle .= $aPrm[ 'cssIdSelPrefix' ] . ($aPrm[ 'cssSelContainer' ]??'.swiper-container') . ' { --lzl-swpr-sps: ' . ( string )($aPrm[ 'space' ]??10) . 'px; }';
	$cmnStyle .= $aPrm[ 'cssIdSelPrefix' ] . ($aPrm[ 'cssSelContainer' ]??'.swiper-container') . ':not(' . ($aPrm[ 'cssSelContainerInited' ]??'.swiper-container-initialized') . ') > ' . ( string )($aPrm[ 'cssSelNavPrev' ]??'.swiper-button-prev') . ' { display: none !important; }';

	if( ($aPrm[ 'isVert' ]??false) )
	{
		$cmnStyle .= $aPrm[ 'cssIdSelPrefix' ] . ($aPrm[ 'cssSelContainer' ]??'.swiper-container') . ':not(' . ($aPrm[ 'cssSelContainerInited' ]??'.swiper-container-initialized') . ') ' . ($aPrm[ 'cssSelWrapper' ]??'.swiper-wrapper') . ' { flex-direction: column; }';
		$cmnStyle .= $aPrm[ 'cssIdSelPrefix' ] . ($aPrm[ 'cssSelContainer' ]??'.swiper-container') . ':not(' . ($aPrm[ 'cssSelContainerInited' ]??'.swiper-container-initialized') . ') ' . ($aPrm[ 'cssSelSlide' ]??'.swiper-slide') . ' { margin-bottom: var(--lzl-swpr-sps) !important; height: calc((var(--lzl-swpr-sz) - var(--lzl-swpr-sps) * (var(--lzl-swpr-n) - 1)) / var(--lzl-swpr-n)) !important; width: 100% !important; }';
	}
	else
		$cmnStyle .= $aPrm[ 'cssIdSelPrefix' ] . ($aPrm[ 'cssSelContainer' ]??'.swiper-container') . ':not(' . ($aPrm[ 'cssSelContainerInited' ]??'.swiper-container-initialized') . ') ' . ($aPrm[ 'cssSelSlide' ]??'.swiper-slide') . ' { margin-right: var(--lzl-swpr-sps) !important; width: calc((var(--lzl-swpr-sz) - var(--lzl-swpr-sps) * (var(--lzl-swpr-n) - 1)) / var(--lzl-swpr-n)) !important; height: 100% !important; }';

	$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( '--lzl-swpr-sz' => '100%' ) ) ) );

	return( $cmnStyle );
}

// #######################################################################
// #######################################################################

?>