ModuleSearch = {
    
    options:{},
    data:{},
    params :{},
    
    setOptions: function(options){
        this.options = options;
    },
    
    addOption: function(option, value){
        this.options[option] = value;
    },
    
    getOption: function(option, default_res){
        if(typeof this.options[option] !== 'undefined'){
            return this.options[option];
        }
        return default_res ? default_res : '';//null
    },
    
    getOptions: function(){
        return this.options;
    },

    startLoadingEvent: function(){
        var $module = jQueryMod2('#tour_search_module_mod2');
        $module.css('opacity', 0.5);
    },
    
    endLoadingEvent: function(){
        var $module = jQueryMod2('#tour_search_module_mod2');
        $module.css('opacity', 1);
    },
    
    startSearchEvent: function(){
        
    },
    
    endSearchEvent: function(){
        
    },
    
    setParams: function(params){
        var _this = this;
        _this.params = params;
       
        jQueryMod2.each(params, function(name, param){
            jQueryMod2('#itt_tour_search_module').delegate(param.item, 'change', function(){
                _this.refreshValue(name);
            });
            _this.refreshValue(name);
        });
    },
    
    refreshValue: function(name){
        if(!isset(this.params[name])) return false;
        
        var row = this.params[name];
        var def = row.default || ''; ////null
        
        if(typeof this.params[name].val == 'function'){
            this.data[name] = row.val(this.getItem(name), def);
        }else{
            this.data[name] = jQueryMod2(row.item).val() || def;
        }
    },
    
    getData: function(name){
        if(isset(name)){
            this.refreshValue(name);
            return this.data[name] || '';//null
        }else{
            return this.data;
        }
    },
    
    getItem: function(name){
        if(isset(this.params[name])){
            return jQueryMod2(this.params[name].item);
        }
    },
    
    getBlock: function(name){
        if(isset(this.params[name])){
            if(isset(this.params[name].block)){
                return jQueryMod2(this.params[name].block);
            }else{
                this.getItem(name);
            }
        }
    },
    
    getSearchFilteredField: function(params, callback){
        var data = this.getSearchFilteredFieldData(params);
        this.startLoadingEvent();
        jQueryMod2.getJSON(ModuleSearch.getOption('modules_action'), data, function(request){
            ModuleSearch.endLoadingEvent();
            if(typeof callback == 'function') callback(request);
        });
    },
    
    getSearchFormSubmit: function(params, callback){
        var data = this.getSearchFormSubmitData(params);
        var _this = this;
        
        _this.startSearchEvent();
        jQueryMod2.getJSON(_this.getOption('modules_action'), data, function(request){
            _this.endSearchEvent();
            if(typeof callback == 'function') callback(request);
        });
    },
    
    setHtml: function(name, html, callback){
        var item = this.getItem(name);
        if(item){
            item.html(html);
            if(typeof callback == 'function'){
                callback(item, html);
                this.refreshValue(name);
            }
        }
    },
    
    generateHtml: function(obj, template){
        var html = '';
        if(obj && obj.length > 0){
            jQueryMod2.each(obj, function(){
                var array = this; 
                html += template.replace(/{{(\w+)}}/g, function($0, $1){
                    var name = $1;
                    return typeof array[name] != 'undefined' ? array[name] : '';
                });
            });

            html = html.replace(/{%(.+?)%}/g, function($0, $1){
                var action = $1; 
                return eval(action);
            });
        }
        
        return html;
    },
    
    calculateDateTill: function(date_from, period, is_short){
       // Set date_from
       var y = date_from.selectedYear;
       var m = date_from.selectedMonth;// from 0
       var d = date_from.selectedDay;
       
       // Check year => fix bug in def_date == now()
       if (y == '0') {
         var y2k = new Date();
       } else {
         var y2k = new Date(y, m, d);
       }
       
       // Get date + perion
       y2k.setDate(y2k.getDate() + parseInt(period));
       
       var new_d = y2k.getDate();
       var new_m = y2k.getMonth() + 1;// месяца идут с '0' поэтому '+1'
       var new_y = String(y2k.getFullYear());// 2014
       var y_array = new_y.split('');// 14
       var new_y_short = y_array[2] + y_array[3];
       
       var date_return = '';
       date_return = new_d + '.' + new_m + '.' + new_y_short;
       
       return date_return; // Example: 29.11.14
    },
};
window.ModuleSearch = ModuleSearch;


Hike = {
    data: {},
    params: {},
    search_filtered_field: 'get_hike_search_filtered_field',
    search_form_submit: 'hike_tour_search',
    
    getSearchFilteredFieldData: function(params){
        
        var arr_to_return =  {
            'action'    : params['action']  || this.search_filtered_field, 
            'city_id'   : params['city_id'] || this.getData('departure_city'),
            'country_id'    : params['country_id']    || this.getData('country'),
            'tour_city_id'  : params['tour_city_id']  || this.getData('tour_city').join(' '),
            'transport_id'  : params['transport_id']  || this.getData('transport'),
            'object_result' : params['object_result'] || 'data',
            'event_owner_level' : params['event_owner_level']   || 1,
        };
        return arr_to_return;
    },
    
    setDateToCustomDate: function(params){
        var text_date = params;// Example: '28.08.2014'
        var obj_date = new Date(text_date.replace(/(\d+).(\d+).(\d+)/, '$2/$1/$3'));// Example: 08/28/2014
        return jQueryMod2.datepicker.formatDate('dd.mm.y', obj_date);// Example: '28.08.14'
    },
    
    getSearchFormSubmitData: function(params){
        var data = {
            'action'    : params['action'] || this.search_form_submit,
            'city'      : params['departure_city'] || this.getData('departure_city'),
            'country'   : params['country'] || this.getData('country'),
            'hike_date_from'  :	params['date_from'] || this.calculateDateTill(this.getItem('date_from').data('datepicker'), 0, true),// New 21.08.2014
            'hike_date_till'  :	params['date_till'] || this.calculateDateTill(this.getItem('date_from').data('datepicker'), this.getData('date_period'), true),// New 21.08.2014
            'hike_price_from' :	params['price_from'] || this.getData('price_from'),
            'hike_price_till' :	params['price_till'] || this.getData('price_till'),
            'currency' : params['currency']    || this.getData('currency'),
            'items_per_page' :  params['items_per_page'] || this.getData('tour_count_per_page'),
            'transport'  : params['transport']  || this.getData('transport'),
            'tour_city'  : params['tour_city_id']  || this.getData('tour_city').join(' '),
            'hike_tour_type' :	params['tour_type'] || this.getData('tour_type'),
            
            'module_location_url' :	window.location.href,
        };
        //fix
        data['city'] = data['city'] == 0 ? '' : data['city'];
        data['transport'] = data['transport'] == 0 ? '' : data['transport'];
        data['tour_city'] = data['tour_city'] == 0 ? '' : data['tour_city'];
        return data;
    },
    
};
Hike.__proto__ = ModuleSearch;


Package = {
    data: {},
    params: {},
    search_filtered_field: 'get_package_search_filtered_field',
    search_form_submit: 'package_tour_search',
    
    getSearchFilteredFieldData: function(params){
        var arr_to_return =  {
            'action'     : params['action']  || this.search_filtered_field, 
            'country' : params['country'] || this.getData('country'),
            'country_id' : params['country'] || this.getData('country'),
            'event_owner_level' : params['event_owner_level'] || 1,
            'hotel_rating_id'   : params['hotel_rating_id'] || this.getData('hotel_rating'),
            'region_id' : params['region_id'] || this.getData('region').join(' '),
            'tour_kind' : params['tour_kind'] || 0, //all type
            'tour_type' : params['tour_type'] || this.getData('tour_type'), 
            'object_result' : params['object_result'] || 'data',
        };
        return arr_to_return;
    },
    
    setDateToCustomDate: function(params){
        var text_date = params;// Example: '28.08.2014'
        var obj_date = new Date(text_date.replace(/(\d+).(\d+).(\d+)/, '$2/$1/$3'));// Example: 08/28/2014
        return jQueryMod2.datepicker.formatDate('dd.mm.y', obj_date);// Example: '28.08.14'
    },
    
    getSearchFormSubmitData: function(params){
        var arr_to_return = {
            'action'    : params['action'] || this.search_form_submit,
            'adults'    : params['adults'] || this.getData('adults'),
            'children'  : params['children']  || this.getData('children'),
            'child1_age'  : params['child1_age']  || this.getData('child1_age'),
            'child2_age'  : params['child2_age']  || this.getData('child2_age'),
            'child3_age'  : params['child3_age']  || this.getData('child3_age'),
            'country': params['country']|| this.getData('country'),
            'country_id' : params['country'] || this.getData('country'),
            'date_from' : params['date_from'] || this.getData('date_from') ? this.calculateDateTill(this.getItem('date_from').data('datepicker'), 0, true) : '',// New 21.11.2014
            'date_till' : params['date_till'] || this.getData('date_from') ? this.calculateDateTill(this.getItem('date_from').data('datepicker'), this.getData('date_period'), true) : '',// New 21.11.2014
            'departure_city': params['departure_city'] || this.getData('departure_city'),
            'food'  : params['food']  || this.getData('food'),            
            'hotel' : (params['hotel'] || this.getData('hotel').replace(/^[0]{1}/, '')),
            'hotel_rating' : params['hotel_rating'] || this.getData('hotel_rating'),
            'night_from' : params['night_from'] || this.getData('night_from'),
            'night_till' : params['night_till'] || this.getData('night_till'),
            'price_from' : params['price_from'] || this.getData('price_from'),
            'price_till' : params['price_till'] || this.getData('price_till'),
            'switch_price': params['currency'] || this.getData('currency'),
            'tour_kind' : params['tour_kind'] || 0, //all type
            'tour_type' : params['tour_type'] || this.getData('tour_type'),
            'region' : (params['region_id'] || this.getData('region').join(' ').replace(/^[0]{1}/, '').replace(/^[\s]{1}/, '')),// New
            'items_per_page' :  params['items_per_page'] || this.getData('tour_count_per_page'), 
            'module_location_url' : window.location.href,
            'preview' : 1,
            'package_tour_type' : params['tour_type'] || this.getData('tour_type'),
        };
        
        // Fix bun in date search
        arr_to_return.date_from = checkDateBeforeSend(arr_to_return.date_from);
        arr_to_return.date_till = checkDateBeforeSend(arr_to_return.date_till);
        
        return arr_to_return;
    },
    
};
Package.__proto__ = ModuleSearch;



/*
 * Popups plugin
 */
(function() {
	function Popups(opt) {
		this.options = jQueryMod2.extend({
			holder: null,
			popup: '.popup',
			btnOpen: '.open',
			btnClose: '.close',
			openClass: 'popup-active',
			clickEvent: 'click',
			mode: 'click',
			hideOnClickLink: true,
			hideOnClickOutside: true,
			delay: 50
		}, opt);
		if(this.options.holder) {
			this.holder = jQueryMod2(this.options.holder);
			this.init();
		}
	}
	Popups.prototype = {
		init: function() {
			this.findElements();
			this.attachEvents();
		},
		findElements: function() {
			this.popup = this.holder.find(this.options.popup);
			this.btnOpen = this.holder.find(this.options.btnOpen);
			this.btnClose = this.holder.find(this.options.btnClose);
			if(this.holder.hasClass(this.options.openClass)) this.popup.show();
			else this.popup.hide();
			if(jQueryMod2.isFunction(this.options.onInit)) this.options.onInit(this);
		},
		attachEvents: function() {
			// handle popup openers
			var self = this;
			this.clickMode = isTouchDevice || (self.options.mode === self.options.clickEvent);

			if(this.clickMode) {
				// handle click mode
				this.btnOpen.bind(self.options.clickEvent, function(e) {
					if(self.holder.hasClass(self.options.openClass)) {
						if(self.options.hideOnClickLink) {
							self.hidePopup();
						}
					} else {
						self.showPopup();
					}
					e.preventDefault();
				});

				// prepare outside click handler
				this.outsideClickHandler = this.bind(this.outsideClickHandler, this);
			} else {
				// handle hover mode
				var timer, delayedFunc = function(func) {
					clearTimeout(timer);
					timer = setTimeout(function() {
						func.call(self);
					}, self.options.delay);
				};
				this.btnOpen.bind('mouseover', function() {
					delayedFunc(self.showPopup);
				}).bind('mouseout', function() {
					delayedFunc(self.hidePopup);
				});
				this.popup.bind('mouseover', function() {
					delayedFunc(self.showPopup);
				}).bind('mouseout', function() {
					delayedFunc(self.hidePopup);
				});
			}

			// handle close buttons
			this.btnClose.bind(self.options.clickEvent, function(e) {
				self.hidePopup();
				e.preventDefault();
			});
		},
		outsideClickHandler: function(e) {
			// hide popup if clicked outside
			var currentNode = (e.changedTouches ? e.changedTouches[0] : e).target;
			if(!jQueryMod2(currentNode).parents().filter(this.holder).length) {
				this.hidePopup();
			}
		},
		showPopup: function() {
			// reveal popup
			this.holder.addClass(this.options.openClass);
			this.popup.css({display:'block'});

			// outside click handler
			if(this.clickMode && this.options.hideOnClickOutside && !this.outsideHandlerActive) {
				this.outsideHandlerActive = true;
				jQueryMod2(document).bind('click touchstart', this.outsideClickHandler);
			}
			if(jQueryMod2.isFunction(this.options.onShow)) this.options.onShow(this);
                        
                        initTinyScrollbar();
		},
		hidePopup: function() {
			// hide popup
			this.holder.removeClass(this.options.openClass);
			this.popup.css({display:'none'});

			// outside click handler
			if(this.clickMode && this.options.hideOnClickOutside && this.outsideHandlerActive) {
				this.outsideHandlerActive = false;
				jQueryMod2(document).unbind('click touchstart', this.outsideClickHandler);
			}
			if(jQueryMod2.isFunction(this.options.onHide)) this.options.onHide(this);
		},
		bind: function(f, scope, forceArgs){
			return function() {return f.apply(scope, forceArgs ? [forceArgs] : arguments);};
		}
	};

	// detect touch devices
	var isTouchDevice = /MSIE 10.*Touch/.test(navigator.userAgent) || ('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch;

	// jQueryMod2 plugin interface
	jQueryMod2.fn.popups = function(opt) {
		return this.each(function() {
			new Popups(jQueryMod2.extend(opt, {holder: this}));
		});
	};
}(jQueryMod2));



// page init
jQueryMod2(function(){
    initCustomCheckboxes();
    initCustomMultipleSelect();    
    initDatepicker();
    initPopups();
    initTabsStretch();// Init tabs 'package', 'hike'
    initCurrencyListAmount();// Init currency amount list
    initOpenClose();
    initSwitchButtons();
    initTinyScrollbar();
    initCustomPreloaderIttour('canvasloader_itt_tour_search_load', '#2473b8', 37, 1.7, 23, true);// Init custom preloader for all project
    initCssForCurrentModuleSize();// Init custom css for current mod size
    initShowDefaultCountryAndRegionPackagePart();
});


/**
 * function show Default Country And Region in input 'Страна, регион'
 * @returns {undefined}
 */
function initShowDefaultCountryAndRegionPackagePart(){
    var countryText = '', regionText = '', fullTextForView = '';
    
    // Get data
    countryText = jQueryMod2('#tour_search_module_mod2 .itt_country-region_popup select.itt_country option:selected').text();
    regionText = jQueryMod2('#tour_search_module_mod2 .itt_country-region_popup .itt_region .itt_row_in_list_region:first label span').text();
    
    // Set value for view
    if (countryText != '' && countryText != null) {
        fullTextForView = countryText;
    }
    if (regionText != '' && regionText != null) {
        if (fullTextForView == '') {
            fullTextForView = regionText;
        } else {
            fullTextForView = fullTextForView + ', '+ regionText;
        }
    }
    // Set dafault value
    jQueryMod2('#tour_search_module_mod2 #itt_module_search_filter').val(fullTextForView);
}

/**
 * function init tabs for new stretch module search
 * @version 1.0.0.1 
 * @date 10-decembet-2014
 * @returns {undefined}
 */
function initTabsStretch() {
    jQueryMod2('#tour_search_module_mod2 .itt_search_module_tab').children('li.itt_package_tab_title').first().children('a').addClass('is-active').next().addClass('is-open').show();
    jQueryMod2('#tour_search_module_mod2 .itt_search_module_tab').on('click', 'li > a.itt_search_module_tab_title', function(event) {
        $this = jQueryMod2(this);
        if (!$this.hasClass('is-active')) {
            event.preventDefault();
            jQueryMod2('#tour_search_module_mod2 .itt_search_module_tab .is-open').removeClass('is-open').hide();
            $this.next().toggleClass('is-open').toggle();
            jQueryMod2('#tour_search_module_mod2 .itt_search_module_tab').find('.is-active').removeClass('is-active');
            $this.addClass('is-active');
        } else {
            event.preventDefault();
        }
        
        // Init scroll and checkbox
        ;initTinyScrollbar();
        ;initCustomCheckboxes();
    });
}

function initCustomSelectStretch() {
    // Init custom select stretch
    createCustomHtmlSelect('itt_mod_search_custom_select', getCssClassFromConfigAndUseForCustomSelect());
}

function initCurrencyListAmount() {
    // Set default list amount 'from' and 'to'
    
    // Get default currency
    var currency = jQueryMod2('#tour_search_module_mod2 .itt_currency_selector option:selected').val();
    
    // Check and set default currency == 2 => 'Грн.'
    if (typeof currency == 'undefined') currency = 'EUR';
    
    // Set new lists ('package' / 'hike')
    setDefaultListAmountFromAndTo(currency, 'package');
    setDefaultListAmountFromAndTo(currency, 'hike');
    
    // Init custom select stretch
    setTimeout(function(){initCustomSelectStretch();}, 300);
}

/**
 * Генерация кастомного прелоадера, юзает либу, имеет различные настройки
 * 
 * @param {string} color, Example: '#2473b8'
 * @param {integer} diameter, Example: 37
 * @param {numeric} range, Example: 1.7
 * @param {numeric} fps, Example: 23
 * @param {boolean} show, Example: true
 * @returns {undefined}
 */
function initCustomPreloaderIttour(html_id_name, color, diameter, range, fps, show) {
    var cl = new CanvasLoader(html_id_name);
    cl.setColor(color); // default is '#000000' // '#2473b8'
    cl.setDiameter(diameter); // default is 40 // 37
    cl.setRange(range); // default is 1.3 // 1.7
    cl.setFPS(fps); // default is 24 // 23
    if (show) cl.show();
    
    // This bit is only for positioning - not necessary
    var loaderObj = document.getElementById("canvasLoader");
    loaderObj.style.position = "relative";
    loaderObj.style["top"] = cl.getDiameter() * -0.5 + "px";
    loaderObj.style["left"] = cl.getDiameter() * -0.5 + "px";
}

function initTinyScrollbar(){
    jQueryMod2.each(jQueryMod2('#tour_search_module_mod2 .scrollbarY'), function(){
        jQueryMod2(this).tinyscrollbar({sizethumb:22, scroll: true, wheel: 40});
    });
}

function initSwitchButtons(){
	var activeClass = 'itt_active';
	jQueryMod2('.itt_search-module_new .itt_group-btns').each(function(){
		var hold = jQueryMod2(this),
			list = hold.find('ul'),
			btns = list.find('a');
		if(list.hasClass('itt_transport')){
			btns.each(function(){
				var btn = jQueryMod2(this),
					field = btn.parent().find('input:hidden');
				if(btn.parent().hasClass(activeClass)){
					field.val(btn.data('value'));
				}
				btn.bind('click', function(e){
					if(!btn.parent().hasClass(activeClass)){
						field.val(btn.data('value'));
						btn.parent().addClass(activeClass);
					} else{
						field.val('');
						btn.parent().removeClass(activeClass);
					}
	
					e.preventDefault();
				});
			});
		} else{
			var field = hold.find('input:hidden');
			btns.each(function(){
				var btn = jQueryMod2(this);
				if(btn.parent().hasClass(activeClass)){
					field.val(btn.data('value'));
				}
				btn.bind('click', function(e){
					if(!btn.parent().hasClass(activeClass)){
						field.val(btn.data('value'));
						btn.parent().siblings().removeClass(activeClass);
						btn.parent().addClass(activeClass);
					}
	
					e.preventDefault();
				});
			});
		}
	});
}

function initOpenClose(){
	jQueryMod2('.itt_search-module_new .itt_link-hide, .itt_search-module_new .itt_link-hide2').each(function(){
		var link = jQueryMod2(this),
			block = jQueryMod2(this).parents('form').find(link.attr('href'));
		
		link.live('click', function(){
			if(link.hasClass('opened')){
				block.addClass('itt_hidden');
				link.removeClass('opened');
			} else{
				block.removeClass('itt_hidden');
				link.addClass('opened');
			}
			
			return false;
		});
	});
}

function initCustomMultipleSelect(){
	jQueryMod2('.itt_search-module_new .itt_multiple-sel').each(function(){
		var holder = jQueryMod2(this),
                        sel = holder.find('select'),
                        overview = holder.find('.overview');
		sel.hide();
		sel.children().map(function(){
			if(this.tagName.toLowerCase() === 'optgroup'){
				overview.append('<div class="itt_sel-optgroup"><span>'+this.label+'</span></div>');
				jQueryMod2(this).children().each(function() {
					overview.append('<div class="itt_sel-opt" name="'+this.value+'"><span>'+this.innerHTML+'</span></div>');
				});
			} else if(this.tagName.toLowerCase() === 'option'){
				overview.append('<div class="itt_sel-opt" name="'+this.value+'"><span>'+this.innerHTML+'</span></div>');
			}
		});
		
		overview.find('.itt_sel-opt').click(function(){
				var e=jQueryMod2(this);
				if(e.hasClass('selected')){
					//e.removeClass('selected');
					//sel.find('option[value='+jQueryMod2(this).attr('name')+']').removeAttr('selected');
				} else {
					e.siblings().removeClass('selected');
					sel.find('option').removeAttr('selected');
					e.addClass('selected');
					sel.val(jQueryMod2(this).attr('name')).trigger('change');
				}
		});
	});
}

function initPopups(){
	jQueryMod2('.itt_search-module_new .itt_group-place').popups({
		popup: '.itt_pop-up-place',
		btnOpen: '.itt_link, .itt_link-arrow',
		btnClose: '.itt_close',
		openClass: 'itt_popup-active',
		hideOnClickLink: true,
		hideOnClickOutside: true
	});
}

function initColorPicker(){
	jQueryMod2('.itt_search-module_new #colorpickerHolder').ColorPicker({
		flat: true,
		onChange: function (hsb, hex, rgb) {
			jQueryMod2('#custom-color').text('.itt_bg-color, .chosen-container > a div, .itt_multiple-sel .scrollbarY .selected, .itt_select-head .chosen-container > a div, .itt_select-head .chosen-with-drop a, .itt_select-head .chosen-drop ul, .chosen-container .chosen-drop .highlighted, .itt_advanced-search a > em, .itt_col-right .itt_active img, .ui-datepicker .ui-datepicker-today a{background-color: #' + hex + ';}.itt_border-color, .itt_pop-up-children{border-color: #' + hex + ';}.itt_text-color, .itt_select-head .chosen-container .chosen-drop .highlighted, .itt_col-right .itt_active a{color: #' + hex + ';}')
		}
	});
}

function initCustomCheckboxes(){
	jQueryMod2(".itt_search-module_new input:checkbox").button();
}

function initDatepicker(){
    // Handler for package calendar / hike calendar
    jQueryMod2("#datepicker_package").datepicker();
    jQueryMod2("#datepicker_hike").datepicker();
}

/**
 * load custom file
 * @param {string} jsFilePath
 * @returns {undefined}
 */
function includeJsCustom(jsFilePath) {
  var js = document.createElement("script");
  js.type = "text/javascript";
  js.src = jsFilePath;
  document.body.appendChild(js);
}

// Check charset
var charsetInPage = document.characterSet;
charsetInPage = charsetInPage.toLowerCase();
if (charsetInPage == 'windows-1251') {
  // windows-1251
  includeJsCustom("http://www.ittour.com.ua/classes/handlers/ittour_external_modules/ittour_modules/new/js/datepicker_custom/datepicker_windows_1251.js");
} else {
  // utf-8
  includeJsCustom("http://www.ittour.com.ua/classes/handlers/ittour_external_modules/ittour_modules/new/js/datepicker_custom/datepicker_utf_8.js");
}


/*** TAB Package ***/

// Handler for click to icon calendar
jQueryMod2('#tour_search_module_mod2 .itt_package .itt_search_module_calendar_icons').live('click', function(){
    jQueryMod2('#tour_search_module_mod2 .itt_package .itt_date_from').focus();
});

// Handler for select count child // part #1
jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position .itt_child_count_selector').live('change', function(){
    // Get count child
    var count_child = jQueryMod2(this).val();
    
    if (count_child == 0) {
        // Hide all children
        jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position .itt_row_child_item_block').hide();
        
        // Set default age '0'
        jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position .itt_row_child_item_block .itt_children_select_bg .itt_child_age li').removeClass('itt_child_age_active');
        jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position .itt_row_child_item_block_num_1 .itt_child_age li:first').addClass('itt_child_age_active');
        jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position .itt_row_child_item_block_num_2 .itt_child_age li:first').addClass('itt_child_age_active');
        jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position .itt_row_child_item_block_num_3 .itt_child_age li:first').addClass('itt_child_age_active');
    } else {
        // Hide all children
        jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position .itt_row_child_item_block').hide();
        
        // Show items child
        for (var i=1; i<=count_child; i++) {
            jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position .itt_row_child_item_block_num_' + i).show();
        }
    }
});

// Handler for select count child // part #2
jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position_two .itt_child_count_selector').live('change', function(){
    // Get count child
    var count_child = jQueryMod2(this).val();
    
    if (count_child == 0) {
        // Hide all children
        jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position_two .itt_row_child_item_block').hide();
        
        // Set default age '0'
        jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position_two .itt_row_child_item_block .itt_children_select_bg .itt_child_age li').removeClass('itt_child_age_active');
        jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position_two .itt_row_child_item_block_num_1 .itt_child_age li:first').addClass('itt_child_age_active');
        jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position_two .itt_row_child_item_block_num_2 .itt_child_age li:first').addClass('itt_child_age_active');
        jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position_two .itt_row_child_item_block_num_3 .itt_child_age li:first').addClass('itt_child_age_active');
    } else {
        // Hide all children
        jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position_two .itt_row_child_item_block').hide();
        
        // Show items child
        for (var i=1; i<=count_child; i++) {
            jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position_two .itt_row_child_item_block_num_' + i).show();
        }
    }
});

/**
 * Custom function for set new active age for item child
 * 
 * @param {string} className Name for main wrapper
 * @param {numner} num, Nuber child
 * @param {string} next_prev, Use for +/- age
 * @returns {undefined}
 */
function setNewAgeForChild(num, next_prev, className) {
    // Check variable
    if (typeof num == 'undefined') return;
    if (typeof next_prev == 'undefined') return;
    if (typeof className == 'undefined') return;
    
    // Set current element with age
    var current_active_age = jQueryMod2('#tour_search_module_mod2 .' + className + ' .itt_row_child_item_block_num_' + num + ' .itt_child_age .itt_child_age_active');
    
    // Next
    if (next_prev == 'next') {
        // Check isset next 'li' with age
        if (current_active_age.next().html() != null) {
            // Move active class 'UP'
            current_active_age.removeClass('itt_child_age_active').next().addClass('itt_child_age_active');
            
            // Set hidden input
            var newValue = current_active_age.next().text();
            if (typeof newValue != 'undefined') jQueryMod2('input[name="itt_child' + num + '_age_package"]').val(newValue);
        }
    }
    
    // Prev
    if (next_prev == 'prev') {
        // Check isset next 'li' with age
        if (current_active_age.prev().html() != null) {
            // Move active class 'UP'
            current_active_age.removeClass('itt_child_age_active').prev().addClass('itt_child_age_active');
            
            // Set hidden input
            var newValue = current_active_age.prev().text();
            if (typeof newValue != 'undefined') jQueryMod2('input[name="itt_child' + num + '_age_package"]').val(newValue);
        }
    }
}

// Handler for item child age "-"
jQueryMod2('#tour_search_module_mod2 .itt_cheld_age_switch_down').live('click', function(){
    // Get block age
    var block_child_age = jQueryMod2(this).parent().parent().parent().parent();
    
    // Wrapper block
    var block_child_age_wrap =  jQueryMod2(this).parent().parent().parent().parent().parent();
    var className = 'itt_children_block_media_position';// Default value
    if (block_child_age_wrap.hasClass('itt_children_block_media_position_two')) className = 'itt_children_block_media_position_two';
    
    // Change age for '1' child
    if (block_child_age.hasClass('itt_row_child_item_block_num_1')) {
       setNewAgeForChild(1, 'prev', className);
    }
    
    // Change age for '2' child
    if (block_child_age.hasClass('itt_row_child_item_block_num_2')) {
       setNewAgeForChild(2, 'prev', className);
    }
    
    // Change age for '3' child
    if (block_child_age.hasClass('itt_row_child_item_block_num_3')) {
        setNewAgeForChild(3, 'prev', className);
    }
});

// Handler for item child age "+"
jQueryMod2('#tour_search_module_mod2 .itt_cheld_age_switch_up').live('click', function(){
    // Get block age
    var block_child_age = jQueryMod2(this).parent().parent().parent().parent();
    
    // Wrapper block
    var block_child_age_wrap =  jQueryMod2(this).parent().parent().parent().parent().parent();
    var className = 'itt_children_block_media_position';// Default value
    if (block_child_age_wrap.hasClass('itt_children_block_media_position_two')) className = 'itt_children_block_media_position_two';
    
    // Change age for '1' child
    if (block_child_age.hasClass('itt_row_child_item_block_num_1')) {
       setNewAgeForChild(1, 'next', className);
    }
    
    // Change age for '2' child
    if (block_child_age.hasClass('itt_row_child_item_block_num_2')) {
       setNewAgeForChild(2, 'next', className);
    }
    
    // Change age for '3' child
    if (block_child_age.hasClass('itt_row_child_item_block_num_3')) {
        setNewAgeForChild(3, 'next', className);
    }
});

// Handlers for editable child age
jQueryMod2('#tour_search_module_mod2 .itt_child_age.itt_pull-left').live('click', function(){
    // Copy value from li to input
    var child_age_input = $(this).prev();
    child_age_input.val($(this).find('li.itt_child_age_active').html());
    child_age_input.show();
    child_age_input.focus();
});
jQueryMod2('#tour_search_module_mod2 .itt_child_age_value').live('blur', function(){
    // Copy value from input to li
    var child_age = Math.abs(parseInt($(this).val(), 10) || 0);
    var child_age_list = $(this).next();
    child_age_list.find('li.itt_child_age_active').removeClass('itt_child_age_active');
    child_age_list.find('li').filter(function() {
        return $(this).text() === String(child_age);
    }).addClass('itt_child_age_active');
    $(this).hide();
    for(var i = 1; i <= 3; i++) {
      if($(this).parent().parent().hasClass('itt_row_child_item_block_num_' + i)) {
        num = i;
      }
    }
    jQueryMod2('input[name="itt_child' + num + '_age_package"]').val(child_age);
});
jQueryMod2('#tour_search_module_mod2 .itt_child_age_value').live('keyup', function(e){
    if(e.keyCode > 36 && e.keyCode < 41) return false;
    // Copy value from input to li
    if($(this).val() == '') return false;
    var child_age = Math.abs(parseInt($(this).val(), 10) || 0);
    child_age = (child_age > 17) ? 17 : child_age;
    $(this).val(child_age);
    
    var child_age_list = $(this).next();
    child_age_list.find('li.itt_child_age_active').removeClass('itt_child_age_active');
    child_age_list.find('li').filter(function() {
        return $(this).text() === String(child_age);
    }).addClass('itt_child_age_active');
    for(var i = 1; i <= 3; i++) {
      if($(this).parent().parent().hasClass('itt_row_child_item_block_num_' + i)) {
        num = i;
      }
    }
    jQueryMod2('input[name="itt_child' + num + '_age_package"]').val(child_age);
});
jQueryMod2('#tour_search_module_mod2 .itt_child_age_value').live('keydown', function(e){
    if (e.keyCode == 9) {// Tab key
      e.preventDefault();
    }
});

/**
 * function change value in selecter 'input[name="itt_hotel_rating_total"]'
 * 
 * @param {void} 
 * @returns {void}
 */
function refreshHotelRatingTotalNewVersion(){
    var hotel_rating_list  = '';
    jQueryMod2('#tour_search_module_mod2 .itt_search_module_list_star_inline li a span').each(function(index){
        if (jQueryMod2(this).hasClass('itt_search_module_hotel_rating_active')) {
            if (hotel_rating_list == '') {
                hotel_rating_list  += jQueryMod2(this).attr('rel');
            } else {
                hotel_rating_list  += ' ' + jQueryMod2(this).attr('rel');
            }
        }
    });
    
    // Set value
    if (hotel_rating_list == '') {
        // Set data in hidden input
        jQueryMod2('#tour_search_module_mod2 input[name="itt_hotel_rating_total"]').val('3 4 78');// Default rating 3*,4*,5*
        
        // Refresh horel rating in view
        jQueryMod2('#tour_search_module_mod2 .itt_mod_search_horel_ratin_set').html('<span>3*</span>,<span>4*</span>,<span>5*</span>');// Default rating view: '3*,4*,5*'
        
        // Set def active 3*,4*,5* in stars
        jQueryMod2('#tour_search_module_mod2 .itt_search_module_list_star_inline li a span').each(function(){
            jQueryMod2(this).removeAttr('class').addClass('itt_search_module_hotel_rating').addClass('itt_search_module_hotel_rating_active');
        });
        
        // Set 2* not active
        jQueryMod2('#tour_search_module_mod2 .itt_search_module_list_star_inline li:first a span').removeAttr('class').addClass('itt_search_module_hotel_rating');
    } else {
        // Set data in hidden input
        jQueryMod2('#tour_search_module_mod2 input[name="itt_hotel_rating_total"]').val(hotel_rating_list);
        
        // Refresh horel rating in view
        var hotel_rating_list_view = hotel_rating_list.replace('78', '<span>5');// Set '5'
        hotel_rating_list_view = hotel_rating_list_view.replace('7', '<span>2');// Set '2'
        hotel_rating_list_view = hotel_rating_list_view.replace('3', '<span>3');// Set '3'
        hotel_rating_list_view = hotel_rating_list_view.replace('4', '<span>4');// Set '4'
        hotel_rating_list_view = hotel_rating_list_view.replace(/\s+/g, '*</span>,');
        hotel_rating_list_view = hotel_rating_list_view + '*</span>';
        jQueryMod2('#tour_search_module_mod2 .itt_mod_search_horel_ratin_set').html(hotel_rating_list_view);
    }
}

// Handler for hotel stars
jQueryMod2('#tour_search_module_mod2 .itt_search_module_list_star_inline li a').delegate('span', 'click', function(){
    var star_element = jQueryMod2(this);
    
    // Check element active
    if (star_element.hasClass('itt_search_module_hotel_rating_active')) {
        // Active
        star_element.removeClass('itt_search_module_hotel_rating_active');
    } else {
        // Not active
        star_element.addClass('itt_search_module_hotel_rating_active');
    }
    
    // Set new rationg in hidden input
    refreshHotelRatingTotalNewVersion();
});

/**
 * function set default list amount 'from' and 'to' use 'currency'
 * 
 * @param {string} tour_type Example: 'package' or 'hike'
 * @param {string} currency Example: 'UAH'
 * @returns {undefined}
 */
function setDefaultListAmountFromAndTo(currency, tour_type){
    // Set variable with html list amount
    var grn_html_from, grn_html_to, usd_eur_html_from, usd_eur_html_to;
    // Html 'Грн' list 'from'
    grn_html_from = '<option value="0" selected="selected">0</option>'
                  + '<option value="1000">1 000</option>'
                  + '<option value="2000">2 000</option>'
                  + '<option value="3000">3 000</option>'
                  + '<option value="4000">4 000</option>'
                  + '<option value="5000">5 000</option>'
                  + '<option value="6000">6 000</option>'
                  + '<option value="7000">7 000</option>'
                  + '<option value="8000">8 000</option>'
                  + '<option value="9000">9 000</option>'
                  + '<option value="10000">10 000</option>'
                  + '<option value="12000">12 000</option>'
                  + '<option value="15000">15 000</option>'
                  + '<option value="20000">20 000</option>'
                  + '<option value="25000">25 000</option>'
                  + '<option value="30000">30 000</option>'
                  + '<option value="35000">35 000</option>'
                  + '<option value="40000">40 000</option>'
                  + '<option value="50000">50 000</option>'
                  + '<option value="100000">100 000</option>'
                  + '<option value="110000">100 000+</option>';

    // Html 'Грн' list 'to'
    grn_html_to = '<option value="110000">100 000+</option>'
                + '<option value="100000" selected="selected">100 000</option>'
                + '<option value="50000">50 000</option>'
                + '<option value="40000">40 000</option>'
                + '<option value="35000">35 000</option>'
                + '<option value="30000">30 000</option>'
                + '<option value="25000">25 000</option>'
                + '<option value="20000">20 000</option>'
                + '<option value="15000">15 000</option>'
                + '<option value="12000">12 000</option>'
                + '<option value="10000">10 000</option>'
                + '<option value="9000">9 000</option>'
                + '<option value="8000">8 000</option>'
                + '<option value="7000">7 000</option>'
                + '<option value="6000">6 000</option>'
                + '<option value="5000">5 000</option>'
                + '<option value="4000">4 000</option>'
                + '<option value="3000">3 000</option>'
                + '<option value="2000">2 000</option>'
                + '<option value="1000">1 000</option>';

    // Html 'USD'/'EUR' list 'from'
    usd_eur_html_from = '<option value="100" selected="selected">100</option>'
                      + '<option value="200">200</option>'
                      + '<option value="300">300</option>'
                      + '<option value="400">400</option>'
                      + '<option value="500">500</option>'
                      + '<option value="600">600</option>'
                      + '<option value="700">700</option>'
                      + '<option value="800">800</option>'
                      + '<option value="900">900</option>'
                      + '<option value="1000">1 000</option>'
                      + '<option value="1200">1 200</option>'
                      + '<option value="1500">1 500</option>'
                      + '<option value="2000">2 000</option>'
                      + '<option value="2500">2 500</option>'
                      + '<option value="3000">3 000</option>'
                      + '<option value="3500">3 500</option>'
                      + '<option value="4000">4 000</option>'
                      + '<option value="5000">5 000</option>'
                      + '<option value="10000">10 000</option>'
                      + '<option value="11000">10 000+</option>';

    // Html 'USD'/'EUR' list 'to'
    usd_eur_html_to = '<option value="11000">10 000+</option>'
                    + '<option value="10000" selected="selected">10 000</option>'
                    + '<option value="5000">5 000</option>'
                    + '<option value="4000">4 000</option>'
                    + '<option value="3500">3 500</option>'
                    + '<option value="3000">3 000</option>'
                    + '<option value="2500">2 500</option>'
                    + '<option value="2000">2 000</option>'
                    + '<option value="1500">1 500</option>'
                    + '<option value="1200">1 200</option>'
                    + '<option value="1000">1 000</option>'
                    + '<option value="900">900</option>'
                    + '<option value="800">800</option>'
                    + '<option value="700">700</option>'
                    + '<option value="600">600</option>'
                    + '<option value="500">500</option>'
                    + '<option value="400">400</option>'
                    + '<option value="300">300</option>'
                    + '<option value="200">200</option>'
                    + '<option value="100">100</option>';    
    
    if (typeof currency == 'undefined') currency = 'UAH';
    if (typeof currency === undefined) currency = 'UAH';
    if (typeof currency === null) currency = 'UAH';
    
    // Package
    if (tour_type == 'package') {
        // Set new list 'Грн.'
        if (currency == 'UAH') {
            jQueryMod2('#tour_search_module_mod2 .itt_amount_list_relation_currency_from').html(grn_html_from);
            jQueryMod2('#tour_search_module_mod2 .itt_amount_list_relation_currency_to').html(grn_html_to);
        }

        // Set new list 'USD'/'EUR'
        if ((currency == 'USD') || (currency == 'EUR')) {
            jQueryMod2('#tour_search_module_mod2 .itt_amount_list_relation_currency_from').html(usd_eur_html_from);
            jQueryMod2('#tour_search_module_mod2 .itt_amount_list_relation_currency_to').html(usd_eur_html_to);
        }
        
        // Save new data to hidden
        setHiddenInputFromCustomSelect(jQueryMod2('#tour_search_module_mod2 .itt_amount_list_relation_currency_from'), jQueryMod2('#tour_search_module_mod2 .itt_amount_list_relation_currency_from option:selected').val());
        setHiddenInputFromCustomSelect(jQueryMod2('#tour_search_module_mod2 .itt_amount_list_relation_currency_to'), jQueryMod2('#tour_search_module_mod2 .itt_amount_list_relation_currency_to option:selected').val());
    }
    
    // Hike
    if (tour_type == 'hike') {
        // Set new list 'Грн.'
        if (currency == 'UAH') {
            jQueryMod2('#tour_search_module_mod2 .itt_amount_list_relation_currency_to_hike').html(grn_html_to);
        }

        // Set new list 'USD'/'EUR'
        if ((currency == 'USD') || (currency == 'EUR')) {
            jQueryMod2('#tour_search_module_mod2 .itt_amount_list_relation_currency_to_hike').html(usd_eur_html_to);
        }
    }
    
}

// Handler for selector currency => change array valueFrom valueTo
jQueryMod2('#tour_search_module_mod2 .itt_currency_selector').live('change', function(){
    var currency = jQueryMod2(this).val();
    
    // Set new lists
    setDefaultListAmountFromAndTo(currency, 'package');
});




/*** TAB Hike ***/
// Handler for selector currency => change array valueFrom valueTo
jQueryMod2('#tour_search_module_mod2 .itt_currency_selector_hike').live('change', function(){
    var currency = jQueryMod2(this).val();
    
    // Set new lists
    setDefaultListAmountFromAndTo(currency, 'hike');
});







/**
 * function get css class from config (input[type="hidden"]) and use for custom select
 * 
 * @param {undefined}
 * @returns {string} custom_class_piptik => Css name, example: 'itt_custom_select_piptik_small' OR 'itt_custom_select_piptik_big'
 */
function getCssClassFromConfigAndUseForCustomSelect() {
    // Get class name
    var custom_select_button_class_name = jQueryMod2('#tour_search_module_mod2 form input[name="module2_type_select"]').val();
    var custom_class_piptik = '';
    
    if (typeof custom_select_button_class_name != 'undefined') {
        // Set custom class from config
        custom_class_piptik = custom_select_button_class_name;
    } else {
        // Error get data from config => set default class 'itt_custom_select_piptik_small'
        custom_class_piptik = 'itt_custom_select_piptik_small';
    }
    
    return custom_class_piptik;
}

/*** Custom select for mod search => for Package and Hike tabs ***/

/**
 * function generate custom select in html page if isset '.class_select_name'
 * 
 * @param {string} custom_class_piptik => Css name, example: 'itt_custom_select_piptik_small' OR 'itt_custom_select_piptik_big'
 * @param {string} class_select_name => Css name
 * @returns {undefined}
 */
function createCustomHtmlSelect(class_select_name, custom_class_piptik){
    // Iterate over each select element
    jQueryMod2('select.' + class_select_name).each(function () {
        // Cache the number of options
	var $this = jQueryMod2(this),
        numberOfOptions = jQueryMod2(this).children('option').length;
        
        // Hides the select element
	$this.addClass('itt_custmon_select_hidden');

	// Wrap the select element in a div
	$this.wrap('<div class="select"></div>');

	// Insert a styled div to sit over the top of the hidden select element
	$this.after('<div class="itt_styled_select"><div class="' + custom_class_piptik + '"></div></div>');

	// Cache the styled div
	var $styledSelect = $this.next('div.itt_styled_select');

	// Show the first select option in the styled div
	//$styledSelect.text($this.children('option').eq(0).text());
	
	// Show the selected option in the styled div
	$styledSelect.prepend($this.children('option:selected').text());
	
	// Insert an unordered list after the styled div and also cache the list
	var $list = jQueryMod2('<ul />', {
				'class': 'itt_custom_select_options'
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
            jQueryMod2('div.itt_styled_select.active').not(this).removeClass('active').next('ul.itt_custom_select_options').hide();
            jQueryMod2(this).toggleClass('active').next('ul.itt_custom_select_options').toggle();
	});
        
        // Hides the unordered list when a list item is clicked and updates the styled div to show the selected list item
	// Updates the select element to have the value of the equivalent option
	$listItems.click(function (e) {
            e.stopPropagation();
            
            $styledSelect.html(jQueryMod2(this).text() + '<div class="' + custom_class_piptik + '"></div>').removeClass('active');// New varik
            $this.val(jQueryMod2(this).attr('rel'));
            $list.hide();
            
            // Set value
            var option_value = jQueryMod2(this).attr('rel');
            
            // Handler for change currency => Package
            if ($this.hasClass('itt_currency_selector') === true) {
                // Set new lists ('package' / 'hike')
                setDefaultListAmountFromAndTo(option_value, 'package');
                
                // Update item select list from_price_list and to_price_list
                setTimeout(function(){
                    // Update list 'from' amd 'to'
                    updateCustomHtmlSelect(jQueryMod2('#tour_search_module_mod2 .itt_amount_list_relation_currency_from').parent(), 'itt_amount_list_relation_currency_from');
                    updateCustomHtmlSelect(jQueryMod2('#tour_search_module_mod2 .itt_amount_list_relation_currency_to').parent(), 'itt_amount_list_relation_currency_to');
                }, 200);
            }
            
            // Handler for change currency => Hike
            if ($this.hasClass('itt_currency_selector_hike') === true) {
                // Set new lists ('package' / 'hike')
                setDefaultListAmountFromAndTo(option_value, 'hike');
                
                // Update item select list to_price_list
                setTimeout(function(){
                    // Update list 'to'
                    updateCustomHtmlSelect(jQueryMod2('#tour_search_module_mod2 .itt_amount_list_relation_currency_to_hike').parent(), 'itt_amount_list_relation_currency_to_hike');
                }, 200);
            }
            
            // Handler for custom select child
            if ($this.hasClass('itt_child_count_selector') === true) {
                var className = 'itt_children_block_media_position';
                if ($this.parent().parent().parent().parent().hasClass('itt_children_block_media_position_two')) {
                    className = 'itt_children_block_media_position_two';
                }
                jQueryMod2('#tour_search_module_mod2 .' + className + ' .itt_child_count_selector option[value="' + option_value + '"]').trigger('change');
            }
            
            // Handler for set 'selected' element in real select
            // Delete old selected
            $this.children('option').each(function(){
                jQueryMod2(this).removeAttr('selected');
            });
            // Set new selected
            $this.find('option[value="' + jQueryMod2(this).attr('rel') + '"]').attr('selected', 'selected');
            
            // Set data from select to input hidden => for sinhronisation all item element
            setHiddenInputFromCustomSelect($this, jQueryMod2(this).attr('rel'));
	});
        
        // Hides the unordered list when clicking outside of it
	jQueryMod2(document).click(function () {
            $styledSelect.removeClass('active');
            $list.hide();
        });
    });
}

/**
 * function update custom html select
 * 
 * @param {object} el_update
 * @param {string} class_name_for_select
 * @returns {undefined}
 */
function updateCustomHtmlSelect(el_update, class_name_for_select){
    // Delete div.itt_styled_select
    el_update.find('div.itt_styled_select').remove();
    
    // Delete ul.itt_custom_select_options
    el_update.find('ul.itt_custom_select_options').remove();
    
    // Delete class
    if (el_update.hasClass('select')) {
        el_update.removeClass('select');
    }
    
    // Create new custom select
    createCustomHtmlSelect(class_name_for_select, getCssClassFromConfigAndUseForCustomSelect());
}

// Fix RE-init custom scroll for Hike
jQueryMod2('#tour_search_module_mod2 .itt_search_module_tab li a.itt_search_module_tab_title').live('click', function(){
    jQueryMod2(window).trigger('resize');
});

// Init custom scroll for Package/Hike
jQueryMod2(window).resize(function(){
    // Init custom css for current mod size
    initCssForCurrentModuleSize();
    
    // Get data from hidden and set select
    getDataFromHiddenInputAndSetSelectValue();
});

/**
 * Function делает проверку у параметра наличие класса(это селект), 
 * и сохраняет значение в скрытое поле для синхронизации всех полей
 * 
 * @param {object} $this
 * @returns {undefined}
 */
function setHiddenInputFromCustomSelect(selectObj, newValue){
    // *** Package ***
    // Set 'date period'
    if (selectObj.hasClass('itt_date_period_package')) jQueryMod2('input[name="itt_date_period_package"]').val(newValue);
    
    // Set 'food'
    if (selectObj.hasClass('itt_food_package')) jQueryMod2('input[name="itt_food_package"]').val(newValue);
    
    // Set 'adult count'
    if (selectObj.hasClass('itt_adult_package')) jQueryMod2('input[name="itt_adult_package"]').val(newValue);
    
    // Set 'child count'
    if (selectObj.hasClass('itt_children_package')) jQueryMod2('input[name="itt_children_package"]').val(newValue);
    
    // Set 'hotel'
    if (selectObj.hasClass('itt_hotel_package')) jQueryMod2('input[name="itt_hotel_package"]').val(newValue);
    
    // Set 'night from'
    if (selectObj.hasClass('itt_night_from_package')) jQueryMod2('input[name="itt_night_from_package"]').val(newValue);
    
    // Set 'night till'
    if (selectObj.hasClass('itt_night_till_package')) jQueryMod2('input[name="itt_night_till_package"]').val(newValue);
    
    // Set 'currency'
    if (selectObj.hasClass('itt_switch_price_package')) jQueryMod2('input[name="itt_switch_price_package"]').val(newValue);
    
    // Set 'price from'
    if (selectObj.hasClass('itt_price_from_package')) jQueryMod2('input[name="itt_price_from_package"]').val(newValue);
    
    // Set 'price till'
    if (selectObj.hasClass('itt_price_till_package')) jQueryMod2('input[name="itt_price_till_package"]').val(newValue);
    
    // Set 'departure city'
    if (selectObj.hasClass('itt_departure_city_package')) jQueryMod2('input[name="itt_departure_city_package"]').val(newValue);
}

/**
 * Function запускает обновление / синхронизацию всех переключателей на новом МП.
 * 
 * @returns {undefined}
 */
function getDataFromHiddenInputAndSetSelectValue(){
    
    // Set 'date period'
    setSelectedHtmlSelect('itt_date_period_package', jQueryMod2('input[name="itt_date_period_package"]').val());
    
    // Set 'food'
    setSelectedHtmlSelect('itt_food_package', jQueryMod2('input[name="itt_food_package"]').val());
    
    // Set 'adult count'
    setSelectedHtmlSelect('itt_adult_package', jQueryMod2('input[name="itt_adult_package"]').val());
        
    // Set 'child count'
    setSelectedHtmlSelect('itt_children_package', jQueryMod2('input[name="itt_children_package"]').val());
    
    // Set 'child age #1'
    setChildAgeCustom(1, jQueryMod2('input[name="itt_child1_age_package"]').val());
    
    // Set 'child age #2'
    setChildAgeCustom(2, jQueryMod2('input[name="itt_child2_age_package"]').val());
    
    // Set 'child age #3'
    setChildAgeCustom(3, jQueryMod2('input[name="itt_child3_age_package"]').val());
    
    // Set 'hotel'
    setSelectedHtmlSelect('itt_hotel_package', jQueryMod2('input[name="itt_hotel_package"]').val());
    
    // Set 'night from'
    setSelectedHtmlSelect('itt_night_from_package', jQueryMod2('input[name="itt_night_from_package"]').val());
    
    // Set 'night till'
    setSelectedHtmlSelect('itt_night_till_package', jQueryMod2('input[name="itt_night_till_package"]').val());
    
    // Set 'currency'
    setSelectedHtmlSelect('itt_switch_price_package', jQueryMod2('input[name="itt_switch_price_package"]').val());
    
    // Set 'price from'
    setSelectedHtmlSelect('itt_price_from_package', jQueryMod2('input[name="itt_price_from_package"]').val());
    
    // Set 'price till'
    setSelectedHtmlSelect('itt_price_till_package', jQueryMod2('input[name="itt_price_till_package"]').val());
    
    // Set 'departure city'
    setSelectedHtmlSelect('itt_departure_city_package', jQueryMod2('input[name="itt_departure_city_package"]').val());
}

/**
 * В данной функции в цикле проходится по всем select-ам 
 * с заданым selectClass, удаляется старое значение selected и ставиться новое, 
 * а также запускается кастомный обработчик обновления селекта. 
 * 
 * @param {string} selectClass
 * @param {string, number} value
 * @returns {undefined}
 */
function setSelectedHtmlSelect(selectClass, value){
    jQueryMod2('#tour_search_module_mod2 .' + selectClass).each(function(){
        var itemSelect = jQueryMod2(this);
        
        // Delete old selected
        itemSelect.children('option').each(function(){
            jQueryMod2(this).removeAttr('selected');
        });
        
        // Set new selected
        itemSelect.find('option[value="' + value + '"]').attr('selected', 'selected');
        
        //*** Begin update view part custom select ***
        // Get new text for select
        var viewSelectText = itemSelect.find('option[value="' + value + '"]').text();
        
        // Get new html for select
        var viewSelectHTML = viewSelectText + '<div class="' + getCssClassFromConfigAndUseForCustomSelect() + '"></div>';
        
        // Update custom select => update view part
        itemSelect.parent().find('div.itt_styled_select').html(viewSelectHTML);
        
        // Update show block for set child age
        if (itemSelect.hasClass('itt_child_count_selector') === true) {
            jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position .itt_child_count_selector option[value="' + value + '"]').trigger('change');
            jQueryMod2('#tour_search_module_mod2 .itt_children_block_media_position_two .itt_child_count_selector option[value="' + value + '"]').trigger('change');
        }
        //*** End update view part custom select ***
    });
}

/**
 * Function задает/синхронизирует возраст для всех переключателей возраста детей.
 * 
 * @param {number} num
 * @param {string} value
 * @returns {undefined}
 */
function setChildAgeCustom(num, value){
    // Check variable
    if (typeof value === undefined) return;
    if (typeof value === null) return;
    
    // Update param // Без привязки ко враперу => враппер тут не нужен
    jQueryMod2('#tour_search_module_mod2 .itt_row_child_item_block_num_' + num).each(function(){
        jQueryMod2(this).find('ul.itt_child_age li').each(function(){
            var li = jQueryMod2(this);
            var li_text = li.text();
            
            // Unset 'active class'
            if(li.hasClass('itt_child_age_active')) li.removeClass('itt_child_age_active');
            
            // Set 'active class'
            if (li_text == value) {
                li.addClass('itt_child_age_active');
            }
        });
    });
}

/**
 * Добавляю / удаляю class для главного div с модулем
 * 
 * @returns {undefined}
 */
function initCssForCurrentModuleSize(){
    var mod_search = jQueryMod2('div#tour_search_module_mod2');
    var mod_width = mod_search.width();
    
    if (typeof mod_width != 'undefined') {
        
        // Удаляю все классы для встраеваемого модуля
        mod_search.removeAttr('class');
        
        // Для модуля поиска
        // All media from 'style.css'
        if (mod_width < 768) {
            mod_search.addClass('itt_custom_media_class_max_width_768');
        }
        if (mod_width >= 768 && mod_width < 992) {
            mod_search.addClass('itt_custom_media_class_min_width_768_and_max_width_992');
        }
        if (mod_width >= 992 && mod_width < 1200) {
            mod_search.addClass('itt_custom_media_class_min_width_992_and_max_width_1200');
        }
        if (mod_width >= 1200 && mod_width < 1500) {
            mod_search.addClass('itt_custom_media_class_min_width_1200_and_max_width_1500');
        }
        if (mod_width >= 1600) {
            mod_search.addClass('itt_custom_media_class_min_width_1600');
        }
        if (mod_width >= 840 && mod_width < 1200) {
            mod_search.addClass('itt_custom_media_class_min_width_840_and_max_width_1200');
        }
        if (mod_width >= 1200) {
            mod_search.addClass('itt_custom_media_class_min_width_1200');
        }
        if (mod_width >= 1 && mod_width < 850) {
            mod_search.addClass('itt_custom_media_class_min_width_1_and_max_width_850');
        }
        if (mod_width >= 1140) {
            mod_search.addClass('itt_custom_media_class_min_width_1140');
        }
        if (mod_width >= 1 && mod_width < 1500) {
            mod_search.addClass('itt_custom_media_class_min_width_1_and_max_width_1500');
        }
        if (mod_width >= 850 && mod_width < 1140) {
            mod_search.addClass('itt_custom_media_class_min_width_850_and_max_width_1140');
        }
        if (mod_width >= 850 && mod_width < 1500) {
            mod_search.addClass('itt_custom_media_class_min_width_850_and_max_width_1500');
        }
        if (mod_width >= 1500 && mod_width < 1600) {
            mod_search.addClass('itt_custom_media_class_min_width_1500_and_max_width_1600');
        }
        if (mod_width >= 1141 && mod_width < 1800) {
            mod_search.addClass('itt_custom_media_class_min_width_1141_and_max_width_1800');
        }
        if (mod_width >= 1501 && mod_width < 1799) {
            mod_search.addClass('itt_custom_media_class_min_width_1501_and_max_width_1799');
        }
        if (mod_width >= 1800) {
            mod_search.addClass('itt_custom_media_class_min_width_1800');
        }
        // All media from 'itt_power_media_custom.css'
        if (mod_width >= 768) {
            mod_search.addClass('itt_custom_media_class_min_width_768');
        }
        if (mod_width >= 992) {
            mod_search.addClass('itt_custom_media_class_min_width_992');
        }
        if (mod_width <= 767) {
            mod_search.addClass('itt_custom_media_class_max_width_767');
        }
        if (mod_width >= 768 && mod_width <= 991) {
            mod_search.addClass('itt_custom_media_class_min_width_768_and_max_width_991');
        }
        if (mod_width >= 992 && mod_width <= 1199) {
            mod_search.addClass('itt_custom_media_class_min_width_992_and_max_width_1199');
        }
        
        // Для результатов поиска
        // All media from 'style_issuing_search_650x350.css'
        if (mod_width <= 992) {
            mod_search.addClass('itt_custom_media_class_max_width_992');
        }
        
        // Для просомотра пакетного тура 
        // последовательность этих if менять НЕЛЬЗя! => влияет на инициализацию css
        // код перенесен из верстки + удалены те классы которые повторяются, 
        // т.е. они уже добавляются ранее в модуле или результате
        if (mod_width >= 850 && mod_width < 992) {
            mod_search.addClass('itt_custom_media_class_min_width_850_and_max_width_992');
        }
        if (mod_width <= 980) {
            mod_search.addClass('itt_custom_media_class_max_width_980');
        }
        if (mod_width <= 800) {
            mod_search.addClass('itt_custom_media_class_max_width_800');
        }
        if (mod_width >= 768 && mod_width < 850) {
            mod_search.addClass('itt_custom_media_class_min_width_768_and_max_width_850');
        }
        if (mod_width >= 850 && mod_width < 900) {
            mod_search.addClass('itt_custom_media_class_min_width_850_and_max_width_900');
        }
        if (mod_width >= 900) {
            mod_search.addClass('itt_custom_media_class_min_width_900');
        }
        if (mod_width >= 1080) {
            mod_search.addClass('itt_custom_media_class_min_width_1080');
        }
        if (mod_width >= 1200 && mod_width < 1300) {
            mod_search.addClass('itt_custom_media_class_min_width_1200_and_max_width_1300');
        }
        if (mod_width >= 1300 && mod_width < 1400) {
            mod_search.addClass('itt_custom_media_class_min_width_1300_and_max_width_1400');
        }
        if (mod_width >= 1400 && mod_width < 1550) {
            mod_search.addClass('itt_custom_media_class_min_width_1400_and_max_width_1550');
        }
        if (mod_width >= 1470) {
            mod_search.addClass('itt_custom_media_class_min_width_1470');
        }
        if (mod_width >= 1550) {
            mod_search.addClass('itt_custom_media_class_min_width_1550');
        }
        if (mod_width >= 1600 && mod_width < 1700) {
            mod_search.addClass('itt_custom_media_class_min_width_1600_and_max_width_1700');
        }
        if (mod_width >= 1700 && mod_width < 1800) {
            mod_search.addClass('itt_custom_media_class_min_width_1700_and_max_width_1800');
        }
    }
}

/**
 * function set data format from '1.4.15' to '01.04.15'
 * 
 * @param {String} date
 * @returns {String}
 */
function checkDateBeforeSend(date) {
    if (typeof date === undefined) return 'Error in date #1!';
    if (typeof date == 'undefined') return 'Error in date #2!';
    if (typeof date === null) return 'Error in date #3!';
    
    var new_date = '';
    var tmp = date.split('.', 3);
    if (typeof tmp !== undefined) {
        var new_date = '';
        for (var i=0; i<tmp.length; i++){
            if (tmp[i].length < 2) tmp[i] = '0' + tmp[i];
            if (i == 0) {
                new_date += tmp[i];
            } else {
                new_date += '.'+ tmp[i];
            }
        }
        return new_date;
    }
    return 'Error in date #4!';
}