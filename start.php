<?php

$newGame = new Game('applicants.csv');
$newGame->start();
$newGame->winner();

class Game
{
	public $athletes = array();
	public $athletesNum = 0;

	public function __construct( $applicantsFile )
	{
		if ($handle = fopen($applicantsFile, "r"))
		{
		    while ($record = fgetcsv($handle))
		    {
		        if (!is_array($record) || count($record) !=7 || strtolower($record[0]) == 'name')
		        	continue;

		        $this->athletes[] = new Athlete($record);
		        $this->athletesNum++;
		    }
		    fclose($handle);
		}
		else
		{
			return false;
		}
	}

	public function start($attackerIndex = 0)
	{
		$matchIndex = $attackerIndex + 1;
		if ( $matchIndex == $this->athletesNum )
			return;

		do {
			$this->athletes[$attackerIndex]->attack($this->athletes[$matchIndex++]);
		} while( $matchIndex < $this->athletesNum );

		$this->start(++$attackerIndex);
	}

	public function winner()
	{
		$result = array();
		foreach( $this->athletes as $athlete )
		{
			$result[$athlete->_name] = $athlete->triumph;
		}

		arsort($result);
		print_r($result);
	}
}


class Athlete
{
	public $_name;
	public $_health;
	public $_damage;
	public $_attacks;
	public $_dodge;
	public $_critical;
	public $_initiative;
	public $triumph = 0;
	public $healthRemain = 0;

	public function __construct( array $property )
	{
		$this->_name 		= $property[0];
		$this->_health 		= intval($property[1]);
		$this->_damage 		= intval($property[2]);
		$this->_attacks		= intval($property[3]);
		$this->_dodge 		= intval($property[4]);
		$this->_critical 	= intval($property[5]);
		$this->_initiative 	= intval($property[6]);

	}

	public function attack( Athlete $match = null )
	{
		echo $this->_name.' VS '.$match->_name."\n";
		$this->healthRemain = $this->_health;
		$match->healthRemain = $match->_health;

		while ( $this->healthRemain > 0 && $match->healthRemain > 0 )
		{
			$this->roundStart( $match );
		}

		if ($this->healthRemain <= 0)
		{
			$match->triumph++;
		}
		else
		{
			$this->triumph++;
		}
	}

	public function roundStart( Athlete $match = null )
	{
		$myTurn = $this->_initiative > $match->_initiative;
		$myAttacks = $this->_attacks;
		$matchAttacks = $match->_attacks;

		while ( $this->healthRemain > 0 && $match->healthRemain > 0 && ($myAttacks || $matchAttacks) )
		{
			if ( $myTurn )
			{
				if ($match->_dodge < rand(1, 100))
				{
					$realDamage = $this->_critical >= rand(1, 100) ? 2 * $this->_damage : $this->_damage;
					$match->healthRemain -= $realDamage;
				}

				$myAttacks--;
			}
			else
			{
				if ($this->_dodge < rand(1, 100))
				{
					$realDamage = $match->_critical >= rand(1, 100) ? 2 * $match->_damage : $match->_damage;
					$this->healthRemain -= $realDamage;
				}

				$matchAttacks--;
			}

			$myTurn = !$myTurn;
		}
	}
}

