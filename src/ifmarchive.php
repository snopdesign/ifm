<?php

/**
 * =======================================================================
 * Improved File Manager
 * ---------------------
 * License: This project is provided under the terms of the MIT LICENSE
 * http://github.com/misterunknown/ifm/blob/master/LICENSE
 * =======================================================================
 * 
 * archive class
 *
 * This class provides support for various archive types for the IFM. It can
 * create and extract the following formats:
 * 	* zip
 * 	* tar
 * 	* tar.gz
 * 	* tar.bz2
*/

class IFMArchive {

	/**
	 * Add a folder to an archive
	 */
	private static function addFolder( &$archive, $folder, $offset=0 ) {
		if( $offset == 0 )
			$offset = strlen( dirname( $folder ) ) + 1;
		$archive->addEmptyDir( $folder, substr( $folder, $offset ) );
		$handle = opendir( $folder );
		while( false !== $f = readdir( $handle ) ) {
			if( $f != '.' && $f != '..'  ) {
				$filePath = $folder . '/' . $f;
				if( file_exists( $filePath ) && is_readable( $filePath ) )
					if( is_file( $filePath ) )
						$archive->addFile( $filePath, substr( $filePath, $offset ) );
					elseif( is_dir( $filePath ) )
						self::addFolder( $archive, $filePath, $offset );
			}
		}
		closedir( $handle );
	}

	/**
	 * Create a zip file
	 */
	public static function createZip( $src, $out )
	{
		$a = new ZipArchive();
		$a->open( $out, ZIPARCHIVE::CREATE);

		if( ! is_array( $src ) )
			$src = array( $src );

		foreach( $src as $s )
			if( is_dir( $s ) )
				self::addFolder( $a, $s );
			elseif( is_file( $s ) )
				$a->addFile( $s, substr( $s, strlen( dirname( $s ) ) + 1 ) );

		try {
			return $a->close();
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Unzip a zip file
	 */
	public static function extractZip( $file, $destination="./" ) {
		if( ! file_exists( $file ) )
			return false;
		$zip = new ZipArchive;
		$res = $zip->open( $file );
		if( $res === true ) {
			$zip->extractTo( $destination );
			$zip->close();
			return true;
		} else
			return false;
	}

	/**
	 * Creates a tar archive
	 */
	public static function createTar( $src, $out ) {
		$tar = new PharData( $out );

		if( ! is_array( $src ) )
			$src = array( $src );

		foreach( $src as $s )
			if( is_dir( $s ) )
				self::addFolder( $a, $s );
			elseif( is_file( $s ) )
				$a->addFile( $s, substr( $s, strlen( dirname( $s ) ) +1 ) ); 
		return true;
	}

	/**
	 * Extracts a tar archive
	 */
	public static function extractTar( $file, $destination="./" ) {
		if( ! file_exists( $file ) )
			return false;
		$tar = new PharData( $file );
		try {
			$tar->extractTo( $destination, null, true );
			return true;
		} catch( Exception $e ) {
			return false;
		}
	}
}
