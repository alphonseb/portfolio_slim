const catInputs = document.querySelectorAll('.projects input[type="checkbox"]')
let values = [];
const resultContainer = document.querySelector('main.projects .js-result');

if (catInputs != null) {
    for (const _input of catInputs) {
        _input.addEventListener('change', (_e) => {
            if (_e.target.checked) {
                values.push(_e.target.value)
            }
            else {
                values = values.filter((_val) => {
                    return _val !== _e.target.value
                })
            }
            const postData = new FormData()
            postData.append('values', JSON.stringify(values))
            

            window.fetch("#",
                {
                    method: "POST",
                    body: postData
                })
                .then(res => res.text())
                .then(data => {
                    resultContainer.innerHTML = data
                })
            
        })
    }
}