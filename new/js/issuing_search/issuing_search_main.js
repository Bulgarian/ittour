/**
 * получает id для валюты которая выбрана сейчас активной в 
 * шапке результатов поиска пакетных туров, не модуля, а именно результатов поиска!
 * 
 * @returns {Number}
 */
function getCurrencyIdInCurrentMoment() {
    var current_active_currency_id = jQueryMod2('select.itt_issuing_search_custom_select_swith_currency').val();
    if (typeof current_active_currency_id == 'undefined') current_active_currency_id = 2;
    return current_active_currency_id;
}

/**
 * Генерация кастомных табов для результатов поиска пакетных туров.
 * 
 * @returns {undefined}
 */
function initTabsForResultatSearchPackageTour() {
    //--- Tabs START ---//
    jQueryMod2('.itt_issuing_search_tabs').children('li').first().children('a.itt_issuing_search_links_head_tab').addClass('is-active').next().addClass('is-open').show();
    jQueryMod2('.itt_issuing_search_tabs').on('click', 'li > a.itt_issuing_search_links_head_tab', function(event) {
        if (!jQueryMod2(this).hasClass('is-active')) {
            event.preventDefault();
            jQueryMod2('.itt_issuing_search_tabs .is-open').removeClass('is-open').hide();
            jQueryMod2(this).next().toggleClass('is-open').toggle();
            jQueryMod2('.itt_issuing_search_tabs').find('.is-active').removeClass('is-active');
            jQueryMod2(this).addClass('is-active');
        } else {
            event.preventDefault();
        }
        
        // Set only active first sub tab (fix bug)
        jQueryMod2(this).next().find('.itt_accordion_tabs li.itt_tab_head_cont:first a.title_sub_tab').trigger('click');
    });
    //--- Tabs END ---//
}

/**
 * Кастомный pop-up при наведении на картинку в результатах выдачи всплывает 
 * окно с этой же картинкой только размером в cssMaxHeight(px)
 * 
 * @returns {undefined}
 */
function initHoverPupUpHotelPhoto(){
    // Begin custom pop-up with image hotel //
    jQueryMod2('ul.itt_issuing_search_more_photo li img').mouseover(function(){
        // Show pop-up
        
        // Set img obj
        var hoverImg = jQueryMod2(this);
        var src = hoverImg.attr('bigsrc');
        
        // Get html pop-up
        var popUpHtml = hoverImg.parent().parent().next(); // == jQueryMod2('div.itt_hotel_image_item_custom_tooltip');
        
        // Check variable and exit
        if (typeof src == 'undefined') return;
        if (typeof popUpHtml == 'undefined') return;
        
        // Insert image to pop-up
        popUpHtml.find('img').attr('src', src);
        
        // Delete / add class
        popUpHtml.removeClass('itt_show_big_popup_preview');
        popUpHtml.addClass('itt_show_small_popup_preview');
        
        // Show pop-up
        popUpHtml.show();
        
        // Load all images for current hotel
        var tmp_image = new Image();
        tmp_image.src = src;
        all_big_photo_for_hotel_obj["'" + src + "'"] = tmp_image;
    }).mouseout(function(){
        // Hide pop-up
        
        // Set img obj
        var hoverImg = jQueryMod2(this);
        
        // Get html pop-up
        var popUpHtml = hoverImg.parent().parent().next(); // == jQueryMod2('div.itt_hotel_image_item_custom_tooltip');
        
        // Unset img in pop-up
        popUpHtml.find('img').attr('src', '');
        
        // Hide pop-up
        popUpHtml.hide();
    });
    // End custom pop-up with image hotel //
    
    // Handler hover to main hotel photo
    jQueryMod2('.itt_issuing_search_photo_prew img').mouseover(function(){
        // Show pop-up
        
        // Set img obj
        var hoverImg = jQueryMod2(this);
        var src = hoverImg.attr('bigsrc');
        
        // Get html pop-up
        var popUpHtml = hoverImg.parent().next().next(); // == jQueryMod2('div.itt_hotel_image_item_custom_tooltip');
        
        // Check variable and exit
        if (typeof src == 'undefined') return;
        if (typeof popUpHtml == 'undefined') return;
        
        // Insert image to pop-up
        popUpHtml.find('img').attr('src', src);
        
        // Delete / add class
        popUpHtml.removeClass('itt_show_small_popup_preview');
        popUpHtml.addClass('itt_show_big_popup_preview');
        
        // Show pop-up
        popUpHtml.show();
        
        // Load all images for current hotel
        var tmp_image = new Image();
        tmp_image.src = src;
        all_big_photo_for_hotel_obj["'" + src + "'"] = tmp_image;
    }).mouseout(function(){
        // Hide pop-up
        
        // Set img obj
        var hoverImg = jQueryMod2(this);
        
        // Get html pop-up
        var popUpHtml = hoverImg.parent().next().next(); // == jQueryMod2('div.itt_hotel_image_item_custom_tooltip');
        
        // Unset img in pop-up
        popUpHtml.find('img').attr('src', '');
        
        // Hide pop-up
        popUpHtml.hide();
    });
}

/**
 * Кастомный селект ТОЛЬКО для переключения валюты к результатам выдачи пакетного тура
 * 
 * @returns {undefined}
 */
function initCustomSelectSearchResultOnlyTopCurrency() {
    // Begin Кастомный селект переключения валюты к результатам выдачи пакетного тура //
    // Iterate over each select element
    jQueryMod2('select.itt_issuing_search_custom_select_swith_currency').each(function () {
        // Cache the number of options
	var $this = jQueryMod2(this),
        numberOfOptions = jQueryMod2(this).children('option').length;
        
        // Hides the select element
	$this.addClass('itt_issuing_search_custom_select_swith_currency_hidden');

	// Wrap the select element in a div
	$this.wrap('<div class="select"></div>');

	// Insert a styled div to sit over the top of the hidden select element
	$this.after('<div class="itt_issuing_search_custom_select_swith_styled_select"></div>');

	// Cache the styled div
	var $styledSelect = $this.next('div.itt_issuing_search_custom_select_swith_styled_select');

	// Show the first select option in the styled div	
	$styledSelect.prepend($this.children('option:selected').text());
        
	// Insert an unordered list after the styled div and also cache the list
	var $list = jQueryMod2('<ul />', {
				'class': 'itt_issuing_search_custom_select_swith_custom_select_options'
                    }).insertAfter($styledSelect);

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
            
            // New version set hide/show custom select 
            // * - (скрывает элемент, если пользователь не выбрал ничего, и кликнул по открытому селекту)
            jQueryMod2('div.itt_issuing_search_custom_select_swith_styled_select.active').not(this).removeClass('active').next('ul.itt_issuing_search_custom_select_swith_custom_select_options').hide();
            jQueryMod2(this).toggleClass('active').next('ul.itt_issuing_search_custom_select_swith_custom_select_options').toggle();
	});
        
        // Hides the unordered list when a list item is clicked and updates the styled div to show the selected list item
	// Updates the select element to have the value of the equivalent option
        // Begin handler selector currency in top search result //
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
            
            
            // Handler foe show/hide new proce for selected currency
            // Set value
            var option_value = jQueryMod2(this).attr('rel');
            if (typeof option_value == 'undefined') option_value = 2;
            
            // Hide all price            
            jQueryMod2('[class^="itt_item_tour_price_currency_"]').each(function(){
                jQueryMod2(this).hide();
            });
            // Show new price
            jQueryMod2('li.itt_item_tour_price_currency_' + option_value).each(function(){
                jQueryMod2(this).show();
            });
            
                        
            // Hide all select 'odher date'
            jQueryMod2('select.itt_item_tour_price_currency_1, select.itt_item_tour_price_currency_2, select.itt_item_tour_price_currency_10').each(function(){
                // Проверяем чтобы выбирать только кастомные селекты
                if (jQueryMod2(this).parent().hasClass('select')) {
                    // Удаляем класс для скрытия самого селекта
                    jQueryMod2(this).removeClass('itt_issuing_search_custom_select_swith_currency_hidden');
                    
                    // Копируем в буфер html вместе с селектом
                    jQueryMod2('div.itt_class_for_save_bufer').html(jQueryMod2(this).parent().html());
                    
                    // В буфере удаляю структуру кастомного селекта
                    jQueryMod2('div.itt_class_for_save_bufer div').remove();
                    jQueryMod2('div.itt_class_for_save_bufer ul').remove();
                    
                    // Добавляем селекту сласс чтобы он не вылазил при переключении валют, hidden!
                    jQueryMod2('div.itt_class_for_save_bufer').find('select').addClass('itt_issuing_search_custom_select_swith_currency_hidden');
                    
                    // Копирую из буфера уже обычный селект обратно в начало блока
                    jQueryMod2(this).parent().parent().prepend(jQueryMod2('div.itt_class_for_save_bufer').html()); // main block
                    
                    // Удаляю прежний кастомный селект в том же блоке
                    jQueryMod2(this).parent().find('div.itt_custom_select_other_date_styled_select').remove();
                    jQueryMod2(this).parent().find('ul.itt_custom_select_other_date_options').remove();
                    jQueryMod2(this).parent().parent().find('div.select').remove();
                    
                    // Удаляю html в буфере
                    jQueryMod2('div.itt_class_for_save_bufer').find('select').remove();
                }
            });
            
            
            // Show all select 'other date' 
            // Генерирую заново кастомные селекты для новой выбранной валюты
            initCustomSelectSearchResult('itt_item_tour_price_currency_' + option_value);
	});
        // End handler selector currency in top search result //
        
        // Hides the unordered list when clicking outside of it
	jQueryMod2(document).click(function () {
            $styledSelect.removeClass('active');
            $list.hide();
        });
    });
    // End Кастомный селект переключения валюты к результатам выдачи пакетного тура //
}

/**
 * Кастомный селект к выборке 'других дат' для результатов выдачи пакетных туров
 * 
 * @param {string} class_name
 * @returns {undefined}
 */
var itt_hide_cursor_delay = 700;
var itt_timeout_nicescroll = null;
function initCustomSelectSearchResult(class_name) {
    // Iterate over each select element
    jQueryMod2('select.itt_custom_select_other_date.' + class_name).each(function () {
        // Cache the number of options
	var $this = jQueryMod2(this),
        numberOfOptions = jQueryMod2(this).children('option').length;
        
        // Hides the select element
	$this.addClass('itt_issuing_search_custom_select_swith_currency_hidden');

	// Wrap the select element in a div
	$this.wrap('<div class="select"></div>');
        
	// Insert a styled div to sit over the top of the hidden select element
	$this.after('<div class="itt_custom_select_other_date_styled_select"></div>');
        
	// Cache the styled div
	var $styledSelect = $this.next('div.itt_custom_select_other_date_styled_select');

	// Show the selected option in the styled div
	$styledSelect.prepend($this.children('option:selected').text());
        
	// Insert an unordered list after the styled div and also cache the list
	var $list = jQueryMod2('<ul />', {
				'class': 'itt_custom_select_other_date_options'
                                }).insertAfter($styledSelect);

	// Insert a list item into the unordered list for each select option
	for (var i = 0; i < numberOfOptions; i++) {
            jQueryMod2('<li />', {
			text: $this.children('option').eq(i).text(),
			rel: $this.children('option').eq(i).val(),
            title: $this.children('option').eq(i).val(),
            tour_id: $this.children('option').eq(i).attr('tour_id'),
            sharding_rule_id: $this.children('option').eq(i).attr('sharding_rule_id')
			}).appendTo($list);
	}
        
	// Cache the list items
	var $listItems = $list.children('li');
        
	// Show the unordered list when the styled div is clicked (also hides it if the div is clicked again)
	$styledSelect.click(function (e) {
            e.stopPropagation();
            
            // New version set hide/show custom select 
            // * - (скрывает элемент, если пользователь не выбрал ничего, и кликнул по открытому селекту)
            jQueryMod2('div.itt_custom_select_other_date_styled_select.active').not(this).removeClass('active').next('ul.itt_custom_select_other_date_options').hide();
            jQueryMod2(this).toggleClass('active').next('ul.itt_custom_select_other_date_options').toggle();
	});
        
        // Hides the unordered list when a list item is clicked and updates the styled div to show the selected list item
	// Updates the select element to have the value of the equivalent option
	$listItems.click(function (e) {
            e.stopPropagation();
            $styledSelect.text(jQueryMod2(this).text()).removeClass('active');
            $this.val(jQueryMod2(this).attr('rel'));
            $list.hide();
            
            // Set new date to link 'Подробнее' , 'Цена' , 'Название отеля'
            var tour_id = jQueryMod2(this).attr('tour_id');
            var sharding_rule_id = jQueryMod2(this).attr('sharding_rule_id');
            if ((typeof tour_id != 'undefined') && (typeof sharding_rule_id != 'undefined')) {
                var onclick_html = 'return openPackageTourViewInTab(' + tour_id + ', ' + sharding_rule_id  + ');';
                
                // Set new link to html // 'подробнее'
                $this.parent().parent().parent().parent().parent().parent()
                     .find('a.itt_issuing_search_read_more_btn')
                     .attr('onclick', onclick_html);
             
                // Set new link to html // 'название отеля'
                $this.parent().parent().parent().parent().parent().parent()
                     .find('a.itt_issuing_search_tours_name_link_castom')
                     .attr('onclick', onclick_html);
             
                // Set new link to html // 'от 58 643 грн'
                $this.parent().parent().parent().parent().parent().parent()
                     .find('a.itt_issuing_search_link_in_main_big_price')
                     .attr('onclick', onclick_html);
            }
            
            // Set new date in 'Выезд'
            var option_value = jQueryMod2(this).attr('rel');
            if (typeof option_value != 'undefined') {
                var tmp_arr = option_value.split(' - ');
                
                // Begin set new 'Выезд' ***************************************
                // Создаем шаблон для замены поля 'Выезд'
                var new_departure_tpl = '<span class="itt_font-bold">xxx</span> <span>weekday.</span> <span class="itt_font-bold">yyy zzz</span>';
                // Example:             '<span class="itt_font-bold">19.02.15</span> <span>пн.</span> <span class="itt_font-bold">на 7 дн./6нч.</span>';
                var new_departure_date_tmp = tmp_arr[0].split(' ');// Разбиваем '31.03.15 вт. на 7дн./5ноч.' по пробелам.
                
                // Получаем и устанавливаем день недели
                var date_tmp = new_departure_date_tmp[0].split('.');// '31.03.15'
                var yyyy = '20' + date_tmp[2];// 15 // год
                var m = date_tmp[1];// 03 // месяц
                var dd = date_tmp[0];// 31 // день
                m = m.replace(/^(0)+/, '');
                m = m - 1;// (нумерация с 0 до 11)
                
                // Получаем день недели
                var weekday = ["вс", "пн", "вт", "ср", "чт", "пт", "сб"][new Date(yyyy, m, dd).getDay()];
                //var weekday = ["вс", "пн", "вт", "ср", "чт", "пт", "сб"][new Date(2015, 2, 26).getDay()];
                
                // Replace data in template
                new_departure_tpl = new_departure_tpl.replace(/(xxx)+/, new_departure_date_tmp[0])// '31.03.15'
                                                     .replace(/(yyy)+/, new_departure_date_tmp[1])// 'на'
                                                     .replace(/(zzz)+/, new_departure_date_tmp[2])// '7дн./6ноч.'
                                                     .replace(/(weekday)+/, weekday);
                // Set new html for #1 block
                $this.parent().parent().parent().parent()
                     .find('.itt_media_min_table .itt_tour_departure_date_in_search_result_package')
                     .html(new_departure_tpl);
             
                // Set new html for #1 block
                $this.parent().parent().parent().parent()
                     .find('.itt_media_max_table .itt_tour_departure_date_in_search_result_package')
                     .html(new_departure_tpl);
                // End set new 'Выезд' *****************************************
                
                // Set new price
                // Get currency in top selector
                var currency_id = getCurrencyIdInCurrentMoment();
                // Get new number price
                var new_price = tmp_arr[1];
                new_price = new_price.slice(0, -1);// Удалил последний символ ('$' или '€' или '.')
                //new_price = new_price.replace(/[a-z, A-Z, а-я, А-Я]+/, '');// Удалил текст 'грн'.// work only utf-8
                $this.parent().parent().parent().parent().parent().parent()
                     .find('ul.itt_issuing_search_text_price_list li.itt_item_tour_price_currency_' + currency_id + ' span.itt_issuing_search_price')
                     .html(new_price);
            }
            
            // Set all price for all currency
            var prices_str = $this.find('option[tour_id="' + tour_id + '"][sharding_rule_id="' + sharding_rule_id + '"]').attr('prices');
            if (typeof prices_str != 'undefined') {
                var prices_arr = prices_str.split(';');//1;2;10
                    
                // Set price '1'
                $this.parent().parent().parent().parent().parent().parent()
                     .find('li.itt_item_tour_price_currency_1 span.itt_issuing_search_price')
                     .html(prices_arr[0]);
                // Set price '2'
                $this.parent().parent().parent().parent().parent().parent()
                     .find('li.itt_item_tour_price_currency_2 span.itt_issuing_search_price')
                     .html(prices_arr[1]);
                // Set price '10'
                $this.parent().parent().parent().parent().parent().parent()
                     .find('li.itt_item_tour_price_currency_10 span.itt_issuing_search_price')
                     .html(prices_arr[2]);
            }
            
            // Set transports 'Вылет'
            var transports = $this.find('option[tour_id="' + tour_id + '"][sharding_rule_id="' + sharding_rule_id + '"]').attr('transports');
            if (typeof transports != 'undefined') {
                var tmp_arr = transports.split(' ');
                var new_transport_tpl = '<span class="itt_font-bold">xxx</span> <span>yyy</span>';
                new_transport_tpl = new_transport_tpl.replace(/(xxx)+/, tmp_arr[0])// 'Днепропетровск'
                                                     .replace(/(yyy)+/, tmp_arr[1]);// '13:25'
                
                // Set new 'Вылет'
                $this.parent().parent().parent().parent()
                     .find('.itt_media_min_table .itt_transports_flights_time_city_from')
                     .html(new_transport_tpl);
                $this.parent().parent().parent().parent()
                     .find('.itt_media_max_table .itt_transports_flights_time_city_from')
                     .html(new_transport_tpl);
            }
            
            // Set meals
            //var meals = $this.find('option[value="' + jQueryMod2(this).attr('rel') + '"]').attr('meals');
            var meals = $this.find('option[tour_id="' + tour_id + '"][sharding_rule_id="' + sharding_rule_id + '"]').attr('meals');
            if (typeof meals != 'undefined') {
                var tmp_arr = meals.split('_sep_');
                var new_meals_tpl = '<span class="itt_font-bold">xxx</span> <span>(yyy)</span>';
                new_meals_tpl = new_meals_tpl.replace(/(xxx)+/, tmp_arr[0])
                                             .replace(/(yyy)+/, tmp_arr[1]);
                
                // Set new meal
                $this.parent().parent().parent().parent()
                     .find('.itt_media_min_table .itt_meal_description')
                     .html(new_meals_tpl);
                $this.parent().parent().parent().parent()
                     .find('.itt_media_max_table .itt_meal_description')
                     .html(new_meals_tpl);
            }
            
            // Handler for set 'selected' element in real select
            // Delete old selected
            $this.children('option').each(function(){
                jQueryMod2(this).removeAttr('selected');
            });
            
            // Set new selected
            $this.find('option[tour_id="' + tour_id + '"][sharding_rule_id="' + sharding_rule_id + '"]').attr('selected', 'selected');
	});
        
        // Hides the unordered list when clicking outside of it
	jQueryMod2(document).click(function () {
            $styledSelect.removeClass('active');
            $list.hide();
        });
    });

    jQueryMod2(".itt_custom_select_other_date_options").niceScroll({
        cursorcolor: "#BCBCBC",
        cursorborder: "0px",
        cursorborderradius: "2px",
        hidecursordelay: itt_hide_cursor_delay,
        background: "#E1E1E1",
        zindex: 1300,
        horizrailenabled: false
    });
    jQueryMod2(".itt_custom_select_other_date_options").mousemove(function(e) {
        var scroll = jQueryMod2(this).getNiceScroll();
        jQueryMod2("#" + scroll[0].id).mouseover();
        
        clearTimeout(itt_timeout_nicescroll);
        itt_timeout_nicescroll = setTimeout(function() {
            jQueryMod2("#" + scroll[0].id).mouseout();
        }, itt_hide_cursor_delay);
    });
    jQueryMod2(".itt_custom_select_other_date_options").mouseout(function(e) {
        var scroll = jQueryMod2(this).getNiceScroll();
        jQueryMod2("#" + scroll[0].id).mouseout();
    });
}



// *** Begin tabs in search result ******************************************
// Set global vars for tabs
var tabPreloaderContentNewText;
tabPreloaderContentNewText = jQueryMod2('#tour_search_module_mod2 #lang_text_constant_for_js .please_wait_ttt').html();
if (typeof tabPreloaderContentNewText == 'undefined') tabPreloaderContentNewText = 'Пожалуйста подождите...';
var tabPreloaderTitleNew   = '<div id="tab_title_loader_777555777" style="text-align: center; display: inline-block;"></div>',
    tabPreloaderContentNew = '<div class="itt_page_loader itt_page_loader_for_tab_content"><span>' + tabPreloaderContentNewText + '</span><div id="tab_content_loader_888999888" class="itt_page_loader_img" style="margin-left: 50px;"></div></div>',
    tabTemplateNew         = '<li class="itt_issuing_search_tabs_head_cont" id="777888777">'
                           +   '<a href="#" class="itt_issuing_search_links_head_tab"><span class="itt_tab_bg_write">' + tabPreloaderTitleNew + '<span class="itt_glyphicon itt_glyphicon-remove itt_issuing_search_remove_tab"></span></span></a>'
                           +   '<section>' + tabPreloaderContentNew + '</section>'
                           + '</li>',
    tabCounterNew = 100;
    
/**
 * function add newtab for user view tour in neew tab
 * @param {void} not params
 * @return {string} 'id' ID html params
 */
function addTabForTourStretch() {
  // Create tabs
  initTabsForResultatSearchPackageTour();
  
  // Increment number
  tabCounterNew++;
  
  // Get object current tab
  var tabs = jQueryMod2('#itt_issuing_search_tabs_main_block');
  
  // Get new html 'id'
  var id = 'search_result_tab_' + tabCounterNew;
    
  // Get html item tab
  var tabTemplateNewTMP = tabTemplateNew;
  
  // Set new 'id' in html
  tabTemplateNewTMP = tabTemplateNewTMP.replace(/(777888777)+/ , id)
                                       .replace(/(888999888)+/ , tabCounterNew)
                                       .replace(/(777555777)+/ , tabCounterNew);
  
  // Add new tab as last li
  tabs.append( tabTemplateNewTMP );
  
  // Cereate pre-loader in content 'tab' 
  initCustomPreloaderIttour('tab_content_loader_' + tabCounterNew, '#2473b8', 37, 1.7, 23, true);
  
  // Cereate pre-loader in title 'tab'
  initCustomPreloaderIttour('tab_title_loader_' + tabCounterNew, '#2473b8', 20, 1.7, 23, true);
  
  // Set active new (last) tab
  jQueryMod2('#itt_issuing_search_tabs_main_block li').last().find('a').trigger('click');
  
  // Check space for new tab
  checkWidthSpaseForTabsStretch();
  
  return id;
}

/**
 * function check 'sum width all li' and 'space for tabs', delete 'second tab'.
 * @param {void} not params
 * @return {void} not params
 */
function checkWidthSpaseForTabsStretch() {
  // Get width for space for tabs  
  var space_for_tabs = jQueryMod2('#itt_issuing_search_tabs_main_block').width();
  
  // Get sum width all 'li' (tabs) in space
  var sum_width_all_li = 0;  
  jQueryMod2('#itt_issuing_search_tabs_main_block li a.itt_issuing_search_links_head_tab').each(function(){
    sum_width_all_li += jQueryMod2(this).width();
    sum_width_all_li += 10;// margin-right: 5px
  });
  
  sum_width_all_li += 50;// 50px as margin for last tab :-]
  
  if (sum_width_all_li > space_for_tabs) {
    // Delete 'second tab'
    // удаляю второй таб т.к. первый содержит результаты поиска
    jQueryMod2('#itt_issuing_search_tabs_main_block li.itt_issuing_search_tabs_head_cont').eq(1).remove();
  }
}

/**
 * Функция для закрытия таба (с какимто туром) в рузультате поиска.
 * 
 * @returns {undefined}
 */
function initButtonCloseCustomItemTab() {
    // Handler for close tab
    jQueryMod2('#itt_issuing_search_tabs_main_block li a.itt_issuing_search_links_head_tab span.itt_issuing_search_remove_tab').live('click', function(){
        // Get current click element (span.itt_issuing_search_remove_tab)
        var a = jQueryMod2(this).parent().parent();// 'a' element
        
        // тут по клику на закрыть, элемент становится активным,
        // соответственно удаляю кликаемый таб и ставлю 
        // активным первый, тоесть все туры
          
        // Remove current tab and content
        if (a.parent().hasClass('itt_issuing_search_tabs_head_cont')) a.parent().remove();
          
        // Set first tab 'active'
        jQueryMod2('#itt_issuing_search_tabs_main_block li').first().find('a.itt_issuing_search_links_head_tab').trigger('click');
    });    
}

// Пересчитывать табы и закрывать те которые невлазят
jQueryMod2(window).resize(function(){
    // Fix for для того чтобы при резком изменении размера => удалить все 
    // невлезаюшие табы, 
    // 25 это кол-во табов на максимальном разрешении еще и с запасом
    for (var i=0; i<25; i++) checkWidthSpaseForTabsStretch();
});
jQueryMod2(document).resize(function(){
    // Fix for для того чтобы при резком изменении размера => удалить все 
    // невлезаюшие табы, 
    // 25 это кол-во табов на максимальном разрешении еще и с запасом
    for (var i=0; i<25; i++) checkWidthSpaseForTabsStretch();
});

/**
 * функция перемещиет полученую шапку в результате поиска на 
 * нужное сверстанное место, а также удаляет ее в случае нового поиска.
 * 
 * @param {boolean} remove
 * @returns {undefined}
 */
function setHeaderInResultSearch(remove) {
  if (remove) {
    // Delete element from content
    
    // Delete left block in header
    jQueryMod2('#itt_issuing_search_package_tours > .itt_row .itt_issuing_search_results_information').remove();
    
    // Delete right block in header
    jQueryMod2('#itt_issuing_search_package_tours > .itt_row .itt_issuing_search_currency_swith').remove();
    jQueryMod2('#itt_issuing_search_package_tours > .itt_row .itt_currency_swith_position').remove();
  } else {
    // Copy element and insert to top header result
    var header_html = jQueryMod2('#tour_search_module_mod2 div.itt_search_result_header_content').html();
    
    if (header_html != '') {
        // Move to new position
        jQueryMod2('#itt_issuing_search_package_tours > .itt_row').prepend(header_html);
        
        // Remove html
        jQueryMod2('#tour_search_module_mod2 div.itt_search_result_header_content').remove();
        
        afterLoadSearchResult();
    }
    
    // Check count_all_package_tours
    var count_all_package_tours = jQueryMod2('#tour_search_module_mod2 #itt_tour_search_result input[name="count_all_package_tours"]').val();
    if (count_all_package_tours == 0) {
      // Скрываю блоки все кроме камента о том что нет туров
      jQueryMod2('#tour_search_module_mod2 #itt_issuing_search_tabs_main_block').hide()
        .prev().hide()// скрыл блок в селектом цен
        .prev().hide(); // Скрыл блок с текстом слправа
      ;
    } else {
      // Показываю блок в табом, если туров больше '0'.
      jQueryMod2('#tour_search_module_mod2 #itt_issuing_search_tabs_main_block').show();
    }
  }
}

/**
 * Реинициализация блоков после загрузки
 * 
 * @returns {undefined}
 */
function afterLoadSearchResult() {
    // Create handler for close tab
    initButtonCloseCustomItemTab();
    
    // Create select for top currency
    initCustomSelectSearchResultOnlyTopCurrency();
    
    // Create select for 'other date'
    initCustomSelectSearchResult('itt_item_tour_price_currency_' + getCurrencyIdInCurrentMoment());
    
    // Create handler for pop-up with image hotel
    initHoverPupUpHotelPhoto();
}
// *** End tabs in search result ********************************************

/**
 * Custom Google map 
 * 
 * @param {integer} zoomInteger
 * @param {string} htmlID
 * @param {float} lat
 * @param {float} lng
 * @returns {undefined}
 */
function initialize_google_map_custom_view_tour(zoomInteger, htmlID, lat, lng) {
    var myLatlng = new google.maps.LatLng(lat, lng);
    
    // Set option for map
    var mapOptions = {
        zoom: zoomInteger,
        center: myLatlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP // HYBRID // SATELLITE // ROADMAP
    };

    // Create map
    var map = new google.maps.Map(document.getElementById(htmlID), mapOptions);
    
    // Set marker
    var marker = new google.maps.Marker({
        position: myLatlng,
        map: map
    });
    
    return false;
}

// Клик на 'Всего 24 тура'
jQueryMod2(document).ready(function() {
    jQueryMod2('#tour_search_module_mod2 .itt_button_copy_click_custom_select_other_date').live('click', function(e){
        e.stopPropagation();
        jQueryMod2(this).parent().prev().find('.itt_custom_select_other_date_styled_select').trigger('click');
    });
});
