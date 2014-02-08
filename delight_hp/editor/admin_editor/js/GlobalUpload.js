var GlobalUpload = {
	preInit : function() { },

	init : function(ed) {
		this.drawFTPFiles();
		if (typeof(FileReader) == 'function') {
			if (typeof(window.filesList) == 'undefined') window.filesList = [];
			GlobalUpload.makeDropable();
		} else {
			GlobalUpload.addUploadForm();
		}
	},
	
	// Old HTML4 Upload Technic
	addUploadForm : function() {
		var c = $('upload_table'),tr,td,ifr,cnt = document.getElementsByTagName('iframe');
		if (!c) {
			return;
		}
		if (cnt.length > 0) {
			cnt = parseInt( cnt[cnt.length-1].getAttribute('id').replace(/upload_/, '') ) + 1;
		} else {
			cnt = 0;
		}
		
		tr = document.createElement('tr');
		tr.setAttribute('id', 'uploadrow_'+cnt);
		c.appendChild(tr);
		
		td = document.createElement('td');
		td.style.border = '1px solid #B5A69C';
		tr.appendChild(td);
		
		ifr = document.createElement('iframe');
		ifr.setAttribute('id', 'upload_'+cnt);
		ifr.setAttribute('frameborder', '0');
		ifr.setAttribute('src', adminUrl+'index.php?lang='+AdminDialog.getLang()+'&adm='+adminAction+'&action=template&template=upload_form&section='+AdminDialog.getWindowArg('section')+'&entry='+AdminDialog.getWindowArg('entry'));
		ifr.style.border = '0px none';
		ifr.style.width = '100%';
		ifr.style.height = '48px';
		td.appendChild(ifr);
		
		if (AdminDialog.getWindowArg('entry') == false) {
			td = document.createElement('td');
			td.style.border = '1px solid #B5A69C';
			td.style.width = '24px';
			td.style.verticalAlign = 'top';
			td.innerHTML = '<div class="toolbar" style="position:relative;height:40px;background:transparent;border:0px none;">'+
		    	           '<a href="javascript:GlobalUpload.removeUploadForm('+cnt+');" class="dadmButton dadmButtonEnabled dadm_uploadRemove"><span class="dadmIcon dadm_uploadRemove" /></a>'+
			               '<a href="javascript:GlobalUpload.addUploadForm();" class="dadmButton dadmButtonEnabled dadm_uploadAdd"><span class="dadmIcon dadm_uploadAdd" /></a>'+
			               '</div>';
			tr.appendChild(td);
		}
	},
	removeUploadForm : function(id) {
		if ($('upload_'+id)) {
			var c = $('uploadrow_'+id);
			c.parentNode.removeChild(c);
		}
	},
	
	drawFTPFiles : function() {
		if (!$('ftp_files')) {
			return;
		}
		var c = $('ftp_files'), files=[], re, filter=AdminDialog.getWindowArg('ftp_files_filter') || '*';
		filter = filter.replace(/\./g, '\\.').replace(/\*/g, '.*');
		re = new RegExp(filter);
		ftpFiles.each(function(v) {
			if (re.match(v)) {
				files.push(v);
			}
		});
		
		files.each(function(v) {
			var o = document.createElement('option');
			o.setAttribute('value', v);
			o.innerHTML = v;
			c.appendChild(o);
		});
		
		if (AdminDialog.getWindowArg('entry') != false) {
			c.removeAttribute('multiple', 0);
		}
	},
	
	uploadFiles : function() {
		var i, ffl = $('ftp_files'), send='';
		
		// Select the Files-Tab
		mcTabs.displayTab('upload_tab','upload_panel');
		
		// "Upload" all FTP-Files - Local files will be uploaded after this action by "uploadNext"
		for (i = 0; i < ffl.options.length; i++) {
			if (ffl.options[i].selected) {
				send += ffl.options[i].value+';';
			}
		}
		AdminDialog.callFunction(adminAction, {
			action: 'upload',
			section: AdminDialog.getWindowArg('section'),
			entry: AdminDialog.getWindowArg('entry'),
			ftp: send+'lastentry'
		}, GlobalUpload);
	},
	
	uploadNext : function(o) {
		// HTML5-Uploades are handled special
		if (typeof(FileReader) == 'function') {
			GlobalUpload.handleQueuedFiles();
			return;
		}

		var fl = document.getElementsByTagName('iframe'),s = false,c = true;
		if ((AdminDialog.getWindowArg('entry') != false) && (o.loaded > 0)) {
			c = false;
		}
		if (c && (fl.length > 0)) {
			for (var i = 0; i < fl.length; i++) {
				if (fl[i].contentWindow.$('upload')) {
					fl[i].contentWindow.$('upload').submit();
					s = true;
					break;
				}
			}
		}
		if (!s || !c) {
			if (o.scope) {
				var win = AdminDialog.getOpener();
				if (win && win[o.scope] && win[o.scope].reloadContent) win[o.scope].reloadContent();
			}
			AdminDialog.close();
		}
	},
	
	// Html5 Upload Technic
	makeDropable: function() {
		var dropbox = $('panel_wrapper');
		dropbox.addEventListener("dragenter", function(e) { Event.stop(e); }, false);
		dropbox.addEventListener("dragexit",  function(e) { Event.stop(e); }, false);
		dropbox.addEventListener("dragover",  function(e) { Event.stop(e); }, false);
		dropbox.addEventListener("drop", GlobalUpload.dropFiles, false);
		
		$('upload_table').insert({
			before: '<p style="text-align:center;margin:0;padding:0 0 5px 0;">' +
	                '<input type="file" onchange="GlobalUpload.filesSelected(this);" multiple="multiple" style="width:100%;" />' +
			        '<em title="'+AdminDialog.getLang('upload_html5_hint')+'">'+AdminDialog.getLang('upload_html5_text')+'</em><br/>' +
			        '</p>'
		});
		var files = AdminDialog.getWindowArg('files');
		if (files) {
			for (var i = 0; i < files.length; i++) {
				GlobalUpload.queueFile(files[i]);
			}
		}
	},
	
	filesSelected : function(input) {
		if (input && input.files) {
			for (var i = 0; i < input.files.length; i++) {
				GlobalUpload.queueFile(input.files[i]);
			}
		}
	},
	
	dropFiles : function(evt) {
		Event.stop(evt);
		var files = evt.dataTransfer.files;
		for (var i = 0; i < files.length; i++) {
			GlobalUpload.queueFile(files[i]);
		}
	},
	
	queueFile: function(file) {
		// If a File should be replaced, remove all already selected files...
		if (AdminDialog.getWindowArg('entry')) {
			$('upload_table').innerHTML = '';
			window.filesList = [];
		}
		
		// in case the user selects an other file after an error occured, we want to close the window after the upload
		window.filesListUploadError = false;
		
		file.isUploaded = false;
		file.htmlId = 'fileProcessingId-'+filesList.length;
		file.sizeHr = file.size || file.fileSize;
		file.sizeHr = (file.sizeHr > 1024 * 1024)
				? (Math.round(file.sizeHr * 100 / (1024 * 1024)) / 100).toString() + ' MiB'
				: (Math.round(file.sizeHr * 100 / 1024) / 100).toString() + ' KiB';
		
		var row = document.createElement('tr'), td1 = document.createElement('td'), td2 = document.createElement('td');
		row.setAttribute('id', file.htmlId+'-row');
		$('upload_table').appendChild(row);
		
		// Show some information about the file
		td1.innerHTML = '<strong>Name:</strong> <span class="upload-filename">'+( file.name || file.fileName )+'</span><br/>' +
			'<strong>Size:</strong> <span class="upload-filesize">'+( file.sizeHr )+'</span><br/>' +
			'<strong>State:</strong> <span class="upload-filestate" id="'+file.htmlId+'-state">'+AdminDialog.getLang('upload_html5_waiting')+'</span></br>' +
			'<div style="border:1px inset #A0A0A0;margin:3px 10px;"><span id="'+file.htmlId+'-progress" style="height:5px;width:0%;display:block;background:red;"></span></div>';
		row.appendChild(td1);
		
		// Show the Image if its one
		td2.style.padding = '3px';
		td2.style.width = '66px';
		if (file.type.match(/image.*/i)) {
			var img = document.createElement('img');
			img.file = file;
			img.style.width = "60px";
			td2.appendChild(img);

			var reader = new FileReader();
			reader.onload = (function(aImg) { return function(e) { aImg.src = e.target.result; }; })(img);
			reader.readAsDataURL(file);
		}
		row.appendChild(td2);
		window.filesList.push(file);
	},
	
	handleQueuedFiles: function(scope) {
		if (typeof(window.filesList) == 'undefined') {
			AdminDialog.close();
			return;
		}
		if (typeof(window.filesListProgress) == 'undefined') {
			window.filesListProgress = 0;
		}
		var section = AdminDialog.getWindowArg('section'), entry = AdminDialog.getWindowArg('entry');
		tinyMCEPopup.editor.setProgressState(1);
		
		for (var i = 0; i < window.filesList.length; i++) {
			// We can increase this here if it's possible to access to individual properties on xhr in "progress"-Event
			if (window.filesListProgress >= 1)
				return;
			
			var file = window.filesList[i];
			if (!file || file.isUploaded) continue;
			file.isUploaded = true;
			window.filesListProgress++;
			
			var xhr = new XMLHttpRequest();
			var htmlId = file.htmlId;
			xhr.htmlId = file.htmlId;
			xhr.upload.addEventListener("progress", function (evt) {
				var d = $(htmlId+"-progress");
				if (d && evt.lengthComputable) {
					d.style.width = Math.round((evt.loaded / evt.total) * 100, 2) + '%';
				}
			}, false);
			
			xhr.addEventListener("load", function (evt) {
				var d = $(htmlId+"-state");
				try {
					var resp = eval("("+this.responseText+")");
					if (d) {
						if (resp && resp.message) d.innerHTML = '<span style="color:red;font-weight:bold;">'+resp.message+'</span>';
						else d.innerHTML = AdminDialog.getLang('upload_html5_finished');
					}
					if (resp && resp.error) window.filesListUploadError = true;
					if (resp && resp.scope) scope = resp.scope;
				} catch(e) {
					window.filesListUploadError = true;
					console.debug(e);
				}
				
				var d = $(htmlId+"-progress");
				if (d) d.style.width = '100%';
				
				window.filesListProgress--;
				GlobalUpload.handleQueuedFiles(scope);
			}, false);
			
			xhr.open("post", adminUrl+'index.php?adm='+adminAction+'&action=html5_upload&section='+section+'&entry='+entry, true);
			xhr.setRequestHeader("Content-Type", "multipart/form-data");
			xhr.setRequestHeader("X-File-Name", file.name || file.fileName);
			xhr.setRequestHeader("X-File-Size", file.size || file.fileSize);
			xhr.setRequestHeader("X-File-Type", file.type || file.fileType);
			
			// FF 3.5 needs getAsBinary
			if ('getAsBinary' in file) {
				xhr.sendAsBinary(file.getAsBinary());
			} else {
				xhr.send(file);
			}
		}
		if (window.filesListProgress >= 1) {
			return;
		}

		tinyMCEPopup.editor.setProgressState(0);
		if (scope) {
			var win = AdminDialog.getOpener();
			if (win && win[scope] && win[scope].reloadContent) {
				win[scope].reloadContent();
			}
		}
		// an error occured...
		if (!window.filesListUploadError) {
			AdminDialog.close();
		}
	}
	
};

GlobalUpload.preInit();
tinyMCEPopup.onInit.add(GlobalUpload.init, GlobalUpload);
