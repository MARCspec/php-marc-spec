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

/**
 * MARCspec subfield interface.
 */
interface SubfieldInterface
{
    /**
     * constructor for MARCspec field.
     *
     * @api
     *
     * @param string $subfield The subfield spec
     */
    public function __construct($subfield);

    /**
     * Sets the subfield tag.
     *
     * @api
     */
    public function setTag($arg);

    /**
     * Get the subfield tag.
     *
     * @api
     *
     * @return string The subfield tag
     */
    public function getTag();

    /**
     * Set the field index starting and ending position.
     *
     * @api
     *
     * @param int|string      $start The index starting position
     * @param int|string|null $end   The index ending position
     */
    public function setIndexStartEnd($start, $end = null);

    /**
     * Set the field index starting and ending position via length.
     *
     * @api
     *
     * @param int|string $start  The index starting position
     * @param int        $length The length count
     */
    public function setIndexStartLength($start, $length);

    /**
     * Get the character starting position.
     *
     * @api
     *
     * @return null|int|string $indexStart The field index starting position
     */
    public function getIndexStart();

    /**
     * Get the field index ending position.
     *
     * @api
     *
     * @return null|int $indexEnd The field index ending position
     */
    public function getIndexEnd();

    /**
     * Set character starting and ending position.
     *
     * @api
     *
     * @param int|string      $start The character starting position
     * @param int|string|null $end   The character ending position
     */
    public function setCharStartEnd($start, $end = null);

    /**
     * Set character starting and ending position via start and length.
     *
     * @api
     *
     * @param int|string $start  The character starting position
     * @param int        $length The character length count
     */
    public function setCharStartLength($start, $length);

    /**
     * Get the character starting position.
     *
     * @api
     *
     * @return null|int $charStart The character starting position
     */
    public function getCharStart();

    /**
     * Get the character ending position.
     *
     * @api
     *
     * @return null|int $charEnd The character ending position
     */
    public function getCharEnd();

    /**
     * Get length of character range.
     *
     * @api
     *
     * @throws \InvalidArgumentException if length is less than 1
     *
     * @return null|int $length The character length
     */
    public function getCharLength();

    /**
     * get array of subspecs.
     *
     * @api
     *
     * @return null|array
     */
    public function getSubSpecs();

    /**
     * add a subspec to the array of subspecs.
     *
     * @api
     *
     * @param SubSpecInterface|array[SubSpecInterface]
     *
     * @return null|array
     */
    public function addSubSpec($SubSpec);

    /**
     * Get the basic spec without subspecs.
     *
     * @api
     *
     * @return string
     */
    public function getBaseSpec();

    /**
     * encodes Field as string.
     *
     * @api
     *
     * @return string
     */
    public function __toString();

    /**
     * Serialize Field as JSON.
     *
     * @api
     *
     * @return array
     */
    public function jsonSerialize();
} // EOI
