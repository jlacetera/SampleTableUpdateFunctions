<?php
 
/*  Revision History

    SMC-396 JL 7/2015 - Initial Version.  Supports client_labels and languages_supported tables.  Additional tables can be added easily.
    SMC-431  JL 10/2015 - Cleaned up a bit.

*/ 
 
/**
* Class Name:  LanguagesSupported
* Class Description:  Contains all logic/code to support maintaining table thru admin managers tab.
                                 The tableAddStructure array must be filled out for the table.  See descriptions of fields/array elements below
                                 
                                 columnName :  column name in sql table.
                                 label              :  label that will appear on the input/edit form.
                                 required          :  0 = field not required on input form, 1 = field required on input form
                                 hideOnBlank    :  1 = hide the field on input/edit form if entry is blank, 0 = don't hide.
                                 readonly:            readonly = make field readonly on input/edit form, ""  = don't make field readonly.
                                 
  Properties:
        $tableStructure - array with table structure required to display and file the data in the database.
        $tableName - the db name of the table.
       
        
*/ 
 
class LanguagesSupported extends TableAdminGateway
{
	
   protected $tableStructure= array(
                                array("columnName"=>"id",
                                                        "label"=>"id",
                                                        "required"=>0,
                                                         "hideOnBlank"=>1,
                                                         "value"=>'',
                                                         "readonly"=>"readonly"),  //using readonly so that on edit the id is included in post array.
                                      array("columnName"=>"language",
                                                        "label"=> "Language",
                                                         "required"=>1,
                                                         "hideOnBlank"=>0,
                                                         "value"=>'',
                                                         "maxLength"=>200,
                                                         "readonly"=>''),
                                      array("columnName"=>"label",
                                                                "label" => "Label/Description",
                                                               "required"=> 1,
                                                               "hideOnBlank"=> 0,
                                                               "value"=>'',
                                                               "maxLength"=>200,
                                                               "readonly"=>""), 
                                      array("columnName"=>'piece',
                                                                "label" => "Piece In Delimited String",
                                                               "required"=> 1,
                                                               "hideOnBlank"=> 0,
                                                               "value"=> "",
                                                               "readonly"=>''),
                                     array("columnName"=>'text_size',
                                                                "label" => "Text Size",
                                                               "required"=> 0,
                                                               "hideOnBlank"=> 0,
                                                               "value"=> "",
                                                               "readonly"=>'',
                                                               "type"=>"SELECT",
                                                               "choices"=>array("small"=>"small",
                                                                                            "medium"=>"medium",
                                                                                            "large"=>"large")
                                                                                            )
                             );                                                                            
      
   
   protected $tableName='languages_supported';
   private $gateway;
   
	public function __construct()
	{
	    parent::__construct();
	}

/* Function Name:  validateTableDataToFile
    Description:  validates the data passed into function (probably from post array), before filing in database.
                         validates that 'piece' is unique.
                     
   Parameters:  
                                           postArray - array of [columnNames]=value - that should be validated before filing in database.
                                           $editId - if sent, then update or delete.  IF not sent - then adding new record.  id in table should be 'id';
                                          
    Returns:  returnMessage - empty array on success, array of errors in format array[columnName]='error message for display'
                                                                                                                       
*/

public function validateTableDataToFile($postArray,$editId) 
{
    $errorMesg=array();
 
    $mesg=$this->checkForUniqueValue($this->tableName,'piece',$postArray['piece'],$editId);  
    if ($mesg != '') {
        $errorMesg['piece']=$mesg;
     }
     
    return $errorMesg;
}
 
}

?>
