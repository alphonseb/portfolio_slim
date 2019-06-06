const catSelect = document.querySelector('.js-cat-select')
const newCatInput = document.querySelector('.js-new-cat')

if (catSelect) {
    if (catSelect.options[catSelect.selectedIndex].value !== 'default') {
        newCatInput.style.display = 'none'
    }
    catSelect.addEventListener('change', (e) => {
        
        if (e.target.options[e.target.selectedIndex].value !== 'default') {
            newCatInput.style.display = 'none'
        }
        else {
            newCatInput.style.display = 'initial'
        }
    })
}