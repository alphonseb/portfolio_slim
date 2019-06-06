'use strict';

var catSelect = document.querySelector('.js-cat-select');
var newCatInput = document.querySelector('.js-new-cat');

if (catSelect) {
    if (catSelect.options[catSelect.selectedIndex].value !== 'default') {
        newCatInput.style.display = 'none';
    }
    catSelect.addEventListener('change', function (e) {

        if (e.target.options[e.target.selectedIndex].value !== 'default') {
            newCatInput.style.display = 'none';
        } else {
            newCatInput.style.display = 'initial';
        }
    });
}
'use strict';

var catInputs = document.querySelectorAll('.projects input[type="checkbox"]');
var values = [];
var resultContainer = document.querySelector('main.projects .js-result');

if (catInputs != null) {
    var _iteratorNormalCompletion = true;
    var _didIteratorError = false;
    var _iteratorError = undefined;

    try {
        for (var _iterator = catInputs[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
            var _input = _step.value;

            _input.addEventListener('change', function (_e) {
                if (_e.target.checked) {
                    values.push(_e.target.value);
                } else {
                    values = values.filter(function (_val) {
                        return _val !== _e.target.value;
                    });
                }
                var postData = new FormData();
                postData.append('values', JSON.stringify(values));

                window.fetch("#", {
                    method: "POST",
                    body: postData
                }).then(function (res) {
                    return res.text();
                }).then(function (data) {
                    resultContainer.innerHTML = data;
                });
            });
        }
    } catch (err) {
        _didIteratorError = true;
        _iteratorError = err;
    } finally {
        try {
            if (!_iteratorNormalCompletion && _iterator.return) {
                _iterator.return();
            }
        } finally {
            if (_didIteratorError) {
                throw _iteratorError;
            }
        }
    }
}