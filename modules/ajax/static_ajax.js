// JavaScript Document

jQuery.support.placeholder = (function(){
    var i = document.createElement('input');
    return 'placeholder' in i;
})();


// Placeholders
$(function () {
	// edit all input titles to placeholders
	$(':input[title]:not(:button)').each(function(){
		$(this).attr('placeholder',$(this).attr('title'));
	});
});

// Placeholders - legacy
function PlaceholderEnter(sender) {
	$(sender).removeClass('utopia-placeholder');
	if (sender.tagName == 'SELECT') return;
	if (sender.value == $(sender).attr('placeholder'))
		sender.value = '';
}
function PlaceholderLeave(sender) {
	var val = $(sender).val();
	if (sender.tagName == 'SELECT') val = $(':selected',sender).text();
	if (val == $(sender).attr('placeholder') || val == '') {
		$(sender).addClass('utopia-placeholder');
		if (!$.support.placeholder) {
			$(sender).val($(sender).attr('placeholder'));
		} else $(sender).val('');	
	}
}
$(function () {
	$(".uFilter, :input[placeholder]:not(:button)").each(function() {
		PlaceholderLeave(this);
	});
});
$(document).on('focus',':input[placeholder]:not(:button)',function (event) {PlaceholderEnter(this);});
$(document).on('blur',':input[placeholder]:not(:button)',function (event) {var sender = this; setTimeout(function(){PlaceholderLeave(sender);},50);});

$(document).on('submit','form',function (event) { $(".uFilter, :input[placeholder]:not(:button)").each(function() { PlaceholderEnter(this); }); });

// Filters
utopia.Initialise.add(function(){ $('.uFilter').each(function(){ if ($(this).data('ov')) return; $(this).data('ov',$(this).val()); }) });
$(document).on('click','.uFilter',function (event) {this.focus(); event.stopPropagation(); return false;});
$(document).on('keydown','.uFilter',function (event) { if ((event.charCode == '13' || event.keyCode == '13') && (!$(this).is('TEXTAREA') && !$(this).is('SELECT'))) this.blur(); });

$(document).on('selectstart','.uFilter',function (event) {event.stopPropagation(); return true;});
$(document).on('blur','.uFilter',function (event) {setTimeout(function(){ReloadFilters();},100);});

function ReloadFilters() {
	var newFilters = {};
	var updated = false;

	$(".uFilter").each(function () {
		var name = $(this).attr('name');
		if (empty(name)) return;
		if ($(this).val() == $(this).data('ov')) return;
		
		var oldVal = gup(name);
		var newVal;

		if (($(this).val() == $(this).attr('title')) || ($(this).val() == $(this).attr('placeholder')) || ($(this).attr('type') == 'checkbox' && !$(this).attr('checked')) || ($(this).attr('type') == 'radio' && !$(this).attr('checked')))
			newVal = '';
		else
			newVal = String($(this).val());

		valHasChanged = (oldVal !== newVal);
		if (valHasChanged) {
			updated = true;
			newFilters[name] = newVal;
		}
	});

	if (!updated) return;
	// need to track existing filters, if they dont have a filter box then we must re-add them

	ReloadWithItems(newFilters);
}


$(document).on('click','.btn-del',function(event) {
	if (confirm('Are you sure you wish to delete this record?')) {
		uf(this);
	}
	event.stopImmediatePropagation();
	event.preventDefault();
	return false;
});

$(document).on('click','a.uPaginationLink',function(){
	$(this).attr('href',$(this).attr('href').replace(/#.+$/,'')+window.location.hash);
});

utopia.Initialise.add(AppendSubmit);
function AppendSubmit() { // auto append submit buttons
	$('form:not(:has(:submit))').append('<input type="submit" class="uHidden" value="" />');
};

$(document).ready(function(){
	$('li:first-child').addClass('first-child');
	$('li:last-child').addClass('last-child');

	$(document).on('tabsactivate', function(event, ui) { // bind after creation to stop immediate redirection to first hash
		var hash = ui.newTab.attr('aria-controls');
		var nodes = $('#'+hash);
		nodes.removeAttr('id'); // remove ID to stop scrolling
		window.location.hash = '#'+hash;
		nodes.attr('id',hash); // re-establish ID
	});
	
	$(document).on('click','th.sortable',function (e) {
		var fieldname = $(this).attr('data-field');
		var mID = $(this).attr('data-mid');
		if (!fieldname || !mID) return;

		var fullCurrent = gup('_s_' + mID);
		var currentSort = '';

		// if shift held, use the existing sort.
		if (e.shiftKey) currentSort = fullCurrent;
		// else if field is already in the filter, retain it.
		else if (fullCurrent.match(new RegExp(fieldname, 'i')) !== null) { currentSort = fullCurrent.match(new RegExp(fieldname+'( (ASC|DESC))?','i'))[0];}

		var order = {};
		// if blank, just add order
		if (!currentSort) {
			order['_s_'+mID] = fieldname;

		} else if (currentSort.match(new RegExp(fieldname+' ASC', 'i')) !== null) {
			// replace to DESC
			order['_s_'+mID] = currentSort.replace(new RegExp(fieldname+' ASC', 'i'),fieldname+' DESC');

		} else if (currentSort.match(new RegExp(fieldname+' DESC', 'i')) !== null) {
			// Replace to ASC
			order['_s_'+mID] = currentSort.replace(new RegExp(fieldname+' DESC', 'i'),fieldname);

		} else if (currentSort.match(new RegExp(fieldname, 'i')) !== null) {
			// append DESC
			order['_s_'+mID] = currentSort.replace(new RegExp(fieldname, 'i'),fieldname+' DESC');

		} else {
			// else append to end.
			order['_s_'+mID] = currentSort + ', '+fieldname;
		}
		ReloadWithItems(order);
	});

	window.onscroll = function() {
		// Thanks to Johan SundstrÃ¶m (http://ecmanaut.blogspot.com/) and David Lantner (http://lantner.net/david)
		// for their help getting Safari working as documented at http://www.derekallard.com/blog/post/conditionally-sticky-sidebar
		if( window.XMLHttpRequest ) { // IE 6 doesn't implement position fixed nicely...
			$('#uPanel').css({'position':'fixed','top':0,'left':0});
		}
	}

	$('img.calendar_trigger').bind('click', function (event) {event.stopPropagation(); return true;});
});

utopia.Initialise.add(RefreshTables);
function RefreshTables() {
	$('.layoutListSection TBODY TR').removeClass('odd even');
	$('.layoutListSection TBODY TR:odd').addClass('odd');
	$('.layoutListSection TBODY TR:even').addClass('even');
}

utopia.Initialise.add(UpdateSelectedLinks);
function UpdateSelectedLinks() {
	$('.active-link').removeClass('active-link');
	$('.active-link-parent').removeClass('active-link-parent');
	var uuid = getParameterByName('uuid');
	var queryArgs = window.location.search.substring(1).split('&');
	$('a').each(function() {
		if (!$(this).attr('href')) return;
		var _href = $(this).attr('href');
		var _hrefArr = _href.split('?');
		var _hrefPath = _hrefArr[0];
		var _hrefArgs = _hrefArr[1] ? _hrefArr[1].split('&') : [];
		var linkUUID = getParameterByName('uuid',_href);
		var rel = $(this).attr('rel'); if (!rel) rel = '';

		// assign rel tag
		if (_href == PATH_REL_ROOT) $(this).attr('rel',$.trim(rel+' home'));

		// assign current link trail
		var classname = '';
		if (uuid || linkUUID) {
			classname = (uuid == linkUUID) ? 'active-link' : '';
		} else {
			if (_href == PATH_REL_ROOT && _href != window.location.pathname) return;
			if ((window.location.pathname+'/').indexOf(_hrefPath+'/') != 0) return;
			var exact = true;
			if (_hrefPath == window.location.pathname) {
				for (var i=0;i<queryArgs.length;i++) {
					if (!queryArgs[i]) continue;
					var item = queryArgs[i].split('=');
					var v = getParameterByName(item[0],_href);
					if (v != item[1]) exact = false;
				}
				for (var i=0;i<_hrefArgs.length;i++) {
					if (!_hrefArgs[i]) continue;
					var item = _hrefArgs[i].split('=');
					var v = getParameterByName(item[0]);
					if (v != item[1]) exact = false;
				}
			}
			classname = ((_hrefPath == window.location.pathname) && exact) ? 'active-link' : 'active-link-parent';
		}
		if (!classname) return;

		if ($(this).parent('li').length) {
			$(this).parent('li').addClass(classname);
			$(this).parent('li').parents('li').addClass('active-link-parent');
		} else {
			$(this).addClass(classname);
		}
	});
}

function GetFieldValues(callback,source,term) {
	$.ajax({
		url: PATH_REL_CORE+'index.php?__ajax=getValues',
		dataType: "json",
		data: {source:source,term:term},
		global:false,
		cache:true,
		success: callback
	});
}

function gup( name ){ name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]"); var regexS = "[\\?&/]"+name+"=([^&#]*)"; var regex = new RegExp( regexS ); var results = regex.exec( window.location.href ); if( results == null ) return ""; else return decodeURIComponent(results[1].replace(/\+/g,' ')); }

function empty(subject) {
	return (typeof(subject)=="undefined" || subject == null || subject == '');
}
/**
*
*	Base64 encode / decode
*	http://www.webtoolkit.info/
*
**/

var Base64 = {

	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

	// public method for encoding
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;

		input = Base64._utf8_encode(input);

		while (i < input.length) {

			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);

			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;

			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}

			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

		}

		return output;
	},

	// public method for decoding
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;

		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

		while (i < input.length) {

			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));

			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;

			output = output + String.fromCharCode(chr1);

			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}

		}

		output = Base64._utf8_decode(output);

		return output;

	},

	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	},

	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;

		while ( i < utftext.length ) {

			c = utftext.charCodeAt(i);

			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}

		}

		return string;
	}

}

function ReloadWithItems(items, ignoreCurrent) {
	if (!ignoreCurrent) {
		arr = window.location.search.substr(1).split('&');
		$(arr).each(function () {
			var arr = this.split('=');
			if (empty(arr[1])) return;
			var name = arr[0];
			var val = decodeURIComponent(arr[1].replace(/\+/g,' '));
			if (empty(name)) return;
			if (typeof(items[name]) !== 'undefined') return;
			items[name] = val;
		});
	}
	var newQS = '';
	for (i in items) {
		if (!items[i]) continue;
		if (i.toLowerCase() == 'uuid') // always put uuid first
			newQS = i + '=' + items[i] + '&' + newQS;
		else
			newQS = newQS + i + '=' + items[i] + '&';
	}
	if (newQS != '') newQS = '?' + newQS;
	newQS = newQS.replace(/&$/,'');
	window.location.assign(window.location.pathname+newQS+window.location.hash);
}

var focused = null;
$(document).on('focus',':input',function() {
	focused = this;
});
$(document).on('click',':not(:input)',function() {
	focused = null;
});
function ReFocus() {
	if (!$.contains(document.documentElement,focused)) {
		if ($(focused).attr('id')) focused = $('#'+$(focused).attr('id'))[0];
		else if ($(focused).attr('name')) focused = $('[name="'+$(focused).attr('name')+'"]')[0];
	}
	if (!focused) return;
	focused.focus();
}

$(function() { // call on docready to allow cancelling events to bind first.
	$(document).on('change',':input.uf',_fieldChange);
	$(document).on('click','a.uf',function(){ uf(this,''); return false; });
	$(document).on('click','input[type=button].uf, .btn.uf',_fieldChange);
	$(document).on('submit','form.uf',function(event) {
		if ($(this).attr('target')) return;
		if ($('[name^="usql-"]',this).length) {
			ufForm(this,this);
			event.stopImmediatePropagation();
			return false;
		}
		return true;
	});
});
var isUpdating = [];
function StoppedUpdating(ele) {
	for (e in isUpdating) {
		if (isUpdating[e].element == ele) delete isUpdating[e];
	}
}
function _fieldChange(event) { if ($(this).closest('form').length) return true; uf(this); event.stopImmediatePropagation(); return true; }
function uf(ele, forcedValue) {
	for (e in isUpdating) {
		if (isUpdating[e].element == ele) return;
	}
	isUpdating.push({element:ele,val:forcedValue});

	$('html').addClass('wait');

	// timeout for autocomplete
	setTimeout(function(){_uf(ele);},200);
}
function _uf(ele) {
	var forcedValue = '';
	for (e in isUpdating) {
		if (isUpdating[e].element == ele) {
			forcedValue = isUpdating[e].val;
			break;
		}
	}

	var eleName = ele;
	if (typeof(ele) != 'string') {
		eleName = $(ele).attr('name');
	}

	var eleVal = '';

	if (forcedValue != undefined || forcedValue != null) {
		eleVal = forcedValue;
	} else {
		eleVal = getEleVal(ele);
	}

	var eleData = {'__ajax':'updateField'};

	targetPage = window.location.pathname+window.location.search;

	if ($(ele).attr('type') == 'file') {
		$(ele).ajaxFileUpload({
			type:'POST',
			async: true,
			cache: false,
			url:targetPage,
			data:eleData,
			dataType: "script",
			success: function (msg) {
				eval(msg);
				$('html').removeClass('wait');
				StoppedUpdating(ele);
				$('.auto-complete-list').hide();
				utopia.Initialise.run();
				ReFocus();
			}
		});
		return;
	}

	eleData[eleName] = eleVal;

	$.ajax({
		type: "POST",
		async: true,
		cache: false,
		url: targetPage,
		data: eleData,
		dataType: "script"
	}).fail(function(obj,type,e){
		if (empty(e)) return;
		$(ele).css('background','red');
		ErrorLog(type+': '+e.message);
	}).always(function(){
		$('html').removeClass('wait');
		StoppedUpdating(ele);
		$('.auto-complete-list').hide();
		ReFocus();
	});
}

function ufForm(frm) {
	setTimeout(function(){_ufData(frm);},200);
}
function _ufData(frm) {
	var eleData = {'__ajax':'updateField'};
	$(':input',frm).each(function(){ if ($(this).attr('name')) eleData[$(this).attr('name')]=getEleVal(this)});
	$('html').addClass('wait');
	
	targetPage = window.location.pathname+window.location.search;
	$.ajax({
		type: "POST",
		async: true,
		cache: false,
		url: targetPage,
		data: eleData,
		dataType: "script"
	}).always(function(){
		$('html').removeClass('wait');
		$('.auto-complete-list').hide();
		ReFocus();
	});
}

function getEleVal(ele) {
	var eleName = ele;
	if (typeof(ele) != 'string') {
		eleName = $(ele).attr('name');
	}
	if (eleName == undefined) return;
	var eleVal = [];
	var n = eleName.replace('usql-','');
	$(ele).closest('span.'+n).find('[name="'+eleName+'"]').add(ele).each(function () {
		if (this.tagName == 'INPUT' && $(this).is(':checkbox')) {
			if ($(this).is(':checked')) eleVal.push($(this).val());
		} else {
			eleVal.push($(this).val());
		}
	});
	switch (eleVal.length) {
		case 0: eleVal = ''; break;
		case 1: eleVal = eleVal[0]; break;
	}
	return eleVal;
}


(function( $ ) {
	$.widget( "ui.combobox", {
		_create: function() {
			var input,
				self = this,
				select = this.element.hide(),
				selected = select.children( ":selected" ),
				placeholder = $(select).attr('placeholder')?$(select).attr('placeholder'):$('option:first-child',select).text(), // placeholder is placeholder or first item
				value = selected.text(), // value is defaulted to current selected item
				value = (value === placeholder) ? '' : value, // if value is the placeholder, set it to empty.
				wrapper = this.wrapper = $( "<span>" )
					.addClass(this.element.attr('class'))
					.addClass( "ui-combobox" )
					.insertAfter( select );

			input = $( "<input>" )
				.appendTo( wrapper )
				.addClass(this.element.attr('class'))
				.addClass( "ui-combobox-input" )
				.addClass( "inputtype-text" )
				.attr('readonly','readonly')
				.attr('placeholder',placeholder)
				.attr('disabled',this.element.attr('disabled'))
				.click(toggleDropdown)
				.val(value)
				.autocomplete({
					delay: 0,
					minLength: 0,
					source: function( request, response ) {
						var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
						response( select.children( "option" ).map(function() {
							var text = $( this ).text();
							if ( /*this.value &&*/ ( !request.term || matcher.test(text) ) )
								return {
									label: text.replace(
										new RegExp(
											"(?![^&;]+;)(?!<[^<>]*)(" +
											$.ui.autocomplete.escapeRegex(request.term) +
											")(?![^<>]*>)(?![^&;]+;)", "gi"
										), "<strong>$1</strong>" ),
									value: text,
									option: this
								};
						}) );
					},
					select: function( event, ui ) {
						ui.item.option.selected = true;
						self._trigger( "selected", event, {
							item: ui.item.option
						});
						select.change();
						input.blur();
					},
					change: function( event, ui ) {
						if ( !ui.item ) {
							var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
								valid = false;
							select.children( "option" ).each(function() {
								if ( $( this ).text().match( matcher ) ) {
									this.selected = valid = true;
									return false;
								}
							});
							if ( !valid ) {
								// remove invalid value, as it didn't match anything
								$( this ).val( "" );
								select.val( "" );
								input.data( "ui-autocomplete" ).term = "";
								return false;
							}
						}
					}
				});
				//.addClass( "ui-widget ui-widget-content ui-corner-left" );

			input.data( "ui-autocomplete" )._renderItem = function( ul, item ) {
				return $( "<li"+($(item.option).val() == select.val() ? ' class="ui-state-highlight"' : '')+"></li>" )
					.data( "ui-autocomplete-item", item )
					.append( "<a>" + item.label + "</a>" )
					.appendTo( ul );
			};

			$( "<a>" )
				.attr( "tabIndex", -1 )
				.attr( "title", "Show All Items" )
				.appendTo( wrapper )
				.removeClass( "ui-corner-all" )
				.addClass( "btn ui-combobox-toggle" )
				.click(toggleDropdown);
				
			function toggleDropdown() {
				if (input.attr('disabled')) return;
				
				// close if already visible
				if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
					input.autocomplete( "close" );
					return;
				}

				// work around a bug (likely same cause as #5265)
				//$( this ).blur();

				// pass empty string as value to search for, displaying all results
				input.autocomplete( "search", "" );
				input.focus();
			}
		},
		_setOptions: function() {
			// _super and _superApply handle keeping the right this-context
			this._superApply( arguments );
		},

		destroy: function() {
			this.wrapper.remove();
			this.element.show();
			$.Widget.prototype.destroy.call( this );
		}
	});
})( jQuery );

utopia.CustomCombo = function() {
	utopia.Initialise.add(function(){$('select').combobox();});
}
