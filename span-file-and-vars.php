<?php
/*
Plugin Name: Span File and Vars
Plugin URI: http://wordpress.org/extend/plugins/span-file-and-vars/
Description: Searches for file and variable names within content, and wraps them with a span html tag.
Version: 1.0
Author: Jean-Sebastien Morisset
Author URI: http://surniaulula.com/

Copyright 2012 - Jean-Sebastien Morisset - http://surniaulula.com/

This script is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later
version.

This script is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE. See the GNU General Public License for more details at
http://www.gnu.org/licenses/.
*/

add_filter( 'the_content', 'span_file_and_vars' );
add_filter( 'comment_text', 'span_file_and_vars' );

function span_file_and_vars( $content ) {
	$in_code = '';
	$new_content = '';
	// split content to exclude comments and javascript sections
	foreach ( preg_split( '/((\r?\n)|(\r\n?))/', $content) as $line) {
		if ( preg_match( '/<(!--|script|pre)/i', $line ) ) $in_code = 1;
		if ( preg_match( '/(--|\/script|\/pre)>/i', $line ) ) $in_code = 0;
		if ( empty( $in_code ) ) {
			$pattern = array(
				/*
				Look for filepaths and filenames:
				
				Prefix: Start of line, or any single character matching
				greater-than (the end of an html tag), space, or open-bracket.
				
				Filepath: Zero or one occurrence of a tilde, dot, or double-dot,
				which could be part of a filepath, followed by a slash, and one
				or more characters that are allowed in filepath names
				(alpha-numeric, underscore, hyphen, period, wildcard, and
				slash).
				
				Filename: One or more characters allowed in a filename
				(alpha-numeric, underscore, hyphen, period, and slash),
				followed by a dot, and 3-4 characters allowed in a filename
				extension (alpha-numeric and underscore).
				
				Suffix: Any single character matching a close-bracket, dot,
				comma, semi-colon, exclamation mark, interrogation mark,
				return, or new-line.
				
				  --Prefix--- --Filepath---------------------- --Filename---------------------- --Suffix--------- */
				'/(^|[>\s\(])((~|\.|\.\.)?\/[a-z0-9_\-\.\*\/]+|[a-z0-9_\-\.\/]+\.[a-z0-9_]{2,4})([\)\.,;!\?<\s\n\r])/i',
				/*
				Look for variables and perl module names:

				  --Prefix--- --Variable-------------------- --Module--------------- --Suffix--------- */
				'/(^|[>\s\(])([\$\@\%][a-z][a-z0-9_:\[\]\']+|[a-z0-9_]+::[a-z0-9_:]+)([\)\.,;!<\s\n\r])/i',
				/*
				Look for function names:

				  --Prefix--- --Function------------------------- --Suffix--------- */
				'/(^|[>\s\(])([a-z0-9_]+\([a-z0-9_:\[\]\'\$\,]*\))([\)\.,;!<\s\n\r])/i',
			);
			$replace = array(
				'$1<span class="spanfile">$2</span>$4',
				'$1<span class="spanvar">$2</span>$3',
				'$1<span class="spanvar">$2</span>$3',
			);
			ksort($pattern);
			ksort($replace);
			$line = preg_replace( $pattern, $replace, $line);
		}
		$new_content .= $line."\n";
	} 
	return $new_content;
}
?>
