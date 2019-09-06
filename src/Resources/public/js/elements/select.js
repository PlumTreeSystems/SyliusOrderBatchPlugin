function select(element) {
    displayValue(element);

    $(element).on('click', function (e) {
        handleEvents(e);
    });

    document.addEventListener("click", function (e) {
        if ($(e.target).closest('.select').length === 0) {
            closeAllLists();
        } else if (
            $(e.target).closest('.selectItem').length === 1 &&
            $($(e.target).closest('.select')[0]).children('.select_display').data('onSubmit')
        ) {
            let span;

            if ($(e.target).hasClass('selectItem')) {
                span = $($(e.target).children('span'));
            } else {
                const div = $(e.target).closest('.selectItem')[0];
                span = $(div).children('[data-select-item-source="true"]');
            }
            const value = span.data('val');
            const title = span.data('title');

            $($(e.target).closest('.select')[0]).children('input[type="hidden"]').val(value);

            changeActiveElement(e.target, value, title);
            $(e.target).closest('form').submit();
            closeAllLists();

        }
    });

    function handleEvents(e) {
        let i, optionsElements, options = [];

        const thisElement = $(e.target);
        const dataAttributes = thisElement.data();
        const label = $(thisElement.parent().children('label')[0]);
        optionsElements = label.children('div');

        for(i = 0; i < optionsElements.length; i++) {
            options.push($(optionsElements[i]).data());
        }
        closeAllLists();

        generateOptions(options, dataAttributes, thisElement);
    }

    function displayValue(elem) {
        const baseElement = $(elem);

        let dataAttr = baseElement.data();

        baseElement.text(dataAttr.title);
        $('#' + dataAttr.inputId).val(dataAttr.value);
    }

    function closeAllLists(elem) {
        let x = document.getElementsByClassName("select-items");
        for (let i = 0; i < x.length; i++) {
            if (elem != x[i] && elem != element) {
                x[i].parentNode.removeChild(x[i]);
            }
        }
    }

    function changeActiveElement(elem, activeId, title) {
        let selectItems = $(elem).parent().children();
        let displayDiv = $(elem).parent().parent().children('.select_display');

        displayDiv.text(title);

        for (let i = 0; i < selectItems.length; i++) {
            if ($(selectItems[i]).children('span').data('val') === activeId) {
                $(selectItems[i]).addClass('active');
            } else {
                $(selectItems[i]).removeClass('active');
            }
        }
    }

    function generateOptions(options, dataAttributes, elem) {
        let i, optionsList;
        optionsList = document.createElement("DIV");
        optionsList.setAttribute("class", "select-items");
        elem[0].parentNode.appendChild(optionsList);

        const activeId = $(elem[0]).parent().children('input')[0].value;

        for (i = 0; i < options.length; i++) {
            generateListItem(optionsList, options[i], dataAttributes.submitButtonText, activeId);
        }
    }

    function generateListItem(listItem, item, addButtonTitle = '', activeId) {
        let span, matchingDiv;
        matchingDiv = document.createElement("DIV");
        if (activeId == item.id) {
            matchingDiv.setAttribute("class", "selectItem active");
        } else {
            matchingDiv.setAttribute("class", "selectItem");
        }

        span = document.createElement("SPAN");
        span.setAttribute("data-val", item.id);
        span.setAttribute("data-title", item.title);
        span.setAttribute("data-select-item-source", true);

        span.innerHTML = item.title;

        matchingDiv.appendChild(span);
        listItem.appendChild(matchingDiv);
    }
}

for (let i = 0; i < $('div[data-select=true]').length; i++) {
    select($('div[data-select=true]')[i]);
}
