/**
 * "Back to" Canada button:
 */

var goBack = {
    element: "back-to-ca",
    cookieName: 'back2ca',
    baseUrl: {
        prod: 'http://canada.ul.com/',
        staging: 'http://canada.ulstage.com/',
    },
    ref: false,
    lang: false,
    staging: false,
    // getUrlParameter src: https://davidwalsh.name/query-string-javascript
    getUrlParameter: function(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? false : decodeURIComponent(results[1].replace(/\+/g, ' '));
    },
    // getCookie & setCookie src: https://plainjs.com/javascript/utilities/set-cookie-get-cookie-and-delete-cookie-5/
    getCookie: function(name) {
        var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
        return v ? v[2] : false;
    },
    setCookie: function(name, value, days) {
        var d = new Date;
        d.setTime(d.getTime() + 24*60*60*1000*days);
        document.cookie = name + "=" + value + ";path=/;expires=" + d.toGMTString();
    },
    buildUrl: function(){
        var Url = this.baseUrl.prod;
        var cookie = this.getCookie(this.cookieName);
        var qstring = window.location.search;
        // If no cookie, or there is a new query string:
        if(!cookie || qstring){
            this.staging = this.getUrlParameter('staging');
            if(this.staging == 1){
                Url = this.baseUrl.staging;
            }
            this.lang = this.getUrlParameter('lang');
            if(this.lang == 'fr'){
                Url += 'fr/';
            }
            this.ref = this.getUrlParameter('ref');
            if(this.ref == 'events' || this.ref == 'news'){
                Url += this.ref + '/';
            }
            return Url;
        }
        // Else there is cookie, but there is no query string:
        else {
            var currpath = window.location.pathname;
            var currpathFr = currpath.indexOf('/fr-ca/');
            var cookieFr = cookie.indexOf('fr');

            // If cookie and currPath both are "fr":
            if(currpathFr != -1 && cookieFr != -1) {
                return cookie;
            }
            // Add "fr" to cookie path:
            if(currpathFr != -1) {
                return cookie.replace('.com/','.com/fr/');
            }
            // Remove "fr" from cookie path:
            else {
                return cookie.replace('.com/fr/','.com/');
            }
        }
    },
    updateHtml: function(){
        var cookie = this.getCookie(this.cookieName);
        if(cookie){
            document.getElementById(this.element).href=cookie;
        }
    },
    init: function(){
        var NewUrl = this.buildUrl();
        if(NewUrl){
            this.setCookie(this.cookieName, NewUrl, 30);
        }
        this.updateHtml();
    }
}

// Instantiate the goBack object:
goBack.init();
