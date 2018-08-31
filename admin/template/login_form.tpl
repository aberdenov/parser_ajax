<script language="JavaScript" type="text/javascript">
	function checkFields(user_form, msg) {
		for (var i = 0; i < user_form.length; i++) {
			if (user_form[i].type == "text") user_form[i].style.background = '#FFFFFF';
		}
		
		for (var i = 0; i < user_form.length; i++) {
			if (user_form[i].type == "text" || user_form[i].type == "textarea" || user_form[i].type == "password") {
				if (user_form[i].id == 1 && user_form[i].value == '') {
					if (msg != '') alert(msg);
					user_form[i].style.background = '#FFF9DF';
					user_form[i].focus();
					return false;
				}
			}
		}
		
		return true;
	}
</script>

<form method="post" action="" onsubmit="return checkFields(this, 'Введите логин и пароль!')">
<div class="sd_pageTitle">Вход в систему</div>
<table width="350" border="0" cellspacing="0" cellpadding="5" align="center">
  <tr>
    <td width="100" class="sd_formLabel2">Логин:</td>
    <td class="sd_formInput"><input type="text" class="sd_textbox" name="login" maxlength="15" value="{USR_LOGIN}" accesskey="L" id="1"></td>
  </tr>
  <tr>
    <td class="sd_formLabel2">Пароль:</td>
    <td class="sd_formInput"><input type="password" class="sd_textbox" name="password" maxlength="30" value="{USR_PASSW}" accesskey="P" id="1"></td>
  </tr>
  <tr>
    <td class="sd_formLabel2">&nbsp;</td>
    <td class="sd_formInput"><input type="checkbox" name="chk_save" id="chk_save" value="checkbox" accesskey="S" {SAVE}>&nbsp;<label for="chk_save">запомнить</label></td>
  </tr>
  <tr>
    <td colspan="2" class="sd_formButton">
	<input type="submit" class="sd_button" value="Войти" accesskey="E">
	</td>
  </tr>
</table>
<br>
<table width="350" border="0" cellspacing="0" cellpadding="5" align="center">
<tr>
	<td>{MSG}</td>
</tr>
</table>
</form>
