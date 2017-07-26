var search_box;
var order_box;
var view_box;
var popupWin;
var search_request_active = false;
var ittour_order_id = '';
var default_serialize_module_form_search = '';

function ajax_load_mod2(url, div_name, show_result_in_popup) {
  var modules_action = ModuleSearch.getOption('modules_action');
  var params = url.substr( (url.indexOf( '?', 0) + 1) );
  params = params.replace(/callback=jsonp[0-9]+&/, '');
  params = params.replace(/callback=jQueryMod2[0-9_]+&/, '');
  
  // Hide
  jQueryMod2(div_name).html('');// Delete data in result = ''
  jQueryMod2('#itt_tour_search_load').show();// Show preloader
  jQueryMod2('#itt_issuing_search_tabs_main_block').hide();// Hide tab all result search
  setHeaderInResultSearch(true);// Remove header in search result
  
  // Request
  jQueryMod2.getJSON(modules_action, params, function(data) {
    jQueryMod2(div_name).html(data.text);// Set data
    jQueryMod2('#itt_tour_search_load').hide();// Hide preloader
    jQueryMod2('#itt_issuing_search_tabs_main_block').show();// Show result
    setHeaderInResultSearch(false);// Set header

    var targetOffset = jQueryMod2('#itt_issuing_search_package_tours').offset().top;
    jQueryMod2('html,body').animate({scrollTop: targetOffset}, 700);//Scroll up

    lang();
    return false;
  });
  
  return false;
}

/**
 * Cодержимое данной функции должно соответствовать function 'package_tour_order_mod2'
 * AS function package_tour_order_mod2(tour_id, sharding_rule_id) {
 * 
 * @param {type} tour_id
 * @param {type} sharding_rule_id
 * @returns {undefined}
 */
function openPackageTourViewInTab(tour_id, sharding_rule_id) {
  var modules_action = ModuleSearch.getOption('modules_action');
  var modules_popup_type = ModuleSearch.getOption('modules_popup_type');
  
  // Create new tab
  var id_new_tab = addTabForTourStretch();
  
  // Get data tour for view
  jQueryMod2.getJSON(modules_action, {'action':'get_package_tour_order_form_mod2', 'tour_id' : tour_id, 'sharding_rule_id':sharding_rule_id}, function(data) {
    
    // Hide pre-loader in item tab
    //jQueryMod2('#' + id_new_tab + ' #itt_tour_search_load_view_item_tab').hide();
    // тепрь скрывать прелоадер ненужно т.к. в каждом табо свой 
    // прелоедер и его просто буду заменять на контент, вот и все 8-].
    
    // Set content
    if(modules_popup_type == 'div') {
      // Set content to new tab
      jQueryMod2('li#' + id_new_tab + ' section').html(data.text);
    } else {
      orderWin.document.write(data.text);
      orderWin.document.close();
    }

    // Set title for tab
    jQueryMod2('#' + id_new_tab + ' .itt_row .itt_price_info li.itt_price_tour_view_user').ready(function(){
      // Ждем пока отработает js в 'package_tour_order_form_mod2.php'
      setTimeout(function(){
        // Get price
        var tour_price = jQueryMod2('#' + id_new_tab + ' .itt_row .itt_price_info li.itt_price_tour_view_user').text();

        // Get currency_id
        var currency_id = jQueryMod2('#' + id_new_tab + ' .itt_row .itt_price_info input[name="itt_current_currency_id"]').val();

        // Get currency symbol
        var tour_currency = jQueryMod2('#' + id_new_tab + ' .itt_currency_tour_selector [currency_id="' + currency_id + '"]').text();

        // Set new title for item tab, example: "16740 Грн"
        //jQueryMod2('#itt_issuing_search_tabs_main_block li.ui-state-default > a[href="#' + id_new_tab + '"]').html(tour_price + ' ' + tour_currency);      

        // Delete pre=loader in title tab
        jQueryMod2('#itt_issuing_search_tabs_main_block li#' + id_new_tab + ' > a div').remove();

        // Set new title for item tab, example: "16740 Грн"
        jQueryMod2('#itt_issuing_search_tabs_main_block li#' + id_new_tab + ' > a > span.itt_tab_bg_write').prepend(tour_price + ' ' + tour_currency);
      }, 400);
    });
    
    // Init js for view tour => all html ready
    jQueryMod2('#' + id_new_tab + ' .itt_popup_more_information_about_booking').ready(function(){
        setTimeout(function(){afterLoadViewTour();}, 800);
    });
    
    var targetOffset = jQueryMod2('#itt_issuing_search_tabs_main_block').offset().top;
    jQueryMod2('html,body').animate({scrollTop: targetOffset}, 700);//Scroll up

    // Validation price
    jQueryMod2.getJSON(modules_action, {'action':'get_package_validate_tour', 'tour_id' : tour_id, 'sharding_rule_id':sharding_rule_id}, function(jsondata) {
      // Warning: валидация написана только на строне client (js), 
      // т.к. подразумевается что серверная часть уже работает и в изменениях нет смысла
      if (jsondata.validate) {
        if (jsondata.validate.prices) {
          var currency_id = jQueryMod2('#' + id_new_tab + ' .itt_select_currency input[name="itt_current_currency_id"]').val();
          if (currency_id) {
            var new_prices = jsondata.validate.prices;
            if (new_prices) {
              var cur_price = Math.ceil(new_prices[currency_id]);
              if(cur_price){
                // Show new price
                jQueryMod2('#' + id_new_tab + ' .ittour_sr_price').attr('class','ittour_old_price');
                
                // Set new price for view
                jQueryMod2('#' + id_new_tab + ' .itt_price_tour_view_user').text(cur_price);
                
                // Set new price in changer currency
                jQueryMod2('#' + id_new_tab + ' .itt_currency_tour_selector option').each(function(){
                  var cur_currency_id = jQueryMod2(this).attr('currency_id');
                  if (typeof cur_currency_id != 'undefined') {
                    var item_new_price = Math.ceil(new_prices[cur_currency_id]);
                    if (typeof item_new_price != 'undefined') {
                      // Set price in select 'value'
                      jQueryMod2(this).attr('value', item_new_price);
                      
                      jQueryMod2('#' + id_new_tab + ' .itt_change_price_flight_symbol').css('display', 'inline');
                      jQueryMod2('#' + id_new_tab + ' .itt_order_desc_price_change').css('display', 'inline');
                    }
                  }
                });
              }
            }
          }
        } else if (jsondata.validate.stop_price) {
          jQueryMod2('#' + id_new_tab + ' .itt_tors_attributes .itt_tors_actual:first').hide();
          jQueryMod2('#' + id_new_tab + ' .itt_change_price_flight_symbol').show();
          jQueryMod2('#' + id_new_tab + ' .itt_order_desc_stop_price').show();
        } else if (jsondata.validate.stop_flight) {
          jQueryMod2('#' + id_new_tab + ' .itt_tors_attributes .itt_tors_actual:first').hide();
          jQueryMod2('#' + id_new_tab + ' .itt_change_price_flight_symbol').show();
          jQueryMod2('#' + id_new_tab + ' .itt_order_desc_stop_flight').show();
        } else if (!jsondata.validate.operator_validate) {
          jQueryMod2('#' + id_new_tab + ' .itt_tors_attributes .itt_tors_actual:first').hide();
          jQueryMod2('#' + id_new_tab + ' .itt_change_price_flight_symbol').show();
          jQueryMod2('#' + id_new_tab + ' .itt_tors_attributes .itt_order_desc_no_validate').show();
        }
      }
    });
    
    return false;
  });
}

function set_cookie(cookie_name, value, expire) {
    var expire_date = new Date();
    
    expire_date.setDate(expire_date.getDate() + expire*1);
    document.cookie = (cookie_name + "=" + escape(value) + ((expire == null) ? "" : ";expires=" + expire_date.toGMTString()));

    return true;
}

function get_cookie(cookie_name) {
    if (document.cookie.length > 0) {
        cookie_start = document.cookie.indexOf(cookie_name + "=");
        
        if (cookie_start != -1) { 
            cookie_start = ((cookie_start + cookie_name.length) + 1); 
            cookie_end   = document.cookie.indexOf(";", cookie_start);
            
            if ( cookie_end == -1) {
                cookie_end = document.cookie.length;
            }
            
            return unescape(document.cookie.substring(cookie_start, cookie_end));
        } 
    }
    
    return false;
}

jQueryMod2(document).ready(function(){
  jQueryMod2('.select').live("click", function(){
    var price = jQueryMod2(this, 'option:selected').val();
    price = price.toString().split(':');
    jQueryMod2('#main_price').html(price[0]);
  });
});

function lang(){
  var lang_search_results = jQueryMod2('#search_results').html();
  var lang_close = jQueryMod2('#close').html();
  jQueryMod2('.it_gradient_right h2').html(lang_search_results);
  jQueryMod2('.it_close span').html(lang_close);
}