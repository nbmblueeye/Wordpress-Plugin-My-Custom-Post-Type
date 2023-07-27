// <-======================Taxonomy Image Upload==========================->

jQuery(document).ready(function($){

    $(".my_custom_post_type_term-category-image-wrap").on("click",".my_custom_post_type_category_image_box",function(e){
        e.preventDefault();
        var frame;

        if(frame){
            frame.open();
            return;
        }

        frame = wp.media({
            title: "Add Category Image",
            button: {
                text: "Add Category Image Here"
            },
            multiple: false  
        });

        frame.on("select", function(){
            var attachments = frame.state().get('selection').first().toJSON();
            let image = $(".my_custom_post_type_category_image_box_show").find("img");
            if(image.length > 0){
                image.attr("src", attachments.url);
            }else{
                $(".my_custom_post_type_category_image_box_show").find(".no_image_box").remove();
                $(".my_custom_post_type_category_image_box_show").prepend(`<img src="${attachments.url}" alt="amcpt-image">`)
            }
            
            $(".my_custom_post_type_category_image_box_show").css({"display":"block"});
            $(".my_custom_post_type_category_image_box").css({"display":"none"});
            $("#my_custom_post_type_category_image_id").val(attachments.id);

        });

        frame.open();

    });

    $(".my_custom_post_type_term-category-image-wrap").on("click",".my_custom_post_type_category_image_remove_image" ,function(e){
        e.preventDefault();
        $(".my_custom_post_type_category_image_box_show").find("img").attr("src", "");
        $(".my_custom_post_type_category_image_box_show").css({"display":"none"});
        $(".my_custom_post_type_category_image_box").css({"display":"flex"});
        $("#my_custom_post_type_category_image_id").val("");

    });    

});

