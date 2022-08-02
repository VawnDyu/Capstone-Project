function closemodal(e){
    let modalItself = e.parentElement.parentElement;
    modalItself.removeAttribute('id');
}

let exitModalViewGuard = document.querySelector("#exit-modal-viewguard");
exitModalViewGuard.addEventListener('click', e => {
    let viewguardModal = document.querySelector('.modal-viewguard');
    viewguardModal.style.display = "none";
});

// deleteguard modal exit btn
let exitModalDeleteGuard = document.querySelector("#exit-modal-deleteguard");
exitModalDeleteGuard.addEventListener('click', e => {
    let deleteguardModal = document.querySelector('.modal-deleteguard');
    deleteguardModal.style.display = "none";
});