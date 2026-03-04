<?php

declare(strict_types=1);

/**
 * Copyright 2024-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Ralf Lang <lang@b1-systems.de>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Argv
 */

namespace Horde\Argv\Test\Modern\Validator;

use Horde\Argv\Modern\Validator\Validators;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Validators::class)]
class ValidatorsTest extends TestCase
{
    public function testRangeValidatorAcceptsValueInRange(): void
    {
        $validator = Validators::range(1, 10);
        $this->assertTrue($validator(5));
    }

    public function testRangeValidatorRejectsValueBelowMin(): void
    {
        $validator = Validators::range(1, 10);
        $result = $validator(0);
        $this->assertIsString($result);
        $this->assertStringContainsString('between 1 and 10', $result);
    }

    public function testRangeValidatorRejectsValueAboveMax(): void
    {
        $validator = Validators::range(1, 10);
        $result = $validator(11);
        $this->assertIsString($result);
        $this->assertStringContainsString('between 1 and 10', $result);
    }

    public function testRangeValidatorAcceptsBoundaryValues(): void
    {
        $validator = Validators::range(1, 10);
        $this->assertTrue($validator(1));
        $this->assertTrue($validator(10));
    }

    public function testRegexValidatorAcceptsMatchingValue(): void
    {
        $validator = Validators::regex('/^[a-z]+$/');
        $this->assertTrue($validator('abc'));
    }

    public function testRegexValidatorRejectsNonMatchingValue(): void
    {
        $validator = Validators::regex('/^[a-z]+$/');
        $result = $validator('ABC123');
        $this->assertIsString($result);
    }

    public function testRegexValidatorWithCustomDescription(): void
    {
        $validator = Validators::regex('/^\d{3}$/', 'Must be exactly 3 digits');
        $result = $validator('12');
        $this->assertIsString($result);
        $this->assertSame('Must be exactly 3 digits', $result);
    }

    public function testChoiceValidatorAcceptsAllowedValue(): void
    {
        $validator = Validators::choice(['json', 'xml', 'csv']);
        $this->assertTrue($validator('json'));
    }

    public function testChoiceValidatorRejectsDisallowedValue(): void
    {
        $validator = Validators::choice(['json', 'xml']);
        $result = $validator('yaml');
        $this->assertIsString($result);
        $this->assertStringContainsString("'json'", $result);
        $this->assertStringContainsString("'xml'", $result);
    }

    public function testMinLengthValidatorAcceptsLongEnoughString(): void
    {
        $validator = Validators::minLength(3);
        $this->assertTrue($validator('abc'));
        $this->assertTrue($validator('abcd'));
    }

    public function testMinLengthValidatorRejectsTooShortString(): void
    {
        $validator = Validators::minLength(3);
        $result = $validator('ab');
        $this->assertIsString($result);
        $this->assertStringContainsString('at least 3', $result);
    }

    public function testMaxLengthValidatorAcceptsShortEnoughString(): void
    {
        $validator = Validators::maxLength(5);
        $this->assertTrue($validator('abc'));
        $this->assertTrue($validator('abcde'));
    }

    public function testMaxLengthValidatorRejectsTooLongString(): void
    {
        $validator = Validators::maxLength(5);
        $result = $validator('abcdef');
        $this->assertIsString($result);
        $this->assertStringContainsString('at most 5', $result);
    }

    public function testEmailValidatorAcceptsValidEmail(): void
    {
        $validator = Validators::email();
        $this->assertTrue($validator('test@example.com'));
    }

    public function testEmailValidatorRejectsInvalidEmail(): void
    {
        $validator = Validators::email();
        $result = $validator('not-an-email');
        $this->assertIsString($result);
        $this->assertStringContainsString('email', strtolower($result));
    }

    public function testUrlValidatorAcceptsValidUrl(): void
    {
        $validator = Validators::url();
        $this->assertTrue($validator('https://example.com'));
        $this->assertTrue($validator('http://example.com/path'));
    }

    public function testUrlValidatorRejectsInvalidUrl(): void
    {
        $validator = Validators::url();
        $result = $validator('not-a-url');
        $this->assertIsString($result);
    }

    public function testAllValidatorPassesWhenAllValidatorsPass(): void
    {
        $validator = Validators::all(
            Validators::minLength(3),
            Validators::maxLength(10)
        );
        $this->assertTrue($validator('hello'));
    }

    public function testAllValidatorFailsWhenAnyValidatorFails(): void
    {
        $validator = Validators::all(
            Validators::minLength(3),
            Validators::maxLength(5)
        );
        $result = $validator('toolong123');
        $this->assertIsString($result);
        $this->assertStringContainsString('at most 5', $result);
    }

    public function testAllValidatorReturnsFirstFailure(): void
    {
        $validator = Validators::all(
            Validators::minLength(10),  // Will fail
            Validators::maxLength(5)     // Would also fail
        );
        $result = $validator('short');
        $this->assertStringContainsString('at least 10', $result);
    }

    public function testAnyValidatorPassesWhenAnyValidatorPasses(): void
    {
        $validator = Validators::any(
            Validators::regex('/^\d+$/'),  // Numbers only
            Validators::regex('/^[a-z]+$/')  // Letters only
        );
        $this->assertTrue($validator('123'));
        $this->assertTrue($validator('abc'));
    }

    public function testAnyValidatorFailsWhenAllValidatorsFail(): void
    {
        $validator = Validators::any(
            Validators::regex('/^\d+$/'),
            Validators::regex('/^[a-z]+$/')
        );
        $result = $validator('ABC123');
        $this->assertIsString($result);
        $this->assertStringContainsString('All validations failed', $result);
    }

    public function testFileValidatorCanCheckExistence(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        try {
            $validator = Validators::file(mustExist: true);
            $this->assertTrue($validator($tempFile));

            $resultNonExistent = $validator('/nonexistent/file.txt');
            $this->assertIsString($resultNonExistent);
            $this->assertStringContainsString('not found', $resultNonExistent);
        } finally {
            unlink($tempFile);
        }
    }

    public function testFileValidatorCanCheckReadability(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        try {
            $validator = Validators::file(mustExist: true, mustBeReadable: true);
            $this->assertTrue($validator($tempFile));
        } finally {
            unlink($tempFile);
        }
    }

    public function testDirectoryValidatorCanCheckExistence(): void
    {
        $validator = Validators::directory(mustExist: true);
        $this->assertTrue($validator(sys_get_temp_dir()));

        $result = $validator('/nonexistent/directory');
        $this->assertIsString($result);
        $this->assertStringContainsString('not found', $result);
    }

    public function testValidatorsAreComposable(): void
    {
        $validator = Validators::all(
            Validators::minLength(5),
            Validators::maxLength(20),
            Validators::regex('/^[a-z]+$/', 'Lowercase letters only')
        );

        $this->assertTrue($validator('hello'));

        $resultTooShort = $validator('hi');
        $this->assertStringContainsString('at least 5', $resultTooShort);

        $resultInvalidFormat = $validator('Hello');
        $this->assertStringContainsString('Lowercase letters only', $resultInvalidFormat);
    }
}
