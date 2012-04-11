app = {
	loadout: {},
	render: false,
	flashparam: "",
	save_in_progress: false,
	save_required: false,
	search_open: false,
	search_delay: 0,
	search_prev: '',
	search_slot: 0,
	search_result: null,
	item_cache: [],
	restrict: 0,
	tracking: false,
	locale: {
		t: null,
		lang: "en_US",
		lookups: [],
		interval: 3000,
		results: 0,
		max: 50
	},

	beginChangeItem: function(slot) {
		if (this.tracking) {
			_gaq.push(['_trackEvent', 'App', 'Change Item']);
		}
		this.search_slot = slot.attr('data-slot');
		this.showDialog(slot);
	},

	showDialog: function(slot) {
		this.search_open = true;
		var offset = 42;
		$("#slot_prompt").css({top : $(slot).position().top, left: $(slot).position().left + offset}).show();
		$("#slot_prompt input").focus();

		var existing = this.itemAtSlot(slot.attr('data-slot'));

		if (existing && existing.id != 0) {
			var html = '<h4>Current:</h4>';
			html += '<div><a class="delete" onclick="app.unequipItem(); return false;" title="Unequip"></a>';
			html += '<img class="icon" src="' + this.base_icon_path + existing.icon + '.jpg" />';
			html += '<span class="q' + existing.quality + '">' + existing.name + '</span>';
			html += '<a class="wowhead" target="_blank" href="http://www.wowhead.com/item=' + existing.id + '"><img src="http://static.wowhead.com/images/logos/favicon.png"></a></span>';
			html += '</div>';

			$("#slot_prompt .current").html(html);
		}
		else {
			$("#slot_prompt .current").html('');
		}
	},

	changeItemSelect: function(item_id) {
		this.closeSearch();
		this.save_required = true;
		this.getDisplayId(item_id, this.search_slot);

		//update item
		var item = this.itemAtSlot(this.search_slot);

		for (var i = 0; i < this.search_result.length; i++) {
			if (this.search_result[i].id == item_id) {
				item.name = this.search_result[i].name;
				item.icon = this.search_result[i].icon;
				item.quality = this.search_result[i].quality;

				break;
			}
		}
		$("#app .items span[data-slot='" + item.slot + "'] ins").css({opacity: 0});
	},

	closeSearch: function() {
		$("#slot_prompt").hide();
		$("#slot_prompt li").remove();
		$("#slot_prompt input").val('');

		this.search_open = false;
	},

	getDisplayId: function(id, slot) {
		$.ajax({
			url: '/ajax/lookup-item/item/' + id,
			type : 'get',
			dataType : 'JSON',
			success: function(data) {
				if (data.display_id == null || data.display_id == 0) {
					return;
				}

				var update = false;

				for(i in app.loadout.items) {
					if (app.loadout.items[i].slot == slot) {
						app.loadout.items[i].id = id;
						app.loadout.items[i].display_id = data.display_id;
						update = true;
						break;
					}
				}

				if (update) {
					app.updateFlashParam();
					app.updateTooltip(slot);

					$("#help section.save").show(200);
				}
			}
		});
	},

	itemAtSlot: function(slot) {
		for(var i = 0; i < this.loadout.items.length; i++) {
			if (this.loadout.items[i].slot == slot) {
				return this.loadout.items[i];
			}
		}
		return false;
	},

	updateFlashParam: function() {
		this.flashparam = "model=" + this.loadout.race + this.loadout.gender + "&modelType=16&mode=3&sk=0&ha=0&hc=0&fa=0&fh=0&fc=0&mode=3&contentPath=" + this.content_url + "&equipList=";

		for(i in this.loadout.items) {
			if (this.loadout.items[i].id != 0) {
				this.flashparam += this.loadout.items[i].slot + "," + this.loadout.items[i].display_id + ",";
			}
		}

		this.flashparam = this.flashparam.substring(0, this.flashparam.length-1);
		this.drawFlash();
	},

	drawFlash: function() {

		$("object param[name='flashvars']").attr("value", this.flashparam);
		$("embed").attr("flashvars", this.flashparam);

		var obj = $("#mviewer div");

		$("#mviewer div").remove();
		$("#mviewer").append("<div>" + obj.html() + "</div>");

	},

	updateTooltip: function(slot) {
		if (typeof slot == 'undefined') {
			for(i in this.loadout.items) {
				this.updateTooltipAction(this.loadout.items[i]);
			}
		}
		else {
			var item = this.itemAtSlot(slot);
			this.updateTooltipAction(item);
		}
	},

	updateTooltipAction: function(item) {
		if (item.id == 0) {
		}
		else {
			$("#app .items span[data-slot='" + item.slot + "'] ins")
			.css("background-image", "url(" + this.base_icon_path_med + item.icon + ".jpg)")
			.animate({opacity: 1}, 1000);
		}
	},

	updateShareUrl: function() {
		if (this.loadout.ref) {
			this.share_url = this.base_url + '/' + this.loadout.ref;
		}
		else {
			this.share_url = this.base_url;
		}

		$("#share_url").val(this.share_url);
	},

	saveLoadout: function() {
		if (this.save_in_progress || !this.save_required) {
			return false;
		}

		this.save_in_progress = true;

		var data = {items: []};

		for(i in this.loadout.items) {
			if (this.loadout.items[i].id != 0) {
				data.items.push({slot: this.loadout.items[i].slot, id: this.loadout.items[i].id});
			}
		}

		data.label = $("#new_label").val();
		data.race = app.loadout.race;
		data.gender = app.loadout.gender;

		$.ajax({
			url: '/ajax/save-loadout/',
			type : 'post',
			data : data,
			dataType : 'JSON',
			success: function(data) {
				app.save_in_progress = false;
				app.save_required = false;

				if (data.status == 1) {
					document.location.href = app.base_url + "/" + data.ref;
				}
			},
			error: function() {
				app.save_in_progress = false;
				app.save_required = true;
			}
		});
	},

	lookupItem: function() {
		$.ajax({
			url: '/ajax/search-item/item/' + $("#slot_prompt input").val() + '/slot/' + app.search_slot + '/restrict/' + app.restrict,
			type : 'get',
			dataType : 'JSON',
			success: function(data) {
				app.search_result = data;
				app.showOptions(data);
			}
		});
	},

	showOptions: function(results) {
		var html = '';
		if (results.length == 0) {
			html = '<li class="last">No matches found :(</li>';
			$("#slot_prompt ul").html(html);

		}
		else {
			for (var i = 0; i < results.length; i++) {
				var css1 = 'q' + results[i].quality;
				var css2 = (i == results.length - 1) ? ' last' : '';
				html += '<li class="' + css2 + '" onclick="app.changeItemSelect(' + results[i].id + ');"><img src="' + this.base_icon_path + results[i].icon + '.jpg" /><span class="' + css1 + '">' + results[i].name + '</span> (' + results[i].i_level + ')</li>';
			}

			$("#slot_prompt ul").html(html);
		}
	},

	unequipItem: function() {
		//search_slot
		var item = this.itemAtSlot(this.search_slot);
		item.id = 0;
		item.name = '';
		item.quality = 0;
		item.icon = '';

		this.updateFlashParam();
		this.closeSearch();
		$("#app .items span[data-slot='" + this.search_slot + "'] ins").animate({opacity: 0}, 1000);
	},

	castVote: function(score) {
		$.ajax({
			url: '/ajax/vote/ref/' + this.loadout.ref + '/score/' + score,
			type : 'get',
			dataType : 'JSON',
			success: function(data) {
				$("#help .rate a").each(function(i) {

					var img = $(this).find("img");
					if (data > i) {
						img.attr("src", "/images/star.png");
					}
					else {
						img.attr("src", "/images/star-empty.png");
					}

					$(this).replaceWith(img);
				});

				$("#help section.rate span.cast").remove();
				$("#help section.rate span.current").text("Thanks for voting!").show();
				$("#help section.rate span.current").delay(3000).fadeOut(200);
			}
		});
	},

	changeRace: function() {
		app.loadout.race = $("select[name='race']").val();
		app.updateFlashParam();
		this.savePrefs();
	},

	changeGender: function() {
		app.loadout.gender = $("select[name='gender']").val();
		app.updateFlashParam();
		this.savePrefs();
	},

	savePrefs: function() {
		var prefs = '{"race": "' + $("select[name='race']").val() + '", "gender": "' + $("select[name='gender']").val() + '"}';
		setCookie('prefs', prefs, 365);

	},

	domReady: function() {
		$("select[name='race']").change(function() {
			app.changeRace();
		});
		$("select[name='gender']").change(function() {
			app.changeGender();
		});
	},

	lookupLocale: function() {
		if (app.locale.lookups.length > 0) {
			$("head").append("<script type=\"text/javascript\" src=\"http://eu.battle.net/api/wow/item/" + app.locale.lookups[0] + "?jsonp=app.localeCallback&locale=" + app.locale.lang + "\"></script>");
			app.locale.t = setTimeout("app.localeFailed()", 8000);
		}
	},

	localeCallback: function(packet) {
		clearTimeout(app.locale.t);

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
	},

	localeFailed: function() {
		$.ajax({
			url: '/ajax/lookup-locale/',
			type: 'post',
			data: {
				id: app.locale.lookups[0],
				lang: app.locale.lang,
				failed: 1
			},
			dataType : 'JSON',
			success: function(response) {
				if (typeof response.next != 'undefined') {
					if (response.next != 0) {
						app.locale.lookups[0] = response.next;
						setTimeout("app.lookupLocale()", app.locale.interval);
					}
				}
			}
		});
	},

	init: function() {
		$(document).keyup(function(e) {
			// detect Esc button
			if (app.search_open && e.which == 27) {
				app.closeSearch();
			}
		});

		$("#app .items span").click(function() {
			app.beginChangeItem($(this));
		});

		$("#slot_prompt input").keyup(function() {
			if (app.search_prev == $(this).val() || $(this).val() == '') {
				return;
			}

			app.search_prev = $(this).val();

			clearTimeout(app.search_delay);
			app.search_delay = setTimeout("app.lookupItem()", 500);
		});

		if (this.render) {
			this.updateFlashParam();
			this.updateTooltip();
			this.updateShareUrl();

			if (this.loadout.gender) {
				$(".tools select[name='gender']").val(this.loadout.gender);
			}
			if (this.loadout.race) {
				$(".tools select[name='race']").val(this.loadout.race);
			}
		}

		// prefill dropdowns

		var cookie = getCookie('prefs');

		if (typeof cookie != 'undefined') {
			cookie = jQuery.parseJSON(cookie);

			if (this.loadout.ref == "") {
				this.loadout.race = cookie.race;
				this.loadout.gender = cookie.gender;
			}
		}

		$("select[name='race']").val(this.loadout.race);
		$("select[name='gender']").val(this.loadout.gender);

		this.updateFlashParam();

		$("#loadouts").change(function() {
			if ($(this).val() != '') {
				if (app.save_required) {
					if (!confirm('You have made changes!\n\nDo you want to abandom them?')) {
						return;
					}
				}
				document.location.href = "/" + $(this).val();
			}
		});

		$("#help section.rate a").hover(
			function() {
				$(this).prevAll().each(function() {
					var img = $(this).find("img");
					img.attr("data-prev", img.attr("src"));
					img.attr("src", "/images/star.png");
				});

				var img = $(this).find("img");
				img.attr("data-prev", img.attr("src"));
				img.attr("src", "/images/star.png");

				$(this).nextAll().each(function() {
					var img = $(this).find("img");
					img.attr("data-prev", img.attr("src"));
					img.attr("src", "/images/star-empty.png");
				});

				var amount = $(this).prevAll().length + 1;
				$("#help section.rate span.cast .amount").text(amount);
				$("#help section.rate span.cast").show();
				$("#help section.rate span.current").hide();
			},
			function() {
				$(this).prevAll().each(function() {
					var img = $(this).find("img");
					img.attr("src", img.attr("data-prev"));

				});

				var img = $(this).find("img");
				img.attr("src", img.attr("data-prev"));

				$(this).nextAll().each(function() {
					var img = $(this).find("img");
					img.attr("src", img.attr("data-prev"));
				});

				$("#help section.rate span.cast").hide();
				$("#help section.rate span.current").show();
		});


		// get localizations
		this.locale.t = setTimeout("app.lookupLocale()", this.locale.interval);
	}
};

function recordOutboundLink(link, category, action) {
	_gat._getTrackerByName()._trackEvent(category, action);
	setTimeout('document.location = "' + link.href + '"', 100);
}

function setCookie(c_name,value,exdays) {
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
	document.cookie=c_name + "=" + c_value;
}

function getCookie(c_name) {
	var i,x,y,ARRcookies=document.cookie.split(";");
	for (i=0;i<ARRcookies.length;i++) {
		x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
		y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
		x=x.replace(/^\s+|\s+$/g,"");
		if (x==c_name) {
			return unescape(y);
		}
	}
}

slider = {
	cache: {
		urls: [],
		html: []
	},
	slide_duration: 200,
	showing: 0,

	init: function() {
		this.bindHistory();
		this.cache.urls.push(document.location.href);
		//this.cache.html.push($("#slider .frame"));
	},

	bindHistory: function() {
		$(window).bind('popstate', function(e) {
			if (e.state) {
				slider.moveToFrame(location.pathname);
			}
		});
	},

	slideTo: function(href, div, dir) {
		if (history && history.pushState) {
			history.pushState({}, document.title, href);
		}
		else {
			return true; // browser doesn't support fancy HTML5
		}

		// is new frame cached?
		var cached = this.cache.urls.indexOf(href);

		if (cached != -1) {
			this.moveToFrame(cached, dir);
		}
		else {
			$.get(href + "?slide=1", function(data) {
				slider.addToCache(href, data);
				slider.moveToFrame(href, dir);
			});
		}

		return false;
	},

	addToCache: function(url, html) {
		this.cache.urls.push(url);

		var idx = (this.cache.urls.length - 1);
		$("#slider").append("<div class=\"frame\" data-frame=\"" + idx + "\" style=\"display: none;\"></div>");
		$("#slider .frame[data-frame='" + idx + "']").append(html);
	},

	moveToFrame: function(location, dir) {

		if (isNaN(location)) {
			// url - probly from Ajax
			location = this.cache.urls.indexOf(location);
		}

		var scroll = (dir == 'next') ? '-100%' : '100%';
		$("#slider .frame[data-frame='" + this.showing + "']").animate({left: scroll}, this.slide_duration, function() {
			$(this).hide().css("left", "auto");
			$("#slider .frame[data-frame='" + location + "']").show();
			slider.showing = location;
		});
	}

};


$(document).ready(function() {
	app.domReady();
});