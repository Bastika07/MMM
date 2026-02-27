{* Smarty Template *}
<html><head>
<link rel="stylesheet" type="text/css" href="style/style.css">
      {literal}
      <script type="text/javascript">
      //<![CDATA[
        function getElementsByClassName(node,classname) {
          if (node.getElementsByClassName) { // use native implementation if available
            return node.getElementsByClassName(classname);
          } else {
            return (function getElementsByClass(searchClass,node) {
                if ( node == null )
                  node = document;
                var classElements = [],
                    els = node.getElementsByTagName("*"),
                    elsLen = els.length,
                    pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)"), i, j;
        
                for (i = 0, j = 0; i < elsLen; i++) {
                  if ( pattern.test(els[i].className) ) {
                      classElements[j] = els[i];
                      j++;
                  }
                }
                return classElements;
            })(classname, node);
          }
        }
        
        function toggle_visibility(className) {
           var elements = getElementsByClassName(document, className),
               n = elements.length;
           for (var i = 0; i < n; i++) {
             var el = elements[i];
            if(el.style.display=="none") {
               el.style.display="";
               el.style.visibility='visible';
            } else {
               el.style.display="none";
               el.style.visibility='hidden';        

            }
          }
        }
      //]]>
      </script>
      {/literal}
</head><body bgcolor="#FFFFFF">
<h1>Turnierverwaltung</h1>

<select size="1" OnChange="document.location.href='{$smarty.server.PHP_SELF}?partyid='+this.value">
{foreach key=id item=party from=$partys}
  <option value="{$id}"{if $id == $partyid} selected="selected" {/if}>{$party.partyname}</option>
{/foreach}
</select>
<br><br>

{foreach key=id item=party from=$partys}{if $partyid == $id}
<table cellspacing="0" cellpadding="0" width="850"><tr><td class="navbar">
<table width="100%" cellspacing="1" cellpadding="3">
  <tr><td class="navbar" colspan="8"><b>{$party.partyname}</b></td></tr>
  <tr>
    <td class="dblau" width="20"></td>
    <td class="dblau" width="250"><b>Turnier</b></td>
    <td class="dblau" width="80"><b>Teams</b></td>
    <td class="dblau" width="50"><b>Liga</b></td>
    <td class="dblau" width="150"><b>Status</b></td>
    <td class="dblau" width="150"><b>Actions</b></td>
    <td class="dblau" width="150"><b>Startzeit / Rundenzeiten</b></td>
  </tr>
  <form method="POST" action="{$smarty.server.PHP_SELF}?action=multicmd">
  {foreach key=turnierid item=turnier from=$party.turniere}
  {if $turnier.groupid != $groupid}
  
    {* php}$this->_tpl_vars['groupid'] = $this->_tpl_vars['turnier']['groupid'];{/php *}
    
    {if $groups.$groupid.flags & $smarty.const.GROUP_SHOW}
      <tr><td class="dblau" colspan="7" align="center"><b>{$groups.$groupid.name}</b></td></tr>
    {/if}
  {/if}
  {cycle values='hblau,dblau' assign=tdclass}
    <tr{if $turnier.flags & $smarty.const.TURNIER_TREE_RUNDEN && $turnier.pturnierid ne 0} class="invisible_{$turnier.pturnierid}" style="display:none; visibility:hidden;"{/if}>
    <td class="{$tdclass}" width="20" align="center">{if !empty($turnier.icon)}<img src="{$turnier.icon}">{/if}</td>
    <td class="{$tdclass}" width="200">
    {if $turnier.admin}<a href="turnier/turnier_verwaltung_detail.php?action=edit&turnierid={$turnierid}">{$turnier.name}</a>{else}{$turnier.name}{/if}
    {if $turnier.flags & $smarty.const.TURNIER_TREE_RUNDEN && $turnier.pturnierid eq 0}
    <a href="turnier/javascript:void(0)" onClick="toggle_visibility('invisible_{$turnier.turnierid}')"><img src="/gfx/add.png" alt="Vorrunden anzeigen" title="Vorrunden anzeigen" style="vertical-align:text-top;"></a>
    {/if}
    </td>
    <td class="{$tdclass}" width="70">{$turnier.teams} / {$turnier.teamnum}
    {if $turnier.flags & $smarty.const.TURNIER_DOUBLE} (DE){/if}
    </td>
    <td class="{$tdclass}" width="50">{$turnier.ligastr}</td>
    <td class="{$tdclass}" width="150">{$turnier.statusstr}</td>
    <td class="{$tdclass}" width="150"><a href="turnier/turnier_verwaltung_status.php?turnierid={$turnierid}">Status &auml;ndern</a></td>
    <td class="{$tdclass}" width="150">
    {if $turnier.admin}<a href="turnier/turnier_roundlist.php?turnierid={$turnierid}">{$turnier.startzeit}</a>
    {else}{$turnier.startzeit}{/if}</td>
  </tr>
  {foreachelse}
  <tr><td class="dblau" colspan="7"></td></tr>
  {/foreach}
  </form>
</table></td></tr></table><br>
{/if}{/foreach}
</body></html>
