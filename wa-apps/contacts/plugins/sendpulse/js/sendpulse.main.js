/*Добавляем иконку в список контактов*/
document.addEventListener("DOMContentLoaded", function(event){
	var target = document.getElementById("c-core");
	var observer = new MutationObserver(function(mutations){
		mutations.forEach(function(mutation){
			if(mutation.addedNodes.length>0){
				if(sids){
					$("#contacts-container .contacts-data table input[type='checkbox'].selector").each(function(){
						id = $(this).val();
						if($.inArray(id,sids)){
							$(this).closest(".details").append("<i class='icon16 sendpulse'></i>");
						}
					});				
				}
			}
		});    
	});
	var config = { attributes: true, childList: true, characterData: true };
	observer.observe(target, config);
});
/*Синхронизировать юзера с сендпульс?*/
function changeUserField(id, field, value){
	$.ajax({
		type: "POST",
		url: "?plugin=sendpulse&action=changeee",
		data: {field: field, value: value, id: id},
		dataType: "text",
		success: function(data){
			
		},
		error: function(error){
			return;
		}
	});	
}


var progress = 0,
	progress_max = 0;
	
var stop_it = false;
var categories = [];
/*Двигаем прогрессбар*/
var progressbar = {
	element: false,
	categories: false,
	per_step: 0,
	current: 0,
	maximum: 0,
	dialog: false,
	init: function(element, categories, dialog){
		this.dialog = dialog;
		this.dialog.find(".input[type=submit]").hide();
		this.element = element;
		this.current = 0;
		this.element.css("width", this.current+"%");
		this.categories = categories;
		this.maximum = this.calculateMaxSteps(this.categories);
		this.per_step = (100/this.maximum);
		this.element.show();
		this.dialog.find("i.loading").show();
		this.dialog.find(".button_stop").show();
		$(".dialog_complete_outer").html("");
	},
	calculateMaxSteps: function(categories){
		cats_count = categories.length;
		cats_pages = 0;
		for(i=0; i!=categories.length; i++){
			if(categories[i].count>100){
				cats_pages = cats_pages+(Math.ceil(categories[i].count/100));
				cats_count--;
			}
		}
		cats_count = cats_count+cats_pages;
		return cats_count;	
	},
	moveProgress: function(){
		this.current += this.per_step;
		this.element.css("width", this.current+"%");
		if(this.current>=100){
			this.stop_it();
		}
	},
	stop_it: function(){
		$(this.dialog).find("i.loading").hide();
		this.dialog.find(".input[type=submit]").show();
		$(this.dialog).find(".button_stop").hide();
		$(this.dialog).find("input[type=submit]").removeAttr("disabled");
		$(this.dialog).find("input[type=checkbox]").removeAttr("disabled");
		$(".dialog_complete_outer").html('<span id="plugins-settings-form-status" style=""><i style="vertical-align:middle" class="icon16 yes"></i>'+$_("Completed")+'</span>');
	}
	
};

/*Заливаем категории*/
function sendCategories(url, categories){
	if(categories.length>0){
		current = categories.pop();
		page = 0;
		pages = Math.ceil(current.count/100);
		sendCategoryPage(url, current, page, pages, categories);
	}else{
		console.log("complete...");
		progressbar.stop_it();
		stop_it = false;
	}
}
window.total_temp = 0;
function sendCategoryPage(url, category, page, pages, categories){
	if(page===0){
		offset = 0;
	}else{
		offset = page*100;
	}
	$.when(				
		$.ajax({
			type: "POST",
			url: url, 
			timeout: 0,
			data: {category_id: category.category_id, offset: offset, count: category.count},
			dataType: "json",
			error: function(x){
				sendCategoryPage(url, category, page, pages, categories);
				console.error("Error 504, try again...");
				return false;
			}
		})
	).then(function(data, textStatus, jqXHR){
		if(data.data.try_again && page!=pages-1 && !stop_it){
			console.error("Try again after 1000 ms... page: "+page+", cagegory: "+category);
			setTimeout(function(){
				sendCategoryPage(url, category, page, pages, categories);
			},1000);
		}else{
			progressbar.moveProgress();
			
			window.total_temp +=100;
			if(page!=pages-1 && !stop_it){
				sendCategoryPage(url, category, (page+1), pages, categories);
			}else{
				if(data.data.local_book_id){
					$("li[rel=category"+data.data.local_book_id+"] span.count").html(data.data.count);
				}
				console.log("finish cat "+category.category_id);
				if(stop_it){
					console.log("aborted...");
					progressbar.stop_it();
					stop_it = false;
					return false;
				}else{
					sendCategories(url, categories);
				}
			}
		}
	});    	
};

$(document).on("click", ".button_stop", function(){
	stop_it = true;
});


//TODO fix
$(document).on("change", "#check_all_cats", function(){
	thas = $(this);
	if(thas.is(":checked")){
		thas.closest("label").find("span").html(thas.data("checked-title"));
		thas.closest(".sendpulse_tab_content").find("input.category:not([disabled])").attr('checked', true);
	}else{
		thas.closest("label").find("span").html(thas.data("unchecked-title"));			
		thas.closest(".sendpulse_tab_content").find("input.category:not([disabled])").attr('checked', false);
	}
	return false;
});	

$(document).on("change", ".sendpulse_tab_content input[type=checkbox]", function(){
	thas = $(this);
	if(thas.closest(".value").find("input[type='checkbox']:checked").length>0){
		thas.closest("form").find("input[type=submit]").removeAttr("disabled");
	}else{
		thas.closest("form").find("input[type=submit]").attr("disabled", "disabled");
	}
});	

$(document).on("click", "#sendpulse_sync_contact_checkbox", function(){
	if($(this).is(":checked")){
		changeUserField($(this).val(), "sync_with_sendpulse", 1);
	}else{
		changeUserField($(this).val(), "sync_with_sendpulse", 0);
	}
});


function showComplete(){
	$(".dialog_complete_outer").html('<span id="plugins-settings-form-status" style=""><i style="vertical-align:middle" class="icon16 yes"></i>'+$_('Completed')+'</span>');	
}
function showError(){
	$(".dialog_complete_outer").html('<span id="plugins-settings-form-status" style=""><i style="vertical-align:middle" class="icon16 no"></i>'+$_('Error')+'</span>');	
}




$(document).ready(function(){
	$.wa.controller.reloadSidebar = function(){ /*Фиксим баг левого меню*/
		$.post("?module=backend&action=sidebar", null, function (response) {
			var sb = $("#wa-app #c-sidebar .sidebar");
			sb.css('height', sb.height()+'px') 
			  .html(response)
			  .css('height', '');
			$.wa.controller.highlightSidebar();
			$.wa.controller.restoreCollapsibleStatusInSidebar();
			if ($.wa.controller.initSidebarDragAndDrop) {
				$.wa.controller.initSidebarDragAndDrop();
			}
		});
	};
	
	$.wa.controller.sendpulseSettingsAction = function(){ };
	$.wa.controller.sendpulsePreexportAction = function(){ };
	$.wa.controller.sendpulsePreimportAction = function(){ };
	$.wa.controller.sendpulseImportexportAction = function(){ };
});





var Sendpulse = {
	settings: function(e){
		$.wa.setHash("/sendpulse/settings");
		e.preventDefault();
		that = this;
		el = $(this).eq(0);
		Sendpulse.setBlock("settings", $_("Loading..."), false, undefined, false);
		jQuery.ajax({
			type: "POST",
			url: el.attr("href"),
			dataType: "html",
			success: function(data){
				Sendpulse.setBlock("settings", "", data, undefined, false);
			},
			error: function(error){
				console.log(error);
			}
		});	
	},
	preimport: function(e){
		$.wa.setHash("/sendpulse/importexport");
		e.preventDefault();
		that = this;
		el = $(this).eq(0);
		Sendpulse.setBlock("settings", $_("Loading..."), false, undefined, false);		
		jQuery.ajax({
			type: "POST",
			url: el.attr("href"),
			dataType: "html",
			success: function(data){
				Sendpulse.setBlock("preimport", "", data, undefined, false);
			},
			error: function(error){
				console.log(error);
			}
		});	
	},
    saveSettings: function(ev){
    	ev.preventDefault();
    	if(ev.target){
    		form = $(ev.target);
    	}else{
    		return false;
    	}
		$(".dialog_complete_outer").html("");
		$(form).find("i.loading").show();
		$(form).find(".on_save_error").html("");			
		$.ajax({
			type: "POST",
			url: "?plugin=sendpulse&action=save",
			data: form.serialize(),
			dataType: "json",
			success: function(data){
				if(!data.status){
					$(form).find(".on_save_error").html(data.message);
					showError();
				}else{
					showComplete();
				}
			},
			error: function(error){
				showError();
				console.log(error);
				return;
			}
		}).done(function(){
			form.find("i.loading").hide();
		});	
		return false;
    },
    importFrom: function(ev){
    	ev.preventDefault();
		form = $(ev.target);
		var ids = [],
	 		els = form.find(".category:checked");
	 	if(!els.length){
	 		return false;
	 	}	

	 	form.find("input[type=checkbox]").attr("disabled", "disabled");	
		$.each(els, function(){
			category_id = $(this).val();
			count = $(this).closest(".value").find(".count").html();
			ids.push({category_id: category_id, count: count});
		});

		progressbar.current=0;
		form.find(".progressbar").show();
		progressbar.init(form.find("#sendpulse-progressbar"), ids, form);
		sendCategories(form.attr("action"), ids);
    },
    exportFrom: function(ev){
    	ev.preventDefault();
		form = $(ev.target);
		var ids = [],
	 		els = form.find(".category:checked");
	 	if(!els.length){
	 		return false;
	 	}	

	 	form.find("input[type=checkbox]").attr("disabled", "disabled");	
		$.each(els, function(){
			category_id = $(this).val();
			count = $(this).closest(".value").find(".count").html();
			ids.push({category_id: category_id, count: count});
		});

		progressbar.current=0;
		form.find(".progressbar").show();
		progressbar.init(form.find("#sendpulse-progressbar"), ids, form);
		sendCategories(form.attr("action"), ids);
    },
	
    setBlock: function(name, title, html, menus, options){
        if (!name) {
            name = 'default';
        }
        if(title === undefined || title === null){
			title = $_('Loading...');
        }
        this.block = name;
        $("#c-core .c-core-header").remove();
        options = options || {};
        menus = typeof menus === 'undefined' ? [] : menus;
        var el = '';
        el = $('#c-core').empty().
            append(
                $(
                    '<div class="contacts-background">' +
                        '<div class="block not-padded c-core-content"></div>' +
                    '</div>'
                )
            ).find(
                '.c-core-content'
            );
        el.html('<div class="block"><div class="c-actions-wrapper"></div><h1 class="wa-page-heading">' + title + ' <i class="icon16 loading"></i></h1></div>');
        $.scrollTo(0);
		
		if(!html){
			return;
		}    
		$('#c-core').html(html);
		$(window).trigger('wa_after_init_html', [name, title, menus]);
    },
    
    
    
    
    
};

$(document).on("click", "#sendpulse-main a", Sendpulse.settings);
$(document).on("click", "#sendpulse_menu a.preimport", Sendpulse.preimport);
$(document).on("click", "#sendpulse_menu a.preexport", Sendpulse.preimport);

$(document).on("submit", "#sendpulse_settings_form", Sendpulse.saveSettings);
$(document).on("submit", "#sendpulse_preimport_form", Sendpulse.importFrom);
$(document).on("submit", "#sendpulse_preexport_form", Sendpulse.exportFrom);








