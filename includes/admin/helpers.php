<?php
/* Hilfsfunktionen */

require_once('dblib.php');
require_once('format.php');


/* Zeige eine Tabelle zur Mandandenauswahl. */
function show_mandant_selection_list() {
    # Gibt's doch nicht, momentan kein Bedarf - sorry :)
}

/* Zeige ein Dropdown-Menü zur Mandantenauswahl. */
function show_mandant_selection_dropdown($mandanten, $key='mandant') {
    $mandantID = (int) $_REQUEST[$key];
?>

<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="get">
  Mandant: <select name="<?= $key ?>" onchange="this.form.submit();">
<?php foreach ($mandanten as $id => $name): ?>
    <option value="<?= $id ?>"<?= (($mandantID == $id) ? ' selected="selected"' : '') ?>><?= $name ?></option>
<?php endforeach; ?>
  </select>
  <input type="submit" value="anzeigen"/>
</form>

<?php
}
?>
