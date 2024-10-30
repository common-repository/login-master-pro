function deletechecked()
{
    var answer = confirm("Are you sure that you want to delete it?")
    if (answer){
        document.messages.submit();
    }
    
    return false;  
}