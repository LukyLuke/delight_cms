
function SimpleShop() {
	this.quantityCrumb = 'prod';
	this.addtoCrumb = 'add';
	this.__basketPreview = 'basket';
	this.__stateField = 'state_';
	
	this.__basketContainer = '';
	this.__basketContainerList = '';
	this.__entriesContainer = '';
	this.__productsField = '';
	
	this.__requiredFields = '';
	
	this.url = '/delight_hp/';
	this.__useSimpleBasket = false;
};

SimpleShop.prototype = {
	
	/**
	 * Check the Quantity inside the given Element from the Event
	 * 
	 * @param {Keypress-Event} e The KeyPress-Event on a QuantityField
	 */
	checkQuantity : function(e) {
		var ok = false;
		e = (!e) ? window.event : e;
		try {
			var i,pass = [9,8,35,36,37,39,9,48,49,50,51,52,53,54,55,56,57];
			var o = e.target ? e.target : e.srcElement;
			var k = (e.which) ? e.which : e.keyCode;
			for (i = 0; i < pass.length; i++) {
				if (pass[i] == k) {
					ok = true;
					break;
				}
			}
			if (!ok && (typeof(e.preventDefault) == 'function')) {
				e.preventDefault();
			} else if (!ok) {
				e.preventDefault = false;
			}
			if (k == 38) { o.value = parseInt(o.value)+1;}
			if (k == 40) { o.value = parseInt(o.value)-1;}
			o.value = (parseInt(o.value) < 0) ? 0 : parseInt(o.value);
		} catch (ex) {
			if (typeof(console) != 'undefined') { console.debug(e); console.debug(ex); }
			ok = true;
		}
		return ok;
	},
	
	/**
	 * Add the Product in given Quantity to the Basket
	 * 
	 * @param {ClickEvent} e The ClickEvent
	 * @access public
	 */
	addToBasket : function(e) {
		try {
			e = (!e) ? window.event : e;
			var t = e.target ? e.target : e.srcElement;
			ss._addToBasket(parseInt(t.getAttribute('name').replace(/[^0-9]+/, '')));

		} catch (ex) {
			if (typeof(console) != 'undefined') { console.debug(e); console.debug(ex); }
		}
	},
	
	/**
	 * Add Events to all needed elements
	 * 
	 * @param {string} qf NamePart of Quantity-Fields
	 * @param {string} bb NamePart of Basket-Buttons/Links
	 * @param {string} bv Name of the Basket-View Container
	 * @access public
	 */
	connectFields : function(qf, bb, bv) {
		var i,fld,f = document.getElementsByTagName('input');
		var name = 'keypress';
		this.quantityCrumb = (!qf) ? this.quantityCrumb : qf;
		this.addtoCrumb = (!bb) ? this.addtoCrumb : bb;
		for (i = 0; i < f.length; i++) {
			fld = f[i];
			if ( (typeof(fld.getAttribute('name')) == 'string') && (fld.getAttribute('name').substring(0, this.quantityCrumb.length) == this.quantityCrumb)) {
				this._attachEvent(name, fld, this.checkQuantity);
				
			} else if ( (typeof(fld.getAttribute('name')) == 'string') && (fld.getAttribute('name').substring(0, this.addtoCrumb.length) == this.addtoCrumb)) {
				this._attachEvent('click', fld, this.addToBasket);
				
			}
		}
		
		this.__basketPreview = bv;
	},
	
	/**
	 * Define the ID's from the Entries-Container and from the Basket-Container
	 * The BasketContainer ist the one, in which the whole appointment-process fits in
	 * 
	 * Normally the Entries-Container is visible and the BasketContainer is not. If the Users
	 * clicks to show the Basket, the EntriesContainer is hidden and the BasketContainer is visible
	 * 
	 * @param {string} e The ID from the EntriesContainer
	 * @param {string} b The ID from the BasketContainer
	 * @param {string} bl The ID from the entriesList inside the BasketContainer
	 * @param {string} pr Field where wo store the selected Products inside the BasketFormular
	 * @param {string} st Field-Section for the different State-Layers
	 * @access public
	 */
	connectContainers : function(e, b, bl, pr, st) {
		this.__entriesContainer = document.getElementById(e);
		this.__basketContainer = document.getElementById(b);
		this.__basketContainerList = document.getElementById(bl);
		this.__productsField = document.getElementsByName(pr);
		this.__stateField = st;
		this.__productsField = (this.__productsField.length > 0) ? this.__productsField[0] : '';
		this.hideBasket();
	},
	
	/**
	 * Set all Fields which the user has to fill out for an Order-Request
	 * 
	 * @param {string} fields Commaseperated List with all Fieldnames (names from input-fields)
	 */
	setRequiredFields : function(fields) {
		this.__requiredFields = '0,' + fields + ',';
	},
	
	/**
	 * Load the last Basket if there is one
	 * 
	 * @access public
	 */
	loadLast : function() {
		this._addToBasket(0);
	},
	
	/**
	 * Show Simple Baket or the Extended one
	 * 
	 * @param {boolean} isSimple If a simple basket should be showed, set to true.
	 * @access public
	 */
	simpleBasket : function(isSimple) {
		if (typeof(isSimple) != 'boolean') {
			isSimple = false;
		}
		this.__useSimpleBasket = isSimple;
	},
	
	/**
	 * Hide the Basket/Appointment Container and show the EntriesContainer
	 * 
	 * @access public
	 */
	hideBasket : function() {
		var bp;
		if (typeof(this.__basketContainer) != 'undefined') {
			this.__basketContainer.style.display = 'none';
		}
		if (typeof(this.__entriesContainer) != 'undefined') {
			this.__entriesContainer.style.display = 'block';
		}
		bp = this._getBasketContainer();
		if (bp != null) {
			bp.style.display = 'block';
		}
		bp = this._getSubmittedContainer('submitted');
		if (bp != null) {
			bp.style.display = 'none';
		}
		bp = this._getSubmittedContainer('error');
		if (bp != null) {
			bp.style.display = 'none';
		}
		bp = this._getSubmittedContainer('failed');
		if (bp != null) {
			bp.style.display = 'none';
		}
	},
	
	/**
	 * Hide the EntriesContainer and show the Basket/Appointment Container
	 * 
	 * @access public
	 */
	showBasket : function() {
		if (this.__productsField.value.length > 0) {
			var bp;
			if (typeof(this.__basketContainer) != 'undefined') {
				this.__basketContainer.style.display = 'block';
			}
			if (typeof(this.__entriesContainer) != 'undefined') {
				this.__entriesContainer.style.display = 'none';
			}
			bp = this._getBasketContainer()
			if (bp != null) {
				bp.style.display = 'none';
			}
			bp = this._getSubmittedContainer('submitted');
			if (bp != null) {
				bp.style.display = 'none';
			}
			bp = this._getSubmittedContainer('error');
			if (bp != null) {
				bp.style.display = 'none';
			}
			bp = this._getSubmittedContainer('failed');
			if (bp != null) {
				bp.style.display = 'none';
			}
		}
	},
	
	submitOrder : function(fn) {
		var required=true,send,elem,form = document.getElementsByName(fn);
		if (form.length > 0) {
			form = form[0];
			send = 'basket=true';

			if (this.__productsField.value.length <= 0) {
				required = false;
			}
			
			for (var i = 0; i < form.elements.length; i++) {
				elem = form.elements[i];
				if ( ( (elem.tagName != 'INPUT') && (elem.tagName != 'TEXTAREA') ) || (elem.getAttribute('type') == 'submit') || (elem.getAttribute('type') == 'button') ) {
					continue;
				}
				if ( (this.__requiredFields.indexOf(',' + elem.name + ',') > 0) && (elem.value.length <= 0) ) {
					required = false;
					if ( (typeof(document.getElementById(elem.name + '_req')) == 'undefined') || (document.getElementById(elem.name + '_req') == null)) {
						var r = document.createElement('span');
						r.innerHTML = '*';
						r.style.fontWeight = 'bold';
						r.style.color = '#A50000';
						r.id = elem.name + '_req';
						this.__insertAfter(r, elem);
					}
				} else {
					if ( (typeof(document.getElementById(elem.name + '_req')) != 'undefined') && (document.getElementById(elem.name + '_req') != null)) {
						var r = document.getElementById(elem.name + '_req');
						r.parentNode.removeChild(r);
					}
				}
				send += '&'+elem.name+'='+escape(elem.value);
			}
			
			var req = this._getTransport();
			var obj = this;
			if (required && req) {
				req.open('POST', location.href, true);
				req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=iso-8859-15");
				req.setRequestHeader("X-Application", "delight cms plugin");
				req.send(send);
				req.onreadystatechange = function() {
					if ( (req.readyState == 4) && (req.status == 200) ) {
						if (req.getResponseHeader('Content-type') != 'application/json') {
							try {
								// cut "while(1){};" from return-value
								var json = eval('(' + req.responseText.substring(11) + ')');
							} catch (e) {
								var json = null;
								obj.__error(e.getMessage());
							}

							if (json[0].error) {
								obj.__error(json[0].errormessage);
							} else {
								var bp;
								if (typeof(obj.__basketContainer) != 'undefined') {
									obj.__basketContainer.style.display = 'none';
								}
								if (typeof(obj.__entriesContainer) != 'undefined') {
									obj.__entriesContainer.style.display = 'none';
								}
								bp = obj._getBasketContainer()
								if (bp != null) {
									bp.style.display = 'none';
								}
								bp = obj._getSubmittedContainer('submitted');
								if (bp != null) {
									bp.style.display = 'none';
								}
								bp = obj._getSubmittedContainer('error');
								if (bp != null) {
									bp.style.display = 'none';
								}
								bp = obj._getSubmittedContainer('failed');
								if (bp != null) {
									bp.style.display = 'none';
								}
								bp = obj._getSubmittedContainer(json[0].state);
								if (bp != null) {
									bp.style.display = 'block';
								}
							}
						}
						
					} else if ( (req.readyState == 4) && (req.status != 200) ) {
						obj.__error("Failed to add. Script ends with State " + req.status);
					}
				}
			}
		}
		return false;
	},
	
	/**
	 * Show the Basket based on the given Data-Object
	 * This Function should be called only after/by a JSON-Server Request
	 * 
	 * @param {Object} data Data-Object to show inside the Basket
	 * @access public
	 */
	showBasketData : function(data) {
		var cont = this._getBasketContainer();
		if ( (data != null) && (cont != null) ) {
			var html='',price=0, num=0, currency='';
			var numCont  = document.getElementById(this.__basketPreview + 'num');
			var prodCont = document.getElementById(this.__basketPreview + 'products');
			var priceCont = document.getElementById(this.__basketPreview + 'price');
			var products = '';
			var row,rTitle,rQuantity,rPrice,eoClass,rowcount;
			
			while (this.__basketContainerList.rows.length > 1) {
				this.__basketContainerList.deleteRow(1);
			}
			rowcount = this.__basketContainerList.rows.length;
			
			for (var i = 0; i < data.length; i++) {
				try {
					if (!this.__useSimpleBasket) {
						//html += '<tr><td class="product">' + data[i].quantity + 'x ' + data[i].name + '</td><td style="align:right;" class="quantity">' + data[i].price + '</td></tr>';
						html += '<span class="product">' + data[i].quantity + 'x ' + data[i].name + ' (' + (data[i].price * data[i].quantity).toFixed(2) + ' ' + data[i].currency + ')</span><br />';
					}
					
					eoClass = (rowcount%2 == 0) ? 'sseven' : 'ssodd';
					row = this.__basketContainerList.insertRow(rowcount++);
					rTitle = row.insertCell(0);
					rTitle.innerHTML = data[i].name;
					rTitle.className = 'sstitle ' + eoClass;
					rQuantity = row.insertCell(1);
					rQuantity.innerHTML = data[i].quantity;
					rQuantity.className = 'ssquantity ' + eoClass;
					rPrice = row.insertCell(2);
					rPrice.innerHTML = (data[i].price * data[i].quantity).toFixed(2) + ' ' + data[i].currency;
					rPrice.className = 'ssprice ' + eoClass;
					
					products += data[i].num + ':' + data[i].quantity + ';';
						
					price += (data[i].price * data[i].quantity);
					num += data[i].quantity;
					currency = data[i].currency;
				} catch (ex) {}
			}
			if ( (typeof(this.__productsField) != 'undefined') && (this.__productsField != '') ) {
				this.__productsField.value = products;
			}
			
			row = this.__basketContainerList.insertRow(rowcount++);
			rTitle = row.insertCell(0);
			rTitle.innerHTML = 'Total:';
			rTitle.className = 'sstitle ssfooter';
			rQuantity = row.insertCell(1);
			rQuantity.innerHTML = num;
			rQuantity.className = 'ssquantity ssfooter';
			rPrice = row.insertCell(2);
			rPrice.innerHTML = price.toFixed(2) + ' ' + currency;
			rPrice.className = 'ssprice ssfooter';
			
			if (typeof(numCont) != 'undefined') {
				numCont.innerHTML = num;
			}
			
			if (typeof(numCont) != 'undefined') {
				priceCont.innerHTML = price.toFixed(2) + ' ' + currency;
			}
			
			if (!this.__useSimpleBasket && (typeof(prodCont) != 'undefined')) {
				//prodCont.innerHTML = '<table cellpadding="0" cellspacing="0" style="width:100%;" class="basket">'+html+'</table>';
				prodCont.innerHTML = html;
			}
		}
	},
	
	__insertAfter : function(n, e) {
		var p = e.parentNode;
		if (e.nextSibling) {
			p.insertBefore(n, e.nextSibling);
		} else {
			p.appendChild(n);
		}
	},
	
	__error : function(str) {
		alert("ERROR: " + str);
	},
	
	/**
	 * Get the Container where the Basket should be showed
	 * Return a value of NULL if there is no such Element
	 * 
	 * @return {DOMElement} The BasketElement or null
	 * @access protected
	 */
	_getBasketContainer : function() {
		var b = document.getElementById(this.__basketPreview);
		return (typeof(b) != 'undefined') ? b : null;
	},
	
	/**
	 * Get teh Container from given State-Message
	 * 
	 * @return {DOMElement} The StateElement or null
	 * @access protected
	 */
	_getSubmittedContainer : function(state) {
		var b = document.getElementById(this.__stateField + state);
		return (typeof(b) != 'undefined') ? b : null;
	},
	
	/**
	 * Add the Product with the given ID to the basket
	 * 
	 * @param {integer} id The ProductId
	 * @access protected
	 */
	_addToBasket : function(id) {
		var req = this._getTransport();
		var obj = this;
		if (req) {
			if ((typeof(id) == 'undefined') || (id <= 0) ) {
				var send = 'prod=0&q=0';
			} else {
				var send = 'prod='+id+'&q='+document.getElementById(this.quantityCrumb+id).value;
			}
			req.open('POST', location.href, true);
			req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=iso-8859-15");
			req.setRequestHeader("X-Application", "delight cms plugin");
			req.send(send);
			req.onreadystatechange = function() {
				if ( (req.readyState == 4) && (req.status == 200) ) {
					if (req.getResponseHeader('Content-type') != 'application/json') {
						try {
							// while(1){};
							var json = eval('(' + req.responseText.substring(11) + ')');
						} catch (e) {
							if (typeof(console) != 'undefined') { console.debug(req.responseText.substring(11)); console.debug(e); }
							var json = null; 
						}
						obj.showBasketData(json);
					}
					
				} else if ( (req.readyState == 4) && (req.status != 200) ) {
					if (typeof(console) != 'undefined') { console.error("Failed to add. Script ends with State " + req.status); }
				}
			}
		}
	},
	
	/**
	 * Get a XMLHttpRequest to pass variables on to the Server
	 * 
	 * @return {object} A XMLHttpRequest or Microsoft.XMLHTTP or Microsoft.XMLHTTP Object
	 */
	_getTransport : function() {
		var trans = null;
		try {
			trans = new XMLHttpRequest();
		} catch (e) {
			try {
				trans = new ActiveXObject('Msxml2.XMLHTTP');
			} catch (e) {
				try {
					trans = new ActiveXObject('Microsoft.XMLHTTP');
				} catch (e) {
					if (typeof(console) != 'undefined') { console.error("No XMLHTTP neither a XMLHttpRequest could be initialized"); }
					trans = false;
				}
			}
		}
		return trans;
	},
	
	/**
	 * Attach the given Event on the Element
	 * 
	 * @param {string} ev The Event to catch on the Element
	 * @param {object} obj The Element to catch the Event on
	 * @param {function} fnc The Function to be called while catching the Event
	 */
	_attachEvent : function(ev, obj, fnc) {
		if (obj.addEventListener) {
			obj.addEventListener(ev, fnc, false);
		} else {
			obj.attachEvent('on' + ev, fnc);
		}
	},
	
	// Nonsens
	n : null
};

var ss = new SimpleShop();
