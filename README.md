# PHP MARCspec parser

PHP based *MARCspec* parser and validator. For specification of **MARCspec** see http://cklee.github.io/marc-spec/marc-spec.html .

# Usage

```php
<?php

require_once "MARCspec.php";

use CK\MARCspec\MARCspec;

// parse Marc spec
$marcSpec = new MARCspec("245$a-c_10");

// get parsed elements
$fieldTag = $marcSpec->getFieldTag(); // '245'
$subfields = $marcSpec->getSubfields(); // ['a'=>['tag'=>'a','start'=>0],'b'=>['tag'=>'b','start'=>0],'c'=>['tag'=>'c','start'=>0]]
$indicator1 = $marcSpec->getIndicator1(); // '1'
$indicator2 = $marcSpec->getIndicator2(); // '0'

// parse Marc spec
$marcSpec = new MARCspec("LDR/0-4");

// get parsed elements
$fieldTag = $marcSpec->getFieldTag(); // 'LDR'
$charStart = $marcSpec->getCharStart(); // 0
$charEnd = $marcSpec->getCharEnd(); // 4
$charLength = $marcSpec->getCharLength(); // 5

// initialize empty instance
$marcSpec = new MARCspec;

$marcSpec->setFieldTag('245');
$marcSpec->addSubfields('$a$b$e');
$marcSpec->setIndicator1('1');
$marcSpec->setIndicator2('0');

$enc = $marcSpec->encode(); // '245$a$b$e_10'

// initialize empty instance
$marcSpec = new MARCspec;

$marcSpec->setFieldTag('007');
$marcSpec->setCharStart(0);
$marcSpec->setLength(5);

$enc = $marcSpec->encode(); // '007/0-4'
$enc = $marcSpec->encode('json'); // { "marcspec": { "fieldTag": "007", "charStart": 0, "charEnd": 4, "charLength": 5 } }

// initialize empty instance
$marcSpec = new MARCspec;

marcSpec->validate('245$a_1'); // true
marcSpec->validate('004$a/1'); // InvalidArgumentException
```

## Public methods

### CK\MARCspec\MARCspec::__construct()

Params:

* string $spec: The MARC spec as string

### CK\MARCspec\MARCspec::decode()

Params:

* string $spec: The MARC spec as string

### CK\MARCspec\MARCspec::encode()

Params:

* string $encoding: The MARCspec encoding ("string" (default) and "json" currently supported)

Return: string or JSON

### CK\MARCspec\MARCspec::validate()

Return: true | InvalidArgumentException

### CK\MARCspec\MARCspec::setFieldTag()

Params:

* string $fieldTag: The field tag

### CK\MARCspec\MARCspec::addSubfields()

Params:

* string $subfields: The string of subfield tags

### CK\MARCspec\MARCspec::setIndicators()

Params:

* string $indicators: The string of indicators 1 and 2

### CK\MARCspec\MARCspec::setIndicator1()

Params:

* string $indicator1: Indicator 1

### CK\MARCspec\MARCspec::setIndicator2()

Params:

* string $indicator1: Indicator 2

### CK\MARCspec\MARCspec::setCharStart()

Params:

* int : charcter start position

### CK\MARCspec\MARCspec::setCharEnd()

Params:

* int : charcter end position

### CK\MARCspec\MARCspec::setCharLength()

Params:

* int : charcter range length

### CK\MARCspec\MARCspec::getFieldTag()

Return: string

### CK\MARCspec\MARCspec::getSubfields()

Return: array

### CK\MARCspec\MARCspec::getIndicator1()

Return: string

### CK\MARCspec\MARCspec::getIndicator2()

Return: string

### CK\MARCspec\MARCspec::getCharStart()

Return: int

### CK\MARCspec\MARCspec::getCharEnd()

Return: int

### CK\MARCspec\MARCspec::getCharLength()

Return: int

