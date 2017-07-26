/**
 * Description: file for add feature in view tour (package/hike),
 * use in new module search.
 */

// *** Package *** //
// Check current tab if user 'click' to module
jQueryMod2('#tour_search_module_mod2').live('click', function(){
    setVariableWithCurrentActiveTabInTourView();
});

/**
 * function get current active tab and set variable for use in all selector
 * @param void
 * @returns void
 */
var id_current_active_tab = '';// Default global
function setVariableWithCurrentActiveTabInTourView() {
    id_current_active_tab = jQueryMod2('#tour_search_module_mod2 #itt_tour_search_module #itt_issuing_search_tabs_main_block li a.is-active').parent().attr('id');// Example: "search_result_tab_102"
    if (typeof id_current_active_tab != 'undefined') {
        id_current_active_tab = '#itt_issuing_search_tabs_main_block #' + id_current_active_tab + ' section ';// Example: "#itt_issuing_search_tabs_main_block #search_result_tab_104 section "
    }
}

/**
 * function scroll current window down
 * @params topPx {number}
 * @return void
 */
function customScrollWindowDown(topPx) {
    if (typeof topPx == 'undefined') {
        // Scroll to end document
        jQueryMod2('body,html').animate({ 'scrollTop': jQueryMod2(document).height() }, 800);
    } else {
        // Scroll to topPx
        jQueryMod2('body,html').animate({ 'scrollTop': topPx }, 800);
    }
}

// Handler for open tab with map
jQueryMod2(id_current_active_tab + '.itt_open_map,' + id_current_active_tab + '.itt_open_map_mini').live('click', function(){
    jQueryMod2(id_current_active_tab + '.itt_tab_map a').trigger('click');
    
    // Scroll to element
    customScrollWindowDown(jQueryMod2(id_current_active_tab + '.itt_tab_map').offset().top);
});

// Handler for label 'Я хочу добавить данные участников тура'
jQueryMod2(id_current_active_tab + '.itt_add_person label').live('click', function(){
    // Set variable
    var $this = jQueryMod2(this);
    var checked = $this.parent().find('input[type="checkbox"]').prop("checked");
    if (checked) {
        $this.parent().find('input[type="checkbox"]').prop("checked", false);
    } else {
        $this.parent().find('input[type="checkbox"]').prop("checked", true);
    }
    
    // Set variable    
    var html_block = jQueryMod2(id_current_active_tab + '.itt_new_person_block');
    var text_comment = jQueryMod2(id_current_active_tab + '.itt_md_info_payment_text');
    var checked = $this.parent().find('input[type="checkbox"]').prop("checked");
    if (checked) {
        // Element checked => show block
        html_block.show();
        text_comment.hide();
        
        // Scroll to block
        customScrollWindowDown(html_block.offset().top);
    } else {
        // Element NOT checked => hide block
        html_block.hide();
        text_comment.show();
    }
});

// Handler for label 'Я хочу оплатить тур онлайн прямо сейчас'
jQueryMod2(id_current_active_tab + '.itt_pay_tour_add label').live('click', function(){
    // Set variable
    var $this = jQueryMod2(this);
    var checked = $this.parent().find('input[type="checkbox"]').prop("checked");
    if (checked) {
        $this.parent().find('input[type="checkbox"]').prop("checked", false);
    } else {
        $this.parent().find('input[type="checkbox"]').prop("checked", true);
    }
    
    var checked = $this.parent().find('input[type="checkbox"]').prop("checked");
    var html_block = jQueryMod2(id_current_active_tab + '.itt_content_pay_block');
    var button = jQueryMod2(id_current_active_tab + '.itt_send_form_button_position button.itt_button_all_in_item');
    var button_wrap = jQueryMod2(id_current_active_tab + '.itt_send_form_button_position');
        
    if (checked) {
        // Element checked => show block
        html_block.show();
        
        // Scroll to block
        customScrollWindowDown(html_block.offset().top);
        
        // Set text for button
        button.text(jQueryMod2(id_current_active_tab + '.itt_wrap_text_new_button_name').html());
        
        // Add link hide payment
        button_wrap.append(jQueryMod2(id_current_active_tab + '.itt_wrap_text_go_to_main_form').html());
    } else {
        // Element NOT checked => hide block
        html_block.hide();
        
        // Set text for button
        button.text(jQueryMod2(id_current_active_tab + '.itt_wrap_text_original_button_name').html());
        
        // Delete link hide payment
        button_wrap.find('.itt_button_hide_payment_varik').remove();
    }
});

// Handler for 'Вернуться к отправке запроса'
jQueryMod2(id_current_active_tab + '.itt_send_form_button_position .itt_button_hide_payment_varik').live('click', function(){
    // Hide block
    jQueryMod2(id_current_active_tab + '.itt_content_pay_block').hide();
    
    // Delete link hide payment
    jQueryMod2(id_current_active_tab + '.itt_send_form_button_position').find('.itt_button_hide_payment_varik').remove();
    
    // Set not checked 'Я оплатить тур онлайн прямо сейчас'
    jQueryMod2(id_current_active_tab + '.itt_pay_tour_add').find('input:checked').removeAttr('checked');
    
    // Set text for button
    jQueryMod2(id_current_active_tab + '.itt_send_form_button_position button.itt_button_all_in_item').text(jQueryMod2(id_current_active_tab + '.itt_wrap_text_original_button_name').html());
});


// Handler for 'Отправить запрос'
jQueryMod2(id_current_active_tab + '.itt_right_block_max-width .itt_button_send_request').live('click', function(){
    // Показываем текст '* Отправка запроса не обязывает Вас производить бронирование этого тура'
    jQueryMod2(id_current_active_tab + '.itt_md_info_payment_text').show();
    
    // Убираю чекбокс 'Я хочу добавить данные участников тура' и 'Я оплатить тур онлайн прямо сейчас'
    jQueryMod2(id_current_active_tab + '.itt_add_person').find('input').prop('checked', false);
    jQueryMod2(id_current_active_tab + '.itt_pay_tour_add').find('input').prop('checked', false);
    
    // Скрываю форму про добавление пользовательских данных и форму выбора оплаты
    jQueryMod2(id_current_active_tab + '.itt_new_person_block').hide();
    jQueryMod2(id_current_active_tab + '.itt_content_pay_block').hide();
    
    // Delete link hide payment
    jQueryMod2(id_current_active_tab + '.itt_send_form_button_position .itt_button_hide_payment_varik').remove();
    
    // Set text for button
    jQueryMod2(id_current_active_tab + '.itt_send_form_button_position button.itt_button_all_in_item').text(jQueryMod2(id_current_active_tab + '.itt_wrap_text_original_button_name').html());
    
    // Scroll to block
    customScrollWindowDown(jQueryMod2(id_current_active_tab + '.itt_form_date_blcok').offset().top);
});

// Handler for 'Забронировать'
jQueryMod2(id_current_active_tab + '.itt_right_block_max-width .itt_button_reserve').live('click', function(){
    // Скрываем текст '* Отправка запроса не обязывает Вас производить бронирование этого тура'
    jQueryMod2(id_current_active_tab + '.itt_md_info_payment_text').hide();
    
    // Ставлю 'Я хочу добавить данные участников тура' выбраным и открываю форму для пользователей то без учета оплат
    jQueryMod2(id_current_active_tab + '.itt_add_person').find('input').prop('checked', true);
    jQueryMod2(id_current_active_tab + '.itt_new_person_block').show();
    
    // Checkbox 'Я оплатить тур онлайн прямо сейчас' убираю и скрываю после него форму
    jQueryMod2(id_current_active_tab + '.itt_pay_tour_add').find('input').prop('checked', false);
    jQueryMod2(id_current_active_tab + '.itt_content_pay_block').hide();
    
    // Delete link hide payment
    jQueryMod2(id_current_active_tab + '.itt_send_form_button_position .itt_button_hide_payment_varik').remove();
    
    // Set text for button
    jQueryMod2(id_current_active_tab + '.itt_send_form_button_position button.itt_button_all_in_item').text(jQueryMod2(id_current_active_tab + '.itt_button_reserve').text());
    
    // Scroll to block
    customScrollWindowDown(jQueryMod2(id_current_active_tab + '.itt_form_date_blcok').offset().top);
});

jQueryMod2(id_current_active_tab + ' a.itt_button_more_information_about_booking').live('click', function(){
   var $this = jQueryMod2(this);
   // Check isset open pop-up
   if ($this.parent().find('div').hasClass('itt_custom_popup_clone')) return;
   // Get html
   var popUpHtml = '<div class="itt_popup_more_information_about_booking itt_custom_popup_clone">'
                 + jQueryMod2('#tour_search_module_mod2 div.itt_popup_more_information_about_booking').html()
                 + '</div>';
   // Set and show popup
   $this.parent().append(popUpHtml).find('.itt_custom_popup_clone').show();
});

jQueryMod2(id_current_active_tab + '.itt_close_popup_more_information_about_booking').live('click', function(){
   jQueryMod2(id_current_active_tab + '.itt_popup_more_information_about_booking').hide();
   jQueryMod2(id_current_active_tab + '.itt_custom_popup_clone').remove();
});
// End custom pop-up 'Подробнее о бронировании'

// Handler for add children
jQueryMod2(id_current_active_tab + '.itt_btn_add_children').live('click', function(){
    // Loop for all row
    jQueryMod2(id_current_active_tab + '.itt_row_child_item').each(function(){
        // Check hide row
        if (jQueryMod2(this).css('display') == 'none') {
            flag_disable_btn += 1;
            
            // Show row
            jQueryMod2(this).css('display', 'block');
            
            // Scroll
            customScrollWindowDown(jQueryMod2(this).offset().top);
            
            // Flag for count active cheldren
            var flag_disable_btn = 0;
            jQueryMod2(id_current_active_tab + '.itt_row_child_item').each(function(){
                if (jQueryMod2(this).css('display') == 'block') {
                    flag_disable_btn += 1;
                }
            });
            
            // Check all row show => desable link 'add children'
            if (flag_disable_btn === 3) {
                jQueryMod2(id_current_active_tab + '.itt_btn_add_children').addClass('itt_btn_add_children_disable');
            }
            
            // Exit from each
            return false;
        }
   });
});

// Handler for delete children
jQueryMod2(id_current_active_tab + '.itt_btn_delete_children').live('click', function(){
    // Get current element
    var el = jQueryMod2(this);
    
    setTimeout(function(){
        // Hide row
        el.parent().parent().css('display', 'none');
            
        // Enable link 'add children'
        jQueryMod2(id_current_active_tab + '.itt_btn_add_children').removeClass('itt_btn_add_children_disable');
            
        // Clear all input text in hide row
        el.parent().parent().find('input[type="text"]').each(function(){
            jQueryMod2(this).val('');
        });
    }, 200);
});

// Handler for selector currency, in item tab (view package tour)
jQueryMod2(id_current_active_tab + '.itt_currency_tour_selector').live('change', function(){
    // Get new price
    var price = jQueryMod2(this).val();
    
    // Get current currency_id
    if (typeof price != 'undefined') {
        var currency_id = jQueryMod2(this).find('[value="' + price + '"]').attr('currency_id');
    }
    
    // Set new price
    jQueryMod2(id_current_active_tab + '.itt_price_tour_view_user').html(price);
    jQueryMod2(id_current_active_tab + 'input[name="input_currency_id"]').val(price);
    
    // Set new currency_id
    if (typeof currency_id != 'undefined') {
        jQueryMod2(id_current_active_tab + 'input[name="itt_current_currency_id"]').val(currency_id);
        jQueryMod2(id_current_active_tab + 'input[name="input_currency_id"]').val(currency_id);
    }
});

// *** Begin gallery scroller **************************************************
// Handler for gallery click 'right button' (клик справа -> скролим влево)
jQueryMod2(id_current_active_tab + '.itt_btn_right_gallery_photo').live('click', function(){
    var galleryObj = jQueryMod2(this).parent().prev().find('ul.itt_scroll_photo_list');
    var liObj = galleryObj.find('li:first').detach();
    var newLiHtml = '<li>' + liObj.html() + '</li>';
    
    // Move li to new position
    galleryObj.append(newLiHtml);
    
    setTimeout(function(){
        // Set hover pop-up to new li img
        initHoverPupUpHotelPhotoViewTour();
        
        // Set handler for gallery click to thumbnail
        initReplaceMainHotelPhotoInViewTour();
    }, 200);
});

// Handler for gallery click 'left button' (клик влево -> скролим вправа)
jQueryMod2(id_current_active_tab + '.itt_btn_left_gallery_photo').live('click', function(){
    var galleryObj = jQueryMod2(this).parent().next().find('ul.itt_scroll_photo_list');
    var liObj = galleryObj.find('li:last').detach();
    var newLiHtml = '<li>' + liObj.html() + '</li>';
    
    // Move li to new position
    galleryObj.prepend(newLiHtml);
    
    // Set hover pop-up to new li img
    setTimeout(function(){
        // Set hover pop-up to new li img
        initHoverPupUpHotelPhotoViewTour();
        
        // Set handler for gallery click to thumbnail
        initReplaceMainHotelPhotoInViewTour();
    }, 200);
});
// *** End gallery scroller ****************************************************

// Handler for show / hide description payment system => 'Оплатить картой Приватбанка'
jQueryMod2(id_current_active_tab + '.itt_payment_privat_card').live('click',function(){
    // Get variable for use in script
    var current_element = jQueryMod2(id_current_active_tab + '.itt_payment_privat_card_description');
    hideShowHTMLElement(current_element);
});

// Handler for show / hide description payment system => 'Оплатить Приват24'
jQueryMod2(id_current_active_tab + '.itt_payment_privat24').live('click',function(){
    // Get variable for use in script
    var current_element = jQueryMod2(id_current_active_tab + '.itt_payment_privat24_description');
    hideShowHTMLElement(current_element);
});

/**
 * function init replace main hotel photo in view tour
 * ( обработчик для клика по картинке в галерее и 
 *   замена главной картинки кликаемой картинкой )
 * 
 * @returns {undefined}
 */
function initReplaceMainHotelPhotoInViewTour(){
    jQueryMod2(id_current_active_tab + '.itt_scroll_photo_list li a img').click(function(){
        // Get src new image
        var img_src = jQueryMod2(this)[0].src;
        
        // Remove all class active
        jQueryMod2(id_current_active_tab + '.itt_scroll_photo_list li.itt_photo_active').removeClass('itt_photo_active');
        
        // Add class active for new li
        jQueryMod2(this).parent().parent().addClass('itt_photo_active');
        
        // Replace big img to new img
        jQueryMod2(id_current_active_tab + '.itt_hotel_big_photo > img').attr('src', img_src);
    });    
}

/**
 * Кастомный pop-up при наведении на картинку в результатах выдачи всплывает 
 * окно с этой же картинкой только размером в cssMaxHeight(px)
 * 
 * @returns {undefined}
 */
function initHoverPupUpHotelPhotoViewTour(){
    // Begin custom pop-up with image hotel //
    jQueryMod2(id_current_active_tab + 'ul.itt_scroll_photo_list li img').mouseover(function(){
        // Show pop-up
        
        // Set img obj
        var hoverImg = jQueryMod2(this);
        var src = hoverImg.attr('src');
        
        // Get html pop-up
        var popUpHtml = hoverImg.parent().parent().parent().next(); // == jQueryMod2('div.itt_hotel_image_item_custom_tooltip');
        
        // Check variable and exit
        if (typeof src == 'undefined') return;
        if (typeof popUpHtml == 'undefined') return;
        
        // Insert image to pop-up
        popUpHtml.find('img').attr('src', src);

        // Show pop-up
        popUpHtml.show();
    }).mouseout(function(){
        // Hide pop-up
        
        // Set img obj
        var hoverImg = jQueryMod2(this);
        
        // Get html pop-up
        var popUpHtml = hoverImg.parent().parent().parent().next(); // == jQueryMod2('div.itt_hotel_image_item_custom_tooltip');
        
        // Unset img in pop-up
        popUpHtml.find('img').attr('src', '');
        
        // Hide pop-up
        popUpHtml.hide();
    });
    // End custom pop-up with image hotel //
}

/**
 * function hide / show element
 * 
 * @param {object} element element for hide / show
 * @return {void}
 */
function hideShowHTMLElement(element) {
    if (element.css('display') == 'none') {
        // 'none'
        
        // Show form
        element.slideToggle('hide');
    } else {
        // 'block'
        element.slideToggle('slow');
    }
}

/**
 * function callbback, use for asynchron load google.maps
 * 
 * @param void
 * @returns void
 */
function initialize_google_map_custom() {
    // Empty function for work versioin !!!
}

/**
 * function init custom select for flight
 * 
 * @returns {undefined}
 */
function initCustomSelectFlightThereBack(){
    // Iterate over each select element
    jQueryMod2(id_current_active_tab + 'select.itt_flight_date_custom_select').each(function(){

      // Cache the number of options
      var $this = jQueryMod2(this),
      numberOfOptions = jQueryMod2(this).children('option').length;

      // Hides the select element
      $this.addClass('itt_custmon_select_hidden');

      // Wrap the select element in a div
      //$this.wrap('<div class="select"></div>');

      // Insert a styled div to sit over the top of the hidden select element
      $this.after('<div class="itt_flight_date_styled_select"></div>');

      // Cache the styled div
      var $styledSelect = $this.next('div.itt_flight_date_styled_select');

      // Show the first select option in the styled div
      $styledSelect.prepend($this.children('option:selected').text());

      // Insert an unordered list after the styled div and also cache the list
      var $list = jQueryMod2('<ul />', {'class': 'itt_flight_date_styled_select_options'}).insertAfter($styledSelect);

      // Insert a list item into the unordered list for each select option
      for (var i = 0; i < numberOfOptions; i++) {
          jQueryMod2('<li />', {
                       text: $this.children('option').eq(i).text(),
                       rel: $this.children('option').eq(i).val()
                       }).appendTo($list);
      }

      // Cache the list items
      var $listItems = $list.children('li');

      // Show the unordered list when the styled div is clicked (also hides it if the div is clicked again)
      $styledSelect.click(function (e) {
          e.stopPropagation();
          jQueryMod2(id_current_active_tab + 'div.itt_flight_date_styled_select.active').not(this).removeClass('active').next('ul.itt_flight_date_styled_select_options').hide();
          jQueryMod2(this).toggleClass('active').next('ul.itt_flight_date_styled_select_options').toggle();
      });

      // Hides the unordered list when a list item is clicked and updates the styled div to show the selected list item
      // Updates the select element to have the value of the equivalent option
      $listItems.click(function (e) {
          e.stopPropagation();
          $styledSelect.text(jQueryMod2(this).text()).removeClass('active');
          $this.val(jQueryMod2(this).attr('rel'));
          $list.hide();
      
          // Handler for set 'selected' element in real select
          // Delete old selected
          $this.children('option').each(function(){
              jQueryMod2(this).removeAttr('selected');
          });
          
          // Set new selected
          $this.find('option[value="' + jQueryMod2(this).attr('rel') + '"]').attr('selected', 'selected');
      });

      // Hides the unordered list when clicking outside of it
      jQueryMod2(document).click(function () {
          $styledSelect.removeClass('active');
          $list.hide();
      });
  });
}

/**
 * functuion init js view tour
 * 
 * @returns {undefined}
 */
function afterLoadViewTour(){
    setTimeout(function(){
        // Замена main картинки по клику на слайдер
        initReplaceMainHotelPhotoInViewTour();
        
        // Показ поп-апа с картинкой отеля, при наведении на слайдер.
        initHoverPupUpHotelPhotoViewTour();
        
        // Inin custom select for 'Перелёт туда', 'Перелёт обратно'.
        initCustomSelectFlightThereBack();
    }, 500);
}

// По клику на таб просмотре тура активируем js
jQueryMod2('#tour_search_module_mod2 a.itt_issuing_search_links_head_tab').live('click', function(){   
   afterLoadViewTour(); 
});

// For validation form 'buy online' and form 'buy in office'
jQueryMod2(document).ready(function() {
    // Form 'buy all'
    jQueryMod2(id_current_active_tab + ".itt_info_tourists_buy_all_form .itt_button_all_in_item").live('click',function(e){
        e.preventDefault();
        var validator = jQueryMod2(id_current_active_tab + '.itt_info_tourists_buy_all_form').validate({
            rules:{
                first_name: "required",
                city: "required",
                phone_code: "required",
                phone: "required",
                captcha: "required",
                email: {
                         required: true,
                         email: true
                       },
                
                clientname_1:"required",
                clientsurname_1:"required",
                clientDobDay_1:"required",
                clientDobMonth_1:"required",
                clientDobYear_1:"required",
                gender_1:"required",
                citizenship_1:"required",
                pass_series_1:"required",
                pass_numb_1:"required",
                clientPassDay_1:"required",
                clientPassMonth_1:"required",
                clientPassYear_1:"required",
                clientAuthor_1:"required",
                clientPassValidDay_1:"required",
                clientPassValidMonth_1:"required",
                clientPassValidYear_1:"required",
                
                clientname_2:"required",
                clientsurname_2:"required",
                clientDobDay_2:"required",
                clientDobMonth_2:"required",
                clientDobYear_2:"required",
                gender_2:"required",
                citizenship_2:"required",
                pass_series_2:"required",
                pass_numb_2:"required",
                clientPassDay_2:"required",
                clientPassMonth_2:"required",
                clientPassYear_2:"required",
                clientAuthor_2:"required",
                clientPassValidDay_2:"required",
                clientPassValidMonth_2:"required",
                clientPassValidYear_2:"required",
                
                clientname_3:"required",
                clientsurname_3:"required",
                clientDobDay_3:"required",
                clientDobMonth_3:"required",
                clientDobYear_3:"required",
                gender_3:"required",
                citizenship_3:"required",
                pass_series_3:"required",
                pass_numb_3:"required",
                clientPassDay_3:"required",
                clientPassMonth_3:"required",
                clientPassYear_3:"required",
                clientAuthor_3:"required",
                clientPassValidDay_3:"required",
                clientPassValidMonth_3:"required",
                clientPassValidYear_3:"required",
                
                clientname_4:"required",
                clientsurname_4:"required",
                clientDobDay_4:"required",
                clientDobMonth_4:"required",
                clientDobYear_4:"required",
                gender_4:"required",
                citizenship_4:"required",
                pass_series_4:"required",
                pass_numb_4:"required",
                clientPassDay_4:"required",
                clientPassMonth_4:"required",
                clientPassYear_4:"required",
                clientAuthor_4:"required",
                clientPassValidDay_4:"required",
                clientPassValidMonth_4:"required",
                clientPassValidYear_4:"required",
                
                clientname_5:"required",
                clientsurname_5:"required",
                clientDobDay_5:"required",
                clientDobMonth_5:"required",
                clientDobYear_5:"required",
                gender_5:"required",
                citizenship_5:"required",
                pass_series_5:"required",
                pass_numb_5:"required",
                clientPassDay_5:"required",
                clientPassMonth_5:"required",
                clientPassYear_5:"required",
                clientAuthor_5:"required",
                clientPassValidDay_5:"required",
                clientPassValidMonth_5:"required",
                clientPassValidYear_5:"required",
                
                clientname_6:"required",
                clientsurname_6:"required",
                clientDobDay_6:"required",
                clientDobMonth_6:"required",
                clientDobYear_6:"required",
                gender_6:"required",
                citizenship_6:"required",
                pass_series_6:"required",
                pass_numb_6:"required",
                clientPassDay_6:"required",
                clientPassMonth_6:"required",
                clientPassYear_6:"required",
                clientAuthor_6:"required",
                clientPassValidDay_6:"required",
                clientPassValidMonth_6:"required",
                clientPassValidYear_6:"required",
                
                clientname_7:"required",
                clientsurname_7:"required",
                clientDobDay_7:"required",
                clientDobMonth_7:"required",
                clientDobYear_7:"required",
                gender_7:"required",
                citizenship_7:"required",
                pass_series_7:"required",
                pass_numb_7:"required",
                clientPassDay_7:"required",
                clientPassMonth_7:"required",
                clientPassYear_7:"required",
                clientAuthor_7:"required",
                clientPassValidDay_7:"required",
                clientPassValidMonth_7:"required",
                clientPassValidYear_7:"required",
                
                clientname_8:"required",
                clientsurname_8:"required",
                clientDobDay_8:"required",
                clientDobMonth_8:"required",
                clientDobYear_8:"required",
                gender_8:"required",
                citizenship_8:"required",
                pass_series_8:"required",
                pass_numb_8:"required",
                clientPassDay_8:"required",
                clientPassMonth_8:"required",
                clientPassYear_8:"required",
                clientAuthor_8:"required",
                clientPassValidDay_8:"required",
                clientPassValidMonth_8:"required",
                clientPassValidYear_8:"required",
                
                clientname_9:"required",
                clientsurname_9:"required",
                clientDobDay_9:"required",
                clientDobMonth_9:"required",
                clientDobYear_9:"required",
                gender_9:"required",
                citizenship_9:"required",
                pass_series_9:"required",
                pass_numb_9:"required",
                clientPassDay_9:"required",
                clientPassMonth_9:"required",
                clientPassYear_9:"required",
                clientAuthor_9:"required",
                clientPassValidDay_9:"required",
                clientPassValidMonth_9:"required",
                clientPassValidYear_9:"required",
                
                clientname_10:"required",
                clientsurname_10:"required",
                clientDobDay_10:"required",
                clientDobMonth_10:"required",
                clientDobYear_10:"required",
                gender_10:"required",
                citizenship_10:"required",
                pass_series_10:"required",
                pass_numb_10:"required",
                clientPassDay_10:"required",
                clientPassMonth_10:"required",
                clientPassYear_10:"required",
                clientAuthor_10:"required",
                clientPassValidDay_10:"required",
                clientPassValidMonth_10:"required",
                clientPassValidYear_10:"required",
                
                clientname_11:"required",
                clientsurname_11:"required",
                clientDobDay_11:"required",
                clientDobMonth_11:"required",
                clientDobYear_11:"required",
                gender_11:"required",
                citizenship_11:"required",
                pass_series_11:"required",
                pass_numb_11:"required",
                clientPassDay_11:"required",
                clientPassMonth_11:"required",
                clientPassYear_11:"required",
                clientAuthor_11:"required",
                clientPassValidDay_11:"required",
                clientPassValidMonth_11:"required",
                clientPassValidYear_11:"required",
                
                is_card_payment:"required",
            },
            errorPlacement: function(error, element) {
                error.appendTo( element.addClass('itt_red_color_placeholder') );
                customScrollWindowDown(element.offset().top);// Scroll
            },
        });
        
        // Exec validation
        jQueryMod2.each(jQueryMod2(id_current_active_tab + '.itt_info_tourists_buy_all_form').children('div'), function(key,el){
            if(jQueryMod2(el).css('display')=='block'){
                jQueryMod2.each(jQueryMod2(el).find('input[type="text"],input[type="radio"],textarea'), function(k,elem){
                    // check element hide => continue;
                    if (jQueryMod2(elem).parents('.itt_row').css('display') == 'none') return;
                    validator.element(jQueryMod2(elem));
                });
            }
        });
        
        if(Object.keys(validator.invalid).length == 0){
            // Valid data => send data
            
            // Check 'hike' or 'package'
            var hidden_action = jQueryMod2(id_current_active_tab + '.itt_info_tourists_buy_all_form input[name="action"]').val();
            if (hidden_action == 'package_tour_order_submit_buy_all') {
                package_tour_order_submit_buy_all();
            }
            if (hidden_action == 'hike_tour_order_submit_buy_all') {
                hike_tour_order_submit_buy_all();
            }
            
        }
        
    });
    
    // Handler for click to label payment varik
    initClickToLabelPaymentVarik();
});

function initClickToLabelPaymentVarik(){
  jQueryMod2(id_current_active_tab + 'label.itt_payment_method_label_color').live('click', function(e){
      e.stopPropagation();
      jQueryMod2(this).parent().find('input[type="radio"]').trigger('click');
  });
}

// Handler send form 'buy online'
function package_tour_order_submit_buy_all() {
  // Set 'id_current_active_tab'
  setVariableWithCurrentActiveTabInTourView();
  
  // Set option for send
  var modules_action = ModuleSearch.getOption('modules_action');
  var params = (jQueryMod2(id_current_active_tab + '#package_order_form_buy_all').serialize());
  var new_ittour_order_id = jQueryMod2(id_current_active_tab + "#package_order_form_buy_all input[name='tour_id']").val();
  if(ittour_order_id != new_ittour_order_id) {
    ittour_order_id = new_ittour_order_id;
    
    // Show preloader
    jQueryMod2(id_current_active_tab + ' #itt_tour_send_data_loader_buy_online').show();
    
    // Hide form
    jQueryMod2(id_current_active_tab + ' #package_order_form_buy_all').hide();
    
    // Send data
    jQueryMod2.getJSON(modules_action, params, function(data) {
      if(data.success == false) {
        // Error 
        
        // Hide preloader
        jQueryMod2(id_current_active_tab + '#itt_tour_send_data_loader_buy_online').hide();
        
        // Show form
        jQueryMod2(id_current_active_tab + '#package_order_form_buy_all').show();
        
        // Show error
        jQueryMod2(id_current_active_tab + '.itt_error_msg_from_server').show().html(data.error);
        
        // Set border red if server respons Error in captcha
        var errorStr = data.error;
        var searchStr = jQueryMod2(id_current_active_tab + 'input[name="captcha"]').attr('placeholder');
        var flagErrCod = '';
        searchStr = searchStr[1] + searchStr[2] + searchStr[3] + searchStr[4] + searchStr[5] + searchStr[6] + searchStr[7];
        flagErrCod = errorStr.indexOf(searchStr);// Проверяем есть пришла ошибка об captcha
        if (flagErrCod != -1) {
          jQueryMod2(id_current_active_tab + 'input[name="captcha"]').removeClass('valid').addClass('error').css('cssText', 'color:#FF3300 !important');
        }
        
        // Scroll to error element
        customScrollWindowDown(jQueryMod2(id_current_active_tab + '.itt_error_msg_from_server').offset().top);
      } else {
        // Success
        
        // Hide preloader
        jQueryMod2(id_current_active_tab + ' #itt_tour_send_data_loader_buy_online').hide();
        
        // Hide error
        jQueryMod2(id_current_active_tab + '.itt_error_msg_from_server').show().html('');
        
        // Show messag 'all ok'
        jQueryMod2(id_current_active_tab + ' #itt_tour_order_success_message_online').show();
        
        // Check payment html form
        if (data.payment_html_form != '') {
          // Insert html payment form
          jQueryMod2(id_current_active_tab + ' .itt_payment_html_form_area').html(data.payment_html_form.replace('_blank','_self')); 
          
          setTimeout(function(){
            // Send payment form
            jQueryMod2(id_current_active_tab + ' .itt_payment_html_form_area form').submit();
          }, 800);
        }
      }
      ittour_order_id = '';
      return false;
    });
  }
  return false;
}

// Добавление маски ввода для формы с телефонным номером
jQueryMod2(id_current_active_tab + 'input[name="phone"]').live('click',function(){
  jQueryMod2(id_current_active_tab + 'input[name="phone"]').mask("999 999 99 99",{autoclear: false});
});

// Add captcha => update image - not create new cod, use old cod.
jQueryMod2(id_current_active_tab + '.itt_captcha_refresh').live('click',function(){
  var buttom_resresh = jQueryMod2(this);
  var parentWrap = buttom_resresh.parent().parent();
  var captcha = parentWrap.find('img.captcha');
  if(typeof captcha != 'undefined') {
    buttom_resresh.css('opacity', '0.6');
    captcha.attr('src', captcha.attr('src') + '&' + Math.floor(Math.random(7,9)*100));
    setTimeout(function(){ buttom_resresh.css('opacity', '1'); }, 200);
  }
});

// *** Hike *** //