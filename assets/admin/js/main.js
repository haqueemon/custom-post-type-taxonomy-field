var txmFrame;
var txmFramePt;
;( function ($) {

    "use strict";

    $( document ).ready( function () {

        var imageUrl = $( "#txm-image-url" ).val();
        if ( imageUrl ) {
            $( ".txm-image-container" ).html(`<img src='${imageUrl}' />`); }

        $( "#upload_image" ).on( "click" , function () {

            if ( txmFrame ) {
                txmFrame.open();
                return false; }

            txmFrame = wp.media({
                title: "Select Image",
                button: {
                    text: "Insert Image"
                },
                multiple: false
            } );

            txmFrame.on( 'select' , function () {
                var attachment = txmFrame.state().get( 'selection' ).first().toJSON();
                $( "#txm-image-id" ).val( attachment.id );
                $( "#txm-image-url" ).val( attachment.sizes.full.url );
                $( ".txm-image-container" ).html( `<img src='${attachment.sizes.full.url}' />` );
            } );

            txmFrame.open();
            return false;
        } );


        var imageUrlPt = $( "#txm_image_url_pt" ).val();
        if ( imageUrlPt ) {
            $( ".txm-image-container" ).html(`<img src='${imageUrlPt}' />`); }

        $( "#upload_image_pt" ).on( "click" , function () {

            if ( txmFramePt ) {
                txmFramePt.open();
                return false; }

            txmFramePt = wp.media({
                title: "Select Image",
                button: {
                    text: "Insert Image"
                },
                multiple: false
            } );

            txmFramePt.on( 'select' , function () {
                var attachment = txmFramePt.state().get( 'selection' ).first().toJSON();
                $( "#txm_image_id_pt" ).val( attachment.id );
                $( "#txm_image_url_pt" ).val( attachment.sizes.full.url );
                $( ".txm-image-container" ).html( `<img src='${attachment.sizes.full.url}' />` );
            } );

            txmFramePt.open();
            return false;
        } );
        

        
        
    } );

} )( jQuery );