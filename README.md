# PHP MarcSpec

PHP based *MARCspec* parser and validator. For currently supported version of **MARCspec - A common MARC record path language** see http://cklee.github.io/marc-spec/marc-spec-4482fbb.html .

# Usage

```php
<?php

require_once "MarcSpec.php";

use CK\MarcSpec\MarcSpec;

// parse Marc spec
$marcSpec = new MarcSpec("245$a-c_10");

// get parsed elements
$fieldTag = $marcSpec->getFieldTag(); // '245'
$subfields = $marcSpec->getSubfields(); // ['a'=>['tag'=>'a','start'=>0],'b'=>['tag'=>'b','start'=>0],'c'=>['tag'=>'c','start'=>0]]
$indicator1 = $marcSpec->getIndicator1(); // '1'
$indicator2 = $marcSpec->getIndicator2(); // '0'

// parse Marc spec
$marcSpec = new MarcSpec("LDR/0-4");

// get parsed elements
$fieldTag = $marcSpec->getFieldTag(); // 'LDR'
$charStart = $marcSpec->getCharStart(); // 0
$charEnd = $marcSpec->getCharEnd(); // 4
$charLength = $marcSpec->getCharLength(); // 5

// initialize empty instance
$marcSpec = new MarcSpec;

$marcSpec->setFieldTag('245');
$marcSpec->addSubfields('$a$b$e');
$marcSpec->setIndicator1('1');
$marcSpec->setIndicator2('0');

$enc = $marcSpec->encode(); // '245$a$b$e_10'

// initialize empty instance
$marcSpec = new MarcSpec;

$marcSpec->setFieldTag('007');
$marcSpec->setCharStart(0);
$marcSpec->setLength(5);

$enc = $marcSpec->encode(); // '007/0-4'
$enc = $marcSpec->encode('json'); // { "marcspec": { "fieldTag": "007", "charStart": 0, "charEnd": 4, "charLength": 5 } }

// initialize empty instance
$marcSpec = new MarcSpec;

marcSpec->validate('245$a_1'); // true
marcSpec->validate('004$a/1'); // InvalidArgumentException
```

## Public methods

### CK\MarcSpec\MarcSpec::__construct()

Params:

* string $spec: The MARC spec as string

### CK\MarcSpec\MarcSpec::decode()

Params:

* string $spec: The MARC spec as string

### CK\MarcSpec\MarcSpec::encode()

Params:

* string $encoding: The MARCspec encoding ("string" (default) and "json" currently supported)

Return: string or JSON

### CK\MarcSpec\MarcSpec::validate()

Return: true | InvalidArgumentException

### CK\MarcSpec\MarcSpec::setFieldTag()

Params:

* string $fieldTag: The field tag

### CK\MarcSpec\MarcSpec::addSubfields()

Params:

* string $subfields: The string of subfield tags

### CK\MarcSpec\MarcSpec::setIndicators()

Params:

* string $indicators: The string of indicators 1 and 2

### CK\MarcSpec\MarcSpec::setIndicator1()

Params:

* string $indicator1: Indicator 1

### CK\MarcSpec\MarcSpec::setIndicator2()

Params:

* string $indicator1: Indicator 2

### CK\MarcSpec\MarcSpec::setCharStart()

Params:

* int : charcter start position

### CK\MarcSpec\MarcSpec::setCharEnd()

Params:

* int : charcter end position

### CK\MarcSpec\MarcSpec::setCharLength()

Params:

* int : charcter range length

### CK\MarcSpec\MarcSpec::getFieldTag()

Return: string

### CK\MarcSpec\MarcSpec::getSubfields()

Return: array

### CK\MarcSpec\MarcSpec::getIndicator1()

Return: string

### CK\MarcSpec\MarcSpec::getIndicator2()

Return: string

### CK\MarcSpec\MarcSpec::getCharStart()

Return: int

### CK\MarcSpec\MarcSpec::getCharEnd()

Return: int

### CK\MarcSpec\MarcSpec::getCharLength()

Return: int

