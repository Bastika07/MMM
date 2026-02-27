{include file='_vorspann.tpl'}

<h1>Duschen-Status</h1>

  <form action="{$smarty.server.SCRIPT_NAME}" method="post">
    {csrf_field}
    <table cellspacing="1" class="outer">
      <tr>
        <th colspan="2">Beameranzeige bearbeiten</th>
      </tr>
      <tr class="row-0">
        <td><label>Freier Text:</label></td>
        <td><textarea id="duschen-text" name="text" cols="40" rows="20">{$text}</textarea></td>
      </tr>
      <tr class="row-1">
        <td>&nbsp;</td>
        <td><button>speichern</button></td>
      </tr>
    </table>
  </form>
  <script type="text/javascript">document.getElementById('duschen-text').focus();</script>

{include file='_nachspann.tpl'}
