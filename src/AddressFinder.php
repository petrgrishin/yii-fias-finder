<?php
/**
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace PetrGrishin\Fias\Finder;

use PetrGrishin\Fias\Entity\AddressObject;
use PetrGrishin\Fias\Entity\AddressHouse;
use CApplicationComponent;
use CDbCriteria;

class AddressFinder extends CApplicationComponent {

    public $limitResponses = 10;

    public static function className() {
        return get_called_class();
    }
    /**
     * @param AddressRequest $addressRequest
     * @param bool $verbose
     * @throws \Exception
     * @return AddressFinderResult[]
     */
    public function findAddress(AddressRequest $addressRequest, $verbose = false) {
        if (empty($addressRequest)) {
            throw new \Exception('Пустая строка поиска');
        }
        $addressRequest->resetParts();
        $parentId = null;
        /** @var AddressFinderResult[] $result */
        $result = null;
        while ($addressPart = $addressRequest->getCurrentPart()) {
            $verbose && printf("Search: `%s` %s\n",
                $addressPart->getAddress(),
                $addressPart->getPrefix() ? sprintf(', prefix `%s`', $addressPart->getPrefix()) : ''
            );
            $addressObjectModel = AddressObject::model()->resetScope();
            $criteria = $addressObjectModel->getDbCriteria();
            $criteria->addSearchCondition('title', sprintf('%s%%', $addressPart->getAddress()), false);
            $addressPart->getPrefix() && $criteria->addSearchCondition('prefix', sprintf('%s%%', $addressPart->getPrefix()), false);
            $criteria->order = 'level';
            $criteria->limit = 1;
            if ($parentId) {
                $criteria->addCondition('parentId = :parentId');
                $criteria->params[':parentId'] = $parentId;
            }
            /** @var AddressObject $addressObjects */
            $addressObjects = $addressObjectModel->find($criteria);
            if (!$addressObjects) {
                (count($result) == 1 || $addressRequest->nextPartExists()) && $result = $this->findHouse(array_shift($result), $addressRequest);
                break;
            }
            $verbose && printf("-->\n");
            $parentId = $addressObjects->addressId;
            $criteria->limit = $this->limitResponses;
            $result = array_map(function (AddressObject $addressObject) use ($addressObjects) {
                $finderResult = new AddressFinderResult();
                $finderResult->setAddressObject($addressObject);
                $addressObjects->oktmo && $finderResult->setOktmo($addressObjects->oktmo);
                $addressObject->oktmo && $finderResult->setOktmo($addressObject->oktmo);
                return $finderResult;
            }, $addressObjectModel->findAll($criteria) ?: array());
            $addressRequest->nextPart();
        }
        $verbose && printf("-->\nEnd search.\n");
        return $result;
    }
    /**
     * @param AddressFinderResult $finderResult
     * @param AddressRequest $addressRequest
     * @return AddressFinderResult[]
     */
    protected function findHouse(AddressFinderResult $finderResult, AddressRequest $addressRequest) {
        $addressId = $finderResult->getAddressObject()->addressId;
        $houseModel = AddressHouse::model()->resetScope();
        $criteria = $houseModel->getDbCriteria();
        $criteria->limit = $this->limitResponses;
        $criteria->addCondition('parentId = :parentId');
        $criteria->params[':parentId'] = $addressId;
        $this->setupFindHouseNumberCriteria($criteria, $addressRequest);
        $this->setupFindHouseCriteria($criteria, $addressRequest);
        $specific = $this->getSpecificByAddressRequest($addressRequest);
        $houses = $houseModel->findAll($criteria);
        return array_map(function (AddressHouse $house) use ($finderResult, $specific) {
            return $finderResult->copy()
                ->setAddressHouse($house)
                ->setSpecific($specific)
                ->setOktmo($house->oktmo);
        }, $houses);
    }
    protected function setupFindHouseNumberCriteria(CDbCriteria $criteria, AddressRequest $addressRequest) {
        $addressPart = $addressRequest->getCurrentPart();
        $addressPart &&
        $criteria->addSearchCondition('number', sprintf('%s%%', $addressPart->getAddress()), false);
        $addressRequest->nextPart();
    }
    protected function setupFindHouseCriteria(CDbCriteria $criteria, AddressRequest $addressRequest) {
        while ($addressPart = $addressRequest->getCurrentPart()) {
            if ($addressPart->isSpecificPrefix() || !$addressPart->isHousePrefix()) {
                break;
            }
            $addressPart->isHousePrefixBuilding() &&
            $criteria->addSearchCondition('building', sprintf('%s%%', $addressPart->getAddress()), false);
            $addressPart->isHousePrefixStructure() &&
            $criteria->addSearchCondition('structure', sprintf('%s%%', $addressPart->getAddress()), false);
            $addressRequest->nextPart();
        }
    }
    /**
     * @param AddressRequest $addressRequest
     * @return array
     */
    protected function getSpecificByAddressRequest(AddressRequest $addressRequest) {
        $specific = array();
        while ($addressPart = $addressRequest->getCurrentPart()) {
            if ($addressPart->isSpecificPrefix()) {
                $specific[$addressPart->getPrefix()] = $addressPart->getAddress();
            } else {
                break;
            }
            $addressRequest->nextPart();
        }
        return $specific;
    }
}