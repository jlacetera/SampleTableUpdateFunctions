<?php
 
/*  Revision History

    SMC-431 JL 10/2015 - Initial Version.  Supports  bev_categories table.
        ** table notes:  Only define fields in table that are used.  This is id, name, is_active, glass_sizes, attributeClasses.
                   Fields that aren't used will default to correct values when inserting new row.  This fixed issues where some unused columns had
                      different names in different SMC databases.  Fields that are used have the same names.
*/ 
 
/**
* Class Name:  BevCategories
* Class Description:  Contains all logic/code to support maintaining table thru admin managers tab.
                                 The tableStructure array must be filled out for the table.  See descriptions of fields/array elements below
                                 
                                 columnName :  column name in sql table.
                                 label              :  label that will appear on the input/edit form.
                                 required          :  0 = field not required on input form, 1 = field required on input form
                                 hideOnBlank    :  1 = hide the field on input/edit form if entry is blank, 0 = don't hide.
                                                           This can be set for fields that aren't used in the table, so that they don't show up on add/edit form.
                                 readonly:            readonly = make field readonly on input/edit form, ""  = don't make field readonly.
                                 
  Properties:
        $tableStructure - array with table structure required to display and file the data in the database.
        $tableName - the db name of the table.
   
        
*/ 
 
class BevCategories extends TableAdminGateway
{
   protected $tableStructure= array(
                                array("columnName"=>"id",
                                                        "label"=>"id",
                                                        "required"=>0,
                                                         "hideOnBlank"=>1,
                                                         "value"=>'',
                                                         "readonly"=>"readonly"),  //using readonly so that on edit the id is included in post array.
                                      array("columnName"=>"name",
                                                        "label"=> "Name",
                                                         "required"=>1,
                                                         "hideOnBlank"=>0,
                                                         "value"=>'',
                                                         "maxLength"=>90,
                                                         "readonly"=>''),
                                    
                                      array("columnName"=>'glass_sizes',
                                                                "label" => "Glass Size",
                                                               "required"=> 1,
                                                               "hideOnBlank"=> 0,
                                                               "value"=> "",
                                                               "readonly"=>'',
                                                                 "type"=>"SELECT",
                                                               "choices"=>array("0"=>"No",
                                                                                            "1"=>"Yes")
                                                               ),
                                     array("columnName"=>'is_active',
                                                                "label" => "Active",
                                                               "required"=> 1,
                                                               "hideOnBlank"=> 0,
                                                               "value"=> "",
                                                               "readonly"=>'',
                                                               "type"=>"SELECT",
                                                               "choices"=>array("0"=>"No",
                                                                                            "1"=>"Yes")
                                                                 ),
                                                                                               
                                   array("columnName"=>'attributeClasses',
                                                                "label" => "Attribute Ids",
                                                               "required"=> 0,
                                                               "hideOnBlank"=> 0,
                                                               "value"=> "",
                                                               "readonly"=>'',
                                                                 )
                                                        
                                                             
                             );    
                                                                                                
   protected $tableName='bev_categories';
   
	public function __construct()
	{
	   parent::__construct();
	}


/*
    Function:  validateDelete
    Description:  does some validation prior to deleting a category.  Checks so that category ids < 5 can't be deleted, and 
                        categories that have entries in the wines table cannot be deleted.
    Parameters:  $postArray,  $editId - id of bev category that will be deleted.
    Returns:  string with error message in it.

*/
public function validateDelete($postArray,$editId)
{
    $error='';
    
    if ($editId < 6) {
        $error="DELETE ERROR:  Category Ids 1 thru 5 cannot be deleted.";    
    } 
    else {  //make sure that there aren't any entries in wine table with this category id.
        $sql="select id from wines where category_id= $editId";
        $retArray=$this->getTableData($sql,true,true);
        if (count($retArray)) {
            $error="DELETE ERROR:  There are entries in the wines table with this category.  It cannot be deleted.";        
        }
    
    }

    return $error;
}

/* Function: deletePreProcessing
    Description:  deletes rows from tables that must be updated prior to deleting a bev_category.  Bev categories can only be deleted if there isn't any
                         data in the wines table, so only a few tables need to be cleaned up - en_wine_types, Globals.
    Parameters:  postArray, editId - id of bev_category row that is being deleted.
    Returns:  empty string on success.  Error handling is not implemented.


*/

public function deletePreProcessing($postArray,$editId)
{

   //delete child tables for this category id.
    $sql="delete from en_wine_types where bev_category = $editId";
    $this->updateTableData($sql,false);   
    
    //also delete field list rows from globals table
    $fieldValue="FieldList_".$editId; 
    $sql="delete from Globals where name = '".$fieldValue."'";
    $this->updateTableData($sql,false); 
    
     $fieldValue="FieldOption_".$editId;
     $sql="delete from Globals where name = '".$fieldValue."'";
     $this->updateTableData($sql,false); 
     
     //delete any rows that might exist in pairings
     $sql="delete from item_relations where parent_category = $editId OR child_category = $editId";
     $this->updateTableData($sql,false); 
     
     //delete any rows that might exist in collections.
     $sql="delete from collection_types where category_id = $editId";
     $this->updateTableData($sql,false); 
     
     //delete any client criterion for this category (attribute tables)
     //Not deleting from attributes table, because this attribute could be attached to several categories.
     //not deleting from attribute_values, because if attribute is associated with more than 1 category, these values are used by the other categories.
   
     $sql="delete from attribute_to_category where category_id = $editId ";
     $this->updateTableData($sql,false); 
     
     return "";
}

/*
    Function:  postFile
    Description:  files entry in the Globals table with defaulted fields for food beverage category type for newly added rows.
    Parameters:  newId:  id of row that was just updated
                        newRecord:  true if newly filed record, false if this is an update.
    Returns:  N/A

*/

public function postFile($newId,$newRecord)
{
    
    if ($newRecord == true) {
        $value="wine_id,name,wine_type,in_stock,quantity,glass_size,glass_price,has_image";
        $name="FieldList_".$newId;
        $data["value"]=$value;
        $data["name"]=$name;
        $this->updateTableDataByColumnArray("Globals", $data);
    }
}

/*
    Function:  editRecordConfirmMessage
    Description:  returns message to display on front end if editing an existing row, to confirm saving changes.  If data exists in wines table for this category,
                        a warning will be displayed on the front end before the user can make changes to it.
    Parameters:  editId - id of row being edited.
    Returns:  string with error message, empty string if edit confirm message is not required.

*/

public function editRecordConfirmMessage($editId)
{
    $retMesg='';
    if ($editId !== '') {
        $sql="select id from wines where category_id= $editId";
        $retArray=$this->getTableData($sql,true,true);
        if (count($retArray)) {
            $retMesg="Data exists for this category.  Are you sure you want to make these changes, and that these changes will not break admin or client side functioning?";
        }
    }
    
    return $retMesg;
}
}

?>
