//console.log(([/MSIE (\d)\.0/i.exec(navigator.userAgent)][0][1] == 6));
window.console = window.console || {
    log : function(){}
};
window.Hat = {
    isIE : (document.all) ? true : false,
    isIE6 : (! - [1, ] && !window.XMLHttpRequest),
    $ : function (id) {
        return "string" == typeof id ? document.getElementById(id) : id;
    },
    Class : {
        create : function () {
            return function () {
                this.initialize.apply(this, arguments);
            }
        }
    },

    Extend : function (destination, source) {
        for (var property in source) {
            destination[property] = source[property];
        }
    },
    Bind : function (object, fun) {
        return function () {
            return fun.apply(object, arguments);
        }
    },
    Each : function (list, fun) {
        for (var i = 0, len = list.length; i < len; i++) {
            fun(list[i], i);
        }
    },
    Contains : function (a, b) {
        return a.contains ? a != b && a.contains(b) : !!(a.compareDocumentPosition(b) & 16);
    },
    getClass : function (cssName, dom) {  //第一个参数 表示是className是所属那个dom标签下
        if (typeof document.getElementsByClassName === "function") {
            if (dom)
                return dom.getElementsByClassName(cssName);
            else
                return document.getElementsByClassName(cssName);
        } else {
            var objArr = dom ? dom.getElementsByTagName("*") : obj.getElementsByTagName("*");
            var tRObj = new Array();
            var rule = "[\"\s]+" + arguments[0] + "[\"\s]+";
            var reg = new RegExp(rule, "g");
            var _cls = "";
            for (var i = 0; i < objArr.length; i++) {
                _cls = '"' + objArr[i].className + '"';
                if (_cls.search(reg) > -1) {
                    tRObj.push(objArr[i]);
                }
            }
            return tRObj;
        }
    },
    addEvent : function (target, eventName, callbackHander) {
        if (window.attachEvent) {
            target.attachEvent("on" + eventName, callbackHander);
        } else {
            target.addEventListener(eventName, callbackHander, false);
        }
    }
}

var OverLay = Hat.Class.create();
OverLay.prototype = {
    initialize : function (options) {

        this.SetOptions(options);

        this.Lay = Hat.$(this.options.Lay) || document.body.insertBefore(document.createElement("div"), document.body.childNodes[0]);

        this.Color = this.options.Color;
        this.Opacity = parseInt(this.options.Opacity);
        this.zIndex = parseInt(this.options.zIndex);
        with (this.Lay.style) {
            display = "none";
            zIndex = this.zIndex - 1;
            left = top = 0;
            position = "fixed";
            width = height = "100%";
        }

        if (Hat.isIE6) {
            this.Lay.style.position = "absolute";
            //ie6设置覆盖层大小程序
            this._resize = Hat.Bind(this, function () {
                    this.Lay.style.width = Math.max(document.documentElement.scrollWidth, document.documentElement.clientWidth) + "px";
                    this.Lay.style.height = Math.max(document.documentElement.scrollHeight, document.documentElement.clientHeight) + "px";
                });
            //遮盖select
            this.Lay.innerHTML = '<iframe style="position:absolute;top:0;left:0;width:100%;height:100%;filter:alpha(opacity=0);"></iframe>'
        }
    },
    //设置默认属性
    SetOptions : function (options) {
        this.options = { //默认值
            Lay : null, //覆盖层对象
            Color : "#000", //背景色
            Opacity : 50, //透明度(0-100)
            zIndex : 1000 //层叠顺序
        };
        Hat.Extend(this.options, options || {});
    },
    //显示
    Show : function () {
        //兼容ie6
        if (Hat.isIE6) {
            this._resize();
            window.attachEvent("onresize", this._resize);
        }
        //设置样式
        with (this.Lay.style) {
            //设置透明度
            Hat.isIE ? filter = "alpha(opacity:" + this.Opacity + ")" : opacity = this.Opacity / 100;
            backgroundColor = this.Color;
            display = "block";
        }
    },
    //关闭
    Close : function () {
        this.Lay.style.display = "none";
        if (Hat.isIE6) {
            window.detachEvent("onresize", this._resize);
        }
    }
};
var MaxZindex = 0;
var LightBox = Hat.Class.create();
LightBox.prototype = {
    initialize : function (box, options) {

        this.Box = Hat.$(box); //显示层

        this.OverLay = new OverLay(options); //覆盖层

        this.SetOptions(options);

        this.Fixed = !!this.options.Fixed;
        this.Over = !!this.options.Over;
        this.Center = !!this.options.Center;
        this.onShow = this.options.onShow;

        this.Box.style.zIndex = MaxZindex > 0 ? MaxZindex*1+1 : this.OverLay.zIndex*1+1;
        MaxZindex = this.Box.style.zIndex;
        this.Box.style.display = "none";

        //兼容ie6用的属性
        if (Hat.isIE6) {
            this._top = this._left = 0;
            this._select = [];
            this._fixed = Hat.Bind(this, function () {
                    this.Center ? this.SetCenter() : this.SetFixed();
                });
        }
    },
    //设置默认属性
    SetOptions : function (options) {
        this.options = { //默认值
            Over : true, //是否显示覆盖层
            Fixed : false, //是否固定定位
            Center : false, //是否居中
            onShow : function () {}
            //显示时执行
        };
        Hat.Extend(this.options, options || {});
    },
    //兼容ie6的固定定位程序
    SetFixed : function () {
        this.Box.style.top = document.documentElement.scrollTop - this._top + this.Box.offsetTop + "px";
        this.Box.style.left = document.documentElement.scrollLeft - this._left + this.Box.offsetLeft + "px";

        this._top = document.documentElement.scrollTop;
        this._left = document.documentElement.scrollLeft;
    },
    //兼容ie6的居中定位程序
    SetCenter : function () {
        this.Box.style.marginTop = document.documentElement.scrollTop - this.Box.offsetHeight / 2 + "px";
        this.Box.style.marginLeft = document.documentElement.scrollLeft - this.Box.offsetWidth / 2 + "px";
    },
    //显示
    Show : function (options) {
        //固定定位
        this.Box.style.position = this.Fixed && !Hat.isIE6 ? "fixed" : "absolute";
        this.Box.style.zIndex = MaxZindex > 0 ? MaxZindex*1+1 : this.OverLay.zIndex*1+1;
        //覆盖层
        this.Over && this.OverLay.Show();

        this.Box.style.display = "block";

        //居中
        if (this.Center) {
            this.Box.style.top = this.Box.style.left = "50%";
            //设置margin
            if (this.Fixed) {
                this.Box.style.marginTop =  - this.Box.offsetHeight / 2 + "px";
                this.Box.style.marginLeft =  - this.Box.offsetWidth / 2 + "px";
            } else {
                this.SetCenter();
            }
        }

        //兼容ie6
        if (Hat.isIE6) {
            if (!this.Over) {
                //没有覆盖层ie6需要把不在Box上的select隐藏
                this._select.length = 0;
                Hat.Each(document.getElementsByTagName("select"), Hat.Bind(this, function (o) {
                        if (!Hat.Contains(this.Box, o)) {
                            o.style.visibility = "hidden";
                            this._select.push(o);
                        }
                    }))
            }
            //设置显示位置
            this.Center ? this.SetCenter() : this.Fixed && this.SetFixed();
            //设置定位
            this.Fixed && window.attachEvent("onscroll", this._fixed);
        }
        this.Drag = !!this.Drag;

        this.onShow();

        if(this.Drag){
            var titleList = Hat.getClass(this.DragClass, this.Box);
            for(var i = 0 ; i < titleList.length ; i++){

                titleList[i].style.cursor = "move";
                DragBox.makeDraggable(this.Box, titleList[i]);
            }
        }

    },
    //关闭
    Close : function () {
        this.Box.style.display = "none";
        this.OverLay.Close();
        if (Hat.isIE6) {
            window.detachEvent("onscroll", this._fixed);
            Hat.Each(this._select, function (o) {
                o.style.visibility = "visible";
            });
        }
    }
};

Number.prototype.NaN0 = function () {
    return isNaN(this) ? 0 : this;
}
var DragBox = {
    iMouseDown : false,
    dragObject : null,
    dragTitle : null,
    mouseOffset : {},
    makeDraggable : function (item, titleBar) {
        if (!item)
            return;
        DragBox.dragObject = item;
        DragBox.dragTitle = titleBar;
        DragBox.dragTitle.onmousedown = DragBox.mouseDown;
        Hat.addEvent(document, "mousemove", DragBox.mouseMove);
        Hat.addEvent(document, "mouseup", DragBox.mouseUp);
    },
    getMouseOffset : function (target, ev) {
        ev = ev || window.event;
        var docPos = DragBox.getPosition(target);
        var mousePos = DragBox.mouseCoords(ev);
        return {
            x : mousePos.x - docPos.x,
            y : mousePos.y - docPos.y
        };
    },
    getPosition : function (e) {
        var left = 0;
        var top = 0;
        while (e.offsetParent) {
            left += e.offsetLeft + (e.currentStyle ? (parseInt(e.currentStyle.borderLeftWidth)).NaN0() : 0);
            top += e.offsetTop + (e.currentStyle ? (parseInt(e.currentStyle.borderTopWidth)).NaN0() : 0);
            e = e.offsetParent;
        }
        left += e.offsetLeft + (e.currentStyle ? (parseInt(e.currentStyle.borderLeftWidth)).NaN0() : 0);
        top += e.offsetTop + (e.currentStyle ? (parseInt(e.currentStyle.borderTopWidth)).NaN0() : 0);
        return {
            x : left,
            y : top
        };
    },
    mouseCoords : function (ev) {
        if (ev.pageX || ev.pageY) {
            return {
                x : ev.pageX,
                y : ev.pageY
            };
        }
        return {
            x : ev.clientX + document.body.scrollLeft - document.body.clientLeft,
            y : ev.clientY + document.body.scrollTop - document.body.clientTop
        };
    },
    mouseDown : function (ev) {
        var event  = ev || window.event;
        DragBox.iMouseDown = true;
        DragBox.mouseOffset = DragBox.getMouseOffset(DragBox.dragObject, ev);
        this.setCapture && this.setCapture();
        return false;
    },
    mouseUp : function (ev) {
        DragBox.iMouseDown = false;
        DragBox.dragTitle.releaseCapture && DragBox.dragTitle.releaseCapture();
    },
    mouseMove : function (ev) {
        if(!DragBox.iMouseDown) return false;
        if (DragBox.dragObject) {
            ev = ev || window.event;
            var target = ev.target || ev.srcElement;
            var mousePos = DragBox.mouseCoords(ev);
            var maxL = document.documentElement.clientWidth - DragBox.dragObject.offsetWidth;
            var maxT = document.documentElement.clientHeight - DragBox.dragObject.offsetHeight;

            var event = event || window.event;
            var iT = Number(mousePos.y - DragBox.mouseOffset.y);
            var iL = Number(mousePos.x - DragBox.mouseOffset.x);

            iL = iL < 0 ? 0 : iL;
            iL = iL > maxL ? maxL : iL;
            iT = iT < 0 ? 0 : iT;
            iT = iT > maxT ? maxT : iT;
            DragBox.dragObject.style.marginTop = DragBox.dragObject.style.marginLeft = 0;
            DragBox.dragObject.style.top = iT + "px";
            DragBox.dragObject.style.left =  iL + "px";
            return false;
        }
    }
}

var WinPop = {
    init : {},
    Open : function (id) {
        if (typeof WinPop.init[id] == "undefined") {
            WinPop.init[id] = new LightBox(id);
            WinPop.init[id].Over = true;
            WinPop.init[id].OverLay.Color = "#000";
            WinPop.init[id].OverLay.opacity = 50;
            WinPop.init[id].Fixed = true;
            WinPop.init[id].Center = true;
            WinPop.init[id].Drag = true;
            WinPop.init[id].DragClass = 'tips_layer_tit';
        }
        WinPop.init[id].Show();
    },
    Close : function (id) {
        for (var i in WinPop.init) {
            if (i == id){
                WinPop.init[i].Close();
                WinPop.init[id] = undefined;
            }
        }
    }
};
