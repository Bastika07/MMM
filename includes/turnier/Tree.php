<?php
/**
 * @package turniersystem
 * @subpackage include
 */

/**
 * @ignore
 */
define ('TREE_UNUSED',	0x0000);	// unbenutzt, kein <td>
/**
 * @ignore
 */
define ('TREE_VALUE',	0x0FFF);	// hier ist das X
/**
 * @ignore
 */
define ('TREE_FREE',	0x1000);	// unbenutzt, <td>
/**
 * @ignore
 */
define ('TREE_MATCH',	0x2000);	// match X <td rowspan=3>
/**
 * @ignore
 */
define ('TREE_LINE',	0x4000);	// linie <td rowspan=X>
/**
 * @ignore
 */
define ('TREE_SPAN',	0x8000);	// span <td rowspan=X>
/**
 * @ignore
 */
define ('TREE_SPAN2',	0x10000);	// span <td rowspan=X colspan=2>
/**
 * @ignore
 */
define ('TREE_ROUND',	0x20000);	// round X

/**
 * Gibt ein Array fürs rendern eines Turnierbaums zurueck.
 *
 * <pre>WARNING: black magic in here
 * prinzip:
 * - erzeugen eines grossen x/y arrays
 * - platzieren der matches & linien zwischen den matches
 * - leerraeume zusammenfassen
 * - ueberfluessige indexe aus dem array entfernen</pre>
 *
 * @param string $mode 'single' | 'double'
 * @param int $size anzahl der teams in der ersten Runde
 *
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @copyright 2004/03 Olaf Rempel
 */
function buildTree($mode, $size) {
	// hoehe berechnen
	$height = 0;
	for ($i = 1; $i < $size; $i *= 2)
	$height++;

	// rows & cols berechnen
	$rows = $size*2 -1;
	$cols = ($mode == 'single') ? ($height*2 -1) : ($height*2 + ($height-1)*3);

	for ($x= 1; $x <= $cols; $x++)
		for ($y= 0; $y <= $rows; $y++)
			$field[$y][$x] = TREE_FREE;

	// ein paar vars...
	$cnt= 0;
	$minY= 1;		// Y-position erstes Match
	$stepY= 4;		// alle Y positionen ein Match
	$mark= true;		// soll zwischen den beiden Matches eine line sein?
	$marklen= 3;		// wie lang ist in der ersten Runde die line?
	$round = 0;

	// single beginnt bei row 1, double irgendwo inner mitte
	$x = ($mode == "single") ? 1 : $cols - ($height*2) +1;

	// single hat insgesamt size-2 paarungen
	while ($cnt <= $size-2) {
		$field[0][$x] = (TREE_ROUND | $round++);
		for ($y = $minY; $y <= $rows; $y += $stepY) {
			// match einfuegen, und fuer optimizer markieren
			$field[$y][$x] = (TREE_MATCH | $cnt);
			$field[$y+1][$x] = TREE_UNUSED;
			$field[$y+2][$x] = TREE_UNUSED;

			// var. rowspan für linien zwischen den spielen
			if ($cnt < $size-2 && $mark) {

				// linie im winnerbracket
				$field[$y+2][$x+1] = (TREE_LINE | $marklen);
				for ($i = 1; $i < $marklen; $i++)
					$field[$y+2+$i][$x+1] = TREE_UNUSED;

				// hack: erste liniengruppe im loserbracket
				if (($cnt < $size/2) && $mode == "double") {
					$field[$y+2][$x-1] = (TREE_LINE | $marklen);
					for ($i = 1; $i < $marklen; $i++)
						$field[$y+2+$i][$x-1] = TREE_UNUSED;
				}
			}
			$cnt++;
			$mark = !$mark;
		}
		$x += 2;

		// abstaende zwischen matches vergroessern
		// 1, 3, 7, 15, ... +(2^x-1)
		$minY = $minY << 1 | 1;
		$marklen = $marklen << 1 | 1;

		$stepY *= 2;
	}

	// zusätzlicher durchlauf für loserbracket
	if ($mode == "double") {
		// absolutes finale hinzufuegen
		$field[$rows/2][$cols]= (TREE_MATCH | $cnt++);
		$field[$rows/2+1][$cols]= TREE_UNUSED;
		$field[$rows/2+2][$cols]= TREE_UNUSED;
		$field[0][$x-1] = (TREE_ROUND | $round++);

		$x = $cols - ($height*2)-1;
		$minY = 3;
		$stepY = 8;
		$mark = true;
		$marklen = 7;
		$round = 257;

		while ($cnt < 3*$size/2 -1) {
			$field[0][$x] = (TREE_ROUND | $round++);
			$field[0][$x-1] = (TREE_ROUND | $round++);
			for ($y = $minY; $y <= $rows; $y += $stepY) {
				// 3er rowspan fuer ein spiel (LB vs LB)
				$field[$y][$x] = (TREE_MATCH | $cnt);
				$field[$y+1][$x] = TREE_UNUSED;
				$field[$y+2][$x] = TREE_UNUSED;
				// 3er rowspan fuer ein spiel (LB vs WB)
				$field[$y][$x-1] = (TREE_MATCH | ($cnt+$size/2));
				$field[$y+1][$x-1] = TREE_UNUSED;
				$field[$y+2][$x-1] = TREE_UNUSED;

				// var. rowspan für linien zwischen den spielen
				if (($cnt < 3*$size/2 -2) && $mark) {
					$field[$y+2][$x-2] = (TREE_LINE | $marklen);
					for ($i = 1; $i < $marklen; $i++)
						$field[$y+2+$i][$x-2] = TREE_UNUSED;
				}
				$mark = !$mark;
				$cnt++;
			}
			$x -= 3;

			// 1, 3, 7, 15, ... (2^x-1)
			$minY = $minY << 1 | 1;
			$marklen = $marklen << 1 | 1;
			$stepY *= 2;
		}

	} else {
		// spiel um platz 3
		$x -= 2;
		$y = ($minY>>1) + 4;

		// match einfuegen, und fuer optimizer markieren
		$field[$y][$x] = (TREE_MATCH | $cnt);
		$field[$y+1][$x] = TREE_UNUSED;
		$field[$y+2][$x] = TREE_UNUSED;
	}

	// rowspan optimizer
	for ($x = 1; $x <= $cols; $x++) {
		$tmpY = 0;
		for ($y = 1; $y <= $rows; $y++) {
			if (($field[$y][$x] == TREE_UNUSED) || ($field[$y][$x] & (TREE_MATCH | TREE_LINE | TREE_SPAN))) {
				// das feld wird genutzt
				if ($tmpY != 0 && $cnt > 1) {
					// wenn davor ein leerer bereich war, diese
					// felder zusammenschliessen

					// gibt es einen linken nachbarn, der den gleichen span hat?
					if (isset($field[$tmpY][$x-1]) && ($field[$tmpY][$x-1] == (TREE_SPAN | $cnt))) {
						// dann wirds ein doppelter span
						$field[$tmpY][$x-1] = (TREE_SPAN2 | $cnt);
						$field[$tmpY][$x] = TREE_UNUSED;
					} else {
						$field[$tmpY][$x] = (TREE_SPAN | $cnt);
					}
					for ($i= 1; $i < $cnt; $i++)
						$field[$tmpY+$i][$x] = TREE_UNUSED;
				}
				$cnt = 0;
				$tmpY = 0;
			} else {
				// das feld wird nicht genutzt, kann also zusammengefasst werden
				if ($tmpY == 0) {
					// anfang der zusammenfassung
					$tmpY = $y;
					$cnt = 1;
				} else {
					// weiterführen
					$cnt++;
				}
			}
		}
		if ($tmpY != 0 && $cnt > 1) {
			// felder zusammenschliessen
			// gibt es einen linken nachbarn, der den gleichen span hat?
			if (isset($field[$tmpY][$x-1]) && ($field[$tmpY][$x-1] == (TREE_SPAN | $cnt))) {
				// dann wirds ein doppelter span
				$field[$tmpY][$x-1] = (TREE_SPAN2 | $cnt);
				$field[$tmpY][$x] = TREE_UNUSED;
			} else {
				$field[$tmpY][$x] = (TREE_SPAN | $cnt);
			}
			for ($i = 1; $i < $cnt; $i++)
				$field[$tmpY+$i][$x] = TREE_UNUSED;
		}
		$cnt= 0;
		$tmpY= 0;
	}

	// ueberfluessige indexe entfernen
	for ($x = 1; $x <= $cols; $x++)
		for ($y = 1; $y <= $rows; $y++)
			if ($field[$y][$x] == TREE_UNUSED)
				unset($field[$y][$x]);
	return $field;
}
?>
