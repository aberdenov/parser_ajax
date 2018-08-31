<div class="box-modal" id="imagesModal">
	<div class="box-modal_close arcticmodal-close">закрыть</div>
    
    <div class="sd_pageTitle">Изображения</div>
    
    <table cellpadding="5" cellspacing="0" border="0" width="600" align="center" style="border-bottom: 1px solid #808080"> 
    <tr>
        <td class="sd_formLabel4">Группы:</td>
        <td class="sd_formInput2">
            
            <table width="100%"  border="0" cellspacing="0" cellpadding="0">
             <tr>
                <td style="padding-right: 5px;">
                    <select class="sd_textbox" name="group_id" id="group_id">
                    {GROUPS}
                    </select>
                </td>
                <td width="80" align="right">
                    <input type="button" class="sd_button" value="Перейти" onclick="changeGroup();">		
                </td>
             </tr>
            </table>
            
        </td>
    </tr>
    </table> 
    
    <div>&nbsp;</div>
    
    <div style="height: 400px; overflow: scroll" id="imagesCont"></div>    
</div>

<script language="javascript">
	changeGroup();
</script>