<?php
// Definitions
define( "GCODE_FILE",      './test.gcode' ); // Source GCODE file
define( "TN_START_MARK",   '/; thumbnail begin [0-9]+x[0-9]+ ([0-9]+)?/i' ); // Start RegEx
define( "TN_END_MARK",     '/; thumbnail end/i' ); // End RegEx
define( "TEMP_FILE",       sys_get_temp_dir()."/gcode-thumbnail.".date( "U" ).".bin" ); // Temporary file

// Variables
$line_counter = 1; // Cursor
$start_line = 0; // First line of thumbnail definition
$end_line = 0; // Last line of thumbnail definition
$tn_line = false; // Marker
$tn_lines = array(); // Array for thumbnail definition lines
$tn_res = array(); // Thumbnail resolution
$line_count = 0; // Number of thumbnail definition lines

// Open GCODE file
$handle = @fopen( GCODE_FILE, "r");
if ( $handle !== false ) {
    // File open successful
    while ( ( $line = fgets( $handle ) ) !== false ) {
        // Read file lines
        if ( preg_match( TN_START_MARK, $line ) ) {
            // Start line of thumbnail definition found
            $start_line = $line_counter;

            // Split line at whitespaces
            $line_parts = explode( " ", trim( $line ) );

            // Part 4 is thumbnail resolution
            $tn_resolution = $line_parts[3];

            // Part 5 is encoded thumbnail stream length
            $tn_streamlength = $line_parts[4];

            // Start line passed
            $tn_line = true;
        }

        if ( $tn_line == true ) {
            // Line is between start end end of thumbnail definition, add to array
            $tn_lines[] = trim( str_replace( array( ";", "\r", "\n" ), "", $line ) );
        }

        if ( preg_match( TN_END_MARK, $line ) ) {
            // End Line of thumbnail definition found
            $end_line = $line_counter - 1;
            $line_count = $end_line - $start_line;

            // End line reached
            $tn_line = false;

            // Skip rest of lines/file
            break;
        }

        // Increment line counter
        $line_counter++;
    }

    // Close GCODE file
    fclose( $handle );
} else {
    // File open failed, ouch
    http_response_code( 500 );
    header ("Content-Type: text/plain" );
    // Return error message and end
    die( "Error 500: Unable to open GCODE file ".GCODE_FILE."\n" );
}

// Remove first line from array
array_shift( $tn_lines );

// Remove last line from array
array_pop( $tn_lines );

// Add array parts to string
$thumbnail_encoded = implode( "", $tn_lines );

// Get length of encoded string
$stream_length = strlen( $thumbnail_encoded );

// Base64 decode string
$thumbnail = base64_decode( $thumbnail_encoded );

// Write thumbnail to temporary file
file_put_contents( TEMP_FILE, $thumbnail );

// Get MIME type of file
$thumbnail_mime = mime_content_type( TEMP_FILE );

// Get resolution of thumbnail
$tn_res = explode( "x", strtolower( trim( $tn_resolution ) ) );
$tn_resolution_x = $tn_res[0];
$tn_resolution_y = $tn_res[1];

// Return HTTP headers with MIME type, file name and length
header( "Content-Type: ".$thumbnail_mime );
header( "Content-Disposition: inline; filename='".basename( GCODE_FILE ).".thumbnail'" );
header( "Content-Length: ".strlen( $thumbnail ) );

// Return informational HTTP headers
header( "X-Gcode-FileName: ".basename( GCODE_FILE ) );
header( "X-Gcode-FileMime: ".mime_content_type( GCODE_FILE ) );
header( "X-Gcode-FileSize: ".filesize( GCODE_FILE ) );
header( "X-Gcode-Timestamp: ".filemtime( GCODE_FILE ) );
if ( !empty( $tn_streamlength) ) {
    // Stream length specified in GCODE
    header( "X-Gcode-SpecifiedStreamLength: ".$tn_streamlength );
} else {
    // No stream length specified in GCODE
    header( "X-Gcode-SpecifiedStreamLength: null" );
}
header( "X-Thumb-ProcessedStreamLength: ".$stream_length );
if ( $stream_length == $tn_streamlength ) {
    // Length of Base64 encoded stream does match GCODE definition
    header( "X-Thumb-StreamLengthMatch: Yes" );
} else {
    // Length of Base64 encoded stream does not match GCODE definition
    header( "X-Thumb-StreamLengthMatch: No" );
}
header( "X-Thumb-Resolution-X: ".$tn_resolution_x );
header( "X-Thumb-Resolution-Y: ".$tn_resolution_y );
header( "X-Thumb-MimeType: ".$thumbnail_mime );
header( "X-Thumb-FileSize: ".filesize( TEMP_FILE ) );
header( "X-Thumb-Timestamp: ".filemtime( TEMP_FILE ) );

// Remove temporary file
@unlink( TEMP_FILE );

// Return thumbnail content
echo $thumbnail;
