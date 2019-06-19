
(function() {

	var body = document.body;
    var html = document.documentElement;

	var window_height = window.innerHeight;
	var body_height = Math.max( body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight );
    var scroll = document.documentElement.scrollTop;

    if (scroll == 0) document.body.classList.add('position-top');
    else document.body.classList.remove('position-top');

	window.onscroll = function() {
        scroll = document.documentElement.scrollTop;
        if (scroll == 0) document.body.classList.add('position-top');
        else document.body.classList.remove('position-top');
    };

    addScrollToTop();
    addScrollDown();

    window.onresize = function(event) {
        addScrollToTop();
        addScrollDown();
    };

    function addScrollToTop() {
        if (body_height > window_height+400) {
	        var button = document.createElement('a');
			button.id = 'site-scroll-to-top';
			button.className = 'pointer';

			var icon = document.createElement('i');
			icon.className = 'fas fa-angle-up';
			button.appendChild(icon);

			var span = document.createElement('span');
			var text = document.createTextNode('Back to top');
			span.appendChild(text);
			button.appendChild(span);

			document.body.appendChild(button);
			document.getElementById('site-scroll-to-top').addEventListener('click', scrollToTop);
        } else {
			var element = document.getElementById('site-scroll-to-top');
			element.removeEventListener('click', scrollToTop);
    		element.parentNode.removeChild(element);
        }
    }

	function scrollToTop() {
		scrollTo(document.documentElement, 0, 1250);
	}

    function addScrollDown() {
        if (body_height > window_height+400) {
	        var button = document.createElement('a');
			button.id = 'site-scroll-down';
			button.className = 'animated infinite rubberBand pointer';
			document.body.appendChild(button);
			document.getElementById('site-scroll-down').addEventListener('click', scrollDown);
        } else {
			var element = document.getElementById('site-scroll-down');
			element.removeEventListener('click', scrollDown);
    		element.parentNode.removeChild(element);
        }
    }

	function scrollDown() {
		scrollTo(document.documentElement, window_height, 1250);
	}

	function scrollTo(element, to, duration) {
	    var start = element.scrollTop,
	        change = to - start,
	        currentTime = 0,
	        increment = 20;

	    var animateScroll = function(){
	        currentTime += increment;
	        var val = Math.easeInOutQuad(currentTime, start, change, duration);
	        element.scrollTop = val;
	        if(currentTime < duration) {
	            setTimeout(animateScroll, increment);
	        }
	    };
	    animateScroll();
	}

	//t = current time
	//b = start value
	//c = change in value
	//d = duration
	Math.easeInOutQuad = function (t, b, c, d) {
		t /= d/2;
		if (t < 1) return c/2*t*t + b;
		t--;
		return -c/2 * (t*(t-2) - 1) + b;
	};
})();
