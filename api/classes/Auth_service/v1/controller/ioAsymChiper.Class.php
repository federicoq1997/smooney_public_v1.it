<?php


class ioAsymChiper {
 
    /**     
     * Get private key     
     * @return bool|resource     
     */    
    private static function getPrivateKey() 
    {        
        if(!file_exists(dirname(__FILE__) . '/../../assets/openssl_private_key.key'))
            self::generateKey();
        $abs_path = dirname(__FILE__) . '/../../assets/openssl_private_key.key';
        $content = file_get_contents($abs_path);    
        return openssl_pkey_get_private($content);    
    }    

    /**     
     * Get public key     
     * @return bool|resource     
     */    
    private static function getPublicKey()
    {   
        if(!file_exists(dirname(__FILE__) . '/../../assets/openssl_public_key.key'))
            self::generateKey();
        
        $abs_path = dirname(__FILE__) . '/../../assets/openssl_public_key.key';
        $content = file_get_contents($abs_path);    
        return openssl_pkey_get_public($content);     
    }

    private static function generateKey(){
        $keys = openssl_pkey_new(["private_key_bits" => 4096,"private_key_type" => OPENSSL_KEYTYPE_RSA]);
        $public_key_pem = openssl_pkey_get_details($keys)['key'];
        openssl_pkey_export($keys, $private_key_pem);
        file_put_contents(dirname(__FILE__).'/../../assets/openssl_public_key.key', $public_key_pem);
        file_put_contents(dirname(__FILE__).'/../../assets/openssl_private_key.key', $private_key_pem);
    }

    /**     
     * Private key encryption     
     * @param string $data     
     * @return null|string     
     */    
    public static function privEncrypt($data = '')    
    {        
        if (!is_string($data)) {            
            return null;       
        } 
        $encrypted='';
        return openssl_private_encrypt($data,$encrypted,self::getPrivateKey()) ? base64_encode($encrypted) : null;    
    }    

    /**     
     * Public key encryption     
     * @param string $data     
     * @return null|string     
     */    
    public static function publicEncrypt($data = '')   
    {        
        if (!is_string($data)) {            
            return null;        
        }   
        $encrypted='';    
        return openssl_public_encrypt($data,$encrypted,self::getPublicKey()) ? base64_encode($encrypted) : null;    
    }    

    /**     
     * Private key decryption     
     * @param string $encrypted     
     * @return null     
     */    
    public static function privDecrypt($encrypted = '')    
    {        
        if (!is_string($encrypted)) {            
            return null;        
        }
        $decrypted='';
        return (openssl_private_decrypt(base64_decode($encrypted), $decrypted, self::getPrivateKey())) ? json_decode($decrypted, true) : null;    
    }    

    /**     
     * Public key decryption     
     * @param string $encrypted     
     * @return null     
     */    
    public static function publicDecrypt($encrypted = '')    
    {        
        if (!is_string($encrypted)) {            
            return null;        
        }     
        $decrypted='';   
        return (openssl_public_decrypt(base64_decode($encrypted), $decrypted, self::getPublicKey())) ? json_decode($decrypted, true) : null;    
    }

    /** 
     * 
     * to generate private.key: 
     *      openssl genrsa -out private.key 2048
     * 
     * to generate public.pem: 
     *      openssl rsa -in private.key -outform PEM -pubout -out public.pem
     * 
    * TYPICALY USAGE
    * $rsa = new Rsa();
    * $data['name'] = 'Tom';
    * $data['age']  = '20';
    * $privEncrypt = $rsa->privEncrypt(json_encode($data));
    * echo 'After private key encryption:'.$privEncrypt.'<br>';
    * 
    * $publicDecrypt = $rsa->publicDecrypt($privEncrypt);
    * echo 'After public key decryption:'.$publicDecrypt.'<br>';
    * 
    * $publicEncrypt = $rsa->publicEncrypt(json_encode($data));
    * echo 'After public key encryption:'.$publicEncrypt.'<br>';
    * 
    * $privDecrypt = $rsa->privDecrypt($publicEncrypt);
    * echo 'After decryption of private key:'.$privDecrypt.'<br>';
    *
    *
    * SOFT_A                 SOFT_B
    * 1) SOFT_A encrypt msg with public key of SOFT_B
    * 2) send msg_encrypted
    * 3) SOFT_B with private key decrypt msg_encrypted and can read the msg
    */

}