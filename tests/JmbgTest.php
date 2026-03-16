<?php

namespace JmbgLabs\Jmbg\Tests;

use DateTime;
use JmbgLabs\Jmbg\Jmbg;
use JmbgLabs\Jmbg\JmbgException;
use PHPUnit\Framework\TestCase;

class JmbgTest extends TestCase
{
    public function testValidJmbgCanBeParsed(): void
    {
        $jmbg = Jmbg::parse('0710003730015');
        $this->assertInstanceOf(Jmbg::class, $jmbg);
    }

    public function testValidJmbgReturnsTrue(): void
    {
        $this->assertTrue(Jmbg::valid('0710003730015'));
    }

    public function testInvalidJmbgReturnsFalse(): void
    {
        $this->assertFalse(Jmbg::valid('1234567890123'));
    }

    public function testInvalidLengthThrowsException(): void
    {
        $this->expectException(JmbgException::class);
        $this->expectExceptionMessage('Input string must have 13 digits.');
        new Jmbg('123456789');
    }

    public function testNonNumericJmbgThrowsException(): void
    {
        $this->expectException(JmbgException::class);
        $this->expectExceptionMessage('JMBG string must have 13 digits.');
        new Jmbg('01019907100ab');
    }

    public function testInvalidDateThrowsException(): void
    {
        $this->expectException(JmbgException::class);
        $this->expectExceptionMessageMatches('/Date .* is not valid./');
        new Jmbg('3201990710009');
    }

    public function testInvalidRegionThrowsException(): void
    {
        $this->expectException(JmbgException::class);
        $this->expectExceptionMessage("Region '66' is not valid for Serbian JMBG.");
        new Jmbg('0710003660015');
    }

    public function testInvalidChecksumThrowsException(): void
    {
        $this->expectException(JmbgException::class);
        $this->expectExceptionMessage('Checksum is not valid.');
        new Jmbg('0710003730025');
    }

    public function testIsMaleReturnsTrue(): void
    {
        $jmbg = new Jmbg('0710003730015');
        $this->assertTrue($jmbg->isMale());
    }

    public function testIsMaleReturnsFalse(): void
    {
        $jmbg = new Jmbg('0710003735017');
        $this->assertFalse($jmbg->isMale());
    }

    public function testIsFemaleReturnsTrue(): void
    {
        $jmbg = new Jmbg('0710003735017');
        $this->assertTrue($jmbg->isFemale());
    }

    public function testIsFemaleReturnsFalse(): void
    {
        $jmbg = new Jmbg('0710003730015');
        $this->assertFalse($jmbg->isFemale());
    }

    public function testGetAge(): void
    {
        $jmbg = new Jmbg('0710003730015');
        $expectedAge = (new DateTime())->diff(new DateTime('2003-10-07'))->y;
        $this->assertEquals($expectedAge, $jmbg->getAge());
    }

    public function testGetDate(): void
    {
        $jmbg = new Jmbg('0710003730015');
        $date = $jmbg->getDate();
        $this->assertInstanceOf(DateTime::class, $date);
        $this->assertEquals('2003-10-07', $date->format('Y-m-d'));
    }

    public function testFormat(): void
    {
        $jmbg = new Jmbg('0710003730015');
        $this->assertEquals('0710003730015', $jmbg->format());
    }

    public function testToString(): void
    {
        $jmbg = new Jmbg('0710003730015');
        $this->assertEquals('0710003730015', (string)$jmbg);
    }

    public function testMagicGetters(): void
    {
        $jmbg = new Jmbg('2902992710005');

        $this->assertEquals('2902992710005', $jmbg->original);
        $this->assertEquals(29, $jmbg->day);
        $this->assertEquals('29', $jmbg->day_original);
        $this->assertEquals(2, $jmbg->month);
        $this->assertEquals('02', $jmbg->month_original);
        $this->assertEquals(1992, $jmbg->year);
        $this->assertEquals('992', $jmbg->year_original);
        $this->assertEquals(71, $jmbg->region);
        $this->assertEquals('71', $jmbg->region_original);
        $this->assertEquals('Belgrade', $jmbg->region_text);
        $this->assertEquals('7', $jmbg->country_code);
        $this->assertEquals('Serbia', $jmbg->country);
        $this->assertEquals(0, $jmbg->unique);
        $this->assertEquals('000', $jmbg->unique_original);
        $this->assertEquals(5, $jmbg->checksum);
    }

    public function testMagicIsset(): void
    {
        $jmbg = new Jmbg('0710003730015');
        $this->assertTrue(isset($jmbg->original));
        $this->assertFalse(isset($jmbg->nonexistent));
    }

    public function testTrimWhitespace(): void
    {
        $jmbg = new Jmbg('  0710003730015  ');
        $this->assertEquals('0710003730015', $jmbg->format());
    }

    public function testYearCalculationFor2000s(): void
    {
        $jmbg = new Jmbg('0101000710009');
        $this->assertEquals(2000, $jmbg->year);
    }

    public function testYearCalculationFor1900s(): void
    {
        $jmbg = new Jmbg('1705978730032');
        $this->assertEquals(1978, $jmbg->year);
    }

    public function testDifferentRegions(): void
    {
        // Belgrade
        $jmbg1 = new Jmbg('2902992710005');
        $this->assertEquals('Belgrade', $jmbg1->region_text);
        $this->assertEquals('Serbia', $jmbg1->country);

        // Novi Sad
        $jmbg2 = new Jmbg('1505995800002');
        $this->assertEquals('Novi Sad', $jmbg2->region_text);
        $this->assertEquals('Serbia/Vojvodina', $jmbg2->country);
    }

    public function testBoundaryUniqueNumbers(): void
    {
        // Male - unique 0
        $jmbg1 = new Jmbg('1505995800002');
        $this->assertTrue($jmbg1->isMale());
        $this->assertEquals(0, $jmbg1->unique);

        // Male - unique 499
        $jmbg2 = new Jmbg('1505995804997');
        $this->assertTrue($jmbg2->isMale());
        $this->assertEquals(499, $jmbg2->unique);

        // Female - unique 500
        $jmbg3 = new Jmbg('1505995805004');
        $this->assertTrue($jmbg3->isFemale());
        $this->assertEquals(500, $jmbg3->unique);

        // Female - unique 999
        $jmbg4 = new Jmbg('1505995809999');
        $this->assertTrue($jmbg4->isFemale());
        $this->assertEquals(999, $jmbg4->unique);
    }

    public function testLeapYearDate(): void
    {
        $jmbg = new Jmbg('2902992710005');
        $this->assertEquals(29, $jmbg->day);
        $this->assertEquals(2, $jmbg->month);
        $this->assertEquals(1992, $jmbg->year);
    }

    public function testInvalidLeapYearDateThrowsException(): void
    {
        $this->expectException(JmbgException::class);
        $this->expectExceptionMessageMatches('/Date .* is not valid./');
        new Jmbg('2902979758318');
    }
}
