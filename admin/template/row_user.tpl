<tr>
	<td style="border-left: 1px solid #808080; border-bottom: solid 1px #808080; background-color: #F8F8F8; padding: 5px"><b>{ID}</b></td>
	<td style="border-bottom: solid 1px #808080; padding: 5px">{LOGIN}</td>
	<td style="border-bottom: solid 1px #808080; padding: 5px"><a href="dialog_user_priv.php?id={ID}" class="ln1">Права доступа</a></td>
	<td style="border-right: solid 1px #808080; border-bottom: solid 1px #808080; background-color: #F8F8F8; padding: 5px">
		<img src="images/gallery/delete.gif" width="16" height="16" border="0" hspace="2" onclick="deleteUser('{ID}');" style="cursor: pointer;" alt="Удалить">
		<img src="images/gallery/rename_group.gif" width="16" height="16" border="0" hspace="2" onclick="changePassword('{ID}');" style="cursor: pointer;" alt="Сменить пароль">
	</td>
</tr>