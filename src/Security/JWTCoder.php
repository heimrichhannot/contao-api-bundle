<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ApiBundle\Security;


use Firebase\JWT\JWT;
use HeimrichHannot\ApiBundle\Exception\InvalidJWTException;

class JWTCoder
{
    const ALG = 'HS256';
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * @param array $payload
     * @param int   $ttl
     *
     * @return string
     */
    public function encode(array $payload, $ttl = 86400)
    {
        $payload['iat'] = time();
        $payload['exp'] = time() + $ttl;

        return JWT::encode($payload, $this->key, self::ALG);
    }

    /**
     * @param string $token
     *
     * @return object
     *
     * @throws InvalidJWTException
     */
    public function decode($token)
    {
        try {
            $payload = JWT::decode($token, $this->key, [self::ALG]);
        } catch (\Exception $e) {
            throw new InvalidJWTException('huh.api.exception.auth.invalid_token');
        }

        if ($this->isExpired($payload)) {
            throw new InvalidJWTException('huh.api.exception.auth.token_expired');
        }

        return $payload;
    }

    /**
     * @param object $payload
     *
     * @return bool
     */
    private function isExpired($payload)
    {
        if (isset($payload->exp) && is_numeric($payload->exp)) {
            return (time() - $payload->exp) > 0;
        }

        return false;
    }
}