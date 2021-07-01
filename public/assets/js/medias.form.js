window.onload = function () {

    let $modal = document.querySelector("#collection_form_builder_modal");
    let $modalType = document.querySelector("#modal_pick_media_type");
    let mediasCollectionContainer = document.querySelector(".medias-collections-container");
    let currentEditMedia = null;
    let currentForm = null;

    document.body.addEventListener("click", function (event) {
        let target = event.target;

        let $modalMode = $modal.getAttribute("data-mode");

        // Si on appuie sur le bouton d"ajout d"media
        if(target.id === "add_media")
        {

            $modalType.classList.remove("hidden");


            $modal.setAttribute("data-mode", "add");


            let prototype = mediasCollectionContainer.getAttribute("data-prototype");
            let index = mediasCollectionContainer.getAttribute("data-index");

            let newForm = prototype.replace(/__name__/g, index);
            mediasCollectionContainer.setAttribute("data-index", parseInt(index, 10) + 1);

            let $builder_content = document.querySelector("#collection_form_builder_content");
            mediasCollectionContainer.innerHTML += newForm;

            let listOfElements = mediasCollectionContainer.querySelectorAll("li");
            let lastElement = listOfElements[listOfElements.length - 1];
            let _form = currentForm = lastElement.querySelector(".collection_form");

            let $modalContent = $modal.querySelector("#collection_form_builder_content");
            _form.classList.remove("hidden");
            $modalContent.append(_form);

        }

        if(target.classList.contains("edit_media"))
        {
            event.preventDefault();
            $modal.setAttribute("data-mode", "edit");

            let currentMedia = currentEditMedia = target.closest("li");
            let currentMediaForm = currentMedia.querySelector(".collection_form");

            let $modalContent = $modal.querySelector("#collection_form_builder_content");
            currentMediaForm.classList.remove("hidden");


            $modalContent.append(currentMediaForm);
            $modal.classList.remove("hidden");

        }

        if(target.classList.contains("remove_media"))
        {
            $modal.setAttribute("data-mode", "remove");

            mediasCollectionContainer.removeChild(target.closest("li"));

        }

        if(target.id === "modal_save"){



            if($modalMode === "add")
            {
                let listOfElements = mediasCollectionContainer.querySelectorAll("li");
                let lastElement = listOfElements[listOfElements.length - 1];

                let $modalContent = $modal.querySelector("#collection_form_builder_content");
                let _form = $modalContent.querySelector(lastElement.getAttribute("data-collection-form"));
                _form.classList.add("hidden");
                lastElement.append(_form);
                $modal.classList.add("hidden");

                let inputLink = lastElement.querySelector("input[id$=link]");
                let inputType = currentForm.querySelector("select[id$=type]");
                //Si c"est de type "image" alors on affiche la prévisualisation
                if(inputType.value === "image")
                {
                    lastElement.querySelector(".media_image").src = inputLink.value;
                    lastElement.querySelector(".media_video").innerHTML = null;
                }
                if(inputType.value === "video")
                {
                    lastElement.querySelector(".media_image").src = "";
                    lastElement.querySelector(".media_video").innerHTML = inputLink.value;
                }

            }
            if($modalMode === "edit")
            {
                let $modalContent = $modal.querySelector("#collection_form_builder_content");
                let _form = $modalContent.querySelector(currentEditMedia.getAttribute("data-collection-form"));



                _form.classList.add("hidden");
                currentEditMedia.append(_form);
                let inputLink = currentEditMedia.querySelector("input[id$=link]");
                let inputType = _form.querySelector("select[id$=type]");
                currentEditMedia.querySelector("img").src = inputLink.value;
                $modal.classList.add("hidden");
                //Si c"est de type "image" alors on affiche la prévisualisation
                if(inputType.value === "image")
                {
                    currentEditMedia.querySelector(".media_image").src = inputLink.value;
                    currentEditMedia.querySelector(".media_video").innerHTML = null;
                }
                if(inputType.value === "video")
                {
                    currentEditMedia.querySelector(".media_image").src = "";
                    currentEditMedia.querySelector(".media_video").innerHTML = inputLink.value;
                }
            }
        }
        if(target.id === "modal_cancel"){
            if($modalMode === "add")
            {
                let listOfElements = mediasCollectionContainer.querySelectorAll("li");
                let lastElement = listOfElements[listOfElements.length - 1];

                let $modalContent = $modal.querySelector("#collection_form_builder_content");

                let _form = $modalContent.querySelector(lastElement.getAttribute("data-collection-form"));
                _form.classList.add("hidden");
                lastElement.append(_form);
                mediasCollectionContainer.removeChild(lastElement);
                $modal.classList.add("hidden")
            }

            if($modalMode === "edit")
            {
                let $modalContent = $modal.querySelector("#collection_form_builder_content");
                let _form = $modalContent.querySelector(currentEditMedia.getAttribute("data-collection-form"));
                _form.classList.add("hidden");
                currentEditMedia.append(_form);
                $modal.classList.add("hidden");
            }

        }

        if(target.id === "form_delete_button")
        {
            event.preventDefault();
            let form = document.querySelector("#form_delete");
            if(window.confirm("Souhaitez-vous réellement supprimer cette figure ?")){
                form.submit();
            }
        }

        if(target.id === "pick_media_image")
        {
            $modal.classList.remove("hidden");
            let inputType = currentForm.querySelector("select[id$=type]");
            inputType.value = "image";
            $modalType.classList.add("hidden");
            $modal.classList.remove("hidden");

        }
        if(target.id === "pick_media_video")
        {
            $modal.classList.remove("hidden");
            let inputType = currentForm.querySelector("select[id$=type]");
            inputType.value = "video";
            $modalType.classList.add("hidden");
            $modal.classList.remove("hidden");
        }
        if(target.id === "pick_media_cancel")
        {
            $modalType.classList.add("hidden");

            let listOfElements = mediasCollectionContainer.querySelectorAll("li");
            let lastElement = listOfElements[listOfElements.length - 1];

            let $modalContent = $modal.querySelector("#collection_form_builder_content");

            let _form = $modalContent.querySelector(lastElement.getAttribute("data-collection-form"));
            _form.classList.add("hidden");
            lastElement.append(_form);
            mediasCollectionContainer.removeChild(lastElement);
            $modal.classList.add("hidden")
        }

        if(target.id === "btn_submit")
        {
            document.forms[0].submit();
        }


    });

    //Active les collapsible
    var coll = document.querySelectorAll("#see_media");
    var i;

    for (i = 0; i < coll.length; i++) {
        coll[i].addEventListener("click", function(event) {
            event.preventDefault();
            this.classList.toggle("active");
            var content = this.nextElementSibling;
            if (!content.classList.contains("hide")) {
                content.classList.add("hide");
            } else {
                content.classList.remove("hide");
            }
        });
    }
}