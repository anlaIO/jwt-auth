<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Anla sheng <anlasheng@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Anla\JWTAuth\Providers\JWT;

use Exception;
use ReflectionClass;
use Namshi\JOSE\JWS;
use ReflectionException;
use InvalidArgumentException;
use Namshi\JOSE\Signer\OpenSSL\PublicKey;
use Anla\JWTAuth\Contracts\Providers\JWT;
use Anla\JWTAuth\Exceptions\JWTException;
use Anla\JWTAuth\Exceptions\TokenInvalidException;

class Namshi extends Provider implements JWT
{
    /**
     * @var \Namshi\JOSE\JWS
     */
    protected $jws;

    /**
     * @param  string  $secret
     * @param  string  $algo
     * @param  array  $keys
     * @param  string|null  $driver
     *
     * @return void
     */
    public function __construct($secret, $algo, array $keys = [], $driver = null)
    {
        parent::__construct($secret, $keys, $algo);

        $this->jws = $driver ?: new JWS(['typ' => 'JWT', 'alg' => $algo]);
    }

    /**
     * Create a JSON Web Token.
     *
     * @param  array  $payload
     *
     * @throws \Anla\JWTAuth\Exceptions\JWTException
     *
     * @return string
     */
    public function encode(array $payload)
    {
        try {
            $this->jws->setPayload($payload)->sign($this->getSigningKey(), $this->getPassphrase());

            return (string) $this->jws->getTokenString();
        } catch (Exception $e) {
            throw new JWTException('Could not create token: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Decode a JSON Web Token.
     *
     * @param  string  $token
     *
     * @throws \Anla\JWTAuth\Exceptions\JWTException
     *
     * @return array
     */
    public function decode($token)
    {
        try {
            // Let's never allow insecure tokens
            $jws = $this->jws->load($token, false);
        } catch (InvalidArgumentException $e) {
            throw new TokenInvalidException('Could not decode token: '.$e->getMessage(), $e->getCode(), $e);
        }

        if (! $jws->verify($this->getVerificationKey(), $this->getAlgo())) {
            throw new TokenInvalidException('Token Signature could not be verified.');
        }

        return (array) $jws->getPayload();
    }

    /**
     * Determine if the algorithm is asymmetric, and thus
     * requires a public/private key combo.
     *
     * @return bool
     */
    protected function isAsymmetric()
    {
        try {
            return (new ReflectionClass(sprintf('Namshi\\JOSE\\Signer\\OpenSSL\\%s', $this->getAlgo())))->isSubclassOf(PublicKey::class);
        } catch (ReflectionException $e) {
            throw new JWTException('The given algorithm could not be found', $e->getCode(), $e);
        }
    }

    /**
     * Get the key used to sign the tokens.
     *
     * @return resource|string
     */
    protected function getSigningKey()
    {
        return $this->isAsymmetric() ? $this->getPrivateKey() : $this->getSecret();
    }

    /**
     * Get the key used to verify the tokens.
     *
     * @return resource|string
     */
    protected function getVerificationKey()
    {
        return $this->isAsymmetric() ? $this->getPublicKey() : $this->getSecret();
    }
}
