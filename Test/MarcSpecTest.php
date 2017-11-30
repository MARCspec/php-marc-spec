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
use PHPUnit\Framework\TestCase;

class MarcSpecTest extends TestCase
{
    /**
     * @dataProvider invalidFromTestSuiteProvider
     *
     * @expectedException Exception
     */
    public function testInvalidFromTestSuite($test)
    {
        new MARCspec($test);
    }

    public function invalidFromTestSuiteProvider()
    {
        foreach (glob(__DIR__.'/../'.'vendor/ck/marcspec-test-suite/invalid/wildCombination_*.json') as $filename) {
            $invalidTests = json_decode(file_get_contents($filename));
        }
        $data = [];
        foreach ($invalidTests->{'tests'} as $test) {
            $data[0][] = $test->{'data'};
        }

        return $data;
    }

    public function testValidFromTestSuite()
    {
        foreach (glob(__DIR__.'/../'.'vendor/ck/marcspec-test-suite/valid/wildCombination_*.json') as $filename) {
            $validTests = json_decode(file_get_contents($filename));
        }
        foreach ($validTests->{'tests'} as $test) {
            $this->assertInstanceOf('CK\MARCspec\MARCspecInterface', new MARCspec($test->{'data'}));
        }
    }

    public function marcspec($arg)
    {
        return new MARCspec($arg);
    }

    /****
    * invalid data types
    ***/

    /**
     * @expectedException RuntimeException
     */
    public function testInvalidArgument01Decode()
    {
        $this->marcspec('24');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument1Decode()
    {
        $this->marcspec((int) '245$a');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument2Decode()
    {
        $this->marcspec(['245$a']);
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidArgument3Decode()
    {
        $this->marcspec('245/#$a');
    }

    /**
     * assert same subfields.
     */
    public function testValidMarcSpec1()
    {
        $marcSpec = $this->marcspec('245$a-c');
        $this->assertSame(3, count($marcSpec['subfields']));
    }

    /**
     * assert same subfields.
     */
    public function testValidMarcSpec2()
    {
        $marcSpec = $this->marcspec('245');
        $marcSpec['subfields'] = '$d-f';
        $this->assertSame(3, count($marcSpec['subfields']));
    }

    /**
     * assert same specs.
     */
    public function testValidMarcSpec4()
    {
        $marcSpec = $this->marcspec('...[#]/1-3');
        $this->assertSame('...[#]/1-3', "$marcSpec");
    }

    /**
     * assert same specs.
     */
    public function testValidMarcSpec3()
    {
        $field = new Field('245');
        $marcSpec = MARCspec::setField($field);
        $marcSpec['subfields'] = '$d{$c/#=\.}{?$a}';
        $_subfields = $marcSpec['subfields'];
        $this->assertSame(1, count($_subfields));
        $leftFieldTag = $marcSpec['d'][0]['subSpecs'][0]['leftSubTerm']['field']['tag'];
        $this->assertSame('245', $leftFieldTag);
        $rightSubfieldTag = $marcSpec['d'][0]['subSpecs'][1]['rightSubTerm']['subfields'][0]['tag'];
        $this->assertSame('.', $marcSpec['d'][0]['subSpecs'][0]['rightSubTerm']['comparable']);
    }

    /**
     * assert same specs.
     */
    public function testValidMarcSpec5()
    {
        $ms = $this->marcspec('245[0]{$a!=$b|300^1!~\1}{\!\=!=\!}');

        // field
        $this->assertSame('245', $ms['field']['tag']);
        $this->assertSame(0, $ms['field']['indexStart']);
        $this->assertSame(2, count($ms['field']['subSpecs']));

        // field subspecs

        // subspec 00
        $this->assertSame('245', $ms['field']['subSpecs'][0][0]['leftSubTerm']['field']['tag']);
        $this->assertSame(0, $ms['field']['subSpecs'][0][0]['leftSubTerm']['field']['indexStart']);
        $this->assertSame('a', $ms['field']['subSpecs'][0][0]['leftSubTerm']['subfields'][0]['tag']);

        $this->assertSame('!=', $ms['field']['subSpecs'][0][0]['operator']);

        $this->assertSame('245', $ms['field']['subSpecs'][0][0]['rightSubTerm']['field']['tag']);
        $this->assertSame(0, $ms['field']['subSpecs'][0][0]['rightSubTerm']['field']['indexStart']);
        $this->assertSame('b', $ms['field']['subSpecs'][0][0]['rightSubTerm']['subfields'][0]['tag']);

        // subspec 01
        $this->assertSame('300', $ms['field']['subSpecs'][0][1]['leftSubTerm']['field']['tag']);
        $this->assertSame('1', $ms['field']['subSpecs'][0][1]['leftSubTerm']['indicator']['position']);

        $this->assertSame('!~', $ms['field']['subSpecs'][0][1]['operator']);

        $this->assertSame('1', $ms['field']['subSpecs'][0][1]['rightSubTerm']['comparable']);

        // subspec 1
        $this->assertSame('!=', $ms['field']['subSpecs'][1]['leftSubTerm']['comparable']);

        $this->assertSame('!=', $ms['field']['subSpecs'][1]['operator']);

        $this->assertSame('!', $ms['field']['subSpecs'][1]['rightSubTerm']['comparable']);

        // subfields
        $ms = $this->marcspec('245[0]$a{$c|!$d}');
        $this->assertSame('a', $ms['subfields'][0]['tag']);

        // subfield subspec 00
        $this->assertSame('245', $ms['a'][0]['subSpecs'][0][0]['leftSubTerm']['field']['tag']);
        $this->assertSame(0, $ms['a'][0]['subSpecs'][0][0]['leftSubTerm']['field']['indexStart']);
        $this->assertSame('a', $ms['a'][0]['subSpecs'][0][0]['leftSubTerm']['subfields'][0]['tag']);

        $this->assertSame('?', $ms['a'][0]['subSpecs'][0][0]['operator']);

        $this->assertSame('245', $ms['a'][0]['subSpecs'][0][0]['rightSubTerm']['field']['tag']);
        $this->assertSame(0, $ms['a'][0]['subSpecs'][0][0]['rightSubTerm']['field']['indexStart']);
        $this->assertSame('c', $ms['a'][0]['subSpecs'][0][0]['rightSubTerm']['subfields'][0]['tag']);

        // subfield subspec 01
        $this->assertSame('245', $ms['a'][0]['subSpecs'][0][1]['leftSubTerm']['field']['tag']);
        $this->assertSame(0, $ms['a'][0]['subSpecs'][0][1]['leftSubTerm']['field']['indexStart']);
        $this->assertSame('a', $ms['a'][0]['subSpecs'][0][1]['leftSubTerm']['subfields'][0]['tag']);

        $this->assertSame('!', $ms['a'][0]['subSpecs'][0][1]['operator']);

        $this->assertSame('245', $ms['a'][0]['subSpecs'][0][1]['rightSubTerm']['field']['tag']);
        $this->assertSame(0, $ms['a'][0]['subSpecs'][0][1]['rightSubTerm']['field']['indexStart']);
        $this->assertSame('d', $ms['a'][0]['subSpecs'][0][1]['rightSubTerm']['subfields'][0]['tag']);
    }

    public function testIteration()
    {
        $ms = $this->marcspec('245$a-c{$b|$c}{$e}');

        $count = 0;

        foreach ($ms as $key => $value) {
            $count++;
        }
        $this->assertSame(3, $count);

        $count = 0;
        foreach ($ms['subfields'] as $key => $value) {
            $count++;
        }
        $this->assertSame(3, $count);

        foreach ($ms['subfields'] as $subfield) {
            $count = 0;
            foreach ($subfield['subSpecs'] as $subSpec) {
                if (is_array($subSpec)) {
                    $this->assertSame(2, count($subSpec));
                } else {
                    foreach ($subSpec as $key => $prop) {
                        $this->assertTrue(in_array($key, ['leftSubTerm', 'operator', 'rightSubTerm']));
                    }
                }

                $count++;
            }
            $this->assertSame(2, $count);
        }
    }

    public function testOffsets()
    {
        $ms = $this->marcspec('LDR/0-3');
        $this->assertTrue($ms['field']->offsetExists('charLength'));

        $ms = $this->marcspec('LDR/0-#');
        $this->assertFalse($ms['field']->offsetExists('charLength'));

        $ms = $this->marcspec('245$a/0-3');
        $this->assertTrue($ms['a'][0]->offsetExists('charLength'));

        $ms = $this->marcspec('245$a/#-3');
        $this->assertTrue($ms['a'][0]->offsetExists('charLength'));

        $ms = $this->marcspec('245$a/0-#');
        $this->assertFalse($ms['a'][0]->offsetExists('charLength'));
    }

    public function testJsonSerialize()
    {
        $ms = $this->marcspec('...[0-3]^1{$a|$b!=$c}');
        $encode = json_encode($ms);
        $test = '{"field":{"tag":"...","indexStart":0,"indexEnd":3,"indexLength":4},"indicator":{"position":"1","subSpecs":[[{"leftSubTerm":{"field":{"tag":"...","indexStart":0,"indexEnd":3,"indexLength":4}},"operator":"?","rightSubTerm":{"field":{"tag":"...","indexStart":0,"indexEnd":3,"indexLength":4},"subfields":[{"tag":"a","indexStart":0,"indexEnd":"#"}]}},{"leftSubTerm":{"field":{"tag":"...","indexStart":0,"indexEnd":3,"indexLength":4},"subfields":[{"tag":"b","indexStart":0,"indexEnd":"#"}]},"operator":"!=","rightSubTerm":{"field":{"tag":"...","indexStart":0,"indexEnd":3,"indexLength":4},"subfields":[{"tag":"c","indexStart":0,"indexEnd":"#"}]}}]]}}';

        $this->assertsame($encode, $test);
    }

    public function testToString()
    {
        $ms = $this->marcspec('...[0-3]$a{300/1-3=\abc}{245$a!~\test}');
        $test = '...[0-3]$a{300/1-3=\abc}{245$a!~\test}';
        $this->assertsame($ms->__toString(), $test);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testOffsetUnset()
    {
        $ms = $this->marcspec('245');
        unset($ms['field']);
    }
}
