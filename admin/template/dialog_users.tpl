<div class="sd_pageTitle">Пользователи</div>

<form action="" name="userForm" method="post" enctype="multipart/form-data">

<table cellpadding="5" cellspacing="0" border="0" width="600" align="center"> 
<tr>
    <td class="sd_formLabel4">Добавить пользователя:</td>
    <td class="sd_formInput2">
        
        <div class="tx1">Логин:</div>
        <div style="width: 200px; margin-bottom: 10px"><input name="login" type="text" class="sd_textbox" autocomplete="off"></div>

        <div class="tx1">Пароль:</div>
        <div style="width: 200px; margin-bottom: 10px"><input name="password" type="password" class="sd_textbox" autocomplete="new-password"></div>

        <div style="width: 200px; margin-bottom: 10px"><input type="submit" name="send" class="sd_button" value="Добавить"></div> 

    </td>
</tr>
</table>

<table cellpadding="5" cellspacing="0" border="0" width="600" align="center">
<tr>
    <td class="sd_outset" style="border-left: 1px solid #808080; border-top: 1px solid #A6A6A6;"><b>ID</b></td>
    <td class="sd_outset" style="border-top: 1px solid #A6A6A6;"><b>Логин</b></td>
    <td class="sd_outset" style="border-top: 1px solid #A6A6A6;">&nbsp;</td>
    <td class="sd_outset" style="border-top: 1px solid #A6A6A6;">&nbsp;</td>
</tr>
{USER_LIST}
</table>
</form>
