<?php
/**
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace PetrGrishin\Fias\Finder\Command;


use PetrGrishin\Fias\Finder\AddressFinder;
use PetrGrishin\Fias\Finder\AddressFinderResult;
use PetrGrishin\Fias\Finder\AddressRequest;
use CConsoleCommand;

class ApiCommand extends CConsoleCommand {

    public $addressFinderComponent = 'addressFinder';

    /**
     * @param string $address
     * @param bool $verbose
     * @throws \Exception
     */
    public function actionFindAddress($address, $verbose = false) {
        $finder = $this->getAddressFinder();
        /** @var AddressFinderResult[] $addresses */
        $addresses = $finder->findAddress(new AddressRequest($address), $verbose) ?: array();
        foreach ($addresses as $address) {
            printf("%s\n", $address->getAddressLine());
        }
    }

    protected function getApp() {
        return \Yii::app();
    }

    /**
     * @return AddressFinder
     */
    protected function getAddressFinder() {
        return $this->getApp()->getComponent($this->addressFinderComponent);
    }
}
 