<?php

namespace Tooly\Tests\Script\Helper;

use TM\GPG\Verification\Exception\VerificationException;
use Tooly\Script\Helper\Verifier;
use TM\GPG\Verification\Verifier as GPGVerifier;

/**
 * @package Tooly\Tests\Helper
 */
class VerifierTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCheckIfFileSumsAreEqual()
    {
        $verifier = new Verifier;
        $this->assertTrue($verifier->checkFileSum(
            __DIR__ . '/../../../resources/phpstorm-setting.png',
            __DIR__ . '/../../../resources/phpstorm-setting.png'
        ));
    }

    public function testNotExistTargetFileReturnsFalse()
    {
        $verifier = new Verifier;
        $this->assertFalse($verifier->checkFileSum(
            __DIR__ . '/../../../resources/foo',
            __DIR__ . '/../../../resources/phpstorm-setting.png'
        ));
    }

    public function testIfNoVerifierGivenSignatureCheckReturnsTrue()
    {
        $verifier = new Verifier;
        $this->assertTrue($verifier->checkGPGSignature('foo.sign', 'foo'));
    }
}
