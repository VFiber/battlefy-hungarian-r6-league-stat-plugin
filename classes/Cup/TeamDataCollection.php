<?php

namespace Grav\Plugin\BattlefyHungarianR6LeagueStatPlugin\Cup;

class TeamDataCollection implements \Iterator
{
    /**
     * @var TeamData[]
     */
    protected $data = [];

    private $iteratorPosition = 0;

    private $keyList = [];


    /**
     * @param TeamData $d
     * @return TeamData
     * @throws AlreadyInCollectionException
     */
    public function addTeamCupData(TeamData $d): TeamData
    {
        if (!empty($this->data[$d->getId()]))
        {
            throw new AlreadyInCollectionException("Team with id " . $d->getId() . " already exists!");
        }

        $this->data[$d->getId()] = $d;

        return $d;
    }

    /**
     * @param string $id
     * @return TeamData
     * @throws NotFoundException
     */
    public function getTeamByID(string $id): TeamData
    {
        if (empty($this->data[$id]))
        {
            throw new NotFoundException("No such team with id: " . $id);
        }

        return $this->data[$id];
    }

    public function removeTeam(string $id): bool
    {

        unset($this->data[$id]);

        return true;
    }

    public function current(): TeamData
    {
        return $this->data[$this->key()];
    }

    public function next()
    {
        $this->iteratorPosition++;
    }

    public function key(): string
    {
        return $this->keyList[$this->iteratorPosition];
    }

    public function valid()
    {
        return !empty($this->keyList[$this->iteratorPosition]);
    }

    public function rewind()
    {
        $this->iteratorPosition = 0;
        $this->keyList = array_keys($this->data);
    }
}
