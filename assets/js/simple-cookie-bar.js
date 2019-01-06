window.onload = function() {
    if (typeof scb_settings !== typeof{}) {
        scb_settings = {}
    }
    if (typeof scb_settings['agreementText'] === typeof undefined || scb_settings['agreementText'] === false || scb_settings['agreementText'] == '' || scb_settings['agreementText'] == null) {
        scb_settings['agreementText'] = 'By visiting this website you automatically give us permission to set cookies.';
    }
    if (typeof scb_settings['privacyLink'] === typeof undefined || scb_settings['privacyLink'] === false || scb_settings['privacyLink'] == '' || scb_settings['privacyLink'] == null) {
        scb_settings['privacyLink'] = '';
    }
    if (typeof scb_settings['privacyLinkText'] === typeof undefined || scb_settings['privacyLinkText'] === false || scb_settings['privacyLinkText'] == '' || scb_settings['privacyLinkText'] == null) {
        scb_settings['privacyLinkText'] = 'More about cookies';
    }
    if (typeof scb_settings['agreeBtnText'] === typeof undefined || scb_settings['agreeBtnText'] === false || scb_settings['agreeBtnText'] == '' || scb_settings['agreeBtnText'] == null) {
        scb_settings['agreeBtnText'] = 'Agree';
    }
    if (typeof scb_settings['adjustBtnText'] === typeof undefined || scb_settings['adjustBtnText'] === false || scb_settings['adjustBtnText'] == '' || scb_settings['adjustBtnText'] == null) {
        scb_settings['adjustBtnText'] = 'Adjust';
    }
    if (typeof scb_settings['adjustContainerTitle'] === typeof undefined || scb_settings['adjustContainerTitle'] === false || scb_settings['adjustContainerTitle'] == '' || scb_settings['adjustContainerTitle'] == null) {
        scb_settings['adjustContainerTitle'] = 'Adjust cookie setting';
    }
    if (typeof scb_settings['funcTitle'] === typeof undefined || scb_settings['funcTitle'] === false || scb_settings['funcTitle'] == '' || scb_settings['funcTitle'] == null) {
        scb_settings['funcTitle'] = 'Functional (mandatory)';
    }
    if (typeof scb_settings['funcText'] === typeof undefined || scb_settings['funcText'] === false || scb_settings['funcText'] == '' || scb_settings['funcText'] == null) {
        scb_settings['funcText'] = 'This website makes use of functional cookies. These cookies are required to get the website to work. By using this website you automatically agree with the use of these cookies.';
    }
    if (typeof scb_settings['trackingTitle'] === typeof undefined || scb_settings['trackingTitle'] === false || scb_settings['trackingTitle'] == '' || scb_settings['trackingTitle'] == null) {
        scb_settings['trackingTitle'] = 'Tracking (optional)';
    }
    if (typeof scb_settings['trackingText'] === typeof undefined || scb_settings['trackingText'] === false || scb_settings['trackingText'] == '' || scb_settings['trackingText'] == null) {
        scb_settings['trackingText'] = 'Tracking cookies are used to analyse the behaviour of our visitors. With this information we can improve our website. These cookies are optional and are enabled by default.';
    }
    if (typeof scb_settings['socialTitle'] === typeof undefined || scb_settings['socialTitle'] === false || scb_settings['socialTitle'] == '' || scb_settings['socialTitle'] == null) {
        scb_settings['socialTitle'] = 'Social Media (optional)';
    }
    if (typeof scb_settings['socialText'] === typeof undefined || scb_settings['socialText'] === false || scb_settings['socialText'] == '' || scb_settings['socialText'] == null) {
        scb_settings['socialText'] = 'Social Media cookies are used for Social Media related functionalities, for example for Like or Share buttons. These cookies are optional and are enabled by default.';
    }

    var agreeCookie = readCookie('scb-agree-cookie');

	if (agreeCookie == 1) {
        enableCookies('tracking');
        enableCookies('social');
    } else if (agreeCookie == 2) {
        enableCookies('tracking');
    } else if (agreeCookie == 3) {
        enableCookies('social');
    } else if (agreeCookie == 4) {

    } else {
        var bar = document.createElement('div');
        bar.id = 'scb-cookie-bar';
        var barCss = "color:#333333;";
        barCss += "padding:8px 15px;";
        barCss += "position:fixed;";
        if (scb_settings['position'] == 'bottom') barCss += "bottom:0;";
        else barCss += "top:0;";
        barCss += "left:0;";
        barCss += "right:0;";
        barCss += "width:100%;";
        barCss += "overflow-x:hidden;";
        barCss += "max-width:100%;";
        barCss += "font-family: Arial;";
        barCss += "border-bottom: 1px solid #cccccc;";
        barCss += "border-top: 1px solid #cccccc;";
        barCss += "line-height:42px;";
        barCss += "letter-Spacing: 0.06rem;";
        barCss += "z-index: 999999;";
        barCss += "background: #ffffff;";
        barCss += "background: -moz-linear-gradient(top, #ffffff 0%, #e5e5e5 100%);";
        barCss += "background: -webkit-linear-gradient(top, #ffffff 0%,#e5e5e5 100%);";
        barCss += "background: linear-gradient(to bottom, #ffffff 0%,#e5e5e5 100%);";
        barCss += "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#e5e5e5',GradientType=0 );";
        bar.style.cssText = barCss;

        var textnode = document.createTextNode(scb_settings['agreementText'] + ' ');
        bar.appendChild(textnode);

        if (scb_settings['privacyLink'] != '') {
            var link = document.createElement('a');
            link.href = scb_settings['privacyLink'];
            link.style.cssText = 'color: #333333';
            var linkText = document.createTextNode(scb_settings['privacyLinkText']);
            link.appendChild(linkText);
            bar.appendChild(link);
        }

        var agreeBtn = document.createElement('button');
        agreeBtn.id = 'scb-cookie-agree';
        var agreeBtnCss = "float:right;";
        agreeBtnCss += "border-radius:6px;";
        agreeBtnCss += "border:1px solid #74b807;";
        agreeBtnCss += "color: #ffffff;";
        agreeBtnCss += "font-size: 16px;";
        agreeBtnCss += "line-height: 16px;";
        agreeBtnCss += "letter-Spacing: 0.06rem;";
        agreeBtnCss += "text-shadow: 1px 1px #555555;";
        agreeBtnCss += "cursor: pointer;";
        agreeBtnCss += "padding: 10px 30px;";
        agreeBtnCss += "margin: 0 10px;";
        agreeBtnCss += "background: rgb(171,220,40);";
        agreeBtnCss += "background: -moz-linear-gradient(top, rgba(171,220,40,1) 0%, rgba(143,200,0,1) 100%);";
        agreeBtnCss += "background: -webkit-linear-gradient(top, rgba(171,220,40,1) 0%,rgba(143,200,0,1) 100%);";
        agreeBtnCss += "background: linear-gradient(to bottom, rgba(171,220,40,1) 0%,rgba(143,200,0,1) 100%);";
        agreeBtnCss += "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#abdc28', endColorstr='#8fc800',GradientType=0 );";
        agreeBtn.style.cssText = agreeBtnCss;
        var btnText = document.createTextNode(scb_settings['agreeBtnText']);
        agreeBtn.appendChild(btnText);
        bar.appendChild(agreeBtn);

        var adjustBtn = document.createElement('button');
        adjustBtn.id = 'scb-cookie-adjust';
        var adjustBtnCss = "float:right;";
        adjustBtnCss += "border-radius:6px;";
        adjustBtnCss += "border:1px solid #207ce5;";
        adjustBtnCss += "color: #ffffff;";
        adjustBtnCss += "font-size: 16px;";
        adjustBtnCss += "line-height: 16px;";
        adjustBtnCss += "letter-Spacing: 0.06rem;";
        adjustBtnCss += "text-shadow: 1px 1px #555555;";
        adjustBtnCss += "cursor: pointer;";
        adjustBtnCss += "padding: 10px 30px !important;";
        adjustBtnCss += "margin: 0 10px;";
        adjustBtnCss += "background: rgb(73,155,234);";
        adjustBtnCss += "background: -moz-linear-gradient(top, rgba(73,155,234,1) 0%, rgba(32,124,229,1) 100%);";
        adjustBtnCss += "background: -webkit-linear-gradient(top, rgba(73,155,234,1) 0%,rgba(32,124,229,1) 100%);";
        adjustBtnCss += "background: linear-gradient(to bottom, rgba(73,155,234,1) 0%,rgba(32,124,229,1) 100%);";
        adjustBtnCss += "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#499bea', endColorstr='#207ce5',GradientType=0 );";
        adjustBtn.style.cssText = adjustBtnCss;
        var btnText = document.createTextNode(scb_settings['adjustBtnText']);
        adjustBtn.appendChild(btnText);
        bar.appendChild(adjustBtn);

        document.body.appendChild(bar);

        agreeBtn.onclick = function() {
            var elem = document.getElementById('scb-cookie-bar');
            elem.style.transition = "all 0.5s ease-in-out";
            if (scb_settings['position'] == 'bottom') elem.style.bottom = '-' + elem.clientHeight + 'px';
            else elem.style.top = '-' + elem.clientHeight + 'px';
            enableCookies('tracking');
            enableCookies('social');
    		createCookie('scb-agree-cookie', 1, 365);
        };

        adjustBtn.onclick = function() {

            // Set overlay
            var overlay = document.createElement('div');
            overlay.id = 'scb-cookie-overlay';
            var overlayCss = "position: fixed;";
            overlayCss += "top: 0;";
            overlayCss += "left: 0;";
            overlayCss += "width: 100%;";
            overlayCss += "height: 100vh;";
            overlayCss += "font-family: \"Open Sans\", Helvetica, Arial, sans-serif;";
            overlayCss += "text-align: center;";
            overlayCss += "background-color: #000000;";
            overlayCss += "background-color: rgba(0,0,0,0.85);";
            overlayCss += "z-index: 9999998";
            overlay.style.cssText = overlayCss;

            // Set container
            var adjustContainer = document.createElement('div');
            adjustContainer.id = 'scb-cookie-adjust-container';
            var adjustContainerCSS = "width: 100%;";
            adjustContainerCSS += "width: 500px;";
            adjustContainerCSS += "margin: 0 auto;";
            adjustContainerCSS += "padding: 1rem;";
            adjustContainerCSS += "font-family: \"Open Sans\", Helvetica, Arial, sans-serif;";
            adjustContainerCSS += "text-align: left;";
            adjustContainerCSS += "margin-top: 20vh;";
            adjustContainerCSS += "background-color: rgba(255,255,255,0.95);";
            adjustContainerCSS += "border-radius: 0.1rem;";
            adjustContainer.style.cssText = adjustContainerCSS;

            // Set close button
            var closeBtn = document.createElement('a');
            var closeBtnCss = "font-size: 20px;";
            closeBtnCss += "line-height: 12px;";
            closeBtnCss += 'color: #333333;';
            closeBtnCss += 'float: right;';
            closeBtnCss += 'cursor: pointer;';
            closeBtn.style.cssText = closeBtnCss;
            var closeBtnText = document.createTextNode("\u00D7");
            closeBtn.appendChild(closeBtnText);
            adjustContainer.appendChild(closeBtn);

            var adjustTitle = document.createElement('div');
            var adjustTitleCss = "font-size: 18px;";
            adjustTitleCss += "line-height: 18px;";
            adjustTitleCss += "font-weight: bold;";
            adjustTitleCss += "letter-spacing: 0.06rem;";
            adjustTitleCss += "margin-bottom: 1.5rem;";
            adjustTitleCss += "text-transform: uppercase;";
            adjustTitle.style.cssText = adjustTitleCss;
            var adjustTitleText = document.createTextNode(scb_settings['adjustContainerTitle']);
            adjustTitle.appendChild(adjustTitleText);
            adjustContainer.appendChild(adjustTitle);

            // Set checkboxes
            var funcLabel = document.createElement('label');
            var funcLabelCss = 'display: block;';
            funcLabelCss += "letter-spacing: 0.06rem;";
            funcLabelCss += 'margin-bottom: 0 !important;';
            funcLabel.style.cssText = funcLabelCss;
            var funcInput = document.createElement('input');
            funcInput.id = 'scb-cookie-adjust-container';
            funcInput.setAttribute("type", "checkbox");
            funcInput.setAttribute("disabled", "disabled");
            funcInput.setAttribute("checked", "checked");
            funcLabel.appendChild(funcInput);
            var funcText = document.createTextNode(' ' + scb_settings['funcTitle']);
            funcLabel.appendChild(funcText);
            adjustContainer.appendChild(funcLabel);

            var funcExplain = document.createElement('p');
            var funcExplainCss = "font-size: 14px;";
            funcExplainCss += "line-height: 1.4em;";
            funcExplainCss += "letter-spacing: 0.06rem;";
            funcExplainCss += "margin-top: 5px;";
            funcExplainCss += "font-family: \"Open Sans\", Helvetica, Arial, sans-serif;";
            funcExplain.style.cssText = funcExplainCss;
            var funcExplainText = document.createTextNode(scb_settings['funcText']);
            funcExplain.appendChild(funcExplainText);
            adjustContainer.appendChild(funcExplain);

            // Set checkboxes
            var trackingLabel = document.createElement('label');
            var trackingLabelCss = 'display: block;';
            trackingLabelCss += "letter-spacing: 0.06rem;";
            trackingLabelCss += 'margin-bottom: 0 !important;';
            trackingLabel.style.cssText = trackingLabelCss;
            var trackingInput = document.createElement('input');
            trackingInput.id = 'scb-cookie-adjust-tracking';
            trackingInput.setAttribute("type", "checkbox");
            trackingInput.setAttribute("checked", "checked");
            trackingLabel.appendChild(trackingInput);
            var trackingText = document.createTextNode(' ' + scb_settings['trackingTitle']);
            trackingLabel.appendChild(trackingText);
            adjustContainer.appendChild(trackingLabel);

            var trackingExplain = document.createElement('p');
            var trackingExplainCss = "font-size: 14px;";
            trackingExplainCss += "line-height: 1.4em;";
            trackingExplainCss += "letter-spacing: 0.06rem;";
            trackingExplainCss += "margin-top: 5px;";
            trackingExplainCss += "font-family: \"Open Sans\", Helvetica, Arial, sans-serif;";
            trackingExplain.style.cssText = trackingExplainCss;
            var trackingExplainText = document.createTextNode(scb_settings['trackingText']);
            trackingExplain.appendChild(trackingExplainText);
            adjustContainer.appendChild(trackingExplain);

            if (typeof scb_settings['social'] !== typeof undefined && scb_settings['social'] !== false && scb_settings['social'] != '' && scb_settings['social'] != null) {

                // Set checkboxes
                var socialLabel = document.createElement('label');
                var socialLabelCss = 'display: block;';
                socialLabelCss += "letter-spacing: 0.06rem;";
                socialLabelCss += 'margin-bottom: 0 !important;';
                socialLabel.style.cssText = socialLabelCss;
                var socialInput = document.createElement('input');
                socialInput.id = 'scb-cookie-adjust-social';
                socialInput.setAttribute("type", "checkbox");
                socialInput.setAttribute("checked", "checked");
                socialLabel.appendChild(socialInput);
                var socialText = document.createTextNode(' ' + scb_settings['socialTitle']);
                socialLabel.appendChild(socialText);
                adjustContainer.appendChild(socialLabel);

                var socialExplain = document.createElement('p');
                var socialExplainCss = "font-size: 14px;";
                socialExplainCss += "line-height: 1.4em;";
                socialExplainCss += "letter-spacing: 0.06rem;";
                socialExplainCss += "margin-top: 5px;";
                socialExplainCss += "font-family: \"Open Sans\", Helvetica, Arial, sans-serif;";
                socialExplain.style.cssText = socialExplainCss;
                var socialExplainText = document.createTextNode(scb_settings['socialText']);
                socialExplain.appendChild(socialExplainText);
                adjustContainer.appendChild(socialExplain);
            }

            // Set button
            var adjustCookieBtn = document.createElement('button');
            adjustCookieBtn.id = 'btn-cookie-adjust';
            var adjustBtnCss = "border-radius:6px;";
            adjustBtnCss += "border:1px solid #207ce5;";
            adjustBtnCss += "color: #ffffff;";
            adjustBtnCss += "font-size: 16px;";
            adjustBtnCss += "line-height: 16px;";
            adjustBtnCss += "text-shadow: 1px 1px #555555;";
            adjustBtnCss += "cursor: pointer;";
            adjustBtnCss += "padding: 10px 30px !important;";
            adjustBtnCss += "background: rgb(73,155,234);";
            adjustBtnCss += "background: -moz-linear-gradient(top, rgba(73,155,234,1) 0%, rgba(32,124,229,1) 100%);";
            adjustBtnCss += "background: -webkit-linear-gradient(top, rgba(73,155,234,1) 0%,rgba(32,124,229,1) 100%);";
            adjustBtnCss += "background: linear-gradient(to bottom, rgba(73,155,234,1) 0%,rgba(32,124,229,1) 100%);";
            adjustBtnCss += "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#499bea', endColorstr='#207ce5',GradientType=0 );";
            adjustCookieBtn.style.cssText = adjustBtnCss;
            var btnText = document.createTextNode(scb_settings['adjustBtnText']);
            adjustCookieBtn.appendChild(btnText);
            adjustContainer.appendChild(adjustCookieBtn);

            overlay.appendChild(adjustContainer);
            document.body.appendChild(overlay);

            adjustCookieBtn.onclick = function() {
                var element = document.getElementById('scb-cookie-overlay');
                element.parentNode.removeChild(element);

                var elem = document.getElementById('scb-cookie-bar');
                elem.style.transition = "all 0.5s ease-in-out";
                if (scb_settings['position'] == 'bottom') elem.style.bottom = '-' + elem.clientHeight + 'px';
                else elem.style.top = '-' + elem.clientHeight + 'px';

                var cookieSetting = 4;
                if (typeof socialInput !== typeof undefined && socialInput !== false) {
                    if (trackingInput.checked && socialInput.checked) {
                        cookieSetting = 1;
                        enableCookies('tracking');
                        enableCookies('social');
                    } else if (trackingInput.checked) {
                        cookieSetting = 2;
                        enableCookies('tracking');
                    } else if (socialInput.checked) {
                        cookieSetting = 3;
                        enableCookies('social');
                    }
                } else {
                    if (trackingInput.checked) {
                        cookieSetting = 2;
                        enableCookies('tracking');
                    }
                }

        		createCookie('scb-agree-cookie', cookieSetting, 365);
            };

            closeBtn.onclick = function() {
                var element = document.getElementById('scb-cookie-overlay');
                element.parentNode.removeChild(element);
            };
        };
	}

    function enableCookies(type) {

        var elements = document.querySelectorAll('[data-cookie-' + type + '="1"]');

	    for (var i = 0; i < elements.length; i++) {
	        var element = elements[i];
            var script = element.innerHTML;
            eval(script);
	    }
    }

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
}
