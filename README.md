# PHP MARCspec parser

PHP based *MARCspec* parser and validator.

For currently supported version of **MARCspec - A common MARC record path language** see http://cklee.github.io/marc-spec/marc-spec-e66931e.html .

# Installation

Installation can be done by using composer or download the [ZIP file](https://github.com/cKlee/php-marc-spec/archive/master.zip).
# Usage

```php
<?php

namespace CK\MARCspec;
require("vendor/autoload.php");

// parse and access MARCspec like an array
$fixed = new MARCspec('007[0]/1-8{/0=\a}');
echo $fixed['field']['tag']."\n";                                                  // '007'
echo $fixed['field']['charStart']."\n";                                            // 1
echo $fixed['field']['charEnd']."\n";                                              // 8
echo $fixed['field']['charLength']."\n";                                           // 8
echo $fixed['field']['subSpecs'][0]['leftSubTerm']."\n";                           // '007[0]/0'
echo $fixed['field']['subSpecs'][0]['operator']."\n";                              // '='
echo $fixed['field']['subSpecs'][0]['rightSubTerm']."\n";                          // '\a'
echo $fixed['field']['subSpecs'][0]['rightSubTerm']['comparable']."\n";            // 'a'

echo $fixed."\n";                                                                  // '007[0]/1-8{007[0]/0=\a}'

$variable = new MARCspec('245_10$a');
echo $variable['field']['tag']."\n";                                               // '245'
echo $variable['field']['indicator1']."\n";                                        // '1'
echo $variable['field']['indicator2']."\n";                                        // '0'
echo $variable['subfields'][0]['tag']."\n";                                        // 'a'
echo $variable['a'][0]['tag']."\n";                                                // 'a'

echo $variable."\n";                                                               // '245_10$a'

$complex = new MARCspec('020$a{$q[0]~\pbk}{$c/0=\€|$c/0=\$}');
echo $complex['field']['tag']."\n";                                                // '020'
echo $complex['subfields'][0]['tag']."\n";                                         // 'a'

echo $complex['a'][0]['subSpecs'][0]['leftSubTerm']."\n";                          // '020$q[0]'
echo $complex['a'][0]['subSpecs'][0]['operator']."\n";                             // '~'
echo $complex['a'][0]['subSpecs'][0]['rightSubTerm']['comparable']."\n";           // 'pbk'

echo $complex['a'][0]['subSpecs'][1][0]['leftSubTerm']."\n";                       // '020$c/0'
echo $complex['a'][0]['subSpecs'][1][0]['leftSubTerm']['c'][0]['charStart']."\n";  // 0
echo $complex['a'][0]['subSpecs'][1][0]['leftSubTerm']['c'][0]['charEnd']."\n";    // null
echo $complex['a'][0]['subSpecs'][1][0]['leftSubTerm']['c'][0]['charLength']."\n"; // 1
echo $complex['a'][0]['subSpecs'][1][0]['operator']."\n";                          // '='
echo $complex['a'][0]['subSpecs'][1][0]['rightSubTerm']['comparable']."\n";        // '€'

echo $complex['a'][0]['subSpecs'][1][1]['leftSubTerm']."\n";                       // '020$c/0'
echo $complex['a'][0]['subSpecs'][1][1]['leftSubTerm']['c'][0]['charStart']."\n";  // 0
echo $complex['a'][0]['subSpecs'][1][1]['leftSubTerm']['c'][0]['charEnd']."\n";    // null
echo $complex['a'][0]['subSpecs'][1][1]['leftSubTerm']['c'][0]['charLength']."\n"; // 1
echo $complex['a'][0]['subSpecs'][1][1]['operator']."\n";                          // '='
echo $complex['a'][0]['subSpecs'][1][1]['rightSubTerm']['comparable']."\n";        // '$'

// creating MARCspec

// creating a new Field
$Field = new Field('...');
$Field['indicator2'] = '1'; // or $Field->setIndicator2('1');
$Field['indexStart'] = 0; // or $Field->setIndex(0);

// creating a new MARCspec by setting the Field
$MARCspec = MARCspec::setField($Field);

// creating a new Subfield
$Subfield = new Subfield('a');

// adding the Subfield to the MARCspec
$MARCspec->addSubfields($Subfield);

// creating instances of MARCspec and ComparisonString
$LeftSubTerm = new MARCspec('...$a/#');
$RightSubTerm = new ComparisonString(',');

// creating a new SubSpec with instances above and an operator '='
$SubSpec = new SubSpec($LeftSubTerm,'=',$RightSubTerm);

// adding the SubSpec to the Subfield
$Subfield['subSpecs'] = $SubSpec;

// echo whole MARCspec
echo $MARCspec; // '...[0]__1$a{...$a/#=\,}' 
```

# ArrayAccess vs. Methods

MARCspec can be accessed like an immutable array with the following offsets or with its correponding methods (see source code for documentation of all methods).

## Instances of MARCspec

| offset    | method get    | method set        |
|:---------:|:-------------:|:-----------------:|
| field     | getField      | setField|
| subfields | getSubfields  | addSubfields|

## Instances of Field

| offset    | method get    | method set        |
|:---------:|:-------------:|:-----------------:|
| tag       | getTag        | setTag|
| indicator1| getIndicator1 | setIndicator1|
| indicator2| getIndicator2 | setIndicator2|
| charStart | getCharStart  | setCharStart|
| charEnd   | getCharEnd    | setCharEnd|
| charLength| getCharLength | |
| indexStart| getIndexStart | setIndexStart|
| indexEnd  | getIndexEnd   | setIndexEnd|
| indexLength| getIndexLength | |
| subSpecs  | getSubSpecs   | addSubSpecs|

## Instances of Subfield

| offset    | method get    | method set        |
|:---------:|:-------------:|:-----------------:|
| tag       | getTag        | setTag|
| charStart | getCharStart  | setCharStart|
| charEnd   | getCharEnd    | setCharEnd|
| charLength| getCharLength | |
| indexStart| getIndexStart | setIndexStart|
| indexEnd  | getIndexEnd   | setIndexEnd|
| indexLength| getIndexLength | |
| subSpecs  | getSubSpecs   | addSubSpecs|

## Instances of ComparisonString

| offset    | method get    | method set        |
|:---------:|:-------------:|:-----------------:|
| raw       | getRaw        | |
| comparable| getComprable  | |

## Instances of SubSpec

| offset    | method get    | method set        |
|:---------:|:-------------:|:-----------------:|
| leftSubTerm| getLeftSubTerm| |
| operator  | getOperator  | |
| rightSubTerm| getRightSubTerm| |
