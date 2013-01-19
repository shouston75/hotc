// == Chat Functions

//Default nick on connect
default_nick = 'Guest';

// Away form messages
msg0 = 'You are already away!';
msg1 = 'You are not away!';
msg2 = 'I\'m currently away';

// send a string to applet
function SendIt(string)
{
	document.pjirc.sendString(string);
	document.pjirc.requestSourceFocus();
}

// send smiley string to applet
function smiley(symbol)
{
	document.pjirc.setFieldText(document.pjirc.getFieldText()+symbol+' ');
	document.pjirc.requestSourceFocus();
}

isaway = false;
// switch nick, set away message
function maway(action, nick)
{
	var away_reason = document.getElementById('away_reason').value;
	switch (action)
	{
		case 'away':
			if (!isaway)
			{
				txt = away_reason;
				if (txt == '') txt = msg2;

				SendIt('/nick '+nick+'|away');
				SendIt('/away '+txt);
				isaway = true;
			} else alert(msg0);
			break;
		case 'back':
			if (isaway)
			{
				SendIt('/nick '+nick);
				SendIt('/away');
				away_reason = '';
				isaway = false;
			} else alert(msg1);
			break;
	}
}

// == Login Page Functions

// Check Form Data
function CheckForm(self)
{
	if (!CheckFormData(document.login.chan, 'Please type a Channel')) return false;
	if (!CheckFormData(document.login.host, 'Please type an IRC Server!')) return false;

  	if (document.login.save && document.login.save.checked && document.cookie)
  	{
  		if (!confirm('Overwrite old settings?')) return false;
  	}

	var nick = document.login.nick;

	if (nick.value == '')
	{
		nick.value = default_nick+Math.round(Math.random()*1000);
	}

/*
	else if(!nick.value.match(/^[A-Za-z0-9\[\]\{\}^\\\|\_\-`]{1,32}$/))
	{
		alert('Please type a valid nick!');
		nick.value = nick.value.replace(/[^A-Za-z0-9\[\]\{\}^\\\|\_\-`]/g, '');
		nick.focus();
		return false;
	}
*/

	if (document.login.popupenabled && document.login.popupenabled.value)
	{
		document.login.target = 'mypopup';
	}

	if (document.login.layerenabled && document.login.layerenabled.value) LoadLayer('400', '200');
	if (document.login.popupenabled && document.login.popupenabled.value)
	{
		OpenPopup(self, '700', '530');
		window.setTimeout('window.location.href = \''+self+'\'', 10000);
	}

	return true;
}

function CheckFormData(inp, msg)
{
	if (inp)
	{
		if (inp.value == '')
		{
			alert(msg);
			inp.focus();
			return false;
		}
		else return true;
	}
	return true;
}

// write invisible layer
function WriteLayer(message)
{
	var html = '<div id="layerwindow" class="layerwindow">\n';
	html += '\t<table width="400" cellspacing="0" cellpadding="0" class="border"><tr>\n';
	html += '\t\t<td align="center" height="100"><h2>'+message+'<\/h2><\/td>\n';
	html += '\t<\/tr><\/table>\n';
	html += '<\/div>\n';

	return html;
}

// make layer visible, and put it to the center of the browser window
function LoadLayer(x, y)
{
	var divwidth  = x;
	var divheight = y;
	var browserwidth  = window.innerWidth || document.body.clientWidth;
	var browserheight = window.innerHeight || document.body.clientHeight;
	var leftpx = (browserwidth-divwidth)/2;
	var toppx  = (browserheight-divheight)/2;

	document.getElementById('layerwindow').style.top  = '100px';
	// document.getElementById('layerwindow').style.top  = Math.round(toppx)+'px';
	document.getElementById('layerwindow').style.left = Math.round(leftpx)+'px';
	document.getElementById('layerwindow').style.visibility = 'visible';
}

// open chat in popup window
function OpenPopup(self, x, y)
{
	var values  = 'width='+x+', height='+y+', left=0, top=0,'
	values += 'dependent=no, hotkeys=no, resizable=yes, scrollbars=no, menubar=no'
	window.open(self, 'mypopup', values);
}

// check, if java is enabled in browser
function JavaCheck()
{
	var html = '<table width="100%" cellspacing="0" cellpadding="0" class="footer">\n';

	var status = 'Disabled';
	if (navigator.javaEnabled()) status = 'Enabled';

	html += '\t<tr><td align="right">\n';
	html += '\t\tJava Status:&nbsp;<span style="color: red;">'+status+'<\/span>\n';

	if (status == 'Disabled')
	{
		html += '\t\t<br>Get it at <a href="http://java.com" target="_blank">java.com<\/a>\n';
	}

	html += '\t<\/td><\/tr>\n';
	html += '<\/table>\n';

	return html;
}