/**
 * Created by hj on 2016/12/7.
 */

$( "#dialog" ).dialog({
    autoOpen: false,
    width:"auto",
    buttons: [
        {
            text: "确定",
            click: function() {
                $( this ).dialog( "close" );
            }
        },
        {
            text: "取消",
            click: function() {
                $( this ).dialog( "close" );
            }
        }
    ]
});

// Link to open the dialog
$( "#dialog-link" ).click(function( event ) {
    $( "#dialog" ).dialog( "open" );
    event.preventDefault();
});
