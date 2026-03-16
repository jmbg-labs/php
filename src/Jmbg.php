<?php

namespace JmbgLabs\Jmbg;

use DateTime;

/**
 * Class Jmbg
 *
 * @package Jmbg
 */
final class Jmbg implements JmbgInterface
{
    private array $parts;

    /**
     * @throws JmbgException
     */
    public function __construct(string $jmbg)
    {
        $jmbg = trim($jmbg);
        $len = strlen($jmbg);
        if ($len != 13) {
            throw new JmbgException('Input string must have 13 digits.');
        }

        $this->parts = self::getParts($jmbg);

        $this->isValid();
    }

    /**
     * @throws JmbgException
     */
    public static function parse(string $jmbg): JmbgInterface
    {
        return new self($jmbg);
    }

    public static function valid(string $jmbg): bool
    {
        try {
            self::parse($jmbg);
            return true;
        } catch (JmbgException) {
            return false;
        }
    }

    public function isMale(): bool
    {
        return $this->parts['unique'] >= 0 && $this->parts['unique'] <= 499;
    }

    public function isFemale(): bool
    {
        return $this->parts['unique'] >= 500 && $this->parts['unique'] <= 999;
    }

    public function getAge(): int
    {
        $diff = $this->getDate()->diff(new DateTime());
        return $diff->invert === 0 ? $diff->y : -$diff->y;
    }

    public function getDate(): DateTime
    {
        return new DateTime(sprintf('%04d-%02d-%02d', $this->parts['year'], $this->parts['month'], $this->parts['day']));
    }

    public function format(): string
    {
        return $this->parts['original'];
    }

    public function __toString(): string
    {
        return $this->parts['original'];
    }

    public function __get(string $name)
    {
        if (isset($this->parts[$name])) {
            return $this->parts[$name];
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        trigger_error(
            sprintf(
                'Undefined property via __get(): %s in %s on line %d',
                $name,
                $trace[0]['file'],
                $trace[0]['line']
            ),
            E_USER_NOTICE
        );

        return null;
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->parts);
    }

    private function isValid(): bool
    {
        $parts = $this->parts;
        $jmbg = $parts['original'];

        // Has to be exactly 13 digits
        if (strlen($jmbg) !== 13 || !ctype_digit($jmbg)) {
            throw new JmbgException('JMBG string must have 13 digits.');
        }

        // Date validation
        if (!checkdate($parts['month'], $parts['day'], $parts['year'])) {
            throw new JmbgException("Date '{$parts['day']}.{$parts['month']}.{$parts['year']}.' is not valid.");
        }

        // Region of birth validation
        $countryCode = substr(sprintf('%02d', $parts['region']), 0, 1);
        $regionCode = substr(sprintf('%02d', $parts['region']), 1, 1);
        
        if (!array_key_exists($countryCode, self::REGIONS) || !array_key_exists($regionCode, self::REGIONS[$countryCode])) {
            throw new JmbgException("Region '{$parts['region']}' is not valid for Serbian JMBG.");
        }

        // Checksum validation
        $digits = array_map('intval', str_split($jmbg));

        $s = 7 * $digits[0] + 6 * $digits[1] + 5 * $digits[2] + 4 * $digits[3]
            + 3 * $digits[4] + 2 * $digits[5] + 7 * $digits[6] + 6 * $digits[7]
            + 5 * $digits[8] + 4 * $digits[9] + 3 * $digits[10] + 2 * $digits[11];

        $m = $s % 11;

        $k = match (true) {
            $m === 0 => 0,
            $m === 1 => null, // invalid JMBG
            default => 11 - $m,
        };

        if ($k === null || $k !== $digits[12]) {
            throw new JmbgException('Checksum is not valid.');
        }

        return true;
    }

    private static function getParts(string $jmbg): array
    {
        $dayOriginal = substr($jmbg, 0, 2);
        $day = (int)$dayOriginal;

        $monthOriginal = substr($jmbg, 2, 2);
        $month = (int)$monthOriginal;

        $yearOriginal = substr($jmbg, 4, 3);
        $currentYear = (int)date('Y');
        $currentShort = $currentYear % 1000;

        $fullYear = (int)$yearOriginal <= $currentShort ?
            2000 + (int)$yearOriginal :
            1000 + (int)$yearOriginal;

        $regionOriginal = substr($jmbg, 7, 2);
        $region = (int)$regionOriginal;

        $countryCode = substr($regionOriginal, 0, 1);
        $regionCode = substr($regionOriginal, 1, 1);

        $uniqueOriginal = substr($jmbg, 9, 3);
        $unique = (int)$uniqueOriginal;

        return [
            'original' => $jmbg,
            'day' => $day,
            'day_original' => $dayOriginal,
            'month' => $month,
            'month_original' => $monthOriginal,
            'year' => $fullYear,
            'year_original' => $yearOriginal,
            'region' => $region,
            'region_original' => $regionOriginal,
            'region_text' => self::REGIONS[$countryCode][$regionCode] ?? null,
            'country_code' => $countryCode,
            'country' => self::COUNTRIES[$countryCode] ?? null,
            'unique' => $unique,
            'unique_original' => $uniqueOriginal,
            'checksum' => (int)substr($jmbg, 12, 1),
        ];
    }

    /**
     * Array of ex-YU countries
     */
    private const COUNTRIES = array(
        '0' => 'foreign citizens',
        '1' => 'Bosnia and Herzegovina',
        '2' => 'Montenegro',
        '3' => 'Croatia',
        '4' => 'Macedonia',
        '5' => 'Slovenia',
        '7' => 'Serbia',
        '8' => 'Serbia/Vojvodina',
        '9' => 'Serbia/Kosovo',
    );

    /**
     * Array of ex-YU countries' regions
     */
    private const REGIONS = array(
        '0' => array(
            '0' => 'naturalized citizens which had no republican citizenship',
            '1' => 'foreigners in Bosnia and Herzegovina',
            '2' => 'foreigners in Montenegro',
            '3' => 'foreigners in Croatia',
            '4' => 'foreigners in Macedonia',
            '5' => 'foreigners in Slovenia',
            '6' => 'foreigners in Serbia',
            '7' => 'foreigners in Serbia/Vojvodina',
            '8' => 'foreigners in Serbia/Kosovo',
            '9' => 'naturalized citizens which had no republican citizenship',
        ),

        '1' => array(
            '0' => 'Banja Luka',
            '1' => 'Bihać',
            '2' => 'Doboj',
            '3' => 'Goražde',
            '4' => 'Livno',
            '5' => 'Mostar',
            '6' => 'Prijedor',
            '7' => 'Sarajevo',
            '8' => 'Tuzla',
            '9' => 'Zenica',
        ),

        '2' => array(
            '0' => '',
            '1' => 'Podgorica',
            '2' => 'Bar, Ulcinj',
            '3' => 'Budva, Kotor, Tivat',
            '4' => 'Herceg Novi',
            '5' => 'Cetinje',
            '6' => 'Nikšić',
            '7' => 'Berane, Rožaje, Plav, Andrijevica',
            '8' => 'Bijelo Polje, Mojkovac',
            '9' => 'Pljevlja, Žabljak',
        ),

        '3' => array(
            '0' => 'Osijek, Slavonija',
            '1' => 'Bjelovar, Virovitica, Koprivnica, Pakrac, Podravina',
            '2' => 'Varaždin, Međimurje',
            '3' => 'Zagreb',
            '4' => 'Karlovac, Kordun',
            '5' => 'Gospić, Lika',
            '6' => 'Rijeka, Pula, Gorski kotar, Istra',
            '7' => 'Sisak, Banovina',
            '8' => 'Split, Zadar, Šibenik, Dubrovnik, Dalmacija',
            '9' => 'Hrvatsko Zagorje',
        ),

        '4' => array(
            '0' => '',
            '1' => 'Bitola',
            '2' => 'Kumanovo',
            '3' => 'Ohrid',
            '4' => 'Prilep',
            '5' => 'Skopje',
            '6' => 'Strumica',
            '7' => 'Tetovo',
            '8' => 'Veles',
            '9' => 'Štip',
        ),

        '5' => array(
            '0' => '',
        ),

        '7' => array(
            '0' => '',
            '1' => 'Belgrade',
            '2' => 'Kragujevac',
            '3' => 'Niš',
            '4' => 'Leskovac, Vranje',
            '5' => 'Zaječar, Bor',
            '6' => 'Smederevo, Požarevac',
            '7' => 'Mačva, Kolubara',
            '8' => 'Čačak, Kraljevo, Kruševac',
            '9' => 'Užice',
        ),

        '8' => array(
            '0' => 'Novi Sad',
            '1' => 'Sombor',
            '2' => 'Subotica',
            '3' => 'Vrbas',
            '4' => 'Kikinda',
            '5' => 'Zrenjanin',
            '6' => 'Pančevo',
            '7' => 'Vršac',
            '8' => 'Ruma',
            '9' => 'Sremska Mitrovica',
        ),

        '9' => array(
            '0' => '',
            '1' => 'Priština',
            '2' => 'Kosovska Mitrovica',
            '3' => 'Peć',
            '4' => 'Đakovica',
            '5' => 'Prizren',
            '6' => 'Gnjilane, Kosovska Kamenica, Vitna, Novo Brdo',
            '7' => '',
            '8' => '',
            '9' => '',
        ),
    );
}