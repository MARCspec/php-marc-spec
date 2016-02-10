<?php
/*
* (c) Carsten Klee <mailme.klee@yahoo.de>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace CK\MARCspec\Test;

use CK\MARCspec\Field;
use CK\MARCspec\MARCspec;
use CK\MARCspec\SubSpec;

/**
 * @covers CK\MARCspec\Field
 * @covers CK\MARCspec\PositionOrRange
 */
class FieldTest extends \PHPUnit_Framework_TestCase
{
    protected $validTests = [];
    protected $invalidTests = [];

    protected function setUp()
    {
        if (0 < count($this->validTests)) {
            return;
        }
        $this->validTests[] = json_decode(file_get_contents(__DIR__.'/../'.'vendor/ck/marcspec-test-suite/valid/validFieldTag.json'));
        $this->invalidTests[] = json_decode(file_get_contents(__DIR__.'/../'.'vendor/ck/marcspec-test-suite/invalid/invalidFieldTag.json'));
    }

    public function fieldspec($arg)
    {
        return new Field($arg);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument2Decode()
    {
        $this->fieldspec(['245']);
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec13()
    {
        $this->fieldspec('007/');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec14()
    {
        $this->fieldspec('007/1-2-');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec15()
    {
        $this->fieldspec('24#');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec16()
    {
        $this->fieldspec('007/-2');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec17()
    {
        $this->fieldspec('245[-2]');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec18()
    {
        $this->fieldspec('245[1-2-]');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec19()
    {
        $this->fieldspec('245[1-2');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec110()
    {
        $this->fieldspec('007/1-X');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec111()
    {
        $this->fieldspec('007/#-');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec112()
    {
        $this->fieldspec('245[0-2a]');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec113()
    {
        $this->fieldspec('300[1-]');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec114()
    {
        $fieldSpec = $this->fieldspec(null);
        $fieldSpec['tag'] = 'aA0';
    }

    /****
    * invalid indicators
    ***/

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec30()
    {
        $this->fieldspec('245_1+');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec31()
    {
        $this->fieldspec('245_123');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec32()
    {
        $this->fieldspec('245_$');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec33()
    {
        $this->fieldspec('245_1|');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidFieldSpec34()
    {
        $this->fieldspec('245_10_');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidArgument310Decode()
    {
        $this->fieldspec('245{$c=$d}$a');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidArgument311Decode()
    {
        $this->fieldspec('245[1]/1_01');
    }

    /**
     * assert same field tag.
     */
    public function testValidFieldSpec1()
    {
        $fieldSpec = $this->fieldspec('LDR');
        $this->assertSame('LDR', $fieldSpec->getTag());

        $fieldSpec = $this->fieldspec('245');
        $this->assertSame('245', $fieldSpec->getTag());

        $fieldSpec = $this->fieldspec('...');
        $this->assertSame('...', $fieldSpec->getTag());

        $fieldSpec = $this->fieldspec('245[1]');
        $this->assertSame('245', $fieldSpec->getTag());
        $this->assertSame(1, $fieldSpec->getIndexStart());

        $fieldSpec = $this->fieldspec('245[1-3]');
        $this->assertSame(1, $fieldSpec->getIndexStart());
        $this->assertSame(3, $fieldSpec->getIndexEnd());

        $fieldSpec = $this->fieldspec('245[1-#]');
        $this->assertSame(1, $fieldSpec->getIndexStart());
        $this->assertSame('#', $fieldSpec->getIndexEnd());

        $fieldSpec = $this->fieldspec('245[#-3]');
        $this->assertSame('#', $fieldSpec->getIndexStart());
        $this->assertSame(3, $fieldSpec->getIndexEnd());
    }

    /**
     * test character position and range.
     */
    public function testValidFieldSpec2()
    {
        $fieldSpec = $this->fieldspec('LDR/0-3');
        $this->assertSame('LDR', $fieldSpec->getTag());
        $this->assertSame(0, $fieldSpec->getCharStart());
        $this->assertSame(3, $fieldSpec->getCharEnd());
        $this->assertSame(4, $fieldSpec->getCharLength());

        $fieldSpec = $this->fieldspec('LDR/0-#');
        $this->assertSame('LDR', $fieldSpec->getTag());
        $this->assertSame(0, $fieldSpec->getCharStart());
        $this->assertSame('#', $fieldSpec->getCharEnd());
        $this->assertSame(null, $fieldSpec->getCharLength());

        $fieldSpec = $this->fieldspec('LDR/#-4');
        $this->assertSame('LDR', $fieldSpec->getTag());
        $this->assertSame('#', $fieldSpec->getCharStart());
        $this->assertSame(4, $fieldSpec->getCharEnd());
        $this->assertSame(5, $fieldSpec->getCharLength());

        $fieldSpec = $this->fieldspec('LDR/#-0');
        $this->assertSame('LDR', $fieldSpec->getTag());
        $this->assertSame('#', $fieldSpec->getCharStart());
        $this->assertSame(0, $fieldSpec->getCharEnd());
        $this->assertSame(1, $fieldSpec->getCharLength());
    }

    /**
     * test character range.
     */
    public function testValidFieldSpec22()
    {
        $fieldSpec = $this->fieldspec('245/#');
        $this->assertSame(1, $fieldSpec->getCharLength());
        $fieldSpec = $this->fieldspec('245/#-#');
        $this->assertSame(1, $fieldSpec->getCharLength());
        $fieldSpec = $this->fieldspec('245/#-0');
        $this->assertSame(1, $fieldSpec->getCharLength());
        $fieldSpec = $this->fieldspec('245/#-1');
        $this->assertSame(2, $fieldSpec->getCharLength());
        $fieldSpec = $this->fieldspec('245/0-#');
        $this->assertSame(0, $fieldSpec->getCharStart());
        $this->assertSame('#', $fieldSpec->getCharEnd());
        $this->assertSame(null, $fieldSpec->getCharLength());
    }

    /**
     * test indicators.
     */
    public function testValidFieldSpec23()
    {
        $fieldSpec = $this->fieldspec('245_0');
        $this->assertSame('0', $fieldSpec->getindicator1());
        $fieldSpec = $this->fieldspec('245__0');
        $this->assertSame('0', $fieldSpec->getindicator2());
        $fieldSpec = $this->fieldspec('245_0_');
        $this->assertSame('0', $fieldSpec->getindicator1());
        $fieldSpec = $this->fieldspec('245[1]_01');
        $this->assertSame('0', $fieldSpec->getindicator1());
        $this->assertSame('1', $fieldSpec->getindicator2());
    }


    public function testValidFieldSpec24()
    {
        $fieldSpec = $this->fieldspec(null);
        $fieldSpec['tag'] = '...';
        $fieldSpec['indexStart'] = '0';
        $fieldSpec['indexEnd'] = '1';
        $fieldSpec['indicator1'] = '0';
        $fieldSpec['indicator2'] = '1';
        $Subspec = new SubSpec(new MARCspec('245$b'), '!=', new MARCspec('245$c'));
        $fieldSpec['subSpecs'] = $Subspec;
        $fieldSpec->addSubSpec($Subspec);
        $this->assertTrue($fieldSpec->offsetExists('tag'));
        $this->assertTrue($fieldSpec->offsetExists('indexStart'));
        $this->assertTrue($fieldSpec->offsetExists('indexEnd'));
        $this->assertTrue($fieldSpec->offsetExists('indicator1'));
        $this->assertTrue($fieldSpec->offsetExists('indicator2'));
        $this->assertTrue($fieldSpec->offsetExists('subSpecs'));
    }

    /**
     * test character position and range.
     */
    public function testSetAndGetChar()
    {
        $fieldSpec = $this->fieldspec('LDR');
        $fieldSpec->setCharStartEnd(0, 3);
        $this->assertSame('LDR', $fieldSpec->getTag());
        $this->assertSame(0, $fieldSpec->getCharStart());
        $this->assertSame(3, $fieldSpec->getCharEnd());
        $this->assertSame(4, $fieldSpec->getCharLength());

        $fieldSpec = $this->fieldspec('LDR');
        $fieldSpec->setCharStartEnd('#', 3);
        $this->assertSame('LDR', $fieldSpec->getTag());
        $this->assertSame('#', $fieldSpec->getCharStart());
        $this->assertSame(3, $fieldSpec->getCharEnd());
        $this->assertSame(4, $fieldSpec->getCharLength());

        $fieldSpec = $this->fieldspec('LDR');
        $fieldSpec->setCharStartEnd(0, 4);
        $this->assertSame('LDR', $fieldSpec->getTag());
        $this->assertSame(0, $fieldSpec->getCharStart());
        $this->assertSame(4, $fieldSpec->getCharEnd());
        $this->assertSame(5, $fieldSpec->getCharLength());

        $fieldSpec = $this->fieldspec('LDR');
        $fieldSpec->setCharStartLength('#', 4);
        $this->assertSame('LDR', $fieldSpec->getTag());
        $this->assertSame('#', $fieldSpec->getCharStart());
        $this->assertSame(3, $fieldSpec->getCharEnd());
        $this->assertSame(4, $fieldSpec->getCharLength());
    }

    /**
     * test index position and range.
     */
    public function testSetAndGetIndex()
    {
        $fieldSpec = $this->fieldspec('300');
        $fieldSpec->setIndexStartEnd(0, 3);
        $this->assertSame('300', $fieldSpec->getTag());
        $this->assertSame(0, $fieldSpec->getIndexStart());
        $this->assertSame(3, $fieldSpec->getIndexEnd());
        $this->assertSame(4, $fieldSpec->getIndexLength());

        $fieldSpec = $this->fieldspec('300');
        $fieldSpec->setIndexStartEnd('#', 3);
        $this->assertSame('300', $fieldSpec->getTag());
        $this->assertSame('#', $fieldSpec->getIndexStart());
        $this->assertSame(3, $fieldSpec->getIndexEnd());
        $this->assertSame(4, $fieldSpec->getIndexLength());

        $fieldSpec = $this->fieldspec('300');
        $fieldSpec->setIndexStartEnd(0, 4);
        $this->assertSame('300', $fieldSpec->getTag());
        $this->assertSame(0, $fieldSpec->getIndexStart());
        $this->assertSame(4, $fieldSpec->getIndexEnd());
        $this->assertSame(5, $fieldSpec->getIndexLength());

        $fieldSpec = $this->fieldspec('300');
        $fieldSpec->setIndexStartLength('#', 4);
        $this->assertSame('300', $fieldSpec->getTag());
        $this->assertSame('#', $fieldSpec->getIndexStart());
        $this->assertSame(3, $fieldSpec->getIndexEnd());
        $this->assertSame(4, $fieldSpec->getIndexLength());

        $fieldSpec = $this->fieldspec('300');
        $fieldSpec->setIndexStartLength(0, 6);
        $this->assertSame('300', $fieldSpec->getTag());
        $this->assertSame(0, $fieldSpec->getIndexStart());
        $this->assertSame(5, $fieldSpec->getIndexEnd());
        $this->assertSame(6, $fieldSpec->getIndexLength());
    }

    /**
     * test encoding.
     */
    public function testEncode()
    {
        $fieldSpec = $this->fieldspec('245');
        $this->assertSame('245[0-#]', "$fieldSpec");

        $fieldSpec = $this->fieldspec('245_1');
        $this->assertSame('245[0-#]_1_', "$fieldSpec");

        $fieldSpec = $this->fieldspec('245__0');
        $this->assertSame('245[0-#]__0', "$fieldSpec");

        $fieldSpec = $this->fieldspec('245_1_');
        $this->assertSame('245[0-#]_1_', "$fieldSpec");

        $fieldSpec = $this->fieldspec('007/1');
        $this->assertSame('007[0-#]/1', "$fieldSpec");
        $this->assertSame(1, $fieldSpec->getCharLength());

        $fieldSpec = $this->fieldspec('007/1-3');
        $this->assertSame('007[0-#]/1-3', "$fieldSpec");
        $this->assertSame(3, $fieldSpec->getCharLength());

        $fieldSpec = $this->fieldspec('300[1]');
        $this->assertSame('300[1]', "$fieldSpec");

        $fieldSpec = $this->fieldspec('300[1-3]');
        $this->assertSame('300[1-3]', "$fieldSpec");
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testOffsetUnset()
    {
        $fieldSpec = $this->fieldspec('245');
        unset($fieldSpec['tag']);
    }

    public function testInvalidFromTestSuite()
    {
        foreach ($this->invalidTests as $invalid) {
            foreach ($invalid->{'tests'} as $test) {
                try {
                    new Field($test->{'data'});
                } catch (\Exception $e) {
                    continue;
                }
                $this->fail('An expected exception has not been raised for '.$test->{'data'});
            }
        }
    }

    public function testValidFromTestSuite()
    {
        foreach ($this->validTests as $valid) {
            foreach ($valid->{'tests'} as $test) {
                new Field($test->{'data'});
            }
        }
    }
}
