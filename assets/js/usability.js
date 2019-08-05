
(function() {

	// The debounce function receives our function as a parameter
	const debounce = (fn) => {

		// This holds the requestAnimationFrame reference, so we can cancel it if we wish
		let frame;

		// The debounce function returns a new function that can receive a variable number of arguments
		return (...params) => {

			// If the frame variable has been defined, clear it now, and queue for next frame
			if (frame) {
			cancelAnimationFrame(frame);
			}

			// Queue our function call for the next frame
			frame = requestAnimationFrame(() => {

				// Call our function and pass any params we received
				fn(...params);
			});

		}
	};

	// Reads out the scroll position and stores it in the data attribute
	// so we can use it in our stylesheets
	const storeScroll = () => {
		document.documentElement.dataset.scroll = window.scrollY;
	}

	// Listen for new scroll events, here we debounce our `storeScroll` function
	document.addEventListener('scroll', debounce(storeScroll), { passive: true });

	// Update scroll position for first time
	storeScroll();


	document.getElementById('site-scroll-to-top').addEventListener('click', scrollToTop);
	function scrollToTop() { fnc_scrollto(0); }


    addScrollDown();
    window.onresize = function(event) { addScrollDown(); };
	function scrollDown() { fnc_scrollto(window.innerHeight); }
    function addScrollDown() {

		var body = document.body;
	    var html = document.documentElement;

		var window_height = window.innerHeight;
		var body_height = Math.max( body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight );
	    var scroll = document.documentElement.scrollTop;

        if (body_height > window_height+400) {
	        var button = document.createElement('a');
			button.id = 'site-scroll-down';
			button.className = 'animated infinite rubberBand pointer';
			document.body.appendChild(button);
			document.getElementById('site-scroll-down').addEventListener('click', scrollDown);
        } else {
			var element = document.getElementById('site-scroll-down');
			if (element) {
				element.removeEventListener('click', scrollDown);
	    		element.parentNode.removeChild(element);
			}
        }
    }
	var fnc_scrollto = function(to,id){
	    var smoothScrollFeature = 'scrollBehavior' in document.documentElement.style;
	    var articles = document.querySelectorAll("ul#content > li"), i;
	    if (to == 'elem') to = articles[id].offsetTop;
	    var i = parseInt(window.pageYOffset);
	    if ( i != to ) {
	        if (!smoothScrollFeature) {
	            to = parseInt(to);
	            if (i < to) {
	                var int = setInterval(function() {
	                    if (i > (to-20)) i += 1;
	                    else if (i > (to-40)) i += 3;
	                    else if (i > (to-80)) i += 8;
	                    else if (i > (to-160)) i += 18;
	                    else if (i > (to-200)) i += 24;
	                    else if (i > (to-300)) i += 40;
	                    else i += 60;
	                    window.scroll(0, i);
	                    if (i >= to) clearInterval(int);
	                }, 15);
	            }
	            else {
	                var int = setInterval(function() {
	                    if (i < (to+20)) i -= 1;
	                    else if (i < (to+40)) i -= 3;
	                    else if (i < (to+80)) i -= 8;
	                    else if (i < (to+160)) i -= 18;
	                    else if (i < (to+200)) i -= 24;
	                    else if (i < (to+300)) i -= 40;
	                    else i -= 60;
	                    window.scroll(0, i);
	                    if (i <= to) clearInterval(int);
	                }, 15);
	            }
	        }
	        else {
	            window.scroll({
	                top: to,
	                left: 0,
	                behavior: 'smooth'
	            });
	        }
	    }
	};

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
