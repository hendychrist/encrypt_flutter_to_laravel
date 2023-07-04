<?php
namespace App\Http\Controllers;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $encrypter;
    private string $encryptMethod = 'AES-256-CBC';
    private string $key;
    private string $iv;

    public function __construct(Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
        $mykey = 'ThisIsASecuredKey';
        $myiv = 'ThisIsASecuredBlock';
        $this->key = substr(hash('sha256', $mykey), 0, 32);
        $this->iv = substr(hash('sha256', $myiv), 0, 16);
    }

    protected function pkcs7_pad($data, $blockSize)
    {
        $padding = $blockSize - (strlen($data) % $blockSize);
        $paddingChar = chr($padding);
        return $data . str_repeat($paddingChar, $padding);
    }

    protected function pkcs7_unpad($data)
    {
        $paddingChar = ord($data[strlen($data) - 1]);
        return substr($data, 0, -$paddingChar);
    }
    protected function base16ToUint8List($input)
    {
        $bytes = [];
        $length = strlen($input);

        for ($i = 0; $i < $length; $i += 2) {
            $bytes[] = hexdec(substr($input, $i, 2));
        }

        return $bytes;
    }

    // Sesama laravel bisa 
    public function encryptData(){
        
        $plaintext = "message to be encrypted test";
        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv = 'ThisIsASecuredBl';
        $key = '5ae1b8a17bad4da4fdac796f64c16ecd';
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
        $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );

        dd(
            'plaintext:  '. $plaintext,
            '',
            'ivlen:  '. $ivlen,
            '',
            'options:'. $options,
            '',
            'iv:  '. $iv,
            '',
            'ciphertext_raw:  '. $ciphertext_raw,
            '',
            'hmac:  '. $hmac,
            '',
            'ciphertext:  '. $ciphertext,
        );
    }
    
    // Sesama laravel bisa 
    public function decryptDataLaravel(){
        $c = base64_decode('VGhpc0lzQVNlY3VyZWRCbOaJ3XCg3rQjhdPAw192KUlBs5xhLMKcFENxNTFFOQM7gFNYxCG/FOyjFyOmPSzr0oB9QnJcJqq6fQSfNqZojiY=');
        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len=32);
        $ciphertext_raw = substr($c, $ivlen+$sha2len);
        $key = '5ae1b8a17bad4da4fdac796f64c16ecd';
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
        if (hash_equals($hmac, $calcmac))// timing attack safe comparison
        {
            echo $original_plaintext."\n";
        }
    }

    public function decryptData(){

        $base64Value = 'IvXXOvJ3iBiuZAMQ/TG61w==';

        return openssl_decrypt(
            $base64Value,
            $this->encryptMethod,
            $this->key,
            0,
            $this->iv
        );
    }

    public function decryptDataOld()
    {
        $encryptedData = 'eyJpdiI6ImJNWjdrY2NHV1QydmNLZEV0bUNMOVE9PSIsInZhbHVlIjoiUXU1TjcyZnNHV0I1UktIMXhJdWlUR0JhNmxoUm1XYW9nVnR1dGY1TGQ4az0iLCJtYWMiOiIwMDY0YTlmMGU2YmNh';
        // $key = 'my132zlengthzkeyabcdefghijlmnopq';
        // $iv = 'spqkeitkrolsderf';

        $key = hex2bin('5ae1b8a17bad4da4fdac796f64c16ecd');
        $keySizeInBits = strlen($key) * 8;
        $iv = hex2bin('34857d973953e44afb49ea9d61104d8c');

        try {
            $decryptedData = openssl_decrypt(base64_decode($encryptedData), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
            // $output = openssl_decrypt($encstr, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

            dd(
                'keySizeInBits:  '. $keySizeInBits . ' Bites',
                'key:    '. $key,
                'iv:     '. $iv , 
                'Encrypted Data:    ' . $encryptedData ,
                'Decrypted Data:    ' , $decryptedData
            );

        } catch (\Exception $e) {
            Log::error('Decryption failed: ' . $e->getMessage());
            dd('Decryption failed.', $e);
        }
    }

}
?>