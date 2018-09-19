<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test\Security;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ApiBundle\Exception\ExpiredTokenException;
use HeimrichHannot\ApiBundle\Exception\InvalidJWTException;
use HeimrichHannot\ApiBundle\Security\JWTCoder;

class JwtCoderTest extends ContaoTestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $encoder = new JWTCoder('secret');

        $this->assertInstanceOf('HeimrichHannot\ApiBundle\Security\JWTCoder', $encoder);
    }

    /**
     * Test encode().
     */
    public function testEncode()
    {
        $time = time();

        $encoder = new JWTCoder('secret');
        $payload = ['username' => 'user@test.tld', 'entity' => 'huh.api.entity.user'];
        $token = $encoder->encode($payload);
        $tokenPayload = $encoder->decode($token);

        $this->assertEquals($tokenPayload->username, $payload['username']);
        $this->assertEquals($tokenPayload->entity, $payload['entity']);
        $this->assertGreaterThanOrEqual($time, $tokenPayload->iat);
        $this->assertGreaterThanOrEqual($time, $tokenPayload->exp);
        $this->assertTrue($tokenPayload->exp > $tokenPayload->iat);
    }

    /**
     * Test encode().
     */
    public function testExpired()
    {
        $this->expectException(ExpiredTokenException::class);
        $payload = ['username' => 'user@test.tld', 'entity' => 'huh.api.entity.user'];

        $encoder = new JWTCoder('secret');
        $token = $encoder->encode($payload, 0);
        sleep(1);
        $encoder->decode($token);
    }

    /**
     * Test encode().
     */
    public function testDecodeInvalidToken()
    {
        $this->expectException(InvalidJWTException::class);

        $encoder = new JWTCoder('secret');

        // secret used for this was wrongSecret
        $encoder->decode('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6InVzZXJAdGVzdC50bGQiLCJlbnRpdHkiOiJodWguYXBpLmVudGl0eS51c2VyIn0.LDLlpAaVrPmfQ2P9tNYI8yyv0niqs1aTDj_cqc64ypk');
    }
}
