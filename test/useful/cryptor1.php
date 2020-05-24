<?php
# Credit where credit is due
/**
    * Command-line example usage of OpenSSL-File-Encrypt class.
    *
    * Processes up to 1.8 GB on CLI, subject to memory availability.
    *
    * Usage:
    * php <thisfilename> -e|-d <filename>
    *
    * @author Martin Latter <copysense.co.uk>
    * @copyright Martin Latter 20/02/2018
    * @version 0.05
    * @license GNU GPL v3.0
    * @link https://github.com/Tinram/OpenSSL-File-Encrypt.git
*/



##### YAY FOR CONSTANTS #####

# Ensure that types are strict when passing type hints
declare(strict_types=1);

# File info
define('DUB_EOL', PHP_EOL . PHP_EOL);
define('LINUX', (stripos(php_uname(), 'linux') !== FALSE) ? TRUE : FALSE);

# How much of the file will we encrypt at a time?
define('FILE_ENCRYPTION_BLOCKS', 10000);

if ( ! extension_loaded('openssl'))
{
    die(PHP_EOL . ' OpenSSL library not available!' . DUB_EOL);
}

# No longer needed, deprecated usage
//require('classes/openssl_file.class.php');




##### USAGE INFORMATION #####

$sUsage =
    PHP_EOL . ' ' . basename(__FILE__, '.php') .
    DUB_EOL . "\tusage: php " . basename(__FILE__) . ' -e|-d <file> ' . ( ! LINUX ? '<password>' : '') . DUB_EOL;

$sMode = null;
$aOptions = getopt('h::e::d::', ['help::', 'h::']);

if ( ! empty($aOptions))
{
    $sOpt = key($aOptions);

    switch ($sOpt)
    {
        case 'h':
            die($sUsage);
        break;

        case 'e':
        case 'd':
           $sMode = $sOpt;
        break;
    }
}
else
{
    die($sUsage);
}

# Check to ensure file argument exists
if ( ! isset($_SERVER['argv'][2]))
{
    echo PHP_EOL . ' missing filename!' . PHP_EOL;
    die($sUsage);
}

$sFilename = $_SERVER['argv'][2];

# Check to ensure file exists
if ( ! file_exists($sFilename))
{
    die(PHP_EOL . ' \'' . $sFilename . '\' does not exist in this directory!' . DUB_EOL);
}

# Take password through stdin
if (LINUX)
{
    echo ' password: ';
    `/bin/stty -echo`;
    $sPassword = trim(fgets(STDIN));
    `/bin/stty echo`;

    if ($sMode === 'e')
    {
        echo PHP_EOL . ' re-enter password: ';
        `/bin/stty -echo`;
        $sPassword2 = trim(fgets(STDIN));
        `/bin/stty echo`;

        if ($sPassword !== $sPassword2)
        {
            die(PHP_EOL . ' entered passwords do not match!' . DUB_EOL);
        }
    }
}
else 	# Take password on cmd line
{
    if ( ! isset($_SERVER['argv'][3]))
    {
        die(PHP_EOL . ' missing password!' . DUB_EOL . "\tusage: " . basename(__FILE__) . ' -e|-d <file> <password>' . DUB_EOL);
    }
    else
    {
        $sPassword = $_SERVER['argv'][3];
    }
}

### These both need to be changed
if ($sMode === 'e')
{
    echo OpenSSLFile::encrypt($sFilename, $sPassword);
}
else if ($sMode === 'd')
{
    echo OpenSSLFile::decrypt($sFilename, $sPassword);
}

/**
 * Encrypt the passed file and saves the result in a new file with ".enc" as suffix.
 * 
 * @param string $source Path to file that should be encrypted
 * @param string $key    The key used for the encryption
 * @param string $dest   File name where the encryped file should be written to.
 * @return string|false  Returns the file name that has been created or FALSE if an error occured
 */

function encryptFile($source, $key, $dest)
{
    $key = substr(sha1($key, true), 0, 16);
    $iv = openssl_random_pseudo_bytes(16);

    $error = false;
    if ($fpOut = fopen($dest, 'w')) {
        // Put the initialzation vector to the beginning of the file
        fwrite($fpOut, $iv);
        if ($fpIn = fopen($source, 'rb')) {
            while (!feof($fpIn)) {
                $plaintext = fread($fpIn, 16 * FILE_ENCRYPTION_BLOCKS);
                $ciphertext = openssl_encrypt($plaintext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                // Use the first 16 bytes of the ciphertext as the next initialization vector
                $iv = substr($ciphertext, 0, 16);
                fwrite($fpOut, $ciphertext);
            }
            fclose($fpIn);
        } else {
            $error = true;
        }
        fclose($fpOut);
    } else {
        $error = true;
    }

    return $error ? false : $dest;
}

/**
 * Dencrypt the passed file and saves the result in a new file, removing the
 * last 4 characters from file name.
 *
 * @param string $source Path to file that should be decrypted
 * @param string $key    The key used for the decryption (must be the same as for encryption)
 * @param string $dest   File name where the decryped file should be written to.
 * @return string|false  Returns the file name that has been created or FALSE if an error occured
 */
function decryptFile($source, $key, $dest)
{
    $key = substr(sha1($key, true), 0, 16);

    $error = false;
    if ($fpOut = fopen($dest, 'w')) {
        if ($fpIn = fopen($source, 'rb')) {
            // Get the initialzation vector from the beginning of the file
            $iv = fread($fpIn, 16);
            while (!feof($fpIn)) {
                $ciphertext = fread($fpIn, 16 * (FILE_ENCRYPTION_BLOCKS + 1)); // we have to read one block more for decrypting than for encrypting
                $plaintext = openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                // Use the first 16 bytes of the ciphertext as the next initialization vector
                $iv = substr($ciphertext, 0, 16);
                fwrite($fpOut, $plaintext);
            }
            fclose($fpIn);
        } else {
            $error = true;
        }
        fclose($fpOut);
    } else {
        $error = true;
    }

    return $error ? false : $dest;
}
