/**
 * Qualtrics JS.
 * @LastUpdated: Sep 7, 2023.
 */

setTimeout(function(){

  // If there is a hash for the modal:
  if (window.location.hash == '#show-modal') {
    return;
  }

  // If the modal is open:
  else if (jQuery('.mktoModal').length && jQuery('.mktoModal').hasClass('is-visible')) {
    return;
  }

  // Else, OK to run Qualtrics:
  else {
    /* BEGIN QUALTRICS WEBSITE FEEDBACK SNIPPET */
    jQuery('body').append('<div id="ZN_1HqnSJOx07wiDzg"></div>');
    (function(){var g=function(e,h,f,g){
    this.get=function(a){for(var a=a+"=",c=document.cookie.split(";"),b=0,e=c.length;b<e;b++){for(var d=c[b];" "==d.charAt(0);)d=d.substring(1,d.length);if(0==d.indexOf(a))return d.substring(a.length,d.length)}return null};
    this.set=function(a,c){var b="",b=new Date;b.setTime(b.getTime()+6048E5);b="; expires="+b.toGMTString();document.cookie=a+"="+c+b+"; path=/; "};
    this.check=function(){var a=this.get(f);if(a)a=a.split(":");else if(100!=e)"v"==h&&(e=Math.random()>=e/100?0:100),a=[h,e,0],this.set(f,a.join(":"));else return!0;var c=a[1];if(100==c)return!0;switch(a[0]){case "v":return!1;case "r":return c=a[2]%Math.floor(100/c),a[2]++,this.set(f,a.join(":")),!c}return!0};
    this.go=function(){if(this.check()){var a=document.createElement("script");a.type="text/javascript";a.src=g;document.body&&document.body.appendChild(a)}};
    this.start=function(){var t=this;"complete"!==document.readyState?window.addEventListener?window.addEventListener("load",function(){t.go()},!1):window.attachEvent&&window.attachEvent("onload",function(){t.go()}):t.go()};};
    try{(new g(100,"r","QSI_S_ZN_1HqnSJOx07wiDzg","https://zn1hqnsjox07widzg-ulsolutions.siteintercept.qualtrics.com/SIE/?Q_ZID=ZN_1HqnSJOx07wiDzg")).start()}catch(i){}})();
    /* END WEBSITE FEEDBACK SNIPPET */
  }

}, 2500);
