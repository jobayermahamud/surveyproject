addEventListener("click", (e) => {
    if (e.target.getAttribute("custom_type") == "edit_option") {
        e.preventDefault();
        console.log(e.target.getAttribute("id_ref"));
        console.log(
            document.getElementById(
                `edit_option_text_value_${e.target.getAttribute("id_ref")}`
            ).value
        );

        $.ajax({
            url: e.target.href,
            type: "POST",
            data: {
                option_text: document.getElementById(
                    `edit_option_text_value_${e.target.getAttribute("id_ref")}`
                ).value,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).done(function () {
            alert("Updated");
        });
    }
});

document
    .getElementById("add_option_to_question")
    .addEventListener("click", (e) => {
        e.preventDefault();

        $.ajax({
            url: e.target.href,
            type: "POST",
            data: {
                option_text: document.getElementById(`add_option_text_value`)
                    .value,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).done(function () {
            location.reload();
        });
    });
