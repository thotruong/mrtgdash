#!/usr/bin/php
<?

/**
 * Entity renamer.
 * 
 * @author	Stuart Ford
 * @see		https://github.com/stuartford/mrtgdash
 * @license	http://www.gnu.org/licenses/gpl.html
 */

// get arguments
if (count($argv) != 3) throw new Exception("Usage: {$argv[0]} <from> <to>");

// rename files
foreach (glob("{$argv[1]}*") as $file) {
	$cmd = "mv {$file} ".preg_replace("/{$argv[1]}/", $argv[2], $file);
	print "{$cmd}\n";
	system($cmd);
}
