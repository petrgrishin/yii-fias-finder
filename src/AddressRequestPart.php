<?php
/**
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace PetrGrishin\Fias\Finder;


class AddressRequestPart {
    private $prefix;
    private $address;
    private $prefixAliases = array(
        'область|обл(?:\.)?' => 'обл',
        'город|г(?:\.)?|гор(?:\.)?' => 'г',
        'улица|ул(?:\.)?' => 'ул',
        'переулок|пер(?:\.)?' => 'пер',
        'проспект|пр-кт|пр(?:\.)?' => 'пр-кт',
        'площадь|пл(?:\.)?' => 'пл',
        'шоссе|ш(?:\.)?' => 'ш',
        'проезд' => 'проезд',
        'линия' => 'линия',
        'аллея|ал(?:\.)?' => 'ал',
        'тер(?:\.)?' => 'тер',
        'деревня' => 'деревня',
        'гск' => 'гск',
        'снт' => 'снт',
        'а/я' => 'а/я',
        'аал' => 'аал',
        'автодорога' => 'автодорога',
        'АО' => 'АО',
        'Аобл' => 'Аобл',
        'арбан' => 'арбан',
        'аул' => 'аул',
        'б-р' => 'б-р',
        'балка' => 'балка',
        'берег' => 'берег',
        'бугор' => 'бугор',
        'бухта' => 'бухта',
        'вал' => 'вал',
        'въезд' => 'въезд',
        'высел' => 'высел',
        'горка' => 'горка',
        'городок' => 'городок',
        'днп' => 'днп',
        'дор' => 'дор',
        'дп' => 'дп',
        'ж/д_будка' => 'ж/д_будка',
        'ж/д_казарм' => 'ж/д_казарм',
        'ж/д_оп' => 'ж/д_оп',
        'ж/д_платф' => 'ж/д_платф',
        'ж/д_пост' => 'ж/д_пост',
        'ж/д_рзд' => 'ж/д_рзд',
        'ж/д_ст' => 'ж/д_ст',
        'жилзона' => 'жилзона',
        'жилрайон' => 'жилрайон',
        'жт' => 'жт',
        'заезд' => 'заезд',
        'заимка' => 'заимка',
        'зона' => 'зона',
        'казарма' => 'казарма',
        'канал' => 'канал',
        'кв-л' => 'кв-л',
        'км' => 'км',
        'кольцо' => 'кольцо',
        'кордон' => 'кордон',
        'коса' => 'коса',
        'кп' => 'кп',
        'край' => 'край',
        'лпх' => 'лпх',
        'м' => 'м',
        'массив' => 'массив',
        'маяк' => 'маяк',
        'местность' => 'местность',
        'мкр' => 'мкр',
        'мост' => 'мост',
        'н/п' => 'н/п',
        'наб' => 'наб',
        'нп' => 'нп',
        'округ' => 'округ',
        'остров' => 'остров',
        'п' => 'п',
        'п/о' => 'п/о',
        'п/р' => 'п/р',
        'п/ст' => 'п/ст',
        'парк' => 'парк',
        'пгт' => 'пгт',
        'переезд' => 'переезд',
        'пл-ка' => 'пл-ка',
        'платф' => 'платф',
        'погост' => 'погост',
        'полустанок' => 'полустанок',
        'починок' => 'починок',
        'промзона' => 'промзона',
        'просек' => 'просек',
        'просека' => 'просека',
        'проселок' => 'проселок',
        'проток' => 'проток',
        'проулок' => 'проулок',
        'р-н' => 'р-н',
        'Респ' => 'Респ',
        'рзд' => 'рзд',
        'рп' => 'рп',
        'ряды' => 'ряды',
        'с/а' => 'с/а',
        'с/мо' => 'с/мо',
        'с/о' => 'с/о',
        'с/п' => 'с/п',
        'с/с' => 'с/с',
        'сад' => 'сад',
        'сквер' => 'сквер',
        'сл' => 'сл',
        'спуск' => 'спуск',
        'ст' => 'ст',
        'ст-ца' => 'ст-ца',
        'стр' => 'стр',
        'тоннель' => 'тоннель',
        'тракт' => 'тракт',
        'туп' => 'туп',
        'у' => 'у',
        'уч-к' => 'уч-к',
        'ф/х' => 'ф/х',
        'ферма' => 'ферма',
        'х' => 'х',
        'Чувашия' => 'Чувашия',
    );

    private $prefixAliasesForHouse = array(
        'дом|д(?:\.)?' => self::PREFIX_HOUSE_NUMBER,
        'строение|с(?:\.)?' => self::PREFIX_HOUSE_STRUCTURE,
        'корпус|к(?:\.)?|кор(?:\.)?' => self::PREFIX_HOUSE_BUILDING,
    );

    private $prefixAliasesForSpecificPart = array(
        'офис|оф(?:\.)?' => 'оф.',
        'квартира|кв(?:\.)?' => 'кв.',
        'помещение|пом(?:\.)?' => 'пом.',
        'комната|ком(?:\.)?' => 'ком.',
    );

    const PREFIX_HOUSE_NUMBER = 'д';
    const PREFIX_HOUSE_STRUCTURE = 'с';
    const PREFIX_HOUSE_BUILDING = 'к';

    public function __construct($address) {
        $prepareAddress = $this->prepareAddress(trim($address));
        $this->address = $prepareAddress['address'];
        $this->prefix = $prepareAddress['prefix'];
    }

    protected function prepareAddress($address) {
        $prefixAliases = array_merge($this->prefixAliases, $this->prefixAliasesForHouse, $this->prefixAliasesForSpecificPart);
        foreach ($prefixAliases as $pattern => $prefix) {
            $matches = array();
            if (preg_match('#^(?:' . $pattern . ')(?:\s)+(.+)$|^(.+)(?:\s)+(?:' . $pattern . ')$#iu', $address, $matches)) {
                return array(
                    'address' => trim(array_key_exists(2, $matches) ? $matches[2] : $matches[1]),
                    'prefix' => trim($prefix),
                );
            }
        }
        return array(
            'address' => trim($address),
            'prefix' => null,
        );
    }

    /**
     * @return string
     */
    public function getPrefix() {
        return $this->prefix;
    }

    public function isSpecificPrefix() {
        return $this->getPrefix() && false !== array_search($this->getPrefix(), $this->prefixAliasesForSpecificPart);
    }

    public function isHousePrefix() {
        return $this->getPrefix() && false !== array_search($this->getPrefix(), $this->prefixAliasesForHouse);
    }

    public function isHousePrefixNumber() {
        return $this->isHousePrefix() && $this->getPrefix() == self::PREFIX_HOUSE_NUMBER;
    }

    public function isHousePrefixBuilding() {
        return $this->isHousePrefix() && $this->getPrefix() == self::PREFIX_HOUSE_BUILDING;
    }

    public function isHousePrefixStructure() {
        return $this->isHousePrefix() && $this->getPrefix() == self::PREFIX_HOUSE_STRUCTURE;
    }

    /**
     * @return string
     */
    public function getAddress() {
        return $this->address;
    }
}
 