<!-- SMC-396 - JL 7/2015 added Table Maintenance for client labels and languages supported tables -->
<!--{include file='XHTML/common/header.tpl'}-->

<script type="text/javascript" >
  var rewriteBase = "<!--{$rewriteBase}-->";
 
</script>

<div id="innerContent">
	
	<h1><!--{$tableInfo.title}--> </h1>
    <form enctype="multipart/form-data" action="<!--{$rewriteBase}-->superadmin/edittable/?page_function=updateTable" method="post" name="<!--{$tableInfo.tableName}-->" onsubmit="return validateForm(this,'<!--{$tableInfo.tableName}-->','requiredField');"/>
        <div class="section">
        
        <input type="hidden" name="table" id="table" value="<!--{$tableInfo.tableName}-->"  />
        <input type="hidden" name="editId" id="editId" value="<!--{$tableInfo.editId}-->"  />
        <input type="hidden" name="editRecordConfirmMessage" id="editRecordConfirmMessage" value="<!--{$editRecordConfirmMessage}-->"  />
			<table  cellpadding="5" text-align="left" width="1000">	
			<col width="300">
			<col width="500">
			<col width="300">
			<tr>
				<td colspan="3"><span class="required">*</span><i> - Required</i></td>
			</tr>

            <!--{foreach from=$fieldInfo item=field}--> 
                <!--{if ($field.hideOnBlank==1)  && ($field.value=="")}-->
                    <span></span>
                <!--{else}-->
                <tr class="tableEditRow">                    
                    <th class="tableEditRow">
                    <!--{$field.label}-->
                    <!--{if $field.required==1}-->
                        <span class="required">*</span>
                     <!--{/if}-->
					</th>
				    <td class="tableEditRow">    
                        <!--{if (isset($field.type) && $field.type == "SELECT")}-->
                        <select name="<!--{$field.columnName}-->" id="<!--{$field.columnName}-->" style="width:300px">
						<!--{foreach $field.choices as $index=>$choice}-->
							<option value="<!--{$index}-->"<!--{if $field.value == $index}--> selected="selected"<!--{/if}-->>
								<!--{$choice}-->
							</option>
						<!--{/foreach}-->
						    <option value=""<!--{if $field.value == ""}--> selected="selected"<!--{/if}-->>
							</option>
					   </select>                         
                        <!--{else}-->				    
					       <!--{if $field.required==1}-->
                             <input type="text" style="width:465px" class="requiredField" name="<!--{$field.columnName}-->" id="<!--{$field.columnName}-->" value="<!--{$field.value}-->"  <!--{$field.readonly}--> />
                            <!--{else}-->	
                                <!--{if $field.readonly == "readonly"}-->
                                    <!--{$field.value}-->
                             <!--{else}--> 										  
						         <input type="text" style="width:465px" name="<!--{$field.columnName}-->" id="<!--{$field.columnName}-->"  value="<!--{$field.value}-->" <!--{$field.readonly}--> />		
                                <!--{/if}-->                         
                            <!--{/if}-->
                          <!--{/if}-->                     					
					</td>
					<td class="error-message">
					<span></span>
					<p id="<!--{$field.columnName}-->_error"> <span></span></p>
					</td>
				</tr>
				<!--{/if}-->            
            <!--{/foreach}-->
             <input type="hidden" name="deleteFlag" id="deleteFlag" value=""  />
			</table>
			</div>
			<table cellpadding="5">
				<tr>
					<td>
						<input type="submit" name="save" id="save" value="Save" onClick="setDelete(0);"/>
					</td>
					<!--{if (isset($tableInfo.editId) && strlen($tableInfo.editId))}-->
					<td>
						<input type="submit" name="delete" id="delete" value="Delete" onClick="setDelete(1);" />
					</td>
					<!--{/if}-->
					<td>
						<input type="button" name="cancel" value="Cancel" onClick="window.location='<!--{$rewriteBase}-->superadmin/updatetable/?page_function=display&table=<!--{$tableInfo.tableName}-->'" />				
					</td>
				</tr>		
			</table>
	</form>
	 <h3 id='FILING_error' ></h3>
</div>

<!--{if (isset($fieldError))}-->
    <!--{foreach from=$fieldError item=errorInfo}--> 
    <script>
        var id='<!--{$errorInfo.id}-->';
        var msg='<!--{$errorInfo.errorMessage}-->';
        var errorId=id+'_error';        
        //this function can't be found ?? displayFieldErrors(id,msg);  putting code here instead of function in editTable.js.
         $('#'+id).addClass('fieldError');  
         $('#'+errorId).html(msg);
         $('#'+errorId).show();               
    </script>
    <!--{/foreach}-->
 <!--{/if}-->	

<script src="<!--{$rewriteBase}-->js/<!--{$userAgentHash}-->/editTable.js" type="text/javascript"></script>

<!--{include file='XHTML/common/footer.tpl'}-->

