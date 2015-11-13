<?php
 
/*  Revision History

    SMC-396 JL 7/2015 - Initial Version.  Supports client_labels and languages_supported tables.  Additional tables can be added easily.
    SMC-431  JL 10/2015 - Cleaned up a bit.

*/ 
 
/**
* Class Name:  LanguagesSupported
* Class Description:  Contains all logic/code to support maintaining table thru admin managers tab.
                                 The tableStructure array must be filled out for the table.  See descriptions of fields/array elements below
                                 
                                 columnName :  column name in sql table.
                                 label              :  label that will appear on the input/edit form.
                                 required          :  0 = field not required on input form, 1 = field required on input form
                                 hideOnBlank    :  1 = hide the field on input/edit form if entry is blank, 0 = don't hide.
                                 readonly:            readonly = make field readonly on input/edit form, ""  = don't make field readonly.
                                 
 Properties:
        $tableStructure - array with table structure required to display and file the data in the database.
        $tableName - the db name of the table.
*/ 
 
class ClientLabels extends TableAdminGateway
{
	
   protected $tableStructure= array(
                                                 array("columnName"=>"id", 
                                                        "label"=>"id",
                                                        "required"=>0,
                                                         "hideOnBlank"=>1,
                                                         "value"=>'',
                                                         "readonly"=>"readonly"),  //using readonly so that on edit the id is included in post array.
                                                 array("columnName"=>"label_key",
                                                        "label"=> "Key",
                                                         "required"=>1,
                                                         "hideOnBlank"=>0,
                                                         "value"=>'',
                                                         "readonly"=>'',
                                                         "maxLength"=>200),
                                                array("columnName"=>"label",
                                                          "label" => "Label/Description",
                                                          "required"=> 1,
                                                          "hideOnBlank"=> 0,
                                                          "maxLength"=>1000,
                                                          "value"=>'',
                                                          "readonly"=>""), 
                                               array("columnName"=>"max_length",
                                                        "label" => "Maximum Length",
                                                        "required"=> 0,
                                                        "hideOnBlank"=> 0,
                                                        "value"=> "",
                                                        "readonly"=>'')   
                             );                                                                            
      
   
   protected $tableName='client_labels';
   
	public function __construct()
	{
        parent::__construct();    	   
	}

/* Function Name:  validateTableDataToFile
    Description:  validates the data passed into function (probably from post array), before filing in database.
                         validates that label_key is unique.
                     
   Parameters:  
                                           postArray - array of [columnNames]=value - that should be validated before filing in database.
                                           $editId - if sent, then update or delete.  IF not sent - then adding new record.  id in table should be 'id';
                                          
    Returns:  returnMessage - empty array on success, array of errors in format array[columnName]='error message for display'
                                                                                                                         
*/

public function validateTableDataToFile($postArray,$editId) 
{
    $errorMesg=array();
    
     $mesg=$this->checkForUniqueValue($this->tableName,'label_key',$postArray['label_key'],$editId); 
     
    if ($mesg != '') {
        $errorMesg['label_key']=$mesg;
     }
          
    return $errorMesg;
   
}
 
}

?>
