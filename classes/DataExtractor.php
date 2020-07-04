<?php

namespace Grav\Plugin\BattlefyHungarianR6LeagueStatPlugin;

use Grav\Plugin\BattlefyHungarianR6LeagueStatPlugin\Cup\AlreadyInCollectionException;
use Grav\Plugin\BattlefyHungarianR6LeagueStatPlugin\Cup\TeamDataCollection;
use Grav\Plugin\BattlefyHungarianR6LeagueStatPlugin\Cup\TeamData;
use Grav\Plugin\BattlefyHungarianR6LeagueStatPlugin\Cup\NotFoundException;


class DataExtractor
{
    /**
     * @var TeamDataCollection
     */
    protected $tCollection = null;
    /**
     * @var TeamData[]
     */
    protected $bonusPoints = [];

    protected $highestRoundNumber = 0;

    protected $bannedTeamlist = [];


    public function __construct()
    {
        $this->tCollection = new TeamDataCollection();
    }

    protected function createTeam(string $teamID, string $teamName): TeamData
    {
        $team = new TeamData($teamID, $teamName);

        try
        {
            return $this->tCollection->getTeamByID($teamID);

        }
        catch (NotFoundException $e)
        {
            return $this->tCollection->addTeamCupData($team);
        }
    }

    /**
     * @param array $bracket
     * @return TeamData
     */
    protected function getTeamDataByBracket(array $bracket): TeamData
    {
        if (empty($bracket['team']['persistentTeamID']) || empty($bracket['team']['name']))
        {
            throw new \InvalidArgumentException("Bracket does not contain team data.");
        }

        $teamID = $bracket['team']['persistentTeamID'];
        $name = $bracket['team']['name'];

        return $this->createTeam($teamID, $name);
    }

    protected function updateTeam($bracket, $score)
    {
        $tcd = $this->getTeamDataByBracket($bracket);

        $tcd->wins += (int)$bracket['winner'];
        $tcd->match_count++;
        $tcd->sumScore += $score;
    }

    protected function setTopTeam($bracket, $place)
    {
        $teamData = $this->getTeamDataByBracket($bracket);
        $this->bonusPoints[$place] = $teamData;
    }

    protected function addMatch(array $match)
    {
        if ($match['matchType'] == 'winner' && $this->highestRoundNumber < $match['roundNumber'])
        {
            $this->highestRoundNumber = $match['roundNumber'];
            echo $this->highestRoundNumber.' '.PHP_EOL;
            if ($match['top']['winner'])
            {
                $this->setTopTeam($match['top'], 1);
                $this->setTopTeam($match['bottom'], 2);
            }
            else
            {
                $this->setTopTeam($match['bottom'], 1);
                $this->setTopTeam($match['top'], 2);
            }
        }

        if ($match['matchType'] == 'loser')
        {
            if ($match['top']['winner'])
            {
                $this->setTopTeam($match['top'], 3);
            }
            else
            {
                $this->setTopTeam($match['bottom'], 3);
            }
        }

        $this->updateTeam($match['top'], $match['stats'][0]['stats']['top']['score']);
        $this->updateTeam($match['bottom'], $match['stats'][0]['stats']['bottom']['score']);
    }


    public function process(array $matchData, array $bannedTeamList = []): TeamDataCollection
    {
        $this->bannedTeamlist = $bannedTeamList;

        foreach ($matchData as $match)
        {
            //check if default win
            if (empty($match['top']['teamID']) || empty($match['bottom']['teamID']))
            {
                continue;
            }

            //DQ match does not count
            if ($match['top']['disqualified'] || $match['bottom']['disqualified'])
            {
                continue;
            }

            // not a real match or not a decend score has been entered
            if (!isset($match['stats'][0]['stats']['top']['score']) && !isset($match['stats'][0]['stats']['bottom']['score']))
            {
                continue;
            }

            if ($match['stats'][0]['stats']['top']['score'] < 6 && $match['stats'][0]['stats']['bottom']['score'] < 6)
            {
                //invalid score
                continue;
            }

            $this->addMatch($match);
        }

        // Bonus points
        foreach ($this->bonusPoints as $placement => $cupData)
        {
            $cupData->bonusPoints = (4 - $placement);
        }

        if (!empty($this->bannedTeamlist))
        {
            foreach ($this->bannedTeamlist as $teamID)
            {
                $this->tCollection->removeTeam($teamID);
            }
        }

        return $this->tCollection;
    }

    /**
     * @return array
     */
    public function getCupDataCollection(): TeamDataCollection
    {
        return $this->tCollection;
    }

    /**
     * @return array
     */
    public function getBonusPoints(): array
    {
        return $this->bonusPoints;
    }

    public function reset()
    {
        $this->tCollection = new TeamDataCollection();
        $this->bonusPoints = [];
        $this->highestRoundNumber = 0;
    }
}
