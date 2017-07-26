        var search_box;
        var order_box;
        var view_box;
        var popupWin;
        var search_request_active = false;
        var ittour_order_id = '';
        var default_serialize_module_form_search = '';
        if(typeof(jQueryMod2) == 'undefined') {
            jQueryMod2 = jQuery;
        }
        
        jQueryMod2(function()  {
            jQueryMod2('.itt_show_currency_popap').live('click', function(){
                jQueryMod2('.itt_currency_popap').show();
            });
            
            jQueryMod2('.itt_currency_popap li').live('click', function(){
                jQueryMod2('.itt_currency_popap').hide();
                jQueryMod2('.itt_active_currency').removeClass('itt_active_currency');
                jQueryMod2(this).addClass('itt_active_currency');
                jQueryMod2('span[class^=itt_price_cur_list_]').hide();
                jQueryMod2('.itt_price_cur_list_'+jQueryMod2(this).attr('show_cur')).show();
                jQueryMod2('.itt_currency_popap_currency').text(jQueryMod2(this).text());
            });
        });


/**
 * function set country and region name in input 'Страна, регион'
 * 
 * @returns {undefined}
 */
function setCountryAndRegionNameInInputPackagePart(){
    var strRegions = '';
    jQueryMod2('#tour_search_module_mod2 .itt_country-region_popup .itt_region label.ui-state-active span').each(function(){
        var regionText = jQueryMod2(this).html();
        if (strRegions == '') {
            strRegions = regionText;
        } else {
            strRegions = strRegions + ', ' + regionText;
        }
    });
    
    // Chrck regions not empty
    if (strRegions != '') {
        // Get current country
        var currentCountryText = jQueryMod2('#tour_search_module_mod2 .itt_multiple-sel .overview div.selected span').html();
        var finalTextForInput = strRegions;
        if (currentCountryText != null) {
            finalTextForInput = currentCountryText + ', ' + strRegions;
        }
        jQueryMod2('#tour_search_module_mod2 #itt_module_search_filter').val(finalTextForInput);
    }
}


var language = "<?php echo $module->settings->language;?>";
var path = (language == 'russian') ? 'new/js/view_tour/validation/localization/messages_ru.js' : '';
ModuleLoader
     //Установим путь к модулям
    .setPath("<?php echo $module->client->get_config('modules_url');?>")
    
    //Загружаемые css файлы. Синхронно
    .addCss('new/css/module_search/module_search_main.css', true)
    
    // Css custom bootstrap
    .addCss('new/css/module_search/css/itt_power_media_custom.css', true)
    
    // New stretch module search
    .addCss('new/css/module_search/css/style_mod_search.css', true)
    .addCss('new/css/module_search/css/scroll_mod_search.css', true)
    
    // New stretch result module search
    .addCss('new/css/module_search/css/style_issuing_search.css', true)
    .addCss('new/css/module_search/css/thickbox_issuing_search.css', true)
    
    // New stretch tour view
    .addCss('new/css/module_search/css/style_view_tour_package.css', true)
    
    
    //Загружаемые js файлы. По очереди
    .addJs('new/js/jquery-1.7.2.min.js', true)
    .addJs('new/js/jquery-ui-1.10.3.custom.min.js', true)
    .addJs('new/js/jquery.tinyscrollbar.min.new.js', true)
    .addJs('new/js/issuing_search/heartcode-canvasloader-min-0.9.1.js', true)// pre-loadr lib
    .addJs('new/js/module_search/module_search.js', false)
    .addJs('new/js/vendor/html5shiv.js', true)
    .addJs('new/js/jquery.nicescroll.min.js', true)
    
    // Js для рузультатов выдачи поиска пакетных туров
    .addJs('new/js/issuing_search/thickbox.js', true)
    .addJs('new/js/issuing_search/issuing_search_main.js', true)
    
    // from old
    .addJs('new/js/from_old/tour_seach_form.js', true)
    
    // Js for new desing view tour (package and hike)
    .addJs('new/js/view_tour/jquery.flexslider.js', true)
    .addJs('new/js/view_tour/jquery.mask.min.js', true)
    .addJs('new/js/view_tour/tour_view_new_mod.js', true)
    .addJs('http://maps.googleapis.com/maps/api/js?key=AIzaSyAeFA35meUbC-ReOBPS_Wbnw2SJ4BUOPsE&sensor=true&callback=initialize_google_map_custom', true)
    .addJs('new/js/view_tour/validation/jquery.validate.min.js', true)
    .addJs(path, true)

    // Событие, после загрузки всех файлов
    .setEvent('after_load', function(){
        
        ModuleSearch.setOptions({
            'modules_url'       : "<?php echo $module->client->get_config('modules_url'); ?>",
            'modules_action'    : "<?php echo $module->client->get_config('search_module_action_with_params'); ?>",
            'show_basket'       : "<?php echo $module->client->get_config('show_basket') ? 1 : 0; ?>",
            'use_hotel_result'  : "<?php echo $module->client->get_config('use_hotel_result') ? 1 : 0 ; ?>",
            'preview'           : "<?php echo $module->client->get_config('preview') ? 1 : 0; ?>",
            'extended_search_url' : "",
            'modules_popup_result'  : true,
            'modules_popup_type'    : 'div',
        });
        
        
        //TODO: INIT JS FILE
        
        var hike_path    = '.itt_search-module_new .itt_hike ';
        var package_path = '.itt_search-module_new .itt_package ';
        
        /*
         * item: путь к элементу
         * val : пользовательская функция получения значения(jQueryMod2 object, default value)
         * default: значиние по умолчанию
         * block: путь к родителю данного элемента. Применимо для списков
         */
        Hike.setParams({
            country:{
                item: (hike_path + '.itt_counry_list'),
                val : function(this_item, def){return this_item.val()?this_item.val()[0]:def;},
                default: 0,
            },
            tour_city:{
                item: (hike_path + '.tour_city .itt_list-checkbox input'),
                val : function(this_item, def){
                        var arr = [];
                        jQueryMod2.each(this_item.filter(':checked'), function(){
                            arr.push(jQueryMod2(this).val());
                        });
                        return arr.length == 0 ? def : arr;
                },
                block: (hike_path + '.tour_city .itt_list-checkbox'),
                default: [0],
            },
            transport:{
                item: (hike_path + '.itt_transport'),
                val : function(this_item, def){
                     if(this_item.is('ul')) {
                        this_item = jQueryMod2('.itt_search-module_new .itt_hike .itt_transport');
                        if(this_item.is('ul')) {
                            var val = '';
                            this_item.find('input[type=hidden]').each(function(){
                                var $tt = jQueryMod2(this);
                                var tmpval = $tt.val(); 
                                if(tmpval && tmpval != ' ') {
                                    val += $tt.val()+' ';
                                }
                            });
                            var strLen = val.length;
                            if(strLen > 1) {
                                val = val.slice(0,strLen-1);
                            }
                            return val; 
                        }
                     } else {
                         return this_item.val();
                     }
                },
                default: 0,
            },
            date_from:{
                item: (hike_path + '.itt_date_from'),
            },
            date_period:{
                item: (hike_path + '.itt_date_period'),
            },
            date_till:{
                item: (hike_path + '.itt_date_till'),
            },
            departure_city:{
                item: (hike_path + '.itt_departure_city'),
                default: 0,
            },
            adults:{
                item: (hike_path + '.itt_adult'),
            },
            child1_age:{
                item: (hike_path + '.itt_pop-up-children select:eq(0)'),
                default: 0,
            },
            child2_age:{
                item: (hike_path + '.itt_pop-up-children select:eq(1)'),
                default: 0,
            },
            child3_age:{
                item: (hike_path + '.itt_pop-up-children select:eq(2)'),
                default: 0,
            },
            children:{
                item: (hike_path + '.itt_children'),
                val: function(this_item, def) {
                    var _val = this_item.val();
                    if( _val == null || _val == 'undefined' || typeof(_val) == 'undefined' || typeof(_val) == 'null') {
                        return 0;
                    } else {
                        return _val;
                    } 
                },
                default: 0,
            },
            night_from:{
                item: (hike_path + '.itt_tour_period'),
                val: function(period){return period.val() ? period.val().split('-')[0] : null}
            },
            night_till:{
                item: (hike_path + '.itt_tour_period'),
                val: function(period){return period.val() ? period.val().split('-')[1] : null}
            },
            price_from:{
                item: (hike_path + '.itt_price_from'),
            },
            price_till:{
                item: (hike_path + '.itt_price_till'),
            },
            currency:{
                item: (hike_path + '.itt_currency'),
                default: 'UAH'
            },
            tour_count_per_page:{
                item: (hike_path + '.itt_tour_count_per_page'),
            },
            tour_type: {
                item: '#itt_tour_type'
            },
        });
        
        Package.setParams({
            country:{
                item: package_path + '.itt_country',
                val : function(this_item, def){
                    var _val = this_item.val();
                    if(typeof(_val) == 'object') {
                        return _val?_val[0]:def;
                    } else {
                        return _val;
                    } 
                },
                default: 0,
            },
            region:{
                item: (package_path + '.itt_region .itt_list-checkbox input'),
                val : function(this_item, def){
                        var arr = [];
                        jQueryMod2.each(this_item.filter(':checked'), function(){
                            arr.push(jQueryMod2(this).val());
                        });
                        
                        // Если выбрали хоть один регион => 'Все регионы' => off
                        if (arr.length > 1) {
                            for (var i=0; i<arr.length; i++) {
                                if (arr[i] == 0) {
                                    jQueryMod2('.itt_region .itt_list-checkbox .itt_row_in_list_region input[value="0"]').trigger('click');
                                }
                            }
                        }
                        
                        return arr.length == 0 ? def : arr;
                },
                block: (package_path + '.itt_region .itt_list-checkbox'),
                default: [0],
            },
            date_from:{
                item: (package_path + '.itt_date_from'),
            },
            date_period:{
                item: (package_path + '.itt_date_period'),
            },
            date_till:{
                item: (package_path + '.itt_date_till'),
            },
            hotel_rating:{
                item: (package_path + 'input[name="itt_hotel_rating_total"]'),
                val: function(){
                    var hotel_total_rating = jQueryMod2(package_path + 'input[name="itt_hotel_rating_total"]').val();
                    if (hotel_total_rating == null || hotel_total_rating == 'undefined' || typeof(hotel_total_rating) == 'undefined' || typeof(hotel_total_rating) == 'null') {
                        return '3 4 78';
                    } else {
                        return hotel_total_rating;
                    }
                },
            },
            food:{
                item: (package_path + 'input[name="itt_food_package"]'),
                val: function(){
                    var food_rating = jQueryMod2(package_path + 'input[name="itt_food_package"]').val();
                    if (food_rating == null || food_rating == 'undefined' || typeof(food_rating) == 'undefined' || typeof(food_rating) == 'null') {
                        return '496 388 498 512 560 1956';
                    } else {
                        return food_rating;
                    }
                },
            },
            price_from:{
                item: (package_path + 'input[name="itt_price_from_package"]'),
                val: function(){
                    var price = jQueryMod2(package_path + 'input[name="itt_price_from_package"]').val();
                    if (price == null || price == 'undefined' || typeof(price) == 'undefined' || typeof(price) == 'null') {
                        return '0';
                    } else {
                        return price;
                    }
                },
            },
            price_till:{
                item: (package_path + 'input[name="itt_price_till_package"]'),
                val: function(){
                    var price = jQueryMod2(package_path + 'input[name="itt_price_till_package"]').val();
                    if (price == null || price == 'undefined' || typeof(price) == 'undefined' || typeof(price) == 'null') {
                        return '110000';
                    } else {
                        return price;
                    }
                },
            },
            currency:{
                item: (package_path + 'input[name="itt_switch_price_package"]'),
                val: function(){
                    var currency = jQueryMod2(package_path + 'input[name="itt_switch_price_package"]').val();
                    if (currency == null || currency == 'undefined' || typeof(currency) == 'undefined' || typeof(currency) == 'null') {
                        return 'UAH';
                    } else {
                        return currency;
                    }
                },
                default: 'UAH',
            },
            departure_city:{
                item: (package_path + 'input[name="itt_departure_city_package"]'),
                val: function(){
                    var departure_city = jQueryMod2(package_path + 'input[name="itt_departure_city_package"]').val();
                    if (departure_city == null || departure_city == 'undefined' || typeof(departure_city) == 'undefined' || typeof(departure_city) == 'null') {
                        return '0';
                    } else {
                        return departure_city;
                    }
                },
                default: '0',
            },
            departure_city_1:{
                item: (package_path + '.itt_departure_city_1'),
            },
            departure_city_2:{
                item: (package_path + '.itt_departure_city_2'),
            },
            departure_city_3:{
                item: (package_path + '.itt_departure_city_3'),
            },
            night_from:{
                item: (package_path + 'input[name="itt_night_from_package"]'),
                val: function(){
                    var night = jQueryMod2(package_path + 'input[name="itt_night_from_package"]').val();
                    if (night == null || night == 'undefined' || typeof(night) == 'undefined' || typeof(night) == 'null') {
                        return '6';
                    } else {
                        return night;
                    }
                },
                default: '6',
            },
            night_till:{
                item: (package_path + 'input[name="itt_night_till_package"]'),
                val: function(){
                    var night = jQueryMod2(package_path + 'input[name="itt_night_till_package"]').val();
                    if (night == null || night == 'undefined' || typeof(night) == 'undefined' || typeof(night) == 'null') {
                        return '14';
                    } else {
                        return night;
                    }
                },
                default: '14',
            },
            adults:{
                item: (package_path + 'input[name="itt_adult_package"]'),
            },
            child1_age:{
                item: (package_path + 'input[name="itt_child1_age_package"]'),
                val: function(){
                    var children = jQueryMod2(package_path + 'input[name="itt_children_package"]').val();
                    var returnVar = '0';
                    if (children == null || children == 'undefined' || typeof(children) == 'undefined' || typeof(children) == 'null') {
                        returnVar = '0';
                    } else if(children >=1) {
                        returnVar = jQueryMod2(package_path + 'input[name="itt_child1_age_package"]').val();
                    }
                    return returnVar;
                },
            },
            child2_age:{
                item: (package_path + 'input[name="itt_child2_age_package"]'),
                val: function(){
                    var children = jQueryMod2(package_path + 'input[name="itt_children_package"]').val();
                    var returnVar = '0';
                    if (children == null || children == 'undefined' || typeof(children) == 'undefined' || typeof(children) == 'null') {
                        returnVar = '0';
                    } else if(children >=2) {
                        returnVar = jQueryMod2(package_path + 'input[name="itt_child2_age_package"]').val();
                    }
                    return returnVar;
                },
            },
            child3_age:{
                item: (package_path + 'input[name="itt_child3_age_package"]'),
                val: function(){
                    var children = jQueryMod2(package_path + 'input[name="itt_children_package"]').val();
                    var returnVar = '0';
                    if (children == null || children == 'undefined' || typeof(children) == 'undefined' || typeof(children) == 'null') {
                        returnVar = '0';
                    } else if(children >=3) {
                        returnVar = jQueryMod2(package_path + 'input[name="itt_child3_age_package"]').val();
                    }
                    return returnVar;
                },
            },
            children:{
                item: (package_path + 'input[name="itt_children_package"]'),
                val: function(){return jQueryMod2(package_path + 'input[name="itt_children_package"]').val();},
                default: 0,
            },
            hotel:{
                item: (package_path + 'input[name="itt_hotel_package"]'),
                val: function(){
                    var hotel = jQueryMod2(package_path + 'input[name="itt_hotel_package"]').val();
                    if (hotel == null || hotel == 'undefined' || typeof(hotel) == 'undefined' || typeof(hotel) == 'null') {
                        return '';
                    } else {
                        return hotel;
                    }
                },
                default: '',
            },
            hotel_1:{
                item: (package_path + '.itt_hotel_1'),
            },
            hotel_2:{
                item: (package_path + '.itt_hotel_2'),
            },
            tour_count_per_page:{
                item: (package_path + '.itt_tour_per_page'),
            },
            tour_type: {
                item: '#itt_tour_type'
            },
        });
        
        
        /**
         * Экскурсионные туры
         */

        //Событие для заполнения: города на маршруте, транспорта и города отправления для Модуля поиска, при переключении страны.
        Hike.getItem('country').change(function(){
            Hike.getSearchFilteredField({}, function(data){
                // Шаблоны
                var tour_city_template = 
                    '<div class="itt_row itt_row_in_list_region">'+
                        '<input value="{{id}}" {% jQueryMod2.inArray({{id}}, ['+Hike.getData("tour_city")+']) == -1 ? "" : "checked" %} type="checkbox" id="tour_city_{{id}}" class="itt_tour_city_list"/>'+
                        '<label for="tour_city_{{id}}">{{name}}</label>'+
                    '</div>';
                var departure_city_template = '<option {% {{id}} == '+Hike.getData("departure_city")+' ? "selected" : "" %} value="{{id}}">{{name}}</option>';
                var transport_template = '<option {% {{id}} == '+Hike.getData("transport")+' ? "selected" : "" %} value="{{id}}">{{name}}</option>';
                
                // Генерация и вставка HTML 
                Hike.setHtml('departure_city', Hike.generateHtml(data.city.data, departure_city_template), function(item){item.trigger("chosen:updated");});
                Hike.setHtml('transport', Hike.generateHtml(data.transport.data, transport_template), function(item){item.trigger("chosen:updated");}); 
                
                // Update custom select 'departure_city' and 'hotel'
                updateCustomHtmlSelect(jQueryMod2('#tour_search_module_mod2 section.itt_hike .itt_transport').parent(), 'itt_transport');
                updateCustomHtmlSelect(jQueryMod2('#tour_search_module_mod2 section.itt_hike .itt_departure_city').parent(), 'itt_departure_city');
                
                // Генерация и вставка HTML для tour_city
                Hike.getBlock('tour_city').html(Hike.generateHtml(data.tour_city.data, tour_city_template));
                if(Hike.getItem('tour_city').filter(':checked').length == 0){
                    Hike.getItem('tour_city').first().attr('checked', 'checked');
                }
                initCustomCheckboxes();
                initTinyScrollbar();
            });
        });
        
        
        /**
         * Пакетные туры
         */
        
        var package_hotel_template = '<option {% {{id}} == '+Package.getData("hotel")+' ? "selected" : "" %} value="{{id}}">{{name}}</option>';              
        //Событие для заполнения: региона, отеля и города отправления для Модуля поиска, при переключении страны.
        Package.getItem('country').change(function(){
            Package.getSearchFilteredField({}, function(data){
                // Шаблоны
                var region_template = 
                    '<div class="itt_row itt_row_in_list_region">'+
                        '<input value="{{id}}" {% jQueryMod2.inArray({{id}}, ['+Package.getData("region")+']) == -1 ? "" : "checked" %} type="checkbox" id="tour_city_{{id}}" class="itt_tour_city_list"/>'+
                        '<label for="tour_city_{{id}}">{{name}}</label>'+
                    '</div>';
                var departure_city_template = '<option {% {{id}} == '+Package.getData("departure_city")+' ? "selected" : "" %} value="{{id}}">{{name}}</option>';
                
                // Генерация и вставка HTML 
                //Package.setHtml('departure_city', Package.generateHtml(data.departure_city.data, departure_city_template), function(item){item.trigger("chosen:updated");});// Old work
                //Package.setHtml('hotel', Package.generateHtml(data.hotel.data, package_hotel_template), function(item){item.trigger("chosen:updated");}); // Old work
                
                // Генерация и вставка HTML // New super stretch
                Package.setHtml('departure_city_1', Package.generateHtml(data.departure_city.data, departure_city_template), function(){
                    // Update custom select 'departure_city'
                    updateCustomHtmlSelect(jQueryMod2('#tour_search_module_mod2 section.itt_package .itt_departure_city_1').parent(), 'itt_departure_city_1');
                });
                Package.setHtml('departure_city_2', Package.generateHtml(data.departure_city.data, departure_city_template), function(){
                    // Update custom select 'departure_city'
                    updateCustomHtmlSelect(jQueryMod2('#tour_search_module_mod2 section.itt_package .itt_departure_city_2').parent(), 'itt_departure_city_2');
                });
                Package.setHtml('departure_city_3', Package.generateHtml(data.departure_city.data, departure_city_template), function(){
                    // Update custom select 'departure_city'
                    updateCustomHtmlSelect(jQueryMod2('#tour_search_module_mod2 section.itt_package .itt_departure_city_3').parent(), 'itt_departure_city_3');
                    
                    // Обновим скрытое поля по selected 'departure_city'
                    var depCity = jQueryMod2('#tour_search_module_mod2 section.itt_package .itt_departure_city_1 option:selected').val();
                    if (depCity != null || depCity != 'undefined' || typeof(depCity) != 'undefined' || typeof(depCity) != 'null') {
                        jQueryMod2('#tour_search_module_mod2 input[name="itt_departure_city_package"]').val(depCity);
                    } else {
                        jQueryMod2('#tour_search_module_mod2 input[name="itt_departure_city_package"]').val(0);
                    }
                });
                Package.setHtml('hotel_1', Package.generateHtml(data.hotel.data, package_hotel_template), function(){
                    // Update custom select 'hotel'
                    //updateCustomHtmlSelect(jQueryMod2('#tour_search_module_mod2 section.itt_package .itt_hotel_1').parent(), 'itt_hotel_1');// Old slow work
                });
                Package.setHtml('hotel_2', Package.generateHtml(data.hotel.data, package_hotel_template), function(){
                    //updateCustomHtmlSelect(jQueryMod2('#tour_search_module_mod2 section.itt_package .itt_hotel_2').parent(), 'itt_hotel_2');// Old slow work
                    
                    // Обновим скрытое поля по selected 'hotel'
                    var selectedHotel = jQueryMod2('#tour_search_module_mod2 section.itt_package .itt_hotel_1 option:selected').val();
                    if (selectedHotel != null || selectedHotel != 'undefined' || typeof(selectedHotel) != 'undefined' || typeof(selectedHotel) != 'null') {
                        jQueryMod2('#tour_search_module_mod2 input[name="itt_hotel_package"]').val(selectedHotel);
                    } else {
                        jQueryMod2('#tour_search_module_mod2 input[name="itt_hotel_package"]').val(0);
                    }
                });
                
                // Генерация и вставка HTML для region
                Package.getBlock('region').html(Package.generateHtml(data.region.data, region_template));
                if(Package.getItem('region').filter(':checked').length == 0){
                    Package.getItem('region').first().attr('checked', 'checked');
                }
                initCustomCheckboxes();
                initTinyScrollbar();
                
                // Update custom select (hotel) // fix jq loop error
                setTimeout(function(){updateCustomHtmlSelect(jQueryMod2('#tour_search_module_mod2 section.itt_package .itt_hotel_1').parent(), 'itt_hotel_1');}, 200);
                setTimeout(function(){updateCustomHtmlSelect(jQueryMod2('#tour_search_module_mod2 section.itt_package .itt_hotel_2').parent(), 'itt_hotel_2');}, 400);
                
                // Add region view in input
                setCountryAndRegionNameInInputPackagePart();
            });
        });
        
        //Событие для заполнения отеля, при переключении региона, рейтига отеля и типа тура
        var hotel_delegate_selector = Package.params.region.item + ', ' + Package.params.hotel_rating.item + ', ' + Package.params.tour_type.item;
        jQueryMod2('#itt_tour_search_module').delegate(hotel_delegate_selector, 'change', function(){
        //jQueryMod2(hotel_delegate_selector).on( 'change', function(){
            
            // Add region view in input
            setCountryAndRegionNameInInputPackagePart();
            
            Package.getSearchFilteredField({'event_owner_level':4}, function(data){
                //Package.setHtml('hotel', Package.generateHtml(data.hotel.data, package_hotel_template), function(item){item.trigger("chosen:updated");}); // Old version
                
                // New mega srtetch
                Package.setHtml('hotel_1', Package.generateHtml(data.hotel.data, package_hotel_template), function(){
                    // Update custom select 'hotel'
                    updateCustomHtmlSelect(jQueryMod2('#tour_search_module_mod2 section.itt_package .itt_hotel_1').parent(), 'itt_hotel_1');
                });
                Package.setHtml('hotel_2', Package.generateHtml(data.hotel.data, package_hotel_template), function(){
                    updateCustomHtmlSelect(jQueryMod2('#tour_search_module_mod2 section.itt_package .itt_hotel_2').parent(), 'itt_hotel_2');
                    
                    // Обновим скрытое поля по selected 'hotel'
                    var selectedHotel = jQueryMod2('#tour_search_module_mod2 section.itt_package .itt_hotel_1 option:selected').val();
                    if (selectedHotel != null || selectedHotel != 'undefined' || typeof(selectedHotel) != 'undefined' || typeof(selectedHotel) != 'null') {
                        jQueryMod2('#tour_search_module_mod2 input[name="itt_hotel_package"]').val(selectedHotel);
                    } else {
                        jQueryMod2('#tour_search_module_mod2 input[name="itt_hotel_package"]').val(0);
                    }
                });
            });
        });
        
        //Поиск среди стран
        //var is_popup_opened = true;
        var popup = jQueryMod2(package_path + '.itt_pop-up-place');
        /**
         * Фильтрует страны, скрывая те, которых нету в списке.
         */
        var hide_options = function(array){
            var options = popup.find('.itt_country + .scrollbarY .overview > div');
            options.hide();
            jQueryMod2.each(array, function(){
                options.filter('[name="'+this+'"]').show().prevAll('.itt_sel-optgroup:first').show();
            });
            initTinyScrollbar();
        };
        
        
        jQueryMod2('.itt_country').bind('change',function() {
            var $tt = jQueryMod2(this);
            $tt.parents('.itt_group-place').find('#itt_module_search_filter').val(jQueryMod2('.itt_country option[value='+$tt.val()+']').text());
        });
        
        
        jQueryMod2('#itt_module_search_filter')
            // Открывает попап при фокусе
            .focus(function(){
                var $tt = jQueryMod2(this);
                if($tt.attr('data-defval') == $tt.val()) {
                    $tt.val('');
                }
                if(!popup.is(':visible')){
                    //is_popup_opened = false;
                    jQueryMod2('#itt_module_search_filter_button').trigger('click');
                }
            })
            // Закрывает попап при фокусе
            .blur(function(){
                var $tt = jQueryMod2(this);
                if($tt.val() == '') {
                    $tt.val($tt.attr('data-defval'));
                }
            })
            // Фильтр по нажатию клавиши
            .keyup(function(){
                var search_text = jQueryMod2(this).val().toLowerCase();
                var match_array = [];
                var options = Package.getItem('country').find('option');
                // Формирование массива видимых элементов
                jQueryMod2.each(options, function(){
                    var option = jQueryMod2(this);

                    if(search_text.length > 0){
                        var text = option.text().toLowerCase();

                        if(text.indexOf(search_text) != -1 ){
                            match_array.push(option.val());
                        }
                    }else{
                        match_array.push(option.val());
                    }
                });
                // Вызов функции фильтрации
                hide_options(match_array);
            });
        
        //Поиск туров
        jQueryMod2('.itt_tour_search_button').click(function(){
            // Деактивиру кнопку, чтобы небыло нескольких запросов на поиск
            var button = jQueryMod2(this);
            button.prop('disabled', true);
            
            var tabs = jQueryMod2( "#itt_tabs_search_result" ).tabs();// Create tabs
            var type = Hike;
            if(jQueryMod2(this).hasClass('package')){
                type = Package;
            }
            
            // Set active tab 'Все туры' => for fix custom bug in load new search
            jQueryMod2('#itt_issuing_search_tabs_main_block li#search_result_tab_1 a.itt_issuing_search_links_head_tab').trigger('click');
            
            setHeaderInResultSearch(true);
            
            jQueryMod2('#itt_tour_search_load').show();
            jQueryMod2('#itt_tabs_search_result').hide();
            jQueryMod2('#itt_issuing_search_package_tours').hide();
            //Получение данных
            type.getSearchFormSubmit({}, function(data){
                // Активирую кнопку, (выключал ее чтобы небыло нескольких запросов на поиск)
                button.removeProp('disabled');
                
                jQueryMod2('#itt_tour_search_result').html(data.text).show();
                jQueryMod2('#itt_tabs_search_result').show();
                jQueryMod2('#itt_issuing_search_package_tours').show();
                jQueryMod2('#itt_tour_search_load').hide();
                
                setHeaderInResultSearch(false);

                var targetOffset = jQueryMod2('#itt_issuing_search_package_tours').offset().top;
                jQueryMod2('html,body').animate({scrollTop: targetOffset}, 700);
            });
        });

        
        // Методы, для скрытия загрузочного превью, после загрузки для сайта ittour
        jQueryMod2('.module_preview_load').hide();
        jQueryMod2('#tour_search_module_mod2').show();
        
        // Обработчик для выбора звезности отеля
        jQueryMod2('#itt_tour_search_module ul.star').ready(function(){
            jQueryMod2('.star input').each(function(){
                if (jQueryMod2(this).attr("checked")) {
                    jQueryMod2(this).attr("checked","checked");
                    jQueryMod2(this).next('span').addClass('on');
                };
            });
            
            jQueryMod2('.star input').change(function(){
                if (jQueryMod2(this).attr("checked")) {
                    jQueryMod2(this).attr("checked","checked");
                    jQueryMod2(this).next('span').addClass('on');
                } else {
                    jQueryMod2(this).attr("checked",false);
                    jQueryMod2(this).next('span').removeClass('on');
                }
            });
            
            jQueryMod2('.star span').live("click", function(){
                jQueryMod2(this).toggleClass('on');
                if (jQueryMod2(this).hasClass('on')) {
                    jQueryMod2(this).prev('input[type="checkbox"]').attr('checked', 'checked');
                } else {
                    jQueryMod2(this).prev('input[type="checkbox"]').removeAttr('checked');
                }
                
                // Set new rating
                refresh_hotel_rating_total();
            });
            
            /**
             * function change value in selecter 'input[name="itt_hotel_rating_total"]'
             * 
             * @param {void} 
             * @returns {void}
             */
            function refresh_hotel_rating_total(){
                var hotel_rating_list  = '';
                jQueryMod2('#itt_hotel_rating_block input:checked').each(function(index){
                    if (hotel_rating_list == '') {
                        hotel_rating_list  += jQueryMod2(this).val();
                    } else {
                        hotel_rating_list  += ' ' + jQueryMod2(this).val();
                    }
                });
                if (hotel_rating_list == '') {
                    jQueryMod2('input[name="itt_hotel_rating_total"]').val('3 4 78');// Default rating *3,*4,*5
                } else {
                    jQueryMod2('input[name="itt_hotel_rating_total"]').val(hotel_rating_list);
                }
            }
        });
        
        // Обработчик для выбора валюты (500x170, 300x500, 200x450, 200x775)
        // Package
        jQueryMod2('#itt_tour_search_module_package_block #itt_currency_radio_total').ready(function(){
            jQueryMod2('#itt_tour_search_module_package_block input[type="radio"].itt_currency_radio').change(function(){
                // Get new currency
                var current_currency = jQueryMod2(this).val();
                // Check value
                if (typeof current_currency == 'undefined') current_currency = 'UAH';
                // Set new currency in hidden input
                jQueryMod2('#itt_tour_search_module_package_block #itt_currency_radio_total').val(current_currency);
            });
        });
        
        // Hike
        jQueryMod2('#itt_tour_search_module_hike_block #itt_currency_radio_total').ready(function(){
            jQueryMod2('#itt_tour_search_module_hike_block input[type="radio"].itt_currency_radio').change(function(){
                // Get new currency
                var current_currency = jQueryMod2(this).val();
                // Check value
                if (typeof current_currency == 'undefined') current_currency = 'UAH';
                // Set new currency in hidden input
                jQueryMod2('#itt_tour_search_module_hike_block #itt_currency_radio_total').val(current_currency);
            });
        });
        
        // Обработчик для нового мульти выбора питания
        jQueryMod2('#itt_tour_search_module ul.star').ready(function(){
            jQueryMod2('.food input').each(function(){
                if (jQueryMod2(this).attr("checked")) {
                    jQueryMod2(this).attr("checked","checked");
                    jQueryMod2(this).next('span').addClass('on');
                };
            });
            
            jQueryMod2('.food span').live("click", function(){
                jQueryMod2(this).toggleClass('on');
                if (jQueryMod2(this).hasClass('on')) {
                    jQueryMod2(this).prev('input[type="checkbox"]').attr('checked', 'checked');
                } else {
                    jQueryMod2(this).prev('input[type="checkbox"]').removeAttr('checked');
                }
                refresh_food_total();
            });
            
            jQueryMod2('.food input').change(function(){
                if (jQueryMod2(this).attr("checked")) {
                    jQueryMod2(this).attr("checked","checked");
                    jQueryMod2(this).next('span').addClass('on');
                } else {
                    jQueryMod2(this).attr("checked",false);
                    jQueryMod2(this).next('span').removeClass('on');
                }
                refresh_food_total();
            });
            
            /**
             * function change value in selecter 'input[name="itt_food_total"]'
             * 
             * @param {void} 
             * @returns {void}
             */
            function refresh_food_total() {
                var food_list  = '';
                jQueryMod2('#itt_food_block input:checked').each(function(index){
                    if (food_list == '') {
                        food_list  += jQueryMod2(this).val();
                    } else {
                        food_list  += ' ' + jQueryMod2(this).val();
                    }
                });
                if (food_list == '') {
                    jQueryMod2('input[name="itt_food_total"]').val('496 512 560');// Default: HB, AI, UAI
                } else {
                    jQueryMod2('input[name="itt_food_total"]').val(food_list);
                }
            }
        });
        
        // Обработчик для 'Проезд включен: да | нет'
        jQueryMod2('input[name="itt_tour_type"]').change(function(){
            jQueryMod2('#tour_search_module_mod2 #itt_tour_type').val(jQueryMod2(this).val());
        });
        
    })
    
    //Событие загрузки js файла
    .setEvent('load_js', function(name){
    })
    
    // Начинаем загрузку. В добрый путь ^_^ !!!
    .load();