<?php
/**
* MARCspec is the specification of a reference, encoded as string, to a set of data
* from within a MARC record.
*
* @author Carsten Klee <mailme.klee@yahoo.de>
* @copyright For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace CK\MARCspec;

use CK\MARCspec\Exception\InvalidMARCspecException;

/**
 * Class to decode, validate and encode MARC spec as string.
 * For Specification of MARC spec as string see
 * <http://cklee.github.io/marc-spec/marc-spec.html>.
 */
class MARCspec implements MARCspecInterface, \JsonSerializable, \ArrayAccess, \IteratorAggregate
{
    /**
     * @var Field The field object
     */
    private $field;

    /**
     * @var array Array of subfields
     */
    private $subfields = [];

    /**
     * {@inheritdoc}
     *
     * @throws InvalidMARCspecException
     */
    public function __construct($spec)
    {
        if ($spec instanceof FieldInterface) {
            $this->field = $spec;
        } else {
            $this->checkIfString($spec);
            $spec = trim($spec);
            $specLength = strlen($spec);
            // check string length
            if (3 > $specLength) {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::MS.
                    InvalidMARCspecException::LENGTH.
                    InvalidMARCspecException::MINIMUM3,
                    $spec
                );
            }
            if (preg_match('/\s/', $spec)) {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::MS.
                    InvalidMARCspecException::SPACE,
                    $spec
                );
            }

            $parser = new MARCspecParser($spec);

            $this->field = new Field();

            $this->field->setTag($parser->field['tag']);

            if (array_key_exists('index', $parser->field)) {
                $_pos = $this->validatePos($parser->field['index']);

                $this->field->setIndexStartEnd($_pos[0], $_pos[1]);
            } else {
                // as of MARCspec 3.2.2 spec without index is always an abbreviation
                $this->field->setIndexStartEnd(0, '#');
            }

            if (array_key_exists('indicators', $parser->field)) {
                $this->field->setIndicators($parser->field['indicators']);
            } elseif (array_key_exists('charpos', $parser->field)) {
                $_chars = $this->validatePos($parser->field['charpos']);

                $this->field->setCharStartEnd($_chars[0], $_chars[1]);
            }

            if (array_key_exists('subspecs', $parser->field)) {
                foreach ($parser->field['subspecs'] as $subspec) {
                    if (!array_key_exists('operator', $subspec)) {
                        foreach ($subspec as $orSubSpec) {
                            $_subSpecs[] = $this->createSubSpec($orSubSpec);
                        }
                        $this->field->addSubSpec($_subSpecs);
                    } else {
                        $Subspec = $this->createSubSpec($subspec);

                        $this->field->addSubSpec($Subspec);
                    }
                }
            }

            if ($parser->subfields) {
                foreach ($parser->subfields as $_subfield) {
                    $this->addSubfield($_subfield);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function setField(FieldInterface $field)
    {
        return new self($field);
    }

    /**
     * {@inheritdoc}
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubfields()
    {
        return (0 < count($this->subfields)) ? $this->subfields : null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \UnexpectedValueException if argument length is higher than 1
     */
    public function getSubfield($arg)
    {
        if (1 < strlen($arg)) {
            throw new \UnexpectedValueException(
                'Method only allows argument to be 1 character long. Got '.strlen($arg)
            );
        }

        if (0 < count($this->subfields)) {
            foreach ($this->subfields as $subfield) {
                if ($subfield->getTag() == $arg) {
                    $_subfields[] = $subfield;
                }
            }

            return $_subfields;
        }

        return;
    }

    /**
     * Creates and adds a single subfield from the MARCspecParser result.
     *
     * @param array $_subfield The MARCspecParser result array
     */
    private function addSubfield($_subfield)
    {
        if (array_key_exists('subfieldtagrange', $_subfield)) {
            $_subfieldRange = $this->handleSubfieldRanges($_subfield['subfieldtagrange']);
        } else {
            $_subfieldRange[] = $_subfield['subfieldtag'];
        }

        foreach ($_subfieldRange as $subfieldTag) {
            $Subfield = new Subfield();

            $Subfield->setTag($subfieldTag);

            if (array_key_exists('index', $_subfield)) {
                $_pos = $this->validatePos($_subfield['index']);

                $Subfield->setIndexStartEnd($_pos[0], $_pos[1]);
            } else {
                // as of MARCspec 3.2.2 spec without index is always an abbreviation
                $Subfield->setIndexStartEnd(0, '#');
            }

            if (array_key_exists('charpos', $_subfield)) {
                $_chars = $this->validatePos($_subfield['charpos']);

                $Subfield->setCharStartEnd($_chars[0], $_chars[1]);
            }

            if (array_key_exists('subspecs', $_subfield)) {
                $_subSpecs = [];

                foreach ($_subfield['subspecs'] as $subspec) {
                    if (!array_key_exists('operator', $subspec)) {
                        foreach ($subspec as $orSubSpec) {
                            $_subSpecs[] = $this->createSubSpec($orSubSpec, $Subfield);
                        }
                        $Subfield->addSubSpec($_subSpecs);
                    } else {
                        $Subspec = $this->createSubSpec($subspec, $Subfield);

                        $Subfield->addSubSpec($Subspec);
                    }
                }
            }

            $this->addSubfields($Subfield);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidMARCspecException
     */
    public function addSubfields($subfields)
    {
        if ($subfields instanceof SubfieldInterface) {
            $this->subfields[] = $subfields;
        } else {
            $this->checkIfString($subfields);
            if (2 > strlen($subfields)) {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::SF.
                    InvalidMARCspecException::LENGTH.
                    InvalidSubfieldspecException::MINIMUM2,
                    $subfields
                );
            }
            if ('$' !== $subfields[0]) {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::SF.
                    InvalidSubfieldspecException::PREFIX,
                    $subfields
                );
            }

            $parser = new MARCspecParser();

            $_subfieldSpecs = $parser->matchSubfields($subfields);

            foreach ($_subfieldSpecs as $subfieldSpec) {
                $this->addSubfield($subfieldSpec);
            }
        }
    }

    /**
     * Parses subfield ranges into single subfields.
     *
     * @internal
     *
     * @param string $arg The assumed subfield range
     *
     * @throws InvalidMARCspecException
     *
     * @return array $_range[string] An array of subfield tags
     */
    private function handleSubfieldRanges($arg)
    {
        if (strlen($arg) < 3) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::LENGTH3,
                $arg
            );
        }

        if (preg_match('/[a-z]/', $arg[0]) && !preg_match('/[a-z]/', $arg[2])) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::RANGE,
                $arg
            );
        }
        if (preg_match('/[A-Z]/', $arg[0]) && !preg_match('/[A-Z]/', $arg[2])) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::RANGE,
                $arg
            );
        }
        if (preg_match('/[0-9]/', $arg[0]) && !preg_match('/[0-9]/', $arg[2])) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::RANGE,
                $arg
            );
        }
        if ($arg[0] > $arg[2]) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::RANGE,
                $arg
            );
        }

        return range($arg[0], $arg[2]);
    }

    /**
     * checks if argument is a string.
     *
     * @internal
     *
     * @param string $arg The argument to check
     *
     * @throws \InvalidArgumentException if the argument is not a string
     */
    private function checkIfString($arg)
    {
        if (!is_string($arg)) {
            throw new \InvalidArgumentException('Method only accepts string as argument. '.
                gettype($arg).' given.');
        }
    }

    /**
     * validate a position or range.
     *
     *
     * @param string $pos The position or range
     *
     * @throws InvalidMARCspecException
     *
     * @return array $_pos[string] An numeric array of character or index positions.
     *               $_pos[1] might be empty.
     */
    public static function validatePos($pos)
    {
        $posLength = strlen($pos);

        if (1 > $posLength) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::PR.
                InvalidMARCspecException::PR1,
                $pos
            );
        }

        if (preg_match('/[^0-9\-#]/', $pos)) { // alphabetic characters etc. are not valid
            throw new InvalidMARCspecException(
                InvalidMARCspecException::PR.
                InvalidMARCspecException::PR2,
                $pos
            );
        }

        if (strpos($pos, '-') === $posLength - 1) { // something like 123- is not valid
            throw new InvalidMARCspecException(
                InvalidMARCspecException::PR.
                InvalidMARCspecException::PR3,
                $pos
            );
        }

        if (0 === strpos($pos, '-')) { // something like -123 ist not valid
            throw new InvalidMARCspecException(
                InvalidMARCspecException::PR.
                InvalidMARCspecException::PR4,
                $pos
            );
        }

        if (strpos($pos, '-') !== strrpos($pos, '-')) { // only one - is allowed
            throw new InvalidMARCspecException(
                InvalidMARCspecException::PR.
                InvalidMARCspecException::PR5,
                $pos
            );
        }

        $_pos = explode('-', $pos);

        if (2 < count($_pos)) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::PR.
                InvalidMARCspecException::PR6,
                $pos
            );
        }

        if (1 == count($_pos)) {
            $_pos[1] = null;
        }

        return $_pos;
    }

    /**
     * Creates SubSpecs.
     *
     * @internal
     *
     * @param array $_subTerms Array representation of a subspec
     * @param SubfieldInterface|null The subfield is optional
     *
     * @throws InvalidMARCspecException
     *
     * @return SubSpecInterface Instance of SubSpecInterface
     */
    private function createSubSpec($_subTerms, $Subfield = null)
    {
        $fieldContext = $this->field->getBaseSpec();
        $context = $fieldContext;
        if (!is_null($Subfield)) {
            $context = $fieldContext.$Subfield->getBaseSpec();
            $subfieldContext = $Subfield->getBaseSpec();
        }

        $handleSubTerm = function ($subTerm) use ($fieldContext, $context) {

            if ('\\' == $subTerm[0]) { // is a comparisonString
                return new ComparisonString(substr($subTerm, 1));
            } else {
                if (strpos('[/_$', $subTerm[0]) && is_null($context)) {
                    throw new InvalidMARCspecException(
                        InvalidMARCspecException::SS.
                        InvalidMARCspecException::MISSINGFIELD,
                        $subTerm
                    );
                }

                switch ($subTerm[0]) {
                    case '_':
                        if ($refPos = strpos($context, $subTerm[0])) {
                            if ('$' !== substr($context, $refPos - 1, 1)) {
                                return new MARCspec(substr($context, 0, $refPos).$subTerm);
                            }
                        }

                        return new MARCspec($fieldContext.$subTerm);

                    case '$':
                        return new MARCspec($fieldContext.$subTerm);

                    case '/':
                        $refPos = strrpos($context, $subTerm[0]);
                        if ($refPos) {
                            if ('$' !== substr($context, $refPos - 1, 1)) {
                                return new MARCspec(substr($context, 0, $refPos).$subTerm);
                            }
                        }

                        return new MARCspec($context.$subTerm);

                    case '[':
                        $refPos = strrpos($context, $subTerm[0]);
                        if ($refPos) {
                            if ('$' !== substr($context, $refPos - 1, 1)) {
                                return new MARCspec(substr($context, 0, $refPos).$subTerm);
                            } else {
                                return new MARCspec($context.$subTerm);
                            
                            }
                        } else {
                            return new MARCspec(substr($context, 0, 3).$subTerm);
                        }

                    default:
                        return new MARCspec($subTerm);
                }
            }
        };

        $_subTermSet = [];

        if (array_key_exists('leftsubterm', $_subTerms)) {
            $_subTermSet['leftsubterm'] = $handleSubTerm($_subTerms['leftsubterm']);
        } else {
            $_subTermSet['leftsubterm'] = new self($context);
        }

        $_subTermSet['rightsubterm'] = $handleSubTerm($_subTerms['rightsubterm']);

        return new SubSpec($_subTermSet['leftsubterm'], $_subTerms['operator'], $_subTermSet['rightsubterm']);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $_marcSpec['field'] = $this->field->jsonSerialize();

        foreach ($this->subfields as $subfield) {
            $_marcSpec['subfields'][] = $subfield->jsonSerialize();
        }

        return $_marcSpec;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $marcspec = "$this->field";
        foreach ($this->subfields as $subfield) {
            $marcspec .= "$subfield";
        }

        return $marcspec;
    }

    /**
     * Access object like an associative array.
     *
     * @api
     *
     * @param string $offset Key field|subfield
     */
    public function offsetExists($offset)
    {
        switch ($offset) {
            case 'field':
                return isset($this->field);
            break;
            case 'subfields':
                return (0 < count($this->subfields)) ? true : false;
            break;
            default:
                return false;
        }
    }

    /**
     * Access object like an associative array.
     *
     * @api
     *
     * @param string $offset Key field|subfield
     */
    public function offsetGet($offset)
    {
        switch ($offset) {
            case 'field':
                return $this->getField();
            break;
            case 'subfields':
                return $this->getSubfields();
            break;
            default:
                return $this->getSubfield($offset);
        }
    }

    /**
     * Access object like an associative array.
     *
     * @api
     *
     * @param string $offset Key subfield
     */
    public function offsetSet($offset, $value)
    {
        switch ($offset) {
            case 'subfields':
                $this->addSubfields($value);
                break;
            default:
                throw new \UnexpectedValueException("Offset $offset cannot be set.");
        }
    }

    /**
     * Access object like an associative array.
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException("Offset $offset can not be unset.");
    }

    public function getIterator()
    {
        return new SpecIterator(['field' => $this->field, 'subfields' => $this->subfields]);
    }
} // EOC
