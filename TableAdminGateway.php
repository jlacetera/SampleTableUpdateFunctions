<?php
 
/*  Revision History

    SMC-396 JL 7/2015 - Initial Version.  Supports client_labels and languages_supported tables.  Additional tables can be added easily.
    SMC-431  JL 10/2015 - Added bev_categories table.  Added some class functions needed for bev_categories table.
                                         Updated - when selecting table data, to only select columns that are defined in table structure, instead of select '*'.  This fixed some 
                                                         issues with bev_categories where table structure was different in different SMC databases.
*/ 
 
/**
* Class Name:  TableAdminGateway
* Class Description:  Contains all logic/code to support maintaining tables thru admin managers tab.
                                Each table supported will have their own class with the table/field descriptions.
                                Initial version - ClientLabels & LanguagesSupported.
 
*/ 
 
class TableAdminGateway extends Gateway
{
    
    protected $gatewayPtr;
  
/* put table name in here and new the appropriate specific table object here based on name passed in */ 
 
	public function __construct()
	{
		parent::__construct();
			
	}

/* 
      Function  Name:       getTableDataForAdminUpdate
		Description:          returns the necessary information to display the table data on the displayTable.tpl.
        Parameters:  
                            returnData - true- all table data is returned.   false - table data is not returned.
                            
        ReturnValue:   tableArray['tableData']
                 tableArray['columnHeaders']
                tableArray['tableInfo']
         
*/

public function getTableDataForAdminUpdate($returnData)
{
    $returnArray=array();
    $table=$this->tableName;
    
    if (isset($table) && !empty($table)) {      
        $fieldsArray=array();    
        
        if ($returnData === true) {
            $fieldsToInclude=$this->getFieldsOnTable();
            $fieldList=implode(",", $fieldsToInclude);
            $sql="select $fieldList from $table";
            $fieldsArray=$this->getTableData($sql,false,true);
            /* send back fields in correct format/values for display on table on front end */
            $fieldsArray=$this->fixFieldsForTableDisplay($fieldsArray);    
            
        }  
        
        $returnArray['tableData']=$fieldsArray;
        $returnArray['columnHeaders']=$this->getLabelsArray();
        $returnArray['tableInfo']['title']="Table Update For $table"; 
        $returnArray['tableInfo']['tableName']=$table;            
    }
    
    return $returnArray;
}

/* Function Name:  fixFieldsForTableDisplay
    Description: updates field values to have display representation instead of table value for Select fields
    Paramters:  fieldArray[row][columnNames]=columnValue - rows from table.
    Return Value:  input array fieldArray with values updated.
*/

private function fixFieldsForTableDisplay($fieldTableArray)
{
    $returnArray=$fieldTableArray;
    
    $selectFields=array();
    foreach ($this->tableStructure as $ind => $fieldArray) {
        if (isset($fieldArray['type']) && $fieldArray['type'] == 'SELECT') {
            $colName=$fieldArray['columnName'];
            foreach ($fieldArray['choices'] as $cIndex => $cValue) {
                  $selectFields[$colName][$cIndex]=$cValue;          
            }
        }   
    }
    
    /* if table has select fields that need to be updated, otherwise skip this processing */
    if (count($selectFields)) {
        foreach ($returnArray as $ind => $row) {
            foreach ($selectFields as $colName=>$choices) {
                if (isset($row[$colName])) {
                    $val=$row[$colName];
                    if (isset($choices[$val])) {
                        $returnArray[$ind][$colName]=$choices[$val];                    
                    }                
                }            
            }        
        }   
    }
   
    return $returnArray;
}

/*
    Function Name:  getFieldsInTable
    Description:  returns an array of fields/columns that are defined in tableStructure - to be included in table.
    Parameters:  none.
    Return Value:  array of fields that should be on table.
    
*/

private function getFieldsOnTable()
{

    $returnArray=array(); 
    foreach ($this->tableStructure as $ind => $fieldArray) {   
        $returnArray[$fieldArray['columnName']]=$fieldArray['columnName'];
     }
     
    return $returnArray; 
}

/* Function Name:  getLabelsArray
    Description:  returns the column labels for the table passed in for display on the displayTable.tpl
    Parameters:  table - table name.
    Return Value - array of labels for columns in table.
*/

private function getLabelsArray()
{
    
    $returnArray=array();
   
    //set up labels array.
    if (isset($this->tableStructure)) {
        foreach ($this->tableStructure as $ind => $fieldArray) {
            $returnArray[$fieldArray['columnName']]=isset($fieldArray['label']) ? $fieldArray['label'] :  $fieldArray['columnName'];
        }
    }
   
   return $returnArray;   
}

/* Function Name:  getAddEditFieldInfo
    Description:  returns the column/values for populating the editTable.tpl - which is used to add and edit rows in the table.
    Parameters:  
                        editId:  for an edit, data will be returned using id=editId in where clause. if editId='', then new record and data will not be returned.
                        defaultArray - array of values to put in table, instead of returning blank for new record, or data from database table on edit.
    Return: An array of in same structure as tableAddStructure. If editing a record, then value will have the correct value for that column, otherwise it will be blank.
*/

public function getAddEditFieldInfo($editId="",$defaultArray=array())
{
    $returnArray=array();
    $table=$this->tableName;
    
    if (isset($this->tableStructure)) {
        $returnArray=$this->tableStructure;    
    }
    
    //if editId is set - then get data and put in return array.
    if ($editId !== "" || count($defaultArray)) {
        if (count($defaultArray)) {
            $dataRow=$defaultArray;        
        }
        else {
            $fieldsToInclude=$this->getFieldsOnTable();
            $fieldList=implode(",", $fieldsToInclude);
            $sql="select $fieldList from $table where id=$editId";
            $dataRow=$this->getTableData($sql,true,true);
        }        
        //if row returned - then loop thru returnArray and set value.        
        if (count($dataRow)) {
            foreach ($returnArray as $index=>$val) {
                $columnName=$returnArray[$index]['columnName'];
                if (isset($dataRow[$columnName]) && $dataRow[$columnName] !== null) {
                    $returnArray[$index]['value']=$dataRow[$columnName];                
                }
            }
        }    
    }
    
    return $returnArray;    
}

/* Function Name:  checkForUniqueValue
    Description:  Called  to determine if field is unique.  
                         
    Parameters:  table - the database table name
                        column - the column in the table.
                        value - the value of the column to validate uniqueness
                        editId:  if blank - then new record, if set make sure we aren't getting duplicate for same row id.
                        
    Returns:  string with error message if not unique, empty string for unique value.
    
*/


public function checkForUniqueValue($table,$column,$value,$editId) 
{
    
    $value=trim($value);
    $editId=trim($editId);
    $returnVal='';
    
    
     if ($value !== '') {
        $sql="select id, $column from $table where $column = '".$value."'";
        if ($editId != '') {
            $sql=$sql." and id <> $editId";        
        }  
             
       $retArray=$this->getTableData($sql,false,true);    
        
        if (count($retArray)) {
           $returnVal='This value is not unique.  Please update with a unique value.';
        }
  }
      
  return $returnVal;  
}

/* Function Name:  fileTableData
    Description:  files data submitted from editTable.tpl.  Supports inserting new rows, updating existing rows, deleting rows.
                     
   Parameters:  tableName:  database table name
                                           postArray - array of [columnNames]=value - that should be updated in database.
                                           $editId - if sent, then update or delete.  IF not sent - then adding new record.  id in table should be 'id';
                                           delete - if true - then delete row, otherwise we are doing insert/update.
    Returns:  returnMessage - empty on success, array of errors.

*/

public function fileTableData($postArray,$editId,$delete=false)
{

    $tableName=$this->tableName;
    
    $dataToFileArray=array();
    $returnMessage=array();
    $errCnt=0;
    $errString='';
    
    if ($delete === true) {
        if (isset($editId) && $editId>0) {
            $errString=$this->validateDelete($postArray,$editId);
            if ($errString === '') {
                $errString=$this->deleteRow($postArray,$editId,$tableName);
            }
           else {
               $returnMessage[$errCnt]['errorMessage']=$errString;
               $returnMessage[$errCnt]['id']="FILING";
               $errCnt++;
           }   
        }
        else {
            //this should never happen.
            $returnMessage[$errCnt]['errorMessage'] = "Error deleting from table $tableName : delete id not set.";
            $returnMessage[$errCnt]['id']='id';
            $errCnt++;
        }
    }   
    else {
        //validate data
        $returnMessage=$this->validateTableData($postArray,$editId);
        if (count($returnMessage) == 0) {
            foreach ($this->tableStructure as $index=>$fieldArray) {
                $columnName=$fieldArray['columnName'];
                 if (isset($postArray[$columnName])) {
                      $dataToFileArray[$columnName]=$postArray[$columnName];            
                 }        
             }
        
             $idField=null; 
              if (isset($dataToFileArray['id'])) {
                  unset($dataToFileArray['id']);    
               }         
 
                if (isset($editId) && ($editId > 0)) {
                    $idField='id';   
                    $dataToFileArray['id']=$editId; 
                }
                else {
                    //to indicate that new row is being inserted.
                    $editId=0;                
                }
                //call gateway function to file data.  $ret will be new id if insert, will be updated rows if update.
                $ret=$this->updateTableDataByColumnArray($tableName, $dataToFileArray, $idField);    
                if ($editId == 0) {
                    $editId = $ret;  
                    $newRecord=true;              
                }
                else {
                    $newRecord=false;                
                }
                $this->postFile($editId,$newRecord);             
                 
            }
    }
    return $returnMessage;  
}   

/*
    Function:  validateFieldData
    Description:  validates fields for max length and required, as set up in the tableStructure array for the table.
    Parameters:  $postArray - array in format array['columnName']=value;
    Returns:  array in format array[columnName]=errorMessage, or empty array if all fields are valid.
    
*/
public function validateFieldData($postArray) 
{
    //for each field - check maxLenth if set, and check required just in case.  Required should already be set - but just in case it should be checked again before filing.
    $errorArray=array();
    
    foreach ($this->tableStructure as $index=>$fieldArray) {
        $columnName=$fieldArray['columnName'];
        $val= isset($postArray[$columnName]) ? $postArray[$columnName] : '';
        $maxLength=isset($fieldArray['maxLength']) ? $fieldArray['maxLength'] : '';
         
        if (($val != '')  && ($maxLength != '')) {
            if (strlen($val) > $maxLength) {
                 $errorArray[$columnName]="Exceeds maximum length of ".$maxLength.".";
            }
       }
                
       if ($val == '') {
           if (isset($fieldArray['required']) && $fieldArray['required'] == 1) {
               $errorArray[$columnName]="Required field.";
           }                
       }
    }   
    
    return $errorArray;                             
}

/* 
   Function:  validateTableData
   Description:  validates fields based on table structure array, and validates data to file based on custom logic in table class.
   Parameters:  $postArray - an array with format arra[columnName]=value.
                        $editId - id of row being updated, or blank for new row.
   Returns:  empty array if validation passes, or array in format array[cnt][id]=columnName,
                                                                                                 array[cnt][errorMessage] = error message to display.
    
*/

public function validateTableData($postArray,$editId) 
{
    
    $fldErrors=array();
    $tblErrors=array();
    $errorMesg=array();
    
    $fldErrors=$this->validateFieldData($postArray);
 
     $tblErrors=$this->validateTableDataToFile($postArray,$editId);  

    $errorMesg=$this->mergeErrorArrays($fldErrors,$tblErrors);
    
     return $errorMesg;
    
}

/*
    Function validateTableDataTofile
    Description:  used to validate table data prior to filing.  Child classes can override with their custom logic.
    Parameters:  postArray, editId - id of row filing, blank for new record.
    Returns:  array with errors.

*/
public function validateTableDataToFile($postArray,$editId) 
{

return array();

}

/*
    Function:  deleteRow
    Description:  deletes row from table based on editId passed in.  assumes that row id is 'id';  If not, child class should override this.
    Parameters:  postArray, editId, tableName
    Returns:  N/A

*/
public function deleteRow($postArray,$editId,$tableName)
{
    $retString='';
    
   $retString=$this->deletePreProcessing($postArray,$editId);
   
   $sql="delete from $tableName where id=$editId";
   
   $ret=$this->updateTableData($sql,false);
                
}

/*
    Function:  validateDelete
    Description:  performs any error checks that should be done prior to deleting row.  Child class should overriden with custom logic, if needed.
    Parameters: postArray, editId
    Returns:  string with error message, empty string if validation passes.

*/
public function validateDelete ($postArray,$editId) 
{
    $retMesg='';
    return $retMesg;

}

/*
    Function: deletePreProcessing
    Description:  performs any preprocessing necessary when deleting a row.  This is called after validation, if delete validation passes.
                         This should be overridden by child class with custom logic.
    Parameters:  $postArray, $editId.
    Returns:  string with any error message.

*/
public function deletePreProcessing($postArray,$editId)
{
    $retMesg='';
    return $retMesg;
}

/*
    Function: postFile
    Description:  performs any post filing logic when filing a row.  
                         This should be overridden by child class with custom logic if needed
    Parameters:  rowId - id of row that was just added/updated.
                        $newRecord = true if new row added, false if update to existing row.
    Returns:  string with any error message.

*/

public function postFile($rowId,$newRecord)
{
    $retMesg='';

    return $retMesg;

}

/*
    Function:  editRecordConfirmMessage
    Description:  returns message to display on front end if editing an existing row, to confirm saving changes.  This will be overriden
                         by table classes.
    Parameters:  editId - id of row being edited.
    Returns:  string with error message, empty string if edit confirm message is not required.

*/

public function editRecordConfirmMessage($editId)
{

    return "";
}
/* Function mergeErrorArrays
    Description:  This function merges 2 arrays in the format array[columnName]= error message, and then puts them in the correct format for front ends to display.
                          array[cnt]['id']=columnName
                          array[cnt]'errorMessage']=errorMessage
                          
    Parameters:  array1,  array2
    
    Return Value  = empty array, or array in format documented above for front ends to display.
    
*/

private function mergeErrorArrays($array1,$array2) {
    
    $returnArray=array();
    $cnt=0;
    
     foreach ($this->tableStructure as $index=>$fieldArray) {
        $columnName=$fieldArray['columnName'];
        
        $errorMessage='';        
        $mesg1=isset($array1[$columnName]) ? $array1[$columnName] : '';
        $mesg2=isset($array2[$columnName]) ? $array2[$columnName] : '';
        
        if (($mesg1 !== '') && ($mesg2 !== '')) {
            $errorMessage=$mesg1.'  '.$mesg2;
        }
        else if ($mesg1 !== '') {
            $errorMessage=$mesg1;        
        }
        else if ($mesg2 !== '') {
            $errorMessage=$mesg2;        
        }
        
        if ($errorMessage !== '') {
            $returnArray[$cnt]['id']=$columnName;
            $returnArray[$cnt]['errorMessage']=$errorMessage;
            $cnt++;      
        }
    }
    
    return $returnArray;   
}
   
 
}

?>
