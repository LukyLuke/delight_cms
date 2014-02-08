<?php
class pAdminHTTP
{
  var $Admin;
  var $BlankComment;

  function pAdminHTTP()
  {
    $this->CreateAdminHTTP();
  }

  function CreateAdminHTTP()
  {
  	$userCheck = pCheckUserData::getInstance();
    $this->BlankComment = "";
    $this->Admin             = array();
    $this->Admin['LogIn']    = $this->LoginDialog();
    $this->Admin['MainMenu'] = $this->MainMenu();

    // HTML which need Admin-Login
    if ($userCheck->checkLogin())
    {
      $this->Admin['ChangeText']           = $this->ChangeText();
      $this->Admin['ChangeMenu']           = $this->ChangeMenu();
      $this->Admin['SectionChange']        = $this->ChangeSection();
      $this->Admin['NewsManagement']       = $this->NewsManagement();
      $this->Admin['ImageManagement']      = $this->ImageManagement();
      $this->Admin['BackupManagement']     = $this->BackupManagement();
      $this->Admin['SectionManagement']    = $this->SectionManagement();
      $this->Admin['ProgramManagement']    = $this->ProgramManagement();
      $this->Admin['LanguageManagement']   = $this->LanguageManagement();
      $this->Admin['StatisticManagement']  = $this->StatisticManagement();
      $this->Admin['StaticSiteManagement'] = $this->StaticSiteManagement();
    }
  }

  function GetHtml($sec='',$val='')
  {
    if (($sec != '') && ($val != ''))
      return $this->Admin[$sec][$val];
    else
      return 'failed';
  }

  function LoginDialog()
  {
    global $template,$TemplateType,$LANG;
    $html['LogInDialog']  = '
      <form action="index.php" method="POST">
        <input type="hidden" name="tpl" value="'.$template.'" />
        <input type="hidden" name="tpt" value="'.$TemplateType.'" />
        <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
        <input type="hidden" name="log" value="doadlogin" />
        <table cellpadding="0" cellspacing="0" style="width:280px;text-align:center;vertical-align:top;table-layout:fixed;border:2px solid rgb(0,111,153);">
          <colgroup>
            <col style="width:100px;min-width:100px;" />
            <col style="width:180px;min-width:180px;" />
          </colgroup>
          <tr><td colspan="2" style="background-color:rgb(0,111,153);color:rgb(224,244,249);height:25px;font-size:13px;font-weight:bold;text-align:center;vertical-align:middle;">'.$LANG->getValue("","txt","admin_001").'</td></tr>
          <tr><td style="font-size:12px;padding:5px;text-align:left;vertical-align:middle;">'.$LANG->getValue("","txt","admin_002").'</td>
          <td style="font-size:12px;padding:5px;text-align:right;vertical-align:middle;"><input type="text" id="alogname" name="alogname" style="font-size:12px;width:160px;border:1px solid rgb(0,111,153);background-color:rgb(255,255,255);" /></td></tr>
          <tr><td style="font-size:12px;padding:5px;text-align:left;vertical-align:middle;">'.$LANG->getValue("","txt","admin_003").'</td>
          <td style="font-size:12px;padding:5px;text-align:right;vertical-align:middle;"><input type="password" id="alogpass" name="alogpass" style="font-size:12px;width:160px;border:1px solid rgb(0,111,153);background-color:rgb(255,255,255);" /></td></tr>
          <tr><td colspan="2" style="font-size:12px;padding:5px;text-align:right;vertical-align:middle;"><input type="submit" value="'.$LANG->getValue("","txt","admin_005").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" /></td></tr>
          <tr><td colspan="2" style="background-color:rgb(0,111,153);color:rgb(224,244,249);height:12px;font-size:10px;font-weight:normal;text-align:right;vertical-align:middle;">'.$LANG->getValue("","txt","admin_004").'</td></tr>
        </table>
      </form>
      <span style="font-size:9px;">&#91;&nbsp;&nbsp;</span><span style="font-size:10px;">&#169;</span><span style="font-size:9px;"> delight software gmbh / switzerland&nbsp;&nbsp;&#93;</span>
      <script type="text/javascript">
      <!--
          document.getElementById(\'alogname\').focus();
      //-->
      </script>';

    $html['ReloadJs']  = '<script type="text/javascript">
    <!--
    opener.parent.menu.location.reload();
    opener.location.reload();
    window.close();
    //-->
    </script>';
    return $html;
  }

  function MainMenu()
  {
    global $LANG;
    $html['LogInJs']  = '
    <!-- BEGIN - - Javascript for Admin-LoginWindow //-->
    <script type="text/javascript">
    <!--
    function OpenAdminLogin()
    {
    var prop  = \'width=300,height=200,top=\'+((screen.height-200)/2)+\',left=\'+((screen.width-300)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=no,status=no,toolbar=no,dependent=yes\';
    var link  = \'index.php?tpl=popup&tpt=page&log=adlogin\';
    var loginwin = window.open(link.replace(/&amp;/gi,\'&\'),\'loginwin\',prop)
    }
    //-->
    </script>
    <!-- END - - Javascript for Admin-LoginWindow //-->';


    $html['LogOutJs']  = '
    <!-- BEGIN - - Javascript for Admin-LoginWindow //-->
    <script type="text/javascript">
    <!--
    function ReloadLogoutFrames()
    {
    parent.document.location.href = \'index.php?log=none\';
    }
    //-->
    </script>
    <!-- END - - Javascript for Admin-LoginWindow //-->';

    return $html;
  }

  function SectionManagement()
  {
    global $LANG,$postId;
    $html['MainScreen']  = '<table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="" />
    </colgroup>
    <tr>
    <td style="padding:10px;">[REPLACE:ButtonBar]</td>
    </tr>
    <tr>
    <td style="padding-left:10px;padding-right:10px;">
    [REPLACE:FileList]
    </td>
    </tr>
    </table>';

    $html['ButtonBar']  = '
    <script type="text/javascript">
    <!--
    function ShowSectionFrame(obj)
    {
    if (document.getElementById(\'SectionChooser\').style.visibility == \'hidden\')
    document.getElementById(\'SectionChooser\').style.visibility = \'visible\';
    else
    document.getElementById(\'SectionChooser\').style.visibility = \'hidden\';
    obj.style.cursor = \'pointer\';
    obj.style.border = \'1px solid rgb(0,111,153)\';
    obj.style.backgroundColor = \'rgb(114,191,236)\';
    }
    function ChangeLocation(obj,act)
    {
    obj.style.cursor = \'pointer\';
    obj.style.border = \'1px solid rgb(0,111,153)\';
    obj.style.backgroundColor = \'rgb(114,191,236)\';
    if (act == \'sec\')
    {
    var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
    var link = \'index.php?tpl=popup&tpt=page&adm=[ADMIN_SECTION]&ada=6\';
    var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }
    else if (act == \'upl\')
    {
    var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
    var link = \'index.php?tpl=popup&tpt=page&adm=[ADMIN_SECTION]&ada=7&sec=[SECTION_ID]\';
    var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }
    }
    function ChangeBackground(obj,act)
    {
    if (act == \'over\')
    {
    obj.style.cursor = \'pointer\';
    obj.style.border = \'1px solid rgb(0,111,153)\';
    obj.style.backgroundColor = \'rgb(114,191,236)\';
    }
    else
    {
    obj.style.cursor = \'normal\';
    obj.style.border = \'1px solid rgb(224,240,249)\';
    obj.style.backgroundColor = \'rgb(224,240,249)\';
    }
    }
    //-->
    </script>
    <div id="SectionChooser" style="visibility:hidden;position:absolute;top:114px;left:123px;width:400px;height:300px;border:2px solid rgb(0,111,153);background-color:rgb(224,240,249);overflow:auto;" onMouseOver="ChangeBackground(document.getElementById(\'SecChos\'),\'over\');">
    [REPLACE:SectionChooser]
    </div>
    <table cellspacing="2" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:*;min-width:10px;" />
    <col style="width:100px;min-width:80px;" />   <!-- Choose a Section  //-->
    <col style="width:100px;min-width:80px;" />   <!-- Admin Section  //-->
    <col style="width:100px;min-width:80px;" />   <!-- Upload Image  //-->
    <col style="width:*;min-width:10px;" />
    </colgroup>
    <tr>
    <td colspan="5" style="border-bottom:1px solid rgb(0,111,153);border-top:1px solid rgb(0,111,153);padding:5px;text-align:center;font-size:15px;font-weight:bold;color:rgb(0,111,153);line-height:20px;background-color:rgb(245,250,255);">[ADMIN_SECTION_NAME]</td>
    </tr>
    <tr>
    <td></td>
    [-->CHANGE_SECTION]<td id="SecChos" style="padding:2px;padding-top:5px;text-align:center;vertical-align:top;border:1px solid rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;" onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="ShowSectionFrame(this);">
    <img src="images/design/admin/ChooseSection_big.gif" alt="Choose a Section" title="Choose a Section" style="width:34px;height:34px;border:0px hidden black;" /><br />'.$LANG->getValue("","txt","admin_036").'</td>[--CHANGE_SECTION:<td></td>]
    [-->ADMIN_SECTION]<td style="padding:2px;padding-top:5px;text-align:center;vertical-align:top;border:1px solid rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;" onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="ChangeLocation(this,\'sec\');">
    <img src="images/design/admin/AdminSection_big.gif" alt="Section Administration" title="Section Administration" style="width:34px;height:34px;border:0px hidden black;" /><br />'.$LANG->getValue("","txt","admin_037").'</td>[--ADMIN_SECTION:<td></td>]
    [-->UPLOAD_SECTION]<td style="padding:2px;padding-top:5px;text-align:center;vertical-align:top;border:1px solid rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;" onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="ChangeLocation(this,\'upl\');">
    <img src="images/design/admin/UploadImage_big.gif" alt="Upload Files" title="Upload Files" style="width:34px;height:34px;border:0px hidden black;" /><br />'.$LANG->getValue("","txt","admin_038").'</td>[--UPLOAD_SECTION:<td></td>]
    <td></td>
    </tr>
    <tr>
    <td colspan="5" style="padding:1px;border-top:1px solid rgb(0,111,153);border-bottom:2px solid rgb(0,111,153);height:5px;"></td>
    </tr>
    <tr>
    <td colspan="5" style="padding:5px;text-align:center;font-size:15px;font-weight:bold;color:rgb(224,240,249);line-height:20px;background-color:rgb(0,111,153);">[SECTION_NAME]</td>
    </tr>
    <tr>
    <td colspan="5" style="padding:1px;border-top:2px solid rgb(0,111,153);border-bottom:1px solid rgb(0,111,153);height:5px;"></td>
    </tr>
    </table>';

    $html['SectionChooser']  = '
    <script type="text/javascript">
    <!--
    function ChangeSectionBackground(obj,act,col)
    {
    if (act == \'over\')
    {
    obj.style.cursor = \'pointer\';
    obj.style.border = \'1px solid rgb(0,111,153)\';
    obj.style.backgroundColor = \'rgb(114,191,236)\';
    }
    else
    {
    obj.style.cursor = \'normal\';
    obj.style.border = \'1px solid \'+col;
    obj.style.backgroundColor = col;
    }
    }
    function ChangeSectionLocation(sid,act,txt)
    {
    if (act != \'sdel\')
    {
    if (act == \'def\')
    var link = \'index.php?tpl=blank&tpt=page&sec=\'+sid+\'&adm=[ADMIN_SECTION]&lan=[LANGUAGE]\';
    else if (act == \'adm\')
    var link = \'index.php?tpl=popup&tpt=page&sec=\'+sid+\'&adm=[ADMIN_SECTION]&ada=6&lan=[LANGUAGE]\';
    else if (act == \'imv\')
    var link = \'index.php?tpl=popup&tpt=page&sec=\'+sid+\'&adm=[ADMIN_SECTION]&ada=5&lan=[LANGUAGE]&i=[POST_ID]\';
    else if (act == \'nev\')
    var link = \'index.php?tpl=popup&tpt=page&sec=\'+sid+\'&adm=[ADMIN_SECTION]&ada=5&lan=[LANGUAGE]&i=[POST_ID]\';
    else if (act == \'pmv\')
    var link = \'index.php?tpl=popup&tpt=page&sec=\'+sid+\'&adm=[ADMIN_SECTION]&ada=5&lan=[LANGUAGE]&i=[POST_ID]\';
    else if (act == \'change\')
    var link = \'index.php?tpl=popup&tpt=page&sec=\'+sid+\'&adm=[ADMIN_SECTION]&ada=9&lan=[LANGUAGE]\';
    else if (act == \'add\')
    var link = \'index.php?tpl=popup&tpt=page&sec=\'+sid+\'&adm=[ADMIN_SECTION]&ada=10&lan=[LANGUAGE]\';
    else if (act == \'delete\')
    var link = \'index.php?tpl=popup&tpt=page&sec=\'+sid+\'&adm=[ADMIN_SECTION]&ada=8&lan=[LANGUAGE]\';
    else if (act == \'image\')
    var link = \'index.php?tpl=blank&tpt=page&sec=\'+sid+\'&m=[MAIN_MENU_ID]&s=[SUB_MENU_ID]&chi=[CHANGE_TEXT_ID]&lan=[LANGUAGE]&act=image&pic_id=[PICTURE_ID]\';
    window.location.href = link.replace(/&amp;/gi,\'&\');
    }
    else
    {
    var newval = document.createTextNode(txt);
    var chlen = document.getElementById(\'sec_move_text\').firstChild.nodeValue.length;
    document.getElementById(\'sec_move_text\').firstChild.deleteData(0,chlen);
    document.getElementById(\'sec_move_text\').firstChild.appendData(newval.nodeValue);
    document.getElementById(\'sec_move\').value = sid;
    document.getElementById(\'SectionChooser\').style.visibility = \'hidden\';
    }
    }
    //-->
    </script>
    <table cellspacing="2" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;" />
    </colgroup>
    [-->SECTION_ENTRY]
    <tr>
    <td style="text-align:left;vetical-align:middle;border:1px solid [ROW_COLOR];padding:2px;background-color:[ROW_COLOR];" onMouseOver="ChangeSectionBackground(this,\'over\');" onMouseOut="ChangeSectionBackground(this,\'out\',\'[ROW_COLOR]\');" onClick="ChangeSectionLocation(\'[SECTION_ID]\',\'[SECTION_TYPE]\',\'[SECTION_NAME_JS]\');">
    <img src="images/design/blank.gif" style="width:[IMAGE_WIDTH]px;height:5px;" alt="blank" />
    <img src="images/design/dataset.gif" style="width:16px;height:16px;vertical-align:bottom;" alt="arrow" />[SECTION_NAME]
    </td>
    </tr>
    [--SECTION_ENTRY]
    </table>';

    $html['SectionAdmin']  = '
    <table cellspacing="5" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:*;min-width:200px;" />
    <col style="width:100px;min-width:100px;" />
    </colgroup>
    <tr>
    <td style="padding:0px;text-align:center;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:*;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_015").'</td>
    </tr>
    <tr>
    <td style="padding:2px;text-align:left;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">[SECTION_LIST]</td>
    </tr>
    </table>
    </td>
    <td style="padding-top:0px;padding-bottom:10px;padding-right:10px;text-align:center;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:80px;min-width:80px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_016").'</td>
    </tr>
    <tr>
    <td style="padding:2px;text-align:left;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">[SECTION_FUNCTION]</td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
    <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />
    </td>
    </tr>
    </table>';

    $html['SectionFunctions']  = '<table cellspacing="2" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;" />
    </colgroup>
    <tr>
    <td style="padding:10px;text-align:center;vetical-align:middle;border:1px solid rgb(224,244,249);background-color:rgb(224,240,249);" onMouseOver="ChangeSectionBackground(this,\'over\');" onMouseOut="ChangeSectionBackground(this,\'out\',\'rgb(224,244,249)\');" onClick="ChangeSectionLocation(\'[SECTION_ID]\',\'add\');">
    <img src="images/design/admin/section_add.gif" style="margin-bottom:5px;width:34px;height:34px;" alt="add" title="Add a section" /><br />
    <span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">'.$LANG->getValue("","txt","admin_018").'</span>
    </td>
    </tr>
    <tr>
    <td style="padding:10px;text-align:center;vetical-align:middle;border:1px solid rgb(224,244,249);background-color:rgb(224,240,249);" onMouseOver="ChangeSectionBackground(this,\'over\');" onMouseOut="ChangeSectionBackground(this,\'out\',\'rgb(224,244,249)\');" onClick="ChangeSectionLocation(\'[SECTION_ID]\',\'change\');">
    <img src="images/design/admin/section_edit.gif" style="margin-bottom:5px;width:34px;height:34px;" alt="add" title="Add a section" /><br />
    <span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">'.$LANG->getValue("","txt","admin_019").'</span>
    </td>
    </tr>
    <tr>
    <td style="padding:10px;text-align:center;vetical-align:middle;border:1px solid rgb(224,244,249);background-color:rgb(224,240,249);" onMouseOver="ChangeSectionBackground(this,\'over\');" onMouseOut="ChangeSectionBackground(this,\'out\',\'rgb(224,244,249)\');" onClick="ChangeSectionLocation(\'[SECTION_ID]\',\'delete\');">
    <img src="images/design/admin/section_delete.gif" style="margin-bottom:5px;width:34px;height:34px;" alt="add" title="Add a section" /><br />
    <span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">'.$LANG->getValue("","txt","admin_017").'</span>
    </td>
    </tr>
    </table>';

    $html['SectionAdd']  = '<form action="index.php" method="POST">
    <input type="hidden" name="tpl" value="popup" />
    <input type="hidden" name="tpt" value="page" />
    <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
    <input type="hidden" name="sec" value="[SECTION_ID]" />
    <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
    <input type="hidden" name="ada" value="[ADMIN_ACTION]" />

    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:0px;text-align:center;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;" />
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_018").'</td>
    </tr>
    <tr>
    <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100px;min-width:100px;" />
    <col style="width:*;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
    '.$LANG->getValue("","txt","admin_021").':
    </td>
    <td style="padding:2px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <input type="text" name="sec_name" value="[SECTION_NAME]" style="width:100%;font-family:Helvetica, sans serif;font-size:10px;font-weight:normal;color:rgb(0,111,153);background-color:rgb(240,250,255);border:1px solid rgb(114,191,236);padding:2px;" />
    <input type="hidden" name="sec_parent" value="[SECTION_PARENT]" />
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
    <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
    <input type="submit" value="'.$LANG->getValue("","txt","admin_007").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
    </td>
    </tr>
    </table>
    </form>';

    $html['SectionDelete']  = '<form action="index.php" method="POST">
    <input type="hidden" name="tpl" value="popup" />
    <input type="hidden" name="tpt" value="page" />
    <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
    <input type="hidden" name="sec_did" value="[SECTION_DELETE]" />
    <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
    <input type="hidden" name="ada" value="[ADMIN_ACTION]" />

    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:0px;text-align:center;vertical-align:top;">
    <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;" />
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_022").'</td>
    </tr>
    <tr>
    <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:150px;min-width:150px;" />
    <col style="width:*;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
    '.$LANG->getValue("","txt","admin_021").':
    </td>
    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <span style="font-weight:bold;color:rgb(255,100,100);">[SECTION_NAME]</span>
    </td>
    </tr>
    <tr>
    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
    '.$LANG->getValue("","txt","admin_024").':
    </td>
    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <input type="radio" name="sec_rec" value="yes" checked="checked" style="vertical-align:bottom;">'.$LANG->getValue("","txt","admin_025").'&nbsp;&nbsp;<input type="radio" name="sec_rec" value="no" style="vertical-align:bottom;">'.$LANG->getValue("","txt","admin_026").'
    </td>
    </tr>
    <tr>
    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
    '.$LANG->getValue("","txt","admin_023").':
    </td>
    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    &#91;&nbsp;<span id="sec_move_text" onMouseOver="this.style.backgroundColor=\'rgb(0,111,153)\';this.style.color=\'rgb(224,240,249)\';this.style.cursor=\'pointer\';" onMouseOut="this.style.backgroundColor=\'rgb(224,240,249)\';this.style.color=\'rgb(0,111,153)\';this.style.cursor=\'normal\';" onClick="document.getElementById(\'SectionChooser\').style.visibility=(document.getElementById(\'SectionChooser\').style.visibility == \'visible\') ? \'hidden\':\'visible\';">main</span>&nbsp;&#93;
    <input id="sec_move" type="hidden" name="sec_move" value="0" />
    <div id="SectionChooser" style="visibility:hidden;position:absolute;top:115px;left:160px;width:400px;height:300px;border:2px solid rgb(0,111,153);background-color:rgb(224,240,249);overflow:auto;">
    [SECTION_CHOOSER]
    </div>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
    <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
    <input type="submit" value="'.$LANG->getValue("","txt","admin_007").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
    </td>
    </tr>
    </table>
    </form>';
    return $html;
  }

  function ImageManagement()
  {
    global $LANG,$postId;
    $html['ImageList']  = '
    <script type="text/javascript">
    <!--

    var IE = document.all?true:false;
    if (!IE) document.captureEvents(Event.MOUSEDOWN);
    document.onmousedown = GetMousePos;

    var MousePosX = 0;
    var MousePosY = 0;

    function ChangeImageBackground(Iobj,Tobj,act)
    {
    if (act == \'over\')
    {
    Iobj.style.border = \'3px solid rgb(0,111,153)\';
    document.getElementById(Tobj).style.backgroundColor = \'rgb(114,191,236)\';
    document.getElementById(Tobj).style.border = \'1px solid rgb(0,111,153)\';
    document.getElementById(Tobj).style.cursor = \'pointer\';
    }
    else
    {
    Iobj.style.border = \'3px solid rgb(114,191,236)\';
    document.getElementById(Tobj).style.backgroundColor = \'rgb(224,240,249)\';
    document.getElementById(Tobj).style.border = \'1px solid rgb(224,240,249)\';
    document.getElementById(Tobj).style.cursor = \'normal\';
    }
    }

    function ShowBigImage(id,imgW,imgH)
    {
    imgW = imgW + 180;
    imgH = imgH + 150;
    if (imgH >= (screen.height-100))
    imgH = (screen.height - 100);
    if (imgW >= (screen.width-50))
    imgW = (screen.width - 50);
    var prop = \'width=\'+imgW+\',height=\'+imgH+\',top=\'+((screen.height-imgH-50)/2)+\',left=\'+((screen.width-imgW)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
    var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=1&i=\'+id;
    var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }

    function ChooseThisPicture(id,source)
    {
    opener.document.getElementById(\'txt_picfile\').value = id;
    opener.document.getElementById(\'img_picfile\').src = source;
    window.close();
    }

    function ImageText(id)
    {
    var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
    var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=2&i=\'+id;
    var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }

    function ImageDelete(id)
    {
    var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
    var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=3&i=\'+id;
    var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }

    function ImageUpdate(id)
    {
    var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
    var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=4&i=\'+id;
    var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }

    var ImgMv = new Array();
    function ImageSelect(id,rmsel)
    {
      var idfound = false;
      var tmparr = new Array();
      for (i = 0; i < ImgMv.length; i++)
      {
        if (ImgMv[i] == id)
          idfound = true;
        if (((ImgMv[i] != id)) && (ImgMv[i] != \'\') && (ImgMv[i] != \'0\'))
          var tmp = tmparr.push(ImgMv[i]);
      }
      if (!idfound)
      {
        document.getElementById(\'divsel_\'+id).style.color = \'rgb(255,50,50)\';
        document.getElementById(\'mvimg_\'+id).src = \'images/design/admin/image_move_selected.gif\';
      }
      else
      {
        document.getElementById(\'divsel_\'+id).style.color = \'rgb(0,111,153)\';
        document.getElementById(\'mvimg_\'+id).src = \'images/design/admin/image_move.gif\';
      }
      ImgMv = tmparr;
      if ((!idfound) || (!rmsel))
        var tmp = ImgMv.push(id);

      document.getElementById(\'div_\'+id).style.visibility = \'hidden\';
    }

    function ImageMove(id)
    {
      ImageSelect(id,false);
      var ImgId = ImgMv.join(\',\');
      var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
      var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=5&i=\'+ImgId;
      var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }

    function ImageMoveShow(id)
    {
      var opt = document.getElementById(\'div_\'+id);
      if (opt.style.visibility == \'hidden\')
      {
        opt.style.top = (MousePosY+2)+\'px\';
        if ((MousePosX+2) > (document.getElementsByTagName(\'body\')[0].offsetWidth-150))
          opt.style.left = (MousePosX+2-150)+\'px\';
        else
          opt.style.left = (MousePosX+2)+\'px\';
        opt.style.visibility = \'visible\';
      }
      else
      {
        opt.style.visibility = \'hidden\';
      }
    }

    function GetMousePos(e)
    {
      if (IE)
      {
        MousePosX = event.clientX + document.body.scrollLeft;
        MousePosY = event.clientY + document.body.scrollTop
      }
      else
      {
        MousePosX = e.pageX
        MousePosY = e.pageY
      }
      if (MousePosX < 0) MousePosX = 0;
      if (MousePosY < 0) MousePosY = 0;

      return true;
    }

    //-->
    </script>
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <coolgroup>
    <col style="min-width:50px;width:*;" />
    <col style="min-width:50px;width:*;" />
    <col style="min-width:50px;width:*;" />
    <col style="min-width:50px;width:*;" />
    </colgroup>
    [-->IMAGE_ENTRY]
    <tr>
    [IMAGE]
    [IMAGE]
    [IMAGE]
    [IMAGE]
    </tr>
    <tr><td colspan="4" style="height:15px;"></td></tr>
    [--IMAGE_ENTRY]
    </table>';

    $html['ImageEntry']  = '<td id="td_[IMAGE_ID]" style="padding:3px;background-color:rgb(224,240,249);border:1px solid rgb(224,240,249);text-align:center;vertical-align:top;">
    [-->IMAGE]
    <span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">&#91;</span><span style="font-weight:normal;font-size:10px;">&nbsp;[IMAGE_TITLE]&nbsp;</span><span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">&#93;</span>
    <br /><img src="[IMAGE_SRC_SMALL]" style="margin-top:5px;margin-bottom:5px;width:[IMAGE_WIDTH_SMALL]px;height:[IMAGE_HEIGHT_SMALL]px;border:3px solid rgb(114,191,236);" alt="[IMAGE_NAME]" title="[IMAGE_NAME]" onMouseOver="ChangeImageBackground(this,\'td_[IMAGE_ID]\',\'over\');" onMouseOut="ChangeImageBackground(this,\'td_[IMAGE_ID]\',\'out\');"
    [-->BIG_IMAGE_FUNCTION]onClick="ShowBigImage(\'[IMAGE_ID]\',[IMAGE_WIDTH_BIG],[IMAGE_HEIGHT_BIG]);"[--BIG_IMAGE_FUNCTION]
    [-->TEXT_CHOOSER_FUNCTION]onClick="ChooseThisPicture(\'[IMAGE_ID]\',\'[IMAGE_SRC_SMALL]\');"[--TEXT_CHOOSER_FUNCTION]
     />
    [-->IMAGE_ADMIN_FUNCTIONS]<br />
    <img src="images/design/admin/image_edit.gif" alt="edit texts" title="edit image texts" style="width:16px;height:16px;border:0px solid black;vertical-align:bottom;" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="ImageText(\'[IMAGE_ID]\');" />
    &nbsp;&nbsp;&nbsp;&nbsp;
    <img src="images/design/admin/image_delete.gif" alt"delete image" title="delete image" style="width:16px;height:16px;border:0px solid black;vertical-align:bottom;" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="ImageDelete(\'[IMAGE_ID]\');" />
    &nbsp;&nbsp;&nbsp;&nbsp;
    <img src="images/design/admin/image_update.gif" alt="update image" title="update image" style="width:16px;height:16px;border:0px solid black;vertical-align:bottom;" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="ImageUpdate(\'[IMAGE_ID]\');" />
    &nbsp;&nbsp;&nbsp;&nbsp;
    <img id="mvimg_[IMAGE_ID]" src="images/design/admin/image_move.gif" alt="move image" title="move image to other section" style="width:16px;height:16px;border:0px solid black;vertical-align:bottom;" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="ImageMoveShow(\'[IMAGE_ID]\');" />
    <div id="div_[IMAGE_ID]" style="padding:3px;visibility:hidden;position:absolute;top:10px;left:10px;width:150px;height:40px;border:1px solid rgb(0,111,153);background-color:rgb(230,240,250);text-align:left;">
    <div id="divsel_[IMAGE_ID]" style="padding:2px;font-weight:bold;color:rgb(0,111,153);" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="ImageSelect(\'[IMAGE_ID]\',true);">-&gt;&nbsp;Select</div>
    <div style="padding:2px;font-weight:bold;color:rgb(0,111,153);" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="ImageMove(\'[IMAGE_ID]\');">-&gt;&nbsp;Move Selected</div>
    </div>[--IMAGE_ADMIN_FUNCTIONS:<br />]
    [--IMAGE]
    </td>';

    $html['BigImage']  = '<script type="text/javascript">
    <!--
    //-->
    </script>
    <table cellspacing="0" cellpadding="0" style="text-align:center;width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="" />
    </colgroup>
    <tr>
    <td style="padding:10px;text-align:center;vertical-align:middle;">
    <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="" />
    </colgroup>
    <tr>
    <td style="text-align:center;padding:5px;border:2px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">[IMAGE_TITLE]</td>
    </tr>
    <tr>
    <td style="text-align:center;padding:10px;border:2px solid rgb(0,111,153);background-color:rgb(114,191,236);">
    <img src="[IMAGE_SRC_BIG]" style="margin-bottom:5px;width:[IMAGE_WIDTH_BIG]px;height:[IMAGE_HEIGHT_BIG]px;border:3px solid rgb(0,111,153);" alt="[IMAGE_NAME]" title="'.$LANG->getValue("","txt","admin_009").': [IMAGE_SIZE]" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="window.close();" />
    <br /><span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">&#91;</span><span style="font-weight:normal;font-size:10px;">&nbsp;'.$LANG->getValue("","txt","admin_009").': [IMAGE_SIZE]&nbsp;</span><span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">&#93;</span>
    <br /><span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">&#91;</span><span style="font-weight:normal;font-size:10px;">&nbsp;'.$LANG->getValue("","txt","admin_010").': [IMAGE_DIMENSION]&nbsp;</span><span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">&#93;</span>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>';

    $html['BigFlashImage']  = '<script type="text/javascript">
    <!--
    //-->
    </script>
    <table cellspacing="0" cellpadding="0" style="text-align:center;width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="" />
    </colgroup>
    <tr>
    <td style="padding:10px;text-align:center;vertical-align:middle;">
    <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="" />
    </colgroup>
    <tr>
    <td style="text-align:center;padding:5px;border:2px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">[IMAGE_TITLE]</td>
    </tr>
    <tr>
    <td style="text-align:center;padding:10px;border:2px solid rgb(0,111,153);background-color:rgb(114,191,236);">
    <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="780" height="500" id="[IMAGE_NAME]" align="middle">
      <param name="allowScriptAccess" value="sameDomain" />
      <param name="movie" value="[IMAGE_SRC_BIG]" />
      <param name="menu" value="false" />
      <param name="quality" value="high" />
      <param name="bgcolor" value="#ffffff" />
      <embed src="[IMAGE_SRC_BIG]" menu="false" quality="high" bgcolor="#ffffff" width="[IMAGE_WIDTH_BIG]" height="[IMAGE_HEIGHT_BIG]" name="[IMAGE_NAME]" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
    </object>
    <br /><span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">&#91;</span><span style="font-weight:normal;font-size:10px;">&nbsp;'.$LANG->getValue("","txt","admin_009").': [IMAGE_SIZE]&nbsp;</span><span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">&#93;</span>
    <br /><span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">&#91;</span><span style="font-weight:normal;font-size:10px;">&nbsp;'.$LANG->getValue("","txt","admin_010").': [IMAGE_DIMENSION]&nbsp;</span><span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">&#93;</span>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>';

    $html['ImageTextMain']  = '<script type="text/javascript">
    <!--
    function ChangeLangBackground(obj,act)
    {
    if (act == \'over\')
    {
    obj.style.cursor = \'pointer\';
    obj.style.backgroundColor = \'rgb(114,191,236)\';
    }
    else
    {
    obj.style.cursor = \'normal\';
    obj.style.backgroundColor = \'rgb(224,240,249)\';
    }
    }
    function ChangeLanguage(pid)
    {
    var link = \'index.php?tpl=popup&tpt=page&i=\'+pid+\'&adm=0&ada=2&lan=[LANGUAGE]\';
    window.location.href = link.replace(/&amp;/gi,\'&\');
    }
    //-->
    </script>
    <form action="index.php" method="POST">
    <input type="hidden" name="tpl" value="popup">
    <input type="hidden" name="tpt" value="page">
    <input type="hidden" name="i" value="'.$postId.'">
    <input type="hidden" name="adm" value="0">
    <input type="hidden" name="ada" value="2">
    <input type="hidden" name="lan" value="[LANGUAGE]">

    <table cellspacing="5" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:*;min-width:200px;" />
    <col style="width:100px;min-width:100px;" />
    </colgroup>
    <tr>
    <td style="padding:10px;text-align:center;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:35px;min-width:35px;" />
    <col style="width:*;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(0,111,153);">
    <img src="images/design/language/[LANGUAGE_ICON]" style="width:30;height:16px;border:0px solid black;vertical-align:bottom;" />
    </td>
    <td style="padding:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(242,240,249);font-size:12px;font-weight:bold;">
    '.$LANG->getValue("","txt","admin_011").'
    </td>
    </tr>
    <tr>
    <td colspan="2" style="padding:2px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);">
    [REPLACE:ImageText]
    </td>
    </tr>
    </table>
    </td>
    <td style="padding-right:10px;padding-top:10px;padding-bottom:10px;text-align:center;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:80px;min-width:80px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(242,240,249);font-size:12px;font-weight:bold;">
    '.$LANG->getValue("","txt","admin_012").'
    </td>
    </tr>
    <tr>
    <td style="padding:2px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);">
    [REPLACE:LangChooser]
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td colspan="2" style="padding:10px;text-align:right;vertical-align:middle;">
    <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
    <input type="submit" value="'.$LANG->getValue("","txt","admin_007").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
    </td>
    </tr>
    </table></form>';

    $html['ImageTextLangChooser']  = '<table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:*;min-width:80px;" />
    </colgroup>
    [-->LANG_CHOOSER]
    <tr>
    <td id="td_[LANGUAGE_ID]" style="text-align:center;padding-bottom:5px;padding-top:5px;" onMouseOver="ChangeLangBackground(this,\'over\');" onMouseOut="ChangeLangBackground(this,\'out\');" onClick="ChangeLanguage(\'[PICTURE_ID]:[LANGUAGE]\');">
    <img src="images/design/language/[LANGUAGE_ICON]" style="width:30;height:16px;margin-bottom:3px;border:0px solid black;" />
    <br />
    <span style="font-weight:bold;color:rgb(0,111,153);font-size:10px;">&#91;</span>&nbsp;
    <span style="font-weight:normal;color:rgb(0,0,0);font-size:10px;">
    [LANGUAGE]
    </span>
    &nbsp;<span style="font-weight:bold;color:rgb(0,111,153);font-size:10px;">&#93;</span>
    </td>
    </tr>
    [--LANG_CHOOSER]
    </table>';

    $html['ImageTextFields']  = '<table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100px;min-width:10px;" />
    <col style="width:*px;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="text-align:left;vertical-align:top;font-weight:bold;padding-left:10px;padding-top:3px;">
    '.$LANG->getValue("","txt","admin_013").':
    </td>
    <td style="text-align:left;">
    <input type="hidden" name="img_id" value="[TEXT_ID]">
    <textarea name="img_short" style="width:100%;height:30px;font-family:Helvetica, sans serif;font-size:10px;font-weight:normal;color:rgb(0,111,153);background-color:rgb(245,250,255);border:1px solid rgb(114,191,236);padding:2px;">[TEXT_SHORT]</textarea>
    </td>
    </tr>
    <tr>
    <td style="text-align:left;vertical-align:top;font-weight:bold;padding-left:10px;padding-top:3px;">
    '.$LANG->getValue("","txt","admin_014").':
    </td>
    <td style="text-align:left;">
    <textarea name="img_text" style="width:100%;height:250px;font-family:Helvetica, sans serif;font-size:10px;font-weight:normal;color:rgb(0,111,153);background-color:rgb(245,250,255);border:1px solid rgb(114,191,236);padding:2px;">[TEXT_LONG]</textarea>
    </td>
    </tr>
    </table>';

    $html['ImageUpload']  = '<form action="index.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="tpl" value="popup" />
    <input type="hidden" name="tpt" value="page" />
    <input type="hidden" name="MAX_FILE_SIZE" value="52428800" />
    <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
    <input type="hidden" name="sec" value="[SECTION_ID]" />
    <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
    <input type="hidden" name="ada" value="[ADMIN_ACTION]" />

    <script type="text/javascript">
    <!--
    var FileCount = 1;
    function AddFileChooser()
    {
    FileCount++;

    var FileChooser = document.getElementById(\'FileChooser\');

    var NewBr = document.createElement(\'br\');
    var NewFile = document.createElement(\'input\');

    var NewName = document.createAttribute(\'name\');
    var NewValue = document.createAttribute(\'value\');
    var NewType = document.createAttribute(\'type\');
    var NewId = document.createAttribute(\'id\');
    var NewStyle = document.createAttribute(\'style\');

    NewName.nodeValue = \'File_\'+FileCount;
    NewValue.nodeValue = \'\';
    NewType.nodeValue = \'file\';
    NewId.nodeValue = \'file_\'+FileCount;
    NewStyle.nodeValue = \'border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);\';

    NewFile.setAttributeNode(NewName);
    NewFile.setAttributeNode(NewValue);
    NewFile.setAttributeNode(NewType);
    NewFile.setAttributeNode(NewId);
    NewFile.setAttributeNode(NewStyle);

    FileChooser.appendChild(NewBr);
    FileChooser.appendChild(NewFile);
    }
    //-->
    </script>

    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:0px;text-align:center;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;" />
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_027").'</td>
    </tr>
    <tr>
    <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:150px;min-width:150px;" />
    <col style="width:*;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
    '.$LANG->getValue("","txt","admin_028").':
    </td>
    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <div id="FileChooser"><input type="file" name="File_1" value="" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);" /></div>
    <div id="add_picture" style="width:1px;padding-top:10px;font-weight:bold;color:rgb(0,111,153);" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="AddFileChooser();">'.$LANG->getValue("","txt","admin_029").'</div>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
    <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
    <input type="submit" value="'.$LANG->getValue("","txt","admin_027").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
    </td>
    </tr>
    </table>
    </form>';

    $html['ImageUpdate']  = '<form action="index.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="tpl" value="popup" />
    <input type="hidden" name="tpt" value="page" />
    <input type="hidden" name="MAX_FILE_SIZE" value="52428800" />
    <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
    <input type="hidden" name="i" value="[PICTURE_ID]" />
    <input type="hidden" name="sec" value="[SECTION_ID]" />
    <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
    <input type="hidden" name="ada" value="[ADMIN_ACTION]" />

    <script type="text/javascript">
    <!--
    //-->
    </script>

    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:0px;text-align:center;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;" />
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_034").'</td>
    </tr>
    <tr>
    <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:150px;min-width:150px;" />
    <col style="width:*;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
    '.$LANG->getValue("","txt","admin_028").':
    </td>
    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <div id="FileChooser"><input type="file" name="File_1" value="" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);" /></div>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
    <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
    <input type="submit" value="'.$LANG->getValue("","txt","admin_035").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
    </td>
    </tr>
    </table>
    </form>';

    $html['ImageUploadMsg']  = '<script type="text/javascript">
    <!--
    opener.location.reload();
    window.close();
    //-->
    </script>

    Close this Window if it\'s not closed automaticly...';


    $html['ImageDelete']  = '<form action="index.php" method="POST">
    <input type="hidden" name="tpl" value="popup" />
    <input type="hidden" name="tpt" value="page" />
    <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
    <input type="hidden" name="img_did" value="[IMAGE_DELETE]" />
    <input type="hidden" name="i" value="[POST_ID]" />
    <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
    <input type="hidden" name="ada" value="[ADMIN_ACTION]" />

    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:0px;text-align:center;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;" />
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_030").'</td>
    </tr>
    <tr>
    <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:*;min-width:150px;" />
    <col style="width:*;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
    '.$LANG->getValue("","txt","admin_031").':
    </td>
    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <span style="font-weight:bold;color:rgb(255,100,100);"><img src="[IMAGE_SRC_SMALL]" style="width:[IMAGE_WIDTH_SMALL]px;height:[IMAGE_HEIGHT_SMALL];" /></span>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
    <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
    <input type="submit" value="'.$LANG->getValue("","txt","admin_030").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
    </td>
    </tr>
    </table>
    </form>';

    $html['ImageDeleteComplete']  = '<script type="text/javascript">
    <!--
    opener.location.reload();
    window.close();
    //-->
    </script>

    Close this Window if it\'s not closed automaticly...';


    $html['ImageMove']  = '<form action="index.php" method="POST">
    <input type="hidden" name="tpl" value="popup" />
    <input type="hidden" name="tpt" value="page" />
    <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
    <input type="hidden" name="img_imv" value="[IMAGE_MOVE]" />
    <input type="hidden" name="sec" value="[SECTION_ID]" />
    <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
    <input type="hidden" name="ada" value="[ADMIN_ACTION]" />

    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:0px;text-align:center;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;" />
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_033").'</td>
    </tr>
    <tr>
    <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:*;min-width:150px;" />
    <col style="width:350px;min-width:350px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
    '.$LANG->getValue("","txt","admin_032").':
    </td>
    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);border-left-width:1px;background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    [SECTION_CHOOSER]
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
    <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
    <input type="submit" value="'.$LANG->getValue("","txt","admin_033").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
    </td>
    </tr>
    </table>
    </form>';
    return $html;
  }

  function ProgramManagement()
  {
    global $LANG,$postId;
    $html['ProgramList']  = '
    <script type="text/javascript">
    <!--

    var IE = document.all?true:false;
    if (!IE) document.captureEvents(Event.MOUSEDOWN);
    document.onmousedown = GetMousePos;

    var MousePosX = 0;
    var MousePosY = 0;

    function ChangeImageBackground(Iobj,Tobj,act)
    {
      if (act == \'over\')
      {
        Iobj.style.border = \'3px solid rgb(0,111,153)\';
        document.getElementById(Tobj).style.backgroundColor = \'rgb(114,191,236)\';
        document.getElementById(Tobj).style.border = \'1px solid rgb(0,111,153)\';
        document.getElementById(Tobj).style.cursor = \'pointer\';
      }
      else
      {
        Iobj.style.border = \'3px solid rgb(114,191,236)\';
        document.getElementById(Tobj).style.backgroundColor = \'rgb(224,240,249)\';
        document.getElementById(Tobj).style.border = \'1px solid rgb(224,240,249)\';
        document.getElementById(Tobj).style.cursor = \'normal\';
      }
    }

    function ShowDetail(id)
    {
      imgW = 400;
      imgH = 300;
      var prop = \'width=\'+imgW+\',height=\'+imgH+\',top=\'+((screen.height-imgH-50)/2)+\',left=\'+((screen.width-imgW)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
      var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=1&i=\'+id;
      var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }

    function ShowMirror(id)
    {
      imgW = 650;
      imgH = 350;
      var prop = \'width=\'+imgW+\',height=\'+imgH+\',top=\'+((screen.height-imgH-50)/2)+\',left=\'+((screen.width-imgW)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
      var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=11&i=\'+id;
      var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }

    function ProgramText(id)
    {
      var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
      var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=2&i=\'+id;
      var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }

    function ProgramDelete(id)
    {
      var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
      var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=3&i=\'+id;
      var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }

    function ProgramUpdate(id)
    {
      var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
      var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=4&i=\'+id;
      var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }

    var ImgMv = new Array();
    function ProgramSelect(id,rmsel)
    {
      var idfound = false;
      var tmparr = new Array();
      for (i = 0; i < ImgMv.length; i++)
      {
        if (ImgMv[i] == id)
          idfound = true;
        if (((ImgMv[i] != id)) && (ImgMv[i] != \'\') && (ImgMv[i] != \'0\'))
          var tmp = tmparr.push(ImgMv[i]);
      }
      if (!idfound)
      {
        document.getElementById(\'divsel_\'+id).style.color = \'rgb(255,50,50)\';
        document.getElementById(\'mvimg_\'+id).src = \'images/design/admin/image_move_selected.gif\';
      }
      else
      {
        document.getElementById(\'divsel_\'+id).style.color = \'rgb(0,111,153)\';
        document.getElementById(\'mvimg_\'+id).src = \'images/design/admin/image_move.gif\';
      }
      ImgMv = tmparr;
      if ((!idfound) || (!rmsel))
        var tmp = ImgMv.push(id);

      document.getElementById(\'div_\'+id).style.visibility = \'hidden\';
    }

    function ProgramMove(id)
    {
      ProgramSelect(id,false);
      var ImgId = ImgMv.join(\',\');
      var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
      var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=5&i=\'+ImgId;
      var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }

    function ProgramMoveShow(id)
    {
      var opt = document.getElementById(\'div_\'+id);
      if (opt.style.visibility == \'hidden\')
      {
        opt.style.top = (MousePosY+2)+\'px\';
        if ((MousePosX+2) > (document.getElementsByTagName(\'body\')[0].offsetWidth-150))
          opt.style.left = (MousePosX+2-150)+\'px\';
        else
          opt.style.left = (MousePosX+2)+\'px\';
        opt.style.visibility = \'visible\';
      }
      else
      {
        opt.style.visibility = \'hidden\';
      }
    }

    function GetMousePos(e)
    {
      if (IE)
      {
        MousePosX = event.clientX + document.body.scrollLeft;
        MousePosY = event.clientY + document.body.scrollTop
      }
      else
      {
        MousePosX = e.pageX
        MousePosY = e.pageY
      }
      if (MousePosX < 0) MousePosX = 0;
      if (MousePosY < 0) MousePosY = 0;

      return true;
    }

    //-->
    </script>
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
      <coolgroup>
        <col style="min-width:50px;width:*;" />
        <col style="min-width:50px;width:*;" />
        <col style="min-width:50px;width:*;" />
      </colgroup>
      [-->PROGRAM_ENTRY]
      <tr>
        [PROGRAM]
        [PROGRAM]
        [PROGRAM]
      </tr>
      <tr>
        <td colspan="3" style="height:15px;"></td>
      </tr>
      [--PROGRAM_ENTRY]
    </table>';

    $html['ProgramEntry']  = '
    <td id="td_[PROGRAM_ID]" style="padding:3px;background-color:rgb(224,240,249);border:1px solid rgb(224,240,249);text-align:center;vertical-align:top;">
      [-->PROGRAM]
      <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;border:3px solid rgb(114,191,236);" onMouseOver="ChangeImageBackground(this,\'td_[PROGRAM_ID]\',\'over\');" onMouseOut="ChangeImageBackground(this,\'td_[PROGRAM_ID]\',\'out\');" onClick="ShowDetail(\'[PROGRAM_ID]\');">
        <colgroup>
          <col style="min-width:36px;width:36px;" />
          <col style="min-width:80px;" />
        </colgroup>
        <tr>
          <td colspan="2" style="padding:5px;border:0px solid black;border-bottom:1px solid rgb(114,191,236);background-color:rgb(245,250,255);text-align:center;vertical-align:middle;">
            <span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">&#91;</span><span style="font-weight:normal;font-size:10px;">&nbsp;[PROGRAM_NAME]&nbsp;</span><span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">&#93;</span>
          </td>
        </tr>
        <tr>
          <td style="padding:5px;width:36px;border:0px solid black;background-color:rgb(245,250,255);text-align:left;vertical-align:top;">
            <img src="[IMAGE_SRC]" style="width:[IMAGE_WIDTH]px;height:[IMAGE_HEIGHT]px;border:0px solid rgb(114,191,236);" alt="[FILE_TYPE]" title="[PROGRAM_NAME]" />
          </td>
          <td style="border:0px solid black;background-color:rgb(245,250,255);text-align:left;padding:5px;vertical-align:top;">
            [PROGRAM_NAME]<br />
            [PROGRAM_SIZE]<br />
            [PROGRAM_DATE]
          </td>
        </tr>
        <tr>
          <td colspan="2" style="height:56px;text-align:left;vertical-align:top;padding:10px;padding-top:5px;border:0px solid black;background-color:rgb(245,250,255);">
            <span style="font-weight:bold;">[PROGRAM_DESC]</span>
          </td>
        </tr>
      </table>
      <br />
      <img src="images/design/admin/dload_mirror.gif" alt="mirror" title="define and change mirrors" style="width:16px;height:16px;border:0px solid black;vertical-align:bottom;" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="ShowMirror(\'[PROGRAM_ID]\');" />
      &nbsp;&nbsp;&nbsp;&nbsp;
      <img src="images/design/admin/image_edit.gif" alt="edit texts" title="edit file texts" style="width:16px;height:16px;border:0px solid black;vertical-align:bottom;" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="ProgramText(\'[PROGRAM_ID]\');" />
      &nbsp;&nbsp;&nbsp;&nbsp;
      <img src="images/design/admin/image_delete.gif" alt"delete file" title="delete file" style="width:16px;height:16px;border:0px solid black;vertical-align:bottom;" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="ProgramDelete(\'[PROGRAM_ID]\');" />
      &nbsp;&nbsp;&nbsp;&nbsp;
      <img src="images/design/admin/image_update.gif" alt="update file" title="update file" style="width:16px;height:16px;border:0px solid black;vertical-align:bottom;" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="ProgramUpdate(\'[PROGRAM_ID]\');" />
      &nbsp;&nbsp;&nbsp;&nbsp;
      <img id="mvimg_[PROGRAM_ID]" src="images/design/admin/image_move.gif" alt="move file" title="move file to other section" style="width:16px;height:16px;border:0px solid black;vertical-align:bottom;" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="ProgramMoveShow(\'[PROGRAM_ID]\');" />
      <div id="div_[PROGRAM_ID]" style="padding:3px;visibility:hidden;position:absolute;top:10px;left:10px;width:150px;height:40px;border:1px solid rgb(0,111,153);background-color:rgb(230,240,250);text-align:left;">
        <div id="divsel_[PROGRAM_ID]" style="padding:2px;font-weight:bold;color:rgb(0,111,153);" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="ProgramSelect(\'[PROGRAM_ID]\',true);">-&gt;&nbsp;Select</div>
        <div style="padding:2px;font-weight:bold;color:rgb(0,111,153);" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="ProgramMove(\'[PROGRAM_ID]\');">-&gt;&nbsp;Move Selected</div>
      </div>
      [--PROGRAM]
    </td>
    ';

    $html['ProgramDetail'] = '
    <script type="text/javascript">
    <!--
      function setDownReg(prg, state)
      {
        var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=1&i=\'+prg+\'&act=\'+state;
        window.location.href = link.replace(/&amp;/gi,\'&\');
      }
    //-->
    </script>
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
      <coolgroup>
        <col style="min-width:50px;" />
      </colgroup>
      [-->PROGRAM_ENTRY]
      <tr>
        [PROGRAM]
      </tr>
      <tr>
        <td style="height:15px;">&#91;&nbsp; <a href="javascript:window.close();">'.$LANG->getValue('','txt','input_003').'</a> &nbsp;&#93;</td>
      </tr>
      [--PROGRAM_ENTRY]
    </table>
    ';

    $html['ProgramDetailEntry']  = '
    <td id="td_[PROGRAM_ID]" style="padding:3px;background-color:rgb(224,240,249);border:1px solid rgb(224,240,249);text-align:center;vertical-align:top;">
      [-->PROGRAM]
      <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;border:3px solid rgb(114,191,236);" onMouseOver="ChangeImageBackground(this,\'td_[PROGRAM_ID]\',\'over\');" onMouseOut="ChangeImageBackground(this,\'td_[PROGRAM_ID]\',\'out\');" onClick="ShowDetail(\'[PROGRAM_ID]\');">
        <colgroup>
          <col style="min-width:36px;width:36px;" />
          <col style="min-width:80px;" />
        </colgroup>
        <tr>
          <td colspan="2" style="padding:5px;border:0px solid black;border-bottom:1px solid rgb(114,191,236);background-color:rgb(245,250,255);text-align:center;vertical-align:middle;">
            <span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">&#91;</span><span style="font-weight:normal;font-size:10px;">&nbsp;[PROGRAM_NAME]&nbsp;</span><span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">&#93;</span>
          </td>
        </tr>
        <tr>
          <td style="padding:5px;width:36px;border:0px solid black;background-color:rgb(245,250,255);text-align:left;vertical-align:top;">
            <img src="[IMAGE_SRC]" style="width:[IMAGE_WIDTH]px;height:[IMAGE_HEIGHT]px;border:0px solid rgb(114,191,236);" alt="[FILE_TYPE]" title="[PROGRAM_NAME]" />
          </td>
          <td style="border:0px solid black;background-color:rgb(245,250,255);text-align:left;padding:5px;vertical-align:top;">
            [PROGRAM_NAME]<br />
            [PROGRAM_SIZE]<br />
            [PROGRAM_DATE]
          </td>
        </tr>
        <tr>
          <td colspan="2" style="height:56px;text-align:left;vertical-align:top;padding:10px;padding-top:5px;border:0px solid black;background-color:rgb(245,250,255);">
            <span style="font-weight:bold;">[PROGRAM_DESC]</span>
          </td>
        </tr>

        <tr>
          <td colspan="2" style="text-decoration:underline;font-weight:bold;font-size:9pt;padding:10px;padding-top:5px;border-top:2px solid rgb(114,191,236);background-color:rgb(245,250,255);">
            '.$LANG->getValue("", "txt", "admin_205").'
          </td>
        </tr>
        <tr>
          <td colspan="2" style="text-align:left;padding:10px;padding-top:0px;background-color:rgb(245,250,255);">
            <input type="button" value="[REG_DOWNLOAD_STATE_TEXT]" onClick="setDownReg(\'[PROGRAM_ID]\', \'[REG_DOWNLOAD_STATE]\');" style="margin-right:10px;" />'.$LANG->getValue("", "txt", "admin_206").'
          </td>
        </tr>

      </table>
      [--PROGRAM]
    </td>
    ';

    $html['ProgramTextMain']  = '
    <script type="text/javascript">
    <!--
    function ChangeLangBackground(obj,act)
    {
      if (act == \'over\')
      {
        obj.style.cursor = \'pointer\';
        obj.style.backgroundColor = \'rgb(114,191,236)\';
      }
      else
      {
        obj.style.cursor = \'normal\';
        obj.style.backgroundColor = \'rgb(224,240,249)\';
      }
    }

    function ChangeLanguage(pid)
    {
      var link = \'index.php?tpl=popup&tpt=page&i=\'+pid+\'&adm=1&ada=2&lan=[LANGUAGE]\';
      window.location.href = link.replace(/&amp;/gi,\'&\');
    }
    //-->
    </script>

    <form action="index.php" method="POST">
      <input type="hidden" name="tpl" value="popup">
      <input type="hidden" name="tpt" value="page">
      <input type="hidden" name="i" value="'.$postId.'">
      <input type="hidden" name="adm" value="1">
      <input type="hidden" name="ada" value="2">
      <input type="hidden" name="lan" value="[LANGUAGE]">

      <table cellspacing="5" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
        <colgroup>
          <col style="width:*;min-width:200px;" />
          <col style="width:100px;min-width:100px;" />
        </colgroup>
        <tr>
          <td style="padding:10px;text-align:center;vertical-align:top;">
            <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
              <colgroup>
                <col style="width:35px;min-width:35px;" />
                <col style="width:*;min-width:200px;" />
              </colgroup>
              <tr>
                <td style="text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(0,111,153);">
                  <img src="images/design/language/[LANGUAGE_ICON]" style="width:30;height:16px;border:0px solid black;vertical-align:bottom;" />
                </td>
                <td style="padding:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(242,240,249);font-size:12px;font-weight:bold;">
                  '.$LANG->getValue("","txt","admin_039").'
                </td>
              </tr>
              <tr>
                <td colspan="2" style="padding:2px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);">
                  [REPLACE:ProgramText]
                </td>
              </tr>
            </table>
          </td>
          <td style="padding-right:10px;padding-top:10px;padding-bottom:10px;text-align:center;vertical-align:top;">
            <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
              <colgroup>
                <col style="width:80px;min-width:80px;" />
              </colgroup>
              <tr>
                <td style="padding:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(242,240,249);font-size:12px;font-weight:bold;">
                  '.$LANG->getValue("","txt","admin_012").'
                </td>
              </tr>
              <tr>
                <td style="padding:2px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);">
                  [REPLACE:LangChooser]
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding:10px;text-align:right;vertical-align:middle;">
            <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
            <input type="submit" value="'.$LANG->getValue("","txt","admin_007").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
          </td>
        </tr>
      </table>
    </form>
    ';

    $html['ProgramTextLangChooser']  = '
    <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
      <colgroup>
        <col style="width:*;min-width:80px;" />
      </colgroup>
      [-->LANG_CHOOSER]
      <tr>
        <td id="td_[LANGUAGE_ID]" style="text-align:center;padding-bottom:5px;padding-top:5px;" onMouseOver="ChangeLangBackground(this,\'over\');" onMouseOut="ChangeLangBackground(this,\'out\');" onClick="ChangeLanguage(\'[PROGRAM_ID]:[LANGUAGE]\');">
          <img src="images/design/language/[LANGUAGE_ICON]" style="width:30;height:16px;margin-bottom:3px;border:0px solid black;" />
          <br />
          <span style="font-weight:bold;color:rgb(0,111,153);font-size:10px;">&#91;</span>&nbsp;
          <span style="font-weight:normal;color:rgb(0,0,0);font-size:10px;">[LANGUAGE]</span>&nbsp;
          <span style="font-weight:bold;color:rgb(0,111,153);font-size:10px;">&#93;</span>
        </td>
      </tr>
      [--LANG_CHOOSER]
    </table>
    ';

    $html['ProgramTextFields']  = '
    <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
      <colgroup>
        <col style="width:100px;min-width:10px;" />
        <col style="width:*px;min-width:200px;" />
      </colgroup>
      <tr>
        <td style="text-align:left;vertical-align:top;font-weight:bold;padding-left:10px;padding-top:3px;">
          '.$LANG->getValue("","txt","admin_013").':
        </td>
        <td style="text-align:left;">
          <input type="hidden" name="prg_id" value="[TEXT_ID]">
          <textarea name="prg_short" style="width:100%;height:30px;font-family:Helvetica, sans serif;font-size:10px;font-weight:normal;color:rgb(0,111,153);background-color:rgb(245,250,255);border:1px solid rgb(114,191,236);padding:2px;">[TEXT_SHORT]</textarea>
        </td>
      </tr>
      <tr>
        <td style="text-align:left;vertical-align:top;font-weight:bold;padding-left:10px;padding-top:3px;">
          '.$LANG->getValue("","txt","admin_014").':
        </td>
        <td style="text-align:left;">
          <textarea name="prg_text" style="width:100%;height:250px;font-family:Helvetica, sans serif;font-size:10px;font-weight:normal;color:rgb(0,111,153);background-color:rgb(245,250,255);border:1px solid rgb(114,191,236);padding:2px;">[TEXT_LONG]</textarea>
        </td>
      </tr>
    </table>
    ';

    $html['ProgramUpload']  = '
    <form action="index.php" method="POST">
      <input type="hidden" name="tpl" value="popup" />
      <input type="hidden" name="tpt" value="page" />
      <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
      <input type="hidden" name="sec" value="[SECTION_ID]" />
      <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
      <input type="hidden" name="ada" value="[ADMIN_ACTION]" />

      <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
        <colgroup>
          <col style="width:100%;min-width:200px;" />
        </colgroup>
        <tr>
          <td style="padding:0px;text-align:center;vertical-align:top;">
            <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
              <colgroup>
                <col style="width:100%;" />
              </colgroup>
              <tr>
                <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_050").'</td>
              </tr>
              <tr>
                <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                  <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
                    <colgroup>
                      <col style="width:150px;min-width:150px;" />
                      <col style="width:*;min-width:200px;" />
                      <col style="width:180px;min-width:180px;" />
                    </colgroup>
                    <tr>
                      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
                        '.$LANG->getValue("","txt","admin_040").':
                      </td>
                      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        <select name="FTP_File" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);width:150px;">
                        [-->FTP_FILE_CHOOSER]<option value="[FTP_FILEPATH]">[FTP_FILENAME]</option> [--FTP_FILE_CHOOSER]
                        </select>
                      </td>
                      <td style="padding:2px;text-align:right;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);">
                        <input type="submit" value="'.$LANG->getValue("","txt","admin_049").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding:5px;text-align:right;vertical-align:middle;"></td>
        </tr>
      </table>
    </form>

    <form action="index.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="tpl" value="popup" />
      <input type="hidden" name="tpt" value="page" />
      <input type="hidden" name="MAX_FILE_SIZE" value="52428800" />
      <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
      <input type="hidden" name="sec" value="[SECTION_ID]" />
      <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
      <input type="hidden" name="ada" value="[ADMIN_ACTION]" />

      <script type="text/javascript">
      <!--
        var FileCount = 1;
        function AddFileChooser()
        {
          FileCount++;

          var FileChooser = document.getElementById(\'FileChooser\');

          var NewBr = document.createElement(\'br");
          var NewFile = document.createElement(\'input");

          var NewName = document.createAttribute(\'name\');
          var NewValue = document.createAttribute(\'value\');
          var NewType = document.createAttribute(\'type\');
          var NewId = document.createAttribute(\'id\');
          var NewStyle = document.createAttribute(\'style\');

          NewName.nodeValue = \'File_\'+FileCount;
          NewValue.nodeValue = \'\';
          NewType.nodeValue = \'file\';
          NewId.nodeValue = \'file_\'+FileCount;
          NewStyle.nodeValue = \'border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);\';

          NewFile.setAttributeNode(NewName);
          NewFile.setAttributeNode(NewValue);
          NewFile.setAttributeNode(NewType);
          NewFile.setAttributeNode(NewId);
          NewFile.setAttributeNode(NewStyle);

          FileChooser.appendChild(NewBr);
          FileChooser.appendChild(NewFile);
        }
      //-->
      </script>

      <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
        <colgroup>
          <col style="width:100%;min-width:200px;" />
        </colgroup>
        <tr>
          <td style="padding:0px;text-align:center;vertical-align:top;">
            <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
              <colgroup>
                <col style="width:100%;" />
              </colgroup>
              <tr>
                <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_041").'</td>
              </tr>
              <tr>
                <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                  <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
                    <colgroup>
                      <col style="width:150px;min-width:150px;" />
                      <col style="width:*;min-width:200px;" />
                    </colgroup>
                    <tr>
                      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
                        '.$LANG->getValue("","txt","admin_040").':
                      </td>
                      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        <div id="FileChooser"><input type="file" name="File_1" value="" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);" /></div>
                        <div id="add_picture" style="width:1px;padding-top:10px;font-weight:bold;color:rgb(0,111,153);" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="AddFileChooser();">'.$LANG->getValue("","txt","admin_029").'</div>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
            <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
            <input type="submit" value="'.$LANG->getValue("","txt","admin_047").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
          </td>
        </tr>
      </table>
    </form>';

    $html['ProgramUpdate']  = '
    <form action="index.php" method="POST">
      <input type="hidden" name="tpl" value="popup" />
      <input type="hidden" name="tpt" value="page" />
      <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
      <input type="hidden" name="i" value="[PRODUCT_ID]" />
      <input type="hidden" name="sec" value="[SECTION_ID]" />
      <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
      <input type="hidden" name="ada" value="[ADMIN_ACTION]" />

      <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
        <colgroup>
          <col style="width:100%;min-width:200px;" />
        </colgroup>
        <tr>
          <td style="padding:0px;text-align:center;vertical-align:top;">
            <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
              <colgroup>
                <col style="width:100%;" />
              </colgroup>
              <tr>
                <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_050").'</td>
              </tr>
              <tr>
                <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                  <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
                    <colgroup>
                      <col style="width:150px;min-width:150px;" />
                      <col style="width:*;min-width:200px;" />
                      <col style="width:180px;min-width:180px;" />
                    </colgroup>
                    <tr>
                      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
                        '.$LANG->getValue("","txt","admin_040").':
                      </td>
                      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        <select name="FTP_File" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);width:150px;">
                        [-->FTP_FILE_CHOOSER]<option value="[FTP_FILEPATH]">[FTP_FILENAME]</option>
                        [--FTP_FILE_CHOOSER]
                        </select>
                      </td>
                      <td style="padding:2px;text-align:right;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);">
                        <input type="submit" value="'.$LANG->getValue("","txt","admin_049").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding:5px;"></td>
        </tr>
      </table>
    </form>

    <form action="index.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="tpl" value="popup" />
      <input type="hidden" name="tpt" value="page" />
      <input type="hidden" name="MAX_FILE_SIZE" value="52428800" />
      <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
      <input type="hidden" name="i" value="[PRODUCT_ID]" />
      <input type="hidden" name="sec" value="[SECTION_ID]" />
      <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
      <input type="hidden" name="ada" value="[ADMIN_ACTION]" />

      <script type="text/javascript">
      <!--
      //-->
      </script>

      <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
        <colgroup>
          <col style="width:100%;min-width:200px;" />
        </colgroup>
        <tr>
          <td style="padding:0px;text-align:center;vertical-align:top;">
            <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
              <colgroup>
                <col style="width:100%;" />
              </colgroup>
              <tr>
                <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_042").'</td>
              </tr>
              <tr>
                <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                  <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
                    <colgroup>
                      <col style="width:150px;min-width:150px;" />
                      <col style="width:*;min-width:200px;" />
                    </colgroup>
                    <tr>
                      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
                        '.$LANG->getValue("","txt","admin_040").':
                      </td>
                      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        <div id="FileChooser"><input type="file" name="File_1" value="" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);" /></div>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
            <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
            <input type="submit" value="'.$LANG->getValue("","txt","admin_048").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
          </td>
        </tr>
      </table>
    </form>
    ';

    $html['ProgramUploadMsg']  = '
    <script type="text/javascript">
    <!--
      opener.location.reload();
      window.close();
    //-->
    </script>

    Close this Window if it"s not closed automaticly...';


    $html['ProgramDelete']  = '
    <form action="index.php" method="POST">
      <input type="hidden" name="tpl" value="popup" />
      <input type="hidden" name="tpt" value="page" />
      <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
      <input type="hidden" name="prg_did" value="[PROGRAM_DELETE]" />
      <input type="hidden" name="i" value="[POST_ID]" />
      <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
      <input type="hidden" name="ada" value="[ADMIN_ACTION]" />

      <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
        <colgroup>
          <col style="width:100%;min-width:200px;" />
        </colgroup>
        <tr>
          <td style="padding:0px;text-align:center;vertical-align:top;">
            <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
              <colgroup>
                <col style="width:100%;" />
              </colgroup>
              <tr>
                <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_045").'</td>
              </tr>
              <tr>
                <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                  <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
                    <colgroup>
                      <col style="width:*;min-width:150px;" />
                      <col style="width:*;min-width:200px;" />
                    </colgroup>
                    <tr>
                      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
                        '.$LANG->getValue("","txt","admin_046").':
                      </td>
                        <td Style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        <span style="font-weight:bold;color:rgb(255,100,100);"><img src="[IMAGE_SRC_SMALL]" style="width:[IMAGE_WIDTH_SMALL]px;height:[IMAGE_HEIGHT_SMALL];" /></span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
            <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
            <input type="submit" value="'.$LANG->getValue("","txt","admin_045").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
          </td>
        </tr>
      </table>
    </form>
    ';

    $html['ProgramDeleteComplete']  = '
    <script type="text/javascript">
    <!--
      opener.location.reload();
      window.close();
    //-->
    </script>

    Close this Window if it does not automaticly...';


    $html['ProgramMove']  = '
    <form action="index.php" method="POST">
      <input type="hidden" name="tpl" value="popup" />
      <input type="hidden" name="tpt" value="page" />
      <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
      <input type="hidden" name="prg_pmv" value="[PROGRAM_MOVE]" />
      <input type="hidden" name="sec" value="[SECTION_ID]" />
      <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
      <input type="hidden" name="ada" value="[ADMIN_ACTION]" />

      <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
        <colgroup>
        <col style="width:100%;min-width:200px;" />
        </colgroup>
        <tr>
          <td style="padding:0px;text-align:center;vertical-align:top;">
            <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
              <colgroup>
                <col style="width:100%;" />
              </colgroup>
              <tr>
                <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_043").'</td>
              </tr>
              <tr>
                <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                  <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
                    <colgroup>
                      <col style="width:*;min-width:150px;" />
                      <col style="width:350px;min-width:350px;" />
                    </colgroup>
                    <tr>
                      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
                        '.$LANG->getValue("","txt","admin_044").':
                      </td>
                      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);border-left-width:1px;background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        [SECTION_CHOOSER]
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
            <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
            <input type="submit" value="'.$LANG->getValue("","txt","admin_043").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
          </td>
        </tr>
      </table>
    </form>
    ';

    $html['ProgramMirror']  = '
    <script type="text/javascript">
      <!--
        function ChangeCursor(obj, act)
        {
          if (act == \'over\')
            obj.style.cursor = \'pointer\';
          else
            obj.style.cursor = \'default\';
        }

        function ChangeMirror(id)
        {
          if (id == 0)
            var chid = 12;
          else
            var chid = 13;
          var loc = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=\' + chid + \'&i=[POST_ID]&chi=\'+id;
          window.location.href = loc.replace(/&amp;/gi, \'&\', loc);
        }

        function DeleteMirror(id,mirr)
        {
          if (confirm(\''.$LANG->getValue("", "txt", "admin_192").'\n\n\t- \' + mirr + \'\'))
          {
            var loc = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=14&i=[POST_ID]&chi=\'+id;
            window.location.href = loc.replace(/&amp;/gi, \'&\', loc);
          }
        }
      //-->
    </script>

    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
      <colgroup>
      <col style="width:100%;min-width:200px;" />
      </colgroup>
      <tr>
        <td style="padding:0px;text-align:center;vertical-align:top;">
          <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
            <colgroup>
              <col style="width:100%;" />
            </colgroup>
            <tr>
              <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">
                '.$LANG->getValue("","txt","admin_191").'
              </td>
            </tr>
            <tr>
              <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
                  <colgroup>
                    <col style="width:50px;min-width:50px;" />
                    <col style="min-width:150px;" />
                    <col style="width:150px;min-width:150px;" />
                    <col style="width:50px;min-width:50px;" />
                  </colgroup>
                  [-->PROGRAM_MIRROR]
                  <tr>
                    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                      <img src="[MIRROR_ICON]" style="width:32px;height:16px;border:0px solid black;" alt="type" title="Mirror-Type" />
                    </td>
                    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);border-left-width:1px;color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                      [PROG_URL]
                    </td>
                    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);border-left-width:1px;color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                      [PROG_UPDATE]
                    </td>
                    <td style="padding:2px;text-align:left;vertical-align:middle;text-align:center;border:0px solid rgb(0,111,153);border-left-width:1px;color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                      <img src="images/design/admin/edit_small.gif" style="width:13px;height:16px;border:0px solid black;" alt="edit" title="Change this Mirror" onClick="ChangeMirror(\'[MIRROR_ID]\');" onMouseOver="ChangeCursor(this, \'over\');" onMouseOut="ChangeCursor(this, \'out\');" />
                      &nbsp;
                      <img src="images/design/admin/delete_small.gif" style="width:16px;height:16px;border:0px solid black;" alt="remove" title="Remove this Mirror" onClick="DeleteMirror(\'[MIRROR_ID]\', \'[PROG_URL]\');" onMouseOver="ChangeCursor(this, \'over\');" onMouseOut="ChangeCursor(this, \'out\');" />
                    </td>
                  </tr>
                  [--PROGRAM_MIRROR]
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
          <input type="button" value="'.$LANG->getValue("","txt","admin_154").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="ChangeMirror(\'0\')" />&nbsp;&nbsp;
          <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
        </td>
      </tr>
    </table>
    ';

    $html['ChangeMirror']  = '
    <script type="text/javascript">
      <!--
        function ChangeCursor(obj, act)
        {
          if (act == \'over\')
            obj.style.cursor = \'pointer\';
          else
            obj.style.cursor = \'default\';
        }

        function ChangeMirror(id)
        {
          var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=12&i=[PROG_ID]&chi=\'+id;
          window.location.href = link.replace(/&amp;/gi,\'&\');
        }
      //-->
    </script>

    <form action="index.php" method="POST">
      <input type="hidden" name="tpl" value="popup" />
      <input type="hidden" name="tpt" value="page" />
      <input type="hidden" name="sec" value="[SECTION_ID]" />
      <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
      <input type="hidden" name="ada" value="15" />
      <input type="hidden" name="i" value="[POST_ID]" />
      <input type="hidden" name="chi" value="[CHANGE_ID]" />
      <input type="hidden" name="cse" value="[IS_UPDATE]" />

      <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
        <colgroup>
        <col style="width:100%;min-width:200px;" />
        </colgroup>
        <tr>
          <td style="padding:0px;text-align:center;vertical-align:top;">
            <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
              <colgroup>
                <col style="width:100%;" />
              </colgroup>
              <tr>
                <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">
                  '.$LANG->getValue("","txt","admin_191").'
                </td>
              </tr>
              <tr>
                <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                  <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
                    <colgroup>
                      <col style="width:150px;min-width:150px;" />
                      <col style="min-width:150px;" />
                    </colgroup>

                    <tr>
                      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        '.$LANG->getValue("", "txt", "admin_193").'
                      </td>
                      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);border-left-width:1px;color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        <input type="text" name="mir_url" value="[MIRR_URL]" style="border:1px solid rgb(0,111,153);padding:1px;width:400px;" />
                      </td>
                    </tr>

                    <tr>
                      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        '.$LANG->getValue("", "txt", "admin_195").'
                      </td>
                      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);border-left-width:1px;color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        <input type="radio" name="mir_type" value="ftp" [CHECK_FTP] />'.$LANG->getValue("", "txt", "admin_198").'
                        &nbsp;&nbsp;
                        <input type="radio" name="mir_type" value="http" [CHECK_HTTP] />'.$LANG->getValue("", "txt", "admin_199").'
                      </td>
                    </tr>

                    <tr>
                      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        '.$LANG->getValue("", "txt", "admin_196").'
                      </td>
                      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);border-left-width:1px;color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        <input type="text" name="mir_user" value="[MIRR_USER]" style="border:1px solid rgb(0,111,153);padding:1px;width:400px;" />
                      </td>
                    </tr>

                    <tr>
                      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        '.$LANG->getValue("", "txt", "admin_197").'
                      </td>
                      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);border-left-width:1px;color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        <input type="text" name="mir_pass" value="[MIRR_PASS]" style="border:1px solid rgb(0,111,153);padding:1px;width:400px;" />
                      </td>
                    </tr>

                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
            <input type="submit" value="'.$LANG->getValue("","txt","admin_194").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />&nbsp;&nbsp;
          </td>
        </tr>
      </table>
    </form>
    ';

    $html['ProgramMirrorRefresh']  = '
    <script type="text/javascript">
    <!--
      var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=11&i=[POST_ID]\';
      window.location.href = link.replace(/&amp;/gi,\'&\');
    //-->
    </script>';

    return $html;
  }

  function LanguageManagement()
  {
    global $LANG,$postId;

    $html['MainScreen']  = '
    <table cellspacing="0" cellpadding="0" style="table-layout:fixed;empty-cells:show;width:100%;">
    <colgroup>
    <col style="width:100px;min-width:100px;" />
    <col style="width:50px;min-width:50px;" />
    <col style="width:80px;min-width:80px;" />
    <col style="min-width:150px;" />
    </colgroup>
    [LANGUAGE_LIST]
    </table>';

    $html['ButtonBar']  = '
    <script type="text/javascript">
    <!--
    function ChangeBackground(obj,act)
    {
    if (act == \'over\')
    {
    obj.style.cursor = \'pointer\';
    obj.style.border = \'1px solid rgb(0,111,153)\';
    obj.style.backgroundColor = \'rgb(114,191,236)\';
    }
    else
    {
    obj.style.cursor = \'normal\';
    obj.style.border = \'1px solid rgb(224,240,249)\';
    obj.style.backgroundColor = \'rgb(224,240,249)\';
    }
    }
    function ChangeLocation(obj,act)
    {
    obj.style.cursor = \'pointer\';
    obj.style.border = \'1px solid rgb(0,111,153)\';
    obj.style.backgroundColor = \'rgb(114,191,236)\';
    if (act == \'add\')
    {
    var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
    var link = \'index.php?tpl=popup&tpt=page&adm=[ADMIN_SECTION]&ada=2\';
    var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }
    else if (act == \'img\')
    {
    var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
    var link = \'index.php?tpl=popup&tpt=page&adm=[ADMIN_SECTION]&ada=4\';
    var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }
    else if (act == \'ide\')
    {
    var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
    var link = \'index.php?tpl=popup&tpt=page&adm=[ADMIN_SECTION]&ada=5\';
    var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }
    }
    function OpenLangWindow(id,act)
    {
    if (act == \'del\')
    var link = \'index.php?tpl=popup&tpt=page&adm=[ADMIN_SECTION]&ada=3&i=\'+id;
    else if (act == \'chg\')
    var link = \'index.php?tpl=popup&tpt=page&adm=[ADMIN_SECTION]&ada=1&i=\'+id;
    var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
    var langwindow = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }
    //-->
    </script>
    <table cellspacing="2" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:*;min-width:10px;" />
    <col style="width:100px;min-width:80px;" />   <!-- Add a Language  //-->
    <col style="width:100px;min-width:80px;" />   <!-- Upload Image (for languages)  //-->
    <col style="width:100px;min-width:80px;" />   <!-- Delete an Language-Image  //-->
    <col style="width:*;min-width:10px;" />
    </colgroup>
    <tr>
    <td colspan="5" style="border-bottom:1px solid rgb(0,111,153);border-top:1px solid rgb(0,111,153);padding:5px;text-align:center;font-size:15px;font-weight:bold;color:rgb(0,111,153);line-height:20px;background-color:rgb(245,250,255);">[ADMIN_SECTION_NAME]</td>
    </tr>
    <tr>
    <td></td>
    <td style="padding:2px;padding-top:5px;text-align:center;vertical-align:top;border:1px solid rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;" onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="ChangeLocation(this,\'add\');">
    <img src="images/design/admin/AddLanguage_big.gif" alt="add new lang" title="Add a new Language" style="width:34px;height:34px;border:0px hidden black;" /><br />'.$LANG->getValue("","txt","admin_053").'</td>
    <td style="padding:2px;padding-top:5px;text-align:center;vertical-align:top;border:1px solid rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;" onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="ChangeLocation(this,\'img\');">
    <img src="images/design/admin/UploadLanguage_big.gif" alt="Upload lang icon" title="Upload Language-Icon" style="width:34px;height:34px;border:0px hidden black;" /><br />'.$LANG->getValue("","txt","admin_051").'</td>
    <td style="padding:2px;padding-top:5px;text-align:center;vertical-align:top;border:1px solid rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;" onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="ChangeLocation(this,\'ide\');">
    <img src="images/design/admin/DeleteLanguage_big.gif" alt="delete lang icon" title="Delete Language-Icon" style="width:34px;height:34px;border:0px hidden black;" /><br />'.$LANG->getValue("","txt","admin_052").'</td>
    <td></td>
    </tr>
    <tr>
    <td colspan="5" style="padding:1px;border-top:1px solid rgb(0,111,153);border-bottom:2px solid rgb(0,111,153);height:5px;"></td>
    </tr>
    <tr>
    <td colspan="5" style="padding:5px;text-align:center;font-size:15px;font-weight:bold;color:rgb(224,240,249);line-height:20px;background-color:rgb(0,111,153);">&nbsp;</td>
    </tr>
    <tr>
    <td colspan="5" style="padding:1px;border-top:2px solid rgb(0,111,153);border-bottom:1px solid rgb(0,111,153);height:5px;"></td>
    </tr>
    </table>';

    $html['LanguageList']  = '
    <tr>
    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:middle;border-right:1px solid rgb(0,111,153);">
    <img src="images/design/language/[LANGUAGE_ICON]" style="width:19px;height:12px;border:0px solid black;" />
    &nbsp;&nbsp;<span style="font-weight:bold;">[LANGUAGE_NAME]</span>
    </td>
    <td style="padding:5px;text-align:left;vertical-align:middle;">
    [LANGUAGE_SHORT]
    </td>
    <td style="padding:5px;text-align:left;vertical-align:middle;border-right:1px solid rgb(0,111,153);">
    [LANGUAGE_CHARSET]
    </td>
    <td style="padding:5px;text-align:left;vertical-align:middle;font-size:12px;color:rgb(0,111,153);">
    <img src="images/design/admin/delete_small.gif" style="vertical-align:middle;text-align:bottom;border:0px solid black;width:16px;height:16px;" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="javascript:OpenLangWindow(\'[LANGUAGE_ID]\',\'del\');" />
    &nbsp;&nbsp;&nbsp;
    <img src="images/design/admin/edit_small.gif" style="vertical-align:middle;text-align:bottom;border:0px solid black;width:16px;height:16px;" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="javascript:OpenLangWindow(\'[LANGUAGE_ID]\',\'chg\');" />
    </td>
    </tr>';

    $html['LanguageDelete']  = '<form action="index.php" method="POST">
    <input type="hidden" name="tpl" value="popup" />
    <input type="hidden" name="tpt" value="page" />
    <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
    <input type="hidden" name="lan_did" value="[LANGUAGE_DELETE]" />
    <input type="hidden" name="i" value="[POST_ID]" />
    <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
    <input type="hidden" name="ada" value="[ADMIN_ACTION]" />

    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:0px;text-align:center;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;" />
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_057").'</td>
    </tr>
    <tr>
    <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:*;min-width:150px;" />
    <col style="width:*;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
    '.$LANG->getValue("","txt","admin_059").':
    </td>
    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <span style="font-weight:bold;color:rgb(255,100,100);font-size:12px;">'.$LANG->getValue("","txt","admin_058").'</span>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
    <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
    <input type="submit" value="'.$LANG->getValue("","txt","admin_055").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
    </td>
    </tr>
    </table>
    </form>';

    $html['LanguageDeleteComplete']  = '<script type="text/javascript">
    <!--
    opener.location.reload();
    opener.parent.lang.location.reload();
    window.close();
    //-->
    </script>

    Close this Window if it"s not closed automaticly...';


    $html['LanguageTextMain']  = '<form action="index.php" method="POST">
    <input type="hidden" name="tpl" value="popup">
    <input type="hidden" name="tpt" value="page">
    <input type="hidden" name="i" value="'.$postId.'">
    <input type="hidden" name="adm" value="[ADMIN_SCRIPT]">
    <input type="hidden" name="ada" value="[ADMIN_ACTION]">
    <input type="hidden" name="lan" value="[LANGUAGE]">

    <table cellspacing="5" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:10px;text-align:center;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:*;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(242,240,249);font-size:12px;font-weight:bold;">
    '.$LANG->getValue("","txt","admin_060").'
    </td>
    </tr>
    <tr>
    <td style="padding:2px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);">
    [REPLACE:ProgramText]
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td colspan="2" style="padding:10px;text-align:right;vertical-align:middle;">
    <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
    <input type="submit" value="'.$LANG->getValue("","txt","admin_007").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
    </td>
    </tr>
    </table></form>';

    $html['LanguageTextFields']  = '<table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:150px;min-width:10px;" />
    <col style="width:*px;min-width:200px;" />
    </colgroup>
    <tr><td style="text-align:left;vertical-align:top;font-weight:bold;padding-left:10px;padding-top:3px;">
    '.$LANG->getValue("","txt","admin_061").':
    </td><td style="text-align:left;">
    <input type="hidden" name="lan_id" value="[TEXT_ID]">
    <input type="text" name="lan_text" value="[TEXT_LONG]" style="width:200px;font-family:Helvetica, sans serif;font-size:10px;font-weight:normal;color:rgb(0,111,153);background-color:rgb(245,250,255);border:1px solid rgb(114,191,236);padding:2px;" />
    </td></tr>
    <tr><td style="text-align:left;vertical-align:top;font-weight:bold;padding-left:10px;padding-top:3px;">
    '.$LANG->getValue("","txt","admin_062").':
    </td><td style="text-align:left;">
    <input type="text" name="lan_short" value="[TEXT_SHORT]" style="width:200px;font-family:Helvetica, sans serif;font-size:10px;font-weight:normal;color:rgb(0,111,153);background-color:rgb(245,250,255);border:1px solid rgb(114,191,236);padding:2px;" />
    </td></tr>
    <tr><td style="text-align:left;vertical-align:top;font-weight:bold;padding-left:10px;padding-top:3px;">
    '.$LANG->getValue("","txt","admin_063").':
    </td><td style="text-align:left;">
    <input type="text" name="lan_char" value="[TEXT_CHARSET]" style="width:200px;font-family:Helvetica, sans serif;font-size:10px;font-weight:normal;color:rgb(0,111,153);background-color:rgb(245,250,255);border:1px solid rgb(114,191,236);padding:2px;" />
    </td></tr>
    <tr><td style="text-align:left;vertical-align:top;font-weight:bold;padding-left:10px;padding-top:3px;">
    '.$LANG->getValue("","txt","admin_064").':
    </td><td style="text-align:left;">
    <img id="lan_ico_img" src="images/design/language/[TEXT_ICON]" alt="language icon" title="Choose language Icon" style="width:19px;height:12px;" />
    <input type="hidden" id="lan_ico" name="lan_icon" value="[TEXT_ICON]" style="width:200px;font-family:Helvetica, sans serif;font-size:10px;font-weight:normal;color:rgb(0,111,153);background-color:rgb(245,250,255);border:1px solid rgb(114,191,236);padding:2px;" />
    <div style="margin-top:5px;border-top:3px double rgb(0,111,153);">
    [-->LANG_ICON_CHOOSER]
    <img src="images/design/language/[LANG_ICON]" alt="language icon" title="Choose language Icon" style="width:[LANG_ICON_WIDTH]px;height:[LANG_ICON_HEIGHT]px;" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="document.getElementById(\'lan_ico\').value=\'[LANG_ICON]\';document.getElementById(\'lan_ico_img\').src=\'images/design/language/[LANG_ICON]\';" />&nbsp;&nbsp;
    [--LANG_ICON_CHOOSER]
    </div>
    </td></tr>
    </table>';

    $html['LanguageDeleteIcon']  = '<table cellspacing="5" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:10px;text-align:center;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:*;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(242,240,249);font-size:12px;font-weight:bold;">
    '.$LANG->getValue("","txt","admin_065").'
    </td>
    </tr>
    <tr>
    <td style="padding:2px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:80px;min-width:80px;" />
    <col style="min-width:200px;" />
    </colgroup>
    [REPLACE:IconList]
    </table>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td colspan="2" style="padding:10px;text-align:right;vertical-align:middle;">
    <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
    </td>
    </tr>
    </table>';

    $html['LanguageDeleteIconList']  = '
    <tr>
    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:middle;border-right:1px solid rgb(0,111,153);">
    <img src="images/design/language/[LANGUAGE_ICON]" style="width:19px;height:12px;border:0px solid black;" alt="lang icon" />
    </td>
    <td style="padding:5px;text-align:left;vertical-align:middle;font-size:12px;color:rgb(0,111,153);">
    <img src="images/design/admin/delete_small.gif" alt="delete" title="Delete Icon" style="vertical-align:middle;text-align:bottom;border:0px solid black;width:16px;height:16px;" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="window.location.href=\'index.php?tpl=popup&tpt=page&adm=[ADMIN_SECTION]&ada=[ADMIN_INDEX]&dlanimg=[LANGUAGE_ICON]\';" />
    </td>
    </tr>';

    return $html;
  }

  function ChangeText()
  {
    global $LANG,$template,$TemplateType;

    $html['MainScreen']  = '
    <form action="index.php" method="POST">
      <input type="hidden" name="tpl" value="'.$template.'" />
      <input type="hidden" name="tpt" value="'.$TemplateType.'" />
      <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
      <input type="hidden" name="chi" value="[CHANGE_TEXT_ID]" />
      <input type="hidden" name="act" value="[CHANGE_ACTION]" />
      <input type="hidden" name="m" value="[MAIN_MENU_ID]" />
      <input type="hidden" name="s" value="[SUB_MENU_ID]" />

      <script type="text/javascript">
      <!--
        function OpenPictureWindow()
        {
          var picture = document.getElementById(\'txt_picfile\').value;
          var linkX   = \'&pic_id=\'+picture;
          var prop    = \'width=610,height=700,top=\'+((screen.height-700)/2)+\',left=\'+((screen.width-610)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
          var link    = \'index.php?tpt=page&tpl=popup&m=[MAIN_MENU_ID]&s=[SUB_MENU_ID]&chi=[CHANGE_TEXT_ID]&lan=[LANGUAGE]&act=image\'+linkX;
          var imgwin  = window.open(link.replace(/&amp;/gi,\'&\'),\'imgwin\',prop);
        }

        function doCloseWindow()
        {
          var loc = \'index.php?tpl='.$template.'&tpt='.$TemplateType.'&lan='.$LANG->getLanguageName().'&m=[MAIN_MENU_ID]&s=[SUB_MENU_ID]\';
          opener.location.href = loc.replace(/&amp;/gi,\'&\');
          window.close();
        }

        [-->IMAGE_CHOOSER_SCRIPT]
        function ClearPicture()
        {
          document.getElementById(\'txt_picfile\').value = \'\';
          document.getElementById(\'img_picfile\').src = \'/\';
        }
        [--IMAGE_CHOOSER_SCRIPT]

        [-->LAYOUT_CHOOSER_SCRIPT]
        function OpenLayoutWindow()
        {
          var file    = document.getElementById(\'lay_file\').value;
          var global  = document.getElementById(\'lay_global\').value;
          var title   = document.getElementById(\'lay_title\').value;
          var text    = document.getElementById(\'lay_text\').value;
          var picture = document.getElementById(\'lay_picture\').value;

          var linkX = \'&lay_file=\'+file+\'&lay_global=\'+global+\'&lay_title=\'+title+\'&lay_text=\'+text+\'&lay_picture=\'+picture;
          var prop  = \'width=610,height=700,top=\'+((screen.height-700)/2)+\',left=\'+((screen.width-610)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
          var link  = \'index.php?tpt=page&tpl=popup&m=[MAIN_MENU_ID]&s=[SUB_MENU_ID]&chi=[CHANGE_TEXT_ID]&lan=[LANGUAGE]&act=layout\'+linkX;
          var laywin = window.open(link.replace(/&amp;/gi,\'&\'),\'laywin\',prop);
        }
        [--LAYOUT_CHOOSER_SCRIPT]

        function ChangeBackground(obj,act)
        {
          if (act == \'over\')
          {
            obj.style.cursor = \'pointer\';
            obj.style.border = \'1px solid rgb(0,111,153)\';
            obj.style.backgroundColor = \'rgb(114,191,236)\';
          }
          else
          {
            obj.style.cursor = \'normal\';
            obj.style.border = \'1px solid rgb(114,191,236)\';
            obj.style.backgroundColor = \'rgb(224,240,249)\';
          }
        }

        function ChangeTagButton(obj,act)
        {
          if (act == \'over\')
          {
            obj.style.backgroundColor = \'rgb(114,191,236);\';
            obj.style.cursor = \'pointer\';
          }
          else
          {
            obj.style.backgroundColor = \'rgb(240,242,250);\';
            obj.style.cursor = \'normal\';
          }
        }

        function DoInsertInternalLink(Link)
        {
          var tagOpen  = \'link=i,\' + Link;
          var tagClose = \'link\';
          InsertStyleTag(tagOpen, tagClose);
        }

        function InsertLinkTag(type)
        {
          if (type == \'i\')
          {
            var prop    = \'width=610,height=700,top=\'+((screen.height-700)/2)+\',left=\'+((screen.width-610)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
            var link    = \'index.php?tpt=page&tpl=popup&m=[MAIN_MENU_ID]&s=[SUB_MENU_ID]&lan=[LANGUAGE]&act=link\';
            var LinkWin = window.open(link.replace(/&amp;/gi,\'&\'), \'LinkWindow\', prop);
          }
          else
          {
            var Link     = window.prompt(\''.$LANG->getValue('','txt','admin_189').'\', \'http://\');
            if ((Link != null) && (Link != \'\') && (Link != \'http://\'))
            {
              var tagOpen  = \'link=e,\' + Link;
              var tagClose = \'link\';
              InsertStyleTag(tagOpen, tagClose);
            }
          }
        }

        function InsertMainListTag()
        {
          var type = window.prompt(\''.$LANG->getValue('','txt','admin_188').'\');
          if ((type != null) && (type != \'\'))
          {
            var tagOpen = \'list=\' + type;
            var tagClose = \'list\';
            InsertStyleTag(tagOpen, tagClose);
          }
        }

        function InsertParagraphTag(type)
        {
          if (type == \'l\')
            var Padding = window.prompt(\''.$LANG->getValue('','txt','admin_186').'\',\'10\') + \',0\';
          else if (type == \'r\')
            var Padding = \'0,\' + window.prompt(\''.$LANG->getValue('','txt','admin_187').'\',\'10\');
          else if (type == \'lr\')
          {
            var Padding = window.prompt(\''.$LANG->getValue('','txt','admin_186').'\',\'10\');
            var Padding = Padding + \',\' + window.prompt(\''.$LANG->getValue('','txt','admin_187').'\',\'10\');
          }
          if ((Padding.search(\'/null/\') <= 0) && (Padding != \'\'))
          {
            var Padding = \'p=\' + Padding;
            InsertStyleTag(Padding,\'p\');
          }
        }

        function InsertStyleTag(otag,ctag)
        {
          var txtFld = document.getElementById(\'txt_text\');
          var tagOpen  = \'[\' + otag + \']\';
          if (ctag != \'\')
            var tagClose = \'[/\' + ctag + \']\';
          else
            var tagClose = \'[/\' + otag + \']\';

          // for Internet-Explorer
          if (document.selection)
          {
            txtFld.focus();
            sel = document.selection.createRange();
            sel.text = tagOpen + sel.text + tagClose;
            txtFld.focus();
          }
          // for Mozilla and Netscape
          else if (txtFld.selectionStart || txtFld.selectionStart == "0")
          {
            var startPos = txtFld.selectionStart;
            var endPos   = txtFld.selectionEnd;
            var curTxt   = txtFld.value;
            var selTxt   = curTxt.substring(startPos, endPos);

            txtFld.value = curTxt.substring(0, startPos) + tagOpen + selTxt + tagClose + curTxt.substring(endPos, curTxt.length);
            txtFld.focus();
          }
          // By any other Browser add the tag on end
          else
          {
            txtFld.value += tagOpen + \' \' + tagClose;
            txtFld.focus();
          }
        }

        function InsertListTag()
        {
          var txtFld = document.getElementById(\'txt_text\');
          // for Internet-Explorer
          if (document.selection)
          {
            txtFld.focus();
            sel = document.selection.createRange();
            sel.text = \'[-]\' + sel.text;
            txtFld.focus();
          }
          // for Mozilla and Netscape
          else if (txtFld.selectionStart || txtFld.selectionStart == "0")
          {
            var startPos = txtFld.selectionStart;
            var endPos   = txtFld.selectionEnd;
            var curTxt   = txtFld.value;
            var selTxt   = curTxt.substring(startPos, endPos);

            txtFld.value = curTxt.substring(0, startPos) + \'[-]\' + selTxt + curTxt.substring(endPos, curTxt.length);
            txtFld.focus();
          }
          // for safari and Konqueror
          //else if (document.getSelection)
          //{
          //  alert(document.getSelection());
          //}
          // By any other Browser add the tag on end
          else
          {
            txtFld.value += \'[-]\';
            txtFld.focus();
          }
        }

        [-->CORRECTION_SCRIPT]
        function CorrectText()
        {
          var CharsSave = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_.!~*\'()";
          var HEX = "0123456789ABCDEF";

          var txtPlain = document.getElementById(\'txt_text\').value;
          var txtValue = \'\';
          // get each Char
          for (var i = 0; i < txtPlain.length; i++ )
          {
            // get Char at current Position
            var chck = txtPlain.charAt(i);

            // Check for a space
            if (chck == \' \')
            {
                txtValue += \'+\';
            }
            // Check if this is a Safe-Char
            else if (CharsSave.indexOf(chck) != -1)
            {
                txtValue += chck;
            }
            // all other Chars
            else
            {
              var charCode = chck.charCodeAt(0);
              // Add a Space if the Char is not allowed in UTF-8
              if (charCode > 255)
              {
                txtValue += \'+\';
              }
              else
              {
                txtValue += \'%\';
                txtValue += HEX.charAt((charCode >> 4) & 0xF);
                txtValue += HEX.charAt(charCode & 0xF);
              }
            }
          }


          var prop     = \'width=610,height=700,top=\'+((screen.height-700)/2)+\',left=\'+((screen.width-610)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
          //var link     = \'index.php?tpt=page&tpl=popup&m=[MAIN_MENU_ID]&s=[SUB_MENU_ID]&lan=[LANGUAGE]&act=correct&ctext=\' + escape(txtValue);
          var link     = \'index.php?tpt=page&tpl=popup&m=[MAIN_MENU_ID]&s=[SUB_MENU_ID]&lan=[LANGUAGE]&act=correct&ctext=\' + txtValue;
          var CorrWin  = window.open(link.replace(/&amp;/gi,\'&\'), \'CorrectWindow\', prop);
        }

        function applyCorrectedText(txtVal)
        {
          document.getElementById(\'txt_text\').value = txtVal;
        }
        [--CORRECTION_SCRIPT]
      //-->
      </script>

      <table cellspacing="0" cellpadding="0" style="margin:10px;width:100%;table-layout:fixed;empty-cells:show;">
        <colgroup>
          <col style="width:100%;min-width:200px;" />
        </colgroup>
        <tr>
          <td style="padding:0px;text-align:center;vertical-align:top;">
            <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
              <colgroup>
              <col style="width:100%;" />
              </colgroup>
              <tr>
                <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">
                  '.$LANG->getValue("","txt","admin_066").'
                </td>
              </tr>
              <tr>
                <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                  <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
                    <colgroup>
                      <col style="width:150px;min-width:150px;" />
                      <col style="min-width:200px;" />
                    </colgroup>
                    <tr>
                      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
                        '.$LANG->getValue("","txt","admin_074").':
                      </td>
                      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        <input type="text" name="txt_title" value="[TEXT_SHORT]" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);width:350px;font-family:Arial,helvetica,sans serif;font-size:12px;padding:1px;color:rgb(0,111,153);" />
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">&nbsp;</td>
                      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        <img src="images/design/admin/FontStyle_bold.gif" style="width:24px;height:24px;border:0px solid black;background-color:rgb(240,242,250);" onMouseOver="ChangeTagButton(this,\'over\');" onMouseOut="ChangeTagButton(this,\'out\');" onClick="InsertStyleTag(\'b\',\'\');" />
                        <img src="images/design/admin/FontStyle_italic.gif" style="width:24px;height:24px;border:0px solid black;background-color:rgb(240,242,250);" onMouseOver="ChangeTagButton(this,\'over\');" onMouseOut="ChangeTagButton(this,\'out\');" onClick="InsertStyleTag(\'i\',\'\');" />
                        <img src="images/design/admin/FontStyle_underline.gif" style="width:24px;height:24px;border:0px solid black;background-color:rgb(240,242,250);" onMouseOver="ChangeTagButton(this,\'over\');" onMouseOut="ChangeTagButton(this,\'out\');" onClick="InsertStyleTag(\'u\',\'\');" />

                        <!-- Spacer for Paragraph-Tags //-->
                        <img src="images/design/admin/FontStyle_spacer.gif" style="width:8px;height:24px;border:0px solid black;" />
                        <img src="images/design/admin/FontStyle_paragraph.gif" style="width:24px;height:24px;border:0px solid black;background-color:rgb(240,242,250);" onMouseOver="ChangeTagButton(this,\'over\');" onMouseOut="ChangeTagButton(this,\'out\');" onClick="InsertParagraphTag(\'l\');" />
                        <img src="images/design/admin/FontStyle_paragraph_r.gif" style="width:24px;height:24px;border:0px solid black;background-color:rgb(240,242,250);" onMouseOver="ChangeTagButton(this,\'over\');" onMouseOut="ChangeTagButton(this,\'out\');" onClick="InsertParagraphTag(\'r\');" />
                        <img src="images/design/admin/FontStyle_paragraph_lr.gif" style="width:24px;height:24px;border:0px solid black;background-color:rgb(240,242,250);" onMouseOver="ChangeTagButton(this,\'over\');" onMouseOut="ChangeTagButton(this,\'out\');" onClick="InsertParagraphTag(\'lr\');" />

                        <!-- Spacer for List-Tags //-->
                        <img src="images/design/admin/FontStyle_spacer.gif" style="width:8px;height:24px;border:0px solid black;" />
                        <img src="images/design/admin/FontStyle_list.gif" style="width:24px;height:24px;border:0px solid black;background-color:rgb(240,242,250);" onMouseOver="ChangeTagButton(this,\'over\');" onMouseOut="ChangeTagButton(this,\'out\');" onClick="InsertMainListTag();" />
                        <img src="images/design/admin/FontStyle_list_entry.gif" style="width:24px;height:24px;border:0px solid black;background-color:rgb(240,242,250);" onMouseOver="ChangeTagButton(this,\'over\');" onMouseOut="ChangeTagButton(this,\'out\');" onClick="InsertListTag();" />

                        <!-- Spacer for Links //-->
                        <img src="images/design/admin/FontStyle_spacer.gif" style="width:8px;height:24px;border:0px solid black;" />
                        <img src="images/design/admin/FontStyle_link_intern.gif" style="width:24px;height:24px;border:0px solid black;background-color:rgb(240,242,250);" onMouseOver="ChangeTagButton(this,\'over\');" onMouseOut="ChangeTagButton(this,\'out\');" onClick="InsertLinkTag(\'i\');" />
                        <img src="images/design/admin/FontStyle_link_extern.gif" style="width:24px;height:24px;border:0px solid black;background-color:rgb(240,242,250);" onMouseOver="ChangeTagButton(this,\'over\');" onMouseOut="ChangeTagButton(this,\'out\');" onClick="InsertLinkTag(\'e\');" />

                        [-->CORRECTION]
                        <!-- Spacer for TextCorrection //-->
                        <img src="images/design/admin/FontStyle_spacer.gif" style="width:8px;height:24px;border:0px solid black;" />
                        <img src="images/design/admin/FontStyle_text_correct.gif" style="width:24px;height:24px;border:0px solid black;background-color:rgb(240,242,250);" onMouseOver="ChangeTagButton(this,\'over\');" onMouseOut="ChangeTagButton(this,\'out\');" onClick="CorrectText();" />
                        [--CORRECTION]
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
                        '.$LANG->getValue("","txt","admin_075").':
                      </td>
                      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                          <textarea name="txt_text" id="txt_text" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);width:350px;height:250px;font-family:Arial,helvetica,sans serif;font-size:12px;padding:1px;color:rgb(0,111,153);">[TEXT_LONG]</textarea>
                      </td>
                    </tr>
                    [-->IMAGE_CHOOSER]
                    <tr>
                      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
                        '.$LANG->getValue("","txt","admin_073").':
                      </td>
                      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        <div style="font-weight:bold;width:115px;padding:0px;padding-top:2px;paddin-bottom:2px;margin-bottom:2px;text-align:center;vertical-align:middle;border:1px solid rgb(114,191,236);" onMouseOver="ChangeBackground(this,\'over\')" onMouseOut="ChangeBackground(this,\'out\')" onCLick="ClearPicture();">
                          '.$LANG->getValue("","txt","admin_082").'
                        </div>
                        <input type="hidden" id="txt_picfile" name="txt_picfile" value="[PICTURE_ID]" />
                        <img id="img_picfile" src="[PICTURE_FILE_SMALL]" alt="image" title="Image for Text" style="width:115px;height:130px;border:1px solid rgb(114,191,236);" onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="OpenPictureWindow();" />
                      </td>
                    </tr>
                    [--IMAGE_CHOOSER]
                    [-->LAYOUT_CHOOSER]
                    <tr>
                    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
                      '.$LANG->getValue("","txt","admin_076").':
                    </td>
                      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                        <div style="font-weight:normal;margin:0px;padding:2px;padding-left:5px;padding-right:5px;border:1px solid rgb(114,191,236);"  onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="OpenLayoutWindow();">
                          <span style="font-weight:bold;text-decoration:underline;">'.$LANG->getValue("","txt","admin_077").'</span><br>
                          <div style="margin:0px;padding:2px;padding-left:10px;padding-right:5px;">
                            '.$LANG->getValue("","txt","admin_068").':&nbsp;&nbsp;<span style="font-style:italic;" id="lay_file_txt">[LAYOUT_FILE_TEXT]&nbsp;</span>
                          </div>
                          <div style="margin:0px;padding:2px;padding-left:10px;padding-right:5px;">
                            '.$LANG->getValue("","txt","admin_069").':&nbsp;&nbsp;<span style="font-style:italic;" id="lay_global_txt">[LAYOUT_GLOBAL_TEXT]&nbsp;</span>
                          </div>
                          <div style="margin:0px;padding:2px;padding-left:10px;padding-right:5px;">
                            '.$LANG->getValue("","txt","admin_070").':&nbsp;&nbsp;<span style="font-style:italic;" id="lay_title_txt">[LAYOUT_TITLE_TEXT]&nbsp;</span>
                          </div>
                          <div style="margin:0px;padding:2px;padding-left:10px;padding-right:5px;">
                            '.$LANG->getValue("","txt","admin_071").':&nbsp;&nbsp;<span style="font-style:italic;" id="lay_text_txt">[LAYOUT_TEXT_TEXT]&nbsp;</span>
                          </div>
                          <div style="margin:0px;padding:2px;padding-left:10px;padding-right:5px;">
                            '.$LANG->getValue("","txt","admin_072").':&nbsp;&nbsp;<span style="font-style:italic;" id="lay_picture_txt">[LAYOUT_PICTURE_TEXT]&nbsp;</span>
                          </div>
                        </div>
                        <input type="hidden" id="lay_file"    name="lay_file"    value="[LAYOUT_FILE]" />
                        <input type="hidden" id="lay_global"  name="lay_global"  value="[LAYOUT_GLOBAL]" />
                        <input type="hidden" id="lay_title"   name="lay_title"   value="[LAYOUT_TITLE]" />
                        <input type="hidden" id="lay_text"    name="lay_text"    value="[LAYOUT_TEXT]" />
                        <input type="hidden" id="lay_picture" name="lay_picture" value="[LAYOUT_PICTURE]" />
                      </td>
                    </tr>
                    [--LAYOUT_CHOOSER]
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
            <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="doCloseWindow();" />&nbsp;&nbsp;
            <input type="submit" value="'.$LANG->getValue("","txt","admin_067").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
          </td>
        </tr>
      </table>
    </form>';

    $html['LayoutScreen']  = '
    <script type="text/javascript">
    <!--
      function ReloadWindow()
      {
        var file    = document.getElementById(\'txt_layfile\').value;
        var global  = document.getElementById(\'txt_globlay\').value;
        var title   = document.getElementById(\'txt_titlay\').value;
        var text    = document.getElementById(\'txt_txtlay\').value;
        var picture = document.getElementById(\'txt_piclay\').value;

        var linkX = \'&lay_file=\'+file+\'&lay_global=\'+global+\'&lay_title=\'+title+\'&lay_text=\'+text+\'&lay_picture=\'+picture;
        var link = \'index.php?tpl=blank&tpt=page&m=[MAIN_MENU_ID]&s=[SUB_MENU_ID]&chi=[CHANGE_TEXT_ID]&lan=[LANGUAGE]&act=layout\'+linkX;

        window.location.href = link.replace(/&amp;/gi,\'&\');
      }

      function ChangePreviewWindow()
      {
        var file    = document.getElementById(\'txt_layfile\').value;
        var global  = document.getElementById(\'txt_globlay\').value;
        var title   = document.getElementById(\'txt_titlay\').value;
        var text    = document.getElementById(\'txt_txtlay\').value;
        var picture = document.getElementById(\'txt_piclay\').value;

        var linkX = \'&lay_file=\'+file+\'&lay_global=\'+global+\'&lay_title=\'+title+\'&lay_text=\'+text+\'&lay_picture=\'+picture;
        var link = \'index.php?tpl=popup&tpt=page&m=[MAIN_MENU_ID]&s=[SUB_MENU_ID]&chi=[CHANGE_TEXT_ID]&lan=[LANGUAGE]&act=preview\'+linkX;

        PreviewWindow.location.href = link.replace(/&amp;/gi,\'&\');
      }

      function SubmitStyles()
      {
        var file    = document.getElementById(\'txt_layfile\').value;
        var global  = document.getElementById(\'txt_globlay\').value;
        var title   = document.getElementById(\'txt_titlay\').value;
        var text    = document.getElementById(\'txt_txtlay\').value;
        var picture = document.getElementById(\'txt_piclay\').value;

        opener.document.getElementById(\'lay_file\').value    = file;
        opener.document.getElementById(\'lay_global\').value  = global;
        opener.document.getElementById(\'lay_title\').value   = title;
        opener.document.getElementById(\'lay_text\').value    = text;
        opener.document.getElementById(\'lay_picture\').value = picture;

        if (document.getElementById(\'fil_\'+file))
          opener.document.getElementById(\'lay_file_txt\').firstChild.nodeValue    = document.getElementById(\'fil_\'+file).firstChild.nodeValue;
        else
          opener.document.getElementById(\'lay_file_txt\').firstChild.nodeValue    = \'\';
        if (document.getElementById(\'glo_\'+global))
          opener.document.getElementById(\'lay_global_txt\').firstChild.nodeValue  = document.getElementById(\'glo_\'+global).firstChild.nodeValue;
        else
          opener.document.getElementById(\'lay_global_txt\').firstChild.nodeValue  = \'\';
        if (document.getElementById(\'tit_\'+title))
          opener.document.getElementById(\'lay_title_txt\').firstChild.nodeValue   = document.getElementById(\'tit_\'+title).firstChild.nodeValue;
        else
          opener.document.getElementById(\'lay_title_txt\').firstChild.nodeValue   = \'\';
        if (document.getElementById(\'txt_\'+text))
          opener.document.getElementById(\'lay_text_txt\').firstChild.nodeValue    = document.getElementById(\'txt_\'+text).firstChild.nodeValue;
        else
          opener.document.getElementById(\'lay_text_txt\').firstChild.nodeValue    = \'\';
        if (document.getElementById(\'pic_\'+picture))
          opener.document.getElementById(\'lay_picture_txt\').firstChild.nodeValue = document.getElementById(\'pic_\'+picture).firstChild.nodeValue;
        else
          opener.document.getElementById(\'lay_picture_txt\').firstChild.nodeValue = \'\';

        window.close();
      }
    //-->
    </script>
    <table cellspacing="0" cellpadding="0" style="margin:10px;width:100%;table-layout:fixed;empty-cells:show;">
      <colgroup>
        <col style="width:100%;min-width:200px;" />
      </colgroup>
      <tr>
        <td style="padding:0px;text-align:center;vertical-align:top;">
          <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
            <colgroup>
              <col style="width:100%;" />
            </colgroup>
            <tr>
              <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">
                '.$LANG->getValue("","txt","admin_076").'
              </td>
            </tr>
            <tr>
              <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
                  <colgroup>
                    <col style="width:150px;min-width:150px;" />
                    <col style="min-width:200px;" />
                  </colgroup>
                  <tr>
                    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
                      '.$LANG->getValue("","txt","admin_068").':
                    </td>
                    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                      <select name="txt_layfile" id="txt_layfile" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);width:250px;" onChange="ReloadWindow();">
                        [-->FILE_LIST]<option id="fil_[FILE_NAME]" value="[FILE_NAME]">[FILE_DESCRIPTION]</option>[--FILE_LIST]
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
                    '.$LANG->getValue("","txt","admin_069").':
                    </td>
                    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                      <select name="txt_globlay" id="txt_globlay" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);width:250px;" onChange="ChangePreviewWindow();">
                      [-->GLOBAL_LAYOUT_LIST]<option id="glo_[NAME]" value="[NAME]">[DESCRIPTION]</option> [--GLOBAL_LAYOUT_LIST]
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
                    '.$LANG->getValue("","txt","admin_070").':
                    </td>
                    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                      <select name="txt_titlay" id="txt_titlay" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);width:250px;" onChange="ChangePreviewWindow();">
                        [-->TITLE_LAYOUT_LIST]<option id="tit_[NAME]" value="[NAME]">[DESCRIPTION]</option> [--TITLE_LAYOUT_LIST]
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
                    '.$LANG->getValue("","txt","admin_071").':
                    </td>
                    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                      <select name="txt_txtlay" id="txt_txtlay" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);width:250px;" onChange="ChangePreviewWindow();">
                        [-->TEXT_LAYOUT_LIST]<option id="txt_[NAME]" value="[NAME]">[DESCRIPTION]</option> [--TEXT_LAYOUT_LIST]
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
                      '.$LANG->getValue("","txt","admin_072").':
                    </td>
                    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                      <select name="txt_piclay" id="txt_piclay" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);width:250px;" onChange="ChangePreviewWindow();">
                        [-->PICTURE_LAYOUT_LIST]<option id="pic_[NAME]" value="[NAME]">[DESCRIPTION]</option> [--PICTURE_LAYOUT_LIST]
                      </select>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;border-bottom:1px solid rgb(0,111,153);">
          <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="window.close();" />&nbsp;&nbsp;
          <input type="button" value="'.$LANG->getValue("","txt","admin_067").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="SubmitStyles();" />
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top:10px;text-align:center;vertical-align:top;">
          <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
            <colgroup>
              <col style="width:100%;" />
            </colgroup>
            <tr>
              <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">
                '.$LANG->getValue("","txt","admin_078").'
              </td>
            </tr>
            <tr>
              <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);">
                <iframe name="PreviewWindow" style="width:100%;height:350px;border:0px solid black;margin:0px;padding:0px;" src="index.php?tpl=popup&tpt=page&m=[MAIN_MENU_ID]&s=[SUB_MENU_ID]&chi=[CHANGE_TEXT_ID]&lan=[LANGUAGE]&act=preview&lay_file=[LAYOUT_FILE]&lay_global=[LAYOUT_GLOBAL]&lay_title=[LAYOUT_TITLE]&lay_text=[LAYOUT_TEXT]&lay_picture=[LAYOUT_PICTURE]">
                </iframe>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>';

    $html['LinkChooser'] = '
    <script type="text/javascript">
    <!--
      function SubmitInternalLink()
      {
        opener.DoInsertInternalLink(\'[INTERNAL_LINK_STRING]\');
        window.close();
      }
    //-->
    </script>
    <table cellspacing="0" cellpadding="0" style="margin:10px;width:100%;table-layout:fixed;empty-cells:show;">
      <colgroup>
        <col style="width:100%;min-width:200px;" />
      </colgroup>
      <tr>
        <td style="padding:0px;text-align:center;vertical-align:top;">
          <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
            <colgroup>
              <col style="" />
            </colgroup>
            <tr>
              <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">
                '.$LANG->getValue("","txt","admin_190").'
              </td>
            </tr>
            <tr>
              <td style="text-align:left;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
                [-->MENU]
                <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
                  <colgroup>
                    <col style="min-width:200px;" />
                  </colgroup>
                  [-->MENU_ENTRY_1]
                    <tr>
                      <td style="padding:3px;text-align:left;vertical-align:middle;background-color:rgb(214,230,239);">
                      [MENU_SPACER]<a href="[MENU_LINK]">[MENU_TEXT]</a>
                      </td>
                    </tr>
                  [--MENU_ENTRY_1]
                  [-->MENU_ENTRY_2]
                    <tr>
                      <td style="padding:3px;text-align:left;vertical-align:middle;background-color:rgb(224,240,249);">
                      [MENU_SPACER]<a href="[MENU_LINK]">[MENU_TEXT]</a>
                      </td>
                    </tr>
                  [--MENU_ENTRY_2]
                  [-->MENU_ENTRY_ACTIVE]
                    <tr>
                      <td style="padding:3px;text-align:left;vertical-align:middle;background-color:rgb(164,220,189);">
                      [MENU_SPACER]<a href="[MENU_LINK]">[MENU_TEXT]</a>
                      </td>
                    </tr>
                  [--MENU_ENTRY_ACTIVE]
                  </table>
                [--MENU]
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
          <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="window.close();" />&nbsp;&nbsp;
          <input type="button" value="'.$LANG->getValue("","txt","admin_067").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="SubmitInternalLink();" />
        </td>
      </tr>
    </table>
    ';

    $html['TextCorrection'] = '
    <script type="text/javascript">
    <!--
      function SubmitCorrectedText()
      {
        var txtValue = document.getElementById(\'fld_txtcorr\').value;
        opener.applyCorrectedText(txtValue);
        window.close();
      }

      function CheckTextAgain()
      {
        var CharsSave = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_.!~*\'()";
        var HEX = "0123456789ABCDEF";

        var txtPlain = document.getElementById(\'fld_txtcorr\').value;
        var txtValue = \'\';
        // get each Char
        for (var i = 0; i < txtPlain.length; i++ )
        {
          // get Char at current Position
          var chck = txtPlain.charAt(i);

          // Check for a space
          if (chck == \' \')
          {
              txtValue += \'+\';
          }
          // Check if this is a Safe-Char
          else if (CharsSave.indexOf(chck) != -1)
          {
              txtValue += chck;
          }
          // all other Chars
          else
          {
            var charCode = chck.charCodeAt(0);
            // Add a Space if the Char is not allowed in UTF-8
            if (charCode > 255)
            {
              txtValue += \'+\';
            }
            else
            {
              txtValue += \'%\';
              txtValue += HEX.charAt((charCode >> 4) & 0xF);
              txtValue += HEX.charAt(charCode & 0xF);
            }
          }
        }

        var link = \'index.php?tpt=page&tpl=popup&m=[MAIN_MENU_ID]&s=[SUB_MENU_ID]&lan=[LANGUAGE]&act=correct&ctext=\' + txtValue;
        window.location.href = link.replace(/&amp;/gi,\'&\');
      }
    //-->
    </script>
    [-->CORR_WORD]<input name="dmy[CORR_ID]" id="word_[CORR_ID]" type="hidden" value="[CORR_WORD_VALUE]" />
    [--CORR_WORD]

    [-->CORR_WORD_VALUE]<div id="corr_div" style="min-width:200px;height:60px;">
      <select name="dmys[CORR_ID]" id="corsel_[CORR_ID]" style="border:1px solid rgb(0,111,153);width:150px;">[CORR_WORD_LIST]</select>&nbsp;&nbsp;
      <input type="hidden" name="dmyo[CORR_ID]" id="corori_[CORR_ID]" value="[CORR_ORIG_VALUE]" />
      <input type="text"   name="dmyv[CORR_ID]" id="corval_[CORR_ID]" style="border:1px solid rgb(0,111,153);width:200px;" value="[CORR_ORIG_VALUE]" />
      <br /><br />
      <input type="button" value="'.$LANG->getValue("","txt","admin_203").'" onClick="applyCurrentValue(\'[CORR_ID]\');"  style="width:130px;font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />&nbsp;&nbsp;
      <input type="button" value="'.$LANG->getValue("","txt","admin_204").'" onClick="showNextCorrection(\'[CORR_ID]\');" style="width:130px;font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
    </div>
    [--CORR_WORD_VALUE]

    [-->CORR_WORD_LIST]<option value="[CORR_VALUE]" onClick="applyChangeSelect(\'[CORR_ID]\',this.value);">[CORR_VALUE]</option>
    [--CORR_WORD_LIST]

    <table cellspacing="0" cellpadding="0" style="margin:10px;width:100%;table-layout:fixed;empty-cells:show;">
      <colgroup>
        <col style="width:100%;min-width:200px;" />
      </colgroup>
      <tr>
        <td style="padding:0px;text-align:center;vertical-align:top;">
          <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
            <colgroup>
              <col style="width:100%;" />
            </colgroup>
            <tr>
              <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">
                '.$LANG->getValue("","txt","admin_201").'
              </td>
            </tr>
            <tr>
              <td style="text-align:left;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">

                <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
                  <colgroup>
                    <col style="min-width:200px;" />
                  </colgroup>
                  <tr>
                    <td style="padding:3px;text-align:center;vertical-align:middle;background-color:rgb(214,230,239);border-bottom:1px solid rgb(0,111,153);">
                      <div id="corr_div" style="min-width:200px;height:60px;"></div>
                    </td>
                  </tr>
                  <tr>
                    <td style="padding:3px;text-align:center;vertical-align:middle;background-color:rgb(214,230,239);">
                      <textarea name="fld_txtcorr" id="fld_txtcorr" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);width:550px;height:250px;font-family:Arial,helvetica,sans serif;font-size:12px;padding:1px;color:rgb(0,111,153);">[TXT_CORRECTED_VALUE]</textarea>
                    </td>
                  </tr>
                  <tr>
                    <td style="padding:3px;text-align:center;vertical-align:middle;background-color:rgb(214,230,239);">
                      <input type="button" value="'.$LANG->getValue("","txt","admin_202").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="CheckTextAgain();" />
                    </td>
                  </tr>
                </table>

              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
          <input type="button" value="'.$LANG->getValue("","txt","admin_008").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="window.close();" />&nbsp;&nbsp;
          <input type="button" value="'.$LANG->getValue("","txt","admin_067").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="SubmitCorrectedText();" />
        </td>
      </tr>
    </table>

    <script type="text/javascript">
    <!--
      var correctionList = new Array([CORR_ID_LIST]);
      var currentCorrection = 0;
      var curPosition = 0;
      function applyChangeSelect(id,repl)
      {
        document.getElementById(\'corval_\' + id).value = repl;
      }

      function applyCurrentValue(id)
      {
        // get the Replacement and the Original-Word
        var repl = document.getElementById(\'corval_\' + id).value;
        var orig = document.getElementById(\'corori_\' + id).value;

        // get the text
        var fld_txt = document.getElementById(\'fld_txtcorr\').value;

        // Search for the word
        var pos = fld_txt.indexOf(orig, curPosition);
        fld_txt = fld_txt.substring(0, pos) + repl + fld_txt.substring(pos+orig.length, fld_txt.length);
        document.getElementById(\'corori_\' + id).value = repl;
        document.getElementById(\'fld_txtcorr\').value = fld_txt;
      }

      function showNextCorrection(id)
      {
        var fld_txt = document.getElementById(\'fld_txtcorr\').value;
        var orig = document.getElementById(\'corori_\' + id).value;
        var pos = fld_txt.indexOf(orig, curPosition);
        curPosition = pos+orig.length;
        showCorrection();
      }

      function showCorrection()
      {
        var cur  = correctionList[currentCorrection];
        var next = currentCorrection + 1;

        if (next <= correctionList.length)
        {
          document.getElementById(\'corr_div\').innerHTML = document.getElementById(\'word_\' + cur).value;

          if (next < correctionList.length)
            currentCorrection++;
        }
      }
      showCorrection();
    //-->
    </script>
    ';

    $html['Successful']  = '
    <script type="text/javascript">
    <!--
      var loc = \'index.php?tpl='.$template.'&tpt='.$TemplateType.'&lan='.$LANG->getLanguageName().'&m=[MAIN_MENU_ID]&s=[SUB_MENU_ID]\';
      opener.location.href = loc.replace(/&amp;/gi,\'&\');
      opener.parent.lang.location.reload();
      window.close();
    //-->
    </script>

    Close this Window if it"s not closed automaticly...';

    $html['DeleteConfirmation']  = '
    <form action="index.php" method="POST">
      <input type="hidden" name="tpl" value="'.$template.'" />
      <input type="hidden" name="tpt" value="'.$TemplateType.'" />
      <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
      <input type="hidden" name="chi" value="[CHANGE_TEXT_ID]" />
      <input type="hidden" name="txt_did" value="[CHANGE_TEXT_ID]" />
      <input type="hidden" name="act" value="[CHANGE_ACTION]" />
      <input type="hidden" name="m" value="[MAIN_MENU_ID]" />
      <input type="hidden" name="s" value="[SUB_MENU_ID]" />

      <table cellspacing="5" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
        <colgroup>
          <col style="width:100%;min-width:200px;" />
        </colgroup>
        <tr>
          <td style="padding:10px;text-align:center;vertical-align:top;">
            <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
              <colgroup>
                <col style="width:*;min-width:200px;" />
              </colgroup>
              <tr>
                <td style="padding:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(242,240,249);font-size:12px;font-weight:bold;">
                  '.$LANG->getValue("","txt","admin_079").'
                </td>
              </tr>
              <tr>
                <td style="padding:2px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);">
                  <span style="color:rgb(180,80,80);font-size:12px;font-weight:bold;">'.$LANG->getValue("","txt","admin_080").'</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding:10px;text-align:right;vertical-align:middle;">
            <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="window.close();" />&nbsp;&nbsp;
            <input type="submit" value="'.$LANG->getValue("","txt","admin_081").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
          </td>
        </tr>
      </table>
    </form>';

    return $html;
  }

  function ChangeMenu()
  {
    global $LANG,$template,$TemplateType;

    $html['MainScreen']  = '<form action="index.php" method="POST">
    <input type="hidden" name="tpl" value="'.$template.'" />
    <input type="hidden" name="tpt" value="'.$TemplateType.'" />
    <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
    <input type="hidden" name="chi" value="[CHANGE_ID]" />
    <input type="hidden" name="act" value="[CHANGE_ACTION]" />
    <input type="hidden" name="ada" value="[ADMIN_ACTION]" />
    <input type="hidden" name="m"   value="[MAIN_MENU_ID]" />
    <input type="hidden" name="s"   value="[SUB_MENU_ID]" />

    <script type="text/javascript">
    <!--
      function doCloseWindow()
      {
        var loc = \'index.php?tpl=submenu&tpt='.$TemplateType.'&lan='.$LANG->getLanguageName().'&m=[MAIN_MENU_ID]&s=[SUB_MENU_ID]&mck=[SUB_MENU_ID]\';
        opener.location.href = loc.replace(/&amp;/gi,\'&\');
        window.close();
      }
    //-->
    </script>

    <table cellspacing="0" cellpadding="0" style="margin:10px;width:100%;table-layout:fixed;empty-cells:show;">
      <colgroup>
        <col style="width:100%;min-width:200px;" />
      </colgroup>
      <tr>
        <td style="padding:0px;text-align:center;vertical-align:top;">
          <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
          <colgroup>
            <col style="width:100%;" />
          </colgroup>
          <tr>
            <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">
            [TITLE]
            </td>
          </tr>
          <tr>
            <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
              <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
              <colgroup>
                <col style="width:150px;min-width:150px;" />
                <col style="min-width:200px;" />
              </colgroup>
                [INPUT_FIELDS]
              </table>
            </td>
          </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
          <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="doCloseWindow();" />&nbsp;&nbsp;
          <input type="submit" value="'.$LANG->getValue("","txt","admin_067").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
        </td>
      </tr>
    </table>

    </form>';

    $html['NewMenu']  = '
    <tr>
      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
        '.$LANG->getValue("","txt","admin_084").':
      </td>
      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
        <input type="text" name="men_text" value="[MENU_TEXT]" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);width:350px;font-family:Arial,helvetica,sans serif;font-size:12px;padding:1px;color:rgb(0,111,153);" />
      </td>
    </tr>
    <tr>
      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
        '.$LANG->getValue("","txt","admin_200").':
      </td>
      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
        <input type="text" name="men_shortlnk" value="[MENU_SHORTLNK]" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);width:350px;font-family:Arial,helvetica,sans serif;font-size:12px;padding:1px;color:rgb(0,111,153);" />
      </td>
    </tr>
    <tr>
      <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
        '.$LANG->getValue("","txt","admin_085").':
      </td>
      <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
        <select name="men_type" style="border:1px solid rgb(0,111,153);background-color:rgb(245,250,255);width:350px;font-family:Arial,helvetica,sans serif;font-size:12px;padding:0px;color:rgb(0,111,153);">
          [-->MENU_TYPE]<option value="[MENU_TYPE_ID]" style="background-color:rgb(245,250,255);">[MENU_TYPE_TEXT]</option>[--MENU_TYPE]
        </select>
      </td>
    </tr>
    ';

    $html['NotExistentMenu']  = '
    <table cellspacing="0" cellpadding="0" style="margin:10px;width:100%;table-layout:fixed;empty-cells:show;">
      <colgroup>
        <col style="width:100%;min-width:200px;" />
      </colgroup>
      <tr>
        <td style="padding:0px;text-align:center;vertical-align:top;">
          <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
            <colgroup>
              <col style="width:100%;" />
            </colgroup>
            <tr>
              <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">
                '.$LANG->getValue("","txt","admin_086").'
              </td>
            </tr>
            <tr>
              <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(180,50,50);font-weight:bold;font-size:12px;">
                '.$LANG->getValue("","txt","admin_087").'
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>';

    $html['Successful']  = '
    <script type="text/javascript">
    <!--
      var loc = \'index.php?tpl=submenu&tpt='.$TemplateType.'&lan='.$LANG->getLanguageName().'&m=[MAIN_MENU_ID]&s=[SUB_MENU_ID]&mck=[SUB_MENU_ID]\';
      opener.location.href = loc.replace(/&amp;/gi,\'&\');
      var loc = \'index.php?tpl='.$template.'&tpt=page&lan='.$LANG->getLanguageName().'&m=[MAIN_MENU_ID]&s=[SUB_MENU_ID]\';
      opener.parent.docu.location.href = loc.replace(/&amp;/gi,\'&\');
      window.close();
    //-->
    </script>

    Close this Window if it"s not closed automaticly...';

    $html['DeleteConfirmation']  = '<form action="index.php" method="POST">
      <input type="hidden" name="tpl" value="'.$template.'" />
      <input type="hidden" name="tpt" value="'.$TemplateType.'" />
      <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
      <input type="hidden" name="chi" value="[CHANGE_ID]" />
      <input type="hidden" name="men_did" value="[CHANGE_ID]" />
      <input type="hidden" name="act" value="[CHANGE_ACTION]" />
      <input type="hidden" name="ada" value="[ADMIN_ACTION]" />
      <input type="hidden" name="m" value="[MAIN_MENU_ID]" />
      <input type="hidden" name="s" value="[SUB_MENU_ID]" />
      <table cellspacing="5" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
        <colgroup>
          <col style="width:100%;min-width:200px;" />
        </colgroup>
        <tr>
          <td style="padding:10px;text-align:center;vertical-align:top;">
            <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
              <colgroup>
                <col style="width:*;min-width:200px;" />
              </colgroup>
              <tr>
                <td style="padding:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(242,240,249);font-size:12px;font-weight:bold;">
                  '.$LANG->getValue("","txt","admin_089").'
                </td>
              </tr>
              <tr>
                <td style="padding:2px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);">
                  <span style="color:rgb(180,80,80);font-size:12px;font-weight:bold;">'.$LANG->getValue("","txt","admin_090").'</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding:10px;text-align:right;vertical-align:middle;">
            <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="window.close();" />&nbsp;&nbsp;
            <input type="submit" value="'.$LANG->getValue("","txt","admin_081").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
          </td>
        </tr>
      </table>
    </form>';

    return $html;
  }

  function ChangeSection()
  {
    global $LANG,$template,$TemplateType;

    $html['MainScreen']  = '<form name="FORM" action="index.php" method="POST">
    <input type="hidden" name="tpl" value="'.$template.'" />
    <input type="hidden" name="tpt" value="'.$TemplateType.'" />
    <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
    <input type="hidden" name="chi" value="[CHANGE_TEXT_ID]" />
    <input type="hidden" name="act" value="[CHANGE_ACTION]" />
    <input type="hidden" name="m" value="[MAIN_MENU_ID]" />
    <input type="hidden" name="s" value="[SUB_MENU_ID]" />

    <script type="text/javascript">
    <!--
    function ChangeSectionBackground(obj,act,col)
    {
    if (act == \'over\')
    {
    obj.style.cursor = \'pointer\';
    obj.style.border = \'1px solid rgb(0,111,153)\';
    obj.style.backgroundColor = \'rgb(114,191,236)\';
    }
    else
    {
    obj.style.cursor = \'normal\';
    obj.style.border = \'1px solid \'+col;
    obj.style.backgroundColor = col;
    }
    }

    function ChangeSectionLocation(id)
    {
    document.getElementById(\'txt_text\').value = id;
    document.FORM.submit();
    }
    //-->
    </script>

    <table cellspacing="0" cellpadding="0" style="margin:10px;width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:0px;text-align:center;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;" />
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">
    [CHOOSE_SECTION_TITLE]
    </td>
    </tr>
    <tr>
    <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:150px;min-width:150px;" />
    <col style="min-width:200px;" />
    </colgroup>
    <tr> <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
    [RECURSIVE_SECTION_TEXT]:
    </td> <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <input type="radio" name="txt_picfile" value="0" [CHECK:txt_picfile:0] />&nbsp;'.$LANG->getValue("","txt","admin_026").'&nbsp;&nbsp;&nbsp;
    <input type="radio" name="txt_picfile" value="1" [CHECK:txt_picfile:1] />&nbsp;'.$LANG->getValue("","txt","admin_025").'
    </td>
    </tr>
    <tr> <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
    [CHOOSE_SECTION_TEXT]:
    </td> <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <input type="hidden" id="txt_text" name="txt_text" value="[TEXT_TEXT]" />
    [-->SECTION_LIST]
    <table cellspacing="2" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;" />
    </colgroup>
    [-->SECTION_ENTRY]
    <tr>
    <td style="text-align:left;vetical-align:middle;border:1px solid [ROW_COLOR];padding:2px;background-color:[ROW_COLOR];" onMouseOver="ChangeSectionBackground(this,\'over\');" onMouseOut="ChangeSectionBackground(this,\'out\',\'[ROW_COLOR]\');" onClick="ChangeSectionLocation(\'[SECTION_ID]\');">
    <img src="images/design/blank.gif" style="width:[IMAGE_WIDTH]px;height:5px;" alt="blank" />
    <img src="images/design/dataset.gif" style="width:16px;height:16px;vertical-align:bottom;" alt="arrow" />[SECTION_NAME]
    </td>
    </tr>
    [--SECTION_ENTRY]
    </table>
    [--SECTION_LIST]
    </td> </tr>
    </table>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>

    </form>';

    return $html;
  }

  function NewsManagement()
  {
    global $LANG,$postId;
    $html['NewsList']  = '
    <script type="text/javascript">
    <!--

    var IE = document.all?true:false;
    if (!IE) document.captureEvents(Event.MOUSEDOWN);
    document.onmousedown = GetMousePos;

    var MousePosX = 0;
    var MousePosY = 0;

    function ChangeImageBackground(Iobj,Tobj,act)
    {
    if (act == \'over\')
    {
    Iobj.style.border = \'3px solid rgb(0,111,153)\';
    document.getElementById(Tobj).style.backgroundColor = \'rgb(114,191,236)\';
    document.getElementById(Tobj).style.border = \'1px solid rgb(0,111,153)\';
    document.getElementById(Tobj).style.cursor = \'pointer\';
    }
    else
    {
    Iobj.style.border = \'3px solid rgb(114,191,236)\';
    document.getElementById(Tobj).style.backgroundColor = \'rgb(224,240,249)\';
    document.getElementById(Tobj).style.border = \'1px solid rgb(224,240,249)\';
    document.getElementById(Tobj).style.cursor = \'normal\';
    }
    }

    function ChooseThisNews(id,source)
    {
    opener.document.getElementById(\'txt_picfile\').value = id;
    opener.document.getElementById(\'img_picfile\').src = source;
    window.close();
    }

    function ChangeNews(id)
    {
    var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
    var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=2&i=\'+id;
    var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }

    function NewsDelete(id)
    {
    var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
    var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=3&i=\'+id;
    var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }

    var ImgMv = new Array();
    function NewsSelect(id,rmsel)
    {
      var idfound = false;
      var tmparr = new Array();
      for (i = 0; i < ImgMv.length; i++)
      {
        if (ImgMv[i] == id)
          idfound = true;
        if (((ImgMv[i] != id)) && (ImgMv[i] != \'\') && (ImgMv[i] != \'0\'))
          var tmp = tmparr.push(ImgMv[i]);
      }
      if (!idfound)
      {
        document.getElementById(\'divsel_\'+id).style.color = \'rgb(255,50,50)\';
        document.getElementById(\'mvimg_\'+id).src = \'images/design/admin/image_move_selected.gif\';
      }
      else
      {
        document.getElementById(\'divsel_\'+id).style.color = \'rgb(0,111,153)\';
        document.getElementById(\'mvimg_\'+id).src = \'images/design/admin/image_move.gif\';
      }
      ImgMv = tmparr;
      if ((!idfound) || (!rmsel))
        var tmp = ImgMv.push(id);

      document.getElementById(\'div_\'+id).style.visibility = \'hidden\';
    }

    function NewsMove(id)
    {
      NewsSelect(id,false);
      var ImgId = ImgMv.join(\',\');
      var prop = \'width=600,height=500,top=\'+((screen.height-600)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
      var link = \'index.php?tpl=popup&tpt=page&sec=[SECTION_ID]&adm=[ADMIN_SECTION]&ada=5&i=\'+ImgId;
      var image = window.open(link.replace(/&amp;/gi,\'&\'),\'\',prop);
    }

    function NewsMoveShow(id)
    {
      var opt = document.getElementById(\'div_\'+id);
      if (opt.style.visibility == \'hidden\')
      {
        opt.style.top = (MousePosY+2)+\'px\';
        if ((MousePosX+2) > (document.getElementsByTagName("body")[0].offsetWidth-150))
          opt.style.left = (MousePosX+2-150)+\'px\';
        else
          opt.style.left = (MousePosX+2)+\'px\';
        opt.style.visibility = \'visible\';
      }
      else
      {
        opt.style.visibility = \'hidden\';
      }
    }

    function GetMousePos(e)
    {
      if (IE)
      {
        MousePosX = event.clientX + document.body.scrollLeft;
        MousePosY = event.clientY + document.body.scrollTop
      }
      else
      {
        MousePosX = e.pageX
        MousePosY = e.pageY
      }
      if (MousePosX < 0) MousePosX = 0;
      if (MousePosY < 0) MousePosY = 0;

      return true;
    }

    //-->
    </script>
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <coolgroup>
    <col style="min-width:50px;" />
    </colgroup>
    [-->NEWS_ENTRY]
    <tr>
    [NEWS]
    </tr>
    <tr><td colspan="1" style="height:15px;"></td></tr>
    [--NEWS_ENTRY]
    </table>';

    $html['NewsEntry']  = '<td id="td_[NEWS_ID]" style="padding:3px;background-color:rgb(224,240,249);border:1px solid rgb(224,240,249);text-align:right;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;border:3px solid rgb(114,191,236);">
    <colgroup>
    <col style="min-width:80px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;border:0px solid black;border-bottom:1px solid rgb(114,191,236);background-color:rgb(245,250,255);text-align:left;vertical-align:middle;">
    <span style="color:rgb(0,111,153);font-weight:bold;font-size:12px;">"[NEWS_TITLE]"</span>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">&#91;</span><span style="font-weight:normal;font-size:10px;">&nbsp;[NEWS_DATE]&nbsp;</span><span style="color:rgb(0,111,153);font-weight:bold;font-size:10px;">&#93;</span>
    </td>
    </tr><tr>
    <td style="height:56px;text-align:left;vertical-align:top;padding:10px;padding-top:5px;border:0px solid black;background-color:rgb(245,250,255);">
    [NEWS_TEXT]
    </td>
    </tr>
    </table>
    <br />
    <img src="images/design/admin/image_edit.gif" alt="edit texts" title="edit news" style="width:16px;height:16px;border:0px solid black;vertical-align:bottom;" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="ChangeNews(\'[NEWS_ID]\');" />
    &nbsp;&nbsp;&nbsp;&nbsp;
    <img src="images/design/admin/image_delete.gif" alt"delete file" title="delete news" style="width:16px;height:16px;border:0px solid black;vertical-align:bottom;" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="NewsDelete(\'[NEWS_ID]\');" />
    &nbsp;&nbsp;&nbsp;&nbsp;
    <img id="mvimg_[NEWS_ID]" src="images/design/admin/image_move.gif" alt="move file" title="move file to other section" style="width:16px;height:16px;border:0px solid black;vertical-align:bottom;" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="NewsMoveShow(\'[NEWS_ID]\');" />
    <div id="div_[NEWS_ID]" style="padding:3px;visibility:hidden;position:absolute;top:10px;left:10px;width:150px;height:40px;border:1px solid rgb(0,111,153);background-color:rgb(230,240,250);text-align:left;">
    <div id="divsel_[NEWS_ID]" style="padding:2px;font-weight:bold;color:rgb(0,111,153);" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="NewsSelect(\'[NEWS_ID]\',true);">-&gt;&nbsp;Select</div>
    <div style="padding:2px;font-weight:bold;color:rgb(0,111,153);" onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\';" onClick="NewsMove(\'[NEWS_ID]\');">-&gt;&nbsp;Move Selected</div>
    </div>
    </td>';

    $html['NewsDelete']  = '<form action="index.php" method="POST">
    <input type="hidden" name="tpl" value="popup" />
    <input type="hidden" name="tpt" value="page" />
    <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
    <input type="hidden" name="new_did" value="[NEWS_DELETE]" />
    <input type="hidden" name="i" value="[POST_ID]" />
    <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
    <input type="hidden" name="ada" value="[ADMIN_ACTION]" />

    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:0px;text-align:center;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;" />
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_096").'</td>
    </tr>
    <tr>
    <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:*;min-width:150px;" />
    <col style="width:*;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
    '.$LANG->getValue("","txt","admin_097").':
    </td>
    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <span style="font-weight:bold;color:rgb(255,100,100);"><img src="[IMAGE_SRC_SMALL]" style="width:[IMAGE_WIDTH_SMALL]px;height:[IMAGE_HEIGHT_SMALL];" /></span>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
    <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
    <input type="submit" value="'.$LANG->getValue("","txt","admin_096").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
    </td>
    </tr>
    </table>
    </form>';

    $html['NewsMove']  = '<form action="index.php" method="POST">
    <input type="hidden" name="tpl" value="popup" />
    <input type="hidden" name="tpt" value="page" />
    <input type="hidden" name="lan" value="'.$LANG->getLanguageName().'" />
    <input type="hidden" name="new_nmv" value="[NEWS_MOVE]" />
    <input type="hidden" name="sec" value="[SECTION_ID]" />
    <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
    <input type="hidden" name="ada" value="[ADMIN_ACTION]" />

    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:0px;text-align:center;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;" />
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:middle;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;font-size:12px;">'.$LANG->getValue("","txt","admin_099").'</td>
    </tr>
    <tr>
    <td style="text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    <table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:*;min-width:150px;" />
    <col style="width:350px;min-width:350px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:top;border:0px solid rgb(0,111,153);background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;font-size:10px;">
    '.$LANG->getValue("","txt","admin_098").':
    </td>
    <td style="padding:2px;text-align:left;vertical-align:middle;border:0px solid rgb(0,111,153);border-left-width:1px;background-color:rgb(224,240,249);color:rgb(0,111,153);font-weight:normal;font-size:10px;">
    [SECTION_CHOOSER]
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td colspan="2" style="padding-top:10px;padding-bottom:10px;padding-right:10px;text-align:right;vertical-align:middle;">
    <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
    <input type="submit" value="'.$LANG->getValue("","txt","admin_099").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
    </td>
    </tr>
    </table>
    </form>';

    $html['NewsChange']  = '<form action="index.php" method="POST">
    <input type="hidden" name="tpl" value="popup" />
    <input type="hidden" name="tpt" value="page" />
    <input type="hidden" name="i" value="'.$postId.'" />
    <input type="hidden" name="adm" value="[ADMIN_SECTION]" />
    <input type="hidden" name="ada" value="[ADMIN_ACTION]" />
    <input type="hidden" name="lan" value="[LANGUAGE]" />

    <table cellspacing="5" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:100%;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:10px;text-align:center;vertical-align:top;">
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:*;min-width:200px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(0,111,153);color:rgb(242,240,249);font-size:12px;font-weight:bold;">
    '.$LANG->getValue("","txt","admin_102").'
    </td>
    </tr>
    <tr>
    <td style="padding:2px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);">
    [REPLACE:NewsText]
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td colspan="2" style="padding:10px;text-align:right;vertical-align:middle;">
    <input type="button" value="'.$LANG->getValue("","txt","admin_020").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" onClick="opener.location.reload();window.close();" />&nbsp;&nbsp;
    <input type="submit" value="'.$LANG->getValue("","txt","admin_007").'" style="font-size:10px;font-weight:bold;color:rgb(0,81,123);border:1px solid rgb(0,111,153);background-color:rgb(114,191,236);padding:2px;padding-left:5px;padding-right:5px;" />
    </td>
    </tr>
    </table></form>';

    $html['NewsTextFields']  = '<table cellspacing="3" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
    <colgroup>
    <col style="width:150px;min-width:10px;" />
    <col style="min-width:200px;" />
    </colgroup>
    <tr><td style="text-align:left;vertical-align:top;font-weight:bold;padding-left:10px;padding-top:3px;">
    '.$LANG->getValue("","txt","admin_103").':
    </td><td style="text-align:left;">
    <input type="text" name="new_date" value="[NEWS_DATE]" style="width:100%;font-family:Helvetica, sans serif;font-size:10px;font-weight:normal;color:rgb(0,111,153);background-color:rgb(245,250,255);border:1px solid rgb(114,191,236);padding:2px;" />
    </td></tr>
    <tr><td style="text-align:left;vertical-align:top;font-weight:bold;padding-left:10px;padding-top:3px;">
    '.$LANG->getValue("","txt","admin_100").':
    </td><td style="text-align:left;">
    <input type="hidden" name="new_id" value="[NEWS_ID]" />
    <input type="hidden" name="new_section" value="[SECTION_ID]" />
    <input type="text" name="new_title" value="[NEWS_TITLE]" style="width:100%;font-family:Helvetica, sans serif;font-size:10px;font-weight:normal;color:rgb(0,111,153);background-color:rgb(245,250,255);border:1px solid rgb(114,191,236);padding:2px;" />
    </td></tr>
    <tr><td style="text-align:left;vertical-align:top;font-weight:bold;padding-left:10px;padding-top:3px;">
    '.$LANG->getValue("","txt","admin_101").':
    </td><td style="text-align:left;">
    <textarea name="new_text" style="width:100%;height:150px;font-family:Helvetica, sans serif;font-size:10px;font-weight:normal;color:rgb(0,111,153);background-color:rgb(245,250,255);border:1px solid rgb(114,191,236);padding:2px;">[NEWS_TEXT]</textarea>
    </td></tr>
    </table>';

    return $html;
  }

  function StatisticManagement()
  {
    global $LANG,$postId;

    $html['MainScreen']  = '
    <script type="text/javascript">
    <!--
    function ChangeCalendarBackground(obj,act,col)
    {
    if (act == \'over\')
    {
    obj.style.cursor = \'pointer\';
    obj.style.backgroundColor = \'rgb(114,191,236)\';
    }
    else
    {
    obj.style.cursor = \'normal\';
    obj.style.backgroundColor = col;
    }
    }

    function ChangeCalendarLocation(lnk)
    {
    window.location.href = \'index.php?\' + lnk;
    }

    //-->
    </script>

    <table cellspacing="2" cellpadding="0" style="padding:10px;table-layout:fixed;empty-cells:show;width:100%;">
    <colgroup>
    <col style="min-width:150px;" />
    </colgroup>
    <tr>
    <td style="border-bottom:1px solid rgb(0,111,153);border-top:1px solid rgb(0,111,153);padding:5px;text-align:center;font-size:15px;font-weight:bold;color:rgb(0,111,153);line-height:20px;background-color:rgb(245,250,255);">[ADMIN_SECTION_NAME]</td>
    </tr>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:top;">
    [CALENDAR_FUNCTIONS]
    </td>
    </tr>
    <tr>
    <td style="padding:1px;border-top:1px solid rgb(0,111,153);border-bottom:2px solid rgb(0,111,153);height:5px;"></td>
    </tr>
    <tr>
    <td style="padding:5px;text-align:center;font-size:15px;font-weight:bold;color:rgb(224,240,249);line-height:20px;background-color:rgb(0,111,153);">[CURRENT_STATISTIC]</td>
    </tr>
    <tr>
    <td style="padding:1px;border-top:2px solid rgb(0,111,153);border-bottom:1px solid rgb(0,111,153);height:5px;"></td>
    </tr>
    <tr>
    <td style="padding:5px;padding-left:10px;text-align:center;vertical-align:middle;">
    [STATISTIC_LISTS]
    </td>
    </tr>
    </table>';

    $html['CalendarFunctions']  = '
    <table cellspacing="0" cellpadding="0" style="table-layout:fixed;empty-cells:show;width:100%;">
    <colgroup>
    <col style="min-width:100px;width:110px;" />
    <col style="min-width:100px;" />
    <col style="min-width:100px;" />
    </colgroup>
    <tr>
    <td style="padding:5px;padding-right:15px;text-align:center;vertical-align:top;border-bottom:0px solid rgb(0,111,153);">
    [CALENDAR_YEAR]
    </td>
    <td style="padding:5px;text-align:center;vertical-align:top;border-bottom:0px solid rgb(0,111,153);">
    [CALENDAR_MONTH]
    </td>
    <td style="padding:5px;padding-left:15px;text-align:center;vertical-align:top;border-bottom:0px solid rgb(0,111,153);">
    [CALENDAR_DAY]
    </td>
    </tr>
    </table>';

    $html['CalendarYear']  = '
    <table cellspacing="0" cellpadding="0" style="border:2px solid rgb(0,111,153);table-layout:fixed;empty-cells:show;width:100%;">
    <colgroup>
    <col style="min-width:10px;width:20px;" />
    <col style="min-width:30px;" />
    <col style="min-width:10px;width:20px;" />
    </colgroup>
    <tr>
    <td colspan="3" style="font-weight:bold;font-size:12px;color:rgb(240,250,255);background-color:rgb(0,111,153);padding:2px;border:2px solid rgb(0,111,153);">'.$LANG->getValue("","txt","admin_111").'</td>
    </tr>
    <tr>
    <td colspan="3" style="background-color:rgb(240,250,255);padding:2px;padding-top:5px;padding-bottom:5px;border-bottom:2px solid rgb(0,111,153);" onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(240,250,255)\');" onCLick="ChangeCalendarLocation(\'[NOW_LINK]\')">
    <span style="font-weight:bold;">'.$LANG->getValue("","txt","admin_106").'</span>
    </td>
    </tr>
    <tr>
    <td style="background-color:rgb(240,250,255);padding:2px;padding-top:5px;padding-bottom:5px;" onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(240,250,255)\');" onCLick="ChangeCalendarLocation(\'[LAST_YEAR_LINK]\')">
    <span style="font-weight:bold;">&lt;&lt;</span>
    </td>
    <td style="background-color:rgb(240,250,255);padding:2px;padding-top:5px;padding-bottom:5px;border-left:1px solid rgb(0,111,153);border-right:1px solid rgb(0,111,153);" onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(240,250,255)\');" onCLick="ChangeCalendarLocation(\'[CURRENT_YEAR_LINK]\')">
    <span style="font-weight:normal;">[CURRENT_YEAR_NAME]</span>
    </td>
    <td style="background-color:rgb(240,250,255);padding:2px;padding-top:5px;padding-bottom:5px;" onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(240,250,255)\');" onCLick="ChangeCalendarLocation(\'[NEXT_YEAR_LINK]\')">
    <span style="font-weight:bold;">&gt;&gt;</span>
    </td>
    </tr>
    </table>';

    $html['CalendarMonth']  = '
    <table cellspacing="0" cellpadding="0" style="border:2px solid rgb(0,111,153);table-layout:fixed;empty-cells:show;width:100%;">
    <colgroup>
    <col style="min-width:30px;" />
    <col style="min-width:30px;" />
    <col style="min-width:30px;" />
    </colgroup>
    <tr>
    <td colspan="3" style="font-weight:bold;font-size:12px;color:rgb(240,250,255);background-color:rgb(0,111,153);padding:2px;border:2px solid rgb(0,111,153);">'.$LANG->getValue("","txt","admin_112").'</td>
    </tr>
    [-->MONTH_LIST]
    <tr>
    [-->MONTH_NORMAL]<td style="background-color:rgb(240,250,255);padding:2px;padding-top:3px;padding-bottom:3px;" onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(240,250,255)\');" onCLick="ChangeCalendarLocation(\'[MONTH_LINK]\')"><span style="font-weight:normal;text-decoration:none;">[MONTH_NAME]</span></td> [--MONTH_NORMAL]
    [-->MONTH_CURRENT]<td style="background-color:rgb(161,234,195);padding:2px;padding-top:3px;padding-bottom:3px;" onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(161,234,195)\');" onCLick="ChangeCalendarLocation(\'[MONTH_LINK]\')"><span style="font-weight:normal;text-decoration:none;">[MONTH_NAME]</span></td> [--MONTH_CURRENT]
    [-->MONTH_SELECTED]<td style="background-color:rgb(240,250,255);padding:2px;padding-top:3px;padding-bottom:3px;" onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(240,250,255)\');" onCLick="ChangeCalendarLocation(\'[MONTH_LINK]\')"><span style="font-weight:bold;text-decoration:underline;">[MONTH_NAME]</span></td> [--MONTH_SELECTED]
    </tr>
    [--MONTH_LIST]
    </table>';

    $html['CalendarDaylist']  = '
    <table cellspacing="0" cellpadding="0" style="border:2px solid rgb(0,111,153);table-layout:fixed;empty-cells:show;width:100%;">
    <colgroup>
    <col style="min-width:20px;" />
    <col style="min-width:20px;" />
    <col style="min-width:20px;" />
    <col style="min-width:20px;" />
    <col style="min-width:20px;" />
    <col style="min-width:20px;" />
    <col style="min-width:20px;" />
    </colgroup>
    <tr>
    <td colspan="7" style="font-weight:bold;font-size:12px;color:rgb(240,250,255);background-color:rgb(0,111,153);padding:2px;border:2px solid rgb(0,111,153);">'.$LANG->getValue("","txt","admin_113").'</td>
    </tr>
    <tr>
    <td style="background-color:rgb(240,250,255);text-align:center;font-weight:bold;border-bottom:1px solid rgb(0,111,153);padding-top:2px;padding-bottom:2px;">mon</td>
    <td style="background-color:rgb(240,250,255);text-align:center;font-weight:bold;border-bottom:1px solid rgb(0,111,153);padding-top:2px;padding-bottom:2px;">tue</td>
    <td style="background-color:rgb(240,250,255);text-align:center;font-weight:bold;border-bottom:1px solid rgb(0,111,153);padding-top:2px;padding-bottom:2px;">wed</td>
    <td style="background-color:rgb(240,250,255);text-align:center;font-weight:bold;border-bottom:1px solid rgb(0,111,153);padding-top:2px;padding-bottom:2px;">thu</td>
    <td style="background-color:rgb(240,250,255);text-align:center;font-weight:bold;border-bottom:1px solid rgb(0,111,153);padding-top:2px;padding-bottom:2px;">fri</td>
    <td style="background-color:rgb(200,230,235);text-align:center;font-weight:bold;border-bottom:1px solid rgb(0,111,153);padding-top:2px;padding-bottom:2px;">sat</td>
    <td style="background-color:rgb(190,220,225);text-align:center;font-weight:bold;border-bottom:1px solid rgb(0,111,153);padding-top:2px;padding-bottom:2px;">sun</td>
    </tr>
    [-->DAY_LIST]
    <tr>
    [-->DAY_NORMAL]<td style="background-color:rgb(240,250,255);text-align:center;padding:2px;"   onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(240,250,255)\');" onCLick="ChangeCalendarLocation(\'[DAY_LINK]\');">[DAY_NUMBER]</td> [--DAY_NORMAL]
    [-->DAY_TODAY]<td style="background-color:rgb(161,234,195);text-align:center;padding:2px;"    onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(161,234,195)\');" onCLick="ChangeCalendarLocation(\'[DAY_LINK]\');">[DAY_NUMBER]</td> [--DAY_TODAY]
    [-->DAY_SATURDAY]<td style="background-color:rgb(200,230,235);text-align:center;padding:2px;" onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(200,230,235)\');" onCLick="ChangeCalendarLocation(\'[DAY_LINK]\');">[DAY_NUMBER]</td> [--DAY_SATURDAY]
    [-->DAY_SUNDAY]<td style="background-color:rgb(190,220,225);text-align:center;padding:2px;"   onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(190,220,225)\');" onCLick="ChangeCalendarLocation(\'[DAY_LINK]\');">[DAY_NUMBER]</td> [--DAY_SUNDAY]
    [-->TEXT_ACTIVE]<span style="color:rgb(0,0,0);">[TEXT]</span>[--TEXT_ACTIVE]
    [-->TEXT_INACTIVE]<span style="color:rgb(180,180,180);">[TEXT]</span>[--TEXT_INACTIVE]
    [-->TEXT_SELECTED]<span style="font-weight:bold;text-decoration:underline;color:rgb(0,0,0);">[TEXT]</span>[--TEXT_SELECTED]
    </tr>
    [--DAY_LIST]
    </table>';

    $html['MainStatistic']  = '
    <table cellspacing="3" cellpadding="0" style="table-layout:fixed;empty-cells:show;width:100%;">
    <colgroup>
    <col style="min-width:60px;" />
    <col style="min-width:60px;" />
    <col style="min-width:60px;" />
    '.
//    <col style="min-width:60px;" />
//    <col style="min-width:60px;" />
//    <col style="min-width:60px;" />
//    <col style="min-width:60px;" />
    $this->BlankComment.'
    </colgroup>
    <tr>
    <td style="padding:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(240,250,255);font-weight:bold;color:rgb(0,111,153);font-size:11px;" onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(240,250,255)\');" onClick="ChangeCalendarLocation(\'[STATISTIC_PRODUCT_VIEW]\');">'.$LANG->getValue("","txt","admin_114").'</td>
    <td style="padding:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(240,250,255);font-weight:bold;color:rgb(0,111,153);font-size:11px;" onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(240,250,255)\');" onClick="ChangeCalendarLocation(\'[STATISTIC_COUNTRY_VIEW]\');">'.$LANG->getValue("","txt","admin_115").'</td>
    <td style="padding:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(240,250,255);font-weight:bold;color:rgb(0,111,153);font-size:11px;" onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(240,250,255)\');" onClick="ChangeCalendarLocation(\'[STATISTIC_BROWSER_VIEW]\');">'.$LANG->getValue("","txt","admin_116").'</td>
    '.
//    <td style="padding:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(240,250,255);font-weight:bold;color:rgb(0,111,153);font-size:11px;" onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(240,250,255)\');" onClick="ChangeCalendarLocation(\'[STATISTIC__VIEW]\');">'.$LANG->getValue("","txt","admin_117").'</td>
//    <td style="padding:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(240,250,255);font-weight:bold;color:rgb(0,111,153);font-size:11px;" onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(240,250,255)\');" onClick="ChangeCalendarLocation(\'[STATISTIC__VIEW]\');">'.$LANG->getValue("","txt","admin_118").'</td>
//    <td style="padding:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(240,250,255);font-weight:bold;color:rgb(0,111,153);font-size:11px;" onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(240,250,255)\');" onClick="ChangeCalendarLocation(\'[STATISTIC__VIEW]\');">'.$LANG->getValue("","txt","admin_119").'</td>
//    <td style="padding:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);background-color:rgb(240,250,255);font-weight:bold;color:rgb(0,111,153);font-size:11px;" onMouseOver="ChangeCalendarBackground(this,\'over\',\'\');" onMouseOut="ChangeCalendarBackground(this,\'out\',\'rgb(240,250,255)\');" onClick="ChangeCalendarLocation(\'[STATISTIC__VIEW]\');">'.$LANG->getValue("","txt","admin_120").'</td>
    $this->BlankComment.'
    </tr>
    <tr>
    <td colspan="3" style="padding:0px;padding-top:15px;text-align:center;vertical-align:top;">[STATISTIC]</td>
    </tr>
    </table>';

    $html['ProductStatistic']  = '
    <table cellspacing="0" cellpadding="0" style="border:2px solid rgb(0,111,153);table-layout:fixed;empty-cells:show;width:100%;">
    <colgroup>
    <col style="min-width:250px;" />
    <col style="min-width:50px;width:80px;" />
    <col style="min-width:50px;width:80px;" />
    <col style="min-width:50px;width:80px;" />
    <col style="min-width:70px;width:70px;" />
    </colgroup>
    <tr>
    <td colspan="5" style="padding:5px;text-align:center;vertical-align:middle;background-color:rgb(0,111,153);color:rgb(240,250,255);font-size:12px;font-weight:bold;">'.$LANG->getValue("","txt","admin_121").'</td>
    </tr>
    <tr>
    <td style="padding:5px;text-align:left;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-bottom:3px double rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;font-size:11px;"><a href="index.php?[STATISTIC_ORDER_LINK:product]" style="color:rgb(0,0,0);font-weight:bold;">'.$LANG->getValue("","txt","admin_122").'</a></td>
    <td style="padding:5px;text-align:center;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-bottom:3px double rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;font-size:11px;"><a href="index.php?[STATISTIC_ORDER_LINK:size]" style="color:rgb(0,0,0);font-weight:bold;">'.$LANG->getValue("","txt","admin_123").'</a></td>
    <td style="padding:5px;text-align:center;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-bottom:3px double rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;font-size:11px;"><a href="index.php?[STATISTIC_ORDER_LINK:downloads]" style="color:rgb(0,0,0);font-weight:bold;">'.$LANG->getValue("","txt","admin_125").'</a></td>
    <td style="padding:5px;text-align:center;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-bottom:3px double rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;font-size:11px;"><a href="index.php?[STATISTIC_ORDER_LINK:total]" style="color:rgb(0,0,0);font-weight:bold;">'.$LANG->getValue("","txt","admin_124").'</a></td>
    <td style="padding:5px;text-align:center;vertical-align:middle;border-right:0px solid rgb(0,111,153);border-bottom:3px double rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;font-size:11px;"><a href="index.php?[STATISTIC_ORDER_LINK:percent]" style="color:rgb(0,0,0);font-weight:bold;">'.$LANG->getValue("","txt","admin_126").'</a></td>
    </tr>
    [-->PRODUCT_ENTRY_ONE]<tr>
    <td style="padding:5px;padding-left:15px;text-align:left;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(224,240,249);">[FILE_NAME]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(224,240,249);">[FILE_SIZE]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(224,240,249);">[TOTAL_DOWNLOADS]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(224,240,249);">[TOTAL_SIZE]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:0px solid rgb(0,111,153);background-color:rgb(224,240,249);">[TOTAL_PERCENT]</td>
    </tr> [--PRODUCT_ENTRY_ONE]
    [-->PRODUCT_ENTRY_TWO]<tr>
    <td style="padding:5px;padding-left:15px;text-align:left;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(214,230,239);">[FILE_NAME]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(214,230,239);">[FILE_SIZE]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(214,230,239);">[TOTAL_DOWNLOADS]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(214,230,239);">[TOTAL_SIZE]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:0px solid rgb(0,111,153);background-color:rgb(214,230,239);">[TOTAL_PERCENT]</td>
    </tr> [--PRODUCT_ENTRY_TWO]
    [-->PRODUCT_ENTRY_TOTAL]<tr>
    <td style="padding:5px;padding-right:15px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-top:2px solid rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;font-size:11px;">'.$LANG->getValue("","txt","admin_127").'</td>
    <td colspan="2" style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-top:2px solid rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;">[TOTAL_DOWNLOADS]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-top:2px solid rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;">[TOTAL_SIZE]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:0px solid rgb(0,111,153);border-top:2px solid rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;">[TOTAL_PERCENT]</td>
    </tr> [--PRODUCT_ENTRY_TOTAL]
    </table>';

    $html['CountryStatistic']  = '
    <table cellspacing="0" cellpadding="0" style="border:2px solid rgb(0,111,153);table-layout:fixed;empty-cells:show;width:100%;">
    <colgroup>
    <col style="min-width:250px;" />
    <col style="min-width:50px;width:60px;" />
    <col style="min-width:50px;width:80px;" />
    <col style="min-width:50px;width:80px;" />
    <col style="min-width:70px;width:70px;" />
    </colgroup>
    <tr>
    <td colspan="5" style="padding:5px;text-align:center;vertical-align:middle;background-color:rgb(0,111,153);color:rgb(240,250,255);font-size:12px;font-weight:bold;">'.$LANG->getValue("","txt","admin_128").'</td>
    </tr>
    <tr>
    <td style="padding:5px;text-align:left;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-bottom:3px double rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;font-size:11px;"><a href="index.php?[STATISTIC_ORDER_LINK:country]" style="color:rgb(0,0,0);font-weight:bold;">'.$LANG->getValue("","txt","admin_129").'</a></td>
    <td style="padding:5px;text-align:center;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-bottom:3px double rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;font-size:11px;"><a href="index.php?[STATISTIC_ORDER_LINK:domain]" style="color:rgb(0,0,0);font-weight:bold;">'.$LANG->getValue("","txt","admin_130").'</a></td>
    <td style="padding:5px;text-align:center;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-bottom:3px double rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;font-size:11px;"><a href="index.php?[STATISTIC_ORDER_LINK:downloads]" style="color:rgb(0,0,0);font-weight:bold;">'.$LANG->getValue("","txt","admin_131").'</a></td>
    <td style="padding:5px;text-align:center;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-bottom:3px double rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;font-size:11px;"><a href="index.php?[STATISTIC_ORDER_LINK:total]" style="color:rgb(0,0,0);font-weight:bold;">'.$LANG->getValue("","txt","admin_132").'</a></td>
    <td style="padding:5px;text-align:center;vertical-align:middle;border-right:0px solid rgb(0,111,153);border-bottom:3px double rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;font-size:11px;"><a href="index.php?[STATISTIC_ORDER_LINK:percent]" style="color:rgb(0,0,0);font-weight:bold;">'.$LANG->getValue("","txt","admin_133").'</a></td>
    </tr>
    [-->COUNTRY_ENTRY_ONE]<tr>
    <td style="padding:5px;padding-left:15px;text-align:left;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(224,240,249);">[COUNTRY_NAME]</td>
    <td style="padding:5px;padding-left:15px;text-align:left;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(224,240,249);">[DOMAIN_NAME]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(224,240,249);">[TOTAL_DOWNLOADS]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(224,240,249);">[TOTAL_SIZE]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:0px solid rgb(0,111,153);background-color:rgb(224,240,249);">[TOTAL_PERCENT]</td>
    </tr> [--COUNTRY_ENTRY_ONE]
    [-->COUNTRY_ENTRY_TWO]<tr>
    <td style="padding:5px;padding-left:15px;text-align:left;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(214,230,239);">[COUNTRY_NAME]</td>
    <td style="padding:5px;padding-left:15px;text-align:left;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(214,230,239);">[DOMAIN_NAME]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(214,230,239);">[TOTAL_DOWNLOADS]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(214,230,239);">[TOTAL_SIZE]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:0px solid rgb(0,111,153);background-color:rgb(214,230,239);">[TOTAL_PERCENT]</td>
    </tr> [--COUNTRY_ENTRY_TWO]
    [-->COUNTRY_ENTRY_TOTAL]<tr>
    <td style="padding:5px;padding-right:15px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-top:2px solid rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;font-size:11px;">'.$LANG->getValue("","txt","admin_127").'</td>
    <td colspan="2" style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-top:2px solid rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;">[TOTAL_DOWNLOADS]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-top:2px solid rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;">[TOTAL_SIZE]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:0px solid rgb(0,111,153);border-top:2px solid rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;">[TOTAL_PERCENT]</td>
    </tr> [--COUNTRY_ENTRY_TOTAL]
    </table>';

    $html['BrowserStatistic']  = '
    <table cellspacing="0" cellpadding="0" style="border:2px solid rgb(0,111,153);table-layout:fixed;empty-cells:show;width:100%;">
    <colgroup>
    <col style="min-width:250px;" />
    <col style="min-width:50px;width:80px;" />
    <col style="min-width:70px;width:70px;" />
    </colgroup>
    <tr>
    <td colspan="3" style="padding:5px;text-align:center;vertical-align:middle;background-color:rgb(0,111,153);color:rgb(240,250,255);font-size:12px;font-weight:bold;">'.$LANG->getValue("","txt","admin_134").'</td>
    </tr>
    <tr>
    <td style="padding:5px;text-align:left;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-bottom:3px double rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;font-size:11px;"><a href="index.php?[STATISTIC_ORDER_LINK:browser]" style="color:rgb(0,0,0);font-weight:bold;">'.$LANG->getValue("","txt","admin_135").'</a></td>
    <td style="padding:5px;text-align:center;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-bottom:3px double rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;font-size:11px;"><a href="index.php?[STATISTIC_ORDER_LINK:count]" style="color:rgb(0,0,0);font-weight:bold;">'.$LANG->getValue("","txt","admin_136").'</a></td>
    <td style="padding:5px;text-align:center;vertical-align:middle;border-right:0px solid rgb(0,111,153);border-bottom:3px double rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;font-size:11px;"><a href="index.php?[STATISTIC_ORDER_LINK:percent]" style="color:rgb(0,0,0);font-weight:bold;">'.$LANG->getValue("","txt","admin_137").'</a></td>
    </tr>
    [-->BROWSER_ENTRY_ONE]<tr>
    <td style="padding:5px;padding-left:15px;text-align:left;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(224,240,249);">[BROWSER_NAME]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(224,240,249);">[BROWSER_COUNT]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:0px solid rgb(0,111,153);background-color:rgb(224,240,249);">[BROWSER_PERCENT]</td>
    </tr> [--BROWSER_ENTRY_ONE]
    [-->BROWSER_ENTRY_TWO]<tr>
    <td style="padding:5px;padding-left:15px;text-align:left;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(214,230,239);">[BROWSER_NAME]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);background-color:rgb(214,230,239);">[BROWSER_COUNT]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:0px solid rgb(0,111,153);background-color:rgb(214,230,239);">[BROWSER_PERCENT]</td>
    </tr> [--BROWSER_ENTRY_TWO]
    [-->BROWSER_ENTRY_TOTAL]<tr>
    <td style="padding:5px;padding-right:15px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-top:2px solid rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;font-size:11px;">'.$LANG->getValue("","txt","admin_127").'</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:1px solid rgb(0,111,153);border-top:2px solid rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;">[TOTAL_COUNT]</td>
    <td style="padding:5px;text-align:right;vertical-align:middle;border-right:0px solid rgb(0,111,153);border-top:2px solid rgb(0,111,153);background-color:rgb(176,212,232);font-weight:bold;">[TOTAL_PERCENT]</td>
    </tr> [--BROWSER_ENTRY_TOTAL]
    </table>';

    return $html;
  }

  function StaticSiteManagement()
  {
    global $LANG,$postId;

    $html['MainScreen']  = '
    <script type="text/javascript">
    <!--
      function ChangeCellBackground(obj,act,col)
      {
        if (act == \'over\')
        {
          obj.style.cursor = \'pointer\';
          obj.style.backgroundColor = \'rgb(114,191,236)\';
        }
        else
        {
          obj.style.cursor = \'normal\';
          obj.style.backgroundColor = col;
        }
      }

      function ChangeBackground(obj,act)
      {
        if (act == \'over\')
        {
          obj.style.cursor = \'pointer\';
          obj.style.border = \'1px solid rgb(0,111,153)\';
          obj.style.backgroundColor = \'rgb(114,191,236)\';
        }
        else
        {
          obj.style.cursor = \'normal\';
          obj.style.border = \'1px solid rgb(224,240,249)\';
          obj.style.backgroundColor = \'rgb(224,240,249)\';
        }
      }

      function ChangeLocation(loc)
      {
        if (loc != \'\')
        {
          window.location.href = loc;
        }
      }

      function OpenActionWindow(loc)
      {
        if (loc != \'\')
        {
          var prop = \'width=590,height=500,top=\'+((screen.height-500)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
          var ActWin = window.open(loc.replace(/&amp;/gi,\'&\'),\'StaticActionWindow\',prop);
        }
      }
    //-->
    </script>

    <table cellspacing="2" cellpadding="0" style="padding:10px;table-layout:fixed;empty-cells:show;width:100%;">
      <colgroup>
        <col style="min-width:150px;" />
      </colgroup>
      <tr>
        <td style="border-bottom:1px solid rgb(0,111,153);border-top:1px solid rgb(0,111,153);padding:5px;text-align:center;font-size:15px;font-weight:bold;color:rgb(0,111,153);line-height:20px;background-color:rgb(245,250,255);">[ADMIN_SECTION_NAME]</td>
      </tr>
      <tr>
        <td style="padding:5px;text-align:center;vertical-align:top;">[STATIC_FUNCTIONS]</td>
      </tr>
      <tr>
        <td style="padding:1px;border-top:1px solid rgb(0,111,153);border-bottom:2px solid rgb(0,111,153);height:5px;"></td>
      </tr>
      <tr>
        <td style="padding:5px;text-align:center;font-size:15px;font-weight:bold;color:rgb(224,240,249);line-height:20px;background-color:rgb(0,111,153);">[CURRENT_SECTION]</td>
      </tr>
      <tr>
        <td style="padding:1px;border-top:2px solid rgb(0,111,153);border-bottom:1px solid rgb(0,111,153);height:5px;"></td>
      </tr>
      <tr>
        <td style="padding:5px;padding-left:10px;text-align:center;vertical-align:middle;">[STATIC_SECTION_TEXT]</td>
      </tr>
    </table>';

    $html['ButtonBar'] = '
    <table cellspacing="2" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
      <colgroup>
        <col style="min-width:10px;" />
        <col style="width:100px;min-width:80px;" />   <!-- Main View  //-->
        <col style="width:100px;min-width:80px;" />   <!-- Create Static texts  //-->
        <col style="width:100px;min-width:80px;" />   <!-- Create Static menus  //-->
        <col style="width:100px;min-width:80px;" />   <!--   reserved  //-->
        '.
//        <col style="width:100px;min-width:80px;" />   <!-- reserved  //-->
//        <col style="width:100px;min-width:80px;" />   <!-- reserved  //-->
//        <col style="width:100px;min-width:80px;" />   <!-- reserved  //-->
        $this->BlankComment.'
        <col style="min-width:10px;" />
      </colgroup>
      <tr>
        <td></td>
        <td style="padding:2px;padding-top:5px;text-align:center;vertical-align:top;border:1px solid rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;" onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="ChangeLocation(\'index.php?tpl=blank&tpt=page&ada=0&[ADMIN_SECTION]\');">
          <img src="images/design/admin/Static_MainView.gif" alt="[SECTION_NAME:1]" title="[SECTION_NAME:1]" style="width:34px;height:34px;border:0px hidden black;" /><br />[SECTION_NAME:1]
        </td>
        <td style="padding:2px;padding-top:5px;text-align:center;vertical-align:top;border:1px solid rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;" onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="ChangeLocation(\'index.php?tpl=blank&tpt=page&ada=1&[ADMIN_SECTION]\');">
          <img src="images/design/admin/Static_Text.gif" alt="[SECTION_NAME:2]" title="[SECTION_NAME:2]" style="width:34px;height:34px;border:0px hidden black;" /><br />[SECTION_NAME:2]
        </td>
        <td style="padding:2px;padding-top:5px;text-align:center;vertical-align:top;border:1px solid rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;" onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="ChangeLocation(\'index.php?tpl=blank&tpt=page&ada=2&[ADMIN_SECTION]\');">
          <img src="images/design/admin/Static_Menu.gif" alt="[SECTION_NAME:3]" title="[SECTION_NAME:3]" style="width:34px;height:34px;border:0px hidden black;" /><br />[SECTION_NAME:3]
        </td>
        <td style="padding:2px;padding-top:5px;text-align:center;vertical-align:top;border:1px solid rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;" onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="ChangeLocation(\'index.php?tpl=blank&tpt=page&ada=3&[ADMIN_SECTION]\');">
          <img src="images/design/admin/Static_Mirror.gif" alt="[SECTION_NAME:4]" title="[SECTION_NAME:4]" style="width:34px;height:34px;border:0px hidden black;" /><br />[SECTION_NAME:4]
        </td>
        '.
//        <td style="padding:2px;padding-top:5px;text-align:center;vertical-align:top;border:1px solid rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;" onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="ChangeLocation(\'index.php?tpl=blank&tpt=page&ada=4&[ADMIN_SECTION]\');">
//          <img src="images/design/admin/Static_.gif" alt="[SECTION_NAME:5]" title="[SECTION_NAME:5]" style="width:34px;height:34px;border:0px hidden black;" /><br />[SECTION_NAME:5]
//        </td>
//        <td style="padding:2px;padding-top:5px;text-align:center;vertical-align:top;border:1px solid rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;" onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="ChangeLocation(\'index.php?tpl=blank&tpt=page&ada=5&[ADMIN_SECTION]\');">
//          <img src="images/design/admin/Static_.gif" alt="[SECTION_NAME:6]" title="[SECTION_NAME:6]" style="width:34px;height:34px;border:0px hidden black;" /><br />[SECTION_NAME:6]
//        </td>
//        <td style="padding:2px;padding-top:5px;text-align:center;vertical-align:top;border:1px solid rgb(224,240,249);color:rgb(0,111,153);font-weight:bold;" onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="ChangeLocation(\'index.php?tpl=blank&tpt=page&ada=6&[ADMIN_SECTION]\');">
//          <img src="images/design/admin/Static_.gif" alt="[SECTION_NAME:7]" title="[SECTION_NAME:7]" style="width:34px;height:34px;border:0px hidden black;" /><br />[SECTION_NAME:7]
//        </td>
        $this->BlankComment.'
        <td></td>
      </tr>
    </table>';

    $html['MainView']  = '
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;border:1px solid rgb(0,111,153);background-color:rgb(240,250,255);">
      <colgroup>
        <col style="min-width:100px;width:100px;" />
        <col style="min-width:180px;width:180px;" />
        <col style="min-width:25px;width:25px;" />
        <col style="min-width:80px;" />
        <col style="min-width:80px;" />
      </colgroup>
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td style="padding:5px;text-align:left;vertical-align:middle;color:rgb(0,111,153);font-weight:bold;font-size:11px;border-left:1px solid rgb(0,111,153);">[ROW_ONE_HEADER]</td>
        <td style="padding:5px;text-align:left;vertical-align:middle;color:rgb(0,111,153);font-weight:bold;font-size:11px;border-left:1px solid rgb(0,111,153);">[ROW_TWO_HEADER]</td>
      </tr>
      [-->ENTRY]
      <tr>
        <td colspan="2" style="padding:5px;padding-left:15px;text-align:left;vertical-align:middle;color:rgb(0,0,0);font-weight:bold;font-size:12px;border-top:2px solid rgb(0,111,153);border-bottom:2px solid rgb(0,111,153);background-color:rgb(176,212,232);">[TITLE]</td>
        <td style="padding:1px;text-align:center;vertical-align:middle;color:rgb(0,0,0);border-top:2px solid rgb(0,111,153);border-bottom:2px solid rgb(0,111,153);background-color:rgb(176,212,232);" onMouseOver="ChangeCellBackground(this,\'over\',\'\');" onMouseOut="ChangeCellBackground(this,\'out\',\'rgb(176,212,232)\');" onClick="OpenActionWindow(\'[STATIC_ACTION_URL_COMPLETE]\');">
          <img src="images/design/admin/Static_Action.gif" alt="[STATIC_ACTIONION]" title="[STATIC_ACTION]" style="width:16px;height:16px;border:0px hidden black;" />
        </td>
        <td colspan="2" style="border-top:2px solid rgb(0,111,153);border-bottom:2px solid rgb(0,111,153);background-color:rgb(176,212,232);"></td>
      </tr>
      [-->LANGUAGE]
      <tr>
        <td></td>
        <td style="padding:2px;padding-left:10px;text-align:left;vertical-align:middle;color:rgb(0,0,0);font-weight:normal;font-size:10px;border-left:0px solid rgb(0,111,153);border-bottom:1px solid rgb(0,111,153);">[LANGUAGE_NAME]</td>
        <td style="padding:1px;text-align:center;vertical-align:middle;color:rgb(0,0,0);font-weight:normal;font-size:10px;border-left:0px solid rgb(0,111,153);border-bottom:1px solid rgb(0,111,153);" onMouseOver="ChangeCellBackground(this,\'over\',\'\');" onMouseOut="ChangeCellBackground(this,\'out\',\'rgb(240,250,255)\');" onClick="OpenActionWindow(\'[STATIC_ACTION_URL]\');">
          <img src="images/design/admin/Static_Action.gif" alt="[STATIC_ACTION]" title="[STATIC_ACTION]" style="width:16px;height:16px;border:0px hidden black;" />
        </td>
        <td style="padding:2px;padding-left:20px;text-align:left;vertical-align:middle;color:rgb(0,0,0);font-weight:normal;font-size:10px;border-left:1px solid rgb(0,111,153);border-bottom:1px solid rgb(0,111,153);">[FILE_COUNT]</td>
        <td style="padding:2px;padding-left:20px;text-align:left;vertical-align:middle;color:rgb(0,0,0);font-weight:normal;font-size:10px;border-left:1px solid rgb(0,111,153);border-bottom:1px solid rgb(0,111,153);">[FILE_SIZE]</td>
      </tr>
      [--LANGUAGE]
      <tr>
        <td style="border-bottom:0px solid rgb(0,111,153);"></td>
        <td colspan="2" style="padding:5px;padding-left:10px;text-align:left;vertical-align:middle;color:rgb(0,0,0);font-weight:bold;font-size:10px;border-left:0px solid rgb(0,111,153);border-bottom:0px solid rgb(0,111,153);">'.$LANG->getValue("","txt","admin_150").'</td>
        <td style="padding:5px;padding-left:20px;text-align:left;vertical-align:middle;color:rgb(0,0,0);font-weight:bold;font-size:10px;border-left:1px solid rgb(0,111,153);border-bottom:0px solid rgb(0,111,153);">[TOTAL_FILE_COUNT]</td>
        <td style="padding:5px;padding-left:20px;text-align:left;vertical-align:middle;color:rgb(0,0,0);font-weight:bold;font-size:10px;border-left:1px solid rgb(0,111,153);border-bottom:0px solid rgb(0,111,153);">[TOTAL_FILE_SIZE]</td>
      </tr>
      [--ENTRY]
    </table>';

    $html['CloseWindow']  = '
    <script type="text/javascript">
    <!--
      window.opener.location.reload();
      window.setTimeout(\'window.close();\',500);
    //-->
    </script>
    <div style="">[CLOSE_WINDOW_TEXT]</div>';

    $html['DeleteFile']  = '
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;border:1px solid rgb(0,111,153);background-color:rgb(240,250,255);">
      <colgroup>
        <col style="min-width:80px;" />
      </colgroup>
      <tr>
        <td style="padding:15px;font-size:13px;font-weight:bold;color:rgb(200,80,80);">[DELETE_CONFIRMATION]</td>
      </tr>
      <tr>
        <td style="text-align:right;padding:5px;">
          <input type="button" value="[ABORD_TEXT]" onClick="window.close();" style="margin-right:5px;padding:1px;padding-left:15px;padding-right:15px;background-color:rgb(114,191,236);color:rgb(0,111,153);border:1px solid rgb(0,111,153);font-weight:bold;" />
          <input type="button" value="[CONFIRM_TEXT]" onClick="ChangeLocation(\'[DELETE_LINK]\');" style="margin-right:5px;padding:1px;padding-left:15px;padding-right:15px;background-color:rgb(114,191,236);color:rgb(0,111,153);border:1px solid rgb(0,111,153);font-weight:bold;" />
        </td>
      </tr>
    </table>';

    $html['CreateFile']  = '
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;border:1px solid rgb(0,111,153);background-color:rgb(240,250,255);">
      <colgroup>
        <col style="min-width:80px;" />
      </colgroup>
      <tr>
        <td style="padding:15px;font-size:13px;font-weight:bold;color:rgb(0,111,153);">[CREATION_CONFIRMATION]</td>
      </tr>
      <tr>
        <td style="padding:15px;font-size:13px;font-weight:bold;color:rgb(200,80,80);">[CREATION_MESSAGE]</td>
      </tr>
      <tr>
        <td style="text-align:right;padding:5px;">
          <input type="button" value="[ABORD_TEXT]" onClick="window.close();" style="margin-right:5px;padding:1px;padding-left:15px;padding-right:15px;background-color:rgb(114,191,236);color:rgb(0,111,153);border:1px solid rgb(0,111,153);font-weight:bold;" />
          <input type="button" value="[CONFIRM_TEXT]" onClick="ChangeLocation(\'[CREATION_LINK]\');" style="margin-right:5px;padding:1px;padding-left:15px;padding-right:15px;background-color:rgb(114,191,236);color:rgb(0,111,153);border:1px solid rgb(0,111,153);font-weight:bold;" />
        </td>
      </tr>
    </table>';

    $html['CreationFinished']  = '
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;border:1px solid rgb(0,111,153);background-color:rgb(240,250,255);">
      <colgroup>
        <col style="min-width:80px;" />
      </colgroup>
      <tr>
        <td style="padding:15px;font-size:13px;font-weight:bold;color:rgb(0,111,153);">[CREATION_CONFIRMATION]</td>
      </tr>
      <tr>
        <td style="text-align:right;padding:5px;">
          <input type="button" value="[ABORD_TEXT]" onClick="window.close();" style="margin-right:5px;padding:1px;padding-left:15px;padding-right:15px;background-color:rgb(114,191,236);color:rgb(0,111,153);border:1px solid rgb(0,111,153);font-weight:bold;" />
        </td>
      </tr>
    </table>';

    $html['CreateMirror']  = '
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;border:1px solid rgb(0,111,153);background-color:rgb(240,250,255);">
      <colgroup>
        <col style="min-width:20px;width:20px;" />
        <col style="min-width:80px;" />
      </colgroup>

      [-->ENTRY]
      <tr>
        <td style="text-align:right;vertical-align:middle;background-color:rgb(224,240,249);">
          <img src="images/design/admin/list_small.gif" alt="arrow" style="width:16px;height:16px;border-width:0px;" />
        </td>
        <td style="padding:2px;padding-left:10px;text-align:left;vertical-align:middle;color:rgb(0,0,0);">[MIRROR]</td>
      </tr>
      [--ENTRY]
    </table>';

    return $html;
  }

  function BackupManagement()
  {
    global $LANG,$postId;

    $html['MainScreen']  = '
    <script type="text/javascript">
    <!--
      function ChangeCellBackground(obj,act,col)
      {
        if (act == \'over\')
        {
          obj.style.cursor = \'pointer\';
          obj.style.backgroundColor = \'rgb(114,191,236)\';
        }
        else
        {
          obj.style.cursor = \'normal\';
          obj.style.backgroundColor = col;
        }
      }

      function ChangeBackground(obj,act)
      {
        if (act == \'over\')
        {
          obj.style.cursor = \'pointer\';
          obj.style.border = \'1px solid rgb(0,111,153)\';
          obj.style.backgroundColor = \'rgb(114,191,236)\';
        }
        else
        {
          obj.style.cursor = \'normal\';
          obj.style.border = \'1px solid rgb(224,240,249)\';
          obj.style.backgroundColor = \'rgb(224,240,249)\';
        }
      }

      function ChangeLocation(loc)
      {
        if (loc != \'\')
        {
          window.location.href = loc;
        }
      }

      function OpenActionWindow(loc)
      {
        if (loc != \'\')
        {
          var prop = \'width=590,height=500,top=\'+((screen.height-500)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
          var ActWin = window.open(loc.replace(/&amp;/gi,\'&\'),\'StaticActionWindow\',prop);
        }
      }
    //-->
    </script>

    <table cellspacing="2" cellpadding="0" style="padding:10px;table-layout:fixed;empty-cells:show;width:100%;">
      <colgroup>
        <col style="min-width:150px;" />
      </colgroup>
      <tr>
        <td style="border-bottom:1px solid rgb(0,111,153);border-top:1px solid rgb(0,111,153);padding:5px;text-align:center;font-size:15px;font-weight:bold;color:rgb(0,111,153);line-height:20px;background-color:rgb(245,250,255);">[ADMIN_SECTION_NAME]</td>
      </tr>
      <tr>
        <td style="padding:5px;text-align:center;vertical-align:top;">[BACKUP_FUNCTIONS]</td>
      </tr>
      <tr>
        <td style="padding:1px;border-top:1px solid rgb(0,111,153);border-bottom:2px solid rgb(0,111,153);height:5px;"></td>
      </tr>
      <tr>
        <td style="padding:5px;text-align:center;font-size:15px;font-weight:bold;color:rgb(224,240,249);line-height:20px;background-color:rgb(0,111,153);">[CURRENT_SECTION]</td>
      </tr>
      <tr>
        <td style="padding:1px;border-top:2px solid rgb(0,111,153);border-bottom:1px solid rgb(0,111,153);height:5px;"></td>
      </tr>
      <tr>
        <td style="padding:5px;padding-left:10px;text-align:center;vertical-align:middle;">[BACKUP_SECTION_TEXT]</td>
      </tr>
    </table>';

    $html['ButtonBar'] = '
    <table cellspacing="2" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;">
      <colgroup>
        <col style="min-width:10px;" />
        <col style="width:100px;min-width:80px;" />   <!-- Main View  //-->
        <col style="width:100px;min-width:80px;" />   <!-- Create Static texts  //-->
        <col style="min-width:10px;" />
      </colgroup>
      <tr>
        <td></td>
        <td style="padding:2px;padding-top:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;" onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="ChangeLocation(\'index.php?tpl=blank&tpt=page&adm=8&ada=0&lan='.$LANG->getLanguageName().'\');">
          <img src="images/design/admin/Static_MainView.gif" alt="'.$LANG->getValue("","txt","admin_177").'" title="'.$LANG->getValue("","txt","admin_177").'" style="width:34px;height:34px;border:0px hidden black;" /><br />'.$LANG->getValue("","txt","admin_177").'
        </td>
        <td style="padding:2px;padding-top:5px;text-align:center;vertical-align:top;border:1px solid rgb(0,111,153);color:rgb(224,240,249);font-weight:bold;" onMouseOver="ChangeBackground(this,\'over\');" onMouseOut="ChangeBackground(this,\'out\');" onClick="ChangeLocation(\'index.php?tpl=blank&tpt=page&adm=8&ada=1&lan='.$LANG->getLanguageName().'\');">
          <img src="images/design/admin/Static_Text.gif" alt="'.$LANG->getValue("","txt","admin_178").'" title="'.$LANG->getValue("","txt","admin_178").'" style="width:34px;height:34px;border:0px hidden black;" /><br />'.$LANG->getValue("","txt","admin_178").'
        </td>
        <td></td>
      </tr>
    </table>';

    $html['MainView']  = '
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;border:1px solid rgb(0,111,153);background-color:rgb(240,250,255);">
      <colgroup>
        <col style="min-width:180px;width:180px;" />
        <col style="min-width:180px;width:180px;" />
        <col style="min-width:80px;width:80px;" />
        <col style="min-width:80px;" />
      </colgroup>
      <tr>
        <td>
          <script type="text/javascript">
          <!--
            function ShowDeleteWindow(lnk)
            {
              var loc = lnk;
              var prop = \'width=590,height=100,top=\'+((screen.height-150)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
              var DelWin = window.open(loc.replace(/&amp;/gi,\'&\'), \'BackupWindow\', prop);
            }

            function ShowDownloadWindow(lnk)
            {
              var loc = lnk;
              var prop = \'width=590,height=100,top=\'+((screen.height-150)/2)+\',left=\'+((screen.width-600)/2)+\',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,dependent=yes\';
              var DelWin = window.open(loc.replace(/&amp;/gi,\'&\'), \'BackupWindow\', prop);
            }
          //-->
          </script>
        </td>
        <td style="padding:5px;text-align:left;vertical-align:middle;color:rgb(0,111,153);font-weight:bold;font-size:11px;border-left:0px solid rgb(0,111,153);border-bottom:2px solid rgb(0,111,153);">
        '.$LANG->getValue("","txt","admin_179").'
        </td>
        <td style="padding:5px;text-align:left;vertical-align:middle;color:rgb(0,111,153);font-weight:bold;font-size:11px;border-left:1px solid rgb(0,111,153);border-bottom:2px solid rgb(0,111,153);">
        '.$LANG->getValue("","txt","admin_180").'
        </td>
        <td style="padding:5px;text-align:left;vertical-align:middle;color:rgb(0,111,153);font-weight:bold;font-size:11px;border-left:1px solid rgb(0,111,153);border-bottom:2px solid rgb(0,111,153);">
        '.$LANG->getValue("","txt","admin_181").'
        </td>
      </tr>
      [BACKUP_ENTRY]
      <tr>
        <td style="border-bottom:0px solid rgb(0,111,153);"></td>
        <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:middle;color:rgb(0,0,0);font-weight:bold;font-size:10px;border-left:0px solid rgb(0,111,153);border-bottom:0px solid rgb(0,111,153);">'.$LANG->getValue("","txt","admin_150").'</td>
        <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:middle;color:rgb(0,0,0);font-weight:bold;font-size:10px;border-left:1px solid rgb(0,111,153);border-bottom:0px solid rgb(0,111,153);">[TOTAL_BACKUP_SIZE]</td>
        <td style="padding:5px;padding-left:10px;text-align:left;vertical-align:middle;color:rgb(0,0,0);font-weight:bold;font-size:10px;border-left:1px solid rgb(0,111,153);border-bottom:0px solid rgb(0,111,153);">[TOTAL_BACKUP_FILES] '.$LANG->getValue("","txt","admin_182").'</td>
      </tr>
    </table>';

    $html['FileEntry'] = '
      <tr>
        <td></td>
        <td style="padding:2px;padding-left:20px;text-align:left;vertical-align:middle;color:rgb(0,0,0);font-weight:normal;font-size:10px;border-left:0px solid rgb(0,111,153);border-bottom:1px solid rgb(0,111,153);">[BACKUP_DATE]</td>
        <td style="padding:2px;padding-left:20px;text-align:left;vertical-align:middle;color:rgb(0,0,0);font-weight:normal;font-size:10px;border-left:1px solid rgb(0,111,153);border-bottom:1px solid rgb(0,111,153);">[BACKUP_SIZE]</td>
        <td style="padding:2px;padding-left:20px;text-align:left;vertical-align:middle;color:rgb(0,0,0);font-weight:normal;font-size:10px;border-left:1px solid rgb(0,111,153);border-bottom:1px solid rgb(0,111,153);">
          <img src="images/design/admin/backup_delete.gif" alt="'.$LANG->getValue("","txt","admin_183").'" title="'.$LANG->getValue("","txt","admin_183").'" style="width:16px;height:16px;border:0px hidden black;"
          onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\'" onClick="ShowDeleteWindow(\'[DELETE_URL]\');" />
          &nbsp;&nbsp;
          <img src="images/design/admin/backup_download.gif" alt="'.$LANG->getValue("","txt","admin_184").'" title="'.$LANG->getValue("","txt","admin_184").'" style="width:16px;height:16px;border:0px hidden black;"
          onMouseOver="this.style.cursor=\'pointer\';" onMouseOut="this.style.cursor=\'normal\'" onClick="window.location.href=\'[DOWNLOAD_URL]\';" />
        </td>
      </tr>';

    $html['DeleteConfirmation']  = '
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;border:1px solid rgb(0,111,153);background-color:rgb(240,250,255);">
      <colgroup>
        <col style="min-width:80px;" />
      </colgroup>
      <tr>
        <td style="padding:15px;font-size:13px;font-weight:bold;color:rgb(0,111,153);">[DELETE_CONFIRMATION]</td>
      </tr>
      <tr>
        <td style="text-align:right;padding:5px;">
          <input type="button" value="[ABORD_TEXT]" onClick="window.close();" style="margin-right:5px;padding:1px;padding-left:15px;padding-right:15px;background-color:rgb(114,191,236);color:rgb(0,111,153);border:1px solid rgb(0,111,153);font-weight:bold;" />
          &nbsp;&nbsp;
          <input type="button" value="[DELETE_TEXT]" onClick="window.location.href=\'[DELETE_LINK]\';" style="margin-right:5px;padding:1px;padding-left:15px;padding-right:15px;background-color:rgb(114,191,236);color:rgb(0,111,153);border:1px solid rgb(0,111,153);font-weight:bold;" />
        </td>
      </tr>
    </table>';

    $html['CloseWindow']  = '
    <script type="text/javascript">
    <!--
      window.opener.location.reload();
      window.setTimeout(\'window.close();\',500);
    //-->
    </script>
    <div style="">[CLOSE_WINDOW_TEXT]</div>';

    $html['CreateNew']  = '
    <table cellspacing="0" cellpadding="0" style="width:100%;table-layout:fixed;empty-cells:show;border:1px solid rgb(0,111,153);background-color:rgb(240,250,255);">
      <colgroup>
        <col style="min-width:80px;" />
      </colgroup>
      <tr>
        <td style="padding:15px;font-size:13px;font-weight:bold;color:rgb(0,111,153);">[BACKUP_STATE]</td>
      </tr>
      <tr>
        <td style="text-align:right;padding:5px;">
          <input type="button" value="[CONTINUE_TEXT]" onClick="window.location.href=\'index.php?tpl=blank&tpt=page&adm=8&ada=0&lan='.$LANG->getLanguageName().'\';" style="margin-right:5px;padding:1px;padding-left:15px;padding-right:15px;background-color:rgb(114,191,236);color:rgb(0,111,153);border:1px solid rgb(0,111,153);font-weight:bold;" />
        </td>
      </tr>
    </table>';

    return $html;
  }
}
?>
