<?php

namespace Cmmia\Encrypt;

class DataEncodeAndDecode{
	private $key;
	private $iv;
	public function __construct($key,$iv){
		$this->key = $key;
		$this->iv = $iv;
	}
	
	public function encode($text){
		$encrypted = openssl_encrypt($text, 'aes-256-cbc', base64_decode($this->key), OPENSSL_RAW_DATA, base64_decode($this->iv));
		return array(base64_encode($encrypted), md5($this->iv."+_+".$text));
	}
	public function decode($text,$hash){
		$text = openssl_decrypt(base64_decode($text), 'aes-256-cbc', base64_decode($this->key), OPENSSL_RAW_DATA, base64_decode($this->iv));
		if(md5($this->iv."+_+".$text) !== $hash){
			return 0;
		}
		return $text;
	}
}