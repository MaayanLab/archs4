var mcVM_options1={menuId:"menu-v1",alignWithMainMenu:false};
var mcVM_options2={menuId:"menu-v2",alignWithMainMenu:false};
/* www.menucool.com/vertical/vertical-menu.*/

function init_v_menu(a) {
    if (window.addEventListener) window.addEventListener("load", function() {
        start_v_menu(a)
    }, false);
    else window.attachEvent && window.attachEvent("onload", function() {
        start_v_menu(a)
    })
}

function start_v_menu(i) {
    var e = document.getElementById(i.menuId),
        j = e.offsetHeight,
        b = e.getElementsByTagName("ul"),
        g = /msie|MSIE 6/.test(navigator.userAgent);
    if (g)
        for (var h = e.getElementsByTagName("li"), a = 0, l = h.length; a < l; a++) {
            h[a].onmouseover = function() {
                this.className = "onhover"
            };
            h[a].onmouseout = function() {
                this.className = ""
            }
        }
    for (var k = function(a, b) {
            if (a.id == i.menuId) return b;
            else {
                b += a.offsetTop;
                return k(a.parentNode.parentNode, b)
            }
        }, a = 0; a < b.length; a++) {
        var c = b[a].parentNode;
        c.getElementsByTagName("a")[0].className += " arrow";
        b[a].style.left = c.offsetWidth + "px";
        b[a].style.top = c.offsetTop + "px";
        if (i.alignWithMainMenu) {
            var d = k(c.parentNode, 0);
            if (b[a].offsetTop + b[a].offsetHeight + d > j) {
                var f;
                if (b[a].offsetHeight > j) f = -d;
                else f = j - b[a].offsetHeight - d;
                b[a].style.top = f + "px"
            }
        }
        c.onmouseover = function() {
            if (g) this.className = "onhover";
            var a = this.getElementsByTagName("ul")[0];
            if (a) {
                a.style.visibility = "visible";
                a.style.display = "block"
            }
        };
        c.onmouseout = function() {
            if (g) this.className = "";
            this.getElementsByTagName("ul")[0].style.visibility = "hidden";
            this.getElementsByTagName("ul")[0].style.display = "none"
        }
    }
    for (var a = b.length - 1; a > -1; a--) b[a].style.display = "none"
}