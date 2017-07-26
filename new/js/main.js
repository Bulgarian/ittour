// Loading js regarding this module

//*** Begin tabs in search result ******************************************
// actual addTab function: adds new tab using the input from the form above
var tabTitle = '<img src="http://www.ittour.com.ua/classes/handlers/ittour_external_modules/ittour_modules/images/ajax_loader_circle.gif" alt="load..." width="15px" height="15px" >',//'Tab title',//jQueryMod2( "#tab_title" ),
    tabContent = '',//'New tab content! New tab content! New tab content!',//jQueryMod2( "#tab_content" ),
    tabTemplate = "<li><a href='#{href}'>#{label}</a><span class='close_current_tab'>&#215;</span></li>",
    tabCounter = 100;

/**
 * function add newtab for user view tour in neew tab
 * @param {void} not params
 * @return {string} 'id' ID html params
 */
function addTabForTour() {
  var tabs = jQueryMod2( "#itt_tabs_search_result" ).tabs();  
  var label = tabTitle,//tabTitle + ' ' + (Math.floor(Math.random() * 100) + Math.floor(Math.random() * 1000)),// For debug v1
      id = "search_result_tab_" + tabCounter,
      li = jQueryMod2( tabTemplate.replace( /#\{href\}/g, "#" + id ).replace( /#\{label\}/g, label ) ),
      tabContentHtml = tabContent;
  
  tabs.find( ".ui-tabs-nav" ).append( li );
  tabs.append( "<div id='" + id + "'><p>" + tabContentHtml + "</p></div>" );
  tabs.tabs( "refresh" );
  
  // Set pre-loader in 'tab'
  var htmlPreloader = jQueryMod2('#itt_tour_search_load_item_tour').clone();
  jQueryMod2('#' + id).prepend(htmlPreloader.html()).show();
  jQueryMod2('#' + id + ' #itt_tour_search_load_view_item_tab').show();
  
  tabCounter++;
  
  // Set active new (last) tab
  jQueryMod2('#itt_tabs_search_result > ul > li').last().find('a').trigger('click');
  
  // Check space for new tab
  checkWidthSpaseForTabs();
  
  return id;
}

/**
 * function check 'sum width all li' and 'space for tabs', delete 'second tab'.
 * @param {void} not params
 * @return {void} not params
 */
function checkWidthSpaseForTabs() {
  // Get width for space for tabs
  var space_for_tabs = jQueryMod2('#itt_tabs_search_result ul.ui-tabs-nav').width();
  
  // Get sum width all 'li' (tabs) in space
  var sum_width_all_li = 0;
  jQueryMod2('#itt_tabs_search_result ul.ui-tabs-nav li').each(function(){
    sum_width_all_li += jQueryMod2(this).width();
    sum_width_all_li += 22;// margin 2px + padding 8px + padding 10px + margin 2px
  });
  
  sum_width_all_li += 20;// 20px as margin for last tab :-]
  sum_width_all_li += 30;// 30px for stock :-]
  
  if (sum_width_all_li > space_for_tabs) {
    // Delete 'second tab'
    jQueryMod2('#itt_tabs_search_result ul.ui-tabs-nav li').eq(1).remove();
  }
}
//*** End tabs in search result ********************************************

function forEach(data, callback) {
  if (data.length > 0) {
    for (var key in data) {
      if (data.hasOwnProperty(key)) {
        callback(key, data[key]);
      }
    }
  }
}

function isset(item) {
  if (typeof item == 'undefined') {
    return false;
  }
  return true;
}

function load_js_and_html_for_jsx() {

  $(document).ready(function() {
    $(".dropdown img.flag").addClass("flagvisibility");

    $(".dropdown dt a").click(function() {
      $(".dropdown dd ul").toggle();
    });

    $(".dropdown dd ul li a").click(function() {
      var text = $(this).html();
      $(".dropdown dt a span").html(text);
      $(".dropdown dd ul").hide();
      $("#result").html("Selected value is: " + getSelectedValue("sample"));
    });

    function getSelectedValue(id) {
      return $("#" + id).find("dt a span.value").html();
    }

    $(document).bind('click', function(e) {
      var $clicked = $(e.target);
      if (! $clicked.parents().hasClass("dropdown"))
        $(".dropdown dd ul").hide();
    });


    $("#flagSwitcher").click(function() {
      $(".dropdown img.flag").toggleClass("flagvisibility");
    });
    
    //*** Begin tabs in search result ******************************************
    // Add js for tour tabs view
    $(function() {
      //var tabs = jQueryMod2( "#itt_tabs_search_result" ).tabs();// Create tab in click button 'search' file 'module_loader.js'
      
      // Handler for close tab
      $('#itt_tabs_search_result ul li .close_current_tab').live('click', function(){
        // Get current click element (span.close_current_tab)
        $tt = $(this);
        var current_clicked_tab = $tt.parent();// li element

        // Get attr 'aria-controls' and use down as ID
        var attr_as_id = current_clicked_tab.attr('aria-controls');

        // Check if close active element
        if (current_clicked_tab.hasClass('ui-tabs-active ui-state-active')) {
          // Close 'active' element
          
          // Remove current tab and content
          current_clicked_tab.remove();
          $('#itt_tabs_search_result #' + attr_as_id).remove();
          
          // Set first tab 'active'
          $('#itt_tabs_search_result ul li a').first().trigger('click');
        } else {
          // Close 'not active' element
          
          // Remove current tab and content
          current_clicked_tab.remove();
          $('#itt_tabs_search_result #' + attr_as_id).remove();
        }
      });
      
      // Hendler for create new tab => open new tour in new tab => For debug
      $('#create_new_tab').click(function(){
        // Add new tab
        addTabForTour();
      });
      
    });
    //*** End tabs in search result ********************************************
    
  });


  // Javascript => new loader
  ModuleLoader = {
    
    path: null,
    items:{
        css: [],
        js : [],
        custom_css: '',
    },
    events:{
        'before_load'   : null,
        'after_load'    : null,
        'load_js'       : null,
        'error'         : null
    },
    
    setPath: function(path){
        this.path = path;
        return this;
    },
    
    getPath: function(){
        return this.path;
    },
    
    addCss: function(filename, is_check_old_ver){
        is_check_old_ver = isset(is_check_old_ver) ? is_check_old_ver : false;
        
        this.items.css.push({'filename':filename, 'is_check_old_ver': is_check_old_ver});
        return this;
    },
    
    addJs: function(filename, is_check_old_ver){
        is_check_old_ver = isset(is_check_old_ver) ? is_check_old_ver : false;
        
        this.items.js.push({'filename':filename, 'is_check_old_ver': is_check_old_ver});
        return this;
    },
    
    addCustomCss: function(css){
        this.items.custom_css += css;
    },
    
    load: function(){
        this.triggerEvent('before_load');
        this.loadCssFiles();
        this.loadCustomCss();
        this.loadJsFiles();
    },
    
    loadCssFiles: function(){
        var links = document.getElementsByTagName('link');
        
        forEach(this.items.css, function(key, file){
            filename = ModuleLoader.getPath() + file.filename;
            var css = document.createElement('link');
            css.rel = 'stylesheet';
            css.type = 'text/css';
            css.href = filename;

            var already_loaded_css = false;
            if(file.is_check_old_ver){
                for (var i = 0; i < links.length; i++){
                    if(links[i].getAttribute('href') === filename){
                        already_loaded_css = true;
                        return;
                    }
                }
            }
            if(already_loaded_css == false){
                document.getElementsByTagName('head')[0].appendChild(css);
            }
        });
    },
    
    loadJsFiles: function(num){
        num = isset(num) ? num : 0;
        
        if(isset(this.items.js[num])){
            
            var do_on_load = function(script){ 
                ModuleLoader.triggerEvent('load_js', script);
                ModuleLoader.loadJsFiles(++num); 
            };
            var script   = document.createElement('script');
            var filename = ModuleLoader.getPath() + this.items.js[num].filename;
            var scripts  = document.getElementsByTagName('script');
            var head     = document.getElementsByTagName("head")[0] || document.documentElement;
            var already_loaded = false;
            
            // Load js for full path 'http://......', use for google.maps
            var filename_tmp = this.items.js[num].filename;
            if (filename_tmp.indexOf('http://', 0) == 0) {
                filename = this.items.js[num].filename;
            }
            
            script.type = 'text/javascript';
            script.src  = filename;

            if(this.items.js[num].is_check_old_ver){
                for (var i = 0; i < scripts.length; i++){
                    if(scripts[i].getAttribute('src') === filename){
                        already_loaded = true;
                        do_on_load(script);
                        return false;
                    }
                }
            }

            if(already_loaded == false){
                script.onload = script.onreadystatechange = function() {
                    if (!this.readyState || this.readyState === "loaded" || this.readyState === "complete"){
                        do_on_load(script);
                        script.onload = script.onreadystatechange = null;
                    }
                };
                
                head.appendChild(script);
            }
        }else{
            this.triggerEvent('after_load');
        }  
    },
    
    /**
    * Loading custom css
    * @param {string} css
    */
    loadCustomCss: function(){
        var css  = this.items.custom_css;
        var style = document.createElement('style');
        
        style.setAttribute("type", "text/css");
        if (style.styleSheet) {   // IE
            style.styleSheet.cssText = css;
        } else {                // the world
            var tt1 = document.createTextNode(css);
            style.appendChild(tt1);
        }
        document.getElementsByTagName('head')[0].appendChild(style);
    },
    
    setEvent: function(event, callback){
        if(typeof callback == 'function'){
            this.events[event] = callback;
        }
        return this;
    },
    
    triggerEvent: function(event, param){
        if(this.events && typeof this.events[event] == 'function'){
            this.events[event](param);
        }
    },
  }
  window.ModuleLoader = ModuleLoader;

  ModuleLoader.addCustomCss('<?php echo $css;?>');


  <?php echo $js; ?>
}