<?php

namespace App\Entity;

use DateTime;

/**
 * Quote entity (pure data model without rendering logic)
 */
class Quote
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $siteId;

    /**
     * @var int
     */
    private $destinationId;

    /**
     * @var DateTime
     */
    private $dateQuoted;

    /**
     * @param int $id
     * @param int $siteId
     * @param int $destinationId
     * @param DateTime $dateQuoted
     */
    public function __construct(int $id, int $siteId, int $destinationId, DateTime $dateQuoted)
    {
        $this->id = $id;
        $this->siteId = $siteId;
        $this->destinationId = $destinationId;
        $this->dateQuoted = $dateQuoted;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getSiteId(): int
    {
        return $this->siteId;
    }

    /**
     * @return int
     */
    public function getDestinationId(): int
    {
        return $this->destinationId;
    }

    /**
     * @return DateTime
     */
    public function getDateQuoted(): DateTime
    {
        return $this->dateQuoted;
    }
}