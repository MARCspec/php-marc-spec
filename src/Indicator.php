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
 * A MARCspec indicatorSpec class.
 */
class Indicator implements IndicatorInterface, \JsonSerializable, \ArrayAccess
{
    /**
     * @var string indicator position
     */
    private $position;

    /**
     * @var array subSpec
     */
    private $subSpecs = [];

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     * @throws InvalidMARCspecException
     */
    public function __construct($indicatorpos)
    {
        if (!is_string($indicatorpos) and !is_int($indicatorpos)) {
            throw new \InvalidArgumentException('Method only accepts string as argument. '.
                gettype($indicatorpos).' given.'
            );
        }

        if (strpos($indicatorpos, '{')) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::IN.
                InvalidMARCspecException::DETECTEDSS,
                $indicatorpos
            );
        }

        if (!preg_match('/^\^?(1|2)$/', $indicatorpos, $pos)) {
            throw new \InvalidArgumentException('For indicator position only digit "1" or "2" is valid.');
        }

        $this->position = $pos[1];
    }

    /**
     * {@inheritdoc}
     */
    public function getPos()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubSpecs()
    {
        return (0 < count($this->subSpecs)) ? $this->subSpecs : null;
    }

    /**
     * {@inheritdoc}
     */
    public function addSubSpec($SubSpec)
    {
        if ($SubSpec instanceof SubSpecInterface) {
            $this->subSpecs[] = $SubSpec;
        } elseif (is_array($SubSpec)) {
            foreach ($SubSpec as $sub) {
                if (!($sub instanceof SubSpecInterface)) {
                    throw new \InvalidArgumentException('Values of array of subSpecs
                        must be instances of SubSpecInterface.'
                    );
                }
            }
            $this->subSpecs[] = $SubSpec;
        } else {
            throw new \InvalidArgumentException('Param 1 must be instance of
                SubSpecInterface or array with instances of SubSpecInterface. Got "'
                .gettype($subSpec).'".'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseSpec()
    {
        return '^'.$this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $indicatorSpec = $this->getBaseSpec();

        if (($subSpecs = $this->getSubSpecs()) !== null) {
            foreach ($subSpecs as $subSpec) {
                if (is_array($subSpec)) {
                    foreach ($subSpec as $orKey => $orSubSpec) {
                        $subSpec[$orKey] = $orSubSpec->__toString();
                    }
                    $indicatorSpec .= '{'.implode('|', $subSpec).'}';
                } else {
                    $indicatorSpec .= '{'.$subSpec->__toString().'}';
                }
            }
        }

        return $indicatorSpec;
    }

    /**
     * Access object like an associative array.
     *
     * @api
     *
     * @param string $offset Key indexStart|indexEnd|charStart|charEnd|charLength
     *                       |indicator1|indicator2|subSpecs
     */
    public function offsetExists($offset)
    {
        switch ($offset) {
            case 'position': return isset($this->position);
            break;
            case 'subSpecs': return (0 < count($this->subSpecs)) ? true : false;
            break;
            default: return false;
        }
    }

    /**
     * Access object like an associative array.
     *
     * @api
     *
     * @param string $offset Key indexStart|indexEnd|charStart|charEnd|charLength
     *                       |indicator1|indicator2|subSpecs
     */
    public function offsetGet($offset)
    {
        switch ($offset) {
            case 'position': return $this->getPos();
            break;
            case 'subSpecs': return $this->getSubSpecs();
            break;
            default: throw new \UnexpectedValueException("Offset $offset does not exist.");
        }
    }

    /**
     * Access object like an associative array.
     *
     * @api
     *
     * @param string $offset Key indexStart|indexEnd|charStart|charEnd|charLength
     *                       |indicator1|indicator2|subSpecs
     */
    public function offsetSet($offset, $value)
    {
        switch ($offset) {
            case 'subSpecs': $this->addSubSpec($value);
            break;
            default: throw new \UnexpectedValueException("Offset $offset cannot be set.");
        }
    }

    /**
     * Access object like an associative array.
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException("Offset $offset can not be unset.");
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $_indicatorSpec['position'] = $this->getPos();

        if (($subSpecs = $this->getSubSpecs()) !== null) {
            $_indicatorSpec['subSpecs'] = [];
            foreach ($subSpecs as $key => $subSpec) {
                if (is_array($subSpec)) {
                    foreach ($subSpec as $altSubSpec) {
                        $_indicatorSpec['subSpecs'][$key][] = $altSubSpec->jsonSerialize();
                    }
                } else {
                    $_indicatorSpec['subSpecs'][$key] = $subSpec->jsonSerialize();
                }
            }
        }

        return $_indicatorSpec;
    }
} // EOC
