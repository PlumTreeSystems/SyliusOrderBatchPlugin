function autocomplete(inp) {

    inp.addEventListener("input", function(e) {
        handleEvents(e);
    });

    $(inp).focus(function (e) {
        if($(e.target).parent().has('.autocomplete-items').length === 0) {
            handleEvents(e);
        }
    });

    function handleEvents(e) {
        let val = e.target.value, input = e.target, id = e.target.id;

        const dataAttributes = $('#'+ id).data();

        const data = {
            'phrase': val,
            'id': dataAttributes['id']
        };

        $.ajax({
            url: dataAttributes['url'],
            type: "GET",
            data: data,
            contentType: 'application/json',
            success: function (res) {
                closeAllLists();

                generateOptions(res, dataAttributes, input, dataAttributes['basePath']);
            }
        });
    }


    function closeAllLists(element) {
        let x = document.getElementsByClassName("autocomplete-items");
        for (let i = 0; i < x.length; i++) {
            if (element != x[i] && element != inp) {
                x[i].parentNode.removeChild(x[i]);
            }
        }
    }

    document.addEventListener("click", function (e) {
        if ($(e.target).closest('.autocomplete-items').length === 0) {
            closeAllLists(e.target);
        }
    });

    function generateOptions(data, attributes, input, basePath) {
        let i, optionsList;
        optionsList = document.createElement("DIV");
        optionsList.setAttribute("class", "autocomplete-items");
        input.parentNode.appendChild(optionsList);

        for (i = 0; i < data.length; i++) {
            generateListItem(optionsList, data[i], attributes.submitButtonText, attributes.method, basePath);
        }
    }

    function generateListItem(listItem, item, addButtonTitle, method, basePath) {
        let matchingDiv, image, form, table, imageTd, priceTd, titleTd, priceTr, firstTr, submitButton;

        table = document.createElement("TABLE");
        table.setAttribute("class", "autocompleteTable");

        form = document.createElement("FORM");

        image = document.createElement("IMG");
        matchingDiv = document.createElement("DIV");
        matchingDiv.setAttribute("class", "autocompleteItem");

        firstTr = document.createElement("TR");

        imageTd = document.createElement("TD");
        imageTd.setAttribute('rowSpan', 2);
        image.setAttribute("src", basePath + '/media/image/' + item['image']);
        image.setAttribute("class", 'autocompleteImg');
        imageTd.appendChild(image);
        firstTr.appendChild(imageTd);

        titleTd = document.createElement("TD");
        titleTd.setAttribute("class", 'autocompleteTextTd');
        titleTd.innerHTML += "<span>" + item['title'] + "</span>";
        firstTr.appendChild(titleTd);

        priceTd = document.createElement("TD");
        priceTd.setAttribute("class", 'autocompleteTextTd');
        priceTd.innerHTML += "<span>" + item['price'] + "</span>";

        priceTr = document.createElement("TR");
        priceTr.appendChild(priceTd);

        table.appendChild(firstTr);
        table.appendChild(priceTr);

        form.appendChild(table);

        submitButton = document.createElement("INPUT");
        submitButton.setAttribute('type', 'submit');
        submitButton.setAttribute('class', 'ui icon primary button');
        submitButton.setAttribute('value', addButtonTitle);
        submitButton.setAttribute('id', 'product-to-add-' + item['id']);

        matchingDiv.appendChild(form);
        form.appendChild(submitButton);
        form.innerHTML += "<input type='hidden' name='selectResourceId' value='" + item['id'] + "'/>";
        form.setAttribute('method', method);

        listItem.appendChild(matchingDiv);
    }
}

for (let i = 0; i < $('input[data-autocomplete=true]').length; i++) {
    autocomplete($('input[data-autocomplete=true]')[i]);
}
