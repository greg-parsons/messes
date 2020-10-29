/*
* Small JS helper functions that don't need their own files
*/

export function showJsOnlyContent() {
    const hidden_elems = document.querySelectorAll('.js-hidden');
    if(hidden_elems.length > 0) {
        hidden_elems.forEach(elem => {
            elem.classList.remove('js-hidden');
        });
    }
}