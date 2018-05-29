var showCalendar; (function() {
	Calendar = function(mondayFirst, dateStr, onSelected, onClose) {
		this.activeDiv = null;
		this.currentDateEl = null;
		this.checkDisabled = null;
		this.timeout = null;
		this.onSelected = onSelected || null;
		this.onClose = onClose || null;
		this.dragging = false;
		this.hidden = false;
		this.minYear = 1970;
		this.maxYear = 2050;
		this.dateFormat = Calendar._TT["DEF_DATE_FORMAT"];
		this.ttDateFormat = Calendar._TT["TT_DATE_FORMAT"];
		this.isPopup = true;
		this.weekNumbers = false;
		this.mondayFirst = mondayFirst;
		this.dateStr = dateStr;
		this.ar_days = null;
		this.table = null;
		this.element = null;
		this.tbody = null;
		this.firstdayname = null;
		this.monthsCombo = null;
		this.yearsCombo = null;
		this.hilitedMonth = null;
		this.activeMonth = null;
		this.hilitedYear = null;
		this.activeYear = null;
		this.dateClicked = false;
		if (!Calendar._DN3) {
			var ar = new Array();
			for (var i = 8; i > 0;) {
				ar[--i] = Calendar._DN[i].substr(0, 3);
			}
			Calendar._DN3 = ar;
			ar = new Array();
			for (var i = 12; i > 0;) {
				ar[--i] = Calendar._MN[i].substr(0, 3);
			}
			Calendar._MN3 = ar;
		}
	};
	Calendar._C = null;
	Calendar.is_ie = (/msie/i.test(navigator.userAgent) && !/opera/i.test(navigator.userAgent));
	Calendar._DN3 = null;
	Calendar._MN3 = null;
	Calendar.getAbsolutePos = function(el) {
		var r = {
			x: el.offsetLeft,
			y: el.offsetTop
		};
		if (el.offsetParent) {
			var tmp = Calendar.getAbsolutePos(el.offsetParent);
			r.x += tmp.x;
			r.y += tmp.y;
		}
		return r;
	};
	Calendar.isRelated = function(el, evt) {
		var related = evt.relatedTarget;
		if (!related) {
			var type = evt.type;
			if (type == "mouseover") {
				related = evt.fromElement;
			} else if (type == "mouseout") {
				related = evt.toElement;
			}
		}
		while (related) {
			if (related == el) {
				return true;
			}
			related = related.parentNode;
		}
		return false;
	};
	Calendar.removeClass = function(el, className) {
		if (! (el && el.className)) {
			return;
		}
		var cls = el.className.split(" ");
		var ar = new Array();
		for (var i = cls.length; i > 0;) {
			if (cls[--i] != className) {
				ar[ar.length] = cls[i];
			}
		}
		el.className = ar.join(" ");
	};
	Calendar.addClass = function(el, className) {
		Calendar.removeClass(el, className);
		el.className += " " + className;
	};
	Calendar.getElement = function(ev) {
		if (Calendar.is_ie) {
			return window.event.srcElement;
		} else {
			return ev.currentTarget;
		}
	};
	Calendar.getTargetElement = function(ev) {
		if (Calendar.is_ie) {
			return window.event.srcElement;
		} else {
			return ev.target;
		}
	};
	Calendar.stopEvent = function(ev) {
		if (Calendar.is_ie) {
			window.event.cancelBubble = true;
			window.event.returnValue = false;
		} else {
			ev.preventDefault();
			ev.stopPropagation();
		}
		return false;
	};
	Calendar.addEvent = function(el, evname, func) {
		if (el.attachEvent) { // IE
			el.attachEvent("on" + evname, func);
		} else if (el.addEventListener) { // Gecko / W3C
			el.addEventListener(evname, func, true);
		} else { // Opera (or old browsers)
			el["on" + evname] = func;
		}
	};
	Calendar.removeEvent = function(el, evname, func) {
		if (el.detachEvent) { // IE
			el.detachEvent("on" + evname, func);
		} else if (el.removeEventListener) { // Gecko / W3C
			el.removeEventListener(evname, func, true);
		} else { // Opera (or old browsers)
			el["on" + evname] = null;
		}
	};

	Calendar.createElement = function(type, parent) {
		var el = null;
		if (document.createElementNS) {
			el = document.createElementNS("http://www.w3.org/1999/xhtml", type);
		} else {
			el = document.createElement(type);
		}
		if (typeof parent != "undefined") {
			parent.appendChild(el);
		}
		return el;
	};
	Calendar._add_evs = function(el) {
		with(Calendar) {
			addEvent(el, "mouseover", dayMouseOver);
			addEvent(el, "mousedown", dayMouseDown);
			addEvent(el, "mouseout", dayMouseOut);
			if (is_ie) {
				addEvent(el, "dblclick", dayMouseDblClick);
				el.setAttribute("unselectable", true);
			}
		}
	};
	Calendar.findMonth = function(el) {
		if (typeof el.month != "undefined") {
			return el;
		} else if (typeof el.parentNode.month != "undefined") {
			return el.parentNode;
		}
		return null;
	};
	Calendar.findYear = function(el) {
		if (typeof el.year != "undefined") {
			return el;
		} else if (typeof el.parentNode.year != "undefined") {
			return el.parentNode;
		}
		return null;
	};
	Calendar.showMonthsCombo = function() {
		var cal = Calendar._C;
		if (!cal) {
			return false;
		}
		var cal = cal;
		var cd = cal.activeDiv;
		var mc = cal.monthsCombo;
		if (cal.hilitedMonth) {
			Calendar.removeClass(cal.hilitedMonth, "hilite");
		}
		if (cal.activeMonth) {
			Calendar.removeClass(cal.activeMonth, "active");
		}
		var mon = cal.monthsCombo.getElementsByTagName("div")[cal.date.getMonth()];
		Calendar.addClass(mon, "active");
		cal.activeMonth = mon;
		mc.style.left = cd.offsetLeft + "px";
		mc.style.top = (cd.offsetTop + cd.offsetHeight) + "px";
		mc.style.display = "block";
	};
	Calendar.showYearsCombo = function(fwd) {
		var cal = Calendar._C;
		if (!cal) {
			return false;
		}
		var cal = cal;
		var cd = cal.activeDiv;
		var yc = cal.yearsCombo;
		if (cal.hilitedYear) {
			Calendar.removeClass(cal.hilitedYear, "hilite");
		}
		if (cal.activeYear) {
			Calendar.removeClass(cal.activeYear, "active");
		}
		cal.activeYear = null;
		var Y = cal.date.getFullYear() + (fwd ? 1 : -1);
		var yr = yc.firstChild;
		var show = false;
		for (var i = 12; i > 0; --i) {
			if (Y >= cal.minYear && Y <= cal.maxYear) {
				yr.firstChild.data = Y;
				yr.year = Y;
				yr.style.display = "block";
				show = true;
			} else {
				yr.style.display = "none";
			}
			yr = yr.nextSibling;
			Y += fwd ? 2 : -2;
		}
		if (show) {
			yc.style.left = cd.offsetLeft + "px";
			yc.style.top = (cd.offsetTop + cd.offsetHeight) + "px";
			yc.style.display = "block";
		}
	};
	Calendar.tableMouseUp = function(ev) {
		var cal = Calendar._C;
		if (!cal) {
			return false;
		}
		if (cal.timeout) {
			clearTimeout(cal.timeout);
		}
		var el = cal.activeDiv;
		if (!el) {
			return false;
		}
		var target = Calendar.getTargetElement(ev);
		Calendar.removeClass(el, "active");
		if (target == el || target.parentNode == el) {
			Calendar.cellClick(el);
		}
		var mon = Calendar.findMonth(target);
		var date = null;
		if (mon) {
			date = new Date(cal.date);
			if (mon.month != date.getMonth()) {
				date.setMonth(mon.month);
				cal.setDate(date);
				cal.dateClicked = false;
				cal.callHandler();
			}
		} else {
			var year = Calendar.findYear(target);
			if (year) {
				date = new Date(cal.date);
				if (year.year != date.getFullYear()) {
					date.setFullYear(year.year);
					cal.setDate(date);
					cal.dateClicked = false;
					cal.callHandler();
				}
			}
		}
		with(Calendar) {
			removeEvent(document, "mouseup", tableMouseUp);
			removeEvent(document, "mouseover", tableMouseOver);
			removeEvent(document, "mousemove", tableMouseOver);
			cal._hideCombos();
			_C = null;
			return stopEvent(ev);
		}

	};
	Calendar.tableMouseOver = function(ev) {
		var cal = Calendar._C;
		if (!cal) {
			return;
		}
		var el = cal.activeDiv;
		var target = Calendar.getTargetElement(ev);
		if (target == el || target.parentNode == el) {
			Calendar.addClass(el, "hilite active");
			Calendar.addClass(el.parentNode, "rowhilite");
		} else {
			Calendar.removeClass(el, "active");
			Calendar.removeClass(el, "hilite");
			Calendar.removeClass(el.parentNode, "rowhilite");
		}
		var mon = Calendar.findMonth(target);
		if (mon) {
			if (mon.month != cal.date.getMonth()) {
				if (cal.hilitedMonth) {
					Calendar.removeClass(cal.hilitedMonth, "hilite");
				}
				Calendar.addClass(mon, "hilite");
				cal.hilitedMonth = mon;
			} else if (cal.hilitedMonth) {
				Calendar.removeClass(cal.hilitedMonth, "hilite");
			}
		} else {
			var year = Calendar.findYear(target);
			if (year) {
				if (year.year != cal.date.getFullYear()) {
					if (cal.hilitedYear) {
						Calendar.removeClass(cal.hilitedYear, "hilite");
					}
					Calendar.addClass(year, "hilite");
					cal.hilitedYear = year;
				} else if (cal.hilitedYear) {
					Calendar.removeClass(cal.hilitedYear, "hilite");
				}
			}
		}
		return Calendar.stopEvent(ev);
	};
	Calendar.tableMouseDown = function(ev) {
		if (Calendar.getTargetElement(ev) == Calendar.getElement(ev)) {
			return Calendar.stopEvent(ev);
		}
	};
	Calendar.calDragIt = function(ev) {
		var cal = Calendar._C;
		if (! (cal && cal.dragging)) {
			return false;
		}
		var posX;
		var posY;
		if (Calendar.is_ie) {
			posY = window.event.clientY + document.body.scrollTop;
			posX = window.event.clientX + document.body.scrollLeft;
		} else {
			posX = ev.pageX;
			posY = ev.pageY;
		}
		cal.hideShowCovered();
		var st = cal.element.style;
		st.left = (posX - cal.xOffs) + "px";
		st.top = (posY - cal.yOffs) + "px";
		return Calendar.stopEvent(ev);
	};
	Calendar.calDragEnd = function(ev) {
		var cal = Calendar._C;
		if (!cal) {
			return false;
		}
		cal.dragging = false;
		with(Calendar) {
			removeEvent(document, "mousemove", calDragIt);
			removeEvent(document, "mouseover", stopEvent);
			removeEvent(document, "mouseup", calDragEnd);
			tableMouseUp(ev);
		}
		cal.hideShowCovered();
	};
	Calendar.batchs= function (Obj, Fn) {
		for(var i = 0,len = Obj.length; i < len; i++) {
			Fn.call(Obj[i],i);
		}
	}
	Calendar.Zeroize = function (Str) {
		return Str.toString().replace(/^\d{1}$/,function ($1){return "0"+$1;});
	};
	Calendar.dayMouseDown = function(ev) {
		var el = Calendar.getElement(ev);
		if (el.disabled) {
			return false;
		}
		if (el.id == "objclose" || el.id == "objmprev" || el.id == "objmnext" || el.id == "objyprev" || el.id == "objynext") el = el.parentNode;
		var cal = el.calendar;
		cal.activeDiv = el;
		Calendar._C = cal;
		if (el.navtype != 300) with(Calendar) {
			addClass(el, "hilite active");
			addEvent(document, "mouseover", tableMouseOver);
			addEvent(document, "mousemove", tableMouseOver);
			addEvent(document, "mouseup", tableMouseUp);
			addEvent(el, "click", Calendar.elClick = function (ev) {
				cal._hideCombos();
				if ( el.navtype == 2013 ) {
					cal.monthsCombo.style.left = el.offsetLeft - 10 + "px";
					cal.monthsCombo.style.top = el.offsetTop + el.offsetHeight + "px";
					cal.monthsCombo.style.display = "block";
					var comboM = document.getElementById("comboM");
					Calendar.addEvent(comboM, "mouseover", function () {
						cal.monthsCombo.style.display = "block";
					});
					Calendar.addEvent(comboM, "mouseout", function () {
						cal.monthsCombo.style.display = "none";
					});
					Calendar.batchs(comboM.childNodes,function () {
						var t = this;
						Calendar.addEvent(t, "click", function () {
							var data = new Date(cal.date);
							data1 = [data.getFullYear(), Calendar.Zeroize(parseInt(t.innerHTML)), Calendar.Zeroize(data.getDate())];
							selected(cal, data1.join("-"));
							cal.setDate(new Date(data1[0], data1[1]-1, data1[2]));
							cal._hideCombos();
						});
						Calendar.addEvent(t, "mouseover", function () {
							t.style.backgroundColor = "#ffffff";
						});
						Calendar.addEvent(t, "mouseout", function () {
							t.style.backgroundColor = "#D8E0E4";
						});
					});
				} else if ( el.navtype == 2012 ) {
					cal.yearsCombo.style.left = el.offsetLeft - 6 + "px";
					cal.yearsCombo.style.top = el.offsetTop + el.offsetHeight + "px";
					cal.yearsCombo.style.display = "block";
					var comboY = document.getElementById("comboY");
					Calendar.addEvent(comboY, "mouseover", function () {
						cal.yearsCombo.style.display = "block";
					});
					Calendar.addEvent(comboY, "mouseout", function () {
						cal.yearsCombo.style.display = "none";
					});
					Calendar.batchs(comboY.childNodes,function () {
						var t = this;
						Calendar.addEvent(t, "click", function () {
							var data = new Date(cal.date),
								data2 = [parseInt(t.innerHTML), Calendar.Zeroize(data.getMonth()+1), Calendar.Zeroize(data.getDate())];
							selected(cal, data2.join("-"));
							cal.setDate(new Date(data2[0], data2[1]-1, data2[2]));
							cal._hideCombos();
						});
						Calendar.addEvent(t, "mouseover", function () {
							t.style.backgroundColor = "#ffffff";
						});
						Calendar.addEvent(t, "mouseout", function () {
							t.style.backgroundColor = "#D8E0E4";
						});
					});
				}
			});
		} else if (cal.isPopup) {
			cal._dragStart(ev);
		}
		if (el.navtype == -1 || el.navtype == 2013) {
//cal.timeout = setTimeout("Calendar.showMonthsCombo()", 250);
		} else if (el.navtype == -2 || el.navtype == 2012) {
//cal.timeout = setTimeout((el.navtype > 0) ? "Calendar.showYearsCombo(true)": "Calendar.showYearsCombo(false)", 250);
		} else {
			cal.timeout = null;
		}
		return Calendar.stopEvent(ev);
	};
	Calendar.dayMouseDblClick = function(ev) {
		return; //注释双击效果;
		Calendar.cellClick(Calendar.getElement(ev));
		if (Calendar.is_ie) {
			document.selection.empty();
		}
	};
	Calendar.dayMouseOver = function(ev) {
		var el = Calendar.getElement(ev);
		if (Calendar.isRelated(el, ev) || Calendar._C || el.disabled) {
			return false;
		}
		if (el.id == "objclose" || el.id == "objmprev" || el.id == "objmnext" || el.id == "objyprev" || el.id == "objynext") el = el.parentNode;
		if (el.ttip) {
			if (el.ttip.substr(0, 1) == "_") {
				var date = null;
				with(el.calendar.date) {
					date = new Date(getFullYear(), getMonth(), el.caldate);
				}
				el.ttip = "星期" + date.print("D") + "  " + date.print("y") + "年 " + date.print("M") + date.print("d") + "日";
			}
			el.calendar.tooltips.firstChild.data = el.ttip;
		}
		if (el.navtype != 300) {
			Calendar.addClass(el, "hilite");
			if (el.caldate) {
				Calendar.addClass(el.parentNode, "rowhilite");
			}
		}
		return Calendar.stopEvent(ev);
	};
	Calendar.dayMouseOut = function(ev) {
		with(Calendar) {
			var el = getElement(ev);
			if (isRelated(el, ev) || _C || el.disabled) {
				return false;
			}
			removeClass(el, "hilite");
			if (el.caldate) {
				removeClass(el.parentNode, "rowhilite");
			}
			return stopEvent(ev);
		}
	};
	Calendar.cellClick = function(el) {
		var cal = el.calendar;
		var closing = false;
		var newdate = false;
		var date = null;
		if (typeof el.navtype == "undefined") {
			Calendar.removeClass(cal.currentDateEl, "selected");
			Calendar.addClass(el, "selected");
			closing = (cal.currentDateEl == el);
			if (!closing) {
				cal.currentDateEl = el;
			}
			cal.date.setDate(el.caldate);
			date = cal.date;
			newdate = true;
			cal.dateClicked = true;
			cal.callCloseHandler(); //单击也引用关闭;added by jarry
		} else {
			if (el.navtype == 200) {
				Calendar.removeClass(el, "hilite");
				cal.callCloseHandler();
				return;
			}
			date = (el.navtype == 0) ? new Date() : new Date(cal.date);
			cal.dateClicked = (el.navtype == 0);
			var year = date.getFullYear();
			var mon = date.getMonth();
			function setMonth(m) {
				var day = date.getDate();
				var max = date.getMonthDays(m);
				if (day > max) {
					date.setDate(max);
				}
				date.setMonth(m);
			};
			switch (el.navtype) {
				case - 2 : if (year > cal.minYear) {
					date.setFullYear(year - 1);
				}
					break;
				case - 1 : if (mon > 0) {
					setMonth(mon - 1);
				} else if (year-->cal.minYear) {
					date.setFullYear(year);
					setMonth(11);
				}
					break;
				case 1:
					if (mon < 11) {
						setMonth(mon + 1);
					} else if (year < cal.maxYear) {
						date.setFullYear(year + 1);
						setMonth(0);
					}
					break;
				case 2:
					if (year < cal.maxYear) {
						date.setFullYear(year + 1);
					}
					break;
				case 100:
					cal.setMondayFirst(!cal.mondayFirst);
					return;
				case 0:
					if ((typeof cal.checkDisabled == "function") && cal.checkDisabled(date)) {
						return false;
					}
					break;
			}
			if (!date.equalsTo(cal.date)) {
				cal.setDate(date);
				newdate = true;
			}
		}
		if (newdate) {
			cal.callHandler();
		}
		if (closing) {
			Calendar.removeClass(el, "hilite");
			cal.callCloseHandler();
		}
	};
	Calendar.prototype.create = function(_par) {
		var parent = null;
		if (!_par) {
			parent = document.getElementsByTagName("body")[0];
			this.isPopup = true;
		} else {
			parent = _par;
			this.isPopup = false;
		}
		this.date = this.dateStr ? new Date(this.dateStr) : new Date();
		var table = Calendar.createElement("table");
		this.table = table;
		table.cellSpacing = 0;
		table.cellPadding = 0;
		table.calendar = this;
		Calendar.addEvent(table, "mousedown", Calendar.tableMouseDown);
		var div = Calendar.createElement("div"),
			ifr = Calendar.createElement("iframe");
		this.element = div;
		div.className = "calendar";
		if (this.isPopup) {
			div.style.position = "absolute";
			div.style.display = "none";
		}
		ifr.style.position = "absolute";
		ifr.style.width = "100%";
		ifr.style.height = "221px";
		ifr.style.opacity = "0";
		ifr.style.filter = "alpha(opacity=0)";
		ifr.style.border = "1px solid #ddd";
		ifr.style.zIndex = "-1";
		ifr.style.overflow = "visible";
		div.appendChild(ifr);
		div.appendChild(table);
		var thead = Calendar.createElement("thead", table);
		var cell = null;
		var row = null;
		var cal = this;
		var hh = function(text, cs, navtype) {
			cell = Calendar.createElement("td", row);
			cell.colSpan = cs;
			cell.className = "button";
			Calendar._add_evs(cell);
			cell.calendar = cal;
			cell.navtype = navtype;
			if (text.substr(0, 1) != "&") {
				if (text.substr(0, 3) == "<a ") cell.innerHTML = text; //如果是图片直接添加;
				else cell.appendChild(document.createTextNode(text));
			} else {
				cell.innerHTML = text;
			}
			return cell;
		};
		row = Calendar.createElement("tr", thead);
		row.className = "headrow";
		var tdd = Calendar.createElement("td", row);
		tdd.colSpan = 7;
		tdd.height = 26;
		var table2 = Calendar.createElement("table", tdd);
		table2.cellSpacing = 0;
		table2.cellPadding = 0;
		table2.width = "100%";
		var thead2 = Calendar.createElement("thead", table2);
		row = Calendar.createElement("tr", thead2);
		row.className = "headrow";
		this._nav_py = hh("<a id='objyprev' href='javascript:;'></a>", 1, -2);
		this._nav_py.width = 25;
		this._nav_py.ttip = Calendar._TT["PREV_YEAR"];
		this.title = hh("", 1, 300);
		this.title.className = "title";
		this.title.navtype = 2012;
		this.title.width = 40;
		this._nav_ny = hh("<a id='objynext' href='javascript:;'></a>", 1, 2);
		this._nav_ny.width = 25;
		this._nav_ny.ttip = Calendar._TT["NEXT_YEAR"];
		this._nav_pm = hh("<a id='objmprev' href='javascript:;'></a>", 1, -1);
		this._nav_pm.width = 25;
		this._nav_pm.ttip = Calendar._TT["PREV_MONTH"];
		this.title2 = hh("", 1, 300);
		this.title2.className = "title";
		this.title2.navtype = 2013;
		this.title2.width = 28;
		this._nav_nm = hh("<a id='objmnext' href='javascript:;'></a>", 1, 1);
		this._nav_nm.width = 25;
		this._nav_nm.ttip = Calendar._TT["NEXT_MONTH"];

		if (this.isPopup) {
			this.title.ttip = Calendar._TT["DRAG_TO_MOVE"];
			var td_close = hh("<a id='objclose' href='javascript:;'></a>", 1, 200);
			td_close.ttip = Calendar._TT["CLOSE"];
			td_close.align = "right";
		}
		row = Calendar.createElement("tr", thead);
		row.className = "daynames";
		if (this.weekNumbers) {
			cell = Calendar.createElement("td", row);
			cell.className = "name wn";
			cell.appendChild(document.createTextNode(Calendar._TT["WK"]));
		}
		for (var i = 7; i > 0; --i) {
			cell = Calendar.createElement("td", row);
			cell.appendChild(document.createTextNode(""));
			if (!i) {
				cell.navtype = 100;
				cell.calendar = this;
				Calendar._add_evs(cell);
			}
		}
		this.firstdayname = (this.weekNumbers) ? row.firstChild.nextSibling: row.firstChild;
		this._displayWeekdays();
		var tbody = Calendar.createElement("tbody", table);
		this.tbody = tbody;
		for (i = 6; i > 0; --i) {
			row = Calendar.createElement("tr", tbody);
			if (this.weekNumbers) {
				cell = Calendar.createElement("td", row);
				cell.appendChild(document.createTextNode(""));
			}
			for (var j = 7; j > 0; --j) {
				cell = Calendar.createElement("td", row);
				cell.appendChild(document.createTextNode(""));
				cell.calendar = this;
				Calendar._add_evs(cell);
			}
		}
		var tfoot = Calendar.createElement("tfoot", table);
		row = Calendar.createElement("tr", tfoot);

		tfoot.className = "tfootf";
		row.className = "footrow";
		row.height = 26;
		cell = hh(Calendar._TT["SEL_DATE"], this.weekNumbers ? 8 : 6, 300);
		cell.className = "ttip";
		tdClear = Calendar.createElement("td", row);
		tdClear.innerHTML = "清空";
		tdClear.className = "footClear";
		tdClear.onclick = function () { cal.sel.value = ""; cal.hide(); };
		if (this.isPopup) {
			cell.ttip = Calendar._TT["DRAG_TO_MOVE"];
		}
		this.tooltips = cell;
		div = Calendar.createElement("div", this.element);
		this.monthsCombo = div;
		div.className = "combo";
		div.id = "comboM";
		for (i = 0; i < Calendar._MN.length; ++i) {
			var mn = Calendar.createElement("div");
			mn.className = "label";
			mn.month = i;
			mn.appendChild(document.createTextNode(Calendar._MN3[i]));
			div.appendChild(mn);
		}
		div = Calendar.createElement("div", this.element);
		this.yearsCombo = div;
		div.className = "combo";
		div.id = "comboY";
		for (i = 1970; i < 2051; ++i) {
			var yr = Calendar.createElement("div");
			yr.className = "label";
			yr.appendChild(document.createTextNode(i));
			div.appendChild(yr);
		}
		this._init(this.mondayFirst, this.date);
		parent.appendChild(this.element);
	};
	Calendar._keyEvent = function(ev) {
		/*
		 if (!window.calendar) {
		 return false;
		 } (Calendar.is_ie) && (ev = window.event);
		 var cal = window.calendar;
		 var act = (Calendar.is_ie || ev.type == "keypress");
		 if (ev.ctrlKey) {
		 switch (ev.keyCode) {
		 case 37:
		 // KEY left
		 act && Calendar.cellClick(cal._nav_pm);
		 break;
		 case 38:
		 // KEY up
		 act && Calendar.cellClick(cal._nav_py);
		 break;
		 case 39:
		 // KEY right
		 act && Calendar.cellClick(cal._nav_nm);
		 break;
		 case 40:
		 // KEY down
		 act && Calendar.cellClick(cal._nav_ny);
		 }

		 } else
		 switch (ev.keyCode) {
		 case 32:
		 // KEY space (now)
		 Calendar.cellClick(cal._nav_now);
		 break;
		 case 27:
		 // KEY esc
		 act && cal.hide();
		 break;
		 case 37:
		 // KEY left
		 case 38:
		 // KEY up
		 case 39:
		 // KEY right
		 case 40:
		 // KEY down
		 if (act) {
		 var date = cal.date.getDate() - 1;
		 var el = cal.currentDateEl;
		 var ne = null;
		 var prev = (ev.keyCode == 37) || (ev.keyCode == 38);
		 switch (ev.keyCode) {
		 case 37:
		 // KEY left
		 (--date >= 0) && (ne = cal.ar_days[date]);
		 break;
		 case 38:
		 // KEY up
		 date -= 7; (date >= 0) && (ne = cal.ar_days[date]);
		 break;
		 case 39:
		 // KEY right
		 (++date < cal.ar_days.length) && (ne = cal.ar_days[date]);
		 break;
		 case 40:
		 // KEY down
		 date += 7; (date < cal.ar_days.length) && (ne = cal.ar_days[date]);
		 break;
		 }
		 if (!ne) {
		 if (prev) {
		 Calendar.cellClick(cal._nav_pm);
		 } else {
		 Calendar.cellClick(cal._nav_nm);
		 }
		 date = (prev) ? cal.date.getMonthDays() : 1;
		 el = cal.currentDateEl;
		 ne = cal.ar_days[date - 1];
		 }
		 Calendar.removeClass(el, "selected");
		 Calendar.addClass(ne, "selected");
		 cal.date.setDate(ne.caldate);
		 cal.callHandler();
		 cal.currentDateEl = ne;
		 }
		 break;
		 case 13:
		 // KEY enter
		 if (act) {
		 cal.callHandler();
		 cal.hide();
		 }
		 break;
		 }
		 */
// return Calendar.stopEvent(ev);
	};
	Calendar.prototype._init = function(mondayFirst, date) {
		var today = new Date();
		var year = date.getFullYear();
		if (year < this.minYear) {
			year = this.minYear;
			date.setFullYear(year);
		} else if (year > this.maxYear) {
			year = this.maxYear;
			date.setFullYear(year);
		}
		this.mondayFirst = mondayFirst;
		this.date = new Date(date);
		var month = date.getMonth();
		var mday = date.getDate();
		var no_days = date.getMonthDays();
		date.setDate(1);
		var wday = date.getDay();
		var MON = mondayFirst ? 1 : 0;
		var SAT = mondayFirst ? 5 : 6;
		var SUN = mondayFirst ? 6 : 0;
		if (mondayFirst) {
			wday = (wday > 0) ? (wday - 1) : 6;
		}
		var iday = 1;
		var row = this.tbody.firstChild;
		var MN = Calendar._MN3[month];
		var hasToday = ((today.getFullYear() == year) && (today.getMonth() == month));
		var todayDate = today.getDate();
		var week_number = date.getWeekNumber();
		var ar_days = new Array();
		for (var i = 0; i < 6; ++i) {
			if (iday > no_days) {
				row.className = "emptyrow";
				row = row.nextSibling;
				continue;
			}
			var cell = row.firstChild;
			if (this.weekNumbers) {
				cell.className = "day wn";
				cell.firstChild.data = week_number;
				cell = cell.nextSibling;
			}++week_number;
			row.className = "daysrow";
			for (var j = 0; j < 7; ++j) {
				cell.className = "day";
				if ((!i && j < wday) || iday > no_days) {
					cell.innerHTML = "&nbsp;";
					cell.disabled = true;
					cell = cell.nextSibling;
					continue;
				}
				cell.disabled = false;
				cell.firstChild.data = iday;
				if (typeof this.checkDisabled == "function") {
					date.setDate(iday);
					if (this.checkDisabled(date)) {
						cell.className += " disabled";
						cell.disabled = true;
					}
				}
				if (!cell.disabled) {
					ar_days[ar_days.length] = cell;
					cell.caldate = iday;
					cell.ttip = "_";
					if (iday == mday) {
						cell.className += " selected";
						this.currentDateEl = cell;
					}
					if (hasToday && (iday == todayDate)) {
						cell.className += " today";
						cell.ttip += Calendar._TT["PART_TODAY"];
					}
					if (wday == SAT || wday == SUN) {
						cell.className += " weekend";
					}
				}++iday; ((++wday) ^ 7) || (wday = 0);
				cell = cell.nextSibling;
			}
			row = row.nextSibling;
		}
		this.ar_days = ar_days;
		this.title.firstChild.data = year + "年";
		this.title2.firstChild.data = Calendar._MN[month];
	};
	Calendar.prototype.setDate = function(date) {
		if (!date.equalsTo(this.date)) {
			this._init(this.mondayFirst, date);
		}
	};
	Calendar.prototype.refresh = function() {
		this._init(this.mondayFirst, this.date);
	};
	Calendar.prototype.setMondayFirst = function(mondayFirst) {
		this._init(mondayFirst, this.date);
		this._displayWeekdays();
	};
	Calendar.prototype.setDisabledHandler = function(unaryFunction) {
		this.checkDisabled = unaryFunction;
	};
	Calendar.prototype.setRange = function(a, z) {
		this.minYear = a;
		this.maxYear = z;
	};
	Calendar.prototype.callHandler = function() {
		if (this.onSelected) {
			this.onSelected(this, this.date.print(this.dateFormat));
		}
	};
	Calendar.prototype.callCloseHandler = function() {
		if (this.onClose) {
			this.onClose(this);
		}
		this.hideShowCovered();
	};
	Calendar.prototype.destroy = function() {
		var el = this.element.parentNode;
		el.removeChild(this.element);
		Calendar._C = null;
	};
	Calendar.prototype.reparent = function(new_parent) {
		var el = this.element;
		el.parentNode.removeChild(el);
		new_parent.appendChild(el);
	};
	Calendar._checkCalendar = function(ev) {
		if (!window.calendar) {
			return false;
		}
		var el = Calendar.is_ie ? Calendar.getElement(ev) : Calendar.getTargetElement(ev);
		for (; el != null && el != calendar.element; el = el.parentNode);
		if (el == null) {
			window.calendar.callCloseHandler();
			return Calendar.stopEvent(ev);
		}
	};
	Calendar.prototype.show = function() {
		var rows = this.table.getElementsByTagName("tr");
		for (var i = rows.length; i > 0;) {
			var row = rows[--i];
			Calendar.removeClass(row, "rowhilite");
			var cells = row.getElementsByTagName("td");
			for (var j = cells.length; j > 0;) {
				var cell = cells[--j];
				Calendar.removeClass(cell, "hilite");
				Calendar.removeClass(cell, "active");
			}
		}
		this.element.style.display = "block";
		this.hidden = false;
		if (this.isPopup) {
			window.calendar = this;
			Calendar.addEvent(document, "keydown", Calendar._keyEvent);
			Calendar.addEvent(document, "keypress", Calendar._keyEvent);
			Calendar.addEvent(document, "mousedown", Calendar._checkCalendar);
		}
		this.hideShowCovered();
	};
	Calendar.prototype.hide = function() {
		if (this.isPopup) {
			Calendar.removeEvent(document, "keydown", Calendar._keyEvent);
			Calendar.removeEvent(document, "keypress", Calendar._keyEvent);
			Calendar.removeEvent(document, "mousedown", Calendar._checkCalendar);
		}
		this.element.style.display = "none";
		this.hidden = true;
		this.hideShowCovered();
	};
	Calendar.prototype.showAt = function(x, y, el) {
		var s = this.element.style,
			doh = document.documentElement.clientHeight,
			scrTop = (top.document.documentElement || top.document.body).scrollTop,
			scrWidth = (document.documentElement || document.body).offsetWidth,
			pl = Calendar.getAbsolutePos(el);
		calendar.element.style.visibility = "hidden";
		this.show();
		x = scrWidth < pl.x + this.element.offsetWidth ?pl.x - ( pl.x + this.element.offsetWidth - scrWidth ) : pl.x;
		y = pl.y > scrTop + this.element.offsetHeight ? y - this.element.offsetHeight - el.offsetHeight - 3 : y;
		s.left = x < 0 ? 0 : x + "px";
		s.top = y + "px";
		calendar.element.style.visibility = "";
	};
	Calendar.prototype.showAtElement = function(el, opts) {
		var p = Calendar.getAbsolutePos(el);
		if (!opts || typeof opts != "string") {
			this.showAt(p.x, p.y + el.offsetHeight, el);
			return true;
		}
		this.show();
		var w = this.element.offsetWidth;
		var h = this.element.offsetHeight;
		this.hide();
		var valign = opts.substr(0, 1);
		var halign = "l";
		if (opts.length > 1) {
			halign = opts.substr(1, 1);
		}
		switch (valign) {
			case "T":
				p.y -= h;
				break;
			case "B":
				p.y += el.offsetHeight;
				break;
			case "C":
				p.y += (el.offsetHeight - h) / 2;
				break;
			case "t":
				p.y += el.offsetHeight - h;
				break;
			case "b":
				break;
		}
		switch (halign) {
			case "L":
				p.x -= w;
				break;
			case "R":
				p.x += el.offsetWidth;
				break;
			case "C":
				p.x += (el.offsetWidth - w) / 2;
				break;
			case "r":
				p.x += el.offsetWidth - w;
				break;
			case "l":
				break;
		}
		this.showAt(p.x, p.y, el);
	};
	Calendar.prototype.setDateFormat = function(str) {
		this.dateFormat = str;
	};
	Calendar.prototype.setTtDateFormat = function(str) {
		this.ttDateFormat = str;
	};
	Calendar.prototype.parseDate = function(str, fmt) {
		var y = 0;
		var m = -1;
		var d = 0;
		var a = str.split(/\W+/);
		if (!fmt) {
			fmt = this.dateFormat;
		}
		var b = fmt.split(/\W+/);
		var i = 0,
			j = 0;
		for (i = 0; i < a.length; ++i) {
			if (b[i] == "D" || b[i] == "DD") {
				continue;
			}
			if (b[i] == "d" || b[i] == "dd") {
				d = parseInt(a[i], 10);
			}
			if (b[i] == "m" || b[i] == "mm") {
				m = parseInt(a[i], 10) - 1;
			}
			if ((b[i] == "y") || (b[i] == "yy")) {
				y = parseInt(a[i], 10); (y < 100) && (y += (y > 29) ? 1900 : 2000);
			}
			if (b[i] == "M" || b[i] == "MM") {
				for (j = 0; j < 12; ++j) {
					if (Calendar._MN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) {
						m = j;
						break;
					}
				}
			}
		}
		if (y != 0 && m != -1 && d != 0) {
			this.setDate(new Date(y, m, d));
			return;
		}
		y = 0;
		m = -1;
		d = 0;
		for (i = 0; i < a.length; ++i) {
			if (a[i].search(/[a-zA-Z]+/) != -1) {
				var t = -1;
				for (j = 0; j < 12; ++j) {
					if (Calendar._MN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) {
						t = j;
						break;
					}
				}
				if (t != -1) {
					if (m != -1) {
						d = m + 1;
					}
					m = t;
				}
			} else if (parseInt(a[i], 10) <= 12 && m == -1) {
				m = a[i] - 1;
			} else if (parseInt(a[i], 10) > 31 && y == 0) {
				y = parseInt(a[i], 10); (y < 100) && (y += (y > 29) ? 1900 : 2000);
			} else if (d == 0) {
				d = a[i];
			}
		}
		if (y == 0) {
			var today = new Date();
			y = today.getFullYear();
		}
		if (m != -1 && d != 0) {
			this.setDate(new Date(y, m, d));
		}
	};
	Calendar.prototype.hideShowCovered = function() {
		function getStyleProp(obj, style) {
			var value = obj.style[style];
			if (!value) {
				if (document.defaultView && typeof(document.defaultView.getComputedStyle) == "function") { // Gecko, W3C
					value = document.defaultView.getComputedStyle(obj, "").getPropertyValue(style);
				} else if (obj.currentStyle) { // IE
					value = obj.currentStyle[style];
				} else {
					value = obj.style[style];
				}
			}
			return value;
		};
		var tags = new Array("applet");
		var el = this.element;
		var p = Calendar.getAbsolutePos(el);
		var EX1 = p.x;
		var EX2 = el.offsetWidth + EX1;
		var EY1 = p.y;
		var EY2 = el.offsetHeight + EY1;
		for (var k = tags.length; k > 0;) {
			var ar = document.getElementsByTagName(tags[--k]);
			var cc = null;
			for (var i = ar.length; i > 0;) {
				cc = ar[--i];
				p = Calendar.getAbsolutePos(cc);
				var CX1 = p.x;
				var CX2 = cc.offsetWidth + CX1;
				var CY1 = p.y;
				var CY2 = cc.offsetHeight + CY1;
				if (this.hidden || (CX1 > EX2) || (CX2 < EX1) || (CY1 > EY2) || (CY2 < EY1)) {
					if (!cc.__msh_save_visibility) {
						cc.__msh_save_visibility = getStyleProp(cc, "visibility");
					}
					cc.style.visibility = cc.__msh_save_visibility;
				} else {
					if (!cc.__msh_save_visibility) {
						cc.__msh_save_visibility = getStyleProp(cc, "visibility");
					}
					cc.style.visibility = "hidden";
				}
			}
		}

	};
	Calendar.prototype._displayWeekdays = function() {
		var MON = this.mondayFirst ? 0 : 1;
		var SUN = this.mondayFirst ? 6 : 0;
		var SAT = this.mondayFirst ? 5 : 6;
		var cell = this.firstdayname;
		for (var i = 0; i < 7; ++i) {
			cell.className = "day name";
			if (!i) {
				cell.ttip = this.mondayFirst ? Calendar._TT["SUN_FIRST"] : Calendar._TT["MON_FIRST"];
				cell.navtype = 100;
				cell.calendar = this;
				Calendar._add_evs(cell);
			}
			if (i == SUN || i == SAT) {
				Calendar.addClass(cell, "weekend");
			}
			cell.firstChild.data = Calendar._DN3[i + 1 - MON];
			cell = cell.nextSibling;
		}
	};
	Calendar.prototype._hideCombos = function() {
		this.monthsCombo.style.display = "none";
		this.yearsCombo.style.display = "none";
	};
	Calendar.prototype._dragStart = function(ev) {
		if (this.dragging) {
			return;
		}
		this.dragging = false;
		var posX;
		var posY;
		if (Calendar.is_ie) {
			posY = window.event.clientY + document.body.scrollTop;
			posX = window.event.clientX + document.body.scrollLeft;
		} else {
			posY = ev.clientY + window.scrollY;
			posX = ev.clientX + window.scrollX;
		}
		var st = this.element.style;
		this.xOffs = posX - parseInt(st.left);
		this.yOffs = posY - parseInt(st.top);
		with(Calendar) {
			addEvent(document, "mouseover", stopEvent);
			addEvent(document, "mouseup", calDragEnd);
		}
	};
	Date._MD = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	Date.SECOND = 1000
	/* milliseconds */
	;
	Date.MINUTE = 60 * Date.SECOND;
	Date.HOUR = 60 * Date.MINUTE;
	Date.DAY = 24 * Date.HOUR;
	Date.WEEK = 7 * Date.DAY;
	Date.prototype.getMonthDays = function(month) {
		var year = this.getFullYear();
		if (typeof month == "undefined") {
			month = this.getMonth();
		}
		if (((0 == (year % 4)) && ((0 != (year % 100)) || (0 == (year % 400)))) && month == 1) {
			return 29;
		} else {
			return Date._MD[month];
		}
	};
	Date.prototype.getWeekNumber = function() {
		var now = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
		var then = new Date(this.getFullYear(), 0, 1, 0, 0, 0);
		var time = now - then;
		var day = then.getDay(); (day > 3) && (day -= 4) || (day += 3);
		return Math.round(((time / Date.DAY) + day) / 7);
	};
	Date.prototype.equalsTo = function(date) {
		return ((this.getFullYear() == date.getFullYear()) && (this.getMonth() == date.getMonth()) && (this.getDate() == date.getDate()));
	};
	Date.prototype.print = function(frm) {
		var str = new String(frm);
		var m = this.getMonth();
		var d = this.getDate();
		var y = this.getFullYear();
		var wn = this.getWeekNumber();
		var w = this.getDay();
		var s = new Array();
		s["d"] = d;
		s["dd"] = (d < 10) ? ("0" + d) : d;
		s["m"] = 1 + m;
		s["mm"] = (m < 9) ? ("0" + (1 + m)) : (1 + m);
		s["y"] = y;
		s["yy"] = new String(y).substr(2, 2);
		s["w"] = wn;
		s["ww"] = (wn < 10) ? ("0" + wn) : wn;
		with(Calendar) {
			s["D"] = _DN3[w];
			s["DD"] = _DN[w];
			s["M"] = _MN3[m];
			s["MM"] = _MN[m];
		}
		var re = /(.*)(\W|^)(d|dd|m|mm|y|yy|MM|M|DD|D|w|ww)(\W|$)(.*)/;
		while (re.exec(str) != null) {
			str = RegExp.$1 + RegExp.$2 + s[RegExp.$3] + RegExp.$4 + RegExp.$5;
		}
		return str;
	};
	window.calendar = null;
//-----------------------
	Calendar._DN = new Array("日", "一", "二", "三", "四", "五", "六", "日");
	Calendar._MN = new Array("1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月");
	Calendar._TT = {};
	Calendar._TT["TOGGLE"] = "切换周显示方式";
// Calendar._TT["PREV_YEAR"] = "上一年(按住鼠标出下拉菜单)";
	Calendar._TT["PREV_YEAR"] = "上一年";
// Calendar._TT["PREV_MONTH"] = "上一月(按住鼠标出下拉菜单)";
	Calendar._TT["PREV_MONTH"] = "上一月";
	Calendar._TT["GO_TODAY"] = "到今日";
// Calendar._TT["NEXT_MONTH"] = "下一月(按住鼠标出下拉菜单)";
	Calendar._TT["NEXT_MONTH"] = "下一月";
// Calendar._TT["NEXT_YEAR"] = "下一年(按住鼠标出下拉菜单)";
	Calendar._TT["NEXT_YEAR"] = "下一年";
	Calendar._TT["SEL_DATE"] = "选择日期";
	Calendar._TT["DRAG_TO_MOVE"] = "拖动";
	Calendar._TT["DRAG_TO_MOVE"] = "";
	Calendar._TT["PART_TODAY"] = " (今日)";
	Calendar._TT["MON_FIRST"] = "首先显示星期一";
	Calendar._TT["SUN_FIRST"] = "首先显示星期日";
	Calendar._TT["CLOSE"] = "关闭";
	Calendar._TT["TODAY"] = "今日";
	Calendar._TT["DEF_DATE_FORMAT"] = "y-mm-dd";
	Calendar._TT["TT_DATE_FORMAT"] = "D, y M d";
	Calendar._TT["WK"] = "周";
//---------------------
	var oldLink = null;
	function setActiveStyleSheet(link, title) {
		var i, a, main;
		for (i = 0; (a = document.getElementsByTagName("link")[i]); i++) {
			if (a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title")) {
				a.disabled = true;
				if (a.getAttribute("title") == title) a.disabled = false;
			}
		}
		if (oldLink) oldLink.style.fontWeight = 'normal';
		oldLink = link;
		link.style.fontWeight = 'bold';
		return false;
	}

	function selected(cal, date) {
		if(calendar.hm == "hm"){
			var d = new Date();
			date += " " + d.getHours() + ":" + d.getMinutes();
		}
		if(calendar.hm == "hms"){
			var d = new Date();
			date += " " + d.getHours() + ":" + d.getMinutes()+ ":" + d.getSeconds()
		}
		cal.sel.value = date;
		if (cal.dateClicked && (cal.sel.id == "sel1" || cal.sel.id == "sel3")) cal.callCloseHandler();
	}
	function closeHandler(cal) {
		cal.hide();
	}
	showCalendar = function(id,format,hm) {
		var format = arguments[1] ? arguments[1] : "y-mm-dd";
		var hm = arguments[2] ? arguments[2] : "";

		var el = typeof id == "string" ? document.getElementById(id) : id;
		calendar = null;
		el.focus();
		var cdiv = document.getElementsByTagName("div"),
			carr = [];
		for(var i = 0,len = cdiv.length;i < len; i++){
			if(cdiv[i].className == "calendar") {
				carr[carr.length] = cdiv[i];
			}
		}
		if(carr[0]){
			document.getElementsByTagName("body")[0].removeChild(carr[0]);
		}
		if (calendar != null) {
			calendar.hide();
		} else {
			var cal = new Calendar(false, null, selected, closeHandler);
			calendar = cal;
			cal.setRange(1900, 2200);
			cal.create();
		}
		calendar.setDateFormat(format);
		calendar.hm = hm;
		calendar.parseDate(el.value);
		calendar.sel = el;
		calendar.showAtElement(el, "Br");
		return false;
	}
	var MINUTE = 60 * 1000;
	var HOUR = 60 * MINUTE;
	var DAY = 24 * HOUR;
	var WEEK = 7 * DAY;
	function isDisabled(date) {
		var today = new Date();
		return (Math.abs(date.getTime() - today.getTime()) / DAY) > 10;
	}
	function flatSelected(cal, date) {
		var el = document.getElementById("preview");
		el.innerHTML = date;
	}
	function showFlatCalendar() {
		var parent = document.getElementById("display");
		var cal = new Calendar(false, null, flatSelected);
		cal.weekNumbers = false;
		cal.setDisabledHandler(isDisabled);
		cal.setDateFormat("DD, M d");
		cal.create(parent);
		cal.show();
	}
})();