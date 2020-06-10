<?php
// 老版本(7.1以下的des加密)
// class DES
// {
//     public static function pkcs5_pad($text, $blocksize)
//     {
//         $pad = $blocksize - (strlen($text) % $blocksize);
//         return $text . str_repeat(chr($pad), $pad);
//     }

//     public static function pkcs5_unpad($text)
//     {
//         $pad = ord($text{strlen($text)-1});
//         if ($pad > strlen($text)) {
//             return false;
//         }
//         if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
//             return false;
//         }
//         return substr($text, 0, -1 * $pad);
//     }

//     public static function encrypt($key, $data)
//     {
//         $size = mcrypt_get_block_size('des', 'ecb');
//         $data = DES::pkcs5_pad($data, $size);
//         $td = mcrypt_module_open('des', '', 'ecb', '');
//         $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
//         @mcrypt_generic_init($td, $key, $iv);
//         $data = mcrypt_generic($td, $data);
//         mcrypt_generic_deinit($td);
//         mcrypt_module_close($td);
//         return bin2hex($data);
//     }

//     public static function decrypt($key, $data)
//     {
//         $td = mcrypt_module_open('des', '', 'ecb', '');
//         $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
//         $ks = mcrypt_enc_get_key_size($td);
//         @mcrypt_generic_init($td, $key, $iv);
//         $decrypted = mdecrypt_generic($td, $data);
//         mcrypt_generic_deinit($td);
//         mcrypt_module_close($td);
//         $result = DES::pkcs5_unpad($decrypted);
//         return $result;
//     }
// }
class Des
{

    private $cipher, $key;

    /**
     * DesEdeCbc constructor.
     * @param $cipher
     * @param $key
     * @param $iv
     */
    public function __construct($cipher, $key)
    {
        $this->cipher = $cipher;
        $this->key = $this->getFormatKey($key);
    }
    /**
     * @func  加密
     * @param $msg
     * @return string
     */
    public function encrypt($msg)
    {
        $des = @openssl_encrypt($msg, $this->cipher, $this->key, OPENSSL_RAW_DATA);
        return base64_encode($des);
    }

    /**
     * @func  解密
     * @param $msg
     * @return string
     */

    public function decrypt($msg)
    {
        return @openssl_decrypt(base64_decode($msg), $this->cipher, $this->key, OPENSSL_RAW_DATA, $this->iv);
    }

    /**
     * @func  生成24位长度的key
     * @param $skey
     * @return bool|string
     */

    private function getFormatKey($skey)
    {
        $md5Value = md5($skey);
        $md5ValueLen = strlen($md5Value);
        $key = $md5Value . substr($md5Value, 0, $md5ValueLen / 2);
        return hex2bin($key);
    }
}

// $cipher = 'DES-EDE-CBC';
// $msg = 'HelloWorld';
// $key = '12345678';
// $des = new Des($cipher, $key);
// // 加密
// $msg = $des->encrypt($msg);
// echo '加密后: ' . $msg . PHP_EOL;
// // 解密
// $src = $des->decrypt($msg);
// echo '解密后: ' . $src . PHP_EOL;
