<!-- SMC-396 - JL 7/2015 added Table Maintenance for client labels and languages supported tables -->

<!--{include file='XHTML/common/header.tpl'}-->

<!--{if (isset($errorMessage) && strlen($errorMessage))}--> <h3 id="error" class="homePageMessage"> <!--{$errorMessage}--> <a class="closeMessage">[x] close</a></h3> <!--{/if}-->
<!--{if (isset($successMessage) && strlen($successMessage))}--> <h3 id="success" class="homePageMessage"> <!--{$successMessage}--> <a class="closeMessage">[x] close</a></h3> <!--{/if}-->

<h3 id="success" class="homePageMessage"> Test Displaying banner <a class="closeMessage">[x] close</a></h3>

<div id="innerContent">

<h1><!--{$tableInfo.title}--> </h1>

<form enctype="multipart/form-data" action="<!--{$rewriteBase}-->superadmin/updatetable" method="post" >

			<table class="common" width="100%"> <span style="color: #fff"> 
				<tr>
				<!--{foreach from=$columnHeaders item=columnLabel}-->
					<th><!--{$columnLabel}--></th>
				<!--{/foreach}-->
				</tr>
				<!--{foreach from=$tableData item=data_record}-->
				    <tr id="'<!--{$data_record.id}-->'" onClick="addUpdateRow('<!--{$tableInfo.tableName}-->','<!--{$data_record.id}-->')">
				    <!--{foreach from=$data_record item=data_value}-->
						<td><!--{$data_value}--></td>
				    <!--{/foreach}-->
				    </tr>
				<!--{/foreach}-->
			</table>	
		
	<table cellpadding="5">
		<tr>
			<td>
				<input type="button" name="add_row" value="Add New Row" onClick="addUpdateRow('<!--{$tableInfo.tableName}-->','')" />
			</td>
		</tr>		
		</table>

</form>

</div>
<script>
    highlightTab('manager');
    
    function addUpdateRow(table,selectedId) {
       // console.log('***** in dataRowSelected, id: '+selectedId+' table: '+table);    
    
    
    //?table=client_labels&page_function=display"    
    var $redirect;
    
    if (selectedId !== '') {
        $redirect='&page_function=editPage&id='+selectedId;
    }
    else {
        $redirect='&page_function=addPage';
    }
    
    $redirect=window.location='<!--{$rewriteBase}-->superadmin/edittable/?table='+table+$redirect;        
    }
    
</script>

<!--{include file='XHTML/common/footer.tpl'}-->

