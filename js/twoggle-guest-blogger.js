/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function onImgLoadError() {   
   showImageError('Error loading image. Please try a different image URL \n (perhaps try a different image hosting service).');
   resetImgCheck();
}

function resetImgCheck() {
   jQuery('#twgb-chkimg').prop('checked', false)
   jQuery('#twgb-chkimg-text').html('Check me, then click somewhere in the post content to insert image');
}

function showError(elementId, msg) {
   e = jQuery('#' + elementId);
   e.removeClass('twgb-success').addClass('twgb-error');
   e.html(msg);
   e.show();
}

function showSuccess(elementId, msg) {
   e = jQuery('#' + elementId);
   e.removeClass('twgb-error').addClass('twgb-success');
   e.html(msg);
   e.show();
   e.fadeOut(3000);   
}

function showImageError(msg) {
   showError('img-info', msg);
}

function showContentError(msg) {
   showError('content-info', msg);
}

function showContentSuccess(msg) {
   showSuccess('content-info', msg);
}

// Generic surround-with functionality
function selectionSurroundWith(sPrepend, sAppend, errMsg) {
    jQuery('#content-info').hide();
   
    var textComponent = document.getElementById('twgb-post-content');
    var selectedText;
    //  // IE version
    //  if (document.selection != undefined)
    //  {
    //    textComponent.focus();
    //    var sel = document.selection.createRange();
    //    selectedText = sel.text;
    //    
    //  }
    // Mozilla version
    if (textComponent.selectionStart != undefined)
    {
       var startPos = textComponent.selectionStart;
       var endPos = textComponent.selectionEnd;
       selectedText = textComponent.value.substring(startPos, endPos);

       if (selectedText.length == 0) {
          showContentError(errMsg);
       } else {
          var pContent = jQuery('#twgb-post-content').val();
          var v = sPrepend + selectedText + sAppend;

          var textBefore = pContent.substring(0,  startPos );
          var textAfter  = pContent.substring(endPos, pContent.length );
          jQuery('#twgb-post-content').val( textBefore + v + textAfter );
          
          return true;
       }   
   } else {
       return false;
   }
}

function selectionToHeading() {   
    if(selectionSurroundWith('<h2>', '</h2>', 'Please select some text to make into h2 heading ...')) {
        showContentSuccess('Successfully added heading.');
    }
        
}

function selectionToLink() {   
    var url = prompt("URL to link to:", "http://");
    if (selectionSurroundWith('<a href="' + url + '">', '</a>', 'Please select some text to make into a link ...')) {
        showContentSuccess('Successfully added link.');
    }
}

function selectionToBold() {
    if (selectionSurroundWith('<strong>', '</strong>', 'Please select some text to make <strong>bold</strong> ...')) {
        showContentSuccess('Successfully added boldness.');
    }
}

function selectionToItalic() {
    if (selectionSurroundWith('<em>', '</em>', 'Please select some text to make <em>italic </em>...')) {
        showContentSuccess('Successfully added italicness.');
    }
}


jQuery(document).ready(function($){   
   /***
    * POST CONTENT TOOLBAR
    ***/
   $('#btn-heading').click(function() {
      //get selected text
      selectionToHeading();      
      return false;
   });
   
   $('#btn-link').click(function() {
      //get selected text
      selectionToLink();      
      return false;
   });
   
   $('#btn-bold').click(function() {
      //get selected text
      selectionToBold();      
      return false;
   });
   
   $('#btn-italic').click(function() {
      //get selected text
      selectionToItalic();      
      return false;
   });
   
   /***
    * IMAGE EVENTS
    ***/
   // Insert image checkbox:
   $('#twgb-chkimg').click(function() {      
      if ($('#twgb-chkimg').prop('checked')) {
         //Check img field not empty (later: check its valid with regex)
         if ($('#twgb-img-url').val() != '') {
            if ($('#twgb-post-title').val() != '') {
               $('#twgb-chkimg-text').html('Now click in post content where you want to insert the image code ...');
            } else {
               showImageError('Please enter post title and try again');
               resetImgCheck();
            }            
         } else {
            showImageError('Please insert image URL');
            resetImgCheck();
         }         
      } else {
         resetImgCheck();
      }      
   });
   
   // On blurring img-url, load pic below.
   $('#twgb-img-url').blur(function() {
      $('#img-info').hide();
      
      if ($('#twgb-img-url').val() != '') {
         $("#twgb-img").show();
         $("#twgb-img").attr('src', $(this).val() );
      }       
   });
   
   $('#twgb-post-content').click(function() {
      
      // If 'insert image' checked, do insertion!
      if ($('#twgb-chkimg').prop('checked')) {
         
         //Later maybe: Check valid image url entered:
         //twgb-img-url
         
         // Get cursor pos
         var el = $(this).get(0);         
         var cursorPos = 0;         

         if ('selectionStart' in el) {             
            cursorPos = el.selectionStart;         
         } else if ('selection' in document) {  
            el.focus();             
            var Sel = document.selection.createRange();             
            var SelLength = document.selection.createRange().text.length;             
            Sel.moveStart('character', -el.value.length);             
            cursorPos = Sel.text.length - SelLength;         
         }         
         
         //no matter where they clicked, insert it         
         var pContent = $('#twgb-post-content').val();
         var imgUrl = $('#twgb-img-url').val();
         var pTitle = $('#twgb-post-title').val();
         var v = '\n <img src="' + imgUrl + '" title="' + pTitle + '" alt="' + pTitle + '" ';
         v += ' width="' + $('#twgb-img').width() + '" ';
         v += ' height="' + $('#twgb-img').height() + '" ';
         v += ' /> \n';
         
         var textBefore = pContent.substring(0,  cursorPos );
         var textAfter  = pContent.substring( cursorPos, pContent.length );
         $('#twgb-post-content').val( textBefore + v + textAfter );
         
         resetImgCheck();         
      }
      
            
      return false;
   });
   
   /***
    * TAGS
    ***/
   // Add tags
   $('#post_tags a').click(function() {
      tag_val = $('input[name=gb_tags]').val(); 
      if (tag_val !== '') {
         $('input[name=gb_tags]').val(tag_val + ', ' + $(this).html());
      } else {
         $('input[name=gb_tags]').val($(this).html());
      }
      $(this).hide();
      return false;
   });
   
   // Replace the bio shortcode values (e.g. '{name}'
   function replaceBioShortCode(bio_shortcode, sc_val) {
      var bio_val = $("#gb_bio").val();
      $("#gb_bio").val( bio_val.replace(bio_shortcode, sc_val));
   }
   
   // About you $ events
   $("input[name=gb_name]").blur(function() {
      replaceBioShortCode('{name}', $(this).val());
   });
   
   $("input[name=gb_site]").blur(function() {      
      var website = $(this).val();
      var url = website;
      if(!(url.indexOf('http') > -1)) {
         url = 'http://' + url;
      }       
      url = '<a href="' + url + '">' + website.replace('http://', '') + '</a>';      
      replaceBioShortCode('{website}', url);
   });

   $('#twgb-form').on('submit', function(event){
      event.preventDefault();
      $.post(
         $("#admin_ajax_url").val(),
         $('#twgb-form').serialize() + '&action=twgb_submit_and_save_guests_post',
         function(response){                  
            if(response.dead){
               $('#twgb-form').children('.info').text(response.dead).show(1500);
               $('#twgb-form').children('.info').css({
                  "color":"#FF0000", 
                  "font-weight":"bold"
               });
               return;
            }                  
            $('#twgb-form').hide().children('.info').text(response.success).show(1500);
            if(response.redirect_to){
               setInterval(function(){
                  window.location.href = response.redirect_to;
               }, 1500);
            }
         }, 'json'
         );
   });
   
});
