
let arrayIds = [];
function setVal(e, id){
    let ids = document.querySelector('#ids');
    

    if(e.checked){
        if(ids.value == ''){
            arrayIds.push(id);
            ids.value += arrayIds;
        } else {

            if(!arrayIds.includes(id)){
                arrayIds.push(id);
                ids.value = arrayIds;
            }
        }
    }

    if(e.checked != true){
        let newArray = arrayIds.filter( arrayId => arrayId != id);
        arrayIds = newArray;
        ids.value = newArray;
    }
    console.log(arrayIds);
}