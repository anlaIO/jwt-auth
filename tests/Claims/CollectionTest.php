<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Anla sheng <anlasheng@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Anla\JWTAuth\Test\Claims;

use Anla\JWTAuth\Claims\JwtId;
use Anla\JWTAuth\Claims\Issuer;
use Anla\JWTAuth\Claims\Subject;
use Anla\JWTAuth\Claims\IssuedAt;
use Anla\JWTAuth\Claims\NotBefore;
use Anla\JWTAuth\Claims\Expiration;
use Anla\JWTAuth\Claims\Collection;
use Anla\JWTAuth\Test\AbstractTestCase;

class CollectionTest extends AbstractTestCase
{
    private function getCollection()
    {
        $claims = [
            new Subject(1),
            new Issuer('http://example.com'),
            new Expiration($this->testNowTimestamp + 3600),
            new NotBefore($this->testNowTimestamp),
            new IssuedAt($this->testNowTimestamp),
            new JwtId('foo'),
        ];

        return new Collection($claims);
    }

    /** @test */
    public function it_should_sanitize_the_claims_to_associative_array()
    {
        $collection = $this->getCollection();

        $this->assertSame(array_keys($collection->toArray()), ['sub', 'iss', 'exp', 'nbf', 'iat', 'jti']);
    }

    /** @test */
    public function it_should_determine_if_a_collection_contains_all_the_given_claims()
    {
        $collection = $this->getCollection();

        $this->assertFalse($collection->hasAllClaims(['sub', 'iss', 'exp', 'nbf', 'iat', 'jti', 'abc']));
        $this->assertFalse($collection->hasAllClaims(['foo', 'bar']));
        $this->assertFalse($collection->hasAllClaims([]));

        $this->assertTrue($collection->hasAllClaims(['sub', 'iss']));
        $this->assertTrue($collection->hasAllClaims(['sub', 'iss', 'exp', 'nbf', 'iat', 'jti']));
    }

    /** @test */
    public function it_should_get_a_claim_instance_by_name()
    {
        $collection = $this->getCollection();

        $this->assertInstanceOf(Expiration::class, $collection->getByClaimName('exp'));
        $this->assertInstanceOf(Subject::class, $collection->getByClaimName('sub'));
    }
}
