
    if (typeof settings === typeof undefined || settings === false || settings == '' || settings == null) {
        var settings = {}
    }
    if (typeof settings['agreementText'] === typeof undefined || settings['agreementText'] === false || settings['agreementText'] == '' || settings['agreementText'] == null) {
        settings['agreementText'] = 'By visiting this website you automatically give us permission to set cookies.';
    }
    if (typeof settings['privacyLink'] === typeof undefined || settings['privacyLink'] === false || settings['privacyLink'] == '' || settings['privacyLink'] == null) {
        settings['privacyLink'] = 'By visiting this website you automatically give us permission to set cookies.';
    }
    if (typeof settings['privacyLinkText'] === typeof undefined || settings['privacyLinkText'] === false || settings['privacyLinkText'] == '' || settings['privacyLinkText'] == null) {
        settings['privacyLinkText'] = 'By visiting this website you automatically give us permission to set cookies.';
    }
    if (typeof settings['btnText'] === typeof undefined || settings['btnText'] === false || settings['btnText'] == '' || settings['btnText'] == null) {
        settings['btnText'] = 'Agree';
    }

    var agreePrivacy = readCookie('agreePrivacy');

	if (agreePrivacy != 1) {
        var bar = document.createElement('div');
        bar.id = 'bar-cookie-agree';
        var barCss = "color:#333333;";
        barCss += "padding:8px 15px;";
        barCss += "position:fixed;";
        if (settings['position'] == 'bottom') barCss += "bottom:0;";
        else barCss += "top:0;";
        barCss += "left:0;";
        barCss += "width:100%;";
        barCss += "font-family: Arial;";
        barCss += "border-bottom: 1px solid #cccccc;";
        barCss += "border-top: 1px solid #cccccc;";
        barCss += "line-height:42px;";
        barCss += "background: #ffffff;";
        barCss += "background: -moz-linear-gradient(top, #ffffff 0%, #e5e5e5 100%);";
        barCss += "background: -webkit-linear-gradient(top, #ffffff 0%,#e5e5e5 100%);";
        barCss += "background: linear-gradient(to bottom, #ffffff 0%,#e5e5e5 100%);";
        barCss += "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#e5e5e5',GradientType=0 );";
        bar.style.cssText = barCss;

        var textnode = document.createTextNode(settings['agreementText'] + ' ');
        bar.appendChild(textnode);

        if (settings['privacyLink'] != '') {
            var link = document.createElement('a');
            link.href = settings['privacyLink'];
            link.style.cssText = 'color: #333333';
            var linkText = document.createTextNode(settings['privacyLinkText']);
            link.appendChild(linkText);
            bar.appendChild(link);
        }

        var button = document.createElement('button');
        button.id = 'btn-cookie-agree';
        var btnCss = "float:right;";
        btnCss += "border-radius:6px;";
        btnCss += "border:1px solid #74b807;";
        btnCss += "color: #ffffff;";
        btnCss += "font-size: 16px;";
        btnCss += "padding: 10px 30px;";
        btnCss += "margin: 0 20px;";
        btnCss += "background: #a9db80;";
        btnCss += "background: -moz-linear-gradient(top, #a9db80 0%, #96c56f 100%);";
        btnCss += "background: -webkit-linear-gradient(top, #a9db80 0%,#96c56f 100%);";
        btnCss += "background: linear-gradient(to bottom, #a9db80 0%,#96c56f 100%);";
        btnCss += "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#a9db80', endColorstr='#96c56f',GradientType=0 );";
        button.style.cssText = btnCss;
        var btnText = document.createTextNode(settings['btnText']);
        button.appendChild(btnText);
        bar.appendChild(button);

        document.body.appendChild(bar);
	}
    button.onclick = function() {
        var elem = document.getElementById('bar-cookie-agree');
        elem.style.transition = "all 0.5s ease-in-out";
        if (settings['position'] == 'bottom') elem.style.bottom = '-' + elem.clientHeight + 'px';
        else elem.style.top = '-' + elem.clientHeight + 'px';
		createCookie('agreePrivacy', 1, 365);
    };

	function createCookie(name, value, days) {
		var expires;
		if (days) {
			var date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			expires = "; expires=" + date.toGMTString();
		} else {
			expires = "";
		}
		document.cookie = escape(name) + "=" + escape(value) + expires + "; path=/;";
	}
	function readCookie(name) {
	    var nameEQ = escape(name) + "=";
	    var ca = document.cookie.split(';');
	    for (var i = 0; i < ca.length; i++) {
	        var c = ca[i];
	        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
	        if (c.indexOf(nameEQ) === 0) return unescape(c.substring(nameEQ.length, c.length));
	    }
	    return null;
	}
