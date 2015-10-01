var form_handler = {
    submit: function (form_id) {
        $(form_id).submit(function(e)
        {
            var post_data = $(this).serializeArray();
            post_data.push({name: 'is_ajax', value: '1'});
            var form_url = $(this).attr('action');
            $.ajax(
            {
                url: form_url,
                type: 'POST',
                data: post_data,
                success: function(data, text_status, jqXHR) 
                {
                    // The data returned from the server will be a 1 if it's
                    // a valid email or a 0 if it's not.
                    if ( data === "1" )
                    {
                        $(form_id + ' p#results').text('Thanks for signing up.');

                        // Make sure the form can't submit again.
                        $(form_id + ' input').remove();
                        $(form_id).attr('action', '');
                    }
                    else if ( data === "0" )
                    {
                        $(form_id + ' p#results').text('Please enter a valid email address.');
                        return false;
                    }
                },
                error: function(jqXHR, text_status, error_thrown) 
                {
                    console.log(data, text_status, error_thrown);
                }
            });
            // Make sure the form doesn't fire
            e.preventDefault(); 
            //$.cookie('$slug', 1, { path: '/', expires: 999999 });
        });
    },
    init: function() { }
};
