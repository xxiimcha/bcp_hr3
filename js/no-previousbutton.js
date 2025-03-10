  // This function will clear the history on page load
  window.onload = function () {
    if (history.length > 0) {
        // Push the current page to history
        window.onpopstate = function () {
        history.pushState(null, null, location.href)
    };
        // Override the default behavior of the back button
        window.onpopstate = function () {
            history.pushState(null, null, location.href);
        };
    }
};  