var Lookup = {
    
    init: function() {
        
    },
    
    lookup: function() {
        //$("head").append("<script type=\"text/javascript\" src=\"http://eu.battle.net/api/wow/item/" + app.locale.lookups[0] + "?jsonp=app.localeCallback&locale=" + app.locale.lang + "\"></script>");
    },
    
    lookupCallback: function() {
        $.ajax({
    		url: '/ajax/lookup-locale/',
			type: 'post',
			dataType : 'JSON',
			data: {
				id: packet.id,
				lang: app.locale.lang,
				name: packet.name
			},
			dataType : 'JSON',
			success: function(response) {

				if (typeof response.next == 'undefined') {
					return;
				}

				app.locale.results++;

				if (app.locale.results >= app.locale.max) {
					return;
				}
				if (response.next != 0) {
					app.locale.lookups[0] = response.next;
					setTimeout("app.lookupLocale()", app.locale.interval);
				}
			}
		});
    }
};