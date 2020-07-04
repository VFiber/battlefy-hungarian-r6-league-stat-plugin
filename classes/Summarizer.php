<?php


namespace Grav\Plugin\BattlefyHungarianR6LeagueStatPlugin;


use Grav\Plugin\BattlefyHungarianR6LeagueStatPlugin\Cup\NotFoundException;
use Grav\Plugin\BattlefyHungarianR6LeagueStatPlugin\Cup\TeamData;
use Grav\Plugin\BattlefyHungarianR6LeagueStatPlugin\Cup\TeamDataCollection;

class Summarizer
{
    private $fileList = [];
    private $rawJsons = [];
    private $bannedTeamFileName = '';
    private $bannedTeamList = [];
    /**
     * @var TeamDataCollection[]
     */
    private $cupCollections = [];

    /**
     * @var TeamDataCollection
     */
    private $sumCollection = [];

    public function __construct(array $fileList, string $bannedTeamsFileName = '')
    {
        $this->fileList = $fileList;
        $this->bannedTeamFileName = $bannedTeamsFileName;
        $this->loadFiles();
        $this->sumCollection = new TeamDataCollection();
    }

    private function loadJsonFile($file)
    {
        if (!file_exists($file))
        {
            throw new \InvalidArgumentException("No such file: " . $file);
        }
        $rawFileData = file_get_contents($file);
        if (!$rawFileData)
        {
            throw new \InvalidArgumentException("Cannot load file content: " . $file);
        }

        $json = json_decode($rawFileData, true);

        if ($json === null)
        {
            throw new \InvalidArgumentException("File is empty or not JSON format: " . $file);
        }

        return $json;
    }

    protected function loadFiles()
    {
        foreach ($this->fileList as $file)
        {
            $this->rawJsons[$file] = $this->loadJsonFile($file);
        }

        if (!empty($this->bannedTeamFileName))
        {
            $this->bannedTeamList = $this->loadJsonFile($this->bannedTeamFileName);
            if (!is_array($this->bannedTeamList))
            {
                throw new \InvalidArgumentException("Banned team list does not contains an array: " . $this->bannedTeamFileName);
            }
        }
    }

    public function process(): TeamDataCollection
    {
        $de = new DataExtractor();

        foreach ($this->rawJsons as $fileName => $json)
        {
            $this->cupCollections[$fileName] = $de->process($json, $this->bannedTeamList);
            $de->reset();
        }

        foreach ($this->cupCollections as $fileName => $collection)
        {
            foreach ($collection as $teamData)
            {
                $this->addTeamToSum($teamData);
            }
        }

        return $this->sumCollection;
    }

    protected function addTeamToSum(TeamData $teamData)
    {
        $sumTeam = null;

        try
        {
            $sumTeam = $this->sumCollection->getTeamByID($teamData->getId());
        }
        catch (NotFoundException $e)
        {
            $sumTeam = $this->sumCollection->addTeamCupData($teamData);
            //we are done, no previous data
            return;
        }

        $sumTeam->addOther($teamData);
    }
}
