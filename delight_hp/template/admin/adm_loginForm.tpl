[STYLE_INCLUDE]css_boxes.css[/STYLE_INCLUDE]

[LAYOUT]
				<center>
					<fieldset class="tdw" style="width:400px;">
						<legend class="tdw h1">[LANG_VALUE:adm_001]</legend>
						<form name="SubForm" action="/de/1/" method="POST">
							<table cellpadding="0" cellspacing="0" style="width:100%;">
								<tr>
									<td style="padding:2px;width:150px;">[LANG_VALUE:msg_009]</td>
									<td style="padding:2px;"><input type="text" name="username" style="width:200px;" /></td>
								</tr>
								<tr>
									<td style="padding:2px;width:150px;">[LANG_VALUE:msg_010]</td>
									<td style="padding:2px;"><input type="password" name="usercode" style="width:200px;" /></td>
								</tr>
								<tr>
									<td style="padding:2px;width:150px;">[LANG_VALUE:msg_011]</td>
									<td style="padding:2px;"><input type="checkbox" name="autologin" style="margin:0px;" /></td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td style="text-align:right;padding:2px;"><input type="button" value="[LANG_VALUE:msg_012]" onclick="hashLogin();" /></td>
								</tr>
							</table>
						</form>
					</fieldset>
				</center>

				<script type="text/javascript">
				<!--
					function hashLogin()
					{
						var user = document.SubForm.username.value;
						var pass = document.SubForm.usercode.value;
						var auto = document.SubForm.autologin.checked;
						document.SubForm.usercode.value = '';
						if ( (user.length > 1) && (pass.length > 1) )
						{
							var expr = /(.*)(\/)(.*)/
							var loc  = window.location.href;
							expr.exec(loc);
							var loc = RegExp.$1 + RegExp.$2;
							var para = RegExp.$3;
							var strip = para.replace(/(ps=)([^\W\+\/\=]+)/, '');
							var check = strip.replace(/(us=)([^\W\+\/\=]+)/, '');
							var strip = check;
							var check = strip.replace(/(\&\&\&)/, '&');
							var strip = check.replace(/(\&\&)/, '&');
							var common = strip.replace(/(\&\=)/, '&');
							var strip = para.split("&");
							var common = '';
							for (var i = 0; i < strip.length; i++)
							{ if ( (strip[i].substring(0,2) != 'us') && (strip[i].substring(0,2) != 'ps') && (strip[i].substring(0,2) != 'al') )
								{
									if (common.length > 0)
										var common = common + '&';
									var common = common + strip[i];
								}
							}
							var loc = loc + common;
							var code = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
							var para = "";
							var check = user;
							var chr1, chr2, chr3 = "";
							var enc1, enc2, enc3, enc4 = "";
							var i = 0;
							do { chr1 = check.charCodeAt(i++); chr2 = check.charCodeAt(i++); chr3 = check.charCodeAt(i++);
								enc1 = chr1 >> 2; enc2 = ((chr1 & 3) << 4) | (chr2 >> 4); enc3 = ((chr2 & 15) << 2) | (chr3 >> 6); enc4 = chr3 & 63;
								if (isNaN(chr2)) {enc3 = enc4 = 64;} else if (isNaN(chr3)) {enc4 = 64;}
								para = para + code.charAt(enc1) + code.charAt(enc2) + code.charAt(enc3) + code.charAt(enc4);
								chr1 = chr2 = chr3 = ""; enc1 = enc2 = enc3 = enc4 = ""; } while (i < check.length);
							var loc = loc + '&us=' + para;
							var para = "";
							var check = pass;
							var chr1, chr2, chr3 = "";
							var enc1, enc2, enc3, enc4 = "";
							var i = 0;
							do { chr1 = check.charCodeAt(i++); chr2 = check.charCodeAt(i++); chr3 = check.charCodeAt(i++);
								enc1 = chr1 >> 2; enc2 = ((chr1 & 3) << 4) | (chr2 >> 4); enc3 = ((chr2 & 15) << 2) | (chr3 >> 6); enc4 = chr3 & 63;
								if (isNaN(chr2)) {enc3 = enc4 = 64;} else if (isNaN(chr3)) {enc4 = 64;}
								para = para + code.charAt(enc1) + code.charAt(enc2) + code.charAt(enc3) + code.charAt(enc4);
								chr1 = chr2 = chr3 = ""; enc1 = enc2 = enc3 = enc4 = ""; } while (i < check.length);
							var loc = loc + '&ps=' + para + '&al=' + auto;
							do {var loc = loc.replace(/amp;/, '');var i = loc.search(/amp;.+/);} while (i > 0)
							window.location.href = loc;
						}
					}
					document.SubForm.username.focus();
				//-->
				</script>
[/LAYOUT]