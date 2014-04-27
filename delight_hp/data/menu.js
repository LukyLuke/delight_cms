(function() {
	/**
	 * Functionality for the responsive menu.
	 */
	var ResponsiveMenu = Class.create({
		initialize: function(id) {
			this.container = id;
			this.initCount = 0;
			this.connected = false;
			this.tryConnect();
		},
		
		tryConnect: function() {
			var t = this;
			window.setTimeout(function() {
				t.connect();
				t.initCount++;
				if ((t.initCount < 20) && !t.connect) {
					t.tryConnect();
				}
			}, 100);
		},
		
		connect: function() {
			if (!$(this.container)) {
				return;
			}
			var t = this;
			this.connected = true;
			
			// Hide all sumbenus.
			$(this.container).select('.submenu').invoke('toggle', false);
			
			// Connect all submenu enttries to their submenu containers to show them.
			var elems = $(this.container).select('.submenu-toggle');
			elems.each(function(item) {
				var parent = $(item.parentNode);
				var submenu = parent.siblings().detect(function(n) { return $(n).hasClassName('submenu'); });
				if (!submenu) {
					item.innerHTML = '&nbsp;';
					return;
				}
				
				item.observe('click', function(ev) {
					var menu = $(ev.target.parentNode).siblings().detect(function(n) { return $(n).hasClassName('submenu'); });
					if (menu) {
						menu.toggle();
					}
				});
			});
			
			// Connect the main menu entry to show the men.
			var main = $(this.container + '-toggle');
			if (main) {
				main.observe('click', function(ev) {
					var target = ev.findElement('#' + t.container + '-toggle');
					if ($(t.container).getStyle('display') != 'inline-block') {
						$(t.container).setStyle({
							display: 'inline-block',
							top: target.getHeight() + 'px'
						});
					} else {
						$(t.container).setStyle({display: 'none'});
					}
				});
			}
			
			// Check for a "responsive menu" for tablets.
			this.connectTabletsMenu();
		},
		
		connectTabletsMenu: function() {
			var menu = $('content-menu'), toggle = $('content-menu-toggle');
			if (menu && toggle) {
				menu.toggle(true);
				toggle.observe('click', function(ev) {
					menu.toggle();
				});
			}
		},
		
		last_dummy: null
	});
	
	window.responsiveMenu = new ResponsiveMenu('responsive-menu');
})();