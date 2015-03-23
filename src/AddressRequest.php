<?php
/**
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace PetrGrishin\Fias\Finder;


class AddressRequest {
    /** @var string */
    private $address;
    /** @var \PetrGrishin\Fias\Finder\AddressRequestPart[] */
    private $addressParts;
    /** @var integer */
    private $postalCode;
    /** @var bool */
    private $isEndParts = false;

    public function __construct($address, $delimiter = ',') {
        $addressWithoutPostalCode = $this->processPostalCodeAddress($address);
        $this->address = $addressWithoutPostalCode;
        $this->addressParts = $this->processPartsAddress($addressWithoutPostalCode, $delimiter);
    }

    /**
     * @return AddressRequestPart[]
     */
    public function getParts() {
        return $this->addressParts;
    }

    public function resetParts() {
        $this->isEndParts = false;
        reset($this->addressParts);
        return $this;
    }

    /**
     * @return \PetrGrishin\Fias\Finder\AddressRequestPart
     */
    public function getCurrentPart() {
        return current($this->addressParts);
    }

    public function nextPart() {
        if (false === next($this->addressParts)) {
            $this->isEndParts = true;
        }
        return $this;
    }

    public function prevPart() {
        if (false === prev($this->addressParts)) {
            $this->isEndParts = false;
        }
        return $this;
    }

    public function nextPartExists() {
        return !$this->isEndParts;
    }

    protected function processPartsAddress($address, $delimiter = ',') {
        return array_map(function ($addressPart) {
            $part = new AddressRequestPart($addressPart);
            return $part;
        }, explode($delimiter, $address));
    }

    protected function processPostalCodeAddress($address, $delimiter = ',') {
        $matches = array();
        if (preg_match('#^(?:\s)*(\d{6})(?:\s)*(?:\\'.$delimiter.'{1})(.+)$#iu', $address, $matches)) {
            $this->postalCode = trim($matches[1]);
            return trim($matches[2]);
        }
        return $address;
    }

    /**
     * @return int
     */
    public function getPostalCode() {
        return $this->postalCode;
    }
}
 