<?php

namespace Grav\Plugin\Shortcodes;

use Grav\Common\Utils;
use Grav\Plugin\BattlefyHungarianR6LeagueStatPlugin\Summarizer;

use TableGenerator\Render\HTMLDataTable;
use TableGenerator\Render\HTMLTable;
use TableGenerator\DataObject;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;


class BattlefyShortCodeHandler extends Shortcode
{
    protected $cupFileList = [];
    protected $bannedTeamList = [];

    public function init()
    {
        $this->shortcode->getHandlers()->add('battlefy', [$this, 'process']);
    }

    protected function addFileToList(&$validFileList, $file)
    {
        if ($file !== null)
        {
            $abspath = $this->getPath(static::sanitize($file));
        }
        if ($abspath === null)
        {
            return false;
        }
        if (!file_exists($abspath))
        {
            return false;
        }

        $validFileList[] = $abspath;

        return true;
    }

    public function process(ShortcodeInterface $sc)
    {
        $fileList = $sc->getParameter('filelist', null);

        if (($fileList === null) && ($fileList === ''))
        {
            return "<p>Battlefy Importer: Malformed shortcode (<tt>" . htmlspecialchars($sc->getShortcodeText()) . "</tt>).</p>";
        }

        // Get absolute file name
        $abspath = null;

        $fileList = explode(",", $fileList);
        $validFileList = [];

        foreach ($fileList as $file)
        {
            if (!$this->addFileToList($this->cupFileList, $file))
            {
                return "<p>Battlefy: Could not use the requested data file '$file'.</p>";
            }
        }

        $bannedTeams = $sc->getParameter('bannedTeamList', '');
        $roundsToQualify = $sc->getParameter('roundsToQualify', 9);

        if ($bannedTeams)
        {
            $bannedTeamsValidFiles = [];

            if ($this->addFileToList($bannedTeamsValidFiles, $bannedTeams))
            {
                $bannedTeams = $bannedTeamsValidFiles[0];
            }
        }

        $s = new Summarizer($this->cupFileList, $bannedTeams);

        $sumData = [];

        foreach ($s->process() as $data)
        {
            $data = $data->toArray();
            $data['roundsToQualify'] = $roundsToQualify;
            $sumData[] = $data;
        }

        $cols = [
            'name'        => 'Csapat Név',
            'cupCount'    => 'Hétvégék',
            'kvalifikalt' => [
                'Kvalifikálható*',
                function ($a, $originalRowData) {
                    return $originalRowData['cupCount'] >= $originalRowData['roundsToQualify'] ? "Igen" : "Nem";
                }
            ],
            'match_count' => 'Meccsek',
            'sumScore'    => 'Nyert kör',
            'wins'        => 'Győzelmek',
            'bonusPoints' => "Bónusz pontok",
            'points'      => 'Pontok'
        ];

        $dataTableAttributes = [
            'lengthMenu' => '[[8, 25, 50, -1], [8, 25, 50, "Mind"]]',
            'order'      => '[[ 2, "asc" ], [ 7, "desc"], [4, "desc"]]',
            'columnDefs' => '[
            {
                targets: [ 1, 2, 3, 4, 5, 6, 7 ],
                className: "dt-body-center"
            }
            ]',
            'stateSave'  => 'false'
        ];

        $do = new DataObject($cols, $sumData);

        $HTMLTable = (new HTMLDataTable(['id' => 'ccup', 'class' => 'cell-border compact stripe'], $dataTableAttributes))->setDataObject($do);
        ob_start();
        $HTMLTable->renderTable();

        return ob_get_clean();
    }

    private function getPath($fn)
    {
        if (Utils::startswith($fn, 'data:'))
        {
            $path = $this->grav['locator']->findResource('user://data', true);
            $fn = str_replace('data:', '', $fn);
        }
        else
        {
            $path = $this->grav['shortcode']->getPage()->path();
        }
        if ((Utils::endswith($path, DS)) || (Utils::startswith($fn, DS)))
        {
            $path = $path . $fn;
        }
        else
        {
            $path = $path . DS . $fn;
        }
        if (file_exists($path))
        {
            return $path;
        }
        return null;
    }

    private static function sanitize($fn)
    {
        $fn = trim($fn);
        $fn = str_replace('..', '', $fn);
        $fn = ltrim($fn, DS);
        $fn = str_replace(DS . DS, DS, $fn);
        return $fn;
    }
}
