<?php
/**
 * Zeigt die Turnier-FAQ an
 * @author Marco Grimm <mad@innovalan.de>
 * @package turniersystem
 * @subpackage frontend
 */

require_once "classes/PelasSmarty.class.php";

$smarty = new PelasSmarty("turnier");
$smarty->displayWithFallback('turnier_faq.tpl');

?>
