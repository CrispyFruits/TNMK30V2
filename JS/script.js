function redirectMySets(){
    // navigate to new page
    window.location.href = 'index.php';
}
function redirectAllSets(){
    // navigate to new page
    window.location.href = 'allsets.php';
}
function redirectsearch_Results(){
    // navigate to new page
    window.location.href = 'search_results.php';
}

function hideCompleteSet(){
    document.getElementById("completeTable").classList.toggle("show");
}

function changeColor(colorID, SetID, setName, partID){
    
    window.location.href = 'my_parts.php?partID=' + partID + '&colorID=' + colorID + '&setID=' + SetID + '&setName=' + setName;
    
}