const deleteButtons = document.querySelectorAll('.js-delete')

for (const _delete of deleteButtons) {
    _delete.addEventListener('click', (_e) => {
        const postData = new FormData()
        postData.append('project_id', _e.target.dataset.projectId)


        window.fetch("#",
            {
                method: "POST",
                body: postData
            })
            .then(res => res.json())
            .then(data => {
                
                if (data.res == true) {
                    document.querySelector(`#project-${_e.target.dataset.projectId}`).remove()
                }
            })
    })
}