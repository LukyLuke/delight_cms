// UID-Generator for LoadingID
if (typeof uidGenerator == 'undefined') {
	function uidGenerator() {
		var lastId = (new Date()).getTime();
		this.generateId = function() {
			var i = (new Date()).getTime();
			lastId = (lastId < i) ? i : lastId + 1;
			return lastId;
		};
		return this;
	}
}
// Check for global var "guid" which should be from type "uidGenerator"
// If it's not, reinitialize it with no check what inside "guid" is stored
if ((typeof guid != 'object') || (typeof guid.generateId != 'function')) {
	var guid = new uidGenerator();
}

// The Atom-Parser
function atomReader() {
	this._IEDateParseMode = false;
	this._feed = '';
	this._line = '';
	this._content = '';
	this._timeout = 1000;
	this._maxtries = 10;
	this._moreinfo = 'Mehr Informationen';
	this._morelink = '';
	this.xmlreq = null;
	this.feedXml = '';
	this.feedUID = guid.generateId();
	this.feedUID1 = guid.generateId();

	this.DATE_DEFAULT = 0;
	this.DATE_SHORT = 1;
	this.DATE_EXTENDED = 2;
}

atomReader.prototype = {
	feed : function(v) { if (typeof(v) == 'undefined') { return this._feed;} else {this._feed = v;} },
	line : function(v) { if (typeof(v) == 'undefined') { return this._line;} else {this._line = this.unescapeHtmlForJavaScript(v);} },
	content : function(v) { if (typeof(v) == 'undefined') { return this._content;} else {this._content = this.unescapeHtmlForJavaScript(v);} },
	timeout : function(v) { if (typeof(v) == 'undefined') { this._timeout *= 2; return this._timeout/2;} else {this._timeout = parseInt(v);} },
	moreinfo : function(v) { if (typeof(v) == 'undefined') { return this._moreinfo;} else {this._moreinfo = v;} },
	morelink : function(v) { if (typeof(v) == 'undefined') { return this._morelink;} else {this._morelink = this.unescapeHtmlForJavaScript(v);} },
	maxnews : function(v) { if (typeof(v) == 'undefined') { return this._maxtries;} else {this._maxtries = parseInt(v);} },

	parseAndWrite : function() {
		document.write('<div id="atomLoading'+this.feedUID+'" style="text-align:center;color:#909090;padding:5px;">loading feed</div>');
		this.fetchFeed();
	},

	parseAndReplace : function(elem) {
		if (elem) {
			var l = document.createElement('div');
			l.setAttribute('id', 'atomLoading'+this.feedUID);
			l.style.textAlign = 'center';
			l.style.color = '#090909';
			l.style.padding = '5px';
			elem.parentNode.replaceChild(l, elem);
			this.fetchFeed();
		}
	},

	parseFeedXML : function() {
		var tmp,val='',entry,title,cont,shortcont,plain,date,entries,link,entrylink;
		var more=this._moreinfo,mlink=this._morelink;

		// Inline-Function to shorten the current content by words to max "p1" chars (called as function by String.replace(/.../, stripRegexWordContent))
		function stripRegexWordContent(found, p1) {
			var j,max=parseInt(p1),back="",words=plain.split(" "),cur;
			for (j = 0; j < words.length; j++) {
				cur = words[j];
				if ((back.length <= 0) && (max > 0)) {
					back = cur;
				} else if (back.length + cur.length <= max) {
					back += " " + cur;
				} else {
					break;
				}
				if (link != undefined) {
					back += link;
				}
			}
			return back;
		}

		if (this.feedXml != null) {
			entries = this.feedXml.getElementsByTagName('entry');
			var max = entries.length;
			for (var i = 0; i < (max>this._maxnews?this._maxnews:max); i++) {
				entry = entries[i];
				title = entry.getElementsByTagName('title')[0].firstChild.data;
				date = entry.getElementsByTagName('published')[0].firstChild.data;
				shortcont = entry.getElementsByTagName('summary')[0].getElementsByTagName('div')[0];
				if (shortcont.innerHTML == undefined) {
					shortcont = shortcont.xml;
				} else {
					shortcont = shortcont.innerHTML;
				}
				cont = entry.getElementsByTagName('content')[0].getElementsByTagName('div')[0];
				if (cont.innerHTML == undefined) {
					cont = cont.xml;
				} else {
					cont = cont.innerHTML;
				}
				plain = cont.replace(/<\/?[^>]+>/gi, '').replace(/\s\s+/g, ' ');
				link = entry.getElementsByTagName('link');
				if ((link != undefined) && (link.length >= 1) && (link[0].getAttribute('href').length > 0)) {
					if (mlink.indexOf('[NEWS_LINK]')>=0) {
						link = mlink.replace(/\[NEWS_LINK\]/g, link[0].getAttribute('href'));
					} else {
						link = '<br /><a href="'+link[0].getAttribute('href')+'">'+more+'</a>';
					}
					shortcont += link;
				}
				tmp = this._content;
				// First we replace the Content, so we can perhaps reference to the title and date inside the [NEWS_LINK]
				tmp = tmp.replace(/\[NEWS_SHORT\]/g, shortcont);
				tmp = tmp.replace(/\[NEWS_TEXT_SHORT\]/g, shortcont);
				tmp = tmp.replace(/\[NEWS_TEXT\]/g, cont);
				tmp = tmp.replace(/\[NEWS_TEXT\:(\d+)\]/g, stripRegexWordContent);
				tmp = tmp.replace(/\[NEWS_TITLE\]/g, title);
				tmp = tmp.replace(/\[NEWS_DATE\]/g, this.parseDate(date, this.DATE_DEFAULT));
				tmp = tmp.replace(/\[NEWS_DATE_SHORT\]/g, this.parseDate(date, this.DATE_SHORT));
				tmp = tmp.replace(/\[NEWS_DATE_EXTENDED\]/g, this.parseDate(date, this.DATE_EXTENDED));
				val += this._line.replace(/\[TEXT\]/g, tmp);
			}
		}
		document.getElementById('atomLoading'+this.feedUID).parentNode.innerHTML = val;
	},

	parseDate : function(date, format) {
		//var e=/(\d\d\d\d)\-(\d\d)\-(\d\d)T(\d\d)\:(\d\d)\:(\d\d)\+(\d\d)\:(\d\d)/.exec(date);
		//var d = new Date(parseInt(e[1]),parseInt(e[2])-1,parseInt(e[3]),parseInt(e[4]),parseInt(e[5]),parseInt(e[6]));
		// IE is not able to parse ISO 8601 dates. We must replace "-" with "/" and remove ":" in Timezone
		if (this._IEDateParseMode) {
			var e=/(\d\d\d\d)\-(\d\d)\-(\d\d)T(\d\d)\:(\d\d)\:(\d\d)(\+|\-)(\d\d)\:(\d\d)/.exec(date);
			date = e[1]+'/'+e[2]+'/'+e[3]+'T'+e[4]+':'+e[5]+':'+e[6]+e[7]+e[8]+e[9];
		}
		var dt = new Date(date);
		switch (format) {
		case this.DATE_SHORT:
			var y = dt.getUTCFullYear();
			var m = dt.getUTCMonth() + 1;
			var d = dt.getUTCDate();
			m = m < 10 ? '0'+m : m;
			d = d < 10 ? '0'+d : d;
			//return y+'-'+m+'-'+d;
			return dt.toLocaleDateString();
			break;
		case this.DATE_DEFAULT:
			var days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
			var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
			var y = dt.getUTCFullYear();
			var m = months[ dt.getUTCMonth() ];
			var d = dt.getUTCDate() + 1;
			var td = days[ dt.getUTCDay() ];
			var h = dt.getUTCHours();
			var i = dt.getUTCMinutes();
			
			m = m < 10 ? '0'+m : m;
			d = d < 10 ? '0'+d : d;
			h = h < 10 ? '0'+h : h;
			i = i < 10 ? '0'+i : i;
			//return td+', '+d+' '+m+' '+y+' '+h+':'+i+' GMT';
			return dt.toLocaleDateString()+' '+h+':'+i;
			break;
		case this.DATE_EXTENDED:
			return dt.toLocaleString();
			break;
		}
	},

	fetchFeed : function() {
		this.xmlreq = this.createRequest();
		if (this.xmlreq) {
			this.xmlreq.onreadystatechange = this.checkRequestState.doBind(this);
			this.xmlreq.open('GET', this._feed.replace(/&amp;/g, '&'), true);
			this.xmlreq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=iso-8859-15");
			this.xmlreq.setRequestHeader("Accept", "text/atom+xml");
			this.xmlreq.setRequestHeader("X-Requested-With", "XMLHttpRequest - atomReader.js");
			this.xmlreq.setRequestHeader("X-Requested-From", window.location.href);
			// Override the MimeType so we can receive text/atom+xml as an XML
			if (this.xmlreq.overrideMimeType) {
				this.xmlreq.overrideMimeType("text/xml");
			}
			if ((navigator.userAgent.match(/Gecko\/(\d{4})/) || [0,2005])[1] < 2005) {
				this.xmlreq.setRequestHeader("Connection", "close");
			}
			this.xmlreq.send(null);
		}
	},

	checkRequestState : function() {
		if ( (this.xmlreq.readyState == 4) && (this.xmlreq.status == 200) ) {
			this.xmlreq.onreadystatechange = function(){};
			try {
				this.feedXml = new ActiveXObject("Microsoft.XMLDOM");
				this.feedXml.loadXML(this.xmlreq.responseText);
				this._IEDateParseMode = true;
			} catch (notms) {
				this.feedXml = this.xmlreq.responseXML.documentElement;
				this._IEDateParseMode = false;
			}
			this.xmlreq = null;
			this.parseFeedXML();
		} else if ( (this.xmlreq.readyState == 4) && (this.xmlreq.status != 200) ) {
			var t = this._timeout;
			if (t/1000 < Math.pow(2, this._maxtries)) {
				this.replaceLoadingText('Error while loading<br/>Next try in ' + (t/1000) + ' seconds');
				setTimeout(this.fetchFeed.doBind(this), t);
			} else {
				this.replaceLoadingText('Feed is unreachable');
			}
		}
	},

	createRequest : function() {
		var req = false;
		if (!req) { try { var req = new XMLHttpRequest(); } catch (trymicrosoft) { req = false; } };
		if (!req) { try { var req = new ActiveXObject("Msxml2.XMLHTTP"); } catch (othermicrosoft) { req = false; } };
		if (!req) { try { var req = new ActiveXObject("Microsoft.XMLHTTP"); } catch (failed) { req = false; } };
		return req;
	},

	replaceLoadingText : function(v) {
		document.getElementById('atomLoading'+this.feedUID).innerHTML = v;
	},

	removeLoading : function() {
		var l = document.getElementById('atomLoading'+this.feedUID);
		l.parentNode.removeChild(l);
	},

	unescapeHtmlForJavaScript : function(v) {
		v = v.replace(/&gt;/g, '>');
		v = v.replace(/&lt;/g, '<');
		v = v.replace(/&amp;/g, '&');
		v = v.replace(/&#34;/g, '"');
		return v;
	},

	nonsens : null
}

// Functions from Prorotype-JS-Library we need
// These fnctins are overriden just to be sure we can use newer prototype-libraries
Function.prototype.doBind = function() {
  var __method = this, args = $toA(arguments), object = args.shift();
  return function() {
    return __method.apply(object, args.concat($toA(arguments)));
  }
}
var $toA = Array.from = function(iterable) {
  if (!iterable) return [];
  if (iterable.toArray) {
    return iterable.toArray();
  } else {
    var results = [];
    for (var i = 0, length = iterable.length; i < length; i++)
      results.push(iterable[i]);
    return results;
  }
}