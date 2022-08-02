let exitAddMore = document.querySelector('#exit-addmore-modal');
let exitAddMoreModal = document.querySelector('.addmore-modal');

exitAddMore.addEventListener('click', e =>{
    exitAddMoreModal.style.display = "none";
});

// open modal
let addmoreEmp = document.querySelector('.addmore-emp');
addmoreEmp.addEventListener('click', e => {
    let addMoreModal = document.querySelector('.addmore-modal');
    addMoreModal.style.display = 'block';
});


// when user pick 1 company
function populate(e){
    let opt = e.selectedIndex;
    
    // call select's position
    let positionsArray = document.querySelectorAll('.position'); // object
    let location = document.querySelector('#location');

    let prices = document.querySelectorAll('.price');
    let ot = document.querySelectorAll('.ot');

    // remove all prices and ot when company changed
    prices.forEach(pri => pri.value = '' );
    ot.forEach(o => o.value = '' );


    if(opt != ''){
        // for location
        let optLocValue = e.options[opt].dataset.loc;
        location.value = optLocValue;

        // for positions, price, ot
        let optPosString = e.options[opt].dataset.pos;
        let optPriceString = e.options[opt].dataset.price;
        let optOtString = e.options[opt].dataset.ot;

        // convert to array separeted by comma
        let optPosArray = optPosString.split(',');
        let optPriceArray = optPriceString.split(',');
        let optOtArray = optOtString.split(',');

        // empty select position before add data
        positionsArray.forEach((pos) => {
            pos.innerText = '';
        });

        positionsArray.forEach((pos) => {
            let defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.innerText = 'Select Position';
            pos.appendChild(defaultOpt);


            for(let i = 0; i < optPosArray.length; i++){
                let option = document.createElement('option');              // create option
                option.setAttribute('data-posprice', optPriceArray[i]);     // set price
                option.setAttribute('data-posot', optOtArray[i]);           // set ot
                option.value = optPosArray[i];                              // option value = pos
                option.innerText = optPosArray[i];                          // option text  = pos
                pos.appendChild(option);
            }
        });
    } else {
        location.value = 'Auto fill';
        positionsArray.forEach((pos) => {
            pos.innerText = '';
            
            let defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.innerText = 'Select Position';
            pos.appendChild(defaultOpt);

        });
    }
}

// setter of price and ot when position change
function getPrice(e){
    let opt = e.selectedIndex;
    
    // TD
    let parentContainer = e.parentElement;
    // remove value
    parentContainer.querySelector('.price').value = '';
    parentContainer.querySelector('.ot').value = '';

    if(e.options[opt].dataset.posprice == undefined){
        // set no value
        parentContainer.querySelector('.price').value;
        parentContainer.querySelector('.ot').value;
    } else {
        // set new value
        parentContainer.querySelector('.price').value = e.options[opt].dataset.posprice;
        parentContainer.querySelector('.ot').value = e.options[opt].dataset.posot;
    }

    
}



function removeMe(me){
    let currentUrl = window.location.href;

    let myId = me.dataset.deleteid; // 22
    let lengthIdIndex = myId.length; // 2

    let startingPoint = currentUrl.indexOf("="); // find my index
    let endingPoint = currentUrl.length;

    let outputMe = currentUrl.substr(startingPoint + 1, endingPoint); // all ids
    let outputArray = outputMe.split(',');

    // remove single id
    let filteredArray = outputArray.filter( arr =>  arr != myId );

    // current filename
    let fileName = "selectedGuards.php?ids=";
    filteredArray.forEach(arr => {
        fileName += arr +',';
    });

    if(fileName.charAt(fileName.length - 1) == ','){
        fileName = fileName.substr(0, fileName.length - 1);
    }

    window.location.replace(fileName);
}

document.addEventListener('DOMContentLoaded', runMe);

function runMe(){
    let currentUrl = window.location.href;
    
    let startingPoint = currentUrl.indexOf("="); // find my index
    let endingPoint = currentUrl.length;

    let myIds = currentUrl.substr(startingPoint + 1, endingPoint);
    
    let myIdsArray = myIds.split(',');

    let dodelete = document.querySelectorAll('.doDelete');
    let dodeleteArray = Object.values(dodelete);

    dodeleteArray.forEach(dodel => {
        let empIdDelete = dodel.dataset.empiddelete;

        // 0, 1, 2
        for(let i = 0; i < myIdsArray.length; i++){
            if(empIdDelete == myIdsArray[i]){
                dodel.remove();
            }
        }
    });
}

function redirectAgain(){
    let ids = document.querySelector('#ids');
    let currentUrl = window.location.href + "," +ids.value;

    window.location.assign(currentUrl);
}


// for expiration date
function setYear(iyear)
{
    let imonth = document.querySelector('#month');
    let iday = document.querySelector('#day');

    if(iyear.selectedIndex == 2){
        imonth.value = '';
        iday.value = '';

        // disable
        imonth.disabled = true;
        iday.disabled = true;
    } else {
        // enable
        imonth.disabled = false;
        iday.disabled = false;
    }
}

function setMonthDay(e)
{
    let iyear = document.querySelector('#year');

    let inputId = e.getAttribute('id');
    if(e.value > 0){
        if(iyear.options[2]){
            // remove 2 in option
            iyear.removeChild(iyear.options[2]);
            iyear.required = false;
        }

        
    } else {
        // add 2 in option
        let opt = document.createElement('option');
        opt.value = '2';
        opt.innerText = 2;

        iyear.appendChild(opt);
        iyear.required = true;
    }
}



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