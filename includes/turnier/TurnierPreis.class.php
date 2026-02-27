<?php
/**
 * @package turniersystem
 * @subpackage include
 */

/**
 * Funktions Sammlung für Turnier Preis Verwaltung
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @version 2004/07/21 ore - initial version
 * @static
 */
class TurnierPreis {

	/**
	 * Holt die Preisliste fuer ein Turnier.
	 * es werden min. die ersten drei Plaetze geholt
	 * @param int $turnierid
	 * @return mixed Array von Preisen
	 */
	function getList($turnierid) {
		$retval = array();
		
		$sql = "SELECT platzierung, beschreibung
			FROM t_preise
			WHERE turnierid = '{$turnierid}'
			ORDER BY platzierung";
		$res = DB::query($sql);
		$max = 0;
		
		while ($row = mysql_fetch_assoc($res)) 
			$retval[$max = $row['platzierung']] = $row['beschreibung'];
			
		// PlŠtze ohne Preis in die Liste aufnehmen
		for ($i = 1; $i < $max; $i++)
			if (!isset($retval[$i])) $retval[$i] = "";
		
		// Array in Platzierungsreihenfolge sortieren
		ksort($retval);
		
		return $retval;
	}


	/**
	 * Setzt die Preisliste fuer ein Turnier.
	 * @param int $turnierid
	 * @param mixed Array von Preisen
	 */
	function setList($turnierid, $newlist) {
		foreach ($newlist as $platzierung => $beschreibung) {
			$sql = "SELECT beschreibung
				FROM t_preise
				WHERE turnierid = '{$turnierid}'
				AND platzierung = '{$platzierung}'";
			$res = DB::query($sql);
			if (mysql_num_rows($res) > 0) {
				$row = mysql_fetch_assoc($res);
				if (empty($beschreibung)) {
					$sql = "DELETE FROM t_preise
						WHERE turnierid = '{$turnierid}'
						AND platzierung = '{$platzierung}'";
					DB::query($sql);

				} else if ($beschreibung != $row['beschreibung']) {
					$sql = "UPDATE t_preise SET
						beschreibung = '{$beschreibung}',
						wann_geaendert = '".time()."',
						wer_geaendert = '".COMPAT::currentID()."'
						WHERE turnierid = '{$turnierid}'
						AND platzierung = '{$platzierung}'";
					DB::query($sql);
				}
			} else {
				if (!empty($beschreibung)) {
					$sql = "INSERT INTO t_preise SET
						turnierid = '{$turnierid}',
						platzierung = '{$platzierung}',
						beschreibung = '{$beschreibung}',
						wann_angelegt = '".time()."',
						wer_angelegt = '".COMPAT::currentID()."'";
					DB::query($sql);
				}
			}
		}
	}
}
?>
