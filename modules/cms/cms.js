// handle the toolbar show/hide with events
$(document).on('click','*',function(event) {
	if ($(this).closest('.mceExternalToolbar').length) return false;
	$('.mceExternalToolbar').hide();
});

var exposeMce = $('<div id="exposeMce"/>');
	
function moveMceToolbars(event,ed) {
	// move the toolbars post render
	ed.onPostRender.add(function(ed, cm) {
		// remove any existing toolbars for this editor
		$('.mceToolbarContainer > .mceExternalToolbar').each(function () {
			if ($(this).attr('id') == ed.editorId+'_external') $(this).remove();
		});
		$('.mceExternalToolbar').appendTo('.mceToolbarContainer');
	});
	
	ed.onInit.add(function(ed) {
		// copy all parents into the editor dom
		var body = $(ed.getDoc().body);
		var span = $(ed.getElement()).parent();
		span.parentsUntil('body').each(function () {
			body.addClass($(this)[0].className);
		});
		body.css({margin:0,padding:0,display:'block','float':'none', width:'auto',
			'-moz-transform':'none',
			'-webkit-transform':'none',
			'-o-transform':'none',
			'-ms-transform':'none',
			'transform':'none',
			'min-width':0
		});

/*		$('body').append(exposeMce);
		var cont = $('.mceLayout',ed.getContainer());
		cont.parents().each(function() {
			if ($(this).css('transform') !== 'none' && $(this).css('transform') !== null) $(this).css('transform','none');
			if ($(this).css('-webkit-transform') !== 'none' && $(this).css('-webkit-transform') !== null) $(this).css('-webkit-transform','none');
			if ($(this).css('-moz-transform') !== 'none' && $(this).css('-moz-transform') !== null) $(this).css('-moz-transform','none');
			if ($(this).css('-o-transform') !== 'none' && $(this).css('-o-transform') !== null) $(this).css('-o-transform','none');
			if ($(this).css('-ms-transform') !== 'none' && $(this).css('-ms-transform') !== null) $(this).css('-ms-transform','none');
		});
		cont.css({'position':'relative','z-index':1001});
*/
		
		// wake up the autoresize plugin
		setTimeout(function(){ed.execCommand('mceAutoResize', false, undefined, {skip_focus: true, skip_undo: true});},1);
	});
};
$(document).on('tinyMceSetup',moveMceToolbars);

// create 'cancel action' events for publish and revert - so the user has chance to abort
$(document).on('click','.page-publish',function(event) {
	if (!confirm('Any changes you have made will become visible to the public.  Do you wish to continue?')) {
		event.stopImmediatePropagation();
		event.preventDefault();
		return false;
	}
});
$(document).on('click','.page-unpublish',function(event) {
	if (!confirm('This page will become hidden from the public and become draft.  Do you wish to continue?')) {
		event.stopImmediatePropagation();
		event.preventDefault();
		return false;
	}
});
$(document).on('click','.page-revert',function(event) {
	if (!confirm('Reverting this page will reset all of your changes to the last published version.  Do you wish to continue?')) {
		event.stopImmediatePropagation();
		event.preventDefault();
		return false;
	}
});
