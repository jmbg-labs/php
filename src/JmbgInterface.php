<?php

namespace JmbgLabs\Jmbg;

use DateTime;

interface JmbgInterface
{
    /**
     * Parse a string representation of a Serbian unique master citizen number.
     *
     * @param string $jmbg Unique master citizen number
     * @return JmbgInterface
     * @throws JmbgException
     */
    public static function parse(string $jmbg): self;

    /**
     * Test a string representation of a Serbian unique master citizen number to see if it's valid.
     *
     * @param string $jmbg Unique master citizen number
     * @return bool
     */
    public static function valid(string $jmbg): bool;

    /**
     * @return bool
     */
    public function isMale(): bool;

    /**
     * @return bool
     */
    public function isFemale(): bool;

    /**
     * Get age from a Serbian unique master citizen number.
     *
     * @return int
     * @throws \DateMalformedStringException
     */
    public function getAge(): int;

    /**
     * Get the date part of the JMBG as a DateTime object.
     *
     * @return DateTime
     * @throws \DateMalformedStringException
     */
    public function getDate(): DateTime;

    /**
     * Format JMBG into string
     *
     * @return string
     */
    public function format(): string;
}