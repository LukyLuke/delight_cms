
// The News-Ticker
function newsTicker() {
	this._direction = 0; // 0: bottom->up, 1:up->bottom, 2: right->left, 3: left->right
	this._delimiter = '&nbsp;';
	this._interval = 250;
	this._stopOnHover = true;
	this._repeat = 2;
	this._step = 1;
	this._entryStyle = '';
	this._entryClass = '';
	this._news = new Array();
	this._run = true;
	this._varname = '';
	this._tickerId = 2;
	this._minNewsNum = 5;
	this._tickerDimension = {width:0,height:0};
}

newsTicker.prototype = {
	set direction(v) {
		v = v.toLowerCase();
		if (v == 'up') this._direction = 0;
		else if (v == 'down') this._direction = 1;
		else if (v == 'left') this._direction = 2;
		else if (v == 'right') this._direction = 3;
		else this._direction = 2;
	},
	set delimiter(v) { this._delimiter = v; },
	set interval(v) { this._interval = parseInt(v); },
	set stopOnHover(v) { this._stopOnHover = (parseInt(v) > 0); },
	set repeat(v) { this._repeat = parseInt(v); },
	set entryStyle(v) { this._entryStyle = v; },
	set entryClass(v) { this._entryClass = v; },
	set step(v) { this._step = parseInt(v); },
	set minNewsNum(v) { this._minNewsNum = parseInt(v); },
	
	get direction() {
		if (this._direction == 0) return 'up';
		else if (this._direction == 1) return 'down';
		else if (this._direction == 2) return 'left';
		else if (this._direction == 3) return 'right';
		else return left;
	},
	get delimiter() { return this._delimiter; },
	get interval() { return this._interval; },
	get stopOnHover() { return this._stopOnHover; },
	get repeat() { return this._repeat; },
	get entryStyle() { return this._entryStyle; },
	get entryClass() { return this._entryClass; },
	get step() { return this._step; },
	get minNewsNum() { return this._minNewsNum; },
	
	add : function(s) {
		this._news.push('<span'+(this._entryStyle.length>0 ? ' style="'+this._entryStyle+'"' : '')+(this._entryClass.length>0 ? ' class="'+this._entryClass+'"' : '')+'>'+s+'</span>');
	},
	
	start : function() {
		newsTickerIntervalList[this._tickerId] = setInterval(this._varname+'.tick()', this._interval);
	},
	
	stop : function() {
		clearInterval(newsTickerIntervalList[this._tickerId]);
	},
	
	show : function(elem, varname) {
		this._varname = varname;
		var i;
		newsTickerIntervalList.push(null);
		this._tickerId = newsTickerIntervalList.length-1;
		var scrollVertical = (this._direction <= 1);
		var tag = scrollVertical ? 'div' : 'nobr';
		var delim = scrollVertical ? '<br />'+this._delimiter+'<br />' : '&nbsp;'+this._delimiter+'&nbsp;';
		var onHover = this._stopOnHover ? ' onmouseover="'+this._varname+'.stop();" onmouseout="'+this._varname+'.start();"':'';
		if (this._news.length > 0) {
			var tmpCont = delim + this._news.join(delim);
		} else {
			var tmpCont = '&nbsp;';
		}
		var cont = tmpCont;
		if (this._news.length > this._minNewsNum) {
			for (i = 0; i < this._repeat; i++) {
				cont += tmpCont;
			}
		}
		cont = '<div style="position:relative;text-align:left;overflow:hidden;width:100%;height:100%;"><'+tag+'>'+
		  '<div id="ticker'+this._tickerId+'" style="position:relative;" '+onHover+'>'+cont+'</div>'+
		  '</'+tag+'></div>';
		if ((elem != null) && document.getElementById(elem)) {
			document.getElementById(elem).innerHTML = cont;
		} else {
			document.write(cont);
		}
		if ( (this._news.length > 0) && (this._news.length > this._minNewsNum) ) {
			this.start();
		}
	},
	
	waitForContentInElem : function(elem, varname) {
		if ((elem != null) && document.getElementById(elem)) {
			var e = document.getElementById(elem);
			try {
				eval(e.innerHTML);
				eval(varname + '.show("'+elem+'", "'+varname+'");');
				if (e.style.display == 'none') e.style.display = 'block';
				if (e.style.visibility == 'hidden') e.style.visibility = 'visible';
			} catch (e) {
				setTimeout(varname+'.waitForContentInElem("'+elem+'", "'+varname+'")', 200);
			}
		}
	},
	
	// let the newsTicker tick
	tick : function() {
		var i,position;
		var step,cpos,prop;
		var ticker = document.getElementById('ticker'+this._tickerId);
		if (this._tickerDimension.width <= 0) {
			this._tickerDimension = this.getTickerDimension(ticker);
		}
			
		switch (this._direction) {
			case 0:
				step = -1;
				cpos = this._tickerDimension.height;
				prop = 'top';
				break;
			case 1:
				step = 1;
				cpos = this._tickerDimension.height;
				prop = 'top';
				break;
			case 2:
				step = 1;
				cpos = this._tickerDimension.width;
				prop = 'left';
				break;
			case 3:
				step = -1;
				cpos = this._tickerDimension.width;
				prop = 'left';
				break;
		}
			
		// get the current position
		position = parseInt(ticker.style[prop]);
		if (isNaN(position)) position = 0;
		position += this._step * step;

		// calculate the position on which we can set the ticker back to '0'
		offset = (cpos/(this._repeat + 1));
		offset -= ((this._repeat + 1) * 4)

		/*if (Math.abs(position) > offset) {
			alert('pos: '+position + '\nOffset: ' + offset + '\nWidth: ' + cpos);
		}*/
		
		// Calculate the new position
		switch (this._direction) {
			case 0: // up
				position = (Math.abs(position) > offset) ? 0 : position;
				break;
			case 1: // down
				position = (position > 0) ? position-offset : position;
				break;
			case 2: // left
				position = (position > 0) ? position-offset : position;
				break;
			case 3: // right
				position = (Math.abs(position) > offset) ? 0 : position;
				break;
		}
		
		// append calculated position
		ticker.style[prop] = position + "px";
	},
	
	getTickerDimension : function(element) {
		if (!document.getElementById(element.id + 'size')) {
			var d = document.createElement('div');
			d.id = element.id + 'size';
			d.style.display = 'none';
			d.style.visibility = 'hidden';
			d.style.position = 'absolute';
			d.style.padding = '0';
			d.style.margin = '0';
			d.style.whiteSpace = 'nowrap';
			d.innerHTML = element.innerHTML;
			d = document.body.appendChild(d);
		} else {
			var d = document.getElementById(element.id + 'size');
		}
		return this.getDimensions(d);
	},
	
	// This function comes from prototype-Javascript-Library
	// see http://www.prototypejs.org/ for more informations
	// modifications are made for use in here
	getDimensions: function(element) {
		var display = element.style.display;
		if (display != 'none' && display != null) // Safari bug
			return {width: element.offsetWidth, height: element.offsetHeight};

		// All *Width and *Height properties give 0 on elements with display none,
		// so enable the element temporarily
		var els = element.style;
		var originalVisibility = els.visibility;
		var originalPosition = els.position;
		var originalDisplay = els.display;
		els.visibility = 'hidden';
		els.position = 'absolute';
		els.display = 'block';
		var originalWidth = element.clientWidth;
		var originalHeight = element.clientHeight;
		els.display = originalDisplay;
		els.position = originalPosition;
		els.visibility = originalVisibility;
		return {width: originalWidth, height: originalHeight};
	},
	
};
var newsTickerIntervalList = new Array();
