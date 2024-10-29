jQuery(document).ready(function($) {

    function wcadShowButtons(){

        $(".variationAttributes select").change(function () {
            $('#wcad_variable_submit_product_url_list').show();
            $('#variable_urlHolder').show();
        });
    }

    $("#wcad_variable_product_url_form .prod_url_select").change(function () {
    
        // We'll pass this variable to the PHP function addVariations_ajax_request
        var productID = $(this).val();
         
        // This does the ajax request
        $.ajax({
            url: addVariations_ajax_obj.ajaxurl,
            data: {
                'action': 'addVariations_ajax_request',
                'productID' : productID,
                'nonce' : addVariations_ajax_obj.nonce
            },
            success:function(data) {
                $('#wcad_variable_product_url_form .variationAttributes').html('');
                $('#wcad_variable_product_url_form .variationAttributes').append(data);
                wcadShowButtons();
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });
    });
});