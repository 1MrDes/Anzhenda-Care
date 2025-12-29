<?php


namespace vm\org\qcloud;


use TencentCloud\Common\Credential;

abstract class QcloudBase
{
    /**
     * @var Credential
     */
    protected $cred;
    protected $secretId;
    protected $secretKey;
    /**
     * @var string token
     */
    protected $token;

    public function __construct($secretId, $secretKey, $token = null)
    {
        $this->secretId = $secretId;
        $this->secretKey = $secretKey;
        $this->token = $token;
        $this->cred = new Credential($secretId, $secretKey, $token);
    }
}