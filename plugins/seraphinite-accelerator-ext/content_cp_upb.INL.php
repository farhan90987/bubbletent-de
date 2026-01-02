<?php

namespace seraph_accel;

// 

// #######################################################################
// #######################################################################

function _ProcessCont_Cp_upbAni( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	if( !( $ctxProcess[ 'mode' ] & 1 ) )
		return;

	if( HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wpb_animate_when_almost_visible ")]' ) ) )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.wpb_start_animation@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.animated@' ] = true;

		/*
		
		
		
		



		*/

		$ctx -> aAniAppear[ '.wpb_animate_when_almost_visible:not(.wpb_start_animation)' ] = "function( e )
{
	e.classList.add( \"wpb_start_animation\" );
	e.classList.add( \"animated\" );
}";
	}
}

function _ProcessCont_Cp_upbBgImg( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	$nSepId = 1;

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," upb_bg_img ")][@data-ultimate-bg]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = $item -> getAttribute( 'data-ultimate-bg' );
		if( !$dataSett )
			continue;

		if( !( $itemRow = _Upb_GetNearestRow( $item ) ) )
			continue;

		$bgOverride = HtmlNd::GetAttr( $item, 'data-bg-override' );
		$themeSupport = HtmlNd::GetAttr( $item, 'data-theme-support' );

		//$itemRow -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemRow -> getAttribute( 'style' ) ), array( 'height' => $bgOverride == 'browser_size' ? '100vh' : null ) ) ) );

		HtmlNd::AddRemoveAttrClass( $item, array( 'upb_row_bg', HtmlNd::GetAttr( $item, 'data-ultimate-bg-style' ) ), array( 'upb_bg_img' ) );
		if( $item -> getAttribute( 'data-overlay' ) == 'true' )
			$item -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'upb_bg_overlay', 'style' => array( 'background-color' => HtmlNd::GetAttr( $item, 'data-overlay-color' ) ) ) ) );
		if( $item -> getAttribute( 'data-theme-support' ) === '' )
			$item -> removeAttribute( 'data-theme-support' );
		$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'background-size' => HtmlNd::GetAttr( $item, 'data-bg-img-size' ), 'background-repeat' => HtmlNd::GetAttr( $item, 'data-bg-img-repeat' ), 'background-position' => HtmlNd::GetAttr( $item, 'data-bg-img-position' ), 'background-image' => HtmlNd::GetAttr( $item, 'data-ultimate-bg' ), 'background-color' => 'rgba(0, 0, 0, 0)', 'background-attachment' => HtmlNd::GetAttr( $item, 'data-bg_img_attach' ) ) ) ) );

		if( $bgOverride == 'browser_size' )
			$itemRow -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'upb-background-text-wrapper', 'full-browser-size' ), 'style' => array( 'height' => '100vh' ) ), array( HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'upb-background-text vc_row wpb_row vc_row-fluid vc_row-o-equal-height vc_row-o-content-middle vc_row-flex' ), HtmlNd::ChildrenAsArr( $itemRow -> childNodes ) ) ) ) );

		$itemRow -> insertBefore( $item, $itemRow -> firstChild );
		HtmlNd::AddRemoveAttrClass( $itemRow, Ui::ParseClassAttr( $item -> getAttribute( 'data-hide-row' ) ) );
		$itemRow -> setAttribute( 'data-rtl', $item -> getAttribute( 'data-rtl' ) );
		$itemRow -> setAttribute( 'data-row-effect-mobile-disable', $item -> getAttribute( 'data-row-effect-mobile-disable' ) );
		$itemRow -> setAttribute( 'data-img-parallax-mobile-disable', $item -> getAttribute( 'data-img-parallax-mobile-disable' ) );

		// wp-content/plugins/Ultimate_VC_Addons/assets/min-js/ultimate_bg.min.js: "vc_row-has-fill"
		if( $themeSupport !== null && $themeSupport !== 'enable' )
			$itemContainer = null;	// MBI!!!!!!!!!!!!!!!
		else
			$itemContainer = $item -> parentNode;
		HtmlNd::AddRemoveAttrClass( $itemContainer, array( 'vc_row-has-fill' ) );

		// Separator
		if( $item -> getAttribute( 'data-seperator' ) == 'true' )
		{
			// wp-content/plugins/Ultimate_VC_Addons/assets/min-js/ultimate_bg.min.js: "seperator-type"

			$o = $item->getAttribute("data-seperator-type");
			$s = (int)$item->getAttribute("data-seperator-shape-size");
			$i = $item->getAttribute("data-seperator-background-color");
			$l = $item->getAttribute("data-seperator-border");
			$d = $item->getAttribute("data-seperator-border-color");
			$n = $item->getAttribute("data-seperator-border-width");
			$p = $item->getAttribute("data-seperator-svg-height");
			$c = $item->getAttribute("data-seperator-full-width");
			$u = HtmlNd::GetAttr($item,"data-seperator-position");
			if($u===null)
				$u = "top_seperator";
			$v = HtmlNd::GetAttr($item,"data-icon");
			$v = null === $v ? "" : '<div class="separator-icon">' . $v . "</div>";
			$h = $seperator_class = $seperator_border_css = $seperator_border_line_css = $seperator_css = "";

			$_ = $shape_css = $svg = $inner_html = $seperator_css = "";
			$t = !1;
			$b = "uvc-seperator-" . $nSepId++;
			$g;
			$m = $s / 2;
			$e = 0;
			if ("triangle_seperator" == $o)
				$seperator_class = "ult-trinalge-seperator";
			else if ("circle_seperator" == $o)
				$seperator_class = "ult-circle-seperator";
			else if ("diagonal_seperator" == $o)
				$seperator_class = "ult-double-diagonal";
			else if ("triangle_svg_seperator" == $o)
			{
				$seperator_class = "ult-svg-triangle";
				$svg = '<svg class="uvc-svg-triangle" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 0.156661 0.1"><polygon points="0.156661,3.93701e-006 0.156661,0.000429134 0.117665,0.05 0.0783307,0.0999961 0.0389961,0.05 -0,0.000429134 -0,3.93701e-006 0.0783307,3.93701e-006 "/></svg>';
				$t = !0;
			}
			else if ("circle_svg_seperator" == $o)
			{
				$seperator_class = "ult-svg-circle";
				$svg = '<svg class="uvc-svg-circle" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 0.2 0.1"><path d="M0.200004 0c-3.93701e-006,0.0552205 -0.0447795,0.1 -0.100004,0.1 -0.0552126,0 -0.0999921,-0.0447795 -0.1,-0.1l0.200004 0z"/></svg>';
				$t = !0;
			}
			else if ("xlarge_triangle_seperator" == $o)
			{
				$seperator_class = "ult-xlarge-triangle";
				$svg = '<svg class="uvc-x-large-triangle" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 4.66666 0.333331" preserveAspectRatio="none"><path class="fil0" d="M-0 0.333331l4.66666 0 0 -3.93701e-006 -2.33333 0 -2.33333 0 0 3.93701e-006zm0 -0.333331l4.66666 0 0 0.166661 -4.66666 0 0 -0.166661zm4.66666 0.332618l0 -0.165953 -4.66666 0 0 0.165953 1.16162 -0.0826181 1.17171 -0.0833228 1.17171 0.0833228 1.16162 0.0826181z"/></svg>';
				$t = !0;
			}
			else if ("xlarge_triangle_left_seperator" == $o)
			{
				$seperator_class = "ult-xlarge-triangle-left";
				$svg = '<svg class="uvc-x-large-triangle-left" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 2000 90" preserveAspectRatio="none"><polygon xmlns="http://www.w3.org/2000/svg" points="535.084,64.886 0,0 0,90 2000,90 2000,0 "></polygon></svg>';
				$t = !0;
			}
			else if ("xlarge_triangle_right_seperator" == $o)
			{
				$seperator_class = "ult-xlarge-triangle-right";
				$svg = '<svg class="uvc-x-large-triangle-right" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 2000 90" preserveAspectRatio="none"><polygon xmlns="http://www.w3.org/2000/svg" points="535.084,64.886 0,0 0,90 2000,90 2000,0 "></polygon></svg>';
				$t = !0;
			}
			else if ("xlarge_circle_seperator" == $o)
			{
				$seperator_class = "ult-xlarge-circle";
				$svg = '<svg class="uvc-x-large-circle" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 4.66666 0.333331" preserveAspectRatio="none"><path class="fil1" d="M4.66666 0l0 7.87402e-006 -3.93701e-006 0c0,0.0920315 -1.04489,0.166665 -2.33333,0.166665 -1.28844,0 -2.33333,-0.0746339 -2.33333,-0.166665l-3.93701e-006 0 0 -7.87402e-006 4.66666 0z"/></svg>';
				$t = !0;
			}
			else if ("curve_up_seperator" == $o)
			{
				$seperator_class = "ult-curve-up-seperator";
				$svg = '<svg class="curve-up-inner-seperator uvc-curve-up-seperator" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 4.66666 0.333331" preserveAspectRatio="none"><path class="fil0" d="M-7.87402e-006 0.0148858l0.00234646 0c0.052689,0.0154094 0.554437,0.154539 1.51807,0.166524l0.267925 0c0.0227165,-0.00026378 0.0456102,-0.000582677 0.0687992,-0.001 1.1559,-0.0208465 2.34191,-0.147224 2.79148,-0.165524l0.0180591 0 0 0.166661 -7.87402e-006 0 0 0.151783 -4.66666 0 0 -0.151783 -7.87402e-006 0 0 -0.166661z"/></svg>';
				$t = !0;
			}
			else if ("curve_down_seperator" == $o)
			{
				$seperator_class = "ult-curve-down-seperator";
				$svg = '<svg class="curve-down-inner-seperator uvc-curve-down-seperator" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 4.66666 0.333331" preserveAspectRatio="none"><path class="fil0" d="M-7.87402e-006 0.0148858l0.00234646 0c0.052689,0.0154094 0.554437,0.154539 1.51807,0.166524l0.267925 0c0.0227165,-0.00026378 0.0456102,-0.000582677 0.0687992,-0.001 1.1559,-0.0208465 2.34191,-0.147224 2.79148,-0.165524l0.0180591 0 0 0.166661 -7.87402e-006 0 0 0.151783 -4.66666 0 0 -0.151783 -7.87402e-006 0 0 -0.166661z"/></svg>';
				$t = !0;
			}
			else if ("tilt_left_seperator" == $o)
			{
				$seperator_class = "ult-tilt-left-seperator";
				$svg = '<svg class="uvc-tilt-left-seperator" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 4 0.266661" preserveAspectRatio="none"><polygon class="fil0" points="4,0 4,0.266661 -0,0.266661 "/></svg>';
				$t = !0;
			}
			else if ("tilt_right_seperator" == $o)
			{
				$seperator_class = "ult-tilt-right-seperator";
				$svg = '<svg class="uvc-tilt-right-seperator" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 4 0.266661" preserveAspectRatio="none"><polygon class="fil0" points="4,0 4,0.266661 -0,0.266661 "/></svg>';
				$t = !0;
			}
			else if ("waves_seperator" == $o)
			{
				$seperator_class = "ult-wave-seperator";
				$svg = '<svg class="wave-inner-seperator uvc-wave-seperator" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 6 0.1" preserveAspectRatio="none"><path d="M0.199945 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c-0.0541102,0 -0.0981929,-0.0430079 -0.0999409,-0.0967008l0 0.0967008 0.0999409 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm0.200004 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm0.200004 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm0.200004 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm0.200004 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm2.00004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm-0.1 0.1l-0.200008 0c-0.0552126,0 -0.0999921,-0.0447795 -0.1,-0.1 -7.87402e-006,0.0552205 -0.0447874,0.1 -0.1,0.1l0.2 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1 3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1zm-0.400008 0l-0.200008 0c-0.0552126,0 -0.0999921,-0.0447795 -0.1,-0.1 -7.87402e-006,0.0552205 -0.0447874,0.1 -0.1,0.1l0.2 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1 3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1zm-0.400008 0l-0.200008 0c-0.0552126,0 -0.0999921,-0.0447795 -0.1,-0.1 -7.87402e-006,0.0552205 -0.0447874,0.1 -0.1,0.1l0.2 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1 3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1zm-0.400008 0l-0.200008 0c-0.0552126,0 -0.0999921,-0.0447795 -0.1,-0.1 -7.87402e-006,0.0552205 -0.0447874,0.1 -0.1,0.1l0.2 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1 3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1zm-0.400008 0l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1 3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1zm1.90004 -0.1c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm0.200004 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm0.200004 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm0.200004 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm0.200004 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.199945 0.00329921l0 0.0967008 -0.0999409 0c0.0541102,0 0.0981929,-0.0430079 0.0999409,-0.0967008z"/></svg>';
				$t = !0;
			}
			else if ("clouds_seperator" == $o)
			{
				$seperator_class = "ult-cloud-seperator";
				$svg = '<svg class="cloud-inner-seperator uvc-cloud-seperator" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 2.23333 0.1" preserveAspectRatio="none"><path class="fil0" d="M2.23281 0.0372047c0,0 -0.0261929,-0.000389764 -0.0423307,-0.00584252 0,0 -0.0356181,0.0278268 -0.0865354,0.0212205 0,0 -0.0347835,-0.00524803 -0.0579094,-0.0283701 0,0 -0.0334252,0.0112677 -0.0773425,-0.00116929 0,0 -0.0590787,0.0524724 -0.141472,0.000779528 0,0 -0.0288189,0.0189291 -0.0762362,0.0111535 -0.00458268,0.0141024 -0.0150945,0.040122 -0.0656811,0.0432598 -0.0505866,0.0031378 -0.076126,-0.0226614 -0.0808425,-0.0308228 -0.00806299,0.000854331 -0.0819961,0.0186969 -0.111488,-0.022815 -0.0076378,0.0114843 -0.059185,0.0252598 -0.083563,-0.000385827 -0.0295945,0.0508661 -0.111996,0.0664843 -0.153752,0.019 -0.0179843,0.00227559 -0.0571181,0.00573622 -0.0732795,-0.0152953 -0.027748,0.0419646 -0.110602,0.0366654 -0.138701,0.00688189 0,0 -0.0771732,0.0395709 -0.116598,-0.0147677 0,0 -0.0497598,0.02 -0.0773346,-0.00166929 0,0 -0.0479646,0.0302756 -0.0998937,0.00944094 0,0 -0.0252638,0.0107874 -0.0839488,0.00884646 0,0 -0.046252,0.000775591 -0.0734567,-0.0237087 0,0 -0.046252,0.0101024 -0.0769567,-0.00116929 0,0 -0.0450827,0.0314843 -0.118543,0.0108858 0,0 -0.0715118,0.0609803 -0.144579,0.00423228 0,0 -0.0385787,0.00770079 -0.0646299,0.000102362 0,0 -0.0387559,0.0432205 -0.125039,0.0206811 0,0 -0.0324409,0.0181024 -0.0621457,0.0111063l-3.93701e-005 0.0412205 2.2323 0 0 -0.0627953z"/></svg>';
				$t = !0;
			}
			else if ("multi_triangle_seperator" == $o)
			{
				$seperator_class = "ult-multi-trianle";
				$f = preg_replace_callback( '/^#?([a-f\\d])([a-f\\d])([a-f\\d])$/i', function($m) { return $m[ 1 ] . $m[ 1 ] . $m[ 2 ] . $m[ 2 ] . $m[ 3 ] . $m[ 3 ]; }, $i );
				if(preg_match( '/^#?([a-f\\d]{2})([a-f\\d]{2})([a-f\\d]{2})$/i', $f, $match ))
					$f = array( 'r' => hex2bin( $match[ 1 ] ), 'g' => hex2bin( $match[ 2 ] ), 'b' => hex2bin( $match[ 3 ] ) );
				else
					$f = null;
				$svg = '<svg class="uvc-multi-triangle-svg" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 100 100" preserveAspectRatio="none" width="100%" height="' . $p . '">\t\t\t\t            <path class="large left" d="M0 0 L50 50 L0 100" fill="rgba(' . $f['r'] . "," . $f['g'] . "," . $f['b'] . ', .1)"></path>\t\t\t\t            <path class="large right" d="M100 0 L50 50 L100 100" fill="rgba(' . $f['r'] . "," . $f['g'] . "," . $f['b'] . ', .1)"></path>\t\t\t\t            <path class="medium left" d="M0 100 L50 50 L0 33.3" fill="rgba(' . $f['r'] . "," . $f['g'] . "," . $f['b'] . ', .3)"></path>\t\t\t\t            <path class="medium right" d="M100 100 L50 50 L100 33.3" fill="rgba(' . $f['r'] . "," . $f['g'] . "," . $f['b'] . ', .3)"></path>\t\t\t\t            <path class="small left" d="M0 100 L50 50 L0 66.6" fill="rgba(' . $f['r'] . "," . $f['g'] . "," . $f['b'] . ', .5)"></path>\t\t\t\t            <path class="small right" d="M100 100 L50 50 L100 66.6" fill="rgba(' . $f['r'] . "," . $f['g'] . "," . $f['b'] . ', .5)"></path>\t\t\t\t            <path d="M0 99.9 L50 49.9 L100 99.9 L0 99.9" fill="rgba(' . $f['r'] . "," . $f['g'] . "," . $f['b'] . ', 1)"></path>\t\t\t\t            <path d="M48 52 L50 49 L52 52 L48 52" fill="rgba(' . $f['r'] . "," . $f['g'] . "," . $f['b'] . ', 1)"></path>\t\t\t\t        </svg>';
				$t = !0;
			}
			else if ("round_split_seperator" == $o)
			{
				// MBI!!!!!!!!!!!!!!!!!!!!!!
				/*
				$t = $temp_border_before = $temp_border_after = $temp_border_line = "";
				$temp_padding = 0;
				$seperator_class = "ult-rounded-split-seperator-wrapper";
				y, w, x, j, Q, z, k, C, M, I, P, A, L;
				jQuery($item).outerHeight();
				0 != $s && ($f = parseInt(jQuery($item).css("padding-bottom")),
				jQuery($item).css({
					"padding-bottom": $s . "px"
				}),
				0 == $f && ($temp_padding = $s)),
				"top_seperator" == $u ? (Q = "top-split-seperator",
				y = "0px",
				w = "auto",
				x = "border-radius: 0 0 " . $s . "px 0 !important;",
				j = "border-radius: 0 0 0 " . $s . "px !important;") : "bottom_seperator" == $u ? (Q = "bottom-split-seperator",
				y = "auto",
				w = "0px",
				x = "border-radius: 0 " . $s . "px 0 0 !important;",
				j = "border-radius: " . $s . "px 0 0 0 !important;") : (Q = "top-bottom-split-seperator",
				C = k = "auto",
				M = z = "0px",
				I = "border-radius: 0 0 " . $s . "px 0 !important;",
				P = "border-radius: 0 0 0 " . $s . "px !important;",
				A = "border-radius: 0 " . $s . "px 0 0 !important;",
				L = "border-radius: " . $s . "px 0 0 0 !important;"),
				$inner_html = '<div class="ult-rounded-split-seperator ' . Q . '"></div>',
				"none" != $l && ($temp_border_line = $n . "px " . $l . " " . $d,
				$temp_border_before = "border-top: " . $temp_border_line . "; border-right: " . $temp_border_line . ";",
				$temp_border_after = "border-top: " . $temp_border_line . "; border-left: " . $temp_border_line . ";"),
				"top_seperator" == $u || "bottom_seperator" == $u ? ($t = "<style>." . $b . " .ult-rounded-split-seperator." . Q . ":before { background-color:" . $i . "; height:" . $s . "px !important; top:" . y . "; bottom:" . w . "; " . $temp_border_before . " " . x . " } ." . $b . " .ult-rounded-split-seperator." . Q . ":after { background-color:" . $i . "; left: 50%; height:" . $s . "px !important; top:" . y . "; bottom:" . w . "; " . $temp_border_after . " " . j . " }</style>",
				jQuery("head").append($t)) : ($t = "<style>." . $b . ".top_seperator .ult-rounded-split-seperator:before { background-color:" . $i . "; height:" . $s . "px !important; top:" . z . "; bottom:" . k . "; " . $temp_border_before . " " . I . " } ." . $b . ".top_seperator .ult-rounded-split-seperator:after { background-color:" . $i . "; left: 50%; height:" . $s . "px !important; top:" . z . "; bottom:" . k . "; " . $temp_border_after . " " . P . " }</style>",
				temp_css_bottom = "<style>." . $b . ".bottom_seperator .ult-rounded-split-seperator:before { background-color:" . $i . "; height:" . $s . "px !important; top:" . C . "; bottom:" . M . "; " . $temp_border_before . " " . A . " } ." . $b . ".bottom_seperator .ult-rounded-split-seperator:after { background-color:" . $i . "; left: 50%; height:" . $s . "px !important; top:" . C . "; bottom:" . M . "; " . $temp_border_after . " " . L . " }</style>",
				jQuery("head").append($t . temp_css_bottom))
				*/
			} else
				$seperator_class = "ult-no-shape-seperator";

			if(null !== $n && "" != $n && 0 != $n)
				$e = (int)$n;
			$shape_css = 'content: "";width:' . $s . "px; height:" . $s . "px; bottom: -" . ($m + $e) . "px;";
			if("" != $i)
				$shape_css .= "background-color:" . $i . ";";
			if("none" != $l && "ult-rounded-split-seperator-wrapper" != $seperator_class && 0 == $t)
			{
				$seperator_border_line_css = $n . "px " . $l . " " . $d;
				$shape_css .= "border-bottom:" . $seperator_border_line_css . "; border-right:" . $seperator_border_line_css . ";";
				$seperator_css .= "border-bottom:" . $seperator_border_line_css . ";";
				$h = "bottom:" . $n . "px !important";
			}

			if("ult-no-shape-seperator" != $seperator_class && "ult-rounded-split-seperator-wrapper" != $seperator_class && 0 == $t)
				$_ = "." . $b . " .ult-main-seperator-inner:after { " . $shape_css . " }";
			else
				$_ = '';

			if(1 == $t)
				$inner_html = $svg;

			if("top_bottom_seperator" == $u)
			{
				$g = '<div class="ult-vc-seperator top_seperator ' . $seperator_class . " " . $b . '" data-full-width="' . $c . '" data-border="' . $l . '" data-border-width="' . $n . '"><div class="ult-main-seperator-inner">' . $inner_html . "</div>" . $v . "</div>";
				$g .= '<div class="ult-vc-seperator bottom_seperator ' . $seperator_class . " " . $b . '" data-full-width="' . $c . '" data-border="' . $l . '" data-border-width="' . $n . '"><div class="ult-main-seperator-inner">' . $inner_html . "</div>" . $v . "</div>";
			}
			else
			{
				$g = '<div class="ult-vc-seperator ' . $u . " " . $seperator_class . " " . $b . '" data-full-width="' . $c . '" data-border="' . $l . '" data-border-width="' . $n . '"><div class="ult-main-seperator-inner">' . $inner_html . "</div>" . $v . "</div>";
			}

			$g = HtmlNd::ParseAndImportAll( $doc, $g );
			foreach( $g as $g1 )
				$itemRow -> insertBefore( $g1, $itemRow -> firstChild );

			$seperator_css = "." . $b . " .ult-main-seperator-inner { " . $seperator_css . " }";
			if("" != $h)
			{
				$h = "." . $b . " .ult-main-seperator-inner { " . $h . " }";
				$seperator_css .= $h;
			}
			if("" != $v)
			{
				$p2 = $p / 2;
				if("none_seperator" == $o || "circle_svg_seperator" == $o || "triangle_svg_seperator" == $o)
					$seperator_css .= "." . $b . " .separator-icon { -webkit-transform: translate(-50%, -50%); -moz-transform: translate(-50%, -50%); -ms-transform: translate(-50%, -50%); -o-transform: translate(-50%, -50%); transform: translate(-50%, -50%); }";
				else
					$seperator_css .= "." . $b . ".top_seperator .separator-icon { -webkit-transform: translate(-50%, calc(-50% . " . $p2 . "px)); -moz-transform: translate(-50%, calc(-50% . " . $p2 . "px)); -ms-transform: translate(-50%, calc(-50% . " . $p2 . "px)); -o-transform: translate(-50%, calc(-50% . " . $p2 . "px)); transform: translate(-50%, calc(-50% . " . $p2 . "px)); } ." . $b . ".bottom_seperator .separator-icon { -webkit-transform: translate(-50%, calc(-50% - " . $p2 . "px)); -moz-transform: translate(-50%, calc(-50% - " . $p2 . "px)); -ms-transform: translate(-50%, calc(-50% - " . $p2 . "px)); -o-transform: translate(-50%, calc(-50% - " . $p2 . "px)); transform: translate(-50%, calc(-50% - " . $p2 . "px)); }";
			}

			if(1 == $t)
			{
				foreach( $g as $g1 )
					foreach( $xpath -> query( './/svg', $g1 ) as $itemSvg )
						$itemSvg -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSvg -> getAttribute( 'style' ) ), array( 'height' => $p . 'px' ) ) ) );

				// MBI!!!!!!!!!
				/*
				setTimeout(function() {
					"multi_triangle_seperator" == $o && jQuery(".ult-multi-trianle").each(function($t, $e) {
						a = B($e).find("svg").height();
						B($e).hasClass("top_seperator") || B($e).hasClass("bottom_seperator") && B($e).css("bottom", a - 1)
					})
				}, 300);
				*/
			}

			if( $ctxProcess[ 'mode' ] & 1 )
			{
				$itemStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemStyle, $_ . $seperator_css );
				$itemRow -> parentNode -> insertBefore( $itemStyle, $itemRow );
			}
		}
	}

	// wp-content/plugins/Ultimate_VC_Addons/assets/min-js/ultimate_bg.min.js: ultimate_bg_color_shift
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," upb_color ")]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		// MBI!!!!!!!!!!!!!!
	}
}

function _ProcessCont_Cp_upbCntVid( $ctx, &$ctxProcess, $settFrm, $doc, $xpath )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," upb_content_video ")][@data-ultimate-video]' ) as $item )
	{
		if( FramesCp_CheckExcl( $ctxProcess, $doc, $settFrm, $item ) || !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$urlVid = $item -> getAttribute( 'data-ultimate-video' );
		if( !$urlVid )
			continue;

		if( !( $itemRow = _Upb_GetNearestRow( $item ) ) )
			continue;

		$themeSupport = HtmlNd::GetAttr( $item, 'data-theme-support' );

		$itemCont = HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'upb_video-wrapper' ) );
		$itemCont -> appendChild( $item );
		$itemRow -> insertBefore( $itemCont, $itemRow -> firstChild );

		HtmlNd::AddRemoveAttrClass( $item, array( 'upb_video-bg' ), array( 'upb_content_video' ) );
		$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'background-image' => 'url(' . HtmlNd::GetAttr( $item, 'data-ultimate-video-poster' ) . ')' ) ) ) );
		$item -> appendChild( HtmlNd::CreateTag( $doc, 'video', array( 'class' => array( 'upb_video-src'/*, 'ult-make-full-height'*/ ), 'muted' => HtmlNd::GetAttr( $item, 'data-ultimate-video-muted' ), 'loop' => HtmlNd::GetAttr( $item, 'data-ultimate-video-loop' ), 'preload' => 'auto', 'autoplay' => HtmlNd::GetAttr( $item, 'data-ultimate-video-autoplay' ) ), array( HtmlNd::CreateTag( $doc, 'source', array( 'type' => 'video/mp4', 'src' => HtmlNd::GetAttr( $item, 'data-ultimate-video' ) ) ) ) ) );
		if( $item -> getAttribute( 'data-overlay' ) == 'true' )
		{
		    $item -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'upb_bg_overlay', 'style' => array( 'background-color' => HtmlNd::GetAttr( $item, 'data-overlay-color' ) ) ) ) );
			if( $overlayPattern = $item -> getAttribute( 'data-overlay-pattern' ) )
			{
				$item -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'upb_bg_overlay_pattern', 'style' => array( 'background-image' => 'url(' . $overlayPattern . ')', 'opacity' => HtmlNd::GetAttr( $item, 'data-overlay-pattern-opacity' ), 'background-attachment' => HtmlNd::GetAttr( $item, 'data-overlay-pattern-attachment' ) ) ) ) );
			}
		}

		HtmlNd::AddRemoveAttrClass( $itemRow, Ui::ParseClassAttr( $item -> getAttribute( 'data-hide-row' ) ) );
		$itemRow -> setAttribute( 'data-rtl', $item -> getAttribute( 'data-rtl' ) );
		$itemRow -> setAttribute( 'data-row-effect-mobile-disable', $item -> getAttribute( 'data-row-effect-mobile-disable' ) );
		$itemRow -> setAttribute( 'data-img-parallax-mobile-disable', $item -> getAttribute( 'data-img-parallax-mobile-disable' ) );

		// wp-content/plugins/Ultimate_VC_Addons/assets/min-js/ultimate_bg.min.js: "vc_row-has-fill"
		if( $themeSupport !== null && $themeSupport !== 'enable' )
			$itemContainer = null;	// MBI!!!!!!!!!!!!!!!
		else
			$itemContainer = $itemCont -> parentNode;
		HtmlNd::AddRemoveAttrClass( $itemContainer, array( 'vc_row-has-fill' ) );
	}
}

// #######################################################################

function _Upb_GetNearestRow( $item )
{
	for( $itemRow = $item; $itemRow = HtmlNd::GetPreviousElementSibling( $itemRow );  )
		if( in_array( 'wpb_row', HtmlNd::GetAttrClass( $itemRow ) ) )
			break;
	return( $itemRow );
}

// #######################################################################
// #######################################################################

?>