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
		
		echo "\n\n======================================================\n";
		echo "                      Final result\n";
		echo "======================================================\n";
		foreach($result as $name => $triumph)
		{
		    echo "$name win $triumph match(s)\n";
		}
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
		$this->healthRemain = $this->_health;
		$match->healthRemain = $match->_health;
		
		$rount = 1;
		echo "\n==========================================================\n";
		echo "<".$this->_name.'> VS <'.$match->_name."> Start!!! \n";
		echo "==========================================================\n";

		while ( $this->healthRemain > 0 && $match->healthRemain > 0 )
		{
		    echo "\n----- <".$this->_name.'> VS <'.$match->_name."> Round - $rount\n";
			$this->roundStart( $match );
			$rount++;
		}

		if ($this->healthRemain <= 0)
		{
			$match->triumph++;
			echo "\n*********** <".$match->_name."> is winnter ***********\n";
		}
		else
		{
			$this->triumph++;
			echo "\n*********** <".$this->_name."> is winnter ***********\n";
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
			    echo '<'.$this->_name.'> hits <'.$match->_name.'>';
				if ($match->_dodge < mt_rand(1, 100))
				{
				    $critical = $this->_critical >= mt_rand(1, 100);
					$realDamage = $critical ? 2 * $this->_damage : $this->_damage;
					$match->healthRemain -= $realDamage;
					echo " with $realDamage damage, ".($critical ? 'CRITICAL!!! ' : '').$match->healthRemain." remain\n";
				}
				else
				{
				    echo " DODGE!!! ".$match->healthRemain." remain\n";
				}

				$myAttacks--;
				if ($matchAttacks) $myTurn = !$myTurn;
			}
			else
			{
			    echo '<'.$match->_name.'> hits <'.$this->_name.'>';
				if ($this->_dodge < mt_rand(1, 100))
				{
				    $critical = $match->_critical >= mt_rand(1, 100);
					$realDamage = $critical ? 2 * $match->_damage : $match->_damage;
					$this->healthRemain -= $realDamage;
					echo " with $realDamage damage, ".($critical ? 'CRITICAL!!! ' : '').$this->healthRemain." remain\n";
				}
				else
				{
				    echo " DODGE!!! ".$this->healthRemain." remain\n";
				}

				$matchAttacks--;
				if ($myAttacks) $myTurn = !$myTurn;
			}
		}
	}
}
