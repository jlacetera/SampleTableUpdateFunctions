
/* Revision History

    SMC-396 JL 7/2015  - Initial Verison.  Supports template editTable.tpl
    SMC-431 JL 10/2015 - Fixed field valiation for languages_supported piece, to not allow decimal in entry.
                                        Added edit confirmation when filing an edit record, based on back end flag/message.
    
*/

/* document ready function */
$( document ).ready(function() {
      highlightTab('manager');
});

/* validateForm - validates required fields, and then does some specific validation based on each table */

function validateForm(thisForm,tableName) 
{
    var dispError='';
    var returnVal=true;
    var deleteFlag=$('#deleteFlag').val();
    var fieldVal='';
    var id;
    var errorMessage='';
    var editId;
    var editRecordConfirmMessage=$('#editRecordConfirmMessage').val();
    
    $('#FILING_error').hide();
    
    if (deleteFlag == 0) {
 
        //check for required fields first.
       dispError=checkRequiredFieldsError();
        
        if (dispError != '') {
            returnVal=false;
        }
        else {  //now that all required fields are entered - do any table/field specific validation that can be done on client side instead of server side.
            switch (tableName) {
                case 'client_labels':
                    id='label_key';              
                    dispError='';
                    fieldVal=$('#label_key').val();
                    errorMessage= "Key Invalid.  Must start with upper case letter and include only A-Z, 0-9 and _ ."
                    
                    //first validate that it doesn't contain any blank fields.
                    if (/\s/g.test(fieldVal)) {
                        dispError=errorMessage;
                        returnVal=false;
                    }
                    //first validate that it must start with uppercase.
                    else if (/[^A-Z]/.test(fieldVal.charAt(0))) {
                        dispError=errorMessage;
                        returnVal=false;
                    }
                    //then validate that it only has A-Z,0-9,_
                    else if (/[^A-Z0-9_]+$/.test(fieldVal)) {    
                        dispError=errorMessage;
                        returnVal=false;
                    }
                
                    updateError(id,dispError);                    
                    break;
                case 'languages_supported':
                    //validate that piece is a number. 
                    id='piece';
                    dispError='';
                    fieldVal=$('#'+id).val();
                    errorMessage= "Piece Invalid.  Must be an integer."
                    if (/[^0-9]+$/.test(fieldVal)) {
                         dispError=errorMessage;   
                         returnVal=false;                
                    }
                    updateError(id,dispError);  
                    break;
                default:
                    break;        
            } 
        }
        //if we get to here without an error, then check edit display message - confirm that edit is ok.
        if (returnVal !== false && editRecordConfirmMessage !=='') {
            if (confirm(editRecordConfirmMessage)) {
                returnVal=true;        
            }   
            else {
                returnVal=false;        
            }
        }
   }
   else {  //need to confirm that delete ok - give message/popup to user for delete.
        if (confirm('Are you sure you want to delete this item?')) {
            returnVal=true;        
        }
        else {
            returnVal=false;        
        }
   }
   return returnVal;
}    

/* id is the field id to display/clear error message.
    errorMessage = if blank then clear error, if set then display error.
*/
function updateError(id,errorMessage) {

var errorId=id+'_error';

 $('#'+errorId).html(errorMessage); 
 
    if (errorMessage === '') {
        $('#'+id).removeClass('fieldError');
        $('#'+errorId).hide();
    }
    else {
        $('#'+id).addClass('fieldError');
        $('#'+errorId).show();
    }

}

/* function setDeleteFlag - sets a hidden field on form with flag if delete selected, so that required field checking and validation is skipped on delete */
function setDelete(flag) {
    //set flag so that validation knows whether or not to skip.  No validation on deleting a record.
    $('#deleteFlag').val(flag);

}

/* function checkRequiredFieldsError - checks that all required fields (fields with class 'requiredField') has a value entered */
/* returns message if error, blank on success */
function checkRequiredFieldsError() {
 
    var value='';
    var id='';
    var returnMesg='';
    var errorId='';
    
    $('.requiredField').each(function() {
       id=$(this).attr('id');
        value = $(this).val();
        errorId=id+"_error";
        
        if (value.trim() === '') {
        //addClass and give error message
            returnMesg="Please enter all required fields.  Missing fields highlighted."; 
            $('#'+id).addClass('fieldError');  
            $('#'+errorId).html("Required Field");
            $('#'+errorId).show();
        }
        else {
            $('#'+id).removeClass('fieldError');   
            $('#'+errorId).hide();     
        }
        
      });
    return returnMesg;
}

