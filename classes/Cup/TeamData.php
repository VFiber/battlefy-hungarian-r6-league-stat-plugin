<?php

namespace Grav\Plugin\BattlefyHungarianR6LeagueStatPlugin\Cup;

class TeamData
{
    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @var string
     */
    protected $id;

    protected $name;
    /**
     * @var int
     */
    public $wins = 0;

    /**
     * @var int
     */
    public $match_count = 0;

    /**
     * @var int
     */
    public $sumScore = 0;

    /**
     * @var int
     */
    public $bonusPoints = 0;

    public $cupCount = 1;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function addOther(TeamData $teamData)
    {
        if ($this === $teamData)
        {
            return;
        }

        if ($teamData->getId() !== $this->id)
        {
            throw new \InvalidArgumentException("Teams are not identical.");
        }

        $this->name = $teamData->name;
        $this->wins += $teamData->wins;
        $this->match_count += $teamData->match_count;
        $this->sumScore += $teamData->sumScore;
        $this->bonusPoints += $teamData->bonusPoints;
        $this->cupCount += $teamData->cupCount;
    }

    public function toString()
    {
        return $this->id . "\t" . $this->name . "\t" . $this->match_count . "\t" . $this->sumScore . "\t" . $this->wins . "\t" . $this->bonusPoints . "\t" . ($this->wins + $this->bonusPoints);
    }

    public function toArray()
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'cupCount'    => $this->cupCount,
            'match_count' => $this->match_count,
            'sumScore'    => $this->sumScore,
            'wins'        => $this->wins,
            'bonusPoints' => $this->bonusPoints,
            'points'      => $this->bonusPoints + $this->wins
        ];
    }
}

