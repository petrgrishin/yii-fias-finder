<?php
/**
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace PetrGrishin\Fias\Finder;


use PetrGrishin\Fias\Entity\AddressObject;
use PetrGrishin\Fias\Entity\AddressHouse;

class AddressFinderResult {
    /** @var AddressObject */
    private $addressObject;
    /** @var AddressHouse */
    private $addressHouse;
    /** @var  string */
    private $oktmo;
    /** @var  array */
    private $specific = array();

    public static function className() {
        return get_called_class();
    }

    public function copy() {
        return clone $this;
    }

    /**
     * @return AddressObject
     */
    public function getAddressObject() {
        return $this->addressObject;
    }

    /**
     * @param AddressObject $addressObject
     * @return $this
     */
    public function setAddressObject($addressObject) {
        $this->addressObject = $addressObject;
        return $this;
    }

    /**
     * @return AddressHouse
     */
    public function getAddressHouse() {
        return $this->addressHouse;
    }

    /**
     * @param AddressHouse $addressHouse
     * @return $this
     */
    public function setAddressHouse($addressHouse) {
        $this->addressHouse = $addressHouse;
        return $this;
    }

    /**
     * @return string
     */
    public function getOktmo() {
        return $this->oktmo;
    }

    /**
     * @param string $oktmo
     * @return $this
     */
    public function setOktmo($oktmo) {
        $this->oktmo = $oktmo;
        return $this;
    }

    public function setSpecific($parts) {
        $this->specific = $parts;
        return $this;
    }

    public function getSpecific() {
        return $this->specific;
    }

    public function getSpecificLine() {
        $linePart = array();
        foreach ($this->getSpecific() as $specificPrefix => $specific) {
            $linePart[] = sprintf('%s %s', $specificPrefix, $specific);
        }
        return implode(', ', $linePart);
    }

    public function getAddressLine() {
        $result = $this->getAddressObject()->getAddressLine();
        $this->getAddressHouse() && ($result .= sprintf(', %s', $this->getAddressHouse()->getAddressLine()));
        $this->getSpecific() && ($result .= sprintf(', %s', $this->getSpecificLine()));
        return $result;
    }

    public function getAddressLineWithPostalCode() {
        $postalCode = $this->getAddressObject()->postalCode;
        $result = $this->getAddressObject()->getAddressLine();
        $this->getAddressHouse() && ($result .= sprintf(', %s', $this->getAddressHouse()->getAddressLine())) && ($postalCode = $this->getAddressHouse()->postalCode);
        $this->getSpecific() && ($result .= sprintf(', %s', $this->getSpecificLine()));
        $postalCode && ($result = sprintf('%s, %s', $postalCode, $result));
        return $result;
    }

    /**
     * @return array
     */
    public function getData() {
        $data['object'] = $this->getAddressObject()->getData();
        $this->getAddressHouse() && ($data['isHouse'] = true) && ($data['house'] = $this->getAddressHouse()->getData());
        $this->getSpecific() && $data['specific'] = $this->getSpecific();
        $this->getOktmo() && $data['oktmo'] = $this->getOktmo();
        return $data;
    }
}
