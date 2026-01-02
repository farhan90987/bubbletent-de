<?php

namespace seraph_accel;

// #######################################################################

//

function _OutImage( $siteId, $settGlob, $sett, $file, $nonce, $aiFileId = null )
{
	if( Gen::StrStartsWith( $file, Gen::GetFileName( WP_CONTENT_DIR ) . '/' ) )
		$pathRoot = Gen::GetFileDir( WP_CONTENT_DIR ) . '/';
	else
		$pathRoot = Gen::SetLastSlash( str_replace( '\\', '/', ABSPATH ) );
	//if( Gen::StrStartsWith( $file, $pathRoot ) )
	//    $file = substr( $file, strlen( $pathRoot ) );

	//



	if( Gen::GetNonce( $file, GetSalt() ) != $nonce )	// Just for security to avoid walking in filesystem except needed file
	{
		http_response_code( 403 );
		return;
	}

	$file = $fileOrig =  $pathRoot. $file;
	$fileExt = Gen::GetFileExt( $file );

	//



	if( !in_array( strtolower( $fileExt ), array( 'jpe','jpg','jpeg','png','gif','bmp', 'webp','avif' ) ) )
	{
		http_response_code( 403 );
		return;
	}

	$settImg = Gen::GetArrField( $sett, array( 'contPr', 'img' ), array() );

	if( $aiFileId )
	{
		$aiId = array();
		if( !preg_match( '@^([\\w\\-]+)\\.(\\w+-(\\w+))$@', $aiFileId, $aiId ) )	// Just for security to avoid walking in filesystem except needed files (e.g. m-a1/ai/9sve9yvslekgc04cs4gww0wgc-O@768.jpeg)
		{
			http_response_code( 403 );
			return;
		}

		$fileAi = GetCacheDir() . '/s/' . $aiId[ 1 ] . '/ai/' . $aiId[ 2 ] . '.' . $fileExt;
		$aiId = $aiId[ 3 ];

		$fileTm = Images_ProcessSrcEx_FileMTime( $file );
		$fileAiTm = Images_ProcessSrcEx_FileMTime( $fileAi );
		if( $fileAiTm !== false && $fileTm !== false && $fileTm <= $fileAiTm )
			$file = $fileOrig = $fileAi;
		else if( Gen::GetArrField( $settImg, array( 'szAdaptOnDemand' ), false ) )
		{
			if( Gen::GetArrField( $settImg, array( 'szAdaptAsync' ), false ) )
			{
				CachePostPrepareObjEx( 20, $file, ( string )$siteId, 10, array( 'ai' => $aiId ) );
			}
			else
			{
				require_once( __DIR__ . '/content.php' );

				{
					$ctxProcess = &GetContentProcessCtxEx( $_SERVER, $sett, $siteId, '', str_replace( '\\', '/', ABSPATH ), WP_CONTENT_DIR, '', GetCacheDir(), false );
					Images_ProcessSrc_SizeAlternatives( $ctxProcess, $file, $sett, true, $aiId );
					unset( $ctxProcess );
				}

				if( file_exists( $fileAi ) )
					$file = $fileOrig = $fileAi;
			}
		}
	}

	$mimeType = Fs::MimeTypeDef;
	$aSkip = array();
	$bNeedCompr = false;
	foreach( array_reverse( array( 'webp','avif' ) ) as $comprType )
	{
		if( !Gen::GetArrField( $settImg, array( $comprType, 'redir' ), false ) )
			continue;

		if( strpos( ($_SERVER[ 'HTTP_ACCEPT' ]??''), 'image/' . $comprType ) === false )
			continue;

		$pathCompr = $file . '.' . $comprType;
		if( !@file_exists( $pathCompr ) )
		{
			$pathCompr .= '.json';
			if( !@file_exists( $pathCompr ) && Gen::GetArrField( $settImg, array( $comprType, 'enable' ), false ) )
				$bNeedCompr = true;
			$aSkip[ $comprType ] = @json_decode( ( string )@file_get_contents( $pathCompr, false, null, 0, 500 ), true );
			continue;
		}

		$file = $pathCompr;
		$mimeType = 'image/' . $comprType;
		break;
	}

	if( $bNeedCompr && Gen::GetArrField( $settImg, array( 'comprAsync' ), false ) )
		CachePostPrepareObjEx( 10, $fileOrig, ( string )$siteId, 10 );

	if( ($sett[ 'hdrTrace' ]??null) )
		@header( 'X-Seraph-Accel-Cache: 2.27.47.1; state=' . ( $mimeType == Fs::MimeTypeDef ? 'original' : 'preoptimized' ) . '; redir=alt;' . ( $aSkip ? ( '; skip=' . @json_encode( $aSkip ) ) : '' ) . ( $mimeType != Fs::MimeTypeDef ? ( '; sizeOrig=' . @filesize( $fileOrig ) ) : '' ) );

	if( $mimeType != Fs::MimeTypeDef && Gen::GetArrField( $settImg, array( 'redirCacheAdapt' ), false ) )
	{
		@header( 'Location: ' . rtrim( ( string )Net::UrlDeParse( Net::UrlParse( $_SERVER[ 'REQUEST_URI' ] ), 0, array(), array( PHP_URL_PATH ) ), '/' ) . '/' . substr( $file, strlen( Gen::SetLastSlash( str_replace( '\\', '/', ABSPATH ) ) ) ), false );
		http_response_code( 302 );
		return;
	}

	@header( 'Vary: Accept', false );

	Fs::StreamOutFileContent( $file, $mimeType, false, 16384, false, Gen::GetArrField( $sett, array( 'cacheBr', 'enable' ), false ) ? Gen::GetArrField( $sett, array( 'cacheBr', 'timeout' ), 0 ) * 60 : 0, Gen::GetArrField( $settGlob, array( 'cache', 'chkNotMdfSince' ), false ) );
}

//

// #######################################################################
// #######################################################################

?>