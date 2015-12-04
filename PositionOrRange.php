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
 * class for index or character position or range spec.
 */
class PositionOrRange implements PositionOrRangeInterface
{
    /**
     * {@inheritdoc}
     */
    public function setIndexStartEnd($start, $end = null)
    {
        list($this->indexStart, $this->indexEnd) = $this->validateStartEnd($start, $end);
    }

    /**
     * {@inheritdoc}
     */
    public function setIndexStartLength($start, $length)
    {
        list($this->indexStart, $this->indexEnd) = $this->validateStartLength($start, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexStart()
    {
        return (isset($this->indexStart)) ? $this->indexStart : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexEnd()
    {
        if (!isset($this->indexStart)) {
            return '#';
        } else {
            return (isset($this->indexEnd)) ? $this->indexEnd : $this->indexStart;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setCharStartEnd($start, $end = null)
    {
        list($this->charStart, $this->charEnd) = $this->validateStartEnd($start, $end);
    }

    /**
     * {@inheritdoc}
     */
    public function setCharStartLength($start, $length)
    {
        list($this->charStart, $this->charEnd) = $this->validateStartLength($start, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function getCharStart()
    {
        return (isset($this->charStart)) ? $this->charStart : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCharEnd()
    {
        return (isset($this->charEnd)) ? $this->charEnd : $this->charStart;
    }

    /**
     * {@inheritdoc}
     */
    public function getCharLength()
    {
        return $this->getLength(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexLength()
    {
        return $this->getLength(false);
    }

    /**
     * Calculate the length of charrange or index range.
     *
     * @param bool $type True for charrange and false for indexrange
     *
     * @return int $length
     */
    private function getLength($type = true)
    {
        if ($type) {
            $start = $this->getCharStart();
            $end = $this->getCharEnd();
        } else {
            $start = $this->getIndexStart();
            $end = $this->getIndexEnd();
        }

        if (is_null($start) && is_null($end)) {
            return;
        }

        if (!is_null($start) && is_null($end)) {
            return 1;
        }

        if ($start === $end) {
            return 1;
        }

        if ('#' === $start && '#' !== $end) {
            return $end + 1;
        }

        if ('#' !== $start && '#' === $end) {
            return;
        }

        $length = $end - $start + 1;

        if (1 > $length) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::PR.
                InvalidMARCspecException::NEGATIVE
            );
        }

        return $length;
    }

    /**
     * Validate starting and ending position.
     *
     * @internal
     *
     * @param int|string $start The starting position
     * @param int|string $end   The ending position
     *
     * @throws \UnexpectedValueException
     *
     * @return null|array $_startEnd index 0 => start, index 1 => end
     */
    private function validateStartEnd($start, $end)
    {
        $_startEnd = [];

        if (preg_match('/[0-9]/', $start)) {
            $_startEnd[0] = (int) $start;
        } elseif ('#' === $start) {
            $_startEnd[0] = '#';
        } else {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::PR.
                InvalidMARCspecException::PR7,
                $start
            );
        }

        if (preg_match('/[0-9#]/', $end)) {
            if ('#' === $end) {
                $_startEnd[1] = '#';
            } elseif (preg_match('/[0-9]/', $end)) {
                $_startEnd[1] = (int) $end;

                if ($_startEnd[1] < $_startEnd[0]) {
                    throw new InvalidMARCspecException(
                        InvalidMARCspecException::PR.
                        InvalidMARCspecException::PR8,
                        $start.'-'.$end
                    );
                }
            } else {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::PR.
                    InvalidMARCspecException::PR8,
                    $start.'-'.$end
                );
            }
        } else {
            $_startEnd[1] = null;
        }

        return $_startEnd;
    }

    /**
     * Validate starting position and length.
     *
     * @internal
     *
     * @param string $start  The starting position
     * @param string $length $length The length count
     *
     * @throws \UnexpectedValueException
     *
     * @return array $_startEnd index 0 => start, index 1 => end
     */
    private function validateStartLength($start, $length)
    {
        $_startEnd = [];
        if (preg_match('/[0-9]/', $start)) {
            $_startEnd[0] = (int) $start;
        } elseif ('#' === $start) {
            $_startEnd[0] = '#';
        } else {
            throw new \UnexpectedValueException(
                'First argument must be positive int, 0 or character #.',
                $start
            );
        }

        if (preg_match('/^[1-9]\d*/', $length)) { // only positive int without 0
            $_startEnd[1] = (int) $length - 1;
        } else {
            throw new \UnexpectedValueException(
                'Second argument must be positive int without 0.',
                $length
            );
        }

        return $_startEnd;
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
    protected function checkIfString($arg)
    {
        if (!is_string($arg)) {
            throw new \InvalidArgumentException('Method only accepts string as argument. '
                .gettype($arg).' given.');
        }
    }
} // EOC
