<?php
/**
 * @package turniersystem
 * @subpackage include
 */
require_once ("turnier/t_constants.php");

define('GROUP_SHOW', 0x10);

/**
 * TurnierGroup bezogene Funktionen
 *
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @version 2005/09/16 ore - initial version
 */
class TurnierGroup {
	static function getGroups() {
		$sql = "SELECT * FROM t_group ORDER BY value";
		$res = DB::query($sql);

		$groups = array();
		while ($row = mysql_fetch_assoc($res)) {
			$groups[$row['groupid']] = $row;
			$helper[] = $row;
		}

		// voodoo magic in here
		foreach($helper as $num => $group) {
			$groupid = $group['groupid'];

			if ($num == 0 && isset($helper[$num +1])) {
				$groups[$groupid]['prev'] = $helper[$num +1]['groupid'];

			} else if ($num == (count($helper) -1) && isset($helper[$num -1])) {
				$groups[$groupid]['next'] = $helper[$num -1]['groupid'];

			} else {
				$groups[$groupid]['prev'] = $helper[$num +1]['groupid'];
				$groups[$groupid]['next'] = $helper[$num -1]['groupid'];
			}
		}

		return $groups;
	}

	function addGroup($name) {
		$sql = "SELECT MAX(value) AS maxvalue FROM t_group";
		$res = DB::query($sql);
		$row = mysql_fetch_assoc($res);
		$next = $row['maxvalue'] +1;

		DB::query("INSERT INTO t_group SET value = '{$next}', name = '{$name}'");
	}

	function delGroup($groupid) {
		DB::query("DELETE FROM t_group WHERE groupid = '{$groupid}'");
	}

	function moveGroup($groupid, $to) {
		$sql = "SELECT * FROM t_group WHERE groupid = '{$groupid}' OR groupid = '{$to}'";
		$res = DB::query($sql);
		$row1 = mysql_fetch_assoc($res);
		$row2 = mysql_fetch_assoc($res);

		DB::query("UPDATE t_group SET value = '{$row2['value']}' WHERE groupid = '{$row1['groupid']}'");
		DB::query("UPDATE t_group SET value = '{$row1['value']}' WHERE groupid = '{$row2['groupid']}'");
	}

	function hide($groupid) {
		DB::query("UPDATE t_group SET flags = flags ^ '".GROUP_SHOW."' WHERE groupid = '{$groupid}'");
	}
}
?>
