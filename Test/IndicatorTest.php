<?php
/*
* (c) Carsten Klee <mailme.klee@yahoo.de>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace CK\MARCspec\Test;

use CK\MARCspec\Indicator;
use CK\MARCspec\MARCspec;
use CK\MARCspec\SubSpec;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class IndicatorTest extends TestCase
{
    protected function indicatorPos($arg)
    {
        $ind = new Indicator($arg);

        return $ind->getPos();
    }

    #[DataProvider('invalidFromTestSuiteProvider')]
    public function testInvalidFromTestSuite($test)
    {
        $this->expectException(\Exception::class);
        new Indicator($test);
    }

    public static function invalidFromTestSuiteProvider()
    {
        $invalidTests = json_decode(file_get_contents(__DIR__ . '/../' . 'vendor/ck/marcspec-test-suite/invalid/invalidIndicators.json'));
        $data = [];
        foreach ($invalidTests->{'tests'} as $test) {
            $data[0][] = $test->{'data'};
        }

        return $data;
    }

    public function testValidFromTestSuite()
    {
        $validTests = json_decode(file_get_contents(__DIR__ . '/../' . 'vendor/ck/marcspec-test-suite/valid/validIndicators.json'));
        foreach ($validTests->{'tests'} as $test) {
            $pos = $this->indicatorPos($test->{'data'});
            $this->assertTrue($pos === '1' or $pos === '2');
        }
    }

    /**
     */
    public function testPosFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $ind = new Indicator('3');
    }

    public function testSetPos()
    {
        $ind = new Indicator(2);
        $this->assertInstanceOf('CK\MARCspec\IndicatorInterface', $ind);
        $this->assertSame('2', $ind->getPos());
        $this->assertSame('^2', $ind->getBaseSpec());
        $ind = new Indicator('1');
        $this->assertSame('1', $ind->getPos());
        $this->assertSame('^1', $ind->getBaseSpec());
    }

    /**
     */
    public function testIndicatorFail1()
    {
        $this->expectException(\InvalidArgumentException::class);
        $ind = new Indicator(3);
    }

    /**
     */
    public function testIndicatorFail2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $ind = new Indicator(['3']);
    }

    /**
     */
    public function testIndicatorFail3()
    {
        $this->expectException(\InvalidArgumentException::class);
        $ind = new Indicator('12');
    }

    /**
     */
    public function testIndicatorFail4()
    {
        $this->expectException(\CK\MARCspec\Exception\InvalidMARCspecException::class);
        $ind = new Indicator('1{$a}');
    }

    public function testSubspecsAndArrayAccessAndToString()
    {
        $ind = new Indicator('1');
        $Subspec = new SubSpec(new MARCspec('245$b'), '!=', new MARCspec('245$c'));
        $ind['subSpecs'] = $Subspec;
        $this->assertSame('^1{245$b!=245$c}', $ind->__toString());
        $ind->addSubSpec($Subspec);
        $this->assertTrue($ind->offsetExists('position'));
        $this->assertTrue($ind->offsetExists('subSpecs'));
    }

    /**
     * JSON encoding of an indicator spec.
     */
    public function testJson()
    {
        $ind = new Indicator('2');
        $_ind['position'] = '2';
        $this->assertSame(json_encode($_ind), json_encode($ind));
    }
}
