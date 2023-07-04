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

    public function decryptData(){
        // useranme : IvXXOvJ3iBiuZAMQ/TG61w==
        // password : IvXKpHrUgQdAcSAKuObXPg==

        $base64Value = 'IvXKpHrUgQdAcSAKuObXPg==';

        return openssl_decrypt(
                    $base64Value,
                    $this->encryptMethod,
                    $this->key,
                    0,
                    $this->iv
                );
    }

}
?>