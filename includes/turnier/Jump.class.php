<?php
/**
 * @package turniersystem
 * @subpackage include
 */

/**
 * Sprungberechnungen fuer Single/Double Elimination Turniere
 *
 * Die Jumptable ist so optimal wie es geht.
 * Bewertung dafuer ist die Anzahl der absolut möglichen Wiederbegegnungen im
 * Loserbracket. Ein Aufeinandertreffen in der ersten Runde bringt einen Punkt,
 * in der zweiten 2, in der dritten 4, usw.
 * Die Jumptable mit der hoechsten Punktzahl wurde ausgewaehlt.
 *
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @version 2004/07/16 ore - initial version
 * @version 2007/03/27 mth - Umstellung wg. verbessertem Seeding
 */
class Jump {
	/**
	 * @var int Anzahl der Paarungen in der ersten Runde des Turniers (NICHT Anzahl der Teams!)
	 */
	var $size = 0;

	/**
	 * Sprungtabelle
	 *
	 * @var array Die Sprungtabelle für WB->LB Uebergaenge
	 * @private zu wichtig, als das es in die Doku kommt, ausserdem zu gross :)
	 * @ignore
	 */
	var $jumptable = array(
		// 1ser und 2er Baum. da gibts kein DE...
		-1, -1,

		// 4er Baum
		6,
		-1,

		// 8er Baum
		// pos 10: 4 Hits
		// pos 14: 6 Hits
		13,12,
		14,
		-1,

		// 16er Baum
		// pos 28: 2 Hits
		// pos 29: 2 Hits

		// pos 22: 16 Hits
		// pos 30: 14 Hits
		26,27, 24,25,
		29,28,
		30,
		-1,

		// 32er Baum
		// pos 44: 8 Hits
		// pos 45: 8 Hits
		// pos 60: 2 Hits
		// pos 61: 2 Hits

		// pos 46: 48 Hits
		// pos 62: 30 Hits
		50,51,48,49, 54,55,52,53,
		58,59,56,57,
		61,60,
		62,
		-1,

		// 64er Baum
		// pos 92: 20 Hits
		// pos 93: 20 Hits
		// pos 124: 6 Hits
		// pos 125: 6 Hits

		// pos 94: 144 Hits
		// pos 126: 62 Hits
		100,101,102,103, 96,97,98,99, 108,109,110,111, 104,105,106,107,
		118,119,116,117, 114,115,112,113,
		122,123,120,121,
		125,124,
		126,
		-1,

		// 128er Baum
		// pos 248: 2 Hits
		// pos 249: 2 Hits
		// pos 250: 2 Hits
		// pos 251: 2 Hits

		// pos 188: 48 Hits
		// pos 189: 48 Hits
		// pos 252: 14 Hits
		// pos 253: 14 Hits

		// pos 190: 384 Hits
		// pos 254: 126 Hits
		200,201,202,203, 204,205,206,207, 192,193,194,195, 196,197,198,199,
		216,217,218,219, 220,221,222,223, 208,209,210,211, 212,213,214,215,

		236,237,238,239, 232,233,234,235, 228,229,230,231, 224,225,226,227,
		244,245,246,247, 240,241,242,243,
		250,251,248,249,
		253,252,
		254,
		-1,

		// 256er Baum
		// pos 376: 8 Hits
		// pos 377: 8 Hits
		// pos 378: 8 Hits
		// pos 379: 8 Hits
		// pos 504: 2 Hits
		// pos 505: 2 Hits
		// pos 506: 2 Hits
		// pos 507: 2 Hits

		// pos 380: 112 Hits
		// pos 381: 112 Hits
		// pos 508: 30 Hits
		// pos 509: 30 Hits

		// pos 382: 960 Hits
		// pos 510: 254 Hits
		400,401,402,403, 404,405,406,407, 408,409,410,411, 412,413,414,415,
		384,385,386,387, 388,389,390,391, 392,393,394,395, 396,397,398,399,
		432,433,434,435, 436,437,438,439, 440,441,442,443, 444,445,446,447,
		416,417,418,419, 420,421,422,423, 424,425,426,427, 428,429,430,431,

		476,477,478,479, 472,473,474,475, 468,469,470,471, 464,465,466,467,
		460,461,462,463, 456,457,458,459, 452,453,454,455, 448,449,450,451,

		492,493,494,495, 488,489,490,491, 484,485,486,487, 480,481,482,483,
		500,501,502,503, 496,497,498,499,
		506,507,504,505,
		509,508,
		510,
		-1);


	/**
	 * Gibt die Matchnummer an, an die der Gewinner dieses Matches geschickt wird
	 * @param int $pos Matchid des Gewinners
	 * @return int Matchid wo der Gewinner hinspringt
	 */
	function getNewWinnerPos($pos) {
		// Unterscheidung Winnerbracket/Loserbracket
		if ($pos < 2*$this->size) {

			// winnerbracket overall final
			if ($pos == 2*$this->size -1)
				return -1;

			// Winnerbracket
			return ($pos>>1) + $this->size;

		} else {
			// Loserbracket
			if ($pos < 3*$this->size) {
				// Loserbracket vs Loserbracket
				return $pos + $this->size;

			} else if ($pos == $this->size*4 -2) {
				// beim letzter Paarung des Loserbrackets zurueck ins Winnerbracket
				return $pos/2;

			} else {
				// Loserbracket vs Winnerbracket
				return ($pos>>1) + $this->size;
			}
		}
	}


	/**
	 * Gibt die Matchnummer an, an die der Verlierer dieses Matches geschickt wird
	 * @param int $pos Matchid des Verlierer
	 * @return int Matchid wo der Verlierer hinspringt
	 */
	function getNewLoserPos($pos) {
		if ($pos < 2*$this->size) {
			// Winnerbracket
			if ($pos < $this->size) {
				// erste runde
				return ($pos>>1) + 2*$this->size;

			} else {
				// weitere runden springen
				return $this->jumptable[$pos];
			}
		} else {
			// Im Loserbracket gibt es keine Loser
			return -1;
		}
	}


	/**
	 * Gibt an, in welches Team der Sprungpaarung der Gewinner kommt
	 * @param int $pos Matchid des Gewinners
	 * @return int T_TEAM1 | T_TEAM2
	 */
	function winnerTeam($pos) {
		// Winnerbracket
		if ($pos < 2*$this->size) {
			// ungerade in team2
			return (($pos % 2) ? T_TEAM2 : T_TEAM1);

		// Loserbracket
		} else {
			// Loserbracket
			if ($pos < 3*$this->size) {
				// Loserbracket vs Loserbracket
				return T_TEAM1;

			} else if ($pos == $this->size*4 -2) {
				// beim letzter Paarung des Loserbrackets zurueck ins Winnerbracket
				return T_TEAM2;

			} else {
				// Loserbracket vs Winnerbracket
				// ungerade in team2
				return (($pos % 2) ? T_TEAM2 : T_TEAM1);
			}
		}
	}


	/**
	 * Gibt an, in welches Team der Sprungpaarung der Verlierer kommt
	 * @param int $pos Matchid des Verlierers
	 * @return int T_TEAM1 | T_TEAM2 | -1 wenn im LB verloren
	 */
	function loserTeam($pos) {
		// Winnerbracket
		if ($pos < 2*$this->size) {
			// erste runde
			if ($pos < $this->size)
				return (($pos % 2) ? T_TEAM2 : T_TEAM1);

			return T_TEAM2;

		// Loserbracket
		} else {
			// Im Loserbracket gibt es keine Loser
			return -1;
		}
	}


	/**
	 * korregiert die Nummerierung der Matchids im Turnierbaum
	 * @param int $pos Matchid
	 * @return int paarungsnummer die angezeigt werden soll
	 * @todo implementieren
	 */
	function calcReal($pos) {
		return $pos +1;
	}


	/**
	 * Errechnet die Xste Seed Position
	 * @param int $pos Position
	 * @param int $size Groesse des Feldes
	 * @param int $freilose Anzahl Freilose im Baum
	 * @return int gesetzte Position
	 *
	 */
	function seed($pos, $size, $freilose) {
		#19.03.2007 Markus Thomas
		# Hier wird die Position im Turnierbaum so manipuliert
		# das möglichst viele Teams die hoch geseedet wurden
		# ein Freilos bekommen.
		# Beispiel: Wird in einem 8er Baum ein Freilos vergeben,
		# wird der erste geseedete an Position 4 gesetzte, damit
		# er auf das Freilos trifft, welches am Ende(also Position 8)
		# eingefügt wird.
		# Die Idee, das die guten die Freilose bekommen, stammt
		# btw von Panic.
		# Die "Positionskorrektur" wird nicht bei Freilosen vorgenommen
		# da, wenn alles verschoben würde, alles wie vorher währe.
		# Beim Verschieben der Teilnehmer wird ja schließlich 
		# davon ausgegangen das die Freilose an den letzten
		# Positionen bleiben!
		if(($size/2>$freilose)&&($pos<$size-$freilose)) {
			$pos = $size/2-$freilose+$pos;
			$pos = ($pos>=$size-$freilose) ? $pos-($size-$freilose) : $pos;
		}
		$out = 0;
		$lsb = 0;
		while (($size = $size>>1) > 0) {
			$lsb = ($pos & 1) ^ $lsb;
			$out = ($out<<1) | $lsb;
			$pos = ($pos>>1);
		}
		return $out;
	}


	/**
	 * gibt ein Seed Array zurueck
	 * @param int size Groesse des Feldes (2^x)
	 * @param TurnierObj turnier wird für Freilosermittlung benötigt
	 * @return mixed Array von Positionen
	 */
	function getSeedTable($size, $turnier) {
		$Freilose = $turnier->teamnum  - Team::getTeamCount($turnier->turnierid);
		for ($i = 0; $i<$size; $i++)
			$retval[$i] = Jump::seed($i, $size, $Freilose);
		return $retval;
	}
}
?>
