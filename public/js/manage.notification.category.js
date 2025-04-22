;(function($) {
    $new_category_modal = $("#modal_new_category");
    $("#add_category_btn").click(show_new_category_modal);
    $("#save_category_btn").click(save_category);

    function show_new_category_modal(e) {
        $new_category_modal.modal({backdrop: 'static'})
    }

    function save_category(e) {
        console.log(e.target);
        name = $("#category_name").val().trim();
        parent = $("#category_parent").val().trim();
        current_category = $('#category').val();

        $(".loading").remove();
        var template = $.templates("#alert_div");

        if (!name) {
            $(".alert").remove();

            try {
                var htmlOutput = template.render({
                                        type: "danger",
                                        message: "Name cannot be blank"
                                    });
                $new_category_modal.find(".modal-body").append(htmlOutput);
            } catch(err) {}

            return false;
        }

        $("#category_name").after('<span class="loading"></span>');

        try {
            $.ajax({
                url: '/manage/notification-category-save',
                type: 'post',
                dataType: 'json',
                data: {
                    name: name,
                    parent: parseInt(parent)
                },
            })
            .done(function(data) {
                console.log("success");

                if (typeof data.error === "undefined" && data.type && data.message) {
                    var htmlOutput = template.render({
                                            type: data.type,
                                            message: data.message
                                        });
                    $new_category_modal.find(".modal-body").append(htmlOutput);

                    if (typeof data.new_data !== "undefined" && data.new_data) {
                        $("#category option").not(':first').remove();
                        $("#category_parent option").not(':first').remove();

                        for (cat_id in data.new_data) {
                            option_modal = '<option value="'+cat_id+'">'
                                            + data.new_data[cat_id]
                                            + '</option>';
                            option_ = '<option value="'+cat_id+'" '
                                            + (cat_id == current_category ? 'selected' : '')
                                            + '>'
                                            + data.new_data[cat_id]
                                            + '</option>';

                            $("#category_parent").append(option_modal);
                            $("#category").append(option_);
                        }
                    }

                    if (data.type == "success") {
                        setTimeout(function() {
                            $("#category_name").val('');
                            $("#category_parent").val('');
                            $(".alert").remove();
                            $new_category_modal.modal('hide');
                        }, 2000);
                    }

                } else {

                    var htmlOutput = template.render({
                                            type: "danger",
                                            message: "Error"
                                        });
                    $new_category_modal.find(".modal-body").append(htmlOutput);
                }
            }) // END done
            .fail(function() {
                console.log("error");
            })
            .always(function() {
                console.log("complete");
                $(".loading").remove();
            });
        } catch (err) {}

    }
})(jQuery);

