<?php

use Illuminate\Support;
use LSS\Array2Xml;
use Tightenco\Collect\Support\LazyCollection;

require_once('include/utils.php');
include_once './classes/Exporter.php';

class Roster
{
	var $id;
	var $team_code;
	var $number;
	var $name;
	var $pos;
	var $height;
	var $weight;
	var $dob;
	var $nationality;
	var $years_exp;
	var $college;

	public function __construct($_id=null,$_team_code=null,$_number=null,$_name=null,$_pos=null,$_height=null,$_weight=null,$_dob=null,$_nationality=null,$_years_exp=null,$_college=null){
		$this->id = $_id;
		$this->team_code = $_team_code;
		$this->number = $_number;
		$this->name = $_name;
		$this->pos = $_pos;
		$this->height = $_height;
		$this->weight = $_weight;
		$this->dob = $_dob;
		$this->nationality = $_nationality;
		$this->years_exp = $_years_exp;
		$this->college = $_college;
	}

	function index($query) {
		$idf = collect($query->getParams());
		$exporter = new Exporter();
		$data = [];
		if ($idf->has('type') && $idf['type'] == 'players') {
			$data = $this->getPlayers($idf);
		}

		if ($idf->has('type') && $idf['type'] == 'playerstats') {
			$data = $this->getPlayerStats($idf);
		}

		if ($idf->has('type') && $idf['type'] == 'playerstats') {
			$data = $this->getPlayerStats($idf);
		}

		return $exporter->format($data, $idf['format']);
	}


	function getPlayers($search) {
		$search = collect($search);
        $where = $this->queryRoster($search);
        $whereString = implode(' OR ', $where);
        $sql = count($where) > 0? 
			"
			SELECT roster.*
            FROM roster
			WHERE $whereString" : 

			"
			SELECT roster.*
            FROM roster";
        return collect(query($sql))
            ->map(function($item, $key) {
                unset($item['id']);
                return $item;
            });
    }

	function getPlayerStats($search) {
		$search = collect($search);
        $where = $this->queryRoster($search);
        $whereString = implode(' OR ', $where);
        $sql = 
			count($where) > 0?
			"
            SELECT roster.name, player_totals.*
            FROM player_totals
                INNER JOIN roster ON (roster.id = player_totals.player_id)
            WHERE $whereString" :
			"
			SELECT roster.name, player_totals.*
            FROM player_totals
                INNER JOIN roster ON (roster.id = player_totals.player_id)";
        $data = query($sql) ?: [];

        // calculate totals
        foreach ($data as &$row) {
            unset($row['player_id']);
            $row['total_points'] = ($row['3pt'] * 3) + ($row['2pt'] * 2) + $row['free_throws'];
            $row['field_goals_pct'] = $row['field_goals_attempted'] ? (round($row['field_goals'] / $row['field_goals_attempted'], 2) * 100) . '%' : 0;
            $row['3pt_pct'] = $row['3pt_attempted'] ? (round($row['3pt'] / $row['3pt_attempted'], 2) * 100) . '%' : 0;
            $row['2pt_pct'] = $row['2pt_attempted'] ? (round($row['2pt'] / $row['2pt_attempted'], 2) * 100) . '%' : 0;
            $row['free_throws_pct'] = $row['free_throws_attempted'] ? (round($row['free_throws'] / $row['free_throws_attempted'], 2) * 100) . '%' : 0;
            $row['total_rebounds'] = $row['offensive_rebounds'] + $row['defensive_rebounds'];
        }
        return collect($data);
    }

	function queryRoster($search){
		$where = [];
		if ($search->has('playerId')) $where[] = "roster.id LIKE '%" . $search['playerId'] . "%'";
        if ($search->has('player')) $where[] = "roster.name LIKE '%" . $search['player'] . "%'";
        if ($search->has('team')) $where[] = "roster.team_code LIKE '%" . $search['team']. "%'";
        if ($search->has('position')) $where[] = "roster.pos LIKE '%" . $search['position'] . "%'";
        if ($search->has('country')) $where[] = "roster.nationality LIKE '%" . $search['country'] . "%'";
		return $where;
	}
}