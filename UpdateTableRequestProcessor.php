<?php
/**
  UpdateTableRequestProcessor.
  This request processor is used with TableAdminGateway, and supports updating tables from the admin managers tab.
  
  Revision History
  
    SMC-396   JL  7/2015 - Initial verison.  Supports displaying, updating, deleteing rows for various tables defined in TableAdminGateway.  Templates
                                          displayTable.tpl and editTable.tpl,  editTable.js support this feature.
                                          
    SMC-431  JL 10/2015 - Some improvements that were needed to implement bev_categies table.
 */

class UpdateTableRequestProcessor extends TemplatedRequestProcessor {

	public function processRequest() {
	    
		$this->templateInitial(); 
        
        //process delete/update first and set message to return, and then always process display - to display updated data on table.
        $getPageFunction = isset($_GET['page_function']) ? $_GET['page_function'] : '';
         $table=isset($_GET['table']) ? $_GET['table'] : '';
         if ($table=='') {
            $table=isset($_POST['table']) ? $_POST['table'] : '';
         }
        
        $tableAdminGateway=$this->setupTableRef($table);
        
        if ($getPageFunction === 'updateTable') {   //posting data - Save or Delete Data
            //$tableName= isset($_POST['tableName']) ? $_POST['tableName'] : '';
            $editId= isset($_POST['editId']) ? $_POST['editId'] : '';
            $delete= isset($_POST['delete']) ? true : false;
            $errorMessage=array();
            $successMessage='';
            
            $errorMessage=$tableAdminGateway->fileTableData($_POST,$editId,$delete);            
          
            
            if (count($errorMessage) == 0) {
                $successMessage='Table Data Saved Successfully.';
                if ($delete === true) {
                    $successMessage='Row Deleted From Table.';                
                }
                //redirect to display updated table.
                header('Location: ' . eGlooConfiguration::getRewriteBase() . 'superadmin/updatetable/?table=' . $table . '&page_function=display');   
            }
            else {
                //put error message in templateVariables after re-setting them from post array.
                 header('Location: ' . eGlooConfiguration::getRewriteBase() . 'superadmin/edittable/?table=' . $table . '&page_function=editPage&id='.$editId.'&loadFromPost=1');   
                 $_SESSION['updateTable']['postData']=$_POST;
                 $_SESSION['updateTable']['error']=$errorMessage;
            }
           
        }	
        else if ($getPageFunction === 'display')	{
	       //if get function called, or we just processed a post call - get table data to display/return to front end.
            //set any filing message from above in outgoing template data.        
            $this->templateVariables['tableData']=array();
            $this->templateVariables['columnHeaders']=array();
            $this->templateVariables['tableInfo']=array();
            //listed here are the tables supported for add/edit from admin managers tab.
            $tableFieldsArray=$tableAdminGateway->getTableDataForAdminUpdate(true);
            
            $this->templateVariables['tableData']=$tableFieldsArray['tableData'];
            $this->templateVariables['columnHeaders']=$tableFieldsArray['columnHeaders'];
            //tableInfo is stuff like title, table name
            $this->templateVariables['tableInfo']=$tableFieldsArray['tableInfo'];  
        }
        else if (($getPageFunction == 'addPage')  || ($getPageFunction == 'editPage')) {
        //for this case - return array of labels, required flag, and data if editing. 
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                $editId=$_GET['id'];
            }
            else {
                $editId='';
            }
            $loadFromPost=isset($_GET['loadFromPost'])  ? $_GET['loadFromPost'] : '';         
            $tableFieldsArray=$tableAdminGateway->getTableDataForAdminUpdate(false);
            $this->templateVariables['tableData']=$tableFieldsArray['tableData'];
            $this->templateVariables['columnHeaders']=$tableFieldsArray['columnHeaders'];
            //tableInfo is stuff like title, table name
            $this->templateVariables['tableInfo']=$tableFieldsArray['tableInfo'];  
            //for add new - editid should be blank, and all fields should be blank.
            $this->templateVariables['tableInfo']["editId"]=$editId;
            
            if ($loadFromPost == 1 && isset($_SESSION['updateTable']['postData'])) {
                $defaultArray= $_SESSION['updateTable']['postData'];         
                //$this->templateVariables['errorMessage']=$_SESSION['updateTable']['error'];
                /*$fieldError[0]['id']='label_key';
                $fieldError[0]['errorMessage']='duplicate label key';*/
                $this->templateVariables['fieldError']=$_SESSION['updateTable']['error'];
                unset($_SESSION['updateTable']['postData']);
                unset($_SESSION['updateTable']['error']);                  
            }
            else {
                $defaultArray=array();            
            }
            
            $this->templateVariables['fieldInfo']=$tableAdminGateway->getAddEditFieldInfo($editId,$defaultArray);
            $this->templateVariables['editRecordConfirmMessage']=$tableAdminGateway->editRecordConfirmMessage($editId);
        }
		//die_r($this->templateVariables);
		$this->templateOut();
	}

/* Function:  setupTableRef
    Description:  sets up table class based on table passed in for request
    Parameters:  table name send to request processor.
    Returns:  object reference for table class
*/
 
private function setupTableRef($table)
{
	
	switch ($table) {
            case 'client_labels':
                $tableRef=new ClientLabels();
                break;
             case 'languages_supported':
                $tableRef=new LanguagesSupported();
                break;
            case "bev_categories":
                $tableRef= new BevCategories();
                break;
             default:  //this should never happen
                $tableRef=null;
                break;	
    }	
	
	return $tableRef;
}		
		
}
?>

