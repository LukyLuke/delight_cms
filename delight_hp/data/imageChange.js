function ImageChange() {};
ImageChange.prototype = {
	contA: null,
	contB: null,
	timeout: 3,
	fadeTime: 2,
	fadeStep: 2,
	random: false,
	images: [],
	step: 1,
	ival: null,
	incA: false,
	imgCounter: 0,
	is_ie6: false,

	setContainer: function(a, b) {
		if ((a != undefined) && (document.getElementById(a) != undefined)) this.contA = document.getElementById(a);
		if ((b != undefined) && (document.getElementById(b) != undefined)) this.contB = document.getElementById(b);
		if (navigator.appVersion.indexOf('MSIE 6') > 0) {
			this.is_ie6 = true;
		}
	},
	setTimeout: function(t) {
		this.timeout = parseInt(t);
	},
	setFadeTime: function(t) {
		this.fadeTime = parseInt(t);
	},
	useRandom: function(r) {
		this.random = false;
		if (r) this.random = true;
	},
	addImage: function(img) {
		if (img != '') this.images.push(img);
	},
	start: function() {
		if (this.contA == null || this.contB == null) return;
		if (this.ival != null) return;
		if (this.images.length < 2) return;

		var t = this;

		this.step = this.fadeTime / (100/this.fadeStep);
		this.setOpacity(this.contA, 100);
		this.setOpacity(this.contB, 0);
		this.setImage(this.contB);
		setTimeout(function(e) {t.changeImage(t);}, this.timeout*1000);
	},
	changeImage: function(t) {
		if (t.ival != null) return;
		var copa = 100;

		t.ival = setInterval(function() {
			copa -= t.fadeStep;
			if (t.incA) {
				t.setOpacity(t.contA, 100-copa);
				t.setOpacity(t.contB, copa);
				if (t.contB.style.visibility == 'hidden') {
					clearInterval(t.ival);
					t.ival = null;
					t.incA = false;
					t.setImage(t.contB);
					setTimeout(function(e) {t.changeImage(t);}, t.timeout*1000);
				}
			} else {
				t.setOpacity(t.contA, copa);
				t.setOpacity(t.contB, 100-copa);
				if (t.contA.style.visibility == 'hidden') {
					clearInterval(t.ival);
					t.ival = null;
					t.incA = true;
					t.setImage(t.contA);
					setTimeout(function(e) {t.changeImage(t);}, t.timeout*1000);
				}
			}
		}, t.step*1000);
	},
	setOpacity: function(c, o) {
		if (o > 1) c.style.visibility = 'visible';
		else c.style.visibility = 'hidden';
		c.style.opacity = o/100;
		if (c.filters && c.filters.alpha) c.filters.alpha.opacity = o;
	},
	setImage: function(c, r) {
		if (this.random) this.imgCounter = Math.floor(Math.random()*(this.images.length+1));
		else this.imgCounter = (this.imgCounter+1)%this.images.length;
		c.style.backgroundImage = 'url('+this.images[this.imgCounter]+')';
	}
};
