<?php

//---------------------------------------------------------
//Crypt Library
//	This is a shorthand library for using AES encryption
//	on the fly
//---------------------------------------------------------

class Crypt {

	//NOTE: changing these would require new keys
	static $crypt_cipher	=	MCRYPT_RIJNDAEL_256;
	static $crypt_mode		=	MCRYPT_MODE_CBC;
	static $crypt_rand		=	MCRYPT_DEV_URANDOM;

	private $key;
	private $iv;

	protected $verified = false;

	//-----------------------------------------------------
	//Static Access
	//-----------------------------------------------------

	//generate usable IV for config
	public static function IVCreate(){
		return base64_encode(mcrypt_create_iv(
			 mcrypt_get_iv_size(self::$crypt_cipher,self::$crypt_mode)
			,self::$crypt_rand
		));
	}

	//generate usable key for config
	public static function keyCreate(){
		return base64_encode(mcrypt_create_iv(
			 mcrypt_get_key_size(self::$crypt_cipher,self::$crypt_mode)
			,self::$crypt_rand
		));
	}

	//static constructor access
	public static function _get($key,$iv){
		return new static($key,$iv);
	}

	//-----------------------------------------------------
	//Object Methods
	//-----------------------------------------------------

	//setup and store keys
	protected function __construct($key,$iv){
		$this->key = $key;
		$this->iv = $iv;
	}

	public function verify(){
		$this->verifyKey();
		$this->verifyIV();
		$this->verified = true;
		return $this;
	}

	//verify existence and size of key
	protected function verifyKey(){
		$key_size = mcrypt_get_key_size(self::$crypt_cipher,self::$crypt_mode);
		//verify we have a key before starting
		if(!isset($this->key) || is_null($this->key)){
			throw new Exception('No encryption key defined in config');
		}
		if(strlen(base64_decode($this->key)) < $key_size){
			throw new Exception('Encryption key is too shorted, required length: '.$key_size);
		}
		return true;
	}

	//verify existencve and size of iv
	protected function verifyIV(){
		$iv_size = mcrypt_get_iv_size(self::$crypt_cipher,self::$crypt_mode);
		//verify we have a key before starting
		if(!isset($this->iv) || is_null($this->iv)){
			throw new Exception('No IV key defined in config');
		}
		if(strlen(base64_decode($this->iv)) < $iv_size){
			throw new Exception('Encryption IV is too shorted, required length: '.$iv_size);
		}
		return true;
	}

	//encrypt string and optionally base64_encode
	public function encrypt($plain_string,$base64_encode=true){
		if(!$this->verified) $this->verify();
		//encrypt and return
		$enc_string = mcrypt_encrypt(
			 self::$crypt_cipher
			,base64_decode($this->key)
			,$plain_string
			,self::$crypt_mode
			,base64_decode($this->iv)
		);
		if($base64_encode) return base64_encode($enc_string);
		return $enc_string;
	}

	//decrypt string from an optionally base64_encoded source
	function decrypt($enc_string,$base64_decode=true){
		if(is_null($enc_string) || empty($enc_string)) return NULL;
		if(!$this->verified) $this->verify();
		//decrypt and return
		if($base64_decode) $enc_string = base64_decode($enc_string);
		return mcrypt_decrypt(
			 self::$crypt_cipher
			,base64_decode($this->key)
			,$enc_string
			,self::$crypt_mode
			,base64_decode($this->iv)
		);
	}

}
